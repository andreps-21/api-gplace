<?php

namespace App\Http\Controllers\Admin;

use App\Rules\CpfCnpj;
use App\Models\Person;
use App\Models\Customer;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:customers_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:customers_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:customers_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:customers_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data =  Customer::person()
            ->when(!empty($request->search), function ($q) use ($request) {
                return  $q->where(function ($quer) use ($request) {
                    return $quer->where('people.name', 'LIKE', "%$request->search%")
                        ->orWhereRaw('people.nif like "%' . $request->search . '%"');
                });
            })
            ->when(!empty($request->status), function ($q) use ($request) {
                return $q->where('customers.status', $request->status);
            })
            ->when(!empty($request->start_date), function ($q) use ($request) {
                return $q->whereDate('customers.created_at', '>=', $request->start_date);
            })
            ->when(!empty($request->end_date), function ($q) use ($request) {
                return $q->whereDate('customers.created_at', '<=', $request->end_date);
            })
            ->when(session()->exists('store'), function ($q) use ($request) {
                return $q->whereHas('tenants', function ($que) {
                    return $que->where('tenant_id', session('store')['tenant_id']);
                });
            })
            ->when(session()->exists('tenants'), function ($query) {
                $query->whereHas('tenants', function ($query) {
                    $query->where('tenants.id', session('tenant')['id']);
                });
            })
            ->orderBy('name')
            ->paginate(10);

        return view('customers.index', compact('data'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $person = Person::where('nif', $request->nif)->first();

        Validator::make(
            $request->all(),
            $this->rules($request, $person['id'] ?? null)
        )->validate();


        DB::transaction(function () use ($request) {
            $inputs = $request->all();
            $inputs['password'] = bcrypt($request->nif);

            $person = Person::updateOrCreate(
                ['nif' => $request->nif],
                $inputs
            );

            $inputs['person_id'] = $person->id;

            $customer = Customer::updateOrCreate(
                ['person_id' => $inputs['person_id']],
                $inputs
            );

             User::updateOrCreate(
                ['person_id' => $person->id],
                $inputs
            );

            if (session()->exists('tenant')) {
                $customer->tenants()->attach(session('tenant')['id']);
            }
        });

        return redirect()->route('customers.index')
            ->withStatus('Registro adicionado com sucesso.');
    }

    public function show($id)
    {
        $item = Customer::person()->findOrFail($id);

        return view('customers.show', compact('item'));
    }

    public function edit($id)
    {
        $tenants = Tenant::person()->get();
        $item = Customer::person()->with('tenants')->findOrFail($id);

        return view('customers.edit', compact('item', 'tenants'));
    }

    public function update(Request $request, $id)
    {
        $item = Customer::findOrFail($id);

        Validator::make(
            $request->all(),
            $this->rules($request, $item->person_id)
        )->validate();

        DB::transaction(function () use ($request, $item) {
            $inputs = $request->except(['nif', 'email']);

            $item->fill($inputs)->save();


            $people = Person::find($item->person_id);
            $people->fill($inputs)->save();

        });


        return redirect()->route('customers.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = Customer::findOrFail($id);
        DB::beginTransaction();
        try {
            $item->tenants()->detach();
            User::where('person_id', $item->person_id)->delete();
            Address::where('customer_id', $item->id)->delete();
            Customer::where('person_id', $item->person_id)->delete();
            Person::where('id', $item->person_id)->delete();
            DB::commit();
            return redirect()->route('customers.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('customers.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    public function addresses($id)
    {
        $item = Address::select(
            "addresses.*",
            DB::raw("concat(cities.title, ' - ', states.letter) as city_uf")
        )
            ->join('cities', 'cities.id', '=', 'addresses.city_id')
            ->join('states', 'states.id', '=', 'cities.state_id')
            ->where('addresses.customer_id', $id)->get();

        return view('customers.addresses', compact('item'));
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'state_registration' => ['nullable', 'max:25'],
            'origin' => ['required'],
            'formal_name' => ['required', 'max:50'],
            'birth_date' => ['nullable', 'date'],
            'nif' => ['required', 'max:14', new CpfCnpj, Rule::unique('people')->ignore($primaryKey)],
            'name' => ['required', 'string', 'max:30'],
            'zip_code' => ['required', 'max:9'],
            'number' => ['required', 'max:10'],
            'street' => ['required', 'string', 'max:60'],
            'city_id' => ['required', 'string', 'exists:cities,id'],
            'phone' => ['required', 'string', 'min:10', 'max:15'],
            'district' => ['required', 'string', 'min:3', 'max:30'],
            'email' => ['required', 'max:89', Rule::unique('people')->ignore($primaryKey)],
            'contact' => ['nullable', 'max:30'],
            'contact_phone' => ['nullable', 'max:15'],
            'contact_email' => ['nullable', 'email'],
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
