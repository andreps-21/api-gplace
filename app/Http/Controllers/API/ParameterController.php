<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Parameter;
use Illuminate\Http\Request;

class ParameterController extends BaseController
{
    public function index(Request $request){
        $data = Parameter::orderBy('name')->get();

        return $this->sendResponse($data);
    }
}
