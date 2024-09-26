<?php

namespace App\Http\Controllers\API\V6;

use App\Http\Controllers\API\V6\BaseController as BaseController;
use App\Models\TermCondition;
use Illuminate\Support\Facades\Auth;

class TermConditionController extends BaseController
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

    public function termConditionAutocomplete($search = null)
    {
        $termCondition = TermCondition::select('id', 'name', 'description')
            ->where([
                ['name', 'LIKE', '%' . $search . '%'],
//                ['id', '=', $search],
                ['status', '=', 0],
                ['company_id', '=', $this->company_id]
            ])
            ->get();
        return $this->sendResponse($termCondition, 'Term condition retrieved successfully');
    }

}
