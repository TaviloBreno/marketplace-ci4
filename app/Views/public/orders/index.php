<?= $this->extend('layouts/public') ?>

<?= $this->section('title') ?>Meus Pedidos<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .order-card {
        border: none;
        border-radius: 12px;
        transition: all 0.2s ease;
    }
    
    .order-card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .order-status {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .status-completed {
        background: #dcfce7;
        color: #166534;
    }
    
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    
    .status-refunded {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .status-cancelled {
        background: #f1f5f9;
        color: #475569;
    }
    
    .order-image {
        width: 100px;
        height: 70px;
        object-fit: cover;
        border-radius: 8px;
    }
    
    .empty-state {
        text-align: center;
        padding: 80px 20px;
    }
    
    .empty-state i {
        font-size: 80px;
        color: #cbd5e1;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container py-5">
    <h1 class="h3 fw-bold mb-4">
        <i class="bi bi-bag me-2 text-primary"></i>Meus Pedidos
    </h1>
    
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
    
    <?php if (empty($orders)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body empty-state">
                <i class="bi bi-bag-x"></i>
                <h3 class="mt-4 mb-2">Você ainda não fez nenhum pedido</h3>
                <p class="text-muted mb-4">Explore nossos eventos e garanta seus ingressos!</p>
                <a href="<?= base_url('eventos') ?>" class="btn btn-primary btn-lg">
                    <i class="bi bi-search me-2"></i>Explorar eventos
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($orders as $order): ?>
                <div class="col-12">
                    <div class="card order-card shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex flex-column flex-md-row gap-4">
                                <!-- Event Image -->
                                <?php if ($order->event && $order->event->image): ?>
                                    <img src="<?= base_url('uploads/events/' . esc($order->event->image)) ?>" 
                                         alt="<?= esc($order->event->title) ?>"
                                         class="order-image">
                                <?php else: ?>
                                    <div class="order-image bg-secondary d-flex align-items-center justify-content-center">
                                        <i class="bi bi-calendar-event text-white fs-4"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Order Details -->
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="text-muted small">Pedido #<?= str_pad($order->id, 6, '0', STR_PAD_LEFT) ?></span>
                                            <h5 class="fw-bold mb-0">
                                                <?= $order->event ? esc($order->event->title) : 'Evento não encontrado' ?>
                                            </h5>
                                        </div>
                                        
                                        <span class="order-status status-<?= esc($order->status) ?>">
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
                                    
                                    <p class="text-muted mb-2">
                                        <?php if ($order->event_day): ?>
                                            <i class="bi bi-calendar3 me-1"></i>
                                            <?= date('d/m/Y', strtotime($order->event_day->event_date)) ?>
                                            <?php if ($order->event_day->start_time): ?>
                                                às <?= date('H:i', strtotime($order->event_day->start_time)) ?>
                                            <?php endif; ?>
                                            <span class="mx-2">•</span>
                                        <?php endif; ?>
                                        <i class="bi bi-ticket-perforated me-1"></i>
                                        <?= $order->tickets_count ?> ingresso(s)
                                    </p>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div>
                                            <span class="text-muted small">Comprado em <?= date('d/m/Y H:i', strtotime($order->created_at)) ?></span>
                                        </div>
                                        
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="fw-bold text-primary fs-5">
                                                R$ <?= number_format($order->total, 2, ',', '.') ?>
                                            </span>
                                            <a href="<?= base_url('meus-pedidos/' . $order->id) ?>" class="btn btn-outline-primary btn-sm">
                                                Ver detalhes
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
