<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:cities_view', ['only' => ['show', 'index']]);
    }

    public function index(Request $request)
    {
        $data = City::with('state')
            ->orderBy('cities.title', 'asc')
            ->paginate(10);

        return view('cities.index', compact('data'));
    }
}
