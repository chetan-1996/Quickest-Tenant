<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\CustomerCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomerCategoryController extends Controller
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
            $totalRecords = CustomerCategory::select('count(id) as allcount')->where('company_id',$this->company_id)->where(function ($query) use ($name, $status) {
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
            })->count();
            $totalRecordswithFilter = CustomerCategory::select('count(id) as allcount')->where('company_id',$this->company_id)->where(function ($query) use ($name, $status) {
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
            })->where(function ($query) use ($search_arr) {
                $query->where(function ($query) use ($search_arr) {
                    $query->where('name', 'like', '%' . $search_arr . '%');
                    $query->orwhere('description', 'like', '%' . $search_arr . '%');
                });
            })->count();


//            DB::enableQueryLog();
            $records = DB::table('customer_categories')
                ->where('company_id',$this->company_id)
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
                    $query->where(function ($query) use ($search_arr) {
                        $query->where('name', 'like', '%' . $search_arr . '%');
                        $query->orwhere('description', 'like', '%' . $search_arr . '%');
                    });
                })
                ->select('*')
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

        return view('app.customer-category');
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
            $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
            $input['company_id'] = $this->company_id;
            if (CustomerCategory::where([['name', '=', $input['name']], ['company_id', $input['company_id']]])->where(function ($query) use ($id) {
                if ($id != 0) {
                    $query->Where(function ($query) use ($id) {
                        $query->where('id', '!=', $id);
                    });
                }
            })->first()) {
                return response()->json(['success' => 'Customer category exists!'], 409);
            }
            if ($id == 0) {
                $activityLogMsg = 'Customer category created by ' . $this->logged_user->name;
                $customerCategory = CustomerCategory::create($input);
            } else {
                $customerCategory = CustomerCategory::find($id)->update($input);
                $activityLogMsg = 'Customer category updated by ' . $this->logged_user->name;
            }

            // Add activity logs
            $input['id'] = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
//            LogActivity::addToLog($activityLogMsg, $input);

            return response()->json(['success' => 'Customer category Saved!'], 201);
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

            $customerCategory = CustomerCategory::find($id)->toArray();

            if (is_null($customerCategory)) {
                return response()->json(['success' => 'Customer category not found!'], 422);
            }
            $customerCategory['id'] = Crypt::encrypt($customerCategory['id']);
            return response()->json([
                "success" => true,
                "message" => "Customer category retrieved successfully.",
                "data" => $customerCategory
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
//            $leadGroupsWithTransactions = DB::table('customers')->join("customer_categories", 'customers.customer_category_id', '=', 'customer_categories.id')
//                ->whereIn('customers.customer_category_id', $ids)
//                ->pluck('customers.customer_category_id','customer_categories.name')
//                ->toArray();
            $leadGroupsWithTransactions =[];

            foreach ($ids as $leadGroupId) {
                if (in_array($leadGroupId, $leadGroupsWithTransactions)) {
                    $key = array_search($leadGroupId, $leadGroupsWithTransactions);
                    $errors[] = "{$key} has transactions associated with it in the leads list";
                    continue;
                }
                try {
                    DB::table('customer_categories')
                        ->where('id', $leadGroupId)
                        ->delete();
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if($errors){
                return response()->json($errors,400);
            }

            /* $id = [];
             foreach (explode(",", $request->id) as $value) {
                 $id[] = Crypt::decrypt($value);
             }
             $customerCategory = CustomerCategory::whereIn('id', $id)->delete();

             LogActivity::addToLog('Customer category deleted by ' . $this->logged_user->name, $id);*/
            return response()->json(['success' => 'Customer Category Deleted!'], 201);
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
            /*$leadGroupsWithTransactions = DB::table('customers')->join("customer_categories", 'customers.customer_category_id', '=', 'customer_categories.id')
                ->whereIn('customers.customer_category_id', $ids)
                ->pluck('customers.customer_category_id','customer_categories.name')
                ->toArray();*/
            $leadGroupsWithTransactions =[];

            foreach ($ids as $leadGroupId) {
                if (in_array($leadGroupId, $leadGroupsWithTransactions)) {
                    $key = array_search($leadGroupId, $leadGroupsWithTransactions);
                    $errors[] = "{$key} has transactions associated with it in the leads list";
                    continue;
                }
                try {
                    CustomerCategory::where('id', $leadGroupId)->update(["status" => $input['status']]);
                    $data['id'] = $leadGroupId;
                    $data['status'] = ($input['status'] == 0) ? 'Active' : 'Deactive';
//                    LogActivity::addToLog('Lead category status updated by ' . $this->logged_user->name, $data);
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
            if (!CustomerCategory::whereIn('id', $id)->first()) {
                return response()->json(['success' => 'Customer category exists!'], 422);
            }
            $customerCategory = CustomerCategory::whereIn('id', $id)->update(["status" => $input['status']]);

            $data['id'] = $id;
            $data['status'] = ($input['status'] == 0) ? 'Active' : 'Deactive';
            LogActivity::addToLog('Customer category status updated by ' . $this->logged_user->name, $data);*/

            return response()->json(['success' => 'Customer category status updated!'], 201);
        }
    }
}
