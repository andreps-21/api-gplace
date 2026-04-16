<?php

namespace App\Http\Controllers\API;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Person;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserLeadController extends BaseController
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $person = Person::where('email', $request->email)->first();

        $validator = $this->getValidationFactory()
            ->make(
                $request->all(),
                $this->rules($request, $person['id'] ?? null)
            );

        if ($validator->fails()) {
            return $this->sendError('Erro de Validação.', $validator->errors()->toArray(), 422);
        }

        try {
            DB::beginTransaction();

            $inputs = $request->all();

            if ($request->filled('password')) {
                $inputs['password'] = bcrypt($request->input('password'));
            }

            $inputs['status'] = 1;
            $inputs['type'] = 1;
            $inputs['origin'] = 1;
            $inputs['formal_name'] = $inputs['name'];
            $inputs['city_id'] = 323;

            $person = Person::updateOrCreate(['email' => $request->email], $inputs);

            $user = User::updateOrCreate([
                'person_id' => $person->id],
                $inputs);

            Lead::updateOrCreate(
                ['store_id' => $request->get('store')['id'],'person_id' => $person->id],$inputs);

            // $customer = Customer::updateOrCreate(
            //     ['person_id' => $person->id],
            //     $inputs
            // );
            // $customer->tenants()->attach($request->get('store')['tenant_id']);

            $store = $request->get('store')['id'];
            if (!$user->stores()->where('store_id', $store)->exists()) {
                $user->stores()->attach($store);
            }

            DB::commit();
            return $this->sendResponse($user);
        } catch (\Throwable $th) {
            return $this->sendError("Não foi possível criar a conta. {$th->getMessage()}");
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'name' => ['required', 'max:120'],
            'email' => ['required', 'max:89', Rule::unique('people')->ignore($primaryKey)],
            'password' => ['required', 'min:8'],
            'city_id' => ['nullable', 'exists:cities,id']
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
