<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Image;
use LogActivity;

class ProductController extends Controller
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
            $totalRecords = Product::select('count(*) as allcount')->where(function ($query) use ($name, $status) {
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
            $totalRecordswithFilter = Product::select('count(*) as allcount')
                ->where(function ($query) use ($search_arr) {
                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('name', 'like', '%' . $search_arr . '%');
                    });

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('description', 'like', '%' . $search_arr . '%');
                    });
                })
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
                })->where('company_id', $company_id)->count();


            // DB::enableQueryLog();
            $records = DB::table('products')
                ->where('company_id', $company_id)
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

                    $query->orWhere(function ($query) use ($search_arr) {
                        $query->where('description', 'like', '%' . $search_arr . '%');
                    });
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
                    /*"image_one" => ($record->image_one)?Storage::url($record->image_one):Storage::url('64x64.png'),
                    "image_two" => ($record->image_two)?Storage::url($record->image_two):Storage::url('64x64.png'),
                    "image_three" => ($record->image_three)?Storage::url($record->image_three):Storage::url('64x64.png'),
                    "thumb_image_one" => ($record->thumb_image_one)?Storage::url($record->thumb_image_one):Storage::url('64x64.png'),
                    "thumb_image_two" => ($record->thumb_image_two)?Storage::url($record->thumb_image_two):Storage::url('64x64.png'),
                    "thumb_image_three" => ($record->thumb_image_three)?Storage::url($record->thumb_image_three):Storage::url('64x64.png'),*/
                    "image_one" => ($record->image_one)?Storage::disk('s3')->temporaryUrl(trim($record->image_one),Carbon::now()->addMinutes(20)):Storage::url('64x64.png'),
                    "image_two" => ($record->image_two)?Storage::disk('s3')->temporaryUrl(trim($record->image_two),Carbon::now()->addMinutes(20)):Storage::url('64x64.png'),
                    "image_three" =>($record->image_three)?Storage::disk('s3')->temporaryUrl(trim($record->image_three),Carbon::now()->addMinutes(20)):Storage::url('64x64.png'),
                    "thumb_image_one" => ($record->thumb_image_one)?Storage::disk('s3')->temporaryUrl(trim($record->thumb_image_one),Carbon::now()->addMinutes(20)):Storage::url('64x64.png'),
                    "thumb_image_two" => ($record->thumb_image_two)?Storage::disk('s3')->temporaryUrl(trim($record->thumb_image_two),Carbon::now()->addMinutes(20)):Storage::url('64x64.png'),
                    "thumb_image_three" => ($record->thumb_image_three)?Storage::disk('s3')->temporaryUrl(trim($record->thumb_image_three),Carbon::now()->addMinutes(20)):Storage::url('64x64.png'),
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
        // return view('product',compact('proposal_template'));
        return view('app.product',compact('segment'));
    }

    public function create()
    {

        $user = Auth::user();
        $company_id = ($user->company_id) ? $user->company_id : $user->id;
        // $proposal_template = Product::where('company_id',$company_id)->first();

        return view('product-photos.add'); //,compact('proposal_template')
    }

//    public function store(Request $request)
//    {
//        if ($request->ajax()) {
//            $input = $request->all();
//            $validator = Validator::make($input, [
//                'name' => 'required',
//            ]);
//
//
//            $user = Auth::user();
//            $input['user_id'] = $user->id;
//            $input['company_id'] = ($user->company_id) ? $user->company_id : $user->id;
//            $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
//
//            if ($id == 0) {
//                $validator = Validator::make($input, [
//                    'name' => 'required',
//                    'image_one' => 'required|image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
//                    'image_two' => 'image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
//                    'image_three' => 'image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
//                ]);
//            }
//            if ($validator->fails()) {
//                return response()->json(['errors' => $validator->errors()->all()], 400);;
//            }
//
//
//            if (Product::where('name', '=', $input['name'])->where('company_id', $input['company_id'])->where(function ($query) use ($id) {
//                if ($id != 0) {
//                    $query->Where(function ($query) use ($id) {
//                        $query->where('id', '!=', $id);
//                    });
//                }
//            })->first()) {
//                return response()->json(['success' => 'Product exists!'], 409);
//            }
//            if ($id == 0) {
//                $activityLogMsg = 'Product created by ' . $user->name;
//
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
//
//                    $decodedImage = $image_base64;
//                    $imageNames = Str::uuid() . '-1.' . $ext;
//                    $imagePath = public_path('storage/uploads/resize_image/') . $imageNames;
//
//                    // Use intervention/image package to create an image instance and save it to the desired path
//                    $image = Image::make($decodedImage);
//
//                    // Resize the image to the desired dimensions (e.g., width: 800px, height: 600px)
//                    $image->resize(64, 64);
//
//                    // Save the resized image to the specified path
//                    $image->save($imagePath);
//
//                    $input['thumb_image_one'] = 'public/uploads/resize_image/' . $imageNames;
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
//
//                    $decodedImage = $image_base64;
//                    $imageNames = Str::uuid() . '-2.' . $ext;
//                    $imagePath = public_path('storage/uploads/resize_image/') . $imageNames;
//
//                    // Use intervention/image package to create an image instance and save it to the desired path
//                    $image = Image::make($decodedImage);
//
//                    // Resize the image to the desired dimensions (e.g., width: 800px, height: 600px)
//                    $image->resize(64, 64);
//
//                    // Save the resized image to the specified path
//                    $image->save($imagePath);
//
//                    $input['thumb_image_two'] = 'public/uploads/resize_image/' . $imageNames;
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
//                    $decodedImage = $image_base64;
//                    $imageNames = Str::uuid() . '-3.' . $ext;
//                    $imagePath = public_path('storage/uploads/resize_image/') . $imageNames;
//
//                    // Use intervention/image package to create an image instance and save it to the desired path
//                    $image = Image::make($decodedImage);
//
//                    // Resize the image to the desired dimensions (e.g., width: 800px, height: 600px)
//                    $image->resize(64, 64);
//
//                    // Save the resized image to the specified path
//                    $image->save($imagePath);
//
//                    $input['thumb_image_three'] = 'public/uploads/resize_image/' . $imageNames;
//                }
//
//                /*if ($request->file('image_one')) {
//
//                    $image = $request->file('image_one');
//                    $input['file'] = time().'-1.'.$image->getClientOriginalExtension();
//                    $destinationPath = public_path('/storage/uploads/thumbnail');
//                    $imgFile = Image::make($image->getRealPath());
//                    $height = Image::make($image)->height();
//                    $width = Image::make($image)->width();
//                    if($height <= 1754){
//                        $imgFile->resize($width, $height, function ($constraint) {
//                            $constraint->aspectRatio();
//                        })->save($destinationPath . '/' . $input['file']);
//                    }
//                    else{
//                    $imgFile->resize(null, 720, function($constraint) { //412, 391        460, 442
//                        $constraint->aspectRatio();
//                    })->save($destinationPath.'/'.$input['file']);
//                    }
//
////                    $path = $request->file('image_one')->store('public/uploads');
//                    $input['image_one'] = 'public/uploads/thumbnail/'.$input['file'];
//                }*/
//
//                /*if ($request->file('image_two')) {
//                    $image = $request->file('image_two');
//                    $input['file'] = time().'-2.'.$image->getClientOriginalExtension();
//                    $destinationPath = public_path('/storage/uploads/thumbnail');
//                    $imgFile = Image::make($image->getRealPath());
//                    $height = Image::make($image)->height();
//                    $width = Image::make($image)->width();
//                    if($height <= 480){
//                        $imgFile->resize($width, $height, function ($constraint) {
//                            $constraint->aspectRatio();
//                        })->save($destinationPath . '/' . $input['file']);
//                    }else{
//                        $imgFile->resize(null, 480, function($constraint) { //412, 391        460, 442
//                            $constraint->aspectRatio();
//                        })->save($destinationPath.'/'.$input['file']);
//                    }
//
//                    $input['image_two'] = 'public/uploads/thumbnail/'.$input['file'];
////                    $path = $request->file('image_two')->store('public/uploads');
////                    $input['image_two'] = $path;
//                }*/
//
//                /*if ($request->file('image_three')) {
//                    $image = $request->file('image_three');
//                    $input['file'] = time().'-3.'.$image->getClientOriginalExtension();
//                    $destinationPath = public_path('/storage/uploads/thumbnail');
//                    $imgFile = Image::make($image->getRealPath());
//                    $height = Image::make($image)->height();
//                    $width = Image::make($image)->width();
//                    if($height <= 480){
//                        $imgFile->resize($width, $height, function ($constraint) {
//                            $constraint->aspectRatio();
//                        })->save($destinationPath . '/' . $input['file']);
//                    }else{
//                        $imgFile->resize(null, 480, function($constraint) { //412, 391        460, 442
//                            $constraint->aspectRatio();
//                        })->save($destinationPath.'/'.$input['file']);
//                    }
//
//                    $input['image_three'] = 'public/uploads/thumbnail/'.$input['file'];
////                    $path = $request->file('image_three')->store('public/uploads');
////                    $input['image_three'] = $path;
//                }*/
//
//                $product = Product::create($input);
//            } else {
//                $productData = Product::find($id);
//
//                if ($request->hasFile('image_one')) {
//                    $request->validate([
//                        'image_one' => 'required|image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
//                    ]);
//
//                    if (Storage::exists($productData->image_one)) {
//                        Storage::delete($productData->image_one);
//                    }
//
//                    if (Storage::exists($productData->thumb_image_one)) {
//                        Storage::delete($productData->thumb_image_one);
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
//
//                    $decodedImage = $image_base64;
//                    $imageNames = Str::uuid() . '-1.' . $ext;
//                    $imagePath = public_path('storage/uploads/resize_image/') . $imageNames;
//
//                    // Use intervention/image package to create an image instance and save it to the desired path
//                    $image = Image::make($decodedImage);
//
//                    // Resize the image to the desired dimensions (e.g., width: 800px, height: 600px)
//                    $image->resize(64, 64);
//
//                    // Save the resized image to the specified path
//                    $image->save($imagePath);
//
//                    $input['thumb_image_one'] = 'public/uploads/resize_image/' . $imageNames;
//                    /*$image = $request->file('image_one');
//                    $input['file'] = time().'-1.'.$image->getClientOriginalExtension();
//                    $destinationPath = public_path('/storage/uploads/thumbnail');
//                    $imgFile = Image::make($image->getRealPath());
//                    $height = Image::make($image)->height();
//                    $width = Image::make($image)->width();
//                    if($height <= 1754){
//                        $imgFile->resize($width, $height, function ($constraint) {
//                            $constraint->aspectRatio();
//                        })->save($destinationPath . '/' . $input['file']);
//                    }else{
//                        $imgFile->resize(null, 720, function($constraint) {
//                            $constraint->aspectRatio();
//                        })->save($destinationPath.'/'.$input['file']);
//                    }
//
//
//                    $input['image_one'] = 'public/uploads/thumbnail/'.$input['file'];*/
////                    $path = $request->file('image_one')->store('public/uploads');
////                    $input['image_one'] = $path;
//                }
//
//                if ($request->hasFile('image_two')) {
//                    $request->validate([
//                        'image_two' => 'required|image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
//                    ]);
//                    if (Storage::exists($productData->image_two)) {
//                        Storage::delete($productData->image_two);
//                    }
//
//                    if (Storage::exists($productData->thumb_image_two)) {
//                        Storage::delete($productData->thumb_image_two);
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
//                    $decodedImage = $image_base64;
//                    $imageNames = Str::uuid() . '-2.' . $ext;
//                    $imagePath = public_path('storage/uploads/resize_image/') . $imageNames;
//
//                    // Use intervention/image package to create an image instance and save it to the desired path
//                    $image = Image::make($decodedImage);
//
//                    // Resize the image to the desired dimensions (e.g., width: 800px, height: 600px)
//                    $image->resize(64, 64);
//
//                    // Save the resized image to the specified path
//                    $image->save($imagePath);
//
//                    $input['thumb_image_two'] = 'public/uploads/resize_image/' . $imageNames;
//
//                    /* $image = $request->file('image_two');
//                     $input['file'] = time().'-2.'.$image->getClientOriginalExtension();
//                     $destinationPath = public_path('/storage/uploads/thumbnail');
//                     $imgFile = Image::make($image->getRealPath());
//                     $height = Image::make($image)->height();
//                     $width = Image::make($image)->width();
//                     if($height <= 480){
//                         $imgFile->resize($width, $height, function ($constraint) {
//                             $constraint->aspectRatio();
//                         })->save($destinationPath . '/' . $input['file']);
//                     }else{
//                         $imgFile->resize(null, 480, function($constraint) { //412, 391        460, 442
//                             $constraint->aspectRatio();
//                         })->save($destinationPath.'/'.$input['file']);
//                     }
//
//                     $input['image_two'] = 'public/uploads/thumbnail/'.$input['file'];*/
////                    $path = $request->file('image_two')->store('public/uploads');
////                    $input['image_two'] = $path;
//                }
//
//                if ($request->hasFile('image_three')) {
//                    $request->validate([
//                        'image_three' => 'required|image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
//                    ]);
//                    if (Storage::exists($productData->image_three)) {
//                        Storage::delete($productData->image_three);
//                    }
//
//                    if (Storage::exists($productData->thumb_image_three)) {
//                        Storage::delete($productData->thumb_image_three);
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
//
//                    $decodedImage = $image_base64;
//                    $imageNames = Str::uuid() . '-3.' . $ext;
//                    $imagePath = public_path('storage/uploads/resize_image/') . $imageNames;
//
//                    // Use intervention/image package to create an image instance and save it to the desired path
//                    $image = Image::make($decodedImage);
//
//                    // Resize the image to the desired dimensions (e.g., width: 800px, height: 600px)
//                    $image->resize(64, 64);
//
//                    // Save the resized image to the specified path
//                    $image->save($imagePath);
//
//                    $input['thumb_image_three'] = 'public/uploads/resize_image/' . $imageNames;
//
//                    /*$image = $request->file('image_three');
//                    $input['file'] = time().'-3.'.$image->getClientOriginalExtension();
//                    $destinationPath = public_path('/storage/uploads/thumbnail');
//                    $imgFile = Image::make($image->getRealPath());
//                    $height = Image::make($image)->height();
//                    $width = Image::make($image)->width();
//                    if($height <= 480){
//                        $imgFile->resize($width, $height, function ($constraint) {
//                            $constraint->aspectRatio();
//                        })->save($destinationPath . '/' . $input['file']);
//                    }else {
//                        $imgFile->resize(null, 480, function($constraint) { //412, 391        460, 442
//                            $constraint->aspectRatio();
//                        })->save($destinationPath.'/'.$input['file']);
//                    }
//
//                    $input['image_three'] = 'public/uploads/thumbnail/'.$input['file'];*/
////                    $path = $request->file('image_three')->store('public/uploads');
////                    $input['image_three'] = $path;
//                }
//                $product = Product::find($id)->update($input);
//                $activityLogMsg = 'Product updated by ' . $user->name;
//            }
//
//            // Add activity logs
//            $input['id'] = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
//            LogActivity::addToLog($activityLogMsg, $input);
//
//            return response()->json(['success' => 'Product Saved!'], 201);
//        }
//    }

    public function store(Request $request) //s3 code store
    {
        if ($request->ajax()) {
            $input = $request->all();
            $validator = Validator::make($input, [
                'name' => 'required',
            ]);


            $user = Auth::user();
            $input['user_id'] = $user->id;
            $input['company_id'] = ($user->company_id) ? $user->company_id : $user->id;
            $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];

            if ($id == 0) {
                $validator = Validator::make($input, [
                    'name' => 'required',
                    'image_one' => 'required|image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
                    'image_two' => 'image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
                    'image_three' => 'image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
                ]);
            }
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);;
            }


            if (Product::where('name', '=', $input['name'])->where('company_id', $input['company_id'])->where(function ($query) use ($id) {
                if ($id != 0) {
                    $query->Where(function ($query) use ($id) {
                        $query->where('id', '!=', $id);
                    });
                }
            })->first()) {
                return response()->json(['success' => 'Product exists!'], 409);
            }
            if ($id == 0) {
                $activityLogMsg = 'Product created by ' . $user->name;

                if ($request->h_image_one) {
                    $ext = explode(";", explode("/", $request->h_image_one)[1])[0];
                    $imageName = uniqid() . '-1' . '.' . $ext;
                    //S3 bucket
                    $imageData = $request->input('h_image_one');
                    // Decode the base64 image data
                    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

                    // Upload the image to S3
                    Storage::disk('s3')->put('public/'.$input["company_id"].'/products/thumbnail/'.$imageName, $imageData,'public');

                    // Get the S3 URL of the uploaded image
                    $s3Url = Storage::disk('s3')->url('public/'.$input["company_id"].'/products/thumbnail/'.$imageName);
                    $input['image_one'] = 'public/'.$input['company_id'].'/products/thumbnail/'.$imageName;

                    $imageNames = Str::uuid() . '-1.' . $ext;


                    // Use intervention/image package to create an image instance and save it to the desired path
                    $image = Image::make($imageData);

                    // Resize the image to the desired dimensions (e.g., width: 800px, height: 600px)
                    $image->resize(64, 64);

                    // Upload the resized image to S3
                    Storage::disk('s3')->put('public/'.$input['company_id'].'/products/resize_image/'.$imageNames, $image->encode($ext),'public');

                    // Get the S3 URL of the uploaded image
                    $s3Url = Storage::disk('s3')->url('public/'.$input['company_id'].'/products/resize_image/'.$imageNames);

//                    $input['thumb_image_one'] = 'public/uploads/resize_image/' . $imageNames;
                    $input['thumb_image_one'] = 'public/'.$input['company_id'].'/products/resize_image/'.$imageNames;
                }

                if ($request->h_image_two) {
                    $ext = explode(";", explode("/", $request->h_image_two)[1])[0];
                    $imageName = uniqid() . '-2' . '.' . $ext;

//                    S3 Bucket
                    $imageData = $request->input('h_image_two');
                    // Decode the base64 image data
                    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

                    // Upload the image to S3
                    Storage::disk('s3')->put('public/'.$input["company_id"].'/products/thumbnail/'.$imageName, $imageData,'public');

                    // Get the S3 URL of the uploaded image
                    $s3Url = Storage::disk('s3')->url('public/'.$input["company_id"].'/products/thumbnail/'.$imageName);
                    $input['image_two'] = 'public/'.$input['company_id'].'/products/thumbnail/'.$imageName;


                    $imageNames = Str::uuid() . '-2.' . $ext;
                    $image = Image::make($imageData);
                    $image->resize(64, 64);

                    // Upload the resized image to S3
                    Storage::disk('s3')->put('public/'.$input['company_id'].'/products/resize_image/'.$imageNames, $image->encode($ext),'public');

                    // Get the S3 URL of the uploaded image
                    $s3Url = Storage::disk('s3')->url('public/'.$input['company_id'].'/products/resize_image/'.$imageNames);

//                    $input['thumb_image_one'] = 'public/uploads/resize_image/' . $imageNames;
                    $input['thumb_image_two'] = 'public/'.$input['company_id'].'/products/resize_image/'.$imageNames;
                }

                if ($request->h_image_three) {
                    $ext = explode(";", explode("/", $request->h_image_three)[1])[0];
                    $imageName = uniqid() . '-3' . '.' . $ext;


//                    S3 Bucket
                    $imageData = $request->input('h_image_three');
                    // Decode the base64 image data
                    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

                    // Upload the image to S3
                    Storage::disk('s3')->put('public/'.$input["company_id"].'/products/thumbnail/'.$imageName, $imageData,'public');

                    // Get the S3 URL of the uploaded image
                    $s3Url = Storage::disk('s3')->url('public/'.$input["company_id"].'/products/thumbnail/'.$imageName);
                    $input['image_three'] = 'public/'.$input['company_id'].'/products/thumbnail/'.$imageName;

                    $imageNames = Str::uuid() . '-3.' . $ext;
                    $image = Image::make($imageData);
                    $image->resize(64, 64);

                    // Upload the resized image to S3
                    Storage::disk('s3')->put('public/'.$input['company_id'].'/products/resize_image/'.$imageNames, $image->encode($ext),'public');

                    // Get the S3 URL of the uploaded image
                    $s3Url = Storage::disk('s3')->url('public/'.$input['company_id'].'/products/resize_image/'.$imageNames);

                    $input['thumb_image_three'] = 'public/'.$input['company_id'].'/products/resize_image/'.$imageNames;
                }

                $product = Product::create($input);
            } else {
                $productData = Product::find($id);

                if ($request->hasFile('image_one')) {
                    $request->validate([
                        'image_one' => 'required|image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
                    ]);

                    if ($productData->image_one) {
                        Storage::disk('s3')->delete($productData->image_one);
                    }

                    if ($productData->thumb_image_one) {
                        Storage::disk('s3')->delete($productData->thumb_image_one);
                    }


                    $ext = explode(";", explode("/", $request->h_image_one)[1])[0];
                    $imageName = uniqid() . '-1' . '.' . $ext;


                    //S3 bucket
                    $imageData = $request->input('h_image_one');
                    // Decode the base64 image data
                    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

                    // Upload the image to S3
                    Storage::disk('s3')->put('public/'.$input["company_id"].'/products/thumbnail/'.$imageName, $imageData,'public');

                    // Get the S3 URL of the uploaded image
                    $s3Url = Storage::disk('s3')->url('public/'.$input["company_id"].'/products/thumbnail/'.$imageName);
                    $input['image_one'] = 'public/'.$input['company_id'].'/products/thumbnail/'.$imageName;

                    $imageNames = Str::uuid() . '-1.' . $ext;
                    $image = Image::make($imageData);
                    $image->resize(64, 64);

                    // Upload the resized image to S3
                    Storage::disk('s3')->put('public/'.$input['company_id'].'/products/resize_image/'.$imageNames, $image->encode($ext),'public');

                    // Get the S3 URL of the uploaded image
                    $s3Url = Storage::disk('s3')->url('public/'.$input['company_id'].'/products/resize_image/'.$imageNames);

                    $input['thumb_image_one'] = 'public/'.$input['company_id'].'/products/resize_image/'.$imageNames;
                }

                if ($request->hasFile('image_two')) {
                    $request->validate([
                        'image_two' => 'required|image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
                    ]);

                    if ($productData->image_two) {
                        Storage::disk('s3')->delete($productData->image_two);
                    }

                    if ($productData->thumb_image_two) {
                        Storage::disk('s3')->delete($productData->thumb_image_two);
                    }


                    $ext = explode(";", explode("/", $request->h_image_two)[1])[0];
                    $imageName = uniqid() . '-2' . '.' . $ext;


                    //S3 bucket
                    $imageData = $request->input('h_image_two');
                    // Decode the base64 image data
                    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

                    // Upload the image to S3
                    Storage::disk('s3')->put('public/'.$input["company_id"].'/products/thumbnail/'.$imageName, $imageData,'public');

                    // Get the S3 URL of the uploaded image
                    $s3Url = Storage::disk('s3')->url('public/'.$input["company_id"].'/products/thumbnail/'.$imageName);
                    $input['image_two'] = 'public/'.$input['company_id'].'/products/thumbnail/'.$imageName;

                    $imageNames = Str::uuid() . '-2.' . $ext;
                    $image = Image::make($imageData);
                    $image->resize(64, 64);

                    // Upload the resized image to S3
                    Storage::disk('s3')->put('public/'.$input['company_id'].'/products/resize_image/'.$imageNames, $image->encode($ext),'public');

                    // Get the S3 URL of the uploaded image
                    $s3Url = Storage::disk('s3')->url('public/'.$input['company_id'].'/products/resize_image/'.$imageNames);

                    $input['thumb_image_two'] = 'public/'.$input['company_id'].'/products/resize_image/'.$imageNames;

                }

                if ($request->hasFile('image_three')) {
                    $request->validate([
                        'image_three' => 'required|image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
                    ]);

                    if ($productData->image_three) {
                        Storage::disk('s3')->delete($productData->image_three);
                    }

                    if ($productData->thumb_image_three) {
                        Storage::disk('s3')->delete($productData->thumb_image_three);
                    }


                    $ext = explode(";", explode("/", $request->h_image_three)[1])[0];
                    $imageName = uniqid() . '-3' . '.' . $ext;


                    //S3 bucket
                    $imageData = $request->input('h_image_three');
                    // Decode the base64 image data
                    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

                    // Upload the image to S3
                    Storage::disk('s3')->put('public/'.$input["company_id"].'/products/thumbnail/'.$imageName, $imageData,'public');

                    // Get the S3 URL of the uploaded image
                    $s3Url = Storage::disk('s3')->url('public/'.$input["company_id"].'/products/thumbnail/'.$imageName);
                    $input['image_three'] = 'public/'.$input['company_id'].'/products/thumbnail/'.$imageName;

                    $imageNames = Str::uuid() . '-3.' . $ext;
                    $image = Image::make($imageData);
                    $image->resize(64, 64);

                    // Upload the resized image to S3
                    Storage::disk('s3')->put('public/'.$input['company_id'].'/products/resize_image/'.$imageNames, $image->encode($ext),'public');

                    // Get the S3 URL of the uploaded image
                    $s3Url = Storage::disk('s3')->url('public/'.$input['company_id'].'/products/resize_image/'.$imageNames);

                    $input['thumb_image_three'] = 'public/'.$input['company_id'].'/products/resize_image/'.$imageNames;

                }
                $product = Product::find($id)->update($input);
                $activityLogMsg = 'Product updated by ' . $user->name;
            }

            // Add activity logs
            $input['id'] = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
            LogActivity::addToLog($activityLogMsg, $input);

            return response()->json(['success' => 'Product Saved!'], 201);
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

            $product = Product::find($id)->toArray();

            if (is_null($product)) {
                return response()->json(['success' => 'Product not found!'], 422);
            }
            $product['id'] = Crypt::encrypt($product['id']);
            return response()->json([
                "success" => true,
                "message" => "Product retrieved successfully.",
                "data" => $product
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
            $id = [];


            foreach (explode(",", $request->id) as $value) {
                $id[] = Crypt::decrypt($value);

                $productData = Product::find(Crypt::decrypt($value));

                if ($productData->image_one) {
                    Storage::disk('s3')->delete($productData->image_one);
                }

                if ($productData->thumb_image_one) {
                    Storage::disk('s3')->delete($productData->thumb_image_one);
                }

                if ($productData->image_two) {
                    Storage::disk('s3')->delete($productData->image_two);
                }

                if ($productData->thumb_image_two) {
                    Storage::disk('s3')->delete($productData->thumb_image_two);
                }

                if ($productData->image_three) {
                    Storage::disk('s3')->delete($productData->image_three);
                }

                if ($productData->thumb_image_three) {
                    Storage::disk('s3')->delete($productData->thumb_image_three);
                }
                /*if (Storage::exists($productData->image_one)) {
                    Storage::delete($productData->image_one);
                }

                if (Storage::exists($productData->thumb_image_one)) {
                    Storage::delete($productData->thumb_image_one);
                }

                if (Storage::exists($productData->image_two)) {
                    Storage::delete($productData->image_two);
                }

                if (Storage::exists($productData->thumb_image_two)) {
                    Storage::delete($productData->thumb_image_two);
                }

                if (Storage::exists($productData->image_three)) {
                    Storage::delete($productData->image_three);
                }

                if (Storage::exists($productData->thumb_image_three)) {
                    Storage::delete($productData->thumb_image_three);
                }*/

            }
            $product = Product::whereIn('id', $id)->delete();

            LogActivity::addToLog('Product deleted by ' . $user->name, $id);
            return response()->json(['success' => 'Product Deleted!'], 201);
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
            $id = [];
            foreach (explode(",", $input['id']) as $value) {
                $id[] = Crypt::decrypt($value);
            }
            if (!Product::whereIn('id', $id)->first()) {
                return response()->json(['success' => 'Product exists!'], 422);
            }
            $product = Product::whereIn('id', $id)->update(["status" => $input['status']]);

            $data['id'] = $id;
            $data['status'] = ($input['status'] == 0) ? 'Active' : 'Deactive';
            LogActivity::addToLog('Product status updated by ' . $user->name, $data);

            return response()->json(['success' => 'Product status updated!'], 201);
        }
    }

    public function productAutocomplete(Request $request)
    {
        if ($request->ajax()) {
            $search = $request->get('search');
            $user = Auth::user();
            $company_id = ($user->company_id) ? $user->company_id : $user->id;
            $products = Product::select('id', 'name', 'thumb_image_one', 'thumb_image_two', 'thumb_image_three', 'image_one', 'image_two', 'image_three')->where('products.company_id', $company_id)->where('name', 'LIKE', '%' . $search . '%')->where('status', 0)->get();
            $response = array();
            foreach ($products as $product) {
                $response[] = array(
                    "value" => $product->id,
                    "label" => $product->name,
                    /*"image_one" => ($product->image_one)?Storage::url($product->image_one):Storage::url('64x64.png'),
                    "image_two" => ($product->image_two)?Storage::url($product->image_two):Storage::url('64x64.png'),
                    "image_three" => ($product->image_three)?Storage::url($product->image_three):Storage::url('64x64.png'),
                    "thumb_image_one" => ($product->thumb_image_one)?Storage::url($product->thumb_image_one):Storage::url('64x64.png'),
                    "thumb_image_two" => ($product->thumb_image_two)?Storage::url($product->thumb_image_two):Storage::url('64x64.png'),
                    "thumb_image_three" => ($product->thumb_image_three)?Storage::url($product->thumb_image_three):Storage::url('64x64.png'),*/
                    /*"image_one" => Storage::url($product->image_one),
                    "image_two" => Storage::url($product->image_two),
                    "image_three" => Storage::url($product->image_three),
                    "thumb_image_one" => ($product->thumb_image_one) ?Storage::url($product->thumb_image_one):'',
                    "thumb_image_two" => ($product->thumb_image_two)?Storage::url($product->thumb_image_two):'',
                    "thumb_image_three" => ($product->thumb_image_three)?Storage::url($product->thumb_image_three):'',*/
                    "image_one" => ($product->image_one)?Storage::disk('s3')->temporaryUrl(trim($product->image_one),Carbon::now()->addMinutes(20)):Storage::disk('s3')->temporaryUrl('template/64x64.png',Carbon::now()->addMinutes(20)),
                    "image_two" => ($product->image_two)?Storage::disk('s3')->temporaryUrl(trim($product->image_two),Carbon::now()->addMinutes(20)):Storage::disk('s3')->temporaryUrl('template/64x64.png',Carbon::now()->addMinutes(20)),
                    "image_three" =>($product->image_three)?Storage::disk('s3')->temporaryUrl(trim($product->image_three),Carbon::now()->addMinutes(20)):Storage::disk('s3')->temporaryUrl('template/64x64.png',Carbon::now()->addMinutes(20)),
                    "thumb_image_one" => ($product->thumb_image_one)?Storage::disk('s3')->temporaryUrl(trim($product->thumb_image_one),Carbon::now()->addMinutes(20)):Storage::disk('s3')->temporaryUrl('template/64x64.png',Carbon::now()->addMinutes(20)),
                    "thumb_image_two" => ($product->thumb_image_two)?Storage::disk('s3')->temporaryUrl(trim($product->thumb_image_two),Carbon::now()->addMinutes(20)):Storage::disk('s3')->temporaryUrl('template/64x64.png',Carbon::now()->addMinutes(20)),
                    "thumb_image_three" => ($product->thumb_image_three)?Storage::disk('s3')->temporaryUrl(trim($product->thumb_image_three),Carbon::now()->addMinutes(20)):Storage::disk('s3')->temporaryUrl('template/64x64.png',Carbon::now()->addMinutes(20)),
                );
            }

            return response()->json($response);
            return response()->json($result);
        }
    }

    public function copytothumbimg($company_id, $id = 0)
    {
        $products = Product::where('company_id', $company_id)
            ->where(function ($query) use ($id) {
                if ($id != 0) {
                    $query->where('id', '=', $id);
                }
            })->get();
        foreach ($products as $product) {
            if (Storage::exists($product->image_one)) {
                $imageOne = explode('public/', $product->image_one)[1];
                $resizedImageOne = env('APP_URL') . 'storage/' . $imageOne;
                Image::make($resizedImageOne)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . basename($product->image_one)));
                $product->thumb_image_one = 'public/uploads/resize_image/' . basename($product->image_one);
                $product->save();
            }

            if (Storage::exists($product->image_two)) {
                $imageTwo = explode('public/', $product->image_two)[1];
                $resizedImageTwo = env('APP_URL') . 'storage/' . $imageTwo;
                Image::make($resizedImageTwo)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . basename($product->image_two)));
                $product->thumb_image_two = 'public/uploads/resize_image/' . basename($product->image_two);
                $product->save();
            }

            if (Storage::exists($product->image_three)) {
                $imageThree = explode('public/', $product->image_three)[1];
                $resizedImageThree = env('APP_URL') . 'storage/' . $imageThree;
                Image::make($resizedImageThree)->resize(64, 64)->save(storage_path("app/public/uploads/resize_image/" . basename($product->image_three)));
                $product->thumb_image_three = 'public/uploads/resize_image/' . basename($product->image_three);
                $product->save();
            }
        }
    }

    public function EstimateProductStore(Request $request) //s3 code store
    {
        if ($request->ajax()) {
            $input = $request->all();



            $user = Auth::user();
            $input['user_id'] = $user->id;
            $input['company_id'] = ($user->company_id) ? $user->company_id : $user->id;
            $id = $input['id'];

            $productData = Product::find($id);

            if ($input['img_type']==1) {

                /*if ($productData->image_one) {
                    Storage::disk('s3')->delete($productData->image_one);
                }

                if ($productData->thumb_image_one) {
                    Storage::disk('s3')->delete($productData->thumb_image_one);
                }*/


                $ext = explode(";", explode("/", $request->image)[1])[0];
                $imageName = uniqid() . '-1' . '.' . $ext;


                //S3 bucket
                $imageData = $request->input('image');
                // Decode the base64 image data
                $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

                // Upload the image to S3
                Storage::disk('s3')->put('public/'.$input["company_id"].'/products/thumbnail/'.$imageName, $imageData,'public');

                // Get the S3 URL of the uploaded image
                $s3Url = Storage::disk('s3')->url('public/'.$input["company_id"].'/products/thumbnail/'.$imageName);
                $input['image_one'] = 'public/'.$input['company_id'].'/products/thumbnail/'.$imageName;

                $imageNames = Str::uuid() . '-1.' . $ext;
                $image = Image::make($imageData);
                $image->resize(64, 64);

                // Upload the resized image to S3
                Storage::disk('s3')->put('public/'.$input['company_id'].'/products/resize_image/'.$imageNames, $image->encode($ext),'public');

                // Get the S3 URL of the uploaded image
                $s3Url = Storage::disk('s3')->url('public/'.$input['company_id'].'/products/resize_image/'.$imageNames);

                $input['thumb_image_one'] = 'public/'.$input['company_id'].'/products/resize_image/'.$imageNames;
            }

            if ($input['img_type']==2) {
                /*$request->validate([
                    'image' => 'required|image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
                ]);*/

                /*if ($productData->image_two) {
                    Storage::disk('s3')->delete($productData->image_two);
                }

                if ($productData->thumb_image_two) {
                    Storage::disk('s3')->delete($productData->thumb_image_two);
                }*/


                $ext = explode(";", explode("/", $request->image)[1])[0];
                $imageName = uniqid() . '-2' . '.' . $ext;


                //S3 bucket
                $imageData = $request->input('image');
                // Decode the base64 image data
                $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

                // Upload the image to S3
                Storage::disk('s3')->put('public/'.$input["company_id"].'/products/thumbnail/'.$imageName, $imageData,'public');

                // Get the S3 URL of the uploaded image
                $s3Url = Storage::disk('s3')->url('public/'.$input["company_id"].'/products/thumbnail/'.$imageName);
                $input['image_two'] = 'public/'.$input['company_id'].'/products/thumbnail/'.$imageName;

                $imageNames = Str::uuid() . '-2.' . $ext;
                $image = Image::make($imageData);
                $image->resize(64, 64);

                // Upload the resized image to S3
                Storage::disk('s3')->put('public/'.$input['company_id'].'/products/resize_image/'.$imageNames, $image->encode($ext),'public');

                // Get the S3 URL of the uploaded image
                $s3Url = Storage::disk('s3')->url('public/'.$input['company_id'].'/products/resize_image/'.$imageNames);

                $input['thumb_image_two'] = 'public/'.$input['company_id'].'/products/resize_image/'.$imageNames;

            }

            if ($input['img_type']==3) {
               /* $request->validate([
                    'image' => 'required|image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
                ]);*/

                /*if ($productData->image_three) {
                    Storage::disk('s3')->delete($productData->image_three);
                }

                if ($productData->thumb_image_three) {
                    Storage::disk('s3')->delete($productData->thumb_image_three);
                }*/


                $ext = explode(";", explode("/", $request->image)[1])[0];
                $imageName = uniqid() . '-3' . '.' . $ext;


                //S3 bucket
                $imageData = $request->input('image');
                // Decode the base64 image data
                $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

                // Upload the image to S3
                Storage::disk('s3')->put('public/'.$input["company_id"].'/products/thumbnail/'.$imageName, $imageData,'public');

                // Get the S3 URL of the uploaded image
                $s3Url = Storage::disk('s3')->url('public/'.$input["company_id"].'/products/thumbnail/'.$imageName);
                $input['image_three'] = 'public/'.$input['company_id'].'/products/thumbnail/'.$imageName;

                $imageNames = Str::uuid() . '-3.' . $ext;
                $image = Image::make($imageData);
                $image->resize(64, 64);

                // Upload the resized image to S3
                Storage::disk('s3')->put('public/'.$input['company_id'].'/products/resize_image/'.$imageNames, $image->encode($ext),'public');

                // Get the S3 URL of the uploaded image
                $s3Url = Storage::disk('s3')->url('public/'.$input['company_id'].'/products/resize_image/'.$imageNames);

                $input['thumb_image_three'] = 'public/'.$input['company_id'].'/products/resize_image/'.$imageNames;

            }
            $product = Product::find($id)->update($input);


            return response()->json(['success' => 'Product Saved!'], 201);
        }
    }
}
