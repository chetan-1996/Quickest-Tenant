<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\admin\Plans;
use App\Models\PlanHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Http\Response;

class PlanController extends Controller
{
    protected $segment = null;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->segment = $request->segment(1);
            return $next($request);
        });
    }

    public function index()
    {
        $user = Auth::user();
        $plans = Plans::take(2)->get();
        $companyId = isset($user->company_id) ? $user->company_id : $user->id;
        $companyData = User::where('id', $companyId)->first();
        $activePlan = Plans::where('id', isset($companyData) ? $companyData->plan_id : $user->plan_id)->first();
        $userCount = User::where('company_id', $companyId)->count();
        $segment = $this->segment;
        return view('app.plan.index', compact('plans', 'user', 'userCount', 'activePlan', 'companyData', 'segment'));
    }
    public function promo_code(Request $request)
    {
        $input = $request->all();
        $promo_code = PromoCode::where('code', $input['promo_code_check'])->first();
        $price = $input['firth_value'];
        if (!isset($promo_code)) {
            return response()->json(['success' => 2, 'price_total' => 00]);
        }
        $price_total = $promo_code->discount;
        if ($promo_code->type == 1) {
            $price_total = $price * $promo_code->discount / 100;
        }
        return response()->json(['success' => 1, 'price_total' => (float)$price_total]);
    }
}
