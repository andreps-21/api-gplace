@push('css')
<style>
    /* INICIO TELA DE ATRIBUIÇÃO DE PERMISSÕES E REGRAS */
    .group-main, .group {
        list-style: none;
    }

    .group {
        display: none;
    }

    .group-flex {
        display: flex;
        flex-wrap: wrap;
    }

    .group-title {
        cursor: pointer;
    }

    .treeview-title, .group-title, .permission-name {
        user-select: none;
    }

    .active {
        display: block;
    }

    input:checked {
        accent-color: grey;
    }

    .expand-all, .tag-all {
        font-size: 10px;
        width: 130px;
    }

    /* FIM TELA DE ATRIBUIÇÃO DE PERMISSÕES E REGRAS */

</style>
@endpush

<div class="row">
    <div class="col-md-6">
        {!!Form::text('name', 'Nome')
        ->required()
        ->attrs(['maxlength' => 15])!!}
    </div>
    <div class="col-md-6">
        {!!Form::text('description', 'Descrição')
        ->required()
        ->attrs(['maxlength' => 40])!!}
    </div>

    {{-- INICIO TELA DE ATRIBUIÇÃO DE PERMISSÕES E REGRAS --}}
    <div class="col-md-12">
        <div class="form-group">
            <div class="treeview border rounded p-3">
                <h4 class="treeview-title m-0">Permissões</h4>
                <ul class="group-main p-0 m-0">
                    <li class="group-item my-2">
                        <button type="button" class="expand-all btn-default btn btn-sm my-1">
                            Expandir Tudo
                        </button>
                        <button type="button" class="tag-all btn-default btn btn-sm my-1">
                            Marcar Todos
                        </button>
                    </li>
                    @include('roles.treeview-permissions', ['permissions' => $permissions, 'item' => $item ?? null])
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <div class="treeview border rounded p-3">
                <h4 class="">Regras</h4>
                <div class="row">
                    @foreach ($rules as $rule)
                    <div class="col-md-4">
                        <div class="form-group">
                            <input type="checkbox" name="rules[]" value="{{ $rule->id }}" @if(isset($item) && in_array($rule->id, $rulesRole)) checked @endif>
                            <label for="">{{ $rule->description }}</label>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    {{-- FIM TELA DE ATRIBUIÇÃO DE PERMISSÕES E REGRAS --}}

</div>
<div class="row">
    <div class="col-12">
        <button type="submit" class="btn btn-success float-right mt-4">Salvar</button>
    </div>
</div>

@push('js')
<script>
    /* INÍCIO TELA DE ATRIBUIÇÃO DE PERMISSÕES E REGRAS*/

    $('.tag-all-below').each(function () {
        if($(this).closest('li').find(':checked').length){
            $(this).prop('checked', true);
            $('.tag-all').text('Desmarcar Todos');
        }
    });

    $('.expand-all').on('click', function () {
        let groups = $('.group');

        if (this.innerText == 'Expandir Tudo') {
            this.innerText = 'Recolher Tudo';
            groups.each(function () {
                if (!$(this).hasClass('group-flex')) {
                    $(this).addClass('active');
                } else {
                    $(this).addClass('active-flex');
                }
            });
        } else {
            this.innerText = 'Expandir Tudo';
            groups.each(function () {
                if (!$(this).hasClass('group-flex')) {
                    $(this).removeClass('active');
                } else {
                    $(this).removeClass('active-flex');
                }
            });
        }
    });

    $('.tag-all').on('click', function () {
        if (this.innerText == 'Marcar Todos') {
            $('.treeview').find(':checkbox').prop('checked', true);
            this.innerText = 'Desmarcar Todos';
        } else {
            $('.treeview').find(':checkbox').prop('checked', false);
            this.innerText = 'Marcar Todos';
        }
    });

    $('.group-title').on("click", function() {
        let group =  $(this.parentElement.querySelector(".group"));

        if (!group.hasClass('group-flex')) {
            group.toggleClass('active');
        } else {
            group.toggleClass('active-flex');
        }

        var caret = $(this).find('i');
        if(caret.hasClass('fa-caret-down')){
            caret.removeClass('fa-caret-down');
            caret.addClass('fa-caret-up');
        } else {
            caret.removeClass('fa-caret-up');
            caret.addClass('fa-caret-down');
        }
    });

    $('.tag-all-below').on('click', function () {
        var inputs = $(this).next().next().find('input');
        if ($(this).prop('checked')) {
            inputs.each(function () {
                $(this).prop('checked', true);
            });
        } else {
            inputs.each(function () {
                $(this).prop('checked', false);
            });
        }
    });

    /* FIM TELA DE ATRIBUIÇÃO DE PERMISSÕES E REGRAS */
</script>
@endpush
