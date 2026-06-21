<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class QuickBroadcastImport implements ToArray
{
    /**
     * Parse spreadsheet to a raw array format.
     */
    public function array(array $array): array
    {
        return $array;
    }
}
