<?php

namespace App\Actions\Maxdata;

use App\Models\Customer;
use App\Models\Person;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GetClientMaxdata
{
    public function execute(
        string $url,
        string $token,
        string $empId,
        int $storeId,
        int $tenantId
    ) {
        $totalPages = 1;
        $page = 1;
        $clients = [];
        do {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'empId' => $empId,
            ])
                ->withToken($token)
                ->get("{$url}/v1/cliente/consultar?page={$page}");

            if ($response->failed()) {
                Log::error("Erro na busca de produtos max data: " . json_encode($response->body()));
                throw new Exception($response->body());
            }

            $result = $response->json();
            $totalPages = $result['pages'];
            $page++;
            $clients = array_merge($clients, $result['docs']);
        } while ($page <= $totalPages);

        $clientsId = array_column($clients, 'codigo');

        $clientsExists = Customer::whereIn('external_id', $clientsId)
            ->whereHas('tenants', function ($query) use ($tenantId) {
                $query->where('tenants.id', $tenantId);
            })
            ->get(['external_id'])
            ->pluck('external_id')
            ->all();

        $codesToInsert = array_diff($clientsId, $clientsExists);

        $clientsToInsert = array_filter($clients, function ($item) use ($codesToInsert) {
            return in_array($item['codigo'], $codesToInsert);
        });


        try {
            DB::beginTransaction();
            $peopleDB = Person::whereIn('nif', array_column($clients, 'cpfCnpj'))->get(['id', 'nif']);

            $people = [];
            $clients = [];
            $users = [];
            foreach ($clientsToInsert as $client) {
                if (!empty($client['cpfCnpj']) && !in_array($client['cpfCnpj'], $peopleDB->pluck('nif')->all())) {
                    $people[] = [
                        'nif' => $client['cpfCnpj'],
                        'name' => !empty($client['fantasia']) ? $client['fantasia'] : $client['nome'],
                        'formal_name' =>  $client['nome'],
                        'email' =>  $client['email'],
                        'phone' => $client['telefone'],
                        'state_registration' => $client['rgInscEstadual'],
                        'street' => !empty($client['enderecos']) ?  $client['enderecos'][0]['endereco'] : null,
                        'number'  => !empty($client['enderecos']) ?  $client['enderecos'][0]['numeroEndereco'] : null,
                        'city_id' => 443,
                        'district' => !empty($client['enderecos']) ?  $client['enderecos'][0]['bairro'] : null,
                        'zip_code' => !empty($client['enderecos']) ?  $client['enderecos'][0]['cep'] : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $users[] = [
                        'name' => !empty($client['fantasia']) ? $client['fantasia'] : $client['nome'],
                        'email' =>  $client['email'],
                        'password' => bcrypt($client['cpfCnpj']),
                        'is_enabled' => true,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }

                $clients[] = [
                    'nif' => $client['cpfCnpj'],
                    'state_registration' => $client['rgInscEstadual'],
                    'origin' => 2,
                    'birth_date' => null,
                    'type' => 1,
                    'contact' => null,
                    'contact_phone' => null,
                    'contact_email' => null,
                    'status' => 1,
                    'notes' => null,
                    'sync_at' => now(),
                    'municipal_registration' => null,
                    'external_id' => $client['codigo']
                ];
            }
            Person::insert($people);

            $peopleDB = Person::whereIn('nif', array_column($people, 'nif'))
                ->orWhereIn('nif', $peopleDB->pluck('nif')->all())
                ->get(['id', 'nif']);

            foreach ($clients as $index => $client) {
                $person = $peopleDB->firstWhere('nif', $client['nif']);
                $clients[$index]['person_id'] = $person->id;
                unset($clients[$index]['nif']);
            }

            foreach ($users as $index => $user) {
                $person = $peopleDB->firstWhere('email', $user['email']);
                $users[$index]['person_id'] = $person->id;
            }
            Customer::insert($clients);
            User::insert($users);

            $tenant = Tenant::find($tenantId);

            $customers = Customer::whereIn('external_id', $clientsId)
                ->whereHas('tenants', function ($query) use ($tenantId) {
                    $query->where('tenants.id', $tenantId);
                })
                ->with('people.user')
                ->get();

            $tenant->customers()->attach($customers->modelKeys());

            $store = Store::find($storeId);

            $users = $customers->pluck('people.user.id')->all();
            $store->users()->attach($users);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("ERRO na MIGRAÇÃO de clientes maxdata: " . $th->getMessage());
        }
    }
}
