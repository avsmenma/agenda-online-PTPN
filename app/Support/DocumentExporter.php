<?php

namespace App\Support;

/**
 * Penulis Excel tanpa dependensi composer — menghasilkan dokumen "XML
 * Spreadsheet 2003" (.xls yang dibuka Excel via progid Excel.Sheet), dipakai
 * bersama oleh fitur export dokumen lintas-role.
 *
 * Digeneralisasi dari struktur XML yang sudah terbukti di
 * OwnerDashboardController::exportRekapanKeterlambatan() (~baris 1996-2240):
 * palet <Styles> (Title/Header/Cell/CellRight + border), <Worksheet><Table>
 * <Row><Cell><Data ss:Type="...">, tapi di sini data-driven lewat
 * $columns + $rows alih-alih hardcode kolom rekapan keterlambatan.
 *
 * Satu sheet: toXlsx($columns, $rows, ['title' => .., 'total_key' => ..]).
 * Multi-sheet (mis. export pembayaran per-vendor):
 * toXlsx($columns, [], ['sheets' => [['name'=>.., 'rows'=>[...], 'subtotal'=>true], ...]]).
 */
class DocumentExporter
{
    /**
     * Kolom tanggal yang disimpan DocumentRow::baseRow() di bawah kunci
     * 'dates' (App\Support\DocumentRow::formatDates()), bukan sebagai
     * kunci top-level baris.
     */
    private const DATE_KEYS = [
        'tanggal_spp',
        'tanggal_berita_acara',
        'tanggal_spk',
        'tanggal_berakhir_spk',
        'tanggal_masuk',
        'tanggal_paraf',
        'tanggal_selesai_diproses',
        'tanggal_kembali_ke_bagian',
        'tanggal_hasil_koreksi_bagian',
        'tanggal_dibayar',
        'tanggal_faktur',
        'tanggal_selesai_verifikasi_pajak',
    ];

    /**
     * Bangun dokumen XML Spreadsheet 2003 lengkap sebagai string.
     *
     * @param  array<int, array{key: string, label: string}>  $columns
     * @param  array<int, array<string, mixed>>  $rows  Diabaikan bila $options['sheets'] diisi.
     * @param  array{title?: string, total_key?: string, sheets?: array<int, array{name: string, rows: array<int, array<string, mixed>>, subtotal?: bool}>}  $options
     */
    public static function toXlsx(array $columns, array $rows, array $options = []): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            . "\n" . '<?mso-application progid="Excel.Sheet"?>'
            . "\n" . '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"'
            . ' xmlns:o="urn:schemas-microsoft-com:office:office"'
            . ' xmlns:x="urn:schemas-microsoft-com:office:excel"'
            . ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"'
            . ' xmlns:html="http://www.w3.org/TR/REC-html40">'
            . "\n" . self::stylesXml();

        $sheets = $options['sheets'] ?? null;

        if (is_array($sheets) && $sheets !== []) {
            $grandTotal = 0.0;
            $wantGrandTotal = false;

            foreach ($sheets as $sheet) {
                $sheetRows = $sheet['rows'] ?? [];
                $subtotal = (bool) ($sheet['subtotal'] ?? false);
                $sheetOptions = $options;
                unset($sheetOptions['sheets']);
                $sheetOptions['title'] = $sheet['name'] ?? ($options['title'] ?? null);

                $xml .= self::worksheetXml(
                    self::sanitizeSheetName((string) ($sheet['name'] ?? 'Sheet')),
                    $columns,
                    $sheetRows,
                    $sheetOptions,
                    $subtotal
                );

                if ($subtotal) {
                    $wantGrandTotal = true;
                    $totalKey = $options['total_key'] ?? null;
                    if ($totalKey !== null) {
                        foreach ($sheetRows as $row) {
                            $grandTotal += self::numericValue($row, $totalKey);
                        }
                    }
                }
            }

            if ($wantGrandTotal && isset($options['total_key'])) {
                $xml .= self::grandTotalWorksheetXml($columns, $grandTotal);
            }
        } else {
            $title = $options['title'] ?? 'Sheet1';
            $xml .= self::worksheetXml(self::sanitizeSheetName($title), $columns, $rows, $options, false);
        }

        $xml .= '</Workbook>';

        return $xml;
    }

    /**
     * Ambil nilai tampilan sebuah kolom dari baris DTO. Prioritas:
     * 1. "<key>_formatted" bila kunci itu ada di baris (mis. nilai_rupiah_formatted).
     * 2. row['dates'][key] bila key adalah kolom tanggal yang ter-daftar di sana.
     * 3. row[key] apa adanya.
     * 4. '-' bila tidak ditemukan / null.
     */
    public static function cellValue(array $row, string $key): string
    {
        $formattedKey = $key . '_formatted';
        if (array_key_exists($formattedKey, $row) && $row[$formattedKey] !== null) {
            return (string) $row[$formattedKey];
        }

        if (isset($row['dates']) && is_array($row['dates']) && array_key_exists($key, $row['dates'])) {
            $value = $row['dates'][$key];

            return $value === null ? '-' : (string) $value;
        }

        if (array_key_exists($key, $row)) {
            $value = $row[$key];

            if ($value === null || $value === '') {
                return '-';
            }

            return (string) $value;
        }

        return '-';
    }

    /** Escape entitas XML wajib. '&' harus lebih dulu agar tidak dobel-escape. */
    protected static function xmlEscape(string $v): string
    {
        $v = str_replace('&', '&amp;', $v);
        $v = str_replace('<', '&lt;', $v);
        $v = str_replace('>', '&gt;', $v);
        $v = str_replace('"', '&quot;', $v);

        return $v;
    }

    /** Palet <Styles> — disalin dari OwnerDashboardController (Title/Header/Cell/CellCenter/CellRight/Summary). */
    private static function stylesXml(): string
    {
        return <<<'XML'
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Center"/>
   <Font ss:FontName="Arial" ss:Size="10"/>
  </Style>
  <Style ss:ID="Title">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Font ss:FontName="Arial" ss:Size="14" ss:Bold="1" ss:Color="#FFFFFF"/>
   <Interior ss:Color="#2E7D32" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="Header">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Font ss:FontName="Arial" ss:Size="10" ss:Bold="1" ss:Color="#FFFFFF"/>
   <Interior ss:Color="#1976D2" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="Cell">
   <Alignment ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="CellCenter">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="CellRight">
   <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="Total">
   <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
   <Font ss:FontName="Arial" ss:Size="10" ss:Bold="1"/>
   <Interior ss:Color="#E3F2FD" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="TotalLabel">
   <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
   <Font ss:FontName="Arial" ss:Size="10" ss:Bold="1"/>
   <Interior ss:Color="#E3F2FD" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
 </Styles>

XML;
    }

    /**
     * Bangun satu blok <Worksheet>...</Worksheet>.
     *
     * @param  array<int, array{key: string, label: string}>  $columns
     * @param  array<int, array<string, mixed>>  $rows
     */
    private static function worksheetXml(string $sheetName, array $columns, array $rows, array $options, bool $forceTotalRow): string
    {
        $columnCount = max(count($columns), 1);
        $lastIndex = $columnCount - 1;

        $xml = ' <Worksheet ss:Name="' . self::xmlEscape($sheetName) . '">' . "\n";
        $xml .= '  <Table ss:DefaultColumnWidth="120">' . "\n";

        $title = $options['title'] ?? null;
        if ($title !== null && $title !== '') {
            $xml .= '   <Row ss:Height="26">' . "\n";
            $xml .= '    <Cell ss:StyleID="Title"' . ($lastIndex > 0 ? ' ss:MergeAcross="' . $lastIndex . '"' : '') . '><Data ss:Type="String">' . self::xmlEscape((string) $title) . '</Data></Cell>' . "\n";
            $xml .= '   </Row>' . "\n";
        }

        // Baris header.
        $xml .= '   <Row>' . "\n";
        foreach ($columns as $column) {
            $label = (string) ($column['label'] ?? $column['key'] ?? '');
            $xml .= '    <Cell ss:StyleID="Header"><Data ss:Type="String">' . self::xmlEscape($label) . '</Data></Cell>' . "\n";
        }
        $xml .= '   </Row>' . "\n";

        // Baris data.
        $totalKey = $options['total_key'] ?? null;
        $totalSum = 0.0;
        $hasTotalableRow = false;

        foreach ($rows as $row) {
            $xml .= '   <Row>' . "\n";
            foreach ($columns as $column) {
                $key = (string) ($column['key'] ?? '');
                $value = self::cellValue($row, $key);
                $isRightAligned = $totalKey !== null && $key === $totalKey;
                $styleId = $isRightAligned ? 'CellRight' : 'Cell';
                $xml .= '    <Cell ss:StyleID="' . $styleId . '"><Data ss:Type="String">' . self::xmlEscape($value) . '</Data></Cell>' . "\n";
            }
            $xml .= '   </Row>' . "\n";

            if ($totalKey !== null) {
                $totalSum += self::numericValue($row, $totalKey);
                $hasTotalableRow = true;
            }
        }

        // Baris TOTAL bila diminta (baris data ada, atau sheet ini eksplisit minta subtotal).
        if ($totalKey !== null && ($hasTotalableRow || $forceTotalRow)) {
            $xml .= self::totalRowXml($columns, $totalKey, $totalSum, $lastIndex);
        }

        $xml .= '  </Table>' . "\n";
        $xml .= ' </Worksheet>' . "\n";

        return $xml;
    }

    /**
     * Baris TOTAL bold: label "TOTAL" di kolom pertama, Σ total_key (Number)
     * di posisi kolom total_key, sel lain kosong. Bila total_key kebetulan
     * jadi kolom pertama, label disisipkan sebagai sel tambahan di depan
     * (tetap valid XML, hanya menambah satu sel di baris ini).
     */
    private static function totalRowXml(array $columns, string $totalKey, float $sum, int $lastIndex): string
    {
        $columns = array_values($columns);

        $totalKeyIndex = null;
        foreach ($columns as $i => $column) {
            if (($column['key'] ?? null) === $totalKey) {
                $totalKeyIndex = $i;
                break;
            }
        }

        $xml = '   <Row>' . "\n";

        if ($totalKeyIndex === 0) {
            $xml .= '    <Cell ss:StyleID="TotalLabel"><Data ss:Type="String">TOTAL</Data></Cell>' . "\n";
        }

        foreach ($columns as $i => $column) {
            if ($i === $totalKeyIndex) {
                $xml .= '    <Cell ss:StyleID="Total"><Data ss:Type="Number">' . self::formatNumberForXml($sum) . '</Data></Cell>' . "\n";
                continue;
            }

            if ($i === 0) {
                $xml .= '    <Cell ss:StyleID="TotalLabel"><Data ss:Type="String">TOTAL</Data></Cell>' . "\n";
                continue;
            }

            $xml .= '    <Cell ss:StyleID="Total"><Data ss:Type="String"></Data></Cell>' . "\n";
        }
        $xml .= '   </Row>' . "\n";

        return $xml;
    }

    /** Sheet ringkasan "GRAND TOTAL" untuk mode multi-sheet dengan subtotal. */
    private static function grandTotalWorksheetXml(array $columns, float $grandTotal): string
    {
        $lastIndex = max(count($columns), 1) - 1;

        $xml = ' <Worksheet ss:Name="Grand Total">' . "\n";
        $xml .= '  <Table ss:DefaultColumnWidth="120">' . "\n";
        $xml .= '   <Row ss:Height="26">' . "\n";
        $xml .= '    <Cell ss:StyleID="Title"' . ($lastIndex > 0 ? ' ss:MergeAcross="' . $lastIndex . '"' : '') . '><Data ss:Type="String">GRAND TOTAL</Data></Cell>' . "\n";
        $xml .= '   </Row>' . "\n";
        $xml .= '   <Row>' . "\n";
        $xml .= '    <Cell ss:StyleID="TotalLabel"><Data ss:Type="String">TOTAL KESELURUHAN</Data></Cell>' . "\n";
        $xml .= '    <Cell ss:StyleID="Total"><Data ss:Type="Number">' . self::formatNumberForXml($grandTotal) . '</Data></Cell>' . "\n";
        $xml .= '   </Row>' . "\n";
        $xml .= '  </Table>' . "\n";
        $xml .= ' </Worksheet>' . "\n";

        return $xml;
    }

    /** Ambil nilai numerik mentah kolom total_key dari baris (0 bila non-numerik). */
    private static function numericValue(array $row, string $key): float
    {
        $value = $row[$key] ?? null;

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (float) $value;
        }

        return 0.0;
    }

    /** Angka bulat tampil tanpa desimal; pecahan tetap presisi. */
    private static function formatNumberForXml(float $n): string
    {
        if (floor($n) === $n) {
            return (string) (int) $n;
        }

        return rtrim(rtrim(sprintf('%.4f', $n), '0'), '.');
    }

    /** Nama sheet Excel dibatasi 31 karakter dan tak boleh memuat karakter terlarang. */
    private static function sanitizeSheetName(string $name): string
    {
        $name = str_replace([':', '\\', '/', '?', '*', '[', ']'], ' ', $name);
        $name = trim($name);

        if ($name === '') {
            $name = 'Sheet1';
        }

        if (function_exists('mb_substr')) {
            return mb_substr($name, 0, 31);
        }

        return substr($name, 0, 31);
    }
}
