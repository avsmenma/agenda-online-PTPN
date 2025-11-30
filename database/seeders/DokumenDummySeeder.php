<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dokumen;
use App\Models\DokumenPR;
use App\Models\DokumenPO;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DokumenDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data yang akan digunakan berulang (beberapa dokumen akan memiliki data yang sama)
        $bagianList = ['DPM', 'SKH', 'SDM', 'TEP', 'KPL', 'AKN', 'TAN', 'PMO'];
        $namaPengirimList = [
            'Ibu Tarapul',
            'Bapak Ahmad',
            'Ibu Siti',
            'Bapak Budi',
            'Ibu Rina',
            'Bapak Joko',
            'Ibu Maya',
            'Bapak Agung'
        ];
        
        // Data yang akan diulang untuk beberapa dokumen
        $vendorList = [
            'PT ABC Perkebunan',
            'PT XYZ Konstruksi',
            'CV DEF Supplier',
            'PT GHI Trading',
            'PT JKL Services',
            'PT MNO Agriculture',
            'CV PQR Logistics',
            'PT STU Manufacturing'
        ];
        
        $kebunList = [
            'Kebun A',
            'Kebun B',
            'Kebun C',
            'Kebun D',
            'Kebun E',
            'Kebun F',
            'Kebun G',
            'Kebun H'
        ];
        
        $kategoriList = [
            'Operasional',
            'Investasi',
            'Pemeliharaan',
            'Pengembangan'
        ];
        
        $jenisDokumenList = [
            'SPP',
            'Kontrak',
            'PO',
            'BA',
            'SPK'
        ];
        
        $jenisSubPekerjaanList = [
            'Surat Masuk/Keluar Reguler',
            'Surat Undangan',
            'Surat Perintah',
            'Surat Keputusan',
            'Surat Tugas'
        ];
        
        $jenisPembayaranList = [
            'Karyawan',
            'Mitra',
            'MPN',
            'TBS',
            'Transfer',
            'Tunai'
        ];
        
        $uraianSppList = [
            'Pembayaran kontraktor pekerjaan konstruksi',
            'Pembayaran vendor material bangunan',
            'Pembayaran supplier alat berat',
            'Pembayaran jasa konsultan',
            'Pembayaran material konstruksi',
            'Pembayaran tenaga kerja',
            'Pembayaran sewa alat',
            'Pembayaran transportasi',
            'Pembayaran maintenance',
            'Pembayaran operasional harian'
        ];
        
        // Generate 100 dokumen
        for ($i = 1; $i <= 100; $i++) {
            // Generate nomor agenda (format: AGD/XXX/MM/YYYY)
            $bulan = str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT);
            $tahun = 2024;
            $nomorUrut = str_pad($i, 3, '0', STR_PAD_LEFT);
            $nomorAgenda = "AGD/{$nomorUrut}/{$bulan}/{$tahun}";
            
            // Pastikan nomor agenda unique
            while (Dokumen::where('nomor_agenda', $nomorAgenda)->exists()) {
                $nomorUrut = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
                $nomorAgenda = "AGD/{$nomorUrut}/{$bulan}/{$tahun}";
            }
            
            // Generate tanggal SPP (random dalam 6 bulan terakhir)
            $tanggalSpp = Carbon::now()->subMonths(rand(0, 6))->subDays(rand(0, 30));
            $tanggalMasuk = $tanggalSpp->copy()->addDays(rand(1, 5));
            
            // Generate tanggal BA (setelah tanggal SPP)
            $tanggalBa = $tanggalSpp->copy()->addDays(rand(5, 15));
            
            // Generate tanggal SPK (setelah tanggal BA)
            $tanggalSpk = $tanggalBa->copy()->addDays(rand(1, 10));
            $tanggalBerakhirSpk = $tanggalSpk->copy()->addDays(rand(30, 90));
            
            // Generate nomor SPP
            $bagian = $bagianList[array_rand($bagianList)];
            $nomorSpp = rand(1, 999) . "/M/SPP/" . rand(1, 31) . "/" . str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT) . "/" . $tahun;
            
            // Pastikan nomor SPP unique
            while (Dokumen::where('nomor_spp', $nomorSpp)->exists()) {
                $nomorSpp = rand(1, 999) . "/M/SPP/" . rand(1, 31) . "/" . str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT) . "/" . $tahun;
            }
            
            // Pilih data yang akan diulang (beberapa dokumen akan memiliki data yang sama)
            // Untuk membuat beberapa dokumen dengan data yang sama, kita akan menggunakan modulo
            $vendorIndex = ($i - 1) % count($vendorList);
            $kebunIndex = ($i - 1) % count($kebunList);
            $kategoriIndex = ($i - 1) % count($kategoriList);
            $jenisDokumenIndex = ($i - 1) % count($jenisDokumenList);
            $jenisSubPekerjaanIndex = ($i - 1) % count($jenisSubPekerjaanList);
            $jenisPembayaranIndex = ($i - 1) % count($jenisPembayaranList);
            
            // Generate nilai rupiah (antara 10 juta sampai 1 milyar)
            $nilaiRupiah = rand(10000000, 1000000000);
            
            // Generate nomor PR dan PO
            $nomorPr = "PR/" . str_pad($i, 4, '0', STR_PAD_LEFT) . "/" . $bulan . "/" . $tahun;
            $nomorPo = "PO/" . str_pad($i, 4, '0', STR_PAD_LEFT) . "/" . $bulan . "/" . $tahun;
            
            // Generate nomor BA dan SPK
            $noBeritaAcara = "BA/" . str_pad($i, 3, '0', STR_PAD_LEFT) . "/" . $bulan . "/" . $tahun;
            $noSpk = "SPK/" . str_pad($i, 3, '0', STR_PAD_LEFT) . "/" . $bulan . "/" . $tahun;
            
            // Generate nomor mirror
            $nomorMirror = "MIR-" . str_pad($i, 4, '0', STR_PAD_LEFT);
            
            // Get bulan Indonesia
            $bulanIndonesia = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            
            // Create dokumen
            $dokumen = Dokumen::create([
                'nomor_agenda' => $nomorAgenda,
                'bulan' => $bulanIndonesia[(int)$bulan],
                'tahun' => $tahun,
                'tanggal_masuk' => $tanggalMasuk,
                'nomor_spp' => $nomorSpp,
                'tanggal_spp' => $tanggalSpp,
                'uraian_spp' => $uraianSppList[array_rand($uraianSppList)],
                'nilai_rupiah' => $nilaiRupiah,
                'kategori' => $kategoriList[$kategoriIndex],
                'jenis_dokumen' => $jenisDokumenList[$jenisDokumenIndex],
                'jenis_sub_pekerjaan' => $jenisSubPekerjaanList[$jenisSubPekerjaanIndex],
                'jenis_pembayaran' => $jenisPembayaranList[$jenisPembayaranIndex],
                'kebun' => $kebunList[$kebunIndex],
                'bagian' => $bagian,
                'nama_pengirim' => $namaPengirimList[array_rand($namaPengirimList)],
                'dibayar_kepada' => $vendorList[$vendorIndex],
                'no_berita_acara' => $noBeritaAcara,
                'tanggal_berita_acara' => $tanggalBa,
                'no_spk' => $noSpk,
                'tanggal_spk' => $tanggalSpk,
                'tanggal_berakhir_spk' => $tanggalBerakhirSpk,
                'nomor_mirror' => $nomorMirror,
                'status' => 'draft',
                'created_by' => 'ibuA',
                'current_handler' => 'ibuA',
            ]);
            
            // Create PR
            DokumenPR::create([
                'dokumen_id' => $dokumen->id,
                'nomor_pr' => $nomorPr,
            ]);
            
            // Create PO
            DokumenPO::create([
                'dokumen_id' => $dokumen->id,
                'nomor_po' => $nomorPo,
            ]);
            
            $this->command->info("Created dokumen {$i}/100: {$nomorAgenda}");
        }
        
        $this->command->info('Successfully created 100 dummy documents!');
    }
}

