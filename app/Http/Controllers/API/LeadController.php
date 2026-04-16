<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Person;
use App\Models\Rule;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeadController extends BaseController
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return $this->sendError('Erro de validação', $validator->errors(), 213);
        }

        try {
            $inputs = $request->all();
            $inputs['status'] = 1;
            $inputs['formal_name'] = $inputs['name'];

            $person = Person::where('email', $request->email)->first();
            
            if (!$person) {
                $person = Person::create($inputs);
            }

            Lead::updateOrCreate(
                [
                    'store_id' => $request->get('store')['id'],
                    'person_id' => $person->id,
                ],
                $inputs
            );

            return $this->sendResponse([], "Lead criado com sucesso", 201);
        } catch (Exception $e) {
            return $this->sendError("", $e->getMessage(), 500);
        }
    }
}
