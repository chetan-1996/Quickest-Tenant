<?php

namespace App\Http\Controllers\API\V6;

use App\Http\Controllers\API\V6\BaseController as BaseController;
use App\Models\Country;
use App\Models\Customer;
use App\Models\CustomerLabel;
use App\Models\CustomerLead;
use App\Models\Estimate;
use App\Models\EstimateTimeline;
use App\Models\LeadExclusive;
use App\Models\LeadStage;
use App\Models\LostReason;
use App\Models\SalesPersonPerformances;
use App\Models\User;
use App\Models\ViewCustomerData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use LogActivity;
use Illuminate\Support\Facades\Log;

class LeadController extends BaseController
{
    protected $logged_user = null;
    protected $company_id = 0;
    protected $user_param = 0;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->logged_user = \Illuminate\Support\Facades\Auth::user();
            $this->company_id = ($this->logged_user->company_id) ? $this->logged_user->company_id : $this->logged_user->id;
            $this->user_param = \App\Helpers\PermissionCheck::check_permission('role-list');
            return $next($request);
        });
    }
    public function index($start, $rowperpage, $orderBy, $fil_user_id, Request $request)
    {

        if ($orderBy == 0) {
            $order_by = 'id';
            $order_by_name = 'desc';
        }

        if ($orderBy == 1) {
            $order_by = 'last_activity_date';
            $order_by_name = 'desc';
        }

        if ($orderBy == 2) {
            $order_by = 'name';
            $order_by_name = 'asc';
        }

        // Define default order by values
        /* $defaultOrderBy = [
             0 => ['column' => 'id', 'direction' => 'desc'],
             1 => ['column' => 'last_activity_date', 'direction' => 'desc'],
             2 => ['column' => 'name', 'direction' => 'asc']
         ];

         // Set order by based on input or default
         $orderByColumn = $defaultOrderBy[$orderBy]['column'] ?? $defaultOrderBy[0]['column'];
         $orderByDirection = $defaultOrderBy[$orderBy]['direction'] ?? $defaultOrderBy[0]['direction'];*/


        $leadStageId = $request->input('lead_stage_id');
        $leadLabelId = $request->input('lead_label_id');
        $leadSourceId = $request->input('lead_source_id');
        $leadCategoryId = $request->input('lead_category_id');
        $estimateStatus = $request->input('estimate_status');
        $leadStageIdArr = ($leadStageId) ? explode(",", $leadStageId) : [];
        $leadLabelIdArr = ($leadLabelId) ? explode(",", $leadLabelId) : [];
        $leadSourceIdArr = ($leadSourceId) ? explode(",", $leadSourceId) : [];
        $leadCategoryIdArr = ($leadCategoryId) ? explode(",", $leadCategoryId) : [];
        $estimateStatusArr = ($estimateStatus) ? explode(",", $estimateStatus) : [];
        $cityName = $request->input('city_name');
        $stateId = $request->input('state_id');
        $countryId = $request->input('country_id');
        $leadStartDate = $request->input('lead_start_date');
        $leadEndDate = $request->input('lead_end_date');

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
//        DB::enableQueryLog();
        $records = ViewCustomerData::
        leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'customers_views.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->where(function ($query) use ($user_perm) {
//                if (!in_array('give-access-to-attend-unassigned-leads', $user_perm)) {
                $query->where('customers_views.company_id', $this->company_id);
//                }
            })
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('customers_views.user_id', '=', $this->logged_user->id); CMX
                }

                if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm)) {
                    if (in_array('give-access-to-attend-unassigned-leads', $user_perm)) {

                        $query->where('customers_views.company_id', $this->company_id);
                        $query->orwhere('customers_views.assigned_to_user', '=', 0);
                    } else {
                        $query->where('customers_views.assigned_to_user', '!=', 0);
                    }
                } else {
                    if (in_array('give-access-to-attend-unassigned-leads', $user_perm)) {
                        $query->orwhere('customers_views.assigned_to_user', '=', 0);
                    }
                }
            })
            ->where(function ($query) use ($fil_user_id, $leadStageIdArr, $leadLabelIdArr, $cityName,$stateId,$countryId,$leadSourceIdArr,$leadCategoryIdArr,$estimateStatusArr,$leadStartDate,$leadEndDate) {
                if (!empty($leadStageIdArr) && count($leadStageIdArr) > 0) {
                    $query->WhereIn('customers_views.lead_stage_id', $leadStageIdArr);
                }
                if (!empty($leadSourceIdArr) && count($leadSourceIdArr) > 0) {
                    $query->WhereIn('customers_views.customer_lead_id', $leadSourceIdArr);
                }
                if (!empty($leadCategoryIdArr) && count($leadCategoryIdArr) > 0) {
                    $query->WhereIn('customers_views.customer_category_id', $leadCategoryIdArr);
                }
                if (!empty($leadLabelIdArr) && count($leadLabelIdArr) > 0) {
                    $query->WhereIn('lg.id', $leadLabelIdArr);
                }
                if (!empty($cityName)) {
                    $query->Where('customers_views.city_name', $cityName);
                }
                if (!empty($stateId)) {
                    $query->Where('customers_views.state_id', $stateId);
                }
                if (!empty($countryId)) {
                    $query->Where('customers_views.country_id', $countryId);
                }
                if ($fil_user_id > 0) {
                    $query->where(function ($query) use ($fil_user_id) {
                        $query->where('customers_views.assigned_to_user', '=', $fil_user_id);
//                        $query->orwhere('customers_views.user_id', '=', $fil_user_id); CMX
                    });
                    //$query->where('customers_views.assigned_to_user', '=', $fil_user_id);
                }
                if (!empty($estimateStatusArr) && count($estimateStatusArr) > 0) {
                    $query->WhereIn('customers_views.estimate_status',$estimateStatusArr);
                }
                if($leadStartDate && $leadEndDate) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(customers_views.created_at, '%Y-%m-%d')"), [$leadStartDate, $leadEndDate]);
                }
            })
//        where('company_id', $this->company_id)
            /*->where(function ($query) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_param) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_param)) {
                    $query->where('assigned_to_user', '=', $this->logged_user->id);
                    $query->orwhere('user_id', '=', $this->logged_user->id);
                }
            })*/
            ->select('customers_views.*')
            ->orderBy($order_by, $order_by_name)
//            ->orderBy($orderByColumn, $orderByDirection)
            ->groupBy('customers_views.id')
            ->skip($start)
            ->take($rowperpage)
            ->get();
//        dd(DB::getQueryLog($records));
        $data = array();
        $i = 0;
        foreach ($records as $record) {
            //Log::channel('webhook')->info('Webhook test after', ['data' => $i."-".$record->id."-".$record->company_id]);
            $id = $record->id;
            $estimate_id = $record->estimate_id;
            $new_lead_flag = $record->new_lead_flag;
            $customer_type = $record->customer_type;
            $name = $record->name;
            $lead_origin = $record->lead_origin;
            $lead_category = $record->lead_category;
            $email = $record->email;
            $phone_no = $record->phone_no;
            $whatsapp_no = $record->whatsapp_no;
            $address = $record->address;
            $pincode = $record->pincode;
            $description = $record->description;
            $status = $record->status;
            $country_name = $record->country_name;
            $state_name = $record->state_name;
            $city_name = $record->city_name;
            $last_activity = $record->last_activity;
            $assign_user_name = $record->user_name;
            $net_amount = $record->net_amount;
            $estimate_status = $record->estimate_status;
            $last_activity_type = $record->last_activity_type;
            $last_activity_name = $record->last_activity_name;
            $last_internal_remarks = $record->last_internal_remarks;
            $some_day_flg = $record->some_day_flg;
            $date_added = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('d-m-Y h:i A');
            $last_activity_date = ($record->last_activity_date) ? $record->last_activity_date : null;
            $follow_up_datetime = ($record->last_follow_up_datetime) ? $record->last_follow_up_datetime : null;
            $i++;
            $country_data = [];
            /* if ($record->currency_name_country_id)
                 $country_data = Country::where("id", $record->currency_name_country_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->orderBy('id', 'DESC')->get()->first();*/

            if ($record->est_currency_id)
                $country_data = Country::where("id", $record->est_currency_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->first();
            $results = DB::table('customer_labels')
                ->select(DB::raw("group_concat(label_id separator ',') as labelId"))
                ->where('customer_id', '=', $record->id)
                ->groupBy('customer_id')
                ->get()->toArray();
            $label_color = [];
            $selectedLabelArr = [];
            if ($results) {
                $selectedLabelArr = explode(',', $results[0]->labelId);

                $label_color = DB::table('lead_groups')
                    ->select('name', 'color_code', 'id')
                    ->whereIn('id', $selectedLabelArr)
                    ->get()->toArray();
            }
            $data[] = array(
                "sr_no" => $i,
                "id" => $id,
                "estimate_id" => $estimate_id,
                "name" => $name,
                "company_name" => $record->company_name,
                "lead_origin" => $lead_origin,
                "lead_category" => $lead_category,
                "created_at" => $date_added,
                "customer_type" => $customer_type,
                "email" => $email,
                "phone_no" => $phone_no,
                "whatsapp_no" => $whatsapp_no,
                "address" => $address,
                "pincode" => $pincode,
                "country_name" => $country_name,
                "state_name" => $state_name,
                "city_name" => $city_name,
                "description" => $description,
                "last_activity" => $last_activity,
                "assign_user_name" => $assign_user_name,
                "net_amount" => $net_amount,
                "status" => $status,
                "last_activity_date" => $last_activity_date,
                "last_activity_type" => $last_activity_type,
                "last_activity_name" => $last_activity_name,
                "last_internal_remarks" => $last_internal_remarks,
                "label_color" => $label_color,
                "selectedLabelArr" => $selectedLabelArr,
                "estimate_status" => $estimate_status,
                "estimate_no" => $record->estimate_no,
                "follow_up_datetime" => $follow_up_datetime,
                "some_day_flg" => $some_day_flg,
                "country_code" => $record->country_code,
                "whatsapp_country_code" => $record->whatsapp_country_code,
                "new_lead_flag" => $record->new_lead_flag,
                "created_at" => ($record->created_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('Y-m-d H:i:s'):'',
                "updated_at" => ($record->updated_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->updated_at)
                ->format('Y-m-d H:i:s'):'',
                "last_is_modified" => $record->last_is_modified,
                "last_is_follow_up" => $record->last_is_follow_up,
                "currency_symbol" => (isset($country_data->currency_symbol)) ? $country_data->currency_symbol : '',
                "lead_stage_name" => $record->lead_stage_name,
                "lead_stage_color_code" => $record->lead_stage_color_code,
                "last_activity_id" => $record->last_activity_id,
                "user_id" => $record->user_id,
            );

        }
        if (is_null($data)) {
            return $this->sendError('Lead not found', ['Lead not found'], 422);
        }
        return $this->sendResponse($data, 'Lead retrieved successfully');
    }

    /*public function index($start, $rowperpage, $orderBy, $fil_user_id, Request $request)
    {
        // Define default order by options
        $defaultOrderBy = [
            0 => ['column' => 'id', 'direction' => 'desc'],
            1 => ['column' => 'last_activity_date', 'direction' => 'desc'],
            2 => ['column' => 'name', 'direction' => 'asc'],
        ];

        // Set order by based on input or default
        $orderByData = $defaultOrderBy[$orderBy] ?? $defaultOrderBy[0];
        $orderByColumn = $orderByData['column'];
        $orderByDirection = $orderByData['direction'];

        // Extract filter parameters from request
        $leadStageIdArr = $this->explodeIfNotEmpty($request->input('lead_stage_id'));
        $leadLabelIdArr = $this->explodeIfNotEmpty($request->input('lead_label_id'));
        $leadSourceIdArr = $this->explodeIfNotEmpty($request->input('lead_source_id'));
        $estimateStatusArr = $this->explodeIfNotEmpty($request->input('estimate_status'));
        $cityName = $request->input('city_name');
        $stateId = $request->input('state_id');
        $countryId = $request->input('country_id');
        $leadStartDate = $request->input('lead_start_date');
        $leadEndDate = $request->input('lead_end_date');

        // Check user permissions
        $userPerm = \App\Helpers\PermissionCheck::check_permission('role-list');

        DB::enableQueryLog();

        $query = ViewCustomerData::select('customers_views.*')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'customers_views.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id');

        // Apply user permission filters
        $this->applyUserPermissionFilters($query, $userPerm, $fil_user_id);

        // Apply filter conditions
        $this->applyFilterConditions($query, $leadStageIdArr, $leadLabelIdArr, $cityName, $stateId, $countryId, $leadSourceIdArr, $estimateStatusArr, $leadStartDate, $leadEndDate,$fil_user_id);

        $records = $query->orderBy($orderByColumn, $orderByDirection)
            ->groupBy('customers_views.id')
            ->skip($start)
            ->take($rowperpage)
            ->get();

        // Process and format retrieved data
        $data = $this->processData($records);

        // Handle error or success response
        if (is_null($data)) {
            return $this->sendError('Lead not found', ['Lead not found'], 422);
        }

        return $this->sendResponse($data, 'Lead retrieved successfully');
    }

    public function explodeIfNotEmpty($value)
    {
        return ($value) ? explode(",", $value) : [];
    }

    public function applyUserPermissionFilters($query, $userPerm, $fil_user_id)
    {
        $query->where(function ($subQuery) use ($userPerm) {
            $subQuery->where('customers_views.company_id', $this->company_id);
            // Add additional permission-based filters here if needed
        });

        if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $userPerm) ||
            in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $userPerm)) {
            $query->where(function ($subQuery) use ($fil_user_id) {
                $subQuery->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
                $subQuery->orWhere('customers_views.user_id', '=', $this->logged_user->id);
            });
        }

        // Add additional permission-based filters here if needed
    }

    public function applyFilterConditions($query, $leadStageIdArr, $leadLabelIdArr, $cityName, $stateId, $countryId, $leadSourceIdArr, $estimateStatusArr, $leadStartDate, $leadEndDate,$fil_user_id)
    {
        $query->where(function ($subQuery) use ($leadStageIdArr, $leadLabelIdArr, $cityName, $stateId, $countryId, $leadSourceIdArr, $estimateStatusArr, $leadStartDate, $leadEndDate,$fil_user_id) {
            if (!empty($leadStageIdArr) && count($leadStageIdArr) > 0) {
                $subQuery->WhereIn('customers_views.lead_stage_id', $leadStageIdArr);
            }
            if (!empty($leadSourceIdArr) && count($leadSourceIdArr) > 0) {
                $subQuery->WhereIn('customers_views.customer_lead_id', $leadSourceIdArr);
            }
            if (!empty($leadLabelIdArr) && count($leadLabelIdArr) > 0) {
                $subQuery->WhereIn('lg.id', $leadLabelIdArr);
            }
            if (!empty($cityName)) {
                $subQuery->Where('customers_views.city_name', $cityName);
            }
            if (!empty($stateId)) {
                $subQuery->Where('customers_views.state_id', $stateId);
            }
            if (!empty($countryId)) {
                $subQuery->Where('customers_views.country_id', $countryId);
            }
            if ($fil_user_id > 0) {
                $subQuery->where('customers_views.assigned_to_user', '=', $fil_user_id);
            }
            if (!empty($estimateStatusArr) && count($estimateStatusArr) > 0) {
                $subQuery->WhereIn('customers_views.estimate_status', $estimateStatusArr);
            }
            if ($leadStartDate && $leadEndDate) {
                $subQuery->whereBetween(DB::raw("DATE_FORMAT(customers_views.created_at, '%Y-%m-%d')"), [$leadStartDate, $leadEndDate]);
            }
        });
    }

    public function processData($records)
    {
        $data = [];
        $i = 0;
        foreach ($records as $record) {
            $i++;

            // Extract and format relevant data from the record
            $dataItem = [
                "sr_no" => $i,
                "id" => $record->id,
                "estimate_id" => $record->estimate_id,
                "name" => $record->name,
                "company_name" => $record->company_name,
                "lead_origin" => $record->lead_origin,
                "lead_category" => $record->lead_category,
                "created_at" => \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('d-m-Y h:i A'),
                "customer_type" => $record->customer_type,
                "email" => $record->email,
                "phone_no" => $record->phone_no,
                "whatsapp_no" => $record->whatsapp_no,
                "address" => $record->address,
                "pincode" => $record->pincode,
                "country_name" => $record->country_name,
                "state_name" => $record->state_name,
                "city_name" => $record->city_name,
                "description" => $record->description,
                "last_activity" => $record->last_activity,
                "assign_user_name" => $record->user_name,
                "net_amount" => $record->net_amount,
                "status" => $record->status,
                "last_activity_date" => ($record->last_activity_date) ? $record->last_activity_date : null,
                "last_activity_type" => $record->last_activity_type,
                "last_activity_name" => $record->last_activity_name,
                "last_internal_remarks" => $record->last_internal_remarks,
                "some_day_flg" => $record->some_day_flg,
                "date_added" => \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('Y-m-d H:i:s'),
                "updated_at" => \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->updated_at)->format('Y-m-d H:i:s'),
                "last_is_modified" => $record->last_is_modified,
                "last_is_follow_up" => $record->last_is_follow_up,
                "follow_up_datetime" => ($record->last_follow_up_datetime) ? $record->last_follow_up_datetime : null,
                "country_code" => $record->country_code,
                "whatsapp_country_code" => $record->whatsapp_country_code,
                "new_lead_flag" => $record->new_lead_flag,
                "lead_stage_name" => $record->lead_stage_name,
                "lead_stage_color_code" => $record->lead_stage_color_code,
            ];

            // Get country details (if applicable)
            if ($record->est_currency_id) {
                $countryData = Country::where("id", $record->est_currency_id)->first();
                if ($countryData) {
                    $dataItem['currency_symbol'] = $countryData->currency_symbol;
                }
            }

            // Get lead labels (if any)
            $labelResults = DB::table('customer_labels')
                ->select(DB::raw("group_concat(label_id separator ',') as labelId"))
                ->where('customer_id', '=', $record->id)
                ->groupBy('customer_id')
                ->get()->toArray();

            if ($labelResults) {
                $selectedLabelArr = explode(',', $labelResults[0]->labelId);
                $labelColor = DB::table('lead_groups')
                    ->select('name', 'color_code', 'id')
                    ->whereIn('id', $selectedLabelArr)
                    ->get()->toArray();
                $dataItem['label_color'] = $labelColor;
                $dataItem['selectedLabelArr'] = $selectedLabelArr;
            }

            $data[] = $dataItem;
        }

        return (is_null($data)) ? null : $data;
    }*/

    public function getLead($id)
    {

        /* $input = $request->all();

         $id = $input['id'];*/
//        $validator = Validator::make($id, [
//            'id' => 'required'
//        ]);
//        if ($validator->fails()) {
//            return response()->json(['errors' => $validator->errors()->all()], 400);
//        }

        $customers = ViewCustomerData::select("*")->where('id', '=', $id)->first();

        $country_data = [];
        /*if ($customers->currency_name_country_id)
            $country_data = Country::where("id", $customers->currency_name_country_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->orderBy('id', 'DESC')->get()->first();*/
        if ($customers->est_currency_id)
            $country_data = Country::where("id", $customers->est_currency_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->first();
        $customers["currency_symbol"] = (isset($country_data->currency_symbol)) ? $country_data->currency_symbol : '';
        $results = DB::table('customer_labels')
            ->select(DB::raw("group_concat(label_id separator ',') as labelId"))
            ->where('customer_id', '=', $id)
            ->groupBy('customer_id')
            ->get()->toArray();
        $customers['label_color'] = [];
        $customers['selectedLabelArr'] = [];
        if ($results) {
            $customers['selectedLabelArr'] = explode(',', $results[0]->labelId);

            $customers['label_color'] = DB::table('lead_groups')
                ->select('name', 'color_code', 'id')
                ->whereIn('id', $customers['selectedLabelArr'])
                ->get()->toArray();
        }
        if (is_null($customers)) {
            return $this->sendError('Lead not found', ['Lead not found'], 422);
        }
        if ($this->logged_user->id == $customers->assigned_to_user)
            Customer::find($id)->update(["new_lead_flag" => 0]);
        return $this->sendResponse($customers, 'Lead retrieved successfully');
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
            'phone_no' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }

        $input['name'] = ucwords($input['name']);
        $input['user_id'] = $this->logged_user->id;
        $input['company_id'] = ($this->company_id);
        $id = $input['id'];
        $leadSource = 'Lead added from manually';
        if ($input['customer_lead_id'] > 0) {
            $customerLeads = CustomerLead::select(["name", "id"])->where('status', '=', 0)->where('id', $input['customer_lead_id'])->first();

            $leadSource = 'Lead added from ' . $customerLeads->name;
        }
        /*$input['lead_stage_id'] = 0;
        $lead_stage_data = LeadStage::where('company_id',$this->company_id)->where('is_default',1)->select('name','id')->first();
        if($lead_stage_data){
            $input['lead_stage_id'] =$lead_stage_data->id;
        }*/
        if ($id == 0) {
            $activityLogMsg = 'Lead created by ' . $this->logged_user->name;
            $logInput['internal_remarks'] = $leadSource;
            $input['assigned_to_user'] = $this->logged_user->id;
            $input['new_lead_flag'] = 1;
            $customer = Customer::create($input);
            $ids = $customer->id;

            $customers = ViewCustomerData::select('id', 'name', 'phone_no', 'state_id', 'address', 'pincode', 'country_name', 'state_name', 'city_name', 'company_name', 'whatsapp_no', 'whatsapp_country_code')
                ->where('id', $ids)
                ->where('status', 0)
                ->first();
            $logInput['activity_type'] = 6;
        } else {
            $customer = Customer::find($id)->update($input);
            $activityLogMsg = 'Lead updated by ' . $this->logged_user->name;
            $logInput['internal_remarks'] = "Lead updated";
            $ids = $id;

            $customers = ViewCustomerData::select('id', 'name', 'phone_no', 'state_id', 'address', 'pincode', 'country_name', 'state_name', 'city_name', 'company_name', 'country_code', 'last_follow_up_datetime', 'whatsapp_no', 'whatsapp_country_code')
                ->where('id', $id)
                ->where('status', 0)
                ->first();
            $logInput['activity_type'] = 7;
            $logInput['follow_up_datetime'] = $customers->last_follow_up_datetime;
        }

        // Add activity logs
        LogActivity::addToLog($activityLogMsg, $input);


        $logInput['assigned_to'] = $this->logged_user->id;
        $logInput['customer_id'] = $ids;
        $logInput['entry_type'] = "leads";


        $logInput['user_id'] = $this->logged_user->id;
        $logInput['company_id'] = $this->company_id;
        $logInput['created_by'] = $this->logged_user->id;
        $logInput['updated_by'] = $this->logged_user->id;
        LogActivity::addToActivityLog($logInput);


        if ($id == 0) {
            $logInput['activity_type'] = 8;
            $logInput['entry_type'] = "assigned";
            $logInput['follow_up_datetime'] = '0000-00-00 00:00:00';
            $logInput['internal_remarks'] = "Assigned to " . $this->logged_user->name;
            LogActivity::addToActivityLog($logInput);
        }
        return $this->sendResponse(["customer_id" => $ids, "customer_name" => $input['name'], "state_id" => $input["state_id"], "desc" => $customers->phone_no, "country_name" => $customers->country_name, "state_name" => $customers->state_name, "city_name" => $customers->city_name, "address" => $customers->address, "pincode" => $customers->pincode, "phone_no" => $customers->phone_no, "whatsapp_no" => $customers->whatsapp_no, "country_code" => $customers->country_code, "whatsapp_country_code" => $customers->whatsapp_country_code], 'Customer label updated!');
        /*return response()->json(['success' => 'Customer Saved!', "customer_id" => $ids, "customer_name" => $input['name'], "state_id" => $input["state_id"], "desc" => $customers->phone_no, "country_name" => $customers->country_name, "state_name" => $customers->state_name, "city_name" => $customers->city_name, "address" => $customers->address, "pincode" => $customers->pincode, "phone_no" => $customers->phone_no], 201);*/
    }

    public function destroy(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }
        $id = [];
        foreach (explode(",", $request->id) as $value) {
            $id[] = $value;
        }

        $estimates = DB::table('estimates')->select('id')
            ->whereIn('customer_id', $id)
            ->get();
        $estId = [];
        foreach ($estimates as $estimate) {
            $estId[] = $estimate->id;
        }

        $estimateItems = DB::table('estimate_items')
            ->whereIn('estimate_id', $estId)
            ->delete();

        $estimate = DB::table('estimates')
            ->whereIn('customer_id', $id)
            ->delete();

        /* $estimates = EstimateItem::join('estimates', function($q) use ($id)
         {
             $q->on('estimates.id', '=', 'estimate_items.estimate_id')
                 ->whereIn('estimates.customer_id', $id);
         })->delete();*/
        /* $estimates =  DB::table('estimates')
             ->join('estimate_items', 'estimate_items.estimate_id', '=', 'estimates.id')
             ->whereIn('estimates.customer_id', $id)
             ->delete();*/
//            dd(DB::getQueryLog($estimates));
        $customer_labels = DB::table('customer_labels')
            ->whereIn('customer_id', $id)
            ->delete();


        $customer_timelines = DB::table('customer_timelines')->select('id', 'estimate_version_no', 'company_id')
            ->whereIn('customer_id', $id)
            ->where('estimate_version_no', '!=', '')
            ->get();

        foreach ($customer_timelines as $customer_timeline) {
            $path = 'public/document/' . $customer_timeline->company_id . '/' . $customer_timeline->estimate_version_no . '.pdf';
            if (Storage::exists($path)) {
                Storage::delete($path);
            }
        }
        $customer_labels = DB::table('customer_timelines')
            ->whereIn('customer_id', $id)
            ->delete();

        $sales_person_performances = DB::table('sales_person_performances')
            ->whereIn('customer_id', $id)
            ->delete();

        $customer = Customer::whereIn('id', $id)->delete();

        LogActivity::addToLog('Lead deleted by ' . $this->logged_user->name, $id);
        return $this->sendResponse([], 'Lead Deleted!');
    }

    public function duplicateLead($id, $phone_no)
    {
//        $validator = Validator::make($id, [
//            'id' => 'required'
//        ]);
//        if ($validator->fails()) {
//            return response()->json(['errors' => $validator->errors()->all()], 400);
//        }

        $records = ViewCustomerData::
        leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'customers_views.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->where('customers_views.company_id', $this->company_id)
            ->when($id > 0, function ($query) use ($id) {
                return $query->where('customers_views.id', '!=', $id);
            })
//            ->where('customers_views.id', "!=", $id)
            ->where('customers_views.phone_no', "=", $phone_no)
            ->select('customers_views.*')
            ->groupBy('customers_views.id')
            ->orderBy('customers_views.id', 'desc')
            ->get();

        $data = array();
        $i = 0;
        foreach ($records as $record) {
            $country_data = [];
            /*if ($record->currency_name_country_id)
                $country_data = Country::where("id", $record->currency_name_country_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->orderBy('id', 'DESC')->get()->first();*/
            if ($record->est_currency_id)
                $country_data = Country::where("id", $record->est_currency_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->first();
            $id = $record->id;
            $customer_type = $record->customer_type;
            $name = $record->name;
            $lead_origin = $record->lead_origin;
            $lead_category = $record->lead_category;
            $email = $record->email;
            $phone_no = $record->phone_no;
            $whatsapp_no = $record->whatsapp_no;
            $address = $record->address;
            $pincode = $record->pincode;
            $description = $record->description;
            $status = $record->status;
            $country_name = $record->country_name;
            $state_name = $record->state_name;
            $city_name = $record->city_name;
            $last_activity = $record->last_activity;
            $assign_user_name = $record->user_name;
            $net_amount = $record->net_amount;
            $estimate_status = $record->estimate_status;
            $last_activity_type = $record->last_activity_type;
            $last_activity_name = $record->last_activity_name;
            $last_internal_remarks = $record->last_internal_remarks;
            $some_day_flg = $record->some_day_flg;
            $date_added = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('d-m-Y h:i A');
            $last_activity_date = ($record->last_activity_date) ? $record->last_activity_date : null;
            $follow_up_datetime = ($record->last_follow_up_datetime) ? $record->last_follow_up_datetime : null;
            $i++;

            $results = DB::table('customer_labels')
                ->select(DB::raw("group_concat(label_id separator ',') as labelId"))
                ->where('customer_id', '=', $record->id)
                ->groupBy('customer_id')
                ->get()->toArray();
            $label_color = [];
            $selectedLabelArr = [];
            if ($results) {
                $selectedLabelArr = explode(',', $results[0]->labelId);

                $label_color = DB::table('lead_groups')
                    ->select('name', 'color_code', 'id')
                    ->whereIn('id', $selectedLabelArr)
                    ->get()->toArray();
            }
            $data[] = array(
                "sr_no" => $i,
                "id" => $id,
                "name" => $name,
                "company_name" => $record->company_name,
                "lead_origin" => $lead_origin,
                "lead_category" => $lead_category,
                "created_at" => $date_added,
                "customer_type" => $customer_type,
                "email" => $email,
                "phone_no" => $phone_no,
                "whatsapp_no" => $whatsapp_no,
                "address" => $address,
                "pincode" => $pincode,
                "country_name" => $country_name,
                "state_name" => $state_name,
                "city_name" => $city_name,
                "description" => $description,
                "last_activity" => $last_activity,
                "assign_user_name" => $assign_user_name,
//                "net_amount" => $net_amount,
                "net_amount" => $net_amount,

                "status" => $status,
                "last_activity_date" => $last_activity_date,
                "last_activity_type" => $last_activity_type,
                "last_activity_name" => $last_activity_name,
                "last_internal_remarks" => $last_internal_remarks,
                "label_color" => $label_color,
                "selectedLabelArr" => $selectedLabelArr,
                "estimate_status" => $estimate_status,
                "estimate_no" => $record->estimate_no,
                "follow_up_datetime" => $follow_up_datetime,
                "some_day_flg" => $some_day_flg,
                "country_code" => $record->country_code,
                "whatsapp_country_code" => $record->whatsapp_country_code,
                 "created_at" => ($record->created_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('Y-m-d H:i:s'):'',
                "updated_at" => ($record->updated_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->updated_at)
                ->format('Y-m-d H:i:s'):'',
                "last_is_modified" => $record->last_is_modified,
                "last_is_follow_up" => $record->last_is_follow_up,
                "currency_symbol" => (isset($country_data->currency_symbol)) ? $country_data->currency_symbol : '',
                "lead_stage_name" => $record->lead_stage_name,
                "lead_stage_color_code" => $record->lead_stage_color_code,
                "last_activity_id" => $record->last_activity_id,
                "user_id" => $record->user_id,
            );

        }
        if (is_null($data)) {
            return $this->sendError('Lead not found', ['Lead not found'], 400);
        }

        return $this->sendResponse($data, 'Lead retrieved successfully');



       /* $duplicateLeads = DB::select("SELECT a.id, a.name, a.phone_no, a.created_at, a.new_lead_flag,a.assigned_to_user,a.user_id as create_lead_user_id, u.name as assigned_user_name
                                FROM customers a left join users u on a.assigned_to_user=u.id WHERE a.id !=$id AND a.phone_no = '$phone_no' and a.company_id=$this->company_id");*/
        /*if (is_null($duplicateLeads)) {
            return $this->sendError('User not found', ['Duplicate leads not found'], 400);
        }
        return $this->sendResponse($duplicateLeads, 'Duplicate leads retrieved successfully');*/
    }

    public function assignUserList()
    {
        /*$leads = User::select(["name", "id", "email", "mobile_no"])
            ->where('status', 'Approved')
            ->where('company_id', $this->company_id)
            ->orWhere('id', $this->logged_user->id)
            ->orWhere('id', $this->company_id)
            ->get();*/

        $leads = DB::table('users')
            ->leftJoin('customers', 'users.id', '=', 'customers.assigned_to_user')
            ->select("users.name", "users.id", "users.email", "users.mobile_no", DB::raw('count(customers.id) as lead_count'), "users.company_id", "users.mobile_device_key as mobile_device_key", "users.device_key as web_device_key", "users.profile_icon")
//            ->where('users.status', 'Approved')
            ->where('users.invite_status', 1)
//            ->where('users.company_id', $this->company_id)
            ->where(function ($query) {
                $query->orwhere('users.company_id', $this->company_id);
                $query->orwhere('users.id', $this->company_id);
            })
            ->groupBy('users.id')
            ->get();
        foreach ($leads as $lead) {
//            $lead->profile_icon = ($lead->profile_icon) ? Storage::url($lead->profile_icon) : null;
            $lead->profile_icon = ($lead->profile_icon) ? Storage::disk('s3')->temporaryUrl($lead->profile_icon, Carbon::now()->addMinutes(20)) : null;
        }
        if (is_null($leads)) {
            return $this->sendError('User not found', ['Lead not found'], 422);
        }
        return $this->sendResponse($leads, 'User retrieved successfully');
    }

    public function LeadAssignedToUser(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required',
            'assigned_to_user' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }

        $id = $input['id'];
        $user = User::where('id', '=', $input['assigned_to_user'])->select(["name"])->first();
        Customer::find($id)->update(["assigned_to_user" => $input['assigned_to_user'], "new_lead_flag" => 1]);

        $logInput['follow_up_datetime'] = (!empty($input['follow_up_date_assign_user']) && $input['follow_up_date_assign_user'] != '0000-00-00 00:00:00') ? Carbon::createFromFormat('Y-m-d H:i:s', $input['follow_up_date_assign_user'])->format('Y-m-d H:i:s') : '0000-00-00 00:00:00';
        $logInput['assigned_to'] = $input['assigned_to_user'];
        $logInput['customer_id'] = $id;
        $logInput['entry_type'] = "assigned";
        $logInput['activity_type'] = 8;
        $logInput['internal_remarks'] = "Assigned to " . $user->name;
        $logInput['user_id'] = $this->logged_user->id;
        $logInput['company_id'] = $this->company_id;
        $logInput['created_by'] = $this->logged_user->id;
        $logInput['updated_by'] = $this->logged_user->id;
        LogActivity::addToActivityLog($logInput);

        SalesPersonPerformances::where([['customer_id', "=", $id], ['completed_task', '=', 0]])->update(array('user_id' => $input['assigned_to_user'], 'created_at' => date('Y-m-d H:i:s')));
        return $this->sendResponse([], 'Successfully Updated!');
    }

    public function leadTimelineActivity($id)
    {

//        $validator = Validator::make($id, [
//            'id' => 'required'
//        ]);
//        if ($validator->fails()) {
//            return response()->json(['errors' => $validator->errors()->all()], 400);
//        }

        $timelineAcitvityies = EstimateTimeline::select('customer_timelines.*', 'users.name as created_by_name', DB::raw("DATE_FORMAT(customer_timelines.created_at, '%d %b, %Y %h:%i %p') as display_created_at"), 'customers_views.est_currency_id')
            ->leftJoin('users', 'customer_timelines.created_by', '=', 'users.id')
            ->leftJoin('customers_views', 'customer_timelines.customer_id', '=', 'customers_views.id')
            ->where('customer_timelines.customer_id', '=', $id)
            ->where('customer_timelines.company_id', $this->company_id)
            ->orderBy('customer_timelines.id', 'desc')
//            ->where('status', 0)
            ->get();
        foreach ($timelineAcitvityies as $key => $val) {

            $country_data = Country::
            join('estimates', 'estimates.est_currency_id', '=', 'countries.id')
                ->where("estimates.id", $timelineAcitvityies[$key]->estimate_id)
                ->select('name', 'currency_name', 'currency_code', 'currency_symbol')
//                ->orderBy('id', 'DESC')
                ->first();
//            $country_data = [];
            /*if ($timelineAcitvityies[$key]->currency_name_country_id)
                $country_data = Country::where("id", $timelineAcitvityies[$key]->currency_name_country_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->orderBy('id', 'DESC')->get()->first();*/

            /*if ($timelineAcitvityies[$key]->est_currency_id)
                $country_data = Country::where("id", $timelineAcitvityies[$key]->est_currency_id)->select('name', 'currency_name', 'currency_code','currency_symbol')->first();*/

            $timelineAcitvityies[$key]->currency_symbol = (isset($country_data->currency_symbol)) ? $country_data->currency_symbol : '';
        }
        /*foreach ($timelineAcitvityies as $key => $val) {
            $timelineAcitvityies[$key]->estimate_id = Crypt::encrypt($timelineAcitvityies[$key]->estimate_id);
        }*/

        if (is_null($timelineAcitvityies)) {
            return $this->sendError('User not found', ['Timeline not found'], 422);
        }
        return $this->sendResponse($timelineAcitvityies, 'Timeline retrieved successfully');
    }

    public function activityShow($id)
    {
        /*        $input = $request->all();
        //            $id = Crypt::decrypt($input['id']);
                $id = $input['id'];*/
        $validator = Validator::make(['id' => $id], [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Timeline not found', ['Timeline not found'], 422);
        }

        $activities = EstimateTimeline::find($id)->toArray();

        if (is_null($activities)) {
            return $this->sendError('Activity timeline not found', ['Activity timeline not found!'], 422);
        }
        $activities['id'] = $activities['id'];
        if (isset($activities['follow_up_datetime']) && $activities['follow_up_datetime'] != "0000-00-00 00:00:00") {
            $activities['follow_up_datetime'] = Carbon::createFromFormat('Y-m-d H:i:s', $activities['follow_up_datetime'])
                ->format('d-m-Y H:i A');
        }
        return $this->sendResponse($activities, 'Activity timeline retrieved successfully.');
    }

    public function activityDestroy(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Timeline not found', ['Timeline not found'], 422);
        }
        //$id = Crypt::decrypt($input['id']);
        $id = $input['id'];
        EstimateTimeline::where('id', $id)->delete();
        return $this->sendResponse([], 'Activity Deleted!');
    }

    public function activityStore(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'activity_type' => 'required',
            'activity_name' => 'required',
//            'follow_up_datetime' => 'required',
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }

        $input['user_id'] = $this->logged_user->id;
        $input['company_id'] = $this->company_id;
        $id = $input['id'];
        $customer_id = $input['customer_id'];

        $customerData = Customer::select("assigned_to_user")->where('id', '=', $customer_id)->first();

        $paramArr['assigned_to'] = $customerData->assigned_to_user;
        $paramArr['customer_id'] = $customer_id;
        $paramArr['activity_type'] = $input['activity_type'];
        $paramArr['activity_name'] = $input['activity_name'];
        $paramArr['activity_notes'] = $input['activity_notes'];
        $paramArr['visit_latitude'] = $input['visit_latitude'];
        $paramArr['visit_longitude'] = $input['visit_longitude'];
        $paramArr['visit_address'] = $input['visit_address'];
//        $paramArr['follow_up_datetime'] = Carbon::createFromFormat('d-m-Y H:i A', $input['follow_up_datetime'])->format('Y-m-d H:i:s');
        $paramArr['follow_up_datetime'] = (!empty($input['follow_up_datetime']) && $input['follow_up_datetime'] != '0000-00-00 00:00:00') ? Carbon::createFromFormat('d-m-Y H:i A', $input['follow_up_datetime'])->format('Y-m-d H:i:s') : '';
        $paramArr['entry_type'] = 'followup';
        $paramArr['is_modified'] = 1;
        $paramArr['user_id'] = $this->logged_user->id;
        $paramArr['company_id'] = $this->company_id;
        $paramArr['created_by'] = $this->logged_user->id;
        $paramArr['updated_by'] = $this->logged_user->id;

        if ($id == 0) {
            if ($paramArr['follow_up_datetime'] && !(Carbon::parse($paramArr['follow_up_datetime'])->eq(Carbon::parse($input['old_follow_up_date_at'])))) {
                $customer_data = EstimateTimeline::select("id")
                    ->where('customer_id', '=', $customer_id)
                    ->wherein('activity_type', [9])    //[1, 2, 3, 9]
                    ->orderBy('id', 'DESC')
                    ->take(1)
                    ->get()
                    ->toArray();

                if ($customer_data && $customer_data[0]['id']) {
                    $a = SalesPersonPerformances::where('timeline_id', $customer_data[0]['id'])->update(array('completed_task' => 1));
                }
            }

            $paramArr['internal_remarks'] = "Activity added";
            $paramArr['internal_remarks'] = $input['activity_notes'];
            if (isset($input['follow_up_datetime']) && $input['old_follow_up_date_at'] != $paramArr['follow_up_datetime']) {
                $activity_notes = $paramArr['activity_notes'];
                $paramArr['internal_remarks'] = " <b>New follow up date</b> : " . $input['follow_up_datetime'] . "</br>";
                $paramArr['internal_remarks'] .= " <b>Notes</b> : " . $input['activity_notes'];
            } //activity merge
            $customer = EstimateTimeline::create($paramArr);
            if (isset($input['follow_up_datetime']) && $input['old_follow_up_date_at'] != $paramArr['follow_up_datetime']) {
                $paramArr['is_modified'] = 0;
                $paramArr['is_follow_up'] = 0;
                $paramArr['internal_remarks'] = "";
                $paramArr['activity_type'] = 9;
                $paramArr['activity_name'] = 'Follow Up';

                $paramArr['internal_remarks'] .= " <b>New follow up date</b> : " . $input['follow_up_datetime'] . "</br>";
                if ($input['activity_notes'])
                    $paramArr['internal_remarks'] .= " <b>Notes</b> : " . $input['activity_notes'];
//                $paramArr['internal_remarks'] .= " Date : " . Carbon::createFromFormat('Y-m-d H:i:s', $input['old_follow_up_date_at'])->format('d-m-Y H:i:s') . " to " . $input['follow_up_datetime'];

                EstimateTimeline::create($paramArr);
                Customer::where('id', $customer_id)->update(array('some_day_flg' => 0));
            }
            if ($paramArr['follow_up_datetime'] && !(Carbon::parse($paramArr['follow_up_datetime'])->eq(Carbon::parse($input['old_follow_up_date_at'])))) {
                if ($input['activity_type'] == 1 || $input['activity_type'] == 2 || $input['activity_type'] == 3 || $input['activity_type'] == 9) {
                    $customer_data = EstimateTimeline::select("id")
                        ->where('customer_id', '=', $customer_id)
                        ->wherein('activity_type', [9])  //[1, 2, 3, 9]
                        ->orderBy('id', 'DESC')
                        ->get()
                        ->toArray();


                    $insArr['timeline_id'] = $customer_data[0]['id'];
                    $insArr['customer_id'] = $customer_id;
                    $insArr['user_id'] = $this->logged_user->id;
                    $insArr['company_id'] = $this->company_id;
                    $insArr['performance_date'] = (!empty($input['follow_up_datetime']) && $input['follow_up_datetime'] != '0000-00-00 00:00:00') ? Carbon::createFromFormat('d-m-Y H:i A', $input['follow_up_datetime'])->format('Y-m-d') : date('Y-m-d');
                    $insArr['total_task'] = 1;
                    SalesPersonPerformances::create($insArr);
                }
            }
        } else {
            $paramArr['internal_remarks'] = "Activity updated";
            //echo "dsd"; //activity merge
            if (isset($input['follow_up_datetime']) && $input['old_follow_up_date_at'] != $paramArr['follow_up_datetime']) {
                /*  echo "ok";
                  echo "ok";*/
                $activity_notes = $paramArr['activity_notes'];
                $paramArr['internal_remarks'] = " <b>New follow up date</b> : " . $input['follow_up_datetime'] . "</br>";
                $paramArr['internal_remarks'] .= " <b>Notes</b> : " . $input['activity_notes'];
            }
            $customer = EstimateTimeline::find($id)->update($paramArr);
        }
        return $this->sendResponse([], 'Successfully Saved!');
    }

    public function updateLabel(Request $request)
    {
        $input = $request->all();
//        $input['lead_id'] = $input['lead_id'];
        $validator = Validator::make($input, [
            'lead_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }
        $selected_lead_id = explode(',', $input['selected_lead_id']);
        $customer_id = $input['lead_id'];


        CustomerLabel::where('customer_id', $input['lead_id'])->delete();
        if ($input['selected_lead_id'] && count($selected_lead_id) > 0) {
            $data = [];
            $i = 0;
            foreach ($selected_lead_id as $value) {
                $data[$i]['customer_id'] = $customer_id;
                $data[$i]['label_id'] = $value;
                $data[$i]['user_id'] = $this->logged_user->id;
                $data[$i]['company_id'] = $this->company_id;
                $i++;
            }
//            Customer::find($customer_id)->update(["new_lead_flag" => 0]);
            CustomerLabel::insert($data);
        }
        return $this->sendResponse([], 'Customer label updated!');

    }

    public function updateLeadDescription(Request $request)
    {
        $input = $request->all();

        $input['id'] = $input['lead_id'];
        $validator = Validator::make($input, [
            'lead_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }

        $customers = Customer::where('id', $input['id'])->update(["description" => $input['lead_description']]);
        return $this->sendResponse(['success' => 'Customer description updated!', 'lead_description' => nl2br($input['lead_description'])], 'Customer label updated!');

    }

    public function userWithCategory()
    {
        $users = User::with('customers')->get();

        $usersArray = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'subcategories' => $user->customers->map(function ($subcategory) {
                    return [
                        'subcategory_name' => $subcategory->name,
                    ];
                })->toArray(),
            ];
        })->toArray();

        print_r($usersArray);
    }

    public function getFollowup($start, $rowperpage)
    {
        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.*', DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code'))
            ->where('cv.company_id', $this->company_id)
            /* ->whereNotNull('cv.last_follow_up_datetime')
             ->where('cv.last_follow_up_datetime','!=','0000-00-00 00:00:00')*/
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
                    $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                }
            })
            ->groupBy('cv.id')
            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        /* $records = ViewCustomerData::where('company_id', $this->company_id)
             ->where(function ($query) {
                 if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_param) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_param)) {
                     $query->where('assigned_to_user', '=', $this->logged_user->id);
                     $query->orwhere('user_id', '=', $this->logged_user->id);
                 }
             })
             ->select('*')
             ->orderBy('id', 'desc')
             ->get();*/
        $data = array();
        $i = 0;
        $data['overdue'] = array();
        $data['duetoday'] = array();
        $data['upcoming'] = array();
        $data['nofollowup'] = array();
        $data['someday'] = array();

        foreach ($records as $record) {
            $id = $record->id;
            $customer_type = $record->customer_type;
            $name = $record->name;
            $lead_origin = $record->lead_origin;
            $lead_category = $record->lead_category;
            $email = $record->email;
            $phone_no = $record->phone_no;
            $whatsapp_no = $record->whatsapp_no;
            $address = $record->address;
            $pincode = $record->pincode;
            $description = $record->description;
            $status = $record->status;
            $country_name = $record->country_name;
            $state_name = $record->state_name;
            $city_name = $record->city_name;
            $last_activity = $record->last_activity;
            $assign_user_name = $record->user_name;
            $net_amount = $record->net_amount;
            $estimate_status = $record->estimate_status;
            $last_activity_type = $record->last_activity_type;
            $last_activity_name = $record->last_activity_name;
            $last_internal_remarks = $record->last_internal_remarks;
            $date_added = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('d-m-Y h:i A');
//            $follow_up_datetime = $record->last_follow_up_datetime;
            $follow_up_datetime = ($record->last_follow_up_datetime && $record->last_follow_up_datetime != '0000-00-00 00:00:00') ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->last_follow_up_datetime)->format('Y-m-d H:i:s') : '';
            $last_activity_date = ($record->last_activity_date) ? $record->last_activity_date : null;

            $i++;

            $results = DB::table('customer_labels')
                ->select(DB::raw("group_concat(label_id separator ',') as labelId"))
                ->where('customer_id', '=', $record->id)
                ->groupBy('customer_id')
                ->get()->toArray();
            $label_color = [];
            $selectedLabelArr = [];
            if ($results) {
                $selectedLabelArr = explode(',', $results[0]->labelId);

                $label_color = DB::table('lead_groups')
                    ->select('name', 'color_code', 'id')
                    ->whereIn('id', $selectedLabelArr)
                    ->get()->toArray();
            }
            $temp = 'overdue';
            if (strtotime(date('d-m-Y', strtotime($follow_up_datetime))) > strtotime(date('d-m-Y')) && $record->some_day_flg == 0) {
                $temp = 'upcoming';
            }

            if (strtotime(date('d-m-Y', strtotime($follow_up_datetime))) == strtotime(date('d-m-Y')) && $record->some_day_flg == 0) {
                $temp = 'duetoday';
                $follow_up_datetime = $record->last_follow_up_datetime;
            }

            if ($follow_up_datetime == '' && $record->some_day_flg == 0) {
                $temp = 'nofollowup';
                $follow_up_datetime = '';
            }

            if ($record->some_day_flg == 1) {
                $temp = 'someday';
                $follow_up_datetime = '';
            }
            $data[$temp][] = array(
                "sr_no" => $i,
                "id" => $id,
                "name" => $name,
                "company_name" => $record->company_name,
                "lead_origin" => $lead_origin,
                "lead_category" => $lead_category,
                "created_at" => $date_added,
                "customer_type" => $customer_type,
                "email" => $email,
                "phone_no" => $phone_no,
                "whatsapp_no" => $whatsapp_no,
                "address" => $address,
                "pincode" => $pincode,
                "country_name" => $country_name,
                "state_name" => $state_name,
                "city_name" => $city_name,
                "description" => $description,
                "last_activity" => $last_activity,
                "assign_user_name" => $assign_user_name,
                "net_amount" => $net_amount,
                "status" => $status,
                "last_activity_date" => $last_activity_date,
                "last_activity_type" => $last_activity_type,
                "last_activity_name" => $last_activity_name,
                "last_internal_remarks" => $last_internal_remarks,
                "label_color" => $label_color,
                "selectedLabelArr" => $selectedLabelArr,
                "estimate_status" => $estimate_status,
                "follow_up_datetime" => $follow_up_datetime,
                 "created_at" => ($record->created_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('Y-m-d H:i:s'):'',
                "updated_at" => ($record->updated_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->updated_at)
                ->format('Y-m-d H:i:s'):'',
                "last_is_modified" => $record->last_is_modified,
                "last_is_follow_up" => $record->last_is_follow_up,
                "user_id" => $record->user_id,
            );

        }
        if (is_null($data)) {
            return $this->sendError('Follow up not found', ['Lead not found'], 422);
        }
        return $this->sendResponse($data, 'Follow up retrieved successfully');
    }

    public function activityFollowupSave(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
//                'activity_type' => 'required',
//                'activity_name' => 'required',
            'follow_up_datetime_status' => 'required',
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }

        $input['user_id'] = $this->logged_user->id;
        $input['company_id'] = $this->company_id;
        $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
        $customer_id = $input['customer_id'];

        $customerData = Customer::select("assigned_to_user")->where('id', '=', $customer_id)->first();

        if ($input['estimate_id'] > 0) {
            $paramArr['estimate_id'] = $input['estimate_id'];
        }
        $paramArr['assigned_to'] = $customerData->assigned_to_user;
        $paramArr['customer_id'] = $customer_id;
        $paramArr['activity_type'] = 9;
        $paramArr['activity_name'] = 'Follow Up';
        $paramArr['activity_notes'] = $input['activity_notes'];
//            $paramArr['follow_up_datetime'] = Carbon::createFromFormat('d-m-Y H:i A', $input['follow_up_datetime_status'])->format('Y-m-d H:i:s');
        $paramArr['follow_up_datetime'] = (!empty($input['follow_up_datetime_status']) && $input['follow_up_datetime_status'] != '0000-00-00 00:00:00') ? Carbon::createFromFormat('d-m-Y H:i A', $input['follow_up_datetime_status'])->format('Y-m-d H:i:s') : '';
        $paramArr['entry_type'] = 'followup';
        $paramArr['is_modified'] = 1;
        $paramArr['is_follow_up'] = 1;
        $paramArr['user_id'] = $this->logged_user->id;
        $paramArr['company_id'] = $this->company_id;
        $paramArr['created_by'] = $this->logged_user->id;
        $paramArr['updated_by'] = $this->logged_user->id;

//            if ($id == 0) {
        if (isset($input['est_flag']) && $input['est_flag'] == 'c') {
//                $paramArr['activity_notes'] = trim($input['activity_notes'].', Date : '.$input['follow_up_datetime_status'],', ');
            $paramArr['internal_remarks'] = trim('<b>New follow up date</b> : ' . $input['follow_up_datetime_status'] . '</br><b>Notes</b> : ' . $input['activity_notes'], ', '); //<b>Status </b> : Inprogress </br>
            Estimate::where('id', $input['estimate_id'])->update(array('status' => "Inprogress"));
            $activitylastId = DB::table('customer_timelines')->select('id')->where('estimate_id', $input['estimate_id'])->orderBy('id', 'DESC')->first();
            EstimateTimeline::where('id', $activitylastId->id)->update(array('activity_estimate_status' => "Inprogress", "internal_remarks" => $paramArr['internal_remarks']));
//            Customer::find($customer_id)->update(["new_lead_flag" => 0]);
            if (isset($input['fl_lead_stage_id'])) {
                $customers = Customer::where('id', $customer_id)->update(["lead_stage_id" => $input['fl_lead_stage_id']]);
            }
        }

//        $paramArr['internal_remarks'] = "";
//            $customer = EstimateTimeline::create($paramArr);

        if (isset($input['est_flag']) && $input['est_flag'] == 'u') {
            $paramArr['is_follow_up'] = 0;
        }

        if (isset($input['est_flag']) && $input['est_flag'] == 'u') {
            $paramArr['internal_remarks'] = "";
//            if ((isset($input['old_status']) && $input['old_status'] != $input['customRadio1']) || (isset($paramArr['follow_up_datetime']) && !empty($input['follow_up_datetime']) && $input['follow_up_datetime'] != '0000-00-00 00:00:00' && $input['old_follow_up_date'] != $paramArr['follow_up_datetime'])) {
//            if (!empty($input['follow_up_datetime']) && $input['follow_up_datetime'] != '0000-00-00 00:00:00' && $input['old_follow_up_date'] != $paramArr['follow_up_datetime']) {
            if ((isset($paramArr['follow_up_datetime']) && !empty($input['follow_up_datetime_status']) && $input['follow_up_datetime_status'] != '0000-00-00 00:00:00' && $input['old_follow_up_date'] != $paramArr['follow_up_datetime'])) {
                $paramArr['is_modified'] = 1;
                $paramArr['is_follow_up'] = 0;

                /* if (isset($input['old_status']) && $input['old_status'] != $input['customRadio1']) {
                     Estimate::where('id', $input['estimate_id'])->update(array('status' => $input['customRadio1']));
                     $paramArr['internal_remarks'] .= " <b>Status updated</b> : " . $input['old_status'] . " to " . $input['customRadio1'] . "</br>";
 //                        EstimateTimeline::create($paramArr);
                 }*/

                if (isset($paramArr['follow_up_datetime']) && $input['old_follow_up_date'] != $paramArr['follow_up_datetime']) {
                    $paramArr['internal_remarks'] .= " <b>New follow up date</b> : " . $input['follow_up_datetime_status'] . "</br>";
                }
//                Customer::find($customer_id)->update(["new_lead_flag" => 0]);
            }
            if ($input['activity_notes'])
                $paramArr['internal_remarks'] .= " <b>Notes</b> : " . $input['activity_notes'];
        }
        if ($paramArr['follow_up_datetime']) {
            $customer_data = EstimateTimeline::select("id")
                ->where('customer_id', '=', $customer_id)
                ->wherein('activity_type', [9])   //[1, 2, 3, 9]
                ->orderBy('id', 'DESC')
                ->take(1)
                ->get()
                ->toArray();

            if ($customer_data && $customer_data[0]['id']) {
                $a = SalesPersonPerformances::where('timeline_id', $customer_data[0]['id'])->update(array('completed_task' => 1));
            }
        }

        $customer = EstimateTimeline::create($paramArr);
        Customer::where('id', $customer_id)->update(array('some_day_flg' => 0));
        /* } else {
             $paramArr['internal_remarks'] = "Activity updated";
             $customer = EstimateTimeline::find($id)->update($paramArr);
         }*/
        if ($paramArr['follow_up_datetime']) {
            $customer_data = EstimateTimeline::select("id")
                ->where('customer_id', '=', $customer_id)
                ->wherein('activity_type', [9])   //[1, 2, 3, 9]
                ->orderBy('id', 'DESC')
                ->get()
                ->toArray();


            $insArr['timeline_id'] = $customer_data[0]['id'];
            $insArr['customer_id'] = $customer_id;
            $insArr['user_id'] = $this->logged_user->id;
            $insArr['company_id'] = $this->company_id;
            $insArr['performance_date'] = (!empty($input['follow_up_datetime_status']) && $input['follow_up_datetime_status'] != '0000-00-00 00:00:00') ? Carbon::createFromFormat('d-m-Y H:i A', $input['follow_up_datetime_status'])->format('Y-m-d') : date('Y-m-d');
            $insArr['total_task'] = 1;
            SalesPersonPerformances::create($insArr);
        }
        return $this->sendResponse(['customer_id' => Crypt::encrypt($customer_id)], 'Successfully Saved!');

    }

    public function activityChangeEstimateStatusSave(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'activity_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }

        $input['user_id'] = $this->logged_user->id;
        $input['company_id'] = $this->company_id;
        $paramArr['id'] = $input['activity_id'];
        $paramArr['estimate_id'] = $input['activity_estimate_id'];
        $old_activity_status = $input['old_activity_status'];
        $old_activity_follow_up_date = $input['old_activity_follow_up_date'];
        $paramArr['customer_id'] = $input['activity_customer_id'];
        $paramArr['activity_notes'] = $input['activity_estimate_notes'];
        $paramArr['estimate_version_no'] = $input['activity_estimate_no'];
        $customerData = Customer::select("assigned_to_user")->where('id', '=', $paramArr['customer_id'])->first();
        $paramArr['assigned_to'] = $customerData->assigned_to_user;
        $paramArr['activity_type'] = 10;
        $paramArr['activity_name'] = 'Status Updated';
        $paramArr['activity_notes'] = $input['activity_estimate_notes'];
        $paramArr['activity_estimate_status'] = $input['activity_estimate_status'];
        $paramArr['follow_up_datetime'] = $input['old_activity_follow_up_date'];
        $follow_up_datetime = (!empty($input['old_activity_follow_up_date']) && $input['old_activity_follow_up_date'] != '0000-00-00 00:00:00') ? Carbon::createFromFormat('Y-m-d H:i:s', $input['old_activity_follow_up_date'])->format('d-m-Y H:i A') : '';

        $paramArr['entry_type'] = 'followup';
        $paramArr['is_modified'] = 1;
        $paramArr['is_follow_up'] = 0;
        $paramArr['user_id'] = $this->logged_user->id;
        $paramArr['company_id'] = $this->company_id;
        $paramArr['created_by'] = $this->logged_user->id;
        $paramArr['updated_by'] = $this->logged_user->id;
        EstimateTimeline::where('id', $paramArr['id'])->update(array('activity_estimate_status' => $paramArr['activity_estimate_status']));
        $testTemp = "<b>Estimate : </b>" . $paramArr['estimate_version_no'];

        if (isset($old_activity_status) && $paramArr['activity_estimate_status'] != $old_activity_status) {
            $testTemp .= "</br><b>Status updated</b> : " . $old_activity_status . " to " . $paramArr['activity_estimate_status'];

        }

        if ($input['activity_estimate_notes']) {
            $testTemp .= "</br><b>Notes</b> : " . $input['activity_estimate_notes'];
        }

        $paramArr['internal_remarks'] = $testTemp;
        $estimate = Estimate::select(["estimate_version", "estimate_no"])->where('id', $paramArr['estimate_id'])->get()->first();
        $tmp_est_name = '';
        if ($estimate->estimate_version > 0) {
            $tmp_est_name = '-V' . $estimate->estimate_version;
        }
        if ($input['activity_estimate_no'] == $estimate->estimate_no . $tmp_est_name)
            Estimate::where('id', $input['activity_estimate_id'])->update(array('status' => $paramArr['activity_estimate_status']));
        EstimateTimeline::create($paramArr);
        $tempEst = Estimate::where('estimate_no', '=', $estimate->estimate_no)->where('company_id', $this->company_id)->where(function ($query) {
            /*if ($id != 0) {
                $query->Where(function ($query) use ($id) {
                    $query->where('id', '!=', $id);
                });
            }*/
        })->select("id", "estimate_no")->get();
        if ($tempEst) {
            foreach ($tempEst as $val_est) {
                if ($paramArr['estimate_id'] != $val_est->id && ($paramArr['activity_estimate_status'] == "Accept" || $paramArr['activity_estimate_status'] == "Decline")) {
                    Estimate::where([['company_id', '=', $this->company_id], ["id", "=", $val_est->id]])->update(array('status' => ''));
                    EstimateTimeline::where('estimate_id', $val_est->id)->update(array('activity_estimate_status' => ''));
                }

                if ($paramArr['activity_estimate_status'] == "Inprogress") {
                    Estimate::where([['company_id', '=', $this->company_id], ["id", "=", $val_est->id]])->update(array('status' => 'Inprogress'));
                    EstimateTimeline::where('estimate_id', $val_est->id)->update(array('activity_estimate_status' => 'Inprogress'));
                }
            }
        }
        return $this->sendResponse([], 'Successfully Saved!');
    }

    public function removeFollowUpDate(Request $request)
    {
        $input = $request->all();
        $paramArr['id'] = $input['id'];
        $customer = EstimateTimeline::where('id', $paramArr['id'])->select('customer_id')->first();
        EstimateTimeline::where('id', $paramArr['id'])->update(array('follow_up_datetime' => "0000-00-00 00:00:00"));
        Customer::where('id', $customer->customer_id)->update(array('some_day_flg' => 0));

        $customer_data = EstimateTimeline::select("id")
            ->where('customer_id', '=', $customer->customer_id)
            ->wherein('activity_type', [9])   //[1, 2, 3, 9]
            ->orderBy('id', 'DESC')
            ->take(1)
            ->get()
            ->toArray();

        if ($customer_data && $customer_data[0]['id']) {
            $a = SalesPersonPerformances::where('timeline_id', $customer_data[0]['id'])->update(array('completed_task' => 1));
        }

        $logInput['follow_up_datetime'] = '0000-00-00 00:00:00';
        $logInput['assigned_to'] = $this->logged_user->id;
        $logInput['customer_id'] = $customer->customer_id;
        $logInput['entry_type'] = "remove follow up";
        $logInput['activity_name'] = "Remove Follow Up";
        $logInput['activity_type'] = "Remove Follow Up";
        $logInput['activity_type'] = 14;
        $logInput['internal_remarks'] = $input['notes'];
        $logInput['user_id'] = $this->logged_user->id;
        $logInput['company_id'] = $this->company_id;
        $logInput['created_by'] = $this->logged_user->id;
        $logInput['updated_by'] = $this->logged_user->id;
        LogActivity::addToActivityLog($logInput);
        Customer::find($customer->customer_id)->update(["new_lead_flag" => 0]);
        return $this->sendResponse([], 'Successfully Saved!');
    }

    public function setSomedayFollowUp(Request $request)
    {
        $input = $request->all();
        $customer_id = $input['customer_id'];
        $a = Customer::where('id', $customer_id)->update(array('some_day_flg' => 1));
        $customer_data = EstimateTimeline::select("id")
            ->where('customer_id', '=', $customer_id)
            ->wherein('activity_type', [9])   //[1, 2, 3, 9]
            ->orderBy('id', 'DESC')
            ->take(1)
            ->get()
            ->toArray();

        if ($customer_data && $customer_data[0]['id']) {
            $a = SalesPersonPerformances::where('timeline_id', $customer_data[0]['id'])->update(array('completed_task' => 1));

        }

        $logInput['follow_up_datetime'] = '0000-00-00 00:00:00';
        $logInput['assigned_to'] = $this->logged_user->id;
        $logInput['customer_id'] = $customer_id;
        $logInput['entry_type'] = "Someday follow up";
        $logInput['activity_name'] = "Someday follow up";
        $logInput['activity_type'] = "Someday Follow Up";
        $logInput['activity_type'] = 15;
        $logInput['internal_remarks'] = $input['notes'];
        $logInput['user_id'] = $this->logged_user->id;
        $logInput['company_id'] = $this->company_id;
        $logInput['created_by'] = $this->logged_user->id;
        $logInput['updated_by'] = $this->logged_user->id;
        LogActivity::addToActivityLog($logInput);
        return $this->sendResponse([], 'Successfully Saved!');
    }

    public function getTodayFollowup($fil_user_id,Request $request)
    {
        $leadStageId = $request->input('lead_stage_id');
        $leadLabelId = $request->input('lead_label_id');
        $leadSourceId = $request->input('lead_source_id');
        $leadCategoryId = $request->input('lead_category_id');
        $estimateStatus = $request->input('estimate_status');
        $cityName = $request->input('city_name');
        $stateId = $request->input('state_id');
        $countryId = $request->input('country_id');
        $leadStartDate = $request->input('lead_start_date');
        $leadEndDate = $request->input('lead_end_date');
        $leadStageIdArr = ($leadStageId) ? explode(",", $leadStageId) : [];
        $leadLabelIdArr = ($leadLabelId) ? explode(",", $leadLabelId) : [];
        $leadSourceIdArr = ($leadSourceId) ? explode(",", $leadSourceId) : [];
        $leadCategoryIdArr = ($leadCategoryId) ? explode(",", $leadCategoryId) : [];
        $estimateStatusArr = ($estimateStatus) ? explode(",", $estimateStatus) : [];

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.*', DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code'))
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 0)
            ->where(function ($query) use ($fil_user_id, $leadStageIdArr, $leadLabelIdArr, $cityName,$stateId,$countryId,$leadSourceIdArr,$leadCategoryIdArr,$estimateStatusArr,$leadStartDate,$leadEndDate) {
                if (!empty($leadStageIdArr) && count($leadStageIdArr) > 0) {
                    $query->WhereIn('cv.lead_stage_id', $leadStageIdArr);
                }
                if (!empty($leadSourceIdArr) && count($leadSourceIdArr) > 0) {
                    $query->WhereIn('cv.customer_lead_id', $leadSourceIdArr);
                }
                if (!empty($leadCategoryIdArr) && count($leadCategoryIdArr) > 0) {
                    $query->WhereIn('cv.customer_category_id', $leadCategoryIdArr);
                }
                if (!empty($leadLabelIdArr) && count($leadLabelIdArr) > 0) {
                    $query->WhereIn('lg.id', $leadLabelIdArr);
                }
                if (!empty($cityName)) {
                    $query->Where('cv.city_name', $cityName);
                }
                if (!empty($stateId)) {
                    $query->Where('cv.state_id', $stateId);
                }
                if (!empty($countryId)) {
                    $query->Where('cv.country_id', $countryId);
                }
                if (!empty($estimateStatusArr) && count($estimateStatusArr) > 0) {
                    $query->WhereIn('cv.estimate_status',$estimateStatusArr);
                }
                if($leadStartDate && $leadEndDate) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(cv.created_at, '%Y-%m-%d')"), [$leadStartDate, $leadEndDate]);
                }
                if ($fil_user_id > 0) {
                    $query->where('cv.assigned_to_user', '=', $fil_user_id);
                }
            })
            ->whereRaw('DATE(cv.last_follow_up_datetime) = ?', [date('Y-m-d')])
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id); CMX
                }
            })
            ->groupBy('cv.id')
            ->orderBy('cv.last_follow_up_datetime', 'ASC')
            ->get();

        $data = array();
        foreach ($records as $record) {

            $country_data = [];
            /*if ($record->currency_name_country_id)
                $country_data = Country::where("id", $record->currency_name_country_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->orderBy('id', 'DESC')->get()->first();*/
            if ($record->est_currency_id)
                $country_data = Country::where("id", $record->est_currency_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->first();
            $results = DB::table('customer_labels')
                ->select(DB::raw("group_concat(label_id separator ',') as labelId"))
                ->where('customer_id', '=', $record->id)
                ->groupBy('customer_id')
                ->get()->toArray();
            $label_color = [];
            $selectedLabelArr = [];
            if ($results) {
                $selectedLabelArr = explode(',', $results[0]->labelId);

                $label_color = DB::table('lead_groups')
                    ->select('name', 'color_code', 'id')
                    ->whereIn('id', $selectedLabelArr)
                    ->get()->toArray();
            }


            $data[] = array(
                "id" => $record->id,
                "name" => $record->name,
                "company_name" => $record->company_name,
                "lead_origin" => $record->lead_origin,
                "lead_category" => $record->lead_category,
                "created_at" => \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('d-m-Y h:i A'),
                "customer_type" => $record->customer_type,
                "email" => $record->email,
                "phone_no" => $record->phone_no,
                "whatsapp_no" => $record->whatsapp_no,
                "address" => $record->address,
                "pincode" => $record->pincode,
                "country_name" => $record->country_name,
                "state_name" => $record->state_name,
                "city_name" => $record->city_name,
                "description" => $record->description,
                "last_activity" => $record->last_activity,
                "assign_user_name" => $record->user_name,
//                "net_amount" => $record->net_amount,
                "net_amount" => $record->net_amount,
                "status" => $record->status,
                "last_activity_date" => ($record->last_activity_date) ? $record->last_activity_date : null,
                "last_activity_type" => $record->last_activity_type,
                "last_activity_name" => $record->last_activity_name,
                "last_internal_remarks" => $record->last_internal_remarks,
                "label_color" => $label_color,
                "selectedLabelArr" => $selectedLabelArr,
                "estimate_status" => $record->estimate_status,
                "follow_up_datetime" => ($record->last_follow_up_datetime && $record->last_follow_up_datetime != '0000-00-00 00:00:00') ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->last_follow_up_datetime)->format('Y-m-d H:i:s') : null,
                 "created_at" => ($record->created_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('Y-m-d H:i:s'):'',
                "updated_at" => ($record->updated_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->updated_at)
                ->format('Y-m-d H:i:s'):'',
                "last_is_modified" => $record->last_is_modified,
                "last_is_follow_up" => $record->last_is_follow_up,
                "currency_symbol" => (isset($country_data->currency_symbol)) ? $country_data->currency_symbol : '',
                "new_lead_flag" => $record->new_lead_flag,
                "lead_stage_name" => $record->lead_stage_name,
                "lead_stage_color_code" => $record->lead_stage_color_code,
                "last_activity_id" => $record->last_activity_id,
                "user_id" => $record->user_id,
            );

        }
        if (is_null($data)) {
            return $this->sendError('Follow up not found', ['Lead not found'], 422);
        }
        return $this->sendResponse($data, 'Follow up retrieved successfully');
    }

    public function getLeadByUser($user_id, $start, $rowperpage, $orderBy, Request $request)
    {
        if ($orderBy == 0) {
            $order_by = 'customers_views.id';
            $order_by_name = 'desc';
        }

        if ($orderBy == 1) {
            $order_by = 'customers_views.last_activity_date';
            $order_by_name = 'desc';
        }

        if ($orderBy == 2) {
            $order_by = 'customers_views.name';
            $order_by_name = 'asc';
        }

        $leadStageId = $request->input('lead_stage_id');
        $leadLabelId = $request->input('lead_label_id');
        $leadSourceId = $request->input('lead_source_id');
        $leadCategoryId = $request->input('lead_category_id');
        $estimateStatus = $request->input('estimate_status');
        $cityName = $request->input('city_name');
        $stateId = $request->input('state_id');
        $countryId = $request->input('country_id');
        $leadStartDate = $request->input('lead_start_date');
        $leadEndDate = $request->input('lead_end_date');
        $leadStageIdArr = ($leadStageId) ? explode(",", $leadStageId) : [];
        $leadLabelIdArr = ($leadLabelId) ? explode(",", $leadLabelId) : [];
        $leadSourceIdArr = ($leadSourceId) ? explode(",", $leadSourceId) : [];
        $leadCategoryIdArr = ($leadCategoryId) ? explode(",", $leadCategoryId) : [];
        $estimateStatusArr = ($estimateStatus) ? explode(",", $estimateStatus) : [];

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
        $records = ViewCustomerData::
        leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'customers_views.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->where(function ($query) use ($user_perm) {
//                if (!in_array('give-access-to-attend-unassigned-leads', $user_perm)) {
                $query->where('customers_views.company_id', $this->company_id);
//                }
            })
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
                    $query->orwhere('customers_views.user_id', '=', $this->logged_user->id);
                }

                if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm)) {
                    if (in_array('give-access-to-attend-unassigned-leads', $user_perm)) {

                        $query->where('customers_views.company_id', $this->company_id);
                        $query->orwhere('customers_views.assigned_to_user', '=', 0);
                    } else {
                        $query->where('customers_views.assigned_to_user', '!=', 0);
                    }
                } else {
                    if (in_array('give-access-to-attend-unassigned-leads', $user_perm)) {
                        $query->orwhere('customers_views.assigned_to_user', '=', 0);
                    }
                }
            })
            ->where(function ($query) use ($user_id, $leadStageIdArr, $leadLabelIdArr, $cityName,$stateId,$countryId,$leadSourceIdArr,$leadCategoryIdArr,$estimateStatusArr,$leadStartDate,$leadEndDate) {
                if (!empty($leadStageIdArr) && count($leadStageIdArr) > 0) {
                    $query->WhereIn('customers_views.lead_stage_id', $leadStageIdArr);
                }
                if (!empty($leadSourceIdArr) && count($leadSourceIdArr) > 0) {
                    $query->WhereIn('customers_views.customer_lead_id', $leadSourceIdArr);
                }
                if (!empty($leadCategoryIdArr) && count($leadCategoryIdArr) > 0) {
                    $query->WhereIn('customers_views.customer_category_id', $leadCategoryIdArr);
                }
                if (!empty($leadLabelIdArr) && count($leadLabelIdArr) > 0) {
                    $query->WhereIn('lg.id', $leadLabelIdArr);
                }
                if (!empty($cityName)) {
                    $query->Where('customers_views.city_name', $cityName);
                }
                if (!empty($stateId)) {
                    $query->Where('customers_views.state_id', $stateId);
                }
                if (!empty($countryId)) {
                    $query->Where('customers_views.country_id', $countryId);
                }
                if (!empty($estimateStatusArr) && count($estimateStatusArr) > 0) {
                    $query->WhereIn('customers_views.estimate_status',$estimateStatusArr);
                }
                if($leadStartDate && $leadEndDate) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(customers_views.created_at, '%Y-%m-%d')"), [$leadStartDate, $leadEndDate]);
                }

                $query->where(function ($query) use ($user_id) {
                    $query->where('customers_views.assigned_to_user', '=', $user_id);
                    $query->orwhere('customers_views.user_id', '=', $user_id);
                });
                //$query->where('customers_views.assigned_to_user', '=', $user_id);
//                $query->orwhere('user_id', '=', $user_id);
            })

//        where('company_id', $this->company_id)
            /*->where(function ($query) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_param) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_param)) {
                    $query->where('assigned_to_user', '=', $this->logged_user->id);
                    $query->orwhere('user_id', '=', $this->logged_user->id);
                }
            })*/
            ->select('customers_views.*')
            ->groupBy('customers_views.id')
            ->orderBy($order_by, $order_by_name)
//            ->orderBy('id', 'desc')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data = array();
        $i = 0;
        foreach ($records as $record) {
            $country_data = [];
            /* if ($record->currency_name_country_id)
                 $country_data = Country::where("id", $record->currency_name_country_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->orderBy('id', 'DESC')->get()->first();*/
            if ($record->est_currency_id)
                $country_data = Country::where("id", $record->est_currency_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->first();
            $id = $record->id;
            $customer_type = $record->customer_type;
            $name = $record->name;
            $lead_origin = $record->lead_origin;
            $lead_category = $record->lead_category;
            $email = $record->email;
            $phone_no = $record->phone_no;
            $whatsapp_no = $record->whatsapp_no;
            $address = $record->address;
            $pincode = $record->pincode;
            $description = $record->description;
            $status = $record->status;
            $country_name = $record->country_name;
            $state_name = $record->state_name;
            $city_name = $record->city_name;
            $last_activity = $record->last_activity;
            $assign_user_name = $record->user_name;
//                "net_amount" => $record->net_amount,
            $net_amount = $record->net_amount;
            $estimate_status = $record->estimate_status;
            $last_activity_type = $record->last_activity_type;
            $last_activity_name = $record->last_activity_name;
            $last_internal_remarks = $record->last_internal_remarks;
            $some_day_flg = $record->some_day_flg;
            $date_added = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('d-m-Y h:i A');
            $last_activity_date = ($record->last_activity_date) ? $record->last_activity_date : null;
            $follow_up_datetime = ($record->last_follow_up_datetime) ? $record->last_follow_up_datetime : null;
            $i++;

            $results = DB::table('customer_labels')
                ->select(DB::raw("group_concat(label_id separator ',') as labelId"))
                ->where('customer_id', '=', $record->id)
                ->groupBy('customer_id')
                ->get()->toArray();
            $label_color = [];
            $selectedLabelArr = [];
            if ($results) {
                $selectedLabelArr = explode(',', $results[0]->labelId);

                $label_color = DB::table('lead_groups')
                    ->select('name', 'color_code', 'id')
                    ->whereIn('id', $selectedLabelArr)
                    ->get()->toArray();
            }
            $data[] = array(
                "sr_no" => $i,
                "id" => $id,
                "name" => $name,
                "company_name" => $record->company_name,
                "lead_origin" => $lead_origin,
                "lead_category" => $lead_category,
                "created_at" => $date_added,
                "customer_type" => $customer_type,
                "email" => $email,
                "phone_no" => $phone_no,
                "whatsapp_no" => $whatsapp_no,
                "address" => $address,
                "pincode" => $pincode,
                "country_name" => $country_name,
                "state_name" => $state_name,
                "city_name" => $city_name,
                "description" => $description,
                "last_activity" => $last_activity,
                "assign_user_name" => $assign_user_name,
                "net_amount" => $net_amount,
                "status" => $status,
                "last_activity_date" => $last_activity_date,
                "last_activity_type" => $last_activity_type,
                "last_activity_name" => $last_activity_name,
                "last_internal_remarks" => $last_internal_remarks,
                "label_color" => $label_color,
                "selectedLabelArr" => $selectedLabelArr,
                "estimate_status" => $estimate_status,
                "estimate_no" => $record->estimate_no,
                "follow_up_datetime" => $follow_up_datetime,
                "some_day_flg" => $some_day_flg,
                "country_code" => $record->country_code,
                "whatsapp_country_code" => $record->whatsapp_country_code,
                 "created_at" => ($record->created_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('Y-m-d H:i:s'):'',
                "updated_at" => ($record->updated_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->updated_at)
                ->format('Y-m-d H:i:s'):'',
                "last_is_modified" => $record->last_is_modified,
                "last_is_follow_up" => $record->last_is_follow_up,
                "currency_symbol" => (isset($country_data->currency_symbol)) ? $country_data->currency_symbol : '',
                "lead_stage_name" => $record->lead_stage_name,
                "lead_stage_color_code" => $record->lead_stage_color_code,
                "last_activity_id" => $record->last_activity_id,
                "user_id" => $record->user_id,
            );

        }
        if (is_null($data)) {
            return $this->sendError('Lead not found', ['Lead not found'], 422);
        }
        return $this->sendResponse($data, 'Lead retrieved successfully');
    }

    public function getUpcomingFollowup($start, $rowperpage, $fil_user_id, Request $request)
    {
        $leadStageId = $request->input('lead_stage_id');
        $leadLabelId = $request->input('lead_label_id');
        $leadSourceId = $request->input('lead_source_id');
        $estimateStatus = $request->input('estimate_status');
        $cityName = $request->input('city_name');
        $stateId = $request->input('state_id');
        $countryId = $request->input('country_id');
        $leadStartDate = $request->input('lead_start_date');
        $leadEndDate = $request->input('lead_end_date');
        $leadStageIdArr = ($leadStageId) ? explode(",", $leadStageId) : [];
        $leadLabelIdArr = ($leadLabelId) ? explode(",", $leadLabelId) : [];
        $leadSourceIdArr = ($leadSourceId) ? explode(",", $leadSourceId) : [];
        $estimateStatusArr = ($estimateStatus) ? explode(",", $estimateStatus) : [];

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.*', DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code'))
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 0)
            ->where(function ($query) use ($fil_user_id, $leadStageIdArr, $leadLabelIdArr, $cityName,$stateId,$countryId,$leadSourceIdArr,$estimateStatusArr,$leadStartDate,$leadEndDate) {
                if (!empty($leadStageIdArr) && count($leadStageIdArr) > 0) {
                    $query->WhereIn('cv.lead_stage_id', $leadStageIdArr);
                }
                if (!empty($leadSourceIdArr) && count($leadSourceIdArr) > 0) {
                    $query->WhereIn('cv.customer_lead_id', $leadSourceIdArr);
                }
                if (!empty($leadLabelIdArr) && count($leadLabelIdArr) > 0) {
                    $query->WhereIn('lg.id', $leadLabelIdArr);
                }
                if (!empty($cityName)) {
                    $query->Where('cv.city_name', $cityName);
                }
                if (!empty($stateId)) {
                    $query->Where('cv.state_id', $stateId);
                }
                if (!empty($countryId)) {
                    $query->Where('cv.country_id', $countryId);
                }
                if (!empty($estimateStatusArr) && count($estimateStatusArr) > 0) {
                    $query->WhereIn('cv.estimate_status',$estimateStatusArr);
                }
                if($leadStartDate && $leadEndDate) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(cv.created_at, '%Y-%m-%d')"), [$leadStartDate, $leadEndDate]);
                }
                if ($fil_user_id > 0) {
                    $query->where('cv.assigned_to_user', '=', $fil_user_id);
                }
            })
            ->where(DB::raw('DATE(cv.last_follow_up_datetime)'), ">", date('Y-m-d'))
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id); CMX
                }
            })
            ->groupBy('cv.id')
            ->orderBy('cv.last_follow_up_datetime', 'ASC')
            ->skip($start)
            ->take($rowperpage)
            ->get();

        $data = array();
        foreach ($records as $record) {
            $country_data = [];
            /*if ($record->currency_name_country_id)
                $country_data = Country::where("id", $record->currency_name_country_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->orderBy('id', 'DESC')->get()->first();*/
            if ($record->est_currency_id)
                $country_data = Country::where("id", $record->est_currency_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->first();
            $results = DB::table('customer_labels')
                ->select(DB::raw("group_concat(label_id separator ',') as labelId"))
                ->where('customer_id', '=', $record->id)
                ->groupBy('customer_id')
                ->get()->toArray();
            $label_color = [];
            $selectedLabelArr = [];
            if ($results) {
                $selectedLabelArr = explode(',', $results[0]->labelId);

                $label_color = DB::table('lead_groups')
                    ->select('name', 'color_code', 'id')
                    ->whereIn('id', $selectedLabelArr)
                    ->get()->toArray();
            }


            $data[] = array(
                "id" => $record->id,
                "name" => $record->name,
                "company_name" => $record->company_name,
                "lead_origin" => $record->lead_origin,
                "lead_category" => $record->lead_category,
                "created_at" => \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('d-m-Y h:i A'),
                "customer_type" => $record->customer_type,
                "email" => $record->email,
                "phone_no" => $record->phone_no,
                "whatsapp_no" => $record->whatsapp_no,
                "address" => $record->address,
                "pincode" => $record->pincode,
                "country_name" => $record->country_name,
                "state_name" => $record->state_name,
                "city_name" => $record->city_name,
                "description" => $record->description,
                "last_activity" => $record->last_activity,
                "assign_user_name" => $record->user_name,
//                "net_amount" => $record->net_amount,
                "net_amount" => $record->net_amount,
                "status" => $record->status,
                "last_activity_date" => ($record->last_activity_date) ? $record->last_activity_date : null,
                "last_activity_type" => $record->last_activity_type,
                "last_activity_name" => $record->last_activity_name,
                "last_internal_remarks" => $record->last_internal_remarks,
                "label_color" => $label_color,
                "selectedLabelArr" => $selectedLabelArr,
                "estimate_status" => $record->estimate_status,
                "follow_up_datetime" => ($record->last_follow_up_datetime && $record->last_follow_up_datetime != '0000-00-00 00:00:00') ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->last_follow_up_datetime)->format('Y-m-d H:i:s') : null,
                 "created_at" => ($record->created_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('Y-m-d H:i:s'):'',
                "updated_at" => ($record->updated_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->updated_at)
                ->format('Y-m-d H:i:s'):'',
                "last_is_modified" => $record->last_is_modified,
                "last_is_follow_up" => $record->last_is_follow_up,
                "currency_symbol" => (isset($country_data->currency_symbol)) ? $country_data->currency_symbol : '',
                "new_lead_flag" => $record->new_lead_flag,
                "lead_stage_name" => $record->lead_stage_name,
                "lead_stage_color_code" => $record->lead_stage_color_code,
                "last_activity_id" => $record->last_activity_id,
                "user_id" => $record->user_id,
            );

        }
        if (is_null($data)) {
            return $this->sendError('Follow up not found', ['Lead not found'], 422);
        }
        return $this->sendResponse($data, 'Follow up retrieved successfully');
    }

    public function getOverdueFollowup($start, $rowperpage, $fil_user_id, Request $request)
    {
        $leadStageId = $request->input('lead_stage_id');
        $leadLabelId = $request->input('lead_label_id');
        $leadSourceId = $request->input('lead_source_id');
        $leadCategoryId = $request->input('lead_category_id');
        $estimateStatus = $request->input('estimate_status');
        $cityName = $request->input('city_name');
        $stateId = $request->input('state_id');
        $countryId = $request->input('country_id');
        $leadStartDate = $request->input('lead_start_date');
        $leadEndDate = $request->input('lead_end_date');
        $leadStageIdArr = ($leadStageId) ? explode(",", $leadStageId) : [];
        $leadLabelIdArr = ($leadLabelId) ? explode(",", $leadLabelId) : [];
        $leadSourceIdArr = ($leadSourceId) ? explode(",", $leadSourceId) : [];
        $leadCategoryIdArr = ($leadCategoryId) ? explode(",", $leadCategoryId) : [];
        $estimateStatusArr = ($estimateStatus) ? explode(",", $estimateStatus) : [];

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.*', DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code'))
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 0)
            ->where(function ($query) use ($fil_user_id, $leadStageIdArr, $leadLabelIdArr, $cityName,$stateId,$countryId,$leadSourceIdArr,$leadCategoryIdArr,$estimateStatusArr,$leadStartDate,$leadEndDate) {
                if (!empty($leadStageIdArr) && count($leadStageIdArr) > 0) {
                    $query->WhereIn('cv.lead_stage_id', $leadStageIdArr);
                }
                if (!empty($leadSourceIdArr) && count($leadSourceIdArr) > 0) {
                    $query->WhereIn('cv.customer_lead_id', $leadSourceIdArr);
                }
                if (!empty($leadCategoryIdArr) && count($leadCategoryIdArr) > 0) {
                    $query->WhereIn('cv.customer_category_id', $leadCategoryIdArr);
                }
                if (!empty($leadLabelIdArr) && count($leadLabelIdArr) > 0) {
                    $query->WhereIn('lg.id', $leadLabelIdArr);
                }
                if (!empty($cityName)) {
                    $query->Where('cv.city_name', $cityName);
                }
                if (!empty($stateId)) {
                    $query->Where('cv.state_id', $stateId);
                }
                if (!empty($countryId)) {
                    $query->Where('cv.country_id', $countryId);
                }
                if (!empty($estimateStatusArr) && count($estimateStatusArr) > 0) {
                    $query->WhereIn('cv.estimate_status',$estimateStatusArr);
                }
                if($leadStartDate && $leadEndDate) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(cv.created_at, '%Y-%m-%d')"), [$leadStartDate, $leadEndDate]);
                }
                if ($fil_user_id > 0) {
                    $query->where(function ($query) use ($fil_user_id) {
                        $query->where('cv.assigned_to_user', '=', $fil_user_id);
//                        $query->orwhere('cv.user_id', '=', $fil_user_id); CMX
                    });
                    //$query->where('cv.assigned_to_user', '=', $fil_user_id);
                }
            })
            ->where(DB::raw('DATE(cv.last_follow_up_datetime)'), "<", date('Y-m-d'))
            ->where('cv.last_follow_up_datetime', '!=', '0000-00-00 00:00:00')
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id); CMX
                }
            })
            ->groupBy('cv.id')
            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();

        $data = array();
        foreach ($records as $record) {
            $country_data = [];
            /*if ($record->currency_name_country_id)
                $country_data = Country::where("id", $record->currency_name_country_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->orderBy('id', 'DESC')->get()->first();*/
            if ($record->est_currency_id)
                $country_data = Country::where("id", $record->est_currency_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->first();
            $results = DB::table('customer_labels')
                ->select(DB::raw("group_concat(label_id separator ',') as labelId"))
                ->where('customer_id', '=', $record->id)
                ->groupBy('customer_id')
                ->get()->toArray();
            $label_color = [];
            $selectedLabelArr = [];
            if ($results) {
                $selectedLabelArr = explode(',', $results[0]->labelId);

                $label_color = DB::table('lead_groups')
                    ->select('name', 'color_code', 'id')
                    ->whereIn('id', $selectedLabelArr)
                    ->get()->toArray();
            }


            $data[] = array(
                "id" => $record->id,
                "name" => $record->name,
                "company_name" => $record->company_name,
                "lead_origin" => $record->lead_origin,
                "lead_category" => $record->lead_category,
                "created_at" => \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('d-m-Y h:i A'),
                "customer_type" => $record->customer_type,
                "email" => $record->email,
                "phone_no" => $record->phone_no,
                "whatsapp_no" => $record->whatsapp_no,
                "address" => $record->address,
                "pincode" => $record->pincode,
                "country_name" => $record->country_name,
                "state_name" => $record->state_name,
                "city_name" => $record->city_name,
                "description" => $record->description,
                "last_activity" => $record->last_activity,
                "assign_user_name" => $record->user_name,
//                "net_amount" => $record->net_amount,
                "net_amount" => $record->net_amount,
                "status" => $record->status,
                "last_activity_date" => ($record->last_activity_date) ? $record->last_activity_date : null,
                "last_activity_type" => $record->last_activity_type,
                "last_activity_name" => $record->last_activity_name,
                "last_internal_remarks" => $record->last_internal_remarks,
                "label_color" => $label_color,
                "selectedLabelArr" => $selectedLabelArr,
                "estimate_status" => $record->estimate_status,
                "follow_up_datetime" => ($record->last_follow_up_datetime && $record->last_follow_up_datetime != '0000-00-00 00:00:00') ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->last_follow_up_datetime)->format('Y-m-d H:i:s') : '',
                 "created_at" => ($record->created_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('Y-m-d H:i:s'):'',
                "updated_at" => ($record->updated_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->updated_at)
                ->format('Y-m-d H:i:s'):'',
                "last_is_modified" => $record->last_is_modified,
                "last_is_follow_up" => $record->last_is_follow_up,
                "currency_symbol" => (isset($country_data->currency_symbol)) ? $country_data->currency_symbol : '',
                "new_lead_flag" => $record->new_lead_flag,
                "lead_stage_name" => $record->lead_stage_name,
                "lead_stage_color_code" => $record->lead_stage_color_code,
                "last_activity_id" => $record->last_activity_id,
                "user_id" => $record->user_id,
            );

        }
        if (is_null($data)) {
            return $this->sendError('Follow up not found', ['Lead not found'], 422);
        }
        return $this->sendResponse($data, 'Follow up retrieved successfully');
    }

    public function getSomedayFollowup($start, $rowperpage, $fil_user_id, Request $request)
    {
        $leadStageId = $request->input('lead_stage_id');
        $leadLabelId = $request->input('lead_label_id');
        $leadSourceId = $request->input('lead_source_id');
        $estimateStatus = $request->input('estimate_status');
        $cityName = $request->input('city_name');
        $stateId = $request->input('state_id');
        $countryId = $request->input('country_id');
        $leadStartDate = $request->input('lead_start_date');
        $leadEndDate = $request->input('lead_end_date');
        $leadStageIdArr = ($leadStageId) ? explode(",", $leadStageId) : [];
        $leadLabelIdArr = ($leadLabelId) ? explode(",", $leadLabelId) : [];
        $leadSourceIdArr = ($leadSourceId) ? explode(",", $leadSourceId) : [];
        $estimateStatusArr = ($estimateStatus) ? explode(",", $estimateStatus) : [];

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.*', DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code'))
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 1)
            ->where(function ($query) use ($fil_user_id, $leadStageIdArr, $leadLabelIdArr, $cityName,$stateId,$countryId,$leadSourceIdArr,$estimateStatusArr,$leadStartDate,$leadEndDate) {
                if (!empty($leadStageIdArr) && count($leadStageIdArr) > 0) {
                    $query->WhereIn('cv.lead_stage_id', $leadStageIdArr);
                }
                if (!empty($leadSourceIdArr) && count($leadSourceIdArr) > 0) {
                    $query->WhereIn('cv.customer_lead_id', $leadSourceIdArr);
                }
                if (!empty($leadLabelIdArr) && count($leadLabelIdArr) > 0) {
                    $query->WhereIn('lg.id', $leadLabelIdArr);
                }
                if (!empty($cityName)) {
                    $query->Where('cv.city_name', $cityName);
                }
                if (!empty($stateId)) {
                    $query->Where('cv.state_id', $stateId);
                }
                if (!empty($countryId)) {
                    $query->Where('cv.country_id', $countryId);
                }
                if (!empty($estimateStatusArr) && count($estimateStatusArr) > 0) {
                    $query->WhereIn('cv.estimate_status',$estimateStatusArr);
                }
                if($leadStartDate && $leadEndDate) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(cv.created_at, '%Y-%m-%d')"), [$leadStartDate, $leadEndDate]);
                }
                if ($fil_user_id > 0) {
                    $query->where('cv.assigned_to_user', '=', $fil_user_id);
                }
            })
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id); CMX
                }
            })
            ->groupBy('cv.id')
            ->orderBy('cv.created_at', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();

        $data = array();
        foreach ($records as $record) {
            $country_data = [];
            /* if ($record->currency_name_country_id)
                 $country_data = Country::where("id", $record->currency_name_country_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->orderBy('id', 'DESC')->get()->first();*/
            if ($record->est_currency_id)
                $country_data = Country::where("id", $record->est_currency_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->first();
            $results = DB::table('customer_labels')
                ->select(DB::raw("group_concat(label_id separator ',') as labelId"))
                ->where('customer_id', '=', $record->id)
                ->groupBy('customer_id')
                ->get()->toArray();
            $label_color = [];
            $selectedLabelArr = [];
            if ($results) {
                $selectedLabelArr = explode(',', $results[0]->labelId);

                $label_color = DB::table('lead_groups')
                    ->select('name', 'color_code', 'id')
                    ->whereIn('id', $selectedLabelArr)
                    ->get()->toArray();
            }


            $data[] = array(
                "id" => $record->id,
                "name" => $record->name,
                "company_name" => $record->company_name,
                "lead_origin" => $record->lead_origin,
                "lead_category" => $record->lead_category,
                "created_at" => \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('d-m-Y h:i A'),
                "customer_type" => $record->customer_type,
                "email" => $record->email,
                "phone_no" => $record->phone_no,
                "whatsapp_no" => $record->whatsapp_no,
                "address" => $record->address,
                "pincode" => $record->pincode,
                "country_name" => $record->country_name,
                "state_name" => $record->state_name,
                "city_name" => $record->city_name,
                "description" => $record->description,
                "last_activity" => $record->last_activity,
                "assign_user_name" => $record->user_name,
//                "net_amount" => $record->net_amount,
                "net_amount" => $record->net_amount,
                "status" => $record->status,
                "last_activity_date" => ($record->last_activity_date) ? $record->last_activity_date : null,
                "last_activity_type" => $record->last_activity_type,
                "last_activity_name" => $record->last_activity_name,
                "last_internal_remarks" => $record->last_internal_remarks,
                "label_color" => $label_color,
                "selectedLabelArr" => $selectedLabelArr,
                "estimate_status" => $record->estimate_status,
                "follow_up_datetime" => ($record->last_follow_up_datetime && $record->last_follow_up_datetime != '0000-00-00 00:00:00') ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->last_follow_up_datetime)->format('Y-m-d H:i:s') : null,
                 "created_at" => ($record->created_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('Y-m-d H:i:s'):'',
                "updated_at" => ($record->updated_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->updated_at)
                ->format('Y-m-d H:i:s'):'',
                "last_is_modified" => $record->last_is_modified,
                "last_is_follow_up" => $record->last_is_follow_up,
                "currency_symbol" => (isset($country_data->currency_symbol)) ? $country_data->currency_symbol : '',
                "new_lead_flag" => $record->new_lead_flag,
                "lead_stage_name" => $record->lead_stage_name,
                "lead_stage_color_code" => $record->lead_stage_color_code,
                "last_activity_id" => $record->last_activity_id,
                "user_id" => $record->user_id,
            );

        }
        if (is_null($data)) {
            return $this->sendError('Follow up not found', ['Lead not found'], 422);
        }
        return $this->sendResponse($data, 'Follow up retrieved successfully');
    }

    public function getNeverFollowup($start, $rowperpage, $fil_user_id, Request $request)
    {
        $leadStageId = $request->input('lead_stage_id');
        $leadLabelId = $request->input('lead_label_id');
        $leadSourceId = $request->input('lead_source_id');
        $leadCategoryId = $request->input('lead_category_id');
        $estimateStatus = $request->input('estimate_status');
        $cityName = $request->input('city_name');
        $stateId = $request->input('state_id');
        $countryId = $request->input('country_id');
        $leadStartDate = $request->input('lead_start_date');
        $leadEndDate = $request->input('lead_end_date');
        $leadStageIdArr = ($leadStageId) ? explode(",", $leadStageId) : [];
        $leadLabelIdArr = ($leadLabelId) ? explode(",", $leadLabelId) : [];
        $leadSourceIdArr = ($leadSourceId) ? explode(",", $leadSourceId) : [];
        $leadCategoryIdArr = ($leadCategoryId) ? explode(",", $leadCategoryId) : [];
        $estimateStatusArr = ($estimateStatus) ? explode(",", $estimateStatus) : [];

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');

        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.*', DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code'))
            ->where('cv.company_id', $this->company_id)
//            ->where('cv.some_day_flg', 0)
            ->where(function ($query) use ($fil_user_id, $leadStageIdArr, $leadLabelIdArr, $cityName,$stateId,$countryId,$leadSourceIdArr,$leadCategoryIdArr,$estimateStatusArr,$leadStartDate,$leadEndDate) {
                if (!empty($leadStageIdArr) && count($leadStageIdArr) > 0) {
                    $query->WhereIn('cv.lead_stage_id', $leadStageIdArr);
                }
                if (!empty($leadSourceIdArr) && count($leadSourceIdArr) > 0) {
                    $query->WhereIn('cv.customer_lead_id', $leadSourceIdArr);
                }
                if (!empty($leadCategoryIdArr) && count($leadCategoryIdArr) > 0) {
                    $query->WhereIn('cv.customer_category_id', $leadCategoryIdArr);
                }
                if (!empty($leadLabelIdArr) && count($leadLabelIdArr) > 0) {
                    $query->WhereIn('lg.id', $leadLabelIdArr);
                }
                if (!empty($cityName)) {
                    $query->Where('cv.city_name', $cityName);
                }
                if (!empty($stateId)) {
                    $query->Where('cv.state_id', $stateId);
                }
                if (!empty($countryId)) {
                    $query->Where('cv.country_id', $countryId);
                }
                if (!empty($estimateStatusArr) && count($estimateStatusArr) > 0) {
                    $query->WhereIn('cv.estimate_status',$estimateStatusArr);
                }
                if($leadStartDate && $leadEndDate) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(cv.created_at, '%Y-%m-%d')"), [$leadStartDate, $leadEndDate]);
                }
                if ($fil_user_id > 0) {
                    $query->where('cv.assigned_to_user', '=', $fil_user_id);
                }
            })
            ->where(function ($query) use ($user_perm) {
                $query->where('cv.last_follow_up_datetime', '=', '0000-00-00 00:00:00');
                $query->orWhereNull('cv.last_follow_up_datetime');
            })
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id); CMX
                }
            })
            ->groupBy('cv.id')
            ->orderBy('cv.created_at', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        if (!$records) {
            return $this->sendError('Follow up not found', ['Follow up not found'], 422);
        }

        $data = array();
        foreach ($records as $record) {
            $country_data = [];
            /*if ($record->currency_name_country_id)
                $country_data = Country::where("id", $record->currency_name_country_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->orderBy('id', 'DESC')->get()->first();*/
            if ($record->est_currency_id)
                $country_data = Country::where("id", $record->est_currency_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->first();
            $results = DB::table('customer_labels')
                ->select(DB::raw("group_concat(label_id separator ',') as labelId"))
                ->where('customer_id', '=', $record->id)
                ->groupBy('customer_id')
                ->get()->toArray();
            $label_color = [];
            $selectedLabelArr = [];
            if ($results) {
                $selectedLabelArr = explode(',', $results[0]->labelId);

                $label_color = DB::table('lead_groups')
                    ->select('name', 'color_code', 'id')
                    ->whereIn('id', $selectedLabelArr)
                    ->get()->toArray();
            }


            $data[] = array(
                "id" => $record->id,
                "name" => $record->name,
                "company_name" => $record->company_name,
                "lead_origin" => $record->lead_origin,
                "lead_category" => $record->lead_category,
                "created_at" => \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('d-m-Y h:i A'),
                "customer_type" => $record->customer_type,
                "email" => $record->email,
                "phone_no" => $record->phone_no,
                "whatsapp_no" => $record->whatsapp_no,
                "address" => $record->address,
                "pincode" => $record->pincode,
                "country_name" => $record->country_name,
                "state_name" => $record->state_name,
                "city_name" => $record->city_name,
                "description" => $record->description,
                "last_activity" => $record->last_activity,
                "assign_user_name" => $record->user_name,
//                "net_amount" => $record->net_amount,
                "net_amount" => $record->net_amount,
                "status" => $record->status,
                "last_activity_date" => ($record->last_activity_date) ? $record->last_activity_date : null,
                "last_activity_type" => $record->last_activity_type,
                "last_activity_name" => $record->last_activity_name,
                "last_internal_remarks" => $record->last_internal_remarks,
                "label_color" => $label_color,
                "selectedLabelArr" => $selectedLabelArr,
                "estimate_status" => $record->estimate_status,
                "follow_up_datetime" => ($record->last_follow_up_datetime && $record->last_follow_up_datetime != '0000-00-00 00:00:00') ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->last_follow_up_datetime)->format('Y-m-d H:i:s') : null,
                 "created_at" => ($record->created_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('Y-m-d H:i:s'):'',
                "updated_at" => ($record->updated_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->updated_at)
                ->format('Y-m-d H:i:s'):'',
                "last_is_modified" => $record->last_is_modified,
                "last_is_follow_up" => $record->last_is_follow_up,
                "currency_symbol" => (isset($country_data->currency_symbol)) ? $country_data->currency_symbol : '',
                "new_lead_flag" => $record->new_lead_flag,
                "lead_stage_name" => $record->lead_stage_name,
                "lead_stage_color_code" => $record->lead_stage_color_code,
                "last_activity_id" => $record->last_activity_id,
                "user_id" => $record->user_id,
            );

        }
        if (is_null($data)) {
            return $this->sendError('Follow up not found', ['Lead not found'], 422);
        }
        return $this->sendResponse($data, 'Follow up retrieved successfully');
    }

    public function leadSearch($fil_user_id, $search = null, Request $request)
    {
        $leadStageId = $request->input('lead_stage_id');
        $leadLabelId = $request->input('lead_label_id');
        $leadSourceId = $request->input('lead_source_id');
        $leadCategoryId = $request->input('lead_category_id');
        $estimateStatus = $request->input('estimate_status');
        $cityName = $request->input('city_name');
        $stateId = $request->input('state_id');
        $countryId = $request->input('country_id');
        $leadStartDate = $request->input('lead_start_date');
        $leadEndDate = $request->input('lead_end_date');
        $leadStageIdArr = ($leadStageId) ? explode(",", $leadStageId) : [];
        $leadLabelIdArr = ($leadLabelId) ? explode(",", $leadLabelId) : [];
        $leadSourceIdArr = ($leadSourceId) ? explode(",", $leadSourceId) : [];
        $leadCategoryIdArr = ($leadCategoryId) ? explode(",", $leadCategoryId) : [];
        $estimateStatusArr = ($estimateStatus) ? explode(",", $estimateStatus) : [];

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
//        DB::enableQueryLog();
        $records = ViewCustomerData::
        leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'customers_views.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->where(function ($query) use ($user_perm) {
                $query->where('customers_views.company_id', $this->company_id);
            })
            ->where(function ($query) use ($user_perm, $fil_user_id) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('customers_views.user_id', '=', $this->logged_user->id); CMX
                }

                if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm)) {
                    if (in_array('give-access-to-attend-unassigned-leads', $user_perm)) {

                        $query->where('customers_views.company_id', $this->company_id);
                        $query->orwhere('customers_views.assigned_to_user', '=', 0);
                    } else {
                        $query->where('customers_views.assigned_to_user', '!=', 0);
                    }
                } else {
                    if (in_array('give-access-to-attend-unassigned-leads', $user_perm)) {
                        $query->orwhere('customers_views.assigned_to_user', '=', 0);
                    }
                }
            })
            ->where(function ($query) use ($user_perm, $fil_user_id, $leadStageIdArr, $leadLabelIdArr, $cityName,$stateId,$countryId,$leadSourceIdArr,$leadCategoryIdArr,$estimateStatusArr,$leadStartDate,$leadEndDate) {
                if (!empty($leadStageIdArr) && count($leadStageIdArr) > 0) {
                    $query->WhereIn('customers_views.lead_stage_id', $leadStageIdArr);
                }
                if (!empty($leadSourceIdArr) && count($leadSourceIdArr) > 0) {
                    $query->WhereIn('customers_views.customer_lead_id', $leadSourceIdArr);
                }
                if (!empty($leadCategoryIdArr) && count($leadCategoryIdArr) > 0) {
                    $query->WhereIn('customers_views.customer_category_id', $leadCategoryIdArr);
                }
                if (!empty($leadLabelIdArr) && count($leadLabelIdArr) > 0) {
                    $query->WhereIn('lg.id', $leadLabelIdArr);
                }
                if (!empty($cityName)) {
                    $query->Where('customers_views.city_name', $cityName);
                }
                if (!empty($stateId)) {
                    $query->Where('customers_views.state_id', $stateId);
                }
                if (!empty($countryId)) {
                    $query->Where('customers_views.country_id', $countryId);
                }
                if (!empty($estimateStatusArr) && count($estimateStatusArr) > 0) {
                    $query->WhereIn('customers_views.estimate_status',$estimateStatusArr);
                }
                if($leadStartDate && $leadEndDate) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(customers_views.created_at, '%Y-%m-%d')"), [$leadStartDate, $leadEndDate]);
                }
                if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm)) {
                    if ($fil_user_id > 0) {
                        $query->where('customers_views.assigned_to_user', '=', $fil_user_id);
                    }
                }
            })
            ->where(function ($query) use ($search) {
                if ($search != '') {
                    $query->where('customers_views.name', 'like', '%' . $search . '%');
                    $query->orwhere('customers_views.company_name', 'like', '%' . $search . '%');
                    $query->orwhere('customers_views.phone_no', 'like', '%' . $search . '%');
                }
            })
            ->select('customers_views.*')
            ->groupBy('customers_views.id')
            ->orderBy('customers_views.id', 'desc')
            ->get();
//        dd(DB::getQueryLog($records));
        $data = array();
        $i = 0;
        foreach ($records as $record) {
            $country_data = [];
            /*if ($record->currency_name_country_id)
                $country_data = Country::where("id", $record->currency_name_country_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->orderBy('id', 'DESC')->get()->first();*/
            if ($record->est_currency_id)
                $country_data = Country::where("id", $record->est_currency_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->first();
            $id = $record->id;
            $customer_type = $record->customer_type;
            $name = $record->name;
            $lead_origin = $record->lead_origin;
            $lead_category = $record->lead_category;
            $email = $record->email;
            $phone_no = $record->phone_no;
            $whatsapp_no = $record->whatsapp_no;
            $address = $record->address;
            $pincode = $record->pincode;
            $description = $record->description;
            $status = $record->status;
            $country_name = $record->country_name;
            $state_name = $record->state_name;
            $city_name = $record->city_name;
            $last_activity = $record->last_activity;
            $assign_user_name = $record->user_name;
            $net_amount = $record->net_amount;
            $estimate_status = $record->estimate_status;
            $last_activity_type = $record->last_activity_type;
            $last_activity_name = $record->last_activity_name;
            $last_internal_remarks = $record->last_internal_remarks;
            $some_day_flg = $record->some_day_flg;
            $date_added = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('d-m-Y h:i A');
            $last_activity_date = ($record->last_activity_date) ? $record->last_activity_date : null;
            $follow_up_datetime = ($record->last_follow_up_datetime) ? $record->last_follow_up_datetime : null;
            $i++;

            $results = DB::table('customer_labels')
                ->select(DB::raw("group_concat(label_id separator ',') as labelId"))
                ->where('customer_id', '=', $record->id)
                ->groupBy('customer_id')
                ->get()->toArray();
            $label_color = [];
            $selectedLabelArr = [];
            if ($results) {
                $selectedLabelArr = explode(',', $results[0]->labelId);

                $label_color = DB::table('lead_groups')
                    ->select('name', 'color_code', 'id')
                    ->whereIn('id', $selectedLabelArr)
                    ->get()->toArray();
            }
            $data[] = array(
                "sr_no" => $i,
                "id" => $id,
                "name" => $name,
                "company_name" => $record->company_name,
                "lead_origin" => $lead_origin,
                "lead_category" => $lead_category,
                "created_at" => $date_added,
                "customer_type" => $customer_type,
                "email" => $email,
                "phone_no" => $phone_no,
                "whatsapp_no" => $whatsapp_no,
                "address" => $address,
                "pincode" => $pincode,
                "country_name" => $country_name,
                "state_name" => $state_name,
                "city_name" => $city_name,
                "description" => $description,
                "last_activity" => $last_activity,
                "assign_user_name" => $assign_user_name,
//                "net_amount" => $net_amount,
                "net_amount" => $net_amount,

                "status" => $status,
                "last_activity_date" => $last_activity_date,
                "last_activity_type" => $last_activity_type,
                "last_activity_name" => $last_activity_name,
                "last_internal_remarks" => $last_internal_remarks,
                "label_color" => $label_color,
                "selectedLabelArr" => $selectedLabelArr,
                "estimate_status" => $estimate_status,
                "estimate_no" => $record->estimate_no,
                "follow_up_datetime" => $follow_up_datetime,
                "some_day_flg" => $some_day_flg,
                "country_code" => $record->country_code,
                "whatsapp_country_code" => $record->whatsapp_country_code,
                 "created_at" => ($record->created_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('Y-m-d H:i:s'):'',
                "updated_at" => ($record->updated_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->updated_at)
                ->format('Y-m-d H:i:s'):'',
                "last_is_modified" => $record->last_is_modified,
                "last_is_follow_up" => $record->last_is_follow_up,
                "currency_symbol" => (isset($country_data->currency_symbol)) ? $country_data->currency_symbol : '',
                "lead_stage_name" => $record->lead_stage_name,
                "lead_stage_color_code" => $record->lead_stage_color_code,
                "last_activity_id" => $record->last_activity_id,
                "user_id" => $record->user_id,
            );

        }
        if (is_null($data)) {
            return $this->sendError('Lead not found', ['Lead not found'], 422);
        }
        return $this->sendResponse($data, 'Lead retrieved successfully');
    }

    public function getLeadByLabel($label_id, $start, $rowperpage, $orderBy, $fil_user_id, Request $request)
    {
        if ($orderBy == 0) {
            $order_by = 'id';
            $order_by_name = 'desc';
        }

        if ($orderBy == 1) {
            $order_by = 'last_activity_date';
            $order_by_name = 'desc';
        }

        if ($orderBy == 2) {
            $order_by = 'name';
            $order_by_name = 'asc';
        }

        $leadStageId = $request->input('lead_stage_id');
        $leadLabelId = $request->input('lead_label_id');
        $leadSourceId = $request->input('lead_source_id');
        $leadCategoryId = $request->input('lead_category_id');
        $estimateStatus = $request->input('estimate_status');
        $cityName = $request->input('city_name');
        $stateId = $request->input('state_id');
        $countryId = $request->input('country_id');
        $leadStartDate = $request->input('lead_start_date');
        $leadEndDate = $request->input('lead_end_date');
        $leadStageIdArr = ($leadStageId) ? explode(",", $leadStageId) : [];
        $leadLabelIdArr = ($leadLabelId) ? explode(",", $leadLabelId) : [];
        $leadSourceIdArr = ($leadSourceId) ? explode(",", $leadSourceId) : [];
        $leadCategoryIdArr = ($leadCategoryId) ? explode(",", $leadCategoryId) : [];
        $estimateStatusArr = ($estimateStatus) ? explode(",", $estimateStatus) : [];

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.*', DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code'))
            ->where('cv.company_id', $this->company_id)
//            ->where('cv.some_day_flg', 0)
//            ->whereRaw('DATE(cv.last_follow_up_datetime) = ?', [date('Y-m-d')])
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id); CMX
                }
            })
            ->where(function ($query) use ($fil_user_id, $leadStageIdArr, $leadLabelIdArr, $cityName,$stateId,$countryId,$leadSourceIdArr,$leadCategoryIdArr,$estimateStatusArr,$leadStartDate,$leadEndDate) {
                if (!empty($leadStageIdArr) && count($leadStageIdArr) > 0) {
                    $query->WhereIn('cv.lead_stage_id', $leadStageIdArr);
                }
                if (!empty($leadSourceIdArr) && count($leadSourceIdArr) > 0) {
                    $query->WhereIn('cv.customer_lead_id', $leadSourceIdArr);
                }
                if (!empty($leadCategoryIdArr) && count($leadCategoryIdArr) > 0) {
                    $query->WhereIn('cv.customer_category_id', $leadCategoryIdArr);
                }
                /* if (!empty($leadLabelIdArr) && count($leadLabelIdArr) > 0) {
                     $query->WhereIn('lg.id', $leadLabelIdArr);
                 }*/
                if (!empty($cityName)) {
                    $query->Where('cv.city_name', $cityName);
                }
                if (!empty($stateId)) {
                    $query->Where('cv.state_id', $stateId);
                }
                if (!empty($countryId)) {
                    $query->Where('cv.country_id', $countryId);
                }
                if (!empty($estimateStatusArr) && count($estimateStatusArr) > 0) {
                    $query->WhereIn('cv.estimate_status',$estimateStatusArr);
                }
                if($leadStartDate && $leadEndDate) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(cv.created_at, '%Y-%m-%d')"), [$leadStartDate, $leadEndDate]);
                }
                if ($fil_user_id > 0) {
                    $query->where('cv.assigned_to_user', '=', $fil_user_id);
                }
            })
//            ->groupBy('cv.id')
//            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->orderBy($order_by, $order_by_name)
            ->groupBy('cv.id');
        if ($label_id) {
            $records = $records->havingRaw("FIND_IN_SET('$label_id', GROUP_CONCAT(lg.id)) > 0");
        }

        $records = $records->skip($start)
            ->take($rowperpage)
            ->get();

        $data = array();
        foreach ($records as $record) {
            $country_data = [];
            /* if ($record->currency_name_country_id)
                 $country_data = Country::where("id", $record->currency_name_country_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->orderBy('id', 'DESC')->get()->first();*/
            if ($record->est_currency_id)
                $country_data = Country::where("id", $record->est_currency_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->first();
            $results = DB::table('customer_labels')
                ->select(DB::raw("group_concat(label_id separator ',') as labelId"))
                ->where('customer_id', '=', $record->id)
                ->groupBy('customer_id')
                ->get()->toArray();
            $label_color = [];
            $selectedLabelArr = [];
            if ($results) {
                $selectedLabelArr = explode(',', $results[0]->labelId);

                $label_color = DB::table('lead_groups')
                    ->select('name', 'color_code', 'id')
                    ->whereIn('id', $selectedLabelArr)
                    ->get()->toArray();
            }

            $data[] = array(
                "id" => $record->id,
                "name" => $record->name,
                "company_name" => $record->company_name,
                "lead_origin" => $record->lead_origin,
                "lead_category" => $record->lead_category,
                "created_at" => \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('d-m-Y h:i A'),
                "customer_type" => $record->customer_type,
                "email" => $record->email,
                "phone_no" => $record->phone_no,
                "whatsapp_no" => $record->whatsapp_no,
                "address" => $record->address,
                "pincode" => $record->pincode,
                "country_name" => $record->country_name,
                "state_name" => $record->state_name,
                "city_name" => $record->city_name,
                "description" => $record->description,
                "last_activity" => $record->last_activity,
                "assign_user_name" => $record->user_name,
//                "net_amount" => $record->net_amount,
                "net_amount" => $record->net_amount,

                "status" => $record->status,
                "last_activity_date" => ($record->last_activity_date) ? $record->last_activity_date : null,
                "last_activity_type" => $record->last_activity_type,
                "last_activity_name" => $record->last_activity_name,
                "last_internal_remarks" => $record->last_internal_remarks,
                "label_color" => $label_color,
                "selectedLabelArr" => $selectedLabelArr,
                "estimate_status" => $record->estimate_status,
                "follow_up_datetime" => ($record->last_follow_up_datetime && $record->last_follow_up_datetime != '0000-00-00 00:00:00') ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->last_follow_up_datetime)->format('Y-m-d H:i:s') : null,
                 "created_at" => ($record->created_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('Y-m-d H:i:s'):'',
                "updated_at" => ($record->updated_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->updated_at)
                ->format('Y-m-d H:i:s'):'',
                "last_is_modified" => $record->last_is_modified,
                "last_is_follow_up" => $record->last_is_follow_up,
                "currency_symbol" => (isset($country_data->currency_symbol)) ? $country_data->currency_symbol : '',
                "lead_stage_name" => $record->lead_stage_name,
                "lead_stage_color_code" => $record->lead_stage_color_code,
                "last_activity_id" => $record->last_activity_id,
                "user_id" => $record->user_id,
            );

        }
        if (is_null($data)) {
            return $this->sendError('Follow up not found', ['Lead not found'], 422);
        }
        return $this->sendResponse($data, 'Follow up retrieved successfully');
    }

    public function getNewLead($label_id, $start, $rowperpage, $orderBy, $fil_user_id, Request $request)
    {
        if ($orderBy == 0) {
            $order_by = 'id';
            $order_by_name = 'desc';
        }

        if ($orderBy == 1) {
            $order_by = 'last_activity_date';
            $order_by_name = 'desc';
        }

        if ($orderBy == 2) {
            $order_by = 'name';
            $order_by_name = 'asc';
        }
        $leadStageId = $request->input('lead_stage_id');
        $leadLabelId = $request->input('lead_label_id');
        $leadSourceId = $request->input('lead_source_id');
        $leadCategoryId = $request->input('lead_category_id');
        $estimateStatus = $request->input('estimate_status');
        $cityName = $request->input('city_name');
        $stateId = $request->input('state_id');
        $countryId = $request->input('country_id');
        $leadStartDate = $request->input('lead_start_date');
        $leadEndDate = $request->input('lead_end_date');
        $leadStageIdArr = ($leadStageId) ? explode(",", $leadStageId) : [];
        $leadLabelIdArr = ($leadLabelId) ? explode(",", $leadLabelId) : [];
        $leadSourceIdArr = ($leadSourceId) ? explode(",", $leadSourceId) : [];
        $leadCategoryIdArr = ($leadCategoryId) ? explode(",", $leadCategoryId) : [];
        $estimateStatusArr = ($estimateStatus) ? explode(",", $estimateStatus) : [];

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.*', DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code'))
            ->where('cv.company_id', $this->company_id)
            ->where('cv.new_lead_flag', 1)
//            ->whereRaw('DATE(cv.last_follow_up_datetime) = ?', [date('Y-m-d')])
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id); CMX
                }
            })
            ->where(function ($query) use ($fil_user_id, $leadStageIdArr, $leadLabelIdArr, $cityName,$stateId,$countryId,$leadSourceIdArr,$leadCategoryIdArr,$estimateStatusArr,$leadStartDate,$leadEndDate) {
                if (!empty($leadStageIdArr) && count($leadStageIdArr) > 0) {
                    $query->WhereIn('cv.lead_stage_id', $leadStageIdArr);
                }
                if (!empty($leadSourceIdArr) && count($leadSourceIdArr) > 0) {
                    $query->WhereIn('cv.customer_lead_id', $leadSourceIdArr);
                }
                if (!empty($leadCategoryIdArr) && count($leadCategoryIdArr) > 0) {
                    $query->WhereIn('cv.customer_category_id', $leadCategoryIdArr);
                }
                if (!empty($leadLabelIdArr) && count($leadLabelIdArr) > 0) {
                    $query->WhereIn('lg.id', $leadLabelIdArr);
                }
                if (!empty($cityName)) {
                    $query->Where('cv.city_name', $cityName);
                }
                if (!empty($stateId)) {
                    $query->Where('cv.state_id', $stateId);
                }
                if (!empty($countryId)) {
                    $query->Where('cv.country_id', $countryId);
                }
                if (!empty($estimateStatusArr) && count($estimateStatusArr) > 0) {
                    $query->WhereIn('cv.estimate_status',$estimateStatusArr);
                }
                if($leadStartDate && $leadEndDate) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(cv.created_at, '%Y-%m-%d')"), [$leadStartDate, $leadEndDate]);
                }
                if ($fil_user_id > 0) {
                    $query->where('cv.assigned_to_user', '=', $fil_user_id);
                }
            })
//            ->groupBy('cv.id')
//            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->orderBy($order_by, $order_by_name)
            ->groupBy('cv.id');
        if ($label_id > 0) {
            $records = $records->havingRaw("FIND_IN_SET('$label_id', GROUP_CONCAT(lg.id)) > 0");
        }

        $records = $records->skip($start)
            ->take($rowperpage)
            ->get();

        $data = array();
        foreach ($records as $record) {
            $country_data = [];
            /* if ($record->currency_name_country_id)
                 $country_data = Country::where("id", $record->currency_name_country_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->orderBy('id', 'DESC')->get()->first();*/
            if ($record->est_currency_id)
                $country_data = Country::where("id", $record->est_currency_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->first();
            $results = DB::table('customer_labels')
                ->select(DB::raw("group_concat(label_id separator ',') as labelId"))
                ->where('customer_id', '=', $record->id)
                ->groupBy('customer_id')
                ->get()->toArray();
            $label_color = [];
            $selectedLabelArr = [];
            if ($results) {
                $selectedLabelArr = explode(',', $results[0]->labelId);

                $label_color = DB::table('lead_groups')
                    ->select('name', 'color_code', 'id')
                    ->whereIn('id', $selectedLabelArr)
                    ->get()->toArray();
            }

            $data[] = array(
                "id" => $record->id,
                "name" => $record->name,
                "company_name" => $record->company_name,
                "lead_origin" => $record->lead_origin,
                "lead_category" => $record->lead_category,
                "created_at" => \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('d-m-Y h:i A'),
                "customer_type" => $record->customer_type,
                "email" => $record->email,
                "phone_no" => $record->phone_no,
                "whatsapp_no" => $record->whatsapp_no,
                "address" => $record->address,
                "pincode" => $record->pincode,
                "country_name" => $record->country_name,
                "state_name" => $record->state_name,
                "city_name" => $record->city_name,
                "description" => $record->description,
                "last_activity" => $record->last_activity,
                "assign_user_name" => $record->user_name,
//                "net_amount" => $record->net_amount,
                "net_amount" => $record->net_amount,
                "status" => $record->status,
                "last_activity_date" => ($record->last_activity_date) ? $record->last_activity_date : null,
                "last_activity_type" => $record->last_activity_type,
                "last_activity_name" => $record->last_activity_name,
                "last_internal_remarks" => $record->last_internal_remarks,
                "label_color" => $label_color,
                "selectedLabelArr" => $selectedLabelArr,
                "new_lead_flag" => $record->new_lead_flag,
                "estimate_status" => $record->estimate_status,
                "follow_up_datetime" => ($record->last_follow_up_datetime && $record->last_follow_up_datetime != '0000-00-00 00:00:00') ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->last_follow_up_datetime)->format('Y-m-d H:i:s') : null,
                 "created_at" => ($record->created_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('Y-m-d H:i:s'):'',
                "updated_at" => ($record->updated_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->updated_at)
                ->format('Y-m-d H:i:s'):'',
                "last_is_modified" => $record->last_is_modified,
                "last_is_follow_up" => $record->last_is_follow_up,
                "currency_symbol" => (isset($country_data->currency_symbol)) ? $country_data->currency_symbol : '',
                "lead_stage_name" => $record->lead_stage_name,
                "lead_stage_color_code" => $record->lead_stage_color_code,
                "last_activity_id" => $record->last_activity_id,
                "user_id" => $record->user_id,
            );

        }
        if (is_null($data)) {
            return $this->sendError('New lead not found', ['Lead not found'], 422);
        }
        return $this->sendResponse($data, 'New lead retrieved successfully');
    }

    public function getNewLeadCount($label_id, $fil_user_id, Request $request)
    {
        $leadStageId = $request->input('lead_stage_id');
        $leadLabelId = $request->input('lead_label_id');
        $leadSourceId = $request->input('lead_source_id');
        $leadCategoryId = $request->input('lead_category_id');
        $estimateStatus = $request->input('estimate_status');
        $cityName = $request->input('city_name');
        $stateId = $request->input('state_id');
        $countryId = $request->input('country_id');
        $leadStartDate = $request->input('lead_start_date');
        $leadEndDate = $request->input('lead_end_date');
        $leadStageIdArr = ($leadStageId) ? explode(",", $leadStageId) : [];
        $leadLabelIdArr = ($leadLabelId) ? explode(",", $leadLabelId) : [];
        $leadSourceIdArr = ($leadSourceId) ? explode(",", $leadSourceId) : [];
        $leadCategoryIdArr = ($leadCategoryId) ? explode(",", $leadCategoryId) : [];
        $estimateStatusArr = ($estimateStatus) ? explode(",", $estimateStatus) : [];

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.*', DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code'))
            ->where('cv.company_id', $this->company_id)
            ->where('cv.new_lead_flag', 1)
//            ->whereRaw('DATE(cv.last_follow_up_datetime) = ?', [date('Y-m-d')])
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id); CMX
                }
            })
            ->where(function ($query) use ($fil_user_id, $leadStageIdArr, $leadLabelIdArr, $cityName,$stateId,$countryId,$leadSourceIdArr,$leadCategoryIdArr,$estimateStatusArr,$leadStartDate,$leadEndDate) {
                if (!empty($leadStageIdArr) && count($leadStageIdArr) > 0) {
                    $query->WhereIn('cv.lead_stage_id', $leadStageIdArr);
                }
                if (!empty($leadSourceIdArr) && count($leadSourceIdArr) > 0) {
                    $query->WhereIn('cv.customer_lead_id', $leadSourceIdArr);
                }
                if (!empty($leadCategoryIdArr) && count($leadCategoryIdArr) > 0) {
                    $query->WhereIn('cv.customer_category_id', $leadCategoryIdArr);
                }
                /*if (!empty($leadLabelIdArr) && count($leadLabelIdArr) > 0) {
                    $query->WhereIn('lg.id', $leadLabelIdArr);
                }*/
                if (!empty($cityName)) {
                    $query->Where('cv.city_name', $cityName);
                }
                if (!empty($stateId)) {
                    $query->Where('cv.state_id', $stateId);
                }
                if (!empty($countryId)) {
                    $query->Where('cv.country_id', $countryId);
                }
                if (!empty($estimateStatusArr) && count($estimateStatusArr) > 0) {
                    $query->WhereIn('cv.estimate_status',$estimateStatusArr);
                }
                if($leadStartDate && $leadEndDate) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(cv.created_at, '%Y-%m-%d')"), [$leadStartDate, $leadEndDate]);
                }
                if ($fil_user_id > 0) {
                    $query->where('cv.assigned_to_user', '=', $fil_user_id);
                }
            })
            ->groupBy('cv.id');
        if (!empty($leadLabelIdArr) && count($leadLabelIdArr) > 0) {
            $query->WhereIn('lg.id', $leadLabelIdArr);
        }
        /* if ($label_id > 0) {
             $records = $records->havingRaw("FIND_IN_SET('$label_id', GROUP_CONCAT(lg.id)) > 0");
         }*/
        $data = array();
        $data[] = $records->get()->count();

//        $data = array();
        if (is_null($data)) {
            return $this->sendError('New lead not found', ['Lead not found'], 422);
        }
        return $this->sendResponse($data, 'New lead count successfully');
    }

    public function markAsUnreadLead(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }

        $customers = Customer::find($input['id'])->update(["new_lead_flag" => 1]);
        return $this->sendResponse(['success' => 'Mark as unread lead!'], 'Mark as unread lead!');

    }

    public function getNewLeadExport()
    {
        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.*', DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code'))
            ->where('cv.company_id', $this->company_id)
            ->where('cv.new_lead_flag', 1)
            ->groupBy('cv.id')
            ->get();

        $data = array();
        foreach ($records as $record) {
            $country_data = [];
            /*if ($record->currency_name_country_id)
                $country_data = Country::where("id", $record->currency_name_country_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->orderBy('id', 'DESC')->get()->first();*/
            if ($record->est_currency_id)
                $country_data = Country::where("id", $record->est_currency_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->first();
            $results = DB::table('customer_labels')
                ->select(DB::raw("group_concat(label_id separator ',') as labelId"))
                ->where('customer_id', '=', $record->id)
                ->groupBy('customer_id')
                ->get()->toArray();
            $label_color = [];
            $selectedLabelArr = [];
            if ($results) {
                $selectedLabelArr = explode(',', $results[0]->labelId);

                $label_color = DB::table('lead_groups')
                    ->select('name', 'color_code', 'id')
                    ->whereIn('id', $selectedLabelArr)
                    ->get()->toArray();
            }

            $data[] = array(
                "id" => $record->id,
                "name" => $record->name,
                "lead_origin" => $record->lead_origin,
                "lead_category" => $record->lead_category,
                "created_at" => \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('d-m-Y h:i A'),
                "customer_type" => $record->customer_type,
                "email" => $record->email,
                "phone_no" => $record->phone_no,
                "whatsapp_no" => $record->whatsapp_no,
                "address" => $record->address,
                "pincode" => $record->pincode,
                "country_name" => $record->country_name,
                "state_name" => $record->state_name,
                "city_name" => $record->city_name,
                "description" => $record->description,
                "last_activity" => $record->last_activity,
                "assign_user_name" => $record->user_name,
//                "net_amount" => $record->net_amount,
                "net_amount" => $record->net_amount,

                "status" => $record->status,
                "last_activity_date" => ($record->last_activity_date) ? $record->last_activity_date : null,
                "last_activity_type" => $record->last_activity_type,
                "last_activity_name" => $record->last_activity_name,
                "last_internal_remarks" => $record->last_internal_remarks,
                "label_color" => $label_color,
                "selectedLabelArr" => $selectedLabelArr,
                "new_lead_flag" => $record->new_lead_flag,
                "estimate_status" => $record->estimate_status,
                "follow_up_datetime" => ($record->last_follow_up_datetime && $record->last_follow_up_datetime != '0000-00-00 00:00:00') ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->last_follow_up_datetime)->format('Y-m-d H:i:s') : '',
                 "created_at" => ($record->created_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('Y-m-d H:i:s'):'',
                "updated_at" => ($record->updated_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->updated_at)
                ->format('Y-m-d H:i:s'):'',
                "last_is_modified" => $record->last_is_modified,
                "last_is_follow_up" => $record->last_is_follow_up,
                "currency_symbol" => (isset($country_data->currency_symbol)) ? $country_data->currency_symbol : '',
                "user_id" => $record->user_id,
            );

        }
        if (is_null($data)) {
            return $this->sendError('New lead not found', ['Lead not found'], 422);
        }
        return $this->sendResponse($data, 'New lead retrieved successfully');
    }

    public function teamUserList()
    {
        /*$leads = User::select(["name", "id", "email", "mobile_no"])
            ->where('status', 'Approved')
            ->where('company_id', $this->company_id)
            ->orWhere('id', $this->logged_user->id)
            ->orWhere('id', $this->company_id)
            ->get();*/
//        $leads = DB::table('users')
//            ->leftJoin('customers', function ($join) {
//                $join->on('users.id', '=', 'customers.assigned_to_user')
//                    ->orOn('users.id', '=', 'customers.user_id')
//                    ->where(function ($query) {
//                        $query->where('users.company_id', $this->company_id)
//                            ->orWhere('users.id', $this->company_id);
//                    });
//            })
////            ->leftJoin('customers', 'users.id', '=', 'customers.assigned_to_user')
//            ->select("users.name", "users.id", "users.email", "users.mobile_no", DB::raw('count(customers.id) as lead_count'), "users.company_id", "users.mobile_device_key as mobile_device_key", "users.device_key as web_device_key", "users.profile_icon")
////            ->where('users.status', 'Approved')
//            ->where('users.invite_status', 1)
////            ->where('users.company_id', $this->company_id)
//            ->where(function ($query) {
//                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_param) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_param)) {
//                    $query->where('customers.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('customers.user_id', '=', $this->logged_user->id);
////                    $query->orwhere('users.id', '=', $this->company_id);
//                }
//
//                if (in_array('access-all-lead-and-assign-to-anyone-in-team', $this->user_param)) {
//                    if (in_array('give-access-to-attend-unassigned-leads', $this->user_param)) {
//
//                        $query->where('customers.company_id', $this->company_id);
//                        $query->orwhere('customers.assigned_to_user', '=', 0);
//                    } else {
//                        $query->where('customers.assigned_to_user', '!=', 0);
//                    }
//                } else {
//                    if (in_array('give-access-to-attend-unassigned-leads', $this->user_param)) {
//                        $query->orwhere('customers.assigned_to_user', '=', 0);
//                    }
//                }
//            })
//            /*->where(function ($query) {
//                $query->orwhere('users.company_id', $this->company_id);
//                $query->orwhere('users.id', $this->company_id);
//            })*/
//            ->groupBy('users.id')
//            ->get();
        $leads = DB::table('users')
            ->leftJoin('customers', function ($join) {
                $join->on('users.id', '=', 'customers.assigned_to_user')
//                    ->orOn('users.id', '=', 'customers.user_id')
                    ->where(function ($query) {
                        $query->where('users.company_id', $this->company_id)
                            ->orWhere('users.id', $this->company_id);
                    });
            })
            ->select(
                "users.name",
                "users.id",
                "users.email",
                "users.mobile_no",
                DB::raw('count(customers.id) as lead_count'),
                "users.company_id",
                "users.mobile_device_key as mobile_device_key",
                "users.device_key as web_device_key",
                "users.profile_icon"
            )
            ->where('users.invite_status', 1)
            ->where(function ($query) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_param) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_param)) {
                    $query->where(function ($subQuery) {
                        $subQuery->where('customers.assigned_to_user', '=', $this->logged_user->id);
//                            ->orWhere('customers.user_id', '=', $this->logged_user->id);
                    });
                }

                if (in_array('access-all-lead-and-assign-to-anyone-in-team', $this->user_param)) {
                    $query->where(function ($subQuery) {
                        if (in_array('give-access-to-attend-unassigned-leads', $this->user_param)) {
                            $subQuery->where('customers.company_id', $this->company_id)
                                ->orWhere('customers.assigned_to_user', '=', 0);
                        } else {
                            $subQuery->where('customers.assigned_to_user', '!=', 0);
                        }
                    });
                } else {
                    if (in_array('give-access-to-attend-unassigned-leads', $this->user_param)) {
                        $query->orWhere('customers.assigned_to_user', '=', 0);
                    }
                }
            })
            ->groupBy('users.id')
            ->get();

        $userId = '';
        foreach ($leads as $lead) {
            $userId .= $lead->id . ',';
//            $lead->profile_icon = ($lead->profile_icon) ? Storage::url($lead->profile_icon) : null;
            $lead->profile_icon = ($lead->profile_icon) ? Storage::disk('s3')->temporaryUrl(trim($lead->profile_icon), Carbon::now()->addMinutes(20)) : null;
        }

        $userIdArr = explode(",", trim($userId, ','));
        $leads1 = DB::table('users')
            ->leftJoin('customers', 'users.id', '=', 'customers.assigned_to_user')
            ->where(function ($query) {
                $query->where('users.company_id', $this->company_id)
                    ->orWhere('users.id', $this->company_id);
            })
            ->whereNotIn('users.id', $userIdArr)
            ->where('users.invite_status', 1)
            ->groupBy('users.id')
            ->select("users.name", "users.id", "users.email", "users.mobile_no", DB::raw('count(customers.id) as lead_count'), "users.company_id", "users.mobile_device_key as mobile_device_key", "users.device_key as web_device_key", "users.profile_icon")
            ->get();

        foreach ($leads1 as $lead1) {
            $lead1->lead_count = 0;
            $lead1->profile_icon = ($lead1->profile_icon) ? Storage::disk('s3')->temporaryUrl(trim($lead1->profile_icon), Carbon::now()->addMinutes(20)) : null;
        }

        $merged = $leads->merge($leads1);

        $data = $merged->all();
        if (is_null($data)) {
            return $this->sendError('User not found', ['Lead not found'], 422);
        }
        return $this->sendResponse($data, 'User retrieved successfully');
    }

    public function MultipleLeadAssignedToUser(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'id' => 'required',
            'assigned_to_user' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()], 400);
        }

        $id = [];
        $i = 0;
        foreach (explode(",", $input['id']) as $value) {
            $id = $value;

            $user = User::where('id', '=', $input['assigned_to_user'])->select(["name", "device_key", "mobile_device_key"])->first();
            Customer::find($id)->update(["assigned_to_user" => $input['assigned_to_user'], "new_lead_flag" => 1]);
            $customerData = Customer::where('id', '=', $id)->select(["name"])->first();
            $customerTimelineData = EstimateTimeline::where('customer_id', '=', $id)->select(["follow_up_datetime"])->orderBy('id', 'desc')->first();

            $logInput['follow_up_datetime'] = ($customerTimelineData) ? $customerTimelineData['follow_up_datetime'] : '0000-00-00 00:00:00';
            $logInput['assigned_to'] = $input['assigned_to_user'];
            $logInput['customer_id'] = $id;
            $logInput['entry_type'] = "assigned";
            $logInput['activity_type'] = 8;
            $logInput['internal_remarks'] = "Assigned to " . $user->name;
            $logInput['user_id'] = $this->logged_user->id;
            $logInput['company_id'] = $this->company_id;
            $logInput['created_by'] = $this->logged_user->id;
            $logInput['updated_by'] = $this->logged_user->id;
            LogActivity::addToActivityLog($logInput);
            SalesPersonPerformances::where([['customer_id', "=", $id], ['completed_task', '=', 0]])->update(array('user_id' => $input['assigned_to_user'], 'created_at' => date('Y-m-d H:i:s')));


            $noficationArr['customer_id'] = $id;
            $noficationArr['notification_type'] = "assign_to_you";
            $noficationArr['device_key'] = $user->device_key;
            $noficationArr['mobile_device_key'] = $user->mobile_device_key;
//                $noficationArr['mobile_device_key'] = null;
            $noficationArr['title'] = 'New Lead Assigned To You';
            $noficationArr['body'] = $customerData->name . ' is assigned to you by ' . $this->logged_user->name;
            $this->sendAssigntoUserNotification($noficationArr);
        }
        return $this->sendResponse(['success' => 'Lead has been successfully assigned!'], 'Lead has been successfully assigned!');

    }

    public function sendAssigntoUserNotification($requestArr = [])
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        /*$users = User::whereNotNull('device_key')->where('id',1)->select('device_key','mobile_device_key', 'id', 'company_id')->get();*/
        $serverKey = 'AAAAsImurqQ:APA91bEqYpdInZ9unkqrVIuF_GTFJHSbWY3T611Kj8qXe_amTYZB4AWrAOBIwPnUGyyqndFH4wQn7DczaUZYzEDTizHL0-cB_CSEHFuvJuQGG6ZKa-deTDIZnogh1CWMopqGHEGXOuQX';

        $FcmToken = array();
        if ($requestArr['device_key']) {
            $FcmToken[] = $requestArr['device_key'];
        }

        if ($requestArr['mobile_device_key']) {
            $FcmToken[] = $requestArr['mobile_device_key'];
        }
//dd($FcmToken);
        $data = [
            "registration_ids" => $FcmToken,
            "notification" => [
                "title" => $requestArr['title'],
                "body" => $requestArr['body'],
                "sound" => 'notification_sound.wav',
                "icon" => url('assets/images/logo.png'),
//                "click_action" => url('/lead/timeline/' . $requestArr['customer_id'])
//                "click_action" => $requestArr['customer_id']
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ],
            "data" => [
                "type" => $requestArr['notification_type'],
                "lead_id" => $requestArr['customer_id']
            ],
            "priority" => 'high'
        ];
        $encodedData = json_encode($data);


        $headers = [
            'Authorization:key=' . $serverKey,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        // Close connection
        curl_close($ch);

        // FCM response
//        dd($result);
    }

    public function multipleLabelToCustomers(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'lead_id' => 'required',
            'label_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }
        $leadIdArr = explode(',', $input['label_id']);
        $customerIdArr = explode(',', $input['lead_id']);

        foreach ($customerIdArr as $customerId) {
            foreach ($leadIdArr as $labelId) {

                $existingRecord = CustomerLabel::where('customer_id', $customerId)
                    ->where('label_id', $labelId)
                    ->first();
                if (!$existingRecord) {
                    CustomerLabel::firstOrCreate(
                        ['customer_id' => $customerId, 'label_id' => $labelId, 'user_id' => $this->logged_user->id, 'company_id' => $this->company_id],
                        ['customer_id' => $customerId, 'label_id' => $labelId]
                    );
                }
            }
//            Customer::find($customerId)->update(["new_lead_flag" => 0]);
        }
        return $this->sendResponse([], 'Customer label updated!');
    }

    public function activityMultipleFollowupSave(Request $request)
    {
        $inputArr = $request->all();
        $validator = Validator::make($inputArr, [
            'lead_follow_up' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }

        $input['user_id'] = $this->logged_user->id;
        $input['company_id'] = $this->company_id;
        $leadFollowUp = $inputArr['lead_follow_up'];
        foreach ($leadFollowUp as $leadData) {
            $customer_id = $leadData['customer_id'];

            $follow_up_datetime = $leadData['follow_up_datetime'];

            if ($leadData['estimate_id'] > 0) {
                $paramArr['estimate_id'] = $leadData['estimate_id'];
            }
            $customerData = Customer::select("assigned_to_user")->where('id', '=', $customer_id)->first();
            $paramArr['follow_up_datetime'] = (!empty($follow_up_datetime) && $follow_up_datetime != '0000-00-00 00:00:00') ? Carbon::createFromFormat('d-m-Y H:i A', $follow_up_datetime)->format('Y-m-d H:i:s') : '';
            $paramArr['assigned_to'] = $customerData->assigned_to_user;
            $paramArr['customer_id'] = $customer_id;
            $paramArr['activity_type'] = 9;
            $paramArr['activity_name'] = 'Follow Up';
            $paramArr['entry_type'] = 'followup';
            $paramArr['is_modified'] = 1;
            $paramArr['is_follow_up'] = 0;
            $paramArr['user_id'] = $this->logged_user->id;
            $paramArr['company_id'] = $this->company_id;
            $paramArr['created_by'] = $this->logged_user->id;
            $paramArr['updated_by'] = $this->logged_user->id;
            if (isset($inputArr['notes'])){
                $paramArr['activity_notes'] = $inputArr['notes'];
                $paramArr['internal_remarks'] =  " <b>Notes</b> : " . $inputArr['notes'];
            }

            $paramArr['follow_up_datetime'] = (!empty($follow_up_datetime) && $follow_up_datetime != '0000-00-00 00:00:00') ? Carbon::createFromFormat('d-m-Y H:i A', $follow_up_datetime)->format('Y-m-d H:i:s') : '';
            $paramArr['user_id'] = $this->logged_user->id;
            $paramArr['company_id'] = $this->company_id;
            $paramArr['created_by'] = $this->logged_user->id;
            $paramArr['updated_by'] = $this->logged_user->id;

            if ($paramArr['follow_up_datetime']) {
                $customer_data = EstimateTimeline::select("id")
                    ->where('customer_id', '=', $customer_id)
                    ->wherein('activity_type', [9])   //[1, 2, 3, 9]
                    ->orderBy('id', 'DESC')
                    ->take(1)
                    ->get()
                    ->toArray();

                if ($customer_data && $customer_data[0]['id']) {
                    $a = SalesPersonPerformances::where('timeline_id', $customer_data[0]['id'])->update(array('completed_task' => 1));
                }
            }

            $customer = EstimateTimeline::create($paramArr);
            Customer::where('id', $customer_id)->update(array('some_day_flg' => 0));

            if ($paramArr['follow_up_datetime']) {
                $customer_data = EstimateTimeline::select("id")
                    ->where('customer_id', '=', $customer_id)
                    ->wherein('activity_type', [9])  //[1, 2, 3, 9]
                    ->orderBy('id', 'DESC')
                    ->get()
                    ->toArray();

                if ($customer_data && $customer_data[0]['id']) {
                    $insArr['timeline_id'] = $customer_data[0]['id'];
                    $insArr['customer_id'] = $customer_id;
                    $insArr['user_id'] = $this->logged_user->id;
                    $insArr['company_id'] = $this->company_id;
                    $insArr['performance_date'] = (!empty($follow_up_datetime) && $follow_up_datetime != '0000-00-00 00:00:00') ? Carbon::createFromFormat('d-m-Y H:i A', $follow_up_datetime)->format('Y-m-d') : date('Y-m-d');
                    $insArr['total_task'] = 1;
                    SalesPersonPerformances::create($insArr);
                }
            }
//                Customer::find($customer_id)->update(["new_lead_flag" => 0]);
        }
        return $this->sendResponse([], 'Successfully Saved!');

    }

    public function getLeadStages()
    {
        $leadStages = LeadStage::select("*")
            ->where('status', '=', 0)
            ->where(function ($query) {
                $query->where('company_id', '=', $this->company_id);
            })
            ->orderBy('priority', 'asc')
            ->get();

        if (is_null($leadStages)) {
            return $this->sendError('Lead stage not found', ['Lead stage not found'], 422);
        }
        return $this->sendResponse($leadStages, 'Lead stage retrieved successfully');
    }

    public function getLostReasons()
    {
        $leadStages = LostReason::select("*")
            ->where('status', '=', 0)
            ->where(function ($query) {
                $query->where('company_id', '=', $this->company_id);
            })
            ->orderBy('name', 'asc')
            ->get();

        if (is_null($leadStages)) {
            return $this->sendError('Lead stage not found', ['Lead stage not found'], 422);
        }
        return $this->sendResponse($leadStages, 'Lead stage retrieved successfully');
    }

    public function updateLeadStage(Request $request)
    {
        $input = $request->all();

        $input['id'] = $input['lead_id'];
        $validator = Validator::make($input, [
            'lead_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }
        $lost_reason_id = ($input['lead_stage_data_id'] == 6) ? $input['lost_reason_id'] : 0;
        $lost_reason_others = ($input['lead_stage_data_id'] == 6) ? $input['lost_reason_others'] : '';
        $customers = Customer::where('id', $input['id'])->update(["lead_stage_id" => $input['lead_stage_id'], "lost_reason_id" => $lost_reason_id, "others_reason" => $lost_reason_others]);
        /*$tmp_customer_data = Customer::select("lead_stage_id")
            ->where('id', '=', $input['id'])
            ->first();*/
        if ($input['lead_stage_data_id'] == 6) { // && $tmp_customer_data->lead_stage_id != $input['lead_stage_id']

            $customer_data = EstimateTimeline::select("id")
                ->where('customer_id', '=', $input['id'])
                ->wherein('activity_type', [9])  ////[1, 2, 3, 9]
                ->orderBy('id', 'DESC')
                ->take(1)
                ->get()
                ->toArray();

            if ($customer_data && $customer_data[0]['id']) {
                $a = SalesPersonPerformances::where('timeline_id', $customer_data[0]['id'])->update(array('completed_task' => 1));
            }

            $logInput['follow_up_datetime'] = '0000-00-00 00:00:00';
            $logInput['assigned_to'] = $this->logged_user->id;
            $logInput['customer_id'] = $input['id'];
            $logInput['entry_type'] = "Lead Lost";
            $logInput['activity_name'] = "Lead Lost";
            $logInput['activity_type'] = "Lead Lost";
            $logInput['activity_type'] = 17;
            $logInput['internal_remarks'] = ($lost_reason_others) ? $lost_reason_others : $input['lost_reason_name'];
            $logInput['user_id'] = $this->logged_user->id;
            $logInput['company_id'] = $this->company_id;
            $logInput['created_by'] = $this->logged_user->id;
            $logInput['updated_by'] = $this->logged_user->id;
            LogActivity::addToActivityLog($logInput);

        }

        if($input['lead_stage_data_id']==2){ // && $tmp_customer_data->lead_stage_id != $input['lead_stage_id']

            /*$customer_data = EstimateTimeline::select("id")
                ->where('customer_id', '=',  $input['id'])
                ->wherein('activity_type', [1, 2, 3, 9])
                ->orderBy('id', 'DESC')
                ->take(1)
                ->get()
                ->toArray();*/

            /*$customer_data = EstimateTimeline::select("id")
                ->where('customer_id', '=',  $input['id'])
                ->wherein('activity_type', [9])  //[1, 2, 3, 9]
                ->orderBy('id', 'DESC')
                ->take(1)
                ->get()
                ->toArray();

            if ($customer_data && $customer_data[0]['id']) {
                $a = SalesPersonPerformances::where('timeline_id', $customer_data[0]['id'])->update(array('completed_task' => 1));
            }*/

            $customer_data_tmp = EstimateTimeline::where('customer_id', $input['id'])->select(['follow_up_datetime'])
                ->latest('id')
                ->first();
            $logInput['follow_up_datetime'] = ($customer_data_tmp)?$customer_data_tmp->follow_up_datetime:'0000-00-00 00:00:00';
            //$logInput['follow_up_datetime'] = '0000-00-00 00:00:00';
            $logInput['assigned_to'] = $this->logged_user->id;
            $logInput['customer_id'] = $input['id'];
            $logInput['entry_type'] = "Lead Won";
            $logInput['activity_name'] = "Lead Won";
            $logInput['activity_type'] = "Lead Won";
            $logInput['activity_type'] = 18;
            $logInput['internal_remarks'] = '';
            $logInput['user_id'] = $this->logged_user->id;
            $logInput['company_id'] = $this->company_id;
            $logInput['created_by'] = $this->logged_user->id;
            $logInput['updated_by'] = $this->logged_user->id;
            LogActivity::addToActivityLog($logInput);

        }
        return $this->sendResponse([], 'Lead stage updated!');
    }

    public function updateMultipleLeadStage(Request $request)
    {
        $input = $request->all();

        $leadIds = explode(',', $input['lead_id']);
        $validator = Validator::make($input, [
            'lead_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }
        $lost_reason_id = ($input['lead_stage_data_id'] == 6) ? $input['lost_reason_id'] : 0;
        $lost_reason_others = ($input['lead_stage_data_id'] == 6) ? $input['lost_reason_others'] : '';
        foreach ($leadIds as $leadId) {
            $customers = Customer::where('id', $leadId)->update(["lead_stage_id" => $input['lead_stage_id'], "lost_reason_id" => $lost_reason_id, "others_reason" => $lost_reason_others]);

            if ($input['lead_stage_data_id'] == 6) {
                $customer_data = EstimateTimeline::select("id")
                    ->where('customer_id', '=', $leadId)
                    ->wherein('activity_type', [9])
                    ->orderBy('id', 'DESC')
                    ->take(1)
                    ->get()
                    ->toArray();

                if ($customer_data && $customer_data[0]['id']) {
                    $a = SalesPersonPerformances::where('timeline_id', $customer_data[0]['id'])->update(array('completed_task' => 1));
                }

                $logInput['follow_up_datetime'] = '0000-00-00 00:00:00';
                $logInput['assigned_to'] = $this->logged_user->id;
                $logInput['customer_id'] = $leadId;
                $logInput['entry_type'] = "Lead Lost";
                $logInput['activity_name'] = "Lead Lost";
                $logInput['activity_type'] = "Lead Lost";
                $logInput['activity_type'] = 17;
                $logInput['internal_remarks'] = ($lost_reason_others) ? $lost_reason_others : $input['lost_reason_name'];
                $logInput['user_id'] = $this->logged_user->id;
                $logInput['company_id'] = $this->company_id;
                $logInput['created_by'] = $this->logged_user->id;
                $logInput['updated_by'] = $this->logged_user->id;
                LogActivity::addToActivityLog($logInput);
            }

            if ($input['lead_stage_data_id'] == 2) {

                $customer_data_tmp = EstimateTimeline::where('customer_id', $leadId)->select(['follow_up_datetime'])
                    ->latest('id')
                    ->first();
                $logInput['follow_up_datetime'] = ($customer_data_tmp) ? $customer_data_tmp->follow_up_datetime : '0000-00-00 00:00:00';
                $logInput['assigned_to'] = $this->logged_user->id;
                $logInput['customer_id'] = $leadId;
                $logInput['entry_type'] = "Lead Won";
                $logInput['activity_name'] = "Lead Won";
                $logInput['activity_type'] = "Lead Won";
                $logInput['activity_type'] = 18;
                $logInput['internal_remarks'] = '';
                $logInput['user_id'] = $this->logged_user->id;
                $logInput['company_id'] = $this->company_id;
                $logInput['created_by'] = $this->logged_user->id;
                $logInput['updated_by'] = $this->logged_user->id;
                LogActivity::addToActivityLog($logInput);

            }
        }
        return $this->sendResponse([], 'Lead stage updated!');
    }

    public function postLeadExclusive(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'mobile_no' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 200);
        }

        /*$input['mobile_no'] = $input['mobile_no'];
        $input['user_id'] = $input['user_id'];
        $input['company_id'] = $input['company_id'];*/

        if (LeadExclusive::where('mobile_no', '=', $input['mobile_no'])->where('company_id', $input['company_id'])->first()) {
            return $this->sendError('Mobile no already exist!', ['Mobile no already exist!'], 200);
        }

        $customer = LeadExclusive::create($input);
        return $this->sendResponse([], ' Successfully Saved!');
    }


    public function getLeadExclusive($user_id, $search = null)
    {
        $items = LeadExclusive::select('lead_exclusives.id', 'lead_exclusives.mobile_no', 'lead_exclusives.name as contact_name', 'lead_exclusives.user_id', 'lead_exclusives.company_id', 'users.name', 'users.company_name')
            ->join('users', 'lead_exclusives.company_id', '=', 'users.id')
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->where('lead_exclusives.mobile_no', '=', $search);
                }
            })
            ->where('lead_exclusives.user_id', '=', $user_id)
            ->get();
        //if ($items->count() > 0)
        return $this->sendResponse($items, 'exclusive retrieved successfully');
        /*
                $items = Customer::select('customers.phone_no as mobile_no', 'customers.name as contact_name', 'customers.user_id', 'customers.company_id', 'users.name', 'users.company_name')
                    ->join('users', 'customers.company_id', '=', 'users.id')
                    ->where(function ($query) use ($search) {
                        if ($search) {
                            $query->where('customers.phone_no', '=', $search);
                        }
                    })
                    ->where('customers.user_id', '=', $user_id)
                    ->get();

                if ($items->count() > 0)
                    return $this->sendResponse($items, 'exclusive retrieved successfully');

                return $this->sendResponse([], 'exclusive retrieved successfully', 200);*/

    }

    public function removeLeadExclusive(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
//            'mobile_no' => 'required',
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 200);
        }

        $item = LeadExclusive::where('id', $input['id'])->delete();

        return $this->sendResponse([], 'Successfully Removed!');

    }

    public function getLeadByUserCount($user_id, Request $request)
    {
        $leadStageId = $request->input('lead_stage_id');
        $leadLabelId = $request->input('lead_label_id');
        $leadSourceId = $request->input('lead_source_id');
        $leadCategoryId = $request->input('lead_category_id');
        $estimateStatus = $request->input('estimate_status');
        $cityName = $request->input('city_name');
        $stateId = $request->input('state_id');
        $countryId = $request->input('country_id');
        $leadStartDate = $request->input('lead_start_date');
        $leadEndDate = $request->input('lead_end_date');
        $leadStageIdArr = ($leadStageId) ? explode(",", $leadStageId) : [];
        $leadLabelIdArr = ($leadLabelId) ? explode(",", $leadLabelId) : [];
        $leadSourceIdArr = ($leadSourceId) ? explode(",", $leadSourceId) : [];
        $leadCategoryIdArr = ($leadCategoryId) ? explode(",", $leadCategoryId) : [];
        $estimateStatusArr = ($estimateStatus) ? explode(",", $estimateStatus) : [];

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
        $records = ViewCustomerData::
        leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'customers_views.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->where(function ($query) use ($user_perm) {
//                if (!in_array('give-access-to-attend-unassigned-leads', $user_perm)) {
                $query->where('customers_views.company_id', $this->company_id);
//                }
            })
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('customers_views.user_id', '=', $this->logged_user->id); CMX
                }

                if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm)) {
                    if (in_array('give-access-to-attend-unassigned-leads', $user_perm)) {

                        $query->where('customers_views.company_id', $this->company_id);
                        $query->orwhere('customers_views.assigned_to_user', '=', 0);
                    } else {
                        $query->where('customers_views.assigned_to_user', '!=', 0);
                    }
                } else {
                    if (in_array('give-access-to-attend-unassigned-leads', $user_perm)) {
                        $query->orwhere('customers_views.assigned_to_user', '=', 0);
                    }
                }
            })
            ->where(function ($query) use ($user_id, $leadStageIdArr, $leadLabelIdArr, $cityName,$stateId,$countryId,$leadSourceIdArr,$leadCategoryIdArr,$estimateStatusArr,$leadStartDate,$leadEndDate) {
                if (!empty($leadStageIdArr) && count($leadStageIdArr) > 0) {
                    $query->WhereIn('customers_views.lead_stage_id', $leadStageIdArr);
                }
                if (!empty($leadSourceIdArr) && count($leadSourceIdArr) > 0) {
                    $query->WhereIn('customers_views.customer_lead_id', $leadSourceIdArr);
                }
                if (!empty($leadCategoryIdArr) && count($leadCategoryIdArr) > 0) {
                    $query->WhereIn('customers_views.customer_category_id', $leadCategoryIdArr);
                }
                if (!empty($leadLabelIdArr) && count($leadLabelIdArr) > 0) {
                    $query->WhereIn('lg.id', $leadLabelIdArr);
                }
                if (!empty($cityName)) {
                    $query->Where('customers_views.city_name', $cityName);
                }
                if (!empty($stateId)) {
                    $query->Where('customers_views.state_id', $stateId);
                }
                if (!empty($countryId)) {
                    $query->Where('customers_views.country_id', $countryId);
                }
                if (!empty($estimateStatusArr) && count($estimateStatusArr) > 0) {
                    $query->WhereIn('customers_views.estimate_status',$estimateStatusArr);
                }
                if($leadStartDate && $leadEndDate) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(customers_views.created_at, '%Y-%m-%d')"), [$leadStartDate, $leadEndDate]);
                }
                $query->where(function ($query) use ($user_id) {
                    $query->where('customers_views.assigned_to_user', '=', $user_id);
                    $query->orwhere('customers_views.user_id', '=', $user_id);
                });
                //$query->where('customers_views.assigned_to_user', '=', $user_id);
            })

            ->select('customers_views.*')
            ->groupBy('customers_views.id')
            ->get();
        $data['count_user_by_lead'] = $records->count();


        /*  if (is_null($data)) {
              return $this->sendError('Lead not found', ['Lead not found'], 422);
          }*/
        return $this->sendResponse($data, 'Lead retrieved successfully');
    }

    public function getLeadByLabelCount($label_id, $fil_user_id, Request $request)
    {
        $leadStageId = $request->input('lead_stage_id');
        $leadLabelId = $request->input('lead_label_id');
        $leadSourceId = $request->input('lead_source_id');
        $leadCategoryId = $request->input('lead_category_id');
        $estimateStatus = $request->input('estimate_status');
        $cityName = $request->input('city_name');
        $stateId = $request->input('state_id');
        $countryId = $request->input('country_id');
        $leadStartDate = $request->input('lead_start_date');
        $leadEndDate = $request->input('lead_end_date');
        $leadStageIdArr = ($leadStageId) ? explode(",", $leadStageId) : [];
        $leadLabelIdArr = ($leadLabelId) ? explode(",", $leadLabelId) : [];
        $leadSourceIdArr = ($leadSourceId) ? explode(",", $leadSourceId) : [];
        $leadCategoryIdArr = ($leadCategoryId) ? explode(",", $leadCategoryId) : [];
        $estimateStatusArr = ($estimateStatus) ? explode(",", $estimateStatus) : [];

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.*', DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code'))
            ->where('cv.company_id', $this->company_id)
//            ->where('cv.some_day_flg', 0)
//            ->whereRaw('DATE(cv.last_follow_up_datetime) = ?', [date('Y-m-d')])
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id); CMX
                }
            })
            ->where(function ($query) use ($fil_user_id, $leadStageIdArr, $leadLabelIdArr, $cityName,$stateId,$countryId,$leadSourceIdArr,$leadCategoryIdArr,$estimateStatusArr,$leadStartDate,$leadEndDate) {
                if (!empty($leadStageIdArr) && count($leadStageIdArr) > 0) {
                    $query->WhereIn('cv.lead_stage_id', $leadStageIdArr);
                }
                if (!empty($leadSourceIdArr) && count($leadSourceIdArr) > 0) {
                    $query->WhereIn('cv.customer_lead_id', $leadSourceIdArr);
                }
                if (!empty($leadCategoryIdArr) && count($leadCategoryIdArr) > 0) {
                    $query->WhereIn('cv.customer_category_id', $leadCategoryIdArr);
                }
                /* if (!empty($leadLabelIdArr) && count($leadLabelIdArr) > 0) {
                     $query->WhereIn('lg.id', $leadLabelIdArr);
                 }*/
                if (!empty($cityName)) {
                    $query->Where('cv.city_name', $cityName);
                }
                if (!empty($stateId)) {
                    $query->Where('cv.state_id', $stateId);
                }
                if (!empty($countryId)) {
                    $query->Where('cv.country_id', $countryId);
                }
                if (!empty($estimateStatusArr) && count($estimateStatusArr) > 0) {
                    $query->WhereIn('cv.estimate_status',$estimateStatusArr);
                }
                if($leadStartDate && $leadEndDate) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(cv.created_at, '%Y-%m-%d')"), [$leadStartDate, $leadEndDate]);
                }
                if ($fil_user_id > 0) {
                    $query->where('cv.assigned_to_user', '=', $fil_user_id);
                }
            })
            ->groupBy('cv.id');
        if ($label_id) {
            $records = $records->havingRaw("FIND_IN_SET('$label_id', GROUP_CONCAT(lg.id)) > 0");
        }

        $records = $records->get();

        $data['count_label_by_lead'] = $records->count();

        /* if (is_null($data)) {
             return $this->sendError('Follow up not found', ['Lead not found'], 422);
         }*/
        return $this->sendResponse($data, 'Follow up retrieved successfully');
    }

    public function getLeadExclusiveSearch($user_id, $company_id, $search = null)
    {
        $items = LeadExclusive::select('lead_exclusives.name as contact_name', 'lead_exclusives.user_id', 'lead_exclusives.company_id', 'users.name', 'users.company_name')
            ->join('users', 'lead_exclusives.company_id', '=', 'users.id')
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->where('lead_exclusives.mobile_no', '=', $search);
                }
            })
            ->where('lead_exclusives.user_id', '=', $user_id)
            ->where('lead_exclusives.company_id', '=', $company_id)
            ->get();

        if ($items->count() > 0)
            return $this->sendResponse($items, 'exclusive retrieved successfully');

        $items = Customer::select('customers.phone_no as mobile_no', 'customers.name as contact_name', 'customers.user_id', 'customers.company_id', 'users.name', 'users.company_name')
            ->join('users', 'customers.assigned_to_user', '=', 'users.id')
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->where('customers.phone_no', '=', $search);
                }
            })
            //->where('customers.user_id', '=', $user_id)
            ->get();

        if ($items->count() > 0)
            return $this->sendResponse($items, 'exclusive retrieved successfully');

        return $this->sendResponse([], 'exclusive retrieved successfully', 200);

    }

    public function removeMultipleFollowUpDate(Request $request)
    {
        $input = $request->all();
        $ids = explode(',', $input['id']);  // Split the input IDs
        $notes = $input['notes'];
        foreach ($ids as $id) {
            $paramArr['id'] = $id;
            $customer = EstimateTimeline::where('id', $paramArr['id'])->select('customer_id')->first();
            EstimateTimeline::where('id', $paramArr['id'])->update(array('follow_up_datetime' => "0000-00-00 00:00:00"));
            Customer::where('id', $customer->customer_id)->update(array('some_day_flg' => 0));

            $customer_data = EstimateTimeline::select("id")
                ->where('customer_id', '=', $customer->customer_id)
                ->wherein('activity_type', [9])   //[1, 2, 3, 9]
                ->orderBy('id', 'DESC')
                ->take(1)
                ->get()
                ->toArray();

            if ($customer_data && $customer_data[0]['id']) {
                $a = SalesPersonPerformances::where('timeline_id', $customer_data[0]['id'])->update(array('completed_task' => 1));
            }

            $logInput['follow_up_datetime'] = '0000-00-00 00:00:00';
            $logInput['assigned_to'] = $this->logged_user->id;
            $logInput['customer_id'] = $customer->customer_id;
            $logInput['entry_type'] = "remove follow up";
            $logInput['activity_name'] = "Remove Follow Up";
            $logInput['activity_type'] = "Remove Follow Up";
            $logInput['activity_type'] = 14;
            $logInput['internal_remarks'] = $notes;
            $logInput['user_id'] = $this->logged_user->id;
            $logInput['company_id'] = $this->company_id;
            $logInput['created_by'] = $this->logged_user->id;
            $logInput['updated_by'] = $this->logged_user->id;
            LogActivity::addToActivityLog($logInput);
            Customer::find($customer->customer_id)->update(["new_lead_flag" => 0]);
        }
        return $this->sendResponse([], 'Successfully Saved!');
    }

    public function getOprOverdueFollowup($start, $rowperpage, $fil_user_id, Request $request)
    {
        $orp_filter_id = ($request->input('orp_filter_id')) ? explode(',', $request->input('orp_filter_id')) : [];
        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.*', DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code'))
            ->where('cv.company_id', $this->company_id)
            /*->where(function ($query) use ($fil_user_id) {
                if ($fil_user_id > 0) {
                    $query->where('cv.assigned_to_user', '=', $fil_user_id);
                }
            })*/
            ->where(function ($query) use ($orp_filter_id) {
                if ($orp_filter_id) {
                    $query->wherein('cv.id', $orp_filter_id);
                }
            })
            ->groupBy('cv.id')
            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();

        $data = array();
        foreach ($records as $record) {
            $country_data = [];
            if ($record->est_currency_id)
                $country_data = Country::where("id", $record->est_currency_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->first();
            $results = DB::table('customer_labels')
                ->select(DB::raw("group_concat(label_id separator ',') as labelId"))
                ->where('customer_id', '=', $record->id)
                ->groupBy('customer_id')
                ->get()->toArray();
            $label_color = [];
            $selectedLabelArr = [];
            if ($results) {
                $selectedLabelArr = explode(',', $results[0]->labelId);

                $label_color = DB::table('lead_groups')
                    ->select('name', 'color_code', 'id')
                    ->whereIn('id', $selectedLabelArr)
                    ->get()->toArray();
            }


            $data[] = array(
                "id" => $record->id,
                "name" => $record->name,
                "company_name" => $record->company_name,
                "lead_origin" => $record->lead_origin,
                "lead_category" => $record->lead_category,
                "created_at" => \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('d-m-Y h:i A'),
                "customer_type" => $record->customer_type,
                "email" => $record->email,
                "phone_no" => $record->phone_no,
                "whatsapp_no" => $record->whatsapp_no,
                "address" => $record->address,
                "pincode" => $record->pincode,
                "country_name" => $record->country_name,
                "state_name" => $record->state_name,
                "city_name" => $record->city_name,
                "description" => $record->description,
                "last_activity" => $record->last_activity,
                "assign_user_name" => $record->user_name,
//                "net_amount" => $record->net_amount,
                "net_amount" => $record->net_amount,
                "status" => $record->status,
                "last_activity_date" => ($record->last_activity_date) ? $record->last_activity_date : null,
                "last_activity_type" => $record->last_activity_type,
                "last_activity_name" => $record->last_activity_name,
                "last_internal_remarks" => $record->last_internal_remarks,
                "label_color" => $label_color,
                "selectedLabelArr" => $selectedLabelArr,
                "estimate_status" => $record->estimate_status,
                "follow_up_datetime" => ($record->last_follow_up_datetime && $record->last_follow_up_datetime != '0000-00-00 00:00:00') ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->last_follow_up_datetime)->format('Y-m-d H:i:s') : '',
                 "created_at" => ($record->created_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('Y-m-d H:i:s'):'',
                "updated_at" => ($record->updated_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->updated_at)
                ->format('Y-m-d H:i:s'):'',
                "last_is_modified" => $record->last_is_modified,
                "last_is_follow_up" => $record->last_is_follow_up,
                "currency_symbol" => (isset($country_data->currency_symbol)) ? $country_data->currency_symbol : '',
                "new_lead_flag" => $record->new_lead_flag,
                "lead_stage_name" => $record->lead_stage_name,
                "lead_stage_color_code" => $record->lead_stage_color_code,
                "last_activity_id" => $record->last_activity_id,
                "user_id" => $record->user_id,
            );

        }
        if (is_null($data)) {
            return $this->sendError('Follow up not found', ['Lead not found'], 422);
        }
        return $this->sendResponse($data, 'Follow up retrieved successfully');
    }

    public function getOprNeverFollowup($start, $rowperpage, $fil_user_id, Request $request)
    {
        $orp_filter_id = ($request->input('orp_filter_id')) ? explode(',', $request->input('orp_filter_id')) : [];
        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.*', DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code'))
            ->where('cv.company_id', $this->company_id)
            /*->where(function ($query) use ($fil_user_id) {
                if ($fil_user_id > 0) {
                    $query->where('cv.assigned_to_user', '=', $fil_user_id);
                }
            })*/
            ->where(function ($query) use ($orp_filter_id) {
                if ($orp_filter_id) {
                    $query->wherein('cv.id', $orp_filter_id);
                }
            })
            ->where(function ($query) use ($user_perm) {
                $query->where('cv.last_follow_up_datetime', '=', '0000-00-00 00:00:00');
                $query->orWhereNull('cv.last_follow_up_datetime');
            })

            ->groupBy('cv.id')
            ->orderBy('cv.created_at', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        if (!$records) {
            return $this->sendError('Follow up not found', ['Follow up not found'], 422);
        }

        $data = array();
        foreach ($records as $record) {
            $country_data = [];
            if ($record->est_currency_id)
                $country_data = Country::where("id", $record->est_currency_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->first();
            $results = DB::table('customer_labels')
                ->select(DB::raw("group_concat(label_id separator ',') as labelId"))
                ->where('customer_id', '=', $record->id)
                ->groupBy('customer_id')
                ->get()->toArray();
            $label_color = [];
            $selectedLabelArr = [];
            if ($results) {
                $selectedLabelArr = explode(',', $results[0]->labelId);

                $label_color = DB::table('lead_groups')
                    ->select('name', 'color_code', 'id')
                    ->whereIn('id', $selectedLabelArr)
                    ->get()->toArray();
            }


            $data[] = array(
                "id" => $record->id,
                "name" => $record->name,
                "company_name" => $record->company_name,
                "lead_origin" => $record->lead_origin,
                "lead_category" => $record->lead_category,
                "created_at" => \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('d-m-Y h:i A'),
                "customer_type" => $record->customer_type,
                "email" => $record->email,
                "phone_no" => $record->phone_no,
                "whatsapp_no" => $record->whatsapp_no,
                "address" => $record->address,
                "pincode" => $record->pincode,
                "country_name" => $record->country_name,
                "state_name" => $record->state_name,
                "city_name" => $record->city_name,
                "description" => $record->description,
                "last_activity" => $record->last_activity,
                "assign_user_name" => $record->user_name,
//                "net_amount" => $record->net_amount,
                "net_amount" => $record->net_amount,
                "status" => $record->status,
                "last_activity_date" => ($record->last_activity_date) ? $record->last_activity_date : null,
                "last_activity_type" => $record->last_activity_type,
                "last_activity_name" => $record->last_activity_name,
                "last_internal_remarks" => $record->last_internal_remarks,
                "label_color" => $label_color,
                "selectedLabelArr" => $selectedLabelArr,
                "estimate_status" => $record->estimate_status,
                "follow_up_datetime" => ($record->last_follow_up_datetime && $record->last_follow_up_datetime != '0000-00-00 00:00:00') ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->last_follow_up_datetime)->format('Y-m-d H:i:s') : null,
                 "created_at" => ($record->created_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('Y-m-d H:i:s'):'',
                "updated_at" => ($record->updated_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->updated_at)
                ->format('Y-m-d H:i:s'):'',
                "last_is_modified" => $record->last_is_modified,
                "last_is_follow_up" => $record->last_is_follow_up,
                "currency_symbol" => (isset($country_data->currency_symbol)) ? $country_data->currency_symbol : '',
                "new_lead_flag" => $record->new_lead_flag,
                "lead_stage_name" => $record->lead_stage_name,
                "lead_stage_color_code" => $record->lead_stage_color_code,
                "last_activity_id" => $record->last_activity_id,
                "user_id" => $record->user_id,
            );

        }
        if (is_null($data)) {
            return $this->sendError('Follow up not found', ['Lead not found'], 422);
        }
        return $this->sendResponse($data, 'Follow up retrieved successfully');
    }

    public function getOprLeadIndex($start, $rowperpage, $fil_user_id, Request $request)
    {

//        if ($orderBy == 0) {
//            $order_by = 'id';
//            $order_by_name = 'desc';
//        }
//
//        if ($orderBy == 1) {
//            $order_by = 'last_activity_date';
//            $order_by_name = 'desc';
//        }
//
//        if ($orderBy == 2) {
//            $order_by = 'name';
//            $order_by_name = 'asc';
//        }
        $orp_filter_id = ($request->input('orp_filter_id')) ? explode(',', $request->input('orp_filter_id')) : [];

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
//        DB::enableQueryLog();
        $records = ViewCustomerData::
        leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'customers_views.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->where(function ($query) use ($user_perm) {
                $query->where('customers_views.company_id', $this->company_id);
            })
            /*->where(function ($query) use ($fil_user_id) {
                if ($fil_user_id > 0) {
                    $query->where('customers_views.assigned_to_user', '=', $fil_user_id);
                }
            })*/
            ->where(function ($query) use ($orp_filter_id) {
                if ($orp_filter_id) {
                    $query->wherein('customers_views.id', $orp_filter_id);
                }
            })
            ->select('customers_views.*')
//            ->orderBy($order_by, $order_by_name)
            ->orderBy('customers_views.created_at', 'DESC')
            ->groupBy('customers_views.id')
            ->skip($start)
            ->take($rowperpage)
            ->get();
        $data = array();
        $i = 0;
        foreach ($records as $record) {
            $id = $record->id;
            $estimate_id = $record->estimate_id;
            $new_lead_flag = $record->new_lead_flag;
            $customer_type = $record->customer_type;
            $name = $record->name;
            $lead_origin = $record->lead_origin;
            $lead_category = $record->lead_category;
            $email = $record->email;
            $phone_no = $record->phone_no;
            $whatsapp_no = $record->whatsapp_no;
            $address = $record->address;
            $pincode = $record->pincode;
            $description = $record->description;
            $status = $record->status;
            $country_name = $record->country_name;
            $state_name = $record->state_name;
            $city_name = $record->city_name;
            $last_activity = $record->last_activity;
            $assign_user_name = $record->user_name;
            $net_amount = $record->net_amount;
            $estimate_status = $record->estimate_status;
            $last_activity_type = $record->last_activity_type;
            $last_activity_name = $record->last_activity_name;
            $last_internal_remarks = $record->last_internal_remarks;
            $some_day_flg = $record->some_day_flg;
            $date_added = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('d-m-Y h:i A');
            $last_activity_date = ($record->last_activity_date) ? $record->last_activity_date : null;
            $follow_up_datetime = ($record->last_follow_up_datetime) ? $record->last_follow_up_datetime : null;
            $i++;
            $country_data = [];

            if ($record->est_currency_id)
                $country_data = Country::where("id", $record->est_currency_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->first();
            $results = DB::table('customer_labels')
                ->select(DB::raw("group_concat(label_id separator ',') as labelId"))
                ->where('customer_id', '=', $record->id)
                ->groupBy('customer_id')
                ->get()->toArray();
            $label_color = [];
            $selectedLabelArr = [];
            if ($results) {
                $selectedLabelArr = explode(',', $results[0]->labelId);

                $label_color = DB::table('lead_groups')
                    ->select('name', 'color_code', 'id')
                    ->whereIn('id', $selectedLabelArr)
                    ->get()->toArray();
            }
            $data[] = array(
                "sr_no" => $i,
                "id" => $id,
                "estimate_id" => $estimate_id,
                "name" => $name,
                "company_name" => $record->company_name,
                "lead_origin" => $lead_origin,
                "lead_category" => $lead_category,
                "created_at" => $date_added,
                "customer_type" => $customer_type,
                "email" => $email,
                "phone_no" => $phone_no,
                "whatsapp_no" => $whatsapp_no,
                "address" => $address,
                "pincode" => $pincode,
                "country_name" => $country_name,
                "state_name" => $state_name,
                "city_name" => $city_name,
                "description" => $description,
                "last_activity" => $last_activity,
                "assign_user_name" => $assign_user_name,
                "net_amount" => $net_amount,
                "status" => $status,
                "last_activity_date" => $last_activity_date,
                "last_activity_type" => $last_activity_type,
                "last_activity_name" => $last_activity_name,
                "last_internal_remarks" => $last_internal_remarks,
                "label_color" => $label_color,
                "selectedLabelArr" => $selectedLabelArr,
                "estimate_status" => $estimate_status,
                "estimate_no" => $record->estimate_no,
                "follow_up_datetime" => $follow_up_datetime,
                "some_day_flg" => $some_day_flg,
                "country_code" => $record->country_code,
                "whatsapp_country_code" => $record->whatsapp_country_code,
                "new_lead_flag" => $record->new_lead_flag,
                 "created_at" => ($record->created_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)->format('Y-m-d H:i:s'):'',
                "updated_at" => ($record->updated_at)?\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->updated_at)
                ->format('Y-m-d H:i:s'):'',
                "last_is_modified" => $record->last_is_modified,
                "last_is_follow_up" => $record->last_is_follow_up,
                "currency_symbol" => (isset($country_data->currency_symbol)) ? $country_data->currency_symbol : '',
                "lead_stage_name" => $record->lead_stage_name,
                "lead_stage_color_code" => $record->lead_stage_color_code,
                "last_activity_id" => $record->last_activity_id,
                "user_id" => $record->user_id,
            );

        }
        if (is_null($data)) {
            return $this->sendError('Lead not found', ['Lead not found'], 422);
        }
        return $this->sendResponse($data, 'Lead retrieved successfully');
    }
}
