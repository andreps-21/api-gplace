<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\CustomerFromView;
use Maatwebsite\Excel\Facades\Excel;

class ExportCustomerController extends Controller
{

    public function index(Request $request)
    {
        $queries = $request->query();

        return Excel::download(new CustomerFromView($queries), 'Clientes.xls');
    }

}
