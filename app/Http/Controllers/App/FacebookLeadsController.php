<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FacebookLeadsController extends Controller
{
      
    public function facebook_lead(Request $request){
        //echo "<pre>"; print_r($request);exit;
        return $request;
    }

    public function facebook_lead_post(Request $request){
        echo "<pre>"; print_r($request);exit;
        return $request;
    }
}
