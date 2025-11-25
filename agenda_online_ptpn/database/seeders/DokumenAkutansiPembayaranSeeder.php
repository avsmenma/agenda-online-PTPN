<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dokumen;
use App\Models\DokumenPO;
use App\Models\DokumenPR;
use App\Models\DibayarKepada;
use Carbon\Carbon;

class DokumenAkutansiPembayaranSeeder extends Seeder
{
    /**
     * Seeder untuk membuat data dummy dokumen dari Akutansi ke Pembayaran
     * Dokumen yang sudah melewati proses perpajakan dan dikirim ke pembayaran
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');

        // Data kategori dan jenis dokumen
        $kategoris = ['Operasional', 'Investasi', 'Rutin', 'Non-Rutin'];
        $jenisDokumens = ['SPP-LS', 'SPP-UP', 'SPP-GU', 'SPP-TU'];
        $jenisSubPekerjaans = ['Pengadaan Barang', 'Jasa Konsultansi', 'Jasa Konstruksi', 'Jasa Lainnya'];
        $jenisPembayarans = ['Transfer Bank', 'Tunai', 'Cek', 'Giro'];
        $jenisPphs = ['PPh 21', 'PPh 22', 'PPh 23', 'PPh 4(2)', 'PPh 15'];

        // Nama-nama vendor/penerima pembayaran
        $vendors = [
            'PT. Maju Bersama Sejahtera',
            'CV. Karya Mandiri',
            'PT. Sumber Makmur Abadi',
            'UD. Berkah Jaya',
            'PT. Nusantara Prima',
            'CV. Cipta Karya Utama',
            'PT. Global Teknik Indonesia',
            'CV. Sinar Harapan',
            'PT. Mega Konstruksi',
            'UD. Sentosa Abadi',
        ];

        // Bagian/departemen
        $bagians = ['Keuangan', 'Operasional', 'SDM', 'Produksi', 'Pengadaan'];

        // Nama pengirim
        $namaPengirims = ['Budi Santoso', 'Siti Rahayu', 'Ahmad Fauzi', 'Dewi Lestari', 'Eko Prasetyo'];

        // Buat 15 dokumen dummy yang sudah dikirim dari Akutansi ke Pembayaran
        $dokumenData = [];

        for ($i = 1; $i <= 15; $i++) {
            $tanggalMasuk = Carbon::now()->subDays(rand(10, 60));
            $tanggalSpp = $tanggalMasuk->copy()->addDays(rand(1, 3));
            $sentToIbubAt = $tanggalMasuk->copy()->addDays(1);
            $processedAt = $sentToIbubAt->copy()->addDays(rand(1, 2));
            $sentToPerpajakanAt = $processedAt->copy()->addDays(1);
            $processedPerpajakanAt = $sentToPerpajakanAt->copy()->addDays(rand(1, 3));
            $sentToPembayaranAt = $processedPerpajakanAt->copy()->addDays(1);

            $nilaiRupiah = rand(5, 500) * 1000000; // 5jt - 500jt
            $dppPph = $nilaiRupiah * 0.9;
            $ppnTerhutang = $nilaiRupiah * 0.11;

            // Generate nomor berita acara dan SPK
            $noBeritaAcara = 'BA/' . date('Y') . '/' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
            $tanggalBeritaAcara = $tanggalMasuk->copy()->subDays(rand(5, 15));
            $noSpk = 'SPK/' . date('Y') . '/' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
            $tanggalSpk = $tanggalBeritaAcara->copy()->subDays(rand(10, 30));
            $tanggalBerakhirSpk = $tanggalSpk->copy()->addMonths(rand(3, 12));

            $dokumen = Dokumen::create([
                'nomor_agenda' => 'AGD/' . date('Y') . '/' . str_pad($i + 100, 4, '0', STR_PAD_LEFT),
                'bulan' => Carbon::now()->format('F'),
                'tahun' => Carbon::now()->year,
                'tanggal_masuk' => $tanggalMasuk,
                'nomor_spp' => 'SPP/' . date('Y') . '/' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                'tanggal_spp' => $tanggalSpp,
                'uraian_spp' => $faker->sentence(10),
                'nilai_rupiah' => $nilaiRupiah,
                'kategori' => $kategoris[array_rand($kategoris)],
                'jenis_dokumen' => $jenisDokumens[array_rand($jenisDokumens)],
                'jenis_sub_pekerjaan' => $jenisSubPekerjaans[array_rand($jenisSubPekerjaans)],
                'jenis_pembayaran' => $jenisPembayarans[array_rand($jenisPembayarans)],
                'dibayar_kepada' => $vendors[array_rand($vendors)],

                // Bagian dan pengirim
                'bagian' => $bagians[array_rand($bagians)],
                'nama_pengirim' => $namaPengirims[array_rand($namaPengirims)],

                // Berita acara dan SPK
                'no_berita_acara' => $noBeritaAcara,
                'tanggal_berita_acara' => $tanggalBeritaAcara,
                'no_spk' => $noSpk,
                'tanggal_spk' => $tanggalSpk,
                'tanggal_berakhir_spk' => $tanggalBerakhirSpk,

                // Status: sudah dikirim ke pembayaran
                'status' => 'sent_to_pembayaran',

                // Workflow Ibu B
                'sent_to_ibub_at' => $sentToIbubAt,
                'processed_at' => $processedAt,
                'deadline_days' => rand(3, 7),
                'deadline_note' => 'Proses sesuai SOP',

                // Perpajakan fields - sudah selesai diproses
                'npwp' => $faker->numerify('##.###.###.#-###.###'),
                'status_perpajakan' => 'selesai',
                'no_faktur' => 'FKT-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'tanggal_faktur' => $processedPerpajakanAt->copy()->subDays(1),
                'tanggal_selesai_verifikasi_pajak' => $processedPerpajakanAt,
                'jenis_pph' => $jenisPphs[array_rand($jenisPphs)],
                'dpp_pph' => $dppPph,
                'ppn_terhutang' => $ppnTerhutang,
                'sent_to_perpajakan_at' => $sentToPerpajakanAt,
                'processed_perpajakan_at' => $processedPerpajakanAt,
                'deadline_perpajakan_days' => rand(2, 5),
                'deadline_perpajakan_note' => 'Verifikasi pajak selesai',

                // Pembayaran fields - baru dikirim, belum diproses
                'sent_to_pembayaran_at' => $sentToPembayaranAt,
                'status_pembayaran' => 'belum_dibayar',
                'deadline_pembayaran_days' => rand(3, 7),
                'deadline_pembayaran_note' => 'Menunggu proses pembayaran',

                // Metadata
                'created_by' => 1,
                'keterangan' => $faker->optional()->sentence(5),
            ]);

            // Tambahkan PO untuk setiap dokumen
            $numPOs = rand(1, 3);
            for ($j = 1; $j <= $numPOs; $j++) {
                DokumenPO::create([
                    'dokumen_id' => $dokumen->id,
                    'nomor_po' => 'PO/' . date('Y') . '/' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                ]);
            }

            // Tambahkan PR untuk setiap dokumen
            $numPRs = rand(1, 2);
            for ($k = 1; $k <= $numPRs; $k++) {
                DokumenPR::create([
                    'dokumen_id' => $dokumen->id,
                    'nomor_pr' => 'PR/' . date('Y') . '/' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                ]);
            }

            // Tambahkan penerima pembayaran
            DibayarKepada::create([
                'dokumen_id' => $dokumen->id,
                'nama_penerima' => $dokumen->dibayar_kepada,
            ]);

            $dokumenData[] = $dokumen;
        }

        $this->command->info('Berhasil membuat ' . count($dokumenData) . ' dokumen dummy dari Akutansi ke Pembayaran!');
    }
}
