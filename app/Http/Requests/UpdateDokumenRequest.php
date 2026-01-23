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

        // Check if using dropdown mode (cash_bank available) or manual mode
        $isDropdownMode = $this->filled('kriteria_cf') && $this->filled('sub_kriteria') && $this->filled('item_sub_kriteria');
        $isManualMode = $this->filled('kategori') && $this->filled('jenis_dokumen') && $this->filled('jenis_sub_pekerjaan');

        // Get valid bagian codes from database
        $validBagianCodes = [];
        try {
            $validBagianCodes = \App\Models\Bagian::active()->pluck('kode')->toArray();
        } catch (\Exception $e) {
            // Fallback to hardcoded values if database error
            $validBagianCodes = ['DPM', 'SKH', 'SDM', 'TEP', 'KPL', 'AKN', 'TAN', 'PMO'];
        }

        $rules = [
            'nomor_agenda' => 'nullable|string|unique:dokumens,nomor_agenda,' . $dokumenId,
            'bagian' => $validBagianCodes ? 'nullable|string|in:' . implode(',', $validBagianCodes) : 'nullable|string',
            'nama_pengirim' => 'nullable|string|max:255',
            'nomor_spp' => 'nullable|string',
            'tanggal_spp' => 'nullable|date',
            'uraian_spp' => 'nullable|string',
            'nilai_rupiah' => 'nullable|string',
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

        // Semua field optional (tidak wajib)
        $rules['kriteria_cf'] = 'nullable|integer';
        $rules['sub_kriteria'] = 'nullable|integer';
        $rules['item_sub_kriteria'] = 'nullable|integer';
        $rules['kategori'] = 'nullable|string|max:255';
        $rules['jenis_dokumen'] = 'nullable|string|max:255';
        $rules['jenis_sub_pekerjaan'] = 'nullable|string|max:255';

        return $rules;
    }

    /**
     * Get the custom error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nomor_agenda.required' => 'Nomor agenda wajib diisi.',
            'nomor_agenda.unique' => 'Nomor agenda sudah digunakan. Silakan gunakan nomor lain.',
            'bagian.required' => 'Bagian wajib dipilih.',
            'bagian.in' => 'Bagian tidak valid. Pilih salah satu dari opsi yang tersedia.',
            'nama_pengirim.max' => 'Nama pengirim maksimal 255 karakter.',
            'nomor_spp.required' => 'Nomor SPP wajib diisi.',
            'tanggal_spp.required' => 'Tanggal SPP wajib diisi.',
            'tanggal_spp.date' => 'Format tanggal SPP tidak valid.',
            'uraian_spp.required' => 'Uraian SPP wajib diisi.',
            'nilai_rupiah.required' => 'Nilai rupiah wajib diisi.',
            'kriteria_cf.required' => 'Kriteria CF wajib dipilih.',
            'sub_kriteria.required' => 'Sub Kriteria wajib dipilih.',
            'item_sub_kriteria.required' => 'Item Sub Kriteria wajib dipilih.',
            'kategori.required' => 'Kategori wajib diisi.',
            'jenis_dokumen.required' => 'Jenis Dokumen wajib diisi.',
            'jenis_sub_pekerjaan.required' => 'Jenis Sub Pekerjaan wajib diisi.',
            'tanggal_berakhir_spk.after_or_equal' => 'Tanggal berakhir SPK harus sama atau setelah tanggal SPK.',
            'dibayar_kepada.*.max' => 'Nama penerima maksimal 255 karakter.',
            'dibayar_kepada.*.distinct' => 'Nama penerima tidak boleh duplikat dalam satu form.',
        ];
    }
}


