<?php

namespace App\Http\Controllers\API\V6;

use App\Http\Controllers\API\V6\BaseController as BaseController;
use App\Models\Customer;
use App\Models\DashboardSetting;
use App\Models\Estimate;
use App\Models\EstimateTimeline;
use App\Models\Event;
use App\Models\LeadStage;
use App\Models\SalesPersonPerformances;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Validator};

class DashboardController extends BaseController
{
    protected $logged_user = null;
    protected $company_id = 0;
    protected $user_perm = 0;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->logged_user = Auth::user();
            $this->company_id = ($this->logged_user->company_id) ? $this->logged_user->company_id : $this->logged_user->id;
            $this->main_company = User::select("company_category")
                ->where('id', $this->company_id)->first();
            $this->user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
            return $next($request);
        });
    }

//    public function __invoke($assign_user,$fil_global_start, $fil_global_end)
    public function __invoke($date,$fil_user_id)
    {
        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
        $widgetsArray = DB::table(function ($query) use ($date) {
            $query->select(DB::raw('DISTINCT estimate_no, status'),'company_id','customer_id')
                ->from('estimates')
                ->where(function ($query) use ($date) {
                    $dateArr = explode("_", $date);
                    $query->whereBetween(DB::raw("DATE_FORMAT(estimate_date, '%Y-%m-%d')"), [$dateArr[0], $dateArr[1]]);
                });
        }, 'subquery')
            ->leftjoin('customers_views', 'subquery.customer_id', '=', 'customers_views.id')
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team',$user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
                    $query->orWhere('customers_views.user_id', '=', $this->logged_user->id);
                }
            })
            ->where(function ($query) use ($fil_user_id) {
                if ($fil_user_id == 0) {
                    $query->where('customers_views.company_id', '=', $this->company_id);
                }

                if ($fil_user_id > 0) {
                    $query->where('customers_views.assigned_to_user', $fil_user_id);
                    $query->where('customers_views.company_id', $this->company_id);
                }
            })

            ->where('subquery.company_id', '=', $this->company_id)
            ->where('subquery.status', '!=', '')
            ->select('subquery.status', DB::raw('COUNT(subquery.status) as widget_total'))
            ->groupBy('subquery.status')
            ->get();

        $widgets = json_decode(json_encode($widgetsArray), true);


//        $widgets = Estimate::query()->groupBy('estimates.status')
//            ->leftjoin('customers_views', 'estimates.customer_id', '=', 'customers_views.id')
//            ->where(function ($query) use ($user_perm) {
//                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
//                    $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('customers_views.user_id', '=', $this->logged_user->id);
//                }
//            })
//            ->where('estimates.company_id', '=', $this->company_id)
//            ->where(function ($query) use ($date) {
//                $dateArr = explode("_", $date);
//                $query->whereBetween(DB::raw("DATE_FORMAT(estimates.estimate_date, '%Y-%m-%d')"), [$dateArr[0], $dateArr[1]]);
//            })
////            ->where('estimates. company_id', '=', $this->company_id)
//            /*->where(function ($query) use ($assign_user) {
//                if ($this->logged_user->company_id == "") {
//                    $query->where('company_id', '=', $this->company_id);
//                } else {
//                    $query->whereRaw('user_id IN  (' . $assign_user . ')');
//                    $query->orWhere('user_id', $this->logged_user->id);
//                }
//            })*/
//            ->select('estimates.status', DB::raw('COUNT(estimates.status) as widget_total'))->get()->toArray();
        $a = [
//            ['status' => 'Sent', 'widget_total' => 0],
            ['status' => 'Inprogress', 'widget_total' => 0],
            ['status' => 'Accept', 'widget_total' => 0],
            ['status' => 'Decline', 'widget_total' => 0],
            ['status' => 'Draft', 'widget_total' => 0]
        ];
        $x = array_column($widgets, 'status');
        $total = 0 + array_sum(array_column($widgets, 'widget_total'));
        foreach ($a as $key => $value) {
            if (!in_array($value['status'], $x)) {
                $widgets[] = $value;
            }
        }
        usort($widgets, function ($a, $b) {
            return $a['status'] <=> $b['status'];
        });
        $widgets[] = array("status" => "Total", "widget_total" => $total);
        return $this->sendResponse($widgets, 'Widget retrieved successfully');
    }

    public function barChart($date, $fil_user_id)
    {
        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
        $monthArr = [];
//        $input = $request->all();
        $dateArr = explode("_", $date);

        $year = date('Y', strtotime($dateArr[0]));
        $month = date('m', strtotime($dateArr[0]));
        for ($i = 0; $i < 12; $i++) {
            array_push($monthArr, date("M` Y", strtotime('+' . $i . ' month', date(strtotime('01-' . $month . '-' . $year)))));
        }

        $records = Estimate::query()
            ->leftjoin('customers_views', 'estimates.customer_id', '=', 'customers_views.id')
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
                    $query->orwhere('customers_views.user_id', '=', $this->logged_user->id);
                }
            })
            ->where('estimates.company_id', '=', $this->company_id)
            ->where(function ($query) use ($dateArr) {
                $query->whereBetween(DB::raw("DATE_FORMAT(estimates.estimate_date, '%Y-%m-%d')"), [$dateArr[0], $dateArr[1]]);
            })
            ->where(function ($query) use ($fil_user_id) {
                if ($fil_user_id == 0) {
                    $query->where('estimates.company_id', '=', $this->company_id);
                }

                if ($fil_user_id > 0) {
                    $query->where('customers_views.assigned_to_user', $fil_user_id);
                    $query->where('estimates.company_id', $this->company_id);
                }
            })
            /* ->where(function ($query) use ($assign_user) {
                 if ($this->logged_user->company_id == "") {
                     $query->where('company_id', '=', $this->company_id);
                 } else {
                     $query->whereRaw('user_id IN  (' . $assign_user . ')');
                     $query->orWhere('user_id', $this->logged_user->id);
                 }
             })*/
            ->groupBy('estimates.status', DB::raw("DATE_FORMAT(estimates.estimate_date, '%Y-%m-%d')"))
            ->orderBy(DB::raw("DATE_FORMAT(estimates.estimate_date, '%Y-%m-%d')"))
            ->select('estimates.status', DB::raw('SUM(estimates.net_amount) as total_count'), DB::raw("DATE_FORMAT(estimates.estimate_date, '%b` %Y') as estimate_date"))->get();

        $labels = array();
        $sent = array();
        $close = array();
        $dataArr = array();
        if (isset($records)) {
            foreach ($monthArr as $k => $v) {

                foreach ($records as $record) {
                    if ($record->estimate_date == $v) {
                        $dataArr[$v]['sent'][] = 0;
                        $dataArr[$v]['sent'][] = $record->total_count;
                        $dataArr[$v]['close'][] = 0;

                        if ($record->status == 'Accept')
                            $dataArr[$v]['close'][] = $record->total_count;

                    } else {
                        $dataArr[$v]['sent'][] = 0;
                        $dataArr[$v]['close'][] = 0;
                    }
                }
                $dataArr[$v]['sent'][] = 0;
                $dataArr[$v]['close'][] = 0;
            }

            foreach ($dataArr as $key => $value) {
                $sum_sent = array_sum($value['sent']);
                $sum_close = array_sum($value['close']);
                array_push($labels, $key);
                array_push($sent, $sum_sent);
                array_push($close, $sum_close);
            }
        }
        $data['labels'] = $labels;
        $data['sent'] = $sent;
        $data['close'] = $close;
        return $this->sendResponse($data, 'Bar chart retrieved successfully');
    }

    public function salesPerformanceChart($date, $fil_user_id)
    {
        $sales_performance = DB::table('sales_person_performances')
            ->where(function ($query) use ($date) {
                $dateArr = explode("_", $date);
                $query->whereBetween('performance_date', [$dateArr[0], $dateArr[1]]);
            })
            ->where(function ($query) use ($fil_user_id) {

                if ($fil_user_id == 0 && in_array('access-all-lead-and-assign-to-anyone-in-team',$this->user_perm)) {
                    $query->where('company_id', '=', $this->company_id);
                }

                if ($fil_user_id > 0) {
                    $query->where('user_id', $fil_user_id);
                    $query->where('company_id', $this->company_id);
                }


                /* if ($this->logged_user->company_id == "") {
                     $query->where('user_id', '=', $this->logged_user->id);
 //                    $query->where('company_id', '=', $this->company_id);
                 } else {
 //                    $query->whereRaw('user_id IN  (' . $assign_user . ')');
                     $query->orWhere('user_id', $this->logged_user->id);
                 }*/
            })
            ->where(function ($query) use ($fil_user_id) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                    $query->where('user_id', '=', $fil_user_id);
                    $query->where('company_id', '=', $this->company_id);
                }
            })
            ->select(DB::raw("SUM(total_task) as total_task"), DB::raw("SUM(completed_task) as completed_task"))
            ->get()->toArray();
        $total_task = array_column($sales_performance, 'total_task');
        $completed_task = array_column($sales_performance, 'completed_task');

        $data['performance']['labels'] = 'Completion Ratio';
        $data['performance']['series'] = ($total_task[0] > 0) ? (float)number_format(($completed_task[0] * 100) / $total_task[0], 2) : 0;
        /*$data['completion_ratio']['labels'] = 'Completion Ratio';
        $data['completion_ratio']['series'] = ($total_task[0] > 0) ? (float)number_format(($completed_task[0] * 100) / $total_task[0], 2) : 0;*/

        $data['total_task'] = (int)$total_task[0];
        $data['completed_task'] = (int)$completed_task[0];
//        $data['total_record'] = (int)$total_record[0];

        return $this->sendResponse($data, 'Sales performance chart retrieved successfully');
    }

    public function calendarData($start, $end, $assign_user)
    {
        $start = (!empty($start)) ? ($start) : ('');
        $end = (!empty($end)) ? ($end) : ('');

        $data = DB::table('events')
            ->leftJoin('estimates', 'events.estimate_id', 'estimates.id')
            ->leftJoin('users', 'events.user_id', 'users.id')
            ->where('events.company_id', $this->company_id)
            ->where(function ($query) use ($assign_user) {
                if ($this->logged_user->company_id == "") {
                    $query->where('events.company_id', '=', $this->company_id);
                } else {
                    $query->whereRaw('events.user_id IN  (' . $assign_user . ')');
                    $query->orWhere('events.user_id', $this->logged_user->id);
                }
            })
            ->whereDate('events.start_date', '>=', $start)->whereDate('end_date', '<=', $end)
            ->select('events.event_type', 'events.id', 'events.start_date as start', 'events.end_date as end', 'events.class_name as className', DB::raw("concat_ws(' - ',events.notes,estimates.estimate_no,users.name) AS title"))
            ->get();
        return $this->sendResponse($data, 'Calendar data retrieved successfully');
    }

    public function getDateWiseFollowUpList($date, $assign_user)
    {

//        $input = $request->all();
        $event = Estimate::query()->select('e.*', 'estimates.estimate_no as estimate_no', 'estimates.customer_name', DB::raw("DATE_FORMAT(e.start_date, '%d-%m-%Y') as display_date"), DB::raw("DATE_FORMAT(e.start_date, '%d-%m-%Y %H:%i:%s') as start_date"), 'estimates.customer_name', DB::raw("RIGHT(estimates.customer_address, 10) as mobile_no"), "estimates.status", "u.name as user_name")
            ->join('events as e', function ($query) {
                $query->on('e.estimate_id', '=', 'estimates.id');
            })
            ->join('users as u', 'e.user_id', 'u.id')
            ->where(function ($query) use ($date, $assign_user) {
                $query->whereRaw("e.id=(SELECT MAX(t2.id) FROM `events` t2 WHERE t2.estimate_id = e.estimate_id )");
                if ($date) {
                    $dateArr = explode("_", $date);
                    if (count($dateArr) == 2) {
                        $query->whereBetween(DB::raw("DATE_FORMAT(e.start_date, '%Y-%m-%d')"), [$dateArr[0], $dateArr[1]]);
                    } else
                        $query->where(DB::raw("DATE_FORMAT(e.start_date, '%Y-%m-%d')"), $date);
                }
                if ($this->logged_user->company_id == "") {
                    $query->where('e.company_id', '=', $this->company_id);
                } else {
                    $query->where(function ($query) use ($assign_user) {
                        $query->whereRaw('e.user_id IN(' . $assign_user . ')');
                        $query->orWhere('e.user_id', $this->logged_user->id);
                    });
                }
            })
            ->orderBy("e.start_date", "desc")
            ->get();

        if (is_null($event)) {
            return response()->json(['success' => 'Follow up not found!'], 422);
        }
        $data = [];
        $dataArr = [];

        foreach ($event as $val) {
            $val['user_loggedin_id'] = $this->logged_user->id;
            $val['color'] = 'danger';
            if (strtotime($val->display_date) > strtotime(date('d-m-Y'))) {
                $val['color'] = 'success';
            }
            if (strtotime($val->display_date) == strtotime(date('d-m-Y'))) {
                $val['color'] = 'warning';
            }

            $data[$val->display_date][] = $val;
        }
        if (empty($data)) {
            return $this->sendResponse($data, 'Follow up not found!');
        }

        foreach ($data as $key => $val) {
            $dataArr[] = $data[$key];
        }

        return $this->sendResponse($dataArr, 'Calendar data retrieved successfully');
        /* return response()->json([
             "success" => true,
             "message" => "Follow up retrieved successfully.",
             "data" => $data
         ], 201);*/
    }


    public function createNextFolloup(Request $request)
    {
        $input = $request->all();


        $validator = Validator::make($input, [
            'followup_date' => 'required',
//                'followup_time' => 'required',
            'notes' => 'required',
            'estimate_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()], 400);;
        }
//        if ($input['id'] == 0) {
//            $input['estimate_id'] = Crypt::decrypt($input['estimate_id']);
//        }
        $fetchData = SalesPersonPerformances::query()->select(DB::raw("DATE_FORMAT(performance_date, '%d-%m-%Y') as display_date"), "total_task", "completed_task", "adv_completed_task")->where([["performance_date", "=", Carbon::createFromFormat('d/m/Y', $input['followup_date'])->format('Y-m-d')], ["user_id", "=", $this->logged_user->id]])
            ->where(function ($query) use ($input) {
                if ($this->logged_user->company_id == "") {
                    $query->where('company_id', '=', $this->company_id);
                } else {
                    $query->whereRaw('user_id IN  (' . $input['assign_user'] . ')');
                    $query->orWhere('user_id', $this->logged_user->id);
                }
            })
            ->get();

        if ($fetchData->count() > 0) {
            if (!isset($input['next_follow_up']) || $input['next_follow_up'] != 'No') {
                $tts = $fetchData[0]->total_task;
                $adv_cts = $fetchData[0]->adv_completed_task;
                $cts = $fetchData[0]->completed_task;
                $next_cts = $tts + 1;
                $dps = (($cts - $adv_cts) * 100) / $next_cts;

                SalesPersonPerformances::where([["performance_date", "=", Carbon::createFromFormat('d/m/Y', $input['followup_date'])->format('Y-m-d')], ["user_id", "=", $this->logged_user->id]])->update(array("total_task" => $next_cts, "daily_performance" => $dps));
//                    SalesPersonPerformances::where([["performance_date", "=", Carbon::createFromFormat('d/m/Y', $input['followup_date'])->format('Y-m-d')], ["user_id", "=", $user->id]])->increment('total_task', 1);
            }
        } else {
            if (!isset($input['next_follow_up']) || $input['next_follow_up'] != 'No') {

                $insert = SalesPersonPerformances::create(["performance_date" => Carbon::createFromFormat('d/m/Y', $input['followup_date'])->format('Y-m-d'), "user_id" => $this->logged_user->id, "total_task" => 1, "completed_task" => 0, "daily_performance" => 0, "company_id" => $this->company_id]);
            }
        }

        if ($input['sp_flag'] == 'u') {
            Event::where('id', $input['event_id'])->update(array('read_at' => date('y-m-d H:i:s'), 'read_by' => $this->logged_user->id));
//                $tt - total task, $ct - completed task, $next_ct - total next completed task, $dp - daily performance
            $eventData = Event::query()->select(DB::raw("DATE_FORMAT(start_date, '%d-%m-%Y') as start_date"))->where("id", $input['event_id'])->latest("id")->first();
            $tmpStartDate = Carbon::createFromFormat('d/m/Y', $input['followup_date'])->format('Y-m-d');
            if ($eventData)
                $tmpStartDate = Carbon::createFromFormat('d-m-Y', $eventData->start_date)->format('Y-m-d');
            $fetchDatas = SalesPersonPerformances::query()->select("daily_performance", "completed_task", "total_task", DB::raw("DATE_FORMAT(performance_date, '%d-%m-%Y') as display_date"), "adv_completed_task")->where([["performance_date", "=", $tmpStartDate], ['user_id', '=', $this->logged_user->id]])
                /*  ->where(function ($query) use ($user) {
                      if ($user->company_id == "") {
                          $query->where('company_id', '=', $user->id);
                      } else {
                          $query->whereRaw('user_id IN  (' . Session::get("get_data_by_id") . ')');
                          $query->orWhere('user_id', $user->id);
                      }
                  })*/
                ->get();

            $tt = $fetchDatas[0]->total_task;
            $adv_ctss = $fetchDatas[0]->adv_completed_task;
            $ct = $fetchDatas[0]->completed_task;
            $next_ct = $ct + 1;
            if ($eventData && strtotime($tmpStartDate) == strtotime(date('Y-m-d'))) {
                $dp = (($next_ct - $adv_ctss) * 100) / $tt;
                SalesPersonPerformances::where([["performance_date", "=", $tmpStartDate], ["user_id", "=", $this->logged_user->id]])->update(array("completed_task" => $next_ct, "daily_performance" => $dp));
            }

            if ($eventData && strtotime($tmpStartDate) != strtotime(date('Y-m-d'))) {
                $adv_completed_task = $adv_ctss;
                if ($input['sp_flag'] === 'u') {
                    $adv_completed_task = $adv_ctss + 1;
                }
                SalesPersonPerformances::where([["performance_date", "=", $tmpStartDate], ["user_id", "=", $this->logged_user->id]])->update(array("completed_task" => $next_ct, "adv_completed_task" => $adv_completed_task));
            }
        }

        $data = array();
        $next_follow_up = 0;
        if (isset($input['next_follow_up']) && $input['next_follow_up'] == 'No') {
            $fetch = Event::query()->where("estimate_id", $input['estimate_id'])->latest("id")->first();
            $data['start_date'] = $fetch->start_date;
            $data['end_date'] = $fetch->end_date;
            $next_follow_up = 1;
        }
        if ($input['sp_flag'] == 'c') {
            $data['start_date'] = Carbon::createFromFormat('d/m/Y', $input['followup_date'])->format('Y-m-d') . ' ' . Carbon::createFromFormat('H:i', $input['followup_time'])->format('H:i:s');
            $data['end_date'] = Carbon::createFromFormat('d/m/Y', $input['followup_date'])->format('Y-m-d') . ' ' . Carbon::createFromFormat('H:i', $input['followup_time'])->format('H:i:s');
        }

        if (isset($input['next_follow_up']) && $input['next_follow_up'] == 'Yes' && $input['sp_flag'] != 'c') {
            $data['start_date'] = Carbon::createFromFormat('d/m/Y', $input['followup_date'])->format('Y-m-d') . ' ' . Carbon::createFromFormat('H:i', $input['followup_time'])->format('H:i:s');
            $data['end_date'] = Carbon::createFromFormat('d/m/Y', $input['followup_date'])->format('Y-m-d') . ' ' . Carbon::createFromFormat('H:i', $input['followup_time'])->format('H:i:s');
        }

        $data['next_follow_up'] = $next_follow_up;
        $data['notes'] = $input['notes'];
        $data['estimate_id'] = $input['estimate_id'];
        $data['event_type'] = 'estimate';
        $data['class_name'] = 'bg-info';

        $data['company_id'] = $this->company_id;
        $data['user_id'] = $this->logged_user->id;
        $data['log_id'] = $input['estimate_id'];
        $data['log_type'] = 'event-follow-up';

        $activityLogMsg = 'follow up created by ' . $this->logged_user->name;
        $follow_up = Event::create($data);
//        LogActivity::addToLog($activityLogMsg, $data, 1);
        if (!(isset($eventData) || $input['sp_flag'] == 'u'))
            $input['estimate_status'] = 'Inprogress';

        if (isset($input['estimate_status'])) {
            Estimate::where(array("id" => $input['estimate_id']))->update(array("status" => $input['estimate_status']));
        }
        return $this->sendResponse([], 'Follow up Saved!');
    }

    public function createTodo(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'title' => 'required',
            'start' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }
        $user = Auth::user();
        $company_id = ($user->company_id) ? $user->company_id : $user->id;
        $date = date('Y-m-d', strtotime($request->start)) . ' ' . $request->event_time;
        $date = date('Y-m-d H:i:s', strtotime($date));
        $insertArr = ['notes' => $request->title,
            'start_date' => $date,
            'end_date' => $date,
            'company_id' => $company_id,
            'class_name' => $request->className,
            'event_type' => 'event',
            'user_id' => $user->id
        ];
        $event = Event::insert($insertArr);
        return $this->sendResponse([], 'Todo Saved!');
    }

    public function updateTodo(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'title' => 'required',
            'start' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }
        $user = Auth::user();
        $company_id = ($user->company_id) ? $user->company_id : $user->id;
        $date = date('Y-m-d', strtotime($request->start)) . ' ' . $request->event_time;
        $date = date('Y-m-d H:i:s', strtotime($date));
        $where = array('id' => $request->id);
        $updateArr = ['notes' => $request->title, 'start_date' => $date, 'end_date' => $date, 'company_id' => $company_id, 'class_name' => $request->className,
            'user_id' => $user->id,];

        $event = Event::where($where)->update($updateArr);
        return $this->sendResponse([], 'Todo Updated!');
    }

    public function destroyTodo(Request $request)
    {
        $event = Event::where('id', $request->id)->delete();
        return $this->sendResponse([], 'Todo Deleted!');
    }

    public function getHeaderNotification($assign_user,$rowperpage)
    {
        $newTime = date("Y-m-d H:i:s", strtotime("+15 minutes", strtotime(date('Y-m-d H:i:s'))));
        /*$notification = DB::table('events')
            ->leftjoin('estimates', 'events.estimate_id', 'estimates.id')
            ->leftJoin('users', 'events.user_id', 'users.id')
            ->where(function ($query) use ($assign_user) {
                if ($this->logged_user->company_id == "") {
                    $query->where('events.company_id', '=', $this->logged_user->id);
                } else {
                    $query->whereRaw('events.user_id IN(' . $assign_user . ')');
                    $query->orWhere('events.user_id', $this->logged_user->id);
                }
            })

            ->where([
                ['events.start_date', '<=', $newTime],
                ['events.event_type', '=', 'estimate'],
                ['events.next_follow_up', '=',0],
                ['events.read_by', '=',0],
            ])
            ->whereNull('read_at')
            ->select('events.*', 'estimates.estimate_no as estimate_no', DB::raw("DATE_FORMAT(events.start_date, '%d %b %Y') as display_date"), DB::raw("DATE_FORMAT(events.start_date, '%h:%i %p') as start_date"), 'estimates.customer_name', DB::raw("RIGHT(estimates.customer_address, 10) as mobile_no"), 'estimates.status','users.name as user_name', DB::raw("DATE_FORMAT(events.created_at, '%d-%m-%Y  %H:%i:%s') as created_at")) // 'users.name',
            ->orderBy('events.id', 'DESC')
            ->orderBy('events.start_date', 'DESC')
            ->get();*/

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');


        /*$notification = DB::table('customers_views as cv')
            ->select('cv.*', DB::raw("IF(cv.last_activity_type = 8, cv.last_activity_updated_at, cv.last_follow_up_datetime) as last_follow_up_datetime"), DB::raw("DATE_FORMAT(cv.last_follow_up_datetime, '%d %b %Y') as display_date"),DB::raw("DATE_FORMAT(cv.last_follow_up_datetime, '%h:%i %p') as start_date"))
//                ->where([['cv.company_id','=', $company_id]])
            ->whereIn('cv.last_activity_type', [8, 9])
            ->whereNotNull('cv.last_follow_up_datetime')
            ->where('cv.last_follow_up_datetime','!=','0000-00-00 00:00:00')
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
                    $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                }
            })
            ->where('cv.last_follow_up_datetime', '<=', $newTime)
            ->orderByDesc('cv.id')
            ->get();*/

        $notification = DB::table('notification_views as cv')
            ->select('cv.*', DB::raw("IF(cv.last_activity_type = 8, cv.last_activity_updated_at, cv.last_follow_up_datetime) as last_follow_up_datetime"), DB::raw("DATE_FORMAT(cv.last_follow_up_datetime, '%d %b %Y') as display_date"), DB::raw("DATE_FORMAT(cv.timeline_updated_at, '%d %b %Y') as display_timeline_updated_at"), DB::raw("DATE_FORMAT(cv.last_follow_up_datetime, '%h:%i %p') as start_date"), DB::raw("DATE_FORMAT(cv.timeline_updated_at, '%h:%i %p') as display_timeline_updated_time_at"))
            ->where('cv.company_id', '=', $this->company_id)
            ->where('cv.last_read_by', '=', 0)
//            ->whereIn('cv.last_activity_type', [8, 9])

            ->where(function ($query) use ($newTime) {

                $query->where(function ($q) use ($newTime) {
                    $q->where('cv.last_activity_type', '=', 8);
//                        ->where('cv.last_follow_up_datetime', '<=', $newTime);
                })
                    ->orWhere(function ($q) use ($newTime) {
                        $q->where('cv.timeline_activity_type', '=', 9)
                            ->whereNotNull('cv.last_follow_up_datetime')
                            ->where('cv.last_follow_up_datetime', '!=', '0000-00-00 00:00:00')
                            ->where('cv.last_follow_up_datetime', '<=', $newTime);
                    });

              /*  $query->Where(function ($query) use ($newTime) {
                    $query->where('cv.timeline_activity_type', '=', 8);
                    $query->where(function ($query) use ($newTime) {
                        $query->where('cv.last_follow_up_datetime', '<=', $newTime);
//                        $query->where('cv.timeline_updated_at', '<=', $newTime);
                    });
                });

                $query->orWhere(function ($query) use ($newTime) {
                    $query->where('cv.timeline_activity_type', '=', 9);
                    $query->whereNotNull('cv.last_follow_up_datetime');
                    $query->where('cv.last_follow_up_datetime', '!=', '0000-00-00 00:00:00');
                    $query->where('cv.last_follow_up_datetime', '<=', $newTime);
                });*/


            })
//            ->whereNotNull('cv.last_follow_up_datetime')
//            ->where('cv.last_follow_up_datetime','!=','0000-00-00 00:00:00')
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
                    $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                }
            })
            ->orderBy(DB::raw("IF(cv.last_activity_type = 8, cv.last_activity_updated_at, cv.last_follow_up_datetime)") ,'desc')
//            ->orderBy('cv.timeline_updated_at', 'desc')
//            ->orderByDesc('cv.id')
            ->skip($assign_user)
            ->take($rowperpage)
            ->get();


        if (is_null($notification)) {
            return $this->sendError("Notification id not found", ["error" => "Notification id not found"], 200);
        }

        $data = [];
        $dataArr = [];
        foreach ($notification as $val) {
            $dis = $val->display_date;
            if ($val->timeline_activity_type == 8)
                $dis = $val->display_timeline_updated_at;
            $data[$dis][] = $val;
        }
        if (empty($data)) {
            return $this->sendError("Notification id not found", ["error" => "Notification id not found"], 200);
        }


        foreach ($data as $key => $val) {
            $dataArr[] = $data[$key];
        }


        return response()->json([
            "notification_count" => $notification->count(),
            "success" => true,
            "message" => "Notification retrieved successfully.",
            "data" => $dataArr
        ], 201);
    }

    public function getHeaderNotificationCount($assign_user)
    {
        $newTime = date("Y-m-d H:i:s", strtotime("+15 minutes", strtotime(date('Y-m-d H:i:s'))));
        /*$notification = DB::table('events')
            ->leftjoin('estimates', 'events.estimate_id', 'estimates.id')
            ->leftJoin('users', 'events.user_id', 'users.id')
            ->where(function ($query) use ($assign_user) {
                if ($this->logged_user->company_id == "") {
                    $query->where('events.company_id', '=', $this->logged_user->id);
                } else {
                    $query->whereRaw('events.user_id IN(' . $assign_user . ')');
                    $query->orWhere('events.user_id', $this->logged_user->id);
                }
            })

            ->where([
                ['events.start_date', '<=', $newTime],
                ['events.event_type', '=', 'estimate'],
                ['events.next_follow_up', '=',0],
                ['events.read_by', '=',0],
            ])
            ->whereNull('read_at')
            ->select('events.*', 'estimates.estimate_no as estimate_no', DB::raw("DATE_FORMAT(events.start_date, '%d %b %Y') as display_date"), DB::raw("DATE_FORMAT(events.start_date, '%h:%i %p') as start_date"), 'estimates.customer_name', DB::raw("RIGHT(estimates.customer_address, 10) as mobile_no"), 'estimates.status','users.name as user_name', DB::raw("DATE_FORMAT(events.created_at, '%d-%m-%Y  %H:%i:%s') as created_at")) // 'users.name',
            ->orderBy('events.id', 'DESC')
            ->orderBy('events.start_date', 'DESC')
            ->get();*/

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');


        /*$notification = DB::table('customers_views as cv')
            ->select('cv.*', DB::raw("IF(cv.last_activity_type = 8, cv.last_activity_updated_at, cv.last_follow_up_datetime) as last_follow_up_datetime"), DB::raw("DATE_FORMAT(cv.last_follow_up_datetime, '%d %b %Y') as display_date"),DB::raw("DATE_FORMAT(cv.last_follow_up_datetime, '%h:%i %p') as start_date"))
//                ->where([['cv.company_id','=', $company_id]])
            ->whereIn('cv.last_activity_type', [8, 9])
            ->whereNotNull('cv.last_follow_up_datetime')
            ->where('cv.last_follow_up_datetime','!=','0000-00-00 00:00:00')
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
                    $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                }
            })
            ->where('cv.last_follow_up_datetime', '<=', $newTime)
            ->orderByDesc('cv.id')
            ->get();*/

        $notification = DB::table('notification_views as cv')
            ->select('cv.*', DB::raw("IF(cv.last_activity_type = 8, cv.last_activity_updated_at, cv.last_follow_up_datetime) as last_follow_up_datetime"), DB::raw("DATE_FORMAT(cv.last_follow_up_datetime, '%d %b %Y') as display_date"), DB::raw("DATE_FORMAT(cv.timeline_updated_at, '%d %b %Y') as display_timeline_updated_at"), DB::raw("DATE_FORMAT(cv.last_follow_up_datetime, '%h:%i %p') as start_date"))
            ->where('cv.company_id', '=', $this->company_id)
            ->where('cv.last_read_by', '=', 0)
//            ->whereIn('cv.last_activity_type', [8, 9])

            ->where(function ($query) use ($newTime) {
                $query->where(function ($q) use ($newTime) {
                    $q->where('cv.last_activity_type', '=', 8);
//                        ->where('cv.last_follow_up_datetime', '<=', $newTime);
                })
                    ->orWhere(function ($q) use ($newTime) {
                        $q->where('cv.timeline_activity_type', '=', 9)
                            ->whereNotNull('cv.last_follow_up_datetime')
                            ->where('cv.last_follow_up_datetime', '!=', '0000-00-00 00:00:00')
                            ->where('cv.last_follow_up_datetime', '<=', $newTime);
                    });
                /*$query->Where(function ($query) use ($newTime) {
                    $query->where('cv.timeline_activity_type', '=', 8);
                    $query->where(function ($query) use ($newTime) {
                        $query->where('cv.last_follow_up_datetime', '<=', $newTime);
//                        $query->where('cv.timeline_updated_at', '<=', $newTime);
                    });
                });

                $query->orWhere(function ($query) use ($newTime) {
                    $query->where('cv.timeline_activity_type', '=', 9);
                    $query->whereNotNull('cv.last_follow_up_datetime');
                    $query->where('cv.last_follow_up_datetime', '!=', '0000-00-00 00:00:00');
                    $query->where('cv.last_follow_up_datetime', '<=', $newTime);
                });*/


            })
//            ->whereNotNull('cv.last_follow_up_datetime')
//            ->where('cv.last_follow_up_datetime','!=','0000-00-00 00:00:00')
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
                    $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                }
            })
            ->orderByDesc('cv.id')
            ->get();

        if (empty($notification)) {
            return response()->json(['success' => 'Notification not found!'], 422);
        }
        $data['notification_count'] = $notification->count();


        return response()->json([
            "success" => true,
            "message" => "Notification count retrieved successfully.",
            "data" => $data
        ], 201);
    }

    public function notificationUpdate(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }
        $user = Auth::user();
        $company_id = ($user->company_id) ? $user->company_id : $user->id;

        $where = explode(",", $request->id);
        $updateArr = ['read_flag' => 1, 'read_by' => $user->id, 'read_date' => date('Y-m-d h:i:s')];

        $event = EstimateTimeline::whereIn("id", $where)->update($updateArr);
        /* $activityLogMsg = 'notification read by ' . $user->name;
         LogActivity::addToLog($activityLogMsg, $input);*/
        return $this->sendResponse([], 'Notification Readed');
    }


    public function notificationAllUpdate()
    {
        $newTime = date("Y-m-d H:i:s", strtotime("+15 minutes", strtotime(date('Y-m-d H:i:s'))));
        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');

        $notification = DB::table('notification_views as cv')
            ->select('cv.*', DB::raw("IF(cv.last_activity_type = 8, cv.last_activity_updated_at, cv.last_follow_up_datetime) as last_follow_up_datetime"), DB::raw("DATE_FORMAT(cv.last_follow_up_datetime, '%d %b %Y') as display_date"), DB::raw("DATE_FORMAT(cv.timeline_updated_at, '%d %b %Y') as display_timeline_updated_at"), DB::raw("DATE_FORMAT(cv.last_follow_up_datetime, '%h:%i %p') as start_date"), DB::raw("DATE_FORMAT(cv.timeline_updated_at, '%h:%i %p') as display_timeline_updated_time_at"))
            ->where('cv.company_id', '=', $this->company_id)
            ->where('cv.last_read_by', '=', 0)
//            ->whereIn('cv.last_activity_type', [8, 9])

            ->where(function ($query) use ($newTime) {

                $query->where(function ($q) use ($newTime) {
                    $q->where('cv.last_activity_type', '=', 8);
                })
                    ->orWhere(function ($q) use ($newTime) {
                        $q->where('cv.timeline_activity_type', '=', 9)
                            ->whereNotNull('cv.last_follow_up_datetime')
                            ->where('cv.last_follow_up_datetime', '!=', '0000-00-00 00:00:00')
                            ->where('cv.last_follow_up_datetime', '<=', $newTime);
                    });
            })

            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
                    $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                }
            })
            ->orderBy(DB::raw("IF(cv.last_activity_type = 8, cv.last_activity_updated_at, cv.last_follow_up_datetime)") ,'desc')
            ->get();


        if (is_null($notification)) {
            return $this->sendError("Notification id not found", ["error" => "Notification id not found"], 400);
        }

        foreach ($notification as $val) {
            $updateArr = ['read_flag' => 1, 'read_by' => $this->logged_user->id, 'read_date' => date('Y-m-d h:i:s')];

            $event = EstimateTimeline::where("id", $val->last_activity_id)->update($updateArr);
        }
        return $this->sendResponse([], 'Notification Readed');
    }

    public function getDashboardSetting()
    {
        $dashboard_settings = DashboardSetting::query()->where('user_id', $this->logged_user->id)->select('*')->get();
        $new_array = [];
        if($dashboard_settings) {
            foreach ($dashboard_settings as $setting) {
                $new_array[] = [
                    'permission_id' => $setting->permission_id,
                    'is_primary' => ($setting->is_primary)?$setting->is_primary:0,
                ];
            }
        }
        return $this->sendResponse($new_array, 'Successfully Retrived');
    }

    public function storeSetting(Request $request)
    {
        $input = $request->all();

        $idArr = explode(",",$input['id']);
        $isPrimaryArr = explode(",",$input['is_primary']);
        $data=[];
        if($idArr){
            $i=0;
            foreach ($idArr as $value){
                $data[$i]['is_primary'] = $isPrimaryArr[$i];
                $data[$i]['permission_id'] = $value;
                $data[$i]['user_id'] = $this->logged_user->id;
                $data[$i]['company_id'] = $this->company_id;
                $i++;
            }
        }

        DashboardSetting::query()->where('user_id', $this->logged_user->id)->delete();

        $permission = DashboardSetting::query()->insert($data);
        return $this->sendResponse([], 'Successfully saved successfully.');
    }

    public function getLeadStage($fil_user_id)
    {
//        $input = $request->all();
        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
        $widgets = LeadStage::query()
            ->leftJoin('customers', function ($join) use($fil_user_id){ //
                $join->on('customers.lead_stage_id', '=', 'lead_stages.id')
                    ->where(function ($query){
                        if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                            $query->where('customers.assigned_to_user', '=', $this->logged_user->id);
//                            $query->orwhere('customers.user_id', '=', $this->logged_user->id); CHX
                        }
                    })
                    ->where(function ($query) use($fil_user_id) {

                        if ($fil_user_id == 0) {
                            $query->where('customers.company_id', '=', $this->company_id);
                        }

                        if ($fil_user_id > 0) {
                            $query->where('customers.assigned_to_user', $fil_user_id);
//                            $query->orwhere('customers.user_id', $fil_user_id); CHX
//                            $query->where('customers.company_id', $this->company_id);
                        }
                    });
                    /*->where(function ($query) use ($input) {
                        $query->whereBetween(DB::raw("DATE(customers.created_at)"), [$input['fil_estimate_start'], $input['fil_estimate_end']]);
                    });*/
            })
            ->where('lead_stages.company_id', $this->company_id)
            ->select('lead_stages.name', 'lead_stages.color_code', 'lead_stages.id', DB::raw('COALESCE(COUNT(customers.id), 0) as widget_total'))
            ->groupBy('lead_stages.id')
            ->orderBy('lead_stages.priority', 'asc')
            ->get()
            ->toArray();

        return $this->sendResponse($widgets, 'Lead Stages retrieved successfully.');
        /*return response()->json([
            "success" => true,
            "message" => "Lead Stages retrieved successfully.",
            "widgets" => $widgets
        ], 200);*/
    }

    public function getOpenOprDashboard($fil_user_id)
    {
        $data=[];
        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');



        $leadcount = DB::table('customers_views as cv')
            ->select('cv.id')
            ->where('cv.company_id', $this->company_id)
            ->whereNotIn('cv.lead_stage_name', ['Lead Won','Lead Lost'])
//            ->where('cv.some_day_flg', 0)
            ->where(function ($query) use ($fil_user_id) {
                if ($fil_user_id > 0) {
                    $query->where('cv.assigned_to_user', '=', $fil_user_id);
                }
            })
//            ->where(DB::raw('DATE(cv.last_follow_up_datetime)'), "<", date('Y-m-d'))
//            ->where('cv.last_follow_up_datetime', '=', '0000-00-00 00:00:00')
            ->where(function ($query){
                $query->where('cv.last_follow_up_datetime', '=', '0000-00-00 00:00:00')
                    ->orWhere('cv.last_follow_up_datetime', '=', '');
            })
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id); CHX
                }
            })
//            ->distinct('cv.id')
            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->count();

        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.id')
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 0)
            ->where(function ($query) use($fil_user_id){
                if ($fil_user_id > 0) {
                    $query->where('cv.assigned_to_user', '=', $fil_user_id);
//                    $query->orwhere('cv.user_id', '=', $fil_user_id); CHX
                }
            })
            ->where(DB::raw('DATE(cv.last_follow_up_datetime)'), "<", date('Y-m-d'))
            ->where('cv.last_follow_up_datetime', '!=', '0000-00-00 00:00:00')
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id); CHX
                }
            })
            ->distinct('cv.id')
            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->count();




        $widgets = LeadStage::query()
            ->join('customers', function ($join) use($fil_user_id){
                $join->on('customers.lead_stage_id', '=', 'lead_stages.id')
                    ->where(function ($query) {
                        if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                            $query->where('customers.assigned_to_user', '=', $this->logged_user->id);
//                            $query->orwhere('customers.user_id', '=', $this->logged_user->id); CHX
                        }
                    })
                    ->where(function ($query) use($fil_user_id){
                        if ($fil_user_id == 0) {
                            $query->where('customers.company_id', '=', $this->company_id);
                        }

                        if ($fil_user_id > 0) {
                            $query->where('customers.assigned_to_user', $fil_user_id);
//                            $query->orwhere('customers.user_id', $fil_user_id); CHX
                        }
                    });
            })
            ->where('lead_stages.is_default', 1)
            ->where('lead_stages.company_id', $this->company_id)
            ->select(DB::raw('COALESCE(COUNT(customers.id), 0) as total_count'))
            ->get()
            ->toArray();
        /*->select('lead_stages.name', 'lead_stages.color_code', 'lead_stages.id', DB::raw('COALESCE(COUNT(customers.id), 0) as widget_total'))
        ->groupBy('customers.id')
        ->orderBy('lead_stages.priority', 'asc')
        ->get()
        ->toArray();*/

        $data['lead_without_followup'] = $leadcount;
        $data['overdue_follow_up'] = $records;
        $data['total_lead'] = $widgets[0]['total_count'];

        return $this->sendResponse($data, 'Lead Stages retrieved successfully.');

        /*return response()->json([
            "success" => true,
            "message" => "Follow up retrieved successfully.",
            "lead_without_followup" => $leadcount,
            "overdue_follow_up" => $records,
            "total_lead" => $widgets[0]['total_count'],
        ], 200);*/
        //        return $this->sendResponse(["count" => $records], 'Follow up retrieved successfully');
    }

    public function getPeriodicOprDashboard($fil_user_id,$date)
    {
        $data=[];
        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');

        $result1 = DB::table('customers_views as cv')
            ->leftJoin('customer_timelines as ct', 'cv.id', '=', 'ct.customer_id')
            ->select('ct.activity_type')
            ->selectRaw('
        CASE
            WHEN ct.activity_type = 1 THEN "Call"
            ELSE "Other"
        END AS activity_name
    ') //WHEN ct.activity_type = 3 THEN "Meeting"
            ->selectRaw('cv.id')
            ->where('cv.company_id', $this->company_id)
//            ->where('cv.some_day_flg', 0)
            ->when($fil_user_id > 0, function ($query) use($fil_user_id) {
                return $query->where(function ($query)use ($fil_user_id) {
//                    $query->where('cv.assigned_to_user', '=', $fil_user_id]); CHX
                    $query->where('ct.user_id', '=', $fil_user_id);
                });
                //return $query->where('cv.assigned_to_user', '=', $fil_user_id);
            })
            ->when(in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm), function ($query) {
                return $query->where(function ($query) {
//                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);  CHX
                    $query->where('ct.user_id', '=', $this->logged_user->id);
                });
            })
//            ->whereIn('ct.activity_type', [1, 2, 3])
            ->whereIn('ct.activity_type', [1])
            ->where(function ($query) use ($date) {
                $dateArr = explode("_", $date);
                $query->whereBetween(DB::raw("DATE(ct.created_at)"), [$dateArr[0], $dateArr[1]]);
            })
            /*->groupBy('ct.activity_type', 'activity_name')
            ->orderByDesc('ct.activity_type')*/
            ->get();

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
            ->selectRaw('COALESCE(COUNT(ct.activity_type), 0) AS activity_type_count')
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 0)
            ->when($fil_user_id > 0, function ($query) use($fil_user_id) {
                return $query->where(function ($query)use ($fil_user_id) {
                    $query->where('cv.assigned_to_user', '=', $fil_user_id)
                        ->orWhere('cv.user_id', '=', $fil_user_id);
                });
                //return $query->where('cv.assigned_to_user', '=', $fil_user_id);
            })
            ->when(in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm), function ($query) {
                return $query->where(function ($query) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id)
                        ->orWhere('cv.user_id', '=', $this->logged_user->id);
                });
            })
//            ->whereIn('ct.activity_type', [1, 2, 3])
            ->whereIn('ct.activity_type', [2])
            ->where(function ($query) use ($date) {
                $dateArr = explode("_", $date);
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
            ->when($fil_user_id > 0, function ($query) use ($fil_user_id) {
                return $query->where(function ($query)use ($fil_user_id) {
//                    $query->where('cv.assigned_to_user', '=', $fil_user_id); // CHX
                    $query->where('ct.user_id', '=', $fil_user_id);
                });
//                return $query->where('cv.assigned_to_user', '=', $input['fil_user_id']);
            })
            ->when(
                in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) ||
                in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm),
                function ($query) {
                    return $query->where(function ($query) {
//                        $query->where('cv.assigned_to_user', '=', $this->logged_user->id); CHX
                        $query->where('ct.user_id', '=', $this->logged_user->id);
                    });
                }
            )
            ->whereIn('ct.activity_type', [2])
            ->where(function ($query) use ($date) {
                $dateArr = explode("_", $date);
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
            ->where(function ($query) use ($date) {
                $dateArr = explode("_", $date);
                $query->whereBetween("performance_date", [$dateArr[0], $dateArr[1]]);
            })
            ->where(function ($query) use ($fil_user_id) {
                if ($fil_user_id == 0) {
                    $query->where('company_id', '=', $this->company_id);
                }

                if ($fil_user_id > 0) {
                    $query->where('user_id', $fil_user_id);
                    $query->where('company_id', $this->company_id);
                }
            })
            ->select(DB::raw("SUM(total_task) as total_task"), DB::raw("SUM(completed_task) as completed_task"))
            ->get()->toArray();

        $total_task = array_column($sales_performance, 'total_task');
        $completed_task = array_column($sales_performance, 'completed_task');


        /*$widgets = LeadStage::query()
            ->join('customers', function ($join) use ($date,$fil_user_id) {
                $join->on('customers.lead_stage_id', '=', 'lead_stages.id')
                    ->where(function ($query) {
                        if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                            $query->where('customers.assigned_to_user', '=', $this->logged_user->id);
                            $query->orwhere('customers.user_id', '=', $this->logged_user->id);
                        }
                    })
                    ->where(function ($query) use ($fil_user_id) {
                        if ($fil_user_id == 0) {
                            $query->where('customers.company_id', '=', $this->company_id);
                        }

                        if ($fil_user_id > 0) {
                            $query->where('customers.assigned_to_user', $fil_user_id);
                            $query->where('customers.company_id', $this->company_id);
                        }
                    })
                    ->where(function ($query) use ($date) {
                        $dateArr = explode("_", $date);
                        $query->whereBetween(DB::raw("DATE(customers.created_at)"), [$dateArr[0], $dateArr[1]]);
                    });

            })
            ->where('lead_stages.company_id', $this->company_id)
//            ->where('lead_stages.is_default', 1)
            ->select('lead_stages.name', 'lead_stages.color_code', 'lead_stages.id', DB::raw('COALESCE(COUNT(customers.id), 0) as widget_total'))
            ->groupBy('lead_stages.id')
            ->orderBy('lead_stages.priority', 'asc')
            ->get()
            ->toArray();*/

        $widgets = LeadStage::query()
            ->join('customers', function ($join) use ($date,$fil_user_id) {
                $join->on('customers.lead_stage_id', '=', 'lead_stages.id')
                    ->where(function ($query) {
                        if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
//                            $query->where('customers.assigned_to_user', '=', $this->logged_user->id); CHX
                            $query->where('customers.user_id', '=', $this->logged_user->id);
                        }
                    })
                    ->where(function ($query) use ($fil_user_id) {
                        if ($fil_user_id == 0) {
                            $query->where('customers.company_id', '=', $this->company_id);
                        }

                        if ($fil_user_id > 0) {
//                            $query->where('customers.assigned_to_user', $fil_user_id);  CHX
                            $query->where('customers.user_id', $fil_user_id);
                        }
                    })
                    ->where(function ($query) use ($date) {
                        $dateArr = explode("_", $date);
                        $query->whereBetween(DB::raw("DATE(customers.created_at)"), [$dateArr[0], $dateArr[1]]);
                    });

            })
            ->where('lead_stages.company_id', $this->company_id)
            ->select('customers.id')
            ->orderBy('lead_stages.priority', 'asc')
            ->get();

        $wids = '';
        foreach ($widgets as $chunk) {
            $wids .= $chunk->id.',';
        }

        $widsString = trim($wids,',');
        $widsStringcount = $widgets->count();

        $widgetsArray = DB::table(function ($query) use ($date) {
            $query->select(DB::raw('DISTINCT estimate_no, status'), 'company_id', 'customer_id')
                ->from('estimates')
                ->where(function ($query) use ($date) {
                    $dateArr = explode("_", $date);
                    $query->whereBetween(DB::raw("DATE(estimate_date)"), [$dateArr[0], $dateArr[1]]);
                });
        }, 'subquery')
            ->leftjoin('customers_views', 'subquery.customer_id', '=', 'customers_views.id')
            ->where(function ($query) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                    $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orWhere('customers_views.user_id', '=', $this->logged_user->id); CHX
                }
            })
            ->where(function ($query) use ($fil_user_id) {
                if ($fil_user_id == 0) {
                    $query->where('customers_views.company_id', '=', $this->company_id);
                }

                if ($fil_user_id > 0) {
                    $query->where('customers_views.assigned_to_user', $fil_user_id);
//                    $query->orwhere('customers_views.user_id', $fil_user_id); CHX
                }
            })
            ->where('subquery.company_id', '=', $this->company_id)
            ->where('subquery.status', '!=', '')
            ->select('subquery.status', DB::raw('COUNT(subquery.status) as widget_total'))
            ->groupBy('subquery.status')
            ->get();

        $est_count = json_decode(json_encode($widgetsArray), true);

        $total = 0 + array_sum(array_column($est_count, 'widget_total'));


        $data['message_count'] = ($result_message)?$result_message['activity_type_count']:0;
        $data['call_count'] = ($result)?$result['activity_type_count']:0;
        $data['total_task'] = (int)$total_task[0];
        $data['new_lead_count'] = [[
            "name" => "New Lead",
            "color_code" => "#006398",
            "id" => $widsString,
            "widget_total" => $widsStringcount
        ]];
        $data['est_count'] = $total;

        return $this->sendResponse($data, 'Opr retrieved successfully.');

        /*return response()->json([
            "success" => true,
            "message" => "Follow up retrieved successfully.",
            "message_count" => ($result_message)?$result_message[0]->activity_type_count:0,
            "call_count" => ($result)?$result[0]->activity_type_count:0,
            "total_task" => (int)$total_task[0],
            "new_lead_count" => $widgets,
            "est_count" => $total,
        ], 200);*/
        //        return $this->sendResponse(["count" => $records], 'Follow up retrieved successfully');
    }

    public function getResultOprDashboard($fil_user_id,$date)
    {
        $data=[];
        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');


        $sales_performance = DB::table('sales_person_performances')
            ->where('completed_task', '=', 1)
            ->where(function ($query) use ($date) {
                $dateArr = explode("_", $date);
                $query->whereBetween(DB::raw("DATE(updated_at)"), [$dateArr[0], $dateArr[1]]);
            })
            ->where(function ($query) use ($fil_user_id) {
                if ($fil_user_id == 0 && in_array('access-all-lead-and-assign-to-anyone-in-team',$this->user_perm)) {
                    $query->where('company_id', '=', $this->company_id);
                }

                if ($fil_user_id > 0) {
                    $query->where('user_id', $fil_user_id);
                    $query->where('company_id', $this->company_id);
                }
            })
            ->where(function ($query){
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                    $query->where('user_id', '=', $this->logged_user->id);
                    $query->where('company_id', '=', $this->company_id);
                }
            })
            ->select(DB::raw("SUM(total_task) as total_task"), DB::raw("SUM(completed_task) as completed_task"))
            ->get()->toArray();

        $total_task = array_column($sales_performance, 'total_task');
        $completed_task = array_column($sales_performance, 'completed_task');


        $lead_won_count = DB::table('customers_views as customers')
            ->leftJoin('customer_timeline_lead_lost_common as customer_timeline_lead_won', function ($join) use ($fil_user_id,$date) {
                $join->on('customer_timeline_lead_won.customer_id', '=', 'customers.id')
                    ->where(function ($query) {
                        if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                            $query->where('customer_timeline_lead_won.user_id', '=', $this->logged_user->id);
//                            $query->orwhere('customers.user_id', '=', $this->logged_user->id); CHX
                        }
                    })
                    ->where(function ($query) use ($fil_user_id) {
                        if ($fil_user_id == 0) {
                            $query->where('customers.company_id', '=', $this->company_id);
                        }

                        if ($fil_user_id > 0) {
                            $query->where('customer_timeline_lead_won.user_id', $fil_user_id);
//                            $query->orwhere('customers.user_id', $fil_user_id); CHX
                        }
                    })
                    ->where(function ($query) use ($date) {
                        $dateArr = explode("_", $date);
                        $query->whereBetween(DB::raw("DATE(customer_timeline_lead_won.created_at)"), [$dateArr[0], $dateArr[1]]);
                    });
            })
            ->where('customers.company_id', $this->company_id)
            ->where('customers.lead_stage_name', 'Lead Won')
            /*->where(function ($query) {
                $query->where('customers.last_follow_up_datetime', '=', '0000-00-00 00:00:00');
                $query->orwhere('customers.last_follow_up_datetime', '=', '');
            })*/
            ->where('customer_timeline_lead_won.activity_type',18)
            ->select(DB::raw('COALESCE(COUNT(customer_timeline_lead_won.customer_id), 0) as widget_total'))
//            ->groupBy('lead_stages.id')
            ->get();

        $lead_won_total = $lead_won_count->isEmpty() ? 0 : $lead_won_count->first()->widget_total;

        $lead_lost_count = DB::table('customers_views as customers')
            ->join('customer_timeline_lead_lost_common as customer_timeline_lead_lost', function ($join) use ($fil_user_id,$date) {
                $join->on('customer_timeline_lead_lost.customer_id', '=', 'customers.id')
                    ->where(function ($query) {
                        if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                            $query->where('customer_timeline_lead_lost.user_id', '=', $this->logged_user->id);
//                            $query->orwhere('customers.user_id', '=', $this->logged_user->id); CHX
                        }
                    })
                    ->where(function ($query) use ($fil_user_id) {
                        if ($fil_user_id == 0) {
                            $query->where('customers.company_id', '=', $this->company_id);
                        }

                        if ($fil_user_id > 0) {
                            $query->where('customer_timeline_lead_lost.user_id', $fil_user_id);
//                            $query->orwhere('customers.user_id', $fil_user_id); CHX
                        }
                    })
                    ->where(function ($query) use ($date) {
                        $dateArr = explode("_", $date);
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
            ->select(DB::raw('COALESCE(COUNT(customer_timeline_lead_lost.customer_id), 0) as widget_total'))
//            ->groupBy('lead_stages.id')
            ->get();
        $lead_lost_total = $lead_lost_count->isEmpty() ? 0 : $lead_lost_count->first()->widget_total;
        $result='';
        if($this->main_company->company_category > 0) {
            $result = DB::table('customers_views as cv')
                ->join('customer_timelines as ct', 'cv.id', '=', 'ct.customer_id')
                ->select('ct.activity_type')
                ->selectRaw('
        CASE
            WHEN ct.activity_type = 3 THEN "Meeting"
            ELSE "Other"
        END AS activity_name
    ')
                ->selectRaw('COALESCE(COUNT(ct.activity_type), 0) AS activity_type_count')
                ->where('cv.company_id', $this->company_id)
                ->where('cv.some_day_flg', 0)
                ->when($fil_user_id > 0, function ($query) use ($fil_user_id) {
                    return $query->where('ct.user_id', '=', $fil_user_id);
                })
                ->when(in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm), function ($query) {
                    return $query->where(function ($query) {
                        $query->where('ct.user_id', '=', $this->logged_user->id);
//                            ->orWhere('cv.user_id', '=', $this->logged_user->id); CHX
                    });
                })
                ->whereIn('ct.activity_type', [3])
                ->where(function ($query) use ($date) {
                    $dateArr = explode("_", $date);
                    $query->whereBetween(DB::raw("DATE(ct.created_at)"), [$dateArr[0], $dateArr[1]]);
                })
                ->groupBy('ct.activity_type', 'activity_name')
                ->orderByDesc('ct.activity_type')
                ->get()->first();
        }

        $result_visit='';
        if($this->main_company->company_category == 1) {
            $result_visit = DB::table('customers_views as cv')
                ->join('customer_timelines as ct', 'cv.id', '=', 'ct.customer_id')
                ->select('ct.activity_type')
                ->selectRaw('
        CASE
            WHEN ct.activity_type = 3 THEN "Meeting"
            ELSE "Other"
        END AS activity_name
    ')
                ->selectRaw('COALESCE(COUNT(ct.activity_type), 0) AS activity_type_count')
                ->where('cv.company_id', $this->company_id)
                ->where('cv.some_day_flg', 0)
                ->when($fil_user_id > 0, function ($query) use ($fil_user_id) {
                    return $query->where('ct.user_id', '=', $fil_user_id);
                })
                ->when(in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm), function ($query) {
                    return $query->where(function ($query) {

                        $query->where('ct.user_id', '=', $this->logged_user->id);
//                            ->orWhere('cv.user_id', '=', $this->logged_user->id); CHX
                    });
                })
                ->whereIn('ct.activity_type', [19])
                ->where(function ($query) use ($date) {
                    $dateArr = explode("_", $date);
                    $query->whereBetween(DB::raw("DATE(ct.created_at)"), [$dateArr[0], $dateArr[1]]);
                })
                ->groupBy('ct.activity_type', 'activity_name')
                ->orderByDesc('ct.activity_type')
                ->get()->first();
        }

        $data['call_count'] = ($result)? $result->activity_type_count : 0;
        $data['visit_count'] = ($result_visit)? $result_visit->activity_type_count : 0;
        $data['total_task'] = (int)$completed_task[0];
        $data['lead_won_count'] = $lead_won_total;
        $data['lead_lost_count'] = $lead_lost_total;

        return $this->sendResponse($data, 'Opr retrieved successfully.');


        /*return response()->json([
            "success" => true,
            "message" => "Follow up retrieved successfully.",
            "response" => ($result)? $result->activity_type_count : 0,
            "total_task" => (int)$completed_task[0],
            "lead_won_count" => $lead_won_total,
            "lead_lost_count" => $lead_lost_total,
        ], 200);*/
        //        return $this->sendResponse(["count" => $records], 'Follow up retrieved successfully');
    }

    public function getNewOpenOprDashboard($fil_user_id)
    {
        $data=[];
        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');

        $leadcount = DB::table('customers_views as cv')
            ->select('cv.id')
            ->where('cv.company_id', $this->company_id)
            ->whereNotIn('cv.lead_stage_name', ['Lead Won','Lead Lost'])
            ->where(function ($query) use ($fil_user_id) {
                if ($fil_user_id > 0) {
                    $query->where('cv.assigned_to_user', '=', $fil_user_id);
                }
            })
            ->where(function ($query){
                $query->where('cv.last_follow_up_datetime', '=', '0000-00-00 00:00:00')
                    ->orWhere('cv.last_follow_up_datetime', '=', '');
            })
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
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


        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.id')
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 0)
            ->where(function ($query) use($fil_user_id){
                if ($fil_user_id > 0) {
                    $query->where('cv.assigned_to_user', '=', $fil_user_id);
                }
            })
            ->where(DB::raw('DATE(cv.last_follow_up_datetime)'), "<", date('Y-m-d'))
            ->where('cv.last_follow_up_datetime', '!=', '0000-00-00 00:00:00')
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
                }
            })
            ->distinct('cv.id')
            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->get();

        $rids = '';
        foreach ($records as $chunk) {
            $rids .= $chunk->id.',';
        }

        $ridsString = trim($rids,',');
        $ridsStringcount = $records->count();

        $widgets = LeadStage::query()
            ->join('customers', function ($join) use($fil_user_id){
                $join->on('customers.lead_stage_id', '=', 'lead_stages.id')
                    ->where(function ($query) {
                        if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                            $query->where('customers.assigned_to_user', '=', $this->logged_user->id);
                        }
                    })
                    ->where(function ($query) use($fil_user_id){
                        if ($fil_user_id == 0) {
                            $query->where('customers.company_id', '=', $this->company_id);
                        }

                        if ($fil_user_id > 0) {
                            $query->where('customers.assigned_to_user', $fil_user_id);
                        }
                    });
            })
            ->where('lead_stages.is_default', 1)
            ->where('lead_stages.company_id', $this->company_id)
            ->select('customers.id')
            ->get();

        $wids = '';
        foreach ($widgets as $chunk) {
            $wids .= $chunk->id.',';
        }

        $widsString = trim($wids,',');
        $widsStringcount = $widgets->count();

        $data['lead_without_followup'] = $idsStringcount;
        $data['lead_without_followup_ids'] = $idsString;
        $data['overdue_follow_up'] = $ridsStringcount;
        $data['overdue_follow_up_ids'] = $ridsString;
        $data['total_lead'] = $widsStringcount;
        $data['total_lead_ids'] = $widsString;

        return $this->sendResponse($data, 'Lead Stages retrieved successfully.');

    }

    public function getNewPeriodicOprDashboard($fil_user_id,$date)
    {
        $data=[];
        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');

        $result1 = DB::table('customers_views as cv')
            ->leftJoin('customer_timelines as ct', 'cv.id', '=', 'ct.customer_id')
            ->select('ct.activity_type')
            ->selectRaw('
        CASE
            WHEN ct.activity_type = 1 THEN "Call"
            ELSE "Other"
        END AS activity_name
    ') //WHEN ct.activity_type = 3 THEN "Meeting"
            ->selectRaw('cv.id')
            ->where('cv.company_id', $this->company_id)
            ->when($fil_user_id > 0, function ($query) use($fil_user_id) {
                return $query->where(function ($query)use ($fil_user_id) {
                    $query->where('ct.user_id', '=', $fil_user_id);
                });
            })
            ->when(in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm), function ($query) {
                return $query->where(function ($query) {
                    $query->where('ct.user_id', '=', $this->logged_user->id);
                });
            })
            ->whereIn('ct.activity_type', [1])
            ->where(function ($query) use ($date) {
                $dateArr = explode("_", $date);
                $query->whereBetween(DB::raw("DATE(ct.created_at)"), [$dateArr[0], $dateArr[1]]);
            })
            ->get();

        $result = $result1->groupBy('activity_name')->map(function ($group) {
            return [
                'activity_name' => $group->first()->activity_name,
                'activity_type_count' => $group->count(),
                'ids' => $group->pluck('id')->unique()->implode(','),
            ];
        })->values()->sortByDesc('activity_name')->first();

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
            ->when($fil_user_id > 0, function ($query) use ($fil_user_id) {
                return $query->where(function ($query)use ($fil_user_id) {
                    $query->where('ct.user_id', '=', $fil_user_id);
                });
//                return $query->where('cv.assigned_to_user', '=', $input['fil_user_id']);
            })
            ->when(
                in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) ||
                in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm),
                function ($query) {
                    return $query->where(function ($query) {
                        $query->where('ct.user_id', '=', $this->logged_user->id);
                    });
                }
            )
            ->whereIn('ct.activity_type', [2])
            ->where(function ($query) use ($date) {
                $dateArr = explode("_", $date);
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
            ->where(function ($query) use ($date) {
                $dateArr = explode("_", $date);
                $query->whereBetween("performance_date", [$dateArr[0], $dateArr[1]]);
            })
            ->where(function ($query) use ($fil_user_id) {
                if ($fil_user_id == 0) {
                    $query->where('company_id', '=', $this->company_id);
                }

                if ($fil_user_id > 0) {
                    $query->where('user_id', $fil_user_id);
                    $query->where('company_id', $this->company_id);
                }
            })
            ->select(DB::raw("SUM(total_task) as total_task"), DB::raw("SUM(completed_task) as completed_task"))
            ->get()->toArray();

        $total_task = array_column($sales_performance, 'total_task');
        $completed_task = array_column($sales_performance, 'completed_task');


        $widgets = LeadStage::query()
            ->join('customers', function ($join) use ($date,$fil_user_id) {
                $join->on('customers.lead_stage_id', '=', 'lead_stages.id')
                    ->where(function ($query) {
                        if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                            $query->where('customers.user_id', '=', $this->logged_user->id);
                        }
                    })
                    ->where(function ($query) use ($fil_user_id) {
                        if ($fil_user_id == 0) {
                            $query->where('customers.company_id', '=', $this->company_id);
                        }

                        if ($fil_user_id > 0) {
//                            $query->where('customers.assigned_to_user', $fil_user_id);  CHX
                            $query->where('customers.user_id', $fil_user_id);
                        }
                    })
                    ->where(function ($query) use ($date) {
                        $dateArr = explode("_", $date);
                        $query->whereBetween(DB::raw("DATE(customers.created_at)"), [$dateArr[0], $dateArr[1]]);
                    });

            })
            ->where('lead_stages.company_id', $this->company_id)
            ->select('customers.id')
            ->orderBy('lead_stages.priority', 'asc')
            ->get();

        $wids = '';
        foreach ($widgets as $chunk) {
            $wids .= $chunk->id.',';
        }

        $widsString = trim($wids,',');
        $widsStringcount = $widgets->count();

        $widgetsArray = DB::table(function ($query) use ($date) {
            $query->select(DB::raw('DISTINCT estimate_no, status'), 'company_id', 'customer_id')
                ->from('estimates')
                ->where(function ($query) use ($date) {
                    $dateArr = explode("_", $date);
                    $query->whereBetween(DB::raw("DATE(estimate_date)"), [$dateArr[0], $dateArr[1]]);
                });
        }, 'subquery')
            ->leftjoin('customers_views', 'subquery.customer_id', '=', 'customers_views.id')
            ->where(function ($query) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                    $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orWhere('customers_views.user_id', '=', $this->logged_user->id); CHX
                }
            })
            ->where(function ($query) use ($fil_user_id) {
                if ($fil_user_id == 0) {
                    $query->where('customers_views.company_id', '=', $this->company_id);
                }

                if ($fil_user_id > 0) {
                    $query->where('customers_views.assigned_to_user', $fil_user_id);
                    $query->orwhere('customers_views.user_id', $fil_user_id);
                }
            })
            ->where('subquery.company_id', '=', $this->company_id)
            ->where('subquery.status', '!=', '')
            ->select('subquery.status', DB::raw('COUNT(subquery.status) as widget_total'))
            ->groupBy('subquery.status')
            ->get();

        $est_count = json_decode(json_encode($widgetsArray), true);

        $total = 0 + array_sum(array_column($est_count, 'widget_total'));

        $data['message_count'] = ($result_message)?$result_message['activity_type_count']:0;
        $data['message_count_ids'] = ($result_message)?implode(",",explode(',',$result_message['ids'])):null;
        $data['call_count'] = ($result)?$result['activity_type_count']:0;
        $data['call_count_ids'] = ($result)?implode(",",explode(',',$result['ids'])):null;
        $data['total_task'] = (int)$total_task[0];
        $data['new_lead_count'] = [[
            "name" => "New Lead",
            "color_code" => "#006398",
            "id" => $widsString,
            "widget_total" => $widsStringcount
        ]];
        $data['est_count'] = $total;

        return $this->sendResponse($data, 'Opr periodic retrieved successfully.');
    }

    public function getNewResultOprDashboard($fil_user_id,$date)
    {
        $data=[];
        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');


        $sales_performance = DB::table('sales_person_performances')
            ->where('completed_task', '=', 1)
            ->where(function ($query) use ($date) {
                $dateArr = explode("_", $date);
                $query->whereBetween(DB::raw("DATE(updated_at)"), [$dateArr[0], $dateArr[1]]);
            })
            ->where(function ($query) use ($fil_user_id) {
                if ($fil_user_id == 0 && in_array('access-all-lead-and-assign-to-anyone-in-team',$this->user_perm)) {
                    $query->where('company_id', '=', $this->company_id);
                }

                if ($fil_user_id > 0) {
                    $query->where('user_id', $fil_user_id);
                    $query->where('company_id', $this->company_id);
                }
            })
            ->where(function ($query){
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                    $query->where('user_id', '=', $this->logged_user->id);
                    $query->where('company_id', '=', $this->company_id);
                }
            })
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


        $lead_won_count = DB::table('customers_views as customers')
            ->join('customer_timeline_lead_lost_common as customer_timeline_lead_won', function ($join) use ($fil_user_id,$date) {
                $join->on('customer_timeline_lead_won.customer_id', '=', 'customers.id')
                    ->where(function ($query) {
                        if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                            $query->where('customer_timeline_lead_won.user_id', $this->logged_user->id);
                        }
                    })
                    ->where(function ($query) use ($fil_user_id) {
                        if ($fil_user_id == 0) {
                            $query->where('customers.company_id', '=', $this->company_id);
                        }

                        if ($fil_user_id > 0) {
                            $query->where('customer_timeline_lead_won.user_id', $fil_user_id);
                        }
                    })
                    ->where(function ($query) use ($date) {
                        $dateArr = explode("_", $date);
                        $query->whereBetween(DB::raw("DATE(customer_timeline_lead_won.created_at)"), [$dateArr[0], $dateArr[1]]);
                    });
            })
            ->where('customers.company_id', $this->company_id)
            ->where('customers.lead_stage_name', 'Lead Won')
            /*->where(function ($query) {
                $query->where('customers.last_follow_up_datetime', '=', '0000-00-00 00:00:00');
                $query->orwhere('customers.last_follow_up_datetime', '=', '');
            })*/
            ->where('customer_timeline_lead_won.activity_type',18)
            ->select('customer_timeline_lead_won.customer_id')
            ->get();

        $lead_won_count = collect($lead_won_count);
        $lead_won_total = $lead_won_count->unique('customer_id')->count();
        $lead_won_total_ids = $lead_won_count->pluck('customer_id')->unique()->implode(',');

        $lead_lost_count = DB::table('customers_views as customers')
            ->join('customer_timeline_lead_lost_common as customer_timeline_lead_lost', function ($join) use ($fil_user_id,$date) {
                $join->on('customer_timeline_lead_lost.customer_id', '=', 'customers.id')
                    ->where(function ($query) {
                        if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_perm)) {
                            $query->where('customer_timeline_lead_lost.user_id', $this->logged_user->id);
                        }
                    })
                    ->where(function ($query) use ($fil_user_id) {
                        if ($fil_user_id == 0) {
                            $query->where('customers.company_id', '=', $this->company_id);
                        }

                        if ($fil_user_id > 0) {
                            $query->where('customer_timeline_lead_lost.user_id', $fil_user_id);
                        }
                    })
                    ->where(function ($query) use ($date) {
                        $dateArr = explode("_", $date);
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
            ->select('customer_timeline_lead_lost.customer_id')
            ->get();

        $lead_lost_count = collect($lead_lost_count);
        $lead_lost_total = $lead_lost_count->unique('customer_id')->count();
        $lead_lost_total_ids = $lead_lost_count->pluck('customer_id')->unique()->implode(',');

        $result='';
        if($this->main_company->company_category > 0) {
            $result1 = DB::table('customers_views as cv')
                ->leftJoin('customer_timelines as ct', 'cv.id', '=', 'ct.customer_id')
                ->select('ct.activity_type')
                ->selectRaw('
    CASE
        WHEN ct.activity_type = 3 THEN "Meeting"
        ELSE "Other"
    END AS activity_name
')
                ->selectRaw('cv.id')
                ->where('cv.company_id', $this->company_id)
                ->where('cv.some_day_flg', 0)
                ->when($fil_user_id > 0, function ($query) use ($fil_user_id) {
                    return $query->where(function ($query) use ($fil_user_id) {
                        $query->where('ct.user_id', '=', $fil_user_id);
                    });
                })
                ->when(in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm), function ($query) {
                    return $query->where(function ($query) {
                        $query->where('ct.user_id', '=', $this->logged_user->id);
                    });
                })
                ->whereIn('ct.activity_type', [3])
                ->where(function ($query) use ($date) {
                    $dateArr = explode("_", $date);
                    $query->whereBetween(DB::raw("DATE(ct.created_at)"), [$dateArr[0], $dateArr[1]]);
                })
                ->get();
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
            $result_visit1 = DB::table('customers_views as cv')
                ->leftJoin('customer_timelines as ct', 'cv.id', '=', 'ct.customer_id')
                ->select('ct.activity_type')
                ->selectRaw('
    CASE
        WHEN ct.activity_type = 19 THEN "Meeting"
        ELSE "Other"
    END AS activity_name
')
                ->selectRaw('cv.id')
                ->where('cv.company_id', $this->company_id)
                ->where('cv.some_day_flg', 0)
                ->when($fil_user_id > 0, function ($query) use ($fil_user_id) {
                    return $query->where(function ($query) use ($fil_user_id) {
                        $query->where('ct.user_id', '=', $fil_user_id);
                    });
                })
                ->when(in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm), function ($query) {
                    return $query->where(function ($query) {
                        $query->where('ct.user_id', '=', $this->logged_user->id);
                    });
                })
                ->whereIn('ct.activity_type', [19])
                ->where(function ($query) use ($date) {
                    $dateArr = explode("_", $date);
                    $query->whereBetween(DB::raw("DATE(ct.created_at)"), [$dateArr[0], $dateArr[1]]);
                })
                ->get();
            $result_visit = $result_visit1->groupBy('activity_name')->map(function ($group) {
                return [
                    'activity_name' => $group->first()->activity_name,
                    'activity_type_count' => $group->count(),
                    'ids' => $group->pluck('id')->unique()->implode(','),
                ];
            })->values()->sortByDesc('activity_name')->first();
        }

        $data['call_count'] = ($result)? $result['activity_type_count'] : 0;
        $data['call_count_ids'] = ($result)? $result['ids'] : null;
        $data['visit_count'] = ($result_visit)? $result_visit['activity_type_count'] : 0;
        $data['visit_count_ids'] = ($result_visit)? $result_visit['ids'] : null;
        $data['total_task'] = $completed_task;
        $data['total_task_ids'] = $completed_task_ids;
        $data['lead_won_count'] = $lead_won_total;
        $data['lead_won_count_ids'] = $lead_won_total_ids;
        $data['lead_lost_count'] = $lead_lost_total;
        $data['lead_lost_count_ids'] = $lead_lost_total_ids;

        return $this->sendResponse($data, 'Opr retrieved successfully.');

    }
}
