<?php

namespace App\Http\Controllers\API\V6;

use App\Http\Controllers\API\V6\BaseController as BaseController;
use App\Models\Country;
use App\Models\Customer;
use App\Models\CustomerCategory;
use App\Models\CustomerLead;
use App\Models\ViewCustomerData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use LogActivity;

class CustomerController extends BaseController
{
    protected $logged_user = null;
    protected $company_id = 0;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->logged_user = Auth::user();
            $this->company_id = ($this->logged_user->company_id) ? $this->logged_user->company_id : $this->logged_user->id;
            return $next($request);
        });
    }

    public function customerAutocomplete($search = null)
    {
        $customers = ViewCustomerData::select('id', 'name', 'phone_no', 'state_id', 'address', 'pincode', 'country_name', 'state_name', 'city_name','currency_name','currency_code', 'phone_no_country_id', 'whatsapp_no_country_id', 'currency_name_country_id')
            ->where([['company_id', '=', $this->company_id], ['status', '=', 0]])
            ->where(function ($query) {
                if ($this->logged_user->customer_show_flg==0 && $this->logged_user->company_id !='') {
                    $query->where('user_id', '=', $this->logged_user->id);
                }
            })
            ->where(function ($query) use ($search) {
                $query->orWhere(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                });
                $query->orWhere(function ($query) use ($search) {
                    $query->where('phone_no', 'like', $search . '%');
                });
            })
            ->get();
        $response = array();
        foreach ($customers as $customer) {
            $country_data = [];
            if($customer->currency_name_country_id)
                $country_data = Country::where("id", $customer->currency_name_country_id)->select('name','currency_name','currency_code','currency_symbol')->orderBy('id', 'DESC')->get()->first();
            $response[] = array("value" => $customer->id, "label" => $customer->name, "desc" => $customer->phone_no, "state_id" => $customer->state_id, "country_name" => $customer->country_name, "state_name" => $customer->state_name, "city_name" => $customer->city_name, "address" => $customer->address, "pincode" => $customer->pincode, "phone_no" => $customer->phone_no, "company_name" => $customer->company_name,"currency_name" => $customer->currency_name,"currency_code" => $customer->currency_code, "currency_symbol" => (isset($country_data->currency_symbol)) ? $country_data->currency_symbol:'');
        }

        return $this->sendResponse($response, 'Customer retrieved successfully');
    }

    public function customerStore(Request $request)
    {
        $input = $request->all();


        $validator = Validator::make($input, [
            'name' => 'required',
            'phone_no' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }

        $input['user_id'] = $this->logged_user->id;
        $input['company_id'] = $this->company_id;
//        $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
        $id = $input['id'];

        if (Customer::where('phone_no', '=', $input['phone_no'])
            ->where('company_id', $input['company_id'])
            ->where(function ($query) use ($id) {
                if ($id != 0) {
                    $query->Where(function ($query) use ($id) {
                        $query->where('id', '!=', $id);
                    });
                }
            })
            ->first()) {
            return $this->sendError('Customer phone no exists', ['error' => 'Customer phone no exists'], 409);
        }
//        DB::beginTransaction();
//        try {
        if ($id == 0) {
            $activityLogMsg = 'Customer created by ' . $this->logged_user->name;
            $customer = Customer::create($input);
        } else {

            $customer = Customer::find($id)->update($input);
            $activityLogMsg = 'Customer updated by ' . $this->logged_user->name;
        }

        // Add activity logs
//            $input['id'] = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
        LogActivity::addToLog($activityLogMsg, $input);
//            DB::commit();
        return $this->sendResponse([], 'Customer Saved');
        /* } catch (\Exception $exp) {
             DB::rollBack();
             return $this->sendError('Error in customer create', ['error' => $exp->getMessage()], 400);
         }*/
    }

    public function getSingleCustomer($id = 0)
    {
        $data['customers'] = Customer::where([["id", '=', $id], ['status', '=', 0]])->get(["*"]);
        return $this->sendResponse($data, 'Customer single retrieved successfully');
    }

    public function customerCategoriesAutocomplete($search=null)
    {
        $customerCategories = CustomerCategory::select('id', 'name', 'description')->where('company_id', $this->company_id)->where('name', 'LIKE', '%' . $search . '%')->where('status', 0)->get();
        return $this->sendResponse($customerCategories, 'Customer categories retrieved successfully');
    }

    public function customerLeadsAutocomplete($search=null)
    {
        $customerLeads = CustomerLead::select('id', 'name', 'description')
//            ->where('company_id',$this->company_id)
            ->where(function ($query) {
                $query->where('company_id', '=', $this->company_id);
                $query->orwhere('is_status', '=', 1);
            })
            ->where('name', 'LIKE', '%' . $search . '%')
            ->where('status', 0)
            ->get();
        return $this->sendResponse($customerLeads, 'Customer leads retrieved successfully');
    }
}
