<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use LogActivity;
use Auth;
use Image;

class TestimonialController extends Controller
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
        $company_id = ($user->company_id)? $user->company_id : $user->id;
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
            $totalRecords = Testimonial::select('count(id) as allcount')->where(function ($query) use ($name, $status) {
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
            })->where('company_id',$company_id)->count();
            $totalRecordswithFilter = Testimonial::select('count(id) as allcount')->where('name', 'like', '%' . $search_arr . '%')->where(function ($query) use ($name, $status) {
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
            })->where('company_id',$company_id)->count();


//            DB::enableQueryLog();
            $records = DB::table('testimonials')
                ->where('company_id',$company_id)
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
                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('name', 'like', '%' . $search_arr . '%');
                    });
                })
//                ->orWhere(function ($query) use ($search_arr) {
//                    if ($search_arr) {
//                        $query->orWhere(function ($query) use ($search_arr) {
//                            $query->where('description', 'like', '%' . $search_arr . '%');
//                        });
//                    }
//                })
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
                $is_default = $record->is_default;
//                $rating = $record->rating;
//                $description = $record->description;
                $status = $record->status;
                $i++;
                $data[] = array(
                    "id" => $i,
                    "name" => $name,
                    "is_default" => $is_default,
//                    "rating" => $rating,
                    /*"image_one" => Storage::url($record->image_one),
                    "image_two" => Storage::url($record->image_two),
                    "image_three" => Storage::url($record->image_three),*/
                    "image_one" => ($record->image_one)?Storage::disk('s3')->temporaryUrl(trim($record->image_one),Carbon::now()->addMinutes(20)):null,
                    "image_two" => ($record->image_two)?Storage::disk('s3')->temporaryUrl(trim($record->image_two),Carbon::now()->addMinutes(20)):null,
                    "image_three" =>($record->image_three)?Storage::disk('s3')->temporaryUrl(trim($record->image_three),Carbon::now()->addMinutes(20)):null,
//                    "description" => $description,
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
        return view('app.testimonial', compact('segment'));
    }

//    public function store(Request $request)
//    {
//        if ($request->ajax()) {
//            $input = $request->all();
//            $validator = Validator::make($input, [
//                'name' => 'required',
//            ]);
//
//            $user = Auth::user();
//            $input['user_id'] = $user->id;
//            $input['company_id'] = ($user->company_id)? $user->company_id : $user->id;
//            $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
//
//
//            if($id==0){
//                $validator = Validator::make($input, [
//                    'name' => 'required',
//                    'image_one' => 'required|image|mimes:jpeg,png,jpg|max:1024',
//                    'image_two' => 'required|image|mimes:jpeg,png,jpg|max:1024',
//                    'image_three' => 'required|image|mimes:jpeg,png,jpg|max:1024',
//                ]);
//            }
//            if ($validator->fails()) {
//                return response()->json(['errors' => $validator->errors()->all()], 400);;
//            }
//
//            if (Testimonial::where('name', '=', $input['name'])->where('company_id', $input['company_id'])->where(function ($query) use ($id) {
//                if ($id != 0) {
//                    $query->Where(function ($query) use ($id) {
//                        $query->where('id', '!=', $id);
//                    });
//                }
//            })->first()) {
//                return response()->json(['success' => 'Testimonial exists!'], 409);
//            }
//            if(isset($input['is_default']) && $input['is_default']==1){
//                Testimonial::where([['id', '>', 0],['company_id','=',$input['company_id']]])->update(['is_default'=>0]); //,['user_id','=',$user->id]
//            }
//            if ($id == 0) {
//                $activityLogMsg = 'Testimonial created by ' . $user->name;
//                if ($request->h_image_one) {
//                    $folderPath = public_path('storage/uploads/thumbnail/');
//                    $image_parts = explode(";base64,", $request->h_image_one);
//                    $image_type_aux = explode("image/", $image_parts[0]);
//                    $image_type = $image_type_aux[1];
//                    $image_base64 = base64_decode($image_parts[1]);
//                    $ext = explode(";", explode("/", $request->h_image_one)[1])[0];
//                    $imageName = uniqid() . '-1' . '.' . $ext;
//                    $imageFullPath = $folderPath . $imageName;
//                    file_put_contents($imageFullPath, $image_base64);
//                    $input['image_one'] = 'public/uploads/thumbnail/' . $imageName;
//                }
//
//                if ($request->h_image_two) {
//                    $folderPath = public_path('storage/uploads/thumbnail/');
//                    $image_parts = explode(";base64,", $request->h_image_two);
//                    $image_type_aux = explode("image/", $image_parts[0]);
//                    $image_type = $image_type_aux[1];
//                    $image_base64 = base64_decode($image_parts[1]);
//                    $ext = explode(";", explode("/", $request->h_image_two)[1])[0];
//                    $imageName = uniqid() . '-2' . '.' . $ext;
//                    $imageFullPath = $folderPath . $imageName;
//                    file_put_contents($imageFullPath, $image_base64);
//                    $input['image_two'] = 'public/uploads/thumbnail/' . $imageName;
//                }
//
//                if ($request->h_image_three) {
//                    $folderPath = public_path('storage/uploads/thumbnail/');
//                    $image_parts = explode(";base64,", $request->h_image_three);
//                    $image_type_aux = explode("image/", $image_parts[0]);
//                    $image_type = $image_type_aux[1];
//                    $image_base64 = base64_decode($image_parts[1]);
//                    $ext = explode(";", explode("/", $request->h_image_three)[1])[0];
//                    $imageName = uniqid() . '-3' . '.' . $ext;
//                    $imageFullPath = $folderPath . $imageName;
//                    file_put_contents($imageFullPath, $image_base64);
//                    $input['image_three'] = 'public/uploads/thumbnail/' . $imageName;
//
//                }
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//                /*if ($request->file('image_one')) {
//
//                    $image = $request->file('image_one');
//                    $input['file'] = time().'-1.'.$image->getClientOriginalExtension();
//                    $destinationPath = public_path('/storage/uploads/thumbnail');
//                    $imgFile = Image::make($image->getRealPath());
//                    $imgFile->resize(null, 442, function($constraint) { //412, 391        460, 442
//                        $constraint->aspectRatio();
//                    })->save($destinationPath.'/'.$input['file']);
//
////                    $path = $request->file('image_one')->store('public/uploads');
//                    $input['image_one'] = 'public/uploads/thumbnail/'.$input['file'];
//                }
//                if ($request->file('image_two')) {
//                    $image = $request->file('image_two');
//                    $input['file'] = time().'-2.'.$image->getClientOriginalExtension();
//                    $destinationPath = public_path('/storage/uploads/thumbnail');
//                    $imgFile = Image::make($image->getRealPath());
//                    $imgFile->resize(null, 391, function($constraint) { //412, 391        460, 442
//                        $constraint->aspectRatio();
//                    })->save($destinationPath.'/'.$input['file']);
//
//                    $input['image_two'] = 'public/uploads/thumbnail/'.$input['file'];
////                    $path = $request->file('image_two')->store('public/uploads');
////                    $input['image_two'] = $path;
//                }
//                if ($request->file('image_three')) {
//                    $image = $request->file('image_three');
//                    $input['file'] = time().'-3.'.$image->getClientOriginalExtension();
//                    $destinationPath = public_path('/storage/uploads/thumbnail');
//                    $imgFile = Image::make($image->getRealPath());
//                    $imgFile->resize(null, 391, function($constraint) { //412, 391        460, 442
//                        $constraint->aspectRatio();
//                    })->save($destinationPath.'/'.$input['file']);
//
//                    $input['image_three'] = 'public/uploads/thumbnail/'.$input['file'];
////                    $path = $request->file('image_three')->store('public/uploads');
////                    $input['image_three'] = $path;
//                }*/
//                $testimonial = Testimonial::create($input);
//            } else {
//                $testimonialData = Testimonial::find($id);
//                if ($request->hasFile('image_one')) {
//                    $request->validate([
//                        'image_one' => 'required|image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
//                    ]);
//
//                    if(Storage::exists($testimonialData->image_one))
//                    {
//                        Storage::delete($testimonialData->image_one);
//                    }
//
//                    $folderPath = public_path('storage/uploads/thumbnail/');
//                    $image_parts = explode(";base64,", $request->h_image_one);
//                    $image_type_aux = explode("image/", $image_parts[0]);
//                    $image_type = $image_type_aux[1];
//                    $image_base64 = base64_decode($image_parts[1]);
//                    $ext = explode(";", explode("/", $request->h_image_one)[1])[0];
//                    $imageName = uniqid() . '-1' . '.' . $ext;
//                    $imageFullPath = $folderPath . $imageName;
//                    file_put_contents($imageFullPath, $image_base64);
//                    $input['image_one'] = 'public/uploads/thumbnail/' . $imageName;
//                }
//
//                if ($request->hasFile('image_two')) {
//                    $request->validate([
//                        'image_two' => 'required|image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
//                    ]);
//                    if(Storage::exists($testimonialData->image_two))
//                    {
//                        Storage::delete($testimonialData->image_two);
//                    }
//
//                    $folderPath = public_path('storage/uploads/thumbnail/');
//                    $image_parts = explode(";base64,", $request->h_image_two);
//                    $image_type_aux = explode("image/", $image_parts[0]);
//                    $image_type = $image_type_aux[1];
//                    $image_base64 = base64_decode($image_parts[1]);
//                    $ext = explode(";", explode("/", $request->h_image_two)[1])[0];
//                    $imageName = uniqid() . '-2' . '.' . $ext;
//                    $imageFullPath = $folderPath . $imageName;
//                    file_put_contents($imageFullPath, $image_base64);
//                    $input['image_two'] = 'public/uploads/thumbnail/' . $imageName;
//
//                }
//
//                if ($request->hasFile('image_three')) {
//                    $request->validate([
//                        'image_three' => 'required|image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
//                    ]);
//                    if (Storage::exists($testimonialData->image_three)) {
//                        Storage::delete($testimonialData->image_three);
//                    }
//
//                    $folderPath = public_path('storage/uploads/thumbnail/');
//                    $image_parts = explode(";base64,", $request->h_image_three);
//                    $image_type_aux = explode("image/", $image_parts[0]);
//                    $image_type = $image_type_aux[1];
//                    $image_base64 = base64_decode($image_parts[1]);
//                    $ext = explode(";", explode("/", $request->h_image_three)[1])[0];
//                    $imageName = uniqid() . '-3' . '.' . $ext;
//                    $imageFullPath = $folderPath . $imageName;
//                    file_put_contents($imageFullPath, $image_base64);
//                    $input['image_three'] = 'public/uploads/thumbnail/' . $imageName;
//                }
//
//                /*if($request->hasFile('image_one')){
//                    $request->validate([
//                        'image_one' => 'required|image|mimes:jpeg,png,jpg|max:1024',
//                    ]);
//
//                    if(Storage::exists($testimonialData->image_one))
//                    {
//                        Storage::delete($testimonialData->image_one);
//                    }
//                    $image = $request->file('image_one');
//                    $input['file'] = time().'-1.'.$image->getClientOriginalExtension();
//                    $destinationPath = public_path('/storage/uploads/thumbnail');
//                    $imgFile = Image::make($image->getRealPath());
//                    $imgFile->resize(null, 391, function($constraint) {
//                        $constraint->aspectRatio();
//                    })->save($destinationPath.'/'.$input['file']);
//
//                    $input['image_one'] = 'public/uploads/thumbnail/'.$input['file'];
////                    $path = $request->file('image_one')->store('public/uploads');
////                    $input['image_one'] = $path;
//                }
//                if($request->hasFile('image_two')){
//                    $request->validate([
//                        'image_two' => 'required|image|mimes:jpeg,png,jpg|max:1024',
//                    ]);
//                    if(Storage::exists($testimonialData->image_two))
//                    {
//                        Storage::delete($testimonialData->image_two);
//                    }
//                    $image = $request->file('image_two');
//                    $input['file'] = time().'-2.'.$image->getClientOriginalExtension();
//                    $destinationPath = public_path('/storage/uploads/thumbnail');
//                    $imgFile = Image::make($image->getRealPath());
//                    $imgFile->resize(null, 391, function($constraint) { //412, 391        460, 442
//                        $constraint->aspectRatio();
//                    })->save($destinationPath.'/'.$input['file']);
//
//                    $input['image_two'] = 'public/uploads/thumbnail/'.$input['file'];
////                    $path = $request->file('image_two')->store('public/uploads');
////                    $input['image_two'] = $path;
//                }
//
//                if($request->hasFile('image_three')){
//                    $request->validate([
//                        'image_three' => 'required|image|mimes:jpeg,png,jpg|max:1024',
//                    ]);
//                    if(Storage::exists($testimonialData->image_three))
//                    {
//                        Storage::delete($testimonialData->image_three);
//                    }
//                    $image = $request->file('image_three');
//                    $input['file'] = time().'-3.'.$image->getClientOriginalExtension();
//                    $destinationPath = public_path('/storage/uploads/thumbnail');
//                    $imgFile = Image::make($image->getRealPath());
//                    $imgFile->resize(null, 391, function($constraint) { //412, 391        460, 442
//                        $constraint->aspectRatio();
//                    })->save($destinationPath.'/'.$input['file']);
//
//                    $input['image_three'] = 'public/uploads/thumbnail/'.$input['file'];
////                    $path = $request->file('image_three')->store('public/uploads');
////                    $input['image_three'] = $path;
//                }*/
//
//                $testimonial = Testimonial::find($id)->update($input);
//                $activityLogMsg = 'Testimonial updated by ' . $user->name;
//            }
//
//
//            // Add activity logs
//            $input['id'] = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
//            LogActivity::addToLog($activityLogMsg, $input);
//
//            return response()->json(['success' => 'Testimonial Saved!'], 201);
//        }
//    }

    public function store(Request $request)  //s3 code
    {
        if ($request->ajax()) {
            $input = $request->all();
            $validator = Validator::make($input, [
                'name' => 'required',
            ]);

            $user = Auth::user();
            $input['user_id'] = $user->id;
            $input['company_id'] = ($user->company_id)? $user->company_id : $user->id;
            $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];


            if($id==0){
                $validator = Validator::make($input, [
                    'name' => 'required',
                    'image_one' => 'required|image|mimes:jpeg,png,jpg|max:1024',
                    'image_two' => 'required|image|mimes:jpeg,png,jpg|max:1024',
                    'image_three' => 'required|image|mimes:jpeg,png,jpg|max:1024',
                ]);
            }
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);;
            }

            if (Testimonial::where('name', '=', $input['name'])->where('company_id', $input['company_id'])->where(function ($query) use ($id) {
                if ($id != 0) {
                    $query->Where(function ($query) use ($id) {
                        $query->where('id', '!=', $id);
                    });
                }
            })->first()) {
                return response()->json(['success' => 'Testimonial exists!'], 409);
            }
            if(isset($input['is_default']) && $input['is_default']==1){
                Testimonial::where([['id', '>', 0],['company_id','=',$input['company_id']]])->update(['is_default'=>0]); //,['user_id','=',$user->id]
            }
            if ($id == 0) {
                $activityLogMsg = 'Testimonial created by ' . $user->name;
                if ($request->h_image_one) {
                    $ext = explode(";", explode("/", $request->h_image_one)[1])[0];
                    $imageName = uniqid() . '-1' . '.' . $ext;
                    //S3 bucket
                    $imageData = $request->input('h_image_one');
                    // Decode the base64 image data
                    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

                    // Upload the image to S3
                    Storage::disk('s3')->put('public/'.$input["company_id"].'/testimonials/'.$imageName, $imageData,'public');

                    // Get the S3 URL of the uploaded image
                    $s3Url = Storage::disk('s3')->url('public/'.$input["company_id"].'/testimonials/'.$imageName);
                    $input['image_one'] = 'public/'.$input['company_id'].'/testimonials/'.$imageName;

                }

                if ($request->h_image_two) {
                    $ext = explode(";", explode("/", $request->h_image_two)[1])[0];
                    $imageName = uniqid() . '-2' . '.' . $ext;
                    //S3 bucket
                    $imageData = $request->input('h_image_two');
                    // Decode the base64 image data
                    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

                    // Upload the image to S3
                    Storage::disk('s3')->put('public/'.$input["company_id"].'/testimonials/'.$imageName, $imageData,'public');

                    // Get the S3 URL of the uploaded image
                    $s3Url = Storage::disk('s3')->url('public/'.$input["company_id"].'/testimonials/'.$imageName);
                    $input['image_two'] = 'public/'.$input['company_id'].'/testimonials/'.$imageName;
                }

                if ($request->h_image_three) {
                    $ext = explode(";", explode("/", $request->h_image_three)[1])[0];
                    $imageName = uniqid() . '-3' . '.' . $ext;
                    //S3 bucket
                    $imageData = $request->input('h_image_three');
                    // Decode the base64 image data
                    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

                    // Upload the image to S3
                    Storage::disk('s3')->put('public/'.$input["company_id"].'/testimonials/'.$imageName, $imageData,'public');

                    // Get the S3 URL of the uploaded image
                    $s3Url = Storage::disk('s3')->url('public/'.$input["company_id"].'/testimonials/'.$imageName);
                    $input['image_three'] = 'public/'.$input['company_id'].'/testimonials/'.$imageName;

                }
                $testimonial = Testimonial::create($input);
            } else {
                $testimonialData = Testimonial::find($id);
                if ($request->hasFile('image_one')) {
                    $request->validate([
                        'image_one' => 'required|image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
                    ]);

                    if ($testimonialData->image_one) {
                        Storage::disk('s3')->delete($testimonialData->image_one);
                    }

                    $ext = explode(";", explode("/", $request->h_image_one)[1])[0];
                    $imageName = uniqid() . '-1' . '.' . $ext;
                    //S3 bucket
                    $imageData = $request->input('h_image_one');
                    // Decode the base64 image data
                    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

                    // Upload the image to S3
                    Storage::disk('s3')->put('public/'.$input["company_id"].'/testimonials/'.$imageName, $imageData,'public');

                    // Get the S3 URL of the uploaded image
                    $s3Url = Storage::disk('s3')->url('public/'.$input["company_id"].'/testimonials/'.$imageName);
                    $input['image_one'] = 'public/'.$input['company_id'].'/testimonials/'.$imageName;
                }

                if ($request->hasFile('image_two')) {
                    $request->validate([
                        'image_two' => 'required|image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
                    ]);

                    if ($testimonialData->image_two) {
                        Storage::disk('s3')->delete($testimonialData->image_two);
                    }

                    $ext = explode(";", explode("/", $request->h_image_two)[1])[0];
                    $imageName = uniqid() . '-2' . '.' . $ext;
                    //S3 bucket
                    $imageData = $request->input('h_image_two');
                    // Decode the base64 image data
                    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

                    // Upload the image to S3
                    Storage::disk('s3')->put('public/'.$input["company_id"].'/testimonials/'.$imageName, $imageData,'public');

                    // Get the S3 URL of the uploaded image
                    $s3Url = Storage::disk('s3')->url('public/'.$input["company_id"].'/testimonials/'.$imageName);
                    $input['image_two'] = 'public/'.$input['company_id'].'/testimonials/'.$imageName;

                }

                if ($request->hasFile('image_three')) {
                    $request->validate([
                        'image_three' => 'required|image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
                    ]);

                    if ($testimonialData->image_three) {
                        Storage::disk('s3')->delete($testimonialData->image_three);
                    }

                    $ext = explode(";", explode("/", $request->h_image_three)[1])[0];
                    $imageName = uniqid() . '-3' . '.' . $ext;
                    //S3 bucket
                    $imageData = $request->input('h_image_three');
                    // Decode the base64 image data
                    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

                    // Upload the image to S3
                    Storage::disk('s3')->put('public/'.$input["company_id"].'/testimonials/'.$imageName, $imageData,'public');

                    // Get the S3 URL of the uploaded image
                    $s3Url = Storage::disk('s3')->url('public/'.$input["company_id"].'/testimonials/'.$imageName);
                    $input['image_three'] = 'public/'.$input['company_id'].'/testimonials/'.$imageName;
                }

                $testimonial = Testimonial::find($id)->update($input);
                $activityLogMsg = 'Testimonial updated by ' . $user->name;
            }


            // Add activity logs
            $input['id'] = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
            LogActivity::addToLog($activityLogMsg, $input);

            return response()->json(['success' => 'Testimonial Saved!'], 201);
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

            $testimonial = Testimonial::find($id)->toArray();

            if (is_null($testimonial)) {
                return response()->json(['success' => 'Testimonial not found!'], 422);
            }
            $testimonial['id'] = Crypt::encrypt($testimonial['id']);
            return response()->json([
                "success" => true,
                "message" => "Testimonial retrieved successfully.",
                "data" => $testimonial
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

            $testimonialIds = explode(",", $request->id);
            foreach (explode(",", $request->id) as $value) {
                $ids[] = Crypt::decrypt($value);
            }
            $testimonialsWithTransactions = DB::table('estimates')->join("testimonials", 'estimates.testimonial_id', '=', 'testimonials.id')
                ->whereIn('estimates.testimonial_id', $ids)
                ->pluck('estimates.testimonial_id','testimonials.name')
                ->toArray();

            foreach ($ids as $testimonialId) {
                if (in_array($testimonialId, $testimonialsWithTransactions)) {
                    $key = array_search($testimonialId, $testimonialsWithTransactions);
                    $errors[] = "{$key} has transactions associated with it in the estimates list";
                    continue;
                }
                try {
                    DB::table('testimonials')
                        ->where('id', $testimonialId)
                        ->delete();

                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if($errors){
                return response()->json($errors,400);
            }
            /*$id = [];
            foreach (explode(",", $request->id) as $value) {
                $id[] = Crypt::decrypt($value);

            }
            $testimonial = Testimonial::whereIn('id', $id)->delete();*/

            LogActivity::addToLog('Testimonial deleted by ' . $user->name, $ids);
            return response()->json(['success' => 'Testimonial Deleted!'], 201);
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
            $user = Auth::user();

            $errors = [];

            $testimonialIds = explode(",", $request->id);
            foreach (explode(",", $request->id) as $value) {
                $ids[] = Crypt::decrypt($value);
            }
            $testimonialsWithTransactions = DB::table('estimates')->join("testimonials", 'estimates.testimonial_id', '=', 'testimonials.id')
                ->whereIn('estimates.testimonial_id', $ids)
                ->pluck('estimates.testimonial_id','testimonials.name')
                ->toArray();

            foreach ($ids as $testimonialId) {
                if (in_array($testimonialId, $testimonialsWithTransactions)) {
                    $key = array_search($testimonialId, $testimonialsWithTransactions);
                    $errors[] = "{$key} has transactions associated with it in the estimates list";
                    continue;
                }
                try {
                    Testimonial::where('id', $testimonialId)->update(["status" => $input['status']]);
                    $data['id'] = $testimonialId;
                    $data['status'] = ($input['status'] == 0) ? 'Active' : 'Deactive';
                    LogActivity::addToLog('Testimonial deleted by ' . $user->name, $data);
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
            if (!Testimonial::whereIn('id', $id)->first()) {
                return response()->json(['success' => 'Testimonial exists!'], 422);
            }
            $testimonial = Testimonial::whereIn('id', $id)->update(["status" => $input['status']]);

            $data['id'] = $id;
            $data['status'] = ($input['status'] == 0) ? 'Active' : 'Deactive';
            LogActivity::addToLog('Testimonial status updated by ' . $user->name, $data);*/

            return response()->json(['success' => 'Testimonial status updated!'], 201);
        }
    }

    public function testimonialAutocomplete(Request $request)
    {
        if ($request->ajax()) {
            $search = $request->get('search');
            $user = Auth::user();
            $company_id = ($user->company_id)? $user->company_id : $user->id;
            $testimonials = Testimonial::select('name', 'id', 'client_name_one', 'image_one', 'rating_one', 'description_one', 'client_name_two', 'image_two', 'rating_two', 'description_two', 'client_name_three', 'image_three', 'rating_three', 'description_three')->where('testimonials.company_id',$company_id)->where('testimonials.status',0)->where('name', 'LIKE', '%' . $search . '%')->where('status', 0)->get();
            $response = array();
            foreach($testimonials as $testimonial){
                $response[] = array("value"=>$testimonial->id,
                    "label"=>$testimonial->name,
//                    "image_one"=>Storage::url($testimonial->image_one),
                    "image_one" => ($testimonial->image_one)?Storage::disk('s3')->temporaryUrl(trim($testimonial->image_one),Carbon::now()->addMinutes(20)):null,
                    "description_one" => $testimonial->description_one,
                    "rating_one" =>$testimonial->rating_one,
                    "client_name_one" =>$testimonial->client_name_one,
                    "description_two" => $testimonial->description_two,
                    "rating_two" =>$testimonial->rating_two,
                    "client_name_two" =>$testimonial->client_name_two,
                    "description_three" => $testimonial->description_three,
                    "rating_three" =>$testimonial->rating_three,
                    "client_name_three" =>$testimonial->client_name_three,
//                    "image_two"=>Storage::url($testimonial->image_two),
                    "image_two" => ($testimonial->image_two)?Storage::disk('s3')->temporaryUrl(trim($testimonial->image_two),Carbon::now()->addMinutes(20)):null,
//                    "image_three"=>Storage::url($testimonial->image_three)
                    "image_three" =>($testimonial->image_three)?Storage::disk('s3')->temporaryUrl(trim($testimonial->image_three),Carbon::now()->addMinutes(20)):null,
                );
            }
            return response()->json($response);
        }
    }
}
