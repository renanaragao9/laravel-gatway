# Escopo — Gateway de Pagamento Centralizado com Repasse para ONGs

> Projeto **01 - Laravel Gateway** (evolução). Stack: **Laravel 13.39** + **Filament 5.11** + SQLite.

## 1. Visão geral

Plataforma que **centraliza a arrecadação** de doações destinadas a ONGs e, no
**fechamento mensal (via cron)**, **repassa** o valor líquido a cada ONG (descontando
a taxa da plataforma). O operador é o intermediário: recebe em uma conta central e
distribui.

```
Doador (anônimo) → paga (Pix) → [Plataforma recebe em conta central] → acumula por ONG
                                                                          ↓ (cron, fim do mês)
                                                        Fechamento mensal → Repasse (payout) → ONG
```

## 2. Decisões de projeto (fechadas)

1. **Evolução do projeto 01** (Laravel Gateway), não um projeto novo.
2. **Gateway inicial: Asaas** (Pix em sandbox).
3. **Troca de gateway por classe**: arquitetura com uma interface única
   (`PaymentGateway`) e drivers plugáveis. Trocar de provedor = criar/registrar
   uma classe e apontar a configuração, sem mexer no restante do sistema.
4. **Repasse via cron**: command agendado faz o fechamento e dispara os repasses.
5. **Doação anônima**: sem cadastro de doador. Dados mínimos (nome/e-mail) podem ser
   capturados no checkout apenas para recibo, mas não há entidade `Donor`.

## 3. Atores

- **Admin da plataforma**: gerencia ONGs, taxas, gateway, concilia pagamentos, vê
  repasses e relatórios (painel Filament já existente em `/admin`).
- **ONG (beneficiário)**: cadastrada pelo admin, com dados de repasse (Pix/bancários).
- **Doador**: anônimo; apenas paga pela página pública de checkout.

## 4. Domínio (modelos)

Valores monetários em **centavos (inteiro)** para evitar erro de arredondamento.

| Entidade | Papel | Campos-chave |
|----------|-------|--------------|
| `Ong` | Beneficiário (já existe) | **+** `document` (CNPJ), `pix_key`, `bank_name`, `bank_agency`, `bank_account`, `account_type` |
| `Payment` | Pagamento recebido | `ong_id`, `gateway`, `gateway_payment_id`, `method`, `amount_cents`, `fee_cents`, `net_cents`, `status`, `paid_at`, `period` (YYYY-MM), `payer_name?`, `payer_email?` |
| `GatewayAccount` | Config/credenciais do gateway | `provider` (asaas/...), `mode` (sandbox/prod), credenciais **criptografadas**, `active` |
| `Payout` | Repasse mensal a uma ONG | `ong_id`, `period`, `gross_cents`, `fee_cents`, `net_cents`, `status`, `scheduled_for`, `paid_at`, `gateway_transfer_id?`, `receipt_path?` |
| `PayoutItem` | Liga `Payment`s a um `Payout` | `payout_id`, `payment_id`, `amount_cents` |
| `WebhookEvent` | Auditoria de webhooks | `provider`, `event_type`, `external_id`, `payload` (json), `signature_valid`, `processed_at` |
| `LedgerEntry` | Razão por ONG (crédito/débito) | `ong_id`, `type`, `amount_cents`, `reference_type`, `reference_id`, `balance_after_cents` |

## 5. Arquitetura de gateway (plugável)

Contrato único; drivers implementam o contrato. Resolver escolhe o driver pela
`GatewayAccount.provider`. Trocar de gateway = nova classe + config.

```
app/Services/Payments/
├── Contracts/
│   └── PaymentGateway.php        # interface: createPixCharge, parseWebhook, verifySignature, createTransfer
├── DT:
│   ├── ChargeRequest.php / ChargeResult.php
│   └── WebhookResult.php
├── Drivers/
│   ├── AsaasGateway.php          # implementação inicial
│   └── (futuro) MercadoPagoGateway.php
└── PaymentGatewayManager.php     # resolve o driver ativo
```

Interface mínima (`PaymentGateway`):
- `createPixCharge(ChargeRequest): ChargeResult`
- `verifySignature(Request): bool`
- `parseWebhook(array $payload): WebhookResult`
- `createTransfer(Payout): TransferResult`  *(usado no repasse automático)*

## 6. Fluxos

### 6.1 Arrecadação
1. Doador abre o checkout público da ONG (`/doar/{ong}`).
2. Backend chama `createPixCharge()` no gateway ativo → retorna QR Code/copia-e-cola.
3. Cria `Payment` com status `pending`.

### 6.2 Confirmação (webhook)
1. Gateway chama `POST /webhooks/{provider}`.
2. `verifySignature()` valida a assinatura (rejeita se inválida).
3. Idempotência: se `external_id` já processado, ignora.
4. Marca `Payment` como `paid`, calcula `fee_cents`/`net_cents`, grava `LedgerEntry`
   de crédito. Registra `WebhookEvent`.

### 6.3 Fechamento + repasse (cron)
1. Command `payouts:close-month` roda **todo dia 1º** (agendado no `routes/console.php`).
2. Para cada ONG com pagamentos `paid` e não repassados no período anterior:
   - cria `Payout` (agrupando os `Payment`s via `PayoutItem`), dentro de **transação**.
3. Command `payouts:dispatch` processa `Payout` pendentes:
   - chama `createTransfer()` no gateway → marca `paid`, grava `LedgerEntry` de débito.
4. **Idempotente**: reexecutar o período não duplica repasses.

## 7. Regras de negócio

- `net_cents = amount_cents - fee_cents` por pagamento.
- Taxa configurável (percentual + fixo), com possível override por ONG.
- Um `Payment` só entra num `Payout` se `paid` e ainda não repassado.
- Saldo da ONG = Σ créditos − Σ débitos (`LedgerEntry`).
- Fechamento e repasse idempotentes.

## 8. Segurança

- Credenciais do gateway com cast `encrypted`; nunca versionadas (`.env`).
- Assinatura de webhook **obrigatória**; payload inválido é rejeitado.
- Idempotência em webhook e fechamento.
- Dados bancários/Pix tratados como sensíveis (não logar valores).
- Nota de portfólio: aspectos legais/regulatórios de intermediação financeira
  (KYC/AML, PCI se cartão) ficam **fora de escopo**, mas documentados.

## 9. Roadmap de implementação

1. Migrations + models + relacionamentos (domínio completo).
2. Estender `Ong` com dados de repasse + Filament Resources (`Payment`, `Payout`, `GatewayAccount`).
3. Contrato `PaymentGateway` + `PaymentGatewayManager` + `AsaasGateway` (sandbox, Pix).
4. Checkout público + rota de webhook + `LedgerEntry`.
5. Command de fechamento mensal (`payouts:close-month`) + agendamento (cron).
6. Command de repasse (`payouts:dispatch`) via `createTransfer()`.
7. Dashboards/relatórios no Filament.
8. Testes: cálculo de taxa, fechamento idempotente, webhook assinado, checkout.

## 10. Métricas de sucesso (portfólio)

Demonstra: integração de pagamento real em sandbox, webhooks assinados, jobs/filas,
agendamento (cron), transações, padrão strategy (gateway plugável) e painel admin
completo. README com diagrama de fluxo, GIF do checkout e do fechamento.
