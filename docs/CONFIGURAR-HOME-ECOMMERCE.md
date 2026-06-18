# Configurar a Home do ecommerce pelo frontend da API

Este guia explica como preparar uma loja no `frontend-api-gplace` para que o ecommerce `g-place-next` exiba dados na home (`GET /api/v1/home`).

## Diagnóstico da loja Ramom Oliveira Pereira

No estado atual da loja conectada ao ecommerce:

- A loja tem 21 produtos ativos.
- Os 21 produtos têm imagens cadastradas em `product_images`.
- A API `GET /api/v1/products` retorna produtos com imagem.
- A API `GET /api/v1/home` retorna `sections_home = []`, `banners = []` e sem ranking.
- A tabela `sections` não tem nenhuma seção para `store_id = 2`.
- Os produtos da loja estão com `section_id = NULL`.
- A tabela `product_section` não tem vínculos para os produtos dessa loja.

Conclusão: as imagens existem, mas a home não mostra produtos porque a home depende de seções ativas marcadas para exibição na home e com produtos associados.

## Como a home é montada

O ecommerce chama:

```http
GET /api/v1/home
Header: app: <stores.app_token>
```

A API resolve a loja pelo token e monta a resposta no `HomeController`.

### Novo fluxo: seções da home independentes das categorias

A home agora pode ser configurada por seções visuais em `home_blocks`. O nome técnico da tabela é `home_blocks`, mas no painel essa configuração aparece como **Seções**.

Tipos suportados:

- `categories`: mostra cards de categorias/seções.
- `products`: mostra cards de produtos.
- `brands`: mostra cards de marcas.
- `banners`: suportado na API, mas depende de cadastro de banners.

Cada bloco tem:

- `title`: título exibido na home.
- `type`: tipo do bloco.
- `sort_order`: ordem de exibição.
- `is_enabled`: liga/desliga o bloco.
- `items`: IDs dos itens do tipo escolhido.

Esse fluxo separa:

- `sections`: categorias reais dos produtos, exibidas no painel como **Categorias**.
- `home_blocks`: seções visuais da página inicial, exibidas no painel como **Seções**.

### Fluxo antigo: seções marcadas como home

O campo antigo `sections_home` continua existindo para compatibilidade.

Para produtos aparecerem nos blocos antigos da home, a API exige:

- A seção deve pertencer à loja do token (`sections.store_id`).
- A seção deve estar ativa (`sections.is_enabled = true`).
- A seção deve estar marcada para home (`sections.is_home = true`).
- A seção deve ter ao menos um produto ativo associado.
- O produto deve pertencer à mesma loja (`products.store_id`).
- O produto deve estar ativo (`products.is_enabled = true`).
- O produto precisa estar associado à seção por `products.section_id` ou pelo vínculo extra em `product_section`.

As imagens são lidas por `product.images`. Se o produto não tiver imagem, a API coloca `images/noimage.png`.

## Configuração pelo `frontend-api-gplace`

Antes de configurar, acesse o painel administrativo e selecione no header a loja correta. Para esta loja, selecione **Ramom Oliveira Pereira**.

### 1. Criar categorias

Menu:

`Operacionais > Categorias`

Crie pelo menos uma seção, por exemplo:

- Nome: `Bonés`
- Tipo: `Seção`
- Activa: `Sim`
- Home: `Sim`
- Ordem: `1`
- Ordem home: `1`

Opcionalmente crie outra:

- Nome: `Chapéus`
- Tipo: `Seção`
- Activa: `Sim`
- Home: `Sim`
- Ordem: `2`
- Ordem home: `2`

Observações:

- Para `Tipo = Subseção`, o campo `Seção pai` é obrigatório.
- Se `Home = Sim`, `Ordem home` também é obrigatória pela validação da API.
- O frontend chama `POST /admin/sections` ou `PUT /admin/sections/{id}`.

### 2. Associar produtos às categorias

Menu:

`Operacionais > Produtos`

Para cada produto que deve aparecer na home:

1. Abra o produto para editar.
2. Vá ao passo `Catálogo`.
3. Selecione a `Categoria principal`.
4. Mantenha `Activo = Sim`.
5. Confirme se o produto tem preço, estoque e imagem.
6. Salve.

O formulário envia:

- `section_id`: seção principal do produto.
- `sections`: lista de seções vinculadas ao produto.

A home consegue ler tanto o vínculo principal (`products.section_id`) quanto vínculos extras (`product_section`).

### 3. Criar seções da home

Menu:

`Operacionais > Seções`

Crie as seções visuais que a home deve renderizar.

Exemplo de seção da home com categorias:

- Título: `Categorias`
- Tipo: `Categorias`
- Ativo: `Sim`
- Ordem: `1`
- Itens: `Bonés`, `Chapéus`

Exemplo de seção da home com produtos:

- Título: `Produtos em destaque`
- Tipo: `Produtos`
- Ativo: `Sim`
- Ordem: `2`
- Itens: produtos selecionados manualmente

Exemplo de seção da home com marcas:

- Título: `Marcas`
- Tipo: `Marcas`
- Ativo: `Sim`
- Ordem: `3`
- Itens: marcas públicas do tenant

O frontend chama:

```http
GET /admin/home-blocks
POST /admin/home-blocks
PUT /admin/home-blocks/{id}
DELETE /admin/home-blocks/{id}
```

Quando existe ao menos uma seção da home ativa, o `g-place-next` renderiza `home_blocks`. Se não existir nenhuma seção configurada, ele usa o fluxo antigo de `sections_home`.

### 4. Conferir imagens

Menu:

`Operacionais > Produtos`

Na listagem de produtos, use o indicador/filtro de imagem quando disponível. Para enviar ou substituir imagens, o frontend usa:

```http
POST /admin/products/{id}/images
Content-Type: multipart/form-data
Campo: images[]
```

No ecommerce, as imagens são montadas a partir de:

```env
NEXT_PUBLIC_IMG_URL=http://localhost:8005/storage/
```

Com isso, um valor salvo como `products/local-ramom/product-2.jpg` vira:

```text
http://localhost:8005/storage/products/local-ramom/product-2.jpg
```

### 5. Configurar dados institucionais da loja

Menu:

`Configurações > Configuração`

Essa tela alimenta `settings` dentro de `/home`, usado por rodapé, termos, política de privacidade e dados de contato.

Preencha principalmente:

- Nome fantasia
- Razão social
- CPF/CNPJ
- E-mail
- Telefone
- WhatsApp
- CEP/endereço/cidade
- URL do portal
- Termos de uso
- Política de privacidade
- Rodapé

O frontend chama:

```http
GET /admin/store-settings
PUT /admin/store-settings
```

### 6. Banners e mídias

O endpoint `/home` também retorna `banners`, mas no `frontend-api-gplace` atual o módulo:

`Operacionais > Mídias/Anúncios`

ainda está marcado como pendente:

```text
CRUD admin ainda não migrado para a API v1.
```

Então, pelo frontend Next atual, não há tela funcional para cadastrar banners. Para banners aparecerem na home hoje, é necessário usar o Blade legado ou implementar o CRUD admin de banners na API/frontend.

Mesmo sem banners, os blocos aparecem se `home_blocks` vier preenchido.

## Pontos importantes

### Cache da home

O `GET /home` usa cache por loja:

```text
cms-home-{store_id}
```

com duração de 2 minutos.

Depois de alterar seções, produtos ou settings, a home pode demorar até 2 minutos para refletir a mudança, a menos que o cache seja limpo. O CRUD de `home_blocks` limpa esse cache automaticamente.

### Ranking de produtos

O `g-place-next` tenta renderizar uma seção de ranking (`home.rank`), mas o `HomeController` atual não inclui `rank` na resposta. Portanto, a seção "produtos que mais tiveram visualizações" fica vazia até a API passar a retornar esse campo.

### Catálogo versus Home

`GET /products` lista produtos ativos da loja, mesmo sem seção.

`GET /home` não lista todos os produtos automaticamente. No fluxo novo, ele monta a home a partir de `home_blocks`. No fluxo antigo, monta a partir de seções ativas marcadas para home.

Por isso a loja pode ter produtos com imagem no catálogo e, ao mesmo tempo, a home ficar vazia.

## Checklist mínimo para a loja Ramom

Para a home começar a exibir produtos:

1. No `frontend-api-gplace`, selecione a loja **Ramom Oliveira Pereira** no header.
2. Em `Operacionais > Categorias`, crie a categoria `Bonés`.
3. Em `Operacionais > Categorias`, crie a categoria `Chapéus`.
4. Edite os produtos de boné e associe à categoria `Bonés`.
5. Edite os produtos de chapéu e associe à categoria `Chapéus`.
6. Em `Operacionais > Seções`, crie uma seção `Categorias` com tipo de conteúdo `Categorias`.
7. Adicione `Bonés` e `Chapéus` nessa seção.
8. Opcionalmente crie uma seção `Produtos em destaque` com tipo de conteúdo `Produtos`.
9. Abra `http://localhost:3000`.
10. Valide `GET /api/v1/home`: `home_blocks` deve conter blocos com itens.

