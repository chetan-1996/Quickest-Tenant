<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Exports\CustomersExport;
use App\Exports\CustomerExport;
use App\Imports\CustomerLeadPreview;
use App\Models\Country;
use App\Models\Customer;
use App\Models\CustomerCategory;
use App\Models\CustomerLabel;
use App\Models\CustomerLead;
use App\Models\Estimate;
use App\Models\EstimateTimeline;
use App\Models\Lead_assign_users;
use App\Models\LeadGroup;
use App\Models\LeadStage;
use App\Models\LostReason;
use App\Models\ProposalTemplates;
use App\Models\SalesPersonPerformances;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\ViewCustomerData;
use App\Models\State;
use App\Models\admin\LeadHistory;
use App\Models\admin\EstimateHistory;
use App\Models\admin\AttachmentHistory;
use Auth;
use Carbon\Carbon;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Helpers\LogActivity;
use League\Flysystem\Filesystem;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use Illuminate\Pagination\Paginator;

class CustomerController extends Controller
{
    protected $logged_user = null;
    protected $main_company = null;
    protected $company_id = 0;
    protected $user_perm = 0;
    protected $segment = null;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->logged_user = \Illuminate\Support\Facades\Auth::user();
            $this->company_id = ($this->logged_user->company_id) ? $this->logged_user->company_id : $this->logged_user->id;
            $this->main_company = User::select(["follow_up_note_req_flg","company_category"])
                ->where('id', $this->company_id)->first();
            $this->user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
            $this->segment = $request->segment(1);
            return $next($request);
        });
    }

    public function customerindex(Request $request)
    {

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
        if ($request->ajax()) {
            $input = $request->all();
            ## Read value
            $draw = $request->post('draw');
            $start = $request->post("start");
            $rowperpage = $request->post("length"); // Rows display per page

            $columnIndex_arr = $request->post('order');
            $columnName_arr = $request->post('columns');
            $order_arr = $request->post('order');
            $search_arr = $request->post('search');

            $columnIndex = $columnIndex_arr[0]['column']; // Column index
            $columnName = $columnName_arr[$columnIndex]['data']; // Column name
            $columnSortOrder = $order_arr[0]['dir']; // asc or desc

            if ($columnName == 'last_activity') {
                $columnName = 'cv.last_activity_updated_at';
            }

            // Fetch records
            $name = $request->post('name');
            $status = [];
            if ($request->post('status'))
                $status = explode(",", $request->post('status'));

            $fil_lead_stage_id = $request->post('fil_lead_stage_id');

            $assigned_to_user = $request->post('assigned_to_user');
            $customer_type = $request->post('customer_type');
            $fil_customer_category_id = $request->post('fil_customer_category_id');
            $fil_customer_lead_id = $request->post('fil_customer_lead_id');
            $fil_created_user_id = $request->post('fil_created_user_id');
            $fil_estimate_status_id = $request->post('fil_estimate_status_id');
            $fil_country_id = $request->post('fil_country_id');
            $fil_state_id = $request->post('fil_state_id');
            $fil_city_name = $request->post('fil_city_name');
            $dashboard_lead_filter = $request->post('dashboard_lead_filter');
            $opr_id_2 = ($request->post('opr_id_2')) ? explode(',', $request->post('opr_id_2')) : [];
            $popr_id_1 = ($request->post('popr_id_1')) ? explode(',', $request->post('popr_id_1')) : [];
            $popr_id_3 = ($request->post('popr_id_3')) ? explode(',', $request->post('popr_id_3')) : [];
            $popr_id_4 = ($request->post('popr_id_4')) ? explode(',', $request->post('popr_id_4')) : [];
            $ropr_id_1 = ($request->post('ropr_id_1')) ? explode(',', $request->post('ropr_id_1')) : [];
            $ropr_id_2 = ($request->post('ropr_id_2')) ? explode(',', $request->post('ropr_id_2')) : [];
            $ropr_id_3 = ($request->post('ropr_id_3')) ? explode(',', $request->post('ropr_id_3')) : [];
            $ropr_id_4 = ($request->post('ropr_id_4')) ? explode(',', $request->post('ropr_id_4')) : [];
            $ropr_id_5 = ($request->post('ropr_id_5')) ? explode(',', $request->post('ropr_id_5')) : [];
            $q =$request->post('q');
            // Total records
            $counts = ViewCustomerData::select('customers_views.id')
                ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'customers_views.id')
                ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
                ->where(function ($query) use ($input) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(customers_views.created_at, '%Y-%m-%d')"), [$input['fil_lead_date_start'], $input['fil_lead_date_end']]);
                })
                ->where(function ($query) use ($status, $assigned_to_user, $user_perm, $fil_customer_category_id, $fil_customer_lead_id, $fil_created_user_id, $fil_estimate_status_id, $fil_lead_stage_id, $fil_country_id, $fil_state_id, $fil_city_name,$dashboard_lead_filter,$opr_id_2, $popr_id_1, $popr_id_3, $popr_id_4, $ropr_id_1, $ropr_id_2, $ropr_id_3, $ropr_id_4, $ropr_id_5,$q) {
                    if ($fil_country_id) {
                        $query->where('customers_views.country_id', $fil_country_id);
                    }
                    if ($fil_state_id) {
                        $query->where('customers_views.state_id', $fil_state_id);
                    }
                    if ($fil_city_name) {
                        $query->where('customers_views.city_name', $fil_city_name);
                    }
                    if ($fil_lead_stage_id) {
                        $query->where('customers_views.lead_stage_id', $fil_lead_stage_id);
                    }
                    if ($status) {
                        $query->WhereIn('lg.id', $status);
                    }
                    if ($fil_customer_category_id != '') {
                        $query->where('customers_views.customer_category_id', '=', $fil_customer_category_id);
                    }
                    if ($fil_customer_lead_id != '') {
                        $query->where('customers_views.customer_lead_id', '=', $fil_customer_lead_id);
                    }
                    if ($fil_created_user_id != '') {
                        $query->where('customers_views.user_id', '=', $fil_created_user_id);
                    }
                    if ($fil_estimate_status_id != '') {
                        $query->where('customers_views.estimate_status', '=', $fil_estimate_status_id);
                    }

                    /*if ($assigned_to_user > 0) {
                        $query->where('customers_views.assigned_to_user', '=', $assigned_to_user);
                    }*/
                    if ($assigned_to_user > 0) {
                        if (!$q) {
                            $query->where(function ($query) use ($assigned_to_user, $user_perm, $dashboard_lead_filter) {
                                $query->where('customers_views.assigned_to_user', '=', $assigned_to_user);
                                //if(!$dashboard_lead_filter)
                                //$query->orwhere('customers_views.user_id', '=', $assigned_to_user);
                            });
                        }
                    }
                })
                ->where(function ($query) use ($user_perm) {
                    // if (!in_array('give-access-to-attend-unassigned-leads', $user_perm)) {
                    $query->where('customers_views.company_id', $this->company_id);
                    // }
                })
                ->where(function ($query) use ($user_perm,$opr_id_2, $popr_id_1, $popr_id_3, $popr_id_4, $ropr_id_1, $ropr_id_2, $ropr_id_3, $ropr_id_4, $ropr_id_5,$q) {
                    if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                        if (!$q) {
                            $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
                            // $query->orwhere('customers_views.user_id', '=', $this->logged_user->id);
                        }else{
                            $query->where('customers_views.company_id', $this->company_id);
                        }
                    }

                    if(!$q) {
                        if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm)) {
                            if (in_array('give-access-to-attend-unassigned-leads', $user_perm)) {

                                $query->where('customers_views.company_id', $this->company_id);
                                $query->orwhere('customers_views.assigned_to_user', '=', 0);
                            } else {
                                $query->where('customers_views.assigned_to_user', '!=', 0);
                            }
                        } else {
                            if (in_array('give-access-to-attend-unassigned-leads', $user_perm)) {
                                $query->orwhere('customers_views.assigned_to_user', '=', 0);
                            }
                        }
                    }
                })
                ->where(function ($query) use ($opr_id_2, $popr_id_1, $popr_id_3, $popr_id_4, $ropr_id_1, $ropr_id_2, $ropr_id_3, $ropr_id_4, $ropr_id_5) {
                    if ($opr_id_2) {
                        $query->wherein('customers_views.id', $opr_id_2);
                    }
                    if ($popr_id_1) {
                        $query->wherein('customers_views.id', $popr_id_1);
                    }
                    if ($popr_id_3) {
                        $query->wherein('customers_views.id', $popr_id_3);
                    }
                    if ($popr_id_4) {
                        $query->wherein('customers_views.id', $popr_id_4);
                    }
                    if ($ropr_id_1) {
                        $query->wherein('customers_views.id', $ropr_id_1);
                    }
                    if ($ropr_id_2) {
                        $query->wherein('customers_views.id', $ropr_id_2);
                    }
                    if ($ropr_id_3) {
                        $query->wherein('customers_views.id', $ropr_id_3);
                    }
                    if ($ropr_id_5) {
                        $query->wherein('customers_views.id', $ropr_id_5);
                    }
                    if ($ropr_id_4) {
                        $query->wherein('customers_views.id', $ropr_id_4);
                    }
                })
                /* ->where(function ($query) use ($search_arr) {
                     $query->orWhere(function ($query) use ($search_arr) {
                         $query->where('customers_views.name', 'like', '%' . $search_arr . '%');
                     });

                     $query->orWhere(function ($query) use ($search_arr) {
                         $query->where('customers_views.company_name', 'like', '%' . $search_arr . '%');
                     });

                     $query->orWhere(function ($query) use ($search_arr) {
                         $query->where('customers_views.phone_no', 'like', '%' . $search_arr . '%');
                     });

                     $query->orWhere(function ($query) use ($search_arr) {
                         $query->where('customers_views.lead_category', 'like', '%' . $search_arr . '%');
                     });

                     $query->orWhere(function ($query) use ($search_arr) {
                         $query->where('customers_views.lead_origin', 'like', '%' . $search_arr . '%');
                     });

                 })*/
                ->groupBy('customers_views.id')
                ->get();

            $totalRecords = $counts->count();

            if($search_arr != null) {
                $countswithFilter = ViewCustomerData::select('customers_views.id')
                    ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'customers_views.id')
                    ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
                    ->where(function ($query) use ($input) {
                        $query->whereBetween(DB::raw("DATE_FORMAT(customers_views.created_at, '%Y-%m-%d')"), [$input['fil_lead_date_start'], $input['fil_lead_date_end']]);
                    })
                    ->where(function ($query) use ($status, $assigned_to_user, $user_perm, $fil_customer_category_id, $fil_customer_lead_id, $fil_created_user_id, $fil_estimate_status_id, $fil_lead_stage_id, $fil_country_id, $fil_state_id, $fil_city_name,$dashboard_lead_filter,$opr_id_2, $popr_id_1, $popr_id_3, $popr_id_4, $ropr_id_1, $ropr_id_2, $ropr_id_3, $ropr_id_4, $ropr_id_5,$q) {
                        if ($fil_country_id) {
                            $query->where('customers_views.country_id', $fil_country_id);
                        }
                        if ($fil_state_id) {
                            $query->where('customers_views.state_id', $fil_state_id);
                        }
                        if ($fil_city_name) {
                            $query->where('customers_views.city_name', $fil_city_name);
                        }
                        if ($fil_lead_stage_id) {
                            $query->where('customers_views.lead_stage_id', $fil_lead_stage_id);
                        }
                        if ($status) {
                            $query->WhereIn('lg.id', $status);
                        }
                        if ($fil_customer_category_id != '') {
                            $query->where('customers_views.customer_category_id', '=', $fil_customer_category_id);
                        }
                        if ($fil_customer_lead_id != '') {
                            $query->where('customers_views.customer_lead_id', '=', $fil_customer_lead_id);
                        }
                        if ($fil_created_user_id != '') {
                            $query->where('customers_views.user_id', '=', $fil_created_user_id);
                        }
                        if ($fil_estimate_status_id != '') {
                            $query->where('customers_views.estimate_status', '=', $fil_estimate_status_id);
                        }
                        if ($assigned_to_user > 0) {
                        if (!$q) {
                                $query->where(function ($query) use ($assigned_to_user, $user_perm, $dashboard_lead_filter) {
                                    $query->where('customers_views.assigned_to_user', '=', $assigned_to_user);
                                    //if(!$dashboard_lead_filter)
                                    //$query->orwhere('customers_views.user_id', '=', $assigned_to_user);
                                });
                            }
                        }
                    })
                    ->where(function ($query) use ($user_perm) {
                        // if (!in_array('give-access-to-attend-unassigned-leads', $user_perm)) {
                        $query->where('customers_views.company_id', $this->company_id);
                        // }
                    })
                    ->where(function ($query) use ($user_perm,$opr_id_2, $popr_id_1, $popr_id_3, $popr_id_4, $ropr_id_1, $ropr_id_2, $ropr_id_3, $ropr_id_4, $ropr_id_5,$q) {
                        if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                            if (!$q) {
                                $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
                                // $query->orwhere('customers_views.user_id', '=', $this->logged_user->id);
                            }else{
                                $query->where('customers_views.company_id', $this->company_id);
                            }
                        }

                        if (!$q) {
                            if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm)) {
                                if (in_array('give-access-to-attend-unassigned-leads', $user_perm)) {

                                    $query->where('customers_views.company_id', $this->company_id);
                                    $query->orwhere('customers_views.assigned_to_user', '=', 0);
                                } else {
                                    $query->where('customers_views.assigned_to_user', '!=', 0);
                                }
                            } else {
                                if (in_array('give-access-to-attend-unassigned-leads', $user_perm)) {
                                    $query->orwhere('customers_views.assigned_to_user', '=', 0);
                                }
                            }
                        }
                    })
                    ->where(function ($query) use ($search_arr) {
                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('customers_views.name', 'like', '%' . $search_arr . '%');
                        });

                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('customers_views.company_name', 'like', '%' . $search_arr . '%');
                        });

                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('customers_views.phone_no', 'like', '%' . $search_arr . '%');
                        });

                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('customers_views.address', 'like', '%' . $search_arr . '%');
                        });

                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('customers_views.city_name', 'like', '%' . $search_arr . '%');
                        });

                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('customers_views.lead_category', 'like', '%' . $search_arr . '%');
                        });

                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('customers_views.lead_origin', 'like', '%' . $search_arr . '%');
                        });
                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('customers_views.lead_stage_name', 'like', '%' . $search_arr . '%');
                        });
                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->whereRaw("CONCAT(customers_views.estimate_no, '-V',customers_views.estimate_version) LIKE ?", ['%'.$search_arr.'%']);
                        });

                    })
                    ->where(function ($query) use ($opr_id_2, $popr_id_1, $popr_id_3, $popr_id_4, $ropr_id_1, $ropr_id_2, $ropr_id_3, $ropr_id_4, $ropr_id_5) {
                        if ($opr_id_2) {
                            $query->wherein('customers_views.id', $opr_id_2);
                        }
                        if ($popr_id_1) {
                            $query->wherein('customers_views.id', $popr_id_1);
                        }
                        if ($popr_id_3) {
                            $query->wherein('customers_views.id', $popr_id_3);
                        }
                        if ($popr_id_4) {
                            $query->wherein('customers_views.id', $popr_id_4);
                        }
                        if ($ropr_id_1) {
                            $query->wherein('customers_views.id', $ropr_id_1);
                        }
                        if ($ropr_id_2) {
                            $query->wherein('customers_views.id', $ropr_id_2);
                        }
                        if ($ropr_id_3) {
                            $query->wherein('customers_views.id', $ropr_id_3);
                        }
                        if ($ropr_id_5) {
                            $query->wherein('customers_views.id', $ropr_id_5);
                        }
                        if ($ropr_id_4) {
                            $query->wherein('customers_views.id', $ropr_id_4);
                        }
                    })
                    ->groupBy('customers_views.id')
                    ->get();

                $totalRecordswithFilter = $countswithFilter->count();
            } else {
                $totalRecordswithFilter = 0;
            }
            $rowperpage = ($rowperpage == -1) ? $totalRecords : $rowperpage;
            // DB::enableQueryLog();
            $records = DB::table('customers_views as cv')
                ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
                ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
                //->select('cv.*', DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code'), DB::raw('GROUP_CONCAT(lg.id) as label_id'))
                ->select('cv.updated_at', 'cv.country_code', 'cv.company_name', 'cv.created_at', 'cv.city_name', 'cv.state_name', 'cv.country_name', 'cv.status', 'cv.lead_category', 'cv.email', 'cv.address', 'cv.pincode', 'cv.description', 'cv.lead_origin', 'cv.customer_type', 'cv.id', 'cv.name', 'cv.phone_no', 'cv.last_activity', 'cv.user_name', 'cv.assigned_to_user', 'cv.net_amount', 'cv.estimate_status', 'cv.last_activity_type', 'cv.last_internal_remarks', 'cv.last_activity_name', 'cv.last_follow_up_datetime', 'cv.last_is_modified', 'cv.last_is_follow_up', 'cv.some_day_flg', 'cv.est_currency_id', 'cv.estimate_status', 'cv.last_activity_updated_at', 'cv.new_lead_flag', 'cv.lead_stage_name', 'cv.lead_stage_color_code', DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code'), DB::raw('GROUP_CONCAT(lg.id) as label_id'),'cv.estimate_no','cv.estimate_version')
                ->where(function ($query) use ($user_perm) {
                //    if (!in_array('give-access-to-attend-unassigned-leads', $user_perm)) {
                    $query->where('cv.company_id', $this->company_id);
                //    }
                })
                ->where(function ($query) use ($input) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(cv.created_at, '%Y-%m-%d')"), [$input['fil_lead_date_start'], $input['fil_lead_date_end']]);
                })
                ->where(function ($query) use ($status, $assigned_to_user, $user_perm, $fil_customer_category_id, $fil_customer_lead_id, $fil_created_user_id, $fil_estimate_status_id, $fil_lead_stage_id, $fil_country_id, $fil_state_id, $fil_city_name,$dashboard_lead_filter,$opr_id_2, $popr_id_1, $popr_id_3, $popr_id_4, $ropr_id_1, $ropr_id_2, $ropr_id_3, $ropr_id_4, $ropr_id_5,$q) {
                    if ($fil_country_id) {
                        $query->where('cv.country_id', $fil_country_id);
                    }
                    if ($fil_state_id) {
                        $query->where('cv.state_id', $fil_state_id);
                    }
                    if ($fil_city_name) {
                        $query->where('cv.city_name', $fil_city_name);
                    }
                    if ($fil_lead_stage_id) {
                        $query->where('cv.lead_stage_id', $fil_lead_stage_id);
                    }
                    if ($fil_customer_category_id != '') {
                        $query->where('cv.customer_category_id', '=', $fil_customer_category_id);
                    }
                    if ($fil_customer_lead_id != '') {
                        $query->where('cv.customer_lead_id', '=', $fil_customer_lead_id);
                    }
                    if ($fil_created_user_id != '') {
                        $query->where('cv.user_id', '=', $fil_created_user_id);
                    }
                    if ($fil_estimate_status_id != '') {
                        $query->where('cv.estimate_status', '=', $fil_estimate_status_id);
                    }
                    /*if ($status != '') {
                        $query->where('lg.id', '=', $status);
                    }*/
                    /*if ($assigned_to_user > 0) {
                        $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                    }*/
                    if ($assigned_to_user > 0) {
                        if (!$q) {
                            $query->where(function ($query) use ($assigned_to_user, $dashboard_lead_filter) {
                                $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                                //if(!$dashboard_lead_filter)
                                //$query->orwhere('cv.user_id', '=', $assigned_to_user);
                            });
                        }
                    }
                })
                ->where(function ($query) use ($user_perm,$opr_id_2, $popr_id_1, $popr_id_3, $popr_id_4, $ropr_id_1, $ropr_id_2, $ropr_id_3, $ropr_id_4, $ropr_id_5,$q) {
                    if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                        if (!$q) {
                            $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
                        //    $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                        }else{
                            $query->where('cv.company_id', $this->company_id);
                        }
                    }

                    if (!$q) {
                        if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm)) {
                            if (in_array('give-access-to-attend-unassigned-leads', $user_perm)) {

                                $query->where('cv.company_id', $this->company_id);
                                $query->orwhere('cv.assigned_to_user', '=', 0);
                            } else {
                                $query->where('cv.assigned_to_user', '!=', 0);
                            }
                        } else {
                            if (in_array('give-access-to-attend-unassigned-leads', $user_perm)) {
                                $query->orwhere('cv.assigned_to_user', '=', 0);
                            }
                        }
                    }
                })
                ->where(function ($query) use ($search_arr) {
                    if($search_arr !== null) {
                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('cv.name', 'like', '%' . $search_arr . '%');
                        });

                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('cv.company_name', 'like', '%' . $search_arr . '%');
                        });


                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('cv.phone_no', 'like', '%' . $search_arr . '%');
                        });

                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('cv.address', 'like', '%' . $search_arr . '%');
                        });

                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('cv.city_name', 'like', '%' . $search_arr . '%');
                        });

                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('cv.lead_category', 'like', '%' . $search_arr . '%');
                        });

                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('cv.lead_origin', 'like', '%' . $search_arr . '%');
                        });

                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('cv.lead_stage_name', 'like', '%' . $search_arr . '%');
                        });

                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->whereRaw("CONCAT(cv.estimate_no, '-V',cv.estimate_version) LIKE ?", ['%'.$search_arr.'%']);
                        });
                    }
                })
                ->where(function ($query) use ($opr_id_2, $popr_id_1, $popr_id_3, $popr_id_4, $ropr_id_1, $ropr_id_2, $ropr_id_3, $ropr_id_4, $ropr_id_5) {
                    if ($opr_id_2) {
                        $query->wherein('cv.id', $opr_id_2);
                    }
                    if ($popr_id_1) {
                        $query->wherein('cv.id', $popr_id_1);
                    }
                    if ($popr_id_3) {
                        $query->wherein('cv.id', $popr_id_3);
                    }
                    if ($popr_id_4) {
                        $query->wherein('cv.id', $popr_id_4);
                    }
                    if ($ropr_id_1) {
                        $query->wherein('cv.id', $ropr_id_1);
                    }
                    if ($ropr_id_2) {
                        $query->wherein('cv.id', $ropr_id_2);
                    }
                    if ($ropr_id_3) {
                        $query->wherein('cv.id', $ropr_id_3);
                    }
                    if ($ropr_id_5) {
                        $query->wherein('cv.id', $ropr_id_5);
                    }
                    if ($ropr_id_4) {
                        $query->wherein('cv.id', $ropr_id_4);
                    }
                })
                ->skip($start)
                ->take($rowperpage)
                ->orderBy($columnName, $columnSortOrder)
                ->groupBy('cv.id');
            if ($status) {
            //    $records = $records->havingRaw("FIND_IN_SET('$status', GROUP_CONCAT(lg.id)) > 0");
                $records = $records->WhereIn("lg.id", $status);
            }
            /* ->havingRaw(function ($query) use ($status) {
                 if($status){
                     $query->havingRaw("FIND_IN_SET('$status', GROUP_CONCAT(lg.id)) > 0");
                 }

             })*/
            $records = $records->get();

            //    dd(DB::getQueryLog($records));

            /*$records = ViewCustomerData::where('company_id', $this->company_id)
                ->where(function ($query) use ($user_perm) {
                    if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                        $query->where('assigned_to_user', '=', $this->logged_user->id);
                        $query->orwhere('user_id', '=', $this->logged_user->id);
                    }
                })
                ->where(function ($query) use ($search_arr) {
                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('name', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('phone_no', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('lead_category', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('lead_origin', 'like', '%' . $search_arr . '%');
                    });
                })
                ->select('*')
                ->skip($start)
                ->take($rowperpage)
                ->orderBy($columnName, $columnSortOrder)
                ->get();*/

            // dd(DB::getQueryLog());

            $data = array();
            $i = 0;
            foreach ($records as $record) {
                $country_data = [];
                // if($record->currency_name_country_id)
                if ($record->est_currency_id)
                    $country_data = Country::where("id", $record->est_currency_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->orderBy('id', 'DESC')->first();
                $id = Crypt::encrypt($record->id);
                $customer_type = $record->customer_type;
                $name = ucwords($record->name);
                $lead_origin = $record->lead_origin;
                $lead_category = $record->lead_category;
                $email = $record->email;
                $phone_no = $record->phone_no;
                $address = $record->address;
                $pincode = $record->pincode;
                $description = $record->description;
                $status = $record->status;
                $country_name = $record->country_name;
                $state_name = $record->state_name;
                $city_name = $record->city_name;
                $last_activity = $record->last_activity !== null ? nl2br(htmlentities($record->last_activity)) : '';
                $assign_user_name = $record->user_name;
                $assigned_to_user = $record->assigned_to_user;
                $net_amount = $record->net_amount;
                $estimate_status = $record->estimate_status;
                $last_activity_type = $record->last_activity_type;
                $last_internal_remarks = $record->last_internal_remarks;
                $last_activity_name = $record->last_activity_name;
                $date_added = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)
                    ->format('d-m-Y h:i A');
                $last_is_modified = $record->last_is_modified;
                $last_is_follow_up = $record->last_is_follow_up;
                $i++;
                $labelName = '';

                if ($record->label_name) {
                    $leadLabelNameArr = explode(',', $record->label_name);
                    $labelColorCodeArr = explode(',', $record->label_color_code);
                    foreach ($leadLabelNameArr as $key => $labelLabel) {
                        $st = '';
                        if ($key % 2 == 0) {
                            $st = '<br>';
                        }
                        $labelName .= '<span class="fs-6 badge me-2" style = "background-color: transparent;color: ' . $labelColorCodeArr[$key] . ';border: 1px solid ' . $labelColorCodeArr[$key] . '">' . $labelLabel . '</span>' . $st;
                    }
                }
                $timeAgoStringFun = $record->last_activity_updated_at !== null ? $this->timeAgoStringFun($record->last_activity_updated_at) : '';
                $data[] = array(
                    "id" => $i,
                    "name" => $name,
                    "company_name" => $record->company_name,
                    "lead_origin" => $lead_origin,
                    "lead_category" => $lead_category,
                    "created_at" => $date_added,
                    "customer_type" => $customer_type,
                    "email" => $email,
                    "phone_no" => $phone_no,
                    "address" => $address,
                    "pincode" => $pincode,
                    "country_name" => $country_name,
                    "state_name" => $state_name,
                    "city_name" => $city_name,
                    "description" => $description,
                    "last_activity" => $last_activity,
                    "last_activity_type" => $last_activity_type,
                    "last_internal_remarks" => $last_internal_remarks,
                    "last_activity_name" => $last_activity_name,
                    "assign_user_name" => $assign_user_name,
                    "assigned_to_user" => $assigned_to_user,
                    "net_amount" => (isset($country_data->currency_symbol) && $net_amount > 0) ? $country_data->currency_symbol . ' ' . $net_amount : $net_amount,
                    "status" => $status,
                    "label_name" => $labelName,
                    "estimate_status" => $estimate_status,
                    "country_code" => $record->country_code,
                    "new_lead_flag" => $record->new_lead_flag,
                    "created_at" => $record->created_at,
                    "updated_at" => $record->updated_at,
                    "time_ago_string" => $timeAgoStringFun,
                    "action" => $id,
                    "last_is_modified" => $last_is_modified,
                    "last_is_follow_up" => $last_is_follow_up,
                    "lead_stage_name" => $record->lead_stage_name,
                    "lead_stage_color_code" => $record->lead_stage_color_code,
                    "estimate_no" =>($record->estimate_version == 0) ? $record->estimate_no : $record->estimate_no . '-V' . $record->estimate_version,
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
    }

    public function index(Request $request)
    {

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
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

            if ($columnName == 'last_activity') {
                $columnName = 'cv.last_activity_updated_at';
            }

            // Fetch records
            $name = $request->get('name');
            $status = [];
            if($request->get('status'))
                $status = explode(",",$request->get('status'));

            $fil_lead_stage_id = $request->get('fil_lead_stage_id');

            $assigned_to_user = $request->get('assigned_to_user');
            $customer_type = $request->get('customer_type');
            $fil_customer_category_id = $request->get('fil_customer_category_id');
            $fil_customer_lead_id = $request->get('fil_customer_lead_id');
            $fil_created_user_id = $request->get('fil_created_user_id');
            $fil_estimate_status_id = $request->get('fil_estimate_status_id');
            $fil_country_id = $request->get('fil_country_id');
            $fil_state_id = $request->get('fil_state_id');
            $fil_city_name = $request->get('fil_city_name');
            $opr_id_2 = ($request->get('opr_id_2'))? explode(',', $request->get('opr_id_2')): [];
            $popr_id_1 = ($request->get('popr_id_1'))? explode(',', $request->get('popr_id_1')): [];
            $popr_id_3 = ($request->get('popr_id_3'))? explode(',', $request->get('popr_id_3')): [];
            $popr_id_4 = ($request->get('popr_id_4'))? explode(',', $request->get('popr_id_4')): [];
            $ropr_id_1 = ($request->get('ropr_id_1'))? explode(',', $request->get('ropr_id_1')): [];
            $ropr_id_2 = ($request->get('ropr_id_2'))? explode(',', $request->get('ropr_id_2')): [];
            $ropr_id_3 = ($request->get('ropr_id_3'))? explode(',', $request->get('ropr_id_3')): [];
            $ropr_id_4 = ($request->get('ropr_id_4'))? explode(',', $request->get('ropr_id_4')): [];
            $ropr_id_5 = ($request->get('ropr_id_5'))? explode(',', $request->get('ropr_id_5')): [];
            // Total records
            $counts = ViewCustomerData::select('customers_views.id')
                ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'customers_views.id')
                ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
                ->where(function ($query) use ($input) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(customers_views.created_at, '%Y-%m-%d')"), [$input['fil_lead_date_start'], $input['fil_lead_date_end']]);
                })
                ->where(function ($query) use ($status, $assigned_to_user,$user_perm,$fil_customer_category_id,$fil_customer_lead_id,$fil_created_user_id,$fil_estimate_status_id,$fil_lead_stage_id,$fil_country_id,$fil_state_id,$fil_city_name) {
                    if ($fil_country_id) {
                        $query->where('customers_views.country_id', $fil_country_id);
                    }
                    if ($fil_state_id) {
                        $query->where('customers_views.state_id', $fil_state_id);
                    }
                    if ($fil_city_name) {
                        $query->where('customers_views.city_name', $fil_city_name);
                    }
                    if ($fil_lead_stage_id) {
                        $query->where('customers_views.lead_stage_id', $fil_lead_stage_id);
                    }
                    if ($status) {
                        $query->WhereIn('lg.id', $status);
                    }
                    if ($fil_customer_category_id != '') {
                        $query->where('customers_views.customer_category_id', '=', $fil_customer_category_id);
                    }
                    if ($fil_customer_lead_id != '') {
                        $query->where('customers_views.customer_lead_id', '=', $fil_customer_lead_id);
                    }
                    if ($fil_created_user_id != '') {
                        $query->where('customers_views.user_id', '=', $fil_created_user_id);
                    }
                    if ($fil_estimate_status_id != '') {
                        $query->where('customers_views.estimate_status', '=', $fil_estimate_status_id);
                    }

                    /*if ($assigned_to_user > 0) {
                        $query->where('customers_views.assigned_to_user', '=', $assigned_to_user);
                    }*/
                    if ($assigned_to_user > 0) {
                        $query->where(function ($query) use ($assigned_to_user,$user_perm) {
                            $query->where('customers_views.assigned_to_user', '=', $assigned_to_user);
                            $query->orwhere('customers_views.user_id', '=', $assigned_to_user);
                        });
                    }
                })
                ->where(function ($query) use ($user_perm) {
                    // if (!in_array('give-access-to-attend-unassigned-leads', $user_perm)) {
                        $query->where('customers_views.company_id', $this->company_id);
                    //  }
                })
                ->where(function ($query) use ($user_perm) {
                    if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                        $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
                        $query->orwhere('customers_views.user_id', '=', $this->logged_user->id);
                    }

                    if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm)) {
                        if (in_array('give-access-to-attend-unassigned-leads', $user_perm)) {

                            $query->where('customers_views.company_id', $this->company_id);
                            $query->orwhere('customers_views.assigned_to_user', '=', 0);
                        } else {
                            $query->where('customers_views.assigned_to_user', '!=', 0);
                        }
                    } else {
                        if (in_array('give-access-to-attend-unassigned-leads', $user_perm)) {
                            $query->orwhere('customers_views.assigned_to_user', '=', 0);
                        }
                    }
                })
                ->where(function ($query) use ($opr_id_2,$popr_id_1,$popr_id_3,$popr_id_4,$ropr_id_1,$ropr_id_2,$ropr_id_3,$ropr_id_4,$ropr_id_5) {
                    if($opr_id_2) {
                        $query->wherein('customers_views.id', $opr_id_2);
                    }
                    if($popr_id_1) {
                        $query->wherein('customers_views.id', $popr_id_1);
                    }
                    if($popr_id_3) {
                        $query->wherein('customers_views.id', $popr_id_3);
                    }
                    if($popr_id_4) {
                        $query->wherein('customers_views.id', $popr_id_4);
                    }
                    if($ropr_id_1) {
                        $query->wherein('customers_views.id', $ropr_id_1);
                    }
                    if($ropr_id_2) {
                        $query->wherein('customers_views.id', $ropr_id_2);
                    }
                    if($ropr_id_3) {
                        $query->wherein('customers_views.id', $ropr_id_3);
                    }
                    if($ropr_id_5) {
                        $query->wherein('customers_views.id', $ropr_id_5);
                    }
                    if($ropr_id_4) {
                        $query->wherein('customers_views.id', $ropr_id_4);
                    }
                })
                /* ->where(function ($query) use ($search_arr) {
                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('customers_views.name', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('customers_views.company_name', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('customers_views.phone_no', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('customers_views.lead_category', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('customers_views.lead_origin', 'like', '%' . $search_arr . '%');
                    });

                })*/
                ->groupBy('customers_views.id')
                ->get();

            $totalRecords = $counts->count();

            $countswithFilter = ViewCustomerData::select('customers_views.id')
                ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'customers_views.id')
                ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
                ->where(function ($query) use ($input) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(customers_views.created_at, '%Y-%m-%d')"), [$input['fil_lead_date_start'], $input['fil_lead_date_end']]);
                })
                ->where(function ($query) use ($status, $assigned_to_user,$user_perm,$fil_customer_category_id,$fil_customer_lead_id,$fil_created_user_id,$fil_estimate_status_id,$fil_lead_stage_id,$fil_country_id,$fil_state_id,$fil_city_name) {
                    if ($fil_country_id) {
                        $query->where('customers_views.country_id', $fil_country_id);
                    }
                    if ($fil_state_id) {
                        $query->where('customers_views.state_id', $fil_state_id);
                    }
                    if ($fil_city_name) {
                        $query->where('customers_views.city_name', $fil_city_name);
                    }
                    if ($fil_lead_stage_id) {
                        $query->where('customers_views.lead_stage_id', $fil_lead_stage_id);
                    }
                    if ($status) {
                        $query->WhereIn('lg.id', $status);
                    }
                    if ($fil_customer_category_id != '') {
                        $query->where('customers_views.customer_category_id', '=', $fil_customer_category_id);
                    }
                    if ($fil_customer_lead_id != '') {
                        $query->where('customers_views.customer_lead_id', '=', $fil_customer_lead_id);
                    }
                    if ($fil_created_user_id != '') {
                        $query->where('customers_views.user_id', '=', $fil_created_user_id);
                    }
                    if ($fil_estimate_status_id != '') {
                        $query->where('customers_views.estimate_status', '=', $fil_estimate_status_id);
                    }
                    if ($assigned_to_user > 0) {
                        $query->where(function ($query) use ($assigned_to_user,$user_perm) {
                            $query->where('customers_views.assigned_to_user', '=', $assigned_to_user);
                                $query->orwhere('customers_views.user_id', '=', $assigned_to_user);
                        });
                    }
                })
                ->where(function ($query) use ($user_perm) {
                    // if (!in_array('give-access-to-attend-unassigned-leads', $user_perm)) {
                        $query->where('customers_views.company_id', $this->company_id);
                    //    }
                })
                ->where(function ($query) use ($user_perm) {
                    if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                        $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
                        $query->orwhere('customers_views.user_id', '=', $this->logged_user->id);
                    }

                    if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm)) {
                        if (in_array('give-access-to-attend-unassigned-leads', $user_perm)) {

                            $query->where('customers_views.company_id', $this->company_id);
                            $query->orwhere('customers_views.assigned_to_user', '=', 0);
                        } else {
                            $query->where('customers_views.assigned_to_user', '!=', 0);
                        }
                    } else {
                        if (in_array('give-access-to-attend-unassigned-leads', $user_perm)) {
                            $query->orwhere('customers_views.assigned_to_user', '=', 0);
                        }
                    }
                })
                ->where(function ($query) use ($search_arr) {
                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('customers_views.name', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('customers_views.company_name', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('customers_views.phone_no', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('customers_views.address', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('customers_views.city_name', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('customers_views.lead_category', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('customers_views.lead_origin', 'like', '%' . $search_arr . '%');
                    });
                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('customers_views.lead_stage_name', 'like', '%' . $search_arr . '%');
                    });

                })
                ->where(function ($query) use ($opr_id_2,$popr_id_1,$popr_id_3,$popr_id_4,$ropr_id_1,$ropr_id_2,$ropr_id_3,$ropr_id_4,$ropr_id_5) {
                    if($opr_id_2) {
                        $query->wherein('customers_views.id', $opr_id_2);
                    }
                    if($popr_id_1) {
                        $query->wherein('customers_views.id', $popr_id_1);
                    }
                    if($popr_id_3) {
                        $query->wherein('customers_views.id', $popr_id_3);
                    }
                    if($popr_id_4) {
                        $query->wherein('customers_views.id', $popr_id_4);
                    }
                    if($ropr_id_1) {
                        $query->wherein('customers_views.id', $ropr_id_1);
                    }
                    if($ropr_id_2) {
                        $query->wherein('customers_views.id', $ropr_id_2);
                    }
                    if($ropr_id_3) {
                        $query->wherein('customers_views.id', $ropr_id_3);
                    }
                    if($ropr_id_5) {
                        $query->wherein('customers_views.id', $ropr_id_5);
                    }
                    if($ropr_id_4) {
                        $query->wherein('customers_views.id', $ropr_id_4);
                    }
                })
            ->groupBy('customers_views.id')
            ->get();

            $totalRecordswithFilter = $countswithFilter->count();
            $rowperpage = ($rowperpage == -1) ? $totalRecords : $rowperpage;
            // DB::enableQueryLog();
            $records = DB::table('customers_views as cv')
                ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
                ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
                //->select('cv.*', DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code'), DB::raw('GROUP_CONCAT(lg.id) as label_id'))
                ->select('cv.updated_at','cv.country_code','cv.company_name','cv.created_at','cv.city_name','cv.state_name','cv.country_name','cv.status','cv.lead_category','cv.email','cv.address','cv.pincode','cv.description','cv.lead_origin','cv.customer_type','cv.id','cv.name','cv.phone_no','cv.last_activity','cv.user_name','cv.assigned_to_user','cv.net_amount','cv.estimate_status','cv.last_activity_type','cv.last_internal_remarks','cv.last_activity_name','cv.last_follow_up_datetime','cv.last_is_modified','cv.last_is_follow_up','cv.some_day_flg','cv.est_currency_id','cv.estimate_status','cv.last_activity_updated_at','cv.new_lead_flag','cv.lead_stage_name','cv.lead_stage_color_code', DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code'), DB::raw('GROUP_CONCAT(lg.id) as label_id'))
                ->where(function ($query) use ($user_perm) {
                    // if (!in_array('give-access-to-attend-unassigned-leads', $user_perm)) {
                        $query->where('cv.company_id', $this->company_id);
                    // }
                })
                ->where(function ($query) use ($input) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(cv.created_at, '%Y-%m-%d')"), [$input['fil_lead_date_start'], $input['fil_lead_date_end']]);
                })
                ->where(function ($query) use ($status, $assigned_to_user,$user_perm,$fil_customer_category_id,$fil_customer_lead_id,$fil_created_user_id,$fil_estimate_status_id,$fil_lead_stage_id,$fil_country_id,$fil_state_id,$fil_city_name) {
                    if ($fil_country_id) {
                        $query->where('cv.country_id', $fil_country_id);
                    }
                    if ($fil_state_id) {
                        $query->where('cv.state_id', $fil_state_id);
                    }
                    if ($fil_city_name) {
                        $query->where('cv.city_name', $fil_city_name);
                    }
                    if ($fil_lead_stage_id) {
                        $query->where('cv.lead_stage_id', $fil_lead_stage_id);
                    }
                    if ($fil_customer_category_id != '') {
                        $query->where('cv.customer_category_id', '=', $fil_customer_category_id);
                    }
                    if ($fil_customer_lead_id != '') {
                        $query->where('cv.customer_lead_id', '=', $fil_customer_lead_id);
                    }
                    if ($fil_created_user_id != '') {
                        $query->where('cv.user_id', '=', $fil_created_user_id);
                    }
                    if ($fil_estimate_status_id != '') {
                        $query->where('cv.estimate_status', '=', $fil_estimate_status_id);
                    }
                    /*if ($status != '') {
                        $query->where('lg.id', '=', $status);
                    }*/
                    /*if ($assigned_to_user > 0) {
                        $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                    }*/
                    if ($assigned_to_user > 0) {
                        $query->where('cv.assigned_to_user', '=', $assigned_to_user);
                        $query->orwhere('cv.user_id', '=', $assigned_to_user);
                    }
                })
                ->where(function ($query) use ($user_perm) {
                    if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                        $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
                        $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                    }

                    if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm)) {
                        if (in_array('give-access-to-attend-unassigned-leads', $user_perm)) {

                            $query->where('cv.company_id', $this->company_id);
                            $query->orwhere('cv.assigned_to_user', '=', 0);
                        } else {
                            $query->where('cv.assigned_to_user', '!=', 0);
                        }
                    } else {
                        if (in_array('give-access-to-attend-unassigned-leads', $user_perm)) {
                            $query->orwhere('cv.assigned_to_user', '=', 0);
                        }
                    }
                })
                ->where(function ($query) use ($search_arr) {
                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('cv.name', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('cv.company_name', 'like', '%' . $search_arr . '%');
                    });


                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('cv.phone_no', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('cv.address', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('cv.city_name', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('cv.lead_category', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('cv.lead_origin', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('cv.lead_stage_name', 'like', '%' . $search_arr . '%');
                    });
                })
                ->where(function ($query) use ($opr_id_2,$popr_id_1,$popr_id_3,$popr_id_4,$ropr_id_1,$ropr_id_2,$ropr_id_3,$ropr_id_4,$ropr_id_5) {
                    if($opr_id_2) {
                        $query->wherein('cv.id', $opr_id_2);
                    }
                    if($popr_id_1) {
                        $query->wherein('cv.id', $popr_id_1);
                    }
                    if($popr_id_3) {
                        $query->wherein('cv.id', $popr_id_3);
                    }
                    if($popr_id_4) {
                        $query->wherein('cv.id', $popr_id_4);
                    }
                    if($ropr_id_1) {
                        $query->wherein('cv.id', $ropr_id_1);
                    }
                    if($ropr_id_2) {
                        $query->wherein('cv.id', $ropr_id_2);
                    }
                    if($ropr_id_3) {
                        $query->wherein('cv.id', $ropr_id_3);
                    }
                    if($ropr_id_5) {
                        $query->wherein('cv.id', $ropr_id_5);
                    }
                    if($ropr_id_4) {
                        $query->wherein('cv.id', $ropr_id_4);
                    }
                })
                ->skip($start)
                ->take($rowperpage)
                ->orderBy($columnName, $columnSortOrder)
                ->groupBy('cv.id');
            if ($status) {
                // $records = $records->havingRaw("FIND_IN_SET('$status', GROUP_CONCAT(lg.id)) > 0");
                $records = $records->WhereIn("lg.id",$status);
            }
            /* ->havingRaw(function ($query) use ($status) {
                 if($status){
                     $query->havingRaw("FIND_IN_SET('$status', GROUP_CONCAT(lg.id)) > 0");
                 }

             })*/
            $records = $records->get();

            // dd(DB::getQueryLog($records));

            /*$records = ViewCustomerData::where('company_id', $this->company_id)
                ->where(function ($query) use ($user_perm) {
                    if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                        $query->where('assigned_to_user', '=', $this->logged_user->id);
                        $query->orwhere('user_id', '=', $this->logged_user->id);
                    }
                })
                ->where(function ($query) use ($search_arr) {
                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('name', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('phone_no', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('lead_category', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('lead_origin', 'like', '%' . $search_arr . '%');
                    });
                })
                ->select('*')
                ->skip($start)
                ->take($rowperpage)
                ->orderBy($columnName, $columnSortOrder)
                ->get();*/

            // dd(DB::getQueryLog());

            $data = array();
            $i = 0;
            foreach ($records as $record) {
                $country_data = [];
                // if($record->currency_name_country_id)
                if($record->est_currency_id)
                $country_data = Country::where("id", $record->est_currency_id)->select('name','currency_name','currency_code','currency_symbol')->orderBy('id', 'DESC')->first();
                $id = Crypt::encrypt($record->id);
                $customer_type = $record->customer_type;
                $name = ucwords($record->name);
                $lead_origin = $record->lead_origin;
                $lead_category = $record->lead_category;
                $email = $record->email;
                $phone_no = $record->phone_no;
                $address = $record->address;
                $pincode = $record->pincode;
                $description = $record->description;
                $status = $record->status;
                $country_name = $record->country_name;
                $state_name = $record->state_name;
                $city_name = $record->city_name;
                $last_activity = nl2br(htmlentities($record->last_activity));
                $assign_user_name = $record->user_name;
                $assigned_to_user = $record->assigned_to_user;
                $net_amount = $record->net_amount;
                $estimate_status = $record->estimate_status;
                $last_activity_type = $record->last_activity_type;
                $last_internal_remarks = $record->last_internal_remarks;
                $last_activity_name = $record->last_activity_name;
                $date_added = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)
                    ->format('d-m-Y h:i A');
                $last_is_modified = $record->last_is_modified;
                $last_is_follow_up = $record->last_is_follow_up;
                $i++;
                $labelName = '';

                if ($record->label_name) {
                    $leadLabelNameArr = explode(',', $record->label_name);
                    $labelColorCodeArr = explode(',', $record->label_color_code);
                    foreach ($leadLabelNameArr as $key => $labelLabel) {
                        $st = '';
                        if ($key % 2 == 0) {
                            $st = '<br>';
                        }
                        $labelName .= '<span class="fs-6 badge me-2" style = "background-color: transparent;color: ' . $labelColorCodeArr[$key] . ';border: 1px solid ' . $labelColorCodeArr[$key] . '">' . $labelLabel . '</span>' . $st;
                    }
                }
                $data[] = array(
                    "id" => $i,
                    "name" => $name,
                    "company_name" => $record->company_name,
                    "lead_origin" => $lead_origin,
                    "lead_category" => $lead_category,
                    "created_at" => $date_added,
                    "customer_type" => $customer_type,
                    "email" => $email,
                    "phone_no" => $phone_no,
                    "address" => $address,
                    "pincode" => $pincode,
                    "country_name" => $country_name,
                    "state_name" => $state_name,
                    "city_name" => $city_name,
                    "description" => $description,
                    "last_activity" => $last_activity,
                    "last_activity_type" => $last_activity_type,
                    "last_internal_remarks" => $last_internal_remarks,
                    "last_activity_name" => $last_activity_name,
                    "assign_user_name" => $assign_user_name,
                    "assigned_to_user" => $assigned_to_user,
                    "net_amount" => (isset($country_data->currency_symbol) && $net_amount > 0) ? $country_data->currency_symbol .' ' .$net_amount : $net_amount,
                    "status" => $status,
                    "label_name" => $labelName,
                    "estimate_status" => $estimate_status,
                    "country_code" => $record->country_code,
                    "new_lead_flag" => $record->new_lead_flag,
                    "created_at" => $record->created_at,
                    "updated_at" => $record->updated_at,
                    "time_ago_string" => $this->timeAgoStringFun($record->last_activity_updated_at),
                    "action" => $id,
                    "last_is_modified" => $last_is_modified,
                    "last_is_follow_up" => $last_is_follow_up,
                    "lead_stage_name" => $record->lead_stage_name,
                    "lead_stage_color_code" => $record->lead_stage_color_code,
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

        $countries = Country::select(["name", "id", "phonecode","sortname","currency_name","currency_code","currency_symbol"])->where('status', '=', 0)->orderBy('name','ASC')->get();

        $fil_states = State::select("*")->where('status', '=', 0)->orderBy('name','ASC')->get();

        $customerCategories = CustomerCategory::select(["name", "id"])->where('status', '=', 0)->where('company_id', $this->company_id)->get();
        $customerLeads = CustomerLead::select(["name", "id"])
            ->where('status', '=', 0)
            ->where(function ($query) {
                $query->where('company_id', '=', $this->company_id);
                $query->orwhere('is_status', '=', 1);
            })
            // ->where('company_id', $this->company_id)
            ->get();

        $leadStages = LeadStage::select("*")
            ->where('status', '=', 0)
            ->where(function ($query) {
                $query->where('company_id', '=', $this->company_id);
                // $query->where('status', '=', 0);
            })
            ->orderBy('priority', 'asc')
            ->get();

        $leadGroups = $data = DB::table('lead_groups')
            ->leftJoin('customer_labels', 'lead_groups.id', '=', 'customer_labels.label_id')
            ->select('lead_groups.*', DB::raw('count(customer_labels.id) as lead_count'))
            ->where('lead_groups.company_id', $this->company_id)
            ->where('lead_groups.status', 0)
            ->orderBy('lead_groups.name', 'asc')
            ->groupBy('lead_groups.id')
            ->get();
        $teamUsers = User::select(["name", "id", "email", "mobile_no"])
            ->where('invite_status', 1)
            ->where(function ($query) {
                $query->orwhere('company_id', $this->company_id);
                $query->orwhere('id', $this->company_id);
            })
            ->get();
        $leads = User::select(["name", "id", "email", "mobile_no"])
            // ->where('status', 'Approved')
            ->where('invite_status', 1)
            // ->where('company_id', $this->company_id)
            ->where(function ($query) {
                $query->orwhere('company_id', $this->company_id);
                $query->orwhere('id', $this->company_id);
            })
            ->get();
        $leadLabels = LeadGroup::select(["name", "color_code", "id"])->where('status', '=', 0)->where('company_id', $this->company_id)->get();
        $lostReasons = LostReason::select("*")
            ->where('status', '=', 0)
            ->where(function ($query) {
                $query->where('company_id', '=', $this->company_id);
                // $query->orwhere('status', '=', 1);
            })
            ->orderBy('name', 'asc')
            ->get();
        $segment = $this->segment;
        return view('app.customer', compact('countries', 'customerCategories', 'customerLeads', 'leadGroups', 'teamUsers','leads', 'leadStages', 'fil_states','leadLabels','lostReasons', 'segment'))->with('main_company', $this->main_company);
    }

    public function timeAgoStringFun($activityDate)
    {
        $now = time(); // Get the current time

        $diff = $now - strtotime($activityDate); // Get the difference in seconds

        if ($diff < 60) {
            $timeAgoString = $diff . ' sec ago';
        } elseif ($diff < 120) {
            $timeAgoString = '1 minute ago';
        } elseif ($diff < 3600) {
            $timeAgoString = round($diff / 60) . ' min ago';
        } elseif ($diff < 7200) {
            $timeAgoString = '1 hour ago';
        } elseif ($diff < 86400) {
            $timeAgoString = round($diff / 3600) . ' hours ago';
        } elseif ($diff < 172800) {
            $timeAgoString = 'yesterday';
        } elseif ($diff < 2592000) {
            $timeAgoString = round($diff / 86400) . ' days ago';
        } elseif ($diff < 5184000) {
            $timeAgoString = 'last month';
        } elseif ($diff < 31536000) {
            $months = round($diff / 2592000);
            $timeAgoString = $months . ' ' . (($months == 1) ? 'month' : 'months') . ' ago';
        } else {
            $years = round($diff / 31536000);
            $timeAgoString = $years . ' ' . (($years == 1) ? 'year' : 'years') . ' ago';
        }
        return $timeAgoString;
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $validator = Validator::make($input, [
                'name' => 'required',
                'phone_no' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);
            }

            $input['user_id'] = $this->logged_user->id;
            $input['name'] = ucwords($input['name']);
            $input['company_id'] = ($this->company_id);
            $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];

             if (Customer::where('phone_no', '=', $input['phone_no'])
                 ->where('company_id', $input['company_id'])
                 ->where(function ($query) use ($id) {
                     if ($id != 0) {
                         $query->Where(function ($query) use ($id) {
                             $query->where('id', '!=', $id);
                         });
                     }
                 })
                 ->first() && isset($input['button_value']) && $input['button_value']==0) {
                 $duplicateLeads = DB::select("SELECT a.id, a.name, a.phone_no, a.created_at,a.assigned_to_user,a.user_id as create_lead_user_id, u.name as assigned_user_name,a.new_lead_flag
                                FROM customers a left join users u on a.assigned_to_user=u.id WHERE a.phone_no = '{$input['phone_no']}' and a.company_id=$this->company_id order by a.id desc");

                 $tableHtml = '<p class="text-sm text-dark mb-2 fw-bold">
                            Lead with this mobile number already exists, do you want to countinue?
                        </p>';
                 $tableHtml .= '<table class="table table-centered table-nowrap table-sm">';
                 $tableHtml .= '<thead class="table-light text-uppercase">';
                 $tableHtml .= '<tr>';
                 $tableHtml .= '<th>Lead Name</th>';
                 $tableHtml .= '<th>Matching Field(s)</th>';
                 $tableHtml .= '<th>Date Added</th>';
                 $tableHtml .= '<th></th>';
                 $tableHtml .= '</tr>';
                 $tableHtml .= '</thead>';
                 $tableHtml .= '<tbody>';

                 foreach ($duplicateLeads as $duplicateLead) {
                     $leadName = htmlspecialchars($duplicateLead->name, ENT_QUOTES, 'UTF-8');
                     $assignedUserName = htmlspecialchars($duplicateLead->assigned_user_name, ENT_QUOTES, 'UTF-8');
                     $phoneNo = htmlspecialchars($duplicateLead->phone_no, ENT_QUOTES, 'UTF-8');
                     $createdDate = \Carbon\Carbon::parse($duplicateLead->created_at)->format('d M Y');
                     $timelineUrl = url('lead/timeline/' . Crypt::encrypt($duplicateLead->id));

                     $tableHtml .= '<tr>';
                     $tableHtml .= '<td class="text-dark">';

                     if ($duplicateLead->new_lead_flag == 1) {
                         $tableHtml .= '<span class="badge bg-secondary text-light float-end blinks" style="background:#0acf97 !important;">New</span>';
                     }

                     $tableHtml .= '<h5 class="font-14 my-1">' . $leadName . '</h5>';

                     if ($duplicateLead->assigned_to_user == $this->logged_user->id) {
                         $tableHtml .= '<span class="fs-6 badge bg-primary"><i class="pe-1 mdi mdi-account-check"></i>' . $assignedUserName . '</span>';
                     } elseif ($duplicateLead->assigned_to_user == 0) {
                         $tableHtml .= '<span class="fs-6 badge bg-secondary text-light"><i class="pe-1 mdi mdi-account-off"></i>Unassigned</span>';
                     } else {
                         $tableHtml .= '<span class="fs-6 badge bg-secondary text-light"><i class="pe-1 mdi mdi-account-lock"></i>' . $assignedUserName . '</span>';
                     }

                     $tableHtml .= '</td>';
                     $tableHtml .= '<td class="text-danger">' . $phoneNo . '</td>';
                     $tableHtml .= '<td class="text-dark">' . $createdDate . '</td>';
                     $tableHtml .= '<td>';

                     if (
                         $duplicateLead->create_lead_user_id == $this->logged_user->id ||
                         $duplicateLead->assigned_to_user == $this->logged_user->id ||
                         in_array('access-all-lead-and-assign-to-anyone-in-team', $this->user_perm)
                     ) {
                         $tableHtml .= '<a href="' . $timelineUrl . '" class="action-icon text-dark" target="_blank"><i class="mdi mdi-arrow-top-right-bold-box-outline"></i></a>';
                     }

                     $tableHtml .= '</td>';
                     $tableHtml .= '</tr>';
                 }

                 $tableHtml .= '</tbody>';
                 $tableHtml .= '</table>';

                 //echo $tableHtml;
                 return response()->json(['success' => 'Customer phone no exists!','data' => $tableHtml], 409);
             }
            $leadSource = 'Lead added from manually';
            if ($input['customer_lead_id'] > 0) {

                $customerLeads = CustomerLead::select(["name", "id"])->where('status', '=', 0)->where('id', $input['customer_lead_id'])->first();

                $leadSource = 'Lead added from ' . $customerLeads->name;
            }

            if ($id == 0) {
                $activityLogMsg = 'Customer created by ' . $this->logged_user->name;
                $logInput['internal_remarks'] = $leadSource;
                $input['assigned_to_user'] = $this->logged_user->id;
                $input['new_lead_flag'] = 1;
                $input['country_code'] = '+'.$input['country_code'];
                $input['whatsapp_country_code'] = '+'.$input['whatsapp_country_code'];
                $input['whatsapp_no'] = ($input['whatsapp_no'])?$input['whatsapp_no']:$input['phone_no'];
                /*$input['lead_stage_id'] = 0;
                $lead_stage_data = LeadStage::where('company_id',$this->company_id)->where('is_default',1)->select('name','id')->first();
                if($lead_stage_data){
                    $input['lead_stage_id'] =$lead_stage_data->id;
                }*/
                $customer = Customer::create($input);
                $ids = $customer->id;

                $tenantdata = DB::connection('mysql')->table('tenants')->where('email', $this->logged_user->email)->first();
                $tcompany_id = ($tenantdata->company_id) ? $tenantdata->company_id : $tenantdata->id;
                $hdata['lead_id'] = $ids;
                $hdata['user_id'] = $this->logged_user->id;
                $hdata['company_id'] = $tcompany_id;
                $hdata['email'] = $this->logged_user->email;
                $hdata['domain'] = $this->logged_user->domain;
                $leadhistory = LeadHistory::create($hdata);

                $customers = ViewCustomerData::select('id', 'name', 'phone_no', 'state_id', 'address', 'pincode', 'country_name', 'state_name', 'city_name', 'company_name', 'country_code','whatsapp_no', 'whatsapp_country_code','currency_name_country_id','whatsapp_no_country_id','whatsapp_no_country_id','currency_name')
                    ->where('id', $ids)
                    ->where('status', 0)
                    ->first();
                $logInput['activity_type'] = 6;
            } else {
                $input['country_code'] = '+'.$input['country_code'];
                $input['whatsapp_country_code'] = '+'.$input['whatsapp_country_code'];
                $input['whatsapp_no'] = ($input['whatsapp_no'])?$input['whatsapp_no']:$input['phone_no'];
                $customer = Customer::find($id)->update($input);
                $activityLogMsg = 'Customer updated by ' . $this->logged_user->name;
                $logInput['internal_remarks'] = "Lead updated";
                $ids = $id;

                $customers = ViewCustomerData::select('id', 'name', 'phone_no', 'state_id', 'address', 'pincode', 'country_name', 'state_name', 'city_name', 'company_name', 'country_code', 'last_follow_up_datetime','whatsapp_no', 'whatsapp_country_code','currency_name_country_id','whatsapp_no_country_id','whatsapp_no_country_id','currency_name')
                    ->where('id', $id)
                    ->where('status', 0)
                    ->first();
                $logInput['activity_type'] = 7;
                $logInput['follow_up_datetime'] = $customers->last_follow_up_datetime;
            }

            // Add activity logs
            $input['id'] = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
            LogActivity::addToLog($activityLogMsg, $input);


            $logInput['assigned_to'] = $this->logged_user->id;
            $logInput['customer_id'] = $ids;
            $logInput['entry_type'] = "leads";


            $logInput['user_id'] = $this->logged_user->id;
            $logInput['company_id'] = $this->company_id;
            $logInput['created_by'] = $this->logged_user->id;
            $logInput['updated_by'] = $this->logged_user->id;
            LogActivity::addToActivityLog($logInput);


            if ($id == 0) {
                $logInput['activity_type'] = 8;
                $logInput['entry_type'] = "assigned";
                $logInput['follow_up_datetime'] = '0000-00-00 00:00:00';
                $logInput['internal_remarks'] = "Assigned to " . $this->logged_user->name;
                LogActivity::addToActivityLog($logInput);
            }

            $country_data = [];
            if($customers->currency_name_country_id)
                $country_data = Country::where("id", $customers->currency_name_country_id)->select('name','currency_name','currency_code','currency_symbol')->orderBy('id', 'DESC')->get()->first();
            return response()->json(['success' => 'Customer Saved!', "customer_id" => $ids, "customer_name" => $input['name'], "state_id" => $input["state_id"], "desc" => $customers->phone_no, "country_name" => $customers->country_name, "state_name" => $customers->state_name, "city_name" => $customers->city_name, "address" => $customers->address, "pincode" => $customers->pincode, "phone_no" => $customers->phone_no, "country_code" => $customers->country_code ,'whatsapp_country_code'=>$customers->whatsapp_country_code,'whatsapp_no'=>$customers->whatsapp_no, "currency_symbol" => (isset($country_data->currency_symbol)) ? $country_data->currency_symbol:'',"currency_name" => $customers->currency_name], 201);
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

            $customers = Customer::find($id)->toArray();

            if (is_null($customers)) {
                return response()->json(['success' => 'Customer not found!'], 422);
            }
            $customers['id'] = Crypt::encrypt($customers['id']);
            return response()->json([
                "success" => true,
                "message" => "Customer retrieved successfully.",
                "data" => $customers
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
            // DB::enableQueryLog();

            $estimates = DB::table('estimates')->select('id')
                ->whereIn('customer_id', $id)
                ->get();
            $estId = [];
            foreach ($estimates as $estimate) {
                $estId[] = $estimate->id;
            }

            $estimateItems = DB::table('estimate_items')
                ->whereIn('estimate_id', $estId)
                ->delete();

            $estimate = DB::table('estimates')
                ->whereIn('customer_id', $id)
                ->delete();

            /* $estimates = EstimateItem::join('estimates', function($q) use ($id)
             {
                 $q->on('estimates.id', '=', 'estimate_items.estimate_id')
                     ->whereIn('estimates.customer_id', $id);
             })->delete();*/
            /* $estimates =  DB::table('estimates')
                 ->join('estimate_items', 'estimate_items.estimate_id', '=', 'estimates.id')
                 ->whereIn('estimates.customer_id', $id)
                 ->delete();*/
            // dd(DB::getQueryLog($estimates));
            $customer_labels = DB::table('customer_labels')
                ->whereIn('customer_id', $id)
                ->delete();


            $customer_timelines = DB::table('customer_timelines')->select('id', 'estimate_version_no', 'company_id')
                ->whereIn('customer_id', $id)
                ->where('estimate_version_no', '!=', '')
                ->get();

            foreach ($customer_timelines as $customer_timeline) {
                $path = 'public/document/' . $customer_timeline->company_id . '/' . $customer_timeline->estimate_version_no . '.pdf';
                if (Storage::exists($path)) {
                    Storage::delete($path);
                }
            }
            $customer_labels = DB::table('customer_timelines')
                ->whereIn('customer_id', $id)
                ->delete();

            $sales_person_performances = DB::table('sales_person_performances')
                ->whereIn('customer_id', $id)
                ->delete();

            $country = Customer::whereIn('id', $id)->delete();

            $tenantdata = DB::connection('mysql')->table('tenants')->where('email', $this->logged_user->email)->first();
            $tcompany_id = ($tenantdata->company_id) ? $tenantdata->company_id : $tenantdata->id;
            $leadhistory = LeadHistory::whereIn('lead_id', $id)->where('company_id', $tcompany_id)->delete();

            LogActivity::addToLog('Customer deleted by ' . $this->logged_user->name, $id);
            return response()->json(['success' => 'Customer Deleted!'], 201);
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
            if (!Customer::whereIn('id', $id)->first()) {
                return response()->json(['success' => 'Customer exists!'], 422);
            }
            $customers = Customer::whereIn('id', $id)->update(["status" => $input['status']]);

            $data['id'] = $id;
            $data['status'] = ($input['status'] == 0) ? 'Active' : 'Deactive';
            LogActivity::addToLog('Customer status updated by ' . $this->logged_user->name, $data);

            return response()->json(['success' => 'Customer status updated!'], 201);
        }
    }

    public function customerAutocomplete(Request $request)
    {
        if ($request->ajax()) {
            $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
            $search = $request->get('search');
            $user = Auth::user();
            $customers = ViewCustomerData::select('id', 'name', 'phone_no', 'state_id', 'address', 'pincode', 'country_name', 'state_name', 'city_name', 'company_name',"currency_name","currency_code", "phone_no_country_id", "whatsapp_no_country_id", "currency_name_country_id")
//                ->join('countries', 'country_id', '=', 'countries.id')
//                ->join('states', 'state_id', '=', 'states.id')
//                ->join('cities', 'city_id', '=', 'cities.id')
                /*->where('company_id', $this->company_id)
                ->where(function ($query) use ($user_perm) {
                    if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                        $query->where('assigned_to_user', '=', $this->logged_user->id);
                        $query->orwhere('user_id', '=', $this->logged_user->id);
                    }
                })*/
                ->where(function ($query) use ($user_perm) {
//                    if (!in_array('give-access-to-attend-unassigned-leads', $user_perm)) {
                    $query->where('company_id', $this->company_id);
//                    }
                })
                ->where(function ($query) use ($user_perm) {
                    if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                        $query->where('assigned_to_user', '=', $this->logged_user->id);
                        $query->orwhere('user_id', '=', $this->logged_user->id);
                    }

                    if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm)) {
                        if (in_array('give-access-to-attend-unassigned-leads', $user_perm)) {

                            $query->where('company_id', $this->company_id);
                            $query->orwhere('assigned_to_user', '=', 0);
                        } else {
                            $query->where('assigned_to_user', '!=', 0);
                        }
                    } else {
                        if (in_array('give-access-to-attend-unassigned-leads', $user_perm)) {
                            $query->orwhere('assigned_to_user', '=', 0);
                        }
                    }
                })
                ->where(function ($query) {
                    if ($this->logged_user->customer_show_flg == 0 && $this->logged_user->company_id != '') {
                        $query->where('user_id', '=', $this->logged_user->id);
                    }
                })
                ->where('status', 0)
//                ->where('name', 'LIKE', '%' . $search . '%')
                ->where(function ($query) use ($search) {
                    $query->orWhere(function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%');
                    });
                    $query->orWhere(function ($query) use ($search) {
                        $query->where('phone_no', 'like', $search . '%');
                    });
                    $query->orWhere(function ($query) use ($search) {
                        $query->where('company_name', 'like', $search . '%');
                    });
                })
                ->get();
            $response = array();
            foreach ($customers as $customer) {
                $country_data = [];
                if($customer->currency_name_country_id)
                    $country_data = Country::where("id", $customer->currency_name_country_id)->select('name','currency_name','currency_code','currency_symbol')->orderBy('id', 'DESC')->get()->first();
                $response[] = array("value" => $customer->id, "label" => $customer->name, "desc" => $customer->phone_no, "state_id" => $customer->state_id, "country_name" => $customer->country_name, "state_name" => $customer->state_name, "city_name" => $customer->city_name, "address" => $customer->address, "pincode" => $customer->pincode, "phone_no" => $customer->phone_no, "company_name" => $customer->company_name,"currency_name" => $customer->currency_name,"currency_code" => $customer->currency_code, "currency_symbol" => (isset($country_data->currency_symbol)) ? $country_data->currency_symbol:'');
            }

            return response()->json($response);
        }
    }

    public function leadAutocomplete(Request $request)
    {
        if ($request->ajax()) {
            $search = $request->get('search');
            $user = Auth::user();
            $leadGroups = LeadGroup::select('id', 'name')
                ->where('company_id', $this->company_id)
                ->where('status', 0)
                ->get();
            $response = array();
            foreach ($leadGroups as $leadGroup) {
                $response[] = array("id" => $leadGroup->id, "name" => $leadGroup->name);
            }

            return response()->json($response);
        }
    }

    public function leadTimeline($id)
    {
        //return Auth::user();
        $id = Crypt::decrypt($id);
        // $validator = Validator::make($id, [
        //     'id' => 'required'
        // ]);
        // if ($validator->fails()) {
        //     return response()->json(['errors' => $validator->errors()->all()], 400);
        // }
        $customers = ViewCustomerData::select("*")->where([['id', '=', $id], ['company_id', '=', $this->company_id]])->first();

        if (!$customers) {
            return redirect(route('tenant.customer.index', ['tenant' => $this->segment]));
            // abort(500, 'Something went wrong');
        }
        $results = DB::table('customer_labels')
            ->select(DB::raw("group_concat(label_id separator ',') as labelId"))
            ->where('customer_id', '=', $id)
            ->groupBy('customer_id')
            ->get()->toArray();
        $label_color = [];
        $leadArr = [];
        if ($results) {
            $leadArr = explode(',', $results[0]->labelId);

            $label_color = DB::table('lead_groups')
                ->select('name', 'color_code')
                ->whereIn('id', $leadArr)
                ->get()->toArray();
        }

        // DB::enableQueryLog();
        $duplicateLeads = DB::select("SELECT a.id, a.name, a.phone_no, a.created_at,a.assigned_to_user,a.user_id as create_lead_user_id, u.name as assigned_user_name,a.new_lead_flag
                                FROM customers a left join users u on a.assigned_to_user=u.id WHERE a.id !=$id AND a.phone_no = '$customers->phone_no' and a.company_id=$this->company_id");
        // dd(DB::getQueryLog($duplicateLeads));
        // print_r($duplicateLeads);
        // echo $duplicateLeads[0]->cnt;
        //die;

        $leadLabels = LeadGroup::select(["name", "color_code", "id"])->where('status', '=', 0)->where('company_id', $this->company_id)->get();
        $countries = Country::select(["name", "id", "phonecode","sortname","currency_name","currency_code","currency_symbol"])->where('status', '=', 0)->get();
        $customerCategories = CustomerCategory::select(["name", "id"])->where('status', '=', 0)->where('company_id', $this->company_id)->get();
        $customerLeads = CustomerLead::select(["name", "id"])
            ->where('status', '=', 0)
            ->where(function ($query) {
                $query->where('company_id', '=', $this->company_id);
                $query->orwhere('is_status', '=', 1);
            })
        // ->where('company_id', $this->company_id)
        ->get();
        $leads = User::select(["name", "id", "email", "mobile_no"])
            // ->where('status', 'Approved')
            ->where('invite_status', 1)
            // ->where('company_id', $this->company_id)
            ->where(function ($query) {
                $query->orwhere('company_id', $this->company_id);
                $query->orwhere('id', $this->company_id);
            })
            ->get();
        $leadStages = LeadStage::select("*")
            ->where('status', '=', 0)
            ->where(function ($query) {
                $query->where('company_id', '=', $this->company_id);
                // $query->orwhere('status', '=', 1);
            })
            ->orderBy('priority', 'asc')
            ->get();

        $lostReasons = LostReason::select("*")
            ->where('status', '=', 0)
            ->where(function ($query) {
                $query->where('company_id', '=', $this->company_id);
                // $query->orwhere('status', '=', 1);
            })
            ->orderBy('name', 'asc')
            ->get();

        $activity_counts = DB::table(function ($query) use ($id) {
            $query->from('customer_timelines')
                ->select('activity_type')
                ->where('customer_id', '=', $id)
                ->whereIn('activity_type', [1, 2, 3,19]);
        }, 'a')
            ->rightJoin(DB::raw('(SELECT 1 AS activity_type UNION SELECT 2 UNION SELECT 3 UNION SELECT 19) b'), 'b.activity_type', '=', 'a.activity_type')
            ->select('b.activity_type')
            ->selectRaw('
        CASE
            WHEN b.activity_type = 1 THEN "Call"
            WHEN b.activity_type = 2 THEN "Message"
            WHEN b.activity_type = 3 THEN "Meeting"
            WHEN b.activity_type = 19 THEN "Site Visit"
            ELSE "Other"
        END AS activity_name
    ')
            ->selectRaw('COALESCE(COUNT(a.activity_type), 0) AS activity_type_count')
            ->groupBy('b.activity_type', 'activity_name')
            ->get();
        $segment = $this->segment;
        if ($this->logged_user->id == $customers->assigned_to_user)
            Customer::find($id)->update(["new_lead_flag" => 0]);
        return view('app.leads.timeline', compact('countries', 'customerCategories', 'customerLeads', 'customers', 'leads', 'duplicateLeads', 'leadLabels', 'leadArr', 'label_color','leadStages','lostReasons', 'activity_counts', 'segment'))->with('main_company', $this->main_company);
    }

    public function updateLeadDescription(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();

            $input['id'] = Crypt::decrypt($input['lead_id']);
            $validator = Validator::make($input, [
                'lead_id' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);;
            }

            $customers = Customer::where('id', $input['id'])->update(["description" => $input['lead_description']]);

            /*    $data['status'] = ($input['status'] == 0) ? 'Active' : 'Deactive';
                LogActivity::addToLog('Customer status updated by ' . $this->logged_user->name, $data);*/

            return response()->json(['success' => 'Customer description updated!', 'lead_description' => nl2br($input['lead_description'])], 201);
        }
    }

    public function updateLeadStage(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();

            $input['id'] = Crypt::decrypt($input['lead_id']);
            $validator = Validator::make($input, [
                'lead_id' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);
            }
            $lost_reason_id = ($input['lead_stage_data_id']==6)?$input['lost_reason_id']:0;
            $lost_reason_others = ($input['lead_stage_data_id']==6)?$input['lost_reason_others']:'';
            $customers = Customer::where('id', $input['id'])->update(["lead_stage_id" => $input['leads_stages_id'],"lost_reason_id" =>$lost_reason_id,"others_reason" =>$lost_reason_others]);

            if($input['lead_stage_data_id']==6){

                $customer_data = EstimateTimeline::select("id")
                    ->where('customer_id', '=',  $input['id'])
                    ->wherein('activity_type', [9])
                    ->orderBy('id', 'DESC')
                    ->take(1)
                    ->get()
                    ->toArray();

                if ($customer_data && $customer_data[0]['id']) {
                    $a = SalesPersonPerformances::where('timeline_id', $customer_data[0]['id'])->update(array('completed_task' => 1));
                }

                $logInput['follow_up_datetime'] = '0000-00-00 00:00:00';
                $logInput['assigned_to'] = $this->logged_user->id;
                $logInput['customer_id'] = $input['id'];
                $logInput['entry_type'] = "Lead Lost";
                $logInput['activity_name'] = "Lead Lost";
                $logInput['activity_type'] = "Lead Lost";
                $logInput['activity_type'] = 17;
                $logInput['internal_remarks'] = ($lost_reason_others)?$lost_reason_others:$input['lost_reason_name'];
                $logInput['user_id'] = $this->logged_user->id;
                $logInput['company_id'] = $this->company_id;
                $logInput['created_by'] = $this->logged_user->id;
                $logInput['updated_by'] = $this->logged_user->id;
                LogActivity::addToActivityLog($logInput);

            }

            if($input['lead_stage_data_id']==2){

                /*$customer_data = EstimateTimeline::select("id")
                    ->where('customer_id', '=',  $input['id'])
                    ->wherein('activity_type', [1, 2, 3, 9,19]) chet
                    ->orderBy('id', 'DESC')
                    ->take(1)
                    ->get()
                    ->toArray();*/

                /*$customer_data = EstimateTimeline::select("id")
                    ->where('customer_id', '=',  $input['id'])
                    ->wherein('activity_type', [9])
                    ->orderBy('id', 'DESC')
                    ->take(1)
                    ->get()
                    ->toArray();

                if ($customer_data && $customer_data[0]['id']) {
                    $a = SalesPersonPerformances::where('timeline_id', $customer_data[0]['id'])->update(array('completed_task' => 1));
                }*/

                $customer_data_tmp = EstimateTimeline::where('customer_id', $input['id'])->select(['follow_up_datetime'])
                    ->latest('id')
                    ->first();
                $logInput['follow_up_datetime'] = ($customer_data_tmp)?$customer_data_tmp->follow_up_datetime:'0000-00-00 00:00:00';
                //$logInput['follow_up_datetime'] = '0000-00-00 00:00:00';
                $logInput['assigned_to'] = $this->logged_user->id;
                $logInput['customer_id'] = $input['id'];
                $logInput['entry_type'] = "Lead Won";
                $logInput['activity_name'] = "Lead Won";
                $logInput['activity_type'] = "Lead Won";
                $logInput['activity_type'] = 18;
                $logInput['internal_remarks'] = '';
                $logInput['user_id'] = $this->logged_user->id;
                $logInput['company_id'] = $this->company_id;
                $logInput['created_by'] = $this->logged_user->id;
                $logInput['updated_by'] = $this->logged_user->id;
                LogActivity::addToActivityLog($logInput);

            }

            /*    $data['status'] = ($input['status'] == 0) ? 'Active' : 'Deactive';
                LogActivity::addToLog('Customer status updated by ' . $this->logged_user->name, $data);*/

            return response()->json(['success' => 'Customer stage updated!', 'lead_stage' => $input['leads_stages_id']], 201);
        }
    }

    public function updateLabelSave(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $input['lead_id'] = Crypt::decrypt($input['lead_id']);
            $validator = Validator::make($input, [
                'lead_id' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);;
            }
//            $selected_lead_id = explode(',', $input['selected_lead_id']);
//            $selected_lead_id = (isset($input['selected_lead_id']))?$input['selected_lead_id']:[];
            $customer_id = $input['lead_id'];


            CustomerLabel::where('customer_id', $input['lead_id'])->delete();
            if (isset($input['selected_lead_id']) && count($input['selected_lead_id']) > 0) {
                $data = [];
                $i = 0;
                foreach ($input['selected_lead_id'] as $value) {
                    $data[$i]['customer_id'] = $customer_id;
                    $data[$i]['label_id'] = $value;
                    $data[$i]['user_id'] = $this->logged_user->id;
                    $data[$i]['company_id'] = $this->company_id;
                    $i++;
                }
                CustomerLabel::insert($data);
            }
//            Customer::find($customer_id)->update(["new_lead_flag" => 0]);

            return response()->json(['success' => 'Customer label updated!'], 201);
        }
    }

    public function showCustomerTimeline(Request $request)
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

            $customers = ViewCustomerData::select("*")->where('id', '=', $id)->first();

            if (is_null($customers)) {
                return response()->json(['success' => 'Customer not found!'], 422);
            }
            $customers['description'] = nl2br($customers['description']);
            $customers['id'] = Crypt::encrypt($customers['id']);


            $customers['lead_label_data'] = DB::table('customer_labels')
                ->join('lead_groups', 'customer_labels.label_id', '=', 'lead_groups.id')
                ->where('customer_labels.customer_id', $id)
                ->select('lead_groups.name', 'lead_groups.color_code')
                ->get()
                ->toArray();
            return response()->json([
                "success" => true,
                "message" => "Customer retrieved successfully.",
                "data" => $customers
            ], 201);
        }
    }

    public function activitySave(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $validator = Validator::make($input, [
                'activity_type' => 'required',
                'activity_name' => 'required',
//                'follow_up_datetime' => 'required',
                'id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);
            }

            $input['user_id'] = $this->logged_user->id;
            $input['company_id'] = $this->company_id;
            $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
            $customer_id = ($input['customer_id']) ? Crypt::decrypt($input['customer_id']) : $input['customer_id'];

            $customerData = Customer::select("assigned_to_user")->where('id', '=', $customer_id)->first();


            $paramArr['assigned_to'] = $customerData->assigned_to_user;
            $paramArr['customer_id'] = $customer_id;
            $paramArr['activity_type'] = $input['activity_type'];
            $paramArr['visit_address'] = $input['visit_address'];
            $paramArr['activity_name'] = $input['activity_name'];
            $paramArr['activity_notes'] = $input['activity_notes'];
            $paramArr['follow_up_datetime'] = (!empty($input['follow_up_datetime']) && $input['follow_up_datetime'] != '0000-00-00 00:00:00') ? Carbon::createFromFormat('d-m-Y H:i A', $input['follow_up_datetime'])->format('Y-m-d H:i:s') : '';
            $paramArr['entry_type'] = 'followup';
            $paramArr['is_modified'] = 1;
            $paramArr['user_id'] = $this->logged_user->id;
            $paramArr['company_id'] = $this->company_id;
            $paramArr['created_by'] = $this->logged_user->id;
            $paramArr['updated_by'] = $this->logged_user->id;

            if ($id == 0) {
                $tmp_customer_data = Customer::select("lead_stage_id")
                    ->where('id', '=', $customer_id)
                    ->first();
                if ($paramArr['follow_up_datetime'] && ((!Carbon::parse($paramArr['follow_up_datetime'])->eq(Carbon::parse($input['old_follow_up_date_at'])))|| $tmp_customer_data->lead_stage_id != $input['leads_stages_id_followup'] )) {
                    $customer_data = EstimateTimeline::select("id")
                        ->where('customer_id', '=', $customer_id)
                        ->wherein('activity_type', [9])
                        ->orderBy('id', 'DESC')
                        ->take(1)
                        ->get()
                        ->toArray();

                    if ($customer_data && $customer_data[0]['id']) {
                        $a = SalesPersonPerformances::where('timeline_id', $customer_data[0]['id'])->update(array('completed_task' => 1));
                    }
                }

                $paramArr['internal_remarks'] = "Activity added";
                $paramArr['internal_remarks'] = $input['activity_notes'];
                if (isset($input['follow_up_datetime']) && $input['old_follow_up_date_at'] != $paramArr['follow_up_datetime']) {
                    $activity_notes = $paramArr['activity_notes'];
                    $paramArr['internal_remarks'] = " <b>New follow up date</b> : " . $input['follow_up_datetime'] . "</br>";
                    $paramArr['internal_remarks'] .= " <b>Notes</b> : " . $input['activity_notes'];
//                    Customer::find($customer_id)->update(["new_lead_flag" => 0]);
                } //activity merge
                $estimateTimelineId = EstimateTimeline::create($paramArr);
                if (isset($input['follow_up_datetime']) && $input['old_follow_up_date_at'] != $paramArr['follow_up_datetime']) {
                    $paramArr['is_modified'] = 0;
                    $paramArr['is_follow_up'] = 0;
                    $paramArr['internal_remarks'] = "";
                    $paramArr['activity_type'] = 9;
                    $paramArr['activity_name'] = 'Follow Up';
                    if (isset($paramArr['follow_up_datetime']) && $input['old_follow_up_date_at'] != $paramArr['follow_up_datetime']) {
                        $paramArr['internal_remarks'] .= " <b>New follow up date</b> : " . $input['follow_up_datetime'] . "</br>";
                    }

                    if ($input['activity_notes'])
                        $paramArr['internal_remarks'] .= " <b>Notes</b> : " . $input['activity_notes'];

                    /*$paramArr['internal_remarks'] .= " Date : " . Carbon::createFromFormat('Y-m-d H:i:s', $input['old_follow_up_date_at'])->format('d-m-Y H:i:s') . " to " . $input['follow_up_datetime'];*/

                    $estimateTimelineId = EstimateTimeline::create($paramArr);

                    Customer::where('id', $customer_id)->update(array('some_day_flg' => 0));
                }

                if ($paramArr['follow_up_datetime'] && !(Carbon::parse($paramArr['follow_up_datetime'])->eq(Carbon::parse($input['old_follow_up_date_at'])))) {
                    if ($input['activity_type'] == 1 || $input['activity_type'] == 2 || $input['activity_type'] == 3 || $input['activity_type'] == 19) {
                        $customer_data = EstimateTimeline::select("id")
                            ->where('customer_id', '=', $customer_id)
                            ->wherein('activity_type', [9])
                            ->orderBy('id', 'DESC')
                            ->get()
                            ->toArray();


                        $insArr['timeline_id'] = $customer_data[0]['id'];
                        $insArr['customer_id'] = $customer_id;
                        $insArr['user_id'] = $this->logged_user->id;
                        $insArr['company_id'] = $this->company_id;
                        $insArr['performance_date'] = (!empty($input['follow_up_datetime']) && $input['follow_up_datetime'] != '0000-00-00 00:00:00') ? Carbon::createFromFormat('d-m-Y H:i A', $input['follow_up_datetime'])->format('Y-m-d') : date('Y-m-d');
                        $insArr['total_task'] = 1;
                        SalesPersonPerformances::create($insArr);
                    }
                }


                $lost_reason_id = ($input['lead_stage_data_id']==6)?$input['leads_stages_id_followup']:0;
                $lost_reason_others = ($input['lead_stage_data_id']==6)?$input['lost_reason_others_followup']:'';
                $customers = Customer::where('id', $customer_id)->update(["lead_stage_id" => $input['leads_stages_id_followup'],"lost_reason_id" =>$lost_reason_id,"others_reason" =>$lost_reason_others]);

                if($input['lead_stage_data_id']==6 && $tmp_customer_data->lead_stage_id != $input['leads_stages_id_followup']){
                    $customer_data = EstimateTimeline::select("id")
                        ->where('customer_id', '=', $input['id'])
                        ->wherein('activity_type', [9])
                        ->orderBy('id', 'DESC')
                        ->take(1)
                        ->get()
                        ->toArray();

                    if ($customer_data && $customer_data[0]['id']) {
                        $a = SalesPersonPerformances::where('timeline_id', $customer_data[0]['id'])->update(array('completed_task' => 1));
                    }

                    $logInput['follow_up_datetime'] = '0000-00-00 00:00:00';
                    $logInput['assigned_to'] = $this->logged_user->id;
                    $logInput['customer_id'] = $customer_id;
                    $logInput['entry_type'] = "Lead Lost";
                    $logInput['activity_name'] = "Lead Lost";
                    $logInput['activity_type'] = "Lead Lost";
                    $logInput['activity_type'] = 17;
                    $logInput['internal_remarks'] = ($lost_reason_others)?$lost_reason_others:$input['lost_reason_name'];
                    $logInput['user_id'] = $this->logged_user->id;
                    $logInput['company_id'] = $this->company_id;
                    $logInput['created_by'] = $this->logged_user->id;
                    $logInput['updated_by'] = $this->logged_user->id;
                    LogActivity::addToActivityLog($logInput);

                }
                if($input['lead_stage_data_id']==2 && $tmp_customer_data->lead_stage_id != $input['leads_stages_id_followup']){
                    /*$customer_data = EstimateTimeline::select("id")
                        ->where('customer_id', '=', $input['id'])
                        ->wherein('activity_type', [1, 2, 3, 9,19])
                        ->orderBy('id', 'DESC')
                        ->take(1)
                        ->get()
                        ->toArray();

                    if ($customer_data && $customer_data[0]['id']) {
                        $a = SalesPersonPerformances::where('timeline_id', $customer_data[0]['id'])->update(array('completed_task' => 1));
                    }*/
                    $logInput['follow_up_datetime'] = $paramArr['follow_up_datetime'];
                    $logInput['assigned_to'] = $this->logged_user->id;
                    $logInput['customer_id'] = $customer_id;
                    $logInput['entry_type'] = "Lead Won";
                    $logInput['activity_name'] = "Lead Won";
                    $logInput['activity_type'] = "Lead Won";
                    $logInput['activity_type'] = 18;
                    $logInput['internal_remarks'] = '';
                    $logInput['user_id'] = $this->logged_user->id;
                    $logInput['company_id'] = $this->company_id;
                    $logInput['created_by'] = $this->logged_user->id;
                    $logInput['updated_by'] = $this->logged_user->id;
                    LogActivity::addToActivityLog($logInput);

                }









            } else {
                $paramArr['internal_remarks'] = "Activity updated";
//echo "dsd"; //activity merge
                if (isset($input['follow_up_datetime']) && $input['old_follow_up_date_at'] != $paramArr['follow_up_datetime']) {
                  /*  echo "ok";
                    echo "ok";*/
                    $activity_notes = $paramArr['activity_notes'];
                    $paramArr['internal_remarks'] = " <b>New follow up date</b> : " . $input['follow_up_datetime'] . "</br>";
                    $paramArr['internal_remarks'] .= " <b>Notes</b> : " . $input['activity_notes'];
                }
                $customer = EstimateTimeline::find($id)->update($paramArr);
            }

            return response()->json(['success' => 'Successfully Saved!'], 201);
        }
    }

    public function LeadAssignedToUser(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $validator = Validator::make($input, [
                'id' => 'required',
                'assigned_to_user' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);
            }

            $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
            $user = User::where('id', '=', $input['assigned_to_user'])->select(["name", "device_key", "mobile_device_key"])->first();
            Customer::find($id)->update(["assigned_to_user" => $input['assigned_to_user'], "new_lead_flag" => 1]);
            $customerData = Customer::where('id', '=', $id)->select(["name"])->first();

            $logInput['follow_up_datetime'] = (!empty($input['follow_up_date_assign_user']) && $input['follow_up_date_assign_user'] != '0000-00-00 00:00:00') ? Carbon::createFromFormat('Y-m-d H:i:s', $input['follow_up_date_assign_user'])->format('Y-m-d H:i:s') : '0000-00-00 00:00:00';
            $logInput['assigned_to'] = $input['assigned_to_user'];
            $logInput['customer_id'] = $id;
            $logInput['entry_type'] = "assigned";
            $logInput['activity_type'] = 8;
            $logInput['internal_remarks'] = "Assigned to " . $user->name;
            $logInput['user_id'] = $this->logged_user->id;
            $logInput['company_id'] = $this->company_id;
            $logInput['created_by'] = $this->logged_user->id;
            $logInput['updated_by'] = $this->logged_user->id;
            LogActivity::addToActivityLog($logInput);

            SalesPersonPerformances::where([['customer_id', "=", $id], ['completed_task', '=', 0]])->update(array('user_id' => $input['assigned_to_user'], 'created_at' => date('Y-m-d H:i:s')));


            $noficationArr['customer_id'] = Crypt::decrypt($input['id']);
            $noficationArr['notification_type'] = "assign_to_you";
            $noficationArr['device_key'] = $user->device_key;
            $noficationArr['mobile_device_key'] = $user->mobile_device_key;
            $noficationArr['title'] = 'New Lead Assigned To You';
            $noficationArr['body'] = $customerData->name . ' is assigned to you by ' . $this->logged_user->name;
            $this->sendAssigntoUserNotification($noficationArr);
            return response()->json(['success' => 'Successfully Updated!'], 201);
        }
    }

    public function sendAssigntoUserNotification($requestArr = [])
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        /*$users = User::whereNotNull('device_key')->where('id',1)->select('device_key','mobile_device_key', 'id', 'company_id')->get();*/
        $serverKey = 'AAAAsImurqQ:APA91bEqYpdInZ9unkqrVIuF_GTFJHSbWY3T611Kj8qXe_amTYZB4AWrAOBIwPnUGyyqndFH4wQn7DczaUZYzEDTizHL0-cB_CSEHFuvJuQGG6ZKa-deTDIZnogh1CWMopqGHEGXOuQX';

        $FcmToken = array();
        if ($requestArr['device_key']) {
            $FcmToken[] = $requestArr['device_key'];
        }

        if ($requestArr['mobile_device_key']) {
            $FcmToken[] = $requestArr['mobile_device_key'];
        }
//dd($FcmToken);
        $data = [
            "registration_ids" => $FcmToken,
            "notification" => [
                "title" => $requestArr['title'],
                "body" => $requestArr['body'],
                "sound" => 'notification_sound.wav',
                "icon" => url('assets/images/logo.png'),
//                "click_action" => url('/lead/timeline/' . $requestArr['customer_id'])
//                "click_action" => $requestArr['customer_id']
                'click_action'=> 'FLUTTER_NOTIFICATION_CLICK',
            ],
            "data" =>[
                "type" => $requestArr['notification_type'],
                "lead_id" => $requestArr['customer_id']
            ],
            "priority" => 'high'
        ];
        $encodedData = json_encode($data);


        $headers = [
            'Authorization:key=' . $serverKey,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        // Close connection
        curl_close($ch);

        // FCM response
//        dd($result);
    }

//     public function leadTimelineActivity(Request $request)
//     {

//         $input = $request->all();

//         $id = Crypt::decrypt($input['id']);
// //        $validator = Validator::make($id, [
// //            'id' => 'required'
// //        ]);
// //        if ($validator->fails()) {
// //            return response()->json(['errors' => $validator->errors()->all()], 400);
// //        }

//         $timelineAcitvityies = EstimateTimeline::select('customer_timelines.*', 'users.name as created_by_name', DB::raw("DATE_FORMAT(customer_timelines.created_at, '%d %b, %Y %H:%i %p') as display_created_at"), DB::raw("DATE_FORMAT(customer_timelines.follow_up_datetime, '%d %b, %Y %H:%i %p') as display_follow_up_datetime"), 'customer_timelines.activity_estimate_status', 'customer_timelines.estimate_version_no', 'customer_timelines.id as activity_timeline_id','customers_views.currency_name_country_id','customers_views.est_currency_id')
//             ->leftJoin('users', 'customer_timelines.created_by', '=', 'users.id')
//             ->leftJoin('customers_views', 'customer_timelines.customer_id', '=', 'customers_views.id')
//             ->where('customer_timelines.customer_id', '=', $id)
//             ->where('customer_timelines.company_id', $this->company_id)
//             ->orderBy('customer_timelines.id', 'desc')
// //            ->where('status', 0)
//             ->paginate($input['start']);
//         foreach ($timelineAcitvityies as $key => $val) {

//             $country_data = Country::
//             join('estimates', 'estimates.est_currency_id', '=', 'countries.id')
//                 ->where("estimates.id", $timelineAcitvityies[$key]->estimate_id)
//                 ->select('name','currency_name','currency_code','currency_symbol')
// //                ->orderBy('id', 'DESC')
//                 ->first();

//             /*$country_data = [];
//             if($timelineAcitvityies[$key]->est_currency_id)
//                 $country_data = Country::where("id", $timelineAcitvityies[$key]->est_currency_id)->select('name','currency_name','currency_code','currency_symbol')->orderBy('id', 'DESC')->get()->first();*/
//             $timelineAcitvityies[$key]->estimate_id = Crypt::encrypt($timelineAcitvityies[$key]->estimate_id);
//             $timelineAcitvityies[$key]->content_id = Crypt::encrypt($timelineAcitvityies[$key]->content_id);
// //            $timelineAcitvityies[$key]->net_amount = ($country_data->currency_symbol) ? $country_data->currency_symbol.' ' . $timelineAcitvityies[$key]->net_amount : $timelineAcitvityies[$key]->net_amount;
//             /*$country_data = [];
//             if($timelineAcitvityies[$key]->est_currency_id)
//                 $country_data = Country::where("id", $timelineAcitvityies[$key]->est_currency_id)->select('name','currency_name','currency_code','currency_symbol')->orderBy('id', 'DESC')->first();*/

//             $timelineAcitvityies[$key]->currency_symbol = (isset($country_data->currency_symbol))?$country_data->currency_symbol:'';
// //            $timelineAcitvityies[$key]->currency_symbol = (isset($country_data->currency_symbol))?$country_data->currency_symbol:'';
//             $timelineAcitvityies[$key]->aws_path = ($timelineAcitvityies[$key]->estimate_version_no)?Storage::disk('s3')->url('public/'.$this->company_id.'/documents/'.$timelineAcitvityies[$key]->estimate_version_no.'.pdf'):null;
//              /*$timelineAcitvityies[$key]->aws_path =  ($timelineAcitvityies[$key]->estimate_version_no)?url(Storage::url('public/document/'.$this->company_id.'/'.$timelineAcitvityies[$key]->estimate_version_no)):null;*/
//         }
//         return response()->json($timelineAcitvityies);

// //print_r($duplicateLeads);
// //        echo $duplicateLeads[0]->cnt;
// //die;

//     }

    public function leadTimelineActivity(Request $request)
    {
        // Decrypt and retrieve the customer ID
        $id = Crypt::decrypt($request->input('id'));

        // Define the base query for timeline activities
        $timelineActivities = DB::table('customer_timelines')
            ->leftJoin('users', 'customer_timelines.created_by', '=', 'users.id')
            ->leftJoin('customers_views', 'customer_timelines.customer_id', '=', 'customers_views.id')
            ->leftJoin('estimates', 'customer_timelines.estimate_id', '=', 'estimates.id')
            ->leftJoin('countries', 'estimates.est_currency_id', '=', 'countries.id')
            ->select(
                'customer_timelines.*',
                'users.name as created_by_name',
                DB::raw("DATE_FORMAT(customer_timelines.created_at, '%d %b, %Y %H:%i %p') as display_created_at"),
                DB::raw("DATE_FORMAT(customer_timelines.follow_up_datetime, '%d %b, %Y %H:%i %p') as display_follow_up_datetime"),
                'customer_timelines.activity_estimate_status',
                'customer_timelines.estimate_version_no',
                'customer_timelines.id as activity_timeline_id',
                'countries.currency_name',
                'countries.currency_code',
                'countries.currency_symbol'
            )
            ->where('customer_timelines.customer_id', '=', $id)
            ->where('customer_timelines.company_id', $this->company_id)
            ->where('customer_timelines.company_id', '=', $this->company_id)
            ->orderBy('customer_timelines.id', 'desc')
            ->paginate($request->input('start')); // Paginate based on 'start' parameter

        // Process the timeline activities
        foreach ($timelineActivities as $activity) {
            // Encrypt fields
            $activity->estimate_id = Crypt::encrypt($activity->estimate_id);
            $activity->content_id = Crypt::encrypt($activity->content_id);

            // Set AWS path for the estimate PDF document
            $activity->aws_path = $activity->estimate_version_no
                ? Storage::disk('s3')->url('public/' . $this->company_id . '/documents/' . $activity->estimate_version_no . '.pdf')
                : null;
            // If the country symbol is available, prepend it to the net amount
            $activity->currency_symbol = $activity->currency_symbol ?? '';
        }

        // Return the processed timeline activities
        return response()->json($timelineActivities);
    }

    public function activityShow(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
//            $id = Crypt::decrypt($input['id']);
            $id = $input['id'];
            $validator = Validator::make($input, [
                'id' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);
            }
            $user = Auth::user();

            $activities = EstimateTimeline::find($id)->toArray();

            if (is_null($activities)) {
                return response()->json(['success' => 'Activity timeline not found!'], 422);
            }
            $activities['id'] = Crypt::encrypt($activities['id']);
            if ($activities['follow_up_datetime'] != "0000-00-00 00:00:00") {
                $activities['follow_up_datetime'] = Carbon::createFromFormat('Y-m-d H:i:s', $activities['follow_up_datetime'])
                    ->format('d-m-Y H:i A');
            }
            return response()->json([
                "success" => true,
                "message" => "Activity timeline retrieved successfully.",
                "data" => $activities
            ], 201);
        }
    }

    public function activityDestroy(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $validator = Validator::make($input, [
                'id' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);
            }


//            $id = Crypt::decrypt($input['id']);
            $id = $input['id'];
            EstimateTimeline::where('id', $id)->delete();

            return response()->json(['success' => 'Activity Deleted!'], 201);
        }
    }

    public function getFollowup()
    {

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');


        /*// Total records
        $records = DB::table('customers_views as cv')
            ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
            ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
            ->select('cv.*', DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code'))
            ->where('cv.company_id', $this->company_id)
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('cv.assigned_to_user', '=', $this->logged_user->id);
                    $query->orwhere('cv.user_id', '=', $this->logged_user->id);
                }
            })
            ->groupBy('cv.id')
            ->orderBy('cv.last_follow_up_datetime', 'DESC')
            ->get();


        $data = array();
        $i = 0;
        $followups['overdue'] = array();
        $followups['duetoday'] = array();
        $followups['upcoming'] = array();
        $followups['nofollowup'] = array();
        $followups['someday'] = array();
        foreach ($records as $record) {

            $id = Crypt::encrypt($record->id);
            $customer_type = $record->customer_type;
            $name = $record->name;
            $lead_origin = $record->lead_origin;
            $lead_category = $record->lead_category;
            $email = $record->email;
            $phone_no = $record->phone_no;
            $address = $record->address;
            $pincode = $record->pincode;
            $description = $record->description;
            $status = $record->status;
            $country_name = $record->country_name;
            $state_name = $record->state_name;
            $city_name = $record->city_name;
            $last_activity = $record->last_activity;
            $assign_user_name = $record->user_name;
            $assigned_to_user = $record->assigned_to_user;
            $net_amount = $record->net_amount;
            $estimate_status = $record->estimate_status;
            $last_activity_type = $record->last_activity_type;
            $last_internal_remarks = $record->last_internal_remarks;
            $last_activity_name = $record->last_activity_name;
            $date_added = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)
                ->format('d-m-Y h:i A');

            $follow_up_datetime = ($record->last_follow_up_datetime && $record->last_follow_up_datetime != '0000-00-00 00:00:00') ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->last_follow_up_datetime)->format('d-m-Y h:i A') : '';

            $i++;
            $labelName = '';
            if ($record->label_name) {
                $leadLabelNameArr = explode(',', $record->label_name);
                $labelColorCodeArr = explode(',', $record->label_color_code);
                foreach ($leadLabelNameArr as $key => $labelLabel) {
                    $st = '';
                    if ($key % 2 == 0) {
                        $st = '<br>';
                    }
                    $labelName .= '<span class="fs-6 badge me-2" style = "background-color: ' . $labelColorCodeArr[$key] . '">' . $labelLabel . '</span> ' . $st;
                }
            }

            $temp = 'overdue';
            if (strtotime(date('d-m-Y', strtotime($follow_up_datetime))) > strtotime(date('d-m-Y')) && $record->some_day_flg == 0) {
                $temp = 'upcoming';
            }

            if (strtotime(date('d-m-Y', strtotime($follow_up_datetime))) == strtotime(date('d-m-Y')) && $record->some_day_flg == 0) {
                $temp = 'duetoday';
                $follow_up_datetime = 'Today - ' . \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->last_follow_up_datetime)
                        ->format('h:i A');
            }

            if ($follow_up_datetime == '' && $record->some_day_flg == 0) {
                $temp = 'nofollowup';
                $follow_up_datetime = '';
            }

            if ($record->some_day_flg == 1) {
                $temp = 'someday';
                $follow_up_datetime = '';
            }
            $followups[$temp][] = array(
                "id" => $i,
                "name" => $name,
                "lead_origin" => $lead_origin,
                "lead_category" => $lead_category,
                "created_at" => $date_added,
                "customer_type" => $customer_type,
                "email" => $email,
                "phone_no" => $phone_no,
                "address" => $address,
                "pincode" => $pincode,
                "country_name" => $country_name,
                "state_name" => $state_name,
                "city_name" => $city_name,
                "description" => $description,
                "last_activity" => $last_activity,
                "last_activity_type" => $last_activity_type,
                "last_internal_remarks" => $last_internal_remarks,
                "last_activity_name" => $last_activity_name,
                "time_ago_string" => $this->timeAgoStringFun($record->last_activity_updated_at),
                "assign_user_name" => $assign_user_name,
                "assigned_to_user" => $assigned_to_user,
                "net_amount" => $net_amount,
                "status" => $status,
                "label_name" => $labelName,
                "estimate_status" => $estimate_status,
                "follow_up_datetime" => $follow_up_datetime,
                "action" => $id,
            );
        }*/
        $teamUsers = User::select(["name", "id", "email", "mobile_no"])
            ->where('invite_status', 1)
            ->where(function ($query) {
                $query->orwhere('company_id', $this->company_id);
                $query->orwhere('id', $this->company_id);
            })
            ->get();
        $leadGroups = $data = DB::table('lead_groups')
            ->leftJoin('customer_labels', 'lead_groups.id', '=', 'customer_labels.label_id')
            ->select('lead_groups.*', DB::raw('count(customer_labels.id) as lead_count'))
            ->where('lead_groups.company_id', $this->company_id)
            ->where('lead_groups.status', 0)
            ->orderBy('lead_groups.name', 'asc')
            ->groupBy('lead_groups.id')
            ->get();

        $customerCategories = CustomerCategory::select(["name", "id"])->where('status', '=', 0)->where('company_id', $this->company_id)->get();
        $customerLeads = CustomerLead::select(["name", "id"])
            ->where('status', '=', 0)
            ->where(function ($query) {
                $query->where('company_id', '=', $this->company_id);
                $query->orwhere('is_status', '=', 1);
            })
//            ->where('company_id', $this->company_id)
            ->get();

        $leadStages = LeadStage::select("*")
            ->where('status', '=', 0)
            ->where(function ($query) {
                $query->where('company_id', '=', $this->company_id);
//                $query->orwhere('status', '=', 1);
            })
            ->orderBy('priority', 'asc')
            ->get();

        $leadGroups = $data = DB::table('lead_groups')
            ->leftJoin('customer_labels', 'lead_groups.id', '=', 'customer_labels.label_id')
            ->select('lead_groups.*', DB::raw('count(customer_labels.id) as lead_count'))
            ->where('lead_groups.company_id', $this->company_id)
            ->where('lead_groups.status', 0)
            ->orderBy('lead_groups.name', 'asc')
            ->groupBy('lead_groups.id')
            ->get();
        $teamUsers = User::select(["name", "id", "email", "mobile_no"])
            ->where('invite_status', 1)
            ->where(function ($query) {
                $query->orwhere('company_id', $this->company_id);
                $query->orwhere('id', $this->company_id);
            })
            ->get();
        $leads = User::select(["name", "id", "email", "mobile_no"])
//            ->where('status', 'Approved')
            ->where('invite_status', 1)
//            ->where('company_id', $this->company_id)
            ->where(function ($query) {
                $query->orwhere('company_id', $this->company_id);
                $query->orwhere('id', $this->company_id);
            })
            ->get();
        $countries = Country::select(["name", "id", "phonecode","sortname","currency_name","currency_code","currency_symbol"])->where('status', '=', 0)->orderBy('name','ASC')->get();

        $fil_states = State::select("*")->where('status', '=', 0)->orderBy('name','ASC')->get();
        $leadLabels = LeadGroup::select(["name", "color_code", "id"])->where('status', '=', 0)->where('company_id', $this->company_id)->get();
        $lostReasons = LostReason::select("*")
            ->where('status', '=', 0)
            ->where(function ($query) {
                $query->where('company_id', '=', $this->company_id);
//                $query->orwhere('status', '=', 1);
            })
            ->orderBy('name', 'asc')
            ->get();
        $segment = $this->segment;
        return view('app.follow-up-history-new', compact('customerCategories', 'customerLeads', 'leadGroups', 'teamUsers','leads', 'leadStages', 'countries', 'fil_states','leadLabels','lostReasons', 'segment'))->with('user_perm', $this->user_perm);
    }

    public function activityFollowupSave(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $validator = Validator::make($input, [
//                'activity_type' => 'required',
//                'activity_name' => 'required',
                'follow_up_datetime_status' => 'required',
                'id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);
            }

            $input['user_id'] = $this->logged_user->id;
            $input['company_id'] = $this->company_id;
            $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
            if ($input['est_flag'] == 'c')
                $customer_id = $input['customer_id'];
            else
                $customer_id = ($input['customer_id']) ? Crypt::decrypt($input['customer_id']) : $input['customer_id'];

            $customerData = Customer::select("assigned_to_user")->where('id', '=', $customer_id)->first();

            if ($input['estimate_id'] > 0) {
                $paramArr['estimate_id'] = $input['estimate_id'];
            }
            $paramArr['assigned_to'] = $customerData->assigned_to_user;
            $paramArr['customer_id'] = $customer_id;
            $paramArr['activity_type'] = 9;
            $paramArr['activity_name'] = 'Follow Up';
            $paramArr['activity_notes'] = $input['activity_notes'];
//            $paramArr['follow_up_datetime'] = Carbon::createFromFormat('d-m-Y H:i A', $input['follow_up_datetime_status'])->format('Y-m-d H:i:s');
            $paramArr['follow_up_datetime'] = (!empty($input['follow_up_datetime_status']) && $input['follow_up_datetime_status'] != '0000-00-00 00:00:00') ? Carbon::createFromFormat('d-m-Y H:i A', $input['follow_up_datetime_status'])->format('Y-m-d H:i:s') : '';
            $paramArr['entry_type'] = 'followup';
            $paramArr['is_modified'] = 1;
            $paramArr['is_follow_up'] = 1;
            $paramArr['user_id'] = $this->logged_user->id;
            $paramArr['company_id'] = $this->company_id;
            $paramArr['created_by'] = $this->logged_user->id;
            $paramArr['updated_by'] = $this->logged_user->id;

//            if ($id == 0) {
            $paramArr['internal_remarks'] = "";
            if (isset($input['est_flag']) && $input['est_flag'] == 'c') {
//                if (isset($input['customRadio1']) && $input['customRadio1']) {
                Estimate::where('id', $input['estimate_id'])->update(array('status' => "Inprogress"));

                /*$activitylastId = DB::table('customer_timelines')->latest()->value('id');
                EstimateTimeline::where('id', $activitylastId)->update(array('activity_estimate_status' => "Inprogress"));*/
                $activitylastId = DB::table('customer_timelines')->orderby('id', 'desc')->where('activity_type', 5)->first();
                EstimateTimeline::where('id', $activitylastId->id)->update(array('activity_estimate_status' => "Inprogress", "internal_remarks" => trim('<b>New follow up date</b> : ' . $input['follow_up_datetime_status'] . '</br><b>Notes : </b>' . $input['activity_notes'], ', ')));
//                    $paramArr['internal_remarks'] .= " <b>Status updated</b> : " . $input['old_status'] . " to " . $input['customRadio1']."</br>";
//                    $paramArr['internal_remarks'] .= " <b>Status </b> : Inprogress </br>";
//                        EstimateTimeline::create($paramArr);
//                }
//                $paramArr['activity_notes'] = trim('<b>New follow up date</b> : ' . $input['follow_up_datetime_status'] . '</br><b>Status </b> : Inprogress </br><b>Notes : </b>' . $input['activity_notes'], ', ');
                $paramArr['internal_remarks'] = trim('<b>New follow up date</b> : ' . $input['follow_up_datetime_status'] . '</br><b>Notes : </b>' . $input['activity_notes'], ', ');

//                Customer::find($customer_id)->update(["new_lead_flag" => 0]);

                if(isset($input['fl_lead_stage_id'])){
                    $customers = Customer::where('id', $customer_id)->update(["lead_stage_id" => $input['fl_lead_stage_id']]);
                }

            }

//            $customer = EstimateTimeline::create($paramArr);

            if (isset($input['est_flag']) && $input['est_flag'] == 'u') {
                $paramArr['is_follow_up'] = 0;
            }


            if (isset($input['est_flag']) && $input['est_flag'] == 'u') {

//                if ((isset($input['old_status']) && $input['old_status'] != $input['customRadio1']) || (isset($paramArr['follow_up_datetime']) && !empty($input['follow_up_datetime_status']) && $input['follow_up_datetime_status'] != '0000-00-00 00:00:00' && $input['old_follow_up_date'] != $paramArr['follow_up_datetime'])) {
                if ((isset($paramArr['follow_up_datetime']) && !empty($input['follow_up_datetime_status']) && $input['follow_up_datetime_status'] != '0000-00-00 00:00:00' && $input['old_follow_up_date'] != $paramArr['follow_up_datetime'])) {

                    $paramArr['is_modified'] = 1;
                    $paramArr['is_follow_up'] = 0;

                    /* if (isset($input['old_status']) && $input['old_status'] != $input['customRadio1']) {
                         Estimate::where('id', $input['estimate_id'])->update(array('status' => $input['customRadio1']));
                         $paramArr['internal_remarks'] .= " <b>Status updated</b> : " . $input['old_status'] . " to " . $input['customRadio1'] . "</br>";
                     }*/
                    if (isset($paramArr['follow_up_datetime']) && $input['old_follow_up_date'] != $paramArr['follow_up_datetime']) {
                        $paramArr['internal_remarks'] .= " <b>New follow up date</b> : " . $input['follow_up_datetime_status'] . "</br>";
//                        Customer::find($customer_id)->update(["new_lead_flag" => 0]);
                    }
                }
                if ($input['activity_notes'])
                    $paramArr['internal_remarks'] .= " <b>Notes</b> : " . $input['activity_notes'];
            }

            if ($paramArr['follow_up_datetime']) {
                $customer_data = EstimateTimeline::select("id")
                    ->where('customer_id', '=', $customer_id)
                    ->wherein('activity_type', [9])
                    ->orderBy('id', 'DESC')
                    ->take(1)
                    ->get()
                    ->toArray();

                if ($customer_data && $customer_data[0]['id']) {
                    $a = SalesPersonPerformances::where('timeline_id', $customer_data[0]['id'])->update(array('completed_task' => 1));
                }
            }

            $customer = EstimateTimeline::create($paramArr);
            Customer::where('id', $customer_id)->update(array('some_day_flg' => 0));
            /* } else {
                 $paramArr['internal_remarks'] = "Activity updated";
                 $customer = EstimateTimeline::find($id)->update($paramArr);
             }*/
            if ($paramArr['follow_up_datetime']) {
                $customer_data = EstimateTimeline::select("id")
                    ->where('customer_id', '=', $customer_id)
                    ->wherein('activity_type', [9])
                    ->orderBy('id', 'DESC')
                    ->get()
                    ->toArray();


                $insArr['timeline_id'] = $customer_data[0]['id'];
                $insArr['customer_id'] = $customer_id;
                $insArr['user_id'] = $this->logged_user->id;
                $insArr['company_id'] = $this->company_id;
                $insArr['performance_date'] = (!empty($input['follow_up_datetime_status']) && $input['follow_up_datetime_status'] != '0000-00-00 00:00:00') ? Carbon::createFromFormat('d-m-Y H:i A', $input['follow_up_datetime_status'])->format('Y-m-d') : date('Y-m-d');
                $insArr['total_task'] = 1;
                SalesPersonPerformances::create($insArr);
            }
            if ($input['estimate_id'] > 0) {
                $paramArr['estimate_id'] = Crypt::encrypt($paramArr['estimate_id']);
            } else {
                $paramArr['estimate_id'] = 0;
            }
            return response()->json(['success' => 'Successfully Saved!', 'customer_id' => Crypt::encrypt($customer_id), 'url' => url('quotes/edit/' . $paramArr['estimate_id'])], 201);
        }
    }

    public function activityChangeEstimateStatusSave(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $validator = Validator::make($input, [
//                'activity_type' => 'required',
//                'activity_name' => 'required',
//                'follow_up_datetime_status' => 'required',
                'activity_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);
            }

            $input['user_id'] = $this->logged_user->id;
            $input['company_id'] = $this->company_id;
            $paramArr['id'] = $input['activity_id'];
            $paramArr['estimate_id'] = ($input['activity_estimate_id']) ? Crypt::decrypt($input['activity_estimate_id']) : $input['activity_estimate_id'];
            $old_activity_status = $input['old_activity_status'];
            $old_activity_follow_up_date = $input['old_activity_follow_up_date'];
            $paramArr['customer_id'] = ($input['activity_customer_id']) ? Crypt::decrypt($input['activity_customer_id']) : $input['activity_customer_id'];
            $paramArr['activity_notes'] = $input['activity_estimate_notes'];
            $paramArr['estimate_version_no'] = $input['activity_estimate_no'];
            $customerData = Customer::select("assigned_to_user")->where('id', '=', $paramArr['customer_id'])->first();
            $paramArr['assigned_to'] = $customerData->assigned_to_user;
            $paramArr['activity_type'] = 10;
            $paramArr['activity_name'] = 'Status Updated';
            $paramArr['activity_notes'] = $input['activity_estimate_notes'];
            $paramArr['activity_estimate_status'] = $input['activity_estimate_status'];
            $paramArr['follow_up_datetime'] = $input['old_activity_follow_up_date'];
            $follow_up_datetime = (!empty($input['old_activity_follow_up_date']) && $input['old_activity_follow_up_date'] != '0000-00-00 00:00:00') ? Carbon::createFromFormat('Y-m-d H:i:s', $input['old_activity_follow_up_date'])->format('d-m-Y H:i A') : '';

            $paramArr['entry_type'] = 'followup';
            $paramArr['is_modified'] = 1;
            $paramArr['is_follow_up'] = 0;
            $paramArr['user_id'] = $this->logged_user->id;
            $paramArr['company_id'] = $this->company_id;
            $paramArr['created_by'] = $this->logged_user->id;
            $paramArr['updated_by'] = $this->logged_user->id;
            EstimateTimeline::where('id', $paramArr['id'])->update(array('activity_estimate_status' => $paramArr['activity_estimate_status']));
            $testTemp = "<b>Estimate : </b>" . $paramArr['estimate_version_no'];

            if (isset($old_activity_status) && $paramArr['activity_estimate_status'] != $old_activity_status) {
                $testTemp .= "</br><b>Status updated</b> : " . $old_activity_status . " to " . $paramArr['activity_estimate_status'];

            }

            if ($input['activity_estimate_notes']) {
                $testTemp .= "</br><b>Notes</b> : " . $input['activity_estimate_notes'];
            }

            $paramArr['internal_remarks'] = $testTemp;
        }

        $estimate = Estimate::select(["estimate_version", "estimate_no"])->where('id', $paramArr['estimate_id'])->get()->first();
        $tmp_est_name = '';
        if ($estimate->estimate_version > 0) {
            $tmp_est_name = '-V' . $estimate->estimate_version;
        }
        if ($input['activity_estimate_no'] == $estimate->estimate_no . $tmp_est_name)
            Estimate::where('id', $paramArr['estimate_id'])->update(array('status' => $paramArr['activity_estimate_status']));

        $customer = EstimateTimeline::create($paramArr);
        /* } else {
             $paramArr['internal_remarks'] = "Activity updated";
             $customer = EstimateTimeline::find($id)->update($paramArr);
         }*/

        $tempEst = Estimate::where('estimate_no', '=', $estimate->estimate_no)->where('company_id', $this->company_id)->where(function ($query) {
            /*if ($id != 0) {
                $query->Where(function ($query) use ($id) {
                    $query->where('id', '!=', $id);
                });
            }*/
        })->select("id", "estimate_no")->get();
        if ($tempEst) {
            foreach ($tempEst as $val_est) {
                if ($paramArr['estimate_id'] != $val_est->id && ($paramArr['activity_estimate_status'] == "Accept" || $paramArr['activity_estimate_status'] == "Decline")) {
                    Estimate::where([['company_id', '=', $this->company_id], ["id", "=", $val_est->id]])->update(array('status' => ''));
                    EstimateTimeline::where('estimate_id', $val_est->id)->update(array('activity_estimate_status' => ''));
                }

                if ($paramArr['activity_estimate_status'] == "Inprogress") {
                    Estimate::where([['company_id', '=', $this->company_id], ["id", "=", $val_est->id]])->update(array('status' => 'Inprogress'));
                    EstimateTimeline::where('estimate_id', $val_est->id)->update(array('activity_estimate_status' => 'Inprogress'));
                }
            }
        }
        return response()->json(['success' => 'Successfully Saved!'], 201);
    }

    public function removeFollowUpDate(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();

            $paramArr['id'] = $input['id'];

            $customer = EstimateTimeline::where('id', $paramArr['id'])->select('customer_id')->first();
            EstimateTimeline::where('id', $paramArr['id'])->update(array('follow_up_datetime' => "0000-00-00 00:00:00"));
            Customer::where('id', $customer->customer_id)->update(array('some_day_flg' => 0));

            $customer_data = EstimateTimeline::select("id")
                ->where('customer_id', '=', $customer->customer_id)
                ->wherein('activity_type', [9])
                ->orderBy('id', 'DESC')
                ->take(1)
                ->get()
                ->toArray();

            if ($customer_data && $customer_data[0]['id']) {
                $a = SalesPersonPerformances::where('timeline_id', $customer_data[0]['id'])->update(array('completed_task' => 1));
            }

            $logInput['follow_up_datetime'] = '0000-00-00 00:00:00';
            $logInput['assigned_to'] = $this->logged_user->id;
            $logInput['customer_id'] = $customer->customer_id;
            $logInput['entry_type'] = "remove follow up";
            $logInput['activity_name'] = "Remove Follow Up";
            $logInput['activity_type'] = "Remove Follow Up";
            $logInput['activity_type'] = 14;
            $logInput['internal_remarks'] = $input['textarea'];
            $logInput['user_id'] = $this->logged_user->id;
            $logInput['company_id'] = $this->company_id;
            $logInput['created_by'] = $this->logged_user->id;
            $logInput['updated_by'] = $this->logged_user->id;
            LogActivity::addToActivityLog($logInput);

            Customer::find($customer->customer_id)->update(["new_lead_flag" => 0]);
        }
        return response()->json(['success' => 'Successfully Saved!'], 201);
    }

    public function setSomedayFollowUp(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
            Customer::where('id', $id)->update(array('some_day_flg' => 1));

            $customer_data = EstimateTimeline::select("id")
                ->where('customer_id', '=', $id)
                ->wherein('activity_type', [9])
                ->orderBy('id', 'DESC')
                ->take(1)
                ->get()
                ->toArray();

            if ($customer_data && $customer_data[0]['id']) {
                $a = SalesPersonPerformances::where('timeline_id', $customer_data[0]['id'])->update(array('completed_task' => 1));

            }

            $logInput['follow_up_datetime'] = '0000-00-00 00:00:00';
            $logInput['assigned_to'] = $this->logged_user->id;
            $logInput['customer_id'] = $id;
            $logInput['entry_type'] = "Someday follow up";
            $logInput['activity_name'] = "Someday follow up";
            $logInput['activity_type'] = "Someday Follow Up";
            $logInput['activity_type'] = 15;
            $logInput['internal_remarks'] = $input['textarea'];
            $logInput['user_id'] = $this->logged_user->id;
            $logInput['company_id'] = $this->company_id;
            $logInput['created_by'] = $this->logged_user->id;
            $logInput['updated_by'] = $this->logged_user->id;
            LogActivity::addToActivityLog($logInput);
            return response()->json(['success' => 'Successfully Saved!'], 201);
        }
    }

    /*public function preview_import_lead(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $validator = Validator::make(
                [
                    'file' => $request->file,
                    'extension' => strtolower($request->file->getClientOriginalExtension()),
                ],
                [
                    'file' => 'required',
                    'extension' => 'required|in:csv,xlsx,xls',
                ]
            );
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);
            }

            $fileArr = (new CustomerLeadPreview)->toArray($request->file);
            if (count($fileArr[0]) <= '200') {
                $user = Auth::user();
                $companyId = isset($user->company_id) ? $user->company_id : $user->id;
                foreach ($fileArr[0] as $key => $row) {

                    $errors = [];
                    $fileArr[0][$key]['duplicate'] = false;
                    $fileArr[0][$key]['email_valid'] = true;
                    $fileArr[0][$key]['state_name'] = "";
                    foreach ($fileArr[0] as $search_key => $search_array) {
                        if ($search_array['phone_no'] == $row['phone_no']) {
                            if ($search_key != $key) {
                                $fileArr[0][$key]['duplicate'] = true;
                                //array_push($errors, "Duplicate records found");
                            }
                        }

                        $existData12 = Customer::where('phone_no', '=', $row['phone_no'])
                            ->where('company_id', $companyId)
                            ->orderBy('id','DESC')
                            ->first();
                        if($existData12){
                            $fileArr[0][$key]['duplicate'] = true;
                        }
                    }
                    if ($row['name'] == "") {
                        array_push($errors, "Name is required");
                    }
                    if ($row['phone_no'] == "") {
                        array_push($errors, "Phone number is required");
                    } else {
                        if (!preg_match('/^[0-9]+$/', $row['phone_no'])) {
                            array_push($errors, "Phone number is invalid");
                        }
                    }
                    if ($row['country_code'] == "") {
                        array_push($errors, "Country code is required");
                    }
                    if ($row['email'] != "") {
                        if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                            array_push($errors, "Email is not valid");
                            $fileArr[0][$key]['email_valid'] = false;
                        }
                    }
                    if($row['city']!=""){
                        $fileArr[0][$key]['city_name'] = $row['city'];
                    }
                    if($row['gst_no']!=""){
                        $fileArr[0][$key]['gst_no'] = $row['gst_no'];
                    }
                    if($row['state']!=""){
                        $state = State::select(['id','country_id'])->where(function ($query) use ($row) {
                            $query->where('name', $row['state']);
                        })->first();
                        if(!empty($state)){
                            $fileArr[0][$key]['state_id'] = $state['id'];
                        }
                    }
                    $fileArr[0][$key]['country_id'] = "101";
                    if($row['country']!=""){
                        $country = Country::select(['id'])->where(function ($query) use ($row) {
                            $query->where('name', $row['country']);
                            $query->orWhere('sortname', $row['country']);
                        })->first();
                        if(!empty($country)){
                            $fileArr[0][$key]['country_id'] = $country['id'];
                        }
                    }

                    if($row['lead_origin']!=""){
                        $lead_id = CustomerLead::select('id')->where(function ($query) use ($row,$companyId) {
                            $query->where('name', $row['lead_origin']);
                            $query->where('company_id', $companyId);
                        })->first();
                        if(!empty($lead_id)){
                            $fileArr[0][$key]['customer_lead_id'] = $lead_id['id'];
                        }
                    }
                    $fileArr[0][$key]['errors'] = $errors;
                }
                if (!empty($fileArr)) {
                    return response()->json(['success' => 'Successfully Saved!', 'data' => $fileArr[0]], 201);
                } else {
                    return response()->json(['errors' => "No data found in excelsheet"], 400);
                }
            } else {
                return response()->json(['errors' => "You can import maximum 200 lead at a time."], 400);
            }
        }
    }*/

    public function preview_import_lead(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $validator = Validator::make(
                [
                    'file' => $request->file,
                    'extension' => strtolower($request->file->getClientOriginalExtension()),
                ],
                [
                    'file' => 'required',
                    'extension' => 'required|in:csv,xlsx,xls',
                ]
            );

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);
            }

            $fileArr = (new CustomerLeadPreview)->toArray($request->file);

            if (count($fileArr[0]) > 200) {
                return response()->json(['errors' => "You can import a maximum of 200 leads at a time."], 400);
            }

            $user = Auth::user();
            $companyId = $user->company_id ?? $user->id;

            $phoneNumbers = array_column($fileArr[0], 'phone_no');
            $existingCustomers = Customer::whereIn('phone_no', $phoneNumbers)
                ->where('company_id', $companyId)
                ->orderBy('id', 'DESC')
                ->get()
                ->keyBy('phone_no');

            foreach ($fileArr[0] as $key => &$row) {
                $row['duplicate'] = false;
                $row['email_valid'] = true;
                $row['state_name'] = "";
                $errors = $this->validateRow($row, $key, $fileArr[0], $existingCustomers);

                $row['errors'] = $errors;
            }

            if (!empty($fileArr)) {
                return response()->json(['success' => 'Successfully Saved!', 'data' => $fileArr[0]], 201);
            } else {
                return response()->json(['errors' => "No data found in excelsheet"], 400);
            }
        }
    }

    private function validateRow(&$row, $key, $fileArr, $existingCustomers)
    {
        $errors = [];

        foreach ($fileArr as $search_key => $search_array) {
            if ($search_array['phone_no'] == $row['phone_no'] && $search_key != $key) {
                $row['duplicate'] = true;
            }

            if (isset($existingCustomers[$row['phone_no']])) {
                $row['duplicate'] = true;
            }
        }

        if (empty($row['name'])) {
            $errors[] = "Name is required";
        }
        if (empty($row['phone_no'])) {
            $errors[] = "Phone number is required";
        } elseif (!preg_match('/^[0-9]+$/', $row['phone_no'])) {
            $errors[] = "Phone number is invalid";
        }
        if (empty($row['country_code'])) {
            $errors[] = "Country code is required";
        }
        if (!empty($row['email']) && !filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email is not valid";
            $row['email_valid'] = false;
        }
        if (!empty($row['city'])) {
            $row['city_name'] = $row['city'];
        }
        if (!empty($row['gst_no'])) {
            $row['gst_no'] = $row['gst_no'];
        }
        if (!empty($row['state'])) {
            $state = State::select(['id', 'country_id'])->where('name', $row['state'])->first();
            if ($state) {
                $row['state_id'] = $state['id'];
            }
        }
        $row['country_id'] = "101";
        if (!empty($row['country'])) {
            $country = Country::select(['id'])
                ->where('name', $row['country'])
                ->orWhere('sortname', $row['country'])
                ->first();
            if ($country) {
                $row['country_id'] = $country['id'];
            }
        }
        if (!empty($row['lead_origin'])) {
            $lead_id = CustomerLead::select('id')
                ->where('name', $row['lead_origin'])
                ->where('company_id', $this->company_id)
                ->first();
            if ($lead_id) {
                $row['customer_lead_id'] = $lead_id['id'];
            }
        }

        return $errors;
    }

    public function import_lead(Request $request)
    {
        //echo "<pre>";print_r($request);exit;
        if ($request->ajax()) {
            $input = $request->all();
            $failures_data_array = [];
            $faile_name = 'lead_export_error_report' . date('Y-m-d-H-i-s') . '.xlsx';
            foreach ($input as $key => $value) {
                if (empty($value['errors'])) {
                    unset($value['errors']);
                    $logInput['internal_remarks'] = "Lead added by excel";
                    $input['assigned_to_user'] = $this->logged_user->id;
                    $value['user_id'] = $this->logged_user->id;
                    $value['assigned_to_user'] = $this->logged_user->id;
                    $value['company_id'] = $this->company_id;
                    $value['country_code'] = "+".$value['country_code'];
                    $value['whatsapp_country_code'] = $value['country_code'];
                    $country_data = Country::where("id", $value['country_id'])->select('name','currency_name','currency_code','currency_symbol')->orderBy('id', 'DESC')->get()->first();
                    $value['currency_name'] = $country_data->currency_code;
                    $value['currency_name_country_id'] = $value['country_id'];
                    $value['phone_no_country_id'] = $value['country_id'];
                    $value['whatsapp_no_country_id'] = $value['country_id'];
                    $value['whatsapp_no'] = $value['phone_no'];
                    $value['new_lead_flag'] = 1;

                    $value['lead_stage_id'] = 0;
                    $lead_stage_data = LeadStage::where('company_id',$this->company_id)->where('is_default',1)->select('name','id')->first();
                    if($lead_stage_data){
                        $value['lead_stage_id'] =$lead_stage_data->id;
                    }

                    $customer = Customer::create($value);
                    $value['new_lead_flag'] = 1;
                    $ids = $customer->id;
                    $logInput['activity_type'] = 6;
                    $logInput['assigned_to'] = $this->logged_user->id;
                    $logInput['customer_id'] = $ids;
                    $logInput['entry_type'] = "leads";

                    $logInput['user_id'] = $this->logged_user->id;
                    $logInput['company_id'] = $this->company_id;
                    $logInput['created_by'] = $this->logged_user->id;
                    $logInput['updated_by'] = $this->logged_user->id;
                    LogActivity::addToActivityLog($logInput);

                    $logInput['activity_type'] = 8;
                    $logInput['entry_type'] = "assigned";
                    $logInput['follow_up_datetime'] = "0000-00-00 00:00:00";
                    $logInput['internal_remarks'] = "Assigned to " . $this->logged_user->name;
                    LogActivity::addToActivityLog($logInput);
                } else {
                    $errors = [
                        $value['customer_type'],
                        $value['name'],
                        $value['email'],
                        $value['country_code'],
                        $value['phone_no'],
                        $value['address'],
                        $value['pincode'],
                        $value['description'],
                        $value['errors'],
                    ];
                    array_push($failures_data_array, $errors);
                    Excel::store(new CustomerExport($failures_data_array), $faile_name);
                }
            }
            $response['faile_name'] = url('storage/' . $faile_name);
            $response['failures_data'] = $failures_data_array;
            $response['file_name'] = $faile_name;
            return response()->json(['success' => 'Successfully Saved!', 'data' => $response], 201);
        }
    }

    public function delete_file(Request $request)
    {
        $data = $request->all();
        if (Storage::exists($data['url'])) {
            Storage::delete($data['url']);
        }
    }

    /*public function testem(){
        $mail_details = [
            'subject' => 'OTP for your Quickest sign-in',
            'body' => "xzxzxzxz"
        ];
        \Mail::to("chetan.tatvamasi@gmail.com")->send(new \App\Mail\SalesReportMail($mail_details));
}*/

    /*public function testem()
    {
        $mail_details = [
            'subject' => 'OTP for your Quickest sign-in',
            'body' => "xzxzxzxz"
        ];
        \Mail::to("chetan.tatvamasi@gmail.com")->send(new \App\Mail\SalesReportMail($mail_details));
    }*/

    public function testem()
    {
        $fromDate = Carbon::now()->subWeek()->startOfWeek()->toDateString();
        $toDate = Carbon::now()->subWeek()->endOfWeek()->toDateString();

        $userLists = DB::table('users AS u')
            ->select('u.id', 'u.name', 'u.email', 'u.company_id', DB::raw('GROUP_CONCAT(up.permission_id)'))
            ->join('users_permissions AS up', 'u.id', '=', 'up.user_id')
            ->whereRaw("FIND_IN_SET(?, up.permission_id) > 0", 72) // Replace 72 with the value you want to search for
            ->where('u.invite_status', 1)
            ->where('u.weekly_cron_flg', 1)
//            ->groupBy('u.id', 'u.name')
            ->groupBy('u.id')
            ->get();

        $reportArr = [];
        foreach ($userLists as $userList) {
            $reportArr = [];
            $widgets = [];
            $widgetsAccept = [];
            $user_id = $userList->id;
            $company_id = ($userList->company_id) ? $userList->company_id : $userList->id;
//            DB::enableQueryLog();
            $widgetsArray = DB::table(function ($query) {
                $query->select(DB::raw('DISTINCT estimate_no, status'), 'estimate_date', 'company_id', 'customer_id')
                    ->from('estimates');
            }, 'subquery')
                ->leftjoin('customers_views', 'subquery.customer_id', '=', 'customers_views.id')
                ->where(function ($query) use ($user_id, $company_id) {

                    $query->where('customers_views.assigned_to_user', '=', $user_id);
                    $query->where('customers_views.company_id', '=', $company_id);
//                    $query->orWhere('customers_views.user_id', '=', $user_id);

                })
                ->where(function ($query) use ($fromDate, $toDate) {
                    $query->whereBetween(DB::raw("subquery.estimate_date"), [$fromDate, $toDate]);
                })
                ->where('subquery.company_id', '=', $company_id)
                ->where('subquery.status', '!=', '')
                ->select('subquery.status', DB::raw('COUNT(subquery.status) as widget_total'))
                ->groupBy('subquery.status')
                ->get();
//echo "<pre>";
//            print_r(DB::getQueryLog($widgetsArray));
            $widgets = json_decode(json_encode($widgetsArray), true);

            $widgetsAcceptArray = DB::table(function ($query) {

                $query->select(DB::raw('DISTINCT estimate_no, status'), 'estimate_date', 'company_id', 'customer_id', 'net_amount')
                    ->from('estimates');
            }, 'subquery')
                ->leftjoin('customers_views', 'subquery.customer_id', '=', 'customers_views.id')
                ->where(function ($query) use ($user_id) {

                    $query->where('customers_views.assigned_to_user', '=', $user_id);
                    $query->orWhere('customers_views.user_id', '=', $user_id);

                })
                ->where(function ($query) use ($fromDate, $toDate) {
                    $query->whereBetween(DB::raw("subquery.estimate_date"), [$fromDate, $toDate]);
                })
                ->where('subquery.company_id', '=', $company_id)
                ->where('subquery.status', '=', 'Accept')
                ->select('subquery.status', DB::raw('SUM(subquery.net_amount) as total_net_amount'))
                ->groupBy('subquery.status')
                ->get();
            $widgetsAccept = json_decode(json_encode($widgetsAcceptArray), true);

            $a = [
                ['status' => 'Sent', 'widget_total' => 0, 'widget_net_amount' => 0],
                ['status' => 'Inprogress', 'widget_total' => 0, 'widget_net_amount' => 0],
                ['status' => 'Accept', 'widget_total' => 0, 'widget_net_amount' => 0],
                ['status' => 'Decline', 'widget_total' => 0, 'widget_net_amount' => 0],
                ['status' => 'Draft', 'widget_total' => 0, 'widget_net_amount' => 0]
            ];
            $x = array_column($widgets, 'status');
            $total = 0 + array_sum(array_column($widgets, 'widget_total'));
            foreach ($a as $value) {
                if (!in_array($value['status'], $x)) {
                    $widgets[] = $value;
                }
            }
            usort($widgets, function ($a, $b) {
                return $a['status'] <=> $b['status'];
            });
            $widgets[] = array("status" => "Total", "widget_total" => $total);
            $sales_performance = DB::table('sales_person_performances')
                ->where(function ($query) use ($fromDate, $toDate) {
                    $query->whereBetween(DB::raw("performance_date"), [$fromDate, $toDate]);
                })
//                    ->where('company_id', $this->company_id)

                ->where(function ($query) use ($userList) {
                    $query->where('user_id', $userList->id);
                })
                ->select(DB::raw("SUM(total_task) as total_task"), DB::raw("SUM(completed_task) as completed_task"), DB::raw("COUNT(id) as total_record"), DB::raw("SUM(daily_performance) as daily_performance"))
                ->get()->toArray();

            $count_all = DB::table('sales_person_performances')
                ->where('total_task', '>', 0)
                ->where(function ($query) use ($fromDate, $toDate) {
                    $query->whereBetween(DB::raw("performance_date"), [$fromDate, $toDate]);
                })
                ->where(function ($query) use ($userList) {
                    $query->where('user_id', $userList->id);
                })
                ->select('id')->count();
            $total_task = array_column($sales_performance, 'total_task');
            $total_record = array_column($sales_performance, 'total_record');
            $completed_task = array_column($sales_performance, 'completed_task');
            $total_daily_performance = array_column($sales_performance, 'daily_performance');

            $reportArr["summaryData"][$userList->id]["name"] = $userList->name;
            $reportArr["summaryData"][$userList->id]["widget"] = $widgets;
            $reportArr["summaryData"][$userList->id]["completed_task"] = (int)$completed_task[0];
            $reportArr["summaryData"][$userList->id]["total_task"] = (int)$total_task[0];
            $reportArr["summaryData"][$userList->id]["completion_ratio"] = ($total_task[0]) ? (float)number_format(($completed_task[0] / $total_task[0]) * 100, 2) : 0;
            $reportArr["summaryData"][$userList->id]["performance"] = ($count_all > 0) ? (float)number_format($total_daily_performance[0] / $count_all, 2) : 0;
            $reportArr["summaryData"][$userList->id]['widget'][0]['widget_net_amount'] = (isset($widgetsAccept[0]['total_net_amount'])) ? number_format($widgetsAccept[0]['total_net_amount'], 2, '.', '') : 0;
            $company_data = User::select('company_name')->where("id", $company_id)->first();
            $proposal_template = ProposalTemplates::select('header_logo')->where('company_id', $company_id)->first();
            $mail_details = [
                'company_logo' => ($proposal_template)? url(Storage::url($proposal_template->header_logo)):'',
                'name' => $userList->name,
                'fromdate' => $fromDate,
                'todate' => $toDate,
                'subject' => ($company_data)? 'Weekly Sales Report for '.$company_data->company_name:'Weekly Sales Report for Quickest',
                'body' => "xzxzxzxz",
                'data' => $reportArr['summaryData'],
                "sent_flag" => 1
            ];

            \Mail::mailer('smtp2')->to($userList->email)->send(new \App\Mail\SalesReportMail($mail_details));
        }
    }

    public function testemmonth()
    {
        $fromDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $toDate = Carbon::now()->subMonth()->endOfMonth()->toDateString();

        $userLists = DB::table('users AS u')
            ->select('u.id', 'u.name', 'u.email', 'u.company_id', DB::raw('GROUP_CONCAT(up.permission_id)'))
            ->join('users_permissions AS up', 'u.id', '=', 'up.user_id')
            ->whereRaw("FIND_IN_SET(?, up.permission_id) > 0", 72) // Replace 72 with the value you want to search for
            ->where('u.invite_status', 1)
            ->where('u.monthly_cron_flg', 1)
            //            ->groupBy('u.id', 'u.name')
            ->groupBy('u.id')
            ->get();
        $reportArr = [];
        foreach ($userLists as $userList) {
            $reportArr = [];
            $widgets = [];
            $widgetsAccept = [];
            $user_id = $userList->id;
            $company_id = ($userList->company_id) ? $userList->company_id : $userList->id;
            $widgetsArray = DB::table(function ($query) {
                $query->select(DB::raw('DISTINCT estimate_no, status'), 'estimate_date', 'company_id', 'customer_id')
                    ->from('estimates');
            }, 'subquery')
                ->leftjoin('customers_views', 'subquery.customer_id', '=', 'customers_views.id')
                ->where(function ($query) use ($user_id, $company_id) {

                    $query->where('customers_views.assigned_to_user', '=', $user_id);
                    $query->where('customers_views.company_id', '=', $company_id);
//                    $query->orWhere('customers_views.user_id', '=', $user_id);

                })
                ->where(function ($query) use ($fromDate, $toDate) {
                    $query->whereBetween(DB::raw("subquery.estimate_date"), [$fromDate, $toDate]);
                })
                ->where('subquery.company_id', '=', $company_id)
                ->where('subquery.status', '!=', '')
                ->select('subquery.status', DB::raw('COUNT(subquery.status) as widget_total'))
                ->groupBy('subquery.status')
                ->get();
            $widgets = json_decode(json_encode($widgetsArray), true);

            $widgetsAcceptArray = DB::table(function ($query) {

                $query->select(DB::raw('DISTINCT estimate_no, status'), 'estimate_date', 'company_id', 'customer_id', 'net_amount')
                    ->from('estimates');
            }, 'subquery')
                ->leftjoin('customers_views', 'subquery.customer_id', '=', 'customers_views.id')
                ->where(function ($query) use ($user_id) {

                    $query->where('customers_views.assigned_to_user', '=', $user_id);
                    $query->orWhere('customers_views.user_id', '=', $user_id);

                })
                ->where(function ($query) use ($fromDate, $toDate) {
                    $query->whereBetween(DB::raw("subquery.estimate_date"), [$fromDate, $toDate]);
                })
                ->where('subquery.company_id', '=', $company_id)
                ->where('subquery.status', '=', 'Accept')
                ->select('subquery.status', DB::raw('SUM(subquery.net_amount) as total_net_amount'))
                ->groupBy('subquery.status')
                ->get();
            $widgetsAccept = json_decode(json_encode($widgetsAcceptArray), true);

            $a = [
                ['status' => 'Sent', 'widget_total' => 0, 'widget_net_amount' => 0],
                ['status' => 'Inprogress', 'widget_total' => 0, 'widget_net_amount' => 0],
                ['status' => 'Accept', 'widget_total' => 0, 'widget_net_amount' => 0],
                ['status' => 'Decline', 'widget_total' => 0, 'widget_net_amount' => 0],
                ['status' => 'Draft', 'widget_total' => 0, 'widget_net_amount' => 0]
            ];
            $x = array_column($widgets, 'status');
            $total = 0 + array_sum(array_column($widgets, 'widget_total'));
            foreach ($a as $value) {
                if (!in_array($value['status'], $x)) {
                    $widgets[] = $value;
                }
            }
            usort($widgets, function ($a, $b) {
                return $a['status'] <=> $b['status'];
            });
            $widgets[] = array("status" => "Total", "widget_total" => $total);
            $sales_performance = DB::table('sales_person_performances')
                ->where(function ($query) use ($fromDate, $toDate) {
                    $query->whereBetween(DB::raw("performance_date"), [$fromDate, $toDate]);
                })
//                    ->where('company_id', $this->company_id)

                ->where(function ($query) use ($userList) {
                    $query->where('user_id', $userList->id);
                })
                ->select(DB::raw("SUM(total_task) as total_task"), DB::raw("SUM(completed_task) as completed_task"), DB::raw("COUNT(id) as total_record"), DB::raw("SUM(daily_performance) as daily_performance"))
                ->get()->toArray();

            $count_all = DB::table('sales_person_performances')
                ->where('total_task', '>', 0)
                ->where(function ($query) use ($fromDate, $toDate) {
                    $query->whereBetween(DB::raw("performance_date"), [$fromDate, $toDate]);
                })
//                 ->where(function ($query) use ($dateArr) {
//                     $query->whereBetween(DB::raw("DATE_FORMAT(performance_date, '%Y-%m-%d')"), [$dateArr[0], $dateArr[1]]);
//                 })
                ->where(function ($query) use ($userList) {
                    $query->where('user_id', $userList->id);
                })
                ->select('id')->count();

            $total_task = array_column($sales_performance, 'total_task');
            $total_record = array_column($sales_performance, 'total_record');
            $completed_task = array_column($sales_performance, 'completed_task');
            $total_daily_performance = array_column($sales_performance, 'daily_performance');

            $reportArr["summaryData"][$userList->id]["name"] = $userList->name;
            $reportArr["summaryData"][$userList->id]["widget"] = $widgets;
            $reportArr["summaryData"][$userList->id]["completed_task"] = (int)$completed_task[0];
            $reportArr["summaryData"][$userList->id]["total_task"] = (int)$total_task[0];
            $reportArr["summaryData"][$userList->id]["completion_ratio"] = ($total_task[0]) ? (float)number_format(($completed_task[0] / $total_task[0]) * 100, 2) : 0;
            $reportArr["summaryData"][$userList->id]["performance"] = ($count_all > 0) ? (float)number_format($total_daily_performance[0] / $count_all, 2) : 0;
            $reportArr["summaryData"][$userList->id]['widget'][0]['widget_net_amount'] = (isset($widgetsAccept[0]['total_net_amount'])) ? number_format($widgetsAccept[0]['total_net_amount'], 2, '.', '') : 0;

            $company_data = User::select('company_name')->where("id", $company_id)->first();
            $proposal_template = ProposalTemplates::select('header_logo')->where('company_id', $company_id)->first();
            $mail_details = [
                'company_logo' => ($proposal_template)? url(Storage::url($proposal_template->header_logo)):'',
                'name' => $userList->name,
                'fromdate' => $fromDate,
                'todate' => $toDate,
                'subject' => ($company_data)? 'Monthly Sales Report for '.$company_data->company_name:'Monthly Sales Report for Quickest',
                'body' => "xzxzxzxz",
                'data' => $reportArr["summaryData"],
                "sent_flag" => 1
            ];
//        \Mail::to(["chetan.tatvamasi@gmail.com",$userList->email])->send(new \App\Mail\SalesReportMail($mail_details));
            \Mail::mailer('smtp2')->to($userList->email)->send(new \App\Mail\SalesReportMail($mail_details));
        }
    }

    public function testemmultiple()
    {
        $fromDate = Carbon::now()->subWeek()->startOfWeek()->toDateString();
        $toDate = Carbon::now()->subWeek()->endOfWeek()->toDateString();

        $values = [70, 71]; // Replace with your desired values


        $teamLists = DB::table('users AS u')
            ->select('u.id', 'u.name', 'u.email', 'u.company_id', DB::raw('GROUP_CONCAT(up.permission_id)'), DB::raw("IF(u.company_id, u.company_id, u.id) as last_company_id"))
            ->join('users_permissions AS up', 'u.id', '=', 'up.user_id')
            ->where('u.invite_status', 1)
            ->where('u.weekly_cron_flg', 1)
            ->where(function ($query) use ($values) {
                foreach ($values as $value) {
                    $query->orWhereRaw("FIND_IN_SET(?, up.permission_id) > 0", [$value]);
                }
            })
            /*  ->where('invite_status', 1)
              ->where('weekly_cron_flg', 1)*/
            ->groupBy('u.id')
            ->orderBy(DB::raw("IF(u.company_id, u.company_id, u.id)"), 'asc')
            ->get();

        $reportArr = [];

        $old_company_id = 'zzzzzzzzzzzzz';
        $old_name = '1zzzxxx';
        $mainArr = [];
        $subArray = [];
        foreach ($teamLists as $key => $teamList) {
            $main_user_id = $teamList->id;
            $company_id = $teamList->last_company_id;
            $name = $teamList->name;

            $reportArr["summaryData"][$company_id] = [];

            $userLists = User::query()->select("name", "id", "email", "company_id", DB::raw("IF(company_id, company_id, id) as company_id"))
                ->where('invite_status', 1)
                ->where(function ($query) use ($teamList) {
                    $query->where('company_id', $teamList->last_company_id);
                    $query->orwhere('id', $teamList->last_company_id);
                })->get();
            foreach ($userLists as $key => $userList) {

                $user_id = $userList->id;
                /*if($old_company_id!=$company_id){
    //                $mainArr[$company_id] = [];
                    if($old_company_id != 'zzzzzzzzzzzzz'){
                        $mail_details = [
                            'name' => $old_name,
                            'company_id' => $old_company_id,
                            'fromdate' => $fromDate,
                            'todate' => $toDate,
                            'subject' => 'Weekly Sales Report for Quickest',
                            'body' => "xzxzxzxz",
                            'data' => $reportArr['summaryData'][$old_company_id],
                            "sent_flag" => 2
                        ];

                        \Mail::to(["chetan.tatvamasi@gmail.com",$userList->email])->send(new \App\Mail\SalesReportMail($mail_details));
                    }

                    $reportArr["summaryData"][$company_id]=[];
                }*/

                $widgetsArray = DB::table(function ($query) {
                    $query->select(DB::raw('DISTINCT estimate_no, status'), 'estimate_date', 'company_id', 'customer_id')
                        ->from('estimates');
                }, 'subquery')
                    ->leftjoin('customers_views', 'subquery.customer_id', '=', 'customers_views.id')
                    ->where(function ($query) use ($user_id, $company_id) {

                        $query->where('customers_views.assigned_to_user', '=', $user_id);
                        $query->where('customers_views.company_id', '=', $company_id);
//                        $query->orWhere('customers_views.user_id', '=', $user_id);

                    })
                    ->where(function ($query) use ($fromDate, $toDate) {
                        $query->whereBetween(DB::raw("subquery.estimate_date"), [$fromDate, $toDate]);
                    })
                    ->where('subquery.company_id', '=', $company_id)
                    ->where('subquery.status', '!=', '')
                    ->select('subquery.status', DB::raw('COUNT(subquery.status) as widget_total'))
                    ->groupBy('subquery.status')
                    ->get();
                $widgets = json_decode(json_encode($widgetsArray), true);

                $widgetsAcceptArray = DB::table(function ($query) {

                    $query->select(DB::raw('DISTINCT estimate_no, status'), 'estimate_date', 'company_id', 'customer_id', 'net_amount')
                        ->from('estimates');
                }, 'subquery')
                    ->leftjoin('customers_views', 'subquery.customer_id', '=', 'customers_views.id')
                    ->where(function ($query) use ($user_id) {

                        $query->where('customers_views.assigned_to_user', '=', $user_id);
                        $query->orWhere('customers_views.user_id', '=', $user_id);

                    })
                    ->where(function ($query) use ($fromDate, $toDate) {
                        $query->whereBetween(DB::raw("subquery.estimate_date"), [$fromDate, $toDate]);
                    })
                    ->where('subquery.company_id', '=', $company_id)
                    ->where('subquery.status', '=', 'Accept')
                    ->select('subquery.status', DB::raw('SUM(subquery.net_amount) as total_net_amount'))
                    ->groupBy('subquery.status')
                    ->get();
                $widgetsAccept = json_decode(json_encode($widgetsAcceptArray), true);

                $a = [
                    ['status' => 'Sent', 'widget_total' => 0, 'widget_net_amount' => 0],
                    ['status' => 'Inprogress', 'widget_total' => 0, 'widget_net_amount' => 0],
                    ['status' => 'Accept', 'widget_total' => 0, 'widget_net_amount' => 0],
                    ['status' => 'Decline', 'widget_total' => 0, 'widget_net_amount' => 0],
                    ['status' => 'Draft', 'widget_total' => 0, 'widget_net_amount' => 0]
                ];
                $x = array_column($widgets, 'status');
                $total = 0 + array_sum(array_column($widgets, 'widget_total'));
                foreach ($a as $value) {
                    if (!in_array($value['status'], $x)) {
                        $widgets[] = $value;
                    }
                }
                usort($widgets, function ($a, $b) {
                    return $a['status'] <=> $b['status'];
                });
                $widgets[] = array("status" => "Total", "widget_total" => $total);
                $sales_performance = DB::table('sales_person_performances')
                    ->where(function ($query) use ($fromDate, $toDate) {
                        $query->whereBetween(DB::raw("performance_date"), [$fromDate, $toDate]);
                    })
//                    ->where('company_id', $this->company_id)

                    ->where(function ($query) use ($userList) {
                        $query->where('user_id', $userList->id);
                    })
                    ->select(DB::raw("SUM(total_task) as total_task"), DB::raw("SUM(completed_task) as completed_task"), DB::raw("COUNT(id) as total_record"), DB::raw("SUM(daily_performance) as daily_performance"))
                    ->get()->toArray();

                $count_all = DB::table('sales_person_performances')
                    ->where('total_task', '>', 0)
                    ->where(function ($query) use ($fromDate, $toDate) {
                        $query->whereBetween(DB::raw("performance_date"), [$fromDate, $toDate]);
                    })
                    ->where(function ($query) use ($userList) {
                        $query->where('user_id', $userList->id);
                    })
                    ->select('id')->count();

                $total_task = array_column($sales_performance, 'total_task');
                $total_record = array_column($sales_performance, 'total_record');
                $completed_task = array_column($sales_performance, 'completed_task');
                $total_daily_performance = array_column($sales_performance, 'daily_performance');

                $reportArr["summaryData"][$company_id][$userList->id]["name"] = $userList->name;
                $reportArr["summaryData"][$company_id][$userList->id]["widget"] = $widgets;
                $reportArr["summaryData"][$company_id][$userList->id]["completed_task"] = (int)$completed_task[0];
                $reportArr["summaryData"][$company_id][$userList->id]["total_task"] = (int)$total_task[0];
                $reportArr["summaryData"][$company_id][$userList->id]["completion_ratio"] = ($total_task[0]) ? (float)number_format(($completed_task[0] / $total_task[0]) * 100, 2) : 0;
                $reportArr["summaryData"][$company_id][$userList->id]["performance"] = ($count_all > 0) ? (float)number_format($total_daily_performance[0] / $count_all, 2) : 0;
                $reportArr["summaryData"][$company_id][$userList->id]['widget'][0]['widget_net_amount'] = (isset($widgetsAccept[0]['total_net_amount'])) ? number_format($widgetsAccept[0]['total_net_amount'], 2, '.', '') : 0;
                $old_company_id = $company_id;
                $old_name = $name;
            }
            $company_data = User::select('company_name')->where("id", $company_id)->first();
            $proposal_template = ProposalTemplates::select('header_logo')->where('company_id', $company_id)->first();
            $mail_details = [
                'company_logo' => ($proposal_template)? url(Storage::url($proposal_template->header_logo)):'',
                'name' => $teamList->name,
                'company_id' => $company_id,
                'fromdate' => $fromDate,
                'todate' => $toDate,
                'subject' => ($company_data)? 'Weekly Sales Report for '.$company_data->company_name:'Weekly Sales Report for Quickest',
                'body' => "xzxzxzxz",
                'data' => $reportArr['summaryData'][$company_id],
                "sent_flag" => 2
            ];

//            \Mail::to(["chetan.tatvamasi@gmail.com", $teamList->email])->send(new \App\Mail\SalesReportMail($mail_details));
            \Mail::mailer('smtp2')->to($teamList->email)->send(new \App\Mail\SalesReportMail($mail_details));
        }
    }

    public function testemmonthmultiple()
    {

        $fromDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $toDate = Carbon::now()->subMonth()->endOfMonth()->toDateString();
        $values = [70, 71]; // Replace with your desired values


        $teamLists = DB::table('users AS u')
            ->select('u.id', 'u.name', 'u.email', 'u.company_id', DB::raw('GROUP_CONCAT(up.permission_id)'), DB::raw("IF(u.company_id, u.company_id, u.id) as last_company_id"))
            ->join('users_permissions AS up', 'u.id', '=', 'up.user_id')
            ->where('u.invite_status', 1)
            ->where('u.monthly_cron_flg', 1)
            ->where(function ($query) use ($values) {
                foreach ($values as $value) {
                    $query->orWhereRaw("FIND_IN_SET(?, up.permission_id) > 0", [$value]);
                }
            })
            ->groupBy('u.id')
            ->orderBy(DB::raw("IF(u.company_id, u.company_id, u.id)"), 'asc')
            ->get();

        $reportArr = [];

        $old_company_id = 'zzzzzzzzzzzzz';
        $old_name = '1zzzxxx';
        $mainArr = [];
        $subArray = [];
        foreach ($teamLists as $key => $teamList) {
            $main_user_id = $teamList->id;
            $company_id = $teamList->last_company_id;
            $name = $teamList->name;

            $reportArr["summaryData"][$company_id] = [];

            $userLists = User::query()->select("name", "id", "email", "company_id", DB::raw("IF(company_id, company_id, id) as company_id"))
                ->where('invite_status', 1)
                ->where(function ($query) use ($teamList) {
                    $query->where('company_id', $teamList->last_company_id);
                    $query->orwhere('id', $teamList->last_company_id);
                })->get();
            foreach ($userLists as $key => $userList) {

                $user_id = $userList->id;
                /*if($old_company_id!=$company_id){
    //                $mainArr[$company_id] = [];
                    if($old_company_id != 'zzzzzzzzzzzzz'){
                        $mail_details = [
                            'name' => $old_name,
                            'company_id' => $old_company_id,
                            'fromdate' => $fromDate,
                            'todate' => $toDate,
                            'subject' => 'Weekly Sales Report for Quickest',
                            'body' => "xzxzxzxz",
                            'data' => $reportArr['summaryData'][$old_company_id],
                            "sent_flag" => 2
                        ];

                        \Mail::to(["chetan.tatvamasi@gmail.com",$userList->email])->send(new \App\Mail\SalesReportMail($mail_details));
                    }

                    $reportArr["summaryData"][$company_id]=[];
                }*/

                $widgetsArray = DB::table(function ($query) {
                    $query->select(DB::raw('DISTINCT estimate_no, status'), 'estimate_date', 'company_id', 'customer_id')
                        ->from('estimates');
                }, 'subquery')
                    ->leftjoin('customers_views', 'subquery.customer_id', '=', 'customers_views.id')
                    ->where(function ($query) use ($user_id, $company_id) {

                        $query->where('customers_views.assigned_to_user', '=', $user_id);
                        $query->where('customers_views.company_id', '=', $company_id);
//                        $query->orWhere('customers_views.user_id', '=', $user_id);

                    })
                    ->where(function ($query) use ($fromDate, $toDate) {
                        $query->whereBetween(DB::raw("subquery.estimate_date"), [$fromDate, $toDate]);
                    })
                    ->where('subquery.company_id', '=', $company_id)
                    ->where('subquery.status', '!=', '')
                    ->select('subquery.status', DB::raw('COUNT(subquery.status) as widget_total'))
                    ->groupBy('subquery.status')
                    ->get();
                $widgets = json_decode(json_encode($widgetsArray), true);

                $widgetsAcceptArray = DB::table(function ($query) {

                    $query->select(DB::raw('DISTINCT estimate_no, status'), 'estimate_date', 'company_id', 'customer_id', 'net_amount')
                        ->from('estimates');
                }, 'subquery')
                    ->leftjoin('customers_views', 'subquery.customer_id', '=', 'customers_views.id')
                    ->where(function ($query) use ($user_id) {

                        $query->where('customers_views.assigned_to_user', '=', $user_id);
                        $query->orWhere('customers_views.user_id', '=', $user_id);

                    })
                    ->where(function ($query) use ($fromDate, $toDate) {
                        $query->whereBetween(DB::raw("subquery.estimate_date"), [$fromDate, $toDate]);
                    })
                    ->where('subquery.company_id', '=', $company_id)
                    ->where('subquery.status', '=', 'Accept')
                    ->select('subquery.status', DB::raw('SUM(subquery.net_amount) as total_net_amount'))
                    ->groupBy('subquery.status')
                    ->get();
                $widgetsAccept = json_decode(json_encode($widgetsAcceptArray), true);

                $a = [
                    ['status' => 'Sent', 'widget_total' => 0, 'widget_net_amount' => 0],
                    ['status' => 'Inprogress', 'widget_total' => 0, 'widget_net_amount' => 0],
                    ['status' => 'Accept', 'widget_total' => 0, 'widget_net_amount' => 0],
                    ['status' => 'Decline', 'widget_total' => 0, 'widget_net_amount' => 0],
                    ['status' => 'Draft', 'widget_total' => 0, 'widget_net_amount' => 0]
                ];
                $x = array_column($widgets, 'status');
                $total = 0 + array_sum(array_column($widgets, 'widget_total'));
                foreach ($a as $value) {
                    if (!in_array($value['status'], $x)) {
                        $widgets[] = $value;
                    }
                }
                usort($widgets, function ($a, $b) {
                    return $a['status'] <=> $b['status'];
                });
                $widgets[] = array("status" => "Total", "widget_total" => $total);
                $sales_performance = DB::table('sales_person_performances')
                    ->where(function ($query) use ($fromDate, $toDate) {
                        $query->whereBetween(DB::raw("performance_date"), [$fromDate, $toDate]);
                    })
//                    ->where('company_id', $this->company_id)

                    ->where(function ($query) use ($userList) {
                        $query->where('user_id', $userList->id);
                    })
                    ->select(DB::raw("SUM(total_task) as total_task"), DB::raw("SUM(completed_task) as completed_task"), DB::raw("COUNT(id) as total_record"), DB::raw("SUM(daily_performance) as daily_performance"))
                    ->get()->toArray();

                $count_all = DB::table('sales_person_performances')
                    ->where('total_task', '>', 0)
                    ->where(function ($query) use ($fromDate, $toDate) {
                        $query->whereBetween(DB::raw("performance_date"), [$fromDate, $toDate]);
                    })
                    ->where(function ($query) use ($userList) {
                        $query->where('user_id', $userList->id);
                    })
                    ->select('id')->count();

                $total_task = array_column($sales_performance, 'total_task');
                $total_record = array_column($sales_performance, 'total_record');
                $completed_task = array_column($sales_performance, 'completed_task');
                $total_daily_performance = array_column($sales_performance, 'daily_performance');

                $reportArr["summaryData"][$company_id][$userList->id]["name"] = $userList->name;
                $reportArr["summaryData"][$company_id][$userList->id]["widget"] = $widgets;
                $reportArr["summaryData"][$company_id][$userList->id]["completed_task"] = (int)$completed_task[0];
                $reportArr["summaryData"][$company_id][$userList->id]["total_task"] = (int)$total_task[0];
                $reportArr["summaryData"][$company_id][$userList->id]["completion_ratio"] = ($total_task[0]) ? (float)number_format(($completed_task[0] / $total_task[0]) * 100, 2) : 0;
                $reportArr["summaryData"][$company_id][$userList->id]["performance"] = ($count_all > 0) ? (float)number_format($total_daily_performance[0] / $count_all, 2) : 0;
                $reportArr["summaryData"][$company_id][$userList->id]['widget'][0]['widget_net_amount'] = (isset($widgetsAccept[0]['total_net_amount'])) ? number_format($widgetsAccept[0]['total_net_amount'], 2, '.', '') : 0;
                $old_company_id = $company_id;
                $old_name = $name;
            }

            $company_data = User::select('company_name')->where("id", $company_id)->first();
            $proposal_template = ProposalTemplates::select('header_logo')->where('company_id', $company_id)->first();
            $mail_details = [
                'company_logo' => ($proposal_template)? url(Storage::url($proposal_template->header_logo)):'',
                'name' => $teamList->name,
                'company_id' => $company_id,
                'fromdate' => $fromDate,
                'todate' => $toDate,
                'subject' => ($company_data)? 'Monthly Sales Report for '.$company_data->company_name:'Monthly Sales Report for Quickest',
                'body' => "xzxzxzxz",
                'data' => $reportArr['summaryData'][$company_id],
                "sent_flag" => 2
            ];

//            \Mail::to(["chetan.tatvamasi@gmail.com", $teamList->email])->send(new \App\Mail\SalesReportMail($mail_details));
            \Mail::mailer('smtp2')->to($teamList->email)->send(new \App\Mail\SalesReportMail($mail_details));
        }
    }

//    public function testemmonthmultiple()
//    {
//        $fromDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
//        $toDate = Carbon::now()->subMonth()->endOfMonth()->toDateString();
//
//        $values = [70, 71]; // Replace with your desired values
//
//
//        $userLists = DB::table('users AS u')
//            ->select('u.id', 'u.name', 'u.email', 'u.company_id', DB::raw('GROUP_CONCAT(up.permission_id)'),DB::raw("IF(u.company_id, u.company_id, u.id) as last_company_id"))
//            ->join('users_permissions AS up', 'u.id', '=', 'up.user_id')
//            ->where(function ($query) use ($values) {
//                foreach ($values as $value) {
//                    $query->orWhereRaw("FIND_IN_SET(?, up.permission_id) > 0", [$value]);
//                }
//            })
//            ->groupBy('u.id')
//            ->orderBy(DB::raw("IF(u.company_id, u.company_id, u.id)") ,'asc')
//            ->get();
//
//        $reportArr = [];
//
//        $old_name = '1xxxzzz';
//        $old_company_id = 'zzzzzzzzzzzzz';
//        $mainArr = [];
//        $subArray = [];
//        foreach ($userLists as $key=>$userList) {
//            $user_id = $userList->id;
//            $company_id = $userList->last_company_id;
//            $name = $userList->name;
//
//            if($old_company_id!=$company_id){
//                if($old_company_id != 'zzzzzzzzzzzzz'){
//                    $mail_details = [
//                        'name' => $old_name,
//                        'company_id' => $old_company_id,
//                        'fromdate' => $fromDate,
//                        'todate' => $toDate,
//                        'subject' => 'Monthly Sales Report for Quickest',
//                        'body' => "xzxzxzxz",
//                        'data' => $reportArr['summaryData'][$old_company_id],
//                        "sent_flag" => 2
//                    ];
//
//                    \Mail::to(["chetan.tatvamasi@gmail.com",$userList->email])->send(new \App\Mail\SalesReportMail($mail_details));
//                }
//
//                $reportArr["summaryData"][$company_id]=[];
//            }
//
//            $widgetsArray = DB::table(function ($query) {
//                $query->select(DB::raw('DISTINCT estimate_no, status'), 'estimate_date', 'company_id', 'customer_id')
//                    ->from('estimates');
//            }, 'subquery')
//                ->leftjoin('customers_views', 'subquery.customer_id', '=', 'customers_views.id')
//                ->where(function ($query) use($user_id) {
//
//                    $query->where('customers_views.assigned_to_user', '=', $user_id);
//                    $query->orWhere('customers_views.user_id', '=', $user_id);
//
//                })
//                ->where(function ($query) use ($fromDate, $toDate) {
//                    $query->whereBetween(DB::raw("subquery.estimate_date"), [$fromDate, $toDate]);
//                })
//                ->where('subquery.company_id', '=', $company_id)
//                ->where('subquery.status', '!=', '')
//                ->select('subquery.status', DB::raw('COUNT(subquery.status) as widget_total'))
//                ->groupBy('subquery.status')
//                ->get();
//            $widgets = json_decode(json_encode($widgetsArray), true);
//
//            $widgetsAcceptArray = DB::table(function ($query) {
//
//                $query->select(DB::raw('DISTINCT estimate_no, status'), 'estimate_date', 'company_id', 'customer_id','net_amount')
//                    ->from('estimates');
//            }, 'subquery')
//                ->leftjoin('customers_views', 'subquery.customer_id', '=', 'customers_views.id')
//                ->where(function ($query) use($user_id) {
//
//                    $query->where('customers_views.assigned_to_user', '=', $user_id);
//                    $query->orWhere('customers_views.user_id', '=', $user_id);
//
//                })
//                ->where(function ($query) use ($fromDate, $toDate) {
//                    $query->whereBetween(DB::raw("subquery.estimate_date"), [$fromDate, $toDate]);
//                })
//                ->where('subquery.company_id', '=', $company_id)
//                ->where('subquery.status', '=', 'Accept')
//                ->select('subquery.status', DB::raw('SUM(subquery.net_amount) as total_net_amount'))
//                ->groupBy('subquery.status')
//                ->get();
//            $widgetsAccept = json_decode(json_encode($widgetsAcceptArray), true);
//
//            $a = [
//                ['status' => 'Sent', 'widget_total' => 0, 'widget_net_amount' => 0],
//                ['status' => 'Inprogress', 'widget_total' => 0, 'widget_net_amount' => 0],
//                ['status' => 'Accept', 'widget_total' => 0, 'widget_net_amount' => 0],
//                ['status' => 'Decline', 'widget_total' => 0, 'widget_net_amount' => 0],
//                ['status' => 'Draft', 'widget_total' => 0, 'widget_net_amount' => 0]
//            ];
//            $x = array_column($widgets, 'status');
//            $total = 0 + array_sum(array_column($widgets, 'widget_total'));
//            foreach ($a as $value) {
//                if (!in_array($value['status'], $x)) {
//                    $widgets[] = $value;
//                }
//            }
//            usort($widgets, function ($a, $b) {
//                return $a['status'] <=> $b['status'];
//            });
//            $widgets[] = array("status" => "Total", "widget_total" => $total);
//            $sales_performance = DB::table('sales_person_performances')
//                ->where(function ($query) use ($fromDate, $toDate) {
//                    $query->whereBetween(DB::raw("performance_date"), [$fromDate, $toDate]);
//                })
////                    ->where('company_id', $this->company_id)
//
//                ->where(function ($query) use ($userList) {
//                    $query->where('user_id', $userList->id);
//                })
//                ->select(DB::raw("SUM(total_task) as total_task"), DB::raw("SUM(completed_task) as completed_task"), DB::raw("COUNT(id) as total_record"), DB::raw("SUM(daily_performance) as daily_performance"))
//                ->get()->toArray();
//
//            $count_all = DB::table('sales_person_performances')
//                ->where('total_task', '>', 0)
//                ->where(function ($query) use ($fromDate, $toDate) {
//                    $query->whereBetween(DB::raw("performance_date"), [$fromDate, $toDate]);
//                })
//                ->where(function ($query) use ($userList) {
//                    $query->where('user_id', $userList->id);
//                })
//                ->select('id')->count();
//
//            $total_task = array_column($sales_performance, 'total_task');
//            $total_record = array_column($sales_performance, 'total_record');
//            $completed_task = array_column($sales_performance, 'completed_task');
//            $total_daily_performance = array_column($sales_performance, 'daily_performance');
//
//            $reportArr["summaryData"][$company_id][$userList->id]["name"] = $userList->name;
//            $reportArr["summaryData"][$company_id][$userList->id]["widget"] = $widgets;
//            $reportArr["summaryData"][$company_id][$userList->id]["completed_task"] = (int)$completed_task[0];
//            $reportArr["summaryData"][$company_id][$userList->id]["total_task"] = (int)$total_task[0];
//            $reportArr["summaryData"][$company_id][$userList->id]["completion_ratio"] = ($total_task[0]) ? (float)number_format(($completed_task[0] / $total_task[0]) * 100, 2) : 0;
//            $reportArr["summaryData"][$company_id][$userList->id]["performance"] = ($count_all > 0) ? (float)number_format($total_daily_performance[0] / $count_all, 2) : 0;
//            $reportArr["summaryData"][$company_id][$userList->id]['widget'][0]['widget_net_amount'] = (isset($widgetsAccept[0]['total_net_amount'])) ? number_format($widgetsAccept[0]['total_net_amount'], 2, '.', '') : 0;
//            $old_company_id = $company_id;
//            $old_name = $name;
//        }
//
//        $mail_details = [
//            'name' => $userList->name,
//            'company_id' => $old_company_id,
//            'fromdate' => $fromDate,
//            'todate' => $toDate,
//            'subject' => 'Weekly Sales Report for Quickest',
//            'body' => "xzxzxzxz",
//            'data' => $reportArr['summaryData'][$old_company_id],
//            "sent_flag" => 2
//        ];
//
//        \Mail::to(["chetan.tatvamasi@gmail.com",$userList->email])->send(new \App\Mail\SalesReportMail($mail_details));
//    }

    public function percheck($user_id, $company_id)
    {
        $user_permissions = UserPermission::query()->join('permissions', 'users_permissions.permission_id', '=', 'permissions.id')->where('users_permissions.user_id', $user_id)->where('users_permissions.company_id', $company_id)->pluck('permissions.slug')->toArray();
        return $user_permissions;
    }

    public function viewOnMobile(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $validator = Validator::make($input, [
                'lead_id' => 'required',
                'lead_assign_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);
            }

            $id = $input['lead_id'];
            $user = User::where('id', '=', $input['lead_assign_id'])->select(["name", "device_key", "mobile_device_key"])->first();

            $noficationArr['customer_id'] = $input['lead_id'];
            $noficationArr['notification_type'] = "view_lead";
            $noficationArr['device_key'] = '';
//            $noficationArr['device_key'] = $user->device_key;
            $noficationArr['mobile_device_key'] = $user->mobile_device_key;
            $noficationArr['title'] = 'View CLient: '.$input['lead_name'];
            $noficationArr['body'] = 'Continued from your web browser. Tap to view ' . $input['lead_name'];
            $this->sendAssigntoUserNotification($noficationArr);
            return response()->json(['success' => 'Successfully Updated!'], 201);
        }
    }

    public function MultipleLeadAssignedToUser(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();

            $validator = Validator::make($input, [
                'id' => 'required',
                'assigned_to_user' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);
            }

            $id = [];
            $i = 0;
            foreach (explode(",", $input['id']) as $value) {
                $id = Crypt::decrypt($value);

                $user = User::where('id', '=', $input['assigned_to_user'])->select(["name", "device_key", "mobile_device_key"])->first();
                Customer::find($id)->update(["assigned_to_user" => $input['assigned_to_user'], "new_lead_flag" => 1]);
                $customerData = Customer::where('id', '=', $id)->select(["name"])->first();
                $customerTimelineData = EstimateTimeline::where('customer_id', '=', $id)->select(["follow_up_datetime"])->orderBy('id','desc')->first();

                $logInput['follow_up_datetime'] = ($customerTimelineData)?$customerTimelineData['follow_up_datetime']:'0000-00-00 00:00:00';
                $logInput['assigned_to'] = $input['assigned_to_user'];
                $logInput['customer_id'] = $id;
                $logInput['entry_type'] = "assigned";
                $logInput['activity_type'] = 8;
                $logInput['internal_remarks'] = "Assigned to " . $user->name;
                $logInput['user_id'] = $this->logged_user->id;
                $logInput['company_id'] = $this->company_id;
                $logInput['created_by'] = $this->logged_user->id;
                $logInput['updated_by'] = $this->logged_user->id;
                LogActivity::addToActivityLog($logInput);
                SalesPersonPerformances::where([['customer_id', "=", $id], ['completed_task', '=', 0]])->update(array('user_id' => $input['assigned_to_user'], 'created_at' => date('Y-m-d H:i:s')));


                $noficationArr['customer_id'] = $id ;
                $noficationArr['notification_type'] = "assign_to_you";
                $noficationArr['device_key'] = $user->device_key;
                $noficationArr['mobile_device_key'] = $user->mobile_device_key;
//                $noficationArr['mobile_device_key'] = null;
                $noficationArr['title'] = 'New Lead Assigned To You';
                $noficationArr['body'] = $customerData->name . ' is assigned to you by ' . $this->logged_user->name;
                $this->sendAssigntoUserNotification($noficationArr);
            }

            return response()->json(['success' => 'Successfully Updated!'], 201);
        }
    }

    public function MultipleLeadStage(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();

            $validator = Validator::make($input, [
                'lead_id' => 'required',
                'leads_stages_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);
            }

            $id = [];
            $i = 0;
            foreach (explode(",", $input['lead_id']) as $value) {
                $id = Crypt::decrypt($value);


                $lost_reason_id = ($input['lead_stage_data_id']==6)?$input['lost_reason_id']:0;
                $lost_reason_others = ($input['lead_stage_data_id']==6)?$input['lost_reason_others']:'';
                $customers = Customer::where('id', $id)->update(["lead_stage_id" => $input['leads_stages_id'],"lost_reason_id" =>$lost_reason_id,"others_reason" =>$lost_reason_others]);

                if($input['lead_stage_data_id']==6){

                    $customer_data = EstimateTimeline::select("id")
                        ->where('customer_id', '=',  $id)
                        ->wherein('activity_type', [9])
                        ->orderBy('id', 'DESC')
                        ->take(1)
                        ->get()
                        ->toArray();

                    if ($customer_data && $customer_data[0]['id']) {
                        $a = SalesPersonPerformances::where('timeline_id', $customer_data[0]['id'])->update(array('completed_task' => 1));
                    }

                    $logInput['follow_up_datetime'] = '0000-00-00 00:00:00';
                    $logInput['assigned_to'] = $this->logged_user->id;
                    $logInput['customer_id'] = $id;
                    $logInput['entry_type'] = "Lead Lost";
                    $logInput['activity_name'] = "Lead Lost";
                    $logInput['activity_type'] = "Lead Lost";
                    $logInput['activity_type'] = 17;
                    $logInput['internal_remarks'] = ($lost_reason_others)?$lost_reason_others:$input['lost_reason_name'];
                    $logInput['user_id'] = $this->logged_user->id;
                    $logInput['company_id'] = $this->company_id;
                    $logInput['created_by'] = $this->logged_user->id;
                    $logInput['updated_by'] = $this->logged_user->id;
                    LogActivity::addToActivityLog($logInput);

                }

                if($input['lead_stage_data_id']==2){

                    /*$customer_data = EstimateTimeline::select("id")
                        ->where('customer_id', '=',  $input['id'])
                        ->wherein('activity_type', [1, 2, 3, 9,19])
                        ->orderBy('id', 'DESC')
                        ->take(1)
                        ->get()
                        ->toArray();*/

                   /* $customer_data = EstimateTimeline::select("id")
                        ->where('customer_id', '=',  $id)
                        ->wherein('activity_type', [9])
                        ->orderBy('id', 'DESC')
                        ->take(1)
                        ->get()
                        ->toArray();*/

                    $customer_data_tmp = EstimateTimeline::where('customer_id', $id)->select(['follow_up_datetime'])
                        ->latest('id')
                        ->first();

                    /*if ($customer_data && $customer_data[0]['id']) {
                        $a = SalesPersonPerformances::where('timeline_id', $customer_data[0]['id'])->update(array('completed_task' => 1));
                    }*/

                    $logInput['follow_up_datetime'] = ($customer_data_tmp)?$customer_data_tmp->follow_up_datetime:'0000-00-00 00:00:00';
                    $logInput['assigned_to'] = $this->logged_user->id;
                    $logInput['customer_id'] = $id;
                    $logInput['entry_type'] = "Lead Won";
                    $logInput['activity_name'] = "Lead Won";
                    $logInput['activity_type'] = 18;
                    $logInput['internal_remarks'] = '';
                    $logInput['user_id'] = $this->logged_user->id;
                    $logInput['company_id'] = $this->company_id;
                    $logInput['created_by'] = $this->logged_user->id;
                    $logInput['updated_by'] = $this->logged_user->id;
                    LogActivity::addToActivityLog($logInput);

                }



                //Customer::find($id)->update(["lead_stage_id" => $input['leads_stages_id']]);

            }

            return response()->json(['success' => 'Successfully Updated!'], 201);
        }
    }


    public function setDefaultPer(){
        $usersWithPermission70 = DB::table('users_permissions')
            ->where('permission_id', 70)
            ->get();

        foreach ($usersWithPermission70 as $userPermission) {
            // Access the columns of each record using object properties
            $userId = $userPermission->user_id;
            $permissionId = $userPermission->permission_id;
            UserPermission::firstOrCreate(
                [
                    'user_id' => $userPermission->user_id,
                    'permission_id' => 78,
                    'company_id' => $userPermission->company_id,
                ]
            );

            // Perform actions with the data
            echo "User with ID $userId has permission with ID $permissionId.<br>";
        }
    }

    public function download(Request $request)
    {
        $filePath = 'path/to/remote/file.txt'; // Replace with the path to your S3 file

        // Initialize the S3 adapter
        $adapter = new AwsS3Adapter($yourS3Client, 'your-s3-bucket-name');

        // Initialize the Filesystem
        $filesystem = new Filesystem($adapter);

        if ($filesystem->has($filePath)) {
            // Get the file's contents
            $fileContents = $filesystem->read($filePath);

            // Prepare the response for download
            $response = response()->stream(
                function () use ($fileContents) {
                    echo $fileContents;
                },
                200,
                [
                    'Content-Type' => 'application/octet-stream',
                    'Content-Disposition' => 'attachment; filename="' . basename($filePath) . '"',
                ]
            );

            return $response;
        } else {
            return response()->json(['error' => 'File not found'], 404);
        }
    }

    public function LeadStageDefualt()
    {

        $records = DB::table('users')
            ->where('status', 'Approved')
            ->whereNull('company_id')
            ->select(['id', 'company_category'])
            ->get();

        foreach ($records as $record) {

            $lostReasonArr = [
                ['name' => 'Costly', 'user_id' => $record->id, 'company_id' => $record->id, "priority" => 0],
                ['name' => 'Duplicate Lead', 'user_id' => $record->id, 'company_id' => $record->id, "priority" => 0],
                ['name' => 'Finalize other solution', 'user_id' => $record->id, 'company_id' => $record->id, "priority" => 0],
                ['name' => 'No budget', 'user_id' => $record->id, 'company_id' => $record->id, "priority" => 0],
                ['name' => 'No Need', 'user_id' => $record->id, 'company_id' => $record->id, "priority" => 0],
                ['name' => 'Only Info. required', 'user_id' => $record->id, 'company_id' => $record->id, "priority" => 0],
                ['name' => 'Require specific brand only', 'user_id' => $record->id, 'company_id' => $record->id, "priority" => 0],
                ['name' => 'Others', 'user_id' => $record->id, 'company_id' => $record->id, "priority" => 1]
            ];
            DB::table('lost_reasons')->insert($lostReasonArr);

            $leadStageArr = [
                ['name' => 'New Lead', 'color_code' => '#006398','is_default'=>1,'priority'=>1,'is_delete'=>1, 'user_id' => $record->id, 'company_id' => $record->id],
                ['name' => 'Inprocess', 'color_code' => '#fdac64','is_default'=>0,'priority'=>2,'is_delete'=>0, 'user_id' => $record->id, 'company_id' => $record->id],
                ['name' => 'Qualified', 'color_code' => '#c47933','is_default'=>0,'priority'=>3,'is_delete'=>0, 'user_id' => $record->id, 'company_id' => $record->id],
                ['name' => 'Quote Sent', 'color_code' => '#f678c3','is_default'=>4,'priority'=>4,'is_delete'=>1, 'user_id' => $record->id, 'company_id' => $record->id],
                ['name' => 'Lead Won', 'color_code' => '#13a764','is_default'=>0,'priority'=>5,'is_delete'=>1, 'user_id' => $record->id, 'company_id' => $record->id],
                ['name' => 'Lead Lost', 'color_code' => '#fa4e64','is_default'=>6,'priority'=>6,'is_delete'=>1, 'user_id' => $record->id, 'company_id' => $record->id],
                ['name' => 'On Hold', 'color_code' => '#ab408b','is_default'=>0,'priority'=>7,'is_delete'=>0, 'user_id' => $record->id, 'company_id' => $record->id]];
            DB::table('lead_stages')->insert($leadStageArr);
        }
    }

    public function LeadStageDefualtOne()
    {
        $records = DB::table('users')
            ->where('status', 'Approved')
//            ->whereNull('company_id')
            ->select(['id', 'company_category'])
            ->get();

        foreach ($records as $record) {
            $dashboardSettingArr = [
                ['permission_id' => 2, 'user_id' => $record->id, 'company_id' => $record->id],
                ['permission_id' => 3, 'user_id' => $record->id, 'company_id' => $record->id],
                ['permission_id' => 4, 'user_id' => $record->id, 'company_id' => $record->id],
                ['permission_id' => 5, 'user_id' => $record->id, 'company_id' => $record->id],
                ['permission_id' => 6, 'user_id' => $record->id, 'company_id' => $record->id]
            ];
            DB::table('dashboard_settings')->insert($dashboardSettingArr);
        }
    }

    public function getActivityColunts(Request $request)
    {
        $input = $request->all();
        $id = Crypt::decrypt($input['id']);
//        $validator = Validator::make($id, [
//            'id' => 'required'
//        ]);
//        if ($validator->fails()) {
//            return response()->json(['errors' => $validator->errors()->all()], 400);
//        }

        $activity_counts = DB::table(function ($query) use ($id) {
            $query->from('customer_timelines')
                ->select('activity_type')
                ->where('customer_id', '=', $id)
                ->whereIn('activity_type', [1, 2, 3,19]);
        }, 'a')
            ->rightJoin(DB::raw('(SELECT 1 AS activity_type UNION SELECT 2 UNION SELECT 3 UNION SELECT 19) b'), 'b.activity_type', '=', 'a.activity_type')
            ->select('b.activity_type')
            ->selectRaw('
        CASE
            WHEN b.activity_type = 1 THEN "Call"
            WHEN b.activity_type = 2 THEN "Message"
            WHEN b.activity_type = 3 THEN "Meeting"
            WHEN b.activity_type = 19 THEN "Site Visit"
            ELSE "Other"
        END AS activity_name
    ')
            ->selectRaw('COALESCE(COUNT(a.activity_type), 0) AS activity_type_count')
            ->groupBy('b.activity_type', 'activity_name')
            ->get();
        return response()->json(['success' => 'Successfully Saved!', 'data' => $activity_counts], 201);
    }

    public function multipleLabelToCustomers(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'lead_id' => 'required',
            'selected_lead_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()], 400);
        }
        $leadIdArr = $input['selected_lead_id'];
        $customerIdArr = explode(',', $input['lead_id']);

        foreach ($customerIdArr as $customerId) {
            $id = Crypt::decrypt($customerId);
            $cl = CustomerLabel::where('customer_id', $id)->delete();
            foreach ($leadIdArr as $labelId) {
//                $customerId = Crypt::decrypt($customerId);


                $existingRecord = CustomerLabel::where('customer_id', $id)
                    ->where('label_id', $labelId)
                    ->first();
                if (!$existingRecord) {
                    CustomerLabel::firstOrCreate(
                        ['customer_id' => $id, 'label_id' => $labelId, 'user_id' => $this->logged_user->id, 'company_id' => $this->company_id],
                        ['customer_id' => $id, 'label_id' => $labelId]
                    );
                }
            }
//            Customer::find($customerId)->update(["new_lead_flag" => 0]);
        }
        return response()->json(['success' => 'Successfully Saved!'], 201);
    }

    public function csvUpload(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        // Check if file has been uploaded
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $file = $request->file('file');

            // Open and read the CSV file
            if (($handle = fopen($file->getPathname(), "r")) !== FALSE) {
                // Read the CSV headers
                $headers = fgetcsv($handle, 1000, ",");
                $headers = array_map('trim', $headers);
//dd($headers);
                // Read each data row
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    // Prepare data for insertion
                    $values = "'" . implode("','", array_map('trim', $data)) . "'";



                    $customer_type = $data[0];
                    $company_name = $data[1];
                    $name = $data[2];
                    $email = $data[3];
                    $country_code = $data[4];
                    $phone_no = $data[5];
                    $address = trim($data[6],",");
                    $city_name = $data[7];
                    $state_id = $data[8];
                    $description = $data[9];
                    $customer_lead_id = $data[10];
//                    $assigned_to_user = $data[12];



                    if($data[0] == 'Buisiness') {
                        $customer_type = 'Business';
                    }
                    $state_id = 0;
                    if(trim($data[8])){
                        $lead_origin = DB::table('states')
                            ->select('id')
                            ->where('name', '=', trim($data[8]))->first();

                        if($lead_origin){
                            $state_id = $lead_origin->id;
                        }
                    }
                    $country_id = 101;


                   /* $country_id = 0;
                    if(trim($data[9])){
                        $lead_origin = DB::table('countries')
                            ->select('id')
                            ->where('name', '=', trim($data[9]))->first();

                        if($lead_origin){
                            ECHO $country_id = $lead_origin->id;
                        }else{
                            $country_id = 101;
                        }
                    }*/

                    $lead_origin_id = 0;
                    if(trim($data[10]) && trim($data[10]) != ''){
                        $lead_origin = DB::table('customer_leads')
                            ->select('id')
                            ->where('company_id', '=',  $this->company_id)
                            ->where('name', '=', trim($data[10]))->first();

                        if($lead_origin){
                            $lead_origin_id = $lead_origin->id;
                        }
                        /*else{
                            $insertedId = DB::table('customer_leads')
                                ->insertGetId(
                                    ['name' => $data[12],'user_id' => $this->company_id,'company_id' => $this->company_id]
                                );

                            $lead_origin_id = $insertedId;
                        }*/
                    }

                    $assigned_to_user=0;
                    if(trim($data[12])){
                        if(trim($data[12])=='Anil Dhameliya'){
                            $data[12]='Kapil Jasoliya';
                        }

                        if(trim($data[12])=='Aarti Patel'){
                            $data[12]='Arti Mahawar';
                        }

                        $lead_origin = DB::table('users')
                            ->select('id')
                            ->where('name', '=', trim($data[12]))->first();

                        if($lead_origin){
                            $assigned_to_user = $lead_origin->id;
                        }else{
                            $assigned_to_user = 1513;
                        }
                    }

                    // Build and execute the SQL query to insert data into MySQL
                   /* echo $sql = "INSERT INTO customers (customer_type,company_name,name,email,country_code,phone_no,address,city_name,state_id,country_id,description,customer_lead_id,assigned_to_user,whatsapp_country_code,whatsapp_no,currency_name,phone_no_country_id,whatsapp_no_country_id) VALUES ('" . $customer_type . "','".$company_name."','".$name."','".$email."','".$country_code."','".$phone_no."','".$address."','".$city_name."',".$state_id.",".$country_id.",'".$description."',".$lead_origin_id.",".$assigned_to_user.",'".$country_code."','".$phone_no."','INR',".$country_id.",".$country_id.")";
                    DB::statement($sql);*/
                    $customerId = DB::table('customers')->insertGetId([
                        'customer_type' => $customer_type,
                        'company_name' => $company_name,
                        'name' => $name,
                        'email' => $email,
                        'country_code' => '+'.$country_code,
                        'phone_no' => $phone_no,
                        'address' => $address,
                        'city_name' => $city_name,
                        'state_id' => $state_id,
                        'country_id' => $country_id,
                        'description' => $description,
                        'customer_lead_id' => $lead_origin_id,
                        'assigned_to_user' => $assigned_to_user,
                        'whatsapp_country_code' => '+'.$country_code,
                        'whatsapp_no' => $phone_no,
                        'currency_name' => 'INR',
                        'phone_no_country_id' => $country_id,
                        'whatsapp_no_country_id' => $country_id,
                        'user_id' => $this->company_id,
                        'company_id' => $this->company_id,
                        "created_at" => date('Y-m-d h:i:s'),
                        'lead_stage_id' => 7489
                    ]);

                    $customerId = DB::table('customer_labels')->insertGetId([
                        'customer_id' => $customerId,
                        'label_id' => 4768, //4768
                        'user_id' => $this->company_id,
                        'company_id' => $this->company_id
                    ]);
                }

                // Close the CSV file
                fclose($handle);

                // Notify user of success
                return "CSV file imported successfully";
            } else {
                return "Error opening CSV file";
            }
        } else {
            return "No file uploaded or file upload error";
        }
    }

    public function xyz()
    {

        $perPage = 1000;
        $currentPage = 1; // Initial page

        do {
            // Fetch the customers for the current page
            $customers = Customer::select('id','assigned_to_user')
                ->where('company_id', 1408)
                ->where('lead_stage_id', 7489)
                ->paginate($perPage, ['*'], 'page', $currentPage);

            foreach ($customers as $customer) {
                    $logInput = [
                        'internal_remarks' => "Lead added from ImportCRM",
                        'assigned_to' => $customer->assigned_to_user,
                        'customer_id' => $customer->id,
                        'activity_type' => 6,
                        'entry_type' => "leads",
                        'company_id' => 1408,
                        'user_id' => 1408,
                        'created_by' => 1408,
                        'updated_by' => 1408
                    ];
                    LogActivity::addToActivityLog($logInput);
            }

            // Move to the next page
            $currentPage++;

        } while ($customers->hasMorePages());
    }
    /*public function xyz(){
        $customers = Customer::select('id','assigned_to_user')->where('company_id', 815)->where('lead_stage_id', 1873)->get();

        foreach ($customers as $customer) {

            $logInput['internal_remarks'] = "Lead added from ImportCRM";
            $logInput['assigned_to'] = $customer->assigned_to_user;
            $logInput['customer_id'] = $customer->id;
            $logInput['activity_type'] = 6;
            $logInput['entry_type'] = "leads";
            $logInput['company_id'] = 815;
            $logInput['user_id'] = 815;
            $logInput['created_by'] = 815;
            $logInput['updated_by'] = 815;
            LogActivity::addToActivityLog($logInput);
        }

    }*/



    public function leads(){

       /* $currentTimeStamp = Carbon::now()->format('d-m-Y H:i:s');
        $fiveMinutesAgoTimeStamp = Carbon::now()->subMinutes(10)->addSecond(2)->format('d-m-Y H:i:s');
        //$currentTimeStamp = "01-08-2023";
        //$fiveMinutesAgoTimeStamp = "28-07-2023";
        $webhookUrl = 'https://mapi.indiamart.com/wservce/crm/crmListing/v2/?glusr_crm_key='.$request->indiamart_token.'&start_time='.$fiveMinutesAgoTimeStamp.'&end_time='.$currentTimeStamp;
        $response = Http::withOptions(['verify' => base_path('cacert.pem')])->post($webhookUrl);*/
         $response['RESPONSE'] = [
             [
                 'UNIQUE_QUERY_ID' => '2508565312',
                 'QUERY_TYPE' => 'W',
                 'QUERY_TIME' => '2023-07-31 09:58:33',
                 'SENDER_NAME' => 'Brijesh Jani',
                 'SENDER_MOBILE' => '+91-8460180288',
                 'SENDER_EMAIL' => 'thebrijeshjani@gmail.com',
                 'SUBJECT' => 'Requirement for Solar Panel Manufacturer',
                 'SENDER_COMPANY' => 'D jani Solar',
                 'SENDER_ADDRESS' => 'Surat, Gujarat,         395009',
                 'SENDER_CITY' => 'Surat',
                 'SENDER_STATE' => 'Gujarat',
                 'SENDER_PINCODE' => '395009',
                 'SENDER_COUNTRY_ISO' => 'IN',
                 'SENDER_MOBILE_ALT' => '',
                 'SENDER_PHONE' => '',
                 'SENDER_PHONE_ALT' => '',
                 'SENDER_EMAIL_ALT' => '',
                 'QUERY_PRODUCT_NAME' => 'Solar Panel Manufacturer',
                 'QUERY_MESSAGE' => 'I want to buy Solar Panel Manufacturer. Kindly send me price and other details. Type : Monocrystalline Quantity : 150 Quantity Unit : MW Probable Requirement Type : Business Use',
                 'QUERY_MCAT_NAME' => 'Solar Panels',
                 'CALL_DURATION' => '',
                 'RECEIVER_MOBILE' => '',
             ],
             [
                 'UNIQUE_QUERY_ID' => '76328751',
                 'QUERY_TYPE' => 'P',
                 'QUERY_TIME' => '2023-07-31 10:38:38',
                 'SENDER_NAME' => 'Jaydeep Tank',
                 'SENDER_MOBILE' => '+91-7046365682',
                 'SENDER_EMAIL' => 'thebrijeshjani@gmail.com',
                 'SUBJECT' => 'Buyer Call',
                 'SENDER_COMPANY' => 'D jani Solar',
                 'SENDER_ADDRESS' => 'Surat, Gujarat,         395009',
                 'SENDER_CITY' => 'Surat',
                 'SENDER_STATE' => 'Gujarat',
                 'SENDER_PINCODE' => '395009',
                 'SENDER_COUNTRY_ISO' => 'IN',
                 'SENDER_MOBILE_ALT' => '',
                 'SENDER_PHONE' => '',
                 'SENDER_PHONE_ALT' => '',
                 'SENDER_EMAIL_ALT' => '',
                 'QUERY_PRODUCT_NAME' => '',
                 'QUERY_MESSAGE' => '',
                 'QUERY_MCAT_NAME' => '',
                 'CALL_DURATION' => '152',
                 'RECEIVER_MOBILE' => '7861813600',
             ],
             [
                 'UNIQUE_QUERY_ID' => '76328751',
                 'QUERY_TYPE' => 'P',
                 'QUERY_TIME' => '2023-07-31 10:38:38',
                 'SENDER_NAME' => 'Chetan Mordiya',
                 'SENDER_MOBILE' => '+91-9106522144',
                 'SENDER_EMAIL' => 'thebrijeshjani@gmail.com',
                 'SUBJECT' => 'Buyer Call',
                 'SENDER_COMPANY' => 'D jani Solar',
                 'SENDER_ADDRESS' => 'Surat, Gujarat,         395009',
                 'SENDER_CITY' => 'Surat',
                 'SENDER_STATE' => 'Gujarat',
                 'SENDER_PINCODE' => '395009',
                 'SENDER_COUNTRY_ISO' => 'IN',
                 'SENDER_MOBILE_ALT' => '',
                 'SENDER_PHONE' => '',
                 'SENDER_PHONE_ALT' => '',
                 'SENDER_EMAIL_ALT' => '',
                 'QUERY_PRODUCT_NAME' => '',
                 'QUERY_MESSAGE' => '',
                 'QUERY_MCAT_NAME' => '',
                 'CALL_DURATION' => '152',
                 'RECEIVER_MOBILE' => '7861813600',
             ]
         ];
         $this->import_lead_new($response,'5');
         die;
        if ($response->successful()) {
            switch ($response['CODE']) {
                case '401':
                    if($response['MESSAGE']=="CRM key that you are using is incorrect. Kindly use the correct CRM key as provided in the email."){
                        return response()->json(['errors' => $response['MESSAGE']], 400);
                    }else if (strpos($response['MESSAGE'], "will be available after 24 hours from") !== false) {
                        return response()->json(['errors' => $response['MESSAGE']], 400);
                    }
                    break;
                case '204':
                    return response()->json(['errors' => $response['MESSAGE']], 400);
                    break;
                default:
                    $flg=0;
                    if($request->indiamart_token=='mR21E7hp53fIQPep532D7liLp1PMmzVm'){
                        $flg=1;
                    }
                    $res = $this->import_lead_new($response,$request->user_id,$flg);
                    // $value['user_id'] = $this->company_id;
                    // $value['indiamart_token'] = $data->indiamart_token;
                    // $customer = Indiamart_api_tokens::updateOrCreate($value);
                    return response()->json(['success' => 'Successfully Integrated!'], 201);
                    break;
            }
        } else {
            return $response->status();
        }
    }

    public function import_lead_new($request,$user_id,$flg=0){
        $tmpname = 'IndiaMART';
        if($flg == 1){
            $tmpname = 'IndiaMart 1';
        }
        if(!empty($request['RESPONSE'])){
            $logged_user = User::select(["id","company_id","indiamart_integration","name","assigned_list","lead_merge_flag"])->where('id',$user_id)->first();
            if($logged_user){
                $company_id = $logged_user->company_id?$logged_user->company_id:$logged_user->id;

                $logged_user_company = User::select(["id","company_id","lead_merge_flag"])->where('id',$company_id)->first();

                $lead_id = CustomerLead::select('id')->where(function ($query) use ($company_id,$tmpname) {
                    $query->where('name', $tmpname);
                    $query->where('company_id', $company_id);
                    $query->where('status', '=', 0);
                })->first();

                if(empty($lead_id)){
                    $category_array=[
                        'name'=>$tmpname,
                        'user_id'=>$logged_user->id,
                        'company_id'=>$company_id
                    ];
                    $customerLeadId = CustomerLead::create($category_array);
                    $customer_lead_id = $customerLeadId['id'];
                }else{
                    $customer_lead_id = $lead_id['id'];
                }

                $last_user = 0;
                foreach ($request['RESPONSE'] as $key => $lead) {
                    $lastAssignedUser = Lead_assign_users::join("users", 'lead_assign_users.user_id', '=', 'users.id')->where('lead_assign_users.company_id',$company_id)->where('lead_assign_users.assigned_list',0)->select('lead_assign_users.user_id','users.name','lead_assign_users.company_id','lead_assign_users.id','lead_assign_users.assigned_list','users.device_key','users.mobile_device_key')->orderBy('lead_assign_users.assigned_list','ASC')->orderBy('lead_assign_users.id','ASC')->first();

                    if(!$lastAssignedUser){
                        Lead_assign_users::where('company_id',$company_id)->update(['assigned_list'=>0]);
                        $lastAssignedUser = Lead_assign_users::join("users", 'lead_assign_users.user_id', '=', 'users.id')->where('lead_assign_users.company_id',$company_id)->where('lead_assign_users.assigned_list',0)->select('lead_assign_users.user_id','users.name','lead_assign_users.company_id','lead_assign_users.id','lead_assign_users.assigned_list','users.device_key','users.mobile_device_key')->orderBy('lead_assign_users.assigned_list','ASC')->orderBy('lead_assign_users.id','ASC')->first();
                    }


                    $assignedUserId= $logged_user->id;
                    $assignedUserName = $logged_user->name;

                    if(!empty($lead['SENDER_COMPANY'])){
                        $customer_type = "Business";
                    }else{
                        $customer_type = "Individual";
                    }
                    $sender_mobile = explode("-", $lead['SENDER_MOBILE']);
                    $countryCode = $sender_mobile[0];
                    $phone_no = $sender_mobile[1];
                    $logInput['internal_remarks'] = "Lead added from ".$tmpname;

                    $assigned_to_user = $lastAssignedUser->user_id;
                    $assigned_to_user_name = $lastAssignedUser->name;
                    if($logged_user->indiamart_integration=="Unassigned"){
                        $assigned_to_user = '';
                        $assigned_to_user_name = '';
                    }

                    if($logged_user->indiamart_integration=="Round-Robin"){
                        $assigned_to_user = $lastAssignedUser->user_id;
                        $assigned_to_user_name = $lastAssignedUser->name;
                    }

                    if($logged_user->indiamart_integration=="Others"){

                        $existData = Customer::where('phone_no', '=', $phone_no)
                            ->where('company_id', $company_id)
                            /*->where(function ($query) use ($id) {
                                if ($id != 0) {
                                    $query->Where(function ($query) use ($id) {
                                        $query->where('id', '!=', $id);
                                    });
                                }
                            })*/
                            ->orderBy('id','DESC')
                            ->first();

                        if($existData){
                            $assigned_to_user = $existData->assigned_to_user;

                            $exist_user1 = User::select(["id","name"])->where('id',$assigned_to_user)->where('company_id', $company_id)->first();


                            $assigned_to_user_name = $exist_user1->name;
                        }

                        $exist_user = User::select(["id","name"])->where('mobile_no',$lead['RECEIVER_MOBILE'])->where('company_id', $company_id)->first();
                        if($exist_user){
                            $assigned_to_user = $exist_user->id;
                            $assigned_to_user_name = $exist_user->name;
                        }

                    }

                    $lead_data = [
//                        'assigned_to_user' => $logged_user->indiamart_integration=="Unassigned"?'':$lastAssignedUser->user_id,
                        'assigned_to_user' => $assigned_to_user,
                        'user_id' => $logged_user->id,
                        'company_id' => $company_id,
                        'name'=> $lead['SENDER_NAME'],
                        'email'=> $lead['SENDER_EMAIL'],
                        'phone_no'=> $phone_no,
                        'pincode'=> $lead['SENDER_PINCODE'],
                        'address'=> $lead['SENDER_ADDRESS'],
                        'description'=> $lead['QUERY_MESSAGE'],
                        'new_lead_flag' => 1,
                        'created_at'=> date('Y-m-d H:i:s'),
                        'updated_at'=> date('Y-m-d H:i:s'),
                        'customer_lead_id'=>$customer_lead_id,
                        'customer_type'=>$customer_type,
                        'country_code'=>$countryCode,
                        'whatsapp_country_code'=>$countryCode,
                        'whatsapp_no'=> $phone_no,
                    ];

                    if($lead['SENDER_STATE']!=""){
                        $state = State::select(['id','country_id'])->where(function ($query) use ($lead) {
                            $query->where('name', $lead['SENDER_STATE']);
                        })->first();
                        if(!empty($state)){
                            $lead_data['state_id'] = $state['id'];
                        }
                    }
                    $lead_data['country_id'] = "101";
                    if($lead['SENDER_COUNTRY_ISO']!=""){
                        $country = Country::select(['id'])->where(function ($query) use ($lead) {
                            $query->where('sortname', $lead['SENDER_COUNTRY_ISO']);
                        })->first();
                        if(!empty($country)){
                            $lead_data['country_id'] = $country['id'];
                        }
                    }

                    $country_data = Country::where("id", $lead_data['country_id'])->select('name','currency_name','currency_code','currency_symbol')->orderBy('id', 'DESC')->get()->first();
                    $lead_data['currency_name'] = $country_data->currency_code;
                    $lead_data['currency_name_country_id'] = $lead_data['country_id'];
                    $lead_data['phone_no_country_id'] = $lead_data['country_id'];
                    $lead_data['whatsapp_no_country_id'] = $lead_data['country_id'];

                    $lead_data['lead_stage_id'] = 0;
                    $lead_stage_data = LeadStage::where('company_id',$company_id)->where('is_default',1)->select('name','id')->first();
                    if($lead_stage_data){
                        $lead_data['lead_stage_id'] =$lead_stage_data->id;
                    }

                    $existData = Customer::where('phone_no', '=', $phone_no)
                        ->where('company_id', $company_id)
                        ->orderBy('id','DESC')
                        ->first();

                    if(!$existData || $logged_user_company->lead_merge_flag==0) {
                        $customer = Customer::create($lead_data);
                        $ids = $customer->id;

                        $logInput['activity_type'] = 6;
                        $logInput['customer_id'] = $ids;
                        $logInput['user_id'] =  $logged_user->id;
                        $logInput['company_id'] =  $company_id;
                        $logInput['created_by'] =  $logged_user->id;
                        $logInput['updated_by'] =  $logged_user->id;
                        LogActivity::addToActivityLogGuest($logInput);
                        if($logged_user->indiamart_integration!="Unassigned"){
                            $logInput['activity_type'] = 8;
                            $logInput['entry_type'] = "assigned";
                            $logInput['internal_remarks'] = "Assigned to " . $assigned_to_user_name;
                            LogActivity::addToActivityLogGuest($logInput);

                            $noficationArr['customer_id'] = $ids;
                            $noficationArr['notification_type'] = "assign_to_you";
                            $noficationArr['device_key'] = $lastAssignedUser->device_key;
                            $noficationArr['mobile_device_key'] = $lastAssignedUser->mobile_device_key;
                            //$noficationArr['mobile_device_key'] = null;
                            $noficationArr['title'] = 'New Lead Assigned To You';
                            $noficationArr['body'] = $lead['SENDER_NAME'] . ' is assigned to you by ' . $assigned_to_user_name;
                            $this->sendAssigntoUserNotification($noficationArr);
                        }
                    }

                    if($existData && $logged_user_company->lead_merge_flag==1) {
                        $existDatafollowup = EstimateTimeline::where('customer_id', '=', $existData->id)
                            ->where('company_id', $company_id)
                            ->select('follow_up_datetime')
                            ->orderBy('id','DESC')
                            ->first();
                        $logInput['follow_up_datetime'] = $existDatafollowup->follow_up_datetime;
                        $logInput['assigned_to'] = $assigned_to_user;
                        $logInput['activity_type'] = 20;
                        $logInput['entry_type'] = "merge";
                        $logInput['activity_name'] = "Lead Merge";
                        $logInput['internal_remarks'] = $lead['QUERY_MESSAGE'];
                        $logInput['activity_notes'] = $lead['QUERY_MESSAGE'];
                        $logInput['customer_id'] = $existData->id;
                        $logInput['user_id'] =  $logged_user->id;
                        $logInput['company_id'] =  $company_id;
                        $logInput['created_by'] =  $logged_user->id;
                        $logInput['updated_by'] =  $logged_user->id;
                        LogActivity::addToActivityLogGuest($logInput);

                        $noficationArr['customer_id'] = $existData->id;
                        $noficationArr['notification_type'] = "assign_to_you";
                        $noficationArr['device_key'] = $lastAssignedUser->device_key;
                        $noficationArr['mobile_device_key'] = $lastAssignedUser->mobile_device_key;
                        //$noficationArr['mobile_device_key'] = null;
                        $noficationArr['title'] = 'New Lead Assigned To You';
                        $noficationArr['body'] = $lead['SENDER_NAME'] . ' is assigned to you by ' . $assigned_to_user_name;
                        $this->sendAssigntoUserNotification($noficationArr);
                    }


                    Log::channel('webhook')->info('Webhook Indiamart', ['data' => $assigned_to_user_name]);

//                    Lead_assign_users::where('company_id',$company_id)->where("user_id",$lastAssignedUser->user_id)->update(['assigned_list'=>1]);
                    Lead_assign_users::where('company_id',$company_id)->where("user_id",$assigned_to_user)->update(['assigned_list'=>1]);

                }

            }
        }
    }

    public function export(Request $request)
    {
        $input = $request->all();
        return Excel::download(new CustomersExport($input), 'leads.xlsx');
    }


    public function leadExportIndex(Request $request)
    {

        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
        if ($request->ajax()) {
            $input = $request->all();
            ## Read value

            $fil_lead_stage_id = $request->get('fil_lead_stage_id');
            $status = [];
            if($request->get('status'))
                $status = explode(",",$request->get('status'));

            $fil_customer_category_id = $request->get('fil_customer_category_id');
            $fil_customer_lead_id = $request->get('fil_customer_lead_id');

            $fil_created_user_id = $request->get('fil_created_user_id');
            $fil_estimate_status_id = $request->get('fil_estimate_status_id');

            $fil_country_id = $request->get('fil_country_id');
            $fil_state_id = $request->get('fil_state_id');
            $fil_city_name = $request->get('fil_city_name');

//            DB::enableQueryLog();
            $records = DB::table('customers_views as cv')
                ->leftJoin('customer_labels as cl', 'cl.customer_id', '=', 'cv.id')
                ->leftJoin('lead_groups as lg', 'cl.label_id', '=', 'lg.id')
                ->select('cv.updated_at','cv.country_code','cv.company_name','cv.created_at','cv.city_name','cv.state_name','cv.country_name','cv.status','cv.lead_category','cv.email','cv.address','cv.pincode','cv.description','cv.lead_origin','cv.customer_type','cv.id','cv.name','cv.phone_no','cv.last_activity','cv.user_name','cv.assigned_to_user','cv.net_amount','cv.estimate_status','cv.last_activity_type','cv.last_internal_remarks','cv.last_activity_name','cv.last_follow_up_datetime','cv.last_is_modified','cv.last_is_follow_up','cv.some_day_flg','cv.est_currency_id','cv.estimate_status','cv.last_activity_updated_at','cv.new_lead_flag','cv.lead_stage_name','cv.lead_stage_color_code', DB::raw('GROUP_CONCAT(lg.name) as label_name'), DB::raw('GROUP_CONCAT(lg.color_code) as label_color_code'), DB::raw('GROUP_CONCAT(lg.id) as label_id'))
                ->where(function ($query) use ($input) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(cv.created_at, '%Y-%m-%d')"), [$input['fil_lead_date_start'], $input['fil_lead_date_end']]);
                })
                ->where(function ($query) use ($input) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(customers_views.created_at, '%Y-%m-%d')"), [$input['fil_lead_date_start'], $input['fil_lead_date_end']]);
                })
                ->where(function ($query) use ($fil_lead_stage_id,$status,$fil_customer_category_id,$fil_customer_lead_id,$fil_created_user_id,$fil_estimate_status_id,$fil_country_id,$fil_state_id,$fil_city_name,$user_perm) {
                    if ($fil_lead_stage_id) {
                        $query->where('customers_views.lead_stage_id', $fil_lead_stage_id);
                    }
                    if ($status) {
                        $query->WhereIn('lg.id', $status);
                    }
                    if ($fil_customer_category_id != '') {
                        $query->where('cv.customer_category_id', '=', $fil_customer_category_id);
                    }
                    if ($fil_customer_lead_id != '') {
                        $query->where('cv.customer_lead_id', '=', $fil_customer_lead_id);
                    }
                    if ($fil_created_user_id != '') {
                        $query->where('cv.user_id', '=', $fil_created_user_id);
                    }
                    if ($fil_estimate_status_id != '') {
                        $query->where('cv.estimate_status', '=', $fil_estimate_status_id);
                    }


                    if ($fil_country_id) {
                        $query->where('cv.country_id', $fil_country_id);
                    }
                    if ($fil_state_id) {
                        $query->where('cv.state_id', $fil_state_id);
                    }
                    if ($fil_city_name) {
                        $query->where('cv.city_name', $fil_city_name);
                    }

                })
                ->groupBy('cv.id');
            if ($status) {
                $records = $records->WhereIn("lg.id",$status);
            }

            $records = $records->get();

//            dd(DB::getQueryLog($records));

//            dd(DB::getQueryLog());

            $data = array();
            $i = 0;


        }

        $countries = Country::select(["name", "id", "phonecode","sortname","currency_name","currency_code","currency_symbol"])->where('status', '=', 0)->orderBy('name','ASC')->get();

        $fil_states = State::select("*")->where('status', '=', 0)->orderBy('name','ASC')->get();

        $customerCategories = CustomerCategory::select(["name", "id"])->where('status', '=', 0)->where('company_id', $this->company_id)->get();
        $customerLeads = CustomerLead::select(["name", "id"])
            ->where('status', '=', 0)
            ->where(function ($query) {
                $query->where('company_id', '=', $this->company_id);
                $query->orwhere('is_status', '=', 1);
            })
            ->get();

        $leadStages = LeadStage::select("*")
            ->where('status', '=', 0)
            ->where(function ($query) {
                $query->where('company_id', '=', $this->company_id);
            })
            ->orderBy('priority', 'asc')
            ->get();

        $leadGroups = $data = DB::table('lead_groups')
            ->leftJoin('customer_labels', 'lead_groups.id', '=', 'customer_labels.label_id')
            ->select('lead_groups.*', DB::raw('count(customer_labels.id) as lead_count'))
            ->where('lead_groups.company_id', $this->company_id)
            ->where('lead_groups.status', 0)
            ->orderBy('lead_groups.name', 'asc')
            ->groupBy('lead_groups.id')
            ->get();
        $teamUsers = User::select(["name", "id", "email", "mobile_no"])
            ->where('invite_status', 1)
            ->where(function ($query) {
                $query->orwhere('company_id', $this->company_id);
                $query->orwhere('id', $this->company_id);
            })
            ->get();
        $leads = User::select(["name", "id", "email", "mobile_no"])
            ->where('invite_status', 1)
            ->where(function ($query) {
                $query->orwhere('company_id', $this->company_id);
                $query->orwhere('id', $this->company_id);
            })
            ->get();
        $leadLabels = LeadGroup::select(["name", "color_code", "id"])->where('status', '=', 0)->where('company_id', $this->company_id)->get();
        $lostReasons = LostReason::select("*")
            ->where('status', '=', 0)
            ->where(function ($query) {
                $query->where('company_id', '=', $this->company_id);
            })
            ->orderBy('name', 'asc')
            ->get();
        $teamUsers = User::select(["name", "id", "email", "mobile_no"])
            ->where('invite_status', 1)
            ->where(function ($query) {
                $query->orwhere('company_id', $this->company_id);
                $query->orwhere('id', $this->company_id);
            })
            ->get();
        $segment = $this->segment;
        return view('app.customer-export', compact('countries', 'customerCategories', 'customerLeads', 'leadGroups', 'teamUsers','leads', 'leadStages', 'fil_states','leadLabels','lostReasons','segment'))->with('main_company', $this->main_company);
    }


    public function showWhatsAppQRCode()
    {

//        $output = [];
//        $returnVar = 0;
        $output = shell_exec('python E:\\python\\generate_qr_code.py');
        $qrData = json_decode($output, true);
//        $command = escapeshellcmd('C:\\Python312 E:\\python\\generate_qr_code.py');
//        exec($command, $output, $returnVar);

//        return response()->json([
//            'output' => $output,
//            'status' => $returnVar == 0 ? 'success' : 'error',
//        ]);
//        return view('whatsapp.qr', ['qrCode' => $qrData['qr_code_base64']]);
        return view('whatsapp.qr');


        // Execute the Python script and capture the output
//        $output = shell_exec('python3 E:\\python\\generate_qr_code.py');
/*
        // Check if output was returned
        if ($output === null) {
            return response()->json(['error' => 'Failed to execute the Python script'], 500);
        }

        // Decode JSON output
        $qrData = json_decode($output, true);

        // Check if JSON decoding was successful
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Failed to decode JSON response from Python script'], 500);
        }

        // Return the view with the QR code data
        return view('whatsapp.qr', ['qrCode' => $qrData['qr_code_base64']]);*/
    }
    /*public function showWhatsAppQRCode()
    {
        // Execute the Python script and capture the output
        $output = shell_exec('python3 E:\python\generate_qr_code.py');

        // Log the raw output for debugging
        \Log::info('Python script output: ' . $output);

        // Decode JSON output
        $qrData = json_decode($output, true);

        // Check if the output was decoded successfully
        if ($qrData === null) {
            \Log::error('Failed to decode JSON output from Python script');
            return response()->json(['error' => 'Failed to generate QR code'], 500);
        }

        // Return the view with the QR code data
        return view('whatsapp.qr', ['qrCode' => $qrData['qr_code_base64']]);
    }*/


}

