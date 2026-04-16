@component('mail::message')

# Pedido Cancelado!

Olá {{ $order->customer->people->name }}!

Informamos que o seu pedido <b>#{{ $order->code }}</b>  foi cancelado. Abaixo listamos os possíveis motivos para o cancelamento.

- Falhas na comunicação com a instituição bancária(operadora de cartão). Confira se digitou corretamente todos os dados do cartão e de cadastro ou entre em contato com sua operadora de cartão para mais informações.

- Não identificamos o pagamento do seu boleto no prazo e o seu pedido foi cancelado.

Para continuar sua compra, valide os itens acima. Você também pode realizar um novo pedido em nosso [site]({{$settings->portal_url}}). Estamos prontos para te atender.


## RESUMO DO PEDIDO
<b>#{{ $order->code }}</b>

Data do pedido: {{ carbon($order->purchase_date)->format('d/m/Y H:i') }} <br><br>
Total do pedido: R$ {{ floatToMoney($order->total) }} <br><br>
Forma de Pagamento: {{ $order->payment->description }} <br><br>

<p>Local de entrega:
@if ($order->type == 1)
    {{ $order->address->street }},
    {{ $order->address->complement }},
    {{ $order->address->district }},
    {{ $order->address->zip_code }},
    {{ $order->address->city->title }} - {{ $order->address->city->state->letter }},
    {{ $order->address->reference }}
@else
    Retirada na loja
@endif
</p>

## Estamos aqui para ajudar
Entre em contato através do nosso [whatsapp](https://api.whatsapp.com/send?phone=55{{$settings->phone}}). ou acesse [{{$settings->portal_url}}]({{$settings->portal_url}}) para mais
informações.


Obrigada por escolher a {{ $settings->name }}!

@endcomponent
