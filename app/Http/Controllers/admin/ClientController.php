<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\{Customer, EstimateAutoNumber, PlanHistory, ProposalTemplates, Unit, User};
use App\Models\Tenant;
use App\Models\admin\Plans;
use App\Models\admin\ViewUserData;
use App\Models\admin\LeadHistory;
use App\Models\admin\EstimateHistory;
use App\Models\admin\AttachmentHistory;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{Crypt, DB, Storage, Validator};
use Image;
use App\Helpers\LogActivity;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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
            $status = $request->get('status');
            // Total records
            //$totalRecords = ViewUserData::select('count(*) as allcount')->where(function ($query) use ($name, $status) {
            $totalRecords = Tenant::select('count(*) as allcount')->where(function ($query) use ($name, $status) {
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
            })->whereNull('company_id')->count();
            if($search_arr != '') {
                // $countswithFilter = ViewUserData::select('id')->where('name', 'like', '%' . $search_arr . '%')->where(function ($query) use ($name, $status) {
                $countswithFilter = Tenant::select('id')->where('name', 'like', '%' . $search_arr . '%')->where(function ($query) use ($name, $status) {
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
                })->whereNull('company_id')->get();
                $totalRecordswithFilter = $countswithFilter->count();
            } else {
                $countswithFilter = 0;
                $totalRecordswithFilter = 0;
            }

            $rowperpage = ($rowperpage == -1) ? $totalRecords : $rowperpage;

            /*$records = DB::table('users_views')
                ->leftJoin('estimates', function ($join) use ($input) {
                    $join->on('users_views.id', '=', 'estimates.company_id')
                        ->whereBetween(DB::raw("DATE(estimates.estimate_date)"), [$input['fil_estimate_start'], $input['fil_estimate_end']]);
                })
                ->where(function ($query) use ($name, $status) {
                    if ($name != '') {
                        $query->Where(function ($query) use ($name) {
                            $query->where('users_views.name', '=', $name);
                        });
                    }
                    if ($status != '') {
                        $query->where(function ($query) use ($status) {
                            $query->where('users_views.status', '=', $status);
                        });
                    }
                })
                ->where(function ($query) use ($search_arr) {
                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('users_views.name', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('users_views.company_name', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('mobile_no', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('users_views.email', 'like', '%' . $search_arr . '%');
                    });
                })
                ->whereNull('users_views.company_id')
                ->selectRaw('users_views.*, count(estimates.company_id) as estimate_count')
                ->skip($start)
                ->take($rowperpage)
                ->groupBy('users_views.id')
                ->orderBy($columnName, $columnSortOrder)
                ->get();*/

            $records =  DB::table('tenants')
                ->select(
                    'tenants.*',
                    DB::raw('COUNT(DISTINCT estimate_histroy.id) AS estimate_count'),
                    DB::raw('COALESCE(file_sizes.total_file_size, 0) AS total_file_size'),
                    DB::raw('countries.name AS country_name'),
                    DB::raw('states.name AS state_name'),
                    DB::raw('cities.name AS city_name'),
                    DB::raw('company_categories.name AS business_category_name'),
                )
                ->leftJoin('estimate_histroy', function($join) use ($input) {
                    $join->on('tenants.id', '=', 'estimate_histroy.company_id')
                        ->whereBetween(DB::raw('DATE(estimate_histroy.insert_date)'), [$input['fil_estimate_start'], $input['fil_estimate_end']]);
                })
                ->leftJoin('attachment_histroy', 'tenants.id', '=', 'attachment_histroy.company_id')
                ->leftJoin('countries', 'tenants.country_id', '=', 'countries.id')
                ->leftJoin('states', 'tenants.state_id', '=', 'states.id')
                ->leftJoin('cities', 'tenants.city_id', '=', 'cities.id')
                ->leftJoin('company_categories', 'tenants.company_category', '=', 'company_categories.id')
                ->leftJoin(DB::raw('(SELECT company_id, SUM(storage_size) AS total_file_size FROM attachment_histroy GROUP BY company_id) AS file_sizes'), 'attachment_histroy.company_id', '=', 'tenants.id')
                ->where(function ($query) use ($name, $status) {
                    if ($name != '') {
                        $query->Where(function ($query) use ($name) {
                            $query->where('tenants.name', '=', $name);
                        });
                    }
                    if ($status != '') {
                        $query->where(function ($query) use ($status) {
                            $query->where('tenants.status', '=', $status);
                        });
                    }
                })
                ->where(function ($query) use ($search_arr) {
                    if($search_arr != '') {
                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('tenants.name', 'like', '%' . $search_arr . '%');
                        });

                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('tenants.company_name', 'like', '%' . $search_arr . '%');
                        });

                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('mobile_no', 'like', '%' . $search_arr . '%');
                        });

                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('tenants.email', 'like', '%' . $search_arr . '%');
                        });
                    }
                })
                ->whereNull('tenants.company_id')
                ->groupBy('tenants.id')
                ->orderBy($columnName, $columnSortOrder)
                ->skip($start)
                ->take($rowperpage)
                ->get();


                //echo '<pre>';print_r($records);exit;



            $data = array();
            $i = 0;
            foreach ($records as $record) {
                $id = Crypt::encrypt($record->id);
                $name = $record->name;
                $company_name = $record->company_name;
                $email = $record->email;
                $mobile_no = $record->mobile_no;
                $address = $record->address;
                $pincode = $record->pincode;
                $country_name = $record->country_name;
                $state_name = $record->state_name;
                $city_name = $record->city_name;
                $business_category_name = $record->business_category_name;
                $website_link = $record->website_link;
                $gst_no = $record->gst_no;
                $status = $record->status;

                $i++;
                $user_id = isset($record->company_id) ? $record->company_id : $record->id;
                $lead_count = LeadHistory::where('company_id', $user_id)
                    ->where(function ($query) use ($input) {
                        $query->whereBetween(DB::raw("DATE(insert_date)"), [$input['fil_estimate_start'], $input['fil_estimate_end']]);
                    })
                    ->count();
                // $userCount = User::where('company_id', $user_id)->count();
                $plan = Plans::join('plan_history', 'plans.id', 'plan_history.plan_id')->where([['plan_history.user_id', $user_id], ['plan_history.status', 1]])->select(['plans.name'])->first();
                $remaining_days = Carbon::parse($record->plan_start_date)->diffInDays();
                $totalExp = Carbon::parse($record->plan_start_date)->diffInDays(Carbon::parse($record->plan_end_date));
                if ($remaining_days == 7) {
                    $user = User::where('id', $user_id)->first();
                    if ($user->plan_status == 0) {
                        $planStatus = 1;
                        User::where('id', $user_id)->update(['plan_status' => 1]);
                    } elseif ($user->plan_status == 2) {
                        $planStatus = 2;
                        User::where('id', $user_id)->update(['plan_status' => 3]);
                    } else {
                        $planStatus = $record->plan_status;
                    }
                } else {
                    $planStatus = $record->plan_status;
                }
                $profile_icon = ($record->profile_icon) ? Storage::disk('s3')->temporaryUrl(trim($record->profile_icon), \Carbon\Carbon::now()->addMinutes(20)): url('assets/images/users/avatar-1.jpg'); //Storage::url($record->profile_icon)
                $active_user = Tenant::where([['company_id', $user_id], ['invite_status', 1]])->count();
                $userCount = PlanHistory::where([['user_id', $user_id], ['status', 1]])->first();
                // $planStatus = $record->plan_status;
                $data[] = array(
                    "id" => $record->id,
                    "created_at" => Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)
                        ->format('d-m-Y H:i:s'),
                    "name" => '<img src="' . $profile_icon . '" alt="' . $name . '" title="' . $name . '" class="rounded-circle zoom me-2" height="48"> <p class="m-0 d-inline-block align-middle font-16">' . $name . '</p>',
                    "company_name" => $company_name,
                    "mobile_no" => $mobile_no,
                    "email" => $email,
                    "address" => $address,
                    "pincode" => $pincode,
                    "country_name" => $country_name,
                    "state_name" => $state_name,
                    "city_name" => $city_name,
                    "business_category_name" => $business_category_name,
                    "website_link" => $website_link,
                    "gst_no" => $gst_no,
                    "status" => $status,
                    "estimate_count" => $record->estimate_count,
                    "user_count" => isset($userCount->user_limit) ? $userCount->user_limit : 'Unlimited',
                    "active_user" => $active_user + 1,
                    "lead_count" => $lead_count,
                    "active_plan" => isset($plan->name) ? $plan->name : "",
                    "action" => $id,
                    "plan_end_date" => Carbon::createFromFormat('Y-m-d H:i:s', $record->plan_end_date)->format('d-m-Y H:i:s'),
                    "remaining_days" => $remaining_days . '/' . $totalExp,
                    "total_file_size" => $this->bytesToGB($record->total_file_size).'/'.$record->storage_capacity,
                    "plan_status" => $planStatus
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
        $plans = Plans::all();
        return view('admin.client.client')->with(['plans' => $plans]);
    }

    public function bytesToGB($bytes) {
        $gigabytes = $bytes / pow(1024, 3);
        return 0+number_format($gigabytes,2);
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

            $unit = tenant::find($id)->toArray();

            if (is_null($unit)) {
                return response()->json(['success' => 'User not found!'], 422);
            }
            $unit['id'] = Crypt::encrypt($unit['id']);
            $unit['planHistory'] = PlanHistory::where([['user_id', $id], ['status', 1]])->first();
            return response()->json([
                "success" => true,
                "message" => "User retrieved successfully.",
                "data" => $unit
            ], 201);
        }
    }

    public function storageShow(Request $request)
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

            $unit = Tenant::find($id)->toArray();
            $unit['id'] = Crypt::encrypt($unit['id']);

            if (is_null($unit)) {
                return response()->json(['success' => 'User not found!'], 422);
            }
            return response()->json([
                "success" => true,
                "message" => "User retrieved successfully.",
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
            $user = Auth::user();
            $id = [];
            foreach (explode(",", $request->id) as $value) {
                $id[] = Crypt::decrypt($value);
            }
            Tenant::whereIn('company_id', $id)->delete();
            $company_category = Tenant::whereIn('id', $id)->delete();

            LogActivity::addToLog('Client deleted by ' . $user->name, $id);
            return response()->json(['success' => 'Client Deleted!'], 201);
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
                return response()->json(['errors' => $validator->errors()->all()], 400);
            }
            $user = Auth::user();
            $id = [];
            foreach (explode(",", $input['id']) as $value) {
                $tmpId = Crypt::decrypt($value);
                $id[] = Crypt::decrypt($value);
                if ($input['status'] == 'Approved') {

                    if (!EstimateAutoNumber::where('company_id', $id)->select('id')->first()) {
                        $data_ins['estimate_prefix'] = 'EST-';
                        $data_ins['estimate_next_no'] = '001';
                        $data_ins['company_id'] = Crypt::decrypt($value);

                        $unit = EstimateAutoNumber::create($data_ins);
                    }

                    $unitArr = [
                        ['name' => 'Site', 'user_id' => $tmpId, 'company_id' => $tmpId],
                        ['name' => 'Kw', 'user_id' => $tmpId, 'company_id' => $tmpId],
                        ['name' => 'Nos', 'user_id' => $tmpId, 'company_id' => $tmpId]
                    ];
                    DB::table('units')->insert($unitArr);

                    $taxArr = [
                        ['name' => 0.00, 'user_id' => $tmpId, 'company_id' => $tmpId],
                        ['name' => 5.00, 'user_id' => $tmpId, 'company_id' => $tmpId],
                        ['name' => 12.00, 'user_id' => $tmpId, 'company_id' => $tmpId],
                        ['name' => 18.00, 'user_id' => $tmpId, 'company_id' => $tmpId],
                        ['name' => 28.00, 'user_id' => $tmpId, 'company_id' => $tmpId]
                    ];
                    DB::table('taxs')->insert($taxArr);

                    $catArr = [
                        ['name' => 'B2B', 'user_id' => $tmpId, 'company_id' => $tmpId],
                        ['name' => 'B2C', 'user_id' => $tmpId, 'company_id' => $tmpId]
                    ];
                    DB::table('customer_categories')->insert($catArr);

                    $leadArr = [
                        ['name' => 'Social Media', 'user_id' => $tmpId, 'company_id' => $tmpId],
                        ['name' => 'Reference', 'user_id' => $tmpId, 'company_id' => $tmpId],
                        ['name' => 'Physical Marketing', 'user_id' => $tmpId, 'company_id' => $tmpId]
                    ];
                    DB::table('customer_leads')->insert($leadArr);

                    $path_one = env('APP_URL') . "sample/testimonial/t-1.png";
                    $filename_one = date('YmdHis') . "106" . ".png";
                    Image::make($path_one)->save(storage_path("app/public/uploads/thumbnail/" . $filename_one));

                    $path_two = env('APP_URL') . "sample/testimonial/t-2.png";
                    $filename_two = date('YmdHis') . "107" . ".png";
                    Image::make($path_two)->save(storage_path("app/public/uploads/thumbnail/" . $filename_two));

                    $path_three = env('APP_URL') . "sample/testimonial/t-3.png";
                    $filename_three = date('YmdHis') . "108" . ".png";
                    Image::make($path_three)->save(storage_path("app/public/uploads/thumbnail/" . $filename_three));

                    $testimonialArr = [
                        [
                            'name' => 'Residential Testimonial',
                            'client_name_one' => 'Rahulbhai patel',
                            'client_name_two' => 'Payalben hirpara',
                            'client_name_three' => 'Kiranbhai prajapati',
                            'description_one' => 'I recently installed solar on my rooftop. Thank You team for neat and clean Installation on my rooftop. Also, received my subsidy. Great Work team. Thanks',
                            'description_two' => 'When you have empty roof then why to pay for electricity bill? Thank You for end to end guidance. Your staff is very professional and friendly. Thanks for making my roof solarize! Superb Work by Team.',
                            'description_three' => 'One of the best decision of my life to go solar! I really appreciate your product quality and workmanship. In last 6 month, my plant has generated more than 2000 Units and counting. I strongly recommend everyone to go solar as soon as possible. Thank You',
                            'rating_one' => 5,
                            'rating_two' => 5,
                            'rating_three' => 5,
                            'image_one' => 'public/uploads/thumbnail/' . $filename_one,
                            'image_two' => 'public/uploads/thumbnail/' . $filename_two,
                            'image_three' => 'public/uploads/thumbnail/' . $filename_three,
                            'status' => 0,
                            'is_default' => 1,
                            'user_id' => $tmpId,
                            'company_id' => $tmpId,
                        ],

                    ];
                    DB::table('testimonials')->insert($testimonialArr);
                    $units = Unit::where([["company_id", "=", $tmpId], ["name", "=", "Site"]])->orderBy('id', 'ASC')->select("id")->first();


                    $userDatas = DB::table('users')->where('id', '=', $tmpId)->select(['company_category', 'name', 'email'])->first();
                    if ($userDatas->company_category == 1) {
                        $itemArr = [
                            ['name' => '3.015 KW on grid rooftop solar (URBAN 335W -09P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (10 Qty)
- Inverter 3.3 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 59,243 INR)'), 'item_type' => 'Goods', 'inter_state' => 0, 'intra_state' => 0, 'unit_id' => $units->id, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sale_price' => 87988, 'sales_flag' => 1, 'status' => 0, 'purchase_flag' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '3.35 KW on grid rooftop solar (URBAN 335W -10P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (10 Qty)
- Inverter 3.3 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 59,243 INR)'), 'item_type' => 'Goods', 'inter_state' => 0, 'intra_state' => 0, 'unit_id' => $units->id, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sale_price' => 100874, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '3.685 KW on grid rooftop solar (URBAN 335W -11P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (11 Qty)
- Inverter 3.6 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 61,575 INR)'), 'item_type' => 'Goods', 'inter_state' => 0, 'intra_state' => 0, 'unit_id' => $units->id, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 114553, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '3.015 KW on grid rooftop solar (RURAL 335W 09P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (09 Qty)
- Inverter 3.3 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 56,117 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 91419, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '3.35 KW on grid rooftop solar (RURAL 335W -10P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (10 Qty)
- Inverter 3.3 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 59,243 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 104686, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '3.685 KW on grid rooftop solar (RURAL 335W -11P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (11 Qty)
- Inverter 3.6 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 61,575 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 118747, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '4.02 KW on grid rooftop solar (335W -12P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (12 Qty)
- Inverter 4.2 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 65,493 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 126646, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '4.355 KW on grid rooftop solar (335W -13P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (13 Qty)
- Inverter 4.2 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 67,173 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 140979, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '4.69 KW on grid rooftop solar (335W -14P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (14 Qty)
- Inverter 5 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- TransportationG
- Installation
- 5 years maintenance
  (Subsidy amount – 71,744 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 152419, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '5.025 KW on grid rooftop solar (335W -15P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (15 Qty)
- Inverter 5 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 74,636 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 165538, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '5.36 KW on grid rooftop solar (335W -16P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (16 Qty)
- Inverter 6 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 77,995 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 172092, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '5.695 KW on grid rooftop solar (335W -17P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (17 Qty)
- Inverter 6 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 81,120 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 184597, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '6.03 KW on grid rooftop solar (335W -18P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (18 Qty)
- Inverter 6 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 83,966 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 197382, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '7.035 KW on grid rooftop solar (335W -21P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (21 Qty)
- Inverter 8 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 92,501 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 231735, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '8.04 KW on grid rooftop solar (335W -24P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (24 Qty)
- Inverter 8 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 101,396 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 269160, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '9.045 KW on grid rooftop solar (335W -27P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (27 Qty)
- Inverter 10 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 111,028 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 305847, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '9.715 KW on grid rooftop solar (335W -29P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (29 Qty)
- Inverter 10 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 117,204 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 330550, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],


                            ['name' => 'Industrial Rooftop On-grid Solar Power Plant', 'description' => trim('- 540-Watt Solar panel– 25 years warranty(Adani / Waree / Goldi)
- On Grid Solar Inverter (Sofar / Growatt / EVVO)
- Hot Dip GI Structure as per design
- ACDB with NVR & SPD
- DCDB with Fuse & MCB
- DC / AC copper cable as per requirement (Qty - As required)
- Earthing rod Copper coated 1.5 meter (Qty - As required)
- Earthing wire 25 SQ Aluminum
- Lightning arrestor (Qty – 1)
- PVC conduit as per site (Qty - As required)
- SS cable tie / MC4 & other Accessories
- Conduit & Cable Tray (As required)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 0, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],

                            ['name' => 'Installation & Commissioning', 'description' => trim('- Roof top solar power plant Installation as per design
- 5 years O&M
- Transportation & unloading /loading of material'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 0, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],

                        ];
                        DB::table('items')->insert($itemArr);

                        // 8-PANEL
                        $panel8T_1_path_one = env('APP_URL') . "sample/product/8-PANEL/T-1/8-panel-1.jpg";
                        $panel8T_1_one = date('YmdHis') . "1" . ".jpg";
                        Image::make($panel8T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_1_one));

                        $panel8T_1_path_two = env('APP_URL') . "sample/product/8-PANEL/T-1/8-panel-2.jpg";
                        $panel8T_1_two = date('YmdHis') . "2" . ".jpg";
                        Image::make($panel8T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_1_two));

                        $panel8T_1_path_three = env('APP_URL') . "sample/product/8-PANEL/T-1/8-panel-3.jpg";
                        $panel8T_1_three = date('YmdHis') . "3" . ".jpg";
                        Image::make($panel8T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_1_three));

                        $panel8T_2_path_one = env('APP_URL') . "sample/product/8-PANEL/T-2/10002.jpg";
                        $panel8T_2_one = date('YmdHis') . "4" . ".jpg";
                        Image::make($panel8T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_2_one));

                        $panel8T_2_path_two = env('APP_URL') . "sample/product/8-PANEL/T-2/20001.jpg";
                        $panel8T_2_two = date('YmdHis') . "5" . ".jpg";
                        Image::make($panel8T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_2_two));

                        $panel8T_2_path_three = env('APP_URL') . "sample/product/8-PANEL/T-2/20003.jpg";
                        $panel8T_2_three = date('YmdHis') . "6" . ".jpg";
                        Image::make($panel8T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_2_three));

                        $panel8T_3_path_one = env('APP_URL') . "sample/product/8-PANEL/T-3/10002.jpg";
                        $panel8T_3_one = date('YmdHis') . "7" . ".jpg";
                        Image::make($panel8T_3_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_3_one));

                        $panel8T_3_path_two = env('APP_URL') . "sample/product/8-PANEL/T-3/20001.jpg";
                        $panel8T_3_two = date('YmdHis') . "8" . ".jpg";
                        Image::make($panel8T_3_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_3_two));

                        $panel8T_3_path_three = env('APP_URL') . "sample/product/8-PANEL/T-3/20003.jpg";
                        $panel8T_3_three = date('YmdHis') . "9" . ".jpg";
                        Image::make($panel8T_3_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_3_three));

                        // 9-PANEL
                        $panel9T_1_path_one = env('APP_URL') . "sample/product/9-PANEL/T-1/10002.jpg";
                        $panel9T_1_one = date('YmdHis') . "10" . ".jpg";
                        Image::make($panel9T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_1_one));

                        $panel9T_1_path_two = env('APP_URL') . "sample/product/9-PANEL/T-1/20001.jpg";
                        $panel9T_1_two = date('YmdHis') . "11" . ".jpg";
                        Image::make($panel9T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_1_two));

                        $panel9T_1_path_three = env('APP_URL') . "sample/product/9-PANEL/T-1/20003.jpg";
                        $panel9T_1_three = date('YmdHis') . "12" . ".jpg";
                        Image::make($panel9T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_1_three));

                        $panel9T_2_path_one = env('APP_URL') . "sample/product/9-PANEL/T-2/10002.jpg";
                        $panel9T_2_one = date('YmdHis') . "13" . ".jpg";
                        Image::make($panel9T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_2_one));

                        $panel9T_2_path_two = env('APP_URL') . "sample/product/9-PANEL/T-2/20001.jpg";
                        $panel9T_2_two = date('YmdHis') . "14" . ".jpg";
                        Image::make($panel9T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_2_two));

                        $panel9T_2_path_three = env('APP_URL') . "sample/product/9-PANEL/T-2/20003.jpg";
                        $panel9T_2_three = date('YmdHis') . "15" . ".jpg";
                        Image::make($panel9T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_2_three));

                        $panel9T_3_path_one = env('APP_URL') . "sample/product/9-PANEL/T-3/10002.jpg";
                        $panel9T_3_one = date('YmdHis') . "16" . ".jpg";
                        Image::make($panel9T_3_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_3_one));

                        $panel9T_3_path_two = env('APP_URL') . "sample/product/9-PANEL/T-3/20001.jpg";
                        $panel9T_3_two = date('YmdHis') . "17" . ".jpg";
                        Image::make($panel9T_3_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_3_two));

                        $panel9T_3_path_three = env('APP_URL') . "sample/product/9-PANEL/T-3/20003.jpg";
                        $panel9T_3_three = date('YmdHis') . "18" . ".jpg";
                        Image::make($panel9T_3_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_3_three));

                        // 10-PANEL
                        $panel10T_1_path_one = env('APP_URL') . "sample/product/10-PANEL/T-1/10002.jpg";
                        $panel10T_1_one = date('YmdHis') . "19" . ".jpg";
                        Image::make($panel10T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_1_one));

                        $panel10T_1_path_two = env('APP_URL') . "sample/product/10-PANEL/T-1/20001.jpg";
                        $panel10T_1_two = date('YmdHis') . "20" . ".jpg";
                        Image::make($panel10T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_1_two));

                        $panel10T_1_path_three = env('APP_URL') . "sample/product/10-PANEL/T-1/20003.jpg";
                        $panel10T_1_three = date('YmdHis') . "21" . ".jpg";
                        Image::make($panel10T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_1_three));

                        $panel10T_2_path_one = env('APP_URL') . "sample/product/10-PANEL/T-2/10002.jpg";
                        $panel10T_2_one = date('YmdHis') . "22" . ".jpg";
                        Image::make($panel10T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_2_one));

                        $panel10T_2_path_two = env('APP_URL') . "sample/product/10-PANEL/T-2/20001.jpg";
                        $panel10T_2_two = date('YmdHis') . "23" . ".jpg";
                        Image::make($panel10T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_2_two));

                        $panel10T_2_path_three = env('APP_URL') . "sample/product/10-PANEL/T-2/20003.jpg";
                        $panel10T_2_three = date('YmdHis') . "24" . ".jpg";
                        Image::make($panel10T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_2_three));

                        $panel10T_3_path_one = env('APP_URL') . "sample/product/10-PANEL/T-3/10002.jpg";
                        $panel10T_3_one = date('YmdHis') . "25" . ".jpg";
                        Image::make($panel10T_3_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_3_one));

                        $panel10T_3_path_two = env('APP_URL') . "sample/product/10-PANEL/T-3/20001.jpg";
                        $panel10T_3_two = date('YmdHis') . "26" . ".jpg";
                        Image::make($panel10T_3_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_3_two));

                        $panel10T_3_path_three = env('APP_URL') . "sample/product/10-PANEL/T-3/20003.jpg";
                        $panel10T_3_three = date('YmdHis') . "27" . ".jpg";
                        Image::make($panel10T_3_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_3_three));

                        // 11-PANEL
                        $panel11T_1_path_one = env('APP_URL') . "sample/product/11-PANEL/T-1/10002.jpg";
                        $panel11T_1_one = date('YmdHis') . "28" . ".jpg";
                        Image::make($panel11T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_1_one));

                        $panel11T_1_path_two = env('APP_URL') . "sample/product/11-PANEL/T-1/20001.jpg";
                        $panel11T_1_two = date('YmdHis') . "29" . ".jpg";
                        Image::make($panel11T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_1_two));

                        $panel11T_1_path_three = env('APP_URL') . "sample/product/11-PANEL/T-1/20003.jpg";
                        $panel11T_1_three = date('YmdHis') . "30" . ".jpg";
                        Image::make($panel11T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_1_three));

                        $panel11T_2_path_one = env('APP_URL') . "sample/product/11-PANEL/T-2/10002.jpg";
                        $panel11T_2_one = date('YmdHis') . "31" . ".jpg";
                        Image::make($panel11T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_2_one));

                        $panel11T_2_path_two = env('APP_URL') . "sample/product/11-PANEL/T-2/20001.jpg";
                        $panel11T_2_two = date('YmdHis') . "32" . ".jpg";
                        Image::make($panel11T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_2_two));

                        $panel11T_2_path_three = env('APP_URL') . "sample/product/11-PANEL/T-2/20003.jpg";
                        $panel11T_2_three = date('YmdHis') . "33" . ".jpg";
                        Image::make($panel11T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_2_three));

                        // 12-PANEL
                        $panel12T_1_path_one = env('APP_URL') . "sample/product/12-PANEL/T-1/10002.jpg";
                        $panel12T_1_one = date('YmdHis') . "34" . ".jpg";
                        Image::make($panel12T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_1_one));

                        $panel12T_1_path_two = env('APP_URL') . "sample/product/12-PANEL/T-1/20001.jpg";
                        $panel12T_1_two = date('YmdHis') . "35" . ".jpg";
                        Image::make($panel12T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_1_two));

                        $panel12T_1_path_three = env('APP_URL') . "sample/product/12-PANEL/T-1/20003.jpg";
                        $panel12T_1_three = date('YmdHis') . "36" . ".jpg";
                        Image::make($panel12T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_1_three));

                        $panel12T_2_path_one = env('APP_URL') . "sample/product/12-PANEL/T-2/10002.jpg";
                        $panel12T_2_one = date('YmdHis') . "37" . ".jpg";
                        Image::make($panel12T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_2_one));

                        $panel12T_2_path_two = env('APP_URL') . "sample/product/12-PANEL/T-2/20001.jpg";
                        $panel12T_2_two = date('YmdHis') . "38" . ".jpg";
                        Image::make($panel12T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_2_two));

                        $panel12T_2_path_three = env('APP_URL') . "sample/product/12-PANEL/T-2/20003.jpg";
                        $panel12T_2_three = date('YmdHis') . "39" . ".jpg";
                        Image::make($panel12T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_2_three));

                        $panel12T_3_path_one = env('APP_URL') . "sample/product/12-PANEL/T-3/10002.jpg";
                        $panel12T_3_one = date('YmdHis') . "40" . ".jpg";
                        Image::make($panel12T_3_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_3_one));

                        $panel12T_3_path_two = env('APP_URL') . "sample/product/12-PANEL/T-3/20001.jpg";
                        $panel12T_3_two = date('YmdHis') . "41" . ".jpg";
                        Image::make($panel12T_3_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_3_two));

                        $panel12T_3_path_three = env('APP_URL') . "sample/product/12-PANEL/T-3/20003.jpg";
                        $panel12T_3_three = date('YmdHis') . "42" . ".jpg";
                        Image::make($panel12T_3_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_3_three));

                        // 13-PANEL
                        $panel13T_1_path_one = env('APP_URL') . "sample/product/13-PANEL/T-1/10002.jpg";
                        $panel13T_1_one = date('YmdHis') . "43" . ".jpg";
                        Image::make($panel13T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_1_one));

                        $panel13T_1_path_two = env('APP_URL') . "sample/product/13-PANEL/T-1/20001.jpg";
                        $panel13T_1_two = date('YmdHis') . "44" . ".jpg";
                        Image::make($panel13T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_1_two));

                        $panel13T_1_path_three = env('APP_URL') . "sample/product/13-PANEL/T-1/20003.jpg";
                        $panel13T_1_three = date('YmdHis') . "45" . ".jpg";
                        Image::make($panel13T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_1_three));

                        $panel13T_2_path_one = env('APP_URL') . "sample/product/13-PANEL/T-2/10002.jpg";
                        $panel13T_2_one = date('YmdHis') . "46" . ".jpg";
                        Image::make($panel13T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_2_one));

                        $panel13T_2_path_two = env('APP_URL') . "sample/product/13-PANEL/T-2/20001.jpg";
                        $panel13T_2_two = date('YmdHis') . "47" . ".jpg";
                        Image::make($panel13T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_2_two));

                        $panel13T_2_path_three = env('APP_URL') . "sample/product/13-PANEL/T-2/20003.jpg";
                        $panel13T_2_three = date('YmdHis') . "48" . ".jpg";
                        Image::make($panel13T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_2_three));

                        // 14-PANEL
                        $panel14T_1_path_one = env('APP_URL') . "sample/product/14-PANEL/T-1/10002.jpg";
                        $panel14T_1_one = date('YmdHis') . "49" . ".jpg";
                        Image::make($panel14T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_1_one));

                        $panel14T_1_path_two = env('APP_URL') . "sample/product/14-PANEL/T-1/20001.jpg";
                        $panel14T_1_two = date('YmdHis') . "50" . ".jpg";
                        Image::make($panel14T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_1_two));

                        $panel14T_1_path_three = env('APP_URL') . "sample/product/14-PANEL/T-1/20003.jpg";
                        $panel14T_1_three = date('YmdHis') . "51" . ".jpg";
                        Image::make($panel14T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_1_three));

                        $panel14T_2_path_one = env('APP_URL') . "sample/product/14-PANEL/T-2/10002.jpg";
                        $panel14T_2_one = date('YmdHis') . "52" . ".jpg";
                        Image::make($panel14T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_2_one));

                        $panel14T_2_path_two = env('APP_URL') . "sample/product/14-PANEL/T-2/20001.jpg";
                        $panel14T_2_two = date('YmdHis') . "53" . ".jpg";
                        Image::make($panel14T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_2_two));

                        $panel14T_2_path_three = env('APP_URL') . "sample/product/14-PANEL/T-2/20003.jpg";
                        $panel14T_2_three = date('YmdHis') . "54" . ".jpg";
                        Image::make($panel14T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_2_three));

                        // 15-PANEL
                        $panel15T_1_path_one = env('APP_URL') . "sample/product/15-PANEL/T-1/10002.jpg";
                        $panel15T_1_one = date('YmdHis') . "55" . ".jpg";
                        Image::make($panel15T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_1_one));

                        $panel15T_1_path_two = env('APP_URL') . "sample/product/15-PANEL/T-1/20001.jpg";
                        $panel15T_1_two = date('YmdHis') . "56" . ".jpg";
                        Image::make($panel15T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_1_two));

                        $panel15T_1_path_three = env('APP_URL') . "sample/product/15-PANEL/T-1/20003.jpg";
                        $panel15T_1_three = date('YmdHis') . "57" . ".jpg";
                        Image::make($panel15T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_1_three));

                        $panel15T_2_path_one = env('APP_URL') . "sample/product/15-PANEL/T-2/10002.jpg";
                        $panel15T_2_one = date('YmdHis') . "58" . ".jpg";
                        Image::make($panel15T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_2_one));

                        $panel15T_2_path_two = env('APP_URL') . "sample/product/15-PANEL/T-2/20001.jpg";
                        $panel15T_2_two = date('YmdHis') . "59" . ".jpg";
                        Image::make($panel15T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_2_two));

                        $panel15T_2_path_three = env('APP_URL') . "sample/product/15-PANEL/T-2/20003.jpg";
                        $panel15T_2_three = date('YmdHis') . "60" . ".jpg";
                        Image::make($panel15T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_2_three));

                        // 16-PANEL
                        $panel16T_1_path_one = env('APP_URL') . "sample/product/16-PANEL/T-1/R010001.jpg";
                        $panel16T_1_one = date('YmdHis') . "61" . ".jpg";
                        Image::make($panel16T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_1_one));

                        $panel16T_1_path_two = env('APP_URL') . "sample/product/16-PANEL/T-1/R010002.jpg";
                        $panel16T_1_two = date('YmdHis') . "62" . ".jpg";
                        Image::make($panel16T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_1_two));

                        $panel16T_1_path_three = env('APP_URL') . "sample/product/16-PANEL/T-1/R010003.jpg";
                        $panel16T_1_three = date('YmdHis') . "63" . ".jpg";
                        Image::make($panel16T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_1_three));

                        $panel16T_2_path_one = env('APP_URL') . "sample/product/16-PANEL/T-2/3P6_160002.jpg";
                        $panel16T_2_one = date('YmdHis') . "64" . ".jpg";
                        Image::make($panel16T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_2_one));

                        $panel16T_2_path_two = env('APP_URL') . "sample/product/16-PANEL/T-2/10001.jpg";
                        $panel16T_2_two = date('YmdHis') . "65" . ".jpg";
                        Image::make($panel16T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_2_two));

                        $panel16T_2_path_three = env('APP_URL') . "sample/product/16-PANEL/T-2/10003.jpg";
                        $panel16T_2_three = date('YmdHis') . "66" . ".jpg";
                        Image::make($panel16T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_2_three));

                        // 17-PANEL
                        $panel17T_1_path_one = env('APP_URL') . "sample/product/17-PANEL/T-1/R010002.jpg";
                        $panel17T_1_one = date('YmdHis') . "67" . ".jpg";
                        Image::make($panel17T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel17T_1_one));

                        $panel17T_1_path_two = env('APP_URL') . "sample/product/17-PANEL/T-1/R010001.jpg";
                        $panel17T_1_two = date('YmdHis') . "68" . ".jpg";
                        Image::make($panel17T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel17T_1_two));

                        $panel17T_1_path_three = env('APP_URL') . "sample/product/17-PANEL/T-1/R010003.jpg";
                        $panel17T_1_three = date('YmdHis') . "69" . ".jpg";
                        Image::make($panel17T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel17T_1_three));

                        // 18-PANEL
                        $panel18T_1_path_one = env('APP_URL') . "sample/product/18-PANEL/T-1/R010002.jpg";
                        $panel18T_1_one = date('YmdHis') . "70" . ".jpg";
                        Image::make($panel18T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_1_one));

                        $panel18T_1_path_two = env('APP_URL') . "sample/product/18-PANEL/T-1/R010001.jpg";
                        $panel18T_1_two = date('YmdHis') . "71" . ".jpg";
                        Image::make($panel18T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_1_two));

                        $panel18T_1_path_three = env('APP_URL') . "sample/product/18-PANEL/T-1/R010003.jpg";
                        $panel18T_1_three = date('YmdHis') . "72" . ".jpg";
                        Image::make($panel18T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_1_three));

                        $panel18T_2_path_one = env('APP_URL') . "sample/product/18-PANEL/T-2/3P60002.jpg";
                        $panel18T_2_one = date('YmdHis') . "73" . ".jpg";
                        Image::make($panel18T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_2_one));

                        $panel18T_2_path_two = env('APP_URL') . "sample/product/18-PANEL/T-2/Mr0001.jpg";
                        $panel18T_2_two = date('YmdHis') . "74" . ".jpg";
                        Image::make($panel18T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_2_two));

                        $panel18T_2_path_three = env('APP_URL') . "sample/product/18-PANEL/T-2/Mr0003.jpg";
                        $panel18T_2_three = date('YmdHis') . "75" . ".jpg";
                        Image::make($panel18T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_2_three));

                        // 21-PANEL
                        $panel21T_1_path_one = env('APP_URL') . "sample/product/21-PANEL/T-1/R010002.jpg";
                        $panel21T_1_one = date('YmdHis') . "76" . ".jpg";
                        Image::make($panel21T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_1_one));

                        $panel21T_1_path_two = env('APP_URL') . "sample/product/21-PANEL/T-1/R010001.jpg";
                        $panel21T_1_two = date('YmdHis') . "77" . ".jpg";
                        Image::make($panel21T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_1_two));

                        $panel21T_1_path_three = env('APP_URL') . "sample/product/21-PANEL/T-1/R010003.jpg";
                        $panel21T_1_three = date('YmdHis') . "78" . ".jpg";
                        Image::make($panel21T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_1_three));

                        $panel21T_2_path_one = env('APP_URL') . "sample/product/21-PANEL/T-2/3P70002.jpg";
                        $panel21T_2_one = date('YmdHis') . "79" . ".jpg";
                        Image::make($panel21T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_2_one));

                        $panel21T_2_path_two = env('APP_URL') . "sample/product/21-PANEL/T-2/Mr0001.jpg";
                        $panel21T_2_two = date('YmdHis') . "80" . ".jpg";
                        Image::make($panel21T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_2_two));

                        $panel21T_2_path_three = env('APP_URL') . "sample/product/21-PANEL/T-2/Mr0003.jpg";
                        $panel21T_2_three = date('YmdHis') . "81" . ".jpg";
                        Image::make($panel21T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_2_three));

                        // 24-PANEL
                        $panel24T_1_path_one = env('APP_URL') . "sample/product/24-PANEL/T-1/R010002.jpg";
                        $panel24T_1_one = date('YmdHis') . "82" . ".jpg";
                        Image::make($panel24T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_1_one));

                        $panel24T_1_path_two = env('APP_URL') . "sample/product/24-PANEL/T-1/R010001.jpg";
                        $panel24T_1_two = date('YmdHis') . "83" . ".jpg";
                        Image::make($panel24T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_1_two));

                        $panel24T_1_path_three = env('APP_URL') . "sample/product/24-PANEL/T-1/R010003.jpg";
                        $panel24T_1_three = date('YmdHis') . "84" . ".jpg";
                        Image::make($panel24T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_1_three));

                        $panel24T_2_path_one = env('APP_URL') . "sample/product/24-PANEL/T-2/10002.jpg";
                        $panel24T_2_one = date('YmdHis') . "85" . ".jpg";
                        Image::make($panel24T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_2_one));

                        $panel24T_2_path_two = env('APP_URL') . "sample/product/24-PANEL/T-2/SSEMH0817-Mr0001.jpg";
                        $panel24T_2_two = date('YmdHis') . "86" . ".jpg";
                        Image::make($panel24T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_2_two));

                        $panel24T_2_path_three = env('APP_URL') . "sample/product/24-PANEL/T-2/SSEMH0817-Mr0003.jpg";
                        $panel24T_2_three = date('YmdHis') . "87" . ".jpg";
                        Image::make($panel24T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_2_three));

                        // 27-PANEL
                        $panel27T_1_path_one = env('APP_URL') . "sample/product/27-PANEL/T-1/R010002.jpg";
                        $panel27T_1_one = date('YmdHis') . "88" . ".jpg";
                        Image::make($panel27T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_1_one));

                        $panel27T_1_path_two = env('APP_URL') . "sample/product/27-PANEL/T-1/R010001.jpg";
                        $panel27T_1_two = date('YmdHis') . "89" . ".jpg";
                        Image::make($panel27T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_1_two));

                        $panel27T_1_path_three = env('APP_URL') . "sample/product/27-PANEL/T-1/R010003.jpg";
                        $panel27T_1_three = date('YmdHis') . "90" . ".jpg";
                        Image::make($panel27T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_1_three));

                        $panel27T_2_path_one = env('APP_URL') . "sample/product/27-PANEL/T-2/10002.jpg";
                        $panel27T_2_one = date('YmdHis') . "91" . ".jpg";
                        Image::make($panel27T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_2_one));

                        $panel27T_2_path_two = env('APP_URL') . "sample/product/27-PANEL/T-2/R0_SSEMH0952-Mr0001.jpg";
                        $panel27T_2_two = date('YmdHis') . "92" . ".jpg";
                        Image::make($panel27T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_2_two));

                        $panel27T_2_path_three = env('APP_URL') . "sample/product/27-PANEL/T-2/R0_SSEMH0952-Mr0003.jpg";
                        $panel27T_2_three = date('YmdHis') . "93" . ".jpg";
                        Image::make($panel27T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_2_three));

                        // 29-PANEL
                        $panel29T_1_path_one = env('APP_URL') . "sample/product/29-PANEL/T-1/R010001.jpg";
                        $panel29T_1_one = date('YmdHis') . "94" . ".jpg";
                        Image::make($panel29T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_1_one));

                        $panel29T_1_path_two = env('APP_URL') . "sample/product/29-PANEL/T-1/R010002.jpg";
                        $panel29T_1_two = date('YmdHis') . "95" . ".jpg";
                        Image::make($panel29T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_1_two));

                        $panel29T_1_path_three = env('APP_URL') . "sample/product/29-PANEL/T-1/R010003.jpg";
                        $panel29T_1_three = date('YmdHis') . "96" . ".jpg";
                        Image::make($panel29T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_1_three));

                        $panel29T_2_path_one = env('APP_URL') . "sample/product/29-PANEL/T-2/3P100002.jpg";
                        $panel29T_2_one = date('YmdHis') . "97" . ".jpg";
                        Image::make($panel29T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_2_one));

                        $panel29T_2_path_two = env('APP_URL') . "sample/product/29-PANEL/T-2/R010001.jpg";
                        $panel29T_2_two = date('YmdHis') . "98" . ".jpg";
                        Image::make($panel29T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_2_two));

                        $panel29T_2_path_three = env('APP_URL') . "sample/product/29-PANEL/T-2/R010003.jpg";
                        $panel29T_2_three = date('YmdHis') . "99" . ".jpg";
                        Image::make($panel29T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_2_three));


                        // 30-PANEL
                        $panel30T_1_path_one = env('APP_URL') . "sample/product/30-PANEL/T-1/R010001.jpg";
                        $panel30T_1_one = date('YmdHis') . "100" . ".jpg";
                        Image::make($panel30T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_1_one));

                        $panel30T_1_path_two = env('APP_URL') . "sample/product/30-PANEL/T-1/R010002.jpg";
                        $panel30T_1_two = date('YmdHis') . "101" . ".jpg";
                        Image::make($panel30T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_1_two));

                        $panel30T_1_path_three = env('APP_URL') . "sample/product/30-PANEL/T-1/R010003.jpg";
                        $panel30T_1_three = date('YmdHis') . "102" . ".jpg";
                        Image::make($panel30T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_1_three));

                        $panel30T_2_path_one = env('APP_URL') . "sample/product/30-PANEL/T-2/3P100002.jpg";
                        $panel30T_2_one = date('YmdHis') . "103" . ".jpg";
                        Image::make($panel30T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_2_one));

                        $panel30T_2_path_two = env('APP_URL') . "sample/product/30-PANEL/T-2/3P1010001.jpg";
                        $panel30T_2_two = date('YmdHis') . "104" . ".jpg";
                        Image::make($panel30T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_2_two));

                        $panel30T_2_path_three = env('APP_URL') . "sample/product/30-PANEL/T-2/3P1010003.jpg";
                        $panel30T_2_three = date('YmdHis') . "105" . ".jpg";
                        Image::make($panel30T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_2_three));

                        $commercial_path_one = env('APP_URL') . "sample/product/commercial/10002.jpg";
                        $commercial_one = rand(1000, 9999) . ".jpg";
                        Image::make($commercial_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $commercial_one));

                        $commercial_path_two = env('APP_URL') . "sample/product/commercial/20001.jpg";
                        $commercial_two = rand(1000, 9999) . ".jpg";
                        Image::make($commercial_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $commercial_two));

                        $commercial_path_three = env('APP_URL') . "sample/product/commercial/20003.jpg";
                        $commercial_three = rand(1000, 9999) . ".jpg";
                        Image::make($commercial_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $commercial_three));


                        $productArr = [
                            ['name' => '8 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel8T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel8T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel8T_1_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '8 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel8T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel8T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel8T_2_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '8 Panel_T-3', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel8T_3_one, 'image_two' => 'public/uploads/thumbnail/' . $panel8T_3_two, 'image_three' => 'public/uploads/thumbnail/' . $panel8T_3_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '9 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel9T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel9T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel9T_1_three, 'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '9 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel9T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel9T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel9T_2_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '9 Panel_T-3', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel9T_3_one, 'image_two' => 'public/uploads/thumbnail/' . $panel9T_3_two, 'image_three' => 'public/uploads/thumbnail/' . $panel9T_3_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '10 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel10T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel10T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel10T_1_three, 'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '10 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel10T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel10T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel10T_2_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '10 Panel_T-3', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel10T_3_one, 'image_two' => 'public/uploads/thumbnail/' . $panel10T_3_two, 'image_three' => 'public/uploads/thumbnail/' . $panel10T_3_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '11 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel11T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel11T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel11T_1_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '11 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel11T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel11T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel11T_2_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '12 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel12T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel12T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel12T_1_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '12 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel12T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel12T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel12T_2_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '12 Panel_T-3', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel12T_3_one, 'image_two' => 'public/uploads/thumbnail/' . $panel12T_3_two, 'image_three' => 'public/uploads/thumbnail/' . $panel12T_3_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '13 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel13T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel13T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel13T_1_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '13 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel13T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel13T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel13T_2_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '14 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel14T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel14T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel14T_1_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '14 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel14T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel14T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel14T_2_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '15 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel15T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel15T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel15T_1_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '15 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel15T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel15T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel15T_2_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '16 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel16T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel16T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel16T_1_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '16 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel16T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel16T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel16T_2_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '17 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel17T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel17T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel17T_1_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '18 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel18T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel18T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel18T_1_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '18 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel18T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel18T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel18T_2_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '21 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel21T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel21T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel21T_1_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '21 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel21T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel21T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel21T_2_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '24 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel24T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel24T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel24T_1_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '24 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel24T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel24T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel24T_2_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '27 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel27T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel27T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel27T_1_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '27 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel27T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel27T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel27T_2_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '29 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel29T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel29T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel29T_1_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '29 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel29T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel29T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel29T_2_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '30 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel30T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel30T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel30T_1_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => '30 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel30T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel30T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel30T_2_three, 'status' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                            ['name' => 'Commercial Installations', 'description' => 'Our Previous Project Installation Photos', 'image_one' => 'public/uploads/thumbnail/' . $commercial_one, 'image_two' => 'public/uploads/thumbnail/' . $commercial_two, 'image_three' => 'public/uploads/thumbnail/' . $commercial_three, 'status' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                        ];
                        DB::table('products')->insert($productArr);
                    }
                }
            }
            if (!User::whereIn('id', $id)->first()) {
                return response()->json(['success' => 'Client exists!'], 422);
            }
            $start_date = date('Y-m-d H:i:s');
            $from_date = date('Y-m-d H:i:s', strtotime("+14 day", strtotime($start_date)));
            $company_category = User::whereIn('id', $id)->update(["status" => $input['status'], 'plan_start_date' => $start_date, 'plan_end_date' => $from_date]);

            /*Clone Record*/
            if ($input['status'] === 'Approved') {
                $proposalTemplates = ProposalTemplates::first();
                $newProposalTemplates = $proposalTemplates->replicate();
                $newProposalTemplates->company_id = implode(',', $id);
                $newProposalTemplates->save();

                $lastId = $newProposalTemplates->id;

                $path = 'public/document/' . implode(',', $id);
                if (!Storage::exists($path)) {
                    Storage::makeDirectory($path);
                }
                if ($userDatas->company_category == 1) {

                    $cover_one = env('APP_URL') . "sample/cover/1.png";
                    $filename_cover_one = date('YmdHis') . "106cvr" . ".png";
                    Image::make($cover_one)->save(storage_path("app/public/uploads/thumbnail/" . $filename_cover_one));

                    $cover_two = env('APP_URL') . "sample/cover/2.png";
                    $filename_cover_two = date('YmdHis') . "107cvr" . ".png";
                    Image::make($cover_two)->save(storage_path("app/public/uploads/thumbnail/" . $filename_cover_two));

                    $cover_three = env('APP_URL') . "sample/cover/3.png";
                    $filename_cover_three = date('YmdHis') . "108cvr" . ".png";
                    Image::make($cover_three)->save(storage_path("app/public/uploads/thumbnail/" . $filename_cover_three));

                    $cover_four = env('APP_URL') . "sample/cover/4.png";
                    $filename_cover_four = date('YmdHis') . "109cvr" . ".png";
                    Image::make($cover_four)->save(storage_path("app/public/uploads/thumbnail/" . $filename_cover_four));

                    $cover_five = env('APP_URL') . "sample/cover/5.png";
                    $filename_cover_five = date('YmdHis') . "110cvr" . ".png";
                    Image::make($cover_five)->save(storage_path("app/public/uploads/thumbnail/" . $filename_cover_five));

                    $coverArr = [
                        ['proposal_template_id' => $lastId, 'image_icon' => 'public/uploads/thumbnail/' . $filename_cover_one, 'cover_flg' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                        ['proposal_template_id' => $lastId, 'image_icon' => 'public/uploads/thumbnail/' . $filename_cover_two, 'cover_flg' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                        ['proposal_template_id' => $lastId, 'image_icon' => 'public/uploads/thumbnail/' . $filename_cover_three, 'cover_flg' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                        ['proposal_template_id' => $lastId, 'image_icon' => 'public/uploads/thumbnail/' . $filename_cover_four, 'cover_flg' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                        ['proposal_template_id' => $lastId, 'image_icon' => 'public/uploads/thumbnail/' . $filename_cover_five, 'cover_flg' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId]
                    ];

                    DB::table('proposal_template_cover_photos')->insert($coverArr);

                    $aboutus_one = env('APP_URL') . "sample/about-us/1.jpg";
                    $filename_aboutus_one = date('YmdHis') . "106aboutus" . ".jpg";
                    Image::make($aboutus_one)->save(storage_path("app/public/uploads/thumbnail/" . $filename_aboutus_one));

                    $aboutus_two = env('APP_URL') . "sample/about-us/2.jpg";
                    $filename_aboutus_two = date('YmdHis') . "107aboutus" . ".jpg";
                    Image::make($aboutus_two)->save(storage_path("app/public/uploads/thumbnail/" . $filename_aboutus_two));

                    $aboutus_three = env('APP_URL') . "sample/about-us/3.jpg";
                    $filename_aboutus_three = date('YmdHis') . "108aboutus" . ".jpg";
                    Image::make($aboutus_three)->save(storage_path("app/public/uploads/thumbnail/" . $filename_aboutus_three));

                    $aboutus_four = env('APP_URL') . "sample/about-us/4.jpg";
                    $filename_aboutus_four = date('YmdHis') . "109aboutus" . ".jpg";
                    Image::make($aboutus_four)->save(storage_path("app/public/uploads/thumbnail/" . $filename_aboutus_four));

                    $aboutus_five = env('APP_URL') . "sample/about-us/5.jpg";
                    $filename_aboutus_five = date('YmdHis') . "110aboutus" . ".jpg";
                    Image::make($aboutus_five)->save(storage_path("app/public/uploads/thumbnail/" . $filename_aboutus_five));

                    $aboutusArr = [
                        ['proposal_template_id' => $lastId, 'image_icon' => 'public/uploads/thumbnail/' . $filename_aboutus_one, 'about_flg' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId],
                        ['proposal_template_id' => $lastId, 'image_icon' => 'public/uploads/thumbnail/' . $filename_aboutus_two, 'about_flg' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                        ['proposal_template_id' => $lastId, 'image_icon' => 'public/uploads/thumbnail/' . $filename_aboutus_three, 'about_flg' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                        ['proposal_template_id' => $lastId, 'image_icon' => 'public/uploads/thumbnail/' . $filename_aboutus_four, 'about_flg' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId],
                        ['proposal_template_id' => $lastId, 'image_icon' => 'public/uploads/thumbnail/' . $filename_aboutus_five, 'about_flg' => 0, 'user_id' => $tmpId, 'company_id' => $tmpId]
                    ];
                    DB::table('proposal_template_aboutus_photos')->insert($aboutusArr);
                }

                if ($userDatas->company_category != 1) {
                    $cover_one = env('APP_URL') . "sample/cover/cover-img.png";
                    $filename_cover_one = date('YmdHis') . "150defcvr" . ".png";
                    Image::make($cover_one)->save(storage_path("app/public/uploads/thumbnail/" . $filename_cover_one));

                    $coverArr = [
                        ['proposal_template_id' => $lastId, 'image_icon' => 'public/uploads/thumbnail/' . $filename_cover_one, 'cover_flg' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId]
                    ];
                    DB::table('proposal_template_cover_photos')->insert($coverArr);

                    $aboutus_one = env('APP_URL') . "sample/about-us/2480x1754.png";
                    $filename_aboutus_one = date('YmdHis') . "151defaboutus" . ".png";
                    Image::make($aboutus_one)->save(storage_path("app/public/uploads/thumbnail/" . $filename_aboutus_one));

                    $aboutusArr = [
                        ['proposal_template_id' => $lastId, 'image_icon' => 'public/uploads/thumbnail/' . $filename_aboutus_one, 'about_flg' => 1, 'user_id' => $tmpId, 'company_id' => $tmpId]
                    ];
                    DB::table('proposal_template_aboutus_photos')->insert($aboutusArr);
                }
                $termConditionArr = [
                    "name" => "Basic Terms", "description" => '<p><span style="color:#3498db"><span style="font-size:16px"><strong>Terms &amp; Condition</strong></span></span></p>
<p>Material dispatch and Installation shall be started upon DISCOM approval only.</p>
<p>For better performance, solar panels should be cleaned by customer two times in a week.</p>
<p>Concealed wiring shall be done by company, if possible only. Otherwise, customer should do concealed wiring with their wiremen where material shall be provided by Company.</p>
<p>After successful installation, Customer shall take care of solar plant by doing timely cleaning. If we found less generation at the time of attending complaint due to non-cleaning, we may charge you additional service&nbsp; charge.</p>
<p>There is manufacturing warranty for all electronics equipment. Company will help to claim this warranty if require.&nbsp;</p>
<p>The company will provide up to 30 meter wire 25 Year warranty of PV Module, 10 Year warranty of Inverter and 5 Year O&amp;M of System by Company</p>
<p><span style="color:#3498db"><span style="font-size:16px"><strong>Scope of Work For Customer:</strong></span></span></p>
<p>Providing access/approach to rooftop&nbsp;</p>
<p>If any system modification is required from DISCOM ( i.e. ELCB, changeover etc.)&nbsp;</p>
<p>Provide necessary documents for project approvals from State/Central Government&nbsp;</p>
<p>Site clearance, water, and electricity for smooth installation and commissioning of the project&nbsp;</p>
<p>Required civil work and approvals to complete the project within the timeline proposed</p>
<p>Safe storage of materials (PV modules, Inverter, etc.) upon delivery&nbsp;</p>
<p>Customer shall provide Safe Place for Material unloading and storage during the work execution</p>
<p><span style="color:#3498db"><span style="font-size:16px"><strong>Warranty Exclusion:</strong></span></span></p>
<p>Damage due to improper handling&nbsp;</p>
<p>In absence of full payment&nbsp;</p>
<p>Damage to due to force majeure Defects due to third party inference (direct or indirect) or act to our system&nbsp;</p>
<p>This offer in itself or any subsequent Communications/documents will be subject to standard Force Majeure conditions.&nbsp;</p>
<p>Jurisdiction: Subject to Surat jurisdiction.</p>', 'user_id' => $tmpId, 'company_id' => $tmpId
                ];
                $term_condition_id = DB::table('term_conditions')->insertGetId($termConditionArr);
                ProposalTemplates::where('company_id', $tmpId)->update(array('term_condition_id' => $term_condition_id, 'cover_img' => 'public/uploads/thumbnail/' . $filename_cover_one, 'aboutas_img' => 'public/uploads/thumbnail/' . $filename_aboutus_one));

                \Mail::to($userDatas->email)->send(new \App\Mail\ClientApprovedAccountMail(["name" => $userDatas->name]));
            }

            $data['id'] = $id;
            $data['status'] = $input['status'];
            LogActivity::addToLog('Client status updated by ' . $user->name, $data);

            return response()->json(['success' => 'Client status updated!'], 201);
        }
    }

    public function update(Request $request)
    {
        $input = $request->all();
        $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
        if ((empty($input['plan_end_date']) || empty($input['plan_start_date'])) && ($input['remaining_days'] <= 0 || empty($input['remaining_days']))) {
            return response()->json(['success' => 'please enter days or dates'], 400);
        }

        if ($input['plan_end_date'] && $input['remaining_days'] > 0) {
            return response()->json(['success' => 'Atleast one selected days or dates'], 400);
        }

        if (!empty($input['plan_end_date']) && !empty($input['plan_start_date'])) {
            $input['plan_start_date'] = Carbon::createFromFormat('d/m/Y', $input['plan_start_date'])->format('Y-m-d H:i:s');
            $input['plan_end_date'] = Carbon::createFromFormat('d/m/Y', $input['plan_end_date'])->format('Y-m-d H:i:s');
            $input['remaining_days'] = Carbon::parse($input['plan_start_date'])->diffInDays(Carbon::parse($input['plan_end_date']));
            if ($input['remaining_days'] <= 0) {
                return response()->json(['success' => 'Please select different start and end date'], 400);
            }
        }

        if ($input['remaining_days'] > 0) {
            $input['plan_start_date'] = Carbon::now();
            $input['plan_end_date'] = Carbon::now()->addDays($input['remaining_days']);
        }


        $data['plan_start_date'] = $input['plan_start_date'];
        $data['plan_end_date'] = $input['plan_end_date'];
        $data['remaining_days'] = $input['remaining_days'];
        $data['plan_status'] = '5';
        $data['popupStatus'] = '2';
        if (isset($input['plan_id'])) {
            $data['plan_id'] = $input['plan_id'];
            $plan = Plans::where('id', $input['plan_id'])->first();
            PlanHistory::where([['user_id', $id], ['status', 1]])->update(['status' => 0]);
            PlanHistory::create([
                'user_id' => $id,
                'plan_id' => $plan->id,
                'user_limit' => $plan->users_limit,
                'estimate_limit' => $plan->estimate_limit,
                'status' => 1,
                'start_date' => $input['plan_start_date'],
                'end_date' => $input['plan_end_date']
            ]);

            $email_data = User::where('id', $id)->first();
            $plan_purchase_details = [
                'subject' => "Plan Upgrade Confirmation",
                /* 'body' => "hello this is testing",*/
//                'user_limit' => $planHistory->user_limit + $orderData->add_user,
                'plan_type' => 0,
                'user_name' => $email_data->name,
                'plan_name' => $plan->name,
                'users_limit' => $plan->users_limit,
                'estimate_limit' => $plan->estimate_limit
            ];
            \Mail::to($email_data->email)->send(new \App\Mail\PurchasePlanMail($plan_purchase_details));
        }
        $users = User::find($id)->update($data);
        return response()->json(['success' => 'Plan updated successfully!'], 201);
    }

    public function storageUpdate(Request $request)
    {
        $input = $request->all();
        $data['id'] = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
        $data['storage_capacity'] = $input['storage_capacity'];

        $users = Tenant::find($data['id'])->update($data);
        return response()->json(['success' => 'Storage updated successfully!'], 201);
    }
}
