<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\{Permission, Role, PlanHistory};

class UserController extends Controller
{

    protected $segment = null;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->segment = $request->segment(1);
            return $next($request);
        });
    }

    public function getOptions(Request $request)
    {
        $query = $request->get('q');

        // Replace this with your own data fetching logic
        /*$data = [
            ['id' => 1, 'text' => 'Option 1'],
            ['id' => 2, 'text' => 'Option 2'],
            ['id' => 3, 'text' => 'Option 3'],
        ];*/

        $data = User::query()->get(['id', 'name as text'])->toArray();



        // Filter the data based on the query if needed
        if ($query) {
            $data = array_filter($data, function ($item) use ($query) {
                return stripos($item['text'], $query) !== false;
            });
        }

        return response()->json(array_values($data));
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        if (!auth()->check()) {
            //return redirect()->route('tenant.login', ['tenant' => $this->segment]); // Redirect to login if not authenticated
            return redirect()->route('login'); // Redirect to login if not authenticated
        }
        $user = Auth::user();
        $id = isset($user->company_id) ? $user->company_id : $user->id;
        $userCount = User::where('company_id', $id)->where("invite_status",1)->count();
        // $plan = PlanHistory::where([['user_id', $id], ['status', 1]])->first();dd($plan);
        $plan = PlanHistory::where([['status', 1]])->first();//dd($plan);
        $users = User::query()->get()->toArray();
        $segment = $this->segment;
        return view('app.users.index', compact('users', 'segment', 'plan', 'userCount'));
    }

    public function getUserdata(Request $request) {

        if ($request->ajax()) {
            $user = Auth::user();
            $company_id = ($user->company_id) ? $user->company_id : $user->id;
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

            $totalRecords = DB::table('users_views')
                ->where(function ($query) use ($company_id) {
                    $query->where('company_id', $company_id);
                    $query->orwhere('id', $company_id);
                })
                ->where(function ($query) use ($search_arr) {
                if ($search_arr) {
                    //                    $query->orWhere(function ($query) use ($search_arr) {
                    $query->orwhere('name', 'like', '%' . $search_arr . '%');
                    $query->orwhere('email', 'like', '%' . $search_arr . '%');
                    $query->orwhere('mobile_no', 'like', '%' . $search_arr . '%');
                    $query->orwhere('role_name', 'like', '%' . $search_arr . '%');
                    //                    });
                }
            })
                //          ->whereNotNull('u1.company_id')
                //                ->where('u1.id', $company_id)
                ->select('count(u1.id) as allcount')
                ->count();

            $totalRecordswithFilter = DB::table('users_views')
                ->where(function ($query) use ($company_id) {
                    $query->where('company_id', $company_id);
                    $query->orwhere('id', $company_id);
                })
                ->where(function ($query) use ($search_arr) {
                    if ($search_arr) {
                        //                    $query->where(function ($query) use ($search_arr) {
                        $query->orwhere('name', 'like', '%' . $search_arr . '%');
                        $query->orwhere('email', 'like', '%' . $search_arr . '%');
                        $query->orwhere('mobile_no', 'like', '%' . $search_arr . '%');
                        $query->orwhere('role_name', 'like', '%' . $search_arr . '%');
                        //                    });
                    }
                })
                //                ->whereNotNull('u1.company_id')
                //                ->where('id', $company_id)
                ->select('count(u1.id) as allcount')
                ->count();

            $records = DB::table('users_views')
                ->where(function ($query) use ($company_id) {
                    $query->where('company_id', $company_id);
                    $query->orwhere('id', $company_id);
                })
                ->where(function ($query) use ($search_arr) {
                    if ($search_arr) {
                        //                        $query->orWhere(function ($query) use ($search_arr) {
                        $query->orwhere('name', 'like', '%' . $search_arr . '%');
                        $query->orwhere('email', 'like', '%' . $search_arr . '%');
                        $query->orwhere('mobile_no', 'like', '%' . $search_arr . '%');
                        $query->orwhere('role_name', 'like', '%' . $search_arr . '%');
                        //                        });
                    }
                })
                //                ->whereNotNull('u1.company_id')
                //                ->where('u1.id', $company_id)

                ->select(['id', 'name', 'mobile_no', 'email', 'role_name', 'invite_status','company_id'])
                ->skip($start)
                ->take($rowperpage)
                ->orderBy($columnName, $columnSortOrder)
                ->get();
            $data = array();
            $i = 0;
            foreach ($records as $record) {
                $id = Crypt::encrypt($record->id);
                $role_name = $record->role_name;
                $name = $record->name;
                $email = $record->email;
                $mobile_no = $record->mobile_no;
                $status = $record->invite_status;
                $company_id = $record->company_id;
                $i++;
                $data[] = array(
                    "temp_user_id" => $record->id,
                    "id" => $i,
                    "role_name" => $role_name,
                    "name" => $name,
                    "email" => $email,
                    "mobile_no" => $mobile_no,
                    "status" => $status,
                    "action" => $id,
                    "company_id" => $company_id,
                );
            }
            //            $totalRecords = 0;
            //            $totalRecordswithFilter = 0;
            $response = array(
                "draw" => intval($draw),
                "recordsTotal" => $totalRecords,
                "recordsFiltered" => $totalRecordswithFilter,
                "data" => $data
            );
            return json_encode($response);
        }
    }

    public function editNew(Request $request)
    {
        $input = $request->all();
        $id = Crypt::decrypt($input['id']);
        $data['user'] = User::find($id);
        $company_id = (Auth::user()->company_id) ? Auth::user()->company_id : Auth::user()->id;

        $data['user_permissions'] = UserPermission::query()->where('user_id', $id)->where('company_id', $company_id)->pluck('permission_id')->toArray();
        return response()->json([
            "success" => true,
            "message" => "Customer retrieved successfully.",
            "data" => $data
        ], 201);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $user = Auth::user();
        $user['tenant_id'] = tenant('id');
        $segment = $this->segment;
        return view('app.users.create', compact('user', 'segment'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users|max:255',
            //'domain_name' => 'required|string|unique:domains,domain|max:255',
            'password' => ['required','string','confirmed', Rules\Password::defaults()],
            // 'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $input = $request->all();
        $uid = uniqid();
        $data = [
            'id' => $uid,
            'name' => $input['name'],
            'email' => $input['email'],
            'domain' => $input['domain_name'],
            'company_id' => $input['tenant_id'],
            'password' => Hash::make($input['password']),
            // other columns
        ];
        DB::connection('mysql')->table('tenants')->insert($data);

        $company_id = (Auth::user()->company_id) ? Auth::user()->company_id : Auth::user()->id;
        $tenant = User::query()->create([
            'name' => $input['name'],
            'email' => $input['email'],
            'domain' => $input['domain_name'],
            'password' => Hash::make($input['password']),
            'company_id' => $company_id
        ]);

        // $tenant->domains()->create([
        //     'domain' => $input['domain_name'] . '.' . config('app.domain')
        // ]);
        return redirect()->route('tenant.users.index', ['tenant' => $this->segment]);
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

            $id = Crypt::decrypt($input['id']);

            if (!User::where('id', $id)->first()) {
                return response()->json(['success' => 'User exists!'], 422);
            }

            if($input['status'] == 1){
                $company_id = ($user->company_id) ? $user->company_id : $user->id;
                $companyDataCount = User::where([['company_id',"=", $company_id],["invite_status","=",1]])->count();
                $planData = PlanHistory::where([['user_id', $company_id], ['status', 1]])->first();

                if ($companyDataCount > 0) {
                    $user_limit = $planData->user_limit - 1;
                    if ($companyDataCount >= $user_limit) {
                        return response()->json(['errors' => 'Your account has total '.$planData->user_limit.' Users. To activate this user please deactive any other user from your list. Or else you can buy new user.'], 400);
                    }

                }
            }


            $unit = User::where('id', $id)->update(["invite_status" => $input['status']]);
            if($input['status']==2){
                Lead_assign_users::where('user_id',$id)->delete();
            }
            DB::table('personal_access_tokens')->where('tokenable_id', $id)->delete();
            DB::table('sessions')->where('user_id', $id)->delete();

            $data['id'] = $id;
            $data['status'] = ($input['status'] == 1) ? 'Active' : 'Deactive';
            LogActivity::addToLog('User status updated by ' . $user->name, $data);

            return response()->json(['success' => 'User status updated!'], 201);
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
            $unit = User::whereIn('id', $id)->delete();
            LogActivity::addToLog('Unit deleted by ' . $user->name, $id);
            return response()->json(['success' => 'User Deleted!'], 201);
        }
    }

    public function storeNew(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'mobile_no' => 'required',

        ]);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->with('error', $validator->messages()->first());
        }

       // try {

        $input = $request->all();
        $id = $input['id'];
        // $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
        $tenant_id = tenant('id');
        $company_id = (Auth::user()->company_id) ? Auth::user()->company_id : Auth::user()->id;
        $domain = (Auth::user()->domain) ? Auth::user()->domain : '';

        if (User::query()->where('email', '=', $input['email'])->where(function ($query) use ($company_id, $id) {
            $query->Where(function ($query) use ($company_id, $id) {
                // $query->where('company_id', $company_id);
                if ($id != 0) {
                    $query->where('id', '!=', $id);
                }
            });
        })->first()) {
            return response()->json(['success' => 'Team member exists!'], 409);
        }
        
        foreach ($input['data'] as $key => $val) {
            if ($val['permission_id'] == 0)
                unset($input['data'][$key]);
        }
        if ($id == 0) {
            // \DB::enableQueryLog();
            $users = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'mobile_no' => $input['mobile_no'],
                'role_name' => $input['role_name'],
                'domain' => $domain,
                'company_id' => $company_id,
                'user_role' => "1",
                'permissions' => null,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'status' => 'Approved',
                'is_owner' => 0,
                'customer_show_flg' => 1
            ]);

            $uid = uniqid();
            $tenantdatas = [
                'id' => $uid,
                'name' => $input['name'],
                'email' => $input['email'],
                'mobile_no' => $input['mobile_no'],
                'role_name' => $input['role_name'],
                'domain' => $domain,
                'company_id' => $tenant_id,
                'user_role' => "1",
                'permissions' => null,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'status' => 'Approved',
                'is_owner' => 0,
                'customer_show_flg' => 1
            ];
            DB::connection('mysql')->table('tenants')->insert($tenantdatas);
            // dd(\DB::getQueryLog($users));
            $insert_id = $users->id;
            if (!empty($input['data'])) {
                $input['data'] = array_map(function ($arr) use ($insert_id, $company_id) {
                    return $arr + ['user_id' => $insert_id, 'company_id' => $company_id];
                }, $input['data']);

                $permission = UserPermission::query()->insert($input['data']);
            }

            $dashboardSettingArr = [
                ['permission_id' => 1, 'user_id' => $insert_id, 'company_id' => $company_id, 'is_primary' =>0],
                ['permission_id' => 2, 'user_id' => $insert_id, 'company_id' => $company_id, 'is_primary' =>1],
                // ['permission_id' => 3, 'user_id' => $insert_id, 'company_id' => $company_id', 'is_primary' =>0],
                ['permission_id' => 4, 'user_id' => $insert_id, 'company_id' => $company_id, 'is_primary' =>1],
                ['permission_id' => 5, 'user_id' => $insert_id, 'company_id' => $company_id, 'is_primary' =>0],
                ['permission_id' => 6, 'user_id' => $insert_id, 'company_id' => $company_id, 'is_primary' =>1],
            ];
            \Illuminate\Support\Facades\DB::table('dashboard_settings')->insert($dashboardSettingArr);
            $users->invite_user_name = Auth::user()->name;
            $mail_details = $users;
            \Mail::to($users->email)->send(new \App\Mail\InviteMail($mail_details));
        } else {

            $userDatas = User::where('id', $id)->first();

            $tenantupdatedatas = [
                'name' => $request->name,
                'email' => $request->email,
                'mobile_no' => $request->mobile_no,
                'role_name' => $request->role_name,
                'user_role' => "1",
                'permissions' => null,
                'customer_show_flg' => 1
            ];
            DB::connection('mysql')->table('tenants')->where('email', $userDatas->email)->update($tenantupdatedatas);
            
            $update = User::find($id)->update([
                'name' => $request->name,
                'email' => $request->email,
                'mobile_no' => $request->mobile_no,
                'role_name' => $request->role_name,
                'user_role' => "1",
                'permissions' => null,
                'customer_show_flg' => 1
            ]);
            
            /*echo "<pre>";
            print_r($input['data']); die;*/
            if (!empty($input['data'])) {


                $input['data'] = array_map(function ($arr) use ($id, $company_id) {
                    return $arr + ['user_id' => $id, 'company_id' => $company_id];
                }, $input['data']);
                UserPermission::query()->where('user_id', $id)->delete();

                $permission = UserPermission::query()->insert($input['data']);
            } else {
                UserPermission::query()->where('user_id', $id)->delete();
            }
        }
        return response()->json(['success' => 'Team member created successfully!'], 201);
        // } catch (\Exception $e) {
        //    $bug = $e->getMessage();
        //    return redirect()->back()->with('error', $bug);
        // }
    }

    public function resentMail(Request $request)
    {
        $id = ($request->id) ? Crypt::decrypt($request->id) : $request->id;
        $users = User::where('id', $id)->first();
        $users->invite_user_name = Auth::user()->name;
        $mail_details = $users;
        \Mail::to($users->email)->send(new \App\Mail\InviteMail($mail_details));
        return response()->json(['success' => 'Mail Resend Successfully!'], 201);
    }

    public function verify_account($id)
    {
        try {
            $user = User::where('id', $id)->first();
            DB::connection('mysql')->table('tenants')->where('email', $user->email)->update(['invite_status' => 1]);
            User::where('id', $id)->update(['invite_status' => 1]);
            return redirect()->route('login');
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect()->back()->with('error', $bug);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Tenant $tenant)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tenant $tenant)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tenant $tenant)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(Tenant $tenant)
    // {
    //     //
    // }
}
