@push('css')
<style>
    .buttons-relatorio {
        display: flex;
    }

    .btn-gerar-relatorio {
        @if(isset($item) && $item->catalog !=null) width: 50%;
        @else width: 100%;
        @endif
    }

    .btn-excluir-relatorio {
        width: 50%;
    }


    @media (max-width: 1280px) {
        .buttons-relatorio {
            display: inline;
        }

        .btn-gerar-relatorio {
            width: 100%;
        }

        .btn-excluir-relatorio {
            width: 100%;
        }
    }
</style>
@endpush

<div class="row">
    <div class="col-12 ">
        <div class="col-5" style="display: inline-block;">
            <label>Termos de Uso</label>
            <input type="hidden"
                value="{{ settings('terms') ? asset('storage/'.settings('terms').'?version='.uniqid()) : null}}">
            <canvas class="pdf-miniatura" id="pdfViewer" style="width:360px; height:360px"></canvas>
            <div class="buttons-relatorio">
                <label class="btn btn-block btn-primary btn-gerar-relatorio">
                    Anexar em formato PDF. Tam. Até 2 MB&hellip;
                    <input
                        class="custom-file-input form-control pdf-input @if($errors->has('catalog')) is-invalid @endif"
                        style="display: none;" name="catalog" type="file" id="pdf" accept="application/pdf" /><br>
                </label>
            </div>
            <div>
                @if(settings('terms'))
                <a href="{{ asset('storage/'.settings('terms').'?version='.uniqid()) }}" target="_blank"
                    class="btn btn-block">Visualizar anexo</a>
                @endif
            </div>
        </div>
        <div class="col-5" style="display: inline-block;">
            <label>Políticas de Privacidade</label>
            <input type="hidden"
                value="{{ settings('privacy') ? asset('storage/'.settings('privacy').'?version='.uniqid()) : null}}">
            <canvas class="pdf-miniatura" id="pdfViewer2" style="width:360px; height:360px"></canvas>
            <div class="buttons-relatorio">
                <label class="btn btn-block btn-primary btn-gerar-relatorio">
                    Anexar em formato PDF. Tam. Até 2 MB&hellip;
                    <input
                        class="custom-file-input pdf-input form-control @if($errors->has('privacy')) is-invalid @endif"
                        style="display: none;" name="privacy" type="file" id="pdf2" accept="application/pdf" /><br>
                </label>

            </div>
            <div>
                @if(settings('privacy'))
                <a href="{{ asset('storage/'.settings('privacy').'?version='.uniqid()) }}" target="_blank"
                    class="btn btn-block">Visualizar anexo</a>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <button type="submit" class="btn btn-success float-right mt-4">Salvar</button>
    </div>
</div>
