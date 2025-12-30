<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> - EventHub</title>
    
    <!-- CSS Base -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --secondary-color: #0ea5e9;
            --dark-bg: #0f172a;
            --card-bg: #1e293b;
        }
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background-color: #f8fafc;
            min-height: 100vh;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .navbar-dark {
            background: linear-gradient(135deg, var(--dark-bg) 0%, #1e293b 100%);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #4338ca 100%);
            transform: translateY(-1px);
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--dark-bg) 0%, #1e293b 100%);
            padding: 80px 0;
            color: white;
        }
        
        .event-card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }
        
        .event-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
        }
        
        .event-card .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        
        .event-date-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: white;
            padding: 8px 12px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }
        
        .event-date-badge .day {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1;
            color: var(--primary-color);
        }
        
        .event-date-badge .month {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #64748b;
        }
        
        .category-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--primary-color);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .price-tag {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .footer {
            background: var(--dark-bg);
            color: #94a3b8;
            padding: 60px 0 30px;
        }
        
        .footer h5 {
            color: white;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .footer a {
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .footer a:hover {
            color: white;
        }
        
        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ef4444;
            color: white;
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 10px;
        }
        
        .search-box {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 50px;
            padding: 10px 20px;
        }
        
        .search-box input {
            background: transparent;
            border: none;
            color: white;
            outline: none;
        }
        
        .search-box input::placeholder {
            color: rgba(255,255,255,0.6);
        }
    </style>
    
    <style><?= $this->renderSection('styles') ?></style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url() ?>">
                <i class="bi bi-ticket-perforated-fill me-2"></i>EventHub
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('eventos') ?>">Eventos</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Categorias</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= base_url('eventos?categoria=show') ?>">Shows</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('eventos?categoria=teatro') ?>">Teatro</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('eventos?categoria=esporte') ?>">Esportes</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('eventos?categoria=festival') ?>">Festivais</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('eventos?categoria=conferencia') ?>">Conferências</a></li>
                        </ul>
                    </li>
                </ul>
                
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item me-3">
                        <a class="nav-link position-relative" href="<?= base_url('carrinho') ?>">
                            <i class="bi bi-cart3 fs-5"></i>
                            <span class="cart-badge" id="cartCount" style="display: none;">0</span>
                        </a>
                    </li>
                    
                    <?php if (auth()->loggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i>
                                <?= esc(auth()->user()->username) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= base_url('meus-pedidos') ?>">
                                    <i class="bi bi-bag me-2"></i>Meus Pedidos
                                </a></li>
                                <li><a class="dropdown-item" href="<?= base_url('meus-ingressos') ?>">
                                    <i class="bi bi-ticket-perforated me-2"></i>Meus Ingressos
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <?php if (auth()->user()->is_organizer): ?>
                                    <li><a class="dropdown-item" href="<?= base_url('organizer/dashboard') ?>">
                                        <i class="bi bi-grid me-2"></i>Painel Organizador
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?= base_url('logout') ?>">
                                    <i class="bi bi-box-arrow-right me-2"></i>Sair
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('login') ?>">Entrar</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary btn-sm ms-2" href="<?= base_url('register') ?>">Cadastrar</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Footer -->
    <footer class="footer mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5><i class="bi bi-ticket-perforated-fill me-2"></i>EventHub</h5>
                    <p>A melhor plataforma para comprar ingressos para shows, teatros, esportes e muito mais.</p>
                    <div class="d-flex gap-3">
                        <a href="#"><i class="bi bi-facebook fs-5"></i></a>
                        <a href="#"><i class="bi bi-instagram fs-5"></i></a>
                        <a href="#"><i class="bi bi-twitter-x fs-5"></i></a>
                        <a href="#"><i class="bi bi-youtube fs-5"></i></a>
                    </div>
                </div>
                <div class="col-lg-2">
                    <h5>Eventos</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#">Shows</a></li>
                        <li class="mb-2"><a href="#">Teatro</a></li>
                        <li class="mb-2"><a href="#">Esportes</a></li>
                        <li class="mb-2"><a href="#">Festivais</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h5>Ajuda</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#">Central de Ajuda</a></li>
                        <li class="mb-2"><a href="#">Como Comprar</a></li>
                        <li class="mb-2"><a href="#">Reembolsos</a></li>
                        <li class="mb-2"><a href="#">Contato</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h5>Quer ser um organizador?</h5>
                    <p>Crie seus eventos e venda ingressos pela nossa plataforma.</p>
                    <a href="<?= base_url('organizer/become') ?>" class="btn btn-outline-light">
                        Saiba mais <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            <hr class="my-4" style="border-color: #334155;">
            <div class="row">
                <div class="col-md-6">
                    <small>&copy; <?= date('Y') ?> EventHub. Todos os direitos reservados.</small>
                </div>
                <div class="col-md-6 text-md-end">
                    <small>
                        <a href="#" class="me-3">Termos de Uso</a>
                        <a href="#">Privacidade</a>
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- JS Base -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Cart Count Script -->
    <script>
        function updateCartCount() {
            fetch('<?= base_url('carrinho/contador') ?>')
                .then(r => r.json())
                .then(data => {
                    const badge = document.getElementById('cartCount');
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'block';
                    } else {
                        badge.style.display = 'none';
                    }
                })
                .catch(() => {});
        }
        
        // Atualizar ao carregar
        updateCartCount();
        
        // Atualizar periodicamente
        setInterval(updateCartCount, 30000);
    </script>
    
    <script><?= $this->renderSection('scripts') ?></script>
</body>
</html>
