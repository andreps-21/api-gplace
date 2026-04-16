<?php

namespace App\Actions;

use App\Actions\Maxdata\GetClientMaxdata;
use App\Actions\Maxdata\GetProductMaxdata;
use App\Actions\Maxdata\MaxDataToken;
use App\Models\Setting;
use App\Models\Store;
use Exception;

class IntegrationAction
{
    public function execute(int $storeId)
    {
        $store = Store::findOrFaiL($storeId);

        $settings = Setting::with('erps')->where('store_id', $storeId)->first();

        if (!$settings) {
            throw new Exception("Configuração da loja não cadastrada.");
        }

        if ($settings->erps->isEmpty()) {
            throw new Exception("Integração não configurada para essa loja");
        }

        $maxData = $settings->erps->firstWhere('description', 'MAXDATA');

        try {
            if($maxData){
                $action = new MaxDataToken();

                $token = $action->execute(
                    $maxData->pivot->url,
                    $maxData->pivot->terminal,
                    $maxData->pivot->id_emp,
                );

                $getProductMaxdata = new GetProductMaxdata();

                $getProductMaxdata->execute(
                    $maxData->pivot->url,
                    $token['token'],
                    $maxData->pivot->id_emp,
                    $storeId,
                    $store->tenant_id,
                );
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
