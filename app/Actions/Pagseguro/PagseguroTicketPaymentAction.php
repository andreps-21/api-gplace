<?php

namespace App\Actions\Pagseguro;

use App\Models\City;
use App\Models\Order;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class PagseguroTicketPaymentAction
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
            'reference_id' => $order->code_payment,
            'description' => "Cobrança do pedido {$order->code} - {$order->store->name}",
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
            'amount' => [
                'value' => intval(
                    strval(floatval(
                        preg_replace("/[^0-9.]/", "", str_replace(',', '.', $order->total))
                    ) * 100)
                ),
                'currency' => 'BRL',
            ],
            'items' => $items,
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
            'charges' => [
                [
                    'reference_id' => $order->code_payment,
                    'description' => "Cobrança do pedido {$order->code} - {$order->store->name}",
                    'amount' => [
                        'value' => intval(
                            strval(floatval(
                                preg_replace("/[^0-9.]/", "", str_replace(',', '.', $order->total))
                            ) * 100)
                        ),
                        "currency" => "BRL"
                    ],
                    'payment_method' => [
                        'type' => 'BOLETO',
                        'boleto' => [
                            'due_date' => today()->addDays(5)->format('Y-m-d'),
                            'instruction_lines' => [
                                'line_1' => "Pedido: {$order->code}",
                                'line_2' => 'Via Pagseguro'
                            ],
                            'holder' => [
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
                                'address' => [
                                    'street' => $request->address['street'],
                                    'number' => $request->address['number'],
                                    'complement' => $request->address['complement'],
                                    'locality' => $request->address['district'],
                                    'region' => $city->state->title,
                                    'city' => $city->title,
                                    'region_code' => $city->state->letter,
                                    'country' => "BRA",
                                    'postal_code' => $request->address['zip_code']
                                ]
                            ]
                        ],
                    ]
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
            'ticket_url' => $checkout['charges'][0]['links'][0]['href'],
            'barcode' => $checkout['charges'][0]['payment_method']['boleto']['barcode'],
            'status' => $checkout['charges'][0]['status'],
            'created_at' => $checkout['created_at'],
            'payment_response' => $checkout['charges'][0]['payment_response'],
            'payed' => $checkout['charges'][0] == 'PAID'
        ];

        return $result;
    }
}
