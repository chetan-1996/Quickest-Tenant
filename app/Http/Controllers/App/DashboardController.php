<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DashboardSetting;
use App\Models\Estimate;
use App\Models\EstimateTimeline;
use App\Models\LeadStage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    protected $logged_user = null;
    protected $company_id = 0;
    protected $user_perm = 0;
    protected $segment = null;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->logged_user = \Illuminate\Support\Facades\Auth::user();
            $this->company_id = ($this->logged_user->company_id) ? $this->logged_user->company_id : $this->logged_user->id;
            $this->main_company = User::select("company_category")
                ->where('id', $this->company_id)->first();
            $this->user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
            $this->segment = $request->segment(1);
            return $next($request);
        });
    }

    public function settingStore(Request $request)
    {
        $input = $request->all();
        if (!empty($input['data'])) {
            $input['data'] = array_map(function ($arr) {
                return $arr + ['user_id' => $this->logged_user->id, 'company_id' => $this->company_id];
            }, $input['data']);
        } else {
            $input['data'] = [];
        }

        foreach($input['data'] as $key => $value){
            $getdata = DashboardSetting::query()->select('permission_id','is_primary')->where('permission_id', $value['permission_id'])->where('user_id', $this->logged_user->id)->first();
            $input['data'][$key]['is_primary'] = ($getdata)?$getdata->is_primary:0;

        }

        DashboardSetting::query()->where('user_id', $this->logged_user->id)->delete();

        $permission = DashboardSetting::query()->insert($input['data']);
        return response()->json([
            "success" => true,
            "message" => "Successfully saved successfully.",
        ], 201);
    }

    /*public function getLeadStage(Request $request)
    {
        $input = $request->all();

         $widgets = LeadStage::query()->groupBy('customers_views.lead_stage_id')
             ->leftjoin('customers_views', 'customers_views.lead_stage_id', '=', 'lead_stages.id')
             ->where(function ($query) {
                 if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                     $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
                     $query->orwhere('customers_views.user_id', '=', $this->logged_user->id);
                 }
             })
             ->where(function ($query) use ($input) {
                 if ($input['fil_user_id'] == 0) {
                     $query->where('customers_views.company_id', '=', $this->company_id);
                 }

                 if ($input['fil_user_id'] > 0) {
                     $query->where('customers_views.assigned_to_user', $input['fil_user_id']);
                     $query->where('customers_views.company_id', $this->company_id);
                 }
             })
             ->where(function ($query) use ($input) {
                 $query->whereBetween(DB::raw("DATE(customers_views.created_at)"), [$input['fil_estimate_start'], $input['fil_estimate_end']]);
             })
             ->where('lead_stages.company_id', '=', $this->company_id)
             ->select('lead_stages.name','lead_stages.id', DB::raw('COALESCE(COUNT(customers_views.id), 0) as widget_total'))->orderBy("lead_stages.priority", 'asc')->get()->toArray();

        return response()->json([
            "success" => true,
            "message" => "Lead Stages retrieved successfully.",
            "widgets" => $widgets
        ], 201);
    }*/

    public function getLeadStage(Request $request)
    {
        $input = $request->all();

        $widgets = LeadStage::query()
            ->leftJoin('customers', function ($join) use ($input) {
                $join->on('customers.lead_stage_id', '=', 'lead_stages.id')
                    ->where(function ($query) {
                        if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                            $query->where('customers.assigned_to_user', '=', $this->logged_user->id);
                            // $query->orwhere('customers.user_id', '=', $this->logged_user->id);
                        }
                    })
                    ->where(function ($query) use ($input) {
                        if ($input['fil_user_id'] == 0) {
                            $query->where('customers.company_id', '=', $this->company_id);
                        }

                        if ($input['fil_user_id'] > 0) {
                            $query->where('customers.assigned_to_user', $input['fil_user_id']);
                            // $query->orwhere('customers.user_id', $input['fil_user_id']);
                            // $query->where('customers.company_id', $this->company_id);
                        }
                    })
                    ->where(function ($query) use ($input) {
                        $query->whereBetween(DB::raw("DATE(customers.created_at)"), [$input['fil_estimate_start'], $input['fil_estimate_end']]);
                    });
                /* ->when($input['fil_user_id'] > 0, function ($query) use ($input) {
                     $query->where('customers.assigned_to_user', $input['fil_user_id']);
                 })
                 ->when($input['fil_user_id'] == 0, function ($query) {
                     $query->where('customers.company_id', $this->company_id);
                 })
                 ->whereBetween(DB::raw("DATE(customers.created_at)"), [$input['fil_estimate_start'], $input['fil_estimate_end']]);*/
            })
            ->where('lead_stages.company_id', $this->company_id)
            ->select('lead_stages.name', 'lead_stages.color_code', 'lead_stages.id', DB::raw('COALESCE(COUNT(customers.id), 0) as widget_total'))
            ->groupBy('lead_stages.id')
            ->orderBy('lead_stages.priority', 'asc')
            ->get()
            ->toArray();

        return response()->json([
            "success" => true,
            "message" => "Lead Stages retrieved successfully.",
            "widgets" => $widgets
        ], 201);
    }

    public function getWidget(Request $request)
    {
        $input = $request->all();
        //        DB::enableQueryLog();

        // $widgets = DB::table('estimates')
        // ->leftJoin('customers_views', 'estimates.customer_id', '=', 'customers_views.id')
        // ->where(function ($query) {
        //     if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
        //         $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
        //         $query->orwhere('customers_views.user_id', '=', $this->logged_user->id);
        //     }
        // })
        // ->where('estimates.company_id', $this->company_id)
        // ->whereBetween(DB::raw('DATE(estimates.estimate_date)'), [$input['fil_estimate_start'], $input['fil_estimate_end']])
        // ->groupBy('estimates.status')
        // ->select('estimates.status', DB::raw('COUNT(estimates.status) as widget_total'))
        // ->get()->toArray();


        $widgetsArray = DB::table(function ($query) use ($input) {
            $query->select(DB::raw('DISTINCT estimate_no, status'), 'company_id', 'customer_id')
                ->from('estimates')
                ->where(function ($query) use ($input) {
                    $query->whereBetween(DB::raw("DATE(estimate_date)"), [$input['fil_estimate_start'], $input['fil_estimate_end']]);
                });
        }, 'subquery')
            ->leftjoin('customers_views', 'subquery.customer_id', '=', 'customers_views.id')
            ->where(function ($query) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                    $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
                    $query->orWhere('customers_views.user_id', '=', $this->logged_user->id);
                }
            })
            ->where(function ($query) use ($input) {
                if ($input['fil_user_id'] == 0) {
                    $query->where('customers_views.company_id', '=', $this->company_id);
                }

                if ($input['fil_user_id'] > 0) {
                    $query->where('customers_views.assigned_to_user', $input['fil_user_id']);
                    $query->where('customers_views.company_id', $this->company_id);
                }
            })
            ->where('subquery.company_id', '=', $this->company_id)
            ->where('subquery.status', '!=', '')
            ->select('subquery.status', DB::raw('COUNT(subquery.status) as widget_total'))
            ->groupBy('subquery.status')
            ->get();

        $widgets = json_decode(json_encode($widgetsArray), true);

        /* $widgets = Estimate::query()->groupBy('estimates.status')
             ->leftjoin('customers_views', 'estimates.customer_id', '=', 'customers_views.id')
             ->where(function ($query) {
                 if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                     $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
                     $query->orwhere('customers_views.user_id', '=', $this->logged_user->id);
                 }
             })
             ->where(function ($query) use ($input) {
                 if ($input['fil_user_id'] == 0) {
                     $query->where('customers_views.company_id', '=', $this->company_id);
                 }

                 if ($input['fil_user_id'] > 0) {
                     $query->where('customers_views.assigned_to_user', $input['fil_user_id']);
                     $query->where('customers_views.company_id', $this->company_id);
                 }
             })
             ->where(function ($query) use ($input) {
                 $query->whereBetween(DB::raw("DATE(estimates.estimate_date)"), [$input['fil_estimate_start'], $input['fil_estimate_end']]);
             })
             ->where('estimates.company_id', '=', $this->company_id)
             ->where('estimates.status', '!=', '')
             ->select('estimates.status', DB::raw('COUNT(estimates.status) as widget_total'))->get()->toArray();
        */
        foreach ($widgets as $wkey => $widget) {
            $sorting = 0;
            if ($widget['status'] == 'Accept') {
                $widgets[$wkey]['sorting'] = 1;
            }
            if ($widget['status'] == 'Decline') {
                $widgets[$wkey]['sorting'] = 2;
            }
            if ($widget['status'] == 'Sent') {
                $widgets[$wkey]['sorting'] = 3;
            }
            if ($widget['status'] == 'Inprogress') {
                $widgets[$wkey]['sorting'] = 4;
            }
            if ($widget['status'] == 'Draft') {
                $widgets[$wkey]['sorting'] = 5;
            }
        }

        $a = [
            ['status' => 'Accept', 'widget_total' => 0, 'sorting' => 1],
            ['status' => 'Decline', 'widget_total' => 0, 'sorting' => 2],
            //            ['status' => 'Sent', 'widget_total' => 0, 'sorting' => 3],
            ['status' => 'Inprogress', 'widget_total' => 0, 'sorting' => 4],
            ['status' => 'Draft', 'widget_total' => 0, 'sorting' => 5]
        ];
        $x = array_column($widgets, 'status');
        $total = 0 + array_sum(array_column($widgets, 'widget_total'));
        foreach ($a as $value) {
            if (!in_array($value['status'], $x)) {
                $widgets[] = $value;
            }
        }

        usort($widgets, function ($a, $b) {
            return $a['sorting'] <=> $b['sorting'];
        });

        $widgets[] = array("status" => "Total", "widget_total" => $total, 'sorting' => 6);
        $key_values = array_column($widgets, 'sorting');
        array_multisort($key_values, SORT_ASC, $widgets);
        return response()->json([
            "success" => true,
            "message" => "Follow up retrieved successfully.",
            "widgets" => $widgets,
            "total" => $total,
        ], 201);
    }

    public function index()
    {
        //        $user_perm= $this->user_perm;
        //        $widgets = Cache::rememberForever('dashboard_widgets', function () use ($user) {
        /*$widgets = Estimate::query()->groupBy('status') //->where('status','!=','Draft')
            //->where('company_id',$company_id)
        ->where(function ($query) use ($user) {
            if ($user->company_id == "") {
                $query->where('company_id', '=', $user->id);
            } else {
                $query->whereRaw('user_id IN  (' . Session::get("get_data_by_id") . ')');
                $query->orWhere('user_id', $user->id);
            }
        })
            ->select('status', DB::raw('COUNT(status) as widget_total'))->get()->toArray();
        //});
        // Cache::flush();
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

        $widgets[] = array("status" => "Total", "widget_total" => $total);*/
        $curr_date_month = date('n');
        $bar_chart_filter = $this->calculateFiscalYearForDate($curr_date_month);


        // Total records
        /*$records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->where('cv.company_id', $this->company_id)
            ->whereNotNull('cv.last_follow_up_datetime')
            ->where(function ($query) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
                    $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                }
            })
            ->groupBy('cv.id')
            ->select('cv.id', 'cv.name', 'cv.phone_no', 'cv.status', 'cv.user_name', 'cv.assigned_to_user', 'cv.net_amount', 'cv.estimate_status', 'cv.last_follow_up_datetime', 'cv.some_day_flg', DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code'))
            ->get();


        $data = array();
        $i = 0;
        $followups['overdue'] = array();
        $followups['duetoday'] = array();
        $followups['upcoming'] = array();
        foreach ($records as $record) {

            $id = Crypt::encrypt($record->id);
            // $customer_type = $record->customer_type;
            $name = $record->name;
            // $lead_origin = $record->lead_origin;
            // $lead_category = $record->lead_category;
            // $email = $record->email;
            $phone_no = $record->phone_no;
            // $address = $record->address;
            // $pincode = $record->pincode;
            // $description = $record->description;
            $status = $record->status;
            // $country_name = $record->country_name;
            // $state_name = $record->state_name;
            // $city_name = $record->city_name;
            // $last_activity = $record->last_activity;
            $assign_user_name = $record->user_name;
            $assigned_to_user = $record->assigned_to_user;
            $net_amount = $record->net_amount;
            $estimate_status = $record->estimate_status;
            // $date_added = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)
            // ->format('d-m-Y h:i A');


            $follow_up_datetime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->last_follow_up_datetime)
                ->format('d-m-Y h:i A');

            $i++;
            $labelName = '';
            if ($record->label_name) {
                $leadLabelNameArr = explode(',', $record->label_name);
                $labelColorCodeArr = explode(',', $record->label_color_code);
                foreach ($leadLabelNameArr as $key => $labelLabel) {
                    $labelName .= '<span class="fs-6 badge me-2" style = "background-color: ' . $labelColorCodeArr[$key] . '">' . $labelLabel . '</span> ';
                }
            }

            $temp = 'overdue';
            if (strtotime(date('d-m-Y', strtotime($follow_up_datetime))) > strtotime(date('d-m-Y'))) {
                $temp = 'upcoming';
            }

            if (strtotime(date('d-m-Y', strtotime($follow_up_datetime))) == strtotime(date('d-m-Y')) && $record->some_day_flg == 0) {
                $temp = 'duetoday';
                $follow_up_datetime = 'Today - ' . \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->last_follow_up_datetime)
                        ->format('h:i A');
            }
            $followups[$temp][] = array(
                "id" => $i,
                "name" => $name,
                // "lead_origin" => $lead_origin,
                // "lead_category" => $lead_category,
                // "created_at" => $date_added,
                // "customer_type" => $customer_type,
                // "email" => $email,
                "phone_no" => $phone_no,
                // "address" => $address,
                // "pincode" => $pincode,
                // "country_name" => $country_name,
                // "state_name" => $state_name,
                // "city_name" => $city_name,
                // "description" => $description,
                // "last_activity" => $last_activity,
                "assign_user_name" => $assign_user_name,
                "assigned_to_user" => $assigned_to_user,
                "net_amount" => $net_amount,
                "status" => $status,
                "label_name" => $labelName,
                "estimate_status" => $estimate_status,
                "follow_up_datetime" => $follow_up_datetime,
                "action" => $id,
            );
        }*/

        $teamUsers = User::select(["name", "id", "email", "mobile_no"])
            //->where('status', 'Approved')
            ->where('invite_status', 1)
            //->where('company_id', $this->company_id)
            ->where(function ($query) {
                $query->orwhere('company_id', $this->company_id);
                $query->orwhere('id', $this->company_id);
            })
            ->get();
        $segment = $this->segment;
        $dashboard_settings = DashboardSetting::query()->where('user_id', $this->logged_user->id)->pluck('permission_id')->toArray();
        //        return view('dashboard', compact('widgets', 'bar_chart_filter'))->with(array("total" => $total));
        return view('app.dashboard', compact('bar_chart_filter', 'teamUsers', 'dashboard_settings', 'segment'))->with([
            'user_perm' => $this->user_perm,
            'main_company' => $this->main_company
        ]);
    }

    public function calculateFiscalYearForDate($month)
    {
        if ($month >= 4) {
            $y = date('Y');
            $pt = date('Y', strtotime('+1 year'));
        } else {
            $y = date('Y', strtotime('-1 year'));
            $pt = date('Y');
        }
        $fy = $y . "-04-01" . "_" . $pt . "-03-31";
        return ["current_fiscal_year" => $fy, "previous_fiscal_year" => ($y - 1) . "-04-01" . "_" . ($pt - 1) . "-03-31", "last_twelve_month" => date('Y-m-d', strtotime(' - 12 months')) . "_" . date('Y-m-d'), "fd" => $y . "-04-01", "ed" => $pt . "-03-31"];
    }

    public function donutChart()
    {
        //        $user = auth()->user();
        $widgets = Estimate::query()->groupBy('status')
            ->where(function ($query) use ($user) {
                if ($user->company_id == "") {
                    $query->where('company_id', '=', $this->company_id);
                } else {
                    $query->whereRaw('user_id IN  (' . Session::get("get_data_by_id") . ')');
                    $query->orWhere('user_id', $this->logged_user->id);
                }
            })
            ->select('status', DB::raw('COUNT(status) as widget_total'), DB::raw('count(*)'))->get()->toArray();

        return response()->json([
            "success" => true,
            "message" => "Follow up retrieved successfully.",
            "labels" => array_column($widgets, 'status'),
            "data" => array_column($widgets, 'widget_total'),
        ], 201);
    }

    public function barChart(Request $request)
    {

        $monthArr = [];
        $input = $request->all();
        $dateArr = explode("_", $input['date']);

        $year = date('Y', strtotime($dateArr[0]));
        $month = date('m', strtotime($dateArr[0]));
        for ($i = 0; $i < 12; $i++) {
            array_push($monthArr, date("M` Y", strtotime('+' . $i . ' month', date(strtotime('01-' . $month . '-' . $year)))));
        }
        //        DB::enableQueryLog();
        //        $records = Estimate::select(
        //            'status',
        //            DB::raw("(SUM(net_amount)) as total_count"),
        //            DB::raw("MONTHNAME(estimate_date) as month_name"),
        //            DB::raw("DATE_FORMAT(estimate_date, '%b %Y') as estimate_date")
        //        )
        ////            ->whereYear('estimate_date', date('Y'))
        //            ->where(function ($query) use ($input) {
        //                $dateArr = explode("_", $input['date']);
        //                $query->whereBetween(DB::raw("DATE_FORMAT(estimate_date, '%Y-%m-%d')"), [$dateArr[0], $dateArr[1]]);
        //            })
        //            ->where(function ($query) use ($user) {
        //                if ($user->company_id == "") {
        //                    $query->where('company_id', '=', $user->id);
        //                } else {
        //                    $query->whereRaw('user_id IN  (' . Session::get("get_data_by_id") . ')');
        //                    $query->orWhere('user_id', $user->id);
        //                }
        //            })
        //            ->groupBy('month_name')
        //            ->get();
        // dd(DB::getQueryLog());
        //dd($items);
        $records = DB::table('estimates')
            ->leftJoin('customers_views', 'estimates.customer_id', '=', 'customers_views.id')
            ->select('estimates.status', DB::raw('SUM(estimates.net_amount) as total_count'), DB::raw("DATE_FORMAT(estimates.estimate_date, '%b` %Y') as estimate_date"))
            ->where(function ($query) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                    $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
                    $query->orwhere('customers_views.user_id', '=', $this->logged_user->id);
                }
            })
            ->where(function ($query) use ($input) {
                if ($input['fil_user_id'] == 0) {
                    $query->where('estimates.company_id', '=', $this->company_id);
                }

                if ($input['fil_user_id'] > 0) {
                    $query->where('customers_views.assigned_to_user', $input['fil_user_id']);
                    $query->where('estimates.company_id', $this->company_id);
                }
            })
            // ->where('estimates.company_id', '=', $this->company_id)
            ->whereBetween('estimates.estimate_date', [$dateArr[0], $dateArr[1]])
            ->groupBy('estimates.status', 'estimates.estimate_date')
            ->orderBy("estimates.estimate_date", 'asc')
            ->get();


        //         $records = Estimate::query()
        //             ->leftjoin('customers_views', 'estimates.customer_id', '=', 'customers_views.id')
        //             ->where(function ($query) {
        //                 if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
        //                     $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
        //                     $query->orwhere('customers_views.user_id', '=', $this->logged_user->id);
        //                 }
        //             })
        //             ->where('estimates.company_id', '=', $this->company_id)
        //             ->where(function ($query) use ($input) {
        //                 $dateArr = explode("_", $input['date']);
        //                 $query->whereBetween(DB::raw("DATE(estimates.estimate_date)"), [$dateArr[0], $dateArr[1]]);
        //             })
        // //            ->whereIn('status', ['Sent', 'Accept'])
        //            /* ->where(function ($query) use ($user) {
        //                 if ($user->company_id == "") {
        //                     $query->where('company_id', '=', $user->id);
        //                 } else {
        //                     $query->whereRaw('user_id IN  (' . Session::get("get_data_by_id") . ')');
        //                     $query->orWhere('user_id', $user->id);
        //                 }
        //             })*/
        //             ->groupBy('estimates.status', DB::raw("DATE_FORMAT(estimates.estimate_date, '%Y-%m-%d')"))
        //             ->orderBy(DB::raw("DATE_FORMAT(estimates.estimate_date, '%Y-%m-%d')"))
        //             ->select('estimates.status', DB::raw('SUM(estimates.net_amount) as total_count'), DB::raw("DATE_FORMAT(estimates.estimate_date, '%b` %Y') as estimate_date"))->get();


        $labels = array();
        $sent = array();
        $close = array();
        $dataArr = array();

        if (isset($records)) {
            foreach ($monthArr as $k => $v) {
                //                if($records) {
                foreach ($records as $record) {
                    if ($record->estimate_date == $v) {
                        $dataArr[$v]['sent'][] = 0;
                        //                        if ($record->status != 'Accept')
                        $dataArr[$v]['sent'][] = $record->total_count;

                        $dataArr[$v]['close'][] = 0;
                        if ($record->status == 'Accept')
                            $dataArr[$v]['close'][] = $record->total_count;
                    } else {
                        $dataArr[$v]['sent'][] = 0;
                        $dataArr[$v]['close'][] = 0;
                    }
                }
                //                }
                //                if(!$records){
                $dataArr[$v]['sent'][] = 0;
                $dataArr[$v]['close'][] = 0;
                //                }
            }

            foreach ($dataArr as $key => $value) {
                $sum_sent = array_sum($value['sent']);
                $sum_close = array_sum($value['close']);
                array_push($labels, $key);
                array_push($sent, number_format((float)$sum_sent, 2, '.', ''));
                array_push($close, number_format((float)$sum_close, 2, '.', ''));
            }
        }

        return response()->json([
            "success" => true,
            "message" => "Follow up retrieved successfully.",
            "labels" => $labels,
            "sent" => $sent,
            "close" => $close,
        ], 201);
    }

    /*public function salesPerformanceChart(Request $request)
    {
        $input = $request->all();
        $user = auth()->user();
        //        DB::enableQueryLog();
        $sales_performance = DB::table('sales_person_performances')
            ->where(function ($query) use ($user, $input) {
                $dateArr = explode("_", $input['date']);
                $query->whereBetween(DB::raw("DATE_FORMAT(performance_date, '%Y-%m-%d')"), [$dateArr[0], $dateArr[1]]);
            })
            ->where(function ($query) use ($user) {
                if ($user->company_id == "") {
                    //                    $query->where('company_id', '=', $user->id);
                    $query->where('user_id', '=', $user->id);
                } else {
                    //                    $query->whereRaw('user_id IN(' . Session::get("get_data_by_id") . ')');
                    $query->orWhere('user_id', $user->id);
                }
            })
            ->select(DB::raw("SUM(total_task) as total_task"), DB::raw("SUM(completed_task) as completed_task"), DB::raw("COUNT(id) as total_record"), DB::raw("SUM(daily_performance) as daily_performance"))
            ->get()->toArray();

        $count_all = DB::table('sales_person_performances')
            ->where('total_task', '>', 0)
            ->where(function ($query) use ($input) {
                $dateArr = explode("_", $input['date']);
                $query->whereBetween(DB::raw("DATE_FORMAT(performance_date, '%Y-%m-%d')"), [$dateArr[0], $dateArr[1]]);
            })
            ->where(function ($query) use ($user) {
                if ($user->company_id == "") {
                    //                    $query->where('company_id', '=', $user->id);
                    $query->where('user_id', '=', $user->id);
                } else {
                    //                    $query->whereRaw('user_id IN(' . Session::get("get_data_by_id") . ')');
                    $query->orWhere('user_id', $user->id);
                }
            })
            ->select('id')->count();
        //        dd(DB::getQueryLog());
        $total_task = array_column($sales_performance, 'total_task');
        $total_record = array_column($sales_performance, 'total_record');
        $completed_task = array_column($sales_performance, 'completed_task');
        $total_daily_performance = array_column($sales_performance, 'daily_performance');

        $series[] = ($total_task[0] > 0) ? (float)number_format(($completed_task[0] * 100) / $total_task[0], 2) : 0;
        $series[] = ($count_all > 0) ? (float)number_format($total_daily_performance[0] / $count_all, 2) : 0;
        return response()->json([
            "success" => true,
            "message" => "Follow up retrieved successfully.",
            "data" => $sales_performance,
            "series" => $series,
            "labels" => ["Completion Ratio", "Performance"],
            "total_task" => (int)$total_task[0],
            "completed_task" => (int)$completed_task[0],
            "total_record" => (int)$total_record[0],
        ], 201);
    }*/

    public function salesPerformanceChart(Request $request)
    {
        $input = $request->all();
        $sales_performance = DB::table('sales_person_performances')
            ->where(function ($query) use ($input) {
                $dateArr = explode("_", $input['date']);
                $query->whereBetween("performance_date", [$dateArr[0], $dateArr[1]]);
            })
            ->where(function ($query) use ($input) {
                if ($input['fil_user_id'] == 0 && in_array('access-all-lead-and-assign-to-anyone-in-team',$this->user_perm)) {
                    $query->where('company_id', '=', $this->company_id);
                }

                if ($input['fil_user_id'] > 0) {
                    $query->where('user_id', $input['fil_user_id']);
                    $query->where('company_id', $this->company_id);
                }
                /*if ($this->logged_user->company_id == "") {
                    $query->where('company_id', '=', $this->logged_user->id);
                } else {
                    $query->where('user_id', $this->logged_user->id);
                    $query->where('company_id', $this->company_id);
                }*/
            })
            ->where(function ($query) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                    $query->where('user_id', '=', $this->logged_user->id);
                    $query->where('company_id', '=', $this->company_id);
                }
            })
            ->select(DB::raw("SUM(total_task) as total_task"), DB::raw("SUM(completed_task) as completed_task"))
            ->get()->toArray();

        $total_task = array_column($sales_performance, 'total_task');
        $completed_task = array_column($sales_performance, 'completed_task');


        $series[] = ($total_task[0] > 0) ? (float)number_format(($completed_task[0] * 100) / $total_task[0], 2) : 0;
        return response()->json([
            "success" => true,
            "message" => "Follow up retrieved successfully.",
            "data" => $sales_performance,
            "series" => $series,
            "labels" => ["Completion Ratio"],
            "total_task" => (int)$total_task[0],
            "completed_task" => (int)$completed_task[0],
        ], 201);
    }

    public function getOpenOprDashboard(Request $request)
    {
        $data=[];
        $input = $request->all();
        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');

        /*$leadcount = EstimateTimeline::select('customer_timelines.customer_id', 'customers.*')
            ->distinct()
            ->join('customers', 'customer_timelines.customer_id', '=', 'customers.id')
            ->whereNotIn('customer_timelines.customer_id', function ($query) {
                $query->select('customer_id')
                    ->from('customer_timelines')
                    ->where('customer_timelines.activity_type', '=', 9);
            })
            ->where(function ($query) use ($input) {
                $query->where(DB::raw("DATE(customers.created_at)"), '<=',date('Y-m-d'));
            })
            ->where(function ($query) use ($input) {
                if ($input['fil_user_id'] == 0) {
                    $query->where('customers.company_id', '=', $this->company_id);
                }

                if ($input['fil_user_id'] > 0) {
                    $query->where('customers.user_id', $input['fil_user_id']);
                    $query->where('customers.company_id', $this->company_id);
                }
            })
            ->get();*/
            //DB::enableQueryLog();
        $leadcount = DB::table('customers_views as cv')
            ->select('cv.id')
            ->where('cv.company_id', $this->company_id)
            ->whereNotIn('cv.lead_stage_name', ['Lead Won','Lead Lost'])
            ->where(function ($query) use ($input) {
                if ($input['fil_user_id'] > 0) {
                    $query->where('cv.assigned_to_user', '=', $input['fil_user_id']);
                }
            })
            ->where(function ($query){
                $query->where('cv.last_follow_up_datetime', '=', '0000-00-00 00:00:00')
                    ->orWhere('cv.last_follow_up_datetime', '=', '');
            })
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id);CHX
                }
            })
            ->distinct('cv.id')
            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->get();
        $lids = '';
        foreach ($leadcount as $chunk) {
            $lids .= $chunk->id.',';
        }

        $idsString = trim($lids,',');
        $idsStringcount = $leadcount->count();

        // dd(DB::getQueryLog($leadcount));

            $records = DB::table('customers_views as cv')
                ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
                ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
                ->select('cv.id')
                // ->selectRaw('COUNT(DISTINCT cv.id) as count, GROUP_CONCAT(DISTINCT cv.id) as ids')
                ->where('cv.company_id', $this->company_id)
                ->where('cv.some_day_flg', 0)
                ->where(function ($query) use ($input) {
                    if ($input['fil_user_id'] > 0) {
                        $query->where('cv.assigned_to_user', '=', $input['fil_user_id']);
                        // $query->orwhere('cv.user_id', '=', $input['fil_user_id']);
                    }
                })
                ->where(DB::raw('DATE(cv.last_follow_up_datetime)'), "<", date('Y-m-d'))
                ->where('cv.last_follow_up_datetime', '!=', '0000-00-00 00:00:00')
                ->where(function ($query) use ($user_perm) {
                    if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                        $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
                        // $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                    }
                })
                ->distinct('cv.id')
                ->orderBy('cv.last_follow_up_datetime', 'DESC')
                // ->count();
                ->get();

        $rids = '';
        foreach ($records as $chunk) {
            $rids .= $chunk->id.',';
        }

        $ridsString = trim($rids,',');
        $ridsStringcount = $records->count();


        $widgets = LeadStage::query()
            ->join('customers', function ($join) use ($input) {
                $join->on('customers.lead_stage_id', '=', 'lead_stages.id')
                    ->where(function ($query) {
                        if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                            $query->where('customers.assigned_to_user', '=', $this->logged_user->id);
                            // $query->orwhere('customers.user_id', '=', $this->logged_user->id);
                        }
                    })
                    ->where(function ($query) use ($input) {
                        if ($input['fil_user_id'] == 0) {
                            $query->where('customers.company_id', '=', $this->company_id);
                        }

                        if ($input['fil_user_id'] > 0) {
                            $query->where('customers.assigned_to_user', $input['fil_user_id']);
                            // $query->orwhere('customers.user_id', $input['fil_user_id']);
                        }
                    });
                    /*->where(function ($query) use ($input) {
                        $query->whereBetween(DB::raw("DATE(customers.created_at)"), [date('Y-m-d'), date('Y-m-d')]);
                    });*/
            })
            ->where('lead_stages.is_default', 1)
            ->where('lead_stages.company_id', $this->company_id)
            ->select('customers.id')
            // ->select(DB::raw('COALESCE(COUNT(customers.id), 0) as total_count'),DB::raw('GROUP_CONCAT(customers.id) as ids'))
            ->get();
            //->toArray();
            /*->select('lead_stages.name', 'lead_stages.color_code', 'lead_stages.id', DB::raw('COALESCE(COUNT(customers.id), 0) as widget_total'))
            ->groupBy('customers.id')
            ->orderBy('lead_stages.priority', 'asc')
            ->get()
            ->toArray();*/
        $wids = '';
        foreach ($widgets as $chunk) {
            $wids .= $chunk->id.',';
        }

        $widsString = trim($wids,',');
        $widsStringcount = $widgets->count();


        return response()->json([
            "success" => true,
            "message" => "Follow up retrieved successfully.",
            "total_task" => $idsStringcount,
            "total_task_ids" => $idsString,
            "overdue_follow_up" => $ridsStringcount,
            "overdue_follow_up_ids" => $ridsString,
            "total_lead" => $widsStringcount,
            "total_lead_ids" => $widsString,
        ], 201);
                //        return $this->sendResponse(["count" => $records], 'Follow up retrieved successfully');
    }

    public function getResultOprDashboard(Request $request)
    {
        $data=[];
        $input = $request->all();
        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');

        /*$result = DB::table('customers_views as cv')
            ->leftJoin('customer_timelines as ct', 'cv.id', '=', 'ct.customer_id')
            ->select('ct.activity_type')
            // ->selectRaw('COUNT(DISTINCT cv.id) as count, GROUP_CONCAT(DISTINCT cv.id) as ids')
            ->selectRaw('
        CASE
            WHEN ct.activity_type = 1 THEN "Call"
            ELSE "Other"
        END AS activity_name
    ') //WHEN ct.activity_type = 3 THEN "Meeting"
            ->selectRaw('COALESCE(COUNT(ct.activity_type), 0) AS activity_type_count, GROUP_CONCAT(DISTINCT cv.id) as ids')
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 0)
            ->when($input['fil_user_id'] > 0, function ($query) use ($input) {
                return $query->where('cv.assigned_to_user', '=', $input['fil_user_id']);
            })
            ->when(in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm), function ($query) {
                return $query->where(function ($query) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id)
                        ->orWhere('cv.user_id', '=', $this->logged_user->id);
                });
            })
//            ->whereIn('ct.activity_type', [1, 2, 3])
            ->whereIn('ct.activity_type', [1])
            ->where(function ($query) use ($input) {
                $dateArr = explode("_", $input['date']);
                $query->whereBetween(DB::raw("DATE(ct.created_at)"), [$dateArr[0], $dateArr[1]]);
            })
            ->groupBy('ct.activity_type', 'activity_name')
            ->orderByDesc('ct.activity_type')
            ->get()->toArray();*/



        $result1 = DB::table('customers_views as cv')
            ->leftJoin('customer_timelines as ct', 'cv.id', '=', 'ct.customer_id')
            ->select('ct.activity_type')
            ->selectRaw('
        CASE
            WHEN ct.activity_type = 1 THEN "Call"
            ELSE "Other"
        END AS activity_name
    ')
            ->selectRaw('cv.id') // Select customer ID for later use
            ->where('cv.company_id', $this->company_id)
//            ->where('cv.some_day_flg', 0)
            ->when($input['fil_user_id'] > 0, function ($query) use ($input) {
                return $query->where(function ($query)use ($input) {
                    $query->where('ct.user_id', '=', $input['fil_user_id']);
                   /* $query->where('cv.assigned_to_user', '=', $input['fil_user_id']) CHX
                        ->orWhere('cv.user_id', '=', $input['fil_user_id']);*/
                });
//                return $query->where('cv.assigned_to_user', '=', $input['fil_user_id']);
            })
            ->when(
                in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) ||
                in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm),
                function ($query) {
                    return $query->where(function ($query) {
                        $query->where('ct.user_id', '=', $this->logged_user->id);
                       /*$query->where('cv.assigned_to_user', '=', $this->logged_user->id) CHX
                            ->orWhere('cv.user_id', '=', $this->logged_user->id);*/
                    });
                }
            )
            ->whereIn('ct.activity_type', [1])
            ->where(function ($query) use ($input) {
                $dateArr = explode("_", $input['date']);
                $query->whereBetween(DB::raw("DATE(ct.created_at)"), [$dateArr[0], $dateArr[1]]);
            })
            ->get();

// Manipulate the collection to group by activity_name and count occurrences
        $result = $result1->groupBy('activity_name')->map(function ($group) {
            return [
                'activity_name' => $group->first()->activity_name,
                'activity_type_count' => $group->count(),
                'ids' => $group->pluck('id')->unique()->implode(','),
            ];
        })->values()->sortByDesc('activity_name')->first();

        /*$result_message = DB::table('customers_views as cv')
            ->leftJoin('customer_timelines as ct', 'cv.id', '=', 'ct.customer_id')
            ->select('ct.activity_type')
            ->selectRaw('
        CASE
            WHEN ct.activity_type = 2 THEN "Message"
            ELSE "Other"
        END AS activity_name
    ') //WHEN ct.activity_type = 3 THEN "Meeting"
            ->selectRaw('COALESCE(COUNT(ct.activity_type), 0) AS activity_type_count, GROUP_CONCAT(DISTINCT cv.id) as ids')
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 0)
            ->when($input['fil_user_id'] > 0, function ($query) use ($input) {
                return $query->where('cv.assigned_to_user', '=', $input['fil_user_id']);
            })
            ->when(in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm), function ($query) {
                return $query->where(function ($query) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id)
                        ->orWhere('cv.user_id', '=', $this->logged_user->id);
                });
            })
//            ->whereIn('ct.activity_type', [1, 2, 3])
            ->whereIn('ct.activity_type', [2])
            ->where(function ($query) use ($input) {
                $dateArr = explode("_", $input['date']);
                $query->whereBetween(DB::raw("DATE(ct.created_at)"), [$dateArr[0], $dateArr[1]]);
            })
            ->groupBy('ct.activity_type', 'activity_name')
            ->orderByDesc('ct.activity_type')
            ->get()->toArray();*/



        $result_message1 = DB::table('customers_views as cv')
            ->leftJoin('customer_timelines as ct', 'cv.id', '=', 'ct.customer_id')
            ->select('ct.activity_type')
            ->selectRaw('
        CASE
            WHEN ct.activity_type = 2 THEN "Message"
            ELSE "Other"
        END AS activity_name
    ')
            ->selectRaw('cv.id') // Select customer ID for later use
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 0)
            ->when($input['fil_user_id'] > 0, function ($query) use ($input) {
                return $query->where(function ($query)use ($input) {
                    $query->where('ct.user_id', '=', $input['fil_user_id']);
                   /* $query->where('cv.assigned_to_user', '=', $input['fil_user_id']) CHX
                        ->orWhere('cv.user_id', '=', $input['fil_user_id']);*/
                });
//                return $query->where('cv.assigned_to_user', '=', $input['fil_user_id']);
            })
            ->when(
                in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) ||
                in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm),
                function ($query) {
                    return $query->where(function ($query) {
                        $query->where('ct.user_id', '=', $this->logged_user->id);
                        /*$query->where('cv.assigned_to_user', '=', $this->logged_user->id) CHX
                            ->orWhere('cv.user_id', '=', $this->logged_user->id);*/
                    });
                }
            )
            ->whereIn('ct.activity_type', [2])
            ->where(function ($query) use ($input) {
                $dateArr = explode("_", $input['date']);
                $query->whereBetween(DB::raw("DATE(ct.created_at)"), [$dateArr[0], $dateArr[1]]);
            })
            ->get();

// Manipulate the collection to group by activity_name and count occurrences
        $result_message = $result_message1->groupBy('activity_name')->map(function ($group) {
            return [
                'activity_name' => $group->first()->activity_name,
                'activity_type_count' => $group->count(),
                'ids' => $group->pluck('id')->unique()->implode(','),
            ];
        })->values()->sortByDesc('activity_name')->first();



        $sales_performance = DB::table('sales_person_performances')
            ->where(function ($query) use ($input) {
                $dateArr = explode("_", $input['date']);
                $query->whereBetween("performance_date", [$dateArr[0], $dateArr[1]]);
            })
            ->where(function ($query) use ($input) {
                if ($input['fil_user_id'] == 0) {
                    $query->where('company_id', '=', $this->company_id);
                }

                if ($input['fil_user_id'] > 0) {
                    $query->where('user_id', $input['fil_user_id']);
                    $query->where('company_id', $this->company_id);
                }
            })
            ->select(DB::raw("SUM(total_task) as total_task"), DB::raw("SUM(completed_task) as completed_task"))
            ->get()->toArray();

        $total_task = array_column($sales_performance, 'total_task');
        $completed_task = array_column($sales_performance, 'completed_task');


        $widgets = LeadStage::query()
            ->join('customers', function ($join) use ($input) {
                $join->on('customers.lead_stage_id', '=', 'lead_stages.id')
                    ->where(function ($query) {
                        if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                            //$query->where('customers.assigned_to_user', '=', $this->logged_user->id); //CHX
                            $query->where('customers.user_id', '=', $this->logged_user->id);
                        }
                    })
                    ->where(function ($query) use ($input) {
                        if ($input['fil_user_id'] == 0) {
                            $query->where('customers.company_id', '=', $this->company_id);
                        }

                        if ($input['fil_user_id'] > 0) {
//                            $query->where('customers.assigned_to_user', $input['fil_user_id']); //CHX
                            $query->where('customers.user_id', $input['fil_user_id']);
                        }
                    })
                    ->where(function ($query) use ($input) {
                        $dateArr = explode("_", $input['date']);
                        $query->whereBetween(DB::raw("DATE(customers.created_at)"), [$dateArr[0], $dateArr[1]]);
                    });

            })
            ->where('lead_stages.company_id', $this->company_id)
//            ->where('lead_stages.is_default', 1)
            //->select('lead_stages.name', 'lead_stages.color_code', 'lead_stages.id', DB::raw('COALESCE(COUNT(customers.id), 0) as widget_total'),DB::raw('GROUP_CONCAT(DISTINCT customers.id) as ids'))
                ->select('customers.id')
//            ->groupBy('lead_stages.id')
            ->orderBy('lead_stages.priority', 'asc')
            ->get();
            //->toArray();
//dd($widgets);
        $wids = '';
        foreach ($widgets as $chunk) {
            $wids .= $chunk->id.',';
        }

        $widsString = trim($wids,',');
        $widsStringcount = $widgets->count();


        $widgetsArray = DB::table(function ($query) use ($input) {
            $query->select(DB::raw('DISTINCT estimate_no, status'), 'company_id', 'customer_id')
                ->from('estimates')
                ->where(function ($query) use ($input) {
                    $dateArr = explode("_", $input['date']);
                    $query->whereBetween(DB::raw("DATE(estimate_date)"), [$dateArr[0], $dateArr[1]]);
                });
        }, 'subquery')
            ->leftjoin('customers_views', 'subquery.customer_id', '=', 'customers_views.id')
            ->where(function ($query) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                    $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
                    $query->orWhere('customers_views.user_id', '=', $this->logged_user->id);
                }
            })
            ->where(function ($query) use ($input) {
                if ($input['fil_user_id'] == 0) {
                    $query->where('customers_views.company_id', '=', $this->company_id);
                }

                if ($input['fil_user_id'] > 0) {
                    $query->where('customers_views.assigned_to_user', $input['fil_user_id']);
                    $query->orwhere('customers_views.user_id', $input['fil_user_id']);
                }
            })
            ->where('subquery.company_id', '=', $this->company_id)
            ->where('subquery.status', '!=', '')
            ->select('subquery.status', DB::raw('COUNT(subquery.status) as widget_total'))
            ->groupBy('subquery.status')
            ->get();

        $est_count = json_decode(json_encode($widgetsArray), true);

        $total = 0 + array_sum(array_column($est_count, 'widget_total'));
//dd($widgets);

        return response()->json([
            "success" => true,
            "message" => "Follow up retrieved successfully.",
            "message_count" => ($result_message)?$result_message['activity_type_count']:0,
            "message_count_ids" => ($result_message)?implode(",",explode(',',$result_message['ids'])):null,
            "response" => ($result)?$result['activity_type_count']:0,
            "response_ids" => ($result)?implode(",",explode(',',$result['ids'])):0,
            "total_task" => (int)$total_task[0],
            "new_lead_count" => $widsStringcount,
            "new_lead_count_ids" => $widsString,
            "est_count" => $total,
        ], 201);
                //        return $this->sendResponse(["count" => $records], 'Follow up retrieved successfully');
    }

    public function getPeriodicOprDashboard(Request $request)
    {
        $data=[];
        $input = $request->all();
        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');


        $sales_performance = DB::table('sales_person_performances')
            ->where('completed_task', '=', 1)
            ->where(function ($query) use ($input) {
                $dateArr = explode("_", $input['date']);
                $query->whereBetween(DB::raw("DATE(updated_at)"), [$dateArr[0], $dateArr[1]]);
            })
            ->where(function ($query) use ($input) {
                if ($input['fil_user_id'] == 0 && in_array('access-all-lead-and-assign-to-anyone-in-team',$this->user_perm)) {
                    $query->where('company_id', '=', $this->company_id);
                }

                if ($input['fil_user_id'] > 0) {
                    $query->where('user_id', $input['fil_user_id']);
                    $query->where('company_id', $this->company_id);
                }
            })
            ->where(function ($query) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                    $query->where('user_id', '=', $this->logged_user->id);
                    $query->where('company_id', '=', $this->company_id);
                }
            })
            //->select(DB::raw("SUM(total_task) as total_task"), DB::raw("SUM(completed_task) as completed_task"),DB::raw('GROUP_CONCAT(DISTINCT customer_id) as ids'))
            ->select("total_task", "completed_task","customer_id")
            ->get();

        $tasksCollection = collect($sales_performance);

        // Get the total_task values
        $totalTasks = $tasksCollection->sum('completed_task');
        $totalTaskssd = $tasksCollection->pluck('customer_id')->unique()->implode(',');

//        dd($totalTaskssd);
//        $total_task = array_column($sales_performance, 'total_task');
        $completed_task = $totalTasks;
        $completed_task_ids = $totalTaskssd;


        /*$lead_won_count =DB::table('customers_views as customers')
            ->leftJoin('customer_timeline_lead_lost_common as customer_timeline_lead_won', function ($join) use ($input) {
                $join->on('customer_timeline_lead_won.customer_id', '=', 'customers.id')
                    ->where(function ($query) {
                        if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                            $query->where('customers.assigned_to_user', '=', $this->logged_user->id);
                            $query->orwhere('customers.user_id', '=', $this->logged_user->id);
                        }
                    })
                    ->where(function ($query) use ($input) {
                        if ($input['fil_user_id'] == 0) {
                            $query->where('customers.company_id', '=', $this->company_id);
                        }

                        if ($input['fil_user_id'] > 0) {
                            $query->where('customers.assigned_to_user', $input['fil_user_id']);
                            $query->where('customers.company_id', $this->company_id);
                        }
                    })
                    ->where(function ($query) use ($input) {
                        $dateArr = explode("_", $input['date']);
                        $query->whereBetween(DB::raw("DATE(customer_timeline_lead_won.created_at)"), [$dateArr[0], $dateArr[1]]);
                    });
            })
            ->where('customers.company_id', $this->company_id)
            ->where('customers.lead_stage_name', 'Lead Won')
            ->where('customer_timeline_lead_won.activity_type',18)
            ->select(DB::raw('COALESCE(COUNT(customer_timeline_lead_won.customer_id), 0) as widget_total'))
//            ->groupBy('lead_stages.id')
            ->get()
            ->toArray();*/

        $lead_won_count = DB::table('customers_views as customers')
            ->join('customer_timeline_lead_lost_common as customer_timeline_lead_won', function ($join) use ($input) {
                $join->on('customer_timeline_lead_won.customer_id', '=', 'customers.id')
                    ->where(function ($query) use ($input) {
                        if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
//                            $query->where('customers.assigned_to_user', '=', $this->logged_user->id); CHX
//                            $query->orWhere('customers.user_id', '=', $this->logged_user->id);
                            $query->where('customer_timeline_lead_won.user_id', $this->logged_user->id);
                        }
                    })
                    ->where(function ($query) use ($input) {
                        if ($input['fil_user_id'] == 0) {
                            $query->where('customers.company_id', '=', $this->company_id);
                        }

                        if ($input['fil_user_id'] > 0) {
                            $query->where('customer_timeline_lead_won.user_id', '=', $input['fil_user_id']);
                            /*$query->where('customers.assigned_to_user', $input['fil_user_id']); CHX
                            $query->orwhere('customers.user_id', $input['fil_user_id']);*/
                        }
                    })
                    ->whereBetween(DB::raw("DATE(customer_timeline_lead_won.created_at)"), explode("_", $input['date']));
            })
            ->where('customers.company_id', $this->company_id)
            ->where('customers.lead_stage_name', 'Lead Won')
            /*->where(function ($query) {
                    $query->where('customers.last_follow_up_datetime', '=', '0000-00-00 00:00:00');
                    $query->orwhere('customers.last_follow_up_datetime', '=', '');
            })*/
            ->where('customer_timeline_lead_won.activity_type', 18)
//            ->select(DB::raw('COALESCE(COUNT(customer_timeline_lead_won.customer_id), 0) as widget_total'),DB::raw('GROUP_CONCAT(DISTINCT customer_id) as ids'))
            ->select('customer_timeline_lead_won.customer_id')
            ->get();

        $lead_won_count = collect($lead_won_count);

        $lead_won_total = $lead_won_count->unique('customer_id')->count();
        $lead_won_total_ids = $lead_won_count->pluck('customer_id')->unique()->implode(',');



        $lead_lost_count = DB::table('customers_views as customers')
            ->join('customer_timeline_lead_lost_common as customer_timeline_lead_lost', function ($join) use ($input) {
                $join->on('customer_timeline_lead_lost.customer_id', '=', 'customers.id')
                    ->where(function ($query) {
                        if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                            /*$query->where('customers.assigned_to_user', '=', $this->logged_user->id); CHX
                            $query->orwhere('customers.user_id', '=', $this->logged_user->id);*/
                            $query->where('customer_timeline_lead_lost.user_id', $this->logged_user->id);
                        }
                    })
                    ->where(function ($query) use ($input) {
                        if ($input['fil_user_id'] == 0) {
                            $query->where('customers.company_id', '=', $this->company_id);
                        }

                        if ($input['fil_user_id'] > 0) {
//                            $query->where('customers.assigned_to_user', $input['fil_user_id']);CHX
                            $query->where('customer_timeline_lead_lost.user_id', $input['fil_user_id']);
                        }
                    })
                    ->where(function ($query) use ($input) {
                        $dateArr = explode("_", $input['date']);
                        $query->whereBetween(DB::raw("DATE(customer_timeline_lead_lost.created_at)"), [$dateArr[0], $dateArr[1]]);
                    });
            })
            ->where('customers.company_id', $this->company_id)
            ->where('customers.lead_stage_name', 'Lead Lost')
            /*->where(function ($query) {
                $query->where('customers.last_follow_up_datetime', '=', '0000-00-00 00:00:00');
                $query->orwhere('customers.last_follow_up_datetime', '=', '');
            })*/
            ->where('customer_timeline_lead_lost.activity_type',17)
//            ->select(DB::raw('COALESCE(COUNT(customer_timeline_lead_lost.customer_id), 0) as widget_total'),DB::raw('GROUP_CONCAT(DISTINCT customer_id) as ids'))
            ->select('customer_timeline_lead_lost.customer_id')
//            ->groupBy('lead_stages.id')
            ->get();

        $lead_lost_count = collect($lead_lost_count);
        $lead_lost_total = $lead_lost_count->unique('customer_id')->count();
        $lead_lost_total_ids = $lead_lost_count->pluck('customer_id')->unique()->implode(',');


//        $lead_lost_total = $lead_lost_count->isEmpty() ? 0 : $lead_lost_count->first()->widget_total;
//        $lead_lost_total_ids = $lead_lost_count->isEmpty() ? 0 : implode(",",explode(',',$lead_lost_count->first()->ids));
        $result='';
        if($this->main_company->company_category > 0) {

            /*$result = DB::table('customers_views as cv')
                ->leftJoin('customer_timelines as ct', 'cv.id', '=', 'ct.customer_id')
                ->select('ct.activity_type')
                ->selectRaw('
        CASE
            WHEN ct.activity_type = 3 THEN "Meeting"
            ELSE "Other"
        END AS activity_name
    ')
                ->selectRaw('COALESCE(COUNT(ct.activity_type), 0) AS activity_type_count, GROUP_CONCAT(DISTINCT cv.id) as ids')
                ->where('cv.company_id', $this->company_id)
                ->where('cv.some_day_flg', 0)
                ->when($input['fil_user_id'] > 0, function ($query) use ($input) {
                    return $query->where('cv.assigned_to_user', '=', $input['fil_user_id']);
                })
                ->when(in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm), function ($query) {
                    return $query->where(function ($query) {
                        $query->where('cv.assigned_to_user', '=', $this->logged_user->id)
                            ->orWhere('cv.user_id', '=', $this->logged_user->id);
                    });
                })
                ->whereIn('ct.activity_type', [3])
                ->where(function ($query) use ($input) {
                    $dateArr = explode("_", $input['date']);
                    $query->whereBetween(DB::raw("DATE(ct.created_at)"), [$dateArr[0], $dateArr[1]]);
                })
                ->groupBy('ct.activity_type', 'activity_name')
                ->orderByDesc('ct.activity_type')
                ->get()->first();*/

            $result1 = DB::table('customers_views as cv')
                ->leftJoin('customer_timelines as ct', 'cv.id', '=', 'ct.customer_id')
                ->select('ct.activity_type')
                ->selectRaw('
        CASE
            WHEN ct.activity_type = 3 THEN "Meeting"
            ELSE "Other"
        END AS activity_name
    ')
                ->selectRaw('cv.id') // Select customer ID for later use
                ->where('cv.company_id', $this->company_id)
                ->where('cv.some_day_flg', 0)
                ->when($input['fil_user_id'] > 0, function ($query) use ($input) {
                    //return $query->where('cv.assigned_to_user', '=', $input['fil_user_id']);
                    return $query->where(function ($query)use ($input) {
                        $query->where('ct.user_id', '=', $input['fil_user_id']);
                        /*$query->where('cv.assigned_to_user', '=', $input['fil_user_id']) CHX
                            ->orWhere('cv.user_id', '=', $input['fil_user_id']);*/
                    });
                })
                ->when(
                    in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) ||
                    in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm),
                    function ($query) {
                        return $query->where(function ($query) {
                            $query->where('ct.user_id', '=', $this->logged_user->id);
//                            $query->where('cv.assigned_to_user', '=', $this->logged_user->id) CHX
//                                ->orWhere('cv.user_id', '=', $this->logged_user->id);
                        });
                    }
                )
                ->whereIn('ct.activity_type', [3])
                ->where(function ($query) use ($input) {
                    $dateArr = explode("_", $input['date']);
                    $query->whereBetween(DB::raw("DATE(ct.created_at)"), [$dateArr[0], $dateArr[1]]);
                })
                ->get();

// Manipulate the collection to group by activity_name and count occurrences
            $result = $result1->groupBy('activity_name')->map(function ($group) {
                return [
                    'activity_name' => $group->first()->activity_name,
                    'activity_type_count' => $group->count(),
                    'ids' => $group->pluck('id')->unique()->implode(','),
                ];
            })->values()->sortByDesc('activity_name')->first();
        }
        $result_visit='';
        if($this->main_company->company_category == 1) {
//            $result_visit = DB::table('customers_views as cv')
//                ->leftJoin('customer_timelines as ct', 'cv.id', '=', 'ct.customer_id')
//                ->select('ct.activity_type')
//                ->selectRaw('
//        CASE
//            WHEN ct.activity_type = 19 THEN "Meeting"
//            ELSE "Other"
//        END AS activity_name
//    ')
//                ->selectRaw('COALESCE(COUNT(ct.activity_type), 0) AS activity_type_count, GROUP_CONCAT(DISTINCT cv.id) as ids')
//                ->where('cv.company_id', $this->company_id)
//                ->where('cv.some_day_flg', 0)
//                ->when($input['fil_user_id'] > 0, function ($query) use ($input) {
//                    return $query->where('cv.assigned_to_user', '=', $input['fil_user_id']);
//                })
//                ->when(in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm), function ($query) {
//                    return $query->where(function ($query) {
//                        $query->where('cv.assigned_to_user', '=', $this->logged_user->id)
//                            ->orWhere('cv.user_id', '=', $this->logged_user->id);
//                    });
//                })
//                ->whereIn('ct.activity_type', [19])
//                ->where(function ($query) use ($input) {
//                    $dateArr = explode("_", $input['date']);
//                    $query->whereBetween(DB::raw("DATE(ct.created_at)"), [$dateArr[0], $dateArr[1]]);
//                })
//                ->groupBy('ct.activity_type', 'activity_name')
//                ->orderByDesc('ct.activity_type')
//                ->get()->first();
            // Fetch the data without grouping
            $result_visit1 = DB::table('customers_views as cv')
                ->leftJoin('customer_timelines as ct', 'cv.id', '=', 'ct.customer_id')
                ->select('ct.activity_type')
                ->selectRaw('
        CASE
            WHEN ct.activity_type = 19 THEN "Meeting"
            ELSE "Other"
        END AS activity_name
    ')
                ->selectRaw('cv.id') // Select customer ID for later use
                ->where('cv.company_id', $this->company_id)
                ->where('cv.some_day_flg', 0)
                ->when($input['fil_user_id'] > 0, function ($query) use ($input) {
                    //return $query->where('cv.assigned_to_user', '=', $input['fil_user_id']);
                    return $query->where(function ($query)use ($input) {
                        $query->where('ct.user_id', '=', $input['fil_user_id']);
                        /*$query->where('cv.assigned_to_user', '=', $input['fil_user_id']) CHX
                            ->orWhere('cv.user_id', '=', $input['fil_user_id']);*/
                    });
                })
                ->when(
                    in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) ||
                    in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm),
                    function ($query) {
                        return $query->where(function ($query) {
                            $query->where('ct.user_id', '=', $this->logged_user->id);
                            /*$query->where('cv.assigned_to_user', '=', $this->logged_user->id) CHX
                                ->orWhere('cv.user_id', '=', $this->logged_user->id);*/
                        });
                    }
                )
                ->whereIn('ct.activity_type', [19])
                ->where(function ($query) use ($input) {
                    $dateArr = explode("_", $input['date']);
                    $query->whereBetween(DB::raw("DATE(ct.created_at)"), [$dateArr[0], $dateArr[1]]);
                })
                ->get();

// Manipulate the collection to group by activity_name and count occurrences
            $result_visit = $result_visit1->groupBy('activity_name')->map(function ($group) {
                return [
                    'activity_name' => $group->first()->activity_name,
                    'activity_type_count' => $group->count(),
                    'ids' => $group->pluck('id')->unique()->implode(','),
                ];
            })->values()->sortByDesc('activity_name')->first();

        }

        return response()->json([
            "success" => true,
            "message" => "Follow up retrieved successfully.",
            "response" => ($result)? $result['activity_type_count'] : 0,
            "response_ids" => ($result)? $result['ids'] : null,
            "response_visit" => ($result_visit)? $result_visit['activity_type_count'] : 0,
            "response_visit_ids" => ($result_visit)? $result_visit['ids'] : null,
            "total_task" => $completed_task,
            "total_task_ids" => $completed_task_ids,
            "lead_won_count" => $lead_won_total,
            "lead_won_count_ids" => $lead_won_total_ids,
            "lead_lost_count" => $lead_lost_total,
            "lead_lost_count_ids" => $lead_lost_total_ids,
        ], 201);
        //        return $this->sendResponse(["count" => $records], 'Follow up retrieved successfully');
    }

    public function spData()
    {

        $mobileStr = '9823024567, 9687584781, 9879906174, 7600079799, 9849026190, 7048123199, 6281434557, 8306781161, 9624432500, 9374660512, 9875076537, 9694246111, 9510499000, 8320510122, 7567753376, 9909266263, 8980802380, 9228330947, 9974762601, 9825754704, 9376185202, 9426396161, 8433977775, 9825066992, 7567092183, 9112242207, 9173570020, 9687280181, 9825037567, 9825658146, 9825005613, 7600022095, 9157787736, 9624695903, 9374039500, 7600022095, 9427217905, 9726378544, 9825741741, 9726005774, 9033822527, 9824419948, 9909994174, 7795000801, 8735997171, 9898718896, 9908655786, 9894612121, 9016527457, 9998888659, 9925421021, 9825577448, 9179530455, 9909272706, 9725832846, 9030466208, 9173895999, 8735997171, 9998910007, 9443917625, 8463854564, 7977540187, 7778955420, 9167238684, 8879551291, 9766776565, 8208459848, 9893825999, 9880885054, 9904637421, 9167109352, 9819991737, 9998123479, 7413916449, 9164591600, 9902550153, 9009422524, 9637995075, 8000006436, 7780555194, 9099012932, 9806312219, 8980302204, 8072314620, 8885789558, 8849806219, 8140810038, 9427785119, 9913322727, 9104251416, 8401578042, 9574208702, 8866866929, 7600079799, 9300014142, 9424397327, 7028635143, 9824939759, 9337102459, 9998613265, 9058900206, 8875821151, 7984419605, 9773263163, 8866852233, 9158404392, 9964210138, 8180945555, 9737205433, 9227685324, 9067349449, 9824140469, 9111432040, 7434804959, 8608749330, 9945078459, 8708354768, 9427134191, 9922802886, 7405496360, 9376138300, 9226763560, 7778877480, 9890543270, 7600511001, 9111112148, 9821808382, 8758840246, 9902282166, 9662432721, 7060248748, 9845085790, 9619670004, 9866984457, 9881395706, 9893538558, 9825129711, 9945799084, 8411848400, 9867708245, 9928633399, 9530150000, 8200476098, 9045107447, -94710503706, 9913768613, 9799727374, 9521793808, 9413371583, 9265577452, 9920606752, 8160775511, 9371126367, 8309796361, 9424590549, 7020278202, 8875440000, 9737995716, 6355723083, 9979889999, 7874947317, 9827243108, 8329616266, 9825492550, 9173486678, 9016222888, 9824239162, 9824328926, 9913381959, 9825413133, 9825544468, 9773462345, 7567524500, 8003456291, 8200970207, 7793800232, 8200970207, 6351993576, 9440981545, 9924246311, 9925532663, 9925532663, 9772656484, 9799952799, 9171339417, 9930799469, 9284575324, 9769260531, 9377699549, 9879444135, 9898645341, 9443512193, 9011022300, 9825479604, 9825448995, 9374714061, 7990634564, 9825296972, 8460094084, 9638840070, 9825433753, 9106878453, 7718010435, 8866329661, 8733850135, 9818342343, 9825280646, 9974457892, 9924352173, 7869435050, 8209557909, 7020888653, 9879596966, 9106298174, 8839345275, 9998745304, 9638919923, 9004300062, 9966333059, 9925428317, 9974952719, 8824670705, 9998934493, 9723897264, 7995650245, 9024877122, 9422020416, 7046436769, 9351004660, 9824582206, 9414631665, 9448295036, 9550020146, 7405785031, 7620304969, 8208800934, 7386668733, 8000535333, 9898595889, 8888998714, 9769463848, 9036959981, 9790493484, 8109185322, 9225238663, 9784378002, 9909977332, 8888808232, 8952881198, 8077560920, 8368447574, 9974672453, 9823025523, 8320920235, 9428819900, 9510760320, 8451060490, 9327728497, 9879085861, 9785018187, 8668306706, 8952947572, 9909469546, 9924774495, 9978299438, 9978498981, 9319515290, 6261474346, 7987121348, 9537377799, 9724359556, 7999047598, 9016812376, 9574879844, 9890647133, 9409928690, 9913644921, 9016592949, 9893303123, 9726970998, 9099410910, 8018621048, 9371126367, 7509924354, 8698276726, 9825554162, 9426371110, 9998747411, 9320023467, 9833254084, 9922118495, 9825760555, 7981269929, 9130402007, 9673337815, 9427121778, 9372873504, 9414280794, 9640331112, 9700666127, 9036959981, 6360142425, 8487998618, 9924868000, 9879921559, 9607378378, 8390100726, 8103442500, 8356810841, 9106288441, 8248073847, 9465420006, 9849428865, 9979498890, 7740900346, 8928782173, 9314961278, 8209134689, 9765883955, 9518308067, 9133770775, 9825477470, 9414244398, 8401619964, 9921349666, 9491290207, 9505302057, 9130335868, 9371126367, 9021438621, 9970240255, 7989042944, 9461240472, 8141383133, 9920600487, 7990307049, 9289555153, 9763539444, 9713747777, 8451060490, 9099449679, 8866537046, 9898848394, 9815682205, 9449647845, 8291166179, 9904233329, 9887006222, 7406350007, 7727999940, 9770044124, 9867234835, 9910566996, 9337888182, 9948611265, 9425134744, 9924988813, 8551978569, 9106928504, 9979890557, 9171339417, 9908476109, 7506170568, 9845271701, 7622022121, 9823398120, 8742048843, 8078646345, 9824017427, 9636278284, 7389957481, 9833648926, 9422775750, 7383021073, 9586363631, 7201000180, 9904120069, 8141814350, 9726461516, 9594729852, 8000893193, 9825299600, 9763539444, 9925433153, 9652364269, 8200419522, 9860838527, 9509934660, 7397826767, 9166644419, 9398409273, 9871943659, 7005947743, 8130738968, 9080082628, 9582626374, 9165345345, 9898055383, 9247461213, 9461356601, 8094962029, 8357800008, 9909283459, 9013238022, 9421286622, 9849576512, 8209951956, 9429060822, 7978640071, 7425899293, 9928937841, 9966977950, 9916141071, 9414391062, 9623607065, 6299041682, 8770605152, 8275589413, 9893284223, 9022040195, 9986674359, 7990801780, 9824273705, 9763449273, 8871738315, 9966092801, 8805054455, 9924442561, 9591366371, 9909010545, 9739427159, 9428737320, 8460482373, 9782806576, 8879200037, 9950517949, 9778512777, 8769422453, 7818999346, 7297070969, 7976743457, 8058896869, 9284094593, 8058925656, 8087171040, 9400829808, 9723841515, 7046266924, 8984948563, 9428226100, 9784723593, 7086135261, 9913332946, 9441157198, 8905910716, 9886980050, 9942111874, 9214438824, 9829071375, 7738498264, 9820070685, 9943314537, 9022119990, 9461166972, 9982009555, 9314230970, 9890065018, 7985280764, 9032051183, 7568897162, 9966300776, 7791859774, 9099335333, 8875341503, 8875555193, 9783880450, 6397753330, 8766862062, 9703218909, 9913171980, 8179179111, 9542237353, 9848271911, 9981024647, 7045819173, 8949802432, 7506073267, 9826950635, 9602360177, 9009093935, 8329828879, 9753381904, 7620733106, 7904537981, 8169332266, 9030204950, 8511556089, 9502266756, 9039572272, 9898616268, 8779053904, 9047792715, 9827028484, 6302736449, 9811123762, 7984058293, 9879341191, 8239331111, 7990128039, 9879892529, 7676162008, 7776048392, 9848409625, 9737211849, 9321360957, 9940278434, 8697367972, 9016898881, 9925428317, 9828523023, 9820063205, 9301316448, 8989786999, 9265115542, 7524963338, 9822600503, 9351711477, 9820051217, 9242116120, 9841191007, 9924445222, 9904911137, 9030057201, 8951620493, 9887110916, 9727388773, 9075818922, 8234842211, 9824258520, 8779890797, 7284065654, 9373221101, 9371015547, 9009752601, 9887402426, 9182230198, 8349190940, 9079637900, 6354279578, 9414735191, 9443933327, 8169258106, 9309344466, 9657629711, 9314139848, 9987170229, 9371921115, 9982722323, 9408514471, 9930580909, 9898960059, 9770044124, 8122443321, 9829850042, 9821069526, 9926530719, 9970222738, 7974527438, 8149096292, 9359054134, 9099663713, 8978473619, 8793607040, 7878182618, 9327045087, 9176676797, 7972194781, 9825317494, 9353927221, 9460970205, 9924326895, 9703114414, 7574011722, 8147369576, 9824121131, 7066338858, 9737740380, 9421890047, 9408258897, 8969777743, 9571342828, 6232630107, 9879594055, 8764055666, 9925765717, 9743768402, 6301270421, 9111381113, 9449059742, 9839605217, 9067873948, 9928484485, 9727755174, 9977698680, 8238272777, 9989442567, 9247440000, 9624245577, 9179562656, 8454974808, 7415613134, 9867901287, 9552655477, 7208877909, 9821120907, 9769809469, 9970230301, 9913068486, 9427044310, 9390727843, 9979186941, 9892587929, 9427557776, 7709484506, 8999896969, 7871370713, 9039527098, 9766257179, 7984261648, 9384568345, 7228800052, 8460850345, 9825616065, 8999419707, 9221616342, 8087555536, 8840477601, 9691077540, 9998668483, 9443856103, 7737901465, 9588831152, 8870702930, 9983837142, 9351873183, 9898810381, 7899278343, 9552860937, 9845501986, 7728007072, 9347420610, 9981293200, 9822081308, 9574879844, 7297070969, 9414611269, 9824254720, 9403272264, 9529842011, 7977394745, 9629595697, 9922608961, 9284080140, 9887834946, 9928699899, 9057597033, 9509221368, 6261421009, 9844817672, 9049994700, 7878980263, 9510889281, 9636026644, 8197777020, 8008036611, 8209112588, 9726780714, 9685587444, 9429079200, 9421262373, 9033744394, 8180027412, 8317684443, 9920531261, 9673285628, 9890051687, 9770786365, 9422883683, 9252082934, 9819007091, 9828092535, 9079844932, 9760466136, 9769023199, 8655036068, 9930185021, 9676462772, 9963800766, 6201826443, 9571946508, 9722020771, 8805130880, 8769411823, 9985897865, 9770528004, 9941766888, 9680661001, 9825889669, 9028999323, 8888846716, 9833791629, 9970346840, 7340176317, 9893173341, 7026558460, 9729489630, 8141939234, 9966533228, 9019996860, 9879371336, 9820475718, 8169324051, 9011011239, 9130204660, 9926884450, 9632282977, 9967690875, 9699225325, 9575101021, 9672198196, 9100034153, 7000238538, 9022548818, 7014348459, 9649754944, 8233534074, 9834803984, 8160104571, 8668897941, 8905389054, 9001000916, 9549985925, 9553987999, 7710942238, 9636688552, 9825830214, 9825728985, 9824734649, 9057366198, 9828149603, 8837214499, 9005201292, 8619699249, 9898877448, 9825589183, 7276513875, 9860295294, 9940909398, 9413959636, 9292922992, 9075357631, 7013506588, 7869617517, 9892661201, 7021664827, 8096949714, 8955521365, 8154902324, 9702116284, 9481722444, 7977688126, 9880152040, 9892661201, 9110445079, 9986019435, 7010933781, 9460346201, 7447507071, 6355596039, 7405723516, 8238666823, 8347424174, 9618077013, 9328887999, 8879895686, 8839825066, 8762748699, 7021491761, 8150944445, 9653112305, 9111200793, 9340018205, 7899912341, 7020308323, 8130299579, 9825833789, 9752548871, 9769495594, 8328440189, 9972431491, 9461223955, 9440215729, 8329561109, 9461007770, 8121644916, 6398775909, 9377252114, 7891298771, 7676139425, 8939463811, 9752130786, 9882557660, 9712394190, 8977187537, 8875322293, 9428580971, 9352586051, 8547048925, 9687348548, 7000634356, 9587472402, 9940805210, 9950266162, 9822295122, 9594204983, 9723121909, 8080807500, 8209755004, 9828092131, 9078834190, 9601896444, 9315789065, 9461153171, 9869820445, 9998486831, 7287011011, 8169178256, 9829252947, 8885332273, 9460304555, 8160789910, 9035042295, 8208417270, 9320911126, 9882457059, 8839291086, 8224818864, 9510836367, 9518337819, 9766151486, 8951860867, 9712025007, 9527291239, 8264931486, 7231910228, 8460371208, 8140344555, 9537499308, 9825265718, 7000785849, 9829434919, 8109546662, 8511180059, 9827341801, 7666440127, 9819757572, 8890127711, 9636211933, 8459575959, 9035193814, 9879717314, 9428074195, 9928982085, 9845233343, 7828184413, 9887803712, 9377071800, 9487167666, 6261294868, 9898255657, 8000173822, 9265515266, 9030177114, 8866774458, 9867772566, 7227909072, 7023420277, 9662384084, 9095405550, 9571946508, 8982108898, 8780201358, 9033733263, 9099430077, 9950731546, 9845565905, 9537185860, 8003600385, 7976508283, 8085534603, 9772026458, 8652300970, 8319598017, 9160666665, 9885677996, 7567932231, 9898457862, 9594204983, 8949743294, 9411456071, 7566838039, 7984242118, 9667071379, 9845259242, 9460104980, 9825263937, 9636688552, 9944169086, 9978702777, 8141339854, 9701812541, 9314259511, 8562849982, 8921634305, 8058087618, 7041387744, 9915415402, 9874204910, 9158187825, 8082750205, 9414601678, 9011850409, 9982833330, 8109755571, 7483217024, 7357690160, 9982833330, 9424588171, 9143765408, 8094854242, 9950035868, 7550279565, 9487455958, 7046854991, 7293173162, 9509828367, 9011011239, 9623303786, 9021555544, 8885646222, 9160455073, 9370604075, 9599960849, 7070628974, 9993530940, 9827539556, 9887261665, 9444734279, 9828411067, 9977333321, 9082447172, 7989042944, 7415682792, 9640951517, 7202014140, 9359056719, 8217840011, 8381010111, 9985100936, 9447305642, 9302422222, 8055629099, 9769224993, 9099711312, 9052014014, 7780492878, 9800841999, 9869156668, 9825030435, 8003991965, 9158709375, 9251261843, 7982545211, 9867684029, 9428112930, 8742048843, 9772603001, 9047240630, 9414210414, 9468330043, 9427495664, 9982343007, 9168196388, 9828483817, 9584511437, 8200255842, 9487455958, 9001888821, 7014309604, 8849733114, 8860483251, 9925665347, 9829962184, 9008455433, 9725211191, 9826014045, 9924406979, 8128181255, 8880727963, 8160404399, 7899423165, 7877139319, 7014785376, 9374884807, 9974603143, 9825321627, 7412044730, 9413425848, 9030405008, 8764263763, 9413226055, 9908541011, 9409088000, 9444054257, 8378929691, 8722082323, 8511936697, 8770974671, 8080936893, 9610570166, 9982253023, 9016505398, 9214471471, 9600043124, 9902329502, 7878334411, 9599674203, 9632114581, 9782762689, 9840994491, 7016926983, 8319505183, 9702220066, 9993551333, 9503322023, 9574018397, 7999738014, 9413518839, 9588227104, 9846973738, 9755717437, 7892122633, 9448627176, 9685568888, 7728970899, 9535398829, 9422066431, 9769369048, 7990880336, 8095775468, 8526776903, 7014813145, 9649574428, 9214725100, 7738576457, 8667472221, 7427085199, 9636565101, 9840636111, 9414218112, 9928890736, 9978205295, 9246877782, 9428397317, 8217022236, 8769422453, 9929385258, 8849760959, 9214697085, 9983395565, 9376060800, 9829155408, 9943524573, 9076969443, 7665759136, 7976204938, 8619442943, 7014336501, 9571841901, 9265286628, 9394843636, 9579263119, 8105285015, 9723702486, 7728911012, 9950763294, 7869220420, 9824261212, 9374935031, 9116934070, 8488090677, 9898892869, 9662022847, 8124920101, 7016908634, 7405555758, 8019626311, 9131573628, 9828027044, 9414410305, 9925760770, 9016182816, 9537234579, 7014912350, 9440912324, 9373352777, 9425645519, 7744906521, 9810742222, 9381373029, 8600270736, 9833747139, 6369261732, 8949144641, 8919368866, 8058719442, 9024052072, 7976374903, 9316719550, 8511535898, 9571817443, 9928907397, 9898406235, 9449222371, 9449628949, 9866336491, 9423741040, 9949605142, 9112808865, 7093308176, 9848665771, 9829246461, 8639444651, 9373010547, 8827289246, 8600150900, 9228891900, 9842188438, 9996406306, 9079137494, 8983151151, 9024428284, 7891067755, 7698915121, 9959430047, 9021555544, 9764839966, 9448537033, 8310585890, 7789106065, 7874889918, 9841451236, 8553960438, 8358859624, 7228903400, 7016157415, 8955705555, 9869559338, 6375091867, 7666058356, 9825354712, 9323283889, 9887666628, 9680147625, 9493100982, 9908290582, 8369718545, 9121657713, 9444107785, 9414252881, 9414042711, 9983798000, 8107518530, 8825584838, 9904368266, 9762278184, 8696967111, 9849080798, 9633505006, 9081612127, 8780201358, 8838129125, 9976937314, 9380873206, 9314506792, 9607455999, 8058023262, 8109472111, 7411598637, 9909484138, 9558593889, 9314965472, 9623976004, 9752179390, 7003375570, 8980002448, 7013920734, 9893085388, 9824025090, 8427877336, 9950777910, 9825356619, 8007230300, 9942838684, 9930786345, 9660320181, 9025039505, 9767308593, 6281002473, 8449808581, 8527101699, 9527640158, 9842877110, 6350298590, 9929988702, 7972254908, 9950611945, 9182391405, 9714444292, 9344730241, 8239540183, 9413290678, 9824854418, 9309257558, 7260866507, 9922400964, 9958018447, 8285070578, 9455622394, 8277076270, 9137058962, 9822220196, 8330077591, 9908230650, 9680391821, 9448673988, 9825014349, 8980599680, 8877820768, 8277076270, 9003935675, 9664470074, 9425426628, 6200992426, 8160696235, 8017765104, 8698397927, 9866136143, 9482507876, 7387999601, 9588944787, 9974207564, 9693706069, 8279772153, 7725947777, 7760848434, 7842749983, 9559534794, 7737566523, 7289067969, 9427953101, 8277076270, 9440546465, 9160266072, 9664549151, 9548434684, 8955190776, 9924590564, 9820667601, 9904578000, 9494680784, 9571677988, 9711657838, 8349359965, 8588051477, 8329231212, 9730539666, 9462543365, 9887307594, 9982833330, 7583959942, 9137573990, 8003909844, 9025838314, 8267911895, 9712730679, 8268021300, 8604015864, 8423560675, 8600218055, 8306223005, 9623171750, 9417388412, 9703611986, 8330077591, 9081731162, 8149391432, 9985788942, 9879930199, 9110378835, 9414534908, 9370399442, 8141588844, 9755006616, 8105212438, 9898675854, 9826152366, 9894134224, 8209475061, 8855058692, 9924526902, 9033824456, 9849569142, 8329962271, 9860851515, 8689939930, 9329873935, 9885958143, 6362706553, 9075192688, 8828084661, 7977745006, 9850980977, 7878001368, 9785226289, 9099430077, 9573233408, 9979151513, 7568409777, 9359624288, 9758425111, 9879381832, 7427013355, 9820807699, 9958539576, 9460661235, 9662822980, 9772347258, 8109538318, 9967530502, 8088250265, 9022221987, 9538660039, 9625631650, 8309552050, 9351120707, 9972166973, 9414844087, 9322865462, 7776020577, 8618579796, 8074951955, 9001856069, 9966358843, 9591399725, 9986297703, 9004515314, 9029870597, 9978445856, 9003329984, 9881007353, 8411956710, 9426647942, 8619578688, 7798155557, 9680702007, 8890934732, 9982804884, 9380893010, 7020372910, 8003403740, 9461317459, 9789851528, 8310532143, 9589421436, 9413960271, 9414332455, 9521919090, 8905045451, 7976750416, 8320956306, 9928040056, 7757080797, 9214060646, 8830686746, 9922155221, 8866261689, 9829055461, 9841844555, 9904892268, 9820274422, 6363938553, 8000480102, 9886747232, 8160028973, 7019648338, 9511528390, 6353560593, 7016586807, 9712642558, 9642956567, 8817110310, 9845477846, 9685860890, 9841317995, 9894896916, 9940042156, 7770862911, 9694927238, 9029276530, 9711582719, 9427896221, 6354947552, 9898054203, 9788660247, 9414490021, 9929586594, 9428694216, 9166884028, 8310585809, 7984704237, 9043067891, 9879996796, 9739933179, 8767888961, 9173022195, 8866692911, 9824441440, 9985851910, 9156537786, 9820398133, 9703804660, 9970860439, 7874686739, 9747778441, 9782042252, 9594983824, 9604067320, 9413703770, 8097338730, 9284262051, 9996445205, 9994527847, 9314586203, 9414412156, 7417779865, 8980773599, 9892471961, 9819846225, 9901772025, 9702004451, 9326879274, 9328425885, 8956103048, 7820011343, 6354922352, 9167904139, 8511843234, 9443281806, 9059750052, 9981393295, 9033891820, 9265148988, 9894670623, 8698525200, 9303055092, 9558759425, 9703864108, 9820611600, 9833405620, 9610621621, 9562991111, 9823557024, 9860098713, 8754145608, 8200417572, 9414580120, 9408209181, 7014963410, 9414374762, 9916556666, 9886581433, 8233330900, 8866991135, 7733003021, 8078665955, 9413031367, 9667895557, 7600606472, 9426411194, 9694708911, 9714885774, 9952001221, 9672556672, 8078620980, 9414444131, 8309822964, 9727075598, 9666930003, 9660166966, 9929179929, 9314298130, 9833233767, 9896332906, 9290503777, 7597199946, 9782743544, 9549904444, 9558592559, 7624002261, 7000552671, 7990508234, 9441387584, 9672776261, 8770031312, 8897968894, 7588636901, 7020975278, 8779190330, 9825139906, 9840214401, 9785398214, 9702221836, 9251307585, 7502929523, 9620561288, 9529644885, 9425386850, 9979895459, 9960812423, 9841090343, 9981966889, 8277455095, 9314141364, 9928644220, 9925482864, 7043463640, 9460477902, 8754012291, 9167632643, 9420873062, 7676703736, 7073850012, 9819327122, 9829310070, 9414039291, 9130088313, 9043725196, 6355499783, 9403063859, 8962128887, 7014661745, 8696753447, 8838005985, 9252672109, 9314427000, 9828753828, 8949897001, 9998624122, 7715808553, 9003029994, 9573427299, 8079003787, 8898967660, 8005617285, 7386468448, 7678006212, 7383392124, 9327345671, 7892791435, 9825054897, 9179485859, 9820186320, 9426596601, 7981837285, 8074330512, 9649806467, 6350308156, 7416721424, 7900048076, 9223402008, 8519909859, 8905810495, 9558375143, 9510890926, 9166809976, 7568892537, 9987825692, 9314712008, 7046355558, 9003953732, 9866567505, 9133586789, 8088390021, 7600634488, 9095887700, 9829133215, 6383347341, 9762274117, 9414754154, 9008867958, 8850173938, 8886450432, 9844165653, 8879621153, 9730806997, 9375783535, 7676419045, 9773297409, 91-11-46710500, 9770822777, 8010480963, 9967443914, 9672729539, 9688333609, 9225867307, 9909247447, 9898479270, 9908778869, 7877626210, 9942477462, 9950623759, 8688492178, 8758455597, 8128877742, 9819102299, 9377532875, 9985717806, 8209228897, 9644545401, 7875095946, 8928162481, 9703035041, 7000963657, 9844060995, 9912625776, 9925619931, 7045296992, 7416286674, 9799648551, 8098926799, 9444082330, 7904790968, 9549910071, 7838553303, 9769666256, 8825659221, 9887673632, 9833000893, 8660469363, 9351371303, 9995037335, 8970196925, 9024222264, 8094750700, 8892667118, 9037909090, 9924291894, 8302288391, 9999981295, 9820770393, 7799177994, 8248175237, 7676969661, 8905077157, 8101734389, 9166633663, 9944139883, 9460042922, 9490535058, 9772997739, 9947134799, 7976561589, 7013998866, 9978673000, 9979337766, 8208998249, 9251447528, 9033333727, 9325487113, 9480206818, 9822159630, 9789876127, 9443281023, 9866997674, 7010372782, 9636575736, 7796064136, 8983377785, 9087874725, 9845657921, 9841474126, 7490831809, 7006143643, 9167701257, 9928718951, 91-11-46710500, 9980032746, 7287874481, 8128250699, 8939101542, 9512450995, 9886930555, 7726022311, 9328967245, 8875188751, 9660329589, 7710890046, 7773923619, 9849460854, 8238975269, 9829374474, 8087883898, 9829794740, 8302888070, 9890123346, 7728867556, 7667156083, 8160308169, 9950898641, 8623817916, 9428018194, 9589818713, 9636938234, 9928992262, 7222950278, 8400120604, 7028890603, 7014812737, 9977005403, 9828043957, 9892169935, 9358147634, 9886338861, 9829166892, 9901557995, 9712526455, 9764790914, 9512084696, 8080501660, 8160693220, 7014895960, 8850762548, 9824250317, 7020254530, 9664136963, 7426851134, 7415475080, 9610706990, 9982066200, 9571363424, 7737506017, 9660072474, 9079690340, 8140254897, 9106158843, 7742316986, 9829061714, 9971703279, 9799245231, 9480509078, 9276952992, 7358557080, 9586050317, 9828283186, 8005768716, 7014418312, 8019416535, 8982066783, 7014233752, 8224930993, 9943084644, 7990431401, 9558551766, 9892128633, 7984292419, 9974149435, 7797064744, 8290853480, 8839547973, 6383499893, 7013065496, 9900629897, 8952034713, 9322742007, 7372930265, 9158606603, 9636856979, 9967312812, 8401971220, 9783660353, 7069445333, 9819770639, 9849498879, 8078645910, 7016758581, 7069771219, 9638370219, 9574009518, 7990505094, 8209808515, 9424019015, 8107009275, 9694179964, 9082091997, 7014346911, 9927682050, 9752694005, 7014648441, 9769457913, 9326289563, 7624093660, 9492554580, 9924424434, 8517957273, 8108985265, 8109545558, 7898488338, 8605336703, 7878543142, 9610817696, 9597004200, 9950330008, 9468827667, 7878171717, 9879341117, 8839503588, 8167695201, 9967996953, 9824253695, 9413371547, 9677821136, 8449603053, 9414287941, 8112263544, 6356156321, 7666299902, 8200439217, 9821236774, 9825760689, 9686777597, 9901974386, 9600604444, 9721512681, 7000191480, 9029560000, 7874047005, 6354725070, 8208402032, 8591488068, 9352615683, 9116397950, 8502898881, 9670265859, 7096759937, 7096759937, 9952434717, 9940135390, 9979278405, 7898069922, 8522918589, 8866550405, 9561470471, 7222950015, 9011556202, 9825120871, 8889022232, 8866256208, 9664231556, 8698707608, 9663483775, 9137422736, 9443508636, 6200645788, 9879837393, 7019406824, 7567467262, 9770126427, 8764226386, 9000001815, 8056530310, 7736110502, 8639680916, 9804391371, 7667264535, 9139977335, 9739927285, 9998495768, 9167288840, 8696001000, 9783371423, 7359041301, 9149027969, 9004627710, 7737585605, 7383333373, 9904131885, 7659099789, 7622009014, 9837348429, 9898038261, 8107263056, 9829049786, 9879611825, 7795563237, 7300167199, 8504071804, 9900763213, 7016481121, +91 98336 87298, 9894533458, 9705774728, 9509256925, 9772362645, 9461327618, 9823722007, 7665151453, 7822012546, 7024713523, 9898976151, 9351046913, 9926869845, 9824279291, 9928392001, 8970000099, 9414508722, 9772783513, 7727006049, 8118838418, 7000843085, 9214447230, 9799940886, 9784507198, 9099626224, 9814786660, 9829518206, 9969836036, 9377336444, 9680772884, 9587019989, 9673061122, 9116806060, 9636802216, 8769087459, 8769005854, 8949944845, 9913094352, 9929274736, 9660345296, 9704274153, 9649078503, 7093063423, 9667073006, 9994646666, 9666597417, 6281396983, 9985451746, 9824934924, 7995555790, 9405058860, 7276074910, 9638810001, 9929407481, 9824388845, 8696811552, 9413876301, 8883396777, 9033692950, 9438076290, 9414168904, 7976003874, 9376006173, 9545098873, 7875363399, 8949800878, 9326114568, 9314489973, 9928200311, 8949455456, 9529839479, 9131245598, 9461287678, 7892747528, 8460198628, 8600037000, 7906401119, 8247897412, 9825787740, 9825731904, 9799141594, 9825029643, 9685611805, 9820440015, -59300121, 9008643897, 9540916688, 6376227725, 9587435593, 9352135519, 1146710500, 7232057148, 7722861777, 8767685997, 8426999750, 7668631142, 9994423239, 9426115889, 9826507027, 9898325035, 9925096303, 9926315610, 7981488625, 7354699033, 9029830744, 9810899545, 8054539863, 6381699426, 9849031717, 7359674694, 8870112514, 9351507060, 9920270173, 9887648048, 9824102597, 9610575710, 9757403064, 9691188335, 9413074626, -1148111968, 8273229891, 9967785622, 8884385554, 9414430509, 9926802771, 9824023841, 9742176949, 7773923923, 7665682297, 7859850012, 9811502692, 9571139439, 9747018916, 9950674785, 9649696380, 8688688603, 7974598486, 8114405622, 9096813545, 9712979729, 9022991513, 8978388802, 9722680902, 8668728112, 9113197075, 9320824880, 9650693068, 9414121341, 9417401742, 9423586049, 9462684703, 9726285878, 9363299931, 8698015176, 9182124821, 9041683565, 8003110077, 8088784338, 9829584698, 8088414491, 7667031144, 8667269017, 9600959470, 9423968267, 9339129661, 9149893067, 9521130154, 9711294438, 9856763844, 7984155524, 8904607600, 9724252525, 7383470678, 8660843806, 9911273344, 8178072356, 7903398053, 9872133772, 9036062843, 9538023618, 9811564724, 9082277240, 8767472751, 9373888842, 9825050241, 9765200114, 9179323187, 8432747729, 8888786008, 9356435859, 9777726700, 9682296349, 7990539538, 9998209789, 9448445884, 9039366909, 8237272708, 7013242269, 9869746880, 6304517539, 9966664734, 8000898937, 9982290920, 8878852778, 8074662280, 8511521305, 7000341566, 9139611200, -6619927318, 9657797111, 8780440201, 9540761400, 9947455047, 7980910748, 8883658442, 7818011414, 6367679564, 9598810786, -2036442884, 9104277619, 9003484980, 9602107005, 9845040823, 9904518962, 9911699552, 8129085778, 7620996252, 7020180210, 8209317840, 7420006688, 9414668205, 9884494638, 9166719929, 8830077637, 9924563551, 9064937549, 9129176879, 9811216986, 7348010436, 8088731246, 9911447542, 9079870057, 9885051023, 9945665594, 7977993935, 8700778168, 7737644410, 8134995705, 8899799888, 9426031468, -617736922, 8074446503, 9468568068, 7042896191, 9827746807, 8439497412, 9829222659, 9512334472, 9333110071, 9969384819, 9926222785, 9983149550, 9998034313, 9224050799, 9936646128, 7520942452, 9638479413, 8918511762, 7208200107, 9664377593, 8448483999, 9762211464, 9823463751, 9987670004, 9812063748, 7000497519, 9633401358, 8560053200, +961-, 9998774400, 9828657143, 9106307139, 9616579081, 7702401250, 9821091165, 8099967949, 8008700850, 9867207835, 9560642477, 7353654605, 9997018535, 9811000510, 9579459306, 8503989369, 9766356330, 9414151460, 7276772006, 8533005849, 9881782818, 9600365215, 7892956038, 7779931956, 8380013112, 8208443192, 9440730970, 9140162471, 9976699141, 9766696338, 9461450633, 8018154301, 9450064443, 7508132150, 8247529598, 9071244722, 9972200008, 9829013600, -66994449, 9021229012, 8111834999, 9935536561, 8169915372, 9213269595, 9121042375, 7449790026, 9001420489, 8641845635, 7014858739, 9158102391, 9035328868, 9893288971, 8077697338, 9972980354, 9934996637, 9130126237, 9819038096, 7757946065, 9879476081, 9980287238, 7353137424, 8128263216, 8788901220, 9820258925, 8754535277, -9851053236, 6355156077, 9314605601, 9216769399, -691331062, 9738722033, 9766957777, 9686760143, 9923658162, 9725766232, 9896377912, 9691076001, 9436153678, 9204234901, 9335133946, 9356161724, 9727514967, 7795960288, -414605957, 8110081108, 9316238092, 9316238092, 9824114845, 9630507995, 9819643103, 8000980602, -664697565, 8700689661, 8800512890, 9160752556, 7869516439, 8866622391, 7004715318, 8072073828, -8188075261, 9764906560, 9978310668, 7523016088, 9886816126, 9016487179, 9275130919, 8896252575, 7982339396, 9315345696, 9423187956, 9374079769, 7296879595, 9030168823, 9944148783, 7907826057, 6388403433, 7043178343, 8446747320, 9499188108, 8108612737, 6261821766, 9374230868, 9764753797, 6371639761, 8667810753, 9930678080, 7262809466, 9307306738, 9540229004, 9490200117, 9579193519, 9028758901, 8630680522, -5124155971, 9899098117, 6363736395, 7070464798, 9340486690, 9810268312, 7738685281, 7588554402, 9057954063, 7807832102, 8688803888, 9048067723, 8928276650, 8249020267, 9970736663, 9913133533, 9909153463, 9838968565, 9424589963, 8334823082, 7987194353, 9997974237, 9978499796, 9462880696, 9036663713, 7042553456, 8149458321, 6351325965, 8875757693, 9849350288, 8740066152, 9845520868, 9711213789, 8964068973, 8320430926, 8660257811, 9113946220, 9886178703, 9265781243, 9052992799, 9422345544, 9845663682, 9962620021, 8522948421, 6282910751, 9704772639, 9072411114, 7878121244, 9252464699, 8390618149, 9460208526, 7977087944, 7878444321, 8877404582, 9422035239, 9428105723, 9414100039, 9597088822, 7386466799, 9987445898, 7666495556, 8329547241, 8160812506, 9962277398, 9969115716, 9405412288, 7558522233, 9500291283, 9785808004, 9893610773, 8433795539, 9602259570, 9911425375, 7008474752, 8454033768, 9158930437, 9425485194, 9909422823, 9500525160, 9571242141, 9403816096, 9178104727, 9766663741, 8668995210, 9081760607, 8949211022, 7838120628, 7841873026, 9884457899, 9963232234, 9008473086, 8971317490, 8050307335, 9970163645, 9312683681, 7020529376, 7875844653, 9872856063, 9831055741, 9343306968, 9588845267, 9324622503, 8660477533, 9892981201, 6009057844, 7974231037, 9145973772, 8310087039, 9974235607, 8866843928, 8920270004, 9974945367, 8949415020, 9207058856, 7357814177, 8530398668, 7981989186, 8080331882, 8086896545, 9226764767, 8000754321, 6383862397, 9594487952, 8126660061, 7999145511, 9081694006, 9304459417, 9948517592, 8147564145, 6361172730, 9841185161, 9822948964, 8462884397, 9819481543, 8433052178, 7744970754, 7631019668, 8010919005, 9834085022, 7828943949, 7619601847, 8100105204, 9099927000, 8610472276, 9028602284, 9893242262, 7287088870, 6294127177, 9564009309, 9391008511, 8839210038, 9327212492, 9916885078, 7416539962, 9413655055, 9825421300, 8897399675, 9356544298, 8955429529, 8105770381, 9424617625, 9870555810, 7426045951, 9978026543, 8149681889, 9747314492, 8680003868, 9810017610, 6383452299, 9538586641, 9843492955, 9994631090, 9518789431, 9904196168, 7559884607, 9406615904, 9870804853, 7875603998, 9741179428, 8130854100, 9441062958, 8999026477, 9820789897, 9870114056, 9167442409, 7597523023, 9664175237, 8619487731, 6397383509, 9660921857, 9084506060, 9328116088, 9791593826, 8866383477, 9011891848, 9664588953, 9223540839, 9898365230, 9926599080, 9994598257, 9721232828, 7770023111, 7770023111, 9339808382, 9510014176, 7818898838, 9829070759, 8387019566, 7014432911, 9677963742, 7666847939, 9703567877, 8708275179, 9390095362, 9762153583, 8619483211, 9610606666, 9445206887, 7561872569, 7022991486, 9979565159, 6362712544, 9424834332, 9226200200, 9974370121, 9759560045, 9810333600, 9924073336, 9845919939, 9818458108, 8780358609, 9146594041, 9826078153, 9022176063, 9416977811, 9624723434, 8114426572, 9829382222, 9873571340, -70686066, 9080868344, 8600029618, 7276789709, 8154985297, 9106286529, 9824662888, 8919615695, 9047817516, 9908048733, 9739104339, 7724963483, 9919283081, -26771522010, 9597866265, 9030172416, 8446272771, 7355068313, 7973717529, 9620104087, 9993474636, 8872867361, 9950177137, 9406684821, 9936292088, 8225930179, 7907917058, 9922411508, 8499809999, 9762314408, 8209648075, 9414109726, 8982945500, 9924219589, 9638102328, 8909929042, 8878022111, -507842665, 8750726544, 9827073072, 9000201992, 7010995244, 9145057196, 7976232526, 9273955444, 8667052664, 8007798854, -41747199, 8095064652, 9019550501, 9840132145, 9602211291, 9819506145, -6306560328, 9414242101, 7585017394, 9861658011, 6298340722, 9373106403, 9443119898, 9924143686, 9769058678, 7738246909, 7292000089, -771881154, 7088006001, 9902045060, 7798028777, 9422590926, 6352210239, 7737795630, 9422014693, 7984182519, 9212313125, 9565443311, 9246375761, 7359831637, 9457608037, 7977585851, 9521343050, 9584181717, 9784563938, 9834955387, 9890928168, 8882629387, -824509863, 9012666000, 9678777299, 9665830300, 9824013693, 7861087228, 8473800562, 9037578773, 8885343444, 9769875055, 7838324302, 9752645792, 9028633311, 8796650978, 9173216608, 9428233019, 7615960894, 8072343764, 9414046592, 9966866757, 9979860047, 9560803468, 6374237045, 9653305326, 8587806193, -7742971118, 9960989810, 7097637970, 9426492379, 9820287899, 9036156618, 8838380188, 7558694895, 9890621549, 9971531179, 7875029268, 7448610754, 6289052530, 9763350284, 7646968422, 8809008025, 9870146882, 9964436047, 9840462205, 7978674547, 9417522553, 9505054153, 9841483727, 9512210344, 9890118095, 9898439889, 8016079950, 9131056108, 9328120311, 9008196715, 9787181090, 7738796075, 9600834983, 8792780639, 9810365515, -93767950, 9508517660, 9898090333, 7838809594, 7016096897, 9019480550, 9994609893, 9909917102, 9998091291, 9841699432, 8329808295, 9885772448, 9926754671, 9263169212, 9820393261, 9979357048, 8056005795, 9827827347, 8056704452, 8460121237, 9702225052, 9821550490, 9762566666, 9019604619, 9730328366, 8888671658, 8918776840, 9346960054, -3035079992, 9603251322, 8329651116, 9441743332, 8511938584, 7354273003, 9479070442, 8887964590, 9830493243, 9650509467, 8129567857, 8897564688, 8848747160, 9079893990, 9820109591, 9712140811, 8779497381, 9582100733, 9690217188, 9845929718, 9826439249, 9481675987, 9850183847, 8605465674, 8958267392, 9887848988, 7000443109, 9837302549, 9730304131, 9825251514, 9731700451, 8057080670, 9329314005, 9866295006, 9799906951, 7598862991, 9701001239, 7558857000, -3357703979, 8886124112, 7498399570, 8669585991, 9849313955, 9798509492, 9842712045, 8975628695, 9999775096, 7874868944, 9941665052, 9348129044, 9822582456, 8980434985, 7364029738, 6354247900, 9010701070, 8830807323, -35447567, 9925639190, 9625575516, 9025541178, 7984985614, 7775857513, 9843896799, 8509244152, 8130730718, 7226022503, 9818787656, 9077177876, 9549408877, 9420002089, 9799484824, 9824451781, 7219484577, 8779357761, 9619545454, -1912400354, 9817092447, 9795298266, 9326109247, 9860544141, 9822121855, 9994828638, 9845285437, 7979919602, 9448588447, 9789020601, 7973558282, 7675044123, 6353553780, 7350417376, 8459312502, 8277189466, -7999350376, 9810170066, 9949118945, 6379441306, 9911373638, 9562653979, 7808053453, 8078359391, 7780155022, 9036119902, 7878980801, 9082549539, 9377171775, 9188534377, 9844386272, 9690122229, 9704049141, 7350747879, 6281624319, 8696872560, 8141351353, 9355686987, 9944855421, 8605143900, 7666174986, 6299151309, 9956837254, 8079097260, 8160150461, 9769372023, 9824223348, 9860677400, 8090524171, 8696544066, 8763282105, 9994831785, 9891167725, 9825135123, 9825135123, 6358833404, 9999651610, 9959000567, -9846140550, 9036612977, 8112464790, 9463273946, 9044895135, 6351477598, 9850538952, 9879876313, 9449170305, 7015213054, 9661391062, 9846699636, 8909315176, 9718089909, 9974004040, 9182737739, 9624849446, 9588896565, 7448224889, 7982205217, 9023249393, 9829384890, 8504956669, 7091345353, 9753131101, 7770953888, 9510760001, 9994522351, 9891302499, 9988537509, 9732792942, 9694944444, 9711108043, 9880838161, 8110984822, 9629595112, 8104184919, 9375975484, 9501130714, 9574044458, -1711367123, 9866684156, 9788315507, 8295118304, 9600489496, 9866176668, 7720037099, 9373294940, -7901776623, 9726953599, 9039246351, 9725878817, 9664177823, 8247492422, 8084791000, 7734969096, 8789449545, 9993349315, 9843988430, 8668556611, 9880774485, 8129125252, 9347599188, 9110755133, 9978980169, 9129949674, 8830041907, 8291263413, 9790821921, 9828280699, 9328079091, 9646310665, 9349370369, 9698980919, 9594376783, 9421192985, 9930121939, 9694867363, 8000000872, 9526638145, 9890695171, 9823437760, 8218497759, 9722212123, 9873994297, 9230020151, 7992285051, 6355146401, 7984891921, 7021930726, 8821903099, 9867026611, 9823408030, 9665156890, 9731165011, 8955552974, -530308502, 9773041676, 9827288393, 9638134280, 8237412741, 9820142892, 9310305583, 8077057386, 6001390056, 9145721544, 9034648387, 9967790451, 9985137593, 9826256219, 9471541217, 7297869060, 9858121717, 7774062311, 9833016395, 9478470505, 9940585586, 9879866998, 9949996626, 7983545652, 7972216774, 9729748097, 9826060291, 6358062812, 7387070700, 7702143664, 7879877879, 7904415101, -576381124, 9694980292, 9460443496, 8430548277, 9701957954, 9081788284, 9595402626, 8879710283, 9824396408, 8758665656, 9428769522, 7391085883, 9754348868, 9689909267, 9850624123, 9866524333, 9701348499, 9370610669, 9727850806, 9351040833, 9016789367, 8941985003, 9928548166, 9814104635, 7733882500, 9106208183, 9960227680, 9535184152, 7899873635, 9778575549, 9829336871, 9453932393, 9961220916, 9021679824, 9619971386, 8290546577, 7622071615, 9340975383, 9707720005, 7708063135, 6209136459, 7410163919, 9841424034, 9553185185, 8824160942, 9869958192, 9988521500, 7023202271, 9822389981, 9820667388, 6201692824, 9978040336, 8755800830, 9975912655, 9769326640, 7708074645, -91159329605, 9491723333, 9175114122, 9414160377, 9638112178, 9440373001, 9919588504, 7000921838, -799876721, 7620988904, 9709451550, -752561625, 7373051819, 9452854384, 9905066770, 6291674034, 6378846995, 9099886100, 9637264462, 8582877897, 8356909422, 8460849383, 9044779947, 9927049967, 9341760783, 9898959569, 8439161204, 7887372626, 9980211089, 7972032377, 9827678500, 9978877876, 9258147664, 9467035301, -5525655418, 8439240655, 8866649798, 8619997233, 9971168189, 9767448925, 9424515721, 7355650702, 7889302789, 9941286511, 9464874886, 9592110200, 7977010021, 9896900778, 7620131672, 8421756017, 8299876733, 8248427401, 8838123628, 9925024729, 6380734482, 9252577777, 6352124184, 9580213975, 9428942682, 9712798098, 9359182410, 9830079704, 9040295351, 9151777590, 9751083410, 9803139204, 9924111469, 8237535651, 9860169465, 9167776259, 9822453048, 9434895153, 8708496157, 9009394661, 8438281481, 7988623232, 9464503100, 8141053000, 9466836264, 9555552343, 8897520274, 9913332374, 9084805927, 9601349590, 9898744000, 9825971776, 9892333779, 8128379908, 9821598369, 9182205781, 9822034149, 7632873435, 9054067093, 9994611702, 9199615324, -9675001254, 9553655078, 9463672678, 9042365256, 7013698126, 9913013013, 9618368809, 8956674367, 6291453935, 7605042578, 8237746104, 9928663666, 9510357428, 9426573413, 9175960314, 9861242957, 9340956566, 9099398845, 8754595872, 9081068888, 7041080332, 9262474809, 8789771227, 8076219757, 7488137539, 8307713893, 9174287308, 8141631880, 7303028369, 9726297971, 9940708352, 9521179132, 9869980233, 9993316996, 9581363636, 8073616627, 9899778535, 9743833888, 7710958886, 8247774524, 9990535999, 8962611504, 8433665592, 9810158220, 7339666756, 7400080999, 9035773963, 9975601530, 8970705464, 7772931999, 9880698980, 9999956963, 9594871846, 9044924440, 9351489870, 9428739861, 9422757072, 7758064405, 8469010444, 7976934979, 9205911759, 9033122482, 9958334988, 9521023467, 9898606618, 9475646303, 9989975067, 8200709304, 8058797841, 9892007707, 9324766067, 9420691835, 6303199813, 9036498358, 9765447414, 7879345547, 9084492043, 8295115145, 8766866946, 9953009110, 7499914309, 9980322676, 9885887671, 9727635928, 8389839383, 9930944021, 8080110911, 7052325555, 9763192342, 7568404816, 9099902335, 6361662148, 7017634373, 9163214909, 7814763218, 7874125128, 9727124902, 9823266654, 7666764448, 9658785785, 7986950744, 9581953572, 9760807055, 9623414343, 9494617129, 7006307510, 9739092651, 6206460975, 8309898569, 9712655336, 8087106771, 7015766476, 8329674070, 8414807117, 9313486376, 8937887706, 9331076735, 9829150876, 9422865174, 8446682296, 9429579366, 9892108192, 9537860749, 8237456846, 9449866576, 9821618868, 8126299869, 9871179891, 9009383128, 8903540004, 9879481882, 8309064625, 7500852001, 8095303758, 9094331481, 9869260682, 7420836475, 7020571612, 9960128234, 7758083032, 7822021548, 9879748042, 9694119479, 8209638633, 8087757980, 9898631738, 9326830907, 9063875559, 9033816191, 9727700068, 9822996052, 9825224277, 7208991060, 8104446705, 9985828065, 8200120811, 9548129600, 9428317253, 6287168701, 8431731931, 9791370344, 7984801921, 8601458420, 7778887242, 9920420300, 8087569680, 8851385443, 9906493955, 9589380106, 8349998913, 7506254126, 9814401114, 9537801543, 9315459329, 8985511655, 9999169550, 9227012099, 9704086512, 9601321190, 9342737395, 7217282194, 9315147447, 9898171163, 9924999969, 9382370318, 8959176556, 7744878711, 9620065635, 8583058551, 7676767669, 8667333139, 7776063475, 7725964892, 8384857545, 9326116009, 9549933167, 9860028445, 9820779155, 6003093108, 9597256474, 9886037393, 9471095539, 9995909177, 9997926789, 9342154294, 9595628211, 9340698832, 8073159172, 8602250781, 8962974362, 9824175126, 9616056180, 7016466809, 8074904559, 9265182105, 9164809918, 7887553787, 9815652757, 8801993317, 6361319597, 8000141648, 7760447970, 9925800259, 9786631182, 8449063841, 8349368664, 9473561048, 8000003951, 8767190260, 8000657090, 8979779903, 6378335939, 9824188918, 7700087786, 9246464657, 7895846342, 9766790817, 9701250042, 9953062700, 9981713393, 7588961566, 7600680649, 8160595493, 9356183361, 9867225560, 8302230394, 8169304700, 9824109996, 7004059127, 8449226712, 8308144391, 7806976003, 9962730939, 9557856394, 9888315649, 8778272947, 7983471230, 9999037204, 9962677721, 9409018427, 8951601615';

        $mobileArr = explode(', ',$mobileStr);

        $getdatas = Customer::query()->select('id')->whereIn('phone_no', $mobileArr)->where('company_id', 1408)->get();


        $updatedIds = [];
        foreach ($getdatas as $getdata) {
            Customer::where('id', $getdata->id)->update(array('assigned_to_user' => 1643,'sp_tmp_flg'=>2));
            $updatedIds[] = $getdata->id;
        }
        dd($updatedIds);
    }
}
