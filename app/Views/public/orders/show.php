<?= $this->extend('layouts/public') ?>

<?= $this->section('title') ?>Pedido #<?= str_pad($order->id, 6, '0', STR_PAD_LEFT) ?><?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .order-status-badge {
        padding: 6px 16px;
        border-radius: 20px;
        font-weight: 500;
    }
    
    .status-completed {
        background: #dcfce7;
        color: #166534;
    }
    
    .status-refunded {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .ticket-item {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
    }
    
    .ticket-code {
        font-family: monospace;
        font-size: 1rem;
        letter-spacing: 1px;
        background: #f1f5f9;
        padding: 6px 12px;
        border-radius: 6px;
    }
    
    .timeline-item {
        position: relative;
        padding-left: 30px;
        padding-bottom: 20px;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: 9px;
        top: 25px;
        bottom: 0;
        width: 2px;
        background: #e2e8f0;
    }
    
    .timeline-item:last-child::before {
        display: none;
    }
    
    .timeline-dot {
        position: absolute;
        left: 0;
        top: 5px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #6366f1;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .timeline-dot i {
        color: white;
        font-size: 10px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('meus-pedidos') ?>">Meus Pedidos</a></li>
            <li class="breadcrumb-item active">Pedido #<?= str_pad($order->id, 6, '0', STR_PAD_LEFT) ?></li>
        </ol>
    </nav>
    
    <?php if (session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= esc(session('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= esc(session('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Order Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h1 class="h4 fw-bold mb-1">Pedido #<?= str_pad($order->id, 6, '0', STR_PAD_LEFT) ?></h1>
                            <p class="text-muted mb-0">Realizado em <?= date('d/m/Y \à\s H:i', strtotime($order->created_at)) ?></p>
                        </div>
                        <span class="order-status-badge status-<?= esc($order->status) ?>">
                            <?php
                            $statusLabels = [
                                'completed' => 'Concluído',
                                'pending' => 'Pendente',
                                'refunded' => 'Reembolsado',
                                'partial_refund' => 'Reembolso Parcial',
                                'cancelled' => 'Cancelado'
                            ];
                            echo $statusLabels[$order->status] ?? ucfirst($order->status);
                            ?>
                        </span>
                    </div>
                    
                    <hr>
                    
                    <!-- Event Info -->
                    <div class="d-flex gap-4">
                        <?php if ($event && $event->image): ?>
                            <img src="<?= base_url('uploads/events/' . esc($event->image)) ?>" 
                                 alt="<?= esc($event->title) ?>"
                                 class="rounded" style="width: 120px; height: 80px; object-fit: cover;">
                        <?php endif; ?>
                        
                        <div>
                            <h5 class="fw-bold mb-2"><?= esc($event->title ?? 'Evento não encontrado') ?></h5>
                            <?php if (!empty($tickets[0]['event_day'])): ?>
                                <p class="text-muted mb-1">
                                    <i class="bi bi-calendar3 me-2"></i>
                                    <?= date('d/m/Y', strtotime($tickets[0]['event_day']->event_date)) ?>
                                    <?php if ($tickets[0]['event_day']->start_time): ?>
                                        às <?= date('H:i', strtotime($tickets[0]['event_day']->start_time)) ?>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                            <?php if ($event): ?>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-geo-alt me-2"></i>
                                    <?= esc($event->venue) ?>
                                    <?php if ($event->city): ?> - <?= esc($event->city) ?><?php endif; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tickets -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="bi bi-ticket-perforated me-2 text-primary"></i>
                        Ingressos (<?= count($tickets) ?>)
                    </h5>
                    
                    <?php foreach ($tickets as $detail): ?>
                        <div class="ticket-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="fw-bold mb-1">
                                        <?= esc($detail['seat']->code ?? 'N/A') ?>
                                    </h6>
                                    <p class="text-muted mb-2">
                                        <?= esc($detail['sector']->name ?? 'Setor') ?> - 
                                        Fila <?= esc($detail['queue']->name ?? 'N/A') ?>
                                    </p>
                                    <span class="ticket-code"><?= esc($detail['ticket']->code) ?></span>
                                </div>
                                
                                <div class="text-end">
                                    <span class="fw-bold text-primary">
                                        R$ <?= number_format($detail['ticket']->price, 2, ',', '.') ?>
                                    </span>
                                    <br>
                                    <span class="badge <?= $detail['ticket']->status === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= $detail['ticket']->status === 'active' ? 'Ativo' : 'Cancelado' ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Order Timeline -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="bi bi-clock-history me-2 text-primary"></i>Histórico
                    </h5>
                    
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-dot">
                                <i class="bi bi-check"></i>
                            </div>
                            <div>
                                <p class="fw-medium mb-0">Pedido realizado</p>
                                <small class="text-muted"><?= date('d/m/Y \à\s H:i', strtotime($order->created_at)) ?></small>
                            </div>
                        </div>
                        
                        <?php if ($order->paid_at): ?>
                            <div class="timeline-item">
                                <div class="timeline-dot">
                                    <i class="bi bi-check"></i>
                                </div>
                                <div>
                                    <p class="fw-medium mb-0">Pagamento confirmado</p>
                                    <small class="text-muted"><?= date('d/m/Y \à\s H:i', strtotime($order->paid_at)) ?></small>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($order->refunded_at): ?>
                            <div class="timeline-item">
                                <div class="timeline-dot" style="background: #ef4444;">
                                    <i class="bi bi-arrow-return-left"></i>
                                </div>
                                <div>
                                    <p class="fw-medium mb-0">Reembolso processado</p>
                                    <small class="text-muted"><?= date('d/m/Y \à\s H:i', strtotime($order->refunded_at)) ?></small>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Order Summary -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Resumo do pagamento</h5>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Ingressos (<?= count($tickets) ?>)</span>
                        <span>R$ <?= number_format($order->subtotal, 2, ',', '.') ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Taxa de serviço</span>
                        <span>R$ <?= number_format($order->service_fee, 2, ',', '.') ?></span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold text-primary fs-5">
                            R$ <?= number_format($order->total, 2, ',', '.') ?>
                        </span>
                    </div>
                    
                    <?php if ($order->refund_amount): ?>
                        <div class="d-flex justify-content-between text-danger">
                            <span>Reembolsado</span>
                            <span>- R$ <?= number_format($order->refund_amount, 2, ',', '.') ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <p class="text-muted small mb-0">
                        <i class="bi bi-credit-card me-1"></i>
                        Pago via <?= ucfirst($order->payment_method ?? 'cartão') ?>
                    </p>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Ações</h5>
                    
                    <?php if ($order->status === 'completed'): ?>
                        <a href="<?= base_url('meus-ingressos') ?>" class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-ticket-perforated me-2"></i>Ver Ingressos
                        </a>
                    <?php endif; ?>
                    
                    <a href="<?= base_url("meus-pedidos/{$order->id}/comprovante") ?>" class="btn btn-outline-primary w-100 mb-2">
                        <i class="bi bi-file-earmark-text me-2"></i>Ver Comprovante
                    </a>
                    
                    <?php if ($refundEligibility['eligible']): ?>
                        <a href="<?= base_url("meus-pedidos/{$order->id}/reembolso") ?>" class="btn btn-outline-danger w-100">
                            <i class="bi bi-arrow-return-left me-2"></i>Solicitar Reembolso
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Help -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Precisa de ajuda?</h6>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-question-circle me-2"></i>
                        Entre em contato pelo <a href="mailto:suporte@eventhub.com">suporte@eventhub.com</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
