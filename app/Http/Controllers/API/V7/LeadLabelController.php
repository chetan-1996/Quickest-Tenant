<?php

namespace App\Http\Controllers\API\V6;

use App\Http\Controllers\API\V6\BaseController as BaseController;
use App\Models\LeadGroup;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use LogActivity;

class LeadLabelController extends BaseController
{
    protected $logged_user = null;
    protected $company_id = 0;
    protected $user_param = 0;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->logged_user = \Illuminate\Support\Facades\Auth::user();
            $this->company_id = ($this->logged_user->company_id) ? $this->logged_user->company_id : $this->logged_user->id;
            $this->user_param = \App\Helpers\PermissionCheck::check_permission('role-list');
            return $next($request);
        });
    }

    public function index($fil_user_id)
    {
        /*$data = LeadGroup::where('company_id', $this->company_id)
            ->select('*')
            ->orderBy('id', 'desc')
            ->get();*/
//        $userId = 594; // The user_id value you want to use for the WHERE clause

//        DB::enableQueryLog();
        $data = LeadGroup::select('lead_groups.*', DB::raw('COALESCE(cl.lead_count, 0) as lead_count'))
            ->leftJoinSub(function ($query) use ($fil_user_id) {
                $query->select('label_id', DB::raw('COUNT(*) as lead_count'))
                    ->from('customer_labels')
                    ->leftJoin('customers', 'customers.id', '=', 'customer_labels.customer_id')
                    ->where(function ($subquery) use ($fil_user_id) {
                        if ($fil_user_id > 0) {
//                            $subquery->where('customers.assigned_to_user', '=', $fil_user_id);
                            $subquery->where('customers.assigned_to_user', '=', $fil_user_id);
                            if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_param) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_param)) {
                                $subquery->orwhere('customers.user_id', '=', $fil_user_id);
                            }
                        }
                    })
                    ->where(function ($subquery) {
                        if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_param) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_param)) {
                            $subquery->where('customers.assigned_to_user', '=', $this->logged_user->id);
                            $subquery->orwhere('customers.user_id', '=', $this->logged_user->id);
//                    $subquery->orwhere('users.id', '=', $this->company_id);
                        }

                        if (in_array('access-all-lead-and-assign-to-anyone-in-team', $this->user_param)) {
                            if (in_array('give-access-to-attend-unassigned-leads', $this->user_param)) {

                                $subquery->where('customers.company_id', $this->company_id);
                                $subquery->orwhere('customers.assigned_to_user', '=', 0);
                            } else {
                                $subquery->where('customers.assigned_to_user', '!=', 0);
                            }
                        } else {
                            if (in_array('give-access-to-attend-unassigned-leads', $this->user_param)) {
                                $subquery->orwhere('customers.assigned_to_user', '=', 0);
                            }
                        }
                    })
                    /*->where(function ($subquery) use ($userId) {
                        $subquery->where('customers.assigned_to_user', $userId)
                            ->orWhere('customers.user_id', $userId)
                            ->orWhere('customers.assigned_to_user', 0);
                    })*/
                    ->groupBy('label_id');
            }, 'cl', function ($join) {
                $join->on('lead_groups.id', '=', 'cl.label_id');
            })
            ->where('lead_groups.company_id', $this->company_id)
            ->where('lead_groups.status', 0)
            ->get();

//dd(DB::getQueryLog($data));




        /*$data = DB::table('lead_groups')
            ->leftJoin('customer_labels', 'lead_groups.id', '=', 'customer_labels.label_id')
            ->leftJoin('customers', 'customers.id', '=', 'customer_labels.customer_id')
            ->select('lead_groups.*', DB::raw('count(customer_labels.id) as lead_count'))
            ->where('lead_groups.company_id', $this->company_id)
            ->where(function ($query) use ($fil_user_id) {
                if ($fil_user_id > 0) {
                    $query->where('customer_labels.user_id', '=', $fil_user_id);
                }
            })
            ->where(function ($query) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $this->user_param) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $this->user_param)) {
                    $query->where('customers.assigned_to_user', '=', $this->logged_user->id);
                    $query->orwhere('customers.user_id', '=', $this->logged_user->id);
//                    $query->orwhere('users.id', '=', $this->company_id);
                }

                if (in_array('access-all-lead-and-assign-to-anyone-in-team', $this->user_param)) {
                    if (in_array('give-access-to-attend-unassigned-leads', $this->user_param)) {

                        $query->where('customers.company_id', $this->company_id);
                        $query->orwhere('customers.assigned_to_user', '=', 0);
                    } else {
                        $query->where('customers.assigned_to_user', '!=', 0);
                    }
                } else {
                    if (in_array('give-access-to-attend-unassigned-leads', $this->user_param)) {
                        $query->orwhere('customers.assigned_to_user', '=', 0);
                    }
                }
            })
            ->where('lead_groups.status', 0)
            ->groupBy('lead_groups.id')
            ->get();
dd(DB::getQueryLog($data));*/
        if (is_null($data)) {
            return $this->sendError('Label not found', ['Label not found'], 422);
        }
        return $this->sendResponse($data, 'Label retrieved successfully');
    }

    public function show($id)
    {
        $LeadGroup = LeadGroup::query()->find($id);
        if (is_null($LeadGroup)) {
            return $this->sendError('Label not found', ['error' => 'Label not found!'], 422);
        }
        return $this->sendResponse($LeadGroup, 'Label retrieved successfully');
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
            'color_code' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }
        $id = $input['id'];
        if (LeadGroup::query()->where([['name', '=', $input['name']],['company_id','=', $this->company_id]])->select('id')->where(function ($query) use ($id) {
            if ($id !== 0) {
                $query->Where(function ($query) use ($id) {
                    $query->where('id', '!=', $id);
                });
            }
        })->first()) {
            return $this->sendError('Label exists', ['error' => 'Label exists'], 409);
        }

        $input['user_id'] = $this->logged_user->id;
        $input['company_id'] = $this->company_id;

        if ($id == 0) {
            $activityLogMsg = 'Label created by ' . $this->logged_user->name;
            $logInput['internal_remarks'] = "Label added";
            $leadGroup = LeadGroup::create($input);
        } else {
            $customer = LeadGroup::find($id)->update($input);
            $activityLogMsg = 'Label updated by ' . $this->logged_user->name;
        }

        // Add activity logs
        LogActivity::addToLog($activityLogMsg, $input);

        return $this->sendResponse([], 'Label Saved');
    }

    public function destroy(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }
        $id = [];
        foreach (explode(",", $request->id) as $value) {
            $id[] = $value;
        }
        $customer = LeadGroup::whereIn('id', $id)->delete();

        LogActivity::addToLog('Label deleted by ' . $this->logged_user->name, $id);
        return $this->sendResponse([], 'Label Deleted!');
    }
}
