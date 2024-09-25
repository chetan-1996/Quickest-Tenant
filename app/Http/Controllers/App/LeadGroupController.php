<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
//use App\Models\CustomerLabel;
use App\Models\LeadGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LeadGroupController extends Controller
{
    protected $logged_user = null;
    protected $company_id = 0;
    protected $segment = null;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->logged_user = \Illuminate\Support\Facades\Auth::user();
            $this->company_id = ($this->logged_user->company_id) ? $this->logged_user->company_id : $this->logged_user->id;
            $this->segment = $request->segment(1);
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

            $records = DB::table('lead_groups')
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
            if(!empty($search_arr)) {
                $records->where(function ($query) use ($search_arr) {
                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('name', 'like', $search_arr . '%');
                    });
                })
                    ->orWhere(function ($query) use ($search_arr) {
                        if ($search_arr) {
                            $query->orWhere(function ($query) use ($search_arr) {
                                $query->where('description', 'like', $search_arr . '%');
                            });
                        }
                    });
                $totalRecordswithFilter = $records->count();
            } else {
                $totalRecordswithFilter = $totalRecords;
            }
            $recs = $records->skip($start)
                ->select('id', 'name', 'color_code', 'description', 'status', 'company_id', 'user_id')
                ->take($rowperpage)
                ->orderBy($columnName, $columnSortOrder)
                ->get();

            $data = array();
            $i = 0;
            foreach ($recs as $record) {
                $id = Crypt::encrypt($record->id);
                $name = $record->name;
                $description = $record->description;
                $status = $record->status;
                $color_code = $record->color_code;
                $i++;
                $data[] = array(
                    "id" => $i,
                    "name" => $name,
                    "color_code" => $color_code,
                    "description" => $description,
                    "status" => $status,
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
        $segment = $this->segment;
        return view('app.lead-groups', compact('segment'));
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

            $input['user_id'] = $this->logged_user->id;
            $input['company_id'] =  $this->company_id;
            $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
            
            if (leadGroup::where('name', '=', $input['name'])->select('id')->where('company_id', $input['company_id'])->where(function ($query) use ($id) {
                if ($id != 0) {
                    $query->Where(function ($query) use ($id) {
                        $query->where('id', '!=', $id);
                    });
                }
            })->first()) {
                return response()->json(['success' => 'Lead group exists!'], 409);
            }
            if ($id == 0) {
                $activityLogMsg = 'Lead group created by ' . $this->logged_user->name;
                $leadGroup = leadGroup::create($input);
                $id = $leadGroup->id;
            } else {
                $leadGroup = leadGroup::find($id)->update($input);
                $activityLogMsg = 'lead group updated by ' . $this->logged_user->name;
            }

            // Add activity logs
            $input['id'] = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
//            LogActivity::addToLog($activityLogMsg, $input);

            return response()->json(['success' => 'lead group Saved!', "id" => $id, "name" => $input['name']], 201);
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

            $leadGroup = leadGroup::find($id)->toArray();

            if (is_null($leadGroup)) {
                return response()->json(['success' => 'Lead group not found!'], 422);
            }
            $leadGroup['id'] = Crypt::encrypt($leadGroup['id']);
            return response()->json([
                "success" => true,
                "message" => "Lead group retrieved successfully.",
                "data" => $leadGroup
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

            $errors = [];

            $leadGroupIds = explode(",", $request->id);
            foreach ($leadGroupIds as $value) {
                $ids[] = Crypt::decrypt($value);
            }
           /* $leadGroupsWithTransactions = DB::table('customer_labels')->join("lead_groups", 'customer_labels.label_id', '=', 'lead_groups.id')
                ->whereIn('customer_labels.label_id', $ids)
                ->pluck('customer_labels.label_id','lead_groups.name')
                ->toArray();*/
            $leadGroupsWithTransactions=[];
            foreach ($ids as $leadGroupId) {
                if (in_array($leadGroupId, $leadGroupsWithTransactions)) {
                    $key = array_search($leadGroupId, $leadGroupsWithTransactions);
                    $errors[] = "{$key} has transactions associated with it in the leads list";
                    continue;
                }
                try {
                    DB::table('lead_groups')
                        ->where('id', $leadGroupId)
                        ->delete();
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if($errors){
                return response()->json($errors,400);
            }

            /*$id = [];
            foreach (explode(",", $request->id) as $value) {
                $id[] = Crypt::decrypt($value);
            }
            $leadGroup = leadGroup::whereIn('id', $id)->delete();

            LogActivity::addToLog('Lead group deleted by ' . $this->logged_user->name, $id);*/
            return response()->json(['success' => 'Lead group Deleted!'], 201);
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

            $leadGroupIds = explode(",", $request->id);
            foreach ($leadGroupIds as $value) {
                $ids[] = Crypt::decrypt($value);
            }
            $leadGroupsWithTransactions=[];
//            $leadGroupsWithTransactions = DB::table('customer_labels')->join("lead_groups", 'customer_labels.label_id', '=', 'lead_groups.id')
//                ->whereIn('customer_labels.label_id', $ids)
//                ->pluck('customer_labels.label_id','lead_groups.name')
//                ->toArray();

            foreach ($ids as $leadGroupId) {
                /*if (in_array($leadGroupId, $leadGroupsWithTransactions)) {
                    $key = array_search($leadGroupId, $leadGroupsWithTransactions);
                    $errors[] = "{$key} has transactions associated with it in the leads list";
                    continue;
                }*/
                try {
                    leadGroup::where('id', $leadGroupId)->update(["status" => $input['status']]);
                    $data['id'] = $leadGroupId;
                    $data['status'] = ($input['status'] == 0) ? 'Active' : 'Deactive';
                    /*if($input['status'] == 1){
                        CustomerLabel::where('label_id', $leadGroupId)->delete();
                    }*/
//                    LogActivity::addToLog('Lead group status updated by ' . $this->logged_user->name, $data);
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if($errors){
                return response()->json($errors,400);
            }



            /*$id = [];
            foreach (explode(",", $input['id']) as $value) {
                $id[] = Crypt::decrypt($value);
            }
            if (!leadGroup::whereIn('id', $id)->first()) {
                return response()->json(['success' => 'Lead group exists!'], 422);
            }
            $leadGroup = leadGroup::whereIn('id', $id)->update(["status" => $input['status']]);

            $data['id'] = $id;
            $data['status'] = ($input['status'] == 0) ? 'Active' : 'Deactive';
            LogActivity::addToLog('Lead group status updated by ' . $this->logged_user->name, $data);*/

            return response()->json(['success' => 'Lead group status updated!'], 201);
        }
    }
}
