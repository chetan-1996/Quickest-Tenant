<?php

namespace App\Http\Controllers\API\V6;

use App\Http\Controllers\API\V6\BaseController as BaseController;
use App\Models\{
    admin\ViewUserData,
    City,
    Country,
    Customer,
    Estimate,
    EstimateAutoNumber,
    EstimateItems,
    EstimateTimeline,
    Event,
    Item,
    PlanHistory,
    Product,
    ProposalTemplates,
    SalesPersonPerformances,
    State,
    Tax,
    TermCondition,
    Testimonial,
    User,
    ViewCustomerData
};
use App\Models\admin\LeadHistory;
use App\Models\admin\EstimateHistory;
use App\Models\admin\AttachmentHistory;
use App\Services\MpdfService;
use Carbon\Carbon;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Crypt, DB, Storage, Validator};
//use Image;
use LogActivity;
use Intervention\Image\Facades\Image;
use Mpdf\Mpdf;

class EstimateController extends BaseController
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

    public function testimonialAutocomplete($search = null)
    {
        $testimonials = Testimonial::select('name', 'id', 'client_name_one', 'image_one', 'rating_one', 'description_one', 'client_name_two', 'image_two', 'rating_two', 'description_two', 'client_name_three', 'image_three', 'rating_three', 'description_three', 'is_default')->where('testimonials.company_id', $this->company_id)->where('testimonials.status', 0)->where('name', 'LIKE', '%' . $search . '%')->where('status', 0)->get();
        $response = array();
        foreach ($testimonials as $testimonial) {
            $response[] = array("value" => $testimonial->id, "label" => $testimonial->name, "image_one" => Storage::disk('s3')->temporaryUrl(trim($testimonial->image_one),Carbon::now()->addMinutes(20)), "description_one" => $testimonial->description_one, "rating_one" => $testimonial->rating_one, "client_name_one" => $testimonial->client_name_one, "description_two" => $testimonial->description_two, "rating_two" => $testimonial->rating_two, "client_name_two" => $testimonial->client_name_two, "description_three" => $testimonial->description_three, "rating_three" => $testimonial->rating_three, "client_name_three" => $testimonial->client_name_three, "image_two" => Storage::disk('s3')->temporaryUrl(trim($testimonial->image_two),Carbon::now()->addMinutes(20)), "image_three" => Storage::disk('s3')->temporaryUrl(trim($testimonial->image_three),Carbon::now()->addMinutes(20)), "is_default" => $testimonial->is_default);
        }
        return $this->sendResponse($response, 'Testimonial retrieved successfully');
    }

    public function getTaxAutocomplete($search = null)
    {
        $tax = Tax::query()
            ->select('id', 'name')
            ->where([['company_id', "=", $this->company_id], ['name', 'LIKE', '%' . $search . '%'], ['status', "=", 0]])
            ->orderBy("name")
            ->get();

        return $this->sendResponse($tax, 'Tax retrieved successfully');
    }

    public function testimonialStore(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
        ]);

        $input['user_id'] = $this->logged_user->id;
        $input['company_id'] = ($this->company_id) ? $this->company_id : $this->logged_user->id;
        $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];


        if ($id == 0) {
            $validator = Validator::make($input, [
                'name' => 'required',
                'image_one' => 'required|image|mimes:jpeg,png,jpg|max:1024',
                'image_two' => 'required|image|mimes:jpeg,png,jpg|max:1024',
                'image_three' => 'required|image|mimes:jpeg,png,jpg|max:1024',
            ]);
        }
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }

        if (Testimonial::where('name', '=', $input['name'])->where('company_id', $input['company_id'])->where(function ($query) use ($id) {
            if ($id != 0) {
                $query->Where(function ($query) use ($id) {
                    $query->where('id', '!=', $id);
                });
            }
        })->first()) {
            return $this->sendError('Testimonial exists', ['error' => 'Testimonial exists'], 409);
        }
        if (isset($input['is_default']) && $input['is_default'] == 1) {
            Testimonial::where([['id', '>', 0], ['company_id', '=', $input['company_id']]])->update(['is_default' => 0]);
        }
        if ($id == 0) {
            $activityLogMsg = 'Testimonial created by ' . $this->logged_user->name;

            if ($request->file('image_one')) {

                $image = $request->file('image_one');
                $input['file'] = time() . '-1.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('/storage/uploads/thumbnail');
                $imgFile = Image::make($image->getRealPath());
                $imgFile->resize(null, 442, function ($constraint) { //412, 391        460, 442
                    $constraint->aspectRatio();
                })->save($destinationPath . '/' . $input['file']);

                //                    $path = $request->file('image_one')->store('public/uploads');
                $input['image_one'] = 'public/uploads/thumbnail/' . $input['file'];
            }
            if ($request->file('image_two')) {
                $image = $request->file('image_two');
                $input['file'] = time() . '-2.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('/storage/uploads/thumbnail');
                $imgFile = Image::make($image->getRealPath());
                $imgFile->resize(null, 391, function ($constraint) { //412, 391        460, 442
                    $constraint->aspectRatio();
                })->save($destinationPath . '/' . $input['file']);

                $input['image_two'] = 'public/uploads/thumbnail/' . $input['file'];
                //                    $path = $request->file('image_two')->store('public/uploads');
                //                    $input['image_two'] = $path;
            }
            if ($request->file('image_three')) {
                $image = $request->file('image_three');
                $input['file'] = time() . '-3.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('/storage/uploads/thumbnail');
                $imgFile = Image::make($image->getRealPath());
                $imgFile->resize(null, 391, function ($constraint) { //412, 391        460, 442
                    $constraint->aspectRatio();
                })->save($destinationPath . '/' . $input['file']);

                $input['image_three'] = 'public/uploads/thumbnail/' . $input['file'];
                //                    $path = $request->file('image_three')->store('public/uploads');
                //                    $input['image_three'] = $path;
            }
            $testimonial = Testimonial::create($input);
        } else {
            $testimonialData = Testimonial::find($id);
            if ($request->hasFile('image_one')) {
                $request->validate([
                    'image_one' => 'required|image|mimes:jpeg,png,jpg|max:1024',
                ]);

                if (Storage::exists($testimonialData->image_one)) {
                    Storage::delete($testimonialData->image_one);
                }
                $image = $request->file('image_one');
                $input['file'] = time() . '-1.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('/storage/uploads/thumbnail');
                $imgFile = Image::make($image->getRealPath());
                $imgFile->resize(null, 391, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPath . '/' . $input['file']);

                $input['image_one'] = 'public/uploads/thumbnail/' . $input['file'];
                //                    $path = $request->file('image_one')->store('public/uploads');
                //                    $input['image_one'] = $path;
            }
            if ($request->hasFile('image_two')) {
                $request->validate([
                    'image_two' => 'required|image|mimes:jpeg,png,jpg|max:1024',
                ]);
                if (Storage::exists($testimonialData->image_two)) {
                    Storage::delete($testimonialData->image_two);
                }
                $image = $request->file('image_two');
                $input['file'] = time() . '-2.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('/storage/uploads/thumbnail');
                $imgFile = Image::make($image->getRealPath());
                $imgFile->resize(null, 391, function ($constraint) { //412, 391        460, 442
                    $constraint->aspectRatio();
                })->save($destinationPath . '/' . $input['file']);

                $input['image_two'] = 'public/uploads/thumbnail/' . $input['file'];
                //                    $path = $request->file('image_two')->store('public/uploads');
                //                    $input['image_two'] = $path;
            }

            if ($request->hasFile('image_three')) {
                $request->validate([
                    'image_three' => 'required|image|mimes:jpeg,png,jpg|max:1024',
                ]);
                if (Storage::exists($testimonialData->image_three)) {
                    Storage::delete($testimonialData->image_three);
                }
                $image = $request->file('image_three');
                $input['file'] = time() . '-3.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('/storage/uploads/thumbnail');
                $imgFile = Image::make($image->getRealPath());
                $imgFile->resize(null, 391, function ($constraint) { //412, 391        460, 442
                    $constraint->aspectRatio();
                })->save($destinationPath . '/' . $input['file']);

                $input['image_three'] = 'public/uploads/thumbnail/' . $input['file'];
                //                    $path = $request->file('image_three')->store('public/uploads');
                //                    $input['image_three'] = $path;
            }

            $testimonial = Testimonial::find($id)->update($input);
            $activityLogMsg = 'Testimonial updated by ' . $this->logged_user->name;
        }


        // Add activity logs
        $input['id'] = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
        LogActivity::addToLog($activityLogMsg, $input);
        return $this->sendResponse([], 'Testimonial Saved');
    }

    public function productAutocomplete($search = null)
    {
        $products = Product::select('id', 'name', 'image_one', 'image_two', 'image_three', 'thumb_image_one', 'thumb_image_two', 'thumb_image_three')->where('products.company_id', $this->company_id)->where('name', 'LIKE', '%' . $search . '%')->where('status', 0)->get();
        $response = array();
        foreach ($products as $product) {
            /*$response[] = array("value" => $product->id, "label" => $product->name, "image_one" => Storage::disk('s3')->temporaryUrl(trim($product->image_one),Carbon::now()->addMinutes(20)), "image_two" => Storage::disk('s3')->temporaryUrl(trim($product->image_two),Carbon::now()->addMinutes(20)), "image_three" => Storage::disk('s3')->temporaryUrl(trim($product->image_three),Carbon::now()->addMinutes(20)), "thumb_image_one" => ($product->thumb_image_one) ?Storage::disk('s3')->temporaryUrl(trim($product->thumb_image_one),Carbon::now()->addMinutes(20)):'', "thumb_image_two" => ($product->thumb_image_two)?Storage::disk('s3')->temporaryUrl(trim($product->thumb_image_two),Carbon::now()->addMinutes(20)):'', "thumb_image_three" => ($product->thumb_image_three)?Storage::disk('s3')->temporaryUrl(trim($product->thumb_image_three),Carbon::now()->addMinutes(20)):'');*/
            $response[] = array("value" => $product->id, "label" => $product->name, "image_one" => ($product->image_one)?Storage::disk('s3')->temporaryUrl(trim($product->image_one),Carbon::now()->addMinutes(20)):Storage::url('64x64.png'),
                "image_two" => ($product->image_two)?Storage::disk('s3')->temporaryUrl(trim($product->image_two),Carbon::now()->addMinutes(20)):Storage::url('64x64.png'),
                "image_three" =>($product->image_three)?Storage::disk('s3')->temporaryUrl(trim($product->image_three),Carbon::now()->addMinutes(20)):Storage::url('64x64.png'),
                "thumb_image_one" => ($product->thumb_image_one)?Storage::disk('s3')->temporaryUrl(trim($product->thumb_image_one),Carbon::now()->addMinutes(20)):Storage::url('64x64.png'),
                "thumb_image_two" => ($product->thumb_image_two)?Storage::disk('s3')->temporaryUrl(trim($product->thumb_image_two),Carbon::now()->addMinutes(20)):Storage::url('64x64.png'),
                "thumb_image_three" => ($product->thumb_image_three)?Storage::disk('s3')->temporaryUrl(trim($product->thumb_image_three),Carbon::now()->addMinutes(20)):Storage::url('64x64.png'),);
        }
        return $this->sendResponse($response, 'Product retrieved successfully');
    }

    public function productStore(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
        ]);

        $input['user_id'] = $this->logged_user->id;
        $input['company_id'] = ($this->company_id) ? $this->company_id : $this->logged_user->id;
        $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];

        if ($id == 0) {
            $validator = Validator::make($input, [
                'name' => 'required',
                'image_one' => 'required|image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
                'image_two' => 'required|image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
                'image_three' => 'required|image|mimes:jpeg,png,jpg,Jpeg,Png,Jpg,JPEG,PNG,JPG|max:1024',
            ]);
        }
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }


        if (Product::where('name', '=', $input['name'])->where('company_id', $input['company_id'])->where(function ($query) use ($id) {
            if ($id != 0) {
                $query->Where(function ($query) use ($id) {
                    $query->where('id', '!=', $id);
                });
            }
        })->first()) {
            return $this->sendError('Product exists', ['error' => 'Product exists'], 409);
        }
        if ($id == 0) {
            $activityLogMsg = 'Product created by ' . $this->logged_user->name;

            if ($request->file('image_one')) {

                $image = $request->file('image_one');
                $input['file'] = time() . '-1.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('/storage/uploads/thumbnail');
                $imgFile = Image::make($image->getRealPath());
                $imgFile->resize(null, 442, function ($constraint) { //412, 391        460, 442
                    $constraint->aspectRatio();
                })->save($destinationPath . '/' . $input['file']);

                $input['image_one'] = 'public/uploads/thumbnail/' . $input['file'];

                $path = storage_path('app/public/uploads/resize_image/' . $input['file']);

                Image::make($image->getRealPath())
                    ->resize(64, 64)
                    ->save($path);

                $input['thumb_image_one'] = 'public/uploads/resize_image/' . $input['file'];


                $old_path_one = url(Storage::url($input['image_one']));
                $path_one = 'public/'.$input['company_id'].'/products/thumbnail/' . $input['file'];
                Storage::disk('s3')->put($path_one, file_get_contents($old_path_one),'public');
                $publicUrl = Storage::disk('s3')->url($path_one);

                $old_thumb_path_one = url(Storage::url('public/uploads/resize_image/' . $input['file']));
                $thumb_path_one = 'public/'.$input['company_id'].'/products/resize_image/' . $input['file'];
                Storage::disk('s3')->put($thumb_path_one, file_get_contents($old_thumb_path_one),'public');
                $publicUrl = Storage::disk('s3')->url($thumb_path_one);

                $input['image_one'] = $path_one;
                $input['thumb_image_one'] = $thumb_path_one;
            }

            if ($request->file('image_two')) {
                $image = $request->file('image_two');
                $input['file'] = time() . '-2.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('/storage/uploads/thumbnail');
                $imgFile = Image::make($image->getRealPath());
                $imgFile->resize(null, 391, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPath . '/' . $input['file']);

                $input['image_two'] = 'public/uploads/thumbnail/' . $input['file'];

                $path = storage_path('app/public/uploads/resize_image/' . $input['file']);

                Image::make($image->getRealPath())
                    ->resize(64, 64)
                    ->save($path);

                $input['thumb_image_two'] = 'public/uploads/resize_image/' . $input['file'];


                $old_path_two = url(Storage::url($input['image_two']));
                $path_two = 'public/'.$input['company_id'].'/products/thumbnail/' . $input['file'];
                Storage::disk('s3')->put($path_two, file_get_contents($old_path_two),'public');
                $publicUrl = Storage::disk('s3')->url($path_two);

                $old_thumb_path_two = url(Storage::url('public/uploads/resize_image/' . $input['file']));
                $thumb_path_two = 'public/'.$input['company_id'].'/products/resize_image/' . $input['file'];
                Storage::disk('s3')->put($thumb_path_two, file_get_contents($old_thumb_path_two),'public');
                $publicUrl = Storage::disk('s3')->url($thumb_path_two);

                $input['image_two'] = $path_two;
                $input['thumb_image_two'] = $thumb_path_two;
            }

            if ($request->file('image_three')) {
                $image = $request->file('image_three');
                $input['file'] = time() . '-3.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('/storage/uploads/thumbnail');
                $imgFile = Image::make($image->getRealPath());
                $imgFile->resize(null, 391, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPath . '/' . $input['file']);

                $input['image_three'] = 'public/uploads/thumbnail/' . $input['file'];
                $path = storage_path('app/public/uploads/resize_image/' . $input['file']);

                Image::make($image->getRealPath())
                    ->resize(64, 64)
                    ->save($path);

                $input['thumb_image_three'] = 'public/uploads/resize_image/' . $input['file'];

                $old_path_three = url(Storage::url($input['image_three']));
                $path_three = 'public/'.$input['company_id'].'/products/thumbnail/' . $input['file'];
                Storage::disk('s3')->put($path_three, file_get_contents($old_path_three),'public');
                $publicUrl = Storage::disk('s3')->url($path_three);

                $old_thumb_path_three = url(Storage::url('public/uploads/resize_image/' . $input['file']));
                $thumb_path_three = 'public/'.$input['company_id'].'/products/resize_image/' . $input['file'];
                Storage::disk('s3')->put($thumb_path_three, file_get_contents($old_thumb_path_three),'public');
                $publicUrl = Storage::disk('s3')->url($thumb_path_three);

                $input['image_three'] = $path_three;
                $input['thumb_image_three'] = $thumb_path_three;
            }
            $product = Product::create($input);
        } else {
            $productData = Product::find($id);

            if ($request->hasFile('image_two')) {
                $request->validate([
                    'image_one' => 'required|image|mimes:jpeg,png,jpg|max:1024',
                ]);

                if (Storage::exists($productData->image_one)) {
                    Storage::delete($productData->image_one);
                }
                $image = $request->file('image_one');
                $input['file'] = time() . '-1.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('/storage/uploads/thumbnail');
                $imgFile = Image::make($image->getRealPath());
                $imgFile->resize(null, 391, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPath . '/' . $input['file']);

                $input['image_one'] = 'public/uploads/thumbnail/' . $input['file'];


            }

            if ($request->hasFile('image_two')) {
                $request->validate([
                    'image_two' => 'required|image|mimes:jpeg,png,jpg|max:1024',
                ]);
                if (Storage::exists($productData->image_two)) {
                    Storage::delete($productData->image_two);
                }
                $image = $request->file('image_two');
                $input['file'] = time() . '-2.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('/storage/uploads/thumbnail');
                $imgFile = Image::make($image->getRealPath());
                $imgFile->resize(null, 391, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPath . '/' . $input['file']);

                $input['image_two'] = 'public/uploads/thumbnail/' . $input['file'];
            }

            if ($request->hasFile('image_three')) {
                $request->validate([
                    'image_three' => 'required|image|mimes:jpeg,png,jpg|max:1024',
                ]);
                if (Storage::exists($productData->image_three)) {
                    Storage::delete($productData->image_three);
                }
                $image = $request->file('image_three');
                $input['file'] = time() . '-3.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('/storage/uploads/thumbnail');
                $imgFile = Image::make($image->getRealPath());
                $imgFile->resize(null, 391, function ($constraint) { //412, 391        460, 442
                    $constraint->aspectRatio();
                })->save($destinationPath . '/' . $input['file']);

                $input['image_three'] = 'public/uploads/thumbnail/' . $input['file'];
            }
            $product = Product::find($id)->update($input);
            $activityLogMsg = 'Product updated by ' . $this->logged_user->name;
        }

        // Add activity logs
        $input['id'] = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
        LogActivity::addToLog($activityLogMsg, $input);
        return $this->sendResponse([], 'Product Saved');
    }

    public function getFollowUpByEstimateId($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation error', ['error' => $validator->errors()->all()], 400);
        }
        $event = DB::table('events')
            ->leftjoin('estimates', 'events.estimate_id', 'estimates.id')
            ->leftJoin('users', 'events.user_id', 'users.id')
            ->where([
                ['events.estimate_id', '=', $id],
                ['events.event_type', '=', 'estimate'],
            ])
            ->select('events.*', 'estimates.estimate_no as estimate_no', DB::raw("DATE_FORMAT(events.start_date, '%d-%m-%Y') as display_date"), DB::raw("DATE_FORMAT(events.start_date, '%d-%m-%Y %H:%i:%s') as start_date"), 'estimates.customer_name', DB::raw("RIGHT(estimates.customer_address, 10) as mobile_no"), 'estimates.status', 'users.name as user_name')
            ->orderBy('events.id', 'DESC')
            ->orderBy('events.start_date', 'DESC')
            ->get();

        if (is_null($event)) {
            return $this->sendError('Follow up not found', ['Follow up not found'], 422);
        }
        return $this->sendResponse($event, 'Follow up retrieved successfully');
    }

    public function getEstimateList($fil_user_id, $status, $date,$start,$rowperpage, $orderBy, $search = null)
    {
        if($orderBy==0){
            $order_by = 'e.id';
            $order_by_name = 'desc';
        }

        if($orderBy==1){
            $order_by = 'ct.updated_at';
            $order_by_name = 'desc';
        }

        if($orderBy==2){
            $order_by = 'c.name';
            $order_by_name = 'asc';
        }
        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');

        $data = DB::table('estimates as e')
            ->select(
                'e.id',
                'e.customer_id',
                'e.customer_name',
                'e.estimate_no',
                'e.estimate_date',
                'e.addless_amount',
                'e.net_amount',
                'e.est_currency_id',
                DB::raw("RIGHT(e.customer_address, 10) as mobile_no"),
                'e.status',
                'u.name as created_by',
                'e.estimate_version',
                'ct.id as last_activity_id',
                'ct.updated_at as last_updated_at',
                'ct.follow_up_datetime as last_follow_up_datetime',
                'u2.name as assigned_user_name',
                'c.currency_name_country_id'
            )
            ->leftJoinSub(function ($query) {
                $query->select('estimate_id', DB::raw('MAX(id) as max_id'))
                    ->from('customer_timelines')
                    ->where('activity_type', 5)
                    ->groupBy('estimate_id');
            }, 'sub', 'e.id', '=', 'sub.estimate_id')
            ->leftJoin('customer_timelines as ct', 'sub.max_id', '=', 'ct.id')
            ->leftJoin('users as u', 'e.sales_person_id', '=', 'u.id')
            ->leftJoin('customers as c', 'e.customer_id', '=', 'c.id')
            ->leftJoin('users as u2', 'c.assigned_to_user', '=', 'u2.id')
            ->where(function($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('c.assigned_to_user', '=', $this->logged_user->id)
                        ->orWhere('c.user_id', '=', $this->logged_user->id);
                }

                /*if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm)) {
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
                }*/
            })
            ->where(function ($query) use ($fil_user_id) {
                if ($fil_user_id > 0) {
                    $query->where('e.sales_person_id', '=', $fil_user_id);
                }
            })
            ->where(function ($query) use ($status) {
                if ($status != 'All') {
                    $query->where('e.status', '=', $status);
                }
            })
            ->where(function ($query) use ($search) {
                if ($search != '') {
                    $query->where('e.id', '=',$search);
                }
            })
            ->where('e.company_id', $this->company_id)
            ->orderBy($order_by, $order_by_name)
            ->offset($start)
            ->limit($rowperpage)
            /*->skip($start)
            ->take($rowperpage)*/
            ->get();

        if (is_null($data)) {
            return $this->sendError('Estimate not found', ['Estimate not found'], 422);
        }
        foreach ($data as $key => $val) {
            $country_data = [];
            if($data[$key]->currency_name_country_id)
                $country_data = Country::where("id", $data[$key]->currency_name_country_id)->select('name','currency_name','currency_code','currency_symbol')->orderBy('id', 'DESC')->get()->first();

            if ($data[$key]->est_currency_id)
                $country_data = Country::where("id", $data[$key]->est_currency_id)->select('name', 'currency_name', 'currency_code','currency_symbol')->first();

            $data[$key]->currency_symbol = (isset($country_data->currency_symbol)) ? $country_data->currency_symbol :'';
        }
        return $this->sendResponse($data, 'Estimaste retrieved successfully');
    }
//    public function getEstimateList($assign_user, $status, $date, $search = null)
//    public function getEstimateList($assign_user, $status, $date,$start,$rowperpage, $orderBy, $search = null) latest
//    {
//        if($orderBy==0){
//            $order_by = 'estimates.id';
//            $order_by_name = 'desc';
//        }
//
//        if($orderBy==1){
//            $order_by = 'customer_timelines.updated_at';
//            $order_by_name = 'desc';
//        }
//
//        if($orderBy==2){
//            $order_by = 'c.name';
//            $order_by_name = 'asc';
//        }
//        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
//
//        $data = DB::table('estimates as e')
//            ->select(
//                'e.id',
//                'e.customer_id',
//                'e.customer_name',
//                'e.estimate_no',
//                'e.estimate_date',
//                'e.addless_amount',
//                'e.net_amount',
//                DB::raw("RIGHT(e.customer_address, 10) as mobile_no"),
//                'e.status',
//                'u.name as created_by',
//                'e.estimate_version',
//                'ct.id as last_activity_id',
//                'ct.updated_at as last_updated_at',
//                'ct.follow_up_datetime as last_follow_up_datetime',
//                'u2.name as assigned_user_name'
//            )
//            ->leftJoinSub(function ($query) {
//                $query->select('estimate_id', DB::raw('MAX(id) as max_id'))
//                    ->from('customer_timelines')
//                    ->where('activity_type', 5)
//                    ->groupBy('estimate_id');
//            }, 'sub', 'e.id', '=', 'sub.estimate_id')
//            ->leftJoin('customer_timelines as ct', 'sub.max_id', '=', 'ct.id')
//            ->leftJoin('users as u', 'e.sales_person_id', '=', 'u.id')
//            ->leftJoin('customers as c', 'e.customer_id', '=', 'c.id')
//            ->leftJoin('users as u2', 'c.assigned_to_user', '=', 'u2.id')
//            ->where(function($query) use ($user_perm) {
//                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
//                    $query->where('c.assigned_to_user', '=', $this->logged_user->id)
//                        ->orWhere('c.user_id', '=', $this->logged_user->id);
//                }
//
//                /*if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm)) {
//                    if (in_array('give-access-to-attend-unassigned-leads', $user_perm)) {
//
//                        $query->where('cv.company_id', $this->company_id);
//                        $query->orwhere('cv.assigned_to_user', '=', 0);
//                    } else {
//                        $query->where('cv.assigned_to_user', '!=', 0);
//                    }
//                } else {
//                    if (in_array('give-access-to-attend-unassigned-leads', $user_perm)) {
//                        $query->orwhere('cv.assigned_to_user', '=', 0);
//                    }
//                }*/
//            })
//            ->where(function ($query) use ($status) {
//                if ($status != 'All') {
//                    $query->where('estimates.status', '=', $status);
//                }
//            })
//            ->where(function ($query) use ($search) {
//                if ($search != '') {
//                    $query->where('estimates.id', '=',$search);
//                }
//            })
//            ->where('estimates.company_id', $this->company_id)
//            ->orderBy($order_by, $order_by_name)
//            ->offset($start)
//            ->limit($rowperpage)
//            /*->skip($start)
//            ->take($rowperpage)*/
//            ->get();
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
//
//
////        $subquery = DB::table('customer_timelines')
////            ->select('estimate_id', DB::raw('MAX(id) as max_id'))
////            ->where('activity_type','=',5)
////            ->groupBy('estimate_id');
////
////        $data = DB::table('estimates')
////            ->leftJoin('customer_timelines', function($join) use ($subquery) {
////                $join->on('estimates.id', '=', 'customer_timelines.estimate_id')
////                    ->whereIn('customer_timelines.id', function($query) use ($subquery) {
////                        $query->select('max_id')
////                            ->fromSub($subquery, 'sub');
////                    });
////            })
////            ->leftJoin('users', 'estimates.sales_person_id', '=', 'users.id')
////            ->leftJoin('customers as c', function($join) use ($user_perm) {
////                $join->on('estimates.customer_id', '=', 'c.id');
////            })
////            ->leftJoin('users as u', 'c.assigned_to_user', '=', 'u.id')
////            ->select('estimates.id','estimates.customer_id', 'estimates.customer_name', 'estimates.estimate_no', 'estimates.estimate_date', 'estimates.addless_amount', 'estimates.net_amount', DB::raw("RIGHT(estimates.customer_address, 10) as mobile_no"), "estimates.status", "users.name as created_by","estimate_version", 'customer_timelines.id as last_activity_id','customer_timelines.updated_at as last_updated_at','customer_timelines.follow_up_datetime as last_follow_up_datetime','u.name as assigned_user_name')
////            ->where(function($query) use ($user_perm) {
////                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
////                    $query->where('c.assigned_to_user', '=', $this->logged_user->id)
////                        ->orWhere('c.user_id', '=', $this->logged_user->id);
////                }
////
////                /*if (in_array('access-all-lead-and-assign-to-anyone-in-team', $user_perm)) {
////                    if (in_array('give-access-to-attend-unassigned-leads', $user_perm)) {
////
////                        $query->where('cv.company_id', $this->company_id);
////                        $query->orwhere('cv.assigned_to_user', '=', 0);
////                    } else {
////                        $query->where('cv.assigned_to_user', '!=', 0);
////                    }
////                } else {
////                    if (in_array('give-access-to-attend-unassigned-leads', $user_perm)) {
////                        $query->orwhere('cv.assigned_to_user', '=', 0);
////                    }
////                }*/
////            })
////            ->where(function ($query) use ($status) {
////                if ($status != 'All') {
////                    $query->where('estimates.status', '=', $status);
////                }
////            })
////            ->where(function ($query) use ($search) {
////                if ($search != '') {
////                    $query->where('estimates.id', '=',$search);
////                }
////            })
////            ->where('estimates.company_id', $this->company_id)
////            ->orderBy($order_by, $order_by_name)
////            ->skip($start)
////            ->take($rowperpage)
////            ->get();
////        dd(DB::getQueryLog($data));
//
////dd(DB::getQueryLog($data));
//
//        /*$data = DB::table('estimates')
//            ->leftJoin('users', 'estimates.sales_person_id', '=', 'users.id')
//            ->leftJoin('customers_views', 'estimates.customers', '=', 'customers_views.id')
//            ->where(function ($query) use ($date) {
//                $dateArr = explode("_", $date);
//                $query->whereBetween(DB::raw("DATE_FORMAT(estimates.estimate_date, '%Y-%m-%d')"), [$dateArr[0], $dateArr[1]]);
//            })
//            ->where('estimates.company_id', $this->company_id)
//            ->where(function ($query) use ($assign_user) {
//                $query->whereRaw('estimates.user_id IN  (' . $assign_user . ')');
//                $query->orWhere('estimates.user_id', $this->logged_user->id);
//            })
//            ->where(function ($query) use ($status) {
//                if ($status != 'All') {
//                    $query->where('estimates.status', '=', $status);
//                }
//            })
//            ->where(function ($query) use ($search, $assign_user) {
//                if ($search != '') {
//                    $query->where('estimates.customer_name', 'like', '%' . $search . '%');
//                    $query->orWhere('estimates.estimate_no', 'like', '%' . $search . '%');
//                }
//            })
//            ->select(array('estimates.id', 'estimates.customer_name', 'estimates.estimate_no', 'estimates.estimate_date', 'estimates.addless_amount', 'estimates.net_amount', DB::raw("RIGHT(estimates.customer_address, 10) as mobile_no"), "estimates.status", "users.name as created_by","estimate_version"))
//            ->orderBy("estimates.id", 'desc')
//            ->get();*/
//        if (is_null($data)) {
//            return $this->sendError('Estimate not found', ['Estimate not found'], 422);
//        }
//        return $this->sendResponse($data, 'Estimaste retrieved successfully');
//    }

    public function getGenerateLink($id)
    {
        $isExist = Estimate::query()->where('id', $id)->first();
        if (!$isExist)
            return $this->sendError('Id not found', [], 400);

        $id = ($id) ? Crypt::encrypt($id) : $id;
        return $this->sendResponse(["url" => url('/quotes/generate-link/' . $id)], 'Link generated successfully');
    }

    public function postEstimateDuplicate(Request $request)
    {
        $input = $request->all();
        $isExist = Estimate::query()->where([["id", "=", $input['id']], ["company_id", "=", $this->company_id]])->first();
        if (!$isExist)
            return $this->sendError('Id not found', [], 400);

        $estimate_auto_number = DB::table('estimate_auto_numbers')->where('company_id', $this->company_id)
            ->select('estimate_prefix', 'estimate_next_no')
            ->first();
        $estimate_no = $estimate_auto_number->estimate_prefix . $estimate_auto_number->estimate_next_no;

        //            $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
        $id = $input['id'];
        $estimate = Estimate::query()->where("id", $id)->first();
        $newEstimate = $estimate->replicate();
        $newEstimate->estimate_no = $estimate_no;
        $newEstimate->status = 'Draft';
        $newEstimate->save();
        $insert_id = $newEstimate->id;
        $estimateItems = EstimateItems::where('estimate_id', $id)->get();

        foreach ($estimateItems as $estimateItems) {
            $newEstimateItems = $estimateItems->replicate();
            $newEstimateItems->estimate_id = $insert_id;
            $newEstimateItems->save();
        }

        $tmp_est_no = $estimate_auto_number->estimate_next_no + 1;
        EstimateAutoNumber::where('company_id', $this->company_id)->update(array('estimate_next_no' => '00' . $tmp_est_no));
        return $this->sendResponse(['estimate_id' => $insert_id], 'Estimate Copied!');
    }

    public function postEstimateDelete(Request $request)
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
            $id[] = $value; // $id[] = Crypt::decrypt($value);
        }
        Event::whereIn('estimate_id', $id)->delete();
        //        SalesPersonPerformances::whereIn('estimate_id', $id)->delete();

        $estimate = Estimate::where('id',$id[0])->get()->first();

        $pdf_name = $estimate['estimate_no'];
        if ($estimate['estimate_version'] > 0) {
            $pdf_name = $estimate['estimate_no'] . '-V' . $estimate['estimate_version'];
            $paramArr['internal_remarks'] = "Estimate updated";
        }
        $customerData = Customer::select("assigned_to_user")->where('id', '=', $estimate['customer_id'])->first();
        $customer_view_data = ViewCustomerData::select("last_follow_up_datetime")->where('id', '=', $estimate['customer_id'])->first();
        Event::where('estimate_id', $id[0])->delete();
        //           SalesPersonPerformances::whereIn('estimate_id', $id)->delete();
        $unit = Estimate::where('id', $id[0])->delete();

        $paramArr['internal_remarks'] = "Estimate Deleted";
        $paramArr['assigned_to'] = $customerData->assigned_to_user;
        $paramArr['customer_id'] = $estimate['customer_id'];
        $paramArr['estimate_id'] = $id[0];
        $paramArr['activity_type'] = 11;
        $paramArr['estimate_version_no'] = $pdf_name;
        $paramArr['activity_name'] = 'Estimate';
        $paramArr['activity_notes'] = 'Estimate Deleted';
        $paramArr['entry_type'] = 'estimate';
        $paramArr['is_modified'] = 0;
        $paramArr['user_id'] = $this->logged_user->id;
        $paramArr['company_id'] = $this->company_id;
        $paramArr['created_by'] = $this->logged_user->id;
        $paramArr['updated_by'] = $this->logged_user->id;
        $paramArr['net_amount'] = $estimate['net_amount'];
        $paramArr['follow_up_datetime'] = $customer_view_data->last_follow_up_datetime;
        EstimateTimeline::create($paramArr);

        Estimate::where('id', $id[0])->delete();

        $tenantdata = DB::connection('mysql')->table('tenants')->where('id', $this->logged_user->id)->first();
        $tcompany_id = ($tenantdata->company_id) ? $tenantdata->company_id : $tenantdata->id;
        $leadhistory = EstimateHistory::where('estimate_id', $id[0])->where('company_id', $tcompany_id)->delete();
        return $this->sendResponse([], 'Estimate Deleted!');
        /*else
            return $this->sendError("Error in remove estimate", ["error" => "Error in remove estimate"], 400);*/
    }

    public function postEstimateCreate(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'customer_name' => 'required',
            'customer_id' => 'required',
            'customer_state_id' => 'required',
            'estimate_no' => 'required',
            'estimate_date' => 'required',
            'subtotal' => 'required',
            'net_amount' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError("Validation Error", ["error" => $validator->errors()->all()], 400);
        }

        if (Estimate::where('estimate_no', '=', $input['estimate_no'])->where('company_id', $this->company_id)->first()) {
            return $this->sendError("Estimate exists", ["error" => "Estimate exists"], 400);
        }

        $proposal_template = ProposalTemplates::where('company_id', $this->company_id)->first();

        $data = array();
        $data['customer_name'] = $input['customer_name'];
        $data['customer_address'] = $input['customer_address'];
        $data['customer_id'] = $input['customer_id'];
        $data['customer_state_id'] = $input['customer_state_id'];
        //            $data['company_state_id'] = $input['company_state_id'];
        $data['estimate_no'] = $input['estimate_no'];
        $data['reference'] = $input['reference'];
        $data['estimate_date'] = Carbon::createFromFormat('d/m/Y', $input['estimate_date'])->format('Y-m-d');
        $data['expiry_date'] = Carbon::createFromFormat('d/m/Y', $input['expiry_date'])->format('Y-m-d');
        $data['subtotal'] = $input['subtotal'];
        $data['total_cgst_amount'] = $input['total_cgst_amount'];
        $data['total_sgst_amount'] = $input['total_sgst_amount'];
        $data['total_igst_amount'] = $input['total_igst_amount'];
        $data['addless_amount'] = 0 + $input['addless_amount'];
        $data['addless_title'] = $input['addless_title'];
        $data['net_amount'] = $input['net_amount'];
        $data['company_id'] = $this->company_id;
        $data['sales_person_id'] = $this->logged_user->id;
        $data['user_id'] = $input['user_id'];
        $data['item_rate_are'] = $input['item_rate_are'];
        $data['customer_notes'] = $input['customer_notes'];
        $data['term_condition'] = $input['term_condition'];
        $data['term_condition_id'] = $input['term_condition_id'];
        $data['est_currency_id'] = $input['est_currency_id'];

        $data['est_cover_page_title'] = $proposal_template->cover_title;
        $data['est_cover_page_content'] = $proposal_template->cover_content;
        $data['est_cover_page_footer_one'] = $proposal_template->cover_footer_one;
        $data['est_cover_page_footer_two'] = $proposal_template->cover_footer_two;
        $data['est_aboutus_title'] = $proposal_template->aboutas_title;
        $data['est_aboutus_content'] = $proposal_template->aboutas_content;
        $data['est_term_condition_title'] = $proposal_template->terms_title;
        $term_condition_data = TermCondition::where("id", $input['term_condition_id'])->orderBy('id', 'ASC')->first();
        $data['est_term_condition_content'] = ($term_condition_data)?$term_condition_data->description:'';

        $data['est_cover_page_title_div'] = $proposal_template->cover_title;
        $data['est_cover_page_content_div'] = $proposal_template->cover_content;
        preg_match_all('#\${(.*?)\}#', strip_tags($data['est_cover_page_content_div']), $match);
        foreach ($match[1] as $key => $value) {
            $valueArr = explode('.', $value);
            if ($valueArr[0] == 'customers') {
                $id = $data['customer_id'];
                $table = 'customers_views';
            }

            if ($valueArr[0] == 'companies') {
                $table = 'users_views';
            }

            if ($valueArr[0] == 'estimates') {
                $id = $data['estimate_id'];
            }
            $result = DB::table($table)
                ->where('id', $id)
                ->select([$valueArr[1]])
                ->get()->first();
            $a = $valueArr[1];

            $data['est_cover_page_content_div'] = str_replace('${' . $value . '}', $result->$a, $data['est_cover_page_content_div']);
        }
        $data['est_cover_page_footer_one_div'] = $proposal_template->cover_footer_one;
        $data['est_cover_page_footer_two_div'] = $proposal_template->cover_footer_two;
        $data['est_aboutus_title_div'] = $proposal_template->aboutas_title;
        $data['est_aboutus_content_div'] = $proposal_template->aboutas_content;
        preg_match_all('#\${(.*?)\}#', strip_tags($data['est_aboutus_content_div']), $matchAbs);
        foreach ($matchAbs[1] as $key => $value) {
            $valueArr = explode('.', $value);
            //                if ($valueArr[0] == 'customers') {
            //                    $id = $data['customer_id'];
            //                    $table = 'customers_views';
            //                }

            if ($valueArr[0] == 'companies') {
                $table = 'users_views';
                $id = $this->company_id;
            }

            //                if ($valueArr[0] == 'estimates') {
            //                    $id = $data['estimate_id'];
            //                }
            $result = DB::table($table)
                ->where('id', $id)
                ->select([$valueArr[1]])
                ->get()->first();
            $a = $valueArr[1];
            $data['est_aboutus_content_div'] = str_replace('${' . $value . '}', $result->$a, $data['est_aboutus_content_div']);
        }
        $data['est_term_condition_title_div'] = $proposal_template->terms_title;
        $data['est_term_condition_content_div'] = $proposal_template->terms_content;

        $data['testimonial_id'] = $input['testimonial_id'];
        $data['product_id'] = $input['product_id'];
        $data['pdf_cover_page_flg'] = $proposal_template->cover_page_flg;
        $data['pdf_about_us_flg'] = $proposal_template->about_us_flg;
        // $data['pdf_thank_you_flg'] = $proposal_template->thank_you_flg;
        $data['pdf_product_flg'] = $input['pdf_product_flg'];
        $data['pdf_est_flg'] = 1;
        $data['pdf_terms_flg'] = $input['pdf_terms_flg'];
        // $data['pdf_thank_you_flg'] = 1;
        $data['pdf_testimonial_flg'] = $input['pdf_testimonial_flg'];
        $data['status'] = 'Draft';
        $data['tilt'] = $input['tilt'];
        $data['azumuth'] = $input['azumuth'];
        $data['no_of_panel'] = $input['no_of_panel'];
        $data['panel_wattage'] = $input['panel_wattage'];
        $data['estimate_version'] = 0;

        $estimate = Estimate::create($data);
        $insert_id = $estimate->id;
        if ($insert_id) {
            $user = User::where('id', $input['user_id'])->first();
            $tenantdata = DB::connection('mysql')->table('tenants')->where('user_id', $input['user_id'])->first();
            $tcompany_id = ($tenantdata->company_id) ? $tenantdata->company_id : $tenantdata->id;
            $hdata['estimate_id'] = $insert_id;
            $hdata['user_id'] = $input['user_id'];
            $hdata['company_id'] = $tcompany_id;
            $hdata['email'] = $user->email;
            $hdata['domain'] = $user->domain;
            $leadhistory = EstimateHistory::create($hdata);

            $estimateArr = $input["items"];
            foreach ($estimateArr as $key => $csm) {
                $estimateArr[$key]['estimate_id'] = $insert_id;
                $estimateArr[$key]['company_id'] = $this->company_id;
                $estimateArr[$key]['user_id'] = $this->logged_user->id;
                /*if(isset($csm['item_technical_specification'])){
                    $estimateArr[$key]['technical_specification'] = $csm['item_technical_specification'];
                    unset($estimateArr[$key]['item_technical_specification']);
                }*/
            }
            EstimateItems::insert($estimateArr);
        }

        $update_estimate = Estimate::query()->select(['est_cover_page_footer_one_div', 'est_cover_page_footer_two_div'])->where('id', '=', $insert_id)->first()->toArray();

        preg_match_all('#\${(.*?)\}#', strip_tags($update_estimate['est_cover_page_footer_one_div']), $match);
        foreach ($match[1] as $key => $value) {
            $valueArr = explode('.', $value);
            $table = $valueArr[0];
            if ($valueArr[0] == 'companies') {
                $tmpId = $this->company_id;
                $table = 'users_views';
            }

            $result = DB::table($table)
                ->where('id', $tmpId)
                ->select([$valueArr[1]])
                ->get()->first();
            $a = $valueArr[1];

            $field_name = '';
            if(!empty($result->$a))
                $field_name = $result->$a;
            $update_estimate['est_cover_page_footer_one_div'] = str_replace('${' . $value . '}', $result->$a, $update_estimate['est_cover_page_footer_one_div']);
        }

        preg_match_all('#\${(.*?)\}#', strip_tags($update_estimate['est_cover_page_footer_two_div']), $match);
        foreach ($match[1] as $key => $value) {
            $valueArr = explode('.', $value);
            $table = $valueArr[0];
            if ($valueArr[0] == 'estimates') {
                $tmpId = $insert_id;
            }

            if ($valueArr[1] == 'sales_person_id') {
                $table = 'users_views';
                $tmpId = $this->logged_user->id;
                $valueArr[1] = 'name';
            }

            if ($valueArr[0] == 'customers') {
                $tmpId = $data['customer_id'];
                $table = 'customers_views';
                $valueArr[1] = 'lead_category';
            }

            $result = DB::table($table)
                ->where('id', $tmpId)
                ->select([$valueArr[1]])
                ->get()->first();
            $a = $valueArr[1];

            if($valueArr[1]=='estimate_date'){
                $result->$a = Carbon::createFromFormat('Y-m-d', $result->$a)->format('j F, Y');
            }
            $update_estimate['est_cover_page_footer_two_div'] = str_replace('${' . $value . '}', $result->$a, $update_estimate['est_cover_page_footer_two_div']);
        }

        Estimate::where('id', $insert_id)->update(array('est_cover_page_footer_one_div' => $update_estimate['est_cover_page_footer_one_div'], 'est_cover_page_footer_two_div' => $update_estimate['est_cover_page_footer_two_div']));

        $estimate_auto_number = EstimateAutoNumber::where('company_id', $this->company_id)
            ->select('estimate_prefix', 'estimate_next_no')
            ->get()->first();

        $tmp_est_no = $estimate_auto_number->estimate_prefix . $estimate_auto_number->estimate_next_no;
        // $cleanedStr = 0+preg_replace("/[^0-9]/", "", $input['estimate_no']);
        $explodeEst = explode("-",$input['estimate_no']);

        if(is_array($explodeEst)){
            $lastElement = end($explodeEst);
            $cleanedStr = 0+preg_replace("/[^0-9]/", "", $lastElement);
        }else{
            $cleanedStr = 0+preg_replace("/[^0-9]/", "", $input['estimate_no']);
        }
        if ($cleanedStr >= 0+($estimate_auto_number->estimate_next_no)) {
            $tmp_est_no = $cleanedStr+1;
            EstimateAutoNumber::where('company_id', $this->company_id)->update(array('estimate_next_no' => '00' . $tmp_est_no));
        }
        /*if ($tmp_est_no == $input['estimate_no']) {
            $tmp_est_no = $estimate_auto_number->estimate_next_no + 1;

            EstimateAutoNumber::where('company_id', $this->company_id)->update(array('estimate_next_no' => '00' . $tmp_est_no));
        }*/

        $proposal_template_temp = ProposalTemplates::where('company_id', $this->company_id)->select('new_pdf_flag')->first();

        if($proposal_template_temp->new_pdf_flag==0)
            $this->estimateGeneratePdf($insert_id);
        if($proposal_template_temp->new_pdf_flag ==1)
            $this->estimateGeneratemPdf($insert_id);
        // 


        return $this->sendResponse(["estimate_id" => $insert_id], 'Estimate Saved!');
    }

    public function postEstimateUpdate(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'id' => 'required',
            'customer_name' => 'required',
            'customer_id' => 'required',
            'customer_state_id' => 'required',
            'estimate_no' => 'required',
            'estimate_date' => 'required',
            'subtotal' => 'required',
            'net_amount' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError("Validation Error", ["error" => $validator->errors()->all()], 400);
        }

        $id = $input['id'];
        $user = Auth::user();
        $company_id = ($user->company_id) ? $user->company_id : $user->id;

        $planDetails = User::query()->select('plan_id')->where("id",$company_id)->first();

        $estimate = Estimate::where([["id", $id], ["company_id", "=", $company_id]])->get()->first();
        if (!$estimate) {
            return redirect()->back()->withInput();
        }
        $plan = PlanHistory::where([['user_id', $company_id], ['status', 1]])->first();
        $status = 0;
        if (isset($plan->start_date) && isset($plan->end_date)) {
            $dateS =  \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $plan->start_date);
            $dateE = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $plan->end_date);
//            $estimates = Estimate::where("company_id", $company_id)->whereBetween('created_at', [$dateS, $dateE])->get();
            $estimates = Estimate::where("company_id", $company_id)->orderby('created_at','desc')->take(10)->get();
            foreach ($estimates as $key => $value) {
                if ($value->id == $id) {
                    $status = 1;
                    break;
                } else {
                    $status = 0;
                }
            }
        }
        /* if ($status == 0 && $user->plan_id==2) {
             $plan = PlanHistory::where([['user_id', $company_id], ['status', 0]])->latest()->first();
             if (isset($plan->start_date) && isset($plan->end_date)) {
                 $dateS = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $plan->start_date);
                 $dateE = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $plan->end_date);

                 $newEstimates = Estimate::where("company_id", $company_id)->whereBetween('created_at', [$dateS, $dateE])->orderBy('created_at', 'DESC')->get();

                 foreach ($newEstimates as $key => $value) {

                     if ($value->id == $id) {
                         $status = 1;
                     }
                     if ($key == 9) {
                         break;
                     }
                 }
             } else {
                 $plan = 1;
             }
         }*/
        if ($status == 0 && $planDetails->plan_id==1) {
            return $this->sendError("Estimate Not Editable", ["error" => "You Are Not Editable to this record..."], 312);
        }
        $old_est = Estimate::where('id', '=', $id)->where([['id', '=', $id],['company_id',"=", $company_id]])->select(['estimate_version'])->first();
        if (Estimate::where([['estimate_no', '=', $input['estimate_no']], ["estimate_version","=",$old_est->estimate_version], ['company_id', '=', $this->company_id]])->where(function ($query) use ($id) {
                if ($id != 0) {
                    $query->Where(function ($query) use ($id) {
                        $query->where('id', '!=', $id);
                    });
                }
            })->count() > 0) {
            return $this->sendError("Estimate exists", ["error" => "Estimate exists"], 400);
        }

        $proposal_template = ProposalTemplates::where('company_id', $this->company_id)->first();

        $data = array();
        $data['customer_name'] = $input['customer_name'];
        $data['customer_address'] = $input['customer_address'];
        $data['customer_id'] = $input['customer_id'];
        $data['customer_state_id'] = $input['customer_state_id'];
        //            $data['company_state_id'] = $input['company_state_id'];
        $data['estimate_no'] = $input['estimate_no'];
        $data['reference'] = $input['reference'];
        $data['estimate_date'] = Carbon::createFromFormat('d/m/Y', $input['estimate_date'])->format('Y-m-d');
        $data['expiry_date'] = Carbon::createFromFormat('d/m/Y', $input['expiry_date'])->format('Y-m-d');
        $data['subtotal'] = $input['subtotal'];
        $data['total_cgst_amount'] = $input['total_cgst_amount'];
        $data['total_sgst_amount'] = $input['total_sgst_amount'];
        $data['total_igst_amount'] = $input['total_igst_amount'];
        $data['addless_amount'] = 0 + $input['addless_amount'];
        $data['addless_title'] = $input['addless_title'];
        $data['net_amount'] = $input['net_amount'];
        $data['company_id'] = $this->company_id;
        $data['sales_person_id'] = $this->logged_user->id;
        $data['user_id'] = $input['user_id'];
        $data['item_rate_are'] = $input['item_rate_are'];
        $data['customer_notes'] = $input['customer_notes'];
        $data['term_condition'] = $input['term_condition'];
        $data['term_condition_id'] = $input['term_condition_id'];
        $data['est_currency_id'] = $input['est_currency_id'];

        $data['est_cover_page_title'] = $proposal_template->cover_title;
        $data['est_cover_page_content'] = $proposal_template->cover_content;
        $data['est_cover_page_footer_one'] = $proposal_template->cover_footer_one;
        $data['est_cover_page_footer_two'] = $proposal_template->cover_footer_two;
        $data['est_aboutus_title'] = $proposal_template->aboutas_title;
        $data['est_aboutus_content'] = $proposal_template->aboutas_content;
        $data['est_term_condition_title'] = $proposal_template->terms_title;
        $term_condition_data = TermCondition::where("id", $input['term_condition_id'])->orderBy('id', 'ASC')->first();
        $data['est_term_condition_content'] = ($term_condition_data)?$term_condition_data->description:'';
//        $data['est_term_condition_content'] = $proposal_template->terms_content;

        $data['est_cover_page_title_div'] = $proposal_template->cover_title;
        $data['est_cover_page_content_div'] = $proposal_template->cover_content;
        preg_match_all('#\${(.*?)\}#', strip_tags($data['est_cover_page_content_div']), $match);
        foreach ($match[1] as $key => $value) {
            $valueArr = explode('.', $value);
            if ($valueArr[0] == 'customers') {
                $common_id = $data['customer_id'];
                $table = 'customers_views';
            }

            if ($valueArr[0] == 'companies') {
                $table = 'users_views';
            }

            if ($valueArr[0] == 'estimates') {
                $common_id = $data['estimate_id'];
            }
            $result = DB::table($table)
                ->where('id', $common_id)
                ->select([$valueArr[1]])
                ->get()->first();
            $a = $valueArr[1];

            $data['est_cover_page_content_div'] = str_replace('${' . $value . '}', $result->$a, $data['est_cover_page_content_div']);
        }
        $data['est_cover_page_footer_one_div'] = $proposal_template->cover_footer_one;
        $data['est_cover_page_footer_two_div'] = $proposal_template->cover_footer_two;
        $data['est_aboutus_title_div'] = $proposal_template->aboutas_title;
        $data['est_aboutus_content_div'] = $proposal_template->aboutas_content;
        preg_match_all('#\${(.*?)\}#', strip_tags($data['est_aboutus_content_div']), $matchAbs);
        foreach ($matchAbs[1] as $key => $value) {
            $valueArr = explode('.', $value);
            //                if ($valueArr[0] == 'customers') {
            //                    $id = $data['customer_id'];
            //                    $table = 'customers_views';
            //                }

            if ($valueArr[0] == 'companies') {
                $table = 'users_views';
                $id = $this->company_id;
            }

            //                if ($valueArr[0] == 'estimates') {
            //                    $id = $data['estimate_id'];
            //                }
            $result = DB::table($table)
                ->where('id', $id)
                ->select([$valueArr[1]])
                ->get()->first();
            $a = $valueArr[1];
            $data['est_aboutus_content_div'] = str_replace('${' . $value . '}', $result->$a, $data['est_aboutus_content_div']);
        }
        $data['est_term_condition_title_div'] = $proposal_template->terms_title;
        $data['est_term_condition_content_div'] = $proposal_template->terms_content;

        $data['testimonial_id'] = $input['testimonial_id'];
        $data['product_id'] = $input['product_id'];
        /*$data['pdf_cover_page_flg'] = $input['pdf_cover_page_flg'];
        $data['pdf_about_us_flg'] = $input['pdf_about_us_flg'];*/
        $data['pdf_cover_page_flg'] = $proposal_template->cover_page_flg;
        $data['pdf_about_us_flg'] = $proposal_template->about_us_flg;
//        $data['pdf_thank_you_flg'] = $proposal_template->thank_you_flg;
        $data['pdf_product_flg'] = $input['pdf_product_flg'];
        $data['pdf_est_flg'] = $input['pdf_est_flg'];
        $data['pdf_terms_flg'] = $input['pdf_terms_flg'];
        //        $data['pdf_thank_you_flg'] = $input['pdf_thank_you_flg'];
        $data['pdf_testimonial_flg'] = $input['pdf_testimonial_flg'];
        /* $data['pdf_cover_page_flg'] = 1;
         $data['pdf_about_us_flg'] = 1;
         $data['pdf_product_flg'] = 1;
         $data['pdf_est_flg'] = 1;
         $data['pdf_terms_flg'] = 1;
         $data['pdf_thank_you_flg'] = 1;
         $data['pdf_testimonial_flg'] = 1;*/
        $data['tilt'] = $input['tilt'];
        $data['azumuth'] = $input['azumuth'];
        $data['no_of_panel'] = $input['no_of_panel'];
        $data['panel_wattage'] = $input['panel_wattage'];
        $estimate_tmp = Estimate::where([["estimate_no", $input['estimate_no']], ["company_id", "=", $this->company_id]])->orderby("id","desc")->first();
//        $estimate_tmp = Estimate::where([["id", $input['id']], ["company_id", "=", $this->company_id]])->get()->first();
        $new_estimate_version = $estimate_tmp['estimate_version'] + 1;
        $data['estimate_version'] = $new_estimate_version;

//        $estimate = Estimate::find($input['id'])->update($data);
        $estimate = Estimate::create($data);
        $input['id'] = $estimate->id;
        if ($estimate) {
            EstimateItems::where("estimate_id", $input['id'])->delete();
            $estimateArr = $input["items"];
            foreach ($estimateArr as $key => $csm) {
                $estimateArr[$key]['estimate_id'] = $input['id'];
                $estimateArr[$key]['company_id'] = $this->company_id;
                $estimateArr[$key]['user_id'] = $this->logged_user->id;
                /*if(isset($csm['item_technical_specification'])){
                    $estimateArr[$key]['technical_specification'] = $csm['item_technical_specification'];
                    $estimateArr[$key]['technical_specification'] = $csm['item_technical_specification'];
                    unset($estimateArr[$key]['item_technical_specification']);
                }*/
            }
            EstimateItems::insert($estimateArr);
        }

        $update_estimate = Estimate::query()->select(['est_cover_page_footer_one_div', 'est_cover_page_footer_two_div'])->where('id', '=', $input['id'])->first()->toArray();

        preg_match_all('#\${(.*?)\}#', strip_tags($update_estimate['est_cover_page_footer_one_div']), $match);
        foreach ($match[1] as $key => $value) {
            $valueArr = explode('.', $value);
            $table = $valueArr[0];
            if ($valueArr[0] == 'companies') {
                $tmpId = $this->company_id;
                $table = 'users_views';
            }
            if ($valueArr[1] == 'sales_person_id') {
                $table = 'users_views';
                $tmpId = $this->logged_user->id;
                $valueArr[1] = 'name';
            }

            $result = DB::table($table)
                ->where('id', $tmpId)
                ->select([$valueArr[1]])
                ->get()->first();
            $a = $valueArr[1];
            $update_estimate['est_cover_page_footer_one_div'] = str_replace('${' . $value . '}', $result->$a, $update_estimate['est_cover_page_footer_one_div']);
        }
        /*$estimate_tmp = Estimate::where([["estimate_no", $input['estimate_no']], ["company_id", "=", $company_id]])->orderby("id","desc")->first();
        $new_estimate_version = $estimate_tmp->estimate_version + 1;*/
        preg_match_all('#\${(.*?)\}#', strip_tags($update_estimate['est_cover_page_footer_two_div']), $match);
        foreach ($match[1] as $key => $value) {
            $valueArr = explode('.', $value);
            $table = $valueArr[0];
            if ($valueArr[0] == 'estimates') {
                $tmpId = $input['id'];
            }
            if ($valueArr[1] == 'sales_person_id') {
                $table = 'users_views';
                $tmpId = $this->logged_user->id;
                $valueArr[1] = 'name';
            }

            if ($valueArr[0] == 'customers') {
                $tmpId = $data['customer_id'];
                $table = 'customers_views';
                $valueArr[1] = 'lead_category';
            }

            $result = DB::table($table)
                ->where('id', $tmpId)
                ->select([$valueArr[1]])
                ->get()->first();
            $a = $valueArr[1];
            $estVar ='';
            if($valueArr[1]=='estimate_no'){
                $est_result = DB::table('estimates')
                    ->where('id', $tmpId)
                    ->select('estimate_version')->first();
                if($new_estimate_version > 0){
                    $estVar = '-V'.$new_estimate_version;
                }
            }

            if($valueArr[1]=='estimate_date'){
                $result->$a = Carbon::createFromFormat('Y-m-d', $result->$a)->format('j F, Y');
            }
            $update_estimate['est_cover_page_footer_two_div'] = str_replace('${' . $value . '}', $result->$a.$estVar, $update_estimate['est_cover_page_footer_two_div']);
//            $update_estimate['est_cover_page_footer_two_div'] = str_replace('${' . $value . '}', $result->$a, $update_estimate['est_cover_page_footer_two_div']);
        }

        Estimate::where('id', $input['id'])->update(array('est_cover_page_footer_one_div' => $update_estimate['est_cover_page_footer_one_div'], 'est_cover_page_footer_two_div' => $update_estimate['est_cover_page_footer_two_div']));

        $estimate_auto_number = EstimateAutoNumber::where('company_id', $this->company_id)
            ->select('estimate_prefix', 'estimate_next_no')
            ->get()->first();

        $tmp_est_no = $estimate_auto_number->estimate_prefix . $estimate_auto_number->estimate_next_no;

//        $cleanedStr = 0+preg_replace("/[^0-9]/", "", $input['estimate_no']);
        $explodeEst = explode("-",$input['estimate_no']);

        if(is_array($explodeEst)){
            $lastElement = end($explodeEst);
            $cleanedStr = 0+preg_replace("/[^0-9]/", "", $lastElement);
        }else{
            $cleanedStr = 0+preg_replace("/[^0-9]/", "", $input['estimate_no']);
        }
        if ($cleanedStr >= 0+($estimate_auto_number->estimate_next_no)) {
            $tmp_est_no = $cleanedStr+1;
            EstimateAutoNumber::where('company_id', $this->company_id)->update(array('estimate_next_no' => '00' . $tmp_est_no));
        }
        /*if ($tmp_est_no == $input['estimate_no']) {
            $tmp_est_no = $estimate_auto_number->estimate_next_no + 1;

            EstimateAutoNumber::where('company_id', $this->company_id)->update(array('estimate_next_no' => '00' . $tmp_est_no));
        }*/


        $tempEst = Estimate::where('estimate_no', '=', $input['estimate_no'])->where('company_id', $company_id)->where(function ($query) {
            /*if ($id != 0) {
                $query->Where(function ($query) use ($id) {
                    $query->where('id', '!=', $id);
                });
            }*/
        })->select("id","estimate_no")->get();
        if ($tempEst) {
            foreach($tempEst as $val_est){
                Estimate::where([['company_id','=', $company_id],["id","=",$val_est->id]])->update(array('status' => 'Inprogress'));
                EstimateTimeline::where('estimate_id', $val_est->id)->update(array('activity_estimate_status' => 'Inprogress'));
            }
        }

        $proposal_template_temp = ProposalTemplates::where('company_id', $company_id)->select('new_pdf_flag')->first();

        if($proposal_template_temp->new_pdf_flag==0)
            $this->estimateGeneratePdf($input['id']);
        if($proposal_template_temp->new_pdf_flag ==1)
            $this->estimateGeneratemPdf($input['id']);

//        $this->estimateGeneratePdf($input['id']);
//        $this->estimateGeneratemPdf($input['id']);

        return $this->sendResponse(["estimate_id" => $input['id']], 'Estimate Saved!');
    }

    public function estimateGeneratePdf($insert_id)
    {
        //PDF start
        $estimate = Estimate::where([["id", $insert_id], ["company_id", "=", $this->company_id]])->get()->first();
        //        $estimate_items = EstimateItems::where([["estimate_id", $insert_id], ["company_id", "=", $this->company_id]])->orderBy('id', 'ASC')->get(["*"]);
        $estimate_items = EstimateItems::leftJoin('items', 'estimate_items.item_id', '=', 'items.id')
            ->where([["estimate_items.estimate_id", $insert_id], ["estimate_items.company_id", "=", $this->company_id]])
            ->orderBy('estimate_items.id', 'ASC')
            ->get(["estimate_items.*", "items.image_icon as image_icon"]);

        $estimate_items_sp = EstimateItems::leftJoin('items', 'estimate_items.item_id', '=', 'items.id')
            ->where([["estimate_items.estimate_id", "=", $insert_id], ["estimate_items.company_id", "=", $this->company_id], ["estimate_items.technical_specification", "!=", '']]);
        $proposal_template = ProposalTemplates::where('company_id', $this->company_id)->first();
        $company_data = ViewUserData::where("id", $this->company_id)->orderBy('id', 'ASC')->get()->first();


        $salesPersonInfo = User::where("id", $estimate->sales_person_id)->get()->first();


        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf::setHeaderCallback(function ($pdf) use ($proposal_template,$estimate) {

//            if ($pdf->PageNo() > 1) {
            if ($estimate['pdf_cover_page_flg'] != 1 || $estimate['pdf_about_us_flg'] == 1 || $estimate['pdf_product_flg'] == 1 || $estimate['pdf_est_flg'] == 1 || $estimate['pdf_terms_flg'] == 1 || $estimate['pdf_testimonial_flg'] == 1) {
//                $image_file = public_path(Storage::url($proposal_template->header_logo));
                $image_file = Storage::disk('s3')->url($proposal_template->header_logo);
                $pdf->Image($image_file, $proposal_template->header_logo_left, $proposal_template->header_logo_top, $proposal_template->header_logo_size, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
                //                    $pdf->Image($image_file, 164, 2, 40, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
                $pdf->SetY(7);
                // Set font
                $pdf->SetFont('helvetica', 'B', 20);
                $pdf->setPageMark();
                /* $pdf->SetAlpha(0.1);
                 $img_file = public_path('storage/logo.png');
                 $pdf->Image($img_file, 50, 135, 100, '', 0, 0, '', false, 300, '', false, false, false);*/

                // Title
                //                $pdf->Cell(0, 15, 'Heaven Designs Pvt Ltd.', 0, false, 'C', 0, '', 1, false, 'M', 'M');
                //            $pdf->line(1, 20, 209, 20, array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'solid' => 1, 'color' => "#dee2e6"));
            }
        });

        // Custom Footer
        $pdf::setFooterCallback(function ($pdf) use ($proposal_template, $company_data) {
            $facebook_url = '';
            $instagram_url = '';
            $linkedin_url = '';
            $twitter_url = '';
            $width = 0;
            if ($company_data->facebook_url) {
                $width += 31.25;
                $facebook_url = '<td width="30"><a href="' . $company_data->facebook_url . '"><img src="' . Storage::url('facebook.png') . '" height="40"></a></td>';
            }
            if ($company_data->instagram_url) {
                $width += 31.25;
                $instagram_url = '<td width="30"><a href="' . $company_data->instagram_url . '"><img src="' . Storage::url('instagram.png') . '" height="40"></a></td>';
            }
            if ($company_data->linkedin_url) {
                $width += 31.25;
                $linkedin_url = '<td width="30"><a href="' . $company_data->linkedin_url . '"><img src="' . Storage::url('linkedin.png') . '" height="40"></a></td>';
            }
            if ($company_data->twitter_url) {
                $width += 31.25;
                $twitter_url = '<td width="30"><a href="' . $company_data->twitter_url . '"><img src="' . Storage::url('twitter.png') . '" height="40"></a></td>';
            }
            if ($facebook_url == '' && $instagram_url == '' && $linkedin_url == '' && $twitter_url == '') {
                $facebook_url = '<td width="30"></td>';
            }

            $first_td_width = 470 + (125 - $width);
            //            if ($pdf->PageNo() > 1) {
            //                $footer = '<table cellpadding="6"><tr style="background-color:' . $proposal_template->theme_color_one . ';"><td><a href="https://heavendesigns.in" target="_blank" style="text-decoration: none;color:#fff;">Heaven Designs</a></td><td style="text-align: right;color:#fff;">Social Media Link</td></tr></table>';
            $footer = '<table><tr style="background-color:' . $proposal_template->theme_footer_color . ';"><td width="' . $first_td_width . '"><table cellpadding="6"><tr><td><a href="' . $company_data->website_link . '" target="_blank" style="text-decoration: none;color:#fff;">' . $company_data->company_name . '</a></td></tr></table></td><td style="text-align: right;color:#fff;"  width="125" align="right"><table border="0" style="text-align: right;" align="right"><tr>' . $facebook_url . $instagram_url . $linkedin_url . $twitter_url . '</tr></table></td></tr></table>';
            $pdf->SetY(-9.6);
            $pdf->SetX(0);
            /*
                        } else {
                            $footer = '<table cellpadding="6"><tr style="background-color:' . $proposal_template->theme_color_two . ';"><td><a href="https://heavendesigns.in" target="_blank" style="text-decoration: none;color:#fff;">Heaven Designs</a></td><td style="text-align: right;color:#fff;">Social Media Link</td></tr></table>';
                            $pdf->SetY(-9.6);
                        }*/
            $pdf->writeHTML($footer, true, false, true, false, '');
            /* $footer = '<table cellpadding="6"><tr style="background-color:' . $proposal_template->theme_color_two . ';"><td><a href="https://heavendesigns.in" target="_blank" style="text-decoration: none;color:' . $proposal_template->theme_color_one . ';">Heaven Designs</a></td><td style="text-align: right;color:' . $proposal_template->theme_color_one . ';">Social Media Link</td></tr></table>';
             $pdf->SetY(-9.6);
             $pdf->writeHTML($footer, true, false, true, false, '');*/
        });

        $pdf::SetAuthor('System');
        $pdf::SetTitle('My Report');
        $pdf::SetSubject('Report of System');


        //First page
        if ($estimate['pdf_cover_page_flg']) {
            $pdf::SetMargins(0, 0, 0, false);
            $pdf::SetFontSubsetting(true);
            $pdf::SetFontSize('12px');
            $pdf::SetFont('helvetica', 'R', 11);
            $pdf::SetAutoPageBreak(false, PDF_MARGIN_FOOTER);
            $pdf::AddPage('P', 'A4');

            $view = \View::make('pdf.cover-page-web', compact('estimate', 'proposal_template', 'company_data'));
            $html = $view->render();
            $pdf::writeHTML($html, true, false, true, false, '');
        }

        //Second page
        if ($estimate['pdf_about_us_flg']) {
            $pdf::startPageGroup();
            $pdf::SetMargins(7, 15.5, 7, false);
            $pdf::SetFontSubsetting(true);
            $pdf::SetFont('helvetica', 'R', 11);
            $pdf::SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
            $pdf::AddPage('P', 'A4');

            $view = \View::make('pdf.about-page-web', compact('estimate', 'proposal_template', 'company_data'));
            $html = $view->render();
            $pdf::writeHTML($html, true, false, true, false, '');
        }

        //Third page
        if ($estimate['pdf_product_flg']) {
            $products = Product::select(["name", "id", "image_one", "image_two", "image_three"])->where('status', '=', 0)->whereIn('id', explode(',', $estimate->product_id))->where('company_id', $this->company_id)->get();

            $pdf::startPageGroup();
            $pdf::SetFontSubsetting(true);
            $pdf::SetMargins(7, 15.5, 7, false);
            $pdf::SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
            $pdf::SetFont('helvetica', 'R', 11);

            //$pdf::SetFont('helvetica', 'R', 11);
            $pdf::AddPage('P', 'A4');

            $view = \View::make('pdf.product-page-web', compact('products', 'proposal_template', 'company_data'));
            $html = $view->render();
            $pdf::writeHTML($html, true, false, true, false, '');
        }

        //Fourth page
        if ($estimate['pdf_est_flg']) {
            $customer_data = Customer::where("id", $estimate->customer_id)->select('gst_no','email','company_name','currency_name_country_id')->orderBy('id', 'DESC')->get()->first();

           /* $currency_id = $customer_data->currency_name_country_id;
            if($customer_data->currency_name_country_id==0){
                $currency_id = $company_data->country_id;
            }*/

            $currency_id = $estimate->est_currency_id;
            if($estimate->est_currency_id==0){
                $currency_id = $company_data->country_id;
            }

            $country_data = Country::where("id", $currency_id)->select('name','currency_name','currency_code','currency_symbol')->orderBy('id', 'DESC')->get()->first();
            $pdf::startPageGroup();
            $pdf::SetFontSubsetting(true);
            $pdf::SetMargins(7, 15.5, 7, false);
            $pdf::SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
            $pdf::SetFont('helvetica', 'R', 8);
            $pdf::AddPage('P', 'A4');

            $view = \View::make('pdf.estimate-page-web', compact('estimate', 'estimate_items', 'proposal_template', 'company_data', 'salesPersonInfo','customer_data','country_data'));
            $html = $view->render();
            $pdf::writeHTML($html, true, false, true, false, '');
        }

        if($estimate_items_sp->count() > 0){
            $pdf::startPageGroup();
            $pdf::SetMargins(7, 15.5, 7, false);
            $pdf::SetFontSubsetting(true);
            $pdf::SetFont('helvetica', 'R', 11);
            $pdf::SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
            $pdf::AddPage('P', 'A4');
            $estimate_items_sp = $estimate_items_sp->select(["estimate_items.technical_specification","items.name"])->get();

            $view = \View::make('pdf.specification-page-new', compact('estimate_items_sp'));
            $html = $view->render();
            $pdf::writeHTML($html, true, false, true, false, '');
        }

        //Fifth page
        if ($estimate['pdf_terms_flg']) {
            $term_condition_data = TermCondition::where("id", $estimate->term_condition_id)->orderBy('id', 'ASC')->get()->first();
            $pdf::startPageGroup();
            $pdf::SetFontSubsetting(true);
            $pdf::SetMargins(7, 15.5, 7, false);
            $pdf::SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
            $pdf::SetFont('helvetica', 'R', 10);
            $pdf::AddPage('P', 'A4');

            $view = \View::make('pdf.term-and-condition-page-web', compact('estimate', 'proposal_template', 'term_condition_data'));
            $html = $view->render();
            $pdf::writeHTML($html, true, false, true, false, '');
        }

        //Sixth page
        if ($estimate['pdf_testimonial_flg']) {
            $testimonials = Testimonial::select(["name", "id", "client_name_one", "image_one", "rating_one", "description_one", "client_name_two", "image_two", "rating_two", "description_two", "client_name_three", "image_three", "rating_three", "description_three"])->where('status', '=', 0)->where('id', $estimate->testimonial_id)->where('company_id', $this->company_id)->get()->first();
            $pdf::startPageGroup();
            $pdf::SetFontSubsetting(true);
            $pdf::SetMargins(7, 15.5, 7, false);
            $pdf::SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
            $pdf::SetFont('helvetica', 'R', 11);
            $pdf::AddPage('P', 'A4');

            $view = \View::make('pdf.testimonial-page-web', compact('proposal_template', 'company_data', 'testimonials'));
            $html = $view->render();
            $pdf::writeHTML($html, true, false, true, false, '');
        }

        //Seven page
        if ($proposal_template->thank_you_flg) {
            $pdf::startPageGroup();
            $pdf::SetFontSubsetting(true);
            $pdf::SetMargins(7, 15.5, 7, false);
            $pdf::SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
            $pdf::SetFont('helvetica', 'R', 11);
            $pdf::AddPage('P', 'A4');

            $view = \View::make('pdf.thank-you-page-web', compact('company_data', 'proposal_template', 'salesPersonInfo'));
            $html = $view->render();
            $pdf::writeHTML($html, true, false, true, false, '');
        }
        $pdf_name = $estimate['estimate_no'];
        $paramArr['internal_remarks'] = "Estimate created";
        if ($estimate['estimate_version'] > 0) {
            $pdf_name = $estimate['estimate_no'] . '-V' . $estimate['estimate_version'];
            $paramArr['internal_remarks'] = "Estimate updated";
        }
        $customerData = Customer::select("assigned_to_user")->where('id', '=', $estimate['customer_id'])->first();

        $customer_view_data = ViewCustomerData::select("last_follow_up_datetime")->where('id', '=', $estimate['customer_id'])->first();


        $paramArr['assigned_to'] = $customerData->assigned_to_user;
        $paramArr['customer_id'] = $estimate['customer_id'];
        $paramArr['estimate_id'] = $insert_id;
        $paramArr['activity_type'] = 5;
        $paramArr['estimate_version_no'] = $pdf_name;
        $paramArr['activity_name'] = 'Estimate';
        //        $paramArr['activity_estimate_status'] = 'Draft';
//        $paramArr['activity_estimate_status'] = $estimate['status'];
//        $paramArr['activity_estimate_status'] = 'Inprogress';
//        $paramArr['activity_estimate_status'] = ($estimate['status'] == 'Draft')?'Draft':'Inprogress'; // && $estimate['estimate_version'] == 0
        $paramArr['activity_estimate_status'] = ($estimate['status'] == 'Draft' && $estimate['estimate_version']==0)?'Draft':'Inprogress';
        $paramArr['activity_notes'] = 'updated Estimate';
        $paramArr['entry_type'] = 'estimate';
        $paramArr['is_modified'] = 0;
        $paramArr['user_id'] = $this->logged_user->id;
        $paramArr['company_id'] = $this->company_id;
        $paramArr['created_by'] = $this->logged_user->id;
        $paramArr['updated_by'] = $this->logged_user->id;
        $paramArr['net_amount'] = $estimate['net_amount'];
        $paramArr['follow_up_datetime'] = $customer_view_data->last_follow_up_datetime;
        EstimateTimeline::create($paramArr);
        Estimate::where('id', $insert_id)->update(array('status' => $paramArr['activity_estimate_status']));
        //            $pdf::Output('hello_world.pdf', 'I');
        $pdf::Output(public_path('storage/document/' . $this->company_id . '/' . $pdf_name . '.pdf'), 'F');

        $company_id = $this->company_id;
        $path = storage_path('app/public/document/'.$company_id. '/' . $pdf_name . '.pdf');
        $tmp_paths= Storage::disk('s3')->put('public/'.$company_id. '/documents/'. $pdf_name . '.pdf', file_get_contents($path),'public');
//        Storage::disk('s3')->setVisibility($tmp_paths, 'public');
        Storage::disk('s3')->url('public/'.$company_id. '/documents/'. $pdf_name . '.pdf');
        unlink($path);
        //$pdf::Output('hello_world.pdf', 'I');
        //        $pdf::Output(public_path('storage/document/' . $this->company_id . '/' . $estimate['estimate_no'] . '.pdf'), 'F');
    }

    public function estimateGeneratemPdf($insert_id)
    {
        //PDF start
        $estimate = Estimate::where([["id", $insert_id], ["company_id", "=", $this->company_id]])->get()->first();
        //        $estimate_items = EstimateItems::where([["estimate_id", $insert_id], ["company_id", "=", $this->company_id]])->orderBy('id', 'ASC')->get(["*"]);
        $estimate_items = EstimateItems::leftJoin('items', 'estimate_items.item_id', '=', 'items.id')
            ->where([["estimate_items.estimate_id", $insert_id], ["estimate_items.company_id", "=", $this->company_id]])
            ->orderBy('estimate_items.id', 'ASC')
            ->get(["estimate_items.*", "items.image_icon as image_icon"]);

        $estimate_items_sp = EstimateItems::leftJoin('items', 'estimate_items.item_id', '=', 'items.id')
            ->where([["estimate_items.estimate_id","=", $insert_id], ["estimate_items.company_id", "=", $this->company_id]]) //
            ->whereNotNull('estimate_items.technical_specification')
            ->orderBy('estimate_items.id', 'ASC')
            ->get(["estimate_items.*", "items.image_icon as image_icon"]);

        $proposal_template = ProposalTemplates::where('company_id', $this->company_id)->first();
        $company_data = ViewUserData::where("id", $this->company_id)->orderBy('id', 'ASC')->get()->first();
        $salesPersonInfo = User::where("id", $estimate->sales_person_id)->get()->first();

        /*$mpdf = new Mpdf([
            'mode' => 'utf-8',
            'tempDir'=>storage_path('tempdir')
        ]);*/
        $mpdf = MpdfService::createMpdfInstance();
//        $mpdf->SetCompression(false);
        $mpdf->showImageErrors = true;
        $mpdf->debug = true;
        $mpdf->curlAllowUnsafeSslRequests = true;
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;
        $mpdf->allow_charset_conversion = true;

        /*$mpdf->SetWatermarkText('Heaven Design Pvt. Ltd.');
        $mpdf->showWatermarkText = true;
        $mpdf->watermarkTextAlpha = 0.1;
        $mpdf->watermarkImageAlpha = 0.5;*/
        $logo = Storage::disk('s3')->temporaryUrl($proposal_template->header_logo,Carbon::now()->addMinutes(20));
        $header = '<div style="text-align: right; font-weight: bold;border-bottom: 1px solid #fff;margin-right:'.$proposal_template->header_logo_left.'px;padding-top:'.$proposal_template->header_logo_top.'px;"><img src="'.$logo.'" width="'.$proposal_template->header_logo_size.'"/></div>';
        // Define the Headers before writing anything so they appear on the first page



        $facebook_url = '';
        $instagram_url = '';
        $linkedin_url = '';
        $twitter_url = '';
        $call_url = '';
        $gmail_url = '';
        $whatsapp_url = '';
        $width = 0;
        $current_loggedin_user = Auth::user();
        if ($company_data->facebook_url) {
            $facebook_url = '<td><a href="' . $company_data->facebook_url . '"><img src="' . public_path(Storage::url('facebook.png')) . '" width="30"></a></td>';
        }
        if ($company_data->instagram_url) {
            $instagram_url = '<td><a href="' . $company_data->instagram_url . '"><img src="' . public_path(Storage::url('instagram.png')) . '" width="30"></a></td>';
        }
        if ($company_data->linkedin_url) {
            $linkedin_url = '<td><a href="' . $company_data->linkedin_url . '"><img src="' . public_path(Storage::url('linkedin.png')) . '" width="30"></a></td>';
        }
        if ($company_data->twitter_url) {
            $twitter_url = '<td><a href="' . $company_data->twitter_url . '"><img src="' . public_path(Storage::url('twitter.png')) . '" width="30"></a></td>';
        }

        if ($current_loggedin_user->call_url) {
            $call_url = '<td><a href="tel:' .$current_loggedin_user->call_code_url.$current_loggedin_user->call_url . '"><img src="' . public_path(Storage::url('call.png')) . '" width="34"></a></td>';
        }

        if ($current_loggedin_user->gmail_url) {
            $gmail_url = '<td><a href="mailto:' . $current_loggedin_user->gmail_url . '"><img src="' . public_path(Storage::url('email.png')) . '" width="34"></a></td>';
        }

        if ($current_loggedin_user->whatsapp_url) {
            $whatsapp_url = '<td><a href="https://wa.me/' . $current_loggedin_user->whatsapp_code_url.$current_loggedin_user->whatsapp_url . '"><img src="' . public_path(Storage::url('whatsapp.png')) . '" width="34"></a></td>';
        }

        $footer = '
        <table width="100%" style="vertical-align: middle; font-family: Arial, Helvetica, serif;
            font-size: 12pt; color: #fff; font-style: normal;background: ' . $proposal_template->theme_footer_color . ';">
            <tr>
                <td width="47%"><a href="' . $company_data->website_link . '" target="_blank" style="text-decoration: none !important;color:#fff;">&nbsp;&nbsp;' . $company_data->company_name . '</a></td>
                <td width="6%" align="center">{PAGENO}/{nbpg}</td>
                <td width="47%" style="text-align: right;">
                    <table>
                        <tr>
                            '.$facebook_url.$instagram_url.$linkedin_url.$twitter_url.$call_url.$gmail_url.$whatsapp_url.'
                        </tr>
                    </table>
                </td>
            </tr>
        </table>';

        if ($estimate['pdf_cover_page_flg']) {
            $mpdf->SetHTMLHeader();
            $mpdf->SetHTMLFooter();
            $mpdf->AddPage('P', '', '', '', '', 0, 0, 0, -1, 0, 0);
            $coverHtml1 = view('pdf.cover-page-new-web', compact('estimate', 'proposal_template', 'company_data'))->render();
            $mpdf->WriteHTML($coverHtml1);
        }

        if ($estimate['pdf_about_us_flg']) {
            $mpdf->SetHTMLHeader($header);
            $mpdf->SetHTMLFooter($footer);
            $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);
            $aboutHtml = view('pdf.about-page-new-web', compact('estimate', 'proposal_template', 'company_data'))->render();
            $mpdf->WriteHTML($aboutHtml);
        }

        if ($estimate['pdf_product_flg'] && $proposal_template->photo_position_flg==0) {
            $pro_title=($proposal_template->product_title)? html_entity_decode($proposal_template->product_title, ENT_QUOTES, 'UTF-8') : '';
            $pro_content=($proposal_template->product_content)? html_entity_decode($proposal_template->product_content, ENT_QUOTES, 'UTF-8') : '';
            $products = Product::select(["name", "id", "image_one", "image_two", "image_three"])->where('status', '=', 0)->whereIn('id', explode(',', $estimate->product_id))->where('company_id', $this->company_id)->orderByRaw("FIELD(id,$estimate->product_id)")->get();
            $mpdf->SetHTMLHeader($header);
            $mpdf->SetHTMLFooter($footer);
            $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);

            $productHtml = view('pdf.product-page-new-web', compact('products', 'proposal_template', 'company_data'))->render();
            $mpdf->WriteHTML($productHtml);
        }

        //Fourth page
        if ($estimate['pdf_est_flg']) {
            $pdf_title_name= ($proposal_template->est_title)?$proposal_template->est_title : 'Estimate';
            $customer_data = Customer::where("id", $estimate->customer_id)->select('gst_no','email','company_name','currency_name_country_id')->orderBy('id', 'DESC')->get()->first();

            /*$currency_id = $customer_data->currency_name_country_id;
            if($customer_data->currency_name_country_id==0){
                $currency_id = $company_data->country_id;
            }*/

            $currency_id = $estimate->est_currency_id;
            if($estimate->est_currency_id==0){
                $currency_id = $company_data->country_id;
            }

            $country_data = Country::where("id", $currency_id)->select('name','currency_name','currency_code','currency_symbol')->orderBy('id', 'DESC')->get()->first();
            $mpdf->SetHTMLHeader($header);
            $mpdf->SetHTMLFooter($footer);
            $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);

            $estHtml = view('pdf.estimate-page-new-web', compact('estimate', 'estimate_items', 'proposal_template', 'company_data', 'salesPersonInfo','customer_data','country_data'))->render();
            $mpdf->writeHTML($estHtml);
        }

        if($estimate_items_sp->count() > 0){
            $mpdf->SetHTMLHeader($header);
            $mpdf->SetHTMLFooter($footer);
            $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);

//            $estHtml = view('pdf.estimate-page-new-web', compact('estimate', 'estimate_items', 'proposal_template', 'company_data', 'salesPersonInfo','customer_data', 'country_data'))->render();
//            $estHtml = view('pdf.specification-page-new-web', compact('estimate_items_sp'))->render();


            $estHtml = "<!DOCTYPE html>
<html>
<head><title>Product Page</title><style>
        /* Define your CSS styles here */
        .centered-div {
            width: 89.60%; /* Set the width of the div */
            margin: 0 auto; /* Center the div horizontally */
        }
    </style></head>
<body>";
            foreach($estimate_items_sp as $pk => $estimate_items) {
                if ($pk > 0)
                    $estHtml .= "<pagebreak/>";

                $estHtml .= '<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%"
               style="margin-top: 30px;font-family: helvetica;" valign="middle">

            <tr valign="middle">
                <td align="center" valign="bottom">
                    <table border="0" cellspacing="5" align="center" cellpadding="1"
                           style="font-family: helvetica;text-align:center;" width="90%">
                        <tr>
                            <td align="left"><p>(' . ++$pk . ') ' . $estimate_items->item_name . '</p></td>
                        </tr>
                    </table>
                </td>
           </tr>
        </table><div class="centered-div">' .$estimate_items->technical_specification . '</div>';
            }
            $estHtml .= '</body>
</html>';

            $mpdf->writeHTML($estHtml);
        }

        if ($estimate['pdf_product_flg'] && $proposal_template->photo_position_flg==1) {
            $pro_title=($proposal_template->product_title)? html_entity_decode($proposal_template->product_title, ENT_QUOTES, 'UTF-8') : '';
            $pro_content=($proposal_template->product_content)? html_entity_decode($proposal_template->product_content, ENT_QUOTES, 'UTF-8') : '';
            $products = Product::select(["name", "id", "image_one", "image_two", "image_three"])->where('status', '=', 0)->whereIn('id', explode(',', $estimate->product_id))->where('company_id', $this->company_id)->orderByRaw("FIELD(id,$estimate->product_id)")->get();
            $mpdf->SetHTMLHeader($header);
            $mpdf->SetHTMLFooter($footer);
            $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);

            $productHtml = view('pdf.product-page-new-web', compact('products', 'proposal_template', 'company_data'))->render();
            $mpdf->WriteHTML($productHtml);
        }

       /* if($estimate_items_sp->count() > 0){
            $mpdf->SetHTMLHeader($header);
            $mpdf->SetHTMLFooter($footer);
            $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);

//            $estHtml = view('pdf.estimate-page-new-web', compact('estimate', 'estimate_items', 'proposal_template', 'company_data', 'salesPersonInfo','customer_data', 'country_data'))->render();
            $estHtml = view('pdf.specification-page-new-web', compact('estimate_items_sp'))->render();
            $mpdf->writeHTML($estHtml);
        }*/

        //Fifth page
        if ($estimate['pdf_terms_flg']) {
            $term_condition_data = TermCondition::where("id", $estimate->term_condition_id)->orderBy('id', 'ASC')->get()->first();
            $mpdf->SetHTMLHeader($header);
            $mpdf->SetHTMLFooter($footer);
            $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);

            $termsHtml = view('pdf.term-and-condition-page-new-web', compact('estimate', 'proposal_template', 'term_condition_data'))->render();
            $mpdf->autoPageBreak = true;
            $mpdf->writeHTML($termsHtml);
        }

        //Sixth page
        if ($estimate['pdf_testimonial_flg']) {

            $testimonials = Testimonial::select(["name", "id", "client_name_one", "image_one", "rating_one", "description_one", "client_name_two", "image_two", "rating_two", "description_two", "client_name_three", "image_three", "rating_three", "description_three"])->where('status', '=', 0)->where('id', $estimate->testimonial_id)->where('company_id', $this->company_id)->get()->first();
            $mpdf->SetHTMLHeader($header);
            $mpdf->SetHTMLFooter($footer);
            $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);

            $testiHtml = view('pdf.testimonial-page-new-web', compact('proposal_template', 'company_data', 'testimonials'))->render();
            $mpdf->writeHTML($testiHtml);
            //            $mpdf->writeHTML("મિત્રો દ્રશ્ય સંસ્કાર પ્રકાશ પંથ ત્રિકમ", true, false, true, false, '');
        }

        //Seven page
        if ($proposal_template->thank_you_flg) {
            $mpdf->SetHTMLHeader($header);
            $mpdf->SetHTMLFooter($footer);
            $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);

            $thanksHtml = view('pdf.thank-you-page-new-web', compact('company_data', 'proposal_template', 'salesPersonInfo'))->render();
            $mpdf->writeHTML($thanksHtml);
        }

        $pdf_name = $estimate['estimate_no'];
        $paramArr['internal_remarks'] = "Estimate created";
        if ($estimate['estimate_version'] > 0) {
            $pdf_name = $estimate['estimate_no'] . '-V' . $estimate['estimate_version'];
            $paramArr['internal_remarks'] = "Estimate updated";
        }
        $customerData = Customer::select("assigned_to_user")->where('id', '=', $estimate['customer_id'])->first();

        $customer_view_data = ViewCustomerData::select("last_follow_up_datetime")->where('id', '=', $estimate['customer_id'])->first();


        $paramArr['assigned_to'] = $customerData->assigned_to_user;
        $paramArr['customer_id'] = $estimate['customer_id'];
        $paramArr['estimate_id'] = $insert_id;
        $paramArr['activity_type'] = 5;
        $paramArr['estimate_version_no'] = $pdf_name;
        $paramArr['activity_name'] = 'Estimate';
        //        $paramArr['activity_estimate_status'] = 'Draft';
//        $paramArr['activity_estimate_status'] = $estimate['status'];
//        $paramArr['activity_estimate_status'] = 'Inprogress';
//        $paramArr['activity_estimate_status'] = ($estimate['status'] == 'Draft')?'Draft':'Inprogress'; // && $estimate['estimate_version'] == 0
        $paramArr['activity_estimate_status'] = ($estimate['status'] == 'Draft' && $estimate['estimate_version']==0)?'Draft':'Inprogress';
        $paramArr['activity_notes'] = 'updated Estimate';
        $paramArr['entry_type'] = 'estimate';
        $paramArr['is_modified'] = 0;
        $paramArr['user_id'] = $this->logged_user->id;
        $paramArr['company_id'] = $this->company_id;
        $paramArr['created_by'] = $this->logged_user->id;
        $paramArr['updated_by'] = $this->logged_user->id;
        $paramArr['net_amount'] = $estimate['net_amount'];
        $paramArr['follow_up_datetime'] = $customer_view_data->last_follow_up_datetime;
        EstimateTimeline::create($paramArr);
        Estimate::where('id', $insert_id)->update(array('status' => $paramArr['activity_estimate_status']));
        $mpdf->Output(public_path('storage/document/' . $this->company_id . '/' . $pdf_name . '.pdf'), 'F');

        $company_id = $this->company_id;
        $path = storage_path('app/public/document/'.$company_id. '/' . $pdf_name . '.pdf');
        $tmp_paths= Storage::disk('s3')->put('public/'.$company_id. '/documents/'. $pdf_name . '.pdf', file_get_contents($path),'public');
//        Storage::disk('s3')->setVisibility($tmp_paths, 'public');
        Storage::disk('s3')->url('public/'.$company_id. '/documents/'. $pdf_name . '.pdf');
        unlink($path);
    }

    public function getEstimateNumber()
    {
        $estimate_auto_number = EstimateAutoNumber::select(["estimate_prefix", "estimate_next_no"])->where('company_id', $this->company_id)->get()->first();
        return $this->sendResponse(["estimate_no" => $estimate_auto_number->estimate_prefix . $estimate_auto_number->estimate_next_no], 'Estimate Number retrieved!');
    }

    public function getSalesman()
    {
        $user_list = [];
        if ($this->logged_user->company_id == null) {
            $user_list['salesman'] = User::query()->select(["name", "id", "email"])
                ->where('status', 'Approved')
                ->where('company_id', $this->company_id)
                ->where(function ($query) {
                    $query->where(function ($query) {
                        $query->where('user_id', $this->company_id);
                        $query->orwhere('role_id', 6);
                    });
                })
                ->get();
        }
        $estimate_auto_number = EstimateAutoNumber::select(["estimate_prefix", "estimate_next_no"])->where('company_id', $this->company_id)->get()->first();
        return $this->sendResponse($user_list, 'Salesman retrieved!');
    }

    public function getEstimateSingle($id)
    {

        $data['estimate'] = Estimate::where([["id", $id], ["company_id", "=", $this->company_id]])->get()->first();
        if (!$data['estimate']) {
            return $this->sendError("Estimate id not found", ["error" => "Estimate id not found"], 400);
        }
        $data['customer'] = Customer::select('phone_no','country_code','whatsapp_country_code','whatsapp_no','whatsapp_country_code','currency_name','currency_code','phone_no_country_id','whatsapp_no_country_id','currency_name_country_id')->where([["id", $data['estimate']->customer_id], ["company_id", "=", $this->company_id]])->first();

        $country_data = [];
        /*if($data['customer']->currency_name_country_id)
            $country_data = Country::where("id", $data['customer']->currency_name_country_id)->select('name','currency_name','currency_code','currency_symbol')->orderBy('id', 'DESC')->get()->first();*/

        if ($data['estimate']->est_currency_id)
            $country_data = Country::where("id", $data['estimate']->est_currency_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->first();

$data['currency_symbol'] = (isset($country_data->currency_symbol)) ? $country_data->currency_symbol:'';
        $data['estimate_items'] = EstimateItems::where([["estimate_id", $id], ["company_id", "=", $this->company_id]])->orderBy('id', 'ASC')->get(["*"]);
        return $this->sendResponse($data, 'Estimate retrieved!');
    }

    /*public function getEstimateSearch($search = null)
    {
        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');
        $subquery = DB::table('customer_timelines')
            ->select('estimate_id', DB::raw('MAX(id) as max_id'))
            ->where('activity_type','=',5)
            ->groupBy('estimate_id');

        $data = DB::table('estimates')
            ->leftJoin('customer_timelines', function($join) use ($subquery) {
                $join->on('estimates.id', '=', 'customer_timelines.estimate_id')
                    ->whereIn('customer_timelines.id', function($query) use ($subquery) {
                        $query->select('max_id')
                            ->fromSub($subquery, 'sub');
                    });
            })
            ->leftJoin('users', 'estimates.sales_person_id', '=', 'users.id')
            ->leftJoin('customers as c', function($join) use ($user_perm) {
                $join->on('estimates.customer_id', '=', 'c.id');
            })
            ->leftJoin('users as u', 'c.assigned_to_user', '=', 'u.id')
            ->select('estimates.id','estimates.customer_id', 'estimates.customer_name', 'estimates.estimate_no', 'estimates.estimate_date', 'estimates.addless_amount', 'estimates.net_amount', DB::raw("RIGHT(estimates.customer_address, 10) as mobile_no"), "estimates.status", "users.name as created_by","estimate_version", 'customer_timelines.id as last_activity_id','customer_timelines.updated_at as last_updated_at','customer_timelines.follow_up_datetime as last_follow_up_datetime','u.name as assigned_user_name', 'c.currency_name_country_id')
            ->where(function($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('c.assigned_to_user', '=', $this->logged_user->id)
                        ->orWhere('c.user_id', '=', $this->logged_user->id);
                }
            })
            ->where(function ($query) use ($search) {
                if ($search != '') {
                    $query->where('estimates.estimate_no', 'like', '%'.$search . '%');
                    $query->orwhere('c.name', 'like', '%'.$search . '%');
                    $query->orwhere('c.company_name', 'like', '%'.$search . '%');
                    $query->orwhere('c.phone_no', 'like', '%'.$search . '%');
                }
            })
            ->where('estimates.company_id', $this->company_id)
            ->get();
        if (is_null($data)) {
            return $this->sendError('Estimate not found', ['Estimate not found'], 422);
        }

        foreach ($data as $key => $val) {
            $country_data = [];
            if($data[$key]->currency_name_country_id)
                $country_data = Country::where("id", $data[$key]->currency_name_country_id)->select('name','currency_name','currency_code','currency_symbol')->orderBy('id', 'DESC')->get()->first();
            $data[$key]->currency_symbol = (isset($country_data->currency_symbol)) ? $country_data->currency_symbol :'';.
        }
        return $this->sendResponse($data, 'Estimaste retrieved successfully');
    }*/

    public function getEstimateSearch($search = null)
    {
        $user_perm = \App\Helpers\PermissionCheck::check_permission('role-list');

        $data = DB::table('estimates')
            ->leftJoin('customer_timelines as ct', 'estimates.id', '=', 'ct.estimate_id')
            ->leftJoin('users', 'estimates.sales_person_id', '=', 'users.id')
            ->leftJoin('customers as c', 'estimates.customer_id', '=', 'c.id')
            ->leftJoin('users as u', 'c.assigned_to_user', '=', 'u.id')
            ->leftJoin(DB::raw('(SELECT estimate_id, MAX(id) AS max_id FROM customer_timelines WHERE activity_type = 5 GROUP BY estimate_id) AS max_ct'), function ($join) {
                $join->on('ct.estimate_id', '=', 'max_ct.estimate_id')
                    ->on('ct.id', '=', 'max_ct.max_id');
            })
            ->select(
                'estimates.id',
                'estimates.customer_id',
                'estimates.customer_name',
                'estimates.estimate_no',
                'estimates.estimate_date',
                'estimates.addless_amount',
                'estimates.net_amount',
                DB::raw("RIGHT(estimates.customer_address, 10) as mobile_no"),
                'estimates.status',
                'users.name as created_by',
                'estimates.estimate_version',
                'max_ct.max_id as last_activity_id',
                'ct.updated_at as last_updated_at',
                'ct.follow_up_datetime as last_follow_up_datetime',
                'u.name as assigned_user_name',
                'c.currency_name_country_id'
            )
            ->where(function ($query) use ($user_perm) {
                if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                    $query->where('c.assigned_to_user', '=', $this->logged_user->id)
                        ->orWhere('c.user_id', '=', $this->logged_user->id);
                }
            })
            ->where(function ($query) use ($search) {
                if (!is_null($search)) {
                    $query->where('estimates.estimate_no', 'like', '%' . $search . '%')
                        ->orWhere('c.name', 'like', '%' . $search . '%')
                        ->orWhere('c.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c.phone_no', 'like', '%' . $search . '%');
                }
            })
            ->where('estimates.company_id', $this->company_id)
            ->get();

        if ($data->isEmpty()) {
            return $this->sendError('Estimate not found', ['Estimate not found'], 200);
        }

        foreach ($data as $key => $val) {
            $country_data = [];
            if ($data[$key]->currency_name_country_id) {
                $country_data = Country::where("id", $data[$key]->currency_name_country_id)
                    ->select('name', 'currency_name', 'currency_code', 'currency_symbol')
                    ->orderBy('id', 'DESC')
                    ->first();
            }
            $data[$key]->currency_symbol = isset($country_data->currency_symbol) ? $country_data->currency_symbol : '';
        }
        return $this->sendResponse($data, 'Estimate retrieved successfully');
    }

    public function getAwsGenerateLink($company_id,$pdf_name)
    {
        $path = Storage::disk('s3')->url('public/'.$company_id.'/documents/' . $pdf_name . '.pdf');
//        $path = Storage::disk('s3')->temporaryUrl(trim('public/'.$company_id.'/documents/' . $pdf_name . '.pdf'),Carbon::now()->addMinutes(20));
        return $this->sendResponse(["url" => $path], 'Link generated successfully');
    }
}
