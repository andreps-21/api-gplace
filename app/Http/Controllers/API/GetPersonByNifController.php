<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Person;
use Illuminate\Http\Request;

class GetPersonByNifController extends BaseController
{
    public function __invoke(Request $request)
    {
        $nif = clean($request->nif);

        $person = Person::with(['city' => function ($query) {
            $query->stateName();
        }])
            ->where('nif', $nif)
            ->first();

        return $this->sendResponse($person);
    }
}
