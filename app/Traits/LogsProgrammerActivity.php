<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\ProgrammerActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait LogsProgrammerActivity
{
    /**
     * Catat aktivitas sensitif programmer ke database.
     *
     * @param  string       $action       Kode aksi (misal: 'user_update', 'reset_2fa')
     * @param  mixed|null   $target       Model target (opsional) atau array ['type'=>'...', 'id'=>..., 'description'=>'...']
     * @param  string       $description  Deskripsi tambahan (opsional, dipakai jika $target adalah string/null)
     */
    protected function logActivity(string $action, mixed $target = null, string $description = ''): void
    {
        try {
            $programmerId = Auth::id();

            // Jika tidak ada user yang login, skip logging
            if (!$programmerId) {
                return;
            }

            // Ekstrak informasi target
            [$targetType, $targetId, $targetDescription] = $this->resolveTarget($target, $description);

            ProgrammerActivityLog::create([
                'programmer_id'      => $programmerId,
                'action'             => $action,
                'target_type'        => $targetType,
                'target_id'          => $targetId,
                'target_description' => $targetDescription ?: null,
                'ip_address'         => request()->ip() ?? '0.0.0.0',
                'user_agent'         => substr(request()->userAgent() ?? '', 0, 500),
            ]);
        } catch (\Throwable $e) {
            // Log error tapi jangan sampai gagalkan proses utama
            Log::error('[ProgrammerActivityLog] Gagal mencatat log: ' . $e->getMessage(), [
                'action'      => $action,
                'programmer'  => Auth::id(),
            ]);
        }
    }

    /**
     * Resolve informasi target dari berbagai format input.
     *
     * @return array{0: string|null, 1: int|null, 2: string|null}
     */
    private function resolveTarget(mixed $target, string $description): array
    {
        // Jika target adalah Eloquent Model
        if ($target instanceof \Illuminate\Database\Eloquent\Model) {
            return [
                class_basename($target),
                (int) $target->getKey(),
                $description ?: $this->getModelDescription($target),
            ];
        }

        // Jika target adalah array asosiatif
        if (is_array($target)) {
            return [
                $target['type'] ?? null,
                isset($target['id']) ? (int) $target['id'] : null,
                $target['description'] ?? $description ?: null,
            ];
        }

        // Jika target adalah string (deskripsi langsung)
        if (is_string($target) && $target !== '') {
            return [null, null, $target];
        }

        // Tidak ada target, pakai description saja
        return [null, null, $description ?: null];
    }

    /**
     * Coba ambil representasi deskriptif dari Eloquent Model.
     */
    private function getModelDescription(\Illuminate\Database\Eloquent\Model $model): string
    {
        foreach (['name', 'username', 'title', 'nomor_agenda', 'email'] as $field) {
            if (isset($model->$field) && is_string($model->$field)) {
                return $model->$field;
            }
        }
        return class_basename($model) . ' #' . $model->getKey();
    }
}
