# 01 - Laravel Gateway

Gateway de pagamento **centralizado** que recebe doações para ONGs via **Pix** e, no
fechamento mensal, **repassa** o valor líquido a cada ONG (descontando a taxa da
plataforma). Construído em **Laravel 13** + **Filament 5**, com arquitetura de gateway
**plugável** (começando pelo **Asaas**).

## Stack

- **Laravel Framework** 13.x
- **PHP** 8.4
- **Filament** 5.x (painel administrativo em `/admin`)
- **SQLite** (banco padrão, zero configuração)
- **Asaas** como gateway inicial (Pix, sandbox)

## Fluxo

```
Doador (anônimo)
   │  GET /doar/{ong}  → preenche valor
   ▼
Checkout ── cria cobrança Pix no gateway ──► Payment (pending) + QR Code
   │
   ▼  (doador paga)
Webhook POST /webhooks/asaas
   │  valida assinatura + idempotência
   ▼
Payment (paid) + taxa aplicada ──► LedgerEntry (crédito p/ a ONG)
   │
   ▼  (cron, todo dia 1º)
payouts:close-month  → agrupa pagamentos do período em Payout (pending)
payouts:dispatch     → transferência via gateway ──► Payout (paid) + LedgerEntry (débito)
```

Valores monetários são sempre armazenados em **centavos (inteiro)**.

## Domínio (models)

| Model | Papel |
|-------|-------|
| `Ong` | Beneficiário; inclui dados de repasse (`document`, `pix_key`, dados bancários) |
| `Payment` | Pagamento recebido (bruto/taxa/líquido, status, período, dados Pix) |
| `GatewayAccount` | Credenciais do gateway (`provider`, `mode`, `credentials` **criptografadas**) |
| `Payout` | Repasse mensal por ONG |
| `PayoutItem` | Liga `Payment`s a um `Payout` |
| `WebhookEvent` | Auditoria/idempotência de webhooks |
| `LedgerEntry` | Razão por ONG (crédito na entrada, débito no repasse) |

## Arquitetura de gateway (plugável)

Contrato único em `app/Services/Payments/`:

```
Contracts/PaymentGateway.php     # createPixCharge, verifyWebhookSignature, parseWebhook, createTransfer
Data/                            # DTOs: ChargeRequest, ChargeResult, WebhookResult, TransferResult
Enums/                           # PaymentMethod, PaymentStatus
Drivers/AsaasGateway.php         # implementação Asaas
PaymentGatewayManager.php        # resolve o driver da GatewayAccount ativa
PaymentProcessor.php             # cria cobrança e confirma pagamento (+ LedgerEntry)
PayoutService.php                # fechamento mensal e repasse
FeeCalculator.php                # taxa configurável (percentual + fixo)
```

**Trocar de gateway** = criar uma classe `XGateway implements PaymentGateway` e
adicionar um `case` no `PaymentGatewayManager`. Nada mais muda.

## Painel administrativo (Filament)

Acesse **http://localhost:8000/admin**. Navegação:

- **Dashboard** — widgets: totais (arrecadado, taxas, repassado, pendentes),
  gráfico de arrecadação por mês e tabela de repasses pendentes.
- **Cadastros → ONGs** (`/admin/ongs`) — CRUD com dados de repasse.
- **Gateway → Pagamentos** (`/admin/payments`) — monitoramento (somente leitura).
- **Gateway → Repasses** (`/admin/payouts`) — monitoramento (somente leitura).
- **Gateway → Contas de Gateway** (`/admin/gateway-accounts`) — credenciais do provedor.

> O model `User` implementa `FilamentUser` (`canAccessPanel()`).

## Checkout público

```
GET  /doar/{ong}                        # formulário de doação
POST /doar/{ong}                        # gera a cobrança Pix
GET  /doar/{ong}/pagamento/{payment}    # exibe QR Code + copia-e-cola
```

## Webhook

```
POST /webhooks/{provider}   # ex.: /webhooks/asaas  (isento de CSRF)
```

Valida a assinatura do provedor, é **idempotente** (por `provider` + `external_id`),
marca o `Payment` como pago, aplica a taxa e grava o `LedgerEntry` de crédito.

## Repasse mensal (cron)

```bash
php artisan payouts:close-month [YYYY-MM]   # agrupa pagamentos do período em repasses
php artisan payouts:dispatch                # processa os repasses pendentes
```

Agendados em `routes/console.php` para rodar **todo dia 1º** (03:00 e 03:30).
Em `mode=sandbox`, a transferência é **simulada**; em `production`, usa a API real.

Para o agendador rodar em produção:

```
* * * * * cd /caminho/projeto && php artisan schedule:run >> /dev/null 2>&1
```

## Requisitos

- PHP >= 8.2
- Composer 2.x
- Node.js 18+ e npm

## Como rodar

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed    # cria o admin (admin@example.com)
npm install && npm run build
php artisan serve
```

## Configuração do gateway

1. Em `/admin/gateway-accounts`, cadastre a conta: provedor `asaas`, ambiente
   `sandbox`, credenciais `api_key` e `webhook_token`, e marque como **ativa**.
2. No painel do Asaas, configure o webhook apontando para
   `https://SEU_DOMINIO/webhooks/asaas` com o mesmo `webhook_token`.
3. A taxa da plataforma é configurável via `.env`:
   `PAYMENTS_FEE_PERCENT` (padrão 5) e `PAYMENTS_FEE_FIXED_CENTS` (padrão 0).

## Rodar os testes

```bash
php artisan test
```

A suíte cobre: CRUD de ONGs, domínio/relacionamentos, driver Asaas (com `Http::fake`),
checkout, webhook (assinatura + idempotência), fechamento e repasse (idempotentes),
e carregamento do dashboard.

## Decisões técnicas

- **Valores em centavos** para evitar erro de arredondamento.
- **Credenciais criptografadas** (`encrypted:array`), nunca versionadas.
- **Idempotência** em webhook, fechamento e repasse; operações críticas em transação.
- **SQLite** para execução imediata; trocar por MySQL/PostgreSQL só ajustando `DB_*`.
- Aspectos legais/regulatórios de intermediação financeira (KYC/AML, PCI) estão
  **fora de escopo** deste projeto de portfólio.

## Verificação

- `php artisan test` → **34 testes passando (96 assertions)**.
- `php artisan schedule:list` → comandos de fechamento e repasse agendados (dia 1º).
- Painel `/admin` carrega com os widgets do dashboard.
