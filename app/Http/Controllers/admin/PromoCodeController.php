<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Helpers\LogActivity;
use Illuminate\Support\Facades\Auth;

class PromoCodeController extends Controller
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
            // Total records
            $totalRecords = PromoCode::select('count(*) as allcount')->where(function ($query) use ($name) {
                if ($name != '') {
                    $query->Where(function ($query) use ($name) {
                        $query->where('code', '=', $name);
                    });
                }
            })->count();
            $totalRecordswithFilter = PromoCode::select('count(*) as allcount')->where('code', 'like', '%' . $search_arr . '%')->where(function ($query) use ($name) {
                if ($name != '') {
                    $query->Where(function ($query) use ($name) {
                        $query->where('code', '=', $name);
                    });
                }
            })->count();


            //            DB::enableQueryLog();
            $records = DB::table('promo_code')
                ->where(function ($query) use ($name) {
                    if ($name != '') {
                        $query->Where(function ($query) use ($name) {
                            $query->where('code', '=', $name);
                        });
                    }
                })
                ->where(function ($query) use ($search_arr) {
                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('code', 'like', '%' . $search_arr . '%');
                    });
                })
                ->orWhere(function ($query) use ($search_arr) {
                    if ($search_arr) {
                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('discount', 'like', '%' . $search_arr . '%');
                        });
                    }
                })
                ->orWhere(function ($query) use ($search_arr) {
                    if ($search_arr) {
                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('type', 'like', '%' . $search_arr . '%');
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
                $code = $record->code;
                $discount = $record->discount;
                $type = $record->type;
                // $status = $record->status;
                $i++;
                $data[] = array(
                    "id" => $i,
                    "code" => $code,
                    "discount" => $discount,
                    "type" => $type,
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

        return view('admin.promo-code');
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $validator = Validator::make($input, [
                'code' => 'required',
                'discount' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);;
            }
            $user = Auth::user();
            $input['user_id'] = $user->id;
            $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
            if (PromoCode::where('code', '=', $input['code'])->where(function ($query) use ($id) {
                if ($id != 0) {
                    $query->Where(function ($query) use ($id) {
                        $query->where('id', '!=', $id);
                    });
                }
            })->first()) {
                return response()->json(['success' => 'Promo Code exists!'], 409);
            }
            if ($id == 0) {
                $activityLogMsg = 'Promo Code created by ' . $user->name;
                $company_category = PromoCode::create($input);
            } else {
                $company_category = PromoCode::find($id)->update($input);
                $activityLogMsg = 'Promo Code updated by ' . $user->name;
            }

            // Add activity logs
            $input['id'] = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
            LogActivity::addToLog($activityLogMsg, $input);

            return response()->json(['success' => 'Promo Code Saved!'], 201);
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

            $company_category = PromoCode::find($id)->toArray();

            if (is_null($company_category)) {
                return response()->json(['success' => 'Promo Code not found!'], 422);
            }
            $company_category['id'] = Crypt::encrypt($company_category['id']);
            return response()->json([
                "success" => true,
                "message" => "Promo Code retrieved successfully.",
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
            $company_category = PromoCode::whereIn('id', $id)->delete();

            LogActivity::addToLog('Promo Code deleted by ' . $user->name, $id);
            return response()->json(['success' => 'Promo Code Deleted!'], 201);
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
            if (!PromoCode::whereIn('id', $id)->first()) {
                return response()->json(['success' => 'Promo Code exists!'], 422);
            }
            $company_category = PromoCode::whereIn('id', $id)->update(["status" => $input['status']]);

            $data['id'] = $id;
            $data['status'] = ($input['status'] == 0) ? 'Active' : 'Deactive';
            LogActivity::addToLog('Promo Code status updated by ' . $user->name, $data);

            return response()->json(['success' => 'Promo Code status updated!'], 201);
        }
    }
}
