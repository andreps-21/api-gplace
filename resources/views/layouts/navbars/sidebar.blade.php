<style>
    .sidebar .logo, .off-canvas-sidebar .logo
    {
        position: relative;
        padding: 1.5rem 0.5rem;
        z-index: 4;
    }
</style>

<div style="width: 250px; background-color: #363f94;" class="sidebar">
    <div class="sidebar-wrapper">
        <div class="logo">
            <a href="#" class="simple-text logo-normal text-center">
                <!-- Gooding Solutions -->
                <img src="{{ asset('white') }}/img/logo.png" style="width:80%;height:80%" alt="Logo">
            </a>
        </div>
        <ul class="nav">
            <li>
                <a href="{{ route('home') }}">
                    <i class="fas fa-chart-pie"></i>
                    <p>Dashboard</p>
                </a>
            </li>
            @canany(['customers_view', 'leads_view', 'tenants_view', 'stores_view', 'products_view', 'sections_view',
                'measurement-units_view', 'grid_view', 'brands_view', 'cities_view', 'states_view', 'payment-methods_view','erp_view',
                'coupons_view', 'businessunits_view'])
                <li>
                    <a data-toggle="collapse" href="#register" aria-expanded="false" class="collapsed">
                        <i class="fas fa-pencil-alt"></i>
                        <span class="nav-link-text">Cadastros</span>
                        <b class="caret mt-1"></b>
                    </a>

                    <div class="collapse" id="register" style="">
                        <ul class="nav pl-4">

                            @canany(['customers_view', 'leads_view', 'tenants_view', 'stores_view'])
                                <li>
                                    <a data-toggle="collapse" href="#person" aria-expanded="false" class="collapsed">
                                        <i class="fas fa-users"></i>
                                        <span class="nav-link-text">Pessoas</span>
                                        <b class="caret mt-1"></b>
                                    </a>

                                    <div class="collapse" id="person" style="">
                                        <ul class="nav pl-4">
                                            @can('leads_view')
                                                <li>
                                                    <a href="{{ route('leads.index') }}">
                                                        <i class="fas fa-walking"></i>
                                                        <p>Leads</p>
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('customers_view')
                                                <li>
                                                    <a href="{{ route('customers.index') }}">
                                                        <i class="fas fa-walking"></i>
                                                        <p>Clientes</p>
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('tenants_view')
                                                <li>
                                                    <a href="{{ route('tenants.index') }}">
                                                        <i class="fas fa-user-tag"></i>
                                                        <p>Contratantes</p>
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('stores_view')
                                                <li>
                                                    <a href="{{ route('stores.index') }}">
                                                        <i class="fas fa-user-tag"></i>
                                                        <p>Lojas</p>
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('salesman_view')
                                            <li>
                                                <a href="{{ route('salesman.index') }}">
                                                    <i class="fas fa-user-tag"></i>
                                                    <p>Vendedores</p>
                                                </a>
                                            </li>
                                            @endcan
                                        </ul>
                                    </div>
                                </li>
                            @endcanany

                            @canany(['products_view', 'sections_view', 'measurement-units_view', 'grid_view',
                                'brands_view'])
                                <li>
                                    <a data-toggle="collapse" href="#operation" aria-expanded="false" class="collapsed">
                                        <i class="fas fa-clipboard-list"></i>
                                        <span class="nav-link-text">Operacionais</span>
                                        <b class="caret mt-1"></b>
                                    </a>

                                    <div class="collapse" id="operation" style="">
                                        <ul class="nav pl-4">
                                            @if(session()->has('store'))
                                            @can('products_view')
                                                <li>
                                                    <a href="{{ route('products.index') }}">
                                                        <i class="fas fa-drumstick-bite"></i>
                                                        <p>Produtos</p>
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('sections_view')
                                                <li>
                                                    <a href="{{ route('sections.index') }}">
                                                        <i class="fas fa-tag"></i>
                                                        <p>Seções</p>
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('grid_view')
                                                <li>
                                                    <a href="{{ route('grid.index') }}">
                                                        <i class="fas fa-tasks"></i>
                                                        <p>Grade de<br /> Produto</p>
                                                    </a>
                                                </li>
                                            @endcan
                                            @endif
                                            @if (session()->exists('tenant'))
                                                @can('brands_view')
                                                    <li>
                                                        <a href="{{ route('brands.index') }}">
                                                            <i class="fab fa-500px"></i>
                                                            <p>Marca</p>
                                                        </a>
                                                    </li>
                                                @endcan
                                            @endif
                                            @can('measurement-units_view')
                                                <li>
                                                    <a href="{{ route('measurement-units.index') }}">
                                                        <i class="fab fa-algolia"></i>
                                                        <p>Unidade de<br /> Medida</p>
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('freights_view')
                                            <li>
                                                    <a href="{{ route('freights.index') }}">
                                                        <i class="fas fa-route"></i>
                                                        <p>Regras de<br /> Frete</p>
                                                    </a>
                                                </li>
                                            @endcan
                                            @if(session()->has('store'))
                                                @can('banners_view')
                                                <li>
                                                    <a href="{{ route('banners.index') }}">
                                                            <i class="fas fa-image"></i>
                                                            <p>Mídias/Anúncios</p>
                                                        </a>
                                                    </li>
                                                @endcan
                                            @endif
                                            @can('size-image_view')
                                                <li>
                                                    <a href="{{ route('size-image.index') }}">
                                                        <i class="far fa-file-alt"></i>
                                                        <p>Tamanho <br />Mídia</p>
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('interface-positions_view')
                                                <li>
                                                    <a href="{{ route('interface-positions.index') }}">
                                                        <i class="fas fa-arrows-alt"></i>
                                                        <p>Posição na <br />Interface</p>
                                                    </a>
                                                </li>
                                            @endcan
                                        </ul>
                                    </div>
                                </li>
                            @endcanany
                            @canany(['cities_view', 'states_view', 'payment-methods_view','erp_view', 'coupons_view',
                                'businessunits_view'])
                                <li>
                                    <a data-toggle="collapse" href="#generalCreate" aria-expanded="false" class="collapsed">
                                        <i class="fas fa-bars"></i>
                                        <span class="nav-link-text">Gerais</span>
                                        <b class="caret mt-1"></b>
                                    </a>

                                    <div class="collapse" id="generalCreate" style="">
                                        <ul class="nav pl-4">
                                            @if(session()->has('store'))
                                            @can('coupons_view')
                                                <li>
                                                    <a href="{{ route('coupons.index') }}">
                                                        <i class="fas fa-ticket-alt"></i>
                                                        <p>Cupons</p>
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('businessunits_view')
                                                <li>
                                                    <a href="{{ route('business-units.index') }}">
                                                        <i class="fas fa-store-alt"></i>
                                                        <p>Unidades</p>
                                                    </a>
                                                </li>
                                            @endcan
                                            @endif
                                            @can('payment-methods_view')
                                                <li>
                                                    <a href="{{ route('payment-methods.index') }}">
                                                        <i class="fas fa-money-check-alt"></i>
                                                        <p>Formas de<br /> Pagamento</p>
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('erp_view')
                                            <li>
                                                <a href="{{ route('erp.index') }}">
                                                    <i class="fas fa-layer-group"></i>
                                                    <p>ERP</p>
                                                </a>
                                            </li>
                                            @endcan
                                            @can('social-medias_view')
                                                <li>
                                                    <a href="{{ route('social-medias.index') }}">
                                                        <i class="fab fa-facebook"></i>
                                                        <p>Redes Sociais</p>
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('cities_view')
                                                <li>
                                                    <a href="{{ route('cities.index') }}">
                                                        <i class="fas fa-city"></i>
                                                        <p>Cidades</p>
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('states_view')
                                                <li>
                                                    <a href="{{ route('states.index') }}">
                                                        <i class="fas fa-layer-group"></i>
                                                        <p>Estados</p>
                                                    </a>
                                                </li>
                                            @endcan
                                        </ul>
                                    </div>
                                </li>
                            @endcanany
                        </ul>
                    </div>
                </li>
            @endcanany
            @if(session()->has('store'))
            @canany(['orders_view'])
                <li>
                    <a data-toggle="collapse" href="#movements" aria-expanded="false" class="collapsed">
                        <i class="fas fa-tasks"></i>
                        <span class="nav-link-text">Vendas</span>
                        <b class="caret mt-1"></b>
                    </a>

                    <div class="collapse" id="movements" style="">
                        <ul class="nav pl-4">
                            @can('orders_view')
                                <li>
                                    <a href="{{ route('orders.index') }}">
                                        <i class="fas fa-shopping-cart"></i>
                                        <p>Pedido de Venda</p>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </li>
            @endcanany
            @endif
            @if(session()->has('store'))
            @canany(['product_report_view', 'order_report_view'])
                <li>
                    <a data-toggle="collapse" href="#reports" aria-expanded="false" class="collapsed">
                        <i class="fas fa-file-pdf"></i>
                        <span class="nav-link-text">Relatórios</span>
                        <b class="caret mt-1"></b>
                    </a>

                    <div class="collapse" id="reports" style="">
                        <ul class="nav pl-4">
                            @can('product_report_view')
                                <li>
                                    <a href="{{ route('products.report') }}">
                                        <i class="fas fa-drumstick-bite"></i>
                                        <p>Produtos</p>
                                    </a>
                                </li>
                            @endcan
                            @can('order_report_view')
                                <li>
                                    <a href="{{ route('orders.report') }}">
                                        <i class="fas fa-shopping-cart"></i>
                                        <p>Pedidos</p>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </li>
            @endcanany
            @endif
            @canany(['users_view', 'roles_view', 'permissions_view'])
                <li>
                    <a data-toggle="collapse" href="#collapseGerenciamento" aria-expanded="false" class="collapsed">
                        <i class="fas fa-users-cog"></i>
                        <span class="nav-link-text">Gerenciamento</span>
                        <b class="caret mt-1"></b>
                    </a>

                    <div class="collapse" id="collapseGerenciamento" style="">
                        <ul class="nav pl-4">

                            @can('users_view')
                                <li>
                                    <a href="{{ route('users.index') }}">
                                        <i class="fas fa-users"></i>
                                        <p>Usuários</p>
                                    </a>
                                </li>
                            @endcan

                            @can('roles_view')
                                <li>
                                    <a href="{{ route('roles.index') }}">
                                        <i class="fas fa-user-lock"></i>
                                        <p>Atribuições</p>
                                    </a>
                                </li>
                            @endcan
                            @can('permissions_view')
                                <li>
                                    <a href="{{ route('permissions.index') }}">
                                        <i class="fas fa-lock"></i>
                                        <p>Permissões</p>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </li>
            @endcanany
            @canany(['parameters_view', 'banners_view', 'catalogs_view', 'settings_edit'])
                <li>
                    <a data-toggle="collapse" href="#configs" aria-expanded="false" class="collapsed">
                        <i class="fas fa-cogs"></i>
                        <span class="nav-link-text">Configurações</span>
                        <b class="caret mt-1"></b>
                    </a>

                    <div class="collapse" id="configs" style="">
                        <ul class="nav pl-4">
                            @if(session()->has('store'))
                            @can('faq_view')
                                <li>
                                    <a href="{{ route('faq.index') }}">
                                        <i class="fas fa-question"></i>
                                        <p>FAQ</p>
                                    </a>
                                </li>
                            @endcan
                            @can('catalogs_view')
                                <li>
                                    <a href="{{ route('catalogs.index') }}">
                                        <i class="fas fa-images"></i>
                                        <p>Catálogo</p>
                                    </a>
                                </li>
                            @endcan

                            @endif
                            @can('parameters_view')
                                <li>
                                    <a href="{{ route('parameters.index') }}">
                                        <i class="fas fa-cog"></i>
                                        <p>Parâmetros</p>
                                    </a>
                                </li>
                            @endcan
                            @if(session()->has('store'))
                            @can('settings_edit')
                                <li>
                                    <a href="{{ route('settings.edit') }}">
                                        <i class="fas fa-cogs"></i>
                                        <p>Configuração</p>
                                    </a>
                                </li>
                            @endcan
                            @endif
                            @can('tokens_view')
                                <li>
                                    <a href="{{ route('tokens.index') }}">
                                        <i class="fas fa-key"></i>
                                        <p>Tokens Integração</p>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </li>
            @endcanany
        </ul>
    </div>
</div>
