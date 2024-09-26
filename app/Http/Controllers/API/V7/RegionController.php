<?php

namespace App\Http\Controllers\API\V6;

use App\Http\Controllers\API\V6\BaseController as BaseController;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\Request;

class RegionController extends BaseController
{
    public function getCountry(Request $request)
    {
        $data['countries'] = Country::where('status', '=', 0)->get(["*"]);
        return $this->sendResponse($data, 'Country retrieved successfully');
    }

    public function getState($country_id)
    {
        $data['states'] = State::where([["country_id", '=', $country_id], ['status', '=', 0]])->get(["name", "id"]);
        return $this->sendResponse($data, 'State retrieved successfully');
    }

    public function getCity($state_id)
    {
        $data['cities'] = City::where([["state_id", '=', $state_id], ['status', '=', 0]])->get(["name", "id"]);
        return $this->sendResponse($data, 'City retrieved successfully');
    }
}
