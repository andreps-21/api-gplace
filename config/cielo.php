<?php

return [
    /* DEFINE SE SERÁ UTILIZADO O AMBIENTE DE TESTES */
    'use-sandbox' =>  env('CIELO_SANDBOX', true),

    /*
     * Coloque abaixo as informações do seu cadastro no Cielo
     */
    'credentials' => [
        'merchant_id' => env('CIELO_MERCHANT_ID', null),
        'merchant_key' => env('CIELO_MERCHANT_KEY', null),
    ],


    /*
     * ATENÇÃO: Não altere as configurações abaixo
     * */
    'host' => [
        'sandbox' => [
            'transaction' => 'https://apisandbox.cieloecommerce.cielo.com.br',
            'query' => 'https://apiquerysandbox.cieloecommerce.cielo.com.br'
        ],
        'production' => [
            'transaction' => 'https://api.cieloecommerce.cielo.com.br',
            'query' => 'https://apiquery.cieloecommerce.cielo.com.br'
        ],
    ],
    'url' => [
        'checkout' => '/1/sales/'
    ],
];
