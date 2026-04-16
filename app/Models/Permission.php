<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permission extends \Spatie\Permission\Models\Permission
{
    use HasFactory;

    const PERMISSIONS = [
        [
            'title' => 'Painel de Controle',
            'items' => []
        ],
        [
            'title' => 'Painel',
            'items' => [
                array('name' => 'panel_view', 'description' => 'Visualizar'),
            ]
        ],
        [
            'title' => 'Cadastros',
            'items' => [
                [
                    'title' => 'Pessoas',
                    'items' => [
                        [
                            'title' => 'Leads',
                            'items' => [
                                array('name' => 'leads_view', 'description' => 'Visualizar'),
                                array('name' => 'leads_create', 'description' => 'Criar'),
                                array('name' => 'leads_edit', 'description' => 'Editar'),
                                array('name' => 'leads_delete', 'description' => 'Deletar'),
                            ]
                        ],

                        [
                            'title' => 'Clientes',
                            'items' => [
                                array('name' => 'customers_view', 'description' => 'Visualizar'),
                                array('name' => 'customers_create', 'description' => 'Criar'),
                                array('name' => 'customers_edit', 'description' => 'Editar'),
                                array('name' => 'customers_delete', 'description' => 'Deletar'),
                            ]
                        ],
                        [
                            'title' => 'Contrantates',
                            'items' => [
                                array('name' => 'tenants_view', 'description' => 'Visualizar'),
                                array('name' => 'tenants_create', 'description' => 'Criar'),
                                array('name' => 'tenants_edit', 'description' => 'Editar'),
                                array('name' => 'tenants_delete', 'description' => 'Deletar'),
                            ]
                        ],
                        [
                            'title' => 'Lojas',
                            'items' => [
                                array('name' => 'stores_view', 'description' => 'Visualizar'),
                                array('name' => 'stores_create', 'description' => 'Criar'),
                                array('name' => 'stores_edit', 'description' => 'Editar'),
                                array('name' => 'stores_delete', 'description' => 'Deletar'),
                            ]
                        ],
                        [
                            'title' => 'Vendedores',
                            'items' => [
                                array('name' => 'salesman_view', 'description' => 'Visualizar'),
                                array('name' => 'salesman_create', 'description' => 'Criar'),
                                array('name' => 'salesman_edit', 'description' => 'Editar'),
                                array('name' => 'salesman_delete', 'description' => 'Deletar'),
                            ]
                        ],
                    ]
                ],
                [
                    'title' => 'Operacionais',
                    'items' => [
                        [
                            'title' => 'Produtos',
                            'items' => [
                                array('name' => 'products_view', 'description' => 'Visualizar'),
                                array('name' => 'products_create', 'description' => 'Criar'),
                                array('name' => 'products_edit', 'description' => 'Editar'),
                                array('name' => 'products_delete', 'description' => 'Deletar'),
                            ]
                        ],
                        [
                            'title' => 'Seções',
                            'items' => [
                                array('name' => 'sections_view', 'description' => 'Visualizar'),
                                array('name' => 'sections_create', 'description' => 'Criar'),
                                array('name' => 'sections_edit', 'description' => 'Editar'),
                                array('name' => 'sections_delete', 'description' => 'Deletar'),
                            ]
                        ],
                        [
                            'title' => 'Grade de Produtos',
                            'items' => [
                                array('name' => 'grid_view', 'description' => 'Visualizar'),
                                array('name' => 'grid_create', 'description' => 'Criar'),
                                array('name' => 'grid_edit', 'description' => 'Editar'),
                                array('name' => 'grid_delete', 'description' => 'Deletar'),
                            ]
                        ],
                        [
                            'title' => 'Marca',
                            'items' => [
                                array('name' => 'brands_view', 'description' => 'Visualizar'),
                                array('name' => 'brands_create', 'description' => 'Criar'),
                                array('name' => 'brands_edit', 'description' => 'Editar'),
                                array('name' => 'brands_delete', 'description' => 'Deletar'),
                            ]
                        ],
                        [
                            'title' => 'Unidades de Medida',
                            'items' => [
                                array('name' => 'measurement-units_view', 'description' => 'Visualizar'),
                                array('name' => 'measurement-units_create', 'description' => 'Criar'),
                                array('name' => 'measurement-units_edit', 'description' => 'Editar'),
                                array('name' => 'measurement-units_delete', 'description' => 'Deletar'),
                            ]
                        ],
                        [
                            'title' => 'Formas de Pagamento',
                            'items' => [
                                array('name' => 'payment-methods_view', 'description' => 'Visualizar'),
                                array('name' => 'payment-methods_create', 'description' => 'Criar'),
                                array('name' => 'payment-methods_edit', 'description' => 'Editar'),
                                array('name' => 'payment-methods_delete', 'description' => 'Deletar'),
                            ]
                        ],
                        [
                            'title' => 'Redes Sociais',
                            'items' => [
                                array('name' => 'social-medias_view', 'description' => 'Visualizar'),
                                array('name' => 'social-medias_create', 'description' => 'Criar'),
                                array('name' => 'social-medias_edit', 'description' => 'Editar'),
                                array('name' => 'social-medias_delete', 'description' => 'Deletar'),
                            ]
                        ],
                        [
                            'title' => 'Regras de Frete',
                            'items' => [
                                array('name' => 'freights_view', 'description' => 'Visualizar'),
                                array('name' => 'freights_create', 'description' => 'Criar'),
                                array('name' => 'freights_edit', 'description' => 'Editar'),
                                array('name' => 'freights_delete', 'description' => 'Deletar'),
                            ]
                        ],
                        [
                            'title' => 'Tamanhos da Imagem',
                            'items' => [
                                array('name' => 'size-image_view', 'description' => 'Visualizar'),
                                array('name' => 'size-image_create', 'description' => 'Criar'),
                                array('name' => 'size-image_edit', 'description' => 'Editar'),
                                array('name' => 'size-image_delete', 'description' => 'Deletar'),
                            ]
                        ],
                        [
                            'title' => 'Posição na Interface',
                            'items' => [
                                array('name' => 'interface-positions_view', 'description' => 'Visualizar'),
                                array('name' => 'interface-positions_create', 'description' => 'Criar'),
                                array('name' => 'interface-positions_edit', 'description' => 'Editar'),
                                array('name' => 'interface-positions_delete', 'description' => 'Deletar'),
                            ]
                        ],

                    ],
                ],
                [
                    'title' => 'Gerais',
                    'items' => [
                        [
                            'title' => 'Cupons',
                            'items' => [
                                array('name' => 'coupons_view', 'description' => 'Visualizar'),
                                array('name' => 'coupons_create', 'description' => 'Criar'),
                                array('name' => 'coupons_edit', 'description' => 'Editar'),
                                array('name' => 'coupons_delete', 'description' => 'Deletar'),
                            ]
                        ],
                        [
                            'title' => 'ERP',
                            'items' => [
                                array('name' => 'erp_view', 'description' => 'Visualizar'),
                                array('name' => 'erp_create', 'description' => 'Criar'),
                                array('name' => 'erp_edit', 'description' => 'Editar'),
                                array('name' => 'erp_delete', 'description' => 'Deletar'),
                            ]
                        ],
                        [
                            'title' => 'Unidades',
                            'items' => [
                                array('name' => 'businessunits_view', 'description' => 'Visualizar'),
                                array('name' => 'businessunits_create', 'description' => 'Criar'),
                                array('name' => 'businessunits_edit', 'description' => 'Editar'),
                                array('name' => 'businessunits_delete', 'description' => 'Deletar'),
                            ]
                        ],
                        [
                            'title' => 'Cidades',
                            'items' => [
                                array('name' => 'cities_view', 'description' => 'Visualizar'),

                            ]
                        ],
                        [
                            'title' => 'Estados',
                            'items' => [
                                array('name' => 'states_view', 'description' => 'Visualizar'),
                            ]
                        ],
                    ]
                ]
            ],
        ],
        [
            'title' => 'Movimentações',
            'items' => [
                [
                    'title' => 'Pedidos de Venda',
                    'items' => [
                        array('name' => 'orders_view', 'description' => 'Visualizar'),
                        array('name' => 'orders_create', 'description' => 'Criar'),
                        array('name' => 'orders_edit', 'description' => 'Editar'),
                        array('name' => 'orders_delete', 'description' => 'Deletar'),
                    ]
                ]
            ]
        ],
        [
            'title' => 'Relatórios',
            'items' => [
                [
                    'title' => 'Produtos',
                    'items' => [
                        array('name' => 'product_report_view', 'description' => 'Visualizar'),
                    ]
                ],
                [
                    'title' => 'Pedidos',
                    'items' => [
                        array('name' => 'order_report_view', 'description' => 'Visualizar'),
                    ]
                ]
            ]
        ],
        [
            'title' => 'Gerenciamento',
            'items' => [
                [
                    'title' => 'Usuários',
                    'items' => [
                        array('name' => 'users_view', 'description' => 'Visualizar'),
                        array('name' => 'users_create', 'description' => 'Criar'),
                        array('name' => 'users_edit', 'description' => 'Editar'),
                        array('name' => 'users_delete', 'description' => 'Deletar'),
                    ]
                ],
                [
                    'title' => 'Atribuições',
                    'items' => [
                        array('name' => 'roles_view', 'description' => 'Visualizars'),
                        array('name' => 'roles_create', 'description' => 'Criar'),
                        array('name' => 'roles_edit', 'description' => 'Editars'),
                        array('name' => 'roles_delete', 'description' => 'Deletar'),
                    ]
                ],
                [
                    'title' => 'Permissões',
                    'items' => [
                        array('name' => 'permissions_view', 'description' => 'Visualizar'),
                        array('name' => 'permissions_create', 'description' => 'Criar'),
                        array('name' => 'permissions_edit', 'description' => 'Editar'),
                        array('name' => 'permissions_delete', 'description' => 'Deletar'),
                    ]
                ]
            ]
        ],
        [
            'title' => 'Configurações',
            'items' => [
                [
                    'title' => 'Faq',
                    'items' => [
                        array('name' => 'faq_view', 'description' => 'Visualizar'),
                        array('name' => 'faq_create', 'description' => 'Criar'),
                        array('name' => 'faq_edit', 'description' => 'Editar'),
                        array('name' => 'faq_delete', 'description' => 'Deletar'),
                    ]
                ],
                [
                    'title' => 'Catálago',
                    'items' => [
                        array('name' => 'catalogs_view', 'description' => 'Visualizar'),
                        array('name' => 'catalogs_create', 'description' => 'Criar'),
                        array('name' => 'catalogs_edit', 'description' => 'Editar'),
                        array('name' => 'catalogs_delete', 'description' => 'Deletar'),
                    ]
                ],
                [
                    'title' => 'Mídias',
                    'items' => [
                        array('name' => 'banners_view', 'description' => 'Visualizar'),
                        array('name' => 'banners_create', 'description' => 'Criar'),
                        array('name' => 'banners_edit', 'description' => 'Editar'),
                        array('name' => 'banners_delete', 'description' => 'Deletar'),
                    ]
                ],
                [
                    'title' => 'Parâmetros',
                    'items' => [
                        array('name' => 'parameters_view', 'description' => 'Visualizar'),
                        array('name' => 'parameters_create', 'description' => 'Criar'),
                        array('name' => 'parameters_edit', 'description' => 'Editar'),
                        array('name' => 'parameters_delete', 'description' => 'Deletar'),
                    ]
                ],
                [
                    'title' => 'Configuração',
                    'items' => [
                        array('name' => 'settings_view', 'description' => 'Visualizar'),
                        array('name' => 'settings_create', 'description' => 'Criar'),
                        array('name' => 'settings_edit', 'description' => 'Editar'),
                        array('name' => 'settings_delete', 'description' => 'Deletar'),
                    ]
                ],
                [
                    'title' => 'Tokens Integração',
                    'items' => [
                        array('name' => 'tokens_view', 'description' => 'Visualizar'),
                        array('name' => 'tokens_create', 'description' => 'Criar'),
                        array('name' => 'tokens_edit', 'description' => 'Editar'),
                        array('name' => 'tokens_delete', 'description' => 'Deletar'),
                    ]
                ],
            ]
        ]
    ];
}
