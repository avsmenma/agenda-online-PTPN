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

        $rules = [
            'nomor_agenda' => 'required|string|unique:dokumens,nomor_agenda,' . $dokumenId,
            'bagian' => 'required|string|in:DPM,SKH,SDM,TEP,KPL,AKN,TAN,PMO',
            'nama_pengirim' => 'nullable|string|max:255',
            'nomor_spp' => 'required|string',
            'tanggal_spp' => 'required|date',
            'uraian_spp' => 'required|string',
            'nilai_rupiah' => 'required|string',
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

        // Conditional validation based on mode
        if ($isDropdownMode) {
            // Dropdown mode: validate kriteria_cf, sub_kriteria, item_sub_kriteria
            $rules['kriteria_cf'] = [
                'required',
                function ($attribute, $value, $fail) {
                    try {
                        if (!\App\Models\KategoriKriteria::on('cash_bank')->where('id_kategori_kriteria', $value)->exists()) {
                            $fail('Kriteria CF yang dipilih tidak valid.');
                        }
                    } catch (\Exception $e) {
                        \Log::error('Error validating kriteria_cf (cash_bank not available): ' . $e->getMessage());
                        // Skip validation jika database tidak tersedia (backward compatibility)
                    }
                },
            ];
            $rules['sub_kriteria'] = [
                'required',
                function ($attribute, $value, $fail) {
                    try {
                        if (!\App\Models\SubKriteria::on('cash_bank')->where('id_sub_kriteria', $value)->exists()) {
                            $fail('Sub Kriteria yang dipilih tidak valid.');
                        }
                    } catch (\Exception $e) {
                        \Log::error('Error validating sub_kriteria (cash_bank not available): ' . $e->getMessage());
                        // Skip validation jika database tidak tersedia (backward compatibility)
                    }
                },
            ];
            $rules['item_sub_kriteria'] = [
                'required',
                function ($attribute, $value, $fail) {
                    try {
                        if (!\App\Models\ItemSubKriteria::on('cash_bank')->where('id_item_sub_kriteria', $value)->exists()) {
                            $fail('Item Sub Kriteria yang dipilih tidak valid.');
                        }
                    } catch (\Exception $e) {
                        \Log::error('Error validating item_sub_kriteria (cash_bank not available): ' . $e->getMessage());
                        // Skip validation jika database tidak tersedia (backward compatibility)
                    }
                },
            ];
            // Manual fields optional in dropdown mode
            $rules['kategori'] = 'nullable|string';
            $rules['jenis_dokumen'] = 'nullable|string';
            $rules['jenis_sub_pekerjaan'] = 'nullable|string';
        } else {
            // Manual mode: validate kategori, jenis_dokumen, jenis_sub_pekerjaan
            $rules['kategori'] = 'required|string';
            $rules['jenis_dokumen'] = 'required|string';
            $rules['jenis_sub_pekerjaan'] = 'required|string';
            // Dropdown fields optional in manual mode
            $rules['kriteria_cf'] = 'nullable';
            $rules['sub_kriteria'] = 'nullable';
            $rules['item_sub_kriteria'] = 'nullable';
        }

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
