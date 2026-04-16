<div class="row">
    <div class="col-12">
        <div class="painel-header">
            <div class="row">
                <div class="col-md-2">
                    {!!Form::text('code', 'N. Pedido')
                    ->attrs(['class' => 'number'])
                    ->required()
                    !!}
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="inp-purchase_date">Dt. Compra</label>
                        <input type="datetime-local" name="purchase_date" id="inp-purchase_date" class="form-control"
                            value="{{ old('purchase_date', isset($item) ? $item->purchase_date : null) }}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    {!!Form::select('status', 'Status', [null => 'Selecione...'] + \App\Models\Order::status())
                    ->required()
                    !!}
                </div>
                <div class="col-md-3">
                    {!!Form::text('tracking_code', 'Cod. Rastreio')
                     ->attrs(['maxlength' => 20])!!}
                </div>
                <div class="col-md-6">
                    {!!Form::select('customer_id', 'Cliente')
                    ->options($clients->prepend('Selecione', ''), 'info')
                    ->attrs(['class' => 'select2'])
                    ->required()
                    !!}
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs" style="margin-top:30px">
            <li class="nav-item">
                <a style="background-color:white" data-toggle="tab" class="nav-link active"
                    href="#home"><span>Itens</span></a>
            </li>
        </ul>

        <div class="tab-content">
            <div id="home" class="tab-pane fade show active">
                <div class="painel-body">
                    <div class="row">
                        <div class="table-responsive">
                            <table id="tabela" class="table table-dynamic">
                                <thead>
                                    <tr class="conteudo-th">
                                        <th class="op"></th>
                                        <th class="data">Produto/Serviço</th>
                                        <th  class="op">Un. Medida</th>
                                        <th class="values">Qtde</th>
                                        <th class="values">Vl.Unit</th>
                                        <th class="values">Desconto</th>
                                        <th class="values">Total do Item(R$)</th>
                                        <th class="values">IPI (R$)</th>
                                        <th class="values">ICMS (R$)</th>
                                        <th class="values">Pontos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="dynamic-form">
                                        <td>
                                            <button type="button" class="btn-sm btn-danger btn-remove"><i
                                                    class="fas fa-trash"></i></button>
                                        </td>
                                        <td>
                                            <div class="form-group" style="width:250px">
                                                <label for="product"></label>
                                                <select name="product[]" class="form-control product " required>
                                                    <option value="">Selecione</option>
                                                    @foreach ($products as $product)
                                                    <option value="{{ $product->id }}"
                                                        data-um="{{ $product->initials }}">
                                                        {{ $product->commercial_name." - ".$product->sku }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            {!!Form::text('um[]')
                                            ->readonly()
                                            ->required()
                                            !!}
                                        </td>
                                        <td>
                                            {!!Form::text('quantity[]')
                                            ->attrs(['class' => 'quantity'])
                                            ->required()
                                            !!}
                                        </td>
                                        <td>
                                            {!!Form::text('value_unit[]')
                                            ->attrs(['class' => 'money value'])
                                            ->required()
                                            !!}
                                        </td>
                                        <td>
                                            {!!Form::text('discount[]')
                                            ->attrs(['class' => 'money discount'])
                                            !!}
                                        </td>
                                        <td>
                                            {!!Form::text('total[]')
                                            ->attrs(['class' => 'money total'])
                                            ->readonly()
                                            ->required()
                                            !!}
                                        </td>
                                        <td>
                                            {!!Form::text('icms[]')
                                            ->attrs(['class' => 'money ipi'])
                                            !!}
                                        </td>
                                        <td>
                                            {!!Form::text('ipi[]')
                                            ->attrs(['class' => 'money icms'])
                                            !!}
                                        </td>
                                        <td>
                                            {!!Form::text('spots[]')
                                            ->attrs(['class' => 'money spots'])
                                            !!}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row increment mt-3">
                        <div class="col-12">
                            <button class="btn btn-success btn-add" type="button"><i class="fas fa-plus"></i>Adicionar
                                Item</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="painel-footer">
            <div class="row">
                <div class="col-md-2">
                    {!!Form::text('vl_amount', 'Total de produtos')
                    ->attrs(['class' => 'money'])
                    ->readonly()
                    ->required(true)
                    !!}
                </div>
                <div class="col-md-2">
                    {!!Form::text('vl_icms', 'ICMS')
                    ->attrs(['class' => 'money'])
                    ->readonly()
                    !!}
                </div>
                <div class="col-md-2">
                    {!!Form::text('vl_ipi', 'IPI')
                    ->attrs(['class' => 'money'])
                    ->readonly()
                    !!}
                </div>
                <div class="col-md-2">
                    {!!Form::text('vl_discount', 'Desconto')
                    ->attrs(['class' => 'money'])
                    ->readonly()
                    !!}
                </div>
                <div class="col-md-2">
                    {!!Form::text('vl_freight', 'Frete')
                    ->attrs(['class' => 'money'])!!}
                </div>
                <div class="col-md-2">
                    {!!Form::text('vl_total', 'Vl.Total do Pedido')
                    ->attrs(['maxlength' => 15])
                    ->readonly()
                    !!}
                </div>
                <div class="col-md-2">
                    {!!Form::text('vl_spots', 'Vl.Total de Pontos')
                    ->attrs(['maxlength' => 15])
                    ->readonly()
                    !!}
                </div>
                <div class="col-md-5">
                    {!!Form::select('payment_method_id', 'Formas de pagto')
                    ->options($payments->prepend('Selecione', ''), 'info')
                    ->attrs(['class' => 'select2'])
                    ->required()
                    !!}
                </div>
                <div class="col-md-5">
                    {!!Form::text('payment_condition', 'Cond. Pagamento')
                    ->attrs(['maxlength' => 30])
                    !!}
                </div>
                <div class="col-md-3">
                    {!!Form::select('type', 'Tipo de Entrega', [null => 'Selecione...'] + App\Models\Order::types())
                    ->required(true)
                    !!}
                </div>
                <div class="col-md-9">
                    {!!Form::text('delivery_place', 'Local de Entrega')
                    ->attrs(['maxlength' => 120])
                    !!}
                </div>
                <div class="col-md-12">
                    {!!Form::textarea('description', 'Observação')
                    ->attrs(['maxlength' => 60])
                    !!}
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<div class="row">
    <div class="col-12">
        <button type="submit" class="btn btn-success float-right mt-4">Salvar</button>
    </div>
</div>
