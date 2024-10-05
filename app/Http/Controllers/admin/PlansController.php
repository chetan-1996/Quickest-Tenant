<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\admin\Plans;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Helpers\LogActivity;
use Illuminate\Support\Facades\Auth;

class PlansController extends Controller
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
            $name = $request->get('name');
            $price = $request->get('price');
            $yearly_price = $request->get('yearly_price');
            $user_limit = $request->get('user_limit');
            $estimate_limit = $request->get('estimate_limit');
            // Total records
            $totalRecords = Plans::select('count(*) as allcount')->where(function ($query) use ($name, $price, $yearly_price, $user_limit, $estimate_limit) {
                if ($name != '') {
                    $query->Where(function ($query) use ($name) {
                        $query->where('name', '=', $name);
                    });
                }
                if ($price != '') {
                    $query->where(function ($query) use ($price) {
                        $query->where('price', '=', $price);
                    });
                }
                if ($yearly_price != '') {
                    $query->where(function ($query) use ($yearly_price) {
                        $query->where('yearly_price', '=', $yearly_price);
                    });
                }
                if ($user_limit != '') {
                    $query->where(function ($query) use ($user_limit) {
                        $query->where('users_limit', '=', $user_limit);
                    });
                }
                if ($estimate_limit != '') {
                    $query->where(function ($query) use ($estimate_limit) {
                        $query->where('estimate_limit', '=', $estimate_limit);
                    });
                }
            })->count();
            $totalRecordswithFilter = Plans::select('count(*) as allcount')->where('name', 'like', '%' . $search_arr . '%')->where(function ($query) use ($name, $price, $yearly_price, $user_limit, $estimate_limit) {
                if ($name != '') {
                    $query->Where(function ($query) use ($name) {
                        $query->where('name', '=', $name);
                    });
                }
                if ($price != '') {
                    $query->where(function ($query) use ($price) {
                        $query->where('price', '=', $price);
                    });
                }
                if ($yearly_price != '') {
                    $query->where(function ($query) use ($yearly_price) {
                        $query->where('yearly_price', '=', $yearly_price);
                    });
                }
                if ($user_limit != '') {
                    $query->where(function ($query) use ($user_limit) {
                        $query->where('users_limit', '=', $user_limit);
                    });
                }
                if ($estimate_limit != '') {
                    $query->where(function ($query) use ($estimate_limit) {
                        $query->where('estimate_limit', '=', $estimate_limit);
                    });
                }
            })->count();


            //            DB::enableQueryLog();
            $records = DB::table('plans')
                ->where(function ($query) use ($name, $price, $yearly_price, $user_limit, $estimate_limit) {
                    if ($name != '') {
                        $query->Where(function ($query) use ($name) {
                            $query->where('name', '=', $name);
                        });
                    }
                    if ($price != '') {
                        $query->where(function ($query) use ($price) {
                            $query->where('price', '=', $price);
                        });
                    }
                    if ($yearly_price != '') {
                        $query->where(function ($query) use ($yearly_price) {
                            $query->where('yearly_price', '=', $yearly_price);
                        });
                    }
                    if ($user_limit != '') {
                        $query->where(function ($query) use ($user_limit) {
                            $query->where('users_limit', '=', $user_limit);
                        });
                    }
                    if ($estimate_limit != '') {
                        $query->where(function ($query) use ($estimate_limit) {
                            $query->where('estimate_limit', '=', $estimate_limit);
                        });
                    }
                })
                ->where(function ($query) use ($search_arr) {
                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('name', 'like', '%' . $search_arr . '%');
                    });
                })
                ->orWhere(function ($query) use ($search_arr) {
                    if ($search_arr) {
                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('description', 'like', '%' . $search_arr . '%');
                        });
                    }
                })
                ->select('*')
                ->skip($start)
                ->take($rowperpage)
                ->orderBy($columnName, $columnSortOrder)
                ->get();

            //dd(DB::getQueryLog());
            $data = array();
            $i = 0;
            foreach ($records as $record) {
                $id = Crypt::encrypt($record->id);
                $name = $record->name;
                $description = $record->description;
                $price = $record->price;
                $yearly_price = $record->yearly_price;
                $user_limit = $record->users_limit;
                $estimate_limit = $record->estimate_limit;
                // $status = $record->status;
                $i++;
                $data[] = array(
                    "id" => $i,
                    "name" => $name,
                    "description" => $description,
                    "price" => $price,
                    "yearly_price" => $yearly_price,
                    "user_limit" => $user_limit,
                    "estimate_limit" => $estimate_limit,
                    // "status" => $status,
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

        return view('admin.plans');
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $validator = Validator::make($input, [
                'name' => 'required',
                'price' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);;
            }

            $user = Auth::user();
            $input['user_id'] = $user->id;
            if (!isset($input['isDefault'])) {
                $input['isDefault'] = 0;
            } else {
                $input['isDefault'] = 1;
            }
            $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
            if (Plans::where('name', '=', $input['name'])->where(function ($query) use ($id) {
                if ($id != 0) {
                    $query->Where(function ($query) use ($id) {
                        $query->where('id', '!=', $id);
                    });
                }
            })->first()) {
                return response()->json(['success' => 'Plan exists!'], 409);
            }
            if ($id == 0) {
                $activityLogMsg = 'Plan created by ' . $user->name;
                $company_category = Plans::create($input);
            } else {
                $company_category = Plans::find($id)->update($input);
                $activityLogMsg = 'Plan updated by ' . $user->name;
            }

            // Add activity logs
            $input['id'] = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
            LogActivity::addToLog($activityLogMsg, $input);

            return response()->json(['success' => 'Plan Saved!'], 201);
        }
    }

    public function show(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $id = strlen($input['id']) > 2 ? Crypt::decrypt($input['id']) : $input['id'];
            $validator = Validator::make($input, [
                'id' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);
            }
            $user = Auth::user();

            $company_category = Plans::find($id)->toArray();

            if (is_null($company_category)) {
                return response()->json(['success' => 'Plan not found!'], 422);
            }
            $company_category['id'] = Crypt::encrypt($company_category['id']);
            return response()->json([
                "success" => true,
                "message" => "Plan retrieved successfully.",
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
            $company_category = Plans::whereIn('id', $id)->delete();

            LogActivity::addToLog('Plan deleted by ' . $user->name, $id);
            return response()->json(['success' => 'Plan Deleted!'], 201);
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
            if (!Plans::whereIn('id', $id)->first()) {
                return response()->json(['success' => 'Plan exists!'], 422);
            }
            $company_category = Plans::whereIn('id', $id)->update(["status" => $input['status']]);

            $data['id'] = $id;
            $data['status'] = ($input['status'] == 0) ? 'Active' : 'Deactive';
            LogActivity::addToLog('Plan status updated by ' . $user->name, $data);

            return response()->json(['success' => 'Plan status updated!'], 201);
        }
    }
}
