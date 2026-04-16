@component('mail::message')
# Novo Pedido!

Olá!

Você tem um novo pedido!

Veja os detalhes do pedido clicando no botão abaixo, não deixe seu cliente esperando.

@component('mail::button', ['url' => route('orders.show', $order->id) ])
Visualizar Pedido
@endcomponent

@endcomponent
