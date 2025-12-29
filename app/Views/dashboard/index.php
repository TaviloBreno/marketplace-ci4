<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Dashboard</h4>
        <p class="text-muted mb-0">Bem-vindo de volta, <?= auth()->user()->username ?? 'Usuário' ?>!</p>
    </div>
    <div>
        <button class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Novo Produto
        </button>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-stat">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                        <i class="bi bi-currency-dollar text-primary fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <p class="text-muted mb-0 small">Vendas Hoje</p>
                        <h4 class="mb-0">R$ 12.450</h4>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-success-subtle text-success">
                        <i class="bi bi-arrow-up"></i> 12%
                    </span>
                    <span class="text-muted small ms-2">vs ontem</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-stat">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                        <i class="bi bi-cart-check text-success fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <p class="text-muted mb-0 small">Pedidos</p>
                        <h4 class="mb-0">156</h4>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-success-subtle text-success">
                        <i class="bi bi-arrow-up"></i> 8%
                    </span>
                    <span class="text-muted small ms-2">vs ontem</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-stat">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                        <i class="bi bi-box-seam text-warning fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <p class="text-muted mb-0 small">Produtos</p>
                        <h4 class="mb-0">1.248</h4>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-warning-subtle text-warning">
                        <i class="bi bi-dash"></i> 2%
                    </span>
                    <span class="text-muted small ms-2">este mês</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-stat">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3">
                        <i class="bi bi-people text-info fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <p class="text-muted mb-0 small">Clientes</p>
                        <h4 class="mb-0">3.842</h4>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-success-subtle text-success">
                        <i class="bi bi-arrow-up"></i> 24%
                    </span>
                    <span class="text-muted small ms-2">este mês</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Orders -->
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Pedidos Recentes</h5>
                <a href="#" class="btn btn-sm btn-outline-primary">Ver todos</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Pedido</th>
                                <th>Cliente</th>
                                <th>Produto</th>
                                <th>Valor</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>#12345</strong></td>
                                <td>João Silva</td>
                                <td>iPhone 15 Pro</td>
                                <td>R$ 8.999,00</td>
                                <td><span class="badge bg-success">Entregue</span></td>
                            </tr>
                            <tr>
                                <td><strong>#12344</strong></td>
                                <td>Maria Santos</td>
                                <td>MacBook Air M2</td>
                                <td>R$ 12.499,00</td>
                                <td><span class="badge bg-warning">Em trânsito</span></td>
                            </tr>
                            <tr>
                                <td><strong>#12343</strong></td>
                                <td>Pedro Oliveira</td>
                                <td>AirPods Pro</td>
                                <td>R$ 2.299,00</td>
                                <td><span class="badge bg-info">Processando</span></td>
                            </tr>
                            <tr>
                                <td><strong>#12342</strong></td>
                                <td>Ana Costa</td>
                                <td>Apple Watch Series 9</td>
                                <td>R$ 4.799,00</td>
                                <td><span class="badge bg-success">Entregue</span></td>
                            </tr>
                            <tr>
                                <td><strong>#12341</strong></td>
                                <td>Carlos Lima</td>
                                <td>iPad Pro 12.9"</td>
                                <td>R$ 10.299,00</td>
                                <td><span class="badge bg-danger">Cancelado</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Products -->
    <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Produtos Mais Vendidos</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                    <div class="bg-light rounded p-2">
                        <i class="bi bi-phone text-primary fs-4"></i>
                    </div>
                    <div class="ms-3 flex-grow-1">
                        <h6 class="mb-0">iPhone 15 Pro</h6>
                        <small class="text-muted">324 vendas</small>
                    </div>
                    <span class="badge bg-primary-subtle text-primary">+12%</span>
                </div>
                
                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                    <div class="bg-light rounded p-2">
                        <i class="bi bi-laptop text-success fs-4"></i>
                    </div>
                    <div class="ms-3 flex-grow-1">
                        <h6 class="mb-0">MacBook Air M2</h6>
                        <small class="text-muted">256 vendas</small>
                    </div>
                    <span class="badge bg-success-subtle text-success">+8%</span>
                </div>
                
                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                    <div class="bg-light rounded p-2">
                        <i class="bi bi-headphones text-warning fs-4"></i>
                    </div>
                    <div class="ms-3 flex-grow-1">
                        <h6 class="mb-0">AirPods Pro</h6>
                        <small class="text-muted">198 vendas</small>
                    </div>
                    <span class="badge bg-warning-subtle text-warning">+5%</span>
                </div>
                
                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                    <div class="bg-light rounded p-2">
                        <i class="bi bi-smartwatch text-info fs-4"></i>
                    </div>
                    <div class="ms-3 flex-grow-1">
                        <h6 class="mb-0">Apple Watch S9</h6>
                        <small class="text-muted">167 vendas</small>
                    </div>
                    <span class="badge bg-info-subtle text-info">+3%</span>
                </div>
                
                <div class="d-flex align-items-center">
                    <div class="bg-light rounded p-2">
                        <i class="bi bi-tablet text-danger fs-4"></i>
                    </div>
                    <div class="ms-3 flex-grow-1">
                        <h6 class="mb-0">iPad Pro 12.9"</h6>
                        <small class="text-muted">145 vendas</small>
                    </div>
                    <span class="badge bg-danger-subtle text-danger">-2%</span>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
