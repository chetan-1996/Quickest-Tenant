<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\TermCondition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;
use App\Helpers\LogActivity;

class TermConditionController extends Controller
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
            $search_arr = $request->get('search');

            $columnIndex = $columnIndex_arr[0]['column']; // Column index
            $columnName = $columnName_arr[$columnIndex]['data']; // Column name
            $columnSortOrder = $order_arr[0]['dir']; // asc or desc

            // Fetch records
            $name = $request->get('name');
            $status = $request->get('status');
            // Total records

            $records = DB::table('term_conditions')
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
                });
            $totalRecords = $records->count();
            if($search_arr != null) {
                $records->where(function ($query) use ($search_arr) {
                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('name', 'like', $search_arr . '%');
                    });
                })
                    ->orWhere(function ($query) use ($search_arr) {
                        if ($search_arr) {
                            $query->orWhere(function ($query) use ($search_arr) {
                                $query->where('description', 'like', $search_arr . '%');
                            });
                        }
                    });
                $totalRecordswithFilter = $records->count();
            } else {
                $totalRecordswithFilter = 0;
            }
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
        $segment = $this->segment;
        return view('app.term-condition', compact('segment'));
    }

    public function create(Request $request)
    {
        $segment = $this->segment;
        return view('app.terms-conditions.new', compact('segment'));
    }

    public function edit($id)
    {
        $id = ($id) ? Crypt::decrypt($id) : $id;

        $terms_conditions = TermCondition::where("id", $id)->first();
        if (!$terms_conditions) {
            return redirect()->back()->withInput();
        }
        $segment = $this->segment;
        return view('app.terms-conditions.edit', compact('terms_conditions', 'segment'));
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
            $id = ($input['id']) ? $input['id'] : $input['id'];
            if (TermCondition::where('name', '=', $input['name'])->where('company_id', $input['company_id'])->select('id')->where(function ($query) use ($id) {
                if ($id != 0) {
                    $query->Where(function ($query) use ($id) {
                        $query->where('id', '!=', $id);
                    });
                }
            })->first()) {
                return response()->json(['success' => 'Term & Condition exists!'], 409);
            }
            if ($id == 0) {
                $activityLogMsg = 'Term & Condition created by ' . $this->logged_user->name;
                $termConditions = TermCondition::create($input);
            } else {
                $termConditions = TermCondition::find($id)->update($input);
                $activityLogMsg = 'Term & Condition updated by ' . $this->logged_user->name;
            }

            // Add activity logs
            $input['id'] = ($input['id']) ? $input['id'] : $input['id'];
            LogActivity::addToLog($activityLogMsg, $input);

            return response()->json(['success' => 'Term & Condition Saved!'], 201);
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

            $termConditions = TermCondition::find($id)->toArray();

            if (is_null($termConditions)) {
                return response()->json(['success' => 'Term & Condition not found!'], 422);
            }
            $termConditions['id'] = Crypt::encrypt($termConditions['id']);
            return response()->json([
                "success" => true,
                "message" => "Term & Condition retrieved successfully.",
                "data" => $termConditions
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

            $termIds = explode(",", $request->id);
            foreach ($termIds as $value) {
                $ids[] = Crypt::decrypt($value);
            }
            $termsWithTransactions = DB::table('estimates')->join("term_conditions", 'estimates.term_condition_id', '=', 'term_conditions.id')
                ->whereIn('estimates.term_condition_id', $ids)
                ->pluck('estimates.term_condition_id','term_conditions.name')
                ->toArray();

            foreach ($ids as $termId) {
                if (in_array($termId, $termsWithTransactions)) {
                    $key = array_search($termId, $termsWithTransactions);
                    $errors[] = "{$key} has transactions associated with it in the estimate list";
                    continue;
                }
                try {
                    DB::table('term_conditions')
                        ->where('id', $termId)
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
            $termConditions = TermCondition::whereIn('id', $id)->delete();*/

            LogActivity::addToLog('Term & Condition deleted by ' . $this->logged_user->name, $ids);
            return response()->json(['success' => 'Term & Condition Deleted!'], 201);
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

            $termIds = explode(",", $request->id);
            foreach ($termIds as $value) {
                $ids[] = Crypt::decrypt($value);
            }
            $termsWithTransactions = DB::table('estimates')->join("term_conditions", 'estimates.term_condition_id', '=', 'term_conditions.id')
                ->whereIn('estimates.term_condition_id', $ids)
                ->pluck('estimates.term_condition_id','term_conditions.name')
                ->toArray();

            foreach ($ids as $termId) {
                if (in_array($termId, $termsWithTransactions)) {
                    $key = array_search($termId, $termsWithTransactions);
                    $errors[] = "{$key} has transactions associated with it in the estimates list";
                    continue;
                }
                try {
                    TermCondition::where('id', $termId)->update(["status" => $input['status']]);
                    $data['id'] = $termId;
                    $data['status'] = ($input['status'] == 0) ? 'Active' : 'Deactive';
                    LogActivity::addToLog('Term & Condition status updated by ' . $this->logged_user->name, $data);
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
            if (!TermCondition::whereIn('id', $id)->first()) {
                return response()->json(['success' => 'Term & Condition exists!'], 422);
            }
            $termConditions = TermCondition::whereIn('id', $id)->update(["status" => $input['status']]);

            $data['id'] = $id;
            $data['status'] = ($input['status'] == 0) ? 'Active' : 'Deactive';
            LogActivity::addToLog('Term & Condition status updated by ' . $this->logged_user->name, $data);*/

            return response()->json(['success' => 'Term & Condition status updated!'], 201);
        }
    }

    public function termAjax(Request $request)
    {
        if ($request->ajax()) {
            $search = $request->get('id');
            $response = TermCondition::select('*')
                ->where('company_id', $this->company_id)
                ->where('id', $search)
//                ->where('status', 1)
                ->first();

           /* foreach ($items as $item) {
                $response[] = array("value" => $item->id, "label" => $item->name, "desc_span" => nl2br($item->description),"desc" => $item->description, "sale_price" => (float)$item->sale_price, "inter_state" => $item->inter_state, "intra_state" => $item->intra_state, "hsn_code" => $item->hsn_code, "unit_name" => $item->unit_name,"item_discount" =>$item->item_discount,"item_discount_flag" =>$item->item_discount_flag);
            }*/

            return response()->json($response);
        }
    }
}
