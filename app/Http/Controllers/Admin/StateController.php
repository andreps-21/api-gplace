<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\State;
use Illuminate\Http\Request;

class StateController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:states_view', ['only' => ['show', 'index']]);
    }

    public function index(Request $request)
    {
        $data = State::orderBy('title', 'asc')->paginate(10);

        return view('states.index', compact('data'));
    }
}
