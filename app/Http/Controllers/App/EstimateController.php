<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Services\MpdfService;
use App\Models\{Country,
    Customer,
    CustomerCategory,
    CustomerLead,
    Estimate,
    EstimateAutoNumber,
    EstimateItems,
    EstimatePhoto,
    EstimateTimeline,
    Event,
    LeadStage,
    PlanHistory,
    Product,
    ProposalTemplates,
    Tax,
    TermCondition,
    Testimonial,
    Unit,
    User,
    ViewCustomerData
};
use App\Models\admin\ViewUserData;
use App\Models\admin\LeadHistory;
use App\Models\admin\EstimateHistory;
use App\Models\admin\AttachmentHistory;
use Auth;
use Carbon\Carbon;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Crypt, DB, Storage, Validator};
use Illuminate\Support\Str;
use Image;
use App\Helpers\LogActivity;
use Mpdf\Mpdf;

class EstimateController extends Controller
{
    protected $logged_user = null;
    protected $company_id = 0;
    protected $segment = null;
    public function __construct()
    {
        if (request()->segment(2) != "generate-link") { //request()->segment(2) &&
            $this->middleware(function ($request, $next) {
                $this->logged_user = \Illuminate\Support\Facades\Auth::user();
                $this->company_id = ($this->logged_user->company_id) ? $this->logged_user->company_id : $this->logged_user->id;
                $this->segment = $request->segment(1);
                return $next($request);
            });
        }
    }

    public function testScriptCoverPhoto()
    {

        $records = DB::table('proposal_templates')
            ->select(['id', 'company_id', 'cover_img', "user_id"])
            ->get();

        foreach ($records as $record) {
            $coverPhotoArr = [
                'image_icon' => $record->cover_img, 'proposal_template_id' => $record->id, 'cover_flg' => 1, 'user_id' => $record->user_id, 'company_id' => $record->company_id
            ];
            DB::table('proposal_template_cover_photos')->insert($coverPhotoArr);
        }
        echo "ok_1";
    }

    public function testScriptAboutPhoto()
    {

        $records = DB::table('proposal_templates')
            ->select(['id', 'company_id', 'aboutas_img', "user_id"])
            ->get();

        foreach ($records as $record) {
            $aboutPhotoArr = [
                'image_icon' => $record->aboutas_img, 'proposal_template_id' => $record->id, 'about_flg' => 1, 'user_id' => $record->user_id, 'company_id' => $record->company_id
            ];
            DB::table('proposal_template_aboutus_photos')->insert($aboutPhotoArr);
        }
        echo "ok_2";
    }

    public function testScriptProOne()
    {

        $records = DB::table('users')
            ->where('status', 'Approved')
            ->whereNotIn('id', [39, 106, 144])
            ->whereNull('company_id')
            ->select(['id', 'company_category'])
            ->get();

        foreach ($records as $record) {
            if ($record->company_category == 1) {

                // 8-PANEL
                $panel8T_1_path_one = env('APP_URL') . "sample/product/8-PANEL/T-1/8-panel-1.jpg";
                $panel8T_1_one = date('YmdHis') . "1" . ".jpg";
                Image::make($panel8T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_1_one));

                $panel8T_1_path_two = env('APP_URL') . "sample/product/8-PANEL/T-1/8-panel-2.jpg";
                $panel8T_1_two = date('YmdHis') . "2" . ".jpg";
                Image::make($panel8T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_1_two));

                $panel8T_1_path_three = env('APP_URL') . "sample/product/8-PANEL/T-1/8-panel-3.jpg";
                $panel8T_1_three = date('YmdHis') . "3" . ".jpg";
                Image::make($panel8T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_1_three));

                $panel8T_2_path_one = env('APP_URL') . "sample/product/8-PANEL/T-2/10002.jpg";
                $panel8T_2_one = date('YmdHis') . "4" . ".jpg";
                Image::make($panel8T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_2_one));

                $panel8T_2_path_two = env('APP_URL') . "sample/product/8-PANEL/T-2/20001.jpg";
                $panel8T_2_two = date('YmdHis') . "5" . ".jpg";
                Image::make($panel8T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_2_two));

                $panel8T_2_path_three = env('APP_URL') . "sample/product/8-PANEL/T-2/20003.jpg";
                $panel8T_2_three = date('YmdHis') . "6" . ".jpg";
                Image::make($panel8T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_2_three));

                $panel8T_3_path_one = env('APP_URL') . "sample/product/8-PANEL/T-3/10002.jpg";
                $panel8T_3_one = date('YmdHis') . "7" . ".jpg";
                Image::make($panel8T_3_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_3_one));

                $panel8T_3_path_two = env('APP_URL') . "sample/product/8-PANEL/T-3/20001.jpg";
                $panel8T_3_two = date('YmdHis') . "8" . ".jpg";
                Image::make($panel8T_3_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_3_two));

                $panel8T_3_path_three = env('APP_URL') . "sample/product/8-PANEL/T-3/20003.jpg";
                $panel8T_3_three = date('YmdHis') . "9" . ".jpg";
                Image::make($panel8T_3_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_3_three));

                // 9-PANEL
                $panel9T_1_path_one = env('APP_URL') . "sample/product/9-PANEL/T-1/10002.jpg";
                $panel9T_1_one = date('YmdHis') . "10" . ".jpg";
                Image::make($panel9T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_1_one));

                $panel9T_1_path_two = env('APP_URL') . "sample/product/9-PANEL/T-1/20001.jpg";
                $panel9T_1_two = date('YmdHis') . "11" . ".jpg";
                Image::make($panel9T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_1_two));

                $panel9T_1_path_three = env('APP_URL') . "sample/product/9-PANEL/T-1/20003.jpg";
                $panel9T_1_three = date('YmdHis') . "12" . ".jpg";
                Image::make($panel9T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_1_three));

                $panel9T_2_path_one = env('APP_URL') . "sample/product/9-PANEL/T-2/10002.jpg";
                $panel9T_2_one = date('YmdHis') . "13" . ".jpg";
                Image::make($panel9T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_2_one));

                $panel9T_2_path_two = env('APP_URL') . "sample/product/9-PANEL/T-2/20001.jpg";
                $panel9T_2_two = date('YmdHis') . "14" . ".jpg";
                Image::make($panel9T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_2_two));

                $panel9T_2_path_three = env('APP_URL') . "sample/product/9-PANEL/T-2/20003.jpg";
                $panel9T_2_three = date('YmdHis') . "15" . ".jpg";
                Image::make($panel9T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_2_three));

                $panel9T_3_path_one = env('APP_URL') . "sample/product/9-PANEL/T-3/10002.jpg";
                $panel9T_3_one = date('YmdHis') . "16" . ".jpg";
                Image::make($panel9T_3_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_3_one));

                $panel9T_3_path_two = env('APP_URL') . "sample/product/9-PANEL/T-3/20001.jpg";
                $panel9T_3_two = date('YmdHis') . "17" . ".jpg";
                Image::make($panel9T_3_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_3_two));

                $panel9T_3_path_three = env('APP_URL') . "sample/product/9-PANEL/T-3/20003.jpg";
                $panel9T_3_three = date('YmdHis') . "18" . ".jpg";
                Image::make($panel9T_3_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_3_three));

                $productArr = [
                    ['name' => '8 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel8T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel8T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel8T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '8 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel8T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel8T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel8T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '8 Panel_T-3', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel8T_3_one, 'image_two' => 'public/uploads/thumbnail/' . $panel8T_3_two, 'image_three' => 'public/uploads/thumbnail/' . $panel8T_3_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '9 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel9T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel9T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel9T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '9 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel9T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel9T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel9T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '9 Panel_T-3', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel9T_3_one, 'image_two' => 'public/uploads/thumbnail/' . $panel9T_3_two, 'image_three' => 'public/uploads/thumbnail/' . $panel9T_3_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id]
                ];
                DB::table('products')->insert($productArr);
            }
        }
        echo "ok_1";
    }

    public function testScriptProTwo()
    {
        $records = DB::table('users')
            ->where('status', 'Approved')
            ->whereNotIn('id', [39, 106, 144])
            ->whereNull('company_id')
            ->select(['id', 'company_category'])
            ->get();

        foreach ($records as $record) {
            if ($record->company_category == 1) {

                // 10-PANEL
                $panel10T_1_path_one = env('APP_URL') . "sample/product/10-PANEL/T-1/10002.jpg";
                $panel10T_1_one = date('YmdHis') . "19" . ".jpg";
                Image::make($panel10T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_1_one));

                $panel10T_1_path_two = env('APP_URL') . "sample/product/10-PANEL/T-1/20001.jpg";
                $panel10T_1_two = date('YmdHis') . "20" . ".jpg";
                Image::make($panel10T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_1_two));

                $panel10T_1_path_three = env('APP_URL') . "sample/product/10-PANEL/T-1/20003.jpg";
                $panel10T_1_three = date('YmdHis') . "21" . ".jpg";
                Image::make($panel10T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_1_three));

                $panel10T_2_path_one = env('APP_URL') . "sample/product/10-PANEL/T-2/10002.jpg";
                $panel10T_2_one = date('YmdHis') . "22" . ".jpg";
                Image::make($panel10T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_2_one));

                $panel10T_2_path_two = env('APP_URL') . "sample/product/10-PANEL/T-2/20001.jpg";
                $panel10T_2_two = date('YmdHis') . "23" . ".jpg";
                Image::make($panel10T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_2_two));

                $panel10T_2_path_three = env('APP_URL') . "sample/product/10-PANEL/T-2/20003.jpg";
                $panel10T_2_three = date('YmdHis') . "24" . ".jpg";
                Image::make($panel10T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_2_three));

                $panel10T_3_path_one = env('APP_URL') . "sample/product/10-PANEL/T-3/10002.jpg";
                $panel10T_3_one = date('YmdHis') . "25" . ".jpg";
                Image::make($panel10T_3_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_3_one));

                $panel10T_3_path_two = env('APP_URL') . "sample/product/10-PANEL/T-3/20001.jpg";
                $panel10T_3_two = date('YmdHis') . "26" . ".jpg";
                Image::make($panel10T_3_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_3_two));

                $panel10T_3_path_three = env('APP_URL') . "sample/product/10-PANEL/T-3/20003.jpg";
                $panel10T_3_three = date('YmdHis') . "27" . ".jpg";
                Image::make($panel10T_3_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_3_three));

                // 11-PANEL
                $panel11T_1_path_one = env('APP_URL') . "sample/product/11-PANEL/T-1/10002.jpg";
                $panel11T_1_one = date('YmdHis') . "28" . ".jpg";
                Image::make($panel11T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_1_one));

                $panel11T_1_path_two = env('APP_URL') . "sample/product/11-PANEL/T-1/20001.jpg";
                $panel11T_1_two = date('YmdHis') . "29" . ".jpg";
                Image::make($panel11T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_1_two));

                $panel11T_1_path_three = env('APP_URL') . "sample/product/11-PANEL/T-1/20003.jpg";
                $panel11T_1_three = date('YmdHis') . "30" . ".jpg";
                Image::make($panel11T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_1_three));

                $panel11T_2_path_one = env('APP_URL') . "sample/product/11-PANEL/T-2/10002.jpg";
                $panel11T_2_one = date('YmdHis') . "31" . ".jpg";
                Image::make($panel11T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_2_one));

                $panel11T_2_path_two = env('APP_URL') . "sample/product/11-PANEL/T-2/20001.jpg";
                $panel11T_2_two = date('YmdHis') . "32" . ".jpg";
                Image::make($panel11T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_2_two));

                $panel11T_2_path_three = env('APP_URL') . "sample/product/11-PANEL/T-2/20003.jpg";
                $panel11T_2_three = date('YmdHis') . "33" . ".jpg";
                Image::make($panel11T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_2_three));

                $productArr = [
                    ['name' => '10 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel10T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel10T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel10T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '10 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel10T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel10T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel10T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '10 Panel_T-3', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel10T_3_one, 'image_two' => 'public/uploads/thumbnail/' . $panel10T_3_two, 'image_three' => 'public/uploads/thumbnail/' . $panel10T_3_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '11 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel11T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel11T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel11T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '11 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel11T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel11T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel11T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                ];
                DB::table('products')->insert($productArr);
            }
        }
        echo "ok_2";
    }

    public function testScriptProThree()
    {
        $records = DB::table('users')
            ->where('status', 'Approved')
            ->whereNotIn('id', [39, 106, 144])
            ->whereNull('company_id')
            ->select(['id', 'company_category'])
            ->get();

        foreach ($records as $record) {
            if ($record->company_category == 1) {

                // 12-PANEL
                $panel12T_1_path_one = env('APP_URL') . "sample/product/12-PANEL/T-1/10002.jpg";
                $panel12T_1_one = date('YmdHis') . "34" . ".jpg";
                Image::make($panel12T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_1_one));

                $panel12T_1_path_two = env('APP_URL') . "sample/product/12-PANEL/T-1/20001.jpg";
                $panel12T_1_two = date('YmdHis') . "35" . ".jpg";
                Image::make($panel12T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_1_two));

                $panel12T_1_path_three = env('APP_URL') . "sample/product/12-PANEL/T-1/20003.jpg";
                $panel12T_1_three = date('YmdHis') . "36" . ".jpg";
                Image::make($panel12T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_1_three));

                $panel12T_2_path_one = env('APP_URL') . "sample/product/12-PANEL/T-2/10002.jpg";
                $panel12T_2_one = date('YmdHis') . "37" . ".jpg";
                Image::make($panel12T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_2_one));

                $panel12T_2_path_two = env('APP_URL') . "sample/product/12-PANEL/T-2/20001.jpg";
                $panel12T_2_two = date('YmdHis') . "38" . ".jpg";
                Image::make($panel12T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_2_two));

                $panel12T_2_path_three = env('APP_URL') . "sample/product/12-PANEL/T-2/20003.jpg";
                $panel12T_2_three = date('YmdHis') . "39" . ".jpg";
                Image::make($panel12T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_2_three));

                $panel12T_3_path_one = env('APP_URL') . "sample/product/12-PANEL/T-3/10002.jpg";
                $panel12T_3_one = date('YmdHis') . "40" . ".jpg";
                Image::make($panel12T_3_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_3_one));

                $panel12T_3_path_two = env('APP_URL') . "sample/product/12-PANEL/T-3/20001.jpg";
                $panel12T_3_two = date('YmdHis') . "41" . ".jpg";
                Image::make($panel12T_3_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_3_two));

                $panel12T_3_path_three = env('APP_URL') . "sample/product/12-PANEL/T-3/20003.jpg";
                $panel12T_3_three = date('YmdHis') . "42" . ".jpg";
                Image::make($panel12T_3_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_3_three));

                // 13-PANEL
                $panel13T_1_path_one = env('APP_URL') . "sample/product/13-PANEL/T-1/10002.jpg";
                $panel13T_1_one = date('YmdHis') . "43" . ".jpg";
                Image::make($panel13T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_1_one));

                $panel13T_1_path_two = env('APP_URL') . "sample/product/13-PANEL/T-1/20001.jpg";
                $panel13T_1_two = date('YmdHis') . "44" . ".jpg";
                Image::make($panel13T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_1_two));

                $panel13T_1_path_three = env('APP_URL') . "sample/product/13-PANEL/T-1/20003.jpg";
                $panel13T_1_three = date('YmdHis') . "45" . ".jpg";
                Image::make($panel13T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_1_three));

                $panel13T_2_path_one = env('APP_URL') . "sample/product/13-PANEL/T-2/10002.jpg";
                $panel13T_2_one = date('YmdHis') . "46" . ".jpg";
                Image::make($panel13T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_2_one));

                $panel13T_2_path_two = env('APP_URL') . "sample/product/13-PANEL/T-2/20001.jpg";
                $panel13T_2_two = date('YmdHis') . "47" . ".jpg";
                Image::make($panel13T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_2_two));

                $panel13T_2_path_three = env('APP_URL') . "sample/product/13-PANEL/T-2/20003.jpg";
                $panel13T_2_three = date('YmdHis') . "48" . ".jpg";
                Image::make($panel13T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_2_three));

                $productArr = [
                    ['name' => '12 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel12T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel12T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel12T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '12 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel12T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel12T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel12T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '12 Panel_T-3', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel12T_3_one, 'image_two' => 'public/uploads/thumbnail/' . $panel12T_3_two, 'image_three' => 'public/uploads/thumbnail/' . $panel12T_3_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '13 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel13T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel13T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel13T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '13 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel13T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel13T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel13T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                ];
                DB::table('products')->insert($productArr);
            }
        }
        echo "ok_3";
    }

    public function testScriptProFour()
    {
        $records = DB::table('users')
            ->where('status', 'Approved')
            ->whereNotIn('id', [39, 106, 144])
            ->whereNull('company_id')
            ->select(['id', 'company_category'])
            ->get();

        foreach ($records as $record) {
            if ($record->company_category == 1) {
                // 14-PANEL
                $panel14T_1_path_one = env('APP_URL') . "sample/product/14-PANEL/T-1/10002.jpg";
                $panel14T_1_one = date('YmdHis') . "49" . ".jpg";
                Image::make($panel14T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_1_one));

                $panel14T_1_path_two = env('APP_URL') . "sample/product/14-PANEL/T-1/20001.jpg";
                $panel14T_1_two = date('YmdHis') . "50" . ".jpg";
                Image::make($panel14T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_1_two));

                $panel14T_1_path_three = env('APP_URL') . "sample/product/14-PANEL/T-1/20003.jpg";
                $panel14T_1_three = date('YmdHis') . "51" . ".jpg";
                Image::make($panel14T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_1_three));

                $panel14T_2_path_one = env('APP_URL') . "sample/product/14-PANEL/T-2/10002.jpg";
                $panel14T_2_one = date('YmdHis') . "52" . ".jpg";
                Image::make($panel14T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_2_one));

                $panel14T_2_path_two = env('APP_URL') . "sample/product/14-PANEL/T-2/20001.jpg";
                $panel14T_2_two = date('YmdHis') . "53" . ".jpg";
                Image::make($panel14T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_2_two));

                $panel14T_2_path_three = env('APP_URL') . "sample/product/14-PANEL/T-2/20003.jpg";
                $panel14T_2_three = date('YmdHis') . "54" . ".jpg";
                Image::make($panel14T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_2_three));

                // 15-PANEL
                $panel15T_1_path_one = env('APP_URL') . "sample/product/15-PANEL/T-1/10002.jpg";
                $panel15T_1_one = date('YmdHis') . "55" . ".jpg";
                Image::make($panel15T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_1_one));

                $panel15T_1_path_two = env('APP_URL') . "sample/product/15-PANEL/T-1/20001.jpg";
                $panel15T_1_two = date('YmdHis') . "56" . ".jpg";
                Image::make($panel15T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_1_two));

                $panel15T_1_path_three = env('APP_URL') . "sample/product/15-PANEL/T-1/20003.jpg";
                $panel15T_1_three = date('YmdHis') . "57" . ".jpg";
                Image::make($panel15T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_1_three));

                $panel15T_2_path_one = env('APP_URL') . "sample/product/15-PANEL/T-2/10002.jpg";
                $panel15T_2_one = date('YmdHis') . "58" . ".jpg";
                Image::make($panel15T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_2_one));

                $panel15T_2_path_two = env('APP_URL') . "sample/product/15-PANEL/T-2/20001.jpg";
                $panel15T_2_two = date('YmdHis') . "59" . ".jpg";
                Image::make($panel15T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_2_two));

                $panel15T_2_path_three = env('APP_URL') . "sample/product/15-PANEL/T-2/20003.jpg";
                $panel15T_2_three = date('YmdHis') . "60" . ".jpg";
                Image::make($panel15T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_2_three));

                // 16-PANEL
                $panel16T_1_path_one = env('APP_URL') . "sample/product/16-PANEL/T-1/R010001.jpg";
                $panel16T_1_one = date('YmdHis') . "61" . ".jpg";
                Image::make($panel16T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_1_one));

                $panel16T_1_path_two = env('APP_URL') . "sample/product/16-PANEL/T-1/R010002.jpg";
                $panel16T_1_two = date('YmdHis') . "62" . ".jpg";
                Image::make($panel16T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_1_two));

                $panel16T_1_path_three = env('APP_URL') . "sample/product/16-PANEL/T-1/R010003.jpg";
                $panel16T_1_three = date('YmdHis') . "63" . ".jpg";
                Image::make($panel16T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_1_three));

                $panel16T_2_path_one = env('APP_URL') . "sample/product/16-PANEL/T-2/3P6_160002.jpg";
                $panel16T_2_one = date('YmdHis') . "64" . ".jpg";
                Image::make($panel16T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_2_one));

                $panel16T_2_path_two = env('APP_URL') . "sample/product/16-PANEL/T-2/10001.jpg";
                $panel16T_2_two = date('YmdHis') . "65" . ".jpg";
                Image::make($panel16T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_2_two));

                $panel16T_2_path_three = env('APP_URL') . "sample/product/16-PANEL/T-2/10003.jpg";
                $panel16T_2_three = date('YmdHis') . "66" . ".jpg";
                Image::make($panel16T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_2_three));

                $productArr = [

                    ['name' => '14 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel14T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel14T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel14T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '14 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel14T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel14T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel14T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '15 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel15T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel15T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel15T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '15 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel15T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel15T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel15T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '16 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel16T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel16T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel16T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '16 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel16T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel16T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel16T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id]
                ];
                DB::table('products')->insert($productArr);
            }
        }
        echo "ok_4";
    }

    public function testScriptProFive()
    {
        $records = DB::table('users')
            ->where('status', 'Approved')
            ->whereNotIn('id', [39, 106, 144])
            ->whereNull('company_id')
            ->select(['id', 'company_category'])
            ->get();

        foreach ($records as $record) {
            if ($record->company_category == 1) {
                // 17-PANEL
                $panel17T_1_path_one = env('APP_URL') . "sample/product/17-PANEL/T-1/R010002.jpg";
                $panel17T_1_one = date('YmdHis') . "67" . ".jpg";
                Image::make($panel17T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel17T_1_one));

                $panel17T_1_path_two = env('APP_URL') . "sample/product/17-PANEL/T-1/R010001.jpg";
                $panel17T_1_two = date('YmdHis') . "68" . ".jpg";
                Image::make($panel17T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel17T_1_two));

                $panel17T_1_path_three = env('APP_URL') . "sample/product/17-PANEL/T-1/R010003.jpg";
                $panel17T_1_three = date('YmdHis') . "69" . ".jpg";
                Image::make($panel17T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel17T_1_three));

                // 18-PANEL
                $panel18T_1_path_one = env('APP_URL') . "sample/product/18-PANEL/T-1/R010002.jpg";
                $panel18T_1_one = date('YmdHis') . "70" . ".jpg";
                Image::make($panel18T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_1_one));

                $panel18T_1_path_two = env('APP_URL') . "sample/product/18-PANEL/T-1/R010001.jpg";
                $panel18T_1_two = date('YmdHis') . "71" . ".jpg";
                Image::make($panel18T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_1_two));

                $panel18T_1_path_three = env('APP_URL') . "sample/product/18-PANEL/T-1/R010003.jpg";
                $panel18T_1_three = date('YmdHis') . "72" . ".jpg";
                Image::make($panel18T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_1_three));

                $panel18T_2_path_one = env('APP_URL') . "sample/product/18-PANEL/T-2/3P60002.jpg";
                $panel18T_2_one = date('YmdHis') . "73" . ".jpg";
                Image::make($panel18T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_2_one));

                $panel18T_2_path_two = env('APP_URL') . "sample/product/18-PANEL/T-2/Mr0001.jpg";
                $panel18T_2_two = date('YmdHis') . "74" . ".jpg";
                Image::make($panel18T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_2_two));

                $panel18T_2_path_three = env('APP_URL') . "sample/product/18-PANEL/T-2/Mr0003.jpg";
                $panel18T_2_three = date('YmdHis') . "75" . ".jpg";
                Image::make($panel18T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_2_three));

                // 21-PANEL
                $panel21T_1_path_one = env('APP_URL') . "sample/product/21-PANEL/T-1/R010002.jpg";
                $panel21T_1_one = date('YmdHis') . "76" . ".jpg";
                Image::make($panel21T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_1_one));

                $panel21T_1_path_two = env('APP_URL') . "sample/product/21-PANEL/T-1/R010001.jpg";
                $panel21T_1_two = date('YmdHis') . "77" . ".jpg";
                Image::make($panel21T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_1_two));

                $panel21T_1_path_three = env('APP_URL') . "sample/product/21-PANEL/T-1/R010003.jpg";
                $panel21T_1_three = date('YmdHis') . "78" . ".jpg";
                Image::make($panel21T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_1_three));

                $panel21T_2_path_one = env('APP_URL') . "sample/product/21-PANEL/T-2/3P70002.jpg";
                $panel21T_2_one = date('YmdHis') . "79" . ".jpg";
                Image::make($panel21T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_2_one));

                $panel21T_2_path_two = env('APP_URL') . "sample/product/21-PANEL/T-2/Mr0001.jpg";
                $panel21T_2_two = date('YmdHis') . "80" . ".jpg";
                Image::make($panel21T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_2_two));

                $panel21T_2_path_three = env('APP_URL') . "sample/product/21-PANEL/T-2/Mr0003.jpg";
                $panel21T_2_three = date('YmdHis') . "81" . ".jpg";
                Image::make($panel21T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_2_three));

                // 24-PANEL
                $panel24T_1_path_one = env('APP_URL') . "sample/product/24-PANEL/T-1/R010002.jpg";
                $panel24T_1_one = date('YmdHis') . "82" . ".jpg";
                Image::make($panel24T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_1_one));

                $panel24T_1_path_two = env('APP_URL') . "sample/product/24-PANEL/T-1/R010001.jpg";
                $panel24T_1_two = date('YmdHis') . "83" . ".jpg";
                Image::make($panel24T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_1_two));

                $panel24T_1_path_three = env('APP_URL') . "sample/product/24-PANEL/T-1/R010003.jpg";
                $panel24T_1_three = date('YmdHis') . "84" . ".jpg";
                Image::make($panel24T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_1_three));

                $panel24T_2_path_one = env('APP_URL') . "sample/product/24-PANEL/T-2/10002.jpg";
                $panel24T_2_one = date('YmdHis') . "85" . ".jpg";
                Image::make($panel24T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_2_one));

                $panel24T_2_path_two = env('APP_URL') . "sample/product/24-PANEL/T-2/SSEMH0817-Mr0001.jpg";
                $panel24T_2_two = date('YmdHis') . "86" . ".jpg";
                Image::make($panel24T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_2_two));

                $panel24T_2_path_three = env('APP_URL') . "sample/product/24-PANEL/T-2/SSEMH0817-Mr0003.jpg";
                $panel24T_2_three = date('YmdHis') . "87" . ".jpg";
                Image::make($panel24T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_2_three));

                $productArr = [
                    ['name' => '17 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel17T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel17T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel17T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '18 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel18T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel18T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel18T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '18 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel18T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel18T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel18T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '21 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel21T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel21T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel21T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '21 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel21T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel21T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel21T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '24 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel24T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel24T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel24T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '24 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel24T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel24T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel24T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id]
                ];
                DB::table('products')->insert($productArr);
            }
        }
        echo "ok_5";
    }

    public function testScriptProSix()
    {
        $records = DB::table('users')
            ->where('status', 'Approved')
            ->whereNotIn('id', [39, 106, 144])
            ->whereNull('company_id')
            ->select(['id', 'company_category'])
            ->get();

        foreach ($records as $record) {
            if ($record->company_category == 1) {
                // 27-PANEL
                $panel27T_1_path_one = env('APP_URL') . "sample/product/27-PANEL/T-1/R010002.jpg";
                $panel27T_1_one = date('YmdHis') . "88" . ".jpg";
                Image::make($panel27T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_1_one));

                $panel27T_1_path_two = env('APP_URL') . "sample/product/27-PANEL/T-1/R010001.jpg";
                $panel27T_1_two = date('YmdHis') . "89" . ".jpg";
                Image::make($panel27T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_1_two));

                $panel27T_1_path_three = env('APP_URL') . "sample/product/27-PANEL/T-1/R010003.jpg";
                $panel27T_1_three = date('YmdHis') . "90" . ".jpg";
                Image::make($panel27T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_1_three));

                $panel27T_2_path_one = env('APP_URL') . "sample/product/27-PANEL/T-2/10002.jpg";
                $panel27T_2_one = date('YmdHis') . "91" . ".jpg";
                Image::make($panel27T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_2_one));

                $panel27T_2_path_two = env('APP_URL') . "sample/product/27-PANEL/T-2/R0_SSEMH0952-Mr0001.jpg";
                $panel27T_2_two = date('YmdHis') . "92" . ".jpg";
                Image::make($panel27T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_2_two));

                $panel27T_2_path_three = env('APP_URL') . "sample/product/27-PANEL/T-2/R0_SSEMH0952-Mr0003.jpg";
                $panel27T_2_three = date('YmdHis') . "93" . ".jpg";
                Image::make($panel27T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_2_three));

                // 29-PANEL
                $panel29T_1_path_one = env('APP_URL') . "sample/product/29-PANEL/T-1/R010001.jpg";
                $panel29T_1_one = date('YmdHis') . "94" . ".jpg";
                Image::make($panel29T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_1_one));

                $panel29T_1_path_two = env('APP_URL') . "sample/product/29-PANEL/T-1/R010002.jpg";
                $panel29T_1_two = date('YmdHis') . "95" . ".jpg";
                Image::make($panel29T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_1_two));

                $panel29T_1_path_three = env('APP_URL') . "sample/product/29-PANEL/T-1/R010003.jpg";
                $panel29T_1_three = date('YmdHis') . "96" . ".jpg";
                Image::make($panel29T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_1_three));

                $panel29T_2_path_one = env('APP_URL') . "sample/product/29-PANEL/T-2/3P100002.jpg";
                $panel29T_2_one = date('YmdHis') . "97" . ".jpg";
                Image::make($panel29T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_2_one));

                $panel29T_2_path_two = env('APP_URL') . "sample/product/29-PANEL/T-2/R010001.jpg";
                $panel29T_2_two = date('YmdHis') . "98" . ".jpg";
                Image::make($panel29T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_2_two));

                $panel29T_2_path_three = env('APP_URL') . "sample/product/29-PANEL/T-2/R010003.jpg";
                $panel29T_2_three = date('YmdHis') . "99" . ".jpg";
                Image::make($panel29T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_2_three));


                // 30-PANEL
                $panel30T_1_path_one = env('APP_URL') . "sample/product/30-PANEL/T-1/R010001.jpg";
                $panel30T_1_one = date('YmdHis') . "100" . ".jpg";
                Image::make($panel30T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_1_one));

                $panel30T_1_path_two = env('APP_URL') . "sample/product/30-PANEL/T-1/R010002.jpg";
                $panel30T_1_two = date('YmdHis') . "101" . ".jpg";
                Image::make($panel30T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_1_two));

                $panel30T_1_path_three = env('APP_URL') . "sample/product/30-PANEL/T-1/R010003.jpg";
                $panel30T_1_three = date('YmdHis') . "102" . ".jpg";
                Image::make($panel30T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_1_three));

                $panel30T_2_path_one = env('APP_URL') . "sample/product/30-PANEL/T-2/3P100002.jpg";
                $panel30T_2_one = date('YmdHis') . "103" . ".jpg";
                Image::make($panel30T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_2_one));

                $panel30T_2_path_two = env('APP_URL') . "sample/product/30-PANEL/T-2/3P1010001.jpg";
                $panel30T_2_two = date('YmdHis') . "104" . ".jpg";
                Image::make($panel30T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_2_two));

                $panel30T_2_path_three = env('APP_URL') . "sample/product/30-PANEL/T-2/3P1010003.jpg";
                $panel30T_2_three = date('YmdHis') . "105" . ".jpg";
                Image::make($panel30T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_2_three));


                $productArr = [
                    ['name' => '27 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel27T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel27T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel27T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '27 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel27T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel27T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel27T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '29 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel29T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel29T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel29T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '29 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel29T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel29T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel29T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '30 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel30T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel30T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel30T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '30 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel30T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel30T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel30T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                ];
                DB::table('products')->insert($productArr);
            }
        }
        echo "ok_6";
    }

    public function testScriptTwo()
    {
        $records = DB::table('users')
            ->where('status', 'Approved')
            ->whereNotIn('id', [39, 106, 144])
            ->whereNull('company_id')
            ->select(['id', 'company_category'])
            ->get();

        foreach ($records as $record) {
            if ($record->company_category == 1) {
                $path_one = env('APP_URL') . "sample/testimonial/t-1.png";
                $filename_one = date('YmdHis') . "106" . ".png";
                Image::make($path_one)->save(storage_path("app/public/uploads/thumbnail/" . $filename_one));

                $path_two = env('APP_URL') . "sample/testimonial/t-2.png";
                $filename_two = date('YmdHis') . "107" . ".png";
                Image::make($path_two)->save(storage_path("app/public/uploads/thumbnail/" . $filename_two));

                $path_three = env('APP_URL') . "sample/testimonial/t-3.png";
                $filename_three = date('YmdHis') . "108" . ".png";
                Image::make($path_three)->save(storage_path("app/public/uploads/thumbnail/" . $filename_three));

                $testimonialArr = [
                    [
                        'name' => 'Residential Testimonial',
                        'client_name_one' => 'Rahulbhai patel',
                        'client_name_two' => 'Payalben hirpara',
                        'client_name_three' => 'Kiranbhai prajapati',
                        'description_one' => 'I recently installed solar on my rooftop. Thank You team for neat and clean Installation on my rooftop. Also, received my subsidy. Great Work team. Thanks',
                        'description_two' => 'When you have empty roof then why to pay for electricity bill? Thank You for end to end guidance. Your staff is very professional and friendly. Thanks for making my roof solarize! Superb Work by Team.',
                        'description_three' => 'One of the best decision of my life to go solar! I really appreciate your product quality and workmanship. In last 6 month, my plant has generated more than 2000 Units and counting. I strongly recommend everyone to go solar as soon as possible. Thank You',
                        'rating_one' => 5,
                        'rating_two' => 5,
                        'rating_three' => 5,
                        'image_one' => 'public/uploads/thumbnail/' . $filename_one,
                        'image_two' => 'public/uploads/thumbnail/' . $filename_two,
                        'image_three' => 'public/uploads/thumbnail/' . $filename_three,
                        'status' => 0,
                        'is_default' => 1,
                        'user_id' => $record->id,
                        'company_id' => $record->id,
                    ],

                ];
                DB::table('testimonials')->insert($testimonialArr);
            }
            /*$taxArr = [
                ['name' => 0.00, 'user_id' => $record->id, 'company_id' => $record->id],
                ['name' => 5.00, 'user_id' => $record->id, 'company_id' => $record->id],
                ['name' => 12.00, 'user_id' => $record->id, 'company_id' => $record->id],
                ['name' => 18.00, 'user_id' => $record->id, 'company_id' => $record->id],
                ['name' => 28.00, 'user_id' => $record->id, 'company_id' => $record->id]
            ];
            DB::table('taxs')->insert($taxArr);

            $catArr = [
                ['name' => 'B2B', 'user_id' => $record->id, 'company_id' => $record->id],
                ['name' => 'B2C', 'user_id' => $record->id, 'company_id' => $record->id]
            ];
            DB::table('customer_categories')->insert($catArr);

            $leadArr = [
                ['name' => 'Social Media', 'user_id' => $record->id, 'company_id' => $record->id],
                ['name' => 'Reference', 'user_id' => $record->id, 'company_id' => $record->id],
                ['name' => 'Physical Marketing', 'user_id' => $record->id, 'company_id' => $record->id]
            ];
            DB::table('customer_leads')->insert($leadArr);*/
        }
        echo "ok_7";
    }

    public function testScriptShort()
    {
        $records = DB::table('users')
            ->where('status', 'Approved')
            ->whereNotIn('id', [39, 106, 144])
            ->whereNull('company_id')
            ->select(['id', 'company_category'])
            ->get();

        foreach ($records as $record) {
            if ($record->company_category == 1) {
                $unitArr = [
                    ['name' => 'Site', 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => 'Kw', 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => 'Nos', 'user_id' => $record->id, 'company_id' => $record->id]
                ];
                DB::table('units')->insert($unitArr);

                $units = Unit::where([["company_id", "=", $record->id], ["name", "=", "Site"]])->orderBy('id', 'ASC')->select("id")->first();
                $itemArr = [
                    ['name' => '3.015 KW on grid rooftop solar (URBAN 335W -09P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (10 Qty)
- Inverter 3.3 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 59,243 INR)'), 'item_type' => 'Goods', 'inter_state' => 0, 'intra_state' => 0, 'unit_id' => $units->id, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sale_price' => 87988, 'sales_flag' => 1, 'status' => 0, 'purchase_flag' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '3.35 KW on grid rooftop solar (URBAN 335W -10P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (10 Qty)
- Inverter 3.3 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 59,243 INR)'), 'item_type' => 'Goods', 'inter_state' => 0, 'intra_state' => 0, 'unit_id' => $units->id, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sale_price' => 100874, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '3.685 KW on grid rooftop solar (URBAN 335W -11P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (11 Qty)
- Inverter 3.6 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 61,575 INR)'), 'item_type' => 'Goods', 'inter_state' => 0, 'intra_state' => 0, 'unit_id' => $units->id, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 114553, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '3.015 KW on grid rooftop solar (RURAL 335W 09P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (09 Qty)
- Inverter 3.3 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 56,117 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 91419, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '3.35 KW on grid rooftop solar (RURAL 335W -10P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (10 Qty)
- Inverter 3.3 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 59,243 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 104686, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '3.685 KW on grid rooftop solar (RURAL 335W -11P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (11 Qty)
- Inverter 3.6 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 61,575 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 118747, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '4.02 KW on grid rooftop solar (335W -12P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (12 Qty)
- Inverter 4.2 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 65,493 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 126646, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '4.355 KW on grid rooftop solar (335W -13P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (13 Qty)
- Inverter 4.2 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 67,173 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 140979, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '4.69 KW on grid rooftop solar (335W -14P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (14 Qty)
- Inverter 5 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- TransportationG
- Installation
- 5 years maintenance
  (Subsidy amount – 71,744 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 152419, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '5.025 KW on grid rooftop solar (335W -15P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (15 Qty)
- Inverter 5 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 74,636 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 165538, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '5.36 KW on grid rooftop solar (335W -16P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (16 Qty)
- Inverter 6 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 77,995 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 172092, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '5.695 KW on grid rooftop solar (335W -17P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (17 Qty)
- Inverter 6 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 81,120 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 184597, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '6.03 KW on grid rooftop solar (335W -18P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (18 Qty)
- Inverter 6 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 83,966 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 197382, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '7.035 KW on grid rooftop solar (335W -21P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (21 Qty)
- Inverter 8 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 92,501 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 231735, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '8.04 KW on grid rooftop solar (335W -24P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (24 Qty)
- Inverter 8 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 101,396 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 269160, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '9.045 KW on grid rooftop solar (335W -27P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (27 Qty)
- Inverter 10 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 111,028 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 305847, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '9.715 KW on grid rooftop solar (335W -29P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (29 Qty)
- Inverter 10 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 117,204 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 330550, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                ];
                DB::table('items')->insert($itemArr);
            }
        }
        echo "ok_8";
    }

    public function testScript()
    {

        $records = DB::table('users')
            ->where('status', 'Approved')
            ->whereNull('company_id')
            ->select(['id', 'company_category'])
            ->get();

        foreach ($records as $record) {

            $unitArr = [
                ['name' => 'Site', 'user_id' => $record->id, 'company_id' => $record->id],
                ['name' => 'Kw', 'user_id' => $record->id, 'company_id' => $record->id],
                ['name' => 'Nos', 'user_id' => $record->id, 'company_id' => $record->id]
            ];
            DB::table('units')->insert($unitArr);

            $taxArr = [
                ['name' => 0.00, 'user_id' => $record->id, 'company_id' => $record->id],
                ['name' => 5.00, 'user_id' => $record->id, 'company_id' => $record->id],
                ['name' => 12.00, 'user_id' => $record->id, 'company_id' => $record->id],
                ['name' => 18.00, 'user_id' => $record->id, 'company_id' => $record->id],
                ['name' => 28.00, 'user_id' => $record->id, 'company_id' => $record->id]
            ];
            DB::table('taxs')->insert($taxArr);

            $catArr = [
                ['name' => 'B2B', 'user_id' => $record->id, 'company_id' => $record->id],
                ['name' => 'B2C', 'user_id' => $record->id, 'company_id' => $record->id]
            ];
            DB::table('customer_categories')->insert($catArr);

            $leadArr = [
                ['name' => 'Social Media', 'user_id' => $record->id, 'company_id' => $record->id],
                ['name' => 'Reference', 'user_id' => $record->id, 'company_id' => $record->id],
                ['name' => 'Physical Marketing', 'user_id' => $record->id, 'company_id' => $record->id]
            ];
            DB::table('customer_leads')->insert($leadArr);

            $path_one = env('APP_URL') . "sample/testimonial/t-1.png";
            $filename_one = date('YmdHis') . ".png";
            Image::make($path_one)->save(storage_path("app/public/uploads/thumbnail/" . $filename_one));

            $path_two = env('APP_URL') . "sample/testimonial/t-2.png";
            $filename_two = date('YmdHis') . ".png";
            Image::make($path_two)->save(storage_path("app/public/uploads/thumbnail/" . $filename_two));

            $path_three = env('APP_URL') . "sample/testimonial/t-3.png";
            $filename_three = date('YmdHis') . ".png";
            Image::make($path_three)->save(storage_path("app/public/uploads/thumbnail/" . $filename_three));

            $testimonialArr = [
                [
                    'name' => 'Residential Testimonial',
                    'client_name_one' => 'Rahulbhai patel',
                    'client_name_two' => 'Payalben hirpara',
                    'client_name_three' => 'Kiranbhai prajapati',
                    'description_one' => 'I recently installed solar on my rooftop. Thank You team for neat and clean Installation on my rooftop. Also, received my subsidy. Great Work team. Thanks',
                    'description_two' => 'When you have empty roof then why to pay for electricity bill? Thank You for end to end guidance. Your staff is very professional and friendly. Thanks for making my roof solarize! Superb Work by Team.',
                    'description_three' => 'One of the best decision of my life to go solar! I really appreciate your product quality and workmanship. In last 6 month, my plant has generated more than 2000 Units and counting. I strongly recommend everyone to go solar as soon as possible. Thank You',
                    'rating_one' => 5,
                    'rating_two' => 5,
                    'rating_three' => 5,
                    'image_one' => 'public/uploads/thumbnail/' . $filename_one,
                    'image_two' => 'public/uploads/thumbnail/' . $filename_two,
                    'image_three' => 'public/uploads/thumbnail/' . $filename_three,
                    'status' => 0,
                    'is_default' => 1,
                    'user_id' => $record->id,
                    'company_id' => $record->id,
                ],

            ];
            DB::table('testimonials')->insert($testimonialArr);
            $units = Unit::where([["company_id", "=", $record->id], ["name", "=", "Site"]])->orderBy('id', 'ASC')->select("id")->first();
            if ($record->company_category == 1) {
                $itemArr = [
                    ['name' => '3.015 KW on grid rooftop solar (URBAN 335W -09P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (10 Qty)
- Inverter 3.3 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 59,243 INR)'), 'item_type' => 'Goods', 'inter_state' => 0, 'intra_state' => 0, 'unit_id' => $units->id, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sale_price' => 87988, 'sales_flag' => 1, 'status' => 0, 'purchase_flag' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '3.35 KW on grid rooftop solar (URBAN 335W -10P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (10 Qty)
- Inverter 3.3 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 59,243 INR)'), 'item_type' => 'Goods', 'inter_state' => 0, 'intra_state' => 0, 'unit_id' => $units->id, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sale_price' => 100874, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '3.685 KW on grid rooftop solar (URBAN 335W -11P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (11 Qty)
- Inverter 3.6 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 61,575 INR)'), 'item_type' => 'Goods', 'inter_state' => 0, 'intra_state' => 0, 'unit_id' => $units->id, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 114553, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '3.015 KW on grid rooftop solar (RURAL 335W 09P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (09 Qty)
- Inverter 3.3 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 56,117 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 91419, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '3.35 KW on grid rooftop solar (RURAL 335W -10P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (10 Qty)
- Inverter 3.3 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 59,243 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 104686, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '3.685 KW on grid rooftop solar (RURAL 335W -11P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (11 Qty)
- Inverter 3.6 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 61,575 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 118747, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '4.02 KW on grid rooftop solar (335W -12P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (12 Qty)
- Inverter 4.2 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 65,493 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 126646, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '4.355 KW on grid rooftop solar (335W -13P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (13 Qty)
- Inverter 4.2 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 67,173 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 140979, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '4.69 KW on grid rooftop solar (335W -14P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (14 Qty)
- Inverter 5 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- TransportationG
- Installation
- 5 years maintenance
  (Subsidy amount – 71,744 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 152419, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '5.025 KW on grid rooftop solar (335W -15P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (15 Qty)
- Inverter 5 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 74,636 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 165538, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '5.36 KW on grid rooftop solar (335W -16P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (16 Qty)
- Inverter 6 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 77,995 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 172092, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '5.695 KW on grid rooftop solar (335W -17P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (17 Qty)
- Inverter 6 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 81,120 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 184597, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '6.03 KW on grid rooftop solar (335W -18P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (18 Qty)
- Inverter 6 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 83,966 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 197382, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '7.035 KW on grid rooftop solar (335W -21P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (21 Qty)
- Inverter 8 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 92,501 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 231735, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '8.04 KW on grid rooftop solar (335W -24P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (24 Qty)
- Inverter 8 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 101,396 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 269160, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '9.045 KW on grid rooftop solar (335W -27P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (27 Qty)
- Inverter 10 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 111,028 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 305847, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '9.715 KW on grid rooftop solar (335W -29P)', 'description' => trim('- 340-Watt Solar panel– 25 years warranty (29 Qty)
- Inverter 10 kW with WIFI stick – 10 years warranty (1 Qty)
- AC protection device
- DC protection device
- DC / AC copper cable
- Earthing rod Copper coated 1.5 meter (Qty-3)
- Earthing 25 SQ Aluminium
- Lightning arrestor (Qty – 1)
- PVC conduit as per site
- SS cable tie
- Transportation
- Installation
- 5 years maintenance
  (Subsidy amount – 117,204 INR)'), 'item_type' => 'Goods', 'unit_id' => $units->id, 'inter_state' => 0, 'intra_state' => 0, 'cost_price' => 0, 'tax_preference' => 'Non-Taxable', 'sales_price' => 330550, 'sales_flag' => 1, 'purchase_flag' => 0, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                ];
                DB::table('items')->insert($itemArr);

                // 8-PANEL
                $panel8T_1_path_one = env('APP_URL') . "sample/product/8-PANEL/T-1/8-panel-1.jpg";
                $panel8T_1_one = date('YmdHis') . ".jpg";
                Image::make($panel8T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_1_one));

                $panel8T_1_path_two = env('APP_URL') . "sample/product/8-PANEL/T-1/8-panel-2.jpg";
                $panel8T_1_two = date('YmdHis') . ".jpg";
                Image::make($panel8T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_1_two));

                $panel8T_1_path_three = env('APP_URL') . "sample/product/8-PANEL/T-1/8-panel-3.jpg";
                $panel8T_1_three = date('YmdHis') . ".jpg";
                Image::make($panel8T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_1_three));

                $panel8T_2_path_one = env('APP_URL') . "sample/product/8-PANEL/T-2/10002.jpg";
                $panel8T_2_one = date('YmdHis') . ".jpg";
                Image::make($panel8T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_2_one));

                $panel8T_2_path_two = env('APP_URL') . "sample/product/8-PANEL/T-2/20001.jpg";
                $panel8T_2_two = date('YmdHis') . ".jpg";
                Image::make($panel8T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_2_two));

                $panel8T_2_path_three = env('APP_URL') . "sample/product/8-PANEL/T-2/20003.jpg";
                $panel8T_2_three = date('YmdHis') . ".jpg";
                Image::make($panel8T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_2_three));

                $panel8T_3_path_one = env('APP_URL') . "sample/product/8-PANEL/T-3/10002.jpg";
                $panel8T_3_one = date('YmdHis') . ".jpg";
                Image::make($panel8T_3_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_3_one));

                $panel8T_3_path_two = env('APP_URL') . "sample/product/8-PANEL/T-3/20001.jpg";
                $panel8T_3_two = date('YmdHis') . ".jpg";
                Image::make($panel8T_3_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_3_two));

                $panel8T_3_path_three = env('APP_URL') . "sample/product/8-PANEL/T-3/20003.jpg";
                $panel8T_3_three = date('YmdHis') . ".jpg";
                Image::make($panel8T_3_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel8T_3_three));

                // 9-PANEL
                $panel9T_1_path_one = env('APP_URL') . "sample/product/9-PANEL/T-1/10002.jpg";
                $panel9T_1_one = date('YmdHis') . ".jpg";
                Image::make($panel9T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_1_one));

                $panel9T_1_path_two = env('APP_URL') . "sample/product/9-PANEL/T-1/20001.jpg";
                $panel9T_1_two = date('YmdHis') . ".jpg";
                Image::make($panel9T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_1_two));

                $panel9T_1_path_three = env('APP_URL') . "sample/product/9-PANEL/T-1/20003.jpg";
                $panel9T_1_three = date('YmdHis') . ".jpg";
                Image::make($panel9T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_1_three));

                $panel9T_2_path_one = env('APP_URL') . "sample/product/9-PANEL/T-2/10002.jpg";
                $panel9T_2_one = date('YmdHis') . ".jpg";
                Image::make($panel9T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_2_one));

                $panel9T_2_path_two = env('APP_URL') . "sample/product/9-PANEL/T-2/20001.jpg";
                $panel9T_2_two = date('YmdHis') . ".jpg";
                Image::make($panel9T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_2_two));

                $panel9T_2_path_three = env('APP_URL') . "sample/product/9-PANEL/T-2/20003.jpg";
                $panel9T_2_three = date('YmdHis') . ".jpg";
                Image::make($panel9T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_2_three));

                $panel9T_3_path_one = env('APP_URL') . "sample/product/9-PANEL/T-3/10002.jpg";
                $panel9T_3_one = date('YmdHis') . ".jpg";
                Image::make($panel9T_3_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_3_one));

                $panel9T_3_path_two = env('APP_URL') . "sample/product/9-PANEL/T-3/20001.jpg";
                $panel9T_3_two = date('YmdHis') . ".jpg";
                Image::make($panel9T_3_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_3_two));

                $panel9T_3_path_three = env('APP_URL') . "sample/product/9-PANEL/T-3/20003.jpg";
                $panel9T_3_three = date('YmdHis') . ".jpg";
                Image::make($panel9T_3_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel9T_3_three));

                // 10-PANEL
                $panel10T_1_path_one = env('APP_URL') . "sample/product/10-PANEL/T-1/10002.jpg";
                $panel10T_1_one = date('YmdHis') . ".jpg";
                Image::make($panel10T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_1_one));

                $panel10T_1_path_two = env('APP_URL') . "sample/product/10-PANEL/T-1/20001.jpg";
                $panel10T_1_two = date('YmdHis') . ".jpg";
                Image::make($panel10T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_1_two));

                $panel10T_1_path_three = env('APP_URL') . "sample/product/10-PANEL/T-1/20003.jpg";
                $panel10T_1_three = date('YmdHis') . ".jpg";
                Image::make($panel10T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_1_three));

                $panel10T_2_path_one = env('APP_URL') . "sample/product/10-PANEL/T-2/10002.jpg";
                $panel10T_2_one = date('YmdHis') . ".jpg";
                Image::make($panel10T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_2_one));

                $panel10T_2_path_two = env('APP_URL') . "sample/product/10-PANEL/T-2/20001.jpg";
                $panel10T_2_two = date('YmdHis') . ".jpg";
                Image::make($panel10T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_2_two));

                $panel10T_2_path_three = env('APP_URL') . "sample/product/10-PANEL/T-2/20003.jpg";
                $panel10T_2_three = date('YmdHis') . ".jpg";
                Image::make($panel10T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_2_three));

                $panel10T_3_path_one = env('APP_URL') . "sample/product/10-PANEL/T-3/10002.jpg";
                $panel10T_3_one = date('YmdHis') . ".jpg";
                Image::make($panel10T_3_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_3_one));

                $panel10T_3_path_two = env('APP_URL') . "sample/product/10-PANEL/T-3/20001.jpg";
                $panel10T_3_two = date('YmdHis') . ".jpg";
                Image::make($panel10T_3_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_3_two));

                $panel10T_3_path_three = env('APP_URL') . "sample/product/10-PANEL/T-3/20003.jpg";
                $panel10T_3_three = date('YmdHis') . ".jpg";
                Image::make($panel10T_3_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel10T_3_three));

                // 11-PANEL
                $panel11T_1_path_one = env('APP_URL') . "sample/product/11-PANEL/T-1/10002.jpg";
                $panel11T_1_one = date('YmdHis') . ".jpg";
                Image::make($panel11T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_1_one));

                $panel11T_1_path_two = env('APP_URL') . "sample/product/11-PANEL/T-1/20001.jpg";
                $panel11T_1_two = date('YmdHis') . ".jpg";
                Image::make($panel11T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_1_two));

                $panel11T_1_path_three = env('APP_URL') . "sample/product/11-PANEL/T-1/20003.jpg";
                $panel11T_1_three = date('YmdHis') . ".jpg";
                Image::make($panel11T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_1_three));

                $panel11T_2_path_one = env('APP_URL') . "sample/product/11-PANEL/T-2/10002.jpg";
                $panel11T_2_one = date('YmdHis') . ".jpg";
                Image::make($panel11T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_2_one));

                $panel11T_2_path_two = env('APP_URL') . "sample/product/11-PANEL/T-2/20001.jpg";
                $panel11T_2_two = date('YmdHis') . ".jpg";
                Image::make($panel11T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_2_two));

                $panel11T_2_path_three = env('APP_URL') . "sample/product/11-PANEL/T-2/20003.jpg";
                $panel11T_2_three = date('YmdHis') . ".jpg";
                Image::make($panel11T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel11T_2_three));

                // 12-PANEL
                $panel12T_1_path_one = env('APP_URL') . "sample/product/12-PANEL/T-1/10002.jpg";
                $panel12T_1_one = date('YmdHis') . ".jpg";
                Image::make($panel12T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_1_one));

                $panel12T_1_path_two = env('APP_URL') . "sample/product/12-PANEL/T-1/20001.jpg";
                $panel12T_1_two = date('YmdHis') . ".jpg";
                Image::make($panel12T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_1_two));

                $panel12T_1_path_three = env('APP_URL') . "sample/product/12-PANEL/T-1/20003.jpg";
                $panel12T_1_three = date('YmdHis') . ".jpg";
                Image::make($panel12T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_1_three));

                $panel12T_2_path_one = env('APP_URL') . "sample/product/12-PANEL/T-2/10002.jpg";
                $panel12T_2_one = date('YmdHis') . ".jpg";
                Image::make($panel12T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_2_one));

                $panel12T_2_path_two = env('APP_URL') . "sample/product/12-PANEL/T-2/20001.jpg";
                $panel12T_2_two = date('YmdHis') . ".jpg";
                Image::make($panel12T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_2_two));

                $panel12T_2_path_three = env('APP_URL') . "sample/product/12-PANEL/T-2/20003.jpg";
                $panel12T_2_three = date('YmdHis') . ".jpg";
                Image::make($panel12T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_2_three));

                $panel12T_3_path_one = env('APP_URL') . "sample/product/12-PANEL/T-3/10002.jpg";
                $panel12T_3_one = date('YmdHis') . ".jpg";
                Image::make($panel12T_3_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_3_one));

                $panel12T_3_path_two = env('APP_URL') . "sample/product/12-PANEL/T-3/20001.jpg";
                $panel12T_3_two = date('YmdHis') . ".jpg";
                Image::make($panel12T_3_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_3_two));

                $panel12T_3_path_three = env('APP_URL') . "sample/product/12-PANEL/T-3/20003.jpg";
                $panel12T_3_three = date('YmdHis') . ".jpg";
                Image::make($panel12T_3_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel12T_3_three));

                // 13-PANEL
                $panel13T_1_path_one = env('APP_URL') . "sample/product/13-PANEL/T-1/10002.jpg";
                $panel13T_1_one = date('YmdHis') . ".jpg";
                Image::make($panel13T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_1_one));

                $panel13T_1_path_two = env('APP_URL') . "sample/product/13-PANEL/T-1/20001.jpg";
                $panel13T_1_two = date('YmdHis') . ".jpg";
                Image::make($panel13T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_1_two));

                $panel13T_1_path_three = env('APP_URL') . "sample/product/13-PANEL/T-1/20003.jpg";
                $panel13T_1_three = date('YmdHis') . ".jpg";
                Image::make($panel13T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_1_three));

                $panel13T_2_path_one = env('APP_URL') . "sample/product/13-PANEL/T-2/10002.jpg";
                $panel13T_2_one = date('YmdHis') . ".jpg";
                Image::make($panel13T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_2_one));

                $panel13T_2_path_two = env('APP_URL') . "sample/product/13-PANEL/T-2/20001.jpg";
                $panel13T_2_two = date('YmdHis') . ".jpg";
                Image::make($panel13T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_2_two));

                $panel13T_2_path_three = env('APP_URL') . "sample/product/13-PANEL/T-2/20003.jpg";
                $panel13T_2_three = date('YmdHis') . ".jpg";
                Image::make($panel13T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel13T_2_three));

                // 14-PANEL
                $panel14T_1_path_one = env('APP_URL') . "sample/product/14-PANEL/T-1/10002.jpg";
                $panel14T_1_one = date('YmdHis') . ".jpg";
                Image::make($panel14T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_1_one));

                $panel14T_1_path_two = env('APP_URL') . "sample/product/14-PANEL/T-1/20001.jpg";
                $panel14T_1_two = date('YmdHis') . ".jpg";
                Image::make($panel14T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_1_two));

                $panel14T_1_path_three = env('APP_URL') . "sample/product/14-PANEL/T-1/20003.jpg";
                $panel14T_1_three = date('YmdHis') . ".jpg";
                Image::make($panel14T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_1_three));

                $panel14T_2_path_one = env('APP_URL') . "sample/product/14-PANEL/T-2/10002.jpg";
                $panel14T_2_one = date('YmdHis') . ".jpg";
                Image::make($panel14T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_2_one));

                $panel14T_2_path_two = env('APP_URL') . "sample/product/14-PANEL/T-2/20001.jpg";
                $panel14T_2_two = date('YmdHis') . ".jpg";
                Image::make($panel14T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_2_two));

                $panel14T_2_path_three = env('APP_URL') . "sample/product/14-PANEL/T-2/20003.jpg";
                $panel14T_2_three = date('YmdHis') . ".jpg";
                Image::make($panel14T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel14T_2_three));

                // 15-PANEL
                $panel15T_1_path_one = env('APP_URL') . "sample/product/15-PANEL/T-1/10002.jpg";
                $panel15T_1_one = date('YmdHis') . ".jpg";
                Image::make($panel15T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_1_one));

                $panel15T_1_path_two = env('APP_URL') . "sample/product/15-PANEL/T-1/20001.jpg";
                $panel15T_1_two = date('YmdHis') . ".jpg";
                Image::make($panel15T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_1_two));

                $panel15T_1_path_three = env('APP_URL') . "sample/product/15-PANEL/T-1/20003.jpg";
                $panel15T_1_three = date('YmdHis') . ".jpg";
                Image::make($panel15T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_1_three));

                $panel15T_2_path_one = env('APP_URL') . "sample/product/15-PANEL/T-2/10002.jpg";
                $panel15T_2_one = date('YmdHis') . ".jpg";
                Image::make($panel15T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_2_one));

                $panel15T_2_path_two = env('APP_URL') . "sample/product/15-PANEL/T-2/20001.jpg";
                $panel15T_2_two = date('YmdHis') . ".jpg";
                Image::make($panel15T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_2_two));

                $panel15T_2_path_three = env('APP_URL') . "sample/product/15-PANEL/T-2/20003.jpg";
                $panel15T_2_three = date('YmdHis') . ".jpg";
                Image::make($panel15T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel15T_2_three));

                // 16-PANEL
                $panel16T_1_path_one = env('APP_URL') . "sample/product/16-PANEL/T-1/R010001.jpg";
                $panel16T_1_one = date('YmdHis') . ".jpg";
                Image::make($panel16T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_1_one));

                $panel16T_1_path_two = env('APP_URL') . "sample/product/16-PANEL/T-1/R010002.jpg";
                $panel16T_1_two = date('YmdHis') . ".jpg";
                Image::make($panel16T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_1_two));

                $panel16T_1_path_three = env('APP_URL') . "sample/product/16-PANEL/T-1/R010003.jpg";
                $panel16T_1_three = date('YmdHis') . ".jpg";
                Image::make($panel16T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_1_three));

                $panel16T_2_path_one = env('APP_URL') . "sample/product/16-PANEL/T-2/3P6_160002.jpg";
                $panel16T_2_one = date('YmdHis') . ".jpg";
                Image::make($panel16T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_2_one));

                $panel16T_2_path_two = env('APP_URL') . "sample/product/16-PANEL/T-2/10001.jpg";
                $panel16T_2_two = date('YmdHis') . ".jpg";
                Image::make($panel16T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_2_two));

                $panel16T_2_path_three = env('APP_URL') . "sample/product/16-PANEL/T-2/10003.jpg";
                $panel16T_2_three = date('YmdHis') . ".jpg";
                Image::make($panel16T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel16T_2_three));

                // 17-PANEL
                $panel17T_1_path_one = env('APP_URL') . "sample/product/17-PANEL/T-1/R010002.jpg";
                $panel17T_1_one = date('YmdHis') . ".jpg";
                Image::make($panel17T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel17T_1_one));

                $panel17T_1_path_two = env('APP_URL') . "sample/product/17-PANEL/T-1/R010001.jpg";
                $panel17T_1_two = date('YmdHis') . ".jpg";
                Image::make($panel17T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel17T_1_two));

                $panel17T_1_path_three = env('APP_URL') . "sample/product/17-PANEL/T-1/R010003.jpg";
                $panel17T_1_three = date('YmdHis') . ".jpg";
                Image::make($panel17T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel17T_1_three));

                // 18-PANEL
                $panel18T_1_path_one = env('APP_URL') . "sample/product/18-PANEL/T-1/R010002.jpg";
                $panel18T_1_one = date('YmdHis') . ".jpg";
                Image::make($panel18T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_1_one));

                $panel18T_1_path_two = env('APP_URL') . "sample/product/18-PANEL/T-1/R010001.jpg";
                $panel18T_1_two = date('YmdHis') . ".jpg";
                Image::make($panel18T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_1_two));

                $panel18T_1_path_three = env('APP_URL') . "sample/product/18-PANEL/T-1/R010003.jpg";
                $panel18T_1_three = date('YmdHis') . ".jpg";
                Image::make($panel18T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_1_three));

                $panel18T_2_path_one = env('APP_URL') . "sample/product/18-PANEL/T-2/3P60002.jpg";
                $panel18T_2_one = date('YmdHis') . ".jpg";
                Image::make($panel18T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_2_one));

                $panel18T_2_path_two = env('APP_URL') . "sample/product/18-PANEL/T-2/Mr0001.jpg";
                $panel18T_2_two = date('YmdHis') . ".jpg";
                Image::make($panel18T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_2_two));

                $panel18T_2_path_three = env('APP_URL') . "sample/product/18-PANEL/T-2/Mr0003.jpg";
                $panel18T_2_three = date('YmdHis') . ".jpg";
                Image::make($panel18T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel18T_2_three));

                // 21-PANEL
                $panel21T_1_path_one = env('APP_URL') . "sample/product/21-PANEL/T-1/R010002.jpg";
                $panel21T_1_one = date('YmdHis') . ".jpg";
                Image::make($panel21T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_1_one));

                $panel21T_1_path_two = env('APP_URL') . "sample/product/21-PANEL/T-1/R010001.jpg";
                $panel21T_1_two = date('YmdHis') . ".jpg";
                Image::make($panel21T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_1_two));

                $panel21T_1_path_three = env('APP_URL') . "sample/product/21-PANEL/T-1/R010003.jpg";
                $panel21T_1_three = date('YmdHis') . ".jpg";
                Image::make($panel21T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_1_three));

                $panel21T_2_path_one = env('APP_URL') . "sample/product/21-PANEL/T-2/3P70002.jpg";
                $panel21T_2_one = date('YmdHis') . ".jpg";
                Image::make($panel21T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_2_one));

                $panel21T_2_path_two = env('APP_URL') . "sample/product/21-PANEL/T-2/Mr0001.jpg";
                $panel21T_2_two = date('YmdHis') . ".jpg";
                Image::make($panel21T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_2_two));

                $panel21T_2_path_three = env('APP_URL') . "sample/product/21-PANEL/T-2/Mr0003.jpg";
                $panel21T_2_three = date('YmdHis') . ".jpg";
                Image::make($panel21T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel21T_2_three));

                // 24-PANEL
                $panel24T_1_path_one = env('APP_URL') . "sample/product/24-PANEL/T-1/R010002.jpg";
                $panel24T_1_one = date('YmdHis') . ".jpg";
                Image::make($panel24T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_1_one));

                $panel24T_1_path_two = env('APP_URL') . "sample/product/24-PANEL/T-1/R010001.jpg";
                $panel24T_1_two = date('YmdHis') . ".jpg";
                Image::make($panel24T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_1_two));

                $panel24T_1_path_three = env('APP_URL') . "sample/product/24-PANEL/T-1/R010003.jpg";
                $panel24T_1_three = date('YmdHis') . ".jpg";
                Image::make($panel24T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_1_three));

                $panel24T_2_path_one = env('APP_URL') . "sample/product/24-PANEL/T-2/10002.jpg";
                $panel24T_2_one = date('YmdHis') . ".jpg";
                Image::make($panel24T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_2_one));

                $panel24T_2_path_two = env('APP_URL') . "sample/product/24-PANEL/T-2/SSEMH0817-Mr0001.jpg";
                $panel24T_2_two = date('YmdHis') . ".jpg";
                Image::make($panel24T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_2_two));

                $panel24T_2_path_three = env('APP_URL') . "sample/product/24-PANEL/T-2/SSEMH0817-Mr0003.jpg";
                $panel24T_2_three = date('YmdHis') . ".jpg";
                Image::make($panel24T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel24T_2_three));

                // 27-PANEL
                $panel27T_1_path_one = env('APP_URL') . "sample/product/27-PANEL/T-1/R010002.jpg";
                $panel27T_1_one = date('YmdHis') . ".jpg";
                Image::make($panel27T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_1_one));

                $panel27T_1_path_two = env('APP_URL') . "sample/product/27-PANEL/T-1/R010001.jpg";
                $panel27T_1_two = date('YmdHis') . ".jpg";
                Image::make($panel27T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_1_two));

                $panel27T_1_path_three = env('APP_URL') . "sample/product/27-PANEL/T-1/R010003.jpg";
                $panel27T_1_three = date('YmdHis') . ".jpg";
                Image::make($panel27T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_1_three));

                $panel27T_2_path_one = env('APP_URL') . "sample/product/27-PANEL/T-2/10002.jpg";
                $panel27T_2_one = date('YmdHis') . ".jpg";
                Image::make($panel27T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_2_one));

                $panel27T_2_path_two = env('APP_URL') . "sample/product/27-PANEL/T-2/R0_SSEMH0952-Mr0001.jpg";
                $panel27T_2_two = date('YmdHis') . ".jpg";
                Image::make($panel27T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_2_two));

                $panel27T_2_path_three = env('APP_URL') . "sample/product/27-PANEL/T-2/R0_SSEMH0952-Mr0003.jpg";
                $panel27T_2_three = date('YmdHis') . ".jpg";
                Image::make($panel27T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel27T_2_three));

                // 29-PANEL
                $panel29T_1_path_one = env('APP_URL') . "sample/product/29-PANEL/T-1/R010001.jpg";
                $panel29T_1_one = date('YmdHis') . ".jpg";
                Image::make($panel29T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_1_one));

                $panel29T_1_path_two = env('APP_URL') . "sample/product/29-PANEL/T-1/R010002.jpg";
                $panel29T_1_two = date('YmdHis') . ".jpg";
                Image::make($panel29T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_1_two));

                $panel29T_1_path_three = env('APP_URL') . "sample/product/29-PANEL/T-1/R010003.jpg";
                $panel29T_1_three = date('YmdHis') . ".jpg";
                Image::make($panel29T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_1_three));

                $panel29T_2_path_one = env('APP_URL') . "sample/product/29-PANEL/T-2/3P100002.jpg";
                $panel29T_2_one = date('YmdHis') . ".jpg";
                Image::make($panel29T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_2_one));

                $panel29T_2_path_two = env('APP_URL') . "sample/product/29-PANEL/T-2/R010001.jpg";
                $panel29T_2_two = date('YmdHis') . ".jpg";
                Image::make($panel29T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_2_two));

                $panel29T_2_path_three = env('APP_URL') . "sample/product/29-PANEL/T-2/R010003.jpg";
                $panel29T_2_three = date('YmdHis') . ".jpg";
                Image::make($panel29T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel29T_2_three));


                // 30-PANEL
                $panel30T_1_path_one = env('APP_URL') . "sample/product/30-PANEL/T-1/R010001.jpg";
                $panel30T_1_one = date('YmdHis') . ".jpg";
                Image::make($panel30T_1_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_1_one));

                $panel30T_1_path_two = env('APP_URL') . "sample/product/30-PANEL/T-1/R010002.jpg";
                $panel30T_1_two = date('YmdHis') . ".jpg";
                Image::make($panel30T_1_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_1_two));

                $panel30T_1_path_three = env('APP_URL') . "sample/product/30-PANEL/T-1/R010003.jpg";
                $panel30T_1_three = date('YmdHis') . ".jpg";
                Image::make($panel30T_1_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_1_three));

                $panel30T_2_path_one = env('APP_URL') . "sample/product/30-PANEL/T-2/3P100002.jpg";
                $panel30T_2_one = date('YmdHis') . ".jpg";
                Image::make($panel30T_2_path_one)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_2_one));

                $panel30T_2_path_two = env('APP_URL') . "sample/product/30-PANEL/T-2/3P1010001.jpg";
                $panel30T_2_two = date('YmdHis') . ".jpg";
                Image::make($panel30T_2_path_two)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_2_two));

                $panel30T_2_path_three = env('APP_URL') . "sample/product/30-PANEL/T-2/3P1010003.jpg";
                $panel30T_2_three = date('YmdHis') . ".jpg";
                Image::make($panel30T_2_path_three)->save(storage_path("app/public/uploads/thumbnail/" . $panel30T_2_three));


                $productArr = [
                    ['name' => '8 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel8T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel8T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel8T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '8 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel8T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel8T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel8T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '8 Panel_T-3', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel8T_3_one, 'image_two' => 'public/uploads/thumbnail/' . $panel8T_3_two, 'image_three' => 'public/uploads/thumbnail/' . $panel8T_3_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '9 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel9T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel9T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel9T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '9 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel9T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel9T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel9T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '9 Panel_T-3', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel9T_3_one, 'image_two' => 'public/uploads/thumbnail/' . $panel9T_3_two, 'image_three' => 'public/uploads/thumbnail/' . $panel9T_3_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '10 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel10T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel10T_1_one, 'image_three' => 'public/uploads/thumbnail/' . $panel10T_1_one, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '10 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel10T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel10T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel10T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '10 Panel_T-3', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel10T_3_one, 'image_two' => 'public/uploads/thumbnail/' . $panel10T_3_two, 'image_three' => 'public/uploads/thumbnail/' . $panel10T_3_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '11 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel11T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel11T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel11T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '11 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel11T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel11T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel11T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '12 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel12T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel12T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel12T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '12 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel12T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel12T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel12T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '12 Panel_T-3', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel12T_3_one, 'image_two' => 'public/uploads/thumbnail/' . $panel12T_3_two, 'image_three' => 'public/uploads/thumbnail/' . $panel12T_3_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '13 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel13T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel13T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel13T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '13 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel13T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel13T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel13T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '14 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel14T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel14T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel14T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '14 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel14T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel14T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel14T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '15 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel15T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel15T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel15T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '15 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel15T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel15T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel15T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '16 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel16T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel16T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel16T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '16 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel16T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel16T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel16T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '17 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel17T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel17T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel17T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '18 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel18T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel18T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel18T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '18 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel18T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel18T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel18T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '21 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel21T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel21T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel21T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '21 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel21T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel21T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel21T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '24 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel24T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel24T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel24T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '24 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel24T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel24T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel24T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '27 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel27T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel27T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel27T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '27 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel27T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel27T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel27T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '29 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel29T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel29T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel29T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '29 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel29T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel29T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel29T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '30 Panel_T-1', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel30T_1_one, 'image_two' => 'public/uploads/thumbnail/' . $panel30T_1_two, 'image_three' => 'public/uploads/thumbnail/' . $panel30T_1_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                    ['name' => '30 Panel_T-2', 'description' => '', 'image_one' => 'public/uploads/thumbnail/' . $panel30T_2_one, 'image_two' => 'public/uploads/thumbnail/' . $panel30T_2_two, 'image_three' => 'public/uploads/thumbnail/' . $panel30T_2_three, 'status' => 0, 'user_id' => $record->id, 'company_id' => $record->id],
                ];
                DB::table('products')->insert($productArr);
            }
        }
        /*$records = DB::table('users')
            ->join('proposal_templates', 'users.id', '=', 'proposal_templates.company_id')
            ->where('users.status', 'Approved')
            ->select('users.id')
            ->get();
        foreach ($records as $record){

            $termConditionArr = ["name" => "Basic Terms", "description" => '<p><span style="color:#3498db"><span style="font-size:16px"><strong>Terms &amp; Condition</strong></span></span></p>
<p>Material dispatch and Installation shall be started upon DISCOM approval only.</p>
<p>For better performance, solar panels should be cleaned by customer two times in a week.</p>
<p>Concealed wiring shall be done by company, if possible only. Otherwise, customer should do concealed wiring with their wiremen where material shall be provided by Company.</p>
<p>After successful installation, Customer shall take care of solar plant by doing timely cleaning. If we found less generation at the time of attending complaint due to non-cleaning, we may charge you additional service&nbsp; charge.</p>
<p>There is manufacturing warranty for all electronics equipment. Company will help to claim this warranty if require.&nbsp;</p>
<p>The company will provide up to 30 meter wire 25 Year warranty of PV Module, 10 Year warranty of Inverter and 5 Year O&amp;M of System by Company</p>
<p><span style="color:#3498db"><span style="font-size:16px"><strong>Scope of Work For Customer:</strong></span></span></p>
<p>Providing access/approach to rooftop&nbsp;</p>
<p>If any system modification is required from DISCOM ( i.e. ELCB, changeover etc.)&nbsp;</p>
<p>Provide necessary documents for project approvals from State/Central Government&nbsp;</p>
<p>Site clearance, water, and electricity for smooth installation and commissioning of the project&nbsp;</p>
<p>Required civil work and approvals to complete the project within the timeline proposed</p>
<p>Safe storage of materials (PV modules, Inverter, etc.) upon delivery&nbsp;</p>
<p>Customer shall provide Safe Place for Material unloading and storage during the work execution</p>
<p><span style="color:#3498db"><span style="font-size:16px"><strong>Warranty Exclusion:</strong></span></span></p>
<p>Damage due to improper handling&nbsp;</p>
<p>In absence of full payment&nbsp;</p>
<p>Damage to due to force majeure Defects due to third party inference (direct or indirect) or act to our system&nbsp;</p>
<p>This offer in itself or any subsequent Communications/documents will be subject to standard Force Majeure conditions.&nbsp;</p>
<p>Jurisdiction: Subject to Surat jurisdiction.</p>', 'user_id' => $record->id, 'company_id' => $record->id
            ];
            $term_condition_id = DB::table('term_conditions')->insertGetId($termConditionArr);
            ProposalTemplates::where('company_id', $record->id)->update(array('term_condition_id' => $term_condition_id));
        }

        echo "ok";*/
    }

    public function getEstimateListByCustomer(Request $request)
    {
        $user = Auth::user();
        $company_id = ($user->company_id) ? $user->company_id : $user->id;
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

            // Fetch records
            $id = $request->get('id');
            $customerId = Crypt::decrypt($id);
            $name = '';
            $status = '';
            $estimate_no = '';
            $fil_team_member = 0;
            /*$name = $request->get('name');
            $status = $request->get('status');
            $estimate_no = $request->get('estimate_no');*/
//            $fil_team_member = $request->get('fil_team_member');
            $company_id = ($user->company_id) ? $user->company_id : $user->id;
            // Total records
            $subtotalRecords = DB::table('estimates')->leftjoin('customers_views', 'estimates.customer_id', '=', 'customers_views.id')
                ->where(function ($query) use ($user_perm) {
                    if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                        $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
                        $query->orwhere('customers_views.user_id', '=', $this->logged_user->id);
                    }
                })
                ->where('estimates.company_id', $company_id)
                ->where('estimates.customer_id', $customerId)
                ->selectRaw('ROW_NUMBER() OVER (PARTITION BY estimates.estimate_no ORDER BY estimates.estimate_version DESC) AS row_num');

            $totalRecords = DB::table(DB::raw("({$subtotalRecords->toSql()}) as subquerys"))
                ->mergeBindings($subtotalRecords)
                ->where('row_num', 1)
                ->count();
//                ->count();
            $subtotalRecordswithFilter = DB::table('estimates')->leftjoin('customers_views', 'estimates.customer_id', '=', 'customers_views.id')
                ->where(function ($query) use ($user_perm) {
                    if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                        $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
                        $query->orwhere('customers_views.user_id', '=', $this->logged_user->id);
                    }
                })
                ->where('estimates.company_id', $company_id)
                ->where('estimates.customer_id', $customerId)
                ->selectRaw('ROW_NUMBER() OVER (PARTITION BY estimates.estimate_no ORDER BY estimates.estimate_version DESC) AS row_num');

            $totalRecordswithFilter = DB::table(DB::raw("({$subtotalRecordswithFilter->toSql()}) as subquerys"))
                ->mergeBindings($subtotalRecordswithFilter)
                ->where('row_num', 1)
                ->count();

            $rowperpage = ($rowperpage == -1) ? $totalRecords : $rowperpage;

            $subrecords = DB::table('estimates')
                ->leftJoin('users', 'estimates.sales_person_id', '=', 'users.id')
                ->leftjoin('customers_views', 'estimates.customer_id', '=', 'customers_views.id')
                ->where(function ($query) use ($user_perm) {
                    if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                        $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
                        $query->orwhere('customers_views.user_id', '=', $this->logged_user->id);
                    }
                })
                ->where('estimates.company_id', $company_id)
                ->where('estimates.customer_id', $customerId)
                ->select('estimates.*', DB::raw("RIGHT(estimates.customer_address, 10) as mobile_no"), "users.name as sales_person_name", "customers_views.currency_name_country_id")
                ->selectRaw('ROW_NUMBER() OVER (PARTITION BY estimates.estimate_no ORDER BY estimates.estimate_version DESC) AS row_num');
            $records = DB::table(DB::raw("({$subrecords->toSql()}) as subquery"))
                ->mergeBindings($subrecords)
                ->where('row_num', 1)
                ->skip($start)
                ->take($rowperpage)
                ->orderBy($columnName, $columnSortOrder)
                ->get();

            $data = array();
            $i = 0;
            foreach ($records as $record) {
                if ($record->est_currency_id)
                    $country_data = Country::where("id", $record->est_currency_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->orderBy('id', 'DESC')->first();
                /* $country_data = Country::where("id", $record->currency_name_country_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->orderBy('id', 'DESC')->get()->first();*/
                $id = Crypt::encrypt($record->id);
                $estimate_date = Carbon::createFromFormat('Y-m-d', $record->estimate_date)->format('d/m/Y');
                $estimate_no = $record->estimate_no;
                $reference = $record->reference;
                $name = $record->customer_name;
                $net_amount = $record->net_amount;
                $expiry_date = $record->expiry_date;
                $subtotal = $record->subtotal;
                $status = $record->status;
                $mobile_no = $record->mobile_no;
                $sales_person_name = $record->sales_person_name;
                $customer_id = $record->customer_id;
                $i++;

                $pdfname = ($record->estimate_version == 0) ? $estimate_no : $estimate_no . '-V' . $record->estimate_version;
                $data[] = array(
                    "id" => $i,
                    "customer_id" => $customer_id,
                    "estimate_date" => $estimate_date,
                    "estimate_no" => $pdfname,
//                    "download_action" => Storage::url('public/document/' . $company_id . '/' . $pdfname . '.pdf'),
                    "download_action" => Storage::disk('s3')->url('public/' . $company_id . '/documents/' . $pdfname . '.pdf'),
                    "reference" => $reference,
                    "customer_name" => $name,
                    "expiry_date" => $expiry_date,
                    "subtotal" => (isset($country_data->currency_symbol)) ? $country_data->currency_symbol . ' ' . $subtotal : $subtotal,
                    "net_amount" => (isset($country_data->currency_symbol)) ? $country_data->currency_symbol . ' ' . $net_amount : $net_amount,
                    "status" => $status,
                    "action" => $id,
                    "mobile_no" => $mobile_no,
                    "sales_person_name" => $sales_person_name,
                    'customer_id_decode' => Crypt::encrypt($record->customer_id)
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
        $user = Auth::user();
        $id = isset($user->company_id) ? $user->company_id : $user->id;
        $month = Carbon::now()->format('m');
        $estimateCount = Estimate::where('company_id', $id)->whereMonth('created_at', $month)->count();
        $plan = PlanHistory::where([['user_id', $id], ['status', 1]])->first();
        $teamUsers = User::select(["name", "id", "email", "mobile_no"])
            ->where('invite_status', 1)
            ->where(function ($query) {
                $query->orwhere('company_id', $this->company_id);
                $query->orwhere('id', $this->company_id);
            })
            ->get();
        $segment = $this->segment;
        return view('app.estimate.index', compact('segment'))->with(['estimateCount' => $estimateCount, 'plan' => $plan, 'teamUsers' => $teamUsers]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $company_id = ($user->company_id) ? $user->company_id : $user->id;
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

            // Fetch records
            $name = $request->get('name');
            $status = $request->get('status');
            $estimate_no = $request->get('estimate_no');
            $fil_team_member = $request->get('fil_team_member');
            $company_id = ($user->company_id) ? $user->company_id : $user->id;
            // Total records
            $subtotalRecords = DB::table('estimates')->leftjoin('customers_views', 'estimates.customer_id', '=', 'customers_views.id')
                ->where(function ($query) use ($user_perm) {
                    if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                        $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
                        $query->orwhere('customers_views.user_id', '=', $this->logged_user->id);
                    }
                })
                ->where('estimates.company_id', $company_id)
                /*->where(function ($query) use ($input) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(estimates.estimate_date, '%Y-%m-%d')"), [$input['fil_estimate_start'], $input['fil_estimate_end']]);
                })*/
                ->where(function ($query) use ($name, $status, $estimate_no, $fil_team_member) {
                    /*if ($name != '') {
                        $query->Where(function ($query) use ($name) {
                            $query->where('customer_name', '=', $name);
                        });
                    }*/
                    if ($status != '') {
                        $query->where(function ($query) use ($status) {
                            $query->where('estimates.status', '=', $status);
                        });
                    }

                    if ($fil_team_member > 0) {
                        $query->where('estimates.sales_person_id', '=', $fil_team_member);
                    }
                    /* if ($estimate_no != '') {
                         $query->where(function ($query) use ($estimate_no) {
                             $query->where('estimate_no', '=', $estimate_no);
                         });
                     }*/
                })
                /*->where(function ($query) use ($user) {
                    $query->whereRaw('user_id IN  (' . Session::get("get_data_by_id") . ')');
                    $query->orWhere('user_id', $user->id);
                })*/
                ->where(function ($query) use ($search_arr) {
                    if ($search_arr != '') {
                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('estimates.customer_name', 'like', '%' . $search_arr . '%');
                        });
                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('estimates.estimate_no', 'like', '%' . $search_arr . '%');
                        });
                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('estimates.reference', 'like', '%' . $search_arr . '%');
                        });
                    }
                })
                ->selectRaw('ROW_NUMBER() OVER (PARTITION BY estimates.estimate_no ORDER BY estimates.estimate_version DESC) AS row_num');

            $totalRecords = DB::table(DB::raw("({$subtotalRecords->toSql()}) as subquerys"))
                ->mergeBindings($subtotalRecords)
                ->where('row_num', 1)
                ->count();
                // ->count();
            $subtotalRecordswithFilter = DB::table('estimates')->leftjoin('customers_views', 'estimates.customer_id', '=', 'customers_views.id')
                ->where(function ($query) use ($user_perm) {
                    if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                        $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
                        $query->orwhere('customers_views.user_id', '=', $this->logged_user->id);
                    }
                })
                ->where('estimates.company_id', $company_id)
                /*->where(function ($query) use ($input) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(estimates.estimate_date, '%Y-%m-%d')"), [$input['fil_estimate_start'], $input['fil_estimate_end']]);
                })*/
                //                ->where('estimates.customer_name', 'like', '%' . $search_arr . '%')
                ->where(function ($query) use ($name, $status, $estimate_no, $fil_team_member) {
                    /* if ($name != '') {
                         $query->Where(function ($query) use ($name) {
                             $query->where('customer_name', '=', $name);
                         });
                     }*/
                    if ($status != '') {
                        $query->where(function ($query) use ($status) {
                            $query->where('estimates.status', '=', $status);
                        });
                    }

                    if ($fil_team_member > 0) {
                        $query->where('estimates.sales_person_id', '=', $fil_team_member);
                    }
                    /*if ($estimate_no != '') {
                        $query->where(function ($query) use ($estimate_no) {
                            $query->where('estimate_no', '=', $estimate_no);
                        });
                    }*/
                })
                /*->where(function ($query) use ($user) {
                    $query->whereRaw('user_id IN  (' . Session::get("get_data_by_id") . ')');
                    $query->orWhere('user_id', $user->id);
                })*/
                ->where(function ($query) use ($search_arr) {
                    if ($search_arr != '') {
                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('estimates.customer_name', 'like', '%' . $search_arr . '%');
                        });
                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('estimates.estimate_no', 'like', '%' . $search_arr . '%');
                        });
                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('estimates.reference', 'like', '%' . $search_arr . '%');
                        });
                    }
                })
                ->selectRaw('ROW_NUMBER() OVER (PARTITION BY estimates.estimate_no ORDER BY estimates.estimate_version DESC) AS row_num');

            $totalRecordswithFilter = DB::table(DB::raw("({$subtotalRecordswithFilter->toSql()}) as subquerys"))
                ->mergeBindings($subtotalRecordswithFilter)
                ->where('row_num', 1)
                ->count();

            $rowperpage = ($rowperpage == -1) ? $totalRecords : $rowperpage;
            //DB::enableQueryLog();
            $subrecords = DB::table('estimates')
                /*->where(function ($query) use ($input) {
                    $query->whereBetween(DB::raw("DATE_FORMAT(estimates.estimate_date, '%Y-%m-%d')"), [$input['fil_estimate_start'], $input['fil_estimate_end']]);
                })*/
                ->leftJoin('users', 'estimates.sales_person_id', '=', 'users.id')
                ->leftjoin('customers_views', 'estimates.customer_id', '=', 'customers_views.id')
                ->where(function ($query) use ($user_perm) {
                    if (in_array('access-self-leads-only-and-assign-my-leads-to-anyone-in-team', $user_perm) || in_array('access-self-leads-only-and-cant-assign-my-leads-to-anyone-in-team', $user_perm)) {
                        $query->where('customers_views.assigned_to_user', '=', $this->logged_user->id);
                        $query->orwhere('customers_views.user_id', '=', $this->logged_user->id);
                    }
                })
                ->where('estimates.company_id', $company_id)
                /* ->where(function ($query) use ($user) {
                    $query->whereRaw('estimates.user_id IN  (' . Session::get("get_data_by_id") . ')');
                    $query->orWhere('estimates.user_id', $user->id);
                })*/
                ->where(function ($query) use ($name, $status, $estimate_no, $fil_team_member) {
                    /*if ($name != '') {
                        $query->Where(function ($query) use ($name) {
                            $query->where('customer_name', '=', $name);
                        });
                    }*/
                    if ($status != '') {
                        $query->where(function ($query) use ($status) {
                            $query->where('estimates.status', '=', $status);
                        });
                    }

                    if ($fil_team_member > 0) {
                        $query->where('estimates.sales_person_id', '=', $fil_team_member);
                    }
                    /* if ($estimate_no != '') {
                         $query->where(function ($query) use ($estimate_no) {
                             $query->where('estimate_no', '=', $estimate_no);
                         });
                     }*/
                })
                ->where(function ($query) use ($search_arr) {
                    if ($search_arr != '') {
                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('estimates.customer_name', 'like', '%' . $search_arr . '%');
                        });
                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('estimates.estimate_no', 'like', '%' . $search_arr . '%');
                        });
                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('users.name', 'like', '%' . $search_arr . '%');
                        });
                        $query->orWhere(function ($query) use ($search_arr) {
                            $query->where('reference', 'like', '%' . $search_arr . '%');
                        });
                    }
                })

                //                ->orWhere(function ($query) use ($search_arr) {
                //                    if ($search_arr) {
                //                        $query->orWhere(function ($query) use ($search_arr) {
                //                            $query->where('description', 'like', '%' . $search_arr . '%');
                //                        });
                //                    }
                //                })

                ->select('estimates.*', DB::raw("RIGHT(estimates.customer_address, 10) as mobile_no"), "users.name as sales_person_name", "customers_views.currency_name_country_id")
                ->selectRaw('ROW_NUMBER() OVER (PARTITION BY estimates.estimate_no ORDER BY estimates.estimate_version DESC) AS row_num');  // new add 2028 to 2051

            //dd(DB::getQueryLog());

            $records = DB::table(DB::raw("({$subrecords->toSql()}) as subquery"))
                ->mergeBindings($subrecords)
                ->where('row_num', 1)
                ->skip($start)
                ->take($rowperpage)
                ->orderBy($columnName, $columnSortOrder)
                ->get();

            $data = array();
            $i = 0;
            foreach ($records as $record) {
                if ($record->est_currency_id)
                    $country_data = Country::where("id", $record->est_currency_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->orderBy('id', 'DESC')->first();
                /* $country_data = Country::where("id", $record->currency_name_country_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->orderBy('id', 'DESC')->get()->first();*/
                $id = Crypt::encrypt($record->id);
                $estimate_date = Carbon::createFromFormat('Y-m-d', $record->estimate_date)->format('d/m/Y');
                $estimate_no = $record->estimate_no;
                $reference = $record->reference;
                $name = $record->customer_name;
                $net_amount = $record->net_amount;
                $expiry_date = $record->expiry_date;
                $subtotal = $record->subtotal;
                $status = $record->status;
                $mobile_no = $record->mobile_no;
                $sales_person_name = $record->sales_person_name;
                $customer_id = $record->customer_id;
                $i++;

                $pdfname = ($record->estimate_version == 0) ? $estimate_no : $estimate_no . '-V' . $record->estimate_version;
                $data[] = array(
                    "id" => $i,
                    "customer_id" => $customer_id,
                    "estimate_date" => $estimate_date,
                    "estimate_no" => $pdfname,
                    // "download_action" => Storage::url('public/document/' . $company_id . '/' . $pdfname . '.pdf'),
                    "download_action" => Storage::disk('s3')->url('public/' . $company_id . '/documents/' . $pdfname . '.pdf'),
                    "reference" => $reference,
                    "customer_name" => $name,
                    "expiry_date" => $expiry_date,
                    "subtotal" => (isset($country_data->currency_symbol)) ? $country_data->currency_symbol . ' ' . $subtotal : $subtotal,
                    "net_amount" => (isset($country_data->currency_symbol)) ? $country_data->currency_symbol . ' ' . $net_amount : $net_amount,
                    "status" => $status,
                    "action" => $id,
                    "mobile_no" => $mobile_no,
                    "sales_person_name" => $sales_person_name,
                    'customer_id_decode' => Crypt::encrypt($record->customer_id)
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
        $user = Auth::user();
        $id = isset($user->company_id) ? $user->company_id : $user->id;
        $month = Carbon::now()->format('m');
        $estimateCount = Estimate::where('company_id', $id)->whereMonth('created_at', $month)->count();
        $plan = PlanHistory::where([['user_id', $id], ['status', 1]])->first();
        $teamUsers = User::select(["name", "id", "email", "mobile_no"])
            ->where('invite_status', 1)
            ->where(function ($query) {
                $query->orwhere('company_id', $this->company_id);
                $query->orwhere('id', $this->company_id);
            })
            ->get();
            $segment = $this->segment;
        return view('app.estimate.index', compact('segment'))->with(['estimateCount' => $estimateCount, 'plan' => $plan, 'teamUsers' => $teamUsers]);
    }

    public function create(Request $request)
    {
        $customers = [];
        $country_data = [];
        if ($request->has('cid')) {
            $cid = Crypt::decrypt($request->input('cid'));
            $customers = ViewCustomerData::select('id', 'name', 'phone_no', 'state_id', 'address', 'pincode', 'country_name', 'state_name', 'city_name', 'company_name', "currency_name", "currency_code")
                ->where('company_id', $this->company_id)
                ->where(function ($query) {
                    if ($this->logged_user->customer_show_flg == 0 && $this->logged_user->company_id != '') {
                        $query->where('user_id', '=', $this->logged_user->id);
                    }
                })
                ->where('status', 0)
                ->where('id', $cid)
                ->get()->toArray();
            if($customer->currency_name_country_id)
            $country_data = Country::
            leftJoin('customers_views as cv', 'cv.currency_name_country_id', '=', 'countries.id')
                ->where('cv.id', $customers[0]['id'])
                ->select('countries.name', 'countries.currency_name', 'countries.currency_code', 'countries.currency_symbol')->orderBy('countries.id', 'DESC')->get()->first();
        }
        //        $user_list = [];
        $user = Auth::user();
        $company_id = ($user->company_id) ? $user->company_id : $user->id;
        // $countries = Country::select(["name", "id", "phonecode","sortname"])->where('status', '=', 0)->get();
        $countries = Country::select(["name", "id", "phonecode", "sortname", "currency_name", "currency_code", "currency_symbol"])->where('status', '=', 0)->orderBy('name', 'ASC')->get();
        $units = Unit::select(["name", "id"])->where('status', '=', 0)->where('company_id', $company_id)->get();
        $estimate_auto_number = EstimateAutoNumber::select(["estimate_prefix", "estimate_next_no"])->where('company_id', $company_id)->get()->first();
        $proposal_template = ProposalTemplates::where('company_id', $company_id)->first();
        $termConditionDatas = TermCondition::where([["status", "=", 0], ["company_id", "=", $company_id]])->select('*')->get();
        /*if ($user->company_id == null) {
            $user_list = User::query()->select(["name", "id", "email"])
                ->where('status', 'Approved')
                //                ->where('company_id', $company_id)
                ->where(function ($query) use ($company_id, $user) {
                    $query->where(function ($query) use ($company_id, $user) {
                        $query->where('user_id', $company_id);
                        $query->orwhere('role_id', 6);
                        $query->orwhere('id', $user->id);
                    });
                })
                ->where(function ($query) use ($company_id, $user) {
                    $query->where(function ($query) use ($company_id, $user) {
                        $query->where('company_id', $company_id);
                        $query->orwhere('id', $user->id);
                    });
                })
                ->get();
        }*/

        $user_list = User::query()->select(["name", "id", "email", "mobile_no"])
            ->where('status', 'Approved')
            //            ->where('company_id', $this->company_id)
            ->where(function ($query) {
                $query->orwhere('company_id', $this->company_id);
                $query->orwhere('id', $this->company_id);
            })
            ->get();
        $company_data = ViewUserData::where("id", $proposal_template->company_id)->orderBy('id', 'ASC')->get()->first();
        $testimonial_data = Testimonial::query()->where([['company_id', '=', $company_id], ['is_default', '=', 1]])->select(['id', 'name', 'client_name_one', 'description_one', 'rating_one', 'image_one', 'client_name_two', 'description_two', 'rating_two', 'image_two', 'client_name_three', 'description_three', 'rating_three', 'image_three'])->first();
        $taxes = Tax::query()->select(["id", "name"])
            ->where([['company_id', "=", $company_id], ["status", "=", 0]])
            ->orderBy("name")
            ->get();
        $customerCategories = CustomerCategory::select(["name", "id"])->where('status', '=', 0)->where('company_id', $company_id)->get();
        $customerLeads = CustomerLead::select(["name", "id"])->where('status', '=', 0)->where('company_id', $company_id)->get();
        $main_company = User::select(["follow_up_note_req_flg"])
            ->where('id', $company_id)->first();

        $country_data_est = Country::Join('users', 'users.country_id', '=', 'countries.id')
            ->where('users.id', $this->company_id)
            ->select('countries.name', 'countries.currency_name', 'countries.currency_code', 'countries.currency_symbol', 'countries.sortname')->orderBy('countries.id', 'DESC')->first();

        $leadStages = LeadStage::select("*")
            ->where('status', '=', 0)
            ->where(function ($query) {
                $query->where('company_id', '=', $this->company_id);
//                $query->orwhere('status', '=', 0);
            })
            ->orderBy('priority', 'asc')
            ->get();
            $segment = $this->segment;
        return view('app.estimate.new', compact('countries', 'units', 'estimate_auto_number', 'proposal_template', 'user_list', 'company_data', 'testimonial_data', 'taxes', 'termConditionDatas', 'customerCategories', 'customerLeads', 'customers', 'main_company', 'country_data', 'country_data_est', 'leadStages', 'leadStages', 'segment'));
    }

    public function show($id)
    {
        $id = ($id) ? Crypt::decrypt($id) : $id;
        $user = Auth::user();
        $company_id = ($user->company_id) ? $user->company_id : $user->id;
        $estimate = Estimate::where("id", $id)->get()->first();

        if (!$estimate) {
            return redirect()->back()->withInput();
        }
        $estimate_items = EstimateItems::where("estimate_id", $id)->orderBy('id', 'ASC')->get(["*"]);
        $company_data = ViewUserData::where("id", $estimate->company_id)->orderBy('id', 'ASC')->get()->first();
        $proposal_template = ProposalTemplates::where('company_id', $company_id)->first();
        $termConditionDatas = TermCondition::where([["status", "=", 0], ["company_id", "=", $company_id]])->select('*')->get();
        return view('estimate.show', compact('estimate', 'estimate_items', 'company_data', 'proposal_template', 'termConditionDatas'));
    }

    // Generate PDF

    public function createPDF()
    {
        $id = 31;
        $estimate = Estimate::where("id", $id)->get()->first();

        if (!$estimate) {
            return redirect()->back()->withInput();
        }
        $estimate_items = EstimateItems::where("estimate_id", $id)->orderBy('id', 'ASC')->get(["*"]);
        $company_data = ViewUserData::where("id", $estimate->company_id)->orderBy('id', 'ASC')->get()->first();

        //        return view('estimate.previewpdf', compact('estimate', 'estimate_items', 'company_data'));
        //        $pdf = PDF::loadView('estimate.previewpdf', compact('estimate', 'estimate_items', 'company_data'));

        //        view()->share('estimate.previewpdf', compact('estimate', 'estimate_items', 'company_data'));
        //        $pdf = PDF::loadView('estimate.previewpdf', compact('estimate', 'estimate_items', 'company_data'));

        $pdf = PDF::loadView('estimate.previewpdf', compact('estimate', 'estimate_items', 'company_data'));


        return $pdf->download('onlinewebtutorblog.pdf');

        //        return $pdf->download('pdf_file.pdf');

        //        $html = '<h1></h1>';
        //
        //        PDF::SetTitle('Hello World');
        //        PDF::AddPage();
        //        PDF::writeHTML($html, true, false, true, false, '');
        //
        //        PDF::Output('hello_world.pdf');
    }


    public function edit($id)
    {
        $user_list = [];
        $id = ($id) ? Crypt::decrypt($id) : $id;
        $user = Auth::user();
        $company_id = ($user->company_id) ? $user->company_id : $user->id;

        $planDetails = User::query()->select('plan_id')->where("id", $company_id)->first();

        $estimate = Estimate::where([["id", $id], ["company_id", "=", $company_id]])->get()->first();
        if (!$estimate) {
            return redirect()->back()->withInput();
        }
        $status = 0;
        $estimates = Estimate::where("company_id", $company_id)->orderby('created_at', 'desc')->take(10)->get();
        foreach ($estimates as $key => $value) {
            if ($value->id == $id) {
                $status = 1;
                break;
            } else {
                $status = 0;
            }
        }


        /*if ($status == 0 && $user->plan_id==2) {
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
        if ($status == 0 && $planDetails->plan_id == 1) {
//        if ($status == 0) {
            return redirect()->back()->with('error', 'You Are Not Editable to this record...');
        }

        $estimate_items = EstimateItems::where([["estimate_id", $id], ["company_id", "=", $company_id]])->orderBy('id', 'ASC')->get(["*"]);
        $countries = Country::select(["name", "id", "phonecode", "sortname", "currency_name", "currency_code", "currency_symbol"])->where('status', '=', 0)->get();
        $units = Unit::select(["name", "id"])->where('status', '=', 0)->where('company_id', $company_id)->get();
        $products = [];
        if ($estimate->product_id) {
            //$products = Product::select(["name", "id", "image_one", "image_two", "image_three"])->where('status', '=', 0)->whereIn('id', explode(',', $estimate->product_id))->where('company_id', $company_id)->orderByRaw("FIELD(id,$estimate->product_id)")->get();

            $products = EstimatePhoto::select(["products.name", "products.id", "estimate_photos.id as estimate_product_id", "estimate_photos.image_one", "estimate_photos.image_two", "estimate_photos.image_three"])
                ->join('products', 'estimate_photos.product_id', '=', 'products.id')->where([["estimate_photos.estimate_id", $id], ["estimate_photos.comapny_id", "=", $company_id]])->orderBy('estimate_photos.id', 'asc')->get();

        }
        $testimonials = Testimonial::select(["name", "id", "client_name_one", "image_one", "rating_one", "description_one", "client_name_two", "image_two", "rating_two", "description_two", "client_name_three", "image_three", "rating_three", "description_three"])->where('status', '=', 0)->where('id', $estimate->testimonial_id)->where('company_id', $company_id)->get()->first();
        if ($user->company_id == null) {
            $user_list = User::query()->select(["name", "id", "email"])
                ->where('status', 'Approved')
                //                ->where('company_id', $company_id)
                ->where(function ($query) use ($company_id, $user) {
                    $query->where(function ($query) use ($company_id, $user) {
                        $query->where('user_id', $company_id);
                        $query->orwhere('role_id', 6);
                        $query->orwhere('id', $user->id);
                    });
                })
                ->where(function ($query) use ($company_id, $user) {
                    $query->where(function ($query) use ($company_id, $user) {
                        $query->where('company_id', $company_id);
                        $query->orwhere('id', $user->id);
                    });
                })
                ->get();
        }
        $proposal_template = ProposalTemplates::where('company_id', $company_id)->first();
        $company_data = ViewUserData::where("id", $estimate->company_id)->orderBy('id', 'ASC')->get()->first();
        $taxes = Tax::query()->select(["id", "name"])
            ->where([['company_id', "=", $company_id], ["status", "=", 0]])
            ->orderBy("name")
            ->get();
        $customerCategories = CustomerCategory::select(["name", "id"])->where('status', '=', 0)->where('company_id', $company_id)->get();
        $customerLeads = CustomerLead::select(["name", "id"])->where('status', '=', 0)->where('company_id', $company_id)->get();
        $termConditionDatas = TermCondition::where([["status", "=", 0], ["company_id", "=", $company_id]])->select('*')->get();
//        $customer_data = Customer::select(["name", "id"])->where('id', $estimate->customer_id)->get()->first();
        $customers = ViewCustomerData::select('id', 'name', 'phone_no', 'state_id', 'address', 'pincode', 'country_name', 'state_name', 'city_name', 'company_name', "currency_name", "currency_code")
            ->where('company_id', $this->company_id)
            ->where('id', $estimate->customer_id)
            ->get()->toArray();

        $country_data = [];
        $country_data = Country::where('id', $estimate->est_currency_id)->select('*')->orderBy('id', 'DESC')->get()->first();
//        if($customer->currency_name_country_id)
        /*$country_data = Country::
        leftJoin('customers_views as cv', 'cv.currency_name_country_id', '=', 'countries.id')
            ->where('cv.id', $estimate->customer_id)
            ->select('countries.name', 'countries.currency_name', 'countries.currency_code', 'countries.currency_symbol', 'countries.sortname')->orderBy('countries.id', 'DESC')->get()->first();*/
        return view('estimate.edit', compact('estimate', 'estimate_items', 'countries', 'units', 'user_list', 'products', 'proposal_template', 'company_data', 'testimonials', 'taxes', 'termConditionDatas', 'customerCategories', 'customerLeads', 'customers', 'country_data'));
    }

    public function preview($id)
    {
        $user_list = [];
        $id = ($id) ? Crypt::decrypt($id) : $id;
        $estimate = Estimate::where("id", $id)->get()->first();
        if (!$estimate) {
            return redirect()->back()->withInput();
        }
        $user = Auth::user();
        $company_id = $estimate->company_id;
        $estimate_items = EstimateItems::where("estimate_id", $id)->orderBy('id', 'ASC')->get(["*"]);
        $countries = Country::select(["name", "id"])->where('status', '=', 0)->get();
        $units = Unit::select(["name", "id"])->where('status', '=', 0)->where('company_id', $company_id)->get();
        $products = Product::select(["name", "id", "image_one", "image_two", "image_three"])->where('status', '=', 0)->whereIn('id', explode(',', $estimate->product_id))->where('company_id', $company_id)->get();
        $testimonials = Testimonial::select(["name", "id", "client_name_one", "image_one", "rating_one", "description_one", "client_name_two", "image_two", "rating_two", "description_two", "client_name_three", "image_three", "rating_three", "description_three"])->where('status', '=', 0)->where('id', $estimate->testimonial_id)->where('company_id', $company_id)->get()->first();
        //        if ($user->company_id == null) {
        $user_list = User::query()->select(["name", "id", "email"])
            ->where('status', 'Approved')
            ->where(function ($query) use ($company_id) {
                $query->where(function ($query) use ($company_id) {
                    $query->where('company_id', $company_id);
                    $query->orwhere('role_id', 6);
                });
            })
            ->get();
        //        }
        $proposal_template = ProposalTemplates::where('company_id', $company_id)->first();
        $company_data = ViewUserData::where("id", $estimate->company_id)->orderBy('id', 'ASC')->get()->first();
        return view('estimate.preview', compact('estimate', 'estimate_items', 'countries', 'units', 'user_list', 'products', 'proposal_template', 'company_data', 'testimonials'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction(); // Begin a database transaction
        try {
        if ($request->ajax()) {
            $input = $request->all();

            /*foreach($input['image_data'] as $img_value){
                if ($img_value['h_image_one']) {
                    $ext = explode(";", explode("/", $img_value['h_image_one'])[1])[0];
                    $imageName = uniqid() . '-1' . '.' . $ext;
                    //S3 bucket
                    $imageData = $img_value['h_image_one'];
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

                    // $input['thumb_image_one'] = 'public/uploads/resize_image/' . $imageNames;
                    $input['thumb_image_one'] = 'public/'.$input['company_id'].'/products/resize_image/'.$imageNames;
                }
            }*/


            $pdf_cover_page_flg = 0;
            if ($request->has('pdf_cover_page_flg')) {
                $pdf_cover_page_flg = 1;
            }
            $pdf_about_us_flg = 0;
            if ($request->has('pdf_about_us_flg')) {
                $pdf_about_us_flg = 1;
            }
            $pdf_product_flg = 0;
            if ($request->has('pdf_product_flg')) {
                $pdf_product_flg = 1;
            }
            $pdf_est_flg = 0;
            if ($request->has('pdf_est_flg')) {
                $pdf_est_flg = 1;
            }
            $pdf_terms_flg = 0;
            if ($request->has('pdf_terms_flg')) {
                $pdf_terms_flg = 1;
            }

            $pdf_thank_you_flg = 0;
            if ($request->has('pdf_thank_you_flg')) {
                $pdf_thank_you_flg = 1;
            }

            $pdf_testimonial_flg = 0;
            if ($request->has('pdf_testimonial_flg')) {
                $pdf_testimonial_flg = 1;
            }

            $validator = Validator::make($input, [
                'customer_name' => 'required',
                'customer_id' => 'required',
                'customer_state_id' => 'required',
                //                'company_state_id' => 'required',
                'estimate_no' => 'required',
                'estimate_date' => 'required',
                'subtotal' => 'required',
                'net_amount' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);
            }

            if ($input['customer_id'] == 0) {

                return response()->json(['success' => 'Estimate Saved!', 'customer_name' => $input['customer_name']], 400);

            }

            $user = Auth::user();
            $company_id = ($user->company_id) ? $user->company_id : $user->id;

            if (Estimate::where('estimate_no', '=', $input['estimate_no'])->where('company_id', $company_id)->first()) {
                return response()->json(['success' => 'Estimate exists!'], 409);
            }
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
            $data['company_id'] = $company_id;
            $data['sales_person_id'] = $user->id;
            $data['user_id'] = $input['user_id'];
            $data['item_rate_are'] = $input['item_rate_are'];
            $data['customer_notes'] = $input['customer_notes'];
            $data['term_condition'] = $input['term_condition'];
            $data['est_currency_id'] = $input['est_currency_id'];
            $data['est_currency_name'] = $input['est_currency_name'];

            $data['est_cover_page_title'] = $input['est_cover_page_title'];
            $data['est_cover_page_content'] = $input['est_cover_page_content'];

            $data['est_cover_page_footer_one'] = $input['est_cover_page_footer_one'];
            $data['est_cover_page_footer_two'] = $input['est_cover_page_footer_two'];
            $data['est_aboutus_title'] = $input['est_aboutus_title'];
            $data['est_aboutus_content'] = $input['est_aboutus_content'];
            $data['est_term_condition_title'] = $input['est_term_condition_title'];
            $data['est_term_condition_content'] = $input['est_term_condition_content'];
            $data['est_cover_page_title_div'] = $input['est_cover_page_title_div'];
            $data['est_cover_page_content_div'] = $input['est_cover_page_content'];
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

                $field_name = '';
                if (!empty($result->$a))
                    $field_name = $result->$a;

                $data['est_cover_page_content_div'] = trim(str_replace('${' . $value . '}', $field_name, $data['est_cover_page_content_div']), '-');
            }
            $data['est_cover_page_footer_one_div'] = $input['est_cover_page_footer_one_div'];
            $data['est_cover_page_footer_two_div'] = $input['est_cover_page_footer_two_div'];
            preg_match_all('#\${(.*?)\}#', strip_tags($data['est_cover_page_footer_two_div']), $matchAbs);
            foreach ($matchAbs[1] as $key => $value) {
                $valueArr = explode('.', $value);


                if ($valueArr[0] == 'estimates') {
                    $table = 'users_views';
                    $id = $user->id;
                    $selectArr = 'name';
                }

                if ($valueArr[0] == 'customers') {
                    $id = $data['customer_id'];
                    $table = 'customers_views';
                    $selectArr = 'lead_category';
                }

                //                if ($valueArr[0] == 'estimates') {
                //                    $id = $data['estimate_id'];
                //                }
                $result = DB::table($table)
                    ->where('id', $id)
                    ->select($selectArr)
                    ->get()->first();
                $a = $valueArr[1];
                $data['est_cover_page_footer_two_div'] = str_replace('${' . $value . '}', $result->$selectArr, $data['est_cover_page_footer_two_div']);
            }
            $data['est_aboutus_title_div'] = $input['est_aboutus_title_div'];
            $data['est_aboutus_content_div'] = $input['est_aboutus_content_div'];
            preg_match_all('#\${(.*?)\}#', strip_tags($data['est_aboutus_content_div']), $matchAbs);
            foreach ($matchAbs[1] as $key => $value) {
                $valueArr = explode('.', $value);
                //                if ($valueArr[0] == 'customers') {
                //                    $id = $data['customer_id'];
                //                    $table = 'customers_views';
                //                }

                if ($valueArr[0] == 'companies') {
                    $table = 'users_views';
                    $id = $company_id;
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
            $data['est_term_condition_title_div'] = $input['est_term_condition_title_div'];
            $data['est_term_condition_content_div'] = $input['est_term_condition_content_div'];
            $data['testimonial_id'] = 0+$input['testimonial_id'];
            $data['product_id'] = (isset($input['product_id']))?trim(implode(',', $input['product_id']), ','):'';
            $data['pdf_cover_page_flg'] = $pdf_cover_page_flg;
            $data['pdf_about_us_flg'] = $pdf_about_us_flg;
            $data['pdf_product_flg'] = $pdf_product_flg;
            // $data['pdf_est_flg'] = $pdf_est_flg;
            $data['pdf_est_flg'] = 1;
            $data['pdf_terms_flg'] = $pdf_terms_flg;
            $data['pdf_thank_you_flg'] = $pdf_thank_you_flg;
            $data['pdf_testimonial_flg'] = $pdf_testimonial_flg;
            $data['status'] = 'Draft';
            $data['tilt'] = $input['tilt'];
            $data['azumuth'] = $input['azumuth'];
            $data['no_of_panel'] = $input['no_of_panel'];
            $data['panel_wattage'] = $input['panel_wattage'];
            $data['term_condition_id'] = 0+$input['term_condition_id'];
            $data['estimate_version'] = 0;

            $activityLogMsg = 'Estimate created by ' . $user->name;
            $estimate = Estimate::create($data);
            $insert_id = $estimate->id;
            $input['log_id'] = $insert_id;
            $input['log_type'] = 'estimate';
            LogActivity::addToLog($activityLogMsg, $input, 1);

            $tenantdata = DB::connection('mysql')->table('tenants')->where('email', $user->email)->first();
            $tcompany_id = ($tenantdata->company_id) ? $tenantdata->company_id : $tenantdata->id;
            $hdata['estimate_id'] = $insert_id;
            $hdata['user_id'] = $user->id;
            $hdata['company_id'] = $tcompany_id;
            $hdata['email'] = $user->email;
            $hdata['domain'] = $user->domain;
            $leadhistory = EstimateHistory::create($hdata);

            /*$logInput['estimate_id'] = $insert_id;
            $logInput['assigned_to'] = $input['user_id'];
            $logInput['customer_id'] = $input['customer_id'];
            $logInput['internal_remarks'] = "Estimate added";
            $logInput['entry_type'] = "estimates";
            $logInput['user_id'] = $user->id;
            $logInput['company_id'] = $company_id;
            $logInput['created_by'] = $user->id;
            $logInput['updated_by'] = $user->id;
            LogActivity::addToActivityLog($logInput);

            $bindUser = DB::table('users')
                ->where('id', $input['user_id'])
                ->select('name')
                ->get()->first();

            $logInput1['estimate_id'] = $insert_id;
            $logInput1['assigned_to'] = $input['user_id'];
            $logInput1['customer_id'] = $input['customer_id'];
            $logInput1['internal_remarks'] = "Assigned to " . $bindUser->name;
            $logInput1['entry_type'] = "estimates";
            $logInput1['user_id'] = $user->id;
            $logInput1['company_id'] = $company_id;
            $logInput1['created_by'] = $user->id;
            $logInput['updated_by'] = $user->id;
            LogActivity::addToActivityLog($logInput1);*/


            if ($insert_id) {
                $estimate_coll = collect($input['data']);
                $estimateArr = $estimate_coll->values()->toArray();
                foreach ($estimateArr as $key => $csm) {
                    $estimateArr[$key]['estimate_id'] = $insert_id;
                    $estimateArr[$key]['company_id'] = $company_id;
                    $estimateArr[$key]['user_id'] = $user->id;
                    $estimateArr[$key]['technical_specification'] = $csm['item_technical_specification'];
                    unset($estimateArr[$key]['item_technical_specification']);
                }
                EstimateItems::insert($estimateArr);
                if($pdf_product_flg==1) {
                    foreach ($input['image_data'] as $img_value) {
                        $input['image_one'] = '';
                        $input['image_two'] = '';
                        $input['image_three'] = '';
                        $input['thumb_image_one'] = '';
                        $input['thumb_image_two'] = '';
                        $input['thumb_image_three'] = '';
                        if ($img_value['h_image_one'] == null) {

                            $productData = Product::find($img_value['tmp_product_id']);

                            if ($productData->image_one) {
                                $sourcePath1 = $productData->image_one;
                                $destinationPath1 = str_replace('public/' . $company_id . '/products/', 'public/' . $company_id . '/estimate/' . $insert_id . '/', $sourcePath1);

                                $sourcePath1t = $productData->thumb_image_one;
                                $destinationPath1t = str_replace('public/' . $company_id . '/products/', 'public/' . $company_id . '/estimate/' . $insert_id . '/', $sourcePath1t);
                            } else {
                                $sourcePath1 = 'template/64x64.png';
                                $destinationPath1 = 'public/' . $company_id . '/estimate/' . $insert_id . '/thumbnail/' . bin2hex(random_bytes(8)) . '.png';

                                $sourcePath1t = 'template/64x64.png';
                                $destinationPath1t = 'public/' . $company_id . '/estimate/' . $insert_id . '/resize_image/' . bin2hex(random_bytes(8)) . '.png';
                            }

                            $temp_imp_1 = Storage::disk('s3')->get($sourcePath1);
                            Storage::disk('s3')->put($destinationPath1, $temp_imp_1, 'public');
                            $temp_imp_2 = Storage::disk('s3')->get($sourcePath1t);
                            Storage::disk('s3')->put($destinationPath1t, $temp_imp_2, 'public');
                            /*Storage::disk('s3')->copy($sourcePath1, $destinationPath1);
                            Storage::disk('s3')->copy($sourcePath1t, $destinationPath1t);*/

                            $input['image_one'] = $destinationPath1;
                            $input['thumb_image_one'] = $destinationPath1t;
                        }

                        if ($img_value['h_image_one']) {
                            $ext = explode(";", explode("/", $img_value['h_image_one'])[1])[0];
                            $imageName = uniqid() . '-1' . '.' . $ext;
                            // Original image upload to 'estimate' folder in local storage
                            $imageData = $img_value['h_image_one'];
                            // Decode the base64 image data
                            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));


                            // Upload the image to S3
                            Storage::disk('s3')->put('public/' . $company_id . '/estimate/' . $insert_id . '/thumbnail/' . $imageName, $imageData, 'public');

                            // Get the S3 URL of the uploaded image
                            $s3Url = Storage::disk('s3')->url('public/' . $company_id . '/estimate/' . $insert_id . '/thumbnail/' . $imageName);
                            $input['image_one'] = 'public/' . $company_id . '/estimate/' . $insert_id . '/thumbnail/' . $imageName;


                            /*
                                                    // Upload the original image to 'estimate' folder in local storage
                                                    Storage::put('public/estimate/'.$imageName, $imageData);

                                                    // Assign the path of the original image to the input array
                                                    $input['image_one'] = 'public/estimate/'.$imageName;*/
                            $imageNames = Str::uuid() . '-1.' . $ext;
                            // Use intervention/image package to create an image instance and save it to the desired path
                            $image = Image::make($imageData);

                            // Resize the image to the desired dimensions (e.g., width: 64px, height: 64px)
                            $image->resize(64, 64);


                            // Upload the resized image to S3
                            Storage::disk('s3')->put('public/' . $company_id . '/estimate/' . $insert_id . '/resize_image/' . $imageNames, $image->encode($ext), 'public');

                            // Get the S3 URL of the uploaded image
                            $s3Url = Storage::disk('s3')->url('public/' . $company_id . '/estimate/' . $insert_id . '/resize_image/' . $imageNames);

                            $input['thumb_image_one'] = 'public/' . $company_id . '/estimate/' . $insert_id . '/resize_image/' . $imageNames;


                            /* // Generate a unique name for the thumbnail image
                             $thumbImageName = Str::uuid() . '-1.' . $ext;

                             // Save the resized thumbnail image to 'estimate' folder in local storage
                             $image->save(storage_path('app/public/estimate/resize_image/'.$thumbImageName));

                             // Assign the path of the thumbnail image to the input array
                             $input['thumb_image_one'] = 'public/estimate/resize_image/'.$thumbImageName;*/
                        }

                        if ($img_value['h_image_two'] == null) {

                            $productData = Product::find($img_value['tmp_product_id']);
                            if ($productData->image_two) {
                                $sourcePath2 = $productData->image_two;
                                $destinationPath2 = str_replace('public/' . $company_id . '/products/', 'public/' . $company_id . '/estimate/' . $insert_id . '/', $sourcePath2);

                                $sourcePath2t = $productData->thumb_image_two;
                                $destinationPath2t = str_replace('public/' . $company_id . '/products/', 'public/' . $company_id . '/estimate/' . $insert_id . '/', $sourcePath2t);
                            } else {
                                $sourcePath2 = 'template/64x64.png';
                                $destinationPath2 = 'public/' . $company_id . '/estimate/' . $insert_id . '/thumbnail/' . bin2hex(random_bytes(8)) . '.png';

                                $sourcePath2t = 'template/64x64.png';
                                $destinationPath2t = 'public/' . $company_id . '/estimate/' . $insert_id . '/resize_image/' . bin2hex(random_bytes(8)) . '.png';
                            }

                            $temp_imp_1 = Storage::disk('s3')->get($sourcePath2);
                            Storage::disk('s3')->put($destinationPath2, $temp_imp_1, 'public');
                            $temp_imp_2 = Storage::disk('s3')->get($sourcePath2t);
                            Storage::disk('s3')->put($destinationPath2t, $temp_imp_2, 'public');
                            /*Storage::disk('s3')->copy($sourcePath2, $destinationPath2);
                            Storage::disk('s3')->copy($sourcePath2t, $destinationPath2t);*/

                            $input['image_two'] = $destinationPath2;
                            $input['thumb_image_two'] = $destinationPath2t;
                        }

                        if ($img_value['h_image_two']) {
                            $ext = explode(";", explode("/", $img_value['h_image_two'])[1])[0];
                            $imageName = uniqid() . '-2' . '.' . $ext;
                            // Original image upload to 'estimate' folder in local storage
                            $imageData = $img_value['h_image_two'];
                            // Decode the base64 image data
                            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

                            // Upload the image to S3
                            Storage::disk('s3')->put('public/' . $company_id . '/estimate/' . $insert_id . '/thumbnail/' . $imageName, $imageData, 'public');

                            // Get the S3 URL of the uploaded image
                            $s3Url = Storage::disk('s3')->url('public/' . $company_id . '/estimate/' . $insert_id . '/thumbnail/' . $imageName);
                            $input['image_two'] = 'public/' . $company_id . '/estimate/' . $insert_id . '/thumbnail/' . $imageName;


                            /*// Upload the original image to 'estimate' folder in local storage
                            Storage::put('public/estimate/'.$imageName, $imageData);

                            // Assign the path of the original image to the input array
                            $input['image_two'] = 'public/estimate/'.$imageName;*/
                            $imageNames = Str::uuid() . '-2.' . $ext;
                            // Use intervention/image package to create an image instance and save it to the desired path
                            $image = Image::make($imageData);

                            // Resize the image to the desired dimensions (e.g., width: 64px, height: 64px)
                            $image->resize(64, 64);

                            // Upload the resized image to S3
                            Storage::disk('s3')->put('public/' . $company_id . '/estimate/' . $insert_id . '/resize_image/' . $imageNames, $image->encode($ext), 'public');

                            // Get the S3 URL of the uploaded image
                            $s3Url = Storage::disk('s3')->url('public/' . $company_id . '/estimate/' . $insert_id . '/resize_image/' . $imageNames);

                            $input['thumb_image_two'] = 'public/' . $company_id . '/estimate/' . $insert_id . '/resize_image/' . $imageNames;

                            /*// Generate a unique name for the thumbnail image
                            $thumbImageName = Str::uuid() . '-2.' . $ext;

                            // Save the resized thumbnail image to 'estimate' folder in local storage
                            $image->save(storage_path('app/public/estimate/resize_image/'.$thumbImageName));

                            // Assign the path of the thumbnail image to the input array
                            $input['thumb_image_two'] = 'public/estimate/resize_image/'.$thumbImageName;*/
                        }

                        if ($img_value['h_image_three'] == null) {

                            $productData = Product::find($img_value['tmp_product_id']);
                            if ($productData->image_three) {
                                $sourcePath3 = $productData->image_three;
                                $destinationPath3 = str_replace('public/' . $company_id . '/products/', 'public/' . $company_id . '/estimate/' . $insert_id . '/', $sourcePath3);

                                $sourcePath3t = $productData->thumb_image_three;
                                $destinationPath3t = str_replace('public/' . $company_id . '/products/', 'public/' . $company_id . '/estimate/' . $insert_id . '/', $sourcePath3t);
                            } else {
                                $sourcePath3 = 'template/64x64.png';
                                $destinationPath3 = 'public/' . $company_id . '/estimate/' . $insert_id . '/thumbnail/' . bin2hex(random_bytes(8)) . '.png';

                                $sourcePath3t = 'template/64x64.png';
                                $destinationPath3t = 'public/' . $company_id . '/estimate/' . $insert_id . '/resize_image/' . bin2hex(random_bytes(8)) . '.png';
                            }

                            $temp_imp_1 = Storage::disk('s3')->get($sourcePath3);
                            Storage::disk('s3')->put($destinationPath3, $temp_imp_1, 'public');
                            $temp_imp_2 = Storage::disk('s3')->get($sourcePath3t);
                            Storage::disk('s3')->put($destinationPath3t, $temp_imp_2, 'public');
                            /*Storage::disk('s3')->copy($sourcePath3, $destinationPath3);
                            Storage::disk('s3')->copy($sourcePath3t, $destinationPath3t);*/

                            $input['image_three'] = $destinationPath3;
                            $input['thumb_image_three'] = $destinationPath3t;

                        }

                        if ($img_value['h_image_three']) {
                            $ext = explode(";", explode("/", $img_value['h_image_three'])[1])[0];
                            $imageName = uniqid() . '-3' . '.' . $ext;
                            // Original image upload to 'estimate' folder in local storage
                            $imageData = $img_value['h_image_three'];
                            // Decode the base64 image data
                            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

                            // Upload the image to S3
                            Storage::disk('s3')->put('public/' . $company_id . '/estimate/' . $insert_id . '/thumbnail/' . $imageName, $imageData, 'public');

                            // Get the S3 URL of the uploaded image
                            $s3Url = Storage::disk('s3')->url('public/' . $company_id . '/' . $insert_id . '/thumbnail/' . $imageName);
                            $input['image_three'] = 'public/' . $company_id . '/estimate/' . $insert_id . '/thumbnail/' . $imageName;

                            /*// Upload the original image to 'estimate' folder in local storage
                            Storage::put('public/estimate/'.$imageName, $imageData);

                            // Assign the path of the original image to the input array
                            $input['image_three'] = 'public/estimate/'.$imageName;*/
                            $imageNames = Str::uuid() . '-3.' . $ext;
                            // Use intervention/image package to create an image instance and save it to the desired path
                            $image = Image::make($imageData);

                            // Resize the image to the desired dimensions (e.g., width: 64px, height: 64px)
                            $image->resize(64, 64);

                            // Upload the resized image to S3
                            Storage::disk('s3')->put('public/' . $company_id . '/estimate/' . $insert_id . '/resize_image/' . $imageNames, $image->encode($ext), 'public');

                            // Get the S3 URL of the uploaded image
                            $s3Url = Storage::disk('s3')->url('public/' . $company_id . '/estimate/' . $insert_id . '/resize_image/' . $imageNames);

                            $input['thumb_image_three'] = 'public/' . $company_id . '/estimate/' . $insert_id . '/resize_image/' . $imageNames;

                            /*// Generate a unique name for the thumbnail image
                            $thumbImageName = Str::uuid() . '-3.' . $ext;

                            // Save the resized thumbnail image to 'estimate' folder in local storage
                            $image->save(storage_path('app/public/estimate/resize_image/'.$thumbImageName));

                            // Assign the path of the thumbnail image to the input array
                            $input['thumb_image_three'] = 'public/estimate/resize_image/'.$thumbImageName;*/
                        }
                        $estimatePhotos = EstimatePhoto::create([
                            "estimate_id" => $insert_id,
                            "product_flag" => 1,
                            "product_id" => $img_value['tmp_product_id'],
                            "image_one" => $input['image_one'],
                            "image_two" => $input['image_two'],
                            "image_three" => $input['image_three'],
                            "thumb_image_one" => $input['thumb_image_one'],
                            "thumb_image_two" => $input['thumb_image_two'],
                            "thumb_image_three" => $input['thumb_image_three'],
                            "user_id" => $user->id,
                            "comapny_id" => $company_id
                        ]);
                    }
                }
            }

            $estimate_auto_number = EstimateAutoNumber::where('company_id', $company_id)
                ->select('estimate_prefix', 'estimate_next_no')
                ->get()->first();

            $tmp_est_no = $estimate_auto_number->estimate_prefix . $estimate_auto_number->estimate_next_no;
            $explodeEst = explode("-", $input['estimate_no']);

            if (is_array($explodeEst)) {
                $lastElement = end($explodeEst);
                $cleanedStr = 0 + preg_replace("/[^0-9]/", "", $lastElement);
            } else {
                $cleanedStr = 0 + preg_replace("/[^0-9]/", "", $input['estimate_no']);
            }
//            $cleanedStr = 0+preg_replace("/[^0-9]/", "", $input['estimate_no']);
            if ($cleanedStr >= 0 + ($estimate_auto_number->estimate_next_no)) {
                $tmp_est_no = $cleanedStr + 1;
                EstimateAutoNumber::where('company_id', $company_id)->update(array('estimate_next_no' => '00' . $tmp_est_no));
            }

            $proposal_template_temp = ProposalTemplates::where('company_id', $company_id)->select('new_pdf_flag')->first();
            if ($proposal_template_temp->new_pdf_flag == 0)
                $this->estimateGeneratePdf($insert_id, $company_id);
            if ($proposal_template_temp->new_pdf_flag == 1)
                $this->estimateGeneratemPdf($insert_id, $company_id);
            DB::commit();
            return response()->json(['success' => 'Estimate Saved!', 'estimate_id' => $insert_id, 'customer_id' => $input['customer_id'], 'customer_id_decode' => Crypt::encrypt($input['customer_id']), 'url' => url('quotes/edit/' . Crypt::encrypt($insert_id))], 201);
        }
        } catch (\Exception $e) {
            // Log the exception
            \Log::error('Error in store method: ' . $e->getMessage());
            DB::rollback();
            // Return a generic error response
            return response()->json(['error' => 'An error occurred. Please try again.','data' =>$e->getMessage()], 500);

        }
    }

    public function estimateGeneratePdf($insert_id, $company_id)
    {
        try {
            //PDF start
            $estimate = Estimate::where([["id", $insert_id], ["company_id", "=", $company_id]])->get()->first();
            $estimate_items = EstimateItems::leftJoin('items', 'estimate_items.item_id', '=', 'items.id')
                ->where([["estimate_items.estimate_id", "=", $insert_id], ["estimate_items.company_id", "=", $company_id]])
                ->orderBy('estimate_items.id', 'ASC')
                ->get(["estimate_items.*", "items.image_icon as image_icon"]);

            $estimate_items_sp = EstimateItems::leftJoin('items', 'estimate_items.item_id', '=', 'items.id')
                ->where([["estimate_items.estimate_id", "=", $insert_id], ["estimate_items.company_id", "=", $company_id], ["estimate_items.technical_specification", "!=", '']]);


            $proposal_template = ProposalTemplates::where('company_id', $company_id)->first();
            $company_data = ViewUserData::where("id", $company_id)->orderBy('id', 'ASC')->get()->first();


            $salesPersonInfo = User::where("id", $estimate->sales_person_id)->get()->first();
            $term_condition_data = TermCondition::where("id", $estimate->term_condition_id)->orderBy('id', 'ASC')->get()->first();
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf::setHeaderCallback(function ($pdf) use ($proposal_template, $estimate) {
            // if ($pdf->PageNo() > 1) {
                if ($estimate['pdf_cover_page_flg'] != 1 || $estimate['pdf_about_us_flg'] == 1 || $estimate['pdf_product_flg'] == 1 || $estimate['pdf_est_flg'] == 1 || $estimate['pdf_terms_flg'] == 1 || $estimate['pdf_testimonial_flg'] == 1) {
                    // $image_file = public_path(Storage::url($proposal_template->header_logo));
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
                $footer = '<table><tr style="background-color:' . $proposal_template->theme_footer_color . ';"><td width="' . $first_td_width . '"><table cellpadding="6"><tr><td><a href="' . $company_data->website_link . '" target="_blank" style="text-decoration: none;color:#fff;">' . $company_data->company_name . '</a></td></tr></table></td><td style="text-align: right;color:#fff;"  width="125" align="right"><table border="0" style="text-align: right;" align="right"><tr>' . $facebook_url . $instagram_url . $linkedin_url . $twitter_url . '</tr></table></td></tr></table>';
                //                $footer = '<table cellpadding="6"><tr style="background-color:' . $proposal_template->theme_color_one . ';"><td><a href="https://heavendesigns.in" target="_blank" style="text-decoration: none;color:#fff;">Heaven Designs</a></td><td style="text-align: right;color:#fff;">Social Media Link</td></tr></table>';
                $pdf->SetY(-9.6);
                $pdf->SetX(0);
                /* } else {
                     $footer = '<table cellpadding="6"><tr style="background-color:' . $proposal_template->theme_color_two . ';"><td><a href="https://heavendesigns.in" target="_blank" style="text-decoration: none;color:#fff;">Heaven Designs</a></td><td style="text-align: right;color:#fff;">Social Media Link</td></tr></table>';
                     $pdf->SetY(-9.6);
                 }*/
                $pdf->writeHTML($footer, true, false, true, false, '');
                //                $footer = '<table cellpadding="6"><tr style="background-color:' . $proposal_template->theme_color_two . ';"><td><a href="https://heavendesigns.in" target="_blank" style="text-decoration: none;color:' . $proposal_template->theme_color_one . ';">Heaven Designs</a></td><td style="text-align: right;color:' . $proposal_template->theme_color_one . ';">Social Media Link</td></tr></table>';
                //                $pdf->SetY(-9.6);
                //                $pdf->writeHTML($footer, true, false, true, false, '');
            });

            $pdf::SetAuthor('System');
            $pdf::SetTitle('My Report');
            $pdf::SetSubject('Report of System');


            //First page
            if ($estimate['pdf_cover_page_flg']) {
                $pdf::SetMargins(0, 0, 0, false);
                $pdf::SetFontSubsetting(false);
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
                $products = Product::select(["name", "id", "image_one", "image_two", "image_three"])->where('status', '=', 0)->whereIn('id', explode(',', $estimate->product_id))->where('company_id', $company_id)->orderByRaw("FIELD(id,$estimate->product_id)")->get();
                $pdf::startPageGroup();
                $pdf::SetMargins(7, 15.5, 7, false);
                $pdf::SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
                $pdf::SetFont('helvetica', 'R', 11);
                $pdf::AddPage('P', 'A4');

                $view = \View::make('pdf.product-page-web', compact('products', 'proposal_template', 'company_data'));
                $html = $view->render();
                $pdf::writeHTML($html, true, false, true, false, '');
            }

            //Fourth page
            if ($estimate['pdf_est_flg']) {
                $customer_data = Customer::where("id", $estimate->customer_id)->select('gst_no', 'email', 'company_name', 'currency_name_country_id')->orderBy('id', 'DESC')->get()->first();

                $currency_id = $estimate->est_currency_id;
                if ($estimate->est_currency_id == 0) {
                    $currency_id = $company_data->country_id;
                }

                $country_data = Country::where("id", $currency_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->orderBy('id', 'DESC')->get()->first();
                $pdf::startPageGroup();
                $pdf::SetMargins(7, 15.5, 7, false);
                $pdf::SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
                //            $pdf::SetFont('helvetica', 'R', 11); //11
                $pdf::SetFont('helvetica', 'R', 8); //11
                //            $pdf::SetFont('helvetica', '', 8, '', false); //11
                $pdf::AddPage('P', 'A4');

                $view = \View::make('pdf.estimate-page-web', compact('estimate', 'estimate_items', 'proposal_template', 'company_data', 'salesPersonInfo', 'customer_data', 'country_data'));
                $html = $view->render();
                $pdf::writeHTML($html, true, false, true, false, '');
            }

            if ($estimate_items_sp->count() > 0) {
                $pdf::startPageGroup();
                $pdf::SetMargins(7, 15.5, 7, false);
                $pdf::SetFontSubsetting(true);
                $pdf::SetFont('helvetica', 'R', 11);
                $pdf::SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
                $pdf::AddPage('P', 'A4');
                $estimate_items_sp = $estimate_items_sp->select(["estimate_items.technical_specification", "items.name"])->get();

                $view = \View::make('pdf.specification-page-new', compact('estimate_items_sp'));
                $html = $view->render();
                $pdf::writeHTML($html, true, false, true, false, '');
            }

            //Fifth page
            if ($estimate['pdf_terms_flg']) {
                $pdf::startPageGroup();
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

                $testimonials = Testimonial::select(["name", "id", "client_name_one", "image_one", "rating_one", "description_one", "client_name_two", "image_two", "rating_two", "description_two", "client_name_three", "image_three", "rating_three", "description_three"])->where('status', '=', 0)->where('id', $estimate->testimonial_id)->where('company_id', $company_id)->get()->first();
                $pdf::startPageGroup();
                $pdf::SetMargins(7, 15.5, 7, false);
                $pdf::SetAutoPageBreak(true, PDF_MARGIN_FOOTER);
                $pdf::SetFont('helvetica', 'R', 11);
                //            $pdf::SetFont('shruti', 'R', 11);
                //            $pdf::SetFont('gujratisaral1','R',14);
                $pdf::AddPage('P', 'A4');

                $view = \View::make('pdf.testimonial-page-web', compact('proposal_template', 'company_data', 'testimonials'));
                $html = $view->render();
                $pdf::writeHTML($html, true, false, true, false, '');
                //            $pdf::writeHTML("મિત્રો દ્રશ્ય સંસ્કાર પ્રકાશ પંથ ત્રિકમ", true, false, true, false, '');
            }

            //Seven page
            if ($proposal_template->thank_you_flg) {
                $pdf::startPageGroup();
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
            //        $paramArr['activity_estimate_status'] = $estimate['status'];
            $paramArr['activity_estimate_status'] = ($estimate['status'] == 'Draft' && $estimate['estimate_version'] == 0) ? 'Draft' : 'Inprogress'; // && $estimate['estimate_version'] == 0
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
            $pdf::Output(public_path('storage/document/' . $company_id . '/' . $pdf_name . '.pdf'), 'F');

            // $mpdf->Output(public_path('storage/document/' . $company_id . '/' . $pdf_name . '.pdf'), 'F');

            $path = storage_path('app/public/document/' . $company_id . '/' . $pdf_name . '.pdf');
            $tmp_paths = Storage::disk('s3')->put('public/' . $company_id . '/documents/' . $pdf_name . '.pdf', file_get_contents($path), 'public');
            // Storage::disk('s3')->setVisibility($tmp_paths, 'public');
            Storage::disk('s3')->url('public/' . $company_id . '/documents/' . $pdf_name . '.pdf');
            unlink($path);
            //PDF end
        } catch (\Exception $e) {
            // Log the exception
            \Log::error('Error in store method: ' . $e->getMessage());

            // Return a generic error response
            return response()->json(['error' => 'An error occurred. Please try again.'], 500);
        }
    }

//    public function estimateGeneratemPdf($insert_id, $company_id)
//    {
//        try {
//            /*$insert_id = 111;
//            $company_id = 1;*/
//            //PDF start
//            $estimate = Estimate::where([["id", $insert_id], ["company_id", "=", $company_id]])->get()->first();
//            $estimate_items = EstimateItems::leftJoin('items', 'estimate_items.item_id', '=', 'items.id')
//                ->where([["estimate_items.estimate_id", $insert_id], ["estimate_items.company_id", "=", $company_id]])
//                ->orderBy('estimate_items.id', 'ASC')
//                ->get(["estimate_items.*", "items.image_icon as image_icon"]);
//            $proposal_template = ProposalTemplates::where('company_id', $company_id)->first();
//            $company_data = ViewUserData::where("id", $company_id)->orderBy('id', 'ASC')->get()->first();
//
//
//            $salesPersonInfo = User::where("id", $estimate->sales_person_id)->get()->first();
//            $term_condition_data = TermCondition::where("id", $estimate->term_condition_id)->orderBy('id', 'ASC')->get()->first();
//
//            $mpdf = new Mpdf([
//                'mode' => 'utf-8',
//                'tempDir' => storage_path('tempdir')
//            ]);
////        $mpdf->SetCompression(false);
//            $mpdf->showImageErrors = true;
//            $mpdf->debug = true;
//            $mpdf->curlAllowUnsafeSslRequests = true;
//            $mpdf->autoScriptToLang = true;
//            $mpdf->autoLangToFont = true;
//            $mpdf->allow_charset_conversion = true;
//
//            /*$mpdf->SetWatermarkText('Heaven Design Pvt. Ltd.');
//            $mpdf->showWatermarkText = true;
//            $mpdf->watermarkTextAlpha = 0.1;
//            $mpdf->watermarkImageAlpha = 0.5;*/
//
//            $logo = Storage::disk('s3')->url($proposal_template->header_logo);
////        $logo = Storage::disk('s3')->temporaryUrl($proposal_template->header_logo,Carbon::now()->addMinutes(20));
////        $logo = public_path(Storage::url($proposal_template->header_logo));
//            $header = '<div style="text-align: right; font-weight: bold;border-bottom: 1px solid #fff;margin-right:' . $proposal_template->header_logo_left . 'px;padding-top:' . $proposal_template->header_logo_top . 'px;"><img src="' . $logo . '" width="' . $proposal_template->header_logo_size . '"/></div>';
//            // Define the Headers before writing anything so they appear on the first page
//
//
//            $facebook_url = '';
//            $instagram_url = '';
//            $linkedin_url = '';
//            $twitter_url = '';
//            $width = 0;
//            if ($company_data->facebook_url) {
//                $facebook_url = '<td><a href="' . $company_data->facebook_url . '"><img src="' . public_path(Storage::url('facebook.png')) . '" width="30"></a></td>';
//            }
//            if ($company_data->instagram_url) {
//                $instagram_url = '<td><a href="' . $company_data->instagram_url . '"><img src="' . public_path(Storage::url('instagram.png')) . '" width="30"></a></td>';
//            }
//            if ($company_data->linkedin_url) {
//                $linkedin_url = '<td><a href="' . $company_data->linkedin_url . '"><img src="' . public_path(Storage::url('linkedin.png')) . '" width="30"></a></td>';
//            }
//            if ($company_data->twitter_url) {
//                $twitter_url = '<td><a href="' . $company_data->twitter_url . '"><img src="' . public_path(Storage::url('twitter.png')) . '" width="30"></a></td>';
//            }
//
//            $footer = '
//        <table width="100%" style="vertical-align: middle; font-family: Arial, Helvetica, serif;
//            font-size: 12pt; color: #fff; font-style: normal;background: #152e42;">
//            <tr>
//                <td width="47%"><a href="' . $company_data->website_link . '" target="_blank" style="text-decoration: none !important;color:#fff;">' . $company_data->company_name . '</a></td>
//                <td width="6%" align="center">{PAGENO}/{nbpg}</td>
//                <td width="47%" style="text-align: right;">
//                    <table>
//                        <tr>
//                            ' . $facebook_url . $instagram_url . $linkedin_url . $twitter_url . '
//                        </tr>
//                    </table>
//                </td>
//            </tr>
//        </table>';
//
//            if ($estimate['pdf_cover_page_flg']) {
//                $mpdf->SetHTMLHeader();
//                $mpdf->SetHTMLFooter();
//                $mpdf->AddPage('P', '', '', '', '', 0, 0, 0, -1, 0, 0);
//
//                //write content
////            $coverHtml1 = view('pdf.cover-page-new-web', compact('estimate', 'proposal_template', 'company_data'))->render();
//                $coverHtml1 = view('pdf.cover-page-new-web', compact('estimate', 'proposal_template', 'company_data'))->render();
//                $mpdf->WriteHTML($coverHtml1);
//            }
//
//            if ($estimate['pdf_about_us_flg']) {
//                $mpdf->SetHTMLHeader($header);
//                $mpdf->SetHTMLFooter($footer);
//                $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);
////            $aboutHtml = view('pdf.about-page-new-web', compact('estimate', 'proposal_template', 'company_data'))->render();
//                $aboutHtml = view('pdf.about-page-new-web', compact('estimate', 'proposal_template', 'company_data'))->render();
//                $mpdf->WriteHTML($aboutHtml);
//            }
//
//            if ($estimate['pdf_product_flg']) {
//                $pro_title = ($proposal_template->product_title) ? html_entity_decode($proposal_template->product_title, ENT_QUOTES, 'UTF-8') : '';
//                $pro_content = ($proposal_template->product_content) ? html_entity_decode($proposal_template->product_content, ENT_QUOTES, 'UTF-8') : '';
//                $products = Product::select(["name", "id", "image_one", "image_two", "image_three"])->where('status', '=', 0)->whereIn('id', explode(',', $estimate->product_id))->where('company_id', $company_id)->orderByRaw("FIELD(id,$estimate->product_id)")->get();
//                $mpdf->SetHTMLHeader($header);
//                $mpdf->SetHTMLFooter($footer);
//                $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);
//
////            $productHtml = view('pdf.product-page-new-web', compact('products', 'proposal_template', 'company_data'))->render();
//                $productHtml = view('pdf.product-page-new-web', compact('products', 'proposal_template', 'company_data'))->render();
//                $mpdf->WriteHTML($productHtml);
//            }
//
//            //Fourth page
//            if ($estimate['pdf_est_flg']) {
//                $pdf_title_name = ($proposal_template->est_title) ? $proposal_template->est_title : 'Estimate';
//                $customer_data = Customer::where("id", $estimate->customer_id)->select('gst_no', 'email', 'company_name', 'currency_name_country_id')->orderBy('id', 'DESC')->get()->first();
//                $country_data = Country::where("id", $customer_data->currency_name_country_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->orderBy('id', 'DESC')->get()->first();
//                $mpdf->SetHTMLHeader($header);
//                $mpdf->SetHTMLFooter($footer);
//                $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);
//
////            $estHtml = view('pdf.estimate-page-new-web', compact('estimate', 'estimate_items', 'proposal_template', 'company_data', 'salesPersonInfo','customer_data', 'country_data'))->render();
//                $estHtml = view('pdf.estimate-page-new-web', compact('estimate', 'estimate_items', 'proposal_template', 'company_data', 'salesPersonInfo', 'customer_data', 'country_data'))->render();
//                $mpdf->writeHTML($estHtml);
//            }
//
//            //Fifth page
//            if ($estimate['pdf_terms_flg']) {
//                $mpdf->SetHTMLHeader($header);
//                $mpdf->SetHTMLFooter($footer);
//                $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);
//
////            $termsHtml = view('pdf.term-and-condition-page-new-web', compact('estimate', 'proposal_template', 'term_condition_data'))->render();
//                $termsHtml = view('pdf.term-and-condition-page-new', compact('estimate', 'proposal_template', 'term_condition_data'))->render();
//                $mpdf->autoPageBreak = true;
//                $mpdf->writeHTML($termsHtml);
//            }
//
//            //Sixth page
//            if ($estimate['pdf_testimonial_flg']) {
//
//                $testimonials = Testimonial::select(["name", "id", "client_name_one", "image_one", "rating_one", "description_one", "client_name_two", "image_two", "rating_two", "description_two", "client_name_three", "image_three", "rating_three", "description_three"])->where('status', '=', 0)->where('id', $estimate->testimonial_id)->where('company_id', $company_id)->get()->first();
//                $mpdf->SetHTMLHeader($header);
//                $mpdf->SetHTMLFooter($footer);
//                $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);
//
////            $testiHtml = view('pdf.testimonial-page-new-web', compact('proposal_template', 'company_data', 'testimonials'))->render();
//                $testiHtml = view('pdf.testimonial-page-new-web', compact('proposal_template', 'company_data', 'testimonials'))->render();
//                $mpdf->writeHTML($testiHtml);
//                //            $mpdf->writeHTML("મિત્રો દ્રશ્ય સંસ્કાર પ્રકાશ પંથ ત્રિકમ", true, false, true, false, '');
//            }
//
//            //Seven page
//            $mpdf->SetHTMLHeader($header);
//            $mpdf->SetHTMLFooter($footer);
//            $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);
//
////        $thanksHtml = view('pdf.thank-you-page-new-web', compact('company_data', 'proposal_template', 'salesPersonInfo'))->render();
//            $thanksHtml = view('pdf.thank-you-page-new-web', compact('company_data', 'proposal_template', 'salesPersonInfo'))->render();
//            $mpdf->writeHTML($thanksHtml);
//
//
////        $mpdf->Output('test-pdf.pdf', Destination::DOWNLOAD);
//
//            $pdf_name = $estimate['estimate_no'];
//            $paramArr['internal_remarks'] = "Estimate created";
//            if ($estimate['estimate_version'] > 0) {
//                $pdf_name = $estimate['estimate_no'] . '-V' . $estimate['estimate_version'];
//                $paramArr['internal_remarks'] = "Estimate updated";
//            }
//            $customerData = Customer::select("assigned_to_user")->where('id', '=', $estimate['customer_id'])->first();
//            $customer_view_data = ViewCustomerData::select("last_follow_up_datetime")->where('id', '=', $estimate['customer_id'])->first();
//
//
//            $paramArr['assigned_to'] = $customerData->assigned_to_user;
//            $paramArr['customer_id'] = $estimate['customer_id'];
//            $paramArr['estimate_id'] = $insert_id;
//            $paramArr['activity_type'] = 5;
//            $paramArr['estimate_version_no'] = $pdf_name;
//            $paramArr['activity_name'] = 'Estimate';
//            //        $paramArr['activity_estimate_status'] = $estimate['status'];
//            $paramArr['activity_estimate_status'] = ($estimate['status'] == 'Draft' && $estimate['estimate_version'] == 0) ? 'Draft' : 'Inprogress'; // && $estimate['estimate_version'] == 0
//            $paramArr['activity_notes'] = 'updated Estimate';
//            $paramArr['entry_type'] = 'estimate';
//            $paramArr['is_modified'] = 0;
//            $paramArr['user_id'] = $this->logged_user->id;
//            $paramArr['company_id'] = $this->company_id;
//            $paramArr['created_by'] = $this->logged_user->id;
//            $paramArr['updated_by'] = $this->logged_user->id;
//            $paramArr['net_amount'] = $estimate['net_amount'];
//            $paramArr['follow_up_datetime'] = $customer_view_data->last_follow_up_datetime;
//            EstimateTimeline::create($paramArr);
//            Estimate::where('id', $insert_id)->update(array('status' => $paramArr['activity_estimate_status']));
//            //            $pdf::Output('hello_world.pdf', 'I');
//            $mpdf->Output(public_path('storage/document/' . $company_id . '/' . $pdf_name . '.pdf'), 'F');
//
//
//            $path = storage_path('app/public/document/' . $company_id . '/' . $pdf_name . '.pdf');
//            $tmp_paths = Storage::disk('s3')->put('public/' . $company_id . '/documents/' . $pdf_name . '.pdf', file_get_contents($path), 'public');
////        Storage::disk('s3')->setVisibility($tmp_paths, 'public');
//            Storage::disk('s3')->url('public/' . $company_id . '/documents/' . $pdf_name . '.pdf');
//            unlink($path);
////        $pdf::Output(public_path('storage/document/' . $company_id . '/' . $pdf_name . '.pdf'), 'F');
//            //PDF end
//        } catch (\Exception $e) {
//            // Log the exception
//            \Log::error('Error in store method: ' . $e->getMessage());
//
//            // Return a generic error response
//            return response()->json(['error' => 'An error occurred. Please try again.'], 500);
//        }
//    }

    public function estimateGeneratemPdf($insert_id, $company_id)
    {

        /*$insert_id = 111;
        $company_id = 1;*/
        //PDF start
        $estimate = Estimate::where([["id", $insert_id], ["company_id", "=", $company_id]])->get()->first();
        $estimate_items = EstimateItems::leftJoin('items', 'estimate_items.item_id', '=', 'items.id')
            ->where([["estimate_items.estimate_id", "=", $insert_id], ["estimate_items.company_id", "=", $company_id]])
            ->orderBy('estimate_items.id', 'ASC')
            ->get(["estimate_items.*", "items.image_icon as image_icon"]);

        $estimate_items_sp = EstimateItems::leftJoin('items', 'estimate_items.item_id', '=', 'items.id')
            ->where([["estimate_items.estimate_id", "=", $insert_id], ["estimate_items.company_id", "=", $company_id]]) //
            ->whereNotNull('estimate_items.technical_specification')
            ->orderBy('estimate_items.id', 'ASC')
            ->get(["estimate_items.*", "items.image_icon as image_icon"]);
        $proposal_template = ProposalTemplates::where('company_id', $company_id)->first();
        $company_data = ViewUserData::where("id", $company_id)->orderBy('id', 'ASC')->get()->first();


        $salesPersonInfo = User::where("id", $estimate->sales_person_id)->get()->first();
        $term_condition_data = TermCondition::where("id", $estimate->term_condition_id)->orderBy('id', 'ASC')->get()->first();

        $mpdf = MpdfService::createMpdfInstance();
        /*$mpdf = new Mpdf([
            'mode' => 'utf-8',
            'tempDir' => storage_path('tempdir')
        ]);*/
        // $mpdf->SetCompression(false);
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

        $logo = Storage::disk('s3')->url($proposal_template->header_logo);
        // $logo = Storage::disk('s3')->temporaryUrl($proposal_template->header_logo,Carbon::now()->addMinutes(20));
        // $logo = public_path(Storage::url($proposal_template->header_logo));
        $header = '<div style="text-align: right; font-weight: bold;border-bottom: 1px solid #fff;margin-right:' . $proposal_template->header_logo_left . 'px;padding-top:' . $proposal_template->header_logo_top . 'px;"><img src="' . $logo . '" width="' . $proposal_template->header_logo_size . '"/></div>';
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
            $call_url = '<td><a href="tel:' . $current_loggedin_user->call_url . '"><img src="' . public_path(Storage::url('call.png')) . '" width="34"></a></td>';
        }

        if ($current_loggedin_user->gmail_url) {
            $gmail_url = '<td><a href="mailto:' . $current_loggedin_user->gmail_url . '"><img src="' . public_path(Storage::url('email.png')) . '" width="34"></a></td>';
        }

        if ($current_loggedin_user->whatsapp_url) {
            $whatsapp_url = '<td><a href="https://wa.me/' . $current_loggedin_user->whatsapp_code_url . $current_loggedin_user->whatsapp_url . '"><img src="' . public_path(Storage::url('whatsapp.png')) . '" width="34"></a></td>';
        }

        $footer = '
        <table width="100%" style="vertical-align: middle; font-family: Arial, Helvetica, serif;
            font-size: 12pt; color: #fff; font-style: normal;background:' . $proposal_template->theme_footer_color . ';">
            <tr>
                <td width="47%"><a href="' . $company_data->website_link . '" target="_blank" style="text-decoration: none !important;color:#fff;">&nbsp;&nbsp;' . $company_data->company_name . '</a></td>
                <td width="6%" align="center">{PAGENO}/{nbpg}</td>
                <td width="47%" style="text-align: right;">
                    <table>
                        <tr>
                            ' . $facebook_url . $instagram_url . $linkedin_url . $twitter_url . $call_url . $gmail_url . $whatsapp_url . '
                        </tr>
                    </table>
                </td>
            </tr>
        </table>';

        if ($estimate['pdf_cover_page_flg']) {
            $mpdf->SetHTMLHeader();
            $mpdf->SetHTMLFooter();
            $mpdf->AddPage('P', '', '', '', '', 0, 0, 0, -1, 0, 0);

            //write content
            // $coverHtml1 = view('pdf.cover-page-new-web', compact('estimate', 'proposal_template', 'company_data'))->render();
            $coverHtml1 = view('pdf.cover-page-new-web', compact('estimate', 'proposal_template', 'company_data'))->render();
            $mpdf->WriteHTML($coverHtml1);
        }

        if ($estimate['pdf_about_us_flg']) {
            $mpdf->SetHTMLHeader($header);
            $mpdf->SetHTMLFooter($footer);
            $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);
            // $aboutHtml = view('pdf.about-page-new-web', compact('estimate', 'proposal_template', 'company_data'))->render();
            $aboutHtml = view('pdf.about-page-new-web', compact('estimate', 'proposal_template', 'company_data'))->render();
            $mpdf->WriteHTML($aboutHtml);
        }

        if ($estimate['pdf_product_flg'] && $proposal_template->photo_position_flg == 0) {
            $pro_title = ($proposal_template->product_title) ? html_entity_decode($proposal_template->product_title, ENT_QUOTES, 'UTF-8') : '';
            $pro_content = ($proposal_template->product_content) ? html_entity_decode($proposal_template->product_content, ENT_QUOTES, 'UTF-8') : '';
            //$products = Product::select(["name", "id", "image_one", "image_two", "image_three"])->where('status', '=', 0)->whereIn('id', explode(',', $estimate->product_id))->where('company_id', $company_id)->orderByRaw("FIELD(id,$estimate->product_id)")->get();
            $products = EstimatePhoto::where([["estimate_id", $insert_id], ["comapny_id", "=", $company_id]])->orderBy('id', 'asc')->get();
            $mpdf->SetHTMLHeader($header);
            $mpdf->SetHTMLFooter($footer);
            $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);

            // $productHtml = view('pdf.product-page-new-web', compact('products', 'proposal_template', 'company_data'))->render();
            $productHtml = view('pdf.product-page-new-web', compact('products', 'proposal_template', 'company_data'))->render();
            $mpdf->WriteHTML($productHtml);
        }

        //Fourth page
        if ($estimate['pdf_est_flg']) {
            $pdf_title_name = ($proposal_template->est_title) ? $proposal_template->est_title : 'Estimate';
            $customer_data = Customer::where("id", $estimate->customer_id)->select('gst_no', 'email', 'company_name', 'currency_name_country_id')->orderBy('id', 'DESC')->get()->first();
            $currency_id = $estimate->est_currency_id;
            if ($estimate->est_currency_id == 0) {
                $currency_id = $company_data->country_id;
            }
            $country_data = Country::where("id", $currency_id)->select('name', 'currency_name', 'currency_code', 'currency_symbol')->orderBy('id', 'DESC')->get()->first();
            $mpdf->SetHTMLHeader($header);
            $mpdf->SetHTMLFooter($footer);
            $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);

            // $estHtml = view('pdf.estimate-page-new-web', compact('estimate', 'estimate_items', 'proposal_template', 'company_data', 'salesPersonInfo','customer_data', 'country_data'))->render();
            $estHtml = view('pdf.estimate-page-new-web', compact('estimate', 'estimate_items', 'proposal_template', 'company_data', 'salesPersonInfo', 'customer_data', 'country_data'))->render();
            $mpdf->writeHTML($estHtml);
        }

        if ($estimate_items_sp->count() > 0) {
            $mpdf->SetHTMLHeader($header);
            $mpdf->SetHTMLFooter($footer);
            $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);

            // $estHtml = view('pdf.estimate-page-new-web', compact('estimate', 'estimate_items', 'proposal_template', 'company_data', 'salesPersonInfo','customer_data', 'country_data'))->render();
            // $estHtml = view('pdf.specification-page-new-web', compact('estimate_items_sp'))->render();


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
            foreach ($estimate_items_sp as $pk => $estimate_items) {
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
        </table><div class="centered-div">' . $estimate_items->technical_specification . '</div>';
            }
            $estHtml .= '</body>
</html>';

            $mpdf->writeHTML($estHtml);
        }

        if ($estimate['pdf_product_flg'] && $proposal_template->photo_position_flg == 1) {
            $pro_title = ($proposal_template->product_title) ? html_entity_decode($proposal_template->product_title, ENT_QUOTES, 'UTF-8') : '';
            $pro_content = ($proposal_template->product_content) ? html_entity_decode($proposal_template->product_content, ENT_QUOTES, 'UTF-8') : '';
            //$products = Product::select(["name", "id", "image_one", "image_two", "image_three"])->where('status', '=', 0)->whereIn('id', explode(',', $estimate->product_id))->where('company_id', $company_id)->orderByRaw("FIELD(id,$estimate->product_id)")->get();
            $products = EstimatePhoto::where([["estimate_id", $insert_id], ["comapny_id", "=", $company_id]])->orderBy('id', 'asc')->get();
            $mpdf->SetHTMLHeader($header);
            $mpdf->SetHTMLFooter($footer);
            $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);

            // $productHtml = view('pdf.product-page-new-web', compact('products', 'proposal_template', 'company_data'))->render();
            $productHtml = view('pdf.product-page-new-web', compact('products', 'proposal_template', 'company_data'))->render();
            $mpdf->WriteHTML($productHtml);
        }

        //Fifth page
        if ($estimate['pdf_terms_flg']) {
            $mpdf->SetHTMLHeader($header);
            $mpdf->SetHTMLFooter($footer);
            $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);

            // $termsHtml = view('pdf.term-and-condition-page-new-web', compact('estimate', 'proposal_template', 'term_condition_data'))->render();
            $termsHtml = view('pdf.term-and-condition-page-new', compact('estimate', 'proposal_template', 'term_condition_data'))->render();
            $mpdf->autoPageBreak = true;
            $mpdf->writeHTML($termsHtml);
        }

        //Sixth page
        if ($estimate['pdf_testimonial_flg']) {

            $testimonials = Testimonial::select(["name", "id", "client_name_one", "image_one", "rating_one", "description_one", "client_name_two", "image_two", "rating_two", "description_two", "client_name_three", "image_three", "rating_three", "description_three"])->where('status', '=', 0)->where('id', $estimate->testimonial_id)->where('company_id', $company_id)->get()->first();
            $mpdf->SetHTMLHeader($header);
            $mpdf->SetHTMLFooter($footer);
            $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);

            // $testiHtml = view('pdf.testimonial-page-new-web', compact('proposal_template', 'company_data', 'testimonials'))->render();
            $testiHtml = view('pdf.testimonial-page-new-web', compact('proposal_template', 'company_data', 'testimonials'))->render();
            $mpdf->writeHTML($testiHtml);
            //            $mpdf->writeHTML("મિત્રો દ્રશ્ય સંસ્કાર પ્રકાશ પંથ ત્રિકમ", true, false, true, false, '');
        }

        //Seven page
        if ($proposal_template->thank_you_flg) {
            $mpdf->SetHTMLHeader($header);
            $mpdf->SetHTMLFooter($footer);
            $mpdf->AddPage('P', '', '', '', '', 0, 0, $proposal_template->page_top_margin, 20, 0, 0);

        // $thanksHtml = view('pdf.thank-you-page-new-web', compact('company_data', 'proposal_template', 'salesPersonInfo'))->render();
            $thanksHtml = view('pdf.thank-you-page-new-web', compact('company_data', 'proposal_template', 'salesPersonInfo'))->render();
            $mpdf->writeHTML($thanksHtml);
        }


        // $mpdf->Output('test-pdf.pdf', Destination::DOWNLOAD);

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
        //        $paramArr['activity_estimate_status'] = $estimate['status'];
        $paramArr['activity_estimate_status'] = ($estimate['status'] == 'Draft' && $estimate['estimate_version'] == 0) ? 'Draft' : 'Inprogress'; // && $estimate['estimate_version'] == 0
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
        $mpdf->Output(public_path('storage/document/' . $company_id . '/' . $pdf_name . '.pdf'), 'F');


        $path = storage_path('app/public/document/' . $company_id . '/' . $pdf_name . '.pdf');
        $tmp_paths = Storage::disk('s3')->put('public/' . $company_id . '/documents/' . $pdf_name . '.pdf', file_get_contents($path), 'public');
//        Storage::disk('s3')->setVisibility($tmp_paths, 'public');
        Storage::disk('s3')->url('public/' . $company_id . '/documents/' . $pdf_name . '.pdf');
        unlink($path);
//        $pdf::Output(public_path('storage/document/' . $company_id . '/' . $pdf_name . '.pdf'), 'F');
        //PDF end
    }

    public function update(Request $request)
    {
        // try {
        if ($request->ajax()) {
            $input = $request->all();

            $pdf_cover_page_flg = 0;
            if ($request->has('pdf_cover_page_flg')) {
                $pdf_cover_page_flg = 1;
            }
            $pdf_about_us_flg = 0;
            if ($request->has('pdf_about_us_flg')) {
                $pdf_about_us_flg = 1;
            }
            $pdf_product_flg = 0;
            if ($request->has('pdf_product_flg')) {
                $pdf_product_flg = 1;
            }
            $pdf_est_flg = 0;
            if ($request->has('pdf_est_flg')) {
                $pdf_est_flg = 1;
            }
            $pdf_terms_flg = 0;
            if ($request->has('pdf_terms_flg')) {
                $pdf_terms_flg = 1;
            }

            $pdf_thank_you_flg = 0;
            if ($request->has('pdf_thank_you_flg')) {
                $pdf_thank_you_flg = 1;
            }

            $pdf_testimonial_flg = 0;
            if ($request->has('pdf_testimonial_flg')) {
                $pdf_testimonial_flg = 1;
            }

            /* echo "C".$pdf_cover_page_flg."<br>";
             echo "A".$pdf_about_us_flg."<br>";
             echo "P".$pdf_product_flg."<br>";
             echo "E".$pdf_est_flg."<br>";
             echo "TR".$pdf_terms_flg."<br>";
             echo "TH".$pdf_thank_you_flg."<br>";
             echo "TE".$pdf_testimonial_flg."<br>";
             die;*/

            $validator = Validator::make($input, [
                'id' => 'required',
                'customer_name' => 'required',
                'customer_id' => 'required',
                'customer_state_id' => 'required',
                //                'company_state_id' => 'required',
                'estimate_no' => 'required',
                'estimate_date' => 'required',
                'subtotal' => 'required',
                'net_amount' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);
            }

            $user = Auth::user();
            $company_id = ($user->company_id) ? $user->company_id : $user->id;
            //            $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
            $id = $input['id'];
            $tmp_est_id = $input['id'];
            $old_est = Estimate::where('id', '=', $id)->where([['id', '=', $id], ['company_id', "=", $company_id]])->select(['estimate_version'])->first();
            if (Estimate::where([['estimate_no', '=', $input['estimate_no']], ["estimate_version", "=", $old_est->estimate_version]])->where('company_id', $company_id)->where(function ($query) use ($id) {
                if ($id != 0) {
                    $query->Where(function ($query) use ($id) {
                        $query->where('id', '!=', $id);
                    });
                }
            })->first()) {
                return response()->json(['success' => 'Estimate exists!'], 409);
            }
            //            $oldEst = Estimate::where('id', '=', $id)->where('company_id', $company_id)->select(['user_id'])->first();
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
            $data['company_id'] = $company_id;
            $data['sales_person_id'] = $user->id;
            $data['user_id'] = $input['user_id'];
            $data['item_rate_are'] = $input['item_rate_are'];
            $data['customer_notes'] = $input['customer_notes'];
            $data['term_condition'] = $input['term_condition'];
            $data['est_currency_id'] = $input['est_currency_id'];
            $data['est_currency_name'] = $input['est_currency_name'];

            $data['est_cover_page_title'] = $input['est_cover_page_title'];
            $data['est_cover_page_title_div'] = $input['est_cover_page_title'];
            $data['est_cover_page_content'] = $input['est_cover_page_content'];

            $data['est_cover_page_content_div'] = $data['est_cover_page_content'];
            preg_match_all('#\${(.*?)\}#', strip_tags($data['est_cover_page_content_div']), $match);

            foreach ($match[1] as $key => $value) {
                $valueArr = explode('.', $value);
                $table = $valueArr[0];
                if ($valueArr[0] == 'customers') {
                    $tmpId = $data['customer_id'];
                    $table = 'customers_views';
                }

                if ($valueArr[0] == 'companies') {
                    $table = 'users_views';
                    $tmpId = $company_id;
                }

                if ($valueArr[0] == 'estimates') {
                    $tmpId = $data['estimate_id'];
                }
                $result = DB::table($table)
                    ->where('id', $tmpId)
                    ->select([$valueArr[1]])
                    ->get()->first();
                $a = $valueArr[1];

                $data['est_cover_page_content_div'] = str_replace('${' . $value . '}', $result->$a, $data['est_cover_page_content_div']);
            }
            $data['est_cover_page_footer_one'] = $input['est_cover_page_footer_one'];
            $data['est_cover_page_footer_two'] = $input['est_cover_page_footer_two'];
            $data['est_aboutus_title'] = $input['est_aboutus_title'];
            $data['est_aboutus_content'] = $input['est_aboutus_content'];
            $data['est_aboutus_content_div'] = $input['est_aboutus_content'];
            preg_match_all('#\${(.*?)\}#', strip_tags($data['est_aboutus_content_div']), $matchAbs);
            foreach ($matchAbs[1] as $key => $value) {
                $valueArr = explode('.', $value);
                //                if ($valueArr[0] == 'customers') {
                //                    $id = $data['customer_id'];
                //                    $table = 'customers_views';
                //                }

                if ($valueArr[0] == 'companies') {
                    $table = 'users_views';
                    $id = $company_id;
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

            $data['est_term_condition_title'] = $input['est_term_condition_title'];
            $data['est_term_condition_content'] = $input['est_term_condition_content'];

            $data['est_cover_page_title_div'] = $input['est_cover_page_title_div'];
            $data['est_cover_page_footer_one_div'] = $input['est_cover_page_footer_one'];
            $estimate_tmp = Estimate::where([["estimate_no", $input['estimate_no']], ["company_id", "=", $company_id]])->orderby("id", "desc")->first();
            $new_estimate_version = $estimate_tmp->estimate_version + 1;
            preg_match_all('#\${(.*?)\}#', strip_tags($data['est_cover_page_footer_one_div']), $match);
            foreach ($match[1] as $key => $value) {
                $valueArr = explode('.', $value);
                $table = $valueArr[0];
                if ($valueArr[0] == 'companies') {
                    $tmpId = $company_id;
                    $table = 'users_views';
                }

                $result = DB::table($table)
                    ->where('id', $tmpId)
                    ->select([$valueArr[1]])
                    ->get()->first();
                $a = $valueArr[1];
                $data['est_cover_page_footer_one_div'] = str_replace('${' . $value . '}', $result->$a, $data['est_cover_page_footer_one_div']);
            }
            $data['est_cover_page_footer_two_div'] = $input['est_cover_page_footer_two'];
            preg_match_all('#\${(.*?)\}#', strip_tags($data['est_cover_page_footer_two_div']), $match);
            foreach ($match[1] as $key => $value) {
                $valueArr = explode('.', $value);
                $table = $valueArr[0];
                if ($valueArr[0] == 'estimates') {
                    $tmpId = $input['id'];
                }

                if ($valueArr[1] == 'sales_person_id') {
                    $table = 'users_views';
                    $tmpId = $user->id;
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
                $estVar = '';
                if ($valueArr[1] == 'estimate_no') {
                    $est_result = DB::table('estimates')
                        ->where('id', $tmpId)
                        ->select('estimate_version')->first();
                    if ($new_estimate_version > 0) {
                        $estVar = '-V' . $new_estimate_version;
                    }
                }
                $ax = $result->$a;
                if ($valueArr[1] == 'estimate_date') {
                    $ax = Carbon::createFromFormat('d/m/Y', $input['estimate_date'])->format('d M, Y');
                }

                $data['est_cover_page_footer_two_div'] = str_replace('${' . $value . '}', $ax . $estVar, $data['est_cover_page_footer_two_div']);
            }

            $data['est_aboutus_title_div'] = $input['est_aboutus_title_div'];
            //            $data['est_aboutus_content_div'] = $input['est_aboutus_content_div'];
            $data['est_term_condition_title_div'] = $input['est_term_condition_title_div'];
            $data['est_term_condition_content_div'] = $input['est_term_condition_content_div'];
            $data['testimonial_id'] = 0+$input['testimonial_id'];

            // $data['product_id'] = $input['product_id'] ? trim(implode(',', $input['product_id']), ',') : '';
            $data['product_id'] = (isset($input['product_id']))?trim(implode(',', $input['product_id']), ','):'';
            $data['pdf_cover_page_flg'] = $pdf_cover_page_flg;
            $data['pdf_about_us_flg'] = $pdf_about_us_flg;
            $data['pdf_product_flg'] = $pdf_product_flg;
            // $data['pdf_est_flg'] = $pdf_est_flg;
            $data['pdf_est_flg'] = 1;
            $data['pdf_terms_flg'] = $pdf_terms_flg;
            $data['pdf_thank_you_flg'] = $pdf_thank_you_flg;
            $data['pdf_testimonial_flg'] = $pdf_testimonial_flg;
            $data['tilt'] = $input['tilt'];
            $data['azumuth'] = $input['azumuth'];
            $data['no_of_panel'] = $input['no_of_panel'];
            $data['panel_wattage'] = $input['panel_wattage'];
            $data['term_condition_id'] = 0+$input['term_condition_id'];

            $data['estimate_version'] = $new_estimate_version;

            $activityLogMsg = 'Estimate updated by ' . $user->name;
            // $estimate = Estimate::find($input['id'])->update($data);
            $estimate = Estimate::create($data);
            $input['id'] = $estimate->id;

            $input['log_id'] = $input['id'];
            $input['log_type'] = 'estimate';
            LogActivity::addToLog($activityLogMsg, $input, 1);

            LogActivity::addToLog($activityLogMsg, $input, 1);

            /*$logInput['estimate_id'] = $input['id'];
            $logInput['assigned_to'] = $input['user_id'];
            $logInput['customer_id'] = $input['customer_id'];
            $logInput['internal_remarks'] = "Estimate edited";
            $logInput['entry_type'] = "estimates";
            $logInput['user_id'] = $user->id;
            $logInput['company_id'] = $company_id;
            $logInput1['created_by'] = $user->id;
            $logInput['updated_by'] = $user->id;
            LogActivity::addToActivityLog($logInput);

            if ($oldEst->user_id != $input['user_id']) {
                $bindUser = DB::table('users')
                    ->where('id', $input['user_id'])
                    ->select('name')
                    ->get()->first();
                $logInput1['estimate_id'] = $input['id'];
                $logInput1['assigned_to'] = $input['user_id'];
                $logInput1['customer_id'] = $input['customer_id'];
                $logInput1['internal_remarks'] = "Assigned to " . $bindUser->name;
                $logInput1['entry_type'] = "estimates";
                $logInput1['user_id'] = $user->id;
                $logInput1['company_id'] = $company_id;
                $logInput1['created_by'] = $user->id;
                $logInput1['updated_by'] = $user->id;
                LogActivity::addToActivityLog($logInput1);
            }*/
            if ($estimate) {
                EstimateItems::where("estimate_id", $input['id'])->delete();
                $estimate_coll = collect($input['data']);
                $estimateArr = $estimate_coll->values()->toArray();

                foreach ($estimateArr as $key => $csm) {
                    $estimateArr[$key]['estimate_id'] = $input['id'];
                    $estimateArr[$key]['company_id'] = $company_id;
                    $estimateArr[$key]['user_id'] = $user->id;
                    $estimateArr[$key]['technical_specification'] = $csm['item_technical_specification'];
                    unset($estimateArr[$key]['item_technical_specification']);
                }
                EstimateItems::insert($estimateArr);
                $insert_id = $input['id'];
                if($pdf_product_flg==1) {
                    foreach ($input['image_data'] as $img_value) {
                        $input['image_one'] = '';
                        $input['image_two'] = '';
                        $input['image_three'] = '';
                        $input['thumb_image_one'] = '';
                        $input['thumb_image_two'] = '';
                        $input['thumb_image_three'] = '';
                        if ($img_value['h_image_one'] == null) {


                            if ($img_value['estimate_product_id'] > 0) {
                                $productData = EstimatePhoto::find($img_value['estimate_product_id']);
                                $sourcePath1 = $productData->image_one;
                                $destinationPath1 = str_replace('public/' . $company_id . '/estimate/' . $tmp_est_id . '/', 'public/' . $company_id . '/estimate/' . $insert_id . '/', $sourcePath1);

                                $sourcePath1t = $productData->thumb_image_one;
                                $destinationPath1t = str_replace('public/' . $company_id . '/estimate/' . $tmp_est_id . '/', 'public/' . $company_id . '/estimate/' . $insert_id . '/', $sourcePath1t);
                            } else {
                                $productData = Product::find($img_value['tmp_product_id']);
                                if ($productData->image_one) {
                                    $sourcePath1 = $productData->image_one;
                                    $destinationPath1 = str_replace('public/' . $company_id . '/products/', 'public/' . $company_id . '/estimate/' . $insert_id . '/', $sourcePath1);

                                    $sourcePath1t = $productData->thumb_image_one;
                                    $destinationPath1t = str_replace('public/' . $company_id . '/products/', 'public/' . $company_id . '/estimate/' . $insert_id . '/', $sourcePath1t);
                                } else {
                                    $sourcePath1 = 'template/64x64.png';
                                    $destinationPath1 = 'public/' . $company_id . '/estimate/' . $insert_id . '/thumbnail/' . bin2hex(random_bytes(8)) . '.png';

                                    $sourcePath1t = 'template/64x64.png';
                                    $destinationPath1t = 'public/' . $company_id . '/estimate/' . $insert_id . '/resize_image/' . bin2hex(random_bytes(8)) . '.png';
                                }
                            }

                            $temp_imp_1 = Storage::disk('s3')->get($sourcePath1);
                            Storage::disk('s3')->put($destinationPath1, $temp_imp_1, 'public');
                            $temp_imp_2 = Storage::disk('s3')->get($sourcePath1t);
                            Storage::disk('s3')->put($destinationPath1t, $temp_imp_2, 'public');
                            /*Storage::disk('s3')->copy($sourcePath1, $destinationPath1);
                            Storage::disk('s3')->copy($sourcePath1t, $destinationPath1t);*/

                            $input['image_one'] = $destinationPath1;
                            $input['thumb_image_one'] = $destinationPath1t;
                        }

                        if ($img_value['h_image_one']) {
                            $ext = explode(";", explode("/", $img_value['h_image_one'])[1])[0];
                            $imageName = uniqid() . '-1' . '.' . $ext;
                            // Original image upload to 'estimate' folder in local storage
                            $imageData = $img_value['h_image_one'];
                            // Decode the base64 image data
                            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));


                            // Upload the image to S3
                            Storage::disk('s3')->put('public/' . $company_id . '/estimate/' . $insert_id . '/thumbnail/' . $imageName, $imageData, 'public');

                            // Get the S3 URL of the uploaded image
                            $s3Url = Storage::disk('s3')->url('public/' . $company_id . '/estimate/' . $insert_id . '/thumbnail/' . $imageName);
                            $input['image_one'] = 'public/' . $company_id . '/estimate/' . $insert_id . '/thumbnail/' . $imageName;


                            /*
                                                    // Upload the original image to 'estimate' folder in local storage
                                                    Storage::put('public/estimate/'.$imageName, $imageData);

                                                    // Assign the path of the original image to the input array
                                                    $input['image_one'] = 'public/estimate/'.$imageName;*/
                            $imageNames = Str::uuid() . '-1.' . $ext;
                            // Use intervention/image package to create an image instance and save it to the desired path
                            $image = Image::make($imageData);

                            // Resize the image to the desired dimensions (e.g., width: 64px, height: 64px)
                            $image->resize(64, 64);


                            // Upload the resized image to S3
                            Storage::disk('s3')->put('public/' . $company_id . '/estimate/' . $insert_id . '/resize_image/' . $imageNames, $image->encode($ext), 'public');

                            // Get the S3 URL of the uploaded image
                            $s3Url = Storage::disk('s3')->url('public/' . $company_id . '/estimate/' . $insert_id . '/resize_image/' . $imageNames);

                            $input['thumb_image_one'] = 'public/' . $company_id . '/estimate/' . $insert_id . '/resize_image/' . $imageNames;


                            /* // Generate a unique name for the thumbnail image
                             $thumbImageName = Str::uuid() . '-1.' . $ext;

                             // Save the resized thumbnail image to 'estimate' folder in local storage
                             $image->save(storage_path('app/public/estimate/resize_image/'.$thumbImageName));

                             // Assign the path of the thumbnail image to the input array
                             $input['thumb_image_one'] = 'public/estimate/resize_image/'.$thumbImageName;*/
                        }

                        if ($img_value['h_image_two'] == null) {

                            if ($img_value['estimate_product_id'] > 0) {
                                $productData = EstimatePhoto::find($img_value['estimate_product_id']);
                                $sourcePath2 = $productData->image_two;
                                $destinationPath2 = str_replace('public/' . $company_id . '/estimate/' . $tmp_est_id . '/', 'public/' . $company_id . '/estimate/' . $insert_id . '/', $sourcePath2);

                                $sourcePath2t = $productData->thumb_image_two;
                                $destinationPath2t = str_replace('public/' . $company_id . '/estimate/' . $tmp_est_id . '/', 'public/' . $company_id . '/estimate/' . $insert_id . '/', $sourcePath2t);
                            } else {
                                $productData = Product::find($img_value['tmp_product_id']);
                                if ($productData->image_two) {
                                    $sourcePath2 = $productData->image_two;
                                    $destinationPath2 = str_replace('public/' . $company_id . '/products/', 'public/' . $company_id . '/estimate/' . $insert_id . '/', $sourcePath2);

                                    $sourcePath2t = $productData->thumb_image_two;
                                    $destinationPath2t = str_replace('public/' . $company_id . '/products/', 'public/' . $company_id . '/estimate/' . $insert_id . '/', $sourcePath2t);
                                } else {
                                    $sourcePath2 = 'template/64x64.png';
                                    $destinationPath2 = 'public/' . $company_id . '/estimate/' . $insert_id . '/thumbnail/' . bin2hex(random_bytes(8)) . '.png';

                                    $sourcePath2t = 'template/64x64.png';
                                    $destinationPath2t = 'public/' . $company_id . '/estimate/' . $insert_id . '/resize_image/' . bin2hex(random_bytes(8)) . '.png';
                                }
                            }

                            $temp_imp_1 = Storage::disk('s3')->get($sourcePath2);
                            Storage::disk('s3')->put($destinationPath2, $temp_imp_1, 'public');
                            $temp_imp_2 = Storage::disk('s3')->get($sourcePath2t);
                            Storage::disk('s3')->put($destinationPath2t, $temp_imp_2, 'public');
                            /*Storage::disk('s3')->copy($sourcePath2, $destinationPath2);
                            Storage::disk('s3')->copy($sourcePath2t, $destinationPath2t);*/

                            $input['image_two'] = $destinationPath2;
                            $input['thumb_image_two'] = $destinationPath2t;
                        }

                        if ($img_value['h_image_two']) {
                            $ext = explode(";", explode("/", $img_value['h_image_two'])[1])[0];
                            $imageName = uniqid() . '-2' . '.' . $ext;
                            // Original image upload to 'estimate' folder in local storage
                            $imageData = $img_value['h_image_two'];
                            // Decode the base64 image data
                            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

                            // Upload the image to S3
                            Storage::disk('s3')->put('public/' . $company_id . '/estimate/' . $insert_id . '/thumbnail/' . $imageName, $imageData, 'public');

                            // Get the S3 URL of the uploaded image
                            $s3Url = Storage::disk('s3')->url('public/' . $company_id . '/estimate/' . $insert_id . '/thumbnail/' . $imageName);
                            $input['image_two'] = 'public/' . $company_id . '/estimate/' . $insert_id . '/thumbnail/' . $imageName;


                            /*// Upload the original image to 'estimate' folder in local storage
                            Storage::put('public/estimate/'.$imageName, $imageData);

                            // Assign the path of the original image to the input array
                            $input['image_two'] = 'public/estimate/'.$imageName;*/
                            $imageNames = Str::uuid() . '-2.' . $ext;
                            // Use intervention/image package to create an image instance and save it to the desired path
                            $image = Image::make($imageData);

                            // Resize the image to the desired dimensions (e.g., width: 64px, height: 64px)
                            $image->resize(64, 64);

                            // Upload the resized image to S3
                            Storage::disk('s3')->put('public/' . $company_id . '/estimate/' . $insert_id . '/resize_image/' . $imageNames, $image->encode($ext), 'public');

                            // Get the S3 URL of the uploaded image
                            $s3Url = Storage::disk('s3')->url('public/' . $company_id . '/estimate/' . $insert_id . '/resize_image/' . $imageNames);

                            $input['thumb_image_two'] = 'public/' . $company_id . '/estimate/' . $insert_id . '/resize_image/' . $imageNames;

                            /*// Generate a unique name for the thumbnail image
                            $thumbImageName = Str::uuid() . '-2.' . $ext;

                            // Save the resized thumbnail image to 'estimate' folder in local storage
                            $image->save(storage_path('app/public/estimate/resize_image/'.$thumbImageName));

                            // Assign the path of the thumbnail image to the input array
                            $input['thumb_image_two'] = 'public/estimate/resize_image/'.$thumbImageName;*/
                        }

                        if ($img_value['h_image_three'] == null) {
                            if ($img_value['estimate_product_id'] > 0) {
                                $productData = EstimatePhoto::find($img_value['estimate_product_id']);
                                $sourcePath3 = $productData->image_three;
                                $destinationPath3 = str_replace('public/' . $company_id . '/estimate/' . $tmp_est_id . '/', 'public/' . $company_id . '/estimate/' . $insert_id . '/', $sourcePath3);

                                $sourcePath3t = $productData->thumb_image_three;
                                $destinationPath3t = str_replace('public/' . $company_id . '/estimate/' . $tmp_est_id . '/', 'public/' . $company_id . '/estimate/' . $insert_id . '/', $sourcePath3t);
                            } else {
                                $productData = Product::find($img_value['tmp_product_id']);
                                if ($productData->image_two) {
                                    $sourcePath3 = $productData->image_three;
                                    $destinationPath3 = str_replace('public/' . $company_id . '/products/', 'public/' . $company_id . '/estimate/' . $insert_id . '/', $sourcePath3);

                                    $sourcePath3t = $productData->thumb_image_three;
                                    $destinationPath3t = str_replace('public/' . $company_id . '/products/', 'public/' . $company_id . '/estimate/' . $insert_id . '/', $sourcePath3t);
                                } else {
                                    $sourcePath3 = 'template/64x64.png';
                                    $destinationPath3 = 'public/' . $company_id . '/estimate/' . $insert_id . '/thumbnail/' . bin2hex(random_bytes(8)) . '.png';

                                    $sourcePath3t = 'template/64x64.png';
                                    $destinationPath3t = 'public/' . $company_id . '/estimate/' . $insert_id . '/resize_image/' . bin2hex(random_bytes(8)) . '.png';
                                }
                            }

                            $temp_imp_1 = Storage::disk('s3')->get($sourcePath3);
                            Storage::disk('s3')->put($destinationPath3, $temp_imp_1, 'public');
                            $temp_imp_2 = Storage::disk('s3')->get($sourcePath3t);
                            Storage::disk('s3')->put($destinationPath3t, $temp_imp_2, 'public');
                            /*Storage::disk('s3')->copy($sourcePath3, $destinationPath3);
                            Storage::disk('s3')->copy($sourcePath3t, $destinationPath3t);*/

                            $input['image_three'] = $destinationPath3;
                            $input['thumb_image_three'] = $destinationPath3t;

                        }

                        if ($img_value['h_image_three']) {
                            $ext = explode(";", explode("/", $img_value['h_image_three'])[1])[0];
                            $imageName = uniqid() . '-3' . '.' . $ext;
                            // Original image upload to 'estimate' folder in local storage
                            $imageData = $img_value['h_image_three'];
                            // Decode the base64 image data
                            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

                            // Upload the image to S3
                            Storage::disk('s3')->put('public/' . $company_id . '/estimate/' . $insert_id . '/thumbnail/' . $imageName, $imageData, 'public');

                            // Get the S3 URL of the uploaded image
                            $s3Url = Storage::disk('s3')->url('public/' . $company_id . '/' . $insert_id . '/thumbnail/' . $imageName);
                            $input['image_three'] = 'public/' . $company_id . '/estimate/' . $insert_id . '/thumbnail/' . $imageName;

                            /*// Upload the original image to 'estimate' folder in local storage
                            Storage::put('public/estimate/'.$imageName, $imageData);

                            // Assign the path of the original image to the input array
                            $input['image_three'] = 'public/estimate/'.$imageName;*/
                            $imageNames = Str::uuid() . '-3.' . $ext;
                            // Use intervention/image package to create an image instance and save it to the desired path
                            $image = Image::make($imageData);

                            // Resize the image to the desired dimensions (e.g., width: 64px, height: 64px)
                            $image->resize(64, 64);

                            // Upload the resized image to S3
                            Storage::disk('s3')->put('public/' . $company_id . '/estimate/' . $insert_id . '/resize_image/' . $imageNames, $image->encode($ext), 'public');

                            // Get the S3 URL of the uploaded image
                            $s3Url = Storage::disk('s3')->url('public/' . $company_id . '/estimate/' . $insert_id . '/resize_image/' . $imageNames);

                            $input['thumb_image_three'] = 'public/' . $company_id . '/estimate/' . $insert_id . '/resize_image/' . $imageNames;

                            /*// Generate a unique name for the thumbnail image
                            $thumbImageName = Str::uuid() . '-3.' . $ext;

                            // Save the resized thumbnail image to 'estimate' folder in local storage
                            $image->save(storage_path('app/public/estimate/resize_image/'.$thumbImageName));

                            // Assign the path of the thumbnail image to the input array
                            $input['thumb_image_three'] = 'public/estimate/resize_image/'.$thumbImageName;*/
                        }
                        $estimatePhotos = EstimatePhoto::create([
                            "estimate_id" => $insert_id,
                            "product_flag" => 1,
                            "product_id" => $img_value['tmp_product_id'],
                            "image_one" => $input['image_one'],
                            "image_two" => $input['image_two'],
                            "image_three" => $input['image_three'],
                            "thumb_image_one" => $input['thumb_image_one'],
                            "thumb_image_two" => $input['thumb_image_two'],
                            "thumb_image_three" => $input['thumb_image_three'],
                            "user_id" => $user->id,
                            "comapny_id" => $company_id
                        ]);
                    }
                }

            }

            $estimate_auto_number = EstimateAutoNumber::where('company_id', $company_id)
                ->select('estimate_prefix', 'estimate_next_no')
                ->get()->first();

            $tmp_est_no = $estimate_auto_number->estimate_prefix . $estimate_auto_number->estimate_next_no;

            $explodeEst = explode("-", $input['estimate_no']);

            if (is_array($explodeEst)) {
                $lastElement = end($explodeEst);
                $cleanedStr = 0 + preg_replace("/[^0-9]/", "", $lastElement);
            } else {
                $cleanedStr = 0 + preg_replace("/[^0-9]/", "", $input['estimate_no']);
            }


            if ($cleanedStr >= 0 + ($estimate_auto_number->estimate_next_no)) {
                $tmp_est_no = $cleanedStr + 1;
                EstimateAutoNumber::where('company_id', $company_id)->update(array('estimate_next_no' => '00' . $tmp_est_no));
            }

            /*if ($tmp_est_no == $input['estimate_no']) {
                $tmp_est_no = $estimate_auto_number->estimate_next_no + 1;

                EstimateAutoNumber::where('company_id', $company_id)->update(array('estimate_next_no' => '00' . $tmp_est_no));
            }*/
            $tempEst = Estimate::where('estimate_no', '=', $input['estimate_no'])->where('company_id', $company_id)->where(function ($query) use ($id) {
                /*if ($id != 0) {
                    $query->Where(function ($query) use ($id) {
                        $query->where('id', '!=', $id);
                    });
                }*/
            })->select("id", "estimate_no")->get();
            if ($tempEst) {
                foreach ($tempEst as $val_est) {
                    Estimate::where([['company_id', '=', $company_id], ["id", "=", $val_est->id]])->update(array('status' => 'Inprogress'));
                    EstimateTimeline::where('estimate_id', $val_est->id)->update(array('activity_estimate_status' => 'Inprogress'));
                }
            }
            $proposal_template_temp = ProposalTemplates::where('company_id', $company_id)->select('new_pdf_flag')->first();
            if ($proposal_template_temp->new_pdf_flag == 0)
                $this->estimateGeneratePdf($input['id'], $company_id);
            if ($proposal_template_temp->new_pdf_flag == 1)
                $this->estimateGeneratemPdf($input['id'], $company_id);
            return response()->json(['success' => 'Estimate Saved!', 'url' => url('quotes/edit/' . Crypt::encrypt($input['id']) . '?m=1')], 201);
        }
        /*} catch (\Exception $e) {
            // Log the exception
            \Log::error('Error in store method: ' . $e->getMessage());

            // Return a generic error response
            return response()->json(['error' => 'An error occurred. Please try again.'], 500);
        }*/
    }

    public function destroy(Request $request)
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
            $company_id = ($user->company_id) ? $user->company_id : $user->id;
            $id = [];
            foreach (explode(",", $request->id) as $value) {
                $id[] = Crypt::decrypt($value);
            }

            $estimate = Estimate::where('id', $id[0])->get()->first();

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
            $paramArr['user_id'] = $user->id;
            $paramArr['company_id'] = $company_id;
            $paramArr['created_by'] = $user->id;
            $paramArr['updated_by'] = $user->id;
            $paramArr['net_amount'] = $estimate['net_amount'];
            $paramArr['follow_up_datetime'] = $customer_view_data->last_follow_up_datetime;
            EstimateTimeline::create($paramArr);

            $tenantdata = DB::connection('mysql')->table('tenants')->where('email', $user->email)->first();
            $tcompany_id = ($tenantdata->company_id) ? $tenantdata->company_id : $tenantdata->id;
            $leadhistory = EstimateHistory::where('estimate_id', $id[0])->where('company_id', $tcompany_id)->delete();

            LogActivity::addToLog('Estimate deleted by ' . $user->name, $id);
            return response()->json(['success' => 'Estimate Deleted!'], 201);
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
            if (!Estimate::whereIn('id', $id)->first()) {
                return response()->json(['success' => 'Estimate exists!'], 422);
            }
            $unit = Estimate::whereIn('id', $id)->update(["status" => $input['status']]);

            $data['id'] = $id;
            $data['status'] = $input['status'];
            $data['log_id'] = $id[0];
            $data['log_type'] = 'estimate';

            LogActivity::addToLog('Estimate status updated by ' . $user->name, $data, 1);

            return response()->json(['success' => 'Estimate status updated!'], 201);
        }
    }

    public function getEstimateNumber(Request $request)
    {
        $user = Auth::user();

        $company_id = ($user->company_id) ? $user->company_id : $user->id;
        if ($request->ajax()) {


            $records = DB::table('estimate_auto_numbers')->where('company_id', $company_id)
                ->select('estimate_prefix', 'estimate_next_no')
                ->get();

            return json_encode($records);
        }
    }

    public function updateEstimateNumber(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $validator = Validator::make($input, [
                'estimate_next_no' => 'required|numeric'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);;
            }

            $user = Auth::user();

            $input['company_id'] = ($user->company_id) ? $user->company_id : $user->id;

            if (Estimate::where('estimate_no', '=', $input['estimate_prefix'] . $input['estimate_next_no'])->where('company_id', '=', $input['company_id'])->select('id')->first()) {
                return response()->json(['success' => 'Estimate ' . $input['estimate_next_no'] . ' already exists!'], 409);
            }

            $estimate_auto_number = EstimateAutoNumber::where('company_id', $input['company_id'])->update($input);
            $activityLogMsg = 'Estimate number updated by ' . $user->name;

            // Add activity logs
            $input['user_id'] = $user->id;
            LogActivity::addToLog($activityLogMsg, $input);

            return response()->json(['success' => 'Estimate number Saved!', 'data' => $input], 201);
        }
    }

    public function getRecentActivities(Request $request)
    {
        $log_id = $request->id;
        //        $log_id = Crypt::decrypt($request->id);
        return $recent_activity = LogActivity::logActivityLists($log_id, array('event-follow-up', 'estimate'));
    }

    public function estimatePdfInfo(Request $request)
    {
        $input = $request->all();
        //        echo "<pre>";
        //        print_r($input);
        $user = Auth::user();
        $id = ($user->company_id) ? $user->company_id : $user->id;
        $data = array();

        foreach ($input['matches'] as $key => $value) {

            $valueArr = explode('.', $value);
            //            DB::enableQueryLog();
            $table = $valueArr[0];
            if ($valueArr[0] == 'customers') {
                $id = $input['customer_id'];
                $table = 'customers_views';
            }

            if ($valueArr[0] == 'companies') {
                $table = 'users_views';
            }

            if ($valueArr[0] == 'estimates') {
                $id = $input['estimate_id'];
            }
            $result = DB::table($table)
                ->where('id', $id)
                ->get([$valueArr[1]])->first();

            $a = $valueArr[1];
            //            dd(DB::getQueryLog());
            $field_name = '';
            if (!empty($result->$a))
                $field_name = ($valueArr[1]=='estimate_date')?Carbon::createFromFormat('Y-m-d', $result->$a)->format('d-m-Y'):$result->$a;



            $data[$value] = $field_name;
        }
        return json_encode($data);
    }

    public function estimateDuplicate(Request $request)
    {
        if ($request->ajax()) {
            $status = 0;
            $estimates = Estimate::where("company_id", $company_id)->latest()->take(10)->get();

            foreach ($estimates as $key => $value) {
                if ($value->id == $id) {
                    $status = 1;
                    break;
                } else {
                    $status = 0;
                }
            }
            // if ($status == 0) {
            //     $plan = PlanHistory::where([['user_id', $company_id], ['status', 0]])->latest()->first();
            //     if (isset($plan->start_date) && isset($plan->end_date)) {
            //         $dateS = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $plan->start_date);
            //         $dateE = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $plan->end_date);

            //         $newEstimates = Estimate::where("company_id", $company_id)->whereBetween('created_at', [$dateS, $dateE])->orderBy('created_at', 'DESC')->get();

            //         foreach ($newEstimates as $key => $value) {

            //             if ($value->id == $id) {
            //                 $status = 1;
            //             }
            //             if ($key == 9) {
            //                 break;
            //             }
            //         }
            //     } else {
            //         $plan = 1;
            //     }
            // }
            if ($status == 0) {
                return response()->json(['error' => 'You Are Not Editable to this record...'], 201);
            }
            $input = $request->all();
            $user = Auth::user();

            $company_id = ($user->company_id) ? $user->company_id : $user->id;
            $estimate_auto_number = DB::table('estimate_auto_numbers')->where('company_id', $company_id)
                ->select('estimate_prefix', 'estimate_next_no')
                ->first();
            $estimate_no = $estimate_auto_number->estimate_prefix . $estimate_auto_number->estimate_next_no;

            $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
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
            EstimateAutoNumber::where('company_id', $company_id)->update(array('estimate_next_no' => '00' . $tmp_est_no));
            return response()->json(['success' => 'Estimate Copied!', 'estimate_id' => $insert_id], 201);
        }
    }

    public function generateLink($id)
    {
        $data['id'] = $id;
        $id = ($id) ? Crypt::decrypt($id) : $id;
        $estimate = Estimate::query()->where("id", $id)->first();
        $data['company_id'] = $estimate->company_id;
        $data['status'] = $estimate->status;
        $data['estimate_no'] = $estimate->estimate_no;
        $data['estimate_version'] = $estimate->estimate_version;
        $data['customer_name'] = $estimate->customer_name;
        $data['net_amount'] = $estimate->net_amount + $estimate->addless_amount;
        //        $data['pdf_path'] = Storage::url('storage/document/' . $data['estimate_no'] . '.pdf');
        return view('estimate.customer-preview', compact('data'));
    }

    public function activityChangeEstimateStatusSaves(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $validator = Validator::make($input, [
//                'activity_type' => 'required',
//                'activity_name' => 'required',
//                'follow_up_datetime_status' => 'required',
                'activity_customer_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);
            }

            $paramArr['estimate_id'] = ($input['activity_estimate_id']) ? Crypt::decrypt($input['activity_estimate_id']) : $input['activity_estimate_id'];
            $estimateTimelineData = EstimateTimeline::where('customer_id', $input['activity_customer_id'])->select('follow_up_datetime')->orderBy('id', 'desc')->first();
            $getEstimateTimelineData = EstimateTimeline::where('customer_id', $input['activity_customer_id'])->where('activity_type', 5)->where('estimate_id', $paramArr['estimate_id'])->select('id')->orderBy('id', 'desc')->first();

            $input['user_id'] = $this->logged_user->id;
            $input['company_id'] = $this->company_id;
            $paramArr['id'] = $getEstimateTimelineData->id;

            $old_activity_status = $input['old_activity_status'];
            $old_activity_follow_up_date = $estimateTimelineData->follow_up_datetime;
            $paramArr['customer_id'] = $input['activity_customer_id'];
            $paramArr['activity_notes'] = $input['activity_estimate_notes'];
            $paramArr['estimate_version_no'] = $input['activity_estimate_no'];
            $customerData = Customer::select("assigned_to_user")->where('id', '=', $paramArr['customer_id'])->first();
            $paramArr['assigned_to'] = $customerData->assigned_to_user;
            $paramArr['activity_type'] = 10;
            $paramArr['activity_name'] = 'Status Updated';
            $paramArr['activity_notes'] = $input['activity_estimate_notes'];
            $paramArr['activity_estimate_status'] = $input['activity_estimate_status'];
            $paramArr['follow_up_datetime'] = $estimateTimelineData->follow_up_datetime;
            $follow_up_datetime = (!empty($estimateTimelineData->follow_up_datetime) && $estimateTimelineData->follow_up_datetime != '0000-00-00 00:00:00') ? Carbon::createFromFormat('Y-m-d H:i:s', $estimateTimelineData->follow_up_datetime)->format('d-m-Y H:i A') : '';

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
}
