<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Status da Conta<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php if (session('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= session('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= session('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <?php if ($status === 'active'): ?>
                            <div class="mb-3">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                            </div>
                            <h1 class="h2 mb-3 text-success">Conta Ativa!</h1>
                            <p class="text-muted">
                                Sua conta de organizador está totalmente configurada e pronta para receber pagamentos.
                            </p>
                        <?php elseif ($status === 'pending'): ?>
                            <div class="mb-3">
                                <i class="bi bi-hourglass-split text-warning" style="font-size: 4rem;"></i>
                            </div>
                            <h1 class="h2 mb-3 text-warning">Cadastro Pendente</h1>
                            <p class="text-muted">
                                Seu cadastro no Stripe ainda não foi concluído. Complete o processo para começar a vender.
                            </p>
                        <?php else: ?>
                            <div class="mb-3">
                                <i class="bi bi-exclamation-circle-fill text-danger" style="font-size: 4rem;"></i>
                            </div>
                            <h1 class="h2 mb-3 text-danger">Ação Necessária</h1>
                            <p class="text-muted">
                                O Stripe precisa de informações adicionais para ativar sua conta.
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Informações da Conta</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <strong>Nome da Empresa:</strong><br>
                                    <span class="text-muted"><?= esc($user->company_name ?? '-') ?></span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Documento:</strong><br>
                                    <span class="text-muted"><?= esc($user->document ?? '-') ?></span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Status no Stripe:</strong><br>
                                    <?php if ($status === 'active'): ?>
                                        <span class="badge bg-success">Ativo</span>
                                    <?php elseif ($status === 'pending'): ?>
                                        <span class="badge bg-warning text-dark">Pendente</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Requer Ação</span>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>ID Stripe:</strong><br>
                                    <span class="text-muted font-monospace small"><?= esc($user->stripe_account_id ?? '-') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($status === 'active'): ?>
                        <!-- Saldo disponível -->
                        <?php if (isset($balance)): ?>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h6 class="card-subtitle mb-2 opacity-75">Saldo Disponível</h6>
                                        <h3 class="card-title mb-0">R$ <?= number_format($balance['available'] / 100, 2, ',', '.') ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h6 class="card-subtitle mb-2 opacity-75">Saldo Pendente</h6>
                                        <h3 class="card-title mb-0">R$ <?= number_format($balance['pending'] / 100, 2, ',', '.') ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="d-grid gap-3">
                            <a href="<?= url_to('organizer.dashboard') ?>" class="btn btn-primary btn-lg">
                                <i class="bi bi-grid-1x2 me-2"></i>
                                Ir para o Painel do Organizador
                            </a>
                            <a href="<?= url_to('organizer.stripe_dashboard') ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-box-arrow-up-right me-2"></i>
                                Acessar Dashboard do Stripe
                            </a>
                        </div>
                    <?php elseif ($status === 'pending'): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle me-2"></i>
                            Você precisa completar seu cadastro no Stripe para poder receber pagamentos.
                        </div>
                        <div class="d-grid">
                            <a href="<?= url_to('organizer.onboarding_refresh') ?>" class="btn btn-warning btn-lg">
                                <i class="bi bi-arrow-right-circle me-2"></i>
                                Continuar Cadastro no Stripe
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            O Stripe detectou algum problema com sua conta. Acesse o painel para verificar.
                        </div>
                        <div class="d-grid">
                            <a href="<?= url_to('organizer.onboarding_refresh') ?>" class="btn btn-danger btn-lg">
                                <i class="bi bi-arrow-right-circle me-2"></i>
                                Verificar no Stripe
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
