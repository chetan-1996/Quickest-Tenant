<?php

namespace App\Http\Controllers\API\V6;

use App\Http\Controllers\API\V6\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FollowUpHistoryCountController extends BaseController
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

    public function getTodayFollowupCount($fil_user_id, Request $request)
    {
        $leadStageId = $request->input('lead_stage_id');
        $leadLabelId = $request->input('lead_label_id');
        $leadSourceId = $request->input('lead_source_id');
        $leadCategoryId = $request->input('lead_category_id');
        $estimateStatus = $request->input('estimate_status');
        $cityName = $request->input('city_name');
        $stateId = $request->input('state_id');
        $countryId = $request->input('country_id');
        $leadStartDate = $request->input('lead_start_date');
        $leadEndDate = $request->input('lead_end_date');
        $leadStageIdArr = ($leadStageId) ? explode(",", $leadStageId) : [];
        $leadLabelIdArr = ($leadLabelId) ? explode(",", $leadLabelId) : [];
        $leadSourceIdArr = ($leadSourceId) ? explode(",", $leadSourceId) : [];
        $leadCategoryIdArr = ($leadCategoryId) ? explode(",", $leadCategoryId) : [];
        $estimateStatusArr = ($estimateStatus) ? explode(",", $estimateStatus) : [];

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.id')
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 0)
            ->where(function ($query) use ($fil_user_id, $leadStageIdArr, $leadLabelIdArr, $cityName,$stateId,$countryId,$leadSourceIdArr,$leadCategoryIdArr,$estimateStatusArr,$leadStartDate,$leadEndDate) {
                if (!empty($leadStageIdArr) && count($leadStageIdArr) > 0) {
                    $query->WhereIn('cv.lead_stage_id', $leadStageIdArr);
                }
                if (!empty($leadSourceIdArr) && count($leadSourceIdArr) > 0) {
                    $query->WhereIn('cv.customer_lead_id', $leadSourceIdArr);
                }
                if (!empty($leadCategoryIdArr) && count($leadCategoryIdArr) > 0) {
                    $query->WhereIn('cv.customer_category_id', $leadCategoryIdArr);
                }
                if (!empty($leadLabelIdArr) && count($leadLabelIdArr) > 0) {
                    $query->WhereIn('lg.id', $leadLabelIdArr);
                }
                if (!empty($cityName)) {
                    $query->Where('cv.city_name', $cityName);
                }
                if (!empty($stateId)) {
                    $query->Where('cv.state_id', $stateId);
                }
                if (!empty($countryId)) {
                    $query->Where('cv.country_id', $countryId);
                }
                if (!empty($estimateStatusArr) && count($estimateStatusArr) > 0) {
                    $query->WhereIn('cv.estimate_status',$estimateStatusArr);
                }
                if($leadStartDate && $leadEndDate) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(cv.created_at, '%Y-%m-%d')"), [$leadStartDate, $leadEndDate]);
                }
                if ($fil_user_id > 0) {
                    $query->where('cv.assigned_to_user', '=', $fil_user_id);
                }
            })
            ->whereRaw('DATE(cv.last_follow_up_datetime) = ?', [date('Y-m-d')])
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id); CMX
                }
            })
            ->distinct('cv.id')
            ->orderBy('cv.last_follow_up_datetime', 'ASC')
            ->count();



        if (is_null($records)) {
            return $this->sendError('Follow up not found', ['Lead not found'], 422);
        }
        return $this->sendResponse($records, 'Follow up retrieved successfully');
    }

    public function getUpcomingFollowupCount($fil_user_id, Request $request)
    {
        $leadStageId = $request->input('lead_stage_id');
        $leadLabelId = $request->input('lead_label_id');
        $leadSourceId = $request->input('lead_source_id');
        $leadCategoryId = $request->input('lead_category_id');
        $estimateStatus = $request->input('estimate_status');
        $cityName = $request->input('city_name');
        $stateId = $request->input('state_id');
        $countryId = $request->input('country_id');
        $leadStartDate = $request->input('lead_start_date');
        $leadEndDate = $request->input('lead_end_date');
        $leadStageIdArr = ($leadStageId) ? explode(",", $leadStageId) : [];
        $leadLabelIdArr = ($leadLabelId) ? explode(",", $leadLabelId) : [];
        $leadSourceIdArr = ($leadSourceId) ? explode(",", $leadSourceId) : [];
        $leadCategoryIdArr = ($leadCategoryId) ? explode(",", $leadCategoryId) : [];
        $estimateStatusArr = ($estimateStatus) ? explode(",", $estimateStatus) : [];

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.id')
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 0)
            ->where(function ($query) use ($fil_user_id, $leadStageIdArr, $leadLabelIdArr, $cityName,$stateId,$countryId,$leadSourceIdArr,$leadCategoryIdArr,$estimateStatusArr,$leadStartDate,$leadEndDate) {
                if (!empty($leadStageIdArr) && count($leadStageIdArr) > 0) {
                    $query->WhereIn('cv.lead_stage_id', $leadStageIdArr);
                }
                if (!empty($leadSourceIdArr) && count($leadSourceIdArr) > 0) {
                    $query->WhereIn('cv.customer_lead_id', $leadSourceIdArr);
                }
                if (!empty($leadCategoryIdArr) && count($leadCategoryIdArr) > 0) {
                    $query->WhereIn('cv.customer_category_id', $leadCategoryIdArr);
                }
                if (!empty($leadLabelIdArr) && count($leadLabelIdArr) > 0) {
                    $query->WhereIn('lg.id', $leadLabelIdArr);
                }
                if (!empty($cityName)) {
                    $query->Where('cv.city_name', $cityName);
                }
                if (!empty($stateId)) {
                    $query->Where('cv.state_id', $stateId);
                }
                if (!empty($countryId)) {
                    $query->Where('cv.country_id', $countryId);
                }
                if (!empty($estimateStatusArr) && count($estimateStatusArr) > 0) {
                    $query->WhereIn('cv.estimate_status',$estimateStatusArr);
                }
                if($leadStartDate && $leadEndDate) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(cv.created_at, '%Y-%m-%d')"), [$leadStartDate, $leadEndDate]);
                }
                if ($fil_user_id > 0) {
                    $query->where('cv.assigned_to_user', '=', $fil_user_id);
                }
            })
            ->where(DB::raw('DATE(cv.last_follow_up_datetime)'),">",date('Y-m-d'))
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id); CMX
                }
            })
            ->distinct('cv.id')
            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->count();

        return $this->sendResponse(["count" => $records], 'Follow up retrieved successfully');
    }

    public function getOverdueFollowupCount($fil_user_id, Request $request)
    {
        $leadStageId = $request->input('lead_stage_id');
        $leadLabelId = $request->input('lead_label_id');
        $leadSourceId = $request->input('lead_source_id');
        $leadCategoryId = $request->input('lead_category_id');
        $estimateStatus = $request->input('estimate_status');
        $cityName = $request->input('city_name');
        $stateId = $request->input('state_id');
        $countryId = $request->input('country_id');
        $leadStartDate = $request->input('lead_start_date');
        $leadEndDate = $request->input('lead_end_date');
        $leadStageIdArr = ($leadStageId) ? explode(",", $leadStageId) : [];
        $leadLabelIdArr = ($leadLabelId) ? explode(",", $leadLabelId) : [];
        $leadSourceIdArr = ($leadSourceId) ? explode(",", $leadSourceId) : [];
        $leadCategoryIdArr = ($leadCategoryId) ? explode(",", $leadCategoryId) : [];
        $estimateStatusArr = ($estimateStatus) ? explode(",", $estimateStatus) : [];

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.id')
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 0)
            ->where(function ($query) use ($fil_user_id, $leadStageIdArr, $leadLabelIdArr, $cityName,$stateId,$countryId,$leadSourceIdArr,$leadCategoryIdArr,$estimateStatusArr,$leadStartDate,$leadEndDate) {
                if (!empty($leadStageIdArr) && count($leadStageIdArr) > 0) {
                    $query->WhereIn('cv.lead_stage_id', $leadStageIdArr);
                }
                if (!empty($leadSourceIdArr) && count($leadSourceIdArr) > 0) {
                    $query->WhereIn('cv.customer_lead_id', $leadSourceIdArr);
                }
                if (!empty($leadCategoryIdArr) && count($leadCategoryIdArr) > 0) {
                    $query->WhereIn('cv.customer_category_id', $leadCategoryIdArr);
                }
                if (!empty($leadLabelIdArr) && count($leadLabelIdArr) > 0) {
                    $query->WhereIn('lg.id', $leadLabelIdArr);
                }
                if (!empty($cityName)) {
                    $query->Where('cv.city_name', $cityName);
                }
                if (!empty($stateId)) {
                    $query->Where('cv.state_id', $stateId);
                }
                if (!empty($countryId)) {
                    $query->Where('cv.country_id', $countryId);
                }
                if (!empty($estimateStatusArr) && count($estimateStatusArr) > 0) {
                    $query->WhereIn('cv.estimate_status',$estimateStatusArr);
                }
                if($leadStartDate && $leadEndDate) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(cv.created_at, '%Y-%m-%d')"), [$leadStartDate, $leadEndDate]);
                }
                if ($fil_user_id > 0) {
                    $query->where(function ($query) use ($fil_user_id) {
                        $query->where('cv.assigned_to_user', '=', $fil_user_id);
//                        $query->orwhere('cv.user_id', '=', $fil_user_id); CMX
                    });
                    //$query->where('cv.assigned_to_user', '=', $fil_user_id);
                }
            })
            ->where(DB::raw('DATE(cv.last_follow_up_datetime)'),"<",date('Y-m-d'))
            ->where('cv.last_follow_up_datetime','!=','0000-00-00 00:00:00')
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                }
            })
            ->distinct('cv.id')
            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->count();

        return $this->sendResponse(["count" => $records], 'Follow up retrieved successfully');
    }

    public function getSomedayFollowupCount($fil_user_id, Request $request)
    {
        $leadStageId = $request->input('lead_stage_id');
        $leadLabelId = $request->input('lead_label_id');
        $leadSourceId = $request->input('lead_source_id');
        $estimateStatus = $request->input('estimate_status');
        $cityName = $request->input('city_name');
        $stateId = $request->input('state_id');
        $countryId = $request->input('country_id');
        $leadStartDate = $request->input('lead_start_date');
        $leadEndDate = $request->input('lead_end_date');
        $leadStageIdArr = ($leadStageId) ? explode(",", $leadStageId) : [];
        $leadLabelIdArr = ($leadLabelId) ? explode(",", $leadLabelId) : [];
        $leadSourceIdArr = ($leadSourceId) ? explode(",", $leadSourceId) : [];
        $estimateStatusArr = ($estimateStatus) ? explode(",", $estimateStatus) : [];

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.id',)
            ->where('cv.company_id', $this->company_id)
            ->where('cv.some_day_flg', 1)
            ->where(function ($query) use ($fil_user_id, $leadStageIdArr, $leadLabelIdArr, $cityName,$stateId,$countryId,$leadSourceIdArr,$estimateStatusArr,$leadStartDate,$leadEndDate) {
                if (!empty($leadStageIdArr) && count($leadStageIdArr) > 0) {
                    $query->WhereIn('cv.lead_stage_id', $leadStageIdArr);
                }
                if (!empty($leadSourceIdArr) && count($leadSourceIdArr) > 0) {
                    $query->WhereIn('cv.customer_lead_id', $leadSourceIdArr);
                }
                if (!empty($leadLabelIdArr) && count($leadLabelIdArr) > 0) {
                    $query->WhereIn('lg.id', $leadLabelIdArr);
                }
                if (!empty($cityName)) {
                    $query->Where('cv.city_name', $cityName);
                }
                if (!empty($stateId)) {
                    $query->Where('cv.state_id', $stateId);
                }
                if (!empty($countryId)) {
                    $query->Where('cv.country_id', $countryId);
                }
                if (!empty($estimateStatusArr) && count($estimateStatusArr) > 0) {
                    $query->WhereIn('cv.estimate_status',$estimateStatusArr);
                }
                if($leadStartDate && $leadEndDate) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(cv.created_at, '%Y-%m-%d')"), [$leadStartDate, $leadEndDate]);
                }
                if ($fil_user_id > 0) {
                    $query->where('cv.assigned_to_user', '=', $fil_user_id);
                }
            })
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id); CMX
                }
            })
            ->distinct('cv.id')
            ->orderBy('cv.created_at', 'DESC')
            ->count();

        return $this->sendResponse(["count" => $records], 'Follow up retrieved successfully');
    }

    public function getNeverFollowupCount($fil_user_id, Request $request)
    {
        $leadStageId = $request->input('lead_stage_id');
        $leadLabelId = $request->input('lead_label_id');
        $leadSourceId = $request->input('lead_source_id');
        $leadCategoryId = $request->input('lead_category_id');
        $estimateStatus = $request->input('estimate_status');
        $cityName = $request->input('city_name');
        $stateId = $request->input('state_id');
        $countryId = $request->input('country_id');
        $leadStartDate = $request->input('lead_start_date');
        $leadEndDate = $request->input('lead_end_date');
        $leadStageIdArr = ($leadStageId) ? explode(",", $leadStageId) : [];
        $leadLabelIdArr = ($leadLabelId) ? explode(",", $leadLabelId) : [];
        $leadSourceIdArr = ($leadSourceId) ? explode(",", $leadSourceId) : [];
        $leadCategoryIdArr = ($leadCategoryId) ? explode(",", $leadCategoryId) : [];
        $estimateStatusArr = ($estimateStatus) ? explode(",", $estimateStatus) : [];

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.id')
            ->where('cv.company_id', $this->company_id)
//            ->where('cv.some_day_flg', 0)
            ->where(function ($query) use ($fil_user_id, $leadStageIdArr, $leadLabelIdArr, $cityName,$stateId,$countryId,$leadSourceIdArr,$leadCategoryIdArr,$estimateStatusArr,$leadStartDate,$leadEndDate) {
                if (!empty($leadStageIdArr) && count($leadStageIdArr) > 0) {
                    $query->WhereIn('cv.lead_stage_id', $leadStageIdArr);
                }
                if (!empty($leadSourceIdArr) && count($leadSourceIdArr) > 0) {
                    $query->WhereIn('cv.customer_lead_id', $leadSourceIdArr);
                }
                if (!empty($leadCategoryIdArr) && count($leadCategoryIdArr) > 0) {
                    $query->WhereIn('cv.customer_category_id', $leadCategoryIdArr);
                }

                if (!empty($leadLabelIdArr) && count($leadLabelIdArr) > 0) {
                    $query->WhereIn('lg.id', $leadLabelIdArr);
                }
                if (!empty($cityName)) {
                    $query->Where('cv.city_name', $cityName);
                }
                if (!empty($stateId)) {
                    $query->Where('cv.state_id', $stateId);
                }
                if (!empty($countryId)) {
                    $query->Where('cv.country_id', $countryId);
                }
                if (!empty($estimateStatusArr) && count($estimateStatusArr) > 0) {
                    $query->WhereIn('cv.estimate_status',$estimateStatusArr);
                }
                if($leadStartDate && $leadEndDate) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(cv.created_at, '%Y-%m-%d')"), [$leadStartDate, $leadEndDate]);
                }
                if ($fil_user_id > 0) {
                    /*$query->where(function ($query) use ($assigned_to_user, $user_perm) {
                        $query->where('customers_views.assigned_to_user', '=', $assigned_to_user);
                        $query->orwhere('customers_views.user_id', '=', $assigned_to_user);
                    });*/
                    $query->where('cv.assigned_to_user', '=', $fil_user_id);
                }
            })
            ->where(function ($query) use ($user_perm) {
                $query->where('cv.last_follow_up_datetime','=','0000-00-00 00:00:00');
                $query->orWhereNull('cv.last_follow_up_datetime');
            })
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
//                    $query->orwhere('cv.user_id', '=', $this->logged_user->id); CMX
                }
            })
            ->distinct('cv.id')
            ->orderBy('cv.created_at', 'DESC')
            ->count();

        return $this->sendResponse(["count" => $records], 'Follow up retrieved successfully');
    }
}
