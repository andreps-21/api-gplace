<?php

namespace App\Http\Controllers\API;

use App\Enums\FreightType;
use App\Models\Freight;
use App\Models\Parameter;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Settings;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Pool;

class CalcFreightController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, Settings $settings)
    {
        $validator = $this->getValidationFactory()
            ->make(
                $request->all(),
                $this->rules($request)
            );

        if ($validator->fails()) {
            return $this->sendError('Erro de Validação.', $validator->errors()->toArray(), 422);
        }

        $settings = Setting::where('store_id', $request->get('store')['id'])->first();

        if (!$settings) {
            throw new Exception("Configuração da loja não cadastrada.");
        }

        if (!$settings->freight_info) {
            throw new Exception("Pagamento não configurado para essa loja");
        }

        $data = [];

        $origin = preg_replace('/[^0-9]/', '', $settings->freight_info['zip_code']);

        if ($settings->freight_gateway == FreightType::CORREIOS) {

            $totalWeight = 0;
            $totalCm = 0;
            foreach ($request->products as $product) {
                $item = Product::find($product['id']);
                $altura = $item->height;
                $largura = $item->width;
                $comprimento = $item->length;
                $quantity = $product['quantity'];
                $row_peso = $item->weight * $quantity;
                $row_cm = ($altura * $largura * $comprimento) * $quantity;

                $totalWeight += $row_peso;
                $totalCm += $row_cm;
            }

            $raiz_cubica = round(pow($totalCm, 1 / 3), 2);

            $comprimento =  $raiz_cubica < 16 ? 16 : $raiz_cubica;
            $altura = $raiz_cubica < 2 ? 2 : $raiz_cubica;
            $largura = $raiz_cubica < 11 ? 11 : $raiz_cubica;
            $peso = $totalWeight < 0.3 ? 0.3 : $totalWeight;
            $diametro = hypot($comprimento, $largura);

            $token = $settings->freight_info['refresh_token'];

            $services = [
                4090 => 'SEDEX',
                4000 => 'PAC',
            ];
            
            if ($token==null || Carbon::parse($settings->freight_info['token_expiration_date']) < now()) {
                 
                 $httpClient = Http::withHeaders(['Accept' => 'application/json','Content-Type' => 'application/json',])
                 ->withBasicAuth($settings->freight_info['client_id'], $settings->freight_info['token']);
                 
                 $response = $httpClient->post("https://api.correios.com.br/token/v1/autentica/cartaopostagem", [
                     'numero' => $settings->freight_info['client_secret'], 
                    ]);
                    
                if ($response->failed()) {
                    return $this->sendError('Não foi possivel gerar o Token temporário.', json_decode($response->body()), 403);
                }

                $response = $response->json();

                $token = $response['token']; 
                $freightInfo = $settings->freight_info;

                $freightInfo['refresh_token'] = $token;
                $freightInfo['token_expiration_date'] = $response['expiraEm'];

                $settings->freight_info = $freightInfo;
                try{
                    $settings->save();
                }catch(Exception $e){
                    return $this->sendError('Erro no salvamento do Token temporário.', $e->getMessage(), 403);
                }
            }

            foreach ($services as $key => $value) {
                $urlprice[$key] = "https://api.correios.com.br/preco/v1/nacional/" . $key . "?cepDestino=" . $request->zip_code . "&cepOrigem=" . $origin . "&psObjeto=" . $peso . "&comprimento=" . $comprimento . "&largura=" . $largura . "&altura=" . $altura . "&diametro=" . $diametro;
                $urldeadline[$key] = "https://api.correios.com.br/prazo/v1/nacional/" . $key . "?cepOrigem=" . $origin . "&cepDestino=" . $request->zip_code;
                
                $responses = Http::pool(fn (Pool $pool) => [
                    $pool->as('price')->withToken($token)->withHeaders(['Accept' => 'application/json','Content-Type' => 'application/json'])->get($urlprice[$key]),
                    $pool->as('deadline')->withToken($token)->withHeaders(['Accept' => 'application/json','Content-Type' => 'application/json'])->get($urldeadline[$key]),
                ]);

                if ($responses['price']->failed()) {
                    return $this->sendError('Não foi possivel buscar os preços.', json_decode($response['price']->body()), 403);
                }

                if ($responses['deadline']->failed()) {
                    return $this->sendError('Não foi possivel buscar os prazos.', json_decode($response['deadline']->body()), 403);
                }
                
                $price[$key] = $responses['price']->json();
                $deadline[$key] = $responses['deadline']->json();
            }

            
            foreach($services as $key => $value){
                $_arr_ = array();
                
                $_arr_['code'] = $price[$key]['coProduto'];
                $_arr_['value'] = $price[$key]['pcFinal'];
                $_arr_['time'] = $deadline[$key]['prazoEntrega'] . ' Dias';
                $_arr_['description'] = $value;

                $data[] = $_arr_;
            }

            if (empty($data)) {
                return $this->sendError('Não foi possível calcular o frete.', isset($xml) ? $xml->cServico : [], 403);
            }
        } else if ($settings->freight_gateway == FreightType::MELHOR_ENVIO) {

            $isSandbox = boolval($settings->freight_info['sandbox']);
            $url = config('laravelmelhorenvio.host.production');

            if ($isSandbox) {
                $url = config('laravelmelhorenvio.host.sandbox');
            }

            $products = [];

            foreach ($request->products as $product) {
                $item = Product::find($product['id']);

                $object['height'] = $item->height;
                $object['width'] = $item->width;
                $object['length'] = $item->length;
                $object['weight'] = $item->weight;
                $object['quantity'] = $product['quantity'];
                $object['insurance_value'] =
                    isset($product['insurance_value']) ? $product['insurance_value'] : 0;

                array_push($products, $object);
            }

            $data = [
                "from" => [
                    "postal_code" => $origin
                ],
                "to" => [
                    "postal_code" => $request->zip_code
                ],
                "products" => $products
            ];

            // if (Carbon::parse($settings->freight_info['token_expiration_date']) < Carbon::now()) {
            //     $this->refreshAuthenticationToken($settings, $url);
            // }

            $response = Http::withToken($settings->freight_info['token'])
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'User-Agent' => $settings->name . " (hd@dixbpo.com)"
                ])->post(
                    "{$url}" . config('laravelmelhorenvio.url.calculate-freight'),
                    $data
                );

            if ($response->failed()) {
                return $this->sendError('Não foi possível calcular o frete.', json_decode($response->body()), 403);
            }

            $data = json_decode($response->body());
        } else if ($settings->freight_gateway == FreightType::OWNER) {
            $regionShipping = Freight::where('zip_code_start', '<=', $request->zip_code)
                ->where('zip_code_end', '>=', $request->zip_code)
                ->first();

            if (!$regionShipping) {
                return $this->sendError("Nessa região a compra é personalizada, ou seja, compre na loja!", ['error' => true], 403);
            }

            $valueFreight = 0;

            if ($regionShipping->value_freight_fix > 0) {

                $valueFreight = $regionShipping->value_freight_fix;
            } else if ($regionShipping->percentage > 0) {

                $valueFreight = ($request->value * $regionShipping->percentage) / 100;
            }

            if (
                $regionShipping->free_shipping_sales > 0
                && $request->value >= $regionShipping->free_shipping_sales
            ) {

                $valueFreight = 0;
            }

            $data = [
                'type' => "Frete Próprio",
                'value' => number_format($valueFreight, 2),
            ];
        }



        return $this->sendResponse($data);
    }



    private function refreshAuthenticationToken($settings, $url)
    {
        $data = [
            "grant_type" => "refresh_token",
            "client_id" => $settings->freight_info['client_id'],
            "client_secret" => $settings->freight_info['client_secret'],
            "refresh_token" => $settings->freight_info['refresh_token']
        ];

        $response = Http::withToken($settings['access_token'])
            ->withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => $settings->name . " (hd@dixbpo.com)"
            ])->post(
                "{$url}/oauth/token",
                $data
            );

        if ($response->failed()) {
            Log::debug(json_encode($response->body()));
            return;
        }

        $freight_info = $settings->freight_info;

        $expirationDate = $response['expires_in'];

        $freight_info['token'] = $response['access_token'];
        $freight_info['refresh_token'] = $response['refresh_token'];
        $freight_info['token_expiration_date'] = Carbon::now()->addSeconds($expirationDate)->format('Y-m-d');

        $settings->freight_info = $freight_info;
        $settings->save();
    }


    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'products' => ['required', 'array'],
            'products.*.id' => ['required', 'exists:products,id'],
            'products.*.quantity' => ['required', 'integer'],
            'products.*.insurance_value' => ['nullable'],
            'zip_code' => ['required', 'max:8']
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
