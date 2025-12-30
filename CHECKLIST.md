# ✅ Checklist - EventHub MVP

Este documento lista tudo que precisa ser feito para deixar o projeto minimamente viável (MVP).

---

## 🔴 CRÍTICO - Fazer Primeiro

### 1. Configuração do Ambiente
- [ ] **Configurar `.env`** com dados reais do banco de dados
- [ ] **Configurar chaves do Stripe** (STRIPE_PUBLISHABLE_KEY, STRIPE_SECRET_KEY)
- [ ] **Criar banco de dados** MySQL: `marketplace`
- [ ] **Executar migrations**: `php spark migrate`
- [ ] **Criar diretório de uploads**: `writable/uploads/events`

### 2. Stripe Connect (Obrigatório para pagamentos)
- [ ] Criar conta no [Stripe](https://stripe.com)
- [ ] Ativar **Stripe Connect** no dashboard
- [ ] Configurar URLs de callback OAuth:
  - Refresh URL: `http://localhost:8080/organizer/onboarding-refresh`
  - Return URL: `http://localhost:8080/organizer/onboarding-complete`
- [ ] Configurar Webhook:
  - URL: `http://localhost:8080/checkout/webhook`
  - Eventos: `payment_intent.succeeded`, `payment_intent.payment_failed`
- [ ] Copiar o **Webhook Secret** para o `.env`

---

## 🟡 IMPORTANTE - Core do Sistema

### 3. Testes Manuais Necessários
- [ ] Testar fluxo completo de registro de usuário
- [ ] Testar cadastro de organizador + onboarding Stripe
- [ ] Testar criação de evento com dias, setores e assentos
- [ ] Testar publicação de evento
- [ ] Testar seleção de assentos no front-end
- [ ] Testar carrinho de compras (adicionar/remover)
- [ ] Testar checkout completo com cartão de teste Stripe
- [ ] Testar visualização de pedido após compra
- [ ] Testar impressão de ingresso
- [ ] Testar solicitação de reembolso

### 4. Dados de Teste
- [ ] Criar seeder com evento de exemplo completo
- [ ] Criar usuário organizador de teste
- [ ] Criar usuário cliente de teste

---

## 🟢 MELHORIAS - Pós MVP

### 5. Segurança
- [ ] Configurar CSRF em todos os formulários AJAX
- [ ] Implementar rate limiting nas rotas de API
- [ ] Validar e sanitizar todos os inputs
- [ ] Configurar Content Security Policy (CSP)
- [ ] Implementar logging de ações sensíveis

### 6. Performance
- [ ] Adicionar índices no banco para queries frequentes
- [ ] Implementar cache para listagem de eventos
- [ ] Otimizar queries N+1 nos controllers
- [ ] Lazy loading de imagens

### 7. UX/UI
- [ ] Adicionar loading states nos botões
- [ ] Implementar feedback visual nas ações AJAX
- [ ] Adicionar mensagens de erro amigáveis
- [ ] Responsividade em todos os dispositivos
- [ ] Melhorar acessibilidade (ARIA labels)

### 8. Funcionalidades Extras
- [ ] Email de confirmação de compra
- [ ] Email de envio de ingressos
- [ ] Download de ingresso em PDF
- [ ] Notificação de evento próximo
- [ ] Dashboard com gráficos para organizador
- [ ] Sistema de avaliação de eventos
- [ ] Cupons de desconto

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

## 📅 Próximos Passos Recomendados

1. ⬜ Configurar ambiente local (`.env` + banco + Stripe)
2. ⬜ Executar migrations
3. ⬜ Criar primeiro evento de teste manualmente
4. ⬜ Testar compra completa com cartão de teste
5. ⬜ Configurar webhook do Stripe
6. ⬜ Testar fluxo de reembolso
7. ⬜ Deploy em ambiente de staging

---

**Última atualização**: Dezembro 2025
