<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> - Marketplace</title>
    
    <!-- CSS Base -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- CSS Dinâmico (arquivos externos) -->
    <?= $this->renderSection('css') ?>
    
    <style>
        :root {
            --sidebar-width: 280px;
        }
        body {
            min-height: 100vh;
        }
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: linear-gradient(180deg, #1e3a5f 0%, #0d1b2a 100%);
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 4px 12px;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .main-content {
            flex: 1;
            background-color: #f8f9fa;
        }
        .card-stat {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }
        .card-stat:hover {
            transform: translateY(-5px);
        }
        .navbar-top {
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
    </style>
    
    <!-- CSS Dinâmico (inline) -->
    <style><?= $this->renderSection('styles') ?></style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar d-flex flex-column">
            <div class="p-4">
                <a href="<?= base_url('dashboard') ?>" class="text-white text-decoration-none">
                    <h4 class="mb-0"><i class="bi bi-shop"></i> Marketplace</h4>
                </a>
            </div>
            
            <ul class="nav flex-column mt-3">
                <li class="nav-item">
                    <a class="nav-link <?= uri_string() === 'dashboard' ? 'active' : '' ?>" href="<?= base_url('dashboard') ?>">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                
                <?php if (auth()->user()->is_organizer && auth()->user()->stripe_account_status === 'active'): ?>
                <!-- Menu do Organizador -->
                <li class="nav-item mt-3">
                    <small class="text-uppercase text-muted px-3">Organizador</small>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_starts_with(uri_string(), 'organizer/dashboard') ? 'active' : '' ?>" href="<?= base_url('organizer/dashboard') ?>">
                        <i class="bi bi-grid"></i> Painel Organizador
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_starts_with(uri_string(), 'organizer/events') ? 'active' : '' ?>" href="<?= base_url('organizer/events') ?>">
                        <i class="bi bi-calendar-event"></i> Meus Eventos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('organizer/stripe-dashboard') ?>" target="_blank">
                        <i class="bi bi-cash-stack"></i> Financeiro
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= uri_string() === 'organizer/account-status' ? 'active' : '' ?>" href="<?= base_url('organizer/account-status') ?>">
                        <i class="bi bi-person-check"></i> Status da Conta
                    </a>
                </li>
                <?php elseif (!auth()->user()->is_organizer): ?>
                <li class="nav-item mt-3">
                    <a class="nav-link text-warning" href="<?= base_url('organizer/become') ?>">
                        <i class="bi bi-star"></i> Seja um Organizador
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="nav-item mt-3">
                    <small class="text-uppercase text-muted px-3">Geral</small>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="bi bi-ticket-perforated"></i> Meus Ingressos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="bi bi-cart3"></i> Meus Pedidos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="bi bi-gear"></i> Configurações
                    </a>
                </li>
            </ul>

            <div class="mt-auto p-4">
                <a href="<?= base_url('logout') ?>" class="btn btn-outline-light w-100">
                    <i class="bi bi-box-arrow-right"></i> Sair
                </a>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navbar -->
            <nav class="navbar navbar-top navbar-expand-lg px-4 py-3">
                <div class="container-fluid">
                    <button class="btn btn-link d-lg-none" type="button">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    
                    <form class="d-none d-md-flex ms-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" placeholder="Buscar...">
                        </div>
                    </form>

                    <ul class="navbar-nav ms-auto align-items-center">
                        <li class="nav-item dropdown me-3">
                            <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-bell fs-5"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    3
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header">Notificações</h6></li>
                                <li><a class="dropdown-item" href="#">Novo pedido recebido</a></li>
                                <li><a class="dropdown-item" href="#">Produto com estoque baixo</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-center" href="#">Ver todas</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <?= strtoupper(substr(auth()->user()->username ?? 'U', 0, 1)) ?>
                                </div>
                                <span class="ms-2 d-none d-lg-inline"><?= auth()->user()->username ?? 'Usuário' ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i> Perfil</a></li>
                                <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i> Configurações</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= base_url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i> Sair</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Page Content -->
            <div class="p-4">
                <?= $this->renderSection('content') ?>
            </div>
        </div>
    </div>

    <!-- JS Base -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JS Dinâmico (arquivos externos) -->
    <?= $this->renderSection('js') ?>
    
    <!-- JS Dinâmico (inline) -->
    <script><?= $this->renderSection('scripts') ?></script>
</body>
</html>
