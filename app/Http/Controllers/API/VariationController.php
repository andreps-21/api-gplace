<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Variation;
use Illuminate\Support\Facades\DB;

class VariationController extends Controller
{
    public function destroy($id)
    {
        $item = Variation::findOrFail($id);

        try {
            DB::beginTransaction();
            $item->delete();
            DB::commit();
            return ['status' => '200', 'mes' =>'Registro deletado com sucesso.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return  ['status' => '500', 'mes' =>'Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.'];
        }
    }
}
