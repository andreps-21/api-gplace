# Vincular ecommerce a uma loja por `app_token`

## Resposta curta

Sim. Quando o frontend envia o token da loja no header HTTP `app`, a API resolve essa loja por `stores.app_token` e todos os endpoints públicos dentro do middleware `app` passam a consultar dados no escopo dessa loja.

Na prática, um ecommerce configurado com:

```env
NEXT_PUBLIC_API_BASE=https://seu-dominio/api/v1
NEXT_PUBLIC_APP_ID=<stores.app_token da loja>
```

deve ver apenas catálogo, banners, secções, configurações, métodos de pagamento e pedidos relacionados à loja desse token, desde que os endpoints usados estejam cobertos pela API atual.

## Como a API identifica a loja

O middleware `App\Http\Middleware\CheckAppHeader` valida o header `app`:

1. Se o header `app` não existir, responde `403` com `App ID não informada.`.
2. Se não houver loja com `stores.app_token = app`, responde `403` com `App ID não válida.`.
3. Se encontrar a loja, adiciona os dados da loja ao request em:
   - `$request->attributes->get('store')`
   - `$request->get('store')`

Os controladores públicos usam esse contexto para filtrar por `store_id` ou `tenant_id`.

## Endpoints públicos filtrados pelo token

As rotas abaixo estão no grupo `middleware(['app'])`, portanto exigem o header `app`:

- `GET /products` e `GET /products/{id}`: produtos ativos da loja.
- `GET /home`: home da loja, incluindo secções, banners, ranking e settings.
- `GET /settings`: configurações da loja.
- `GET /banners`: banners da loja.
- `GET /sections` e `GET /sections-home`: secções da loja.
- `GET /faqs` e `GET /catalogs`: conteúdo da loja.
- `GET /payment-methods`: métodos de pagamento habilitados e vinculados à loja.
- `GET /coupons`: cupons aplicáveis à unidade de negócio/localização.
- `POST /calc-freight`, `/contact`, `/leads`, `/auth/users`, `/auth/user-lead`.

Rotas de cliente autenticado também ficam dentro do grupo `app` e ainda exigem `Authorization: Bearer`:

- `GET/POST /orders`
- `GET/POST/PUT/DELETE /addresses`

Essas rotas usam simultaneamente:

- `app`: loja ativa da vitrine.
- `Authorization: Bearer`: cliente autenticado.

Assim, pedidos e endereços são lidos/criados no contexto da loja informada pelo token.

## O que o token não faz

O `app_token` não é token de utilizador, não autentica painel admin e não substitui Sanctum. Ele apenas seleciona o contexto de loja para a vitrine.

Para painel/admin, as rotas usam `auth:sanctum` + `user_store`. Nesse caso o header `app` é opcional e só escolhe uma loja entre as lojas vinculadas ao utilizador autenticado.

## Compatibilidade com `g-place-next`

O frontend `/Users/andrepereiradesousa/Documents/FRONTEND/g-place-next` já possui o cliente central `lib/api-client.ts`, que envia:

- `app: env.NEXT_PUBLIC_APP_ID`
- `Authorization: Bearer <token>` quando há sessão do cliente

Portanto, para vincular o ecommerce a uma loja da `api-gplace`, configure `NEXT_PUBLIC_APP_ID` com o `stores.app_token` dessa loja.

Compatível com a API atual:

- Home/catálogo: `/home`, `/products`, `/products/{id}`.
- Checkout base: `/payment-methods`, `/public-key`, `/pagseguro-session`, `/pagseguro-installments`, `/calc-freight`, `/orders`.
- Conta do cliente: `/auth/login`, `/auth/profile`, `/auth/change-password`, `/addresses`, `/orders`.
- Cadastro/recuperação: `/auth/user-lead`, `/auth/password/*`.
- Carrinho autenticado: `/cart`, `/cart/{id}`.
- Favoritos autenticados: `/wishlist`, `/wishlist-products`, `/wishlist/{id}`.
- Avaliações autenticadas: `/ratings`.
- Cidades: `/cities`.

Notas de implementação:

- Carrinho, favoritos e avaliações exigem cliente autenticado (`Authorization: Bearer`) e continuam usando o header `app` para isolar os dados por loja.
- Avaliações são aceitas apenas para itens de pedidos do próprio cliente na loja ativa.
- O detalhe do produto retorna `ratings` e média `rating` para a UI exibir reviews.

## Checklist para vincular uma loja

1. No painel admin de lojas, copie o token da coluna `Token`.
2. No ecommerce `g-place-next`, defina:

```env
NEXT_PUBLIC_API_BASE=http://localhost:8005/api/v1
NEXT_PUBLIC_APP_ID=<token copiado da loja>
NEXT_PUBLIC_IMG_URL=<url pública do storage>
NEXT_PUBLIC_NO_IMG_URL=<url base para imagem fallback>
NEXT_PUBLIC_SITE_URL=http://localhost:3000
API_APP_SECRET=<mesmo token, apenas se mantiver uso server-only>
```

3. Reinicie o servidor Next.
4. Abra a home e confirme:
   - `GET /home` retorna settings/banners/secções da loja esperada.
   - `GET /products` retorna apenas produtos com `products.store_id` da loja do token.
   - Produtos de outra loja não aparecem e `GET /products/{id}` de produto de outra loja responde 404.

