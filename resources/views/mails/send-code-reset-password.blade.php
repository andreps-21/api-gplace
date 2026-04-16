@component('mail::message')
<h1>Olá, recebemos a sua solicitação para redefinir a senha da sua conta.</h1>
<p>Informe o código na tela para alterar a sua senha:</p>

@component('mail::panel')
{{ $code }}
@endcomponent

<p>Para a sua segurança, a duração do código é de 20 minutos.</p>
@endcomponent
