<?php

namespace App\Http\Controllers\App;

use App\Models\admin\Plans;
use App\Models\Country;
use App\Models\PaymentHistory;
use App\Models\PlanHistory;
use App\Models\State;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Razorpay\Api\Api;
use Session;
use Exception;

class RazorpayPaymentController extends Controller
{
    protected $segment = null;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->segment = $request->segment(1);
            return $next($request);
        });
    }
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function index()
    {
        return view('app.razorpayView');
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));
        $razorpay_payment_id =$input['response']['razorpay_payment_id'];
        $payment = $api->payment->fetch($razorpay_payment_id);

        if(count($input)  && !empty($razorpay_payment_id)) {
            try {
                $response = $api->payment->fetch($razorpay_payment_id)->capture(array('amount'=>$payment['amount']));
//                dd($response);
                $order_id = Str::random(12, '0123456789');
                $tid = Str::random(12, '0123456789');
                $user = Auth::user();
                $company_id = ($user->company_id) ? $user->company_id : $user->id;
                $paymentHistory['user_id'] = $company_id;
                $paymentHistory['plan_id'] = $input['plan_id'];
                $paymentHistory['cc_avenue_id'] = $response->id;
                $paymentHistory['status'] = "1";
                $paymentHistory['first_name'] = $input['first_name'];
                $paymentHistory['email'] = $input['email'];
                $paymentHistory['payment_mode'] =  $response->method;
                $paymentHistory['payment'] = $input['payment'];
                $paymentHistory['card_name'] = ($response->method=='card')? $response->card['network']:'';
                $paymentHistory['mobile_no'] = $input['mobile_no'];
                $paymentHistory['address'] = $input['address'];
                $paymentHistory['city'] = $input['city'];
                $paymentHistory['state'] = $input['state'];
                $paymentHistory['country'] = $input['country'];
                $paymentHistory['pincode'] = $input['pincode'];
                $paymentHistory['addUsers'] = $input['addUsers'];
                $paymentHistory['add_user'] = $input['add_user'];
                PaymentHistory::create($paymentHistory);
                User::where('id', $company_id)->update([
                    'plan_id' => $input['plan_id'],
                    'gst_no' => $input['gst_no'],
                    'pincode' => $input['pincode'],
                    'name' => $input['first_name'],
                    'email' => $input['email'],
                    'mobile_no' => $input['mobile_no'],
                    'address' => $input['address'],
                    'city_name' => $input['city'],
                    'state_id' => $input['state'],
                    'country_id' => $input['country'],
                ]);


                $orderData = PaymentHistory::where('cc_avenue_id', $response->id)->first();
//                PaymentHistory::where('cc_avenue_id', $request->orderNo)->update(['status' => 1,'tracking_id' => $array['tracking_id'],'payment_mode' => $array['payment_mode'],'card_name' => $array['card_name']]);
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
//                return redirect('/plan')->with('success', 'We have received your payment. Please check your email.');
                /*$countryName = Country::where('id', $decodedData['country'])->first();
                $stateName = State::where('id', $decodedData['state'])->first();*/

            } catch (Exception $e) {
                return  $e->getMessage();
                Session::put('error',$e->getMessage());
                return redirect()->back();
            }
        }
        return response()->json(['success' => true, 'message' => 'We have received your payment. Please check your email.'], 201);
       /* Session::put('success', 'Payment successful');
        return redirect()->back()->with('success', 'We have received your payment. Please check your email.');*/
    }
}
