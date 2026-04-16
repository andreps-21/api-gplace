<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Person;
use App\Models\Role;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Department;
use App\Models\Store;
use App\Rules\CpfCnpj;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:users_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:users_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:users_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:users_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data =  User::person()
            ->when(!empty($request->search), function ($q) use ($request) {
                return  $q->where(function ($query) use ($request) {
                    return $query->where('users.name', 'LIKE', "%$request->search%")
                        ->orWhere('users.email', 'LIKE', "%$request->search%")
                        ->orWhere('people.nif', 'LIKE', "%$request->search%");
                });
            })
            ->when(!empty($request->start_date), function ($q) use ($request) {
                return $q->whereDate('users.created_at', $request->start_date);
            })
            ->when(session()->exists('store'), function ($query) {
                return $query->whereHas('stores', function ($q) {
                    $q->where('store_id', session('store')['id']);
                });
            })
            ->when(session()->exists(['tenant']), function ($query) {
                return $query->whereHas('stores', function ($q) {
                    $q->where('tenant_id', session('tenant')['id']);
                });
            })
            ->orderBy('users.name')
            ->paginate(10);

        return view('users.index', compact('data'));
    }

    public function create()
    {
        $roles = Role::orderBy('description')
            ->when(session()->exists('tenant'), function ($query) {
                $query->where('tenant_id', session('tenant')['id']);
            })
            ->when(!session()->exists('tenant'), function ($query) {
                $query->whereNull('tenant_id');
            })
            ->select(
                'roles.*',
                DB::raw('CASE WHEN roles.tenant_id IS NULL THEN roles.description ELSE CONCAT(people.formal_name, " - ", roles.description) END AS name_description')
            )
            ->leftJoin('tenants', 'tenants.id', '=', 'roles.tenant_id')
            ->leftJoin('people', 'people.id', '=', 'tenants.person_id')
            ->get()
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name_description' => $role->name . ' - ' . $role->name_description,
                ];
            });

        $stores = Store::person()
            ->orderby('name')
            ->when(session()->exists('tenant'), function ($query) {
                $query->whereIn('stores.id', collect(session('stores'))->pluck('id')->all());
            })
            ->when(session()->exists('store'), function ($query) {
                $query->where('stores.id', session('store')['id']);
            })
            ->get();

        return view('users.create', compact('roles',  'stores'));
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
            $inputs['password'] = bcrypt($request->input('password'));

            $user = User::updateOrCreate(
                ['person_id' => $inputs['person_id']],
                $inputs
            );

            $user->roles()->sync($request->role);

            if ($request->stores) {
                $user->stores()->syncWithoutDetaching($request->stores);
            } else if (session()->exists('store')) {
                $user->stores()->syncWithoutDetaching(session('store')['id']);
            }
        });

        return redirect()->route('users.index')
            ->withStatus('Registro adicionado com sucesso.');
    }

    public function show($id)
    {
        $item = User::person()->with('roles')->findOrFail($id);

        return view('users.show', compact('item'));
    }

    public function edit($id)
    {
        $roles = Role::orderBy('description')
            ->when(session()->exists('tenant'), function ($query) {
                $query->where('tenant_id', session('tenant')['id']);
            })
            ->when(!session()->exists('tenant'), function ($query) {
                $query->whereNull('tenant_id');
            })
            ->select(
                'roles.*',
                DB::raw('CASE WHEN roles.tenant_id IS NULL THEN roles.description ELSE CONCAT(people.formal_name, " - ", roles.description) END AS name_description')
            )
            ->leftJoin('tenants', 'tenants.id', '=', 'roles.tenant_id')
            ->leftJoin('people', 'people.id', '=', 'tenants.person_id')
            ->get()
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name_description' => $role->name . ' - ' . $role->name_description,
                ];
            });

        $item = User::person()->with('roles')->findOrFail($id);

        $stores = Store::person()
            ->orderby('name')
            ->when(session()->exists('tenant'), function ($query) {
                $query->whereIn('stores.id', collect(session('stores'))->pluck('id')->all());
            })
            ->when(session()->exists('store'), function ($query) {
                $query->where('stores.id', session('store')['id']);
            })
            ->get();

        return view('users.edit', compact(
            'item',
            'roles',
            'stores'
        ));
    }

    public function update(Request $request, $id)
    {

        $item = User::with('stores')->findOrFail($id);

        Validator::make(
            $request->all(),
            $this->rules($request, $item->person_id)
        )->validate();

        DB::transaction(function () use ($request, $item) {
            $inputs = $request->except(['nif', 'email']);

            $item->fill($inputs)->save();

            $people = Person::find($item->person_id);
            $people->fill($inputs)->save();

            if ($request->role) {
                $item->roles()->sync($request->role);
            }

            $stores = [];

            if ($request->stores){
                $stores = $request->stores;
            }

            $item->stores()->sync($stores);
        });

        return redirect()->route('users.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = User::findOrFail($id);

        if (auth()->id() != $item->id) {
            try {
                $item->delete();
                return redirect()->route('users.index')
                    ->withStatus('Registro deletado com sucesso.');
            } catch (\Exception $e) {
                return redirect()->route('users.index')
                    ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
            }
        } else {
            return redirect()->route('users.index')
                ->withError('Você não tem permissão para excluir esse usuário.');
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'name' => ['required', 'max:120'],
            'nif' => ['required', 'max:14', new CpfCnpj],
            'city_id' => ['required'],
            'email' => ['required', 'max:89', Rule::unique('people')->ignore($primaryKey)],
            'phone' => ['required', 'max:15'],
            'role' => ['required'],
            'password' => ['sometimes', 'required', 'min:8', 'confirmed'],
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
