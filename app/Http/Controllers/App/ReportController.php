<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\admin\ViewUserData;
use App\Models\Estimate;
use App\Models\ProposalTemplates;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Mpdf\Output\Destination;
use Session;

class ReportController extends Controller
{
    protected $logged_user = null;
    protected $company_id = 0;
    protected $segment = null;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->logged_user = \Illuminate\Support\Facades\Auth::user();
            $this->company_id = ($this->logged_user->company_id) ? $this->logged_user->company_id : $this->logged_user->id;
            $this->user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
            $this->segment = $request->segment(1);
            return $next($request);
        });
    }

    public function index(Request $request)
    {

        $user_list = User::select(["name", "id", "email", "mobile_no"])
            ->where('invite_status', 1)
            ->where(function ($query) {
                $query->orwhere('company_id', $this->company_id);
                $query->orwhere('id', $this->company_id);
            })
            ->get();


        if ($request->ajax()) {
            $input = $request->all();
            $fil_assignee = $input['fil_assignee'];
            $fil_report_type = $input['fil_report_type'];
            $dateArr = explode("_", $input['fil_date']);
            $userLists = User::query()->select(["name", "id", "email"])
                ->where('invite_status', 1)
                ->where(function ($query) use ($fil_assignee) {
                    if ($fil_assignee > 0) {
                        $query->where('id', $fil_assignee);
                    }else{
                        $query->where('company_id', $this->company_id);
                        $query->orwhere('id', $this->company_id);
                    }
                    /*if ($this->logged_user->company_id > 0) {
                        $query->where('company_id', $this->company_id);
                    }*/

                   /* else {
                        $query->whereRaw('id IN  (' . Session::get("get_data_by_id") . ')');
                        if(!$this->logged_user->company_id)
                            $query->orWhere('id', $this->company_id);

                        if($this->logged_user->company_id)
                            $query->orWhere('id', $this->logged_user->id);
                        // $query->orWhere('id', $this->company_id);
                    }*/
                })
                ->get();
            $user_cnt = $userLists->count();
            $reportArr = [];
            foreach ($userLists as $userList) {
                $widgets = [];

                $widgetsArray = DB::table(function ($query) {
                    $query->select(DB::raw('DISTINCT estimate_no, status'),'estimate_date','company_id','customer_id')
                        ->from('estimates');
                }, 'subquery')
                    ->leftjoin('customers_views', 'subquery.customer_id', '=', 'customers_views.id')
                    ->where(function ($query) {
                        if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                            $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
                            $query->orWhere('customers_views.user_id', '=', $this->logged_user->id);
                        }
                    })
                    ->where(function ($query) use ($dateArr) {
                        $query->whereBetween(DB::raw("subquery.estimate_date"), [$dateArr[0], $dateArr[1]]);
                    })
                   /* ->where(function ($query) use ($userList, $fil_assignee) {
                        $query->where('user_id', $userList->id);
                    })*/

                    ->where(function ($query) use ($userList, $fil_assignee) {
                        /*if ($fil_assignee == 0) {
                            $query->where('customers_views.company_id', '=', $this->company_id);
                        }*/

                       // if ($fil_assignee > 0) {
                            $query->where('customers_views.assigned_to_user', $userList->id);
                            $query->where('customers_views.company_id', $this->company_id);
                       // }
                    })
                    ->where('subquery.company_id', '=', $this->company_id)
                    ->where('subquery.status', '!=', '')
                    ->select('subquery.status', DB::raw('COUNT(subquery.status) as widget_total'))
                    ->groupBy('subquery.status')
                    ->get();
                $widgets = json_decode(json_encode($widgetsArray), true);
                /*$widgetsArray = DB::table('estimates')
                    ->select(DB::raw('COUNT(DISTINCT estimate_no) AS widget_total'), 'status')
                    ->where('company_id', $this->company_id)
                    ->where('status', "!=","")
                    ->where(function ($query) use ($dateArr) {
                        $query->whereBetween(DB::raw("estimate_date"), [$dateArr[0], $dateArr[1]]);
                    })
                    ->where(function ($query) use ($userList, $fil_assignee) {
                        $query->where('user_id', $userList->id);
                    })
                    ->groupBy('status')
                    ->get()->toArray();
                $widgets = json_decode(json_encode($widgetsArray), true);*/
                /*$widgets = Estimate::query()
                    ->groupBy('status')
                    ->where('company_id', $this->company_id)
                    ->where(function ($query) use ($dateArr) {
                        $query->whereBetween(DB::raw("DATE_FORMAT(estimate_date, '%Y-%m-%d')"), [$dateArr[0], $dateArr[1]]);
                    })
                    ->where(function ($query) use ($userList, $fil_assignee) {
                        $query->where('user_id', $userList->id);
                    })
                    ->select('status', DB::raw('COUNT(status) as widget_total'))->get()->toArray();*/
                $a = [
                    ['status' => 'Sent', 'widget_total' => 0],
                    ['status' => 'Inprogress', 'widget_total' => 0],
                    ['status' => 'Accept', 'widget_total' => 0],
                    ['status' => 'Decline', 'widget_total' => 0],
                    ['status' => 'Draft', 'widget_total' => 0]
                ];
                $x = array_column($widgets, 'status');
                $total = 0 + array_sum(array_column($widgets, 'widget_total'));
                foreach ($a as $value) {
                    if (!in_array($value['status'], $x)) {
                        $widgets[] = $value;
                    }
                }
                usort($widgets, function ($a, $b) {
                    return $a['status'] <=> $b['status'];
                });
                $widgets[] = array("status" => "Total", "widget_total" => $total);


                $sales_performance = DB::table('sales_person_performances')
                    ->where(function ($query) use ($dateArr) {
                        $query->whereBetween(DB::raw("DATE_FORMAT(performance_date, '%Y-%m-%d')"), [$dateArr[0], $dateArr[1]]);
                    })
                    //->where('company_id', $this->company_id)

                    ->where(function ($query) use ($userList, $fil_assignee) {
                        $query->where('user_id', $userList->id);
                    })
                    ->select(DB::raw("SUM(total_task) as total_task"), DB::raw("SUM(completed_task) as completed_task"), DB::raw("COUNT(id) as total_record"), DB::raw("SUM(daily_performance) as daily_performance"))
                    ->get()->toArray();

                $count_all = DB::table('sales_person_performances')
                    ->where('total_task', '>', 0)
                    ->where(function ($query) use ($dateArr) {
                        $query->whereBetween(DB::raw("DATE_FORMAT(performance_date, '%Y-%m-%d')"), [$dateArr[0], $dateArr[1]]);
                    })
                    ->where(function ($query) use ($userList) {
                        $query->where('user_id', $userList->id);
                    })
                    ->select('id')->count();

                $total_task = array_column($sales_performance, 'total_task');
                $total_record = array_column($sales_performance, 'total_record');
                $completed_task = array_column($sales_performance, 'completed_task');
                $total_daily_performance = array_column($sales_performance, 'daily_performance');

                $reportArr["summaryData"][$userList->id]["name"] = $userList->name;
                $reportArr["summaryData"][$userList->id]["widget"] = $widgets;
                $reportArr["summaryData"][$userList->id]["completed_task"] = (int)$completed_task[0];
                $reportArr["summaryData"][$userList->id]["total_task"] = (int)$total_task[0];
                $reportArr["summaryData"][$userList->id]["completion_ratio"] = ($total_task[0]) ? (float)number_format(($completed_task[0] / $total_task[0]) * 100, 2) : 0;
                $reportArr["summaryData"][$userList->id]["performance"] = ($count_all > 0) ? (float)number_format($total_daily_performance[0] / $count_all, 2) : 0;
            }

            /*$estimateList = DB::table('estimates')
                ->leftJoin('users', 'estimates.sales_person_id', '=', 'users.id')
                ->where(function ($query) use ($dateArr) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(estimates.estimate_date, '%Y-%m-%d')"), [$dateArr[0], $dateArr[1]]);
                })
                ->where('estimates.company_id', $this->company_id)
                ->where(function ($query) use ($userList, $fil_assignee) {
                    if ($fil_assignee > 0) {
                        $query->where('estimates.user_id', $userList->id);
                    } else {
                        $query->whereRaw('estimates.user_id IN  (' . Session::get("get_data_by_id") . ')');
                        $query->orWhere('estimates.user_id', $this->company_id);
                    }
                })
                ->select(array('estimates.id', 'estimates.customer_name', 'estimates.estimate_no', 'estimates.estimate_date', 'estimates.addless_amount', 'estimates.net_amount', DB::raw("RIGHT(estimates.customer_address, 10) as mobile_no"), "estimates.status", "users.name as created_by", DB::raw("DATE_FORMAT(estimates.estimate_date, '%d-%m-%Y') as estimate_dates")))
                ->orderBy("estimates.id", 'desc')
                ->get()->toArray();

            foreach ($estimateList as $key_value => $est_value) {
                $estimateList[$key_value]->followupDetails = [];
                if ($fil_report_type == 1) {
                    $event = DB::table('events')
                        ->leftjoin('estimates', 'events.estimate_id', 'estimates.id')
                        ->leftJoin('users', 'events.user_id', 'users.id')
                        ->where([
                            ['events.estimate_id', '=', $est_value->id],
                            ['events.event_type', '=', 'estimate'],
                        ])
                        ->select('events.*', 'estimates.estimate_no as estimate_no', DB::raw("DATE_FORMAT(events.start_date, '%d-%m-%Y') as display_date"), DB::raw("DATE_FORMAT(events.start_date, '%d-%m-%Y %H:%i:%s') as start_date"), 'estimates.customer_name', DB::raw("RIGHT(estimates.customer_address, 10) as mobile_no"), 'estimates.status', 'users.name as user_name', DB::raw("DATE_FORMAT(events.created_at, '%d-%m-%Y  %H:%i:%s') as created_at")) // 'users.name',
                        ->orderBy('events.id', 'DESC')
                        ->orderBy('events.start_date', 'DESC')
                        ->get()->toArray();
                    $estimateList[$key_value]->followupDetails = $event;
                }
            }*/

            /*$sales_performance_chart = DB::table('sales_person_performances')
                ->groupBy('performance_date')
                ->where('company_id', $this->company_id)
                ->where(function ($query) use ($dateArr) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(performance_date, '%Y-%m-%d')"), [$dateArr[0], $dateArr[1]]);
                })
                ->where(function ($query) use ($userList) {

                    $query->where('user_id', $userList->id);
                })
                ->where(function ($query) use ($fil_assignee) {
                    if ($fil_assignee > 0) {
                        $query->where('user_id', $fil_assignee);
                    } else {
                        $query->whereRaw('user_id IN  (' . Session::get("get_data_by_id") . ')');
                        $query->orWhere('user_id', $this->logged_user->id);
                    }
                })
                ->select("performance_date", "daily_performance", DB::raw("SUM(total_task) as total_task"), DB::raw("SUM(completed_task) as completed_task"), DB::raw("daily_performance as daily_performances"), DB::raw("DATE_FORMAT(performance_date, '%d %b, %y') as performance_date"))
                ->having(DB::raw('daily_performance'), '>', 0)
                ->get()->toArray();

            $performance_date_chart = array_column($sales_performance_chart, 'performance_date');
            $daily_performance_chart = array_column($sales_performance_chart, 'daily_performances');
            $reportArr["chart"]["performance_date_chart"] = $performance_date_chart;
            $reportArr["chart"]["daily_performance_chart"] = $daily_performance_chart;
            // $reportArr["estimateList"] = $estimateList;*/


            return response()->json([
                "success" => true,
                "message" => "Follow up retrieved successfully.",
                "data" => $reportArr
            ], 201);
        }
        $segment = $this->segment;
        return view('app.report.sales-person', compact('user_list', 'segment'));
    }

    public function salesPersonPdfReport(Request $request)
    {
        if ($request->ajax()) {
            $proposal_template = ProposalTemplates::where('company_id', $this->company_id)->first();
            $company_data = ViewUserData::where("id", $this->company_id)->orderBy('id', 'ASC')->get()->first();
            $input = $request->all();
            $fil_assignee = $input['fil_assignee'];
            $fil_report_type = $input['fil_report_type'];
            $dateArr = explode("_", $input['fil_date']);
            $userLists = User::query()->select(["name", "id", "email"])
                ->where('invite_status', 1)
                ->where(function ($query) use ($fil_assignee) {
                    if ($fil_assignee > 0) {
                        $query->where('id', $fil_assignee);
                    }else{
                        $query->where('company_id', $this->company_id);
                        $query->orwhere('id', $this->company_id);
                    }
                    /*if ($this->logged_user->company_id > 0) {
                        $query->where('company_id', $this->company_id);
                    }*/
                   /* if ($fil_assignee > 0) {
                        $query->where('id', $fil_assignee);
                    } else {
                        $query->whereRaw('id IN (' . Session::get("get_data_by_id") . ')');
                        if(!$this->logged_user->company_id)
                            $query->orWhere('id', $this->company_id);
                    }*/
                })
                ->get();
            $user_cnt = $userLists->count();
            $reportArr = [];
            foreach ($userLists as $userList) {
                $widgets = [];
                $widgetsArray = DB::table(function ($query) {
                    $query->select(DB::raw('DISTINCT estimate_no, status'),'estimate_date','company_id','customer_id')
                        ->from('estimates');
                }, 'subquery')
                    ->leftjoin('customers_views', 'subquery.customer_id', '=', 'customers_views.id')
                    ->where(function ($query) {
                        if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                            $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
                            $query->orWhere('customers_views.user_id', '=', $this->logged_user->id);
                        }
                    })
                    ->where(function ($query) use ($dateArr) {
                        $query->whereBetween(DB::raw("subquery.estimate_date"), [$dateArr[0], $dateArr[1]]);
                    })
                    /* ->where(function ($query) use ($userList, $fil_assignee) {
                         $query->where('user_id', $userList->id);
                     })*/

                    ->where(function ($query) use ($userList, $fil_assignee) {
                        /*if ($fil_assignee == 0) {
                            $query->where('customers_views.company_id', '=', $this->company_id);
                        }*/

//                        if ($fil_assignee > 0) {
                        $query->where('customers_views.assigned_to_user', $userList->id);
                        $query->where('customers_views.company_id', $this->company_id);
//                        }
                    })
                    ->where('subquery.company_id', '=', $this->company_id)
                    ->where('subquery.status', '!=', '')
                    ->select('subquery.status', DB::raw('COUNT(subquery.status) as widget_total'))
                    ->groupBy('subquery.status')
                    ->get();
                $widgets = json_decode(json_encode($widgetsArray), true);
                /*$widgets = Estimate::query()
                    ->groupBy('status')
                    ->where('company_id', $this->company_id)
                    ->where(function ($query) use ($dateArr) {
                        $query->whereBetween(DB::raw("DATE_FORMAT(estimate_date, '%Y-%m-%d')"), [$dateArr[0], $dateArr[1]]);
                    })
                    ->where(function ($query) use ($userList, $fil_assignee) {

                        $query->where('user_id', $userList->id);
                    })
                    ->select('status', DB::raw('COUNT(status) as widget_total'))->get()->toArray();*/
                $a = [
                    ['status' => 'Sent', 'widget_total' => 0],
                    ['status' => 'Inprogress', 'widget_total' => 0],
                    ['status' => 'Accept', 'widget_total' => 0],
                    ['status' => 'Decline', 'widget_total' => 0],
                    ['status' => 'Draft', 'widget_total' => 0]
                ];
                $x = array_column($widgets, 'status');
                $total = 0 + array_sum(array_column($widgets, 'widget_total'));
                foreach ($a as $value) {
                    if (!in_array($value['status'], $x)) {
                        $widgets[] = $value;
                    }
                }
                usort($widgets, function ($a, $b) {
                    return $a['status'] <=> $b['status'];
                });
                $widgets[] = array("status" => "Total", "widget_total" => $total);

                $sales_performance = DB::table('sales_person_performances')
                    ->where(function ($query) use ($dateArr) {
                        $query->whereBetween(DB::raw("DATE_FORMAT(performance_date, '%Y-%m-%d')"), [$dateArr[0], $dateArr[1]]);
                    })
//                    ->where('company_id', $this->company_id)

                    ->where(function ($query) use ($userList) {

                        $query->where('user_id', $userList->id);
                    })
                    ->select(DB::raw("SUM(total_task) as total_task"), DB::raw("SUM(completed_task) as completed_task"), DB::raw("COUNT(id) as total_record"), DB::raw("SUM(daily_performance) as daily_performance"))
                    ->get()->toArray();

                $count_all = DB::table('sales_person_performances')
                    ->where('total_task', '>', 0)
                    ->where(function ($query) use ($dateArr) {
                        $query->whereBetween(DB::raw("DATE_FORMAT(performance_date, '%Y-%m-%d')"), [$dateArr[0], $dateArr[1]]);
                    })
                    ->where(function ($query) use ($userList) {
                        $query->where('user_id', $userList->id);
                    })
                    ->select('id')->count();

                $total_task = array_column($sales_performance, 'total_task');
                $total_record = array_column($sales_performance, 'total_record');
                $completed_task = array_column($sales_performance, 'completed_task');
                $total_daily_performance = array_column($sales_performance, 'daily_performance');

                $reportArr["summaryData"][$userList->id]["name"] = $userList->name;
                $reportArr["summaryData"][$userList->id]["widget"] = $widgets;
                $reportArr["summaryData"][$userList->id]["completed_task"] = (int)$completed_task[0];
                $reportArr["summaryData"][$userList->id]["total_task"] = (int)$total_task[0];
                $reportArr["summaryData"][$userList->id]["completion_ratio"] = ($total_task[0]) ? (float)number_format(($completed_task[0] / $total_task[0]) * 100, 2) : 0;
                $reportArr["summaryData"][$userList->id]["performance"] = ($count_all > 0) ? (float)number_format($total_daily_performance[0] / $count_all, 2) : 0;

            }

            $estimateList = DB::table('estimates')
                ->leftJoin('users', 'estimates.sales_person_id', '=', 'users.id')
                ->where(function ($query) use ($dateArr) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(estimates.estimate_date, '%Y-%m-%d')"), [$dateArr[0], $dateArr[1]]);
                })
                ->where('estimates.company_id', $this->company_id)
                ->where(function ($query) use ($userList, $fil_assignee) {
                    if ($fil_assignee > 0) {
                        $query->where('estimates.user_id', $userList->id);
                    } else {
                        $query->whereRaw('estimates.user_id IN  (' . Session::get("get_data_by_id") . ')');
                        $query->orWhere('estimates.user_id', $this->company_id);
                    }
                })
                ->select(array('estimates.id', 'estimates.customer_name', 'estimates.estimate_no', 'estimates.estimate_date', 'estimates.addless_amount', 'estimates.net_amount', DB::raw("RIGHT(estimates.customer_address, 10) as mobile_no"), "estimates.status", "users.name as created_by", DB::raw("DATE_FORMAT(estimates.estimate_date, '%d-%m-%Y') as estimate_dates")))
                ->orderBy("estimates.id", 'desc')
                ->get()->toArray();

            foreach ($estimateList as $key_value => $est_value) {
                $estimateList[$key_value]->followupDetails = [];
                if ($fil_report_type == 1) {
                    $event = DB::table('events')
                        ->leftjoin('estimates', 'events.estimate_id', 'estimates.id')
                        ->leftJoin('users', 'events.user_id', 'users.id')
                        ->where([
                            ['events.estimate_id', '=', $est_value->id],
                            ['events.event_type', '=', 'estimate'],
                        ])
                        ->select('events.*', 'estimates.estimate_no as estimate_no', DB::raw("DATE_FORMAT(events.start_date, '%d-%m-%Y') as display_date"), DB::raw("DATE_FORMAT(events.start_date, '%d-%m-%Y %H:%i:%s') as start_date"), 'estimates.customer_name', DB::raw("RIGHT(estimates.customer_address, 10) as mobile_no"), 'estimates.status', 'users.name as user_name', DB::raw("DATE_FORMAT(events.created_at, '%d-%m-%Y  %H:%i:%s') as created_at")) // 'users.name',
                        ->orderBy('events.id', 'DESC')
                        ->orderBy('events.start_date', 'DESC')
                        ->get()->toArray();
                    $estimateList[$key_value]->followupDetails = $event;
                }
            }
            $reportArr["estimateList"] = $estimateList;

            $company_mobile_no = '';
            $company_email = '';
            $company_website_link = '';
            if($company_data->mobile_no){
                $company_mobile_no = 'Mobile : +91 '.$company_data->mobile_no;
            }

            if($company_data->email){
                $company_email = 'Email : '.$company_data->email;
            }

            if($company_data->website_link){
                $company_website_link = 'Website : '.$company_data->website_link.' | ';
            }

            //pdf
            if ($fil_assignee > 0){

            }

/*<td width="20%" style="color:#000;text-align: left !important;">
                        <table width="100%" style="text-align: left !important;font-family: sans-serif;">
                             <tr>
                                <td style="text-align: left !important; vertical-align: middle;">
                                    <img src="'.public_path(Storage::url($proposal_template->header_logo)).'" height="80"/>
                                </td>
                             </tr>
                        </table>
                    </td>*/
            $header = '<!--mpdf
    <htmlpageheader name="letterheader">
        <div style="border-bottom: 1px solid #000000; font-size: 9pt; text-align: center; font-family: sans-serif;margin-bottom:4px;">
            <table width="100%" style=" font-family: sans-serif;">
                <tr>
                    <td width="100%" style="text-align: center; vertical-align: middle;">
                        <div style="text-align: right; font-weight: bold;">
                            <span style="font-weight: bold; font-size: 20pt;">'.$company_data->company_name.'</span><br />
                            <span style="font-weight: normal; font-size: 8pt;">
                          '.$company_website_link.' '.$company_email.'<br />
                            '.$company_mobile_no.'
                            </span>
                        </div>
                    </td>

                </tr>
            </table>
        </div>
        <table width="100%" style=" font-family: sans-serif;">
            <tr>
                <td width="50%" style="text-align: left; vertical-align: middle;">Report Date : '.date('d-m-Y',strtotime($dateArr[0])).' to ' . date('d-m-Y',strtotime($dateArr[1])).'</td>
                <td width="50%" style="color:#000;text-align:right;"></td>
            </tr>
        </table>
    </htmlpageheader>

    <htmlpagefooter name="letterfooter2">
        <div style="border-top: 1px solid #000000; font-size: 9pt; text-align: center; padding-top: 3mm; font-family: sans-serif; ">
            <table width="100%" style=" font-family: sans-serif;text-align: center;">
                <tr>
                    <td width="50%" style="color:#000;" colspan="3">
                       '.$company_data->address.','.$company_data->city_name.','.$company_data->state_name.' - '.$company_data->pincode.'
                     </td>
                </tr>
                <tr>
                    <td width="35%" style="text-align:left">
                        Page {PAGENO} of {nbpg}
                    </td>
                    <td width="30"></td>
                    <td width="35%" style="text-align:right">
                      Print Date : {DATE j-m-Y}
                    </td>
                </tr>
            </table>
        </div>
    </htmlpagefooter>
mpdf-->

<style>
    @page {
        margin-top: 4.0cm;
        margin-bottom: 2.5cm;
        margin-left: 1cm;
        margin-right: 1cm;
        header: html_letterheader;
        footer: html_letterfooter2;
//        background-color: pink;
    }

//    @page :first {
//        margin-top: 8cm;
//        margin-bottom: 4cm;
//        header: html_letterheader;
//        footer: _blank;
//        resetpagenum: 1;
//        background-color: lightblue;
//    }

//    @page letterhead {
//        margin-top: 2.5cm;
//        margin-bottom: 2.5cm;
//        margin-left: 2cm;
//        margin-right: 2cm;
//        footer: html_letterfooter2;
//        background-color: pink;
//    }

//    @page letterhead :first {
//        margin-top: 6cm;
//        margin-bottom: 4cm;
//        header: html_letterheader;
//        footer: _blank;
//        resetpagenum: 1;
//        background-color: lightblue;
//    }
</style>';

            $summaryHtml = '<table border="1" cellspacing="0" cellpadding="4" width="100%" style=" font-family: sans-serif;text-align: left;">
                <tr>
                    <th>Name</th>
                    <th>Accept</th>
                    <th>Decline</th>
                    <th>Draft</th>
                    <th>Inpro.</th>
                    <th>Total</th>
                    <th>Conv. Ratio(%)</th>
                    <th>Total Task</th>
                    <th>Cmpl. Task</th>
                    <th>Cmpl. Ratio(%)</th>
                </tr>'; //<th>Perform.(%)</th> <th>Sent</th>
            $total_accept = 0;
            $total_decline = 0;
            $total_draft = 0;
            $total_inprogress = 0;
            $total_sent = 0;
            $total_total = 0;
            $total_total_task = 0;
            $total_completed_task = 0;
            $total_performance = 0;
            $total_completion_ratio = 0;
            $count_performance = 0;
            $count_completion_ratio = 0;
            $total_conver_ratio = 0;
            foreach ($reportArr["summaryData"] as $summaryKey => $summaryValue){
                if ($summaryValue['performance'] > 0) {
                    $count_performance+=1;
                }

                if ($summaryValue['completion_ratio'] > 0) {
                    $count_completion_ratio += 1;
                }

                $conver_ratio = ($summaryValue['widget'][0]['widget_total'] > 0) ? number_format(($summaryValue['widget'][0]['widget_total'] /
                        $summaryValue['widget'][5]['widget_total'])*100,2) : 0;
                $total_conver_ratio = ($total_accept > 0) ? number_format(($total_accept / $total_total)*100,2) : 0;

                $total_accept += $summaryValue['widget'][0]['widget_total'];
                $total_decline += $summaryValue['widget'][1]['widget_total'];
                $total_draft += $summaryValue['widget'][2]['widget_total'];
                $total_inprogress += $summaryValue['widget'][3]['widget_total'];
                $total_sent += $summaryValue['widget'][4]['widget_total'];
                $total_total += $summaryValue['widget'][5]['widget_total'];
                $total_total_task += $summaryValue['total_task'];
                $total_completed_task += $summaryValue['completed_task'];
                $total_performance+= $summaryValue['performance'];
                $total_completion_ratio += $summaryValue['completion_ratio'];


                $summaryHtml .= '<tr><td>'. $summaryValue['name'].'</td><td>'.$summaryValue['widget'][0]['widget_total'].'</td><td>'.$summaryValue['widget'][1]['widget_total'].'</td><td>'.$summaryValue['widget'][2]['widget_total'].'</td><td>'.$summaryValue['widget'][3]['widget_total'].'</td><td>'.$summaryValue['widget'][5]['widget_total'].'</td><td>'.$conver_ratio.'</td><td>'.$summaryValue['total_task'].'</td><td>'.$summaryValue['completed_task'].'</td><td>'.$summaryValue['completion_ratio'].'</td></tr>'; //<td>'.$summaryValue['performance'].'</td> <td>'.$summaryValue['widget'][4]['widget_total'].'</td>


                $final_performance = ($count_performance > 0)? number_format(($total_performance/$count_performance),2) :$total_performance;
                $final_completion_ratio = ($count_completion_ratio > 0)? number_format(($total_completion_ratio/$count_completion_ratio),2) :$total_completion_ratio;
            }
            $a = ($total_completed_task > 0)?number_format(($total_completed_task/$total_total_task)*100,2):0;
                $summaryHtml .= '<tr>
                    <th>Total</th>
                    <th>'.$total_accept.'</th>
                    <th>'.$total_decline.'</th>
                    <th>'.$total_draft.'</th>
                    <th>'.$total_inprogress.'</th>
                    <th>'.$total_total.'</th>
                    <th>'.$total_conver_ratio.'</th>
                    <th>'.$total_total_task.'</th>
                    <th>'.$total_completed_task.'</th>
                    <th>'.$a.'</th>
                </tr>
            </table>
'; //<th>'.$final_performance.'</th>  <th>'.$total_sent.'</th>
//            <pagebreak />
           /* $estimateHtml = '<table border="0" cellspacing="0" cellpadding="0" width="100%" style="border: 1px solid;font-family: sans-serif;text-align: left;">
                <tr>
                    <th>Date</th>
                    <th>Est No</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Created By</th>
                    <th>Status</th>
                </tr>';

            foreach ($reportArr["estimateList"] as $estimateKey => $estimateValue){
                $estimateHtml .= '<tr><td>'. $estimateValue->estimate_dates.'</td><td>'.$estimateValue->estimate_no.'</td><td>'.$estimateValue->customer_name.'</td><td>'.$estimateValue->net_amount.'</td><td>'.$estimateValue->created_by.'</td><td>'.$estimateValue->status.'</td></tr>';

                foreach ($estimateValue->followupDetails as $followupDetailsKey => $followupDetailsValue){
                    $nextFollowUp = $followupDetailsValue->start_date;
                                if ($followupDetailsValue->next_follow_up == 1)
                                    $nextFollowUp = "-";
                                $estimateHtml .= '<tr>';
                                $estimateHtml .= '<td colspan="6">';

                                $estimateHtml .= '<table style="border-top: 1px solid;">';
                                $estimateHtml .= '<tbody>';

                                $estimateHtml .= '<tr>';
                                $estimateHtml .= '<td width="58%">';
                                $estimateHtml .= '<span class="text-muted font-13">Notes</span>';
                                $estimateHtml .= '<h5 class="font-14 fw-normal">'.$followupDetailsValue->notes.'</h5>';
                                $estimateHtml .= '</td>';
                                $estimateHtml .= '<td width="16%">';
                                $estimateHtml .= '<span class="text-muted font-13">Created Date</span>';
                                $estimateHtml .= '<h5 class="font-14 fw-normal">'.$followupDetailsValue->created_at.'</h5>';
                                $estimateHtml .= '</td>';
                                $estimateHtml .= '<td width="10%">';
                                $estimateHtml .= '<span class="text-muted font-13">Created by</span>';
                                $estimateHtml .= '<h5 class="font-14 fw-normal">'.$followupDetailsValue->user_name.'</h5>';
                                $estimateHtml .= '</td>';
                                $estimateHtml .= '<td width="16%">';
                                $estimateHtml .= '<span class="text-muted font-13">Next follow up</span>';
                                $estimateHtml .= '<h5 class="font-14 fw-normal">'.$nextFollowUp.'</h5>';
                                $estimateHtml .= '</td>';
                                $estimateHtml .= '</tr>';
                                $estimateHtml .= '</tbody>';
                                $estimateHtml .= '</table>';
                                $estimateHtml .= '</td>';
                                $estimateHtml .= '</tr>';
                }
            }
            $estimateHtml .= '</table>';*/

            $mpdf = new \Mpdf\Mpdf(['tempDir'=>storage_path('tempdir')]);
            $mpdf->showImageErrors = true;
            $mpdf->debug = true;
            $mpdf->curlAllowUnsafeSslRequests = true;
            $mpdf->autoScriptToLang = true;
            $mpdf->autoLangToFont = true;

              $mpdf->SetWatermarkText($company_data->company_name);
              $mpdf->showWatermarkText = true;
              $mpdf->watermarkTextAlpha = 0.1;
              $mpdf->watermarkImageAlpha = 0.5;


            /*$mpdf->SetWatermarkImage(
                public_path(Storage::url($proposal_template->header_logo))
            );
            $mpdf->showWatermarkImage = true;
            $mpdf->watermarkImageAlpha = 0.1;
            $mpdf->watermarkAngle = 33;*/

            /* $stylesheet = file_get_contents('https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css');
            $mpdf->WriteHTML($stylesheet, 1); // CSS Script goes here.*/
            $mpdf->WriteHTML($header);
            $mpdf->WriteHTML('<pagebreak page-selector="letterhead" />');
            $mpdf->WriteHTML($summaryHtml);
           /* $mpdf->WriteHTML('<pagebreak page-selector="letterhead" />');
            $mpdf->WriteHTML($estimateHtml);*/
//            $mpdf->Output('filename.pdf', Destination::FILE);

            return $mpdf->Output('document.pdf', Destination::DOWNLOAD);
            return $mpdf->Output(public_path('storage/report/' . 'REPORT'.$this->logged_user->id.$this->company_id.date('dmY',strtotime($dateArr[0])).'TO' . date('dmY',strtotime($dateArr[1])).'.pdf'), 'F');

            return response()->json([
                "success" => true,
                "message" => "Follow up retrieved successfully.",
                "data" => $reportArr
            ], 201);
        }
    }

    public function GenerateReportPdf()
    {
        $header = '<!--mpdf
    <htmlpageheader name="letterheader">
        <div style="border-bottom: 1px solid #000000; font-size: 9pt; text-align: center; font-family: sans-serif;">
            <table width="100%" style=" font-family: sans-serif;">
                <tr>
                    <td width="70%" style="text-align: left; vertical-align: middle;">
                        <div style="text-align: right; font-weight: bold;">
                            <span style="font-weight: bold; font-size: 20pt;">Heaven Solar Energy Pvt Ltd.</span><br />
                            <span style="font-weight: normal; font-size: 8pt;">
                           Website : www.website.com | Email : hr.tatvamasi@gmail.com<br />
                            Mobile : +91 1777 123 567
                            </span>
                        </div>
                    </td>
                    <td width="30%" style="color:#000; ">
                        <img src="https://app.quickestimate.co/assets/images/logo.png" width="200"/>
                    </td>
                </tr>
            </table>
        </div>
    </htmlpageheader>

    <htmlpagefooter name="letterfooter2">
        <div style="border-top: 1px solid #000000; font-size: 9pt; text-align: center; padding-top: 3mm; font-family: sans-serif; ">
            <table width="100%" style=" font-family: sans-serif;text-align: center;">
                <tr>
                    <td width="50%" style="color:#000;" colspan="3">
                       204, Anupam Square, SAT Swami Narayan Temple Rd, Mota Varachha Surat Gujarat - 394101
                     </td>
                </tr>
                <tr>
                    <td width="35%" style="text-align:left">
                        Page {PAGENO} of {nbpg}
                    </td>
                    <td width="30"></td>
                    <td width="35%" style="text-align:right">
                      Print Date : {DATE j-m-Y}
                    </td>
                </tr>
            </table>
        </div>
    </htmlpagefooter>
mpdf-->

<style>
    @page {
        margin-top: 4.5cm;
        margin-bottom: 2.5cm;
        margin-left: 1cm;
        margin-right: 1cm;
        header: html_letterheader;
        footer: html_letterfooter2;
//        background-color: pink;
    }

//    @page :first {
//        margin-top: 8cm;
//        margin-bottom: 4cm;
//        header: html_letterheader;
//        footer: _blank;
//        resetpagenum: 1;
//        background-color: lightblue;
//    }

//    @page letterhead {
//        margin-top: 2.5cm;
//        margin-bottom: 2.5cm;
//        margin-left: 2cm;
//        margin-right: 2cm;
//        footer: html_letterfooter2;
//        background-color: pink;
//    }

//    @page letterhead :first {
//        margin-top: 8cm;
//        margin-bottom: 4cm;
//        header: html_letterheader;
//        footer: _blank;
//        resetpagenum: 1;
//        background-color: lightblue;
//    }
</style>';

        $letter = 'Dear Sir or Madam,<br />
Contents of your letter...
... more letter on page 2 ...
<pagebreak />
';

        $mpdf = new \Mpdf\Mpdf();
        $mpdf->showImageErrors = true;
        $mpdf->debug = true;
        $mpdf->curlAllowUnsafeSslRequests = true;
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;

        /*  $mpdf->SetWatermarkText('Heaven Design Pvt. Ltd.');
          $mpdf->showWatermarkText = true;
          $mpdf->watermarkTextAlpha = 0.1;
          $mpdf->watermarkImageAlpha = 0.5;*/


        $mpdf->SetWatermarkImage(
            'https://app.quickestimate.co/assets/images/logo.png'
        );
        $mpdf->showWatermarkImage = true;
        $mpdf->watermarkImageAlpha = 0.1;
        $mpdf->watermarkAngle = 33;

        $mpdf->WriteHTML($header);
        $mpdf->WriteHTML('<pagebreak page-selector="letterhead" />');
        $mpdf->WriteHTML($letter);


        return $mpdf->Output('document.pdf', Destination::DOWNLOAD);


    }

    public function postWeeklyMailNotificationFlag(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $input['weekly_cron_flg'] =  ($input['weekly_cron_flg'] == "true") ? 1 : 0;

            User::where(["id" => $this->logged_user->id])->update(["weekly_cron_flg"=>$input['weekly_cron_flg']]);
            return response()->json(['success' => 'Successfully Saved!'], 201);
        }
    }

    public function postMonthlyMailNotificationFlag(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $input['monthly_cron_flg'] =  ($input['monthly_cron_flg'] == "true") ? 1 : 0;

            User::where(["id" => $this->logged_user->id])->update(["monthly_cron_flg"=>$input['monthly_cron_flg']]);
            return response()->json(['success' => 'Successfully Saved!'], 201);
        }
    }
}
