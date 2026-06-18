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

## 2. Contexto de loja: cabeçalho `app` vs. utilizador autenticado

Existem **dois** mecanismos que preenchem `store` no request (formato compatível com os controladores):

| Middleware | Quando aplica | Cabeçalho `app` | Comportamento |
|------------|---------------|-----------------|----------------|
| **`app`** (`CheckAppHeader`) | Catálogo público, registo de cliente na loja, pedidos/moradas do **cliente** na vitrine | **Obrigatório** nesses endpoints | Resolve a loja por `stores.app_token`. Erros típicos: **403** — `App ID não informada.` / `App ID não válida.` |
| **`user_store`** (`BindAuthenticatedUserStore`) | Rotas do **painel** após `auth:sanctum` (logout, perfil, dashboard, `/admin/*`, etc.) | **Opcional** | O utilizador tem de ter pelo menos uma loja no pivot `user_stores`. Usa a **primeira** loja (por `stores.id`) ou, se enviares `app` e for válido **e** o user pertencer a essa loja, usa essa. Erros: **403** sem loja associada; **500** se não for possível resolver a loja. |

**Resumo:** o **login** (`POST /auth/login`) e a **recuperação de password** **não** exigem `app`. O **painel** autenticado por Sanctum **não** exige `app`; o **ecommerce / catálogo público** (e registo `POST /auth/users` na loja) **exigem** `app`.

---

## 3. Autenticação do utilizador (Laravel Sanctum)

As rotas protegidas usam o middleware **`auth:sanctum`**. O token é um **personal access token** (tabela `personal_access_tokens`).

| Uso | Cabeçalho |
|-----|-----------|
| Rotas `auth:sanctum` | `Authorization: Bearer {token}` |

O token obtém-se em **`POST /api/v1/auth/login`** com `email` e `password` **sem** obrigatoriedade do header `app`. Após o login, as rotas do painel usam **`auth:sanctum` + `user_store`**: a loja activa vem do utilizador (ver secção 2).

**Exemplo (perfil no painel — `app` opcional):**

```http
GET /api/v1/auth/profile
Authorization: Bearer {token}
```

Se o utilizador tiver **várias** lojas, podes enviar `app: {app_token}` para forçar o contexto dessa loja (desde que o utilizador pertença a ela).

**Rota utilitária (sem prefixo `v1` no ficheiro — atenção ao path):**

| Método | Path | Auth |
|--------|------|------|
| GET | `/api/user` | `auth:sanctum` |

---

## 4. Endpoints por grupo

Todas as URLs abaixo são relativas a **`/api/v1`**.

### 4.1 Login e recuperação de password (sem middleware `app`)

| Método | Path | `auth:sanctum` | Descrição |
|--------|------|----------------|-------------|
| POST | `/auth/login` | Não | `email`, `password` — devolve `user` + `token` (Sanctum). Não exige header `app`. |
| POST | `/auth/password/email` | Não | Pedido de recuperação de password. |
| POST | `/auth/password/code/check` | Não | Validação de código. |
| POST | `/auth/password/reset` | Não | Reset de password com código. |

### 4.2 Painel autenticado (`auth:sanctum` + `user_store`)

Cabeçalho **`app`** opcional (utilizadores com várias lojas). Inclui conta, dashboard, vendas, estabelecimentos, notificações (stubs) e **`/admin/*`** (detalhe na secção 4.6).

| Método | Path | Descrição |
|--------|------|-----------|
| DELETE | `/auth/logout` | Revoga o token actual. |
| GET | `/auth/profile` | Perfil do utilizador autenticado. |
| PUT | `/auth/profile` | Actualiza perfil. |
| POST | `/auth/change-password` | Alteração de password. |
| POST | `/auth/change-first-password` | Define a primeira senha quando o perfil retorna `requires_first_password: true`. |
| GET | `/dashboard/stats` | Estatísticas. |
| GET | `/dashboard/faturamento` | Faturamento. |
| GET | `/sales` | Listagem de vendas (loja resolvida por `user_store`). |
| GET | `/establishments/stats` | Stats de estabelecimentos. |
| GET | `/establishments` | Lista de estabelecimentos. |
| GET | `/notifications/inbox` | Inbox (resposta mínima para o Next). |
| POST | `/notifications/dismiss` | Dismiss de notificação. |
| POST | `/notifications/dismiss-all` | Dismiss todas. |

### 4.3 Registo na vitrine (middleware `app` obrigatório)

| Método | Path | `auth:sanctum` | Descrição |
|--------|------|----------------|-------------|
| POST | `/auth/users` | Não | Registo de utilizador / pessoa na loja (`UserController`). |
| POST | `/auth/user-lead` | Não | Registo tipo lead (`UserLeadController`). |

### 4.4 Catálogo e conteúdo (público com `app`)

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

### 4.5 Pedidos e moradas do cliente na vitrine (`app` + `auth:sanctum`)

| Método | Path | Descrição |
|--------|------|-----------|
| GET | `/orders` | Lista pedidos do cliente autenticado na loja. |
| GET | `/orders/{id}` | Detalhe do pedido. |
| POST | `/orders` | Cria pedido (payload complexo: totais, `items[]`, `payment_method_id`, dados de cliente/cartão conforme meio de pagamento — ver `OrderController::rules`). **Estoque:** a API recusa o pedido (422) se não houver quantidade suficiente por produto na loja. |
| GET | `/cart` | Carrinho do cliente autenticado na loja (`data.cart`). |
| POST | `/cart` | Cria/atualiza item do carrinho (`product_id`, `quantity`). Quando `isButton=true`, soma à quantidade existente; caso contrário substitui a quantidade. |
| DELETE | `/cart/{id}` | Remove item do carrinho do cliente na loja. |
| GET | `/wishlist` | Favoritos do cliente na loja, com produto e imagens. |
| GET | `/wishlist-products` | Referências leves de favoritos (`id`, `product_id`) para toggle rápido. |
| POST | `/wishlist` | Adiciona produto aos favoritos (`product_id`). |
| DELETE | `/wishlist/{id}` | Remove favorito do cliente na loja. |
| POST | `/ratings` | Avalia um item de pedido do próprio cliente na loja (`order_item_id`, `rating`, `comment`). |
| GET | `/addresses` | Lista moradas. |
| POST | `/addresses` | Cria morada. |
| GET | `/addresses/{id}` | Detalhe. |
| PUT/PATCH | `/addresses/{id}` | Atualiza. |
| DELETE | `/addresses/{id}` | Remove (pode falhar se houver pedidos vinculados). |

**Checkout (`POST /orders`):** valida estoque antes de criar o pedido: a soma das quantidades por `product_id` não pode exceder `products.quantity` da loja (`lockForUpdate`). Erro **422** com mensagem e `data.available` / `data.requested` quando insuficiente. Cada linha gera registo em **`stock_movements`** (`order_sale`).

### 4.6 Administração Gplace (`auth:sanctum` + `user_store`)

Migração das áreas de **configuração** e **gerenciamento** do Blade. O escopo da loja é resolvido pelo middleware **`user_store`** (associação utilizador–loja; header **`app`** opcional para escolher loja). Não confundir com as secções **4.3–4.5**, onde o catálogo e o checkout do **cliente** na vitrine exigem o header **`app`**.

**Módulos (resumo)**  
| Módulo | Leitura na API | Escrita na API | Notas UI Next |
|--------|----------------|----------------|---------------|
| Config. da loja | GET `/admin/store-settings` | PUT `/admin/store-settings` | Já existia. |
| Parâmetros | GET `/admin/parameters` | POST/GET/PUT/DELETE `/admin/parameters` e `/{id}` | CRUD na página admin. |
| Utilizadores da loja | GET `/admin/store-users` | POST/GET/PUT/DELETE `/admin/store-users` e `/{id}`, attach/detach | Cria/edita utilizador + pessoa + role, associa à loja e permite gerir lojas vinculadas do tenant. |
| Roles / Permissões | GET `/admin/store-roles`, GET `/admin/permissions` | CRUD `/admin/store-roles`, CRUD `/admin/permissions`, PUT `/admin/store-roles/{id}/permissions` | Gestão Spatie via API admin. Roles globais continuam protegidas para administradores de plataforma. |
| FAQ | GET `/admin/store-faqs` | POST/GET/PUT/DELETE `/admin/store-faqs` e `/{id}` | CRUD na página admin. |
| Catálogos | GET `/admin/store-catalogs` | POST/GET/PUT/DELETE `/admin/store-catalogs` e `/{id}` | CRUD JSON; upload de imagem opcional via multipart no mesmo controlador Laravel. |
| Tokens | GET `/admin/tokens` | POST/GET/PUT/DELETE `/admin/tokens` e `/{id}` | `store_id` omissão → loja do contexto (`user_store`); criação devolve `access_token_plain` uma vez. |
| Tenant | GET `/admin/tenants`, GET `/admin/tenants/{id}` | POST `/admin/tenants`, PUT `/admin/tenants/{id}`, DELETE `/admin/tenants/{id}` | POST exige `tenants_create`; DELETE exige `tenants_delete` (não permite apagar o tenant da **loja activa** resolvida). Listagem completa se `tenants_create` ou `tenants_edit`; senão só o titular da loja activa. |
| Clientes | GET `/admin/customers` | POST/GET/PUT/DELETE `/admin/customers` e `/{id}` | API completa; UI pode usar os mesmos endpoints (formulários extensos). |
| Leads | GET `/admin/leads` | POST/GET/PUT/DELETE `/admin/leads` e `/{id}` | CRUD na página admin. |
| Lojas | GET `/admin/stores` | POST/GET/PUT/DELETE `/admin/stores` e `/{id}` | API completa; `paymentMethods` opcional em create/update. |
| Vendedores | GET `/admin/salesmen` | POST/GET/PUT/DELETE `/admin/salesmen` e `/{id}` | API alinhada ao Blade (person + pivot loja + role vendedor no create). |
| Produtos | GET `/admin/products` | POST/GET/PUT/DELETE `/admin/products` e `/{id}` | `quantity` (inteiro ≥ 0), **`min_stock`** opcional (alerta de stock baixo na UI), **`stock_change_note`** opcional ao ajustar quantidade; movimentos em `stock_movements`. |
| Armazéns | GET/POST `/admin/warehouses` | GET/PUT/DELETE `/admin/warehouses/{id}` | Base multi-armazém (opção C); nome, `code`, `is_default`. |
| Movimentos de stock | GET `/admin/stock-movements` | — | `product_id` opcional; sem ele lista movimentos recentes da loja. Tipos: `admin_create`, `admin_adjust`, `order_sale`, `lot_receipt`. |
| Lotes (FIFO / documento) | GET/POST `/admin/stock-lots` | — | `product_id` opcional em GET; POST aumenta `products.quantity` e cria lote (`document_reference`, `warehouse_id`, `unit_cost`, `received_at`). Consumo FIFO por linha de pedido ainda não desconta `quantity_remaining` do lote (evolução futura). |
| Estados / Cidades | GET `/admin/states`, GET `/admin/cities` | CRUD `/admin/states`, CRUD `/admin/cities` | Administração das referências usadas nos formulários de pessoas/lojas. |
| Cupons | GET `/admin/coupons` | CRUD `/admin/coupons` | Escopo da loja activa; devolve também `business_units`, `sponsors` e `applies` para formulário. |
| Unidades de negócio | GET `/admin/business-units` | CRUD `/admin/business-units` | Escopo do tenant da loja activa; usa cidade e faixa de CEP. |
| Formas de pagamento | GET `/admin/payment-methods-admin` | CRUD `/admin/payment-methods-admin` | CRUD global. `GET /admin/payment-methods` continua como leitura de métodos habilitados da loja para venda/produtos. |
| ERP | GET `/admin/erp` | CRUD `/admin/erp` | CRUD global simples. |
| Redes sociais | GET `/admin/social-medias` | CRUD `/admin/social-medias` | CRUD global simples; `icon` pode ser path/string no JSON. |

**Rotas (detalhe)**  
| Método | Path | Descrição |
|--------|------|-----------|
| GET | `/admin/store-settings` | `data.settings` + `social_media_options` + `erp_options`. |
| PUT | `/admin/store-settings` | Atualiza `settings` da loja (JSON; primeira fase sem upload de ficheiros). |
| GET/POST | `/admin/parameters`, `/admin/parameters/{id}` | Lista e cria. |
| GET/PUT/DELETE | `/admin/parameters/{id}` | Detalhe, actualização, remoção. |
| GET | `/admin/store-users` | Utilizadores da loja (`page`, `per_page`, `search`). |
| POST | `/admin/store-users` | Cria utilizador e associa à loja do contexto ou `store_ids`. Campos principais: `name`, `email`, `nif`, `phone`, `city_id`, `role_id`, `store_ids`, `password`, `password_confirmation`. |
| GET/PUT/DELETE | `/admin/store-users/{id}` | Detalhe, edição e remoção/remoção dos vínculos do utilizador nas lojas do tenant. |
| POST | `/admin/store-users/attach` | Corpo `{ "user_id": number }` — associa à loja do contexto (`user_store`). |
| DELETE | `/admin/store-users/detach/{userId}` | Remove pivot loja–utilizador. |
| GET | `/admin/store-roles` | Roles Spatie (`page`, `per_page`, `search`). |
| POST/GET/PUT/DELETE | `/admin/store-roles`, `/admin/store-roles/{id}` | CRUD de roles do tenant; `permission_ids` sincroniza permissões no create/update. |
| PUT | `/admin/store-roles/{id}/permissions` | Sincronização total de permissões por `permission_ids`. |
| GET/POST/PUT/DELETE | `/admin/permissions`, `/admin/permissions/{id}` | CRUD de permissões Spatie. |
| GET/POST | `/admin/store-faqs`, `/admin/store-faqs/{id}` | FAQ da loja: lista, cria, detalhe. |
| PUT/DELETE | `/admin/store-faqs/{id}` | Actualiza / remove. |
| GET/POST | `/admin/store-catalogs`, `/admin/store-catalogs/{id}` | Catálogos da loja. |
| PUT/DELETE | `/admin/store-catalogs/{id}` | Actualiza / remove. |
| GET/POST | `/admin/tokens`, `/admin/tokens/{id}` | Tokens (lista global); criação com token gerado no servidor. |
| PUT/DELETE | `/admin/tokens/{id}` | Actualização (`regenerate_token` boolean opcional); remoção. |
| GET | `/admin/tenants` | Titular(is): só o da loja activa, ou listagem completa se o utilizador tiver `tenants_create` ou `tenants_edit`. |
| POST | `/admin/tenants` | Cria contratante (corpo igual ao PUT de actualização). Resposta 201; senha inicial do `User`: dígitos do NIF. |
| GET/PUT | `/admin/tenants/{id}` | Detalhe e actualização: o próprio tenant da loja activa, ou outro `id` se `tenants_edit` / visualização se `tenants_create` ou `tenants_edit`. |
| DELETE | `/admin/tenants/{id}` | Remove o tenant (`tenants_delete`). Bloqueado se `{id}` for o tenant da **loja activa** ou se existirem vínculos (lojas, etc.). |
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
| GET/POST | `/admin/coupons`, `/admin/coupons/{id}` | Cupons da loja. |
| PUT/DELETE | `/admin/coupons/{id}` | Actualização / remoção. |
| GET/POST | `/admin/business-units`, `/admin/business-units/{id}` | Unidades de negócio do tenant. |
| PUT/DELETE | `/admin/business-units/{id}` | Actualização / remoção. |
| GET/POST | `/admin/payment-methods-admin`, `/admin/payment-methods-admin/{id}` | CRUD admin de formas de pagamento. |
| PUT/DELETE | `/admin/payment-methods-admin/{id}` | Actualização / remoção. |
| GET/POST | `/admin/erp`, `/admin/erp/{id}` | CRUD admin de ERP. |
| PUT/DELETE | `/admin/erp/{id}` | Actualização / remoção. |
| GET/POST | `/admin/social-medias`, `/admin/social-medias/{id}` | CRUD admin de redes sociais. |
| PUT/DELETE | `/admin/social-medias/{id}` | Actualização / remoção. |

**Opção C (evolução):** tabelas **`warehouses`**, **`stock_lots`** (referência documental / custo / `quantity_remaining` para FIFO futuro) e **`stock_movements`** (auditoria). **Nota fiscal eletrónica (NF-e/NFC-e)** e **consumo FIFO nos pedidos** não estão implementados — exigem motor fiscal e regras de baixa por lote.

Inventário completo das telas Blade vs API/UI: **`docs/FRONTEND-BLADE-MIGRACAO.md`**.

Fluxo de vinculação de ecommerce por token de loja: **`docs/ECOMMERCE-LOJA-TOKEN.md`**.

---

## 5. Endpoints sem middleware `app` (além do painel `user_store`)

Estes endpoints estão em **`/api/v1`** **sem** o grupo `middleware(['app'])`. O **login** e a **recuperação de password** estão aqui; o **painel** autenticado usa `user_store` (secção 4.2). Consultar cada controlador para parâmetros.

| Método | Path | Descrição |
|--------|------|-----------|
| POST | `/auth/login` | Ver secção 4.1. |
| POST | `/auth/password/email`, `/auth/password/code/check`, `/auth/password/reset` | Ver secção 4.1. |
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
| `app/Http/Middleware/CheckAppHeader.php` | Validação do header `app` (catálogo / vitrine). |
| `app/Http/Middleware/BindAuthenticatedUserStore.php` | Middleware `user_store`: loja do utilizador autenticado (header `app` opcional). |
| `app/Http/Middleware/ValidatedToken.php` | Integração Bearer + `app`. |
| `app/Http/Controllers/API/*` | Controladores da API “loja”. |
| `app/Http/Controllers/API/Admin/*` | Endpoints de administração Gplace (migração Blade). |
| `app/Http/Controllers/Integration/*` | Controladores da API de integração. |

Para detalhes de *body* (campos obrigatórios), a fonte de verdade são as regras `Validator` / `rules()` em cada controlador (ex.: `OrderController::store`, `UserController::store`).

---

## 10. Sanctum no ambiente

O login em `POST /api/v1/auth/login` valida email/password com `Hash::check` (não usa `Auth::attempt` do guard `web`, porque as rotas `api/*` não têm sessão e isso gerava 500). Em seguida chama `$user->createToken(...)` (Sanctum). É necessária a tabela **`personal_access_tokens`** (migração incluída no projecto). O login **não** depende do header `app`.

No servidor (após `composer install` e `.env` com base de dados):

```bash
php artisan migrate
```

Não é necessário `passport:install` nem ficheiros `storage/oauth-*.key`.

Se o login ainda falhar, vê `storage/logs/laravel.log` e confirma que a migração `personal_access_tokens` correu.

### CORS (browser → API em outro domínio)

No `.env` da API, define **`CORS_ALLOWED_ORIGINS`** com o URL exacto do frontend (ex.: `https://gplace.gooding.solutions`). Se estiver vazio, `config/cors.php` usa uma lista por omissão. Depois de alterar variáveis, corre `php artisan config:clear` (ou `config:cache` com o `.env` correcto). Respostas **500** também passam a incluir cabeçalhos CORS (`App\Exceptions\Handler` + `App\Support\CorsResponseHeaders`) para o browser não mascarar o erro como “CORS”.

---

## 11. Frontend Next.js (`frontend-api-gplace`)

Repositório à parte (ou pasta ignorada no Git da API). Variáveis em **`.env.local`**:

| Variável | Exemplo | Descrição |
|----------|---------|-----------|
| `NEXT_PUBLIC_API_URL` | `http://localhost:8005/api/v1` (dev) ou `https://api-gplace.gooding.solutions/api/v1` (produção) | Base da API. |
| `NEXT_PUBLIC_APP_TOKEN` | `gplace-local-frontend` (dev) ou `stores.app_token` | **Obrigatório** para chamadas ao **catálogo público / vitrine** (`products`, `home`, etc.) e fluxos que usam o middleware `app`. **Opcional** para o **painel** só com login (Sanctum): a API infere a loja pelo utilizador; define o token se quiseres forçar uma loja ou testar a vitrine. Em local, sem variável, o cliente pode usar o token de desenvolvimento `gplace-local-frontend` quando a API é local (ver `lib/public-env.ts`). |

O cliente HTTP em `frontend-api-gplace/lib/api.ts` envia `Authorization: Bearer` após login e o header `app` quando `getResolvedAppToken()` devolve valor; avisos no consola sobre `app` aplicam-se sobretudo a rotas de catálogo/ecommerce.

### Produção: obter ou regenerar o `app_token`

Se **`stores` estiver vazia** (primeiro deploy), cria tenant + loja + token ligado ao utilizador admin:

```bash
php artisan store:bootstrap
# ou outro utilizador já existente na BD:
php artisan store:bootstrap email@empresa.com
```

Requer pelo menos um **User** com **person** (ex.: `UserSeeder`). Depois copia `NEXT_PUBLIC_APP_TOKEN` para o Vercel.

No servidor (SSH), na pasta da API:

```bash
# Ver lojas e tokens actuais (sem alterar)
php artisan store:issue-app-token --show

# Gerar um token novo para a primeira loja (pede confirmação se já existir token)
php artisan store:issue-app-token

# Gerar para a loja com id 1
php artisan store:issue-app-token 1

# Regenerar sem pergunta interactiva (útil em CI / scripts)
php artisan store:issue-app-token 1 --force
```

O comando imprime a linha `NEXT_PUBLIC_APP_TOKEN=...` para copiares para o Vercel e fazer **redeploy** do frontend. Regenerar invalida o valor anterior até actualizares o deploy.
