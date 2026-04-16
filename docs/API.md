# Documentação da API (Shoplink / Gplace)

Todas as rotas HTTP abaixo usam o prefixo global **`/api`** definido no `RouteServiceProvider`. A versão atual expõe recursos sob **`/api/v1`**.

**Base URL (exemplo local):** `http://localhost:8005`  
**Prefixo da API:** `/api/v1`  
**URL completa típica:** `http://localhost:8005/api/v1/...`

---

## 1. Formato das respostas JSON

A maioria dos controladores em `App\Http\Controllers\API` estende `BaseController`:

| Cenário | HTTP | Corpo |
|--------|------|--------|
| Sucesso | 200 (ou outro, ex. 201) | `{ "message": "...", "data": ... }` |
| Erro | 4xx / 5xx | `{ "message": "...", "data": { ... } }` — `data` opcional (ex. erros de validação) |

Mensagens de validação comuns: HTTP **422** com `data` contendo os campos em erro.

---

## 2. Cabeçalho `app` (identificação da loja)

Grande parte dos endpoints sob **`/api/v1`** passam pelo middleware **`app`** (`CheckAppHeader`):

| Cabeçalho | Obrigatório | Descrição |
|-----------|-------------|-----------|
| `app` | Sim (nesses endpoints) | Token da loja (`stores.app_token`). Identifica a loja e disponibiliza o contexto em `$request->attributes['store']`. |

**Respostas de erro sem ou com token inválido:**

- `403` — `{ "message": "App ID não informada." }` ou `{ "message": "App ID não válida." }`

**Endpoints que *não* usam o middleware `app`:** estão declarados *fora* do grupo `Route::middleware(['app'])` em `routes/api.php` (ver secção 6).

---

## 3. Autenticação do utilizador (Laravel Passport)

O guard **`api`** usa o driver **`passport`**.

| Uso | Cabeçalho |
|-----|-----------|
| Rotas `auth:api` | `Authorization: Bearer {access_token}` |

O token é obtido no **login** (`POST .../auth/login`). O utilizador tem de estar associado à loja do cabeçalho `app` (ver `SessionController`).

**Exemplo:**

```http
GET /api/v1/auth/profile
app: {app_token_da_loja}
Authorization: Bearer {access_token}
```

**Rota utilitária (sem prefixo `v1` no ficheiro — atenção ao path):**

| Método | Path | Auth |
|--------|------|------|
| GET | `/api/user` | `auth:api` |

---

## 4. Endpoints com `app` + rotas públicas da loja

Base: **`/api/v1`** + cabeçalho **`app`**.

### 4.1 Autenticação e conta (`/api/v1/auth/...`)

| Método | Path | `auth:api` | Descrição |
|--------|------|------------|-----------|
| POST | `/auth/login` | Não | `email`, `password` — devolve `user` + `token` (access token). |
| POST | `/auth/users` | Não | Registo de utilizador / pessoa (regras em `UserController`). |
| POST | `/auth/user-lead` | Não | Registo tipo lead (`UserLeadController`). |
| POST | `/auth/password/email` | Não | Pedido de recuperação de password. |
| POST | `/auth/password/code/check` | Não | Validação de código. |
| POST | `/auth/password/reset` | Não | Reset de password com código. |
| DELETE | `/auth/logout` | Sim | Revoga o token atual. |
| GET | `/auth/profile` | Sim | Perfil do utilizador autenticado. |
| PUT | `/auth/profile` | Sim | Atualiza perfil. |
| POST | `/auth/change-password` | Sim | Alteração de password. |

### 4.2 Catálogo e conteúdo (público com `app`)

| Método | Path | Descrição |
|--------|------|-----------|
| GET | `/products` | Lista de produtos. |
| GET | `/products/{id}` | Detalhe do produto. |
| GET | `/faqs` | FAQs. |
| GET | `/faqs/{id}` | Detalhe FAQ. |
| GET | `/catalogs` | Catálogos. |
| GET | `/catalogs/{id}` | Detalhe. |
| POST | `/leads` | Cria lead (`name`, `email` obrigatórios). |
| GET | `/brands` | Marcas. |
| GET | `/sections` | Secções. |
| GET | `/sections-home` | Secções para home. |
| POST | `/calc-freight` | Cálculo de frete. |
| GET | `/banners` | Banners. |
| POST | `/pagseguro-installments` | Parcelamento PagSeguro (existe rota duplicada no ficheiro; a última definição pode prevalecer — usar a versão activa no ambiente). |
| GET | `/parameters` | Parâmetros da loja. |
| POST | `/contact` | Formulário de contacto. |
| GET | `/payment-methods` | Meios de pagamento. |
| GET | `/settings` | Definições. |
| GET | `/public-key` | Chave pública (ex. gateway). |
| GET | `/pagseguro-session` | Sessão PagSeguro. |
| GET | `/home` | Dados agregados da home. |
| GET | `/coupons` | Cupões. |
| POST | `/validate-coupon` | Validação de cupão. |
| GET | `/salesman` | Vendedores. |

### 4.3 Pedidos e moradas (requerem `app` + `auth:api`)

| Método | Path | Descrição |
|--------|------|-----------|
| GET | `/orders` | Lista pedidos do cliente autenticado na loja. |
| GET | `/orders/{id}` | Detalhe do pedido. |
| POST | `/orders` | Cria pedido (payload complexo: totais, `items[]`, `payment_method_id`, dados de cliente/cartão conforme meio de pagamento — ver `OrderController::rules`). **Estoque:** a API recusa o pedido (422) se não houver quantidade suficiente por produto na loja. |
| GET | `/addresses` | Lista moradas. |
| POST | `/addresses` | Cria morada. |
| GET | `/addresses/{id}` | Detalhe. |
| PUT/PATCH | `/addresses/{id}` | Atualiza. |
| DELETE | `/addresses/{id}` | Remove (pode falhar se houver pedidos vinculados). |

### 4.4 Administração Gplace (`app` + `auth:api`)

Migração das áreas de **configuração** e **gerenciamento** do Blade. O escopo da loja vem do cabeçalho **`app`** (como no painel web com sessão de loja).

**Módulos (resumo)**  
| Módulo | Leitura na API | Escrita na API | Notas UI Next |
|--------|----------------|----------------|---------------|
| Config. da loja | GET `/admin/store-settings` | PUT `/admin/store-settings` | Já existia. |
| Parâmetros | GET `/admin/parameters` | POST/GET/PUT/DELETE `/admin/parameters` e `/{id}` | CRUD na página admin. |
| Utilizadores da loja | GET `/admin/store-users` | POST `/admin/store-users/attach`, DELETE `/admin/store-users/detach/{userId}` | Associa utilizador existente por ID; não duplica o fluxo completo de criação de utilizador do Blade. |
| Roles / Permissões | GET `/admin/store-roles`, GET `/admin/permissions` | — | Gestão Spatie complexa; apenas listagem. |
| FAQ | GET `/admin/store-faqs` | POST/GET/PUT/DELETE `/admin/store-faqs` e `/{id}` | CRUD na página admin. |
| Catálogos | GET `/admin/store-catalogs` | POST/GET/PUT/DELETE `/admin/store-catalogs` e `/{id}` | CRUD JSON; upload de imagem opcional via multipart no mesmo controlador Laravel. |
| Tokens | GET `/admin/tokens` | POST/GET/PUT/DELETE `/admin/tokens` e `/{id}` | `store_id` omissão → loja do header; criação devolve `access_token_plain` uma vez. |
| Tenant | GET `/admin/tenants`, GET `/admin/tenants/{id}` | PUT `/admin/tenants/{id}` | Apenas o titular da loja; UI com edição JSON. |
| Clientes | GET `/admin/customers` | POST/GET/PUT/DELETE `/admin/customers` e `/{id}` | API completa; UI pode usar os mesmos endpoints (formulários extensos). |
| Leads | GET `/admin/leads` | POST/GET/PUT/DELETE `/admin/leads` e `/{id}` | CRUD na página admin. |
| Lojas | GET `/admin/stores` | POST/GET/PUT/DELETE `/admin/stores` e `/{id}` | API completa; `paymentMethods` opcional em create/update. |
| Vendedores | GET `/admin/salesmen` | POST/GET/PUT/DELETE `/admin/salesmen` e `/{id}` | API alinhada ao Blade (person + pivot loja + role vendedor no create). |
| Produtos | GET `/admin/products` | POST/GET/PUT/DELETE `/admin/products` e `/{id}` | `quantity` (inteiro ≥ 0), **`min_stock`** opcional (alerta de stock baixo na UI), **`stock_change_note`** opcional ao ajustar quantidade; movimentos em `stock_movements`. |
| Armazéns | GET/POST `/admin/warehouses` | — | Base multi-armazém (opção C); nome, `code`, `is_default`. |
| Movimentos de stock | GET `/admin/stock-movements` | — | Query **`product_id`** (obrigatório), `per_page`; tipos: `admin_create`, `admin_adjust`, `order_sale`, `lot_receipt`. |
| Lotes (FIFO / documento) | GET/POST `/admin/stock-lots` | — | Query **`product_id`** em GET; POST aumenta `products.quantity` e cria lote (`document_reference`, `warehouse_id`, `unit_cost`, `received_at`). Consumo FIFO por linha de pedido ainda não desconta `quantity_remaining` do lote (evolução futura). |

**Rotas (detalhe)**  
| Método | Path | Descrição |
|--------|------|-----------|
| GET | `/admin/store-settings` | `data.settings` + `social_media_options` + `erp_options`. |
| PUT | `/admin/store-settings` | Atualiza `settings` da loja (JSON; primeira fase sem upload de ficheiros). |
| GET/POST | `/admin/parameters`, `/admin/parameters/{id}` | Lista e cria. |
| GET/PUT/DELETE | `/admin/parameters/{id}` | Detalhe, actualização, remoção. |
| GET | `/admin/store-users` | Utilizadores da loja (`page`, `per_page`, `search`). |
| POST | `/admin/store-users/attach` | Corpo `{ "user_id": number }` — associa à loja do header. |
| DELETE | `/admin/store-users/detach/{userId}` | Remove pivot loja–utilizador. |
| GET | `/admin/store-roles` | Roles Spatie (`page`, `per_page`, `search`). |
| GET | `/admin/permissions` | Permissões Spatie paginadas. |
| GET/POST | `/admin/store-faqs`, `/admin/store-faqs/{id}` | FAQ da loja: lista, cria, detalhe. |
| PUT/DELETE | `/admin/store-faqs/{id}` | Actualiza / remove. |
| GET/POST | `/admin/store-catalogs`, `/admin/store-catalogs/{id}` | Catálogos da loja. |
| PUT/DELETE | `/admin/store-catalogs/{id}` | Actualiza / remove. |
| GET/POST | `/admin/tokens`, `/admin/tokens/{id}` | Tokens (lista global); criação com token gerado no servidor. |
| PUT/DELETE | `/admin/tokens/{id}` | Actualização (`regenerate_token` boolean opcional); remoção. |
| GET | `/admin/tenants` | Titular do `tenant_id` da loja do header. |
| GET/PUT | `/admin/tenants/{id}` | Detalhe e actualização (só se `{id}` for o tenant da loja). |
| GET/POST | `/admin/customers`, `/admin/customers/{id}` | Clientes do tenant. |
| PUT/DELETE | `/admin/customers/{id}` | Actualização / remoção (transacção alinhada ao Blade). |
| GET/POST | `/admin/leads`, `/admin/leads/{id}` | Leads da loja. |
| PUT/DELETE | `/admin/leads/{id}` | Actualização / remoção. |
| GET/POST | `/admin/stores`, `/admin/stores/{id}` | Lojas do mesmo tenant. |
| PUT/DELETE | `/admin/stores/{id}` | Actualização / remoção. |
| GET/POST | `/admin/salesmen`, `/admin/salesmen/{id}` | Vendedores da loja. |
| PUT/DELETE | `/admin/salesmen/{id}` | Actualização / remoção. |
| GET/POST | `/admin/products`, `/admin/products/{id}` | Produtos da loja; resposta inclui **`quantity`** (saldo em estoque). |
| PUT/DELETE | `/admin/products/{id}` | Actualização / remoção; corpo inclui **`quantity`** (inteiro ≥ 0). |

**Pedidos (checkout):** `POST /orders` valida estoque antes de criar o pedido: a soma das quantidades por `product_id` não pode exceder `products.quantity` da loja (`lockForUpdate`). Erro **422** com mensagem e `data.available` / `data.requested` quando insuficiente. Cada linha gera registo em **`stock_movements`** (`order_sale`).

**Opção C (evolução):** tabelas **`warehouses`**, **`stock_lots`** (referência documental / custo / `quantity_remaining` para FIFO futuro) e **`stock_movements`** (auditoria). **Nota fiscal eletrónica (NF-e/NFC-e)** e **consumo FIFO nos pedidos** não estão implementados — exigem motor fiscal e regras de baixa por lote.

Inventário completo das telas Blade vs API/UI: **`docs/FRONTEND-BLADE-MIGRACAO.md`**.

---

## 5. Endpoints sem middleware `app`

Estes endpoints estão em **`/api/v1`** mas **fora** do grupo `app` (não enviam `store` pelo middleware; consultar cada controlador para parâmetros).

| Método | Path | Descrição |
|--------|------|-----------|
| POST | `/pagseguro/notification` | Webhook / notificação PagSeguro (há duas declarações no `routes/api.php`; a ordem de registo determina qual controlador responde — em geral a última é `NotificationPagseguroController`). |
| GET | `/get-person-by-nif` | Dados de pessoa por NIF (query `nif`). |
| POST | `/get-user-by-nif` | Utilizador por NIF. |
| GET | `/states` | Estados (UF). |
| GET | `/states/{id}` | Detalhe. |
| GET | `/cities` | Cidades (filtros conforme `CityController`). |
| GET | `/cities/{id}` | Detalhe. |
| POST | `/variation` | Grelha / variação de produto (`ProductController@getGrid`). |
| DELETE | `/variation/{id}/delete` | Remove variação. |
| GET | `/inactivate-coupon/{id}` | Inactiva cupão. |
| POST | `/public/change-status-orders` | Atualização de estado de pedido (integrações internas; envia e-mails conforme estado). |

---

## 6. API de integração (`/api/v1/integration/...`)

Middleware **`auth.integration`** (`ValidatedToken`):

| Requisito | Detalhe |
|-----------|---------|
| `app` | Mesmo cabeçalho de loja (`app_token`). |
| `Authorization` | `Bearer {access_token}` onde o token existe na tabela **`tokens`** (`access_token`, `store_id`) associado à loja. |

Recursos (REST típico `apiResource`):

| Recurso | Métodos |
|---------|---------|
| `/integration/brands` | GET, POST, GET/{id}, PUT/PATCH/{id}, DELETE/{id} |
| `/integration/products` | idem |
| `/integration/sections` | idem |
| `/integration/measurement-unit` | idem |
| `/integration/orders` | GET, GET/{id}, PUT/PATCH/{id} |

Erros comuns: **401** — `Token não informada.`, `App ID não informada.`, `App ID não válida.`, `Token inválido.`

---

## 7. Limitação de taxa (throttle)

O grupo `api` aplica `throttle:api` (ver `App\Providers\RouteServiceProvider` / `App\Http\Kernel`). Em ambiente de desenvolvimento podes ajustar limites na configuração Laravel.

---

## 8. CORS

Configuração em `config/cors.php` e middleware `HandleCors`. Em desenvolvimento local, confirma `APP_URL` e origens permitidas.

---

## 9. Referência de código

| Ficheiro | Conteúdo |
|----------|----------|
| `routes/api.php` | Lista oficial de rotas. |
| `app/Http/Middleware/CheckAppHeader.php` | Validação do header `app`. |
| `app/Http/Middleware/ValidatedToken.php` | Integração Bearer + `app`. |
| `app/Http/Controllers/API/*` | Controladores da API “loja”. |
| `app/Http/Controllers/API/Admin/*` | Endpoints de administração Gplace (migração Blade). |
| `app/Http/Controllers/Integration/*` | Controladores da API de integração. |

Para detalhes de *body* (campos obrigatórios), a fonte de verdade são as regras `Validator` / `rules()` em cada controlador (ex.: `OrderController::store`, `UserController::store`).

---

## 10. OAuth / Passport no ambiente

O login em `POST /api/v1/auth/login` chama `$user->createToken(...)` (Passport). **Sem infraestrutura Passport completa o servidor devolve HTTP 500.**

No servidor (após `composer install` e `.env` com base de dados):

```bash
php artisan migrate
php artisan passport:install
```

Isto cria as tabelas OAuth (vêm do pacote Passport), as chaves `storage/oauth-private.key` e `storage/oauth-public.key`, e o *personal access client* necessário para emitir tokens. O `AuthServiceProvider` regista `Passport::routes()` (prefixo `/oauth`).

Se o login ainda falhar, vê `storage/logs/laravel.log` e confirma permissões de escrita em `storage/`.

### CORS (browser → API em outro domínio)

No `.env` da API, define **`CORS_ALLOWED_ORIGINS`** com o URL exacto do frontend (ex.: `https://gplace.gooding.solutions`). Se estiver vazio, `config/cors.php` usa uma lista por omissão. Depois de alterar variáveis, corre `php artisan config:clear` (ou `config:cache` com o `.env` correcto). Respostas **500** também passam a incluir cabeçalhos CORS (`App\Exceptions\Handler` + `App\Support\CorsResponseHeaders`) para o browser não mascarar o erro como “CORS”.

---

## 11. Frontend Next.js (`frontend-api-gplace`)

Repositório à parte (ou pasta ignorada no Git da API). Variáveis em **`.env.local`**:

| Variável | Exemplo | Descrição |
|----------|---------|-----------|
| `NEXT_PUBLIC_API_URL` | `http://localhost:8005/api/v1` (dev) ou `https://api-gplace.gooding.solutions/api/v1` (produção) | Base da API. |
| `NEXT_PUBLIC_APP_TOKEN` | `gplace-local-frontend` (dev) ou `stores.app_token` | Em **local**, o seeder `LocalDevStoreSeeder` cria uma loja com token fixo `gplace-local-frontend` e liga ao `admin@gooding.solutions`. O `next dev` usa esse valor por omissão. Em produção, usar o token real da loja. |

O cliente HTTP em `frontend-api-gplace/lib/api.ts` define `Authorization: Bearer` após login (Passport) e o header `app` a partir de `NEXT_PUBLIC_APP_TOKEN`.
