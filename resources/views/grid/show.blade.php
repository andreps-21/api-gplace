@extends('layouts.app', ['page' => 'Grid', 'pageSlug' => 'grid'])

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card ">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-8">
                    </div>
                        <div class="col-4 text-right">
                            <a href="{{ route('grid.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="container">
                        <div class="card-deck">
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="painel-header">
                                                <div class="row">
                                                    <div class="col-md-4 mb-4">
                                                        <p><strong>Nome da Grade de Variação: </strong></p>
                                                        <p class="card-text">
                                                            {{ $item->grid }}
                                                        </p>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <p><strong>Descrição: </strong></p>
                                                        <p class="card-text">
                                                            {{ $item->description }}
                                                        </p>
                                                    </div>     
                                                    <div class="col-md-2">
                                                        <p><strong>Dt. Criada: </strong></p>
                                                        <p class="card-text">
                                                            {{ carbon($item->created_at)->format('d/m/Y H:i') }}
                                                        </p>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <p><strong>Status: </strong></p>
                                                        <p class="card-text">
                                                            {{ $item->is_enabled? 'Sim' : 'Não' }}
                                                        </p>
                                                    </div>                                                
                                                    
                                                </div>
                                            </div>
                                            <ul class="nav nav-tabs" style="margin-top:30px">
                                                <li class="nav-item">
                                                    <a style="background-color: white" data-toggle="tab"
                                                        class="nav-link active" href="#home"><span>Variações</span></a>
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
                                                                            <th class="data">Variação</th>
                                                                            <th class="values">Sigla</th>
                                                                            <th class="values">Status</th>
                                                                            <th class="values">Produtos Vinculados</th>
                                                                           
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($item->variation as $data)
                                                                        <tr class="dynamic-form">
                                                                            <td>
                                                                                <div class="form-group">
                                                                                    <input type="text"
                                                                                        value="{{ $data->variation }}"
                                                                                        class="form-control" readonly>
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                <div class="form-group">
                                                                                    <input type="text"
                                                                                        value="{{ $data->abbreviation }}"
                                                                                        class="form-control" readonly>
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                <div class="form-group">
                                                                                    <input type="text"
                                                                                        value="{{ $data->is_enabled? 'Sim':'Não' }}"
                                                                                        class="form-control" readonly>
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                <div class="form-group">
                                                                                    <input type="text"
                                                                                        value="{{ $data->id }}"
                                                                                        class="form-control" readonly>
                                                                                </div>
                                                                            </td>
                                                                           
                                                                          
                                                                        </tr>
                                                                        @endforeach
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
