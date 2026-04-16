<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Person;
use App\Models\User;
use App\Rules\CpfCnpj;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends BaseController
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $person = Person::where('nif', $request->nif)->first();

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

            $inputs = $request->except('address', 'email', 'nif', 'password');

            if (!$person) {
                $inputs = $request->except('address', 'password');
            }

            if ($request->filled('password')) {
                $inputs['password'] = bcrypt($request->input('password'));
            }

            $inputs['status'] = 1;
            $inputs['type'] = 1;
            $inputs['origin'] = 1;

            $person = Person::updateOrCreate(['nif' => $request->nif], $inputs);

            $user = User::updateOrCreate(
                ['person_id' => $person->id],
                $inputs
            );

            $customer = Customer::updateOrCreate(
                ['person_id' => $person->id],
                $inputs
            );

            $customer->tenants()->attach($request->get('store')['tenant_id']);

            if ($request->address) {
                $inputs = $request->address;
                $inputs['customer_id'] = $customer->id;

                Address::create($inputs);
            }

            $store = $request->get('store')['id'];
            $user->stores()->attach($store);
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
            'phone' => ['required', 'max:15'],
            'password' => ['nullable', 'min:8'],
            'formal_name' => ['required', 'max:50'],
            'nif' => ['required', 'max:14', new CpfCnpj, Rule::unique('people')->ignore($primaryKey)],
            'zip_code' => ['required', 'max:9'],
            'city_id' => ['required', 'exists:cities,id'],
            'district' => ['required', 'string', 'min:3', 'max:30'],
            'state_registration' => ['nullable', 'max:25'],
            'street' => ['required', 'max:60'],
            'address.zip_code' => ['required', 'max:9'],
            'address.street' => ['required', 'max:60'],
            'address.number' => ['required', 'max:15'],
            'address.complement' => ['nullable', 'max:189'],
            'address.district' => ['required', 'max:30'],
            'address.city_id' => ['required', 'exists:cities,id'],
            'address.reference' => ['nullable', 'max:60']
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
