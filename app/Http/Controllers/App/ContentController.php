<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\ContentFile;
use App\Models\ContentMessage;
use App\Models\EstimateTimeline;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Helpers\LogActivity;

class ContentController extends Controller
{
    protected $logged_user = null;
    protected $company_id = 0;
    protected $segment = null;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->logged_user = Auth::user();
            $this->company_id = ($this->logged_user->company_id) ? $this->logged_user->company_id : $this->logged_user->id;
            $this->segment = $request->segment(1);
            return $next($request);
        });
    }

    public function messagesIndex(Request $request){
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
            $totalRecords = ContentMessage::select('id')->where(function ($query) use ($name, $status) {
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
            })->where('company_id',$this->company_id)->count();
            if($search_arr != null) {
                $totalRecordswithFilter = ContentMessage::select('id')->where('name', 'like', '%' . $search_arr . '%')->where(function ($query) use ($name, $status) {
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
                })->where('company_id',$this->company_id)->count();
            } else {
                $totalRecordswithFilter = 0;
            }


            // DB::enableQueryLog();
            $records = DB::table('content_messages')
                ->where('company_id',$this->company_id)
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
                })
                ->where(function ($query) use ($search_arr) {
                    if($search_arr != null) {
                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('name', 'like', '%' . $search_arr . '%');
                        });
                    }
                })
                ->select('*')
                ->skip($start)
                ->take($rowperpage)
                ->orderBy($columnName, $columnSortOrder)
                ->get();

            // dd(DB::getQueryLog());

            $data = array();
            $i = 0;
            foreach ($records as $record) {
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
        return view('app.content.messages', compact('segment'));
    }

    public function messagesStore(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $validator = Validator::make($input, [
                'name' => 'required',
                'description' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);
            }

            $input['user_id'] = $this->logged_user->id;
            $input['company_id'] = ($this->company_id);
            $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];

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
                 return response()->json(['success' => 'Name exists!'], 409);
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
            $input['id'] = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
            LogActivity::addToLog($activityLogMsg, $input);

            LogActivity::addToMessageActivityLog($paramArr);

            return response()->json(['success' => 'Customer Saved!'], 201);
        }
    }

    public function messagesTimeline($id)
    {
        $id = Crypt::decrypt($id);
//        $validator = Validator::make($id, [
//            'id' => 'required'
//        ]);
//        if ($validator->fails()) {
//            return response()->json(['errors' => $validator->errors()->all()], 400);
//        }

        $content_messages = ContentMessage::select("*")->where('id', '=', $id)->first();

       /* $leads = User::select(["name", "id", "email", "mobile_no"])
            ->where('status', 'Approved')
//            ->where('company_id', $this->company_id)
            ->where(function ($query) {
                $query->orwhere('company_id', $this->company_id);
                $query->orwhere('id', $this->company_id);
            })
            ->get();*/
        return view('content.messages-timeline', compact('content_messages'));
    }

    public function messagesShow(Request $request)
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

            $messages = ContentMessage::find($id)->toArray();

            if (is_null($messages)) {
                return response()->json(['success' => 'Content message not found!'], 422);
            }
            $messages['id'] = Crypt::encrypt($messages['id']);
            return response()->json([
                "success" => true,
                "message" => "Content message retrieved successfully.",
                "data" => $messages
            ], 201);
        }
    }

    public function messagesTimelineActivity(Request $request)
    {

        $input = $request->all();

        $id = Crypt::decrypt($input['id']);
//        $validator = Validator::make($id, [
//            'id' => 'required'
//        ]);
//        if ($validator->fails()) {
//            return response()->json(['errors' => $validator->errors()->all()], 400);
//        }

        $timelineAcitvityies = DB::table("content_message_timeline_views as c")->select('c.*','c.user_name as created_by_name', DB::raw("DATE_FORMAT(c.created_at, '%d %b, %Y %H:%i %p') as display_created_at"))

            ->where('c.message_id', '=', $id)
            ->where('c.company_id', $this->company_id)
            ->orderBy('c.id', 'desc')
            ->get();
        foreach ($timelineAcitvityies as $key => $val) {
            $timelineAcitvityies[$key]->message_id = Crypt::encrypt($timelineAcitvityies[$key]->message_id);
        }
        return response()->json($timelineAcitvityies);

//print_r($duplicateLeads);
//        echo $duplicateLeads[0]->cnt;
//die;

    }

    public function messagesDestroy(Request $request)
    {
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
            $country = ContentMessage::whereIn('id', $id)->delete();

//            LogActivity::addToLog('Customer deleted by ' . $this->logged_user->name, $id);
            return response()->json(['success' => 'Content Message Deleted!'], 201);
    }



    public function filesIndex(Request $request){
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
            $totalRecords = ContentFile::select('id')->where(function ($query) use ($name, $status) {
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
            })->where('company_id',$this->company_id)->count();
            if($search_arr != null) {
                $totalRecordswithFilter = ContentFile::select('id')->where('name', 'like', '%' . $search_arr . '%')->where(function ($query) use ($name, $status) {
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
                })->where('company_id',$this->company_id)->count();
            } else {
                $totalRecordswithFilter = 0;
            }


//            DB::enableQueryLog();
            $records = DB::table('content_files')
                ->where('company_id',$this->company_id)
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
                })
                ->where(function ($query) use ($search_arr) {
                    if($search_arr != null) {
                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('name', 'like', '%' . $search_arr . '%');
                        });
                    }
                })
                ->select('*')
                ->skip($start)
                ->take($rowperpage)
                ->orderBy($columnName, $columnSortOrder)
                ->get();

//            dd(DB::getQueryLog());

            $data = array();
            $i = 0;
            foreach ($records as $record) {
                $id = Crypt::encrypt($record->id);
                $name = $record->name;
                $ext = explode(".",$record->path);
                $pdfname = 'img';
                if($ext[1]=='pdf' || $ext[1]=='Pdf' || $ext[1]=='PDF')
                    $pdfname = 'pdf';
                if($ext[1]=='doc' || $ext[1]=='docx')
                    $pdfname = 'doc';
                if($ext[1]=='csv' || $ext[1]=='xlsx' || $ext[1]=='xls')
                    $pdfname = 'xlsx';
                $status = $record->status;
                $i++;
                $data[] = array(
                    "id" => $i,
                    "pdfname" => $pdfname,
                    "name" => $name,
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

        return view('content.messages');
    }

    public function filesStore(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $validator = Validator::make($input, [
                'name' => 'required',
                'image_icon' => 'mimes:pdf,Pdf,PDF,png,Png,PNG,jpeg,Jpeg,JPEG,jpg,Jpg,JPG,docx,doc,xlsx,csv,xls,Docx,Doc,Xlsx,Csv,Xls,DOCX,DOC,XLSX,CSV,XLS|max:20480', //required|
//                'description' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);
            }

            $input['user_id'] = $this->logged_user->id;
//            $input['company_id'] = ($this->company_id);
            $input['company_id'] = $this->company_id;
            $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];

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
                return response()->json(['success' => 'Name exists!'], 409);
            }


            if ($request->hasFile('image_icon')) {
                if ($id > 0) {
                    $itemData = ContentFile::find($id);
                    /*if (Storage::exists($itemData->path) && $itemData->path != 'public/content/files/quickest-broucher.pdf') {
                        Storage::delete($itemData->path);
                    }*/
                    if (isset($itemData->path) && $itemData->path != 'template/quickest-broucher.pdf') {
                        Storage::disk('s3')->delete($itemData->path);
                    }
                }
                $path = Storage::disk('s3')->put('public/'.$input['company_id'].'/contents/', $request->image_icon,'public');
                $input['path'] = 'public/'.$input['company_id'].'/contents/'.basename(Storage::disk('s3')->url($path));


//                $path = $request->file('image_icon')->store('public/content/files');

//                $input['path'] = $path;
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
            $input['id'] = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
            LogActivity::addToLog($activityLogMsg, $input);

            LogActivity::addToFileActivityLog($paramArr);
            return response()->json(['success' => 'Customer Saved!'], 201);
        }
    }

    public function filesTimeline($id)
    {
        $id = Crypt::decrypt($id);
//        $validator = Validator::make($id, [
//            'id' => 'required'
//        ]);
//        if ($validator->fails()) {
//            return response()->json(['errors' => $validator->errors()->all()], 400);
//        }

        $content_files = ContentFile::select("*")->where('id', '=', $id)->first();
        $ext = explode(".",$content_files->path);
        $content_files->pdfname = 'img';
        if($ext[1]=='pdf' || $ext[1]=='Pdf' || $ext[1]=='PDF')
            $content_files->pdfname = 'pdf';
        if($ext[1]=='doc' || $ext[1]=='docx')
            $content_files->pdfname = 'doc';
        if($ext[1]=='csv' || $ext[1]=='xlsx'|| $ext[1]=='xls')
            $content_files->pdfname = 'xlsx';

//        dd($content_files);
        /* $leads = User::select(["name", "id", "email", "mobile_no"])
             ->where('status', 'Approved')
 //            ->where('company_id', $this->company_id)
             ->where(function ($query) {
                 $query->orwhere('company_id', $this->company_id);
                 $query->orwhere('id', $this->company_id);
             })
             ->get();*/
        return view('content.files-timeline', compact('content_files'));
    }

    public function filesShow(Request $request)
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

            $messages = ContentFile::find($id)->toArray();

            if (is_null($messages)) {
                return response()->json(['success' => 'Content file not found!'], 422);
            }
            $messages['id'] = Crypt::encrypt($messages['id']);
            return response()->json([
                "success" => true,
                "message" => "Content file retrieved successfully.",
                "data" => $messages
            ], 201);
        }
    }

    public function filesTimelineActivity(Request $request)
    {

        $input = $request->all();

        $id = Crypt::decrypt($input['id']);
//        $validator = Validator::make($id, [
//            'id' => 'required'
//        ]);
//        if ($validator->fails()) {
//            return response()->json(['errors' => $validator->errors()->all()], 400);
//        }

        $timelineAcitvityies = DB::table("content_file_timeline_views as c")->select('c.*','c.user_name as created_by_name', DB::raw("DATE_FORMAT(c.created_at, '%d %b, %Y %H:%i %p') as display_created_at"))

            ->where('c.file_id', '=', $id)
            ->where('c.company_id', $this->company_id)
            ->orderBy('c.id', 'desc')
            ->get();
        foreach ($timelineAcitvityies as $key => $val) {
            $timelineAcitvityies[$key]->file_id = Crypt::encrypt($timelineAcitvityies[$key]->file_id);
        }
        return response()->json($timelineAcitvityies);

//print_r($duplicateLeads);
//        echo $duplicateLeads[0]->cnt;
//die;

    }

    public function filesDestroy(Request $request)
    {
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

        $itemData = ContentFile::find($id[0]);
//        if (isset($itemData->path) && $itemData->path != 'template/quickest-broucher.pdf') {
        if (isset($itemData->path) && $itemData->path != 'public/content/files/quickest-broucher.pdf') {
//            Storage::disk('s3')->delete($itemData->path);
            Storage::delete($itemData->path);
        }

        $country = ContentFile::whereIn('id', $id)->delete();

//            LogActivity::addToLog('Customer deleted by ' . $this->logged_user->name, $id);
        return response()->json(['success' => 'Content File Deleted!'], 201);
    }


}
