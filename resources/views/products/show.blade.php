@extends('layouts.app', ['page' => 'Produtos/Serviços', 'pageSlug' => 'product'])

@section('content')
<div class="container-fluid mt--7">
    <div class="row">
        <div class="col-xl-12 order-xl-1">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h3 class="mb-0">Produtos</h3>
                        </div>
                        <div class="col-4 text-right">
                            <a href="{{ route('products.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="container">
                        <div class="card-deck">
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Video: </strong></p>
                                    <p class="card-text">
                                        {{ $item->video }}
                                    </p>
                                </div>
                            </div>

                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Referencia: </strong></p>
                                    <p class="card-text">
                                        {{ $item->reference }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-deck">
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Origem: </strong></p>
                                    <p class="card-text">
                                        {{ $item->origin }}
                                    </p>
                                </div>
                            </div>

                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Nome Comercial: </strong></p>
                                    <p class="card-text">
                                        {{ $item->commercial_name }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-deck">
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Descrição: </strong></p>
                                    <p class="card-text">
                                        {{ $item->description }}
                                    </p>
                                </div>
                            </div>

                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Unidade de Medida: </strong></p>
                                    <p class="card-text">
                                        {{ $item->um_id }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-deck">
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Tag: </strong></p>
                                    <p class="card-text">
                                        {{ $item->tag }}
                                    </p>
                                </div>
                            </div>

                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Preço: </strong></p>
                                    <p class="card-text">
                                        {{ $item->price }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-deck">
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Preço Promocional: </strong></p>
                                    <p class="card-text">
                                        {{ $item->promotion_price }}
                                    </p>
                                </div>
                            </div>

                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Desconto á Vista/Pix: </strong></p>
                                    <p class="card-text">
                                        {{ $item->discount }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-deck">
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>% Pontos: </strong></p>
                                    <p class="card-text">
                                        {{ $item->spots }}
                                    </p>
                                </div>
                            </div>

                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>% Pontua: </strong></p>
                                    <p class="card-text">
                                        {{ $item->scores }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-deck">
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Cond. Pagto: </strong></p>
                                    <p class="card-text">
                                        {{ $item->cond_pagto }}
                                    </p>
                                </div>
                            </div>

                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Forma Pagto: </strong></p>
                                    <p class="card-text">
                                        {{ $item->form_pagto_id }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-deck">
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Peso: </strong></p>
                                    <p class="card-text">
                                        {{ $item->weight }}
                                    </p>
                                </div>
                            </div>

                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Peso Cubico: </strong></p>
                                    <p class="card-text">
                                        {{ $item->cubic_weight }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-deck">
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Frete: </strong></p>
                                    <p class="card-text">
                                        {{ $item->shipping }}
                                    </p>
                                </div>
                            </div>

                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Marca: </strong></p>
                                    <p class="card-text">
                                        {{ $item->brand_id }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-deck">
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Familia: </strong></p>
                                    <p class="card-text">
                                        {{ $item->family_id }}
                                    </p>
                                </div>
                            </div>

                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Apresentação: </strong></p>
                                    <p class="card-text">
                                        {{ $item->presentation_id }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-deck">
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Sobre: </strong></p>
                                    <p class="card-text">
                                        {{ $item->about }}
                                    </p>
                                </div>
                            </div>

                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Indicação: </strong></p>
                                    <p class="card-text">
                                        {{ $item->recommendation }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-deck">
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Beneficios: </strong></p>
                                    <p class="card-text">
                                        {{ $item->benefits }}
                                    </p>
                                </div>
                            </div>

                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Formula: </strong></p>
                                    <p class="card-text">
                                        {{ $item->formula }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-deck">
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Modo de Aplicação: </strong></p>
                                    <p class="card-text">
                                        {{ $item->application_mode }}
                                    </p>
                                </div>
                            </div>

                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Dosagem: </strong></p>
                                    <p class="card-text">
                                        {{ $item->dosage }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-deck">
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Carência: </strong></p>
                                    <p class="card-text">
                                        {{ $item->lack }}
                                    </p>
                                </div>
                            </div>

                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Outras Informações: </strong></p>
                                    <p class="card-text">
                                        {{ $item->other_information }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-deck">

                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Avaliação: </strong></p>
                                    <p class="card-text">
                                        {{ $item->assessment }}
                                    </p>
                                </div>
                            </div>

                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Ativo: </strong></p>
                                    <p class="card-text">
                                        {{ $item->is_enabled ? 'Sim' : 'Não' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-deck">
                            <div class="card m-1 shadow">
                                <div class="card-body">
                                    <p><strong>Dt. Criação: </strong></p>
                                    <p class="card-text">
                                        {{ $item->created_at ? $item->created_at->format('d/m/Y') : null }}
                                    </p>
                                </div>
                            </div>
                            <div class="card m-1 shadow">
                                <div class="card-body">
                                    <p><strong>Dt. Atualização: </strong></p>
                                    <p class="card-text">
                                        {{ $item->updated_at ? $item->updated_at->format('d/m/Y') : null }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-deck">
                        <div class="card m-1 shadow" style="height:25%">
                                <div class="card-body">
                                    <p><strong>SKU </strong></p>
                                    <p class="card-text">
                                        {{ $item->sku }}
                                    </p>
                                </div>
                            </div>
                            <div class="card m-1 shadow">
                                <div class="card-body"> Variações
                                    <table class="table tablesorter table-striped" id="">
                                            <thead class=" text-primary">
                                                <th scope="col">Código</th>
                                                <th scope="col">Variação</th>
                                                <th scope="col">Sigla</th>
                                            </thead>
                                            <tbody>
                                                @forelse ($item->variation as $dados)
                                                    <tr>
                                                        <td>{{ $dados->id }}</td>
                                                        <td>{{ $dados->variation }}</td>
                                                        <td>{{ $dados->abbreviation }}</td>
                                                    </tr>
                                                @empty
                                                @endforelse
                                            </tbody>
                                    </table>                                    
                                </div>
                            </div>                           
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
