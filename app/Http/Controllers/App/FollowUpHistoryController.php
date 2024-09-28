<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class FollowUpHistoryController extends Controller
{
    protected $logged_user = null;
    protected $company_id = 0;
    protected $user_param = 0;
    protected $segment = null;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->logged_user = \Illuminate\Support\Facades\Auth::user();
            $this->company_id = ($this->logged_user->company_id) ? $this->logged_user->company_id : $this->logged_user->id;
            $this->user_param = \App\Helpers\PermissionCheck::check_permission('role-list');
            $this->segment = $request->segment(1);
            return $next($request);
        });
    }

    public function timeAgoStringFun($activityDate)
    {
        $now = time(); // Get the current time

        $diff = $now - strtotime($activityDate); // Get the difference in seconds

        if ($diff < 60) {
            $timeAgoString = $diff . ' sec ago';
        } elseif ($diff < 120) {
            $timeAgoString = '1 minute ago';
        } elseif ($diff < 3600) {
            $timeAgoString = round($diff / 60) . ' min ago';
        } elseif ($diff < 7200) {
            $timeAgoString = '1 hour ago';
        } elseif ($diff < 86400) {
            $timeAgoString = round($diff / 3600) . ' hours ago';
        } elseif ($diff < 172800) {
            $timeAgoString = 'yesterday';
        } elseif ($diff < 2592000) {
            $timeAgoString = round($diff / 86400) . ' days ago';
        } elseif ($diff < 5184000) {
            $timeAgoString = 'last month';
        } elseif ($diff < 31536000) {
            $months = round($diff / 2592000);
            $timeAgoString = $months . ' ' . (($months == 1) ? 'month' : 'months') . ' ago';
        } else {
            $years = round($diff / 31536000);
            $timeAgoString = $years . ' ' . (($years == 1) ? 'year' : 'years') . ' ago';
        }
        return $timeAgoString;
    }

    public function dashboardIndex(Request $request)
    {

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');

        $input = $request->all();
        $select_data = explode(',',trim($request->get('today_followup_select_data'),','));

        $select_columns = [
            'cv.id',
            'cv.last_is_modified',
            'cv.last_is_follow_up',
            'cv.some_day_flg',
            'cv.last_activity_updated_at',
            'cv.new_lead_flag',
        ];


            $select_columns[] = 'cv.name';
            $select_columns[] = 'cv.phone_no';


            $select_columns[] = 'cv.est_currency_id';
            $select_columns[] = 'cv.net_amount';



            $select_columns[] = 'cv.last_follow_up_datetime';



            $select_columns[] = 'cv.user_name';
            $select_columns[] = 'cv.assigned_to_user';



            $select_columns[] = 'cv.lead_stage_name';
            $select_columns[] = 'cv.lead_stage_color_code';


            $select_columns[] = 'cv.last_activity';
            $select_columns[] = 'cv.last_activity_type';
            $select_columns[] = 'cv.last_internal_remarks';
            $select_columns[] = 'cv.last_activity_name';
            $select_columns[] = 'cv.last_follow_up_datetime';



            $select_columns[] = 'cv.estimate_status';



            $select_columns[] = DB::raw('GROUP_CONCAT(lg.name) as label_name');
            $select_columns[] = DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code');


        $column_array = $select_columns;


        /*$select_column_name = "'cv.id','cv.last_is_modified','cv.last_is_follow_up','cv.some_day_flg','cv.last_activity_updated_at','cv.new_lead_flag',";
        if(in_array("cv.name", $select_data)){
            $select_column_name .= "'cv.name','cv.phone_no',";
        }

        if(in_array("cv.net_amount", $select_data)){
            $select_column_name .= "'cv.est_currency_id','cv.net_amount',";
        }
        if(in_array("cv.last_followup_datetime", $select_data)){
            $select_column_name .= "'cv.last_follow_up_datetime',";
        }
        if(in_array("cv.assign_user_name", $select_data)){
            $select_column_name .= "'cv.user_name','cv.assigned_to_user',";
        }

        if(in_array("cv.last_stage_name", $select_data)){
            $select_column_name .= "'cv.lead_stage_name','cv.lead_stage_color_code',";
        }
        if(in_array("cv.last_activity", $select_data)){
            $select_column_name .= "'cv.last_activity','cv.last_activity_type','cv.last_internal_remarks','cv.last_activity_name','cv.last_follow_up_datetime',";
        }
        if(in_array("cv.estimate_status", $select_data)){
            $select_column_name .= "'cv.estimate_status',";
        }

        if(in_array("cv.label_name", $select_data)){
            $select_column_name .= DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code');
        }

        $select_column_name = trim($select_column_name,',');

        // Remove quotes and explode by comma
        $column_array = explode(",", str_replace("'", "", $select_column_name));

        // Trim each element to remove extra spaces
        $column_array = array_map('trim', $column_array);*/
//        print_r($column_array); die;
        ## Read value
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length"); // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc

        if($columnName=='last_activity'){
            $columnName='cv.last_activity_updated_at';
        }

        // Fetch records
        $assigned_to_user = $request->get('assigned_to_user');
//        $status = $request->get('status');
        $fil_followup_lead_label_id = [];
        if($request->get('fil_followup_lead_label_id'))
            $fil_followup_lead_label_id = explode(",",$request->get('fil_followup_lead_label_id'));

        $fil_followup_lead_stage_id = $request->get('fil_followup_lead_stage_id');
        $fil_followup_customer_category_id = $request->get('fil_followup_customer_category_id');
        $fil_followup_customer_lead_id = $request->get('fil_followup_customer_lead_id');
        $fil_followup_created_user_id = $request->get('fil_followup_created_user_id');
        $fil_followup_estimate_status_id = $request->get('fil_followup_estimate_status_id');
        $fil_followup_country_id = $request->get('fil_followup_country_id');
        $fil_followup_state_id = $request->get('fil_followup_state_id');
        $fil_followup_city_name = $request->get('fil_followup_city_name');

        // Total records
        $totalRecords = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.id')
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 0)
            ->where(DB::raw('DATE(cv.last_follow_up_datetime)'), date('Y-m-d'))
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                }
            })
            ->where(function ($query) use ($fil_followup_lead_label_id,$fil_followup_lead_stage_id,$fil_followup_customer_category_id,$fil_followup_customer_lead_id,$fil_followup_created_user_id,$fil_followup_estimate_status_id, $assigned_to_user,$fil_followup_country_id,$fil_followup_state_id,$fil_followup_city_name) {
                if ($fil_followup_country_id) {
                    $query->where('cv.country_id', $fil_followup_country_id);
                }
                if ($fil_followup_state_id) {
                    $query->where('cv.state_id', $fil_followup_state_id);
                }
                if ($fil_followup_city_name) {
                    $query->where('cv.city_name', $fil_followup_city_name);
                }
                if ($fil_followup_lead_label_id) {
                    $query->where('lg.id', '=', $fil_followup_lead_label_id);
                }
                if ($fil_followup_lead_stage_id) {
                    $query->where('cv.lead_stage_id', $fil_followup_lead_stage_id);
                }
                if ($fil_followup_customer_category_id != '') {
                    $query->where('cv.customer_category_id', '=', $fil_followup_customer_category_id);
                }
                if ($fil_followup_customer_lead_id != '') {
                    $query->where('cv.customer_lead_id', '=', $fil_followup_customer_lead_id);
                }
                if ($fil_followup_created_user_id != '') {
                    $query->where('cv.user_id', '=', $fil_followup_created_user_id);
                }
                if ($fil_followup_estimate_status_id != '') {
                    $query->where('cv.estimate_status', '=', $fil_followup_estimate_status_id);
                }
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })
            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->groupBy('cv.id')
            ->get();
        $totalRecords = $totalRecords->count();

        $totalRecordswithFilter = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.id')
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 0)
            ->where(DB::raw("DATE(cv.last_follow_up_datetime)"), date('Y-m-d'))
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                }
            })
            ->where(function ($query) use ($fil_followup_lead_label_id,$fil_followup_lead_stage_id,$fil_followup_customer_category_id,$fil_followup_customer_lead_id,$fil_followup_created_user_id,$fil_followup_estimate_status_id, $assigned_to_user,$fil_followup_country_id,$fil_followup_state_id,$fil_followup_city_name) {
                if ($fil_followup_country_id) {
                    $query->where('cv.country_id', $fil_followup_country_id);
                }
                if ($fil_followup_state_id) {
                    $query->where('cv.state_id', $fil_followup_state_id);
                }
                if ($fil_followup_city_name) {
                    $query->where('cv.city_name', $fil_followup_city_name);
                }
                if ($fil_followup_lead_label_id) {
                    $query->where('lg.id', '=', $fil_followup_lead_label_id);
                }
                if ($fil_followup_lead_stage_id) {
                    $query->where('cv.lead_stage_id', $fil_followup_lead_stage_id);
                }
                if ($fil_followup_customer_category_id != '') {
                    $query->where('cv.customer_category_id', '=', $fil_followup_customer_category_id);
                }
                if ($fil_followup_customer_lead_id != '') {
                    $query->where('cv.customer_lead_id', '=', $fil_followup_customer_lead_id);
                }
                if ($fil_followup_created_user_id != '') {
                    $query->where('cv.user_id', '=', $fil_followup_created_user_id);
                }
                if ($fil_followup_estimate_status_id != '') {
                    $query->where('cv.estimate_status', '=', $fil_followup_estimate_status_id);
                }
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })
            ->where(function ($query) use ($search_arr) {
                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.name', 'like', '%' . $search_arr . '%');
                });

                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.phone_no', 'like', '%' . $search_arr . '%');
                });
            })
            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->groupBy('cv.id')
            ->get();

        $totalRecordswithFilter = $totalRecordswithFilter->count();
        DB::enableQueryLog();
        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
//            ->select('cv.*', DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code'))
            ->select($column_array)
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 0)
            ->where(DB::raw("DATE(cv.last_follow_up_datetime)"), date('Y-m-d'))
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                }
            })
            ->where(function ($query) use ($fil_followup_lead_stage_id,$fil_followup_customer_category_id,$fil_followup_customer_lead_id,$fil_followup_created_user_id,$fil_followup_estimate_status_id, $assigned_to_user,$fil_followup_country_id,$fil_followup_state_id,$fil_followup_city_name) {
                if ($fil_followup_country_id) {
                    $query->where('cv.country_id', $fil_followup_country_id);
                }
                if ($fil_followup_state_id) {
                    $query->where('cv.state_id', $fil_followup_state_id);
                }
                if ($fil_followup_city_name) {
                    $query->where('cv.city_name', $fil_followup_city_name);
                }
                if ($fil_followup_lead_stage_id) {
                    $query->where('cv.lead_stage_id', $fil_followup_lead_stage_id);
                }
                if ($fil_followup_customer_category_id != '') {
                    $query->where('cv.customer_category_id', '=', $fil_followup_customer_category_id);
                }
                if ($fil_followup_customer_lead_id != '') {
                    $query->where('cv.customer_lead_id', '=', $fil_followup_customer_lead_id);
                }
                if ($fil_followup_created_user_id != '') {
                    $query->where('cv.user_id', '=', $fil_followup_created_user_id);
                }
                if ($fil_followup_estimate_status_id != '') {
                    $query->where('cv.estimate_status', '=', $fil_followup_estimate_status_id);
                }
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })

            ->where(function ($query) use ($search_arr) {
                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.name', 'like', '%' . $search_arr . '%');
                });

                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.phone_no', 'like', '%' . $search_arr . '%');
                });
            })
            ->groupBy('cv.id')
            ->skip($start)
            ->take($rowperpage)
            ->orderBy($columnName, $columnSortOrder);
        if ($fil_followup_lead_label_id) {
            $records = $records->WhereIn("lg.id",$fil_followup_lead_label_id);
        }

        $records = $records->get();
//dd(DB::getQueryLog($records));

        $data = array();
        $i = 0;

        foreach ($records as $record) {
            $country_data = [];
            if(isset($record->est_currency_id))
                $country_data = Country::where("id", $record->est_currency_id)->select('name','currency_name','currency_code','currency_symbol')->orderBy('id', 'DESC')->get()->first();

            $id = Crypt::encrypt($record->id);
            $name = isset($record->name)?$record->name:'';
            $phone_no = isset($record->phone_no)?$record->phone_no:'';
            $last_activity = isset($record->last_activity)?$record->last_activity:'';
            $assign_user_name = isset($record->user_name)?$record->user_name:'';
            $assigned_to_user = isset($record->assigned_to_user)?$record->assigned_to_user:0;
            $net_amount =isset($record->net_amount)?$record->net_amount:'';
            $estimate_status = isset($record->estimate_status)? $record->estimate_status: '';
            $last_activity_type = isset($record->last_activity_type)?$record->last_activity_type:'';
            $last_internal_remarks = isset($record->last_internal_remarks)?$record->last_internal_remarks:'';
            $last_activity_name = isset($record->last_activity_name)?$record->last_activity_name:'';


            $follow_up_datetime = (isset($record->last_follow_up_datetime) && $record->last_follow_up_datetime != '0000-00-00 00:00:00') ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->last_follow_up_datetime)->format('d-m-Y h:i A') : '';
            $last_is_modified = $record->last_is_modified;
            $last_is_follow_up = $record->last_is_follow_up;
            $i++;
            $labelName = '';
            if (isset($record->label_name)) {
                $leadLabelNameArr = explode(',', $record->label_name);
                $labelColorCodeArr = explode(',', $record->label_color_code);
                foreach ($leadLabelNameArr as $key => $labelLabel) {
                    $st = '';
                    if ($key % 2 == 0) {
                        $st = '<br>';
                    }
                    $labelName .= '<span class="fs-6 badge me-2" style = "background-color: transparent;color: ' . $labelColorCodeArr[$key] . ';border: 1px solid ' . $labelColorCodeArr[$key] . '">' . $labelLabel . '</span>' . $st;
                }
            }

            if (strtotime(date('d-m-Y', strtotime($follow_up_datetime))) == strtotime(date('d-m-Y')) && $record->some_day_flg == 0) {
                $follow_up_datetime = 'Today - ' . \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->last_follow_up_datetime)
                        ->format('h:i A');
            }




            $data[] = array(
                "id" => $i,
                "name" => $name,
                "phone_no" => $phone_no,
                "last_activity" => $last_activity,
                "last_activity_type" => $last_activity_type,
                "last_internal_remarks" => $last_internal_remarks,
                "last_activity_name" => $last_activity_name,
                "assigned_to_user" => $assigned_to_user,
                "assign_user_name" => $assign_user_name,
                "net_amount" => (isset($country_data->currency_symbol) && $net_amount > 0) ? $country_data->currency_symbol.' ' . $net_amount : $net_amount,
                "label_name" => $labelName,
                "estimate_status" => $estimate_status,
                "last_follow_up_datetime" => $follow_up_datetime,
                "time_ago_string" => $this->timeAgoStringFun($record->last_activity_updated_at),
                "action" => $id,
                "last_is_modified" => $last_is_modified,
                "last_is_follow_up" => $last_is_follow_up,
                "new_lead_flag" => $record->new_lead_flag,
                "lead_stage_name" => isset($record->lead_stage_name)?$record->lead_stage_name:'',
                "lead_stage_color_code" => isset($record->lead_stage_color_code)?$record->lead_stage_color_code:'',
            );
        }
        $response = array(
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecordswithFilter,
            "data" => $data
        );

        return json_encode($response);
    }

    public function index(Request $request)
    {

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');

        $input = $request->all();
        $select_data = explode(',',trim($request->get('today_followup_select_data'),','));

        $select_columns = [
            'cv.id',
            'cv.last_is_modified',
            'cv.last_is_follow_up',
            'cv.some_day_flg',
            'cv.last_activity_updated_at',
            'cv.new_lead_flag',
        ];

        if (in_array("cv.name", $select_data)) {
            $select_columns[] = 'cv.name';
            $select_columns[] = 'cv.phone_no';
        }

        if (in_array("net_amount", $select_data)) {
            $select_columns[] = 'cv.est_currency_id';
            $select_columns[] = 'cv.net_amount';
        }

        if (in_array("cv.last_followup_datetime", $select_data)) {
            $select_columns[] = 'cv.last_follow_up_datetime';
        }

        if (in_array("cv.assign_user_name", $select_data)) {
            $select_columns[] = 'cv.user_name';
            $select_columns[] = 'cv.assigned_to_user';
        }

        if (in_array("cv.last_stage_name", $select_data)) {
            $select_columns[] = 'cv.lead_stage_name';
            $select_columns[] = 'cv.lead_stage_color_code';
        }

        if (in_array("cv.last_activity", $select_data)) {
            $select_columns[] = 'cv.last_activity';
            $select_columns[] = 'cv.last_activity_type';
            $select_columns[] = 'cv.last_internal_remarks';
            $select_columns[] = 'cv.last_activity_name';
            $select_columns[] = 'cv.last_follow_up_datetime';
        }

        if (in_array("cv.estimate_status", $select_data)) {
            $select_columns[] = 'cv.estimate_status';
        }

        if (in_array("cv.label_name", $select_data)) {
            $select_columns[] = DB::raw('GROUP_CONCAT(lg.name) as label_name');
            $select_columns[] = DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code');
        }

        $column_array = $select_columns;


        /*$select_column_name = "'cv.id','cv.last_is_modified','cv.last_is_follow_up','cv.some_day_flg','cv.last_activity_updated_at','cv.new_lead_flag',";
        if(in_array("cv.name", $select_data)){
            $select_column_name .= "'cv.name','cv.phone_no',";
        }

        if(in_array("cv.net_amount", $select_data)){
            $select_column_name .= "'cv.est_currency_id','cv.net_amount',";
        }
        if(in_array("cv.last_followup_datetime", $select_data)){
            $select_column_name .= "'cv.last_follow_up_datetime',";
        }
        if(in_array("cv.assign_user_name", $select_data)){
            $select_column_name .= "'cv.user_name','cv.assigned_to_user',";
        }

        if(in_array("cv.last_stage_name", $select_data)){
            $select_column_name .= "'cv.lead_stage_name','cv.lead_stage_color_code',";
        }
        if(in_array("cv.last_activity", $select_data)){
            $select_column_name .= "'cv.last_activity','cv.last_activity_type','cv.last_internal_remarks','cv.last_activity_name','cv.last_follow_up_datetime',";
        }
        if(in_array("cv.estimate_status", $select_data)){
            $select_column_name .= "'cv.estimate_status',";
        }

        if(in_array("cv.label_name", $select_data)){
            $select_column_name .= DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code');
        }

        $select_column_name = trim($select_column_name,',');

        // Remove quotes and explode by comma
        $column_array = explode(",", str_replace("'", "", $select_column_name));

        // Trim each element to remove extra spaces
        $column_array = array_map('trim', $column_array);*/
//        print_r($column_array); die;
        ## Read value
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length"); // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc

        if($columnName=='last_activity'){
            $columnName='cv.last_activity_updated_at';
        }

        // Fetch records
        $assigned_to_user = $request->get('assigned_to_user');
//        $status = $request->get('status');
        $fil_followup_lead_label_id = [];
        if($request->get('fil_followup_lead_label_id'))
            $fil_followup_lead_label_id = explode(",",$request->get('fil_followup_lead_label_id'));

        $fil_followup_lead_stage_id = $request->get('fil_followup_lead_stage_id');
        $fil_followup_customer_category_id = $request->get('fil_followup_customer_category_id');
        $fil_followup_customer_lead_id = $request->get('fil_followup_customer_lead_id');
        $fil_followup_created_user_id = $request->get('fil_followup_created_user_id');
        $fil_followup_estimate_status_id = $request->get('fil_followup_estimate_status_id');
        $fil_followup_country_id = $request->get('fil_followup_country_id');
        $fil_followup_state_id = $request->get('fil_followup_state_id');
        $fil_followup_city_name = $request->get('fil_followup_city_name');

        // Total records
        $totalRecords = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.id')
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 0)
            ->where(DB::raw('DATE(cv.last_follow_up_datetime)'), date('Y-m-d'))
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                }
            })
            ->where(function ($query) use ($fil_followup_lead_label_id,$fil_followup_lead_stage_id,$fil_followup_customer_category_id,$fil_followup_customer_lead_id,$fil_followup_created_user_id,$fil_followup_estimate_status_id, $assigned_to_user,$fil_followup_country_id,$fil_followup_state_id,$fil_followup_city_name) {
                if ($fil_followup_country_id) {
                    $query->where('cv.country_id', $fil_followup_country_id);
                }
                if ($fil_followup_state_id) {
                    $query->where('cv.state_id', $fil_followup_state_id);
                }
                if ($fil_followup_city_name) {
                    $query->where('cv.city_name', $fil_followup_city_name);
                }
                if ($fil_followup_lead_label_id) {
                    $query->where('lg.id', '=', $fil_followup_lead_label_id);
                }
                if ($fil_followup_lead_stage_id) {
                    $query->where('cv.lead_stage_id', $fil_followup_lead_stage_id);
                }
                if ($fil_followup_customer_category_id != '') {
                    $query->where('cv.customer_category_id', '=', $fil_followup_customer_category_id);
                }
                if ($fil_followup_customer_lead_id != '') {
                    $query->where('cv.customer_lead_id', '=', $fil_followup_customer_lead_id);
                }
                if ($fil_followup_created_user_id != '') {
                    $query->where('cv.user_id', '=', $fil_followup_created_user_id);
                }
                if ($fil_followup_estimate_status_id != '') {
                    $query->where('cv.estimate_status', '=', $fil_followup_estimate_status_id);
                }
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })
            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->groupBy('cv.id')
            ->get();
        $totalRecords = $totalRecords->count();

        $totalRecordswithFilter = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.id')
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 0)
            ->where(DB::raw("DATE(cv.last_follow_up_datetime)"), date('Y-m-d'))
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                }
            })
            ->where(function ($query) use ($fil_followup_lead_label_id,$fil_followup_lead_stage_id,$fil_followup_customer_category_id,$fil_followup_customer_lead_id,$fil_followup_created_user_id,$fil_followup_estimate_status_id, $assigned_to_user,$fil_followup_country_id,$fil_followup_state_id,$fil_followup_city_name) {
                if ($fil_followup_country_id) {
                    $query->where('cv.country_id', $fil_followup_country_id);
                }
                if ($fil_followup_state_id) {
                    $query->where('cv.state_id', $fil_followup_state_id);
                }
                if ($fil_followup_city_name) {
                    $query->where('cv.city_name', $fil_followup_city_name);
                }
                if ($fil_followup_lead_label_id) {
                    $query->where('lg.id', '=', $fil_followup_lead_label_id);
                }
                if ($fil_followup_lead_stage_id) {
                    $query->where('cv.lead_stage_id', $fil_followup_lead_stage_id);
                }
                if ($fil_followup_customer_category_id != '') {
                    $query->where('cv.customer_category_id', '=', $fil_followup_customer_category_id);
                }
                if ($fil_followup_customer_lead_id != '') {
                    $query->where('cv.customer_lead_id', '=', $fil_followup_customer_lead_id);
                }
                if ($fil_followup_created_user_id != '') {
                    $query->where('cv.user_id', '=', $fil_followup_created_user_id);
                }
                if ($fil_followup_estimate_status_id != '') {
                    $query->where('cv.estimate_status', '=', $fil_followup_estimate_status_id);
                }
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })
            ->where(function ($query) use ($search_arr) {
                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.name', 'like', '%' . $search_arr . '%');
                });

                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.phone_no', 'like', '%' . $search_arr . '%');
                });
            })
            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->groupBy('cv.id')
            ->get();

        $totalRecordswithFilter = $totalRecordswithFilter->count();
DB::enableQueryLog();
        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
//            ->select('cv.*', DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code'))
            ->select($column_array)
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 0)
            ->where(DB::raw("DATE(cv.last_follow_up_datetime)"), date('Y-m-d'))
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                }
            })
            ->where(function ($query) use ($fil_followup_lead_stage_id,$fil_followup_customer_category_id,$fil_followup_customer_lead_id,$fil_followup_created_user_id,$fil_followup_estimate_status_id, $assigned_to_user,$fil_followup_country_id,$fil_followup_state_id,$fil_followup_city_name) {
                if ($fil_followup_country_id) {
                    $query->where('cv.country_id', $fil_followup_country_id);
                }
                if ($fil_followup_state_id) {
                    $query->where('cv.state_id', $fil_followup_state_id);
                }
                if ($fil_followup_city_name) {
                    $query->where('cv.city_name', $fil_followup_city_name);
                }
                if ($fil_followup_lead_stage_id) {
                    $query->where('cv.lead_stage_id', $fil_followup_lead_stage_id);
                }
                if ($fil_followup_customer_category_id != '') {
                    $query->where('cv.customer_category_id', '=', $fil_followup_customer_category_id);
                }
                if ($fil_followup_customer_lead_id != '') {
                    $query->where('cv.customer_lead_id', '=', $fil_followup_customer_lead_id);
                }
                if ($fil_followup_created_user_id != '') {
                    $query->where('cv.user_id', '=', $fil_followup_created_user_id);
                }
                if ($fil_followup_estimate_status_id != '') {
                    $query->where('cv.estimate_status', '=', $fil_followup_estimate_status_id);
                }
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })

            ->where(function ($query) use ($search_arr) {
                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.name', 'like', '%' . $search_arr . '%');
                });

                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.phone_no', 'like', '%' . $search_arr . '%');
                });
            })
            ->groupBy('cv.id')
            ->skip($start)
            ->take($rowperpage)
            ->orderBy($columnName, $columnSortOrder);
            if ($fil_followup_lead_label_id) {
                $records = $records->WhereIn("lg.id",$fil_followup_lead_label_id);
            }

            $records = $records->get();
//dd(DB::getQueryLog($records));

        $data = array();
        $i = 0;

        foreach ($records as $record) {
            $country_data = [];
            if(isset($record->est_currency_id))
                $country_data = Country::where("id", $record->est_currency_id)->select('name','currency_name','currency_code','currency_symbol')->orderBy('id', 'DESC')->get()->first();

            $id = Crypt::encrypt($record->id);
            $name = isset($record->name)?$record->name:'';
            $phone_no = isset($record->phone_no)?$record->phone_no:'';
            $last_activity = isset($record->last_activity)?$record->last_activity:'';
            $assign_user_name = isset($record->user_name)?$record->user_name:'';
            $assigned_to_user = isset($record->assigned_to_user)?$record->assigned_to_user:0;
            $net_amount =isset($record->net_amount)?$record->net_amount:'';
            $estimate_status = isset($record->estimate_status)? $record->estimate_status: '';
            $last_activity_type = isset($record->last_activity_type)?$record->last_activity_type:'';
            $last_internal_remarks = isset($record->last_internal_remarks)?$record->last_internal_remarks:'';
            $last_activity_name = isset($record->last_activity_name)?$record->last_activity_name:'';


            $follow_up_datetime = (isset($record->last_follow_up_datetime) && $record->last_follow_up_datetime != '0000-00-00 00:00:00') ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->last_follow_up_datetime)->format('d-m-Y h:i A') : '';
            $last_is_modified = $record->last_is_modified;
            $last_is_follow_up = $record->last_is_follow_up;
            $i++;
            $labelName = '';
            if (isset($record->label_name)) {
                $leadLabelNameArr = explode(',', $record->label_name);
                $labelColorCodeArr = explode(',', $record->label_color_code);
                foreach ($leadLabelNameArr as $key => $labelLabel) {
                    $st = '';
                    if ($key % 2 == 0) {
                        $st = '<br>';
                    }
                    $labelName .= '<span class="fs-6 badge me-2" style = "background-color: transparent;color: ' . $labelColorCodeArr[$key] . ';border: 1px solid ' . $labelColorCodeArr[$key] . '">' . $labelLabel . '</span>' . $st;
                }
            }

            if (strtotime(date('d-m-Y', strtotime($follow_up_datetime))) == strtotime(date('d-m-Y')) && $record->some_day_flg == 0) {
                $follow_up_datetime = 'Today - ' . \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->last_follow_up_datetime)
                        ->format('h:i A');
            }




            $data[] = array(
                "id" => $i,
                "name" => $name,
                "phone_no" => $phone_no,
                "last_activity" => $last_activity,
                "last_activity_type" => $last_activity_type,
                "last_internal_remarks" => $last_internal_remarks,
                "last_activity_name" => $last_activity_name,
                "assigned_to_user" => $assigned_to_user,
                "assign_user_name" => $assign_user_name,
                "net_amount" => (isset($country_data->currency_symbol) && $net_amount > 0) ? $country_data->currency_symbol.' ' . $net_amount : $net_amount,
                "label_name" => $labelName,
                "estimate_status" => $estimate_status,
                "last_follow_up_datetime" => $follow_up_datetime,
                "time_ago_string" => $this->timeAgoStringFun($record->last_activity_updated_at),
                "action" => $id,
                "last_is_modified" => $last_is_modified,
                "last_is_follow_up" => $last_is_follow_up,
                "new_lead_flag" => $record->new_lead_flag,
                "lead_stage_name" => isset($record->lead_stage_name)?$record->lead_stage_name:'',
                "lead_stage_color_code" => isset($record->lead_stage_color_code)?$record->lead_stage_color_code:'',
            );
        }
        $response = array(
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecordswithFilter,
            "data" => $data
        );

        return json_encode($response);
    }

    public function upcomingIndex(Request $request)
    {

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');

        $input = $request->all();
        ## Read value
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length"); // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc

        if($columnName=='last_activity'){
            $columnName='cv.last_activity_updated_at';
        }

        // Fetch records
        $assigned_to_user = $request->get('assigned_to_user');
//        $status = $request->get('status');
        $fil_followup_lead_label_id = [];
        if($request->get('fil_followup_lead_label_id'))
            $fil_followup_lead_label_id = explode(",",$request->get('fil_followup_lead_label_id'));

        $fil_followup_lead_stage_id = $request->get('fil_followup_lead_stage_id');
        $fil_followup_customer_category_id = $request->get('fil_followup_customer_category_id');
        $fil_followup_customer_lead_id = $request->get('fil_followup_customer_lead_id');
        $fil_followup_created_user_id = $request->get('fil_followup_created_user_id');
        $fil_followup_estimate_status_id = $request->get('fil_followup_estimate_status_id');
        $fil_followup_country_id = $request->get('fil_followup_country_id');
        $fil_followup_state_id = $request->get('fil_followup_state_id');
        $fil_followup_city_name = $request->get('fil_followup_city_name');

        // Total records
        $counts = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.id')
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 0)
            ->where(DB::raw('DATE(cv.last_follow_up_datetime)'),">",date('Y-m-d'))
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                }
            })
           /* ->where(function ($query) use ($input) {
                $query->whereBetween(DB::raw("DATE_FORMAT(cv.last_follow_up_datetime, '%Y-%m-%d')"), [$input['fil_lead_date_start'], $input['fil_lead_date_end']]);
            })*/
           ->where(function ($query) use ($fil_followup_lead_label_id,$fil_followup_lead_stage_id,$fil_followup_customer_category_id,$fil_followup_customer_lead_id,$fil_followup_created_user_id,$fil_followup_estimate_status_id, $assigned_to_user,$fil_followup_country_id,$fil_followup_state_id,$fil_followup_city_name) {
               if ($fil_followup_country_id) {
                   $query->where('cv.country_id', $fil_followup_country_id);
               }
               if ($fil_followup_state_id) {
                   $query->where('cv.state_id', $fil_followup_state_id);
               }
               if ($fil_followup_city_name) {
                   $query->where('cv.city_name', $fil_followup_city_name);
               }
               if ($fil_followup_lead_label_id) {
                   $query->where('lg.id', '=', $fil_followup_lead_label_id);
               }
               if ($fil_followup_lead_stage_id) {
                   $query->where('cv.lead_stage_id', $fil_followup_lead_stage_id);
               }
               if ($fil_followup_customer_category_id != '') {
                   $query->where('cv.customer_category_id', '=', $fil_followup_customer_category_id);
               }
               if ($fil_followup_customer_lead_id != '') {
                   $query->where('cv.customer_lead_id', '=', $fil_followup_customer_lead_id);
               }
               if ($fil_followup_created_user_id != '') {
                   $query->where('cv.user_id', '=', $fil_followup_created_user_id);
               }
               if ($fil_followup_estimate_status_id != '') {
                   $query->where('cv.estimate_status', '=', $fil_followup_estimate_status_id);
               }
               if ($assigned_to_user > 0) {
                   $query->where('cv.assigned_to_user', '=', $assigned_to_user);
               }
           })
            /*->where(function ($query) use ($status, $assigned_to_user) {
                if ($status != '') {
                    $query->where('lg.id', '=', $status);
                }
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })*/
            /*->where(function ($query) use ($assigned_to_user) {
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })*/
            /*->where(function ($query) use ($search_arr) {
                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.name', 'like', '%' . $search_arr . '%');
                });

                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.phone_no', 'like', '%' . $search_arr . '%');
                });
            })*/
//            ->groupBy('cv.id')
            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->groupBy('cv.id')
                ->get();
        $totalRecords = $counts->count();

        $totalRecordswithFilter = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.id')
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 0)
            ->where(DB::raw("DATE(cv.last_follow_up_datetime)"),">", date('Y-m-d'))
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                }
            })
           /* ->where(function ($query) use ($input) {
                $query->whereBetween(DB::raw("DATE_FORMAT(cv.last_follow_up_datetime, '%Y-%m-%d')"), [$input['fil_lead_date_start'], $input['fil_lead_date_end']]);
            })*/
           ->where(function ($query) use ($fil_followup_lead_label_id,$fil_followup_lead_stage_id,$fil_followup_customer_category_id,$fil_followup_customer_lead_id,$fil_followup_created_user_id,$fil_followup_estimate_status_id, $assigned_to_user,$fil_followup_country_id,$fil_followup_state_id,$fil_followup_city_name) {
               if ($fil_followup_country_id) {
                   $query->where('cv.country_id', $fil_followup_country_id);
               }
               if ($fil_followup_state_id) {
                   $query->where('cv.state_id', $fil_followup_state_id);
               }
               if ($fil_followup_city_name) {
                   $query->where('cv.city_name', $fil_followup_city_name);
               }
               if ($fil_followup_lead_label_id) {
                   $query->where('lg.id', '=', $fil_followup_lead_label_id);
               }
               if ($fil_followup_lead_stage_id) {
                   $query->where('cv.lead_stage_id', $fil_followup_lead_stage_id);
               }
               if ($fil_followup_customer_category_id != '') {
                   $query->where('cv.customer_category_id', '=', $fil_followup_customer_category_id);
               }
               if ($fil_followup_customer_lead_id != '') {
                   $query->where('cv.customer_lead_id', '=', $fil_followup_customer_lead_id);
               }
               if ($fil_followup_created_user_id != '') {
                   $query->where('cv.user_id', '=', $fil_followup_created_user_id);
               }
               if ($fil_followup_estimate_status_id != '') {
                   $query->where('cv.estimate_status', '=', $fil_followup_estimate_status_id);
               }
               if ($assigned_to_user > 0) {
                   $query->where('cv.assigned_to_user', '=', $assigned_to_user);
               }
           })
            /*->where(function ($query) use ($status, $assigned_to_user) {
                if ($status != '') {
                    $query->where('lg.id', '=', $status);
                }
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })*/
            /*->where(function ($query) use ($assigned_to_user) {
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })*/
            ->where(function ($query) use ($search_arr) {
                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.name', 'like', '%' . $search_arr . '%');
                });

                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.phone_no', 'like', '%' . $search_arr . '%');
                });
            })
//            ->groupBy('cv.id')
            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->groupBy('cv.id')
            ->get();

        $totalRecordswithFilter = $totalRecordswithFilter->count();

        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.id','cv.name','cv.phone_no','cv.last_activity','cv.user_name','cv.assigned_to_user','cv.net_amount','cv.estimate_status','cv.last_activity_type','cv.last_internal_remarks','cv.last_activity_name','cv.last_follow_up_datetime','cv.last_is_modified','cv.last_is_follow_up','cv.some_day_flg','cv.est_currency_id','cv.estimate_status','cv.last_activity_updated_at','cv.new_lead_flag','cv.lead_stage_name','cv.lead_stage_color_code', DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code'))
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 0)
            ->where(DB::raw("DATE(cv.last_follow_up_datetime)"),">",date('Y-m-d'))
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                }
            })
           /* ->where(function ($query) use ($input) {
                $query->whereBetween(DB::raw("DATE_FORMAT(cv.last_follow_up_datetime, '%Y-%m-%d')"), [$input['fil_lead_date_start'], $input['fil_lead_date_end']]);
            })*/
           ->where(function ($query) use ($fil_followup_lead_stage_id,$fil_followup_customer_category_id,$fil_followup_customer_lead_id,$fil_followup_created_user_id,$fil_followup_estimate_status_id, $assigned_to_user,$fil_followup_country_id,$fil_followup_state_id,$fil_followup_city_name) {
               if ($fil_followup_country_id) {
                   $query->where('cv.country_id', $fil_followup_country_id);
               }
               if ($fil_followup_state_id) {
                   $query->where('cv.state_id', $fil_followup_state_id);
               }
               if ($fil_followup_city_name) {
                   $query->where('cv.city_name', $fil_followup_city_name);
               }
               /*if ($fil_followup_lead_label_id) {
                   $query->where('lg.id', '=', $fil_followup_lead_label_id);
               }*/
               if ($fil_followup_lead_stage_id) {
                   $query->where('cv.lead_stage_id', $fil_followup_lead_stage_id);
               }
               if ($fil_followup_customer_category_id != '') {
                   $query->where('cv.customer_category_id', '=', $fil_followup_customer_category_id);
               }
               if ($fil_followup_customer_lead_id != '') {
                   $query->where('cv.customer_lead_id', '=', $fil_followup_customer_lead_id);
               }
               if ($fil_followup_created_user_id != '') {
                   $query->where('cv.user_id', '=', $fil_followup_created_user_id);
               }
               if ($fil_followup_estimate_status_id != '') {
                   $query->where('cv.estimate_status', '=', $fil_followup_estimate_status_id);
               }
               if ($assigned_to_user > 0) {
                   $query->where('cv.assigned_to_user', '=', $assigned_to_user);
               }
           })
            /*->where(function ($query) use ($assigned_to_user) {
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })*/
            ->where(function ($query) use ($search_arr) {
                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.name', 'like', '%' . $search_arr . '%');
                });

                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.phone_no', 'like', '%' . $search_arr . '%');
                });
            })
            ->groupBy('cv.id')
//            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->orderBy($columnName, $columnSortOrder);
            if ($fil_followup_lead_label_id) {
                $records = $records->WhereIn("lg.id",$fil_followup_lead_label_id);
            }
            /*if ($status) {
                $records = $records->havingRaw("FIND_IN_SET('$status', GROUP_CONCAT(lg.id)) > 0");
            }*/
            $records = $records->get();


        $data = array();
        $i = 0;
        $followups['overdue'] = array();
        $followups['duetoday'] = array();
        $followups['upcoming'] = array();
        $followups['nofollowup'] = array();
        $followups['someday'] = array();
        foreach ($records as $record) {
            $country_data = [];
            if($record->est_currency_id)
                $country_data = Country::where("id", $record->est_currency_id)->select('name','currency_name','currency_code','currency_symbol')->orderBy('id', 'DESC')->get()->first();

            /*if($record->currency_name_country_id)
                $country_data = Country::where("id", $record->currency_name_country_id)->select('name','currency_name','currency_code','currency_symbol')->orderBy('id', 'DESC')->get()->first();*/
            $id = Crypt::encrypt($record->id);
            $name = $record->name;
            $phone_no = $record->phone_no;
            $last_activity = $record->last_activity;
            $assign_user_name = $record->user_name;
            $assigned_to_user = $record->assigned_to_user;
            $net_amount = $record->net_amount;
            $estimate_status = $record->estimate_status;
            $last_activity_type = $record->last_activity_type;
            $last_internal_remarks = $record->last_internal_remarks;
            $last_activity_name = $record->last_activity_name;

            $follow_up_datetime = ($record->last_follow_up_datetime && $record->last_follow_up_datetime != '0000-00-00 00:00:00') ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->last_follow_up_datetime)->format('d-m-Y h:i A') : '';
            $last_is_modified = $record->last_is_modified;
            $last_is_follow_up = $record->last_is_follow_up;
            $i++;
            $labelName = '';
            if ($record->label_name) {
                $leadLabelNameArr = explode(',', $record->label_name);
                $labelColorCodeArr = explode(',', $record->label_color_code);
                foreach ($leadLabelNameArr as $key => $labelLabel) {
                    $st = '';
                    if ($key % 2 == 0) {
                        $st = '<br>';
                    }
//                    $labelName .= '<span class="fs-6 badge me-2" style = "background-color: ' . $labelColorCodeArr[$key] . '">' . $labelLabel . '</span> ' . $st;
                    $labelName .= '<span class="fs-6 badge me-2" style = "background-color: transparent;color: ' . $labelColorCodeArr[$key] . ';border: 1px solid ' . $labelColorCodeArr[$key] . '">' . $labelLabel . '</span>' . $st;
                }
            }

            $temp = 'overdue';
            if (strtotime(date('d-m-Y', strtotime($follow_up_datetime))) > strtotime(date('d-m-Y')) && $record->some_day_flg == 0) {
                $temp = 'upcoming';
            }

            if (strtotime(date('d-m-Y', strtotime($follow_up_datetime))) == strtotime(date('d-m-Y')) && $record->some_day_flg == 0) {
                $temp = 'duetoday';
                $follow_up_datetime = 'Today - ' . \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->last_follow_up_datetime)
                        ->format('h:i A');
            }

            if ($follow_up_datetime == '' && $record->some_day_flg == 0) {
                $temp = 'nofollowup';
                $follow_up_datetime = '';
            }

            if ($record->some_day_flg == 1) {
                $temp = 'someday';
                $follow_up_datetime = '';
            }

            $data[] = array(
                "id" => $i,
                "name" => $name,
                "phone_no" => $phone_no,
                "last_activity" => $last_activity,
                "last_activity_type" => $last_activity_type,
                "last_internal_remarks" => $last_internal_remarks,
                "last_activity_name" => $last_activity_name,
                "assigned_to_user" => $assigned_to_user,
                "assign_user_name" => $assign_user_name,
                "net_amount" => (isset($country_data->currency_symbol) && $net_amount > 0) ? $country_data->currency_symbol.' ' . $net_amount : $net_amount,
                "label_name" => $labelName,
                "estimate_status" => $record->estimate_status,
                "last_follow_up_datetime" => $follow_up_datetime,
                "time_ago_string" => $this->timeAgoStringFun($record->last_activity_updated_at),
                "action" => $id,
                "last_is_modified" => $last_is_modified,
                "last_is_follow_up" => $last_is_follow_up,
                "new_lead_flag" => $record->new_lead_flag,
                "lead_stage_name" => $record->lead_stage_name,
                "lead_stage_color_code" => $record->lead_stage_color_code,
            );
        }
        $response = array(
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecordswithFilter,
            "data" => $data
        );

        return json_encode($response);
        return view('follow-up-history-new', compact('followups'));
    }

    public function overdueIndex(Request $request)
    {

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');

        $input = $request->all();
        ## Read value
        $draw = $request->post('draw');
        $start = $request->post("start");
        $rowperpage = $request->post("length"); // Rows display per page

        $columnIndex_arr = $request->post('order');
        $columnName_arr = $request->post('columns');
        $order_arr = $request->post('order');
        $search_arr = $request->post('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc

        if($columnName=='last_activity'){
            $columnName='cv.last_activity_updated_at';
        }

        // Fetch records
        $assigned_to_user = $request->post('assigned_to_user');
//        $status = $request->post('status');
        $fil_followup_lead_label_id = [];
        if($request->post('fil_followup_lead_label_id'))
            $fil_followup_lead_label_id = explode(",",$request->post('fil_followup_lead_label_id'));

        $fil_followup_lead_stage_id = $request->post('fil_followup_lead_stage_id');
        $fil_followup_customer_category_id = $request->post('fil_followup_customer_category_id');
        $fil_followup_customer_lead_id = $request->post('fil_followup_customer_lead_id');
        $fil_followup_created_user_id = $request->post('fil_followup_created_user_id');
        $fil_followup_estimate_status_id = $request->post('fil_followup_estimate_status_id');
        $fil_followup_country_id = $request->post('fil_followup_country_id');
        $fil_followup_state_id = $request->post('fil_followup_state_id');
        $fil_followup_city_name = $request->post('fil_followup_city_name');
        $opr_id_1 = ($request->post('opr_id_1'))? explode(',', $request->post('opr_id_1')): [];

        // Total records
        $totalRecords = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.id')
            ->where(function ($query) use ($opr_id_1) {
                if($opr_id_1) {
                    $query->wherein('cv.id', $opr_id_1);
                }
            })
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 0)
            ->where(DB::raw('DATE(cv.last_follow_up_datetime)'),"<",date('Y-m-d'))
            ->where('cv.last_follow_up_datetime','!=','0000-00-00 00:00:00')
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where(function($query){
                        $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                        ->orwhere('cv.user_id', '=', $this->logged_user->id);
                    });
                }
            })
            /*->where(function ($query) use ($input) {
                $query->whereBetween(DB::raw("DATE_FORMAT(cv.last_follow_up_datetime, '%Y-%m-%d')"), [$input['fil_lead_date_start'], $input['fil_lead_date_end']]);
            })*/
            ->where(function ($query) use ($fil_followup_lead_label_id,$fil_followup_lead_stage_id,$fil_followup_customer_category_id,$fil_followup_customer_lead_id,$fil_followup_created_user_id,$fil_followup_estimate_status_id, $assigned_to_user) {
                if ($fil_followup_lead_label_id) {
                    $query->where('lg.id', '=', $fil_followup_lead_label_id);
                }
                if ($fil_followup_lead_stage_id) {
                    $query->where('cv.lead_stage_id', $fil_followup_lead_stage_id);
                }
                if ($fil_followup_customer_category_id != '') {
                    $query->where('cv.customer_category_id', '=', $fil_followup_customer_category_id);
                }
                if ($fil_followup_customer_lead_id != '') {
                    $query->where('cv.customer_lead_id', '=', $fil_followup_customer_lead_id);
                }
                if ($fil_followup_created_user_id != '') {
                    $query->where('cv.user_id', '=', $fil_followup_created_user_id);
                }
                if ($fil_followup_estimate_status_id != '') {
                    $query->where('cv.estimate_status', '=', $fil_followup_estimate_status_id);
                }
                if ($assigned_to_user > 0) {
                    $query->where(function($query) use ($assigned_to_user) {
                        $query->where('cv.assigned_to_user', '=', $assigned_to_user);
//                            ->orwhere('cv.user_id', '=', $assigned_to_user); CHX
                    });
                }
            })
           /* ->where(function ($query) use ($status, $assigned_to_user) {
                if ($status != '') {
                    $query->where('lg.id', '=', $status);
                }
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })*/
            /*->where(function ($query) use ($assigned_to_user) {
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })*/
           /* ->where(function ($query) use ($search_arr) {
                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.name', 'like', '%' . $search_arr . '%');
                });

                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.phone_no', 'like', '%' . $search_arr . '%');
                });
            })*/
//            ->groupBy('cv.id')
            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->groupBy('cv.id')
            ->get();
            $totalRecords = $totalRecords->count();

        $totalRecordswithFilter = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.id')
            ->where(function ($query) use ($opr_id_1) {
                if($opr_id_1) {
                    $query->wherein('cv.id', $opr_id_1);
                }
            })
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 0)
            ->where(DB::raw("DATE(cv.last_follow_up_datetime)"),"<", date('Y-m-d'))
            ->where('cv.last_follow_up_datetime','!=','0000-00-00 00:00:00')
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                }
            })
           /* ->where(function ($query) use ($input) {
                $query->whereBetween(DB::raw("DATE_FORMAT(cv.last_follow_up_datetime, '%Y-%m-%d')"), [$input['fil_lead_date_start'], $input['fil_lead_date_end']]);
            })*/
           ->where(function ($query) use ($fil_followup_lead_label_id,$fil_followup_lead_stage_id,$fil_followup_customer_category_id,$fil_followup_customer_lead_id,$fil_followup_created_user_id,$fil_followup_estimate_status_id, $assigned_to_user,$fil_followup_country_id,$fil_followup_state_id,$fil_followup_city_name) {
               if ($fil_followup_country_id) {
                   $query->where('cv.country_id', $fil_followup_country_id);
               }
               if ($fil_followup_state_id) {
                   $query->where('cv.state_id', $fil_followup_state_id);
               }
               if ($fil_followup_city_name) {
                   $query->where('cv.city_name', $fil_followup_city_name);
               }
               if ($fil_followup_lead_label_id) {
                   $query->where('lg.id', '=', $fil_followup_lead_label_id);
               }
               if ($fil_followup_lead_stage_id) {
                   $query->where('cv.lead_stage_id', $fil_followup_lead_stage_id);
               }
               if ($fil_followup_customer_category_id != '') {
                   $query->where('cv.customer_category_id', '=', $fil_followup_customer_category_id);
               }
               if ($fil_followup_customer_lead_id != '') {
                   $query->where('cv.customer_lead_id', '=', $fil_followup_customer_lead_id);
               }
               if ($fil_followup_created_user_id != '') {
                   $query->where('cv.user_id', '=', $fil_followup_created_user_id);
               }
               if ($fil_followup_estimate_status_id != '') {
                   $query->where('cv.estimate_status', '=', $fil_followup_estimate_status_id);
               }
               if ($assigned_to_user > 0) {
                   $query->where(function($query) use ($assigned_to_user) {
                       $query->where('cv.assigned_to_user', '=', $assigned_to_user);
//                           ->orwhere('cv.user_id', '=', $assigned_to_user); CHX
                   });
               }
           })
            /*->where(function ($query) use ($status, $assigned_to_user) {
                if ($status != '') {
                    $query->where('lg.id', '=', $status);
                }
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })*/
            /*->where(function ($query) use ($assigned_to_user) {
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })*/
            ->where(function ($query) use ($search_arr) {
                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.name', 'like', '%' . $search_arr . '%');
                });

                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.phone_no', 'like', '%' . $search_arr . '%');
                });
            })
//            ->groupBy('cv.id')
            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->groupBy('cv.id')
            ->get();

        $totalRecordswithFilter = $totalRecordswithFilter->count();

        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.id','cv.name','cv.phone_no','cv.last_activity','cv.user_name','cv.assigned_to_user','cv.net_amount','cv.estimate_status','cv.last_activity_type','cv.last_internal_remarks','cv.last_activity_name','cv.last_follow_up_datetime','cv.last_is_modified','cv.last_is_follow_up','cv.some_day_flg','cv.est_currency_id','cv.estimate_status','cv.last_activity_updated_at','cv.new_lead_flag','cv.lead_stage_name','cv.lead_stage_color_code', DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code'))
            ->where(function ($query) use ($opr_id_1) {
                if($opr_id_1) {
                    $query->wherein('cv.id', $opr_id_1);
                }
            })
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 0)
            ->where(DB::raw("DATE(cv.last_follow_up_datetime)"),"<",date('Y-m-d'))
            ->where('cv.last_follow_up_datetime','!=','0000-00-00 00:00:00')
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                }
            })
            /*->where(function ($query) use ($input) {
                $query->whereBetween(DB::raw("DATE_FORMAT(cv.last_follow_up_datetime, '%Y-%m-%d')"), [$input['fil_lead_date_start'], $input['fil_lead_date_end']]);
            })*/
            ->where(function ($query) use ($fil_followup_lead_stage_id,$fil_followup_customer_category_id,$fil_followup_customer_lead_id,$fil_followup_created_user_id,$fil_followup_estimate_status_id, $assigned_to_user,$fil_followup_country_id,$fil_followup_state_id,$fil_followup_city_name) {
                if ($fil_followup_country_id) {
                    $query->where('cv.country_id', $fil_followup_country_id);
                }
                if ($fil_followup_state_id) {
                    $query->where('cv.state_id', $fil_followup_state_id);
                }
                if ($fil_followup_city_name) {
                    $query->where('cv.city_name', $fil_followup_city_name);
                }
               /* if ($fil_followup_lead_label_id) {
                    $query->where('lg.id', '=', $fil_followup_lead_label_id);
                }*/
                if ($fil_followup_lead_stage_id) {
                    $query->where('cv.lead_stage_id', $fil_followup_lead_stage_id);
                }
                if ($fil_followup_customer_category_id != '') {
                    $query->where('cv.customer_category_id', '=', $fil_followup_customer_category_id);
                }
                if ($fil_followup_customer_lead_id != '') {
                    $query->where('cv.customer_lead_id', '=', $fil_followup_customer_lead_id);
                }
                if ($fil_followup_created_user_id != '') {
                    $query->where('cv.user_id', '=', $fil_followup_created_user_id);
                }
                if ($fil_followup_estimate_status_id != '') {
                    $query->where('cv.estimate_status', '=', $fil_followup_estimate_status_id);
                }
                if ($assigned_to_user > 0) {
                    $query->where(function($query) use ($assigned_to_user) {
                        $query->where('cv.assigned_to_user', '=', $assigned_to_user);
//                            ->orwhere('cv.user_id', '=', $assigned_to_user); CHX
                    });
                }
            })
           /* ->where(function ($query) use ($assigned_to_user) {
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })*/
            ->where(function ($query) use ($search_arr) {
                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.name', 'like', '%' . $search_arr . '%');
                });

                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.phone_no', 'like', '%' . $search_arr . '%');
                });
            })
            ->groupBy('cv.id')
//            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->orderBy($columnName, $columnSortOrder);
            if ($fil_followup_lead_label_id) {
                $records = $records->WhereIn("lg.id",$fil_followup_lead_label_id);
            }
            /*if ($status) {
                $records = $records->havingRaw("FIND_IN_SET('$status', GROUP_CONCAT(lg.id)) > 0");
            }*/
            $records = $records->get();


        $data = array();
        $i = 0;
        $followups['overdue'] = array();
        $followups['duetoday'] = array();
        $followups['upcoming'] = array();
        $followups['nofollowup'] = array();
        $followups['someday'] = array();
        foreach ($records as $record) {
            $country_data = [];
            if($record->est_currency_id)
                $country_data = Country::where("id", $record->est_currency_id)->select('name','currency_name','currency_code','currency_symbol')->orderBy('id', 'DESC')->get()->first();

            $id = Crypt::encrypt($record->id);
            $name = $record->name;
            $phone_no = $record->phone_no;
            $last_activity = $record->last_activity;
            $assign_user_name = $record->user_name;
            $assigned_to_user = $record->assigned_to_user;
            $net_amount = $record->net_amount;
            $estimate_status = $record->estimate_status;
            $last_activity_type = $record->last_activity_type;
            $last_internal_remarks = $record->last_internal_remarks;
            $last_activity_name = $record->last_activity_name;

            $follow_up_datetime = ($record->last_follow_up_datetime && $record->last_follow_up_datetime != '0000-00-00 00:00:00') ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->last_follow_up_datetime)->format('d-m-Y h:i A') : '';
            $last_is_modified = $record->last_is_modified;
            $last_is_follow_up = $record->last_is_follow_up;
            $i++;
            $labelName = '';
            if ($record->label_name) {
                $leadLabelNameArr = explode(',', $record->label_name);
                $labelColorCodeArr = explode(',', $record->label_color_code);
                foreach ($leadLabelNameArr as $key => $labelLabel) {
                    $st = '';
                    if ($key % 2 == 0) {
                        $st = '<br>';
                    }
//                    $labelName .= '<span class="fs-6 badge me-2" style = "background-color: ' . $labelColorCodeArr[$key] . '">' . $labelLabel . '</span> ' . $st;
                    $labelName .= '<span class="fs-6 badge me-2" style = "background-color: transparent;color: ' . $labelColorCodeArr[$key] . ';border: 1px solid ' . $labelColorCodeArr[$key] . '">' . $labelLabel . '</span>' . $st;
                }
            }

            $temp = 'overdue';
            if (strtotime(date('d-m-Y', strtotime($follow_up_datetime))) > strtotime(date('d-m-Y')) && $record->some_day_flg == 0) {
                $temp = 'upcoming';
            }

            if (strtotime(date('d-m-Y', strtotime($follow_up_datetime))) == strtotime(date('d-m-Y')) && $record->some_day_flg == 0) {
                $temp = 'duetoday';
                $follow_up_datetime = 'Today - ' . \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->last_follow_up_datetime)
                        ->format('h:i A');
            }

            if ($follow_up_datetime == '' && $record->some_day_flg == 0) {
                $temp = 'nofollowup';
                $follow_up_datetime = '';
            }

            if ($record->some_day_flg == 1) {
                $temp = 'someday';
                $follow_up_datetime = '';
            }

            $data[] = array(
                "id" => $i,
                "name" => $name,
                "phone_no" => $phone_no,
                "last_activity" => $last_activity,
                "last_activity_type" => $last_activity_type,
                "last_internal_remarks" => $last_internal_remarks,
                "last_activity_name" => $last_activity_name,
                "assigned_to_user" => $assigned_to_user,
                "assign_user_name" => $assign_user_name,
                "net_amount" => (isset($country_data->currency_symbol) && $net_amount > 0) ? $country_data->currency_symbol .' ' .$net_amount : $net_amount,
                "label_name" => $labelName,
                "estimate_status" => $record->estimate_status,
                "last_follow_up_datetime" => $follow_up_datetime,
                "time_ago_string" => $this->timeAgoStringFun($record->last_activity_updated_at),
                "action" => $id,
                "last_is_modified" => $last_is_modified,
                "last_is_follow_up" => $last_is_follow_up,
                "new_lead_flag" => $record->new_lead_flag,
                "lead_stage_name" => $record->lead_stage_name,
                "lead_stage_color_code" => $record->lead_stage_color_code,
            );
        }
        $response = array(
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecordswithFilter,
            "data" => $data
        );

        return json_encode($response);
        return view('follow-up-history-new', compact('followups'));
    }

    public function somedayIndex(Request $request)
    {

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');

        $input = $request->all();
        ## Read value
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length"); // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc

        if($columnName=='last_activity'){
            $columnName='cv.last_activity_updated_at';
        }

        // Fetch records
        $assigned_to_user = $request->get('assigned_to_user');
//        $status = $request->get('status');
        $fil_followup_lead_label_id = [];
        if($request->get('fil_followup_lead_label_id'))
            $fil_followup_lead_label_id = explode(",",$request->get('fil_followup_lead_label_id'));

        $fil_followup_lead_stage_id = $request->get('fil_followup_lead_stage_id');
        $fil_followup_customer_category_id = $request->get('fil_followup_customer_category_id');
        $fil_followup_customer_lead_id = $request->get('fil_followup_customer_lead_id');
        $fil_followup_created_user_id = $request->get('fil_followup_created_user_id');
        $fil_followup_estimate_status_id = $request->get('fil_followup_estimate_status_id');
        $fil_followup_country_id = $request->get('fil_followup_country_id');
        $fil_followup_state_id = $request->get('fil_followup_state_id');
        $fil_followup_city_name = $request->get('fil_followup_city_name');

        // Total records
        $totalRecords = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.id')
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 1)
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
                    $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                }
            })
            ->where(function ($query) use ($fil_followup_lead_label_id,$fil_followup_lead_stage_id,$fil_followup_customer_category_id,$fil_followup_customer_lead_id,$fil_followup_created_user_id,$fil_followup_estimate_status_id, $assigned_to_user,$fil_followup_country_id,$fil_followup_state_id,$fil_followup_city_name) {
                if ($fil_followup_country_id) {
                    $query->where('cv.country_id', $fil_followup_country_id);
                }
                if ($fil_followup_state_id) {
                    $query->where('cv.state_id', $fil_followup_state_id);
                }
                if ($fil_followup_city_name) {
                    $query->where('cv.city_name', $fil_followup_city_name);
                }
                if ($fil_followup_lead_label_id) {
                    $query->where('lg.id', '=', $fil_followup_lead_label_id);
                }
                if ($fil_followup_lead_stage_id) {
                    $query->where('cv.lead_stage_id', $fil_followup_lead_stage_id);
                }
                if ($fil_followup_customer_category_id != '') {
                    $query->where('cv.customer_category_id', '=', $fil_followup_customer_category_id);
                }
                if ($fil_followup_customer_lead_id != '') {
                    $query->where('cv.customer_lead_id', '=', $fil_followup_customer_lead_id);
                }
                if ($fil_followup_created_user_id != '') {
                    $query->where('cv.user_id', '=', $fil_followup_created_user_id);
                }
                if ($fil_followup_estimate_status_id != '') {
                    $query->where('cv.estimate_status', '=', $fil_followup_estimate_status_id);
                }
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })
            /*->where(function ($query) use ($status, $assigned_to_user) {
                if ($status != '') {
                    $query->where('lg.id', '=', $status);
                }
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })*/
            /*->where(function ($query) use ($assigned_to_user) {
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })*/
            /*->where(function ($query) use ($search_arr) {
                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.name', 'like', '%' . $search_arr . '%');
                });

                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.phone_no', 'like', '%' . $search_arr . '%');
                });
            })*/
//            ->groupBy('cv.id')
            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->groupBy('cv.id')
            ->get();
        $totalRecords = $totalRecords->count();

        $totalRecordswithFilter = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.id')
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 1)
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
                    $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                }
            })
            ->where(function ($query) use ($fil_followup_lead_label_id,$fil_followup_lead_stage_id,$fil_followup_customer_category_id,$fil_followup_customer_lead_id,$fil_followup_created_user_id,$fil_followup_estimate_status_id, $assigned_to_user,$fil_followup_country_id,$fil_followup_state_id,$fil_followup_city_name) {
                if ($fil_followup_country_id) {
                    $query->where('cv.country_id', $fil_followup_country_id);
                }
                if ($fil_followup_state_id) {
                    $query->where('cv.state_id', $fil_followup_state_id);
                }
                if ($fil_followup_city_name) {
                    $query->where('cv.city_name', $fil_followup_city_name);
                }
                if ($fil_followup_lead_label_id) {
                    $query->where('lg.id', '=', $fil_followup_lead_label_id);
                }
                if ($fil_followup_lead_stage_id) {
                    $query->where('cv.lead_stage_id', $fil_followup_lead_stage_id);
                }
                if ($fil_followup_customer_category_id != '') {
                    $query->where('cv.customer_category_id', '=', $fil_followup_customer_category_id);
                }
                if ($fil_followup_customer_lead_id != '') {
                    $query->where('cv.customer_lead_id', '=', $fil_followup_customer_lead_id);
                }
                if ($fil_followup_created_user_id != '') {
                    $query->where('cv.user_id', '=', $fil_followup_created_user_id);
                }
                if ($fil_followup_estimate_status_id != '') {
                    $query->where('cv.estimate_status', '=', $fil_followup_estimate_status_id);
                }
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })
            /*->where(function ($query) use ($status, $assigned_to_user) {
                if ($status != '') {
                    $query->where('lg.id', '=', $status);
                }
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })*/
            /*->where(function ($query) use ($assigned_to_user) {
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })*/
            ->where(function ($query) use ($search_arr) {
                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.name', 'like', '%' . $search_arr . '%');
                });

                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.phone_no', 'like', '%' . $search_arr . '%');
                });
            })
//            ->groupBy('cv.id')
            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->groupBy('cv.id')
            ->get();

        $totalRecordswithFilter = $totalRecordswithFilter->count();

        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.*', DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code'))
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 1)
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
                    $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                }
            })
            ->where(function ($query) use ($fil_followup_lead_stage_id,$fil_followup_customer_category_id,$fil_followup_customer_lead_id,$fil_followup_created_user_id,$fil_followup_estimate_status_id, $assigned_to_user,$fil_followup_country_id,$fil_followup_state_id,$fil_followup_city_name) {
                if ($fil_followup_country_id) {
                    $query->where('cv.country_id', $fil_followup_country_id);
                }
                if ($fil_followup_state_id) {
                    $query->where('cv.state_id', $fil_followup_state_id);
                }
                if ($fil_followup_city_name) {
                    $query->where('cv.city_name', $fil_followup_city_name);
                }
               /* if ($fil_followup_lead_label_id) {
                    $query->where('lg.id', '=', $fil_followup_lead_label_id);
                }*/
                if ($fil_followup_lead_stage_id) {
                    $query->where('cv.lead_stage_id', $fil_followup_lead_stage_id);
                }
                if ($fil_followup_customer_category_id != '') {
                    $query->where('cv.customer_category_id', '=', $fil_followup_customer_category_id);
                }
                if ($fil_followup_customer_lead_id != '') {
                    $query->where('cv.customer_lead_id', '=', $fil_followup_customer_lead_id);
                }
                if ($fil_followup_created_user_id != '') {
                    $query->where('cv.user_id', '=', $fil_followup_created_user_id);
                }
                if ($fil_followup_estimate_status_id != '') {
                    $query->where('cv.estimate_status', '=', $fil_followup_estimate_status_id);
                }
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })
           /* ->where(function ($query) use ($assigned_to_user) {
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })*/
            ->where(function ($query) use ($search_arr) {
                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.name', 'like', '%' . $search_arr . '%');
                });

                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.phone_no', 'like', '%' . $search_arr . '%');
                });
            })
            ->groupBy('cv.id')
//            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->orderBy($columnName, $columnSortOrder);
            if ($fil_followup_lead_label_id) {
                $records = $records->WhereIn("lg.id",$fil_followup_lead_label_id);
            }
            /*if ($status) {
                $records = $records->havingRaw("FIND_IN_SET('$status', GROUP_CONCAT(lg.id)) > 0");
            }*/
            $records = $records->get();


        $data = array();
        $i = 0;
        $followups['overdue'] = array();
        $followups['duetoday'] = array();
        $followups['upcoming'] = array();
        $followups['nofollowup'] = array();
        $followups['someday'] = array();
        foreach ($records as $record) {
            $country_data = [];
            if($record->est_currency_id)
                $country_data = Country::where("id", $record->est_currency_id)->select('name','currency_name','currency_code','currency_symbol')->orderBy('id', 'DESC')->get()->first();

            /*if($record->currency_name_country_id)
                $country_data = Country::where("id", $record->currency_name_country_id)->select('name','currency_name','currency_code','currency_symbol')->orderBy('id', 'DESC')->get()->first();*/
            $id = Crypt::encrypt($record->id);
            $customer_type = $record->customer_type;
            $name = $record->name;
            $lead_origin = $record->lead_origin;
            $lead_category = $record->lead_category;
            $email = $record->email;
            $phone_no = $record->phone_no;
            $address = $record->address;
            $pincode = $record->pincode;
            $description = $record->description;
            $status = $record->status;
            $country_name = $record->country_name;
            $state_name = $record->state_name;
            $city_name = $record->city_name;
            $last_activity = $record->last_activity;
            $assign_user_name = $record->user_name;
            $assigned_to_user = $record->assigned_to_user;
            $net_amount = $record->net_amount;
            $estimate_status = $record->estimate_status;
            $last_activity_type = $record->last_activity_type;
            $last_internal_remarks = $record->last_internal_remarks;
            $last_activity_name = $record->last_activity_name;
            $date_added = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)
                ->format('d-m-Y h:i A');

            $follow_up_datetime = ($record->last_follow_up_datetime && $record->last_follow_up_datetime != '0000-00-00 00:00:00') ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->last_follow_up_datetime)->format('d-m-Y h:i A') : '';
            $last_is_modified = $record->last_is_modified;
            $last_is_follow_up = $record->last_is_follow_up;
            $i++;
            $labelName = '';
            if ($record->label_name) {
                $leadLabelNameArr = explode(',', $record->label_name);
                $labelColorCodeArr = explode(',', $record->label_color_code);
                foreach ($leadLabelNameArr as $key => $labelLabel) {
                    $st = '';
                    if ($key % 2 == 0) {
                        $st = '<br>';
                    }
//                    $labelName .= '<span class="fs-6 badge me-2" style = "background-color: ' . $labelColorCodeArr[$key] . '">' . $labelLabel . '</span> ' . $st;
                    $labelName .= '<span class="fs-6 badge me-2" style = "background-color: transparent;color: ' . $labelColorCodeArr[$key] . ';border: 1px solid ' . $labelColorCodeArr[$key] . '">' . $labelLabel . '</span>' . $st;
                }
            }

            $temp = 'overdue';
            if (strtotime(date('d-m-Y', strtotime($follow_up_datetime))) > strtotime(date('d-m-Y')) && $record->some_day_flg == 0) {
                $temp = 'upcoming';
            }

            if (strtotime(date('d-m-Y', strtotime($follow_up_datetime))) == strtotime(date('d-m-Y')) && $record->some_day_flg == 0) {
                $temp = 'duetoday';
                $follow_up_datetime = 'Today - ' . \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->last_follow_up_datetime)
                        ->format('h:i A');
            }

            if ($follow_up_datetime == '' && $record->some_day_flg == 0) {
                $temp = 'nofollowup';
                $follow_up_datetime = '';
            }

            if ($record->some_day_flg == 1) {
                $temp = 'someday';
                $follow_up_datetime = '';
            }

            $data[] = array(
                "id" => $i,
                "name" => $name,
                "phone_no" => $phone_no,
                "last_activity" => $last_activity,
                "last_activity_type" => $last_activity_type,
                "last_internal_remarks" => $last_internal_remarks,
                "last_activity_name" => $last_activity_name,
                "assigned_to_user" => $assigned_to_user,
                "assign_user_name" => $assign_user_name,
                "net_amount" => (isset($country_data->currency_symbol) && $net_amount > 0) ? $country_data->currency_symbol .' ' .$net_amount : $net_amount,
                "label_name" => $labelName,
                "estimate_status" => $record->estimate_status,
                "last_follow_up_datetime" => $follow_up_datetime,
                "time_ago_string" => $this->timeAgoStringFun($record->last_activity_updated_at),
                "action" => $id,
                "last_is_modified" => $last_is_modified,
                "last_is_follow_up" => $last_is_follow_up,
                "new_lead_flag" => $record->new_lead_flag,
                "lead_stage_name" => $record->lead_stage_name,
                "lead_stage_color_code" => $record->lead_stage_color_code,
            );
        }
        $response = array(
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecordswithFilter,
            "data" => $data
        );

        return json_encode($response);
        return view('follow-up-history-new', compact('followups'));
    }

    public function neverFollowUpIndex(Request $request)
    {

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');

        $input = $request->all();
        ## Read value
        $draw = $request->post('draw');
        $start = $request->post("start");
        $rowperpage = $request->post("length"); // Rows display per page

        $columnIndex_arr = $request->post('order');
        $columnName_arr = $request->post('columns');
        $order_arr = $request->post('order');
        $search_arr = $request->post('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc

        if($columnName=='last_activity'){
            $columnName='cv.last_activity_updated_at';
        }

        // Fetch records
        $assigned_to_user = $request->post('assigned_to_user');
//        $status = $request->post('status');
        $fil_followup_lead_label_id = [];
        if($request->post('fil_followup_lead_label_id'))
            $fil_followup_lead_label_id = explode(",",$request->post('fil_followup_lead_label_id'));

        $fil_followup_lead_stage_id = $request->post('fil_followup_lead_stage_id');
        $fil_followup_customer_category_id = $request->post('fil_followup_customer_category_id');
        $fil_followup_customer_lead_id = $request->post('fil_followup_customer_lead_id');
        $fil_followup_created_user_id = $request->post('fil_followup_created_user_id');
        $fil_followup_estimate_status_id = $request->post('fil_followup_estimate_status_id');
        $fil_followup_country_id = $request->post('fil_followup_country_id');
        $fil_followup_state_id = $request->post('fil_followup_state_id');
        $fil_followup_city_name = $request->post('fil_followup_city_name');
        $opr_id_3 = ($request->post('opr_id_3'))? explode(',', $request->post('opr_id_3')): [];

        // Total records
        $totalRecords = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.id')
            ->where(function ($query) use ($opr_id_3) {
                if($opr_id_3) {
                    $query->wherein('cv.id', $opr_id_3);
                }
            })
            ->where('cv.company_id', $this->company_id)
//            ->where('cv.some_day_flg', 0)
            ->where(function ($query) use ($user_perm) {
                $query->where('cv.last_follow_up_datetime','=','0000-00-00 00:00:00');
                $query->orWhereNull('cv.last_follow_up_datetime');
            })
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                }
            })
            ->where(function ($query) use ($fil_followup_lead_label_id,$fil_followup_lead_stage_id,$fil_followup_customer_category_id,$fil_followup_customer_lead_id,$fil_followup_created_user_id,$fil_followup_estimate_status_id, $assigned_to_user,$fil_followup_country_id,$fil_followup_state_id,$fil_followup_city_name) {
                if ($fil_followup_country_id) {
                    $query->where('cv.country_id', $fil_followup_country_id);
                }
                if ($fil_followup_state_id) {
                    $query->where('cv.state_id', $fil_followup_state_id);
                }
                if ($fil_followup_city_name) {
                    $query->where('cv.city_name', $fil_followup_city_name);
                }
                if ($fil_followup_lead_label_id) {
                    $query->where('lg.id', '=', $fil_followup_lead_label_id);
                }
                if ($fil_followup_lead_stage_id) {
                    $query->where('cv.lead_stage_id', $fil_followup_lead_stage_id);
                }
                if ($fil_followup_customer_category_id != '') {
                    $query->where('cv.customer_category_id', '=', $fil_followup_customer_category_id);
                }
                if ($fil_followup_customer_lead_id != '') {
                    $query->where('cv.customer_lead_id', '=', $fil_followup_customer_lead_id);
                }
                if ($fil_followup_created_user_id != '') {
                    $query->where('cv.user_id', '=', $fil_followup_created_user_id);
                }
                if ($fil_followup_estimate_status_id != '') {
                    $query->where('cv.estimate_status', '=', $fil_followup_estimate_status_id);
                }
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })
            /*->where(function ($query) use ($assigned_to_user) {
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })*/
           /* ->where(function ($query) use ($search_arr) {
                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.name', 'like', '%' . $search_arr . '%');
                });

                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.phone_no', 'like', '%' . $search_arr . '%');
                });
            })*/
//            ->groupBy('cv.id')
            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->groupBy('cv.id')
            ->get();
        $totalRecords = $totalRecords->count();

        $totalRecordswithFilter = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.id')
            ->where(function ($query) use ($opr_id_3) {
                if($opr_id_3) {
                    $query->wherein('cv.id', $opr_id_3);
                }
            })
            ->where('cv.company_id', $this->company_id)
//            ->where('cv.some_day_flg', 0)
            ->where(function ($query) use ($user_perm) {
                $query->where('cv.last_follow_up_datetime','=','0000-00-00 00:00:00');
                $query->orWhereNull('cv.last_follow_up_datetime');
            })

            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                }
            })
            ->where(function ($query) use ($fil_followup_lead_label_id,$fil_followup_lead_stage_id,$fil_followup_customer_category_id,$fil_followup_customer_lead_id,$fil_followup_created_user_id,$fil_followup_estimate_status_id, $assigned_to_user,$fil_followup_country_id,$fil_followup_state_id,$fil_followup_city_name) {
                if ($fil_followup_country_id) {
                    $query->where('cv.country_id', $fil_followup_country_id);
                }
                if ($fil_followup_state_id) {
                    $query->where('cv.state_id', $fil_followup_state_id);
                }
                if ($fil_followup_city_name) {
                    $query->where('cv.city_name', $fil_followup_city_name);
                }
                if ($fil_followup_lead_label_id) {
                    $query->where('lg.id', '=', $fil_followup_lead_label_id);
                }
                if ($fil_followup_lead_stage_id) {
                    $query->where('cv.lead_stage_id', $fil_followup_lead_stage_id);
                }
                if ($fil_followup_customer_category_id != '') {
                    $query->where('cv.customer_category_id', '=', $fil_followup_customer_category_id);
                }
                if ($fil_followup_customer_lead_id != '') {
                    $query->where('cv.customer_lead_id', '=', $fil_followup_customer_lead_id);
                }
                if ($fil_followup_created_user_id != '') {
                    $query->where('cv.user_id', '=', $fil_followup_created_user_id);
                }
                if ($fil_followup_estimate_status_id != '') {
                    $query->where('cv.estimate_status', '=', $fil_followup_estimate_status_id);
                }
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })
            /*->where(function ($query) use ($assigned_to_user) {
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })*/
            ->where(function ($query) use ($search_arr) {
                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.name', 'like', '%' . $search_arr . '%');
                });

                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.phone_no', 'like', '%' . $search_arr . '%');
                });
            })
//            ->groupBy('cv.id')
            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->groupBy('cv.id')
            ->get();

        $totalRecordswithFilter = $totalRecordswithFilter->count();

        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.id','cv.name','cv.phone_no','cv.last_activity','cv.user_name','cv.assigned_to_user','cv.net_amount','cv.estimate_status','cv.last_activity_type','cv.last_internal_remarks','cv.last_activity_name','cv.last_follow_up_datetime','cv.last_is_modified','cv.last_is_follow_up','cv.some_day_flg','cv.est_currency_id','cv.estimate_status','cv.last_activity_updated_at','cv.new_lead_flag','cv.lead_stage_name','cv.lead_stage_color_code', DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code'))
            ->where(function ($query) use ($opr_id_3) {
                if($opr_id_3) {
                    $query->wherein('cv.id', $opr_id_3);
                }
            })
            ->where('cv.company_id', $this->company_id)
//            ->where('cv.some_day_flg', 0)
            ->where(function ($query) use ($user_perm) {
                $query->where('cv.last_follow_up_datetime','=','0000-00-00 00:00:00');
                $query->orWhereNull('cv.last_follow_up_datetime');
            })
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                }
            })
            ->where(function ($query) use ($fil_followup_lead_stage_id,$fil_followup_customer_category_id,$fil_followup_customer_lead_id,$fil_followup_created_user_id,$fil_followup_estimate_status_id, $assigned_to_user,$fil_followup_country_id,$fil_followup_state_id,$fil_followup_city_name) {
                if ($fil_followup_country_id) {
                    $query->where('cv.country_id', $fil_followup_country_id);
                }
                if ($fil_followup_state_id) {
                    $query->where('cv.state_id', $fil_followup_state_id);
                }
                if ($fil_followup_city_name) {
                    $query->where('cv.city_name', $fil_followup_city_name);
                }
              /*  if ($fil_followup_lead_label_id) {
                    $query->where('lg.id', '=', $fil_followup_lead_label_id);
                }*/
                if ($fil_followup_lead_stage_id) {
                    $query->where('cv.lead_stage_id', $fil_followup_lead_stage_id);
                }
                if ($fil_followup_customer_category_id != '') {
                    $query->where('cv.customer_category_id', '=', $fil_followup_customer_category_id);
                }
                if ($fil_followup_customer_lead_id != '') {
                    $query->where('cv.customer_lead_id', '=', $fil_followup_customer_lead_id);
                }
                if ($fil_followup_created_user_id != '') {
                    $query->where('cv.user_id', '=', $fil_followup_created_user_id);
                }
                if ($fil_followup_estimate_status_id != '') {
                    $query->where('cv.estimate_status', '=', $fil_followup_estimate_status_id);
                }
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })
           /* ->where(function ($query) use ($assigned_to_user) {
                if ($assigned_to_user > 0) {
                    $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                }
            })*/
            ->where(function ($query) use ($search_arr) {
                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.name', 'like', '%' . $search_arr . '%');
                });

                $query->orWhere(function ($query) use ($search_arr) {
                    $query->where('cv.phone_no', 'like', '%' . $search_arr . '%');
                });
            })
            ->groupBy('cv.id')
//            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->skip($start)
            ->take($rowperpage)
            ->orderBy($columnName, $columnSortOrder);
            if ($fil_followup_lead_label_id) {
                $records = $records->WhereIn("lg.id",$fil_followup_lead_label_id);
            }
            /*if ($status) {
                $records = $records->havingRaw("FIND_IN_SET('$status', GROUP_CONCAT(lg.id)) > 0");
            }*/
            $records = $records->get();


        $data = array();
        $i = 0;
        $followups['overdue'] = array();
        $followups['duetoday'] = array();
        $followups['upcoming'] = array();
        $followups['nofollowup'] = array();
        $followups['someday'] = array();
        foreach ($records as $record) {
            $country_data = [];
            if($record->est_currency_id)
                $country_data = Country::where("id", $record->est_currency_id)->select('name','currency_name','currency_code','currency_symbol')->orderBy('id', 'DESC')->get()->first();

            /*if($record->currency_name_country_id)
                $country_data = Country::where("id", $record->currency_name_country_id)->select('name','currency_name','currency_code','currency_symbol')->orderBy('id', 'DESC')->get()->first();*/
            $id = Crypt::encrypt($record->id);
            $name = $record->name;
            $phone_no = $record->phone_no;
            $last_activity = $record->last_activity;
            $assign_user_name = $record->user_name;
            $assigned_to_user = $record->assigned_to_user;
            $net_amount = $record->net_amount;
            $estimate_status = $record->estimate_status;
            $last_activity_type = $record->last_activity_type;
            $last_internal_remarks = $record->last_internal_remarks;
            $last_activity_name = $record->last_activity_name;
            /*$date_added = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)
                ->format('d-m-Y h:i A');*/

            $follow_up_datetime = ($record->last_follow_up_datetime && $record->last_follow_up_datetime != '0000-00-00 00:00:00') ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->last_follow_up_datetime)->format('d-m-Y h:i A') : '';
            $last_is_modified = $record->last_is_modified;
            $last_is_follow_up = $record->last_is_follow_up;
            $i++;
            $labelName = '';
            if ($record->label_name) {
                $leadLabelNameArr = explode(',', $record->label_name);
                $labelColorCodeArr = explode(',', $record->label_color_code);
                foreach ($leadLabelNameArr as $key => $labelLabel) {
                    $st = '';
                    if ($key % 2 == 0) {
                        $st = '<br>';
                    }
//                    $labelName .= '<span class="fs-6 badge me-2" style = "background-color: ' . $labelColorCodeArr[$key] . '">' . $labelLabel . '</span> ' . $st;
                    $labelName .= '<span class="fs-6 badge me-2" style = "background-color: transparent;color: ' . $labelColorCodeArr[$key] . ';border: 1px solid ' . $labelColorCodeArr[$key] . '">' . $labelLabel . '</span>' . $st;
                }
            }

            $temp = 'overdue';
            if (strtotime(date('d-m-Y', strtotime($follow_up_datetime))) > strtotime(date('d-m-Y')) && $record->some_day_flg == 0) {
                $temp = 'upcoming';
            }

            if (strtotime(date('d-m-Y', strtotime($follow_up_datetime))) == strtotime(date('d-m-Y')) && $record->some_day_flg == 0) {
                $temp = 'duetoday';
                $follow_up_datetime = 'Today - ' . \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->last_follow_up_datetime)
                        ->format('h:i A');
            }

            if ($follow_up_datetime == '' && $record->some_day_flg == 0) {
                $temp = 'nofollowup';
                $follow_up_datetime = '';
            }

            if ($record->some_day_flg == 1) {
                $temp = 'someday';
                $follow_up_datetime = '';
            }

            $data[] = array(
                "id" => $i,
                "name" => $name,
                "phone_no" => $phone_no,
                "last_activity" => $last_activity,
                "last_activity_type" => $last_activity_type,
                "last_internal_remarks" => $last_internal_remarks,
                "last_activity_name" => $last_activity_name,
                "assigned_to_user" => $assigned_to_user,
                "assign_user_name" => $assign_user_name,
                "net_amount" => (isset($country_data->currency_symbol) && $net_amount > 0) ? $country_data->currency_symbol .' ' .$net_amount : $net_amount,
                "label_name" => $labelName,
                "estimate_status" => $record->estimate_status,
                "last_follow_up_datetime" => $follow_up_datetime,
                "time_ago_string" => $this->timeAgoStringFun($record->last_activity_updated_at),
                "action" => $id,
                "last_is_modified" => $last_is_modified,
                "last_is_follow_up" => $last_is_follow_up,
                "new_lead_flag" => $record->new_lead_flag,
                "lead_stage_name" => $record->lead_stage_name,
                "lead_stage_color_code" => $record->lead_stage_color_code,
            );
        }
        $response = array(
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecordswithFilter,
            "data" => $data
        );

        return json_encode($response);
        return view('follow-up-history-new', compact('followups'));
    }
}
