<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

/**
 * Resolusi preferensi KOLOM BEKU bersama untuk 5 role keuangan.
 *
 * Cakupan sengaja dibatasi ke kolom beku saja — pemilihan kolom biasa tetap
 * di controller masing-masing karena aturannya memang berbeda per role
 * (legacy default operator, penyaringan status/nomor_mirror, dsb).
 *
 * Kelas biasa, bukan trait: logikanya murni sehingga bisa di-unit-test tanpa
 * kelas inang. $user dioper eksplisit supaya test tak perlu menyalakan sesi auth.
 */
class ColumnCustomization
{
    /**
     * @param  array{
     *     available: array<string,string>,
     *     selected: array<int,string>,
     *     default: array{left: array<int,string>, right: array<int,string>},
     *     pinnedLeft: array<int,string>,
     *     prefKey: ?string,
     *     sessionKey: string
     * }  $options
     * @return array{left: array<int,string>, right: array<int,string>, render: array<int,string>}
     */
    public static function resolveFrozen(Request $request, ?Authenticatable $user, array $options): array
    {
        $available  = $options['available'];
        $selected   = array_values($options['selected']);
        $default    = $options['default'];
        $pinnedLeft = $options['pinnedLeft'] ?? [];
        $prefKey    = $options['prefKey'] ?? null;
        $sessionKey = $options['sessionKey'];

        // Penanda WAJIB: tanpa frozen_config, "user melepas SEMUA kolom beku"
        // (tidak mengirim frozen_left/frozen_right) tampak sama persis dengan
        // "request tidak membawa konfigurasi beku" — preferensi lama akan dipakai
        // ulang dan user tak pernah bisa mengosongkan kolom beku.
        $hasFrozenRequest = $request->has('frozen_config')
            || $request->has('frozen_left')
            || $request->has('frozen_right');

        if ($hasFrozenRequest) {
            $raw = [
                'left'  => (array) $request->get('frozen_left', []),
                'right' => (array) $request->get('frozen_right', []),
            ];
        } else {
            $raw = self::readStored($user, $prefKey, $sessionKey, $default);
        }

        // Kolom pinned selalu beku kiri: mesin tabel (document-tabulator.js)
        // membekukan nomor_agenda tanpa syarat, jadi urutan render wajib sejalan
        // agar kolom beku tetap menempel tepi.
        $left = array_values((array) ($raw['left'] ?? []));
        foreach (array_reverse($pinnedLeft) as $key) {
            $left = array_values(array_diff($left, [$key]));
            array_unshift($left, $key);
        }

        $right = array_values(array_diff((array) ($raw['right'] ?? []), $pinnedLeft));

        $frozen = FrozenColumnLayout::normalize($left, $right, $selected, $available);

        if ($hasFrozenRequest && $user !== null && $prefKey !== null) {
            $preferences = $user->table_columns_preferences ?? [];
            $preferences[$prefKey] = $frozen;
            $user->table_columns_preferences = $preferences;
            $user->save();
        }

        session([$sessionKey => $frozen]);

        return [
            'left'   => $frozen['left'],
            'right'  => $frozen['right'],
            'render' => FrozenColumnLayout::renderOrder($selected, $frozen['left'], $frozen['right']),
        ];
    }

    /**
     * Urutan baca preferensi tersimpan: DB (permanen) -> sesi -> default.
     *
     * @return array{left: array<int,string>, right: array<int,string>}
     */
    private static function readStored(?Authenticatable $user, ?string $prefKey, string $sessionKey, array $default): array
    {
        if ($user !== null && $prefKey !== null && isset($user->table_columns_preferences[$prefKey])) {
            $stored = $user->table_columns_preferences[$prefKey];

            if (is_array($stored)) {
                return $stored;
            }
        }

        $stored = session($sessionKey, $default);

        return is_array($stored) ? $stored : $default;
    }
}
