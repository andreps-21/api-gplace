<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GetUserByNifController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $nif = $request->nif;

        $person = Person::query()
            ->select(
                'customers.*',
                'people.*',
                DB::raw("concat(cities.title, ' - ', states.letter) as city"),
                'states.letter as state',
                'cities.title as city_name'
            )
            ->join('customers', 'people.id', '=', 'customers.person_id')
            ->join('cities', 'cities.id', '=', 'people.city_id')
            ->join('states', 'states.id', '=', 'cities.state_id')
            ->where('nif', $nif)
            ->with(['city' => function ($query) {
                $query->stateName();
            }])
            ->first();

        return $this->sendResponse($person);
    }
}
