<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\LeadStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LeadStageController extends Controller
{
    protected $logged_user = null;
    protected $company_id = 0;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->logged_user = \Illuminate\Support\Facades\Auth::user();
            $this->company_id = ($this->logged_user->company_id) ? $this->logged_user->company_id : $this->logged_user->id;
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            ## Read value
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length"); // Rows display per page

            $columnIndex_arr = $request->get('order');
            $columnName_arr = $request->get('columns');
            $order_arr = $request->get('order');
            $search_arr = $request->get('search')['value'];

            $columnIndex = $columnIndex_arr[0]['column']; // Column index
            $columnName = $columnName_arr[$columnIndex]['data']; // Column name
            $columnSortOrder = $order_arr[0]['dir']; // asc or desc

            // Fetch records
            $name = $request->get('name');
            $status = $request->get('status');
            // Total records

            $records = DB::table('lead_stages')
                ->where('company_id', $this->company_id)
                ->where(function ($query) use ($name, $status) {
                    if ($name != '') {
                        $query->Where(function ($query) use ($name) {
                            $query->where('name', '=', $name);
                        });
                    }
                    if ($status != '') {
                        $query->where(function ($query) use ($status) {
                            $query->where('status', '=', $status);
                        });
                    }
                });
            $totalRecords = $records->count();
            $records->where(function ($query) use ($search_arr) {
                    $query->where('name', 'like', $search_arr . '%');
            });
            /*->orWhere(function ($query) use ($search_arr) {
                if ($search_arr) {
                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('description', 'like', $search_arr . '%');
                    });
                }
            });*/
            $totalRecordswithFilter = $records->count();
            $recs = $records->skip($start)
                ->select('*')
                ->take($rowperpage)
                ->orderBy($columnName, $columnSortOrder)
                ->get();

            $data = array();
            $i = 0;
            foreach ($recs as $record) {
                $id = Crypt::encrypt($record->id);
                $name = $record->name;
//                $description = $record->description;
                $status = $record->status;
                $i++;
                $data[] = array(
                    "id" => $i,
                    "name" => $name,
//                    "description" => $description,
                    "status" => $status,
                    "priority" => $record->priority,
                    "is_delete" => $record->is_delete,
                    "color_code" => $record->color_code,
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

        return view('app.lead-stage');
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $validator = Validator::make($input, [
                'name' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);;
            }

            $input['user_id'] =$this->logged_user->id;
            $input['company_id'] =  $this->company_id;
            $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
            if (LeadStage::where('name', '=', $input['name'])->where('company_id', $input['company_id'])->select('id')->where(function ($query) use ($id) {
                if ($id != 0) {
                    $query->Where(function ($query) use ($id) {
                        $query->where('id', '!=', $id);
                    });
                }
            })->first()) {
                return response()->json(['success' => 'Lead Stage exists!'], 409);
            }
            if ($id == 0) {
                $maxPriority = LeadStage::where('company_id', $this->company_id)->max('priority');
                $newPriority = $maxPriority + 1;
                $input['priority'] = $newPriority;
                $activityLogMsg = 'Lead Stage created by ' . $this->logged_user->name;
                $unit = LeadStage::create($input);
            } else {
                $unit = LeadStage::find($id)->update($input);
                $activityLogMsg = 'Lead Stage updated by ' . $this->logged_user->name;
            }

            // Add activity logs
            $input['id'] = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
//            LogActivity::addToLog($activityLogMsg, $input);

            return response()->json(['success' => 'Lead Stage Saved!'], 201);
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

            $unit = LeadStage::find($id)->toArray();

            if (is_null($unit)) {
                return response()->json(['success' => 'Lead Stage not found!'], 422);
            }
            $unit['id'] = Crypt::encrypt($unit['id']);
            return response()->json([
                "success" => true,
                "message" => "Lead Stage retrieved successfully.",
                "data" => $unit
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
                return response()->json(['errors' => $validator->errors()->all()], 400);
            }
            $errors = [];

            $unitIds = explode(",", $request->id);
            foreach (explode(",", $request->id) as $value) {
                $ids[] = Crypt::decrypt($value);
            }
            /*$unitsWithTransactions = DB::table('customers')->join("lead_stages", 'customers.lead_stage_id', '=', 'lead_stages.id')
                ->whereIn('customers.lead_stage_id', $ids)
                ->pluck('customers.lead_stage_id','lead_stages.name')
                ->toArray();*/
            $unitsWithTransactions = [];

            foreach ($ids as $unitId) {
                if (in_array($unitId, $unitsWithTransactions)) {
                    $key = array_search($unitId, $unitsWithTransactions);
                    $errors[] = "{$key} has transactions associated with it in the leads list";
                    continue;
                }
                try {
                    DB::table('lead_stages')
                        ->where('id', $unitId)
                        ->delete();
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if($errors){
                return response()->json($errors,400);
            }

            /*  foreach (explode(",", $request->id) as $value) {
                  $id[] = Crypt::decrypt($value);
              }
              $unit = Unit::whereIn('id', $id)->delete();*/

//            LogActivity::addToLog('Lead Stage deleted by ' . $this->logged_user->name, $unitIds);
            return response()->json(['success' => 'Lead Stage Deleted!'], 201);
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

            $errors = [];

            $unitIds = explode(",", $request->id);
            foreach (explode(",", $request->id) as $value) {
                $ids[] = Crypt::decrypt($value);
            }
            /*$unitsWithTransactions = DB::table('customers')->join("lead_stages", 'customers.lead_stage_id', '=', 'lead_stages.id')
                ->whereIn('customers.lead_stage_id', $ids)
                ->pluck('customers.lead_stage_id','lead_stages.name')
                ->toArray();*/
            $unitsWithTransactions = [];

            foreach ($ids as $unitId) {
                /* if (in_array($unitId, $unitsWithTransactions)) {
                     $key = array_search($unitId, $unitsWithTransactions);
                     $errors[] = "{$key} has transactions associated with it in the leads list";
                     continue;
                 }*/
                try {
                    LeadStage::where('id', $unitId)->update(["status" => $input['status']]);
                    $data['id'] = $unitId;
                    $data['status'] = ($input['status'] == 0) ? 'Active' : 'Deactive';
//                    LogActivity::addToLog('Lead Stage status updated by ' . $this->logged_user->name, $data);
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if($errors){
                return response()->json($errors,400);
            }

            return response()->json(['success' => 'Lead Stage status updated!'], 201);
        }
    }

    public function sortableLeadStage(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();

            $tasks = LeadStage::all();

            foreach ($tasks as $task) {
                $id = $task->id;

                foreach ($input['order'] as $order) {
                    $rid= Crypt::decrypt($order['id']);
                    if ($rid == $id) {
                        $task->update(['priority' => $order['position']]);
                    }
                }
            }

//            return response('Update Successfully.', 200);

            return response()->json(['success' => 'Lead Stage status updated!'], 201);
        }
    }
}
