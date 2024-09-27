<?php

namespace App\Http\Middleware;

use App\Models\admin\Plans;
use App\Models\PlanHistory;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class CheckPlanValidity
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user =  \Auth::user();
        $companyId = ($user->company_id) ? $user->company_id : $user->id;
        $fetchUser = User::where('id', $companyId)->select(['plan_end_date'])->first();
        $now = Carbon::now();
        $expired_plan = Carbon::createFromFormat('Y-m-d H:i:s', $fetchUser->plan_end_date)->format('Y-m-d H:i:s');
        $companyData = User::where('id', $companyId)->first();
        if ($now <= $expired_plan) {
            return $next($request);
        } else {
            if ($companyData->popupStatus == '0') {
                return redirect('expired-plan-popup');
            } elseif ($companyData->popupStatus == '1') {
                return redirect('second-expired-plan-popup');
            } else {
                $from_date = Carbon::now()->addYear(1);
                $plan = Plans::where('status', 1)->first();
                PlanHistory::where([['user_id', $companyId], ['status', 1]])->update(['status' => 0]);
                User::where('id', $companyId)->update([
                    'plan_id' => $plan->id,
                    'plan_start_date' => $now,
                    'remaining_days' => 365,
                    'plan_end_date' => $from_date,
                    'plan_status' => 4
                ]);
                PlanHistory::create([
                    'user_id' => $companyId,
                    'plan_id' => $plan->id,
                    'user_limit' => $plan->users_limit,
                    'estimate_limit' => $plan->estimate_limit,
                    'status' => 1,
                    'start_date' => $now,
                    'end_date' => $from_date
                ]);
                return redirect('dashboard');
            }
        }
    }
}
