<?php

namespace App\Http\Controllers\API;

use App\Models\State;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;

class StateController extends BaseController
{
    public function index(Request $request)
    {
        $query = State::orderBy('title', 'asc');

        ($request->has('page'))  ? $data = $query->paginate(10) : $data = $query->get();

        return $this->sendResponse($data);
    }

    public function show($id)
    {
        $item = State::findOrFail($id);

        return $this->sendResponse($item);
    }


}
