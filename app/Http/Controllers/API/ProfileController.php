<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Person;
use App\Models\User;
use App\Rules\CpfCnpj;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProfileController extends BaseController
{

    public function show()
    {
        $user = auth()->user();

        $data = User::Person()
            ->where('users.id', $user->id)
            ->first();

        if ($data) {
            $data->load(['roles' => function ($query) {
                $query->select('roles.id', 'roles.name', 'roles.guard_name');
            }]);
            $first = $data->roles->first();
            $data->setAttribute('role', $first ? $first->name : null);
            $data->loadMissing(['roles.permissions', 'permissions']);
            $data->setAttribute(
                'permissions',
                $data->getAllPermissions()->pluck('name')->unique()->values()->all()
            );
        }

        return $this->sendResponse($data);
    }

    public function update(Request $request)
    {
        $validator = $this->getValidationFactory()
            ->make(
                $request->all(),
                $this->rules($request, $request->user()->person_id)
            );


        if ($validator->fails()) {
            return $this->sendError('Erro de Validação.', $validator->errors()->toArray(), 422);
        }

        try {
            $user = User::find($request->user()->id);

            DB::transaction(function () use ($request, $user) {
                $person_id = $request->user()->person_id;

                $inputs = $request->except(['email']);
                $inputs['origin'] = 1;
                $inputs['status'] = 1;
                $inputs['type'] = 1;

                $person = Person::find($person_id);

                if ($person && !empty($person->nif)) {
                    unset($inputs['nif']);
                }

                $person->fill($inputs)->save();

                $user->fill($inputs)->save();

                $customer = Customer::updateOrCreate(
                    ['person_id' => $person->id],
                    $inputs
                );

                $customer->tenants()->attach($request->get('store')['tenant_id']);
            });

            return $this->sendResponse([], "Perfil atualizado com sucesso.");
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), "", 500);
        }
    }


    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'name' => ['required', 'max:120'],
            'email' => ['required', 'max:89', Rule::unique('people')->ignore($primaryKey)],
            'phone' => ['required', 'max:15'],
            'formal_name' => ['required', 'max:50'],
            'nif' => ['required', 'max:14', new CpfCnpj, Rule::unique('people')->ignore($primaryKey)],
            'zip_code' => ['required', 'max:9'],
            'street' => ['required', 'string', 'max:120'],
            'city_id' => ['required', 'exists:cities,id'],
            'district' => ['required', 'string', 'min:3', 'max:30']
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
