# Migração Blade (api-gplace) → Next.js (`frontend-api-gplace`)

O painel web actual usa **sessão** (`session('store')`, `session('tenant')`, Spatie `permission:*`). O Next consome **API JSON** com cabeçalho **`app`** (`stores.app_token`) + **`Authorization: Bearer`** (Sanctum).

| Blade (web) | Rota / recurso | Prioridade sugerida | Estado API v1 admin | Rota Next (prefixo) |
|-------------|----------------|----------------------|---------------------|---------------------|
| **E — Conta** | | | | |
| `home` | `GET home` | E0 — já tens dashboard TIM | — | `/dashboard` |
| `profile` | `profile` edit/update | E5 | `GET/PUT /auth/profile` ✓ | `/dashboard/conta/perfil` ✓ |
| `change-password` | `change-password` | E5 | `POST /auth/change-password` ✓ | Modal no header |
| `change-first-password` | `change-first-password` | E5 | `POST /auth/change-first-password` ✓ | `/dashboard/conta/primeira-senha` ✓ |
| **E1 — Gplace / loja (já iniciado)** | | | | |
| `settings` | `settings` edit/update | **E1** | `GET/PUT /admin/store-settings` ✓ | `/dashboard/admin/configuracao-loja` ✓ |
| `parameters` | `parameters` resource | **E1** | `GET /admin/parameters` ✓ | `/dashboard/admin/parametros` ✓ |
| `users` | `users` resource | **E1** | CRUD `/admin/store-users`, attach/detach ✓ | `/dashboard/admin/usuarios-loja` ✓ |
| `roles` | `roles` resource | **E1** | CRUD `/admin/store-roles` + sync permissões ✓ | `/dashboard/admin/atribuicoes` ✓ |
| `permissions` | `permissions` resource | **E1** | CRUD `/admin/permissions` ✓ | `/dashboard/admin/permissoes` ✓ |
| **E2 — Conteúdo da loja (sessão store)** | | | | |
| `faq` | `faq` resource | **E2** | `GET /admin/store-faqs` ✓ | `/dashboard/admin/faq` ✓ |
| `catalogs` | `catalogs` resource | **E2** | `GET /admin/store-catalogs` ✓ | `/dashboard/admin/catalogos` ✓ |
| `tokens` | `tokens` resource | **E2** | `GET /admin/tokens` ✓ | `/dashboard/admin/tokens` ✓ |
| **E3 — Cadastros “Pessoas”** | | | | |
| `leads` | `leads` | E3 | — | `/dashboard/admin/leads` |
| `customers` | `customers` + addresses | E3 | — | `/dashboard/admin/clientes` |
| `tenants` | `tenants` | E3 | — | `/dashboard/admin/contratantes` |
| `stores` | `stores` | E3 | — | `/dashboard/admin/lojas` |
| `salesman` | `salesman` | E3 | — | `/dashboard/admin/vendedores` |
| **E4 — Operacionais (produto / loja)** | | | | |
| `products` | `products` | E4 | — | `/dashboard/admin/produtos` |
| `sections` | `sections` | E4 | — | `/dashboard/admin/secoes` |
| `grid` | `grid` | E4 | — | `/dashboard/admin/grade-produto` |
| `brands` | `brands` | E4 | — | `/dashboard/admin/marcas` |
| `measurement-units` | `measurement-units` | E4 | — | `/dashboard/admin/unidades-medida` |
| `freights` | `freights` | E4 | — | `/dashboard/admin/fretes` |
| `banners` | `banners` | E4 | — | `/dashboard/admin/banners` |
| `size-image` | `size-image` | E4 | — | `/dashboard/admin/tamanhos-midia` |
| `interface-positions` | `interface-positions` | E4 | — | `/dashboard/admin/posicoes-interface` |
| **E5 — Gerais / financeiros loja** | | | | |
| `coupons` | `coupons` | E5 | CRUD `/admin/coupons` ✓ | `/dashboard/admin/cupons` ✓ |
| `business-units` | `business-units` | E5 | CRUD `/admin/business-units` ✓ | `/dashboard/admin/unidades-negocio` ✓ |
| `payment-methods` | `payment-methods` | E5 | CRUD `/admin/payment-methods-admin` ✓; leitura loja em `/admin/payment-methods` | `/dashboard/admin/formas-pagamento` ✓ |
| `erp` | `erp` | E5 | CRUD `/admin/erp` ✓ | `/dashboard/admin/erp` ✓ |
| `social-medias` | `social-medias` | E5 | CRUD `/admin/social-medias` ✓ | `/dashboard/admin/redes-sociais` ✓ |
| `cities` / `states` | referência | E5 | CRUD `/admin/cities`, `/admin/states` ✓ | `/dashboard/admin/cidades`, `/dashboard/admin/estados` ✓ |
| **E6 — Vendas / pedidos** | | | | |
| `orders` | `orders` + print | E6 | `GET /admin/orders`, `GET /admin/orders/{id}` ✓; estado via `/public/change-status-orders` | `/dashboard/admin/pedidos` ✓ |
| **E7 — Relatórios** | | | | |
| `products-report` | relatório | E7 | — | `/dashboard/admin/relatorios/produtos` |
| `orders-report` | relatório | E7 | — | `/dashboard/admin/relatorios/pedidos` |
| **E8 — Exportações** | | | | |
| `exports/customers` | export | E8 | — | acção + download |
| `exports/leads` | export | E8 | — | idem |
| **E9 — Satélites** | | | | |
| `families`, `presentations`, `professions`, `services-area`, `partners`, `providers`, `product-reviews`, `product-providers` | resources | E9 | — | conforme uso real |

## Ordem de trabalho recomendada

1. **Concluído no Next** — settings, parameters, users da loja (criar/editar/remover + attach/detach + lojas vinculadas), roles, permissões, FAQ, catálogos, tokens, tenants, stores, leads, customers, vendedores, produtos, seções, marcas, unidades de medida, estoque/armazéns/lotes/movimentos, cidades/estados, cupons, unidades de negócio, formas de pagamento admin, ERP, redes sociais, pedidos (lista/detalhe/estado/filtros/impressão), perfil, primeira senha e seletor de loja por `app_token`.
2. **Prioridade seguinte com API a criar/expandir** — fretes, banners, tamanho de mídia, posição na interface, grade/variações dedicada, relatórios e exportações.
3. **Backlog satélite** — famílias, apresentações, profissões, áreas de serviço, parceiros, fornecedores, reviews e vínculos produto-fornecedor conforme uso real.

## Estado actual do frontend Next

### Implementado

- **Conta:** perfil em `/dashboard/conta/perfil`, primeira senha em `/dashboard/conta/primeira-senha` e alteração de senha no header.
- **Multi-loja:** seletor no header quando o perfil retorna múltiplas lojas com `app_token`; o valor selecionado é persistido em `localStorage` e enviado no header `app`.
- **Gerenciamento:** utilizadores da loja com listagem, criação, edição, remoção, attach/detach e lojas vinculadas; roles e permissões com CRUD; roles também sincronizam permissões Spatie.
- **Conteúdo/configuração:** configurações, parâmetros, FAQ, catálogos e tokens.
- **Cadastros:** contratantes, lojas, clientes, leads e vendedores.
- **Operacionais:** produtos, seções, marcas, unidades de medida e módulo dedicado de estoque/armazéns/lotes/movimentos.
- **Gerais:** cidades, estados, cupons, unidades de negócio, formas de pagamento, ERP e redes sociais com CRUD admin; formulários Gplace usam seletor/pesquisa de cidade em vez de `city_id` manual.
- **Pedidos:** listagem, detalhe, filtros por estado/cliente/período/sincronização, alteração de estado usando `/public/change-status-orders` e impressão comercial local.

### Ainda falta no frontend / API

- **Fretes, banners, tamanho de mídia e posição na interface:** menu ainda aponta para `/dashboard/admin/pendente/*`; exigem endpoints admin REST antes de UI real.
- **Relatórios e exportações:** `products-report`, `orders-report`, `exports/customers` e `exports/leads` ainda não foram migrados para API/Next.
- **Moradas de clientes:** existe fluxo Blade `customers/{id}/addresses`; falta endpoint/admin UI no Next.
- **Módulos E9:** `families`, `presentations`, `professions`, `services-area`, `partners`, `providers`, `product-reviews`, `product-providers` continuam no Blade.

## Convenções

- **Prefixo UI:** `/dashboard/admin/...` para tudo espelhado do Blade admin (evita colisão com rotas TIM em `/dashboard/vendas`, etc.).
- **Prefixo API:** `/api/v1/admin/...` (grupo `app` + `auth:sanctum`).
- **Documentação de rotas:** `docs/API.md` secção 4.4 e este ficheiro.

## Diferenças Blade vs API

| Blade | API Next |
|-------|----------|
| `session('store')['id']` | `$request->attributes->get('store')['id']` via middleware `app` |
| `session('tenant')` | derivar de `Store::whereKey($storeId)->value('tenant_id')` quando necessário |
| `permission:x` middleware | por agora só `auth:sanctum`; depois mapear permissões Spatie por rota ou policy |

---

*Última actualização: inventário baseado em `routes/web.php` e controladores em `app/Http/Controllers/Admin/`.*
