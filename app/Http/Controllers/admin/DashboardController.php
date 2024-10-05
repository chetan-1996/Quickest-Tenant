<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use App\Models\admin\LeadHistory;
use App\Models\admin\EstimateHistory;
use App\Models\admin\AttachmentHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function getWidget()
    {
        $widgets = Tenant::query()->groupBy('status')
            ->whereNull('company_id')->select('status', DB::raw('COUNT(status) as widget_total'))->get()->toArray();

        $a = [
            ['status' => 'Pending', 'widget_total' => 0],
            ['status' => 'New', 'widget_total' => 0],
            ['status' => 'Approved', 'widget_total' => 0],
            ['status' => 'Rejected', 'widget_total' => 0],
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

        return response()->json([
            "success" => true,
            "message" => "Follow up retrieved successfully.",
            "widgets" => $widgets,
            "total" => $total,
        ], 201);

    }

    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('admin.login'); // Redirect to login if not authenticated
        }
        /*$top_clients = DB::table('users_views')
            ->leftJoin('estimates', 'users_views.id', '=', 'estimates.company_id')
            ->selectRaw('users_views.name,users_views.email,users_views.profile_icon, count(estimates.company_id) as estimate_count')
            ->whereNull('users_views.company_id')
            ->skip(0)
            ->take(10)

            ->groupBy('users_views.id')
            ->orderBy('estimate_count', 'desc')
            ->get();*/

        $top_clients =  DB::table('tenants')
            ->leftJoin('estimate_histroy', 'tenants.id', '=', 'estimate_histroy.company_id')
            ->selectRaw('tenants.name,tenants.email,tenants.profile_icon, count(estimate_histroy.company_id) as estimate_count')
            ->whereNull('tenants.company_id')
            ->skip(0)
            ->take(10)
            ->groupBy('tenants.id')
            ->orderBy('estimate_count', 'desc')
            ->get();


        /*$new_clients = DB::table('users_views')
            ->selectRaw('name,email,profile_icon,mobile_no,created_at')
            ->where('status','=','New')
            ->whereNull('company_id')
            ->skip(0)
            ->take(10)
            ->orderBy('id', 'desc')
            ->get();

        $in_progress_clients = DB::table('users_views')
            ->selectRaw('name,email,profile_icon,mobile_no,created_at,updated_at')
            ->where('status','=','Pending')
            ->whereNull('company_id')
            ->skip(0)
            ->take(10)
            ->orderBy('id', 'desc')
            ->get();
        return view('admin.dashboard', compact('top_clients','new_clients','in_progress_clients'));*/
        return view('admin.dashboard', compact('top_clients'));
    }


    public function barChart(Request $request)
    {
        $input = $request->all();
        $dateArr = explode("_", $input['date']);
        // $startDate = now()->subWeek(); // Get the start date as one week ago from the current date
        // $endDate = now(); // Get the end date as the current date
        $startDate = $dateArr[0]; // Get the start date as one week ago from the current date
        $endDate = $dateArr[1]; // Get the end date as the current date

        $datewiseCounts =
            DB::table('estimate_histroy')
                ->select(DB::raw('COUNT(id) as total_count'), DB::raw("DATE_FORMAT(insert_date, '%d-%m-%Y') as estimate_date"))
                ->whereBetween(DB::raw('DATE(insert_date)'), [$startDate, $endDate])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy(DB::raw("DATE(created_at)"), 'asc')
                ->pluck('total_count', 'estimate_date');



        // Fill missing dates with 0 count
        $dateRange = $this->getDateRange($startDate, $endDate);

        $datewiseCounts = collect($dateRange)->mapWithKeys(function ($date) use ($datewiseCounts) {
            $formattedDate = $date;
            return [$formattedDate => $datewiseCounts[$formattedDate] ?? 0];
        });


        $keys = $datewiseCounts->keys()->all();
        $values = $datewiseCounts->values()->all();
        return response()->json([
            "success" => true,
            "message" => "Follow up retrieved successfully.",
            "labels" => $keys,
            "sent" => $values,
            // "close" => $close,
        ], 201);
        // return $datewiseCounts;
    }

    public function leadBarChart(Request $request)
    {
        $input = $request->all();
        $dateArr = explode("_", $input['date']);
        // $startDate = now()->subWeek(); // Get the start date as one week ago from the current date
        // $endDate = now(); // Get the end date as the current date
        $startDate = $dateArr[0]; // Get the start date as one week ago from the current date
        $endDate = $dateArr[1]; // Get the end date as the current date

        $datewiseCounts =
            DB::table('lead_histroy')
                ->select(DB::raw('COUNT(id) as total_count'), DB::raw("DATE_FORMAT(insert_date, '%d-%m-%Y') as lead_date"))
                ->whereBetween(DB::raw('DATE(insert_date)'), [$startDate, $endDate])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy(DB::raw("DATE(created_at)"), 'asc')
                ->pluck('total_count', 'lead_date');



        // Fill missing dates with 0 count
        $dateRange = $this->getDateRange($startDate, $endDate);

        $datewiseCounts = collect($dateRange)->mapWithKeys(function ($date) use ($datewiseCounts) {
            $formattedDate = $date;
            return [$formattedDate => $datewiseCounts[$formattedDate] ?? 0];
        });


        $keys = $datewiseCounts->keys()->all();
        $values = $datewiseCounts->values()->all();
        return response()->json([
            "success" => true,
            "message" => "lead retrieved successfully.",
            "labels" => $keys,
            "sent" => $values,
            // "close" => $close,
        ], 201);
        // return $datewiseCounts;
    }

    private function getDateRange($startDate, $endDate)
    {
        $dates = [];
        $currentDate = new \DateTime($startDate);

        while ($currentDate <= new \DateTime($endDate)) {
            $dates[] = $currentDate->format('d-m-Y');
            $currentDate->modify('+1 day');
        }

        return $dates;
    }

    protected function loggedOut(Request $request)
    {
        Auth::guard('web')->logout();
        // dd(route( 'admin.login' ));
        return redirect('/admin/login');
    }

    /*private function getDateRange($startDate, $endDate)
    {
        $dates = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dates[] = $currentDate->copy();
            $currentDate->addDay();
        }

        return collect($dates);
    }*/

   /* private function getDateRange($startDate, $endDate)
    {
        $dates = [];
        echo $startDate;
        echo $endDate;
        $currentDate = clone $startDate;

        while ($currentDate <= $endDate) {
            $dates[] = clone $currentDate;
            $currentDate->addDay();
        }

        return collect($dates);
    }*/


    /*public function barChart(Request $request)
    {

        $monthArr = [];
        $input = $request->all();
        $dateArr = explode("_", $input['date']);

        $year = date('Y', strtotime($dateArr[0]));
        $month = date('m', strtotime($dateArr[0]));
        for ($i = 0; $i < 12; $i++) {
            array_push($monthArr, date("M` Y", strtotime('+' . $i . ' month', date(strtotime('01-' . $month . '-' . $year)))));
        }

        $records = DB::table('estimates')
            ->select(DB::raw('COUNT(id) as total_count'), DB::raw("DATE_FORMAT(created_at, '%d-%b-%Y') as estimate_date"))
            ->whereBetween('created_at', [$dateArr[0], $dateArr[1]])
            ->groupBy('DATE(created_at)')
            ->orderBy("DATE(created_at)", 'asc')
            ->get();

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
                array_push($sent, number_format((float)$sum_sent,2,'.',''));
                array_push($close, number_format((float)$sum_close,2,'.',''));
            }
        }

        return response()->json([
            "success" => true,
            "message" => "Follow up retrieved successfully.",
            "labels" => $labels,
            "sent" => $sent,
            "close" => $close,
        ], 201);
    }*/
}
