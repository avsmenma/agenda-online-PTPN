<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDokumenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow all users for now, adjust as needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $dokumenId = $this->route('dokumen')->id;

        return [
            'nomor_agenda' => 'required|string|unique:dokumens,nomor_agenda,' . $dokumenId,
            'bagian' => 'required|string|in:DPM,SKH,SDM,TEP,KPL,AKN,TAN,PMO',
            'nama_pengirim' => 'nullable|string|max:255',
            'nomor_spp' => 'required|string',
            'tanggal_spp' => 'required|date',
            'uraian_spp' => 'required|string',
            'nilai_rupiah' => 'required|string',
            'kriteria_cf' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!\App\Models\KategoriKriteria::on('cash_bank')->where('id_kategori_kriteria', $value)->exists()) {
                        $fail('Kriteria CF yang dipilih tidak valid.');
                    }
                },
            ],
            'sub_kriteria' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!\App\Models\SubKriteria::on('cash_bank')->where('id_sub_kriteria', $value)->exists()) {
                        $fail('Sub Kriteria yang dipilih tidak valid.');
                    }
                },
            ],
            'item_sub_kriteria' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!\App\Models\ItemSubKriteria::on('cash_bank')->where('id_item_sub_kriteria', $value)->exists()) {
                        $fail('Item Sub Kriteria yang dipilih tidak valid.');
                    }
                },
            ],
            'jenis_pembayaran' => 'nullable|string',
            'dibayar_kepada' => 'array',
            'dibayar_kepada.*' => 'nullable|distinct|string|max:255',
            'no_berita_acara' => 'nullable|string',
            'tanggal_berita_acara' => 'nullable|date',
            'no_spk' => 'nullable|string',
            'tanggal_spk' => 'nullable|date',
            'tanggal_berakhir_spk' => 'nullable|date|after_or_equal:tanggal_spk',
            'nomor_po' => 'array',
            'nomor_po.*' => 'nullable|string',
            'nomor_pr' => 'array',
            'nomor_pr.*' => 'nullable|string',
        ];
    }

    /**
     * Get the custom error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nomor_agenda.unique' => 'Nomor agenda sudah digunakan. Silakan gunakan nomor lain.',
            'bagian.required' => 'Bagian harus dipilih.',
            'bagian.in' => 'Bagian tidak valid. Pilih salah satu dari opsi yang tersedia.',
            'nama_pengirim.max' => 'Nama pengirim maksimal 255 karakter.',
            'kategori.in' => 'Kategori tidak valid. Pilih salah satu dari opsi yang tersedia.',
            'tanggal_berakhir_spk.after_or_equal' => 'Tanggal berakhir SPK harus sama atau setelah tanggal SPK.',
            'dibayar_kepada.*.max' => 'Nama penerima maksimal 255 karakter.',
            'dibayar_kepada.*.distinct' => 'Nama penerima tidak boleh duplikat dalam satu form.',
            'tanggal_spp.required' => 'Tanggal SPP harus diisi.',
            'tanggal_spp.date' => 'Format tanggal SPP tidak valid.',
            'uraian_spp.required' => 'Uraian SPP harus diisi.',
            'nilai_rupiah.required' => 'Nilai rupiah harus diisi.',
        ];
    }
}
