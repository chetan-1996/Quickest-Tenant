<?php

namespace App\Exports;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\Exportable;

class CustomersExport implements FromCollection, WithHeadings, WithChunkReading
{
    use Exportable;
    /**
     * @return \Illuminate\Support\Collection
     */
    private $input;

    public function __construct($input = null)
    {
        $this->input = $input;
    }

    public function collection()
    {
        $this->logged_user = \Illuminate\Support\Facades\Auth::user();
        $this->company_id = ($this->logged_user->company_id) ? $this->logged_user->company_id : $this->logged_user->id;
        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->where(function ($query) {
                if ($this->input['fil_lead_stage_id']) {
                    $query->where('cv.lead_stage_id', $this->input['fil_lead_stage_id']);
                }
                if (array_key_exists('fil_status', $this->input)) {
                    $query->WhereIn('lg.id', $this->input['fil_status']);
                }
                if ($this->input['fil_customer_category_id'] != '') {
                    $query->where('cv.customer_category_id', '=', $this->input['fil_customer_category_id']);
                }
                if ($this->input['fil_customer_lead_id'] != '') {
                    $query->where('cv.customer_lead_id', '=', $this->input['fil_customer_lead_id']);
                }
                if ($this->input['fil_created_user_id'] != '') {
                    $query->where('cv.user_id', '=', $this->input['fil_created_user_id']);
                }
                if ($this->input['fil_estimate_status_id'] != '') {
                    $query->where('cv.estimate_status', '=', $this->input['fil_estimate_status_id']);
                }

                if ($this->input['fil_country_id']) {
                    $query->where('cv.country_id', $this->input['fil_country_id']);
                }
                if ($this->input['fil_state_id']) {
                    $query->where('cv.state_id', $this->input['fil_state_id']);
                }
                if ($this->input['fil_city_name']) {
                    $query->where('cv.city_name', $this->input['fil_city_name']);
                }
                if ($this->input['fil_team_member1'] > 0) {
                    $query->where('cv.assigned_to_user', '=', $this->input['fil_team_member1']);
                }
            })
            ->where(function ($query){
                $query->whereBetween(DB::raw("DATE_FORMAT(cv.created_at, '%Y-%m-%d')"), [$this->input['fil_lead_date_start'], $this->input['fil_lead_date_end']]);
            })
            ->select([
            'cv.id',
            DB::raw("DATE_FORMAT(cv.created_at, '%d-%m-%Y')"),
            'customer_type',
            DB::raw("COALESCE(cv.company_name, '')"),
            DB::raw("COALESCE(cv.name, '')"),
            DB::raw("COALESCE(cv.email, '')"),
            DB::raw("COALESCE(cv.country_code, '')"),
            DB::raw("COALESCE(cv.phone_no, '')"),
            DB::raw("COALESCE(cv.whatsapp_country_code, '')"),
            DB::raw("COALESCE(cv.whatsapp_no, '')"),
            DB::raw("COALESCE(cv.address, '')"),
            DB::raw("COALESCE(cv.pincode, '')"),
            DB::raw("COALESCE(cv.city_name, '')"),
            DB::raw("COALESCE(cv.state_name, '')"),
            DB::raw("COALESCE(cv.country_name, '')"),
            DB::raw("COALESCE(cv.user_name, '')"),
            DB::raw("COALESCE(cv.lead_stage_name, '')"),
            DB::raw("COALESCE(cv.lead_category, '')"),
            DB::raw("COALESCE(cv.lead_origin, '')"),
            DB::raw("COALESCE(cv.net_amount, '')"),
            DB::raw("COALESCE(cv.estimate_status, '')"),
        ])->where('cv.company_id',$this->company_id)
        ->groupBy('cv.id');
        if (array_key_exists('fil_status', $this->input)) {
            $records = $records->WhereIn("lg.id",$this->input['fil_status']);
        }
        return $records = $records->get();

    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function headings(): array
    {
        return ["ID","Date","Business Type","Company Name","Name","Email","Dial Code","Contact No","Whatsapp Dial Code","Whatsapp No",
            "Address","Pincode","City Name","State Name", "Country Name", "Assigned User", "Lead Stage","Lead Category","Lead Source","Amount","Estimate Status"];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
