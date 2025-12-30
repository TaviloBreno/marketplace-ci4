# ✅ Checklist - EventHub MVP

Este documento lista tudo que precisa ser feito para deixar o projeto minimamente viável (MVP).

**Última atualização**: 29 de Dezembro de 2025

---

## 🟢 CRÍTICO - Configuração do Ambiente (CONCLUÍDO)

### 1. Configuração do Ambiente ✅
- [x] **Configurar `.env`** com dados reais do banco de dados
- [x] **Adicionar variáveis Stripe** no `.env` (template pronto)
- [x] **Criar banco de dados** MySQL: `marketplace`
- [x] **Executar migrations**: `php spark migrate` ✅ 9 migrations executadas
- [x] **Criar diretório de uploads**: `writable/uploads/events` ✅

### 2. Dados de Teste ✅
- [x] **Usuário organizador** criado: `admin@marketplace.com` / `Admin@123`
- [x] **Usuário cliente** criado: `cliente@marketplace.com` / `Cliente@123`
- [x] **3 eventos de exemplo** criados com setores, filas e assentos:
  - Show Rock in Rio (categoria: show) - 2 dias, 3 setores, 76 assentos
  - O Fantasma da Ópera (categoria: teatro) - 2 dias, 2 setores, 96 assentos
  - Final Campeonato Brasileiro (categoria: esporte) - 1 dia, 1 setor, 75 assentos

### 3. Stripe Connect (Pendente - Requer conta real)
- [ ] Criar conta no [Stripe](https://stripe.com)
- [ ] Obter **STRIPE_PUBLISHABLE_KEY** e **STRIPE_SECRET_KEY**
- [ ] Ativar **Stripe Connect** no dashboard
- [ ] Configurar URLs de callback OAuth:
  - Refresh URL: `http://localhost:8080/organizer/onboarding-refresh`
  - Return URL: `http://localhost:8080/organizer/onboarding-complete`
- [ ] Configurar Webhook:
  - URL: `http://localhost:8080/checkout/webhook`
  - Eventos: `payment_intent.succeeded`, `payment_intent.payment_failed`
- [ ] Copiar o **Webhook Secret** para o `.env`

---

## 🟡 IMPORTANTE - Core do Sistema (CONCLUÍDO)

### 4. Rotas e URLs ✅
- [x] **Rotas em português**: `/eventos`, `/carrinho`, `/checkout`, `/meus-pedidos`, `/meus-ingressos`
- [x] **URLs de API atualizadas**: `/carrinho/adicionar`, `/carrinho/remover`, `/carrinho/contador`
- [x] **Campo `event_date`** corrigido em todos os models, entities e views

### 5. Testes Manuais Necessários
- [x] ~~Testar fluxo completo de registro de usuário~~ (Seeders criados)
- [ ] Testar cadastro de organizador + onboarding Stripe
- [x] ~~Testar criação de evento com dias, setores e assentos~~ (EventSeeder criado)
- [x] ~~Testar publicação de evento~~ (Eventos publicados via seeder)
- [ ] Testar seleção de assentos no front-end
- [ ] Testar carrinho de compras (adicionar/remover)
- [ ] Testar checkout completo com cartão de teste Stripe
- [ ] Testar visualização de pedido após compra
- [ ] Testar impressão de ingresso
- [ ] Testar solicitação de reembolso

### 6. Dados de Teste ✅
- [x] Criar seeder com evento de exemplo completo (`EventSeeder`)
- [x] Criar usuário organizador de teste (`admin@marketplace.com`)
- [x] Criar usuário cliente de teste (`cliente@marketplace.com`)

---

## 🔵 MELHORIAS PÓS-MVP (IMPLEMENTADO)

### 7. Segurança ✅
- [x] Configurar CSRF em todos os formulários AJAX (`AjaxCsrfFilter`)
- [x] Implementar rate limiting nas rotas de API (`RateLimitFilter`)
- [x] Registrar filtros no `Filters.php`
- [x] Meta tags CSRF nos layouts (`csrf-token`, `csrf-token-name`)
- [x] Implementar logging de ações sensíveis (`AuditLogger`)
- [ ] Validar e sanitizar todos os inputs
- [ ] Configurar Content Security Policy (CSP)

### 8. Performance ✅
- [x] Migration de índices para queries frequentes (`AddPerformanceIndices`)
- [x] Implementar cache para eventos (`EventCache`)
- [ ] Otimizar queries N+1 nos controllers
- [ ] Lazy loading de imagens

### 9. UX/UI ✅
- [x] Biblioteca JavaScript utilitária (`eventhub.js`)
  - Loading states automáticos em botões
  - Sistema de notificações toast
  - Modal de confirmação
  - AJAX com CSRF automático
  - Formatadores de moeda e data
- [ ] Responsividade em todos os dispositivos
- [ ] Melhorar acessibilidade (ARIA labels)

### 10. Funcionalidades Extras (Futuro)
- [ ] Email de confirmação de compra
- [ ] Email de envio de ingressos
- [ ] Download de ingresso em PDF
- [ ] Notificação de evento próximo
- [ ] Dashboard com gráficos para organizador
- [ ] Sistema de avaliação de eventos
- [ ] Cupons de desconto

---

## 📁 Novos Arquivos Criados (Pós-MVP)

### Filtros de Segurança
| Arquivo | Descrição |
|---------|-----------|
| `app/Filters/RateLimitFilter.php` | Rate limiting (60 req/min) |
| `app/Filters/AjaxCsrfFilter.php` | CSRF para requisições AJAX |

### Bibliotecas
| Arquivo | Descrição |
|---------|-----------|
| `app/Libraries/EventCache.php` | Cache de eventos e listagens |
| `app/Libraries/AuditLogger.php` | Log de ações sensíveis |

### JavaScript
| Arquivo | Descrição |
|---------|-----------|
| `public/js/eventhub.js` | Utilitários JS (AJAX, loading, toast) |

### Migrations
| Arquivo | Descrição |
|---------|-----------|
| `AddPerformanceIndices.php` | Índices para performance |

---

## 📝 Arquivos Principais do Projeto

### Controllers
| Arquivo | Status | Descrição |
|---------|--------|-----------|
| `PublicController.php` | ✅ | Home, listagem, detalhes, assentos |
| `CartController.php` | ✅ | Carrinho de compras |
| `CheckoutController.php` | ✅ | Pagamento e webhook |
| `OrderController.php` | ✅ | Pedidos e reembolsos |
| `TicketController.php` | ✅ | Ingressos e impressão |
| `Organizer.php` | ✅ | Área do organizador |
| `Event.php` | ✅ | CRUD de eventos |

### Services
| Arquivo | Status | Descrição |
|---------|--------|-----------|
| `PaymentService.php` | ✅ | Integração Stripe |
| `OrganizerService.php` | ✅ | Stripe Connect |
| `EventStoreService.php` | ✅ | Lógica de eventos |
| `SeatRenderService.php` | ✅ | Renderização de assentos |

### Models
| Arquivo | Status | Descrição |
|---------|--------|-----------|
| `EventModel.php` | ✅ | Eventos |
| `EventDayModel.php` | ✅ | Dias/sessões |
| `SectorModel.php` | ✅ | Setores |
| `QueueModel.php` | ✅ | Filas |
| `SeatModel.php` | ✅ | Assentos |
| `SeatBookingModel.php` | ✅ | Reservas temporárias |
| `OrderModel.php` | ✅ | Pedidos |
| `TicketModel.php` | ✅ | Ingressos |

### Views
| Diretório | Status | Descrição |
|-----------|--------|-----------|
| `layouts/public.php` | ✅ | Layout público |
| `public/home.php` | ✅ | Home page |
| `public/events/*` | ✅ | Listagem e detalhes |
| `public/cart/*` | ✅ | Carrinho |
| `public/checkout/*` | ✅ | Checkout |
| `public/orders/*` | ✅ | Pedidos |
| `public/tickets/*` | ✅ | Ingressos |
| `organizer/*` | ✅ | Área organizador |
| `events/*` | ✅ | CRUD eventos |

### Migrations
| Arquivo | Status | Descrição |
|---------|--------|-----------|
| `AddOrganizerFieldsToUsers` | ✅ | Campos de organizador |
| `CreateEventsTable` | ✅ | Tabela eventos |
| `CreateEventDaysTable` | ✅ | Tabela dias |
| `CreateSectorsTable` | ✅ | Tabela setores |
| `CreateQueuesTable` | ✅ | Tabela filas |
| `CreateSeatsTable` | ✅ | Tabela assentos |
| `CreateSeatBookingsTable` | ✅ | Tabela reservas |
| `CreateOrdersTable` | ✅ | Tabela pedidos |
| `CreateTicketsTable` | ✅ | Tabela ingressos |
| `AddPerformanceIndices` | ✅ | Índices de performance |

---

## 🚀 Comandos Úteis

```bash
# Iniciar servidor
php spark serve

# Executar migrations
php spark migrate

# Reverter migrations
php spark migrate:rollback

# Executar seeders
php spark db:seed UserSeeder
php spark db:seed EventSeeder

# Limpar cache
php spark cache:clear

# Ver rotas
php spark routes
```

---

## 🔧 Cartões de Teste Stripe

| Número | Resultado |
|--------|-----------|
| 4242 4242 4242 4242 | Sucesso |
| 4000 0000 0000 0002 | Recusado |
| 4000 0000 0000 3220 | Requer autenticação 3DS |

**CVV**: Qualquer 3 dígitos
**Data**: Qualquer data futura
**CEP**: Qualquer valor

---

## 📍 Como Iniciar o Projeto

```bash
# Tudo já está configurado! Basta:

# 1. Executar a nova migration de índices (opcional, melhora performance)
php spark migrate

# 2. Iniciar o servidor
php spark serve

# 3. Acessar no navegador
http://localhost:8080
```

### Credenciais de Teste

| Tipo | Email | Senha |
|------|-------|-------|
| **Organizador** | admin@marketplace.com | Admin@123 |
| **Cliente** | cliente@marketplace.com | Cliente@123 |

### Eventos Disponíveis para Teste

1. **Show Rock in Rio** - `/eventos/show-rock-in-rio-teste`
2. **O Fantasma da Ópera** - `/eventos/o-fantasma-da-opera`
3. **Final Campeonato Brasileiro** - `/eventos/final-campeonato-brasileiro`

---

## 📅 Próximos Passos Recomendados

1. ✅ ~~Configurar ambiente local (`.env` + banco + Stripe)~~
2. ✅ ~~Executar migrations~~
3. ✅ ~~Criar primeiro evento de teste manualmente~~
4. ✅ ~~Implementar melhorias de segurança (CSRF, rate limiting)~~
5. ✅ ~~Implementar melhorias de performance (índices, cache)~~
6. ✅ ~~Implementar melhorias de UX (loading states, toasts)~~
7. ⬜ **Configurar chaves reais do Stripe** para testar pagamentos
8. ⬜ Testar compra completa com cartão de teste
9. ⬜ Configurar webhook do Stripe
10. ⬜ Testar fluxo de reembolso
11. ⬜ Deploy em ambiente de staging

---

## 📖 Como Usar os Novos Recursos

### Loading States em Botões
```html
<!-- Adicionar data-loading-text para ativar loading automático -->
<button type="submit" data-loading-text="Processando...">
    Finalizar Compra
</button>
```

### Notificações Toast
```javascript
// Exibir notificações
EventHub.showNotification('Operação realizada com sucesso!', 'success');
EventHub.showNotification('Erro ao processar', 'error');
EventHub.showNotification('Atenção!', 'warning');
```

### Requisições AJAX com CSRF
```javascript
// POST com CSRF automático
const result = await EventHub.post('/carrinho/adicionar', {
    seat_id: 123
});

// GET
const data = await EventHub.get('/carrinho/contador');
```

### Modal de Confirmação
```javascript
const confirmed = await EventHub.confirm('Deseja remover este item?', {
    title: 'Confirmar Remoção',
    confirmText: 'Sim, Remover',
    confirmClass: 'btn-danger'
});

if (confirmed) {
    // executar ação
}
```

### Audit Logger (Backend)
```php
// Usar em controllers/services
$logger = new \App\Libraries\AuditLogger();

// Log de compra
$logger->logTicketPurchase($orderId, $eventId, $amount, $tickets);

// Log de reembolso
$logger->logRefundRequest($orderId, $amount, 'motivo');

// Log de pagamento
$logger->logPaymentSuccess($orderId, $amount, $transactionId);
```

### Event Cache (Backend)
```php
// Usar em controllers
$cache = new \App\Libraries\EventCache();

// Obter evento com cache
$event = $cache->getEvent($eventId, function() use ($eventId) {
    return $this->eventModel->find($eventId);
});

// Invalidar cache após alteração
$cache->invalidateEvent($eventId);
```

---

**Última atualização**: 29 de Dezembro de 2025
