<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\PlanHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Helpers\LogActivity;
use Illuminate\Support\Facades\Auth;

class PlanHistoryController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('admin.login'); // Redirect to login if not authenticated
        }
        if ($request->ajax()) {
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
            // Fetch records

            // Total records
            $totalRecords = PlanHistory::count();

            $totalRecordswithFilter = PlanHistory::leftJoin('tenants', 'plan_history.user_id', '=', 'tenants.id')
                ->leftJoin('plans', 'plan_history.plan_id', '=', 'plans.id')
                ->where(function ($query) use ($search_arr) {
                    if ($search_arr) {
                        $query->orWhere('tenants.name', 'like', '%' .  $search_arr . '%');
                        $query->orWhere('tenants.email', 'like', '%' .  $search_arr . '%');
                        $query->orWhere('tenants.company_name', 'like', '%' . $search_arr . '%');
                        $query->orWhere('plan_history.user_limit', $search_arr);
                        $query->orWhere('plan_history.estimate_limit', $search_arr);
                    }
                })->count();

            // DB::enableQueryLog();
            $records = DB::table('plan_history')
                ->leftJoin('tenants', 'plan_history.user_id', '=', 'tenants.id')
                ->leftJoin('plans', 'plan_history.plan_id', '=', 'plans.id')
                ->where(function ($query) use ($search_arr) {
                    if ($search_arr) {
                        $query->orWhere('tenants.name', 'like', '%' .  $search_arr . '%');
                        $query->orWhere('tenants.email', 'like', '%' .  $search_arr . '%');
                        $query->orWhere('tenants.company_name', 'like', '%' . $search_arr . '%');
                        $query->orWhere('plan_history.user_limit', $search_arr);
                        $query->orWhere('plan_history.estimate_limit', $search_arr);
                    }
                })
                ->select(['tenants.name as user_name', 'tenants.email as user_email', 'tenants.company_name as user_company_name', 'plans.name as plan_name', 'plan_history.user_limit', 'plan_history.estimate_limit', 'plan_history.status', 'plan_history.id', 'plan_history.start_date', 'plan_history.end_date'])
                ->skip($start)
                ->take($rowperpage)
                ->orderBy('plan_history.id', $columnSortOrder)
                ->get();

            // dd(DB::getQueryLog($records));
            $data = array();
            $i = 0;
            foreach ($records as $record) {
                $id = Crypt::encrypt($record->id);
                $name = $record->user_name;
                $description = $record->plan_name;
                $user_limit = $record->user_limit;
                $start_date = $record->start_date;
                $end_date = $record->end_date;
                $user_email = $record->user_email;
                $user_company_name = $record->user_company_name;
                $estimate_limit = isset($record->estimate_limit) ? $record->estimate_limit : 0;
                $status = $record->status == 0 ? 'Inactive' : 'Active';
                // $status = $record->status;
                $i++;
                $data[] = array(
                    "id" => $i,
                    "user_id" => $name,
                    "plan_id" => $description,
                    "user_limit" => $user_limit,
                    "start_date" => $start_date,
                    "end_date" => $end_date,
                    "user_email" => $user_email,
                    "user_company_name" => $user_company_name,
                    "status" => $status,
                    "estimate_limit" => $estimate_limit,
                    "action" => $id,
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

        return view('admin.plan-history');
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();

            $user = Auth::user();
            // $companyId = isset($user->company_id) ? $user->company_id : $user->id;
            // $input['user_id'] = $companyId;
            $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
            // dd($input);
            // if (PlanHistory::with(['users', 'plans'])->where('users.name', '=', $input['name'])->where(function ($query) use ($id) {
            //     if ($id != 0) {
            //         $query->Where(function ($query) use ($id) {
            //             $query->where('id', '!=', $id);
            //         });
            //     }
            // })->first()) {
            //     return response()->json(['success' => 'Plan History exists!'], 409);
            // }
            if ($id == 0) {
                $activityLogMsg = 'Plan History created by ' . $user->name;
                $company_category = PlanHistory::create($input);
            } else {
                $company_category = PlanHistory::find($id)->update($input);
                $activityLogMsg = 'Plan History updated by ' . $user->name;
            }

            // Add activity logs
            $input['id'] = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
            LogActivity::addToLog($activityLogMsg, $input);

            return response()->json(['success' => 'Plan History Saved!'], 201);
        }
    }

    public function show(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $id = Crypt::decrypt($input['id']);
            $validator = Validator::make($input, [
                'id' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);
            }
            $user = Auth::user();

            $company_category = PlanHistory::with(['plans', 'tenants'])->find($id)->toArray();

            if (is_null($company_category)) {
                return response()->json(['success' => 'Plan History not found!'], 422);
            }
            $company_category['id'] = Crypt::encrypt($company_category['id']);
            return response()->json([
                "success" => true,
                "message" => "Plan History retrieved successfully.",
                "data" => $company_category
            ], 201);
        }
    }

    public function destroy(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $validator = Validator::make($input, [
                'id' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);;
            }
            $user = Auth::user();
            $id = [];
            foreach (explode(",", $request->id) as $value) {
                $id[] = Crypt::decrypt($value);
            }
            $company_category = PlanHistory::whereIn('id', $id)->delete();

            LogActivity::addToLog('Plan History deleted by ' . $user->name, $id);
            return response()->json(['success' => 'Plan History Deleted!'], 201);
        }
    }


    public function editStatus(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();

            $validator = Validator::make($input, [
                'id' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);;
            }
            $user = Auth::user();
            $id = [];
            foreach (explode(",", $input['id']) as $value) {
                $id[] = Crypt::decrypt($value);
            }
            if (!PlanHistory::whereIn('id', $id)->first()) {
                return response()->json(['success' => 'Plan History exists!'], 422);
            }
            $company_category = PlanHistory::whereIn('id', $id)->update(["status" => $input['status']]);

            $data['id'] = $id;
            $data['status'] = ($input['status'] == 0) ? 'Active' : 'Deactive';
            LogActivity::addToLog('Plan History status updated by ' . $user->name, $data);

            return response()->json(['success' => 'Plan History status updated!'], 201);
        }
    }
}
