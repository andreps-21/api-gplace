<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Customer;
use Illuminate\Http\Request;

class AddressController extends BaseController
{
    public function index(Request $request)
    {
        $client = Customer::where('person_id', auth()->user()->person_id)->first();

        if (!$client) {
            return $this->sendError('Cadastro incompleto. Favor atualizar seu cadastro.');
        }

        $addresses = Address::info()
            ->where('customer_id', $client->id)
            ->orderBy('street')
            ->get();

        return $this->sendResponse($addresses);
    }

    public function store(Request $request)
    {
        $validator = $this->getValidationFactory()
            ->make(
                $request->all(),
                $this->rules($request)
            );

        if ($validator->fails()) {
            return $this->sendError('Erro de Validação.', $validator->errors()->toArray(), 422);
        }

        $client = Customer::where('person_id', auth()->user()->person_id)->first();

        if (!$client) {
            return $this->sendError('Cadastro incompleto. Favor atualizar seu cadastro.');
        }


        $inputs = $request->all();
        $inputs['customer_id'] = $client->id;

        Address::create($inputs);

        return $this->sendResponse([], "Endereço criado com sucesso", 201);
    }

    public function show($id)
    {
        $client = Customer::where('person_id', auth()->user()->person_id)->first();

        if (!$client) {
            return $this->sendError('Cadastro incompleto. Favor atualizar seu cadastro.');
        }

        $address = Address::info()
            ->where('addresses.customer_id', $client->id)
            ->where('addresses.id', $id)
            ->firstOrFail();

        return $this->sendResponse($address);
    }

    public function update(Request $request, $id)
    {
        $client = Customer::where('person_id', auth()->user()->person_id)->first();

        if (!$client) {
            return $this->sendError('Cadastro incompleto. Favor atualizar seu cadastro.');
        }

        $address = Address::info()
            ->where('addresses.customer_id', $client->id)
            ->where('addresses.id', $id)
            ->firstOrFail();

        $validator = $this->getValidationFactory()
            ->make(
                $request->all(),
                $this->rules($request, $id)
            );

        if ($validator->fails()) {
            return $this->sendError('Erro de Validação.', $validator->errors()->toArray(), 422);
        }

        $inputs = $request->all();

        $address->fill($inputs)->save();

        return $this->sendResponse([], "Endereço atualizado com sucesso", 200);
    }

    public function destroy($id)
    {
        $client = Customer::where('person_id', auth()->user()->person_id)->first();

        if (!$client) {
            return $this->sendError('Cadastro incompleto. Favor atualizar seu cadastro.');
        }

        try {
            Address::query()
                ->where('addresses.customer_id', $client->id)
                ->where('addresses.id', $id)
                ->delete();
            return $this->sendResponse([], "Endereço deletado com sucesso", 200);
        } catch (\Exception $e) {
            return $this->sendError("Não é possível excluir endereço que possua pedidos vinculados.", [], 403);
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'zip_code' => ['required', 'max:9'],
            'street' => ['required', 'max:60'],
            'number' => ['required', 'max:15'],
            'complement' => ['nullable', 'max:189'],
            'district' => ['required', 'max:30'],
            'city_id' => ['required', 'exists:cities,id'],
            'reference' => ['nullable', 'max:60']
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
