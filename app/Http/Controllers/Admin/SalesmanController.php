<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Validation\Rule;
use App\Rules\CpfCnpj;
use App\Models\Person;
use App\Models\Salesman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class SalesmanController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:salesman_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:salesman_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:salesman_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:salesman_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = Salesman::person()
        ->when(!empty($request->search), function ($q) use ($request) {
            return  $q->where(function ($quer) use ($request) {
                return $quer->where('people.name', 'LIKE', "%$request->search%")
                    ->orWhereRaw('people.nif like "%' . $request->search . '%"');
            });
        })
        ->when(!empty($request->status), function ($q) use ($request) {
            return $q->where('salesmen.status', $request->status);
        })
        ->when(!empty($request->start_date), function ($q) use ($request) {
            return $q->whereDate('salesmen.created_at', '>=', $request->start_date);
        })
        ->when(!empty($request->end_date), function ($q) use ($request) {
            return $q->whereDate('salesmen.created_at', '<=', $request->end_date);
        })
        ->when(session()->exists('store'), function ($q) use ($request) {
            return $q->whereHas('stores', function ($que) {
                return $que->where('store_id', session('store')['id']);
            });
        })
        ->orderBy('name')
        ->paginate(10);

        return view('salesman.index', compact('data'));
    }

    public function create()
    {
        return view('salesman.create');
    }

    public function store(Request $request)
    {
        $people = Person::where('nif', $request->nif)->first();

        Validator::make(
            $request->all(),
            $this->rules($request, $people['id'] ?? null)
        )->validate();


        DB::transaction(function () use ($request) {
            $inputs = $request->all();

            $person = Person::updateOrCreate(
                ['nif' => $request->nif],
                $inputs
            );

            $inputs['person_id'] = $person->id;

            $salesmen = Salesman::updateOrCreate(
                ['person_id' => $inputs['person_id']],
                $inputs
            );
            
            $user = User::updateOrCreate(
                ['person_id' => $inputs['person_id']],
                [
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt(preg_replace("/\D+/", "", $inputs['nif'])),
                    'is_enabled' => $inputs['status'] == 1
                ]
            );

            if (session()->exists('store')) {
                $salesmen->stores()->attach(session('store')['id']);
            }
            if (session()->exists('store')) {
                $user->stores()->attach(session('store')['id']);
            }

            $user->assignRole('vendedor');
        });

        return redirect()->route('salesman.index')
            ->withtStatus('Registro cadastrado com sucesso.');
    }

    public function show($id)
    {

        $item = Salesman::person()->findOrFail($id);

        return view('salesman.show', compact('item'));
    }

    public function edit($id)
    {

        $item = Salesman::person()->findOrFail($id);

        return view('salesman.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {

        $item = Salesman::findOrFail($id);

        Validator::make(
            $request->all(),
            $this->rules($request, $item->person_id)
        )->validate();

        DB::transaction(function () use ($request, $item) {

            $inputs = $request->except(['email', 'nif']);

            $item->fill($inputs)->save();

            $people = Person::find($item->person_id);
            $people->fill($inputs)->save();
        });

        return redirect()->route('salesman.index')
            ->withStatus('Registro atualizado com sucesso');
    }

    public function destroy($id)
    {

        $item = Salesman::findOrFail($id);

        try {
            $item->delete();
            return redirect()->route('salesman.index')
                ->withStatus('Registro deletado com sucesso');
        } catch (\Exception $e) {
            return redirect()->route('salesman.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {

        $rules = [
            'state_registration' => ['nullable', 'max:25'],
            'municipal_registration' => ['nullable', 'max:25'],
            'birth_date' => ['date'],
            'nif' => ['required', 'max:14', new CpfCnpj, Rule::unique('people')->ignore($primaryKey)],
            'name' => ['required', 'string', 'max:30'],
            'zip_code' => ['string', 'max:9'],
            'street' => ['required', 'string', 'max:60'],
            'city_id' => ['required', 'string', 'exists:cities,id'],
            'phone' => ['required', 'string', 'min:10', 'max:15'],
            'email' => ['required', 'max:45', Rule::unique('people')->ignore($primaryKey)],
            'notes' => ['nullable', 'max:120']
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
