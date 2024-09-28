<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomerExport implements FromCollection,WithHeadings
{
	use Exportable;
	protected $failures_data;
    public function __construct($failures_data){
    	$this->failures_data = $failures_data;
    }

    public function collection(){
        return collect($this->failures_data);
    }

    public function headings(): array{
       return [
                'Customer Type',
                'Name',
                'Email',
                'Country Code',
                'Phone No',
                'Address',
                'Pincode',
                'Description',
                'Error'
            ];
    }
}
