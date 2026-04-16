<html>
  
<head>
    <link rel='stylesheet'
        href='https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.0/css/bootstrap.min.css' />
    <title>Pedido</title>
    <style type="text/css">
        @import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,700;1,400&display=swap');
         
        .table-header
        {
            border-bottom: 1px solid #000;
            height: 200px;
            padding: 20px;
        }

        .card-body
        {
            padding-left: 20px;
            padding-right: 20px;
        }

        .card-user, .order, .card-order, .obs
        {
            border-bottom: 0.5px solid #000;
            padding: 20px;
        }

        table
        {
            width: 40%;
        }

        .table-order
        {
            width: 80%;
        }

        .table-item
        {
            width: 80%;
        }

        .tr
        {
            background: #e2dcdc;
        }

        .th-text
        {
            font-weight: normal;
        }

        .table-order > th
        {
            font-weight: normal;
        }

        .right
        { 
            margin-left: 60%;
        }

        .card-order-nav
        {
            margin-top: -7%;
        }

        .kcc-label
        {
            margin-left: 15%;
            margin-top: -6%;
        }

        img
        {
            max-width: 150px;
            max-height: 150px;
        }

        .img-footer
        {
            max-width: 20px;
            max-height: 20px;
            margin-left: 60%;
        }

    </style>
</head>

<body>
    <div class="table-header">
      
        <div class="img">
           <img src="{{ public_path('images/logo.png') }}" />
        </div>

        <table class="kcc-label">
            <thead>
                <tr>
                    <th>KCC DE MÓVEIS LTDA</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>37.418.167/0001-87</td></tr>
                <tr><td>kccteste@gmail.com / (63) 9 9999-9999</td></tr>
                <tr><td>Avenida Teot. Segurado Qd 501 Sul (Acsu 50),999.</td></tr>
                <tr><td>77016-002, Centro, Palmas-To</td></tr>
            </tbody>
        </table>

        <table class="right card-order-nav">
            <thead>
                <tr>
                    <th>Pedido:</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Dt. Pedido: </td></tr>
                <tr><td>Emissão: </td></tr>
            </tbody>
        </table>
                           
    </div>
        
    <div class="card-body" >
        <div class="card-user">
            <table>
                <thead>
                    <tr>
                        <th>Dados do Cliente</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Maria</td><td>  Cpf: </td></tr>
                    <tr><td>kccteste@gmail.com / (63) 9 9999-9999</td></tr>
                    <tr><td>Avenida Teot. Segurado Qd 501 Sul (Acsu 50),999. 77016-002, Centro, Palmas-To</td></tr>
                </tbody>
            </table>
        </div>

        <div class="card-order">
            <p><strong>Itens do Pedido</strong></p>
            <table class="table-item">
                <thead>
                    <tr class="tr">
                        <th>Item</th>
                        <th>Produto</th>
                        <th>Cod ERP</th>
                        <th>Qtde</th>
                        <th>Preço un (R$)</th>
                        <th>Total (R$)</th>
                    </tr>
                </thead>
                <tbody>
                    <td>Maria</td>
                    <td>Maria</td>
                    <td>Maria</td>
                    <td>Maria</td>
                    <td>Maria</td>
                    <td>Maria</td>
                </tbody>
                <tbody>
                    <td class="tr">Totais</td>
                    <td class="tr"></td>
                    <td class="tr"></td>
                    <td class="tr">Maria</td>
                    <td class="tr">Maria</td>
                    <td class="tr">Maria</td>
                </tbody>
            </table>
        </div>

        <div class="order">
            <p><strong>Resumo do pedido</strong></p>
            <table class="table-order">
                <thead>
                    <tr>
                        <th class="th-text">Total de produtos: 500</th>
                        <th class="th-text">Desconto: 100</th>
                        <th class="th-text">Frete: 40</th>
                        <th class="th-text">Total do Pedido: 540</th>
                    </tr>
                    <tr>
                        <th class="th-text">Pagamento: 500</th>
                        <th class="th-text">Codinção Pagamento: 100</th>      
                    </tr>
                    <tr>
                        <th class="th-text">Local entrega: Avenida Teot. Segurado Qd 501 Sul (Acsu 50),999. 77016-002, Centro, Palmas-To</th> 
                    </tr>
                </thead>
                
            </table>
        </div>

        <div class="obs">
            <strong>Observação: </strong>
        </div>
        <div class="footer">
            <p>www.dix.digital</p>
            <div class="img-footer">
                <p> Desenvolvido por:</p>
                <img src="{{ public_path('images/logoDix.png') }}"/> 
            </div>
        </div>
    </div>

</body>

</html>
  
