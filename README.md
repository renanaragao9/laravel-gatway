# 01 - Laravel Starter

Projeto base da coleção de 20 mini projetos, construído sobre **Laravel 13** com um
painel administrativo **Filament**. A aplicação servirá de base para integração com
**gateways de pagamento** (Asaas, Mercado Pago, etc.), gerenciando clientes,
cobranças, assinaturas e configurações pelo painel admin.

## Stack

- **Laravel Framework** 13.x
- **PHP** 8.4
- **Filament** 5.x (painel administrativo em `/admin`)
- **SQLite** (banco padrão, zero configuração)
- **Vite** + Tailwind (assets front-end do starter do Laravel)

## O que foi configurado

- Nome da aplicação: `APP_NAME="Laravel Starter"`
- Localização: `APP_LOCALE=pt_BR` (fallback `en`) e `APP_FAKER_LOCALE=pt_BR`
- Fuso horário: `APP_TIMEZONE=America/Sao_Paulo` (lido de env em `config/app.php`)
- Banco de dados SQLite em `database/database.sqlite`
- `APP_KEY` gerada e migrations executadas
- Painel admin Filament instalado em `/admin` (login habilitado)

## Requisitos

- PHP >= 8.2
- Composer 2.x
- Node.js 18+ e npm (para compilar os assets)

## Como rodar

```bash
# 1. Instalar dependências PHP
composer install

# 2. Copiar o arquivo de ambiente (se ainda não existir .env)
cp .env.example .env

# 3. Gerar a chave da aplicação
php artisan key:generate

# 4. Criar o banco SQLite (se não existir) e rodar as migrations
touch database/database.sqlite
php artisan migrate

# 5. Instalar e compilar os assets front-end
npm install
npm run build        # ou: npm run dev (modo watch)

# 6. Subir o servidor de desenvolvimento
php artisan serve
```

Acesse: http://localhost:8000

## Painel administrativo (Filament)

O painel fica em **http://localhost:8000/admin**.

Para criar um usuário de acesso ao painel:

```bash
php artisan make:filament-user
```

> Em desenvolvimento, foi criado um usuário `admin@example.com`. **Troque a senha
> antes de usar em qualquer ambiente real.**

É aqui que os recursos dos gateways de pagamento (clientes, cobranças, assinaturas,
webhooks e configurações) serão gerenciados conforme o projeto evoluir.

## Rodar os testes

```bash
php artisan test
```

## Estrutura relevante

```
01-laravel-starter/
├── app/                # Código da aplicação (Models, Http, Providers)
├── config/             # Configurações (app.php ajustado p/ timezone via env)
├── database/
│   ├── database.sqlite # Banco SQLite
│   └── migrations/     # Migrations
├── routes/             # web.php, console.php
├── tests/              # Testes (Feature e Unit)
├── .env                # Ambiente local (não versionado)
└── .env.example        # Modelo de ambiente (versionado)
```

## Decisões técnicas

- **SQLite** foi escolhido como banco padrão por não exigir servidor externo,
  deixando o projeto executável com um único `git clone`. Para trocar por
  MySQL/PostgreSQL, basta ajustar as variáveis `DB_*` no `.env`.
- **Localização pt_BR** e **timezone de São Paulo** desde o início para refletir
  um cenário de aplicação brasileira.

## Verificação

- `php artisan test` → 2 testes passando.
- Servidor responde HTTP 200 na home com o título "Laravel Starter".
- Painel admin: `/admin/login` responde HTTP 200 e `/admin` redireciona para o login
  (autenticação ativa).
# laravel-gatway
