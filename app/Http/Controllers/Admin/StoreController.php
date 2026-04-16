<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\Person;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\CpfCnpj;


class StoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:stores_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:stores_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:stores_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:stores_delete', ['only' => ['destroy']]);
        $this->middleware('tenant', ['only' => ['create', 'store']]);
    }

    public function index(Request $request)
    {
        $tenants = Tenant::person()
            ->when(session()->exists('tenant'), function ($query) {
                $query->where('tenants.id', session('tenant')['id']);
            })
            ->get();

        $data = Store::with('tenant.people', 'people')
            ->when(session()->exists('tenant'), function ($query) {
                $query->where('tenant_id', session('tenant')['id']);
            })
            ->paginate(10);

        return view('stores.index', compact('data', 'tenants'));
    }

    public function create()
    {
        $paymentMethods = PaymentMethod::where('is_enabled', true)
            ->orderBy('description')
            ->get(['id', 'description']);

        return view('stores.create', compact('paymentMethods'));
    }

    public function store(Request $request)
    {
        $person = Person::where('nif',  $request->nif)->first();

        Validator::make(
            $request->all(),
            $this->rules($request, $person['id'] ?? null)
        )->validate();

        DB::transaction(function () use ($request) {
            $inputs = $request->all();
            $inputs['tenant_id'] = session('tenant')['id'];
            $inputs['app_token'] = uniqid();

            $person = Person::updateOrCreate(
                ['nif' => $request->nif],
                $inputs
            );

            $inputs['person_id'] = $person->id;
            $store  = Store::updateOrCreate(
                ['person_id' => $person->id],
                $inputs
            );

            $store->paymentMethods()->attach($request->paymentMethods);

            if (session()->exists('store') || session()->exists('tenant')) {
                $user = User::find(auth()->id());


                $user->stores()->attach($store->id);

                $user->load(['stores' => function ($query) {
                    $query->person();
                }]);

                session(['stores' => $user->stores->toArray()]);

                if (!session()->exists('store')) {
                    session(['store' => $user->stores[0]->toArray()]);
                }
            }
        });

        return redirect()->route('stores.index')
            ->withStatus('Registro adicionado com sucesso.');
    }

    public function show($id)
    {
        $item = Store::person()->with('paymentMethods')->findOrFail($id);

        return view('stores.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Store::person()->with('paymentMethods')->findOrFail($id);


        $paymentMethods = PaymentMethod::where('is_enabled', true)
            ->orderBy('description')
            ->get(['id', 'description']);


        return view('stores.edit', compact('item', 'paymentMethods'));
    }

    public function update(Request $request, $id)
    {
        $item = Store::findOrFail($id);
        Validator::make(
            $request->all(),
            $this->rules($request, $item->person_id)
        )->validate();

        DB::transaction(function () use ($request, $item) {
            $inputs = $request->all();
            $item->fill($inputs)->save();
            $person = Person::find($item->person_id);
            $person->fill($inputs)->save();

            $item->paymentMethods()->sync($request->paymentMethods);
        });

        return redirect()->route('stores.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = Store::findOrFail($id);

        try {
            $item->delete();
            return redirect()->route('stores.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('stores.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    public function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'name' => ['required', 'max:30'],
            'formal_name' => ['required', 'max:60'],
            'nif' => ['required', 'max:20', new CpfCnpj, Rule::unique('people')->ignore($primaryKey)],
            'city_id' => ['required'],
            'email' => ['required', 'max:89', Rule::unique('people')->ignore($primaryKey)],
            'phone' => ['required', 'max:15'],
            'street' => ['required', 'max:120'],
            'status' => ['required'],
            'paymentMethods' => ['required', 'array'],
        ];
        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
