<?php

namespace App\Http\Controllers\API\V6;

use App\Http\Controllers\API\V6\BaseController as BaseController;
use App\Models\ProposalTemplates;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProposalController extends BaseController
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

    public function __invoke(Request $request){
        $data = ProposalTemplates::select(['id','template_name','company_id'])->where('company_id',$this->company_id)->get();
        $data[0]->proposalimage = (Storage::disk('s3')->exists('public/'.$data[0]->company_id. '/documents/proposal-sample.jpeg'))?Storage::disk('s3')->url('public/'.$data[0]->company_id. '/documents/proposal-sample.jpeg'):url(Storage::url('template/proposal-template.png'));
        return $this->sendResponse($data, 'Proposal template retrieved successfully');
    }

    public function getSingleProposal(Request $request){
        $data = ProposalTemplates::select(['*'])->where('company_id',$this->company_id)->get();
        return $this->sendResponse($data, 'Proposal template single retrieved successfully');
    }
}
