<?php

namespace App\Http\Controllers\App;

use App\Models\PaymentHistory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\admin\CompanyCategory;
use App\Models\admin\Plans;
use App\Models\City;
use App\Models\Country;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\LogActivity;
use App\Models\PlanHistory;
use App\Models\State;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Kishanio\CCAvenue\Payment as CCAvenueClient;
use Softon\Indipay\Facades\Indipay;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use App\Http\Requests\CardVerificationRequest;
use LVR\CreditCard\CardCvc;
use LVR\CreditCard\CardNumber;
use LVR\CreditCard\CardExpirationYear;
use LVR\CreditCard\CardExpirationMonth;

class PaymentHistoryController extends Controller
{
    protected $segment = null;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->segment = $request->segment(1);
            return $next($request);
        });
    }

    public function paymentCheckoutPage(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        /*$company_id = ($user->company_id) ? $user->company_id : $user->id;
        $companyExp = \Illuminate\Support\Facades\DB::table('users')->where("id", $company_id)->select(["id", "plan_end_date"])->first();
        return view('expired-plan', compact('companyExp'));*/

        $data['data'] = $request->all();
        $data['countries'] = Country::select(["name", "id"])->where('status', '=', 0)->get();
        $data['states'] = State::select(["name", "id"])->where([['status', '=', 0], ['country_id', 101]])->get();
        $data['cities'] = City::select(["name", "id"])->where([['status', '=', 0], ['state_id', 12]])->get();
        $data['company_categories'] = CompanyCategory::select(["name", "id"])->where('status', '=', 0)->get();
        $data['segment'] = $this->segment;
        return view('app.plan.checkout')->with($data);
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
            // Total records
            $totalRecords = PaymentHistory::count();
            $totalRecordswithFilter = PaymentHistory::select('count(*) as allcount')->leftJoin('users', 'payment_histories.user_id', 'users.id')
                ->leftJoin('plans', 'payment_histories.plan_id', 'plans.id')->where(function ($query) use ($search_arr) {
                    if ($search_arr) {
                        $query->orWhere('plans.name', 'like', '%' .  $search_arr . '%');
                        $query->orWhere('users.name', 'like', '%' .  $search_arr . '%');
                        $query->orWhere('payment_histories.cc_avenue_id', 'like', '%' . $search_arr . '%');
                        $query->orWhere('payment_histories.tracking_id', 'like', '%' . $search_arr . '%');
                        $query->orWhere('payment_histories.payment_mode', 'like', '%' . $search_arr . '%');
                        $query->orWhere('payment_histories.card_name', 'like', '%' . $search_arr . '%');
                        $query->orWhere('payment_histories.payment', 'like', '%' . $search_arr . '%');
                    }
                })->count();
            // DB::enableQueryLog();
            $records = DB::table('payment_histories')
                ->leftJoin('users', 'payment_histories.user_id', 'users.id')
                ->leftJoin('plans', 'payment_histories.plan_id', 'plans.id')
                ->where(function ($query) use ($search_arr) {
                    if ($search_arr) {
                        $query->orWhere('plans.name', 'like', '%' .  $search_arr . '%');
                        $query->orWhere('users.name', 'like', '%' .  $search_arr . '%');
                        $query->orWhere('payment_histories.tracking_id', 'like', '%' . $search_arr . '%');
                        $query->orWhere('payment_histories.payment_mode', 'like', '%' . $search_arr . '%');
                        $query->orWhere('payment_histories.card_name', 'like', '%' . $search_arr . '%');
                        $query->orWhere('payment_histories.cc_avenue_id', 'like', '%' . $search_arr . '%');
                        $query->orWhere('payment_histories.payment', 'like', '%' . $search_arr . '%');
                    }
                })
                ->select(['plans.name as plan_name', 'users.name as user_name', 'payment_histories.*'])
                ->skip($start)
                ->take($rowperpage)
                ->orderBy($columnName, $columnSortOrder)
                ->get();

            // dd(DB::getQueryLog($records));

            $data = array();
            $i = 0;
            foreach ($records as $record) {
                $id = Crypt::encrypt($record->id);
                $plan_id = $record->plan_name;
                $user_id = $record->user_name;
                $cc_avenue_id = $record->cc_avenue_id;
                $payment = $record->payment;
                $status = $record->status;
                $tracking_id = $record->tracking_id;
                $payment_mode = $record->payment_mode;
                $card_name = $record->card_name;
                $i++;
                $data[] = array(
                    "id" => $i,
                    "plan_id" => $plan_id,
                    "user_id" => $user_id,
                    "cc_avenue_id" => $cc_avenue_id,
                    "tracking_id" => $tracking_id,
                    "status" => $status,
                    "payment" => $payment,
                    "payment_mode" => $payment_mode,
                    "card_name" => $card_name,
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

        return view('admin.payment_history');
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $validator = Validator::make($input, [
                'code' => 'required',
                'discount' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 400);;
            }
            $user = Auth::user();
            $input['user_id'] = $user->id;
            $id = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
            if (PaymentHistory::where('code', '=', $input['code'])->where(function ($query) use ($id) {
                if ($id != 0) {
                    $query->Where(function ($query) use ($id) {
                        $query->where('id', '!=', $id);
                    });
                }
            })->first()) {
                return response()->json(['success' => 'Promo Code exists!'], 409);
            }
            if ($id == 0) {
                $activityLogMsg = 'Promo Code created by ' . $user->name;
                $company_category = PaymentHistory::create($input);
            } else {
                $company_category = PaymentHistory::find($id)->update($input);
                $activityLogMsg = 'Promo Code updated by ' . $user->name;
            }

            // Add activity logs
            $input['id'] = ($input['id']) ? Crypt::decrypt($input['id']) : $input['id'];
            LogActivity::addToLog($activityLogMsg, $input);

            return response()->json(['success' => 'Promo Code Saved!'], 201);
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

            $company_category = PaymentHistory::find($id)->toArray();

            if (is_null($company_category)) {
                return response()->json(['success' => 'Promo Code not found!'], 422);
            }
            $company_category['id'] = Crypt::encrypt($company_category['id']);
            return response()->json([
                "success" => true,
                "message" => "Promo Code retrieved successfully.",
                "data" => $company_category
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
                return response()->json(['errors' => $validator->errors()->all()], 400);
            }
            $user = Auth::user();
            $id = [];
            foreach (explode(",", $request->id) as $value) {
                $id[] = Crypt::decrypt($value);
            }
            $company_category = PaymentHistory::whereIn('id', $id)->delete();

            LogActivity::addToLog('Promo Code deleted by ' . $user->name, $id);
            return response()->json(['success' => 'Promo Code Deleted!'], 201);
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
            if (!PaymentHistory::whereIn('id', $id)->first()) {
                return response()->json(['success' => 'Promo Code exists!'], 422);
            }
            $company_category = PaymentHistory::whereIn('id', $id)->update(["status" => $input['status']]);

            $data['id'] = $id;
            $data['status'] = ($input['status'] == 0) ? 'Active' : 'Deactive';
            LogActivity::addToLog('Promo Code status updated by ' . $user->name, $data);

            return response()->json(['success' => 'Promo Code status updated!'], 201);
        }
    }

    public function payment(Request $request)
    {
        $inputs = $request->all();
        $decodedData = json_decode($inputs['data'], true);
        // $date = explode('/', $decodedData['card_expiry_date']);
        $order_id = Str::random(12, '0123456789');
        $tid = Str::random(12, '0123456789');
        $user = Auth::user();
        $company_id = ($user->company_id) ? $user->company_id : $user->id;
        $paymentHistory['user_id'] = $company_id;
        $paymentHistory['plan_id'] = $decodedData['plan_id'];
        $paymentHistory['cc_avenue_id'] = $order_id;
        $paymentHistory['status'] = "0";
        $paymentHistory['first_name'] = $decodedData['first_name'];
        $paymentHistory['email'] = $decodedData['email'];
        $paymentHistory['payment'] = $decodedData['payment'];
        $paymentHistory['mobile_no'] = $decodedData['mobile_no'];
        $paymentHistory['address'] = $decodedData['address'];
        $paymentHistory['city'] = $decodedData['city'];
        $paymentHistory['state'] = $decodedData['state'];
        $paymentHistory['country'] = $decodedData['country'];
        $paymentHistory['pincode'] = $decodedData['pincode'];
        $paymentHistory['addUsers'] = $decodedData['addUsers'];
        $paymentHistory['add_user'] = $decodedData['add_user'];
        PaymentHistory::create($paymentHistory);
        User::where('id', $company_id)->update([
            'plan_id' => $decodedData['plan_id'],
            'gst_no' => $decodedData['gst_no'],
            'pincode' => $decodedData['pincode'],
            'name' => $decodedData['first_name'],
            'email' => $decodedData['email'],
            'mobile_no' => $decodedData['mobile_no'],
            'address' => $decodedData['address'],
            'city_name' => $decodedData['city'],
            'state_id' => $decodedData['state'],
            'country_id' => $decodedData['country'],
        ]);
        $countryName = Country::where('id', $decodedData['country'])->first();
        $stateName = State::where('id', $decodedData['state'])->first();
        $payment = [];
        $payment['tid'] = $tid;
        $payment['merchant_id'] = env('MERCHANT_ID');
        $payment['order_id'] = $order_id;
        $payment['amount'] = $decodedData['payment'];
        $payment['currency'] = 'INR';
        $payment['redirect_url'] = 'https://app.quickestimate.co/payment-response';
        $payment['cancel_url'] = 'https://app.quickestimate.co/payment-cancel';
        $payment['language'] = 'EN';
        $payment['billing_name'] = $decodedData['first_name'];
        $payment['billing_address'] = $decodedData['address'];
        $payment['billing_city'] = $decodedData['city'];
        $payment['billing_state'] = isset($stateName->name) ? $stateName->name : 'Gujarat';
        $payment['billing_zip'] = $decodedData['pincode'];
        $payment['billing_country'] = isset($countryName->name) ? $countryName->name : 'India';
        $payment['billing_tel'] = $decodedData['mobile_no'];
        $payment['billing_email'] = $decodedData['email'];
        $data['data'] = $payment;
        return view('payment-secure')->with($data);
    }

    public function paymentDone(Request $request)
    {
        $data['data'] = $request->all();
        return view('payment-secure')->with($data);
    }

    public function pay(Request $request)
    {
        $data = $request->all();
        return Response::json(array('success' => true, 'data' => $data));
    }

    public function response(Request $request)
    {
        $workingKey = env('WORKING_KEY');        //Working Key should be provided here.
        $encResponse = $request->encResp;
        $decResponse =  $this->decrypt_ccavnue($encResponse, $workingKey);           //This is the response sent by the CCAvenue Server
        // $decodedResponse = json_decode($decResponse);
        $array = [];
        parse_str($decResponse, $array);

        if($array['order_status']== 'Failure')
            return redirect('/plan')->with('error', 'Payment Failure. Please try agin');

        if($array['order_status']== 'Aborted')
            return redirect('/plan')->with('error', 'Payment Aborted. Please try agin');

        if($array['order_status']== 'Invalid')
            return redirect('/plan')->with('error', 'Payment Failed. Please try agin');

        $orderData = PaymentHistory::where('cc_avenue_id', $request->orderNo)->first();
        PaymentHistory::where('cc_avenue_id', $request->orderNo)->update(['status' => 1,'tracking_id' => $array['tracking_id'],'payment_mode' => $array['payment_mode'],'card_name' => $array['card_name']]);
        if ($orderData->addUsers == 1) {
            $planHistory = PlanHistory::where([['user_id', $orderData->user_id], ['status', 1]])->first();
            PlanHistory::where([['user_id', $orderData->user_id], ['status', 1]])->update(['user_limit' => $planHistory->user_limit + $orderData->add_user]);

            $email_data = User::where('id', $orderData->user_id)->first();
            $plan_purchase_details = [
                'subject' => "Your Quickest User Limit has been Upgraded!",
                /* 'body' => "hello this is testing",*/
                'user_limit' => $planHistory->user_limit + $orderData->add_user,
                'plan_type' => $orderData->addUsers,
                'user_name' => $email_data->name

            ];

        } else {
            $plan = Plans::where('id', $orderData->plan_id)->first();
            if ($plan) {
                PlanHistory::where([['user_id', $orderData->user_id], ['status', 1]])->update(['status' => 0]);
                $end_date = Carbon::now()->addYear();
                $start_date = Carbon::now();
                $paymentHistory['user_id'] = $orderData->user_id;
                $paymentHistory['plan_id'] = $orderData->plan_id;
                $paymentHistory['user_limit'] = $plan->users_limit + $orderData->add_user;
                $paymentHistory['estimate_limit'] = $plan->estimate_limit;
                $paymentHistory['status'] = '1';
                $paymentHistory['start_date'] = $start_date;
                $paymentHistory['end_date'] = $end_date;
                PlanHistory::create($paymentHistory);
                User::where('id', $orderData->user_id)->update([
                    'plan_start_date' => $start_date,
                    'plan_end_date' => $end_date,
                    'remaining_days' => 365,
                    'plan_status' => 5,
                    'popupStatus' => 2,
                ]);
            }

            $email_data = User::where('id', $orderData->user_id)->first();
            $plan_purchase_details = [
                'subject' => "Plan Upgrade Confirmation",
                /* 'body' => "hello this is testing",*/
//                'user_limit' => $planHistory->user_limit + $orderData->add_user,
                'plan_type' => $orderData->addUsers,
                'user_name' => $email_data->name,
                'plan_name' => $plan->name,
                'users_limit' => $plan->users_limit + $orderData->add_user,
                'estimate_limit' => $plan->estimate_limit
            ];



        }
        \Mail::to($email_data->email)->send(new \App\Mail\PurchasePlanMail($plan_purchase_details));

        /*$plan_purchase_details = [
            'subject' => 'Plan Purchase',
            'body' => "hello this is testing"
        ];
        \Mail::to($email_data->email)->send(new \App\Mail\PurchasePlanMail($plan_purchase_details));*/

//        return redirect('/plan')->with('success', 'Payment Successfully Done...!!!');
        return redirect('/plan')->with('success', 'We have received your payment. Please check your email.');
    }

    public function cancel(Request $request)
    {
        $workingKey = env('WORKING_KEY');
        PaymentHistory::where('cc_avenue_id', $request->orderNo)->update(['status' => 2]);
        return redirect('/plan')->with('error', 'Payment failed...!!!');
    }

    public function encrypt_ccccavne($plainText, $key)
    {
        $key = $this->hextobin_ccavenue(md5($key));
        $initVector = pack('C*', 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $openMode = openssl_encrypt($plainText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
        $encryptedText = bin2hex($openMode);
        return $encryptedText;
    }
    public function decrypt_ccavnue($encryptedText, $key)
    {
        $key = $this->hextobin_ccavenue(md5($key));
        $initVector = pack('C*', 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $encryptedText = $this->hextobin_ccavenue($encryptedText);
        $decryptedText = openssl_decrypt($encryptedText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
        return $decryptedText;
    }

    public function hextobin_ccavenue($hexString)
    {
        $length = strlen($hexString);
        $binString = '';
        $count = 0;
        while ($count < $length) {
            $subString = substr($hexString, $count, 2);
            $packedString = pack('H*', $subString);
            if ($count == 0) {
                $binString = $packedString;
            } else {
                $binString .= $packedString;
            }

            $count += 2;
        }
        return $binString;
    }
}
