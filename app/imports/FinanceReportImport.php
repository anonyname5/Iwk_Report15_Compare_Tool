<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class FinanceReportImport implements ToCollection, WithStartRow
{
    public function collection(Collection $rows)
    {
        return $rows;
    }

    public function startRow(): int
    {
        return 7; // Start from row 7
    }
}

