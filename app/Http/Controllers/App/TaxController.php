<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TaxController extends Controller
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

            $totalRecords = Tax::select('count(id) as allcount')->where(function ($query) use ($name, $status) {
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
            })->where('company_id',$this->company_id)
            ->count();
            if(!empty($search_arr)) {
            $totalRecordswithFilter = Tax::select('id')->where('name', 'like', '%' . $search_arr . '%')->where(function ($query) use ($name, $status) {
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
            })->where('company_id', $this->company_id)->count();
            } else {
                $totalRecordswithFilter = $totalRecords;
            }


//            DB::enableQueryLog();
            $records = DB::table('taxs')
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
                })
                ->where(function ($query) use ($search_arr) {
                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('name', 'like', $search_arr . '%');
                    });
                })

                ->where('company_id', $this->company_id)
                ->select('id','name','status','company_id','user_id')
                ->skip($start)
                ->take($rowperpage)
                ->orderBy($columnName, $columnSortOrder)
                ->get();

//            dd(DB::getQueryLog());

            $data = array();
            $i = 0;
            foreach ($records as $record) {
                $id = Crypt::encrypt($record->id);
                $name = $record->name;
                $status = $record->status;
                $i++;
                $data[] = array(
                    "id" => $i,
                    "name" => $name,
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
        return view('app.tax', compact('segment'));
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
            $input['company_id'] = $this->company_id;
            $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
            
            if (Tax::where('name', '=', $input['name'])->select('id')->where('company_id', $input['company_id'])->where(function ($query) use ($id) {
                if ($id != 0) {
                    $query->Where(function ($query) use ($id) {
                        $query->where('id', '!=', $id);
                    });
                }
            })->first()) {
                return response()->json(['success' => 'Tax exists!'], 409);
            }
            if ($id == 0) {
                $activityLogMsg = 'Tax created by ' . $this->logged_user->name;
                $tax = Tax::create($input);
            } else {
                $tax = Tax::find($id)->update($input);
                $activityLogMsg = 'Tax updated by ' .$this->logged_user->name;
            }

            // Add activity logs
            $input['id'] = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
//            LogActivity::addToLog($activityLogMsg, $input);

            return response()->json(['success' => 'Tax Saved!'], 201);
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

            $tax = Tax::find($id)->toArray();

            if (is_null($tax)) {
                return response()->json(['success' => 'Tax not found!'], 422);
            }
            $tax['id'] = Crypt::encrypt($tax['id']);
            return response()->json([
                "success" => true,
                "message" => "Tax retrieved successfully.",
                "data" => $tax
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
            $id = [];
            foreach (explode(",", $request->id) as $value) {
                $id[] = Crypt::decrypt($value);
            }
            $tax = Tax::whereIn('id', $id)->delete();

//            LogActivity::addToLog('Tax deleted by ' . $this->logged_user->name, $id);
            return response()->json(['success' => 'Tax Deleted!'], 201);
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
            $id = [];
            foreach (explode(",", $input['id']) as $value) {
                $id[] = Crypt::decrypt($value);
            }
            if (!Tax::whereIn('id', $id)->first()) {
                return response()->json(['success' => 'Tax exists!'], 422);
            }
            $tax = Tax::whereIn('id', $id)->update(["status" => $input['status']]);

            $data['id'] = $id;
            $data['status'] = ($input['status'] == 0) ? 'Active' : 'Deactive';
//            LogActivity::addToLog('Tax status updated by ' . $this->logged_user->name, $data);

            return response()->json(['success' => 'Tax status updated!'], 201);
        }
    }
}
