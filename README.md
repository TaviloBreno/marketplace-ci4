# 🎫 EventHub - Marketplace de Ingressos

<div align="center">

![PHP Version](https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4.x-EF4223?style=for-the-badge&logo=codeigniter&logoColor=white)
![Stripe](https://img.shields.io/badge/Stripe-Connect-008CDD?style=for-the-badge&logo=stripe&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

**Uma plataforma completa de marketplace para venda de ingressos com integração Stripe Connect**

[Funcionalidades](#-funcionalidades) •
[Instalação](#-instalação) •
[Configuração](#-configuração) •
[Uso](#-uso) •
[API](#-estrutura-do-projeto) •
[Contribuição](#-contribuição)

</div>

---

## 📋 Sobre o Projeto

EventHub é um marketplace de ingressos desenvolvido com **CodeIgniter 4**, permitindo que organizadores criem e vendam ingressos para seus eventos. A plataforma utiliza **Stripe Connect** para processamento de pagamentos com split automático entre a plataforma e os organizadores.

### 🎯 Principais Características

- **Multi-tenant**: Múltiplos organizadores podem gerenciar seus próprios eventos
- **Stripe Connect**: Split de pagamentos automático com taxa configurável
- **Mapa de Assentos**: Sistema visual de seleção de assentos por setores e filas
- **QR Code**: Ingressos com QR Code para validação na entrada
- **Reembolsos**: Sistema de reembolso integrado com Stripe

---

## ✨ Funcionalidades

### 👥 Área Pública (Clientes)
- ✅ Listagem de eventos com filtros (categoria, cidade, data)
- ✅ Página de detalhes do evento
- ✅ Seleção interativa de assentos
- ✅ Carrinho de compras com reserva temporária (10 minutos)
- ✅ Checkout com Stripe Elements
- ✅ Histórico de pedidos
- ✅ Visualização e impressão de ingressos com QR Code
- ✅ Solicitação de reembolso (até 48h antes do evento)

### 🎭 Área do Organizador
- ✅ Cadastro como organizador (onboarding Stripe Connect)
- ✅ Dashboard com estatísticas de vendas
- ✅ CRUD completo de eventos
- ✅ Configuração de datas/sessões
- ✅ Configuração de setores e preços
- ✅ Criação de mapa de assentos (filas e assentos)
- ✅ Publicação/cancelamento de eventos
- ✅ Acesso ao Stripe Dashboard

### 🔐 Autenticação
- ✅ Registro e login (CodeIgniter Shield)
- ✅ Recuperação de senha por email
- ✅ Proteção de rotas por filtros

---

## 🛠️ Stack Tecnológica

| Tecnologia | Versão | Descrição |
|------------|--------|-----------|
| **PHP** | 8.1+ | Linguagem backend |
| **CodeIgniter 4** | 4.x | Framework PHP MVC |
| **CodeIgniter Shield** | 1.2+ | Autenticação e autorização |
| **MySQL/MariaDB** | 8.0+ | Banco de dados relacional |
| **Stripe PHP SDK** | 19.x | Processamento de pagamentos |
| **Bootstrap** | 5.3 | Framework CSS responsivo |
| **Bootstrap Icons** | 1.11 | Biblioteca de ícones |

---

## 🚀 Instalação

### Pré-requisitos

- PHP >= 8.1 com extensões: `intl`, `mbstring`, `json`, `curl`, `mysqlnd`
- Composer 2.x
- MySQL 8.0+ ou MariaDB 10.4+
- Conta Stripe (modo teste para desenvolvimento)

### Passo a Passo

```bash
# 1. Clone o repositório
git clone https://github.com/seu-usuario/marketplace-ci4.git
cd marketplace-ci4

# 2. Instale as dependências
composer install

# 3. Copie o arquivo de ambiente
cp env .env

# 4. Configure o arquivo .env (veja seção Configuração)

# 5. Crie o banco de dados
mysql -u root -p -e "CREATE DATABASE marketplace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 6. Execute as migrations
php spark migrate

# 7. (Opcional) Execute o seeder de usuários de teste
php spark db:seed UserSeeder

# 8. Crie o diretório de uploads
mkdir -p writable/uploads/events

# 9. Inicie o servidor de desenvolvimento
php spark serve
```

Acesse: **http://localhost:8080**

---

## ⚙️ Configuração

### Arquivo `.env`

```env
#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------
CI_ENVIRONMENT = development

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------
app.baseURL = 'http://localhost:8080/'
app.indexPage = ''

#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------
database.default.hostname = localhost
database.default.database = marketplace
database.default.username = root
database.default.password = 
database.default.DBDriver = MySQLi
database.default.port = 3306

#--------------------------------------------------------------------
# STRIPE (OBRIGATÓRIO)
#--------------------------------------------------------------------
STRIPE_PUBLISHABLE_KEY = pk_test_xxxxxxxxxxxxxxxxxxxxx
STRIPE_SECRET_KEY = sk_test_xxxxxxxxxxxxxxxxxxxxx
STRIPE_WEBHOOK_SECRET = whsec_xxxxxxxxxxxxxxxxxxxxx
```

### Configuração do Stripe Connect

1. Crie uma conta em [stripe.com](https://stripe.com)
2. Acesse **Dashboard > Settings > Connect Settings**
3. Ative o **Stripe Connect** e configure:
   - **Tipo de conta**: Express ou Standard
   - **Branding**: Logo e cores da plataforma
4. Configure as URLs de callback OAuth:
   - **Refresh URL**: `{baseURL}/organizer/onboarding-refresh`
   - **Return URL**: `{baseURL}/organizer/onboarding-complete`
5. Configure o Webhook endpoint:
   - **URL**: `{baseURL}/checkout/webhook`
   - **Eventos**: 
     - `payment_intent.succeeded`
     - `payment_intent.payment_failed`
     - `account.updated`

---

## 📁 Estrutura do Projeto

```
marketplace-ci4/
├── app/
│   ├── Commands/              # Comandos CLI personalizados
│   ├── Config/                # Configurações da aplicação
│   │   ├── Routes.php         # Definição de rotas
│   │   ├── Stripe.php         # Configuração do Stripe
│   │   └── ...
│   ├── Controllers/           # Controllers
│   │   ├── PublicController.php    # Home e eventos públicos
│   │   ├── CartController.php      # Carrinho de compras
│   │   ├── CheckoutController.php  # Pagamento e webhooks
│   │   ├── OrderController.php     # Pedidos e reembolsos
│   │   ├── TicketController.php    # Ingressos e QR Code
│   │   ├── Organizer.php           # Área do organizador
│   │   └── Event.php               # CRUD de eventos
│   ├── Database/
│   │   ├── Migrations/        # Migrations do banco
│   │   └── Seeds/             # Seeders de dados
│   ├── Entities/              # Entidades do domínio
│   ├── Filters/               # Filtros de autenticação
│   ├── Models/                # Models do Eloquent
│   ├── Services/              # Camada de serviços
│   │   ├── PaymentService.php      # Integração Stripe Payments
│   │   ├── OrganizerService.php    # Stripe Connect
│   │   ├── EventStoreService.php   # Lógica de eventos
│   │   └── SeatRenderService.php   # Renderização de assentos
│   └── Views/                 # Views Blade
│       ├── layouts/           # Templates base
│       ├── public/            # Views públicas (clientes)
│       ├── organizer/         # Views do organizador
│       └── events/            # Views de eventos
├── public/                    # Document root (index.php)
├── writable/                  # Diretório gravável
│   ├── uploads/               # Uploads de imagens
│   ├── cache/                 # Cache
│   └── logs/                  # Logs da aplicação
├── tests/                     # Testes automatizados
├── .env                       # Variáveis de ambiente
└── composer.json              # Dependências PHP
```

---

## 🗄️ Modelo de Dados

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│     users       │     │     events      │     │   event_days    │
├─────────────────┤     ├─────────────────┤     ├─────────────────┤
│ id              │────<│ user_id         │────<│ event_id        │
│ is_organizer    │     │ title           │     │ event_date      │
│ stripe_account  │     │ slug            │     │ start_time      │
│ company_name    │     │ venue_*         │     │ end_time        │
└─────────────────┘     │ category        │     └─────────────────┘
                        │ status          │              │
                        └─────────────────┘              │
                               │                         │
                               │                         │
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│    sectors      │     │     queues      │     │     seats       │
├─────────────────┤     ├─────────────────┤     ├─────────────────┤
│ event_id        │────>│ sector_id       │────>│ queue_id        │
│ name            │     │ name            │     │ code            │
│ price           │     └─────────────────┘     │ position_x/y    │
│ color           │                             │ status          │
└─────────────────┘                             └─────────────────┘
                                                        │
                                                        │
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│     orders      │     │    tickets      │     │ seat_bookings   │
├─────────────────┤     ├─────────────────┤     ├─────────────────┤
│ user_id         │────<│ order_id        │     │ seat_id         │
│ event_id        │     │ seat_id         │────<│ event_day_id    │
│ stripe_pi_id    │     │ event_day_id    │     │ user_id         │
│ total           │     │ code            │     │ status          │
│ status          │     │ status          │     │ expires_at      │
└─────────────────┘     └─────────────────┘     └─────────────────┘
```

---

## 🔗 Rotas da API

### Rotas Públicas

| Método | Rota | Controller | Descrição |
|--------|------|------------|-----------|
| GET | `/` | `PublicController::index` | Home com eventos em destaque |
| GET | `/events` | `PublicController::events` | Listagem com filtros |
| GET | `/events/{slug}` | `PublicController::event` | Detalhes do evento |
| GET | `/events/{slug}/seats/{dayId}` | `PublicController::selectSeats` | Seleção de assentos |
| POST | `/events/seats-status` | `PublicController::getSeatsStatus` | Status dos assentos (AJAX) |
| GET | `/cart` | `CartController::index` | Carrinho de compras |
| POST | `/cart/add` | `CartController::add` | Adicionar ao carrinho |
| POST | `/cart/remove` | `CartController::remove` | Remover do carrinho |
| GET | `/tickets/validate/{code}` | `TicketController::validateQR` | Validar ingresso |

### Rotas Autenticadas (Cliente)

| Método | Rota | Controller | Descrição |
|--------|------|------------|-----------|
| GET | `/checkout` | `CheckoutController::index` | Página de pagamento |
| POST | `/checkout/process` | `CheckoutController::process` | Processar pagamento |
| GET | `/orders` | `OrderController::index` | Meus pedidos |
| GET | `/orders/{id}` | `OrderController::show` | Detalhes do pedido |
| POST | `/orders/{id}/refund` | `OrderController::processRefund` | Solicitar reembolso |
| GET | `/tickets` | `TicketController::index` | Meus ingressos |
| GET | `/tickets/{code}` | `TicketController::show` | Ver ingresso |
| GET | `/tickets/{code}/print` | `TicketController::print` | Imprimir ingresso |

### Rotas do Organizador

| Método | Rota | Controller | Descrição |
|--------|------|------------|-----------|
| GET | `/organizer/become` | `Organizer::become` | Tornar-se organizador |
| POST | `/organizer/register` | `Organizer::register` | Cadastro organizador |
| GET | `/organizer/dashboard` | `Organizer::dashboard` | Dashboard |
| GET | `/organizer/events` | `Event::index` | Listar eventos |
| GET | `/organizer/events/create` | `Event::create` | Formulário de criação |
| POST | `/organizer/events/store` | `Event::store` | Salvar evento |
| GET | `/organizer/events/{id}/edit` | `Event::edit` | Editar evento |
| POST | `/organizer/events/{id}/publish` | `Event::publish` | Publicar evento |

---

## 📋 Checklist para MVP

### ✅ Core Implementado
- [x] Sistema de autenticação (Shield)
- [x] Cadastro de organizadores com Stripe Connect
- [x] CRUD completo de eventos
- [x] Sistema de dias/sessões por evento
- [x] Sistema de setores com preços
- [x] Sistema de filas e assentos
- [x] Listagem pública de eventos
- [x] Filtros por categoria, cidade, data
- [x] Seleção visual de assentos
- [x] Carrinho com reserva temporária
- [x] Checkout com Stripe Payment Intents
- [x] Criação automática de pedidos
- [x] Geração de ingressos com código único
- [x] Impressão de ingressos com QR Code
- [x] Sistema de reembolsos

### ⚠️ Configuração Necessária
- [ ] **Configurar chaves Stripe** no `.env`
- [ ] **Executar migrations** (`php spark migrate`)
- [ ] **Configurar webhook** no Stripe Dashboard
- [ ] **Criar diretório de uploads** (`writable/uploads/events`)

### 🔜 Melhorias Futuras
- [ ] Email transacional (confirmação de compra, tickets)
- [ ] PDF de ingressos para download
- [ ] App mobile para validação de QR Code
- [ ] Dashboard analytics para organizadores
- [ ] Sistema de cupons de desconto
- [ ] Integração com Google Calendar
- [ ] Notificações push
- [ ] Multi-idioma (i18n)
- [ ] Testes automatizados (PHPUnit)
- [ ] Cache de queries (Redis)
- [ ] Queue para processamento assíncrono

### 🔒 Checklist de Produção
- [ ] `CI_ENVIRONMENT = production`
- [ ] `app.forceGlobalSecureRequests = true`
- [ ] Configurar `encryption.key`
- [ ] Certificado SSL (HTTPS)
- [ ] Configurar rate limiting
- [ ] Backup automático do banco
- [ ] Monitoramento de logs

---

## 🧪 Testes

```bash
# Executar todos os testes
composer test

# Ou com PHPUnit diretamente
./vendor/bin/phpunit

# Testes com cobertura
./vendor/bin/phpunit --coverage-html coverage
```

---

## 🐛 Troubleshooting

### Erro de conexão com banco
```bash
# Verifique se o MySQL está rodando
sudo service mysql status

# Teste a conexão
mysql -u root -p -e "SELECT 1"
```

### Erro de permissão em writable
```bash
# Linux/Mac
chmod -R 775 writable/
chown -R www-data:www-data writable/

# Windows (PowerShell como Admin)
icacls writable /grant "IIS_IUSRS:(OI)(CI)F" /T
```

### Stripe webhook não funciona
```bash
# Use o Stripe CLI para testar localmente
stripe listen --forward-to localhost:8080/checkout/webhook
```

---

## 🤝 Contribuição

1. Fork o projeto
2. Crie sua branch (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add: nova funcionalidade'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

### Convenções de Commit
- `Add:` Nova funcionalidade
- `Fix:` Correção de bug
- `Update:` Atualização de funcionalidade existente
- `Refactor:` Refatoração de código
- `Docs:` Documentação
- `Test:` Testes

---

## 📄 Licença

Distribuído sob a licença MIT. Veja [LICENSE](LICENSE) para mais informações.

---

## 📞 Suporte

- 📧 Email: suporte@eventhub.com
- 💬 Issues: [GitHub Issues](https://github.com/seu-usuario/marketplace-ci4/issues)
- 📖 Wiki: [GitHub Wiki](https://github.com/seu-usuario/marketplace-ci4/wiki)

---

<div align="center">

**Desenvolvido com ❤️ usando CodeIgniter 4**

⭐ Se este projeto te ajudou, deixe uma estrela!

</div>
