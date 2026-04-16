<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\Person;
use Illuminate\Support\Facades\Validator;
use App\Rules\CpfCnpj;
use Illuminate\Support\Facades\DB;
use App\Mail\SendEmailCreatedTenant;
use App\Models\Role;
use App\Models\SizeImage;
use Illuminate\Validation\Rule;
use App\Models\User;
use Illuminate\Support\Str;

class TenantsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:tenants_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:tenants_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:tenants_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:tenants_delete', ['only' => ['destroy']]);

    }

    public function index(Request $request)
    {
           $data =  Tenant::person()
            ->orderBy('name')
            ->when(!empty($request->search), function ($q) use ($request) {
                return  $q->where(function ($quer) use ($request) {
                    return $quer->where('name', 'LIKE', "%$request->search%")
                        ->orWhereRaw('(replace(replace(replace(nif, ".", ""), "/", ""), "-", "") like "%' . clean($request->search) . '%")');
                });
            })
            ->when(!empty($request->due_date), function ($q) use ($request) {
                return $q->whereMonth('tenants.due_date', $request->due_date);
            })
            ->when(!empty($request->dt_accession), function ($q) use ($request) {
                return $q->whereDate('tenants.dt_accession', $request->dt_accession);
            })
            ->when(!empty($request->due_day), function ($q) use ($request) {
                return $q->where('tenants.due_day', $request->due_day);
            })
            ->when(!empty($request->status), function ($q) use ($request) {
                return $q->where('tenants.status', $request->status);
            })
            ->when(!empty($request->signature), function ($q) use ($request) {
                return $q->where('tenants.signature', $request->signature);
            })
            ->when(!empty($request->start_date), function ($q) use ($request) {
                return $q->whereDate('tenants.created_at', '>=', $request->start_date);
            })
            ->when(!empty($request->end_date), function ($q) use ($request) {
                return $q->whereDate('tenants.created_at', '<=', $request->end_date);
            })
            ->paginate(10);

        return view('tenants.index', compact('data'));
    }

    public function create()
    {
        return view('tenants.create');
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
            $inputs['value'] = moeda($request->value);

            $person = Person::updateOrCreate(
                ['nif' => $request->nif],
                $inputs
            );

            $inputs['person_id'] = $person->id;
            $tenant = Tenant::updateOrCreate(
                ['person_id' => $person->id],
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

            $role = Role::query()->with('permissions')->where('name', '=', 'contratante')->first();
            $newRole = $role->replicate();
            $newRole->created_at = now();
            $newRole->updated_at = now();
            $newRole->name = "contratante-" . Str::slug($tenant->people->name);
            $newRole->save();
            $newRole->permissions()->sync($role->permissions);
            $user->roles()->detach();
            $user->roles()->attach($newRole->id);
        });

        return redirect()->route('tenants.index')
            ->withStatus('Registro adicionado com sucesso.');
    }

    public function show($id)
    {
        $item = Tenant::person()->findOrFail($id);

        return view('tenants.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Tenant::person()->with('sizeImages')->findOrFail($id);

        $sizeImages = SizeImage::where('is_enabled', true)->get(['name', 'id']);

        return view('tenants.edit', compact('item', 'sizeImages'));
    }

    public function update(Request $request, $id)
    {
        $item = Tenant::findOrFail($id);

        Validator::make(
            $request->all(),
            $this->rules($request, $item->person_id)
        )->validate();

        DB::transaction(function () use ($request, $item) {
            $inputs = $request->all();
            $inputs['value'] = moeda($request->value);
            $inputs['value'] = moeda($request->value);
            $inputs['value'] = moeda($request->value);

            $item->fill($inputs)->save();

            $person = Person::find($item->person_id);
            $person->fill($inputs)->save();

            $user = User::where('person_id', $item->person_id)->first();

            if ($user) {
                $user->is_enabled = $inputs['status'] == 1;
                $user->save();
            }
        });

        return redirect()->route('tenants.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = Tenant::findOrFail($id);

        try {
            if(!empty($item))
                $item->delete();

            return redirect()->route('tenants.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('tenants.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }


    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'name' => ['required', 'max:30'],
            'formal_name' => ['required','max:60'],
            'nif' => ['required', 'max:20', new CpfCnpj, Rule::unique('people')->ignore($primaryKey)],
            'city_id' => ['required'],
            'email' => ['required', 'max:89', Rule::unique('people')->ignore($primaryKey)],
            'phone' => ['required', 'max:15'],
            'street' => ['required','max:120'],
            'contact_phone' => ['max:15'],
            'contact' => ['nullable','max:120'],
            'status' => ['required'],
            'dt_accession' => ['required','date'],
            'due_date' => ['required'],
            'due_day' => ['required'],
            'value' => ['required'],
            'signature' => ['required'],
        ];
        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }

}
