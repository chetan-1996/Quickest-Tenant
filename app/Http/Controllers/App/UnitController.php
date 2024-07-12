<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules;
use App\Models\User;

class UnitController extends Controller
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
    /**
     * Display a listing of the resource.
     */
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
            $records = DB::table('units')
                ->where('company_id', $this->company_id)
                ->where(function ($query) use ($name, $status) {
                    if ($name != '') {
                        $query->where('name', '=', $name);
                    }
                    if ($status != '') {
                        $query->where('status', '=', $status);
                    }
                });
            $totalRecords = $records->count();
            $records->where(function ($query) use ($search_arr) {
                $query->where('name', 'like', $search_arr . '%');
                $query->orwhere('description', 'like', $search_arr . '%');
            });

            $totalRecordswithFilter = $records->count();
            $recs = $records->skip($start)
                ->select('id','name','description','status','company_id','user_id')
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
                $i++;
                $data[] = array(
                    "id" => $i,
                    "name" => $name,
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
        return view('app.unit');
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
            if (Unit::where('name', '=', $input['name'])->where('company_id', $input['company_id'])->select('id')->where(function ($query) use ($id) {
                if ($id != 0) {
                    $query->Where(function ($query) use ($id) {
                        $query->where('id', '!=', $id);
                    });
                }
            })->first()) {
                return response()->json(['success' => 'Unit exists!'], 409);
            }
            if ($id == 0) {
                $activityLogMsg = 'Unit created by ' . $this->logged_user->name;
                $unit = Unit::create($input);
            } else {
                $unit = Unit::find($id)->update($input);
                $activityLogMsg = 'Unit updated by ' . $this->logged_user->name;
            }

            // Add activity logs
            $input['id'] = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
//            LogActivity::addToLog($activityLogMsg, $input);

            return response()->json(['success' => 'Unit Saved!'], 201);
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

            $unit = Unit::find($id)->toArray();

            if (is_null($unit)) {
                return response()->json(['success' => 'Unit not found!'], 422);
            }
            $unit['id'] = Crypt::encrypt($unit['id']);
            return response()->json([
                "success" => true,
                "message" => "Unit retrieved successfully.",
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
            /*$unitsWithTransactions = DB::table('items')->join("units", 'items.unit_id', '=', 'units.id')
                ->whereIn('items.unit_id', $ids)
                ->pluck('items.unit_id','units.name')
                ->toArray();*/
            $unitsWithTransactions =[];

            foreach ($ids as $unitId) {
                if (in_array($unitId, $unitsWithTransactions)) {
                    $key = array_search($unitId, $unitsWithTransactions);
                    $errors[] = "{$key} has transactions associated with it in the items list";
                    continue;
                }
                try {
                    DB::table('units')
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

//            LogActivity::addToLog('Unit deleted by ' . $this->logged_user->name, $unitIds);
            return response()->json(['success' => 'Unit Deleted!'], 201);
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
            /*$unitsWithTransactions = DB::table('items')->join("units", 'items.unit_id', '=', 'units.id')
                ->whereIn('items.unit_id', $ids)
                ->pluck('items.unit_id','units.name')
                ->toArray();*/
            $unitsWithTransactions = [];

            foreach ($ids as $unitId) {
                if (in_array($unitId, $unitsWithTransactions)) {
                    $key = array_search($unitId, $unitsWithTransactions);
                    $errors[] = "{$key} has transactions associated with it in the items list";
                    continue;
                }
                try {
                    Unit::where('id', $unitId)->update(["status" => $input['status']]);
                    $data['id'] = $unitId;
                    $data['status'] = ($input['status'] == 0) ? 'Active' : 'Deactive';
//                    LogActivity::addToLog('Unit status updated by ' . $this->logged_user->name, $data);
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if($errors){
                return response()->json($errors,400);
            }
            /*
            $id = [];
            foreach (explode(",", $input['id']) as $value) {
                $id[] = Crypt::decrypt($value);
            }
            if (!Unit::whereIn('id', $id)->first()) {
                return response()->json(['success' => 'Unit exists!'], 422);
            }
            $unit = Unit::whereIn('id', $id)->update(["status" => $input['status']]);

            $data['id'] = $id;
            $data['status'] = ($input['status'] == 0) ? 'Active' : 'Deactive';
            LogActivity::addToLog('Unit status updated by ' . $this->logged_user->name, $data);*/

            return response()->json(['success' => 'Unit status updated!'], 201);
        }
    }
}
