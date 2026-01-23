<?php

namespace App\Helpers;

class KebunOptions
{
    /**
     * Get list of kebun options
     */
    public static function getOptions(): array
    {
        return [
            'KEBUN-UNIT',
            '2 REGION OFFICE',
            '3 UNIT GRUP KALBAR',
            '4 GUNUNG MELIAU',
            '5 PKS GUNME',
            '6 SUNGAI DEKAN',
            '7 RIMBA BELIAN',
            '8 PKS RIMBA BELIA',
            '9 GUNUNG MAS',
            '10 SINTANG',
            '11 NGABANG',
            '12 PKS NGABANG',
            '13 PARINDU',
            '14 PKS PARINDU',
            '15 KEMBAYAN',
            '16 PKS KEMBAYAN',
            '17 PPPBB',
            '18 UNIT GRUP KALSEL/TENG',
            '19 DANAU SALAK',
            '20 TAMBARANGAN',
            '21 BATULICIN',
            '22 PELAIHARI',
            '23 PKS PELAIHARI',
            '24 KUMAI',
            '25 PKS PAMUKAN',
            '26 PAMUKAN',
            '27 PRYBB',
            '28 RAREN BATUAH',
            '29 UNIT GRUP KALTIM',
            '30 TABARA',
            '31 TAJATI',
            '32 PANDAWA',
            '33 LONGKALI',
            '34 PKS SAMUNTAI',
            '35 PKS LONG PINANG',
            '36 KP JAKARTA',
            '37 KP BALIKPAPAN',
        ];
    }

    /**
     * Generate HTML select options for kebun
     */
    public static function generateSelectOptions($selectedValue = null, $includeEmpty = true): string
    {
        $options = '';
        
        if ($includeEmpty) {
            $options .= '<option value="">Pilih Kebun</option>';
        }
        
        foreach (self::getOptions() as $kebun) {
            $selected = ($selectedValue == $kebun) ? 'selected' : '';
            $options .= '<option value="' . htmlspecialchars($kebun) . '" ' . $selected . '>' . htmlspecialchars($kebun) . '</option>';
        }
        
        return $options;
    }
}




