#!/usr/bin/env bash
# Sobe o stack Docker e prepara a app: .env → containers → composer → key → migrate → seed.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT"

echo "==> api-gplace — Docker start / bootstrap"

if ! docker info >/dev/null 2>&1; then
  echo "Erro: o daemon Docker não está acessível. Inicia o Docker e tenta de novo."
  exit 1
fi

if docker compose version >/dev/null 2>&1; then
  COMPOSE=(docker compose)
elif docker-compose version >/dev/null 2>&1; then
  COMPOSE=(docker-compose)
else
  echo "Erro: instala Docker Compose (plugin 'docker compose' ou binário 'docker-compose')."
  exit 1
fi

if [[ ! -f .env.example ]]; then
  echo "Erro: falta .env.example na raiz do projeto."
  exit 1
fi

NEW_ENV=0
if [[ ! -f .env ]]; then
  cp .env.example .env
  NEW_ENV=1
  echo "==> Criado .env a partir de .env.example (revisa variáveis se precisares)."
else
  echo "==> .env já existe — mantido (não sobrescrito)."
fi

if [[ "${NEW_ENV}" == "1" ]]; then
  if command -v perl >/dev/null 2>&1; then
    perl -i -pe 's/^DB_PASSWORD=\s*$/DB_PASSWORD=secret/' .env
    perl -i -pe 's|^APP_URL=.*$|APP_URL=http://localhost:8005|' .env
    echo "==> Ajuste inicial Docker: DB_PASSWORD=secret, APP_URL=http://localhost:8005 (MySQL do Compose)."
  else
    echo "==> Aviso: define DB_PASSWORD=secret e APP_URL=http://localhost:8005 no .env para o stack Docker."
  fi
fi

mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache bootstrap/cache 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

echo "==> A subir e a construir contentores (pode demorar na primeira vez)..."
"${COMPOSE[@]}" up -d --build

_read_env_kv() {
  local key="$1"
  grep -E "^${key}=" .env 2>/dev/null | cut -d= -f2- | tr -d '\r' | sed "s/^['\"]//;s/['\"]$//" | sed 's/^[[:space:]]*//;s/[[:space:]]*$//'
}
DB_ENSURE="$(_read_env_kv DB_DATABASE)"
DB_ENSURE="${DB_ENSURE:-loja}"
DB_ENSURE="${DB_ENSURE//\`/}"
echo "==> Garantir base de dados \"${DB_ENSURE}\" (CREATE IF NOT EXISTS; corrige volumes antigos sem loja)..."
"${COMPOSE[@]}" exec -T mysql mysql -uroot -psecret -e "CREATE DATABASE IF NOT EXISTS \`${DB_ENSURE}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "==> composer install (no contentor app)..."
"${COMPOSE[@]}" exec -T -e COMPOSER_ALLOW_SUPERUSER=1 app composer install --no-interaction --prefer-dist --no-progress

if ! grep -qE '^APP_KEY=base64:[A-Za-z0-9+/]+=*' .env 2>/dev/null; then
  echo "==> php artisan key:generate"
  "${COMPOSE[@]}" exec -T app php artisan key:generate --force --no-interaction
else
  echo "==> APP_KEY já definido — key:generate ignorado."
fi

echo "==> php artisan migrate --force"
"${COMPOSE[@]}" exec -T app php artisan migrate --force --no-interaction

echo "==> php artisan db:seed --force (estados/cidades podem demorar um pouco)"
"${COMPOSE[@]}" exec -T app php artisan db:seed --force --no-interaction

_show_port() { grep -E "^${1}=" .env 2>/dev/null | cut -d= -f2- | tr -d '\r' || true; }
APP_PORT_HOST="$(_show_port APP_PORT)"
MYSQL_PORT_HOST="$(_show_port MYSQL_PORT)"
PHPMYADMIN_PORT_HOST="$(_show_port PHPMYADMIN_PORT)"
APP_PORT_HOST="${APP_PORT_HOST:-8005}"
MYSQL_PORT_HOST="${MYSQL_PORT_HOST:-3311}"
PHPMYADMIN_PORT_HOST="${PHPMYADMIN_PORT_HOST:-8085}"

echo ""
echo "Pronto."
echo "  • App (Laravel): http://localhost:${APP_PORT_HOST}"
echo "  • MySQL (host):  localhost:${MYSQL_PORT_HOST}"
echo "  • phpMyAdmin:    http://localhost:${PHPMYADMIN_PORT_HOST}"
echo "  • Utilizador seed: admin@gooding.solutions — senha #G00d#MMYYYY (mês/ano em que correu o seed; ver saída do db:seed)"
echo ""
echo "Comandos úteis:  ${COMPOSE[*]} logs -f app    |    ${COMPOSE[*]} stop"
