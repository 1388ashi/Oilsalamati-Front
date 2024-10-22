<?php

namespace Modules\Order\Imports;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Modules\Order\Entities\ShippingExcel;

class ShippingExcelImport implements ToModel
{
    public function model(array $row)
    {
        $row[2] = str_replace(' ', '', $row[2]);
        if (!is_numeric($row[2]) || ($row[2] <= 0)) {
            return null;
        }
        if (ShippingExcel::where('barcode', $row[2])->exists()) {
            return null;
        }

        return new ShippingExcel([
            'title' => $row[1],
            'barcode' => $row[2],
            'repository' => $row[3],
            'register_date' => $row[5],
            'special_services' => $row[6],
            'destination' => str_replace('ي', 'ی', $row[7]),
            'reference_number' => $row[8],
            'receiver_name' => str_replace('ي', 'ی', $row[11]),
            'sender_name' => str_replace('ي', 'ی', $row[9]),
            'price' => $row[18]
        ]);
    }
}
