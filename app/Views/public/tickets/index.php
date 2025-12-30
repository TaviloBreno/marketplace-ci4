<?= $this->extend('layouts/public') ?>

<?= $this->section('title') ?>Meus Ingressos<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .ticket-group-card {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 20px;
    }
    
    .ticket-group-header {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
        padding: 20px;
    }
    
    .ticket-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .ticket-item:last-child {
        border-bottom: none;
    }
    
    .ticket-code {
        font-family: monospace;
        background: #f1f5f9;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 0.85rem;
    }
    
    .seat-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #f1f5f9;
        padding: 8px 14px;
        border-radius: 8px;
        font-weight: 600;
    }
    
    .sector-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }
    
    .countdown {
        font-size: 0.85rem;
        padding: 4px 10px;
        border-radius: 20px;
    }
    
    .countdown-soon {
        background: #fef3c7;
        color: #92400e;
    }
    
    .countdown-later {
        background: #e0f2fe;
        color: #0369a1;
    }
    
    .empty-tickets {
        text-align: center;
        padding: 80px 20px;
    }
    
    .empty-tickets i {
        font-size: 80px;
        color: #cbd5e1;
    }
    
    .nav-pills .nav-link {
        color: #64748b;
        border-radius: 20px;
    }
    
    .nav-pills .nav-link.active {
        background: #6366f1;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container py-5">
    <h1 class="h3 fw-bold mb-4">
        <i class="bi bi-ticket-perforated me-2 text-primary"></i>Meus Ingressos
    </h1>
    
    <!-- Tabs -->
    <ul class="nav nav-pills mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#upcoming" type="button">
                Próximos (<?= count($upcoming) ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#past" type="button">
                Passados (<?= count($past) ?>)
            </button>
        </li>
    </ul>
    
    <div class="tab-content">
        <!-- Upcoming Events -->
        <div class="tab-pane fade show active" id="upcoming">
            <?php if (empty($upcoming)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body empty-tickets">
                        <i class="bi bi-calendar-x"></i>
                        <h3 class="mt-4 mb-2">Nenhum ingresso para eventos futuros</h3>
                        <p class="text-muted mb-4">Explore eventos incríveis e garanta seus ingressos!</p>
                        <a href="<?= base_url('eventos') ?>" class="btn btn-primary btn-lg">
                            <i class="bi bi-search me-2"></i>Explorar eventos
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($upcoming as $group): ?>
                    <div class="card ticket-group-card shadow-sm">
                        <div class="ticket-group-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="fw-bold mb-1"><?= esc($group['event']->title ?? 'Evento') ?></h5>
                                    <p class="mb-0 opacity-75">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        <?= date('d/m/Y', strtotime($group['event_day']->event_date)) ?>
                                        <?php if ($group['event_day']->start_time): ?>
                                            às <?= date('H:i', strtotime($group['event_day']->start_time)) ?>
                                        <?php endif; ?>
                                    </p>
                                    <p class="mb-0 opacity-75">
                                        <i class="bi bi-geo-alt me-1"></i>
                                        <?= esc($group['event']->venue ?? '') ?>
                                    </p>
                                </div>
                                
                                <?php 
                                $daysUntil = (strtotime($group['event_day']->event_date) - strtotime(date('Y-m-d'))) / 86400;
                                ?>
                                <span class="countdown <?= $daysUntil <= 7 ? 'countdown-soon' : 'countdown-later' ?>">
                                    <?php if ($daysUntil == 0): ?>
                                        <i class="bi bi-exclamation-circle me-1"></i>Hoje!
                                    <?php elseif ($daysUntil == 1): ?>
                                        <i class="bi bi-clock me-1"></i>Amanhã
                                    <?php else: ?>
                                        <i class="bi bi-clock me-1"></i>Em <?= (int)$daysUntil ?> dias
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="card-body p-0">
                            <?php foreach ($group['tickets'] as $detail): ?>
                                <div class="ticket-item">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="seat-badge">
                                            <span class="sector-dot" style="background: <?= esc($detail['sector']->color ?? '#6366f1') ?>"></span>
                                            <span><?= esc($detail['seat']->code ?? 'N/A') ?></span>
                                        </div>
                                        <div>
                                            <span class="text-muted small d-block">
                                                <?= esc($detail['sector']->name ?? 'Setor') ?> - 
                                                Fila <?= esc($detail['queue']->name ?? '') ?>
                                            </span>
                                            <span class="ticket-code"><?= esc($detail['ticket']->code) ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <a href="<?= base_url('ingresso/' . esc($detail['ticket']->code)) ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-qr-code me-1"></i>Ver
                                        </a>
                                        <a href="<?= base_url('ingresso/' . esc($detail['ticket']->code) . '/imprimir') ?>" 
                                           class="btn btn-primary btn-sm" target="_blank">
                                            <i class="bi bi-printer me-1"></i>Imprimir
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Past Events -->
        <div class="tab-pane fade" id="past">
            <?php if (empty($past)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-clock-history display-1 text-muted"></i>
                        <h4 class="mt-3 mb-2">Nenhum evento passado</h4>
                        <p class="text-muted">Seus ingressos de eventos passados aparecerão aqui.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($past as $group): ?>
                    <div class="card ticket-group-card shadow-sm" style="opacity: 0.7;">
                        <div class="ticket-group-header" style="background: #64748b;">
                            <div>
                                <h5 class="fw-bold mb-1"><?= esc($group['event']->title ?? 'Evento') ?></h5>
                                <p class="mb-0 opacity-75">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    <?= date('d/m/Y', strtotime($group['event_day']->event_date ?? 'now')) ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="card-body p-0">
                            <?php foreach ($group['tickets'] as $detail): ?>
                                <div class="ticket-item">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="seat-badge">
                                            <span><?= esc($detail['seat']->code ?? 'N/A') ?></span>
                                        </div>
                                        <span class="ticket-code"><?= esc($detail['ticket']->code) ?></span>
                                    </div>
                                    
                                    <span class="badge bg-secondary">
                                        <?= $detail['ticket']->status === 'used' ? 'Utilizado' : 'Expirado' ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
