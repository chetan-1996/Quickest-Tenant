<?php

namespace App\Http\Controllers\API\V6;

use App\Http\Controllers\API\V6\BaseController as BaseController;
use App\Models\ContentFile;
use App\Models\ContentMessage;
//use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use LogActivity;

class ContentController extends BaseController
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->logged_user = Auth::user();
            $this->company_id = ($this->logged_user->company_id) ? $this->logged_user->company_id : $this->logged_user->id;
            return $next($request);
        });
    }

    public function messagesIndex()
    {
        $records = ContentMessage::select("*")->where('company_id', $this->company_id)->get();
        $data = array();
        foreach ($records as $record) {
            $id = $record->id;
            $name = $record->name;
            $description = $record->description;
            $status = $record->status;
            $user_id = $record->user_id;
            $company_id = $record->company_id;
            $created_at = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)
                ->format('Y-m-d H:i:s');
            $updated_at = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->updated_at)
                ->format('Y-m-d H:i:s');

            $data[] = array(
                "id" => $id,
                "name" => $name,
                "description" => $description,
                "user_id" => $status,
                "created_at" => $user_id,
                "company_id" => $company_id,
                "created_at" => $created_at,
                "updated_at" => $updated_at,
            );

        }
        if (is_null($data)) {
            return $this->sendError('Content massages not found', ['Lead not found'], 422);
        }
        return $this->sendResponse($data, 'Content massages retrieved successfully');
    }

    public function filesIndex()
    {
        $records = ContentFile::select("*")->where('company_id', $this->company_id)->get();
        $data = array();
        foreach ($records as $record) {
            $id = $record->id;
            $name = $record->name;
//            $path = Storage::url($record->path);
            $path = Storage::disk('s3')->url($record->path,\Carbon\Carbon::now()->addMinutes(20));
            $status = $record->status;
            $user_id = $record->user_id;
            $company_id = $record->company_id;
            $created_at = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)
                ->format('Y-m-d H:i:s');
            $updated_at = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->updated_at)
                ->format('Y-m-d H:i:s');


            $data[] = array(
                "id" => $id,
                "name" => $name,
                "path" => $path,
                "user_id" => $status,
                "created_at" => $user_id,
                "company_id" => $company_id,
                "created_at" => $created_at,
                "updated_at" => $updated_at,
            );

        }
        if (is_null($data)) {
            return $this->sendError('Content files not found', ['Lead not found'], 422);
        }
        return $this->sendResponse($data, 'Content files retrieved successfully');
    }

    public function messagesSingleIndex($id)
    {
        $records = ContentMessage::select("*")->where('company_id', $this->company_id)->where('id', $id)->get();
        $data = array();
        foreach ($records as $record) {
            $id = $record->id;
            $name = $record->name;
            $description = $record->description;
            $status = $record->status;
            $user_id = $record->user_id;
            $company_id = $record->company_id;
            $created_at = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)
                ->format('Y-m-d H:i:s');
            $updated_at = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->updated_at)
                ->format('Y-m-d H:i:s');

            $data[] = array(
                "id" => $id,
                "name" => $name,
                "description" => $description,
                "user_id" => $status,
                "created_at" => $user_id,
                "company_id" => $company_id,
                "created_at" => $created_at,
                "updated_at" => $updated_at,
            );

        }
        if (is_null($data)) {
            return $this->sendError('Content massages not found', ['Lead not found'], 422);
        }
        return $this->sendResponse($data, 'Content massages retrieved successfully');
    }

    public function filesSingleIndex($id)
    {
        $records = ContentFile::select("*")->where('company_id', $this->company_id)->where('id', $id)->get();
        $data = array();
        foreach ($records as $record) {
            $id = $record->id;
            $name = $record->name;
//            $path = Storage::url($record->path);
            $path = Storage::disk('s3')->temporaryUrl($record->path,\Carbon\Carbon::now()->addMinutes(20));
            $status = $record->status;
            $user_id = $record->user_id;
            $company_id = $record->company_id;
            $created_at = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->created_at)
                ->format('Y-m-d H:i:s');
            $updated_at = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $record->updated_at)
                ->format('Y-m-d H:i:s');


            $data[] = array(
                "id" => $id,
                "name" => $name,
                "path" => $path,
                "user_id" => $status,
                "created_at" => $user_id,
                "company_id" => $company_id,
                "created_at" => $created_at,
                "updated_at" => $updated_at,
            );

        }
        if (is_null($data)) {
            return $this->sendError('Content files not found', ['Lead not found'], 422);
        }
        return $this->sendResponse($data, 'Content files retrieved successfully');
    }

    public function messagesStore(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
            'description' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }

        $input['user_id'] = $this->logged_user->id;
        $input['company_id'] = $this->company_id;
        $id = $input['id'];

        if (ContentMessage::where('name', '=', $input['name'])
            ->where('company_id', $input['company_id'])
            ->where(function ($query) use ($id) {
                if ($id != 0) {
                    $query->Where(function ($query) use ($id) {
                        $query->where('id', '!=', $id);
                    });
                }
            })
            ->first()) {

            return $this->sendError('Name exists', ['error' => 'Name exists'], 200);
        }

        if ($id == 0) {

            $activityLogMsg = 'Content messages created by ' . $this->logged_user->name;
            $customer = ContentMessage::create($input);
            $ids = $customer->id;
            $paramArr['activity_type'] = 1;
            $paramArr['activity_name'] = "Message Created";
        } else {
            $paramArr['activity_type'] = 2;
            $paramArr['activity_name'] = "Message Updated";
            $customer = ContentMessage::find($id)->update($input);
            $activityLogMsg = 'Content messages updated by ' . $this->logged_user->name;
            $ids = $id;
        }

        $paramArr['message_id'] = $ids;
        $paramArr['user_id'] = $this->logged_user->id;
        $paramArr['company_id'] = $this->company_id;
        $paramArr['created_by'] = $this->logged_user->id;
        $paramArr['updated_by'] = $this->logged_user->id;

        // Add activity logs
        LogActivity::addToLog($activityLogMsg, $input);

        LogActivity::addToMessageActivityLog($paramArr);
        return $this->sendResponse([], 'Content Message Saved');
    }

    public function messagesDestroy(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }

        $id = $input['id'];
        /* foreach (explode(",", $request->id) as $value) {
             $id[] = Crypt::decrypt($value);
         }*/

        $country = ContentMessage::where('id', $id)->delete();

        return $this->sendResponse([], 'Content Message Deleted!');
    }

    public function filesDestroy(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }

        $id = $input['id'];
        $itemData = ContentFile::find($id);
        if (Storage::exists($itemData->path)) {
            Storage::delete($itemData->path);
        }
        /* foreach (explode(",", $request->id) as $value) {
             $id[] = Crypt::decrypt($value);
         }*/

        $country = ContentFile::where('id', $id)->delete();

        return $this->sendResponse([], 'Content File Deleted!');
    }

    public function filesStore(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
            'image_icon' => 'mimes:pdf,Pdf,PDF,png,Png,PNG,jpeg,Jpeg,JPEG,jpg,Jpg,JPG,docx,doc,xlsx,csv,xls,Docx,Doc,Xlsx,Csv,Xls,DOCX,DOC,XLSX,CSV,XLS|max:20480',
//                'description' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }

        $input['user_id'] = $this->logged_user->id;
//        $input['company_id'] = ($this->company_id);
        $input['company_id'] = $this->company_id;
        $id = $input['id'];

        if (ContentFile::where('name', '=', $input['name'])
            ->where('company_id', $input['company_id'])
            ->where(function ($query) use ($id) {
                if ($id != 0) {
                    $query->Where(function ($query) use ($id) {
                        $query->where('id', '!=', $id);
                    });
                }
            })
            ->first()) {
            return $this->sendError('Name exists', ['error' => 'Name exists'], 200);
        }


        if ($request->file('image_icon') && $request->hasFile('image_icon')) {
            if ($id > 0) {
                $itemData = ContentFile::find($id);
                /*if (Storage::exists($itemData->path)) {
                    Storage::delete($itemData->path);
                }*/
                if (isset($itemData->path) && $itemData->path != 'template/quickest-broucher.pdf') {
                    Storage::disk('s3')->delete($itemData->path);
                }
            }
            /*$path = $request->file('image_icon')->store('public/content/files');

            $input['path'] = $path;*/

            $path = Storage::disk('s3')->put('public/'.$input['company_id'].'/contents/', $request->image_icon,'public');
            Storage::disk('s3')->setVisibility($path, 'public');
            $input['path'] = 'public/'.$input['company_id'].'/contents/'.basename(Storage::disk('s3')->url($path));
        }


        if ($id == 0) {

            $activityLogMsg = 'Content files created by ' . $this->logged_user->name;
            $customer = ContentFile::create($input);
            $ids = $customer->id;
            $paramArr['activity_type'] = 1;
            $paramArr['activity_name'] = "Files Created";
        } else {
            $paramArr['activity_type'] = 2;
            $paramArr['activity_name'] = "Files Updated";
            $customer = ContentFile::find($id)->update($input);
            $activityLogMsg = 'Content files updated by ' . $this->logged_user->name;
            $ids = $id;
        }

        $paramArr['file_id'] = $ids;
        $paramArr['user_id'] = $this->logged_user->id;
        $paramArr['company_id'] = $this->company_id;
        $paramArr['created_by'] = $this->logged_user->id;
        $paramArr['updated_by'] = $this->logged_user->id;

        // Add activity logs
//        $input['id'] = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
        LogActivity::addToLog($activityLogMsg, $input);

        LogActivity::addToFileActivityLog($paramArr);
        return $this->sendResponse([], 'Content File Saved');

    }

    public function messagesShare(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'lead_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }


        $paramArr['estimate_id'] = $input['estimate_id'];
        $paramArr['assigned_to'] = $input['assigned_to'];
        $paramArr['customer_id'] = $input['lead_id'];
        $paramArr['activity_type'] = 12;
        $paramArr['activity_name'] = $input['msg_title'];
        $paramArr['activity_notes'] = $input['msg_content'];
        $paramArr['follow_up_datetime'] = $input['follow_up_datetime'];
        $paramArr['entry_type'] = 'content';
        $paramArr['company_id'] = $this->company_id;
        $paramArr['user_id'] = $this->logged_user->id;
        $paramArr['created_by'] = $this->logged_user->id;
        $paramArr['updated_by'] = $this->logged_user->id;
        $paramArr['content_id'] = $input['msg_id'];

        LogActivity::addToActivityLog($paramArr);
        return $this->sendResponse([], 'Content Message Shared');
    }
    public function filesShare(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'lead_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }

        $paramArr['estimate_id'] = $input['estimate_id'];
        $paramArr['assigned_to'] = $input['assigned_to'];
        $paramArr['customer_id'] = $input['lead_id'];
        $paramArr['activity_type'] = 13;
        $paramArr['activity_name'] = $input['file_title'];
        $paramArr['follow_up_datetime'] = $input['follow_up_datetime'];
        $paramArr['entry_type'] = 'content';
        $paramArr['company_id'] = $this->company_id;
        $paramArr['user_id'] = $this->logged_user->id;
        $paramArr['created_by'] = $this->logged_user->id;
        $paramArr['updated_by'] = $this->logged_user->id;
        $paramArr['content_id'] = $input['file_id'];

        LogActivity::addToActivityLog($paramArr);
        return $this->sendResponse([], 'Content File Shared');
    }
}
