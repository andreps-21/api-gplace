<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Validator;
use App\Models\Partner;
use App\Models\Product;
use App\Models\Person;
use App\Models\Profession;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceArea;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class PartnerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:partners_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:partners_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:partners_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:partners_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = Partner::person()->orderBy('name')->paginate(10);

        return view('partners.index', compact('data'));
    }

    public function create()
    {
        $professions = Profession::get();
        $areas = ServiceArea::get();
        $products =  Product::with('brand','paymentMethods','family')->orderBy('description')->get();
        return view('partners.create', compact('professions','areas','products'));
    }

    public function store(Request $request)
    {
        Validator::make(
            $request->all(),
            $this->rules($request)
        )->validate();

        DB::transaction(function () use ($request) {
            $inputs = $request->all();

            $person = Person::updateOrCreate(
                ['nif' => $request->nif],
                $inputs
            );

            $inputs['person_id'] = $person->id;

            $item = Partner::updateOrCreate(
                ['person_id' => $inputs['person_id']],
                $inputs
            );

        });

        return redirect()->route('partners.index')
            ->withStatus('Registro adicionado com sucesso.');
    }

    public function show($id)
    {
        $item = Partner::findOrFail($id);

        return view('partners.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Partner::person()->findOrFail($id);

        $professions = Profession::get();
        $areas = ServiceArea::get();
        $products =  Product::with('brand','paymentMethods','family')->orderBy('description')->get();

        return view('partners.edit', compact('item','professions','areas','products'));
    }

    public function update(Request $request, $id)
    {
        $item = Partner::findOrFail($id);
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

        return redirect()->route('partners.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = Partner::findOrFail($id);

        try {
            $item->delete();
            return redirect()->route('partners.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('partners.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [

            'name' => ['required', 'max:50'],
            'formal_name' => ['max:30'],
            'email' => ['required', 'max:45', Rule::unique('users')->ignore($primaryKey)],
            'nif' => ['required', 'max:14'],
            'status' => ['required'],
            'profession_id'=> ['required'],
            'service_area_id'=> ['required'],
            'product_id'=> ['required'],
            'own_equipment'=> ['required'],
            'own_transport'=> ['required'],
            'district'=> ['required','max:30'],
            'birth_date'=> ['required', 'date'],
            'is_enabled'=> ['required'],
            'state_registration'=> ['required','max:25'],
            'phone' => ['max:15'],
            'zip_code' => ['max:9'],
            'street' => ['required', 'max:60'],
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
