<?php

namespace App\Http\Controllers\Admin;

use App\Models\Lead;
use App\Models\Person;
use App\Http\Controllers\Controller;
use App\Rules\CpfCnpj;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:leads_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:leads_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:leads_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:leads_delete', ['only' => ['destroy']]);
    }
    public function index(Request $request)
    {
        $data = Lead::person()
            ->when(!empty($request->search), function ($query) use ($request) {
                return $query->where(function ($query) use ($request) {
                    return $query->where('people.name', 'LIKE', "%$request->search%")
                        ->orWhere('people.email', 'LIKE', "%$request->search%")
                        ->orWhere('people.nif', 'LIKE', "%$request->search%");
                });
            })
            ->when(session()->has('store'), function ($query) {
                $query->where('store_id', session('store')['id']);
            })
            ->when(!empty($request->start_date), function ($query) use ($request) {
                return $query->whereDate('lead.created_at', $request->start_date);
            })
            ->with('store.people')
            ->orderBy('people.name')
            ->paginate(10);

        return view('leads.index', compact('data'));
    }

    public function create()
    {
        return view('leads.create');
    }

    public function store(Request $request)
    {
        $person = Person::where('nif', $request->nif)->first();

        $this->validate(
            $request,
            $this->rules($request, $person->id ?? null)
        );

        DB::transaction(function () use ($request) {
            $inputs = $request->all();

            $person = Person::updateOrCreate(
                ['nif' => $inputs['nif']],
                $inputs
            );

            $inputs['person_id'] = $person->id;
            $inputs['store_id'] = session('store')['id'];
        
        Lead::updateOrCreate
        (
                [
                    'person_id' => $inputs['person_id'],
                    'store_id' => $inputs['store_id'],             
                ],
                $inputs
            );
        });

        return redirect()->route('leads.index')
            ->withStatus('Registro adicionado com sucesso.');
    }

    public function show($id)
    {
        $item = Lead::person()->findOrFail($id);

        return view('leads.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Lead::person()->findOrFail($id);

        return view('leads.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Lead::findOrFail($id);

        $this->validate(
            $request,
            $this->rules($request, $item->person_id)
        );

        DB::transaction(function () use ($request, $item) {
            $inputs = $request->all();

            $person = Person::findOrFail($item->person_id);
            $person->fill($inputs)->save();
            $item->fill($inputs)->save();
        });

        return redirect()->route('leads.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = Lead::findOrFail($id);

        try {
            $item->delete();
            return redirect()->route('leads.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('leads.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'nif' => ['required', 'max:18', new CpfCnpj, Rule::unique('people')->ignore($primaryKey)],
            'email' => ['required', 'max:100', Rule::unique('people')->ignore($primaryKey)],
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
