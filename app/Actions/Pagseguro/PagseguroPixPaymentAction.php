<?php

namespace App\Actions\Pagseguro;

use App\Models\City;
use App\Models\Order;
use App\Models\Setting;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PagseguroPixPaymentAction
{
    public function execute(Order $order, Request $request, bool $isSandbox, string $token)
    {
        $url = config('laravelpagseguro.host.production');

        if ($isSandbox) {
            $url = config('laravelpagseguro.host.sandbox');
        }

        $city = City::with('state')->find($request->address['city']);

        $items = [];
        foreach ($order->items as $item) {
            $items[] = [
                'reference_id' => $item->code,
                'name' => $item->product->commercial_name,
                'quantity' => intval(
                    strval(floatval(
                        preg_replace("/[^0-9.]/", "", str_replace(',', '.', $item->quantity))
                    ) * 100)
                ),
                'unit_amount' => intval(
                    strval(floatval(
                        preg_replace("/[^0-9.]/", "", str_replace(',', '.', $item->value_unit))
                    ) * 100)
                ),
            ];
        }

        $data = [
            'customer' => [
                'name' => $request->customer['name'],
                'email' => $request->customer['email'],
                'tax_id' => $request->customer['nif'],
                'phones' => [
                    [
                        'country' => '55',
                        'area' => $request->customer['ddd'],
                        'number' => $request->customer['phone'],
                        'type' => 'MOBILE'
                    ]
                ],
            ],
            'items' => $items,
            "qr_codes" => [
                [
                    'amount' => [
                        'value' => intval(
                            strval(floatval(
                                preg_replace("/[^0-9.]/", "", str_replace(',', '.', $order->total))
                            ) * 100)
                        ),
                    ]
                ]
            ],
            'shipping' => [
                'address' => [
                    'street' => $request->address['street'],
                    'number' => $request->address['number'],
                    'complement' => $request->address['complement'],
                    'locality' => $request->address['district'],
                    'city' => $city->title,
                    'region_code' => $city->state->letter,
                    'country' => "BRA",
                    'postal_code' => $request->address['zip_code']
                ]
            ],
        ];

        if (app()->environment('production')) {
            $data['notification_urls'] = [config('app.url') . "/api/v1/pagseguro/notification"];
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer " . $token,
            'x-api-version' => '4.0'
        ])->post(
            "{$url}/orders",
            $data
        );

        if ($response->failed()) {
            throw new Exception($response->body());
        }

        $checkout = $response->json();

        $result = [
            'id' => $checkout['id'],
            'ticket_url' => null,
            'status' => "PENDING",
            'created_at' => $checkout['created_at'],
            'payment_response' => $checkout['qr_codes'],
            'payed' => false,
        ];

        return $result;
    }
}
