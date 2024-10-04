<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Item;
use App\Models\ProposalTemplates;
use App\Models\Tax;
use App\Models\Unit;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use LogActivity;

class ItemController extends Controller
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
        $user = Auth::user();
        $company_id = ($user->company_id) ? $user->company_id : $user->id;
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
            $totalRecords = Item::select('count(id) as allcount')->where(function ($query) use ($name, $status) {
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
            })->where('company_id', $company_id)->count();
            if($search_arr !== null) {
                $totalRecordswithFilter = Item::select('count(id) as allcount')->join('units', 'items.unit_id', '=', 'units.id')->where('items.company_id', $company_id)->where(function ($query) use ($name, $status) {
                    if ($name != '') {
                        $query->Where(function ($query) use ($name) {
                            $query->where('items.name', '=', $name);
                        });
                    }
                    if ($status != '') {
                        $query->where(function ($query) use ($status) {
                            $query->where('items.status', '=', $status);
                        });
                    }
                })
                ->where(function ($query) use ($search_arr) {
                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('units.name', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('items.name', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('items.description', 'like', '%' . $search_arr . '%');
                    });
                })->where('items.company_id', $company_id)->count();
            } else {
                $totalRecordswithFilter = 0;
            }


            // DB::enableQueryLog();
            $records = DB::table('items')
                ->where('items.company_id', $company_id)
                ->join('units', 'items.unit_id', '=', 'units.id')
                ->where(function ($query) use ($name, $status) {
                    if ($name != '') {
                        $query->Where(function ($query) use ($name) {
                            $query->where('items.name', '=', $name);
                        });
                    }
                    if ($status != '') {
                        $query->where(function ($query) use ($status) {
                            $query->where('items.status', '=', $status);
                        });
                    }
                })
                ->where(function ($query) use ($search_arr) {
                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('units.name', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('items.name', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('items.description', 'like', '%' . $search_arr . '%');
                    });
                })
                ->select('items.*', 'units.name as unit_name')
                ->skip($start)
                ->take($rowperpage)
                ->orderBy($columnName, $columnSortOrder)
                ->get();

//            dd(DB::getQueryLog());

            $data = array();
            $i = 0;
            foreach ($records as $record) {
                $id = Crypt::encrypt($record->id);
//                $image_icon = ($record->image_icon)?Storage::url($record->image_icon):null;
                $image_icon = ($record->image_icon)?Storage::disk('s3')->temporaryUrl(trim($record->image_icon),Carbon::now()->addMinutes(20)):null;
                $name = $record->name;
                $description = $record->description;
                $status = $record->status;
                $unit_name = $record->unit_name;
                $i++;
                $data[] = array(
                    "id" => $i,
                    "unit_name" => $unit_name,
                    "image_icon" => $image_icon,
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
        $units = Unit::select(["name", "id"])->where('status', '=', 0)->where('company_id', $company_id)->get();
        $proposal_template = ProposalTemplates::where('company_id', $company_id)->first();
        $taxes = Tax::query()->select(["id", "name"])
            ->where([['company_id', "=", $company_id], ["status", "=", 0]])
            ->orderBy("name")
            ->get();
        $segment = $this->segment;
        $country_data = Country::
        Join('users', 'users.country_id', '=', 'countries.id')
            ->where('users.id', $company_id)
            ->select('countries.name','countries.currency_name','countries.currency_code','countries.currency_symbol','countries.sortname')->orderBy('countries.id', 'DESC')->get()->first();
        return view('app.item', compact('units', 'taxes','proposal_template','country_data', 'segment'));
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $validator = Validator::make($input, [
                'name' => 'required',
                'unit_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);;
            }

            $user = Auth::user();
            $input['user_id'] = $user->id;
            $input['sale_price'] = 0 + $input['sale_price'];
            $input['cost_price'] = 0 + $input['cost_price'];
            $input['company_id'] = ($user->company_id) ? $user->company_id : $user->id;
            $input['intra_state'] = ($input['tax_preference']=='Taxable') ? $input['intra_state'] : 0;
            $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
            if (Item::where('name', '=', $input['name'])->where('company_id', $input['company_id'])->where(function ($query) use ($id) {
                if ($id != 0) {
                    $query->Where(function ($query) use ($id) {
                        $query->where('id', '!=', $id);
                    });
                }
            })->first()) {
                return response()->json(['success' => 'Item exists!'], 409);
            }
            if ($request->hasFile('image_icon')) {
                if ($id > 0) {
                    $itemData = Item::find($id);
                    /*if (Storage::exists($itemData->image_icon)) {
                        Storage::delete($itemData->image_icon);
                    }*/

                    if ($itemData->image_icon) {
                        Storage::disk('s3')->delete($itemData->image_icon);
                    }

                }
                /*$path = $request->file('image_icon')->store('public/uploads/items');
                $input['image_icon'] = $path;*/

                $path = Storage::disk('s3')->put('public/'.$input['company_id'].'/items', $request->image_icon,'public');
                $input['image_icon'] = 'public/'.$input['company_id'].'/items/'.basename(Storage::disk('s3')->url($path));
            }
            if ($id == 0) {
                $activityLogMsg = 'Item created by ' . $user->name;
                $country = Item::create($input);
            } else {
                $country = Item::find($id)->update($input);
                $activityLogMsg = 'Item updated by ' . $user->name;
            }

            // Add activity logs
            $input['id'] = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
            LogActivity::addToLog($activityLogMsg, $input);

            return response()->json(['success' => 'Item Saved!'], 201);
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

            $item = Item::find($id)->toArray();

            if (is_null($item)) {
                return response()->json(['success' => 'Item not found!'], 422);
            }
            $item['id'] = Crypt::encrypt($item['id']);
            return response()->json([
                "success" => true,
                "message" => "Item retrieved successfully.",
                "data" => $item
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

            $errors = [];

            $unitIds = explode(",", $request->id);
            foreach ($unitIds as $value) {
                $ids[] = Crypt::decrypt($value);
            }
            $unitsWithTransactions = DB::table('estimate_items')->join("items", 'estimate_items.item_id', '=', 'items.id')
                ->whereIn('estimate_items.item_id', $ids)
                ->pluck('estimate_items.item_id','items.name')
                ->toArray();

            foreach ($ids as $unitId) {
                if (in_array($unitId, $unitsWithTransactions)) {
                    $key = array_search($unitId, $unitsWithTransactions);
                    $errors[] = "{$key} has transactions associated with it in the estimate list";
                    continue;
                }
                try {
                    Item::where('id', $unitId)->delete();
                    // LogActivity::addToLog('Item status updated by ' . $user->name, $data);
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
            $country = Item::whereIn('id', $id)->delete();

            LogActivity::addToLog('Item deleted by ' . $user->name, $id);*/
            return response()->json(['success' => 'Item Deleted!'], 201);
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

            $errors = [];

            $unitIds = explode(",", $request->id);
            foreach ($unitIds as $value) {
                $ids[] = Crypt::decrypt($value);
            }
           /* $unitsWithTransactions = DB::table('estimate_items')->join("items", 'estimate_items.item_id', '=', 'items.id')
                ->whereIn('estimate_items.item_id', $ids)
                ->pluck('estimate_items.item_id','items.name')
                ->toArray();*/

            foreach ($ids as $unitId) {
              /*  if (in_array($unitId, $unitsWithTransactions)) {
                    $key = array_search($unitId, $unitsWithTransactions);
                    $errors[] = "{$key} has transactions associated with it in the estimate list";
                    continue;
                }*/
                try {
                    Item::where('id', $unitId)->update(["status" => $input['status']]);
                    $data['id'] = $unitId;
                    $data['status'] = ($input['status'] == 0) ? 'Active' : 'Deactive';
                    LogActivity::addToLog('Item status updated by ' . $user->name, $data);
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if($errors){
                return response()->json($errors,400);
            }

           /* $id = [];
            foreach (explode(",", $input['id']) as $value) {
                $id[] = Crypt::decrypt($value);
            }
            if (!Item::whereIn('id', $id)->first()) {
                return response()->json(['success' => 'Item exists!'], 422);
            }
            $item = Item::whereIn('id', $id)->update(["status" => $input['status']]);

            $data['id'] = $id;
            $data['status'] = ($input['status'] == 0) ? 'Active' : 'Deactive';
            LogActivity::addToLog('Item status updated by ' . $user->name, $data);*/

            return response()->json(['success' => 'Item status updated!'], 201);
        }
    }

    public function itemAutocomplete(Request $request)
    {
        if ($request->ajax()) {
            $search = $request->get('search');
            $user = Auth::user();
            $company_id = ($user->company_id) ? $user->company_id : $user->id;
            $items = Item::select('items.id', 'items.name', 'items.sale_price', 'items.description', 'items.inter_state', 'items.intra_state', 'items.hsn_code', 'units.name as unit_name','item_discount','item_discount_flag','technical_specification')
                ->join('units', 'items.unit_id', '=', 'units.id')
                ->where('items.company_id', $company_id)
                ->where('items.name', 'LIKE', '%' . $search . '%')
                ->where('items.status', 0)
                ->get();
            $response = array();
            foreach ($items as $item) {
                $response[] = array("value" => $item->id, "label" => $item->name, "desc_span" => nl2br($item->description),"desc" => $item->description, "sale_price" => (float)$item->sale_price, "inter_state" => $item->inter_state, "intra_state" => $item->intra_state, "hsn_code" => $item->hsn_code, "unit_name" => $item->unit_name,"item_discount" =>$item->item_discount,"item_discount_flag" =>$item->item_discount_flag,'technical_specification' => $item->technical_specification);
            }

            return response()->json($response);
            return response()->json($result);
        }
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $company_id = ($user->company_id) ? $user->company_id : $user->id;
        $units = Unit::select(["name", "id"])->where('status', '=', 0)->where('company_id', $company_id)->get();
        $proposal_template = ProposalTemplates::where('company_id', $company_id)->first();
        $taxes = Tax::query()->select(["id", "name"])
            ->where([['company_id', "=", $company_id], ["status", "=", 0]])
            ->orderBy("name")
            ->get();

        $country_data = Country::
        Join('users', 'users.country_id', '=', 'countries.id')
            ->where('users.id', $company_id)
            ->select('countries.name','countries.currency_name','countries.currency_code','countries.currency_symbol','countries.sortname')->orderBy('countries.id', 'DESC')->get()->first();
        $segment = $this->segment;
        return view('app.item-create', compact('units', 'taxes','proposal_template','country_data', 'segment'));
    }

    public function edit($id)
    {
        $id = Crypt::decrypt($id);
        $user = Auth::user();
        $company_id = ($user->company_id) ? $user->company_id : $user->id;
        $item = Item::where('id',$id)->first();
        $item->id = Crypt::encrypt($id);


        $units = Unit::select(["name", "id"])->where('status', '=', 0)->where('company_id', $company_id)->get();
        $proposal_template = ProposalTemplates::where('company_id', $company_id)->first();
        $taxes = Tax::query()->select(["id", "name"])
            ->where([['company_id', "=", $company_id], ["status", "=", 0]])
            ->orderBy("name")
            ->get();

        $country_data = Country::
        Join('users', 'users.country_id', '=', 'countries.id')
            ->where('users.id', $company_id)
            ->select('countries.name','countries.currency_name','countries.currency_code','countries.currency_symbol','countries.sortname')->orderBy('countries.id', 'DESC')->get()->first();

        return view('item-edit', compact('units', 'taxes','proposal_template','country_data', 'item'));
    }
}
