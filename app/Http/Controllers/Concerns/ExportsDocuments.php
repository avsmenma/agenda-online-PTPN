<?php

namespace App\Http\Controllers\Concerns;

use App\Support\DocumentExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menyatukan respons export dokumen (Excel/PDF) lintas-role. Dipakai controller
 * tiap role (Task 3-4: operator/akutansi/perpajakan/verifikasi/pembayaran) agar
 * dispatch format & sanitasi nama berkas tidak diduplikasi berkali-kali seperti
 * 6 tabel role (CLAUDE.md §1 "penyakit utama").
 *
 * Konsumen: App\Support\DocumentExporter (Task 1, TIDAK diubah di sini) untuk
 * jalur Excel; view exports.document-print untuk jalur PDF (Task 2 bagian a).
 */
trait ExportsDocuments
{
    /**
     * Bangun respons unduhan Excel (.xls) atau tampilan cetak PDF sesuai
     * `?format=excel|pdf` (default excel) dari request.
     *
     * @param  iterable<int, array<string, mixed>>  $rows  Collection<array>|array<array> baris DTO — bentuk array, bukan objek.
     * @param  array<int, array{key: string, label: string}>  $columns
     * @param  array{title?: string, total_key?: string, sheets?: array<int, array<string, mixed>>}  $options  Diteruskan apa adanya ke DocumentExporter::toXlsx().
     */
    protected function respondDocumentExport(Request $request, iterable $rows, array $columns, array $options = []): Response
    {
        $format = $request->get('format', 'excel');
        $rowsArray = $this->normalizeExportRows($rows);
        $title = $options['title'] ?? 'Export';

        if ($format === 'pdf') {
            return response()->view('exports.document-print', [
                'columns' => $columns,
                'rows'    => $rowsArray,
                'title'   => $title,
            ]);
        }

        $xml = DocumentExporter::toXlsx($columns, $rowsArray, $options);
        $filename = $this->exportFilename($title);

        return response($xml, 200, [
            'Content-Type'        => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.xls"',
        ]);
    }

    /**
     * Normalisasi $rows (bisa Collection<array>, array<array>, atau iterable
     * lain berisi baris DTO) → array<int, array> polos, siap dikonsumsi
     * DocumentExporter::toXlsx() maupun view exports.document-print.
     *
     * @param  iterable<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function normalizeExportRows(iterable $rows): array
    {
        if ($rows instanceof Collection) {
            return $rows->map(fn ($row) => is_array($row) ? $row : (array) $row)->values()->all();
        }

        $result = [];
        foreach ($rows as $row) {
            $result[] = is_array($row) ? $row : (array) $row;
        }

        return $result;
    }

    /**
     * Nama berkas unduhan yang aman dari judul export + tanggal hari ini
     * (mis. "Dokumen Akutansi" → "Dokumen-Akutansi-2026-07-26"). Judul yang
     * kosong setelah sanitasi jatuh ke 'export'.
     */
    private function exportFilename(string $title): string
    {
        $slug = (string) preg_replace('/[^A-Za-z0-9_-]+/', '-', trim($title));
        $slug = trim($slug, '-');
        $slug = $slug === '' ? 'export' : $slug;

        return $slug.'-'.now()->format('Y-m-d');
    }
}
