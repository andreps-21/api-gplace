# Migração Blade (api-gplace) → Next.js (`frontend-api-gplace`)

O painel web actual usa **sessão** (`session('store')`, `session('tenant')`, Spatie `permission:*`). O Next consome **API JSON** com cabeçalho **`app`** (`stores.app_token`) + **`Authorization: Bearer`** (Sanctum).

| Blade (web) | Rota / recurso | Prioridade sugerida | Estado API v1 admin | Rota Next (prefixo) |
|-------------|----------------|----------------------|---------------------|---------------------|
| **E — Conta** | | | | |
| `home` | `GET home` | E0 — já tens dashboard TIM | — | `/dashboard` |
| `profile` | `profile` edit/update | E5 | — | `/dashboard/conta/perfil` |
| `change-password` | `change-password` | E5 | `PUT /auth/change-password` existe | `/dashboard/conta/senha` |
| `change-first-password` | `change-first-password` | E5 | — | idem fluxo primeiro acesso |
| **E1 — Gplace / loja (já iniciado)** | | | | |
| `settings` | `settings` edit/update | **E1** | `GET/PUT /admin/store-settings` ✓ | `/dashboard/admin/configuracao-loja` ✓ |
| `parameters` | `parameters` resource | **E1** | `GET /admin/parameters` ✓ | `/dashboard/admin/parametros` ✓ |
| `users` | `users` resource | **E1** | `GET /admin/store-users` ✓ | `/dashboard/admin/usuarios-loja` ✓ |
| `roles` | `roles` resource | **E1** | `GET /admin/store-roles` ✓ | `/dashboard/admin/atribuicoes` ✓ |
| `permissions` | `permissions` resource | **E1** | `GET /admin/permissions` ✓ | `/dashboard/admin/permissoes` ✓ |
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
| `coupons` | `coupons` | E5 | parcial `GET /coupons` público | `/dashboard/admin/cupons` |
| `business-units` | `business-units` | E5 | — | `/dashboard/admin/unidades-negocio` |
| `payment-methods` | `payment-methods` | E5 | parcial `GET /payment-methods` | `/dashboard/admin/formas-pagamento` |
| `erp` | `erp` | E5 | — | `/dashboard/admin/erp` |
| `social-medias` | `social-medias` | E5 | — | `/dashboard/admin/redes-sociais` |
| `cities` / `states` | referência | E5 | `GET /cities`, `GET /states` já existem | componentes partilhados |
| **E6 — Vendas / pedidos** | | | | |
| `orders` | `orders` + print | E6 | parcial `orders` API cliente | `/dashboard/admin/pedidos` |
| **E7 — Relatórios** | | | | |
| `products-report` | relatório | E7 | — | `/dashboard/admin/relatorios/produtos` |
| `orders-report` | relatório | E7 | — | `/dashboard/admin/relatorios/pedidos` |
| **E8 — Exportações** | | | | |
| `exports/customers` | export | E8 | — | acção + download |
| `exports/leads` | export | E8 | — | idem |
| **E9 — Satélites** | | | | |
| `families`, `presentations`, `professions`, `services-area`, `partners`, `providers`, `product-reviews`, `product-providers` | resources | E9 | — | conforme uso real |

## Ordem de trabalho recomendada

1. **E1** — Completar CRUD onde faltar (POST/PUT/DELETE + formulários) para settings, parameters, users, roles, permissions.
2. **E2** — FAQ, catálogos, tokens (lista + detalhe + criar/editar).
3. **E3** — Entidades multi-tenant / loja (stores, tenants, leads, customers) com filtros equivalentes ao Blade.
4. **E4–E9** — Por área de negócio; sempre: endpoint JSON → página Next → testes manuais com header `app`.

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
