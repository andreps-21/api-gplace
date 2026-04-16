@component('mail::message')

# Pedido disponível para retirada!

Olá {{ $order->customer->people->name }}!

<p>Seu pedido <b>#{{ $order->code }}</b>  já está pronto para retirada em nossa loja, localizada no endereço:
{{ $settings->address }},
{{ $settings->number }}
{{ $settings->complement }},
{{ $settings->district }},
{{ $settings->zip_code }},
{{ $settings->city->title }} - {{ $settings->city->state->letter }}.
</p>



@component('mail::button', ['url' => "{$settings->portal_url}/perfil/pedidos"])
    ACOMPANHE SEU PEDIDO
@endcomponent

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
