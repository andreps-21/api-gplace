<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Validator;
use App\Models\ProductReview;
use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Rules\CpfCnpj;

class ProductReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:product-reviews_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:product-reviews_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:product-reviews_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:product-reviews_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = ProductReview::orderBy('name')->paginate(10);

        return view('product-reviews.index', compact('data'));
    }

    public function create()
    {
        $products = Product::get();
        return view('product-reviews.create', compact('products'));
    }

    public function store(Request $request)
    {
        Validator::make(
            $request->all(),
            $this->rules($request)
        )->validate();
        $request['user_id']=1;

        ProductReview::create($request->all());

        return redirect()->route('product-reviews.index')
            ->withStatus('Registro adicionado com sucesso.');
    }

    public function show($id)
    {
        $item = ProductReview::findOrFail($id);

        return view('product-reviews.show', compact('item'));
    }

    public function edit($id)
    {
        $item = ProductReview::findOrFail($id);
        $products = Product::get();

        return view('product-reviews.edit', compact('item','products'));
    }

    public function update(Request $request, $id)
    {
        $item = ProductReview::findOrFail($id);
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


        return redirect()->route('product-reviews.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = Customer::findOrFail($id);

        try {
            $item->delete();
            return redirect()->route('product-reviews.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('product-reviews.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'product_id'=> ['required'],
            'note'=> ['required'],
            'comment'=> ['required'],
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
