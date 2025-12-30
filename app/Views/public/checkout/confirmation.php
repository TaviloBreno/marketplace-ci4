<?= $this->extend('layouts/public') ?>

<?= $this->section('title') ?>Pedido Confirmado<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .success-icon {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 30px;
        animation: scaleIn 0.5s ease;
    }
    
    @keyframes scaleIn {
        from { transform: scale(0); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    
    .ticket-card {
        border: 2px dashed #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        position: relative;
        overflow: hidden;
    }
    
    .ticket-card::before {
        content: '';
        position: absolute;
        left: -10px;
        top: 50%;
        transform: translateY(-50%);
        width: 20px;
        height: 20px;
        background: #f8fafc;
        border-radius: 50%;
        border: 2px dashed #e2e8f0;
    }
    
    .ticket-card::after {
        content: '';
        position: absolute;
        right: -10px;
        top: 50%;
        transform: translateY(-50%);
        width: 20px;
        height: 20px;
        background: #f8fafc;
        border-radius: 50%;
        border: 2px dashed #e2e8f0;
    }
    
    .ticket-code {
        font-family: monospace;
        font-size: 1.1rem;
        letter-spacing: 2px;
        background: #f1f5f9;
        padding: 8px 15px;
        border-radius: 6px;
    }
    
    .qr-placeholder {
        width: 80px;
        height: 80px;
        background: #f1f5f9;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .order-number {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Success Header -->
            <div class="text-center mb-5">
                <div class="success-icon">
                    <i class="bi bi-check-lg text-white display-4"></i>
                </div>
                <h1 class="h2 fw-bold mb-2">Compra realizada com sucesso!</h1>
                <p class="text-muted lead">
                    Seu pedido <span class="order-number fw-bold">#<?= str_pad($order->id, 6, '0', STR_PAD_LEFT) ?></span> foi confirmado
                </p>
            </div>
            
            <!-- Order Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h5 class="fw-bold mb-3">
                                <i class="bi bi-calendar-event me-2 text-primary"></i><?= esc($event->title) ?>
                            </h5>
                            <p class="mb-2">
                                <i class="bi bi-calendar3 text-muted me-2"></i>
                                <?= date('d/m/Y', strtotime($eventDay->event_date)) ?>
                                <?php if ($eventDay->start_time): ?>
                                    às <?= date('H:i', strtotime($eventDay->start_time)) ?>
                                <?php endif; ?>
                            </p>
                            <p class="mb-0">
                                <i class="bi bi-geo-alt text-muted me-2"></i>
                                <?= esc($event->venue) ?>
                                <?php if ($event->city): ?> - <?= esc($event->city) ?><?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="text-muted mb-1">Total pago</p>
                            <p class="h3 fw-bold text-primary mb-0">
                                R$ <?= number_format($order->total, 2, ',', '.') ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tickets -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="bi bi-ticket-perforated me-2 text-primary"></i>
                        Seus ingressos (<?= count($tickets) ?>)
                    </h5>
                    
                    <?php foreach ($tickets as $detail): ?>
                        <div class="ticket-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-1">
                                        <strong class="fs-5"><?= esc($detail['seat']->code) ?></strong>
                                    </p>
                                    <p class="text-muted mb-2">
                                        <?= esc($detail['sector']->name ?? 'Setor') ?> - 
                                        Fila <?= esc($detail['queue']->name ?? '') ?>
                                    </p>
                                    <div class="ticket-code">
                                        <?= esc($detail['ticket']->code) ?>
                                    </div>
                                </div>
                                <div class="qr-placeholder">
                                    <i class="bi bi-qr-code fs-1 text-muted"></i>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Apresente o QR Code do ingresso na entrada do evento
                    </div>
                </div>
            </div>
            
            <!-- Next Steps -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="bi bi-list-check me-2 text-primary"></i>Próximos passos
                    </h5>
                    
                    <div class="d-flex mb-3">
                        <div class="me-3">
                            <span class="badge bg-primary rounded-circle p-2">
                                <i class="bi bi-envelope"></i>
                            </span>
                        </div>
                        <div>
                            <h6 class="mb-1">Verifique seu e-mail</h6>
                            <p class="text-muted mb-0">Enviamos a confirmação e os ingressos para <?= esc(auth()->user()->email) ?></p>
                        </div>
                    </div>
                    
                    <div class="d-flex mb-3">
                        <div class="me-3">
                            <span class="badge bg-primary rounded-circle p-2">
                                <i class="bi bi-phone"></i>
                            </span>
                        </div>
                        <div>
                            <h6 class="mb-1">Acesse seus ingressos</h6>
                            <p class="text-muted mb-0">Você pode acessar seus ingressos a qualquer momento em "Meus Ingressos"</p>
                        </div>
                    </div>
                    
                    <div class="d-flex">
                        <div class="me-3">
                            <span class="badge bg-primary rounded-circle p-2">
                                <i class="bi bi-calendar-check"></i>
                            </span>
                        </div>
                        <div>
                            <h6 class="mb-1">Compareça ao evento</h6>
                            <p class="text-muted mb-0">No dia do evento, apresente o QR Code na entrada</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
                <a href="<?= base_url('meus-ingressos') ?>" class="btn btn-primary btn-lg px-5">
                    <i class="bi bi-ticket-perforated me-2"></i>Ver meus ingressos
                </a>
                <a href="<?= base_url('eventos') ?>" class="btn btn-outline-primary btn-lg px-5">
                    <i class="bi bi-search me-2"></i>Explorar mais eventos
                </a>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
