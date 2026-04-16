<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\LeadsFromView;
use Maatwebsite\Excel\Facades\Excel;

class ExportLeadsController extends Controller
{
    public function index(Request $request)
    {
        $queries = $request->query();

        return Excel::download(new LeadsFromView($queries), 'Leads.xls');
    }
}
