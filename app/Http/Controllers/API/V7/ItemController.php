<?php

namespace App\Http\Controllers\API\V6;

use App\Http\Controllers\API\V6\BaseController as BaseController;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use LogActivity;

class ItemController extends BaseController
{
    protected $logged_user = null;
    protected $company_id = 0;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->logged_user = Auth::user();
            $this->company_id = ($this->logged_user->company_id) ? $this->logged_user->company_id : $this->logged_user->id;
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $search_arr = $request->query('search');

        $records = DB::table('items')
            ->where('items.company_id', $this->company_id)
            ->join('units', 'items.unit_id', '=', 'units.id')
            ->where(function ($query) use ($search_arr) {
                $query->where('units.name', 'like', '%' . $search_arr . '%');
                $query->orwhere('items.name', 'like', '%' . $search_arr . '%');
                $query->orwhere('items.description', 'like', '%' . $search_arr . '%');
            })
            ->select('items.*', 'units.name as unit_name')->orderBy('items.id', 'DESC');
        $datas = $records->paginate(30);

        foreach ($datas as $record) {
            $record->image_icon = ($record->image_icon) ? Storage::url($record->image_icon) : null;
        }

        return response()->json($datas);
    }

    public function itemAutocomplete($search = null)
    {
        $items = Item::select('items.id', 'items.name', 'items.sale_price', 'items.description', 'items.inter_state', 'items.intra_state', 'items.hsn_code', 'units.name as unit_name','item_discount','item_discount_flag','technical_specification')
            ->join('units', 'items.unit_id', '=', 'units.id')
            ->where([['items.name', 'LIKE', '%' . $search . '%'], ['items.status', '=', 0], ['items.company_id', '=', $this->company_id]])
            ->get();
        return $this->sendResponse($items, 'Item retrieved successfully');
    }

    public function itemAutocompleteSingle($id)
    {
        $items = Item::select('items.id', 'items.name', 'items.sale_price', 'items.description', 'items.inter_state', 'items.intra_state', 'items.hsn_code', 'units.name as unit_name','item_discount','item_discount_flag','technical_specification')
            ->join('units', 'items.unit_id', '=', 'units.id')
            ->where([['items.id', '=', $id], ['items.status', '=', 0], ['items.company_id', '=', $this->company_id]])
            ->get();
        return $this->sendResponse($items, 'Item retrieved successfully');
    }

    public function itemStore(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
            'unit_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }

        $input['user_id'] = $this->logged_user->id;
        $input['sale_price'] = 0 + $input['sale_price'];
        $input['cost_price'] = 0 + $input['cost_price'];
        $input['company_id'] = $this->company_id;
        $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
        if (Item::where('name', '=', $input['name'])->where('company_id', $input['company_id'])->where(function ($query) use ($id) {
            if ($id != 0) {
                $query->Where(function ($query) use ($id) {
                    $query->where('id', '!=', $id);
                });
            }
        })->first()) {
            return $this->sendError('Item exists', ['Item exists'], 200);
        }
        if ($id == 0) {
            $activityLogMsg = 'Item created by ' . $this->logged_user->name;
            Item::create($input);
        } else {
            Item::find($id)->update($input);
            $activityLogMsg = 'Item updated by ' . $this->logged_user->name;
        }

        // Add activity logs
        $input['id'] = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
        LogActivity::addToLog($activityLogMsg, $input);

        return $this->sendResponse([], 'Item Saved');
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

        $item = Item::where('id', $input['id'])->delete();

        return $this->sendResponse([], 'Item Deleted!');
    }

    public function editStatus(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }

        if (!Item::where('id', $input['id'])->first()) {
            return $this->sendError('Item exists!', ["Item exists!"], 400);
        }
        $item = Item::where('id', $input['id'])->update(["status" => $input['status']]);

        return $this->sendResponse([], 'Item status updated!');
    }

    public function show($id)
    {
        /*$input = $request->all();

        $validator = Validator::make($input, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }*/

        $item = Item::find($id)->toArray();

        if (is_null($item)) {
            return $this->sendError('Item not found!', ["Item not found!"], 400);
        }

        return $this->sendResponse($item, 'Item retrieved successfully.');


    }

    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
            'unit_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }

        $input['user_id'] = $this->logged_user->id;
        $input['sale_price'] = 0 + $input['sale_price'];
        $input['cost_price'] = 0 + $input['cost_price'];
        $input['company_id'] = $this->company_id;
        $id = $input['id'];
        if (Item::where('name', '=', $input['name'])->where('company_id', $input['company_id'])->where(function ($query) use ($id) {
            if ($id != 0) {
                $query->Where(function ($query) use ($id) {
                    $query->where('id', '!=', $id);
                });
            }
        })->first()) {
            return $this->sendError('Item exists!', ['Item exists!'], 200);
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
           /* $path = $request->file('image_icon')->store('public/uploads/items');
            $input['image_icon'] = $path;*/
            $path = Storage::disk('s3')->put('public/'.$input['company_id'].'/items/', $request->image_icon,'public');
            $input['image_icon'] = 'public/'.$input['company_id'].'/items/'.basename(Storage::disk('s3')->url($path));
        }
        if ($id == 0) {
            Item::create($input);
        } else {
            Item::find($id)->update($input);
        }
        return $this->sendResponse([], 'Item Saved');
    }
}
