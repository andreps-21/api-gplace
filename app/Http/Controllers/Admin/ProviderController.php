<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\Request;
use App\Models\Profession;
use App\Models\Provider;
use App\Models\Person;
use App\Rules\CpfCnpj;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class ProviderController extends Controller
{
    public function index(Request $request)
    {
        $data = Provider::person()
            ->when(!empty($request->search), function ($q) use ($request) {
                return  $q->where(function ($quer) use ($request) {
                    return $quer->where('people.name', 'LIKE', "%$request->search%")
                        ->orWhereRaw('people.nif like "%' . $request->search . '%"');
                });
            })
            ->when(!empty($request->status), function ($q) use ($request) {
                return $q->where('providers.status', $request->status);
            })
            ->when(!empty($request->start_date), function ($q) use ($request) {
                return $q->whereDate('providers.created_at', '>=', $request->start_date);
            })
            ->when(!empty($request->end_date), function ($q) use ($request) {
                return $q->whereDate('providers.created_at', '<=', $request->end_date);
            })
            ->orderBy('people.name')
            ->paginate(10);

        return view('providers.index', compact('data'));
    }

    public function create()
    {
        $professions = Profession::where('is_enabled', true)
            ->orderBy('name')
            ->get();

        $banks = Bank::orderBy('code')->get(['id', 'code', 'name']);

        return view('providers.create', compact('professions', 'banks'));
    }

    public function store(Request $request)
    {
        $person = Person::where('nif',  $request->nif)->first();

        Validator::make(
            $request->all(),
            $this->rules($request, $person['id'] ?? null)
        )->validate();

        $inputs = $request->all();

        $person = Person::updateOrCreate(
            ['nif' => $request->nif],
            $inputs
        );

        $inputs['person_id'] = $person->id;

        Provider::updateOrCreate(
            ['person_id' => $person->id],
            $inputs
        );

        return redirect()->route('providers.index')
            ->withStatus('Registro adicionado com sucesso.');
    }

    public function show($id)
    {
        $item = Provider::person()->findOrFail($id);

        return view('providers.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Provider::person()->findOrFail($id);

        $professions = Profession::where('is_enabled', true)
            ->orderBy('name')
            ->get();

        $banks = Bank::orderBy('code')->get(['id', 'code', 'name']);

        return view('providers.edit', compact('item', 'professions', 'banks'));
    }

    public function update(Request $request, $id)
    {
        $item = Provider::findOrFail($id);

        Validator::make(
            $request->all(),
            $this->rules($request, $item->person_id)
        )->validate();

        $inputs = $request->except(['nif', 'email']);

        DB::transaction(function () use ($request, $item) {
            $inputs = $request->all();

            $item->fill($inputs)->save();

            $person = Person::find($item->person_id);
            $person->fill($inputs)->save();
        });

        return redirect()->route('providers.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = Provider::findOrFail($id);

        try {
            $item->delete();
            return redirect()->route('providers.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('providers.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'name' => ['required', 'max:120'],
            'formal_name' => ['required', 'max:30'],
            'nif' => ['required', 'max:14', new CpfCnpj, Rule::unique('people')->ignore($primaryKey)],
            'email' => ['required', 'max:89', Rule::unique('people')->ignore($primaryKey)],
            'type' => ['required'],
            'status' => ['required'],
            'own_transport' => ['required'],
            'district' => ['required', 'max:30'],
            'birth_date' => ['required', 'date'],
            'state_registration' => ['nullable', 'max:20'],
            'phone' => ['required', 'max:15'],
            'zip_code' => ['required', 'max:9'],
            'city_id' => ['required'],
            'profession_id' => ['required'],
            'street' => ['required', 'max:60'],
            'contact_phone' => ['nullable', 'max:15'],
            'contact_email' => ['nullable', 'max:89'],
            'contact' => ['nullable', 'max:120'],
            'bank_id' => ['nullable'],
            'agency' => ['nullable', 'max:13'],
            'account' =>  ['nullable', 'max:18'],
            'account_type' => ['nullable']
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
