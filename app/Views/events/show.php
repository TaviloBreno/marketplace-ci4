<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($event->title) ?><?= $this->endSection() ?>

<?= $this->section('styles') ?>
.event-banner {
    height: 200px;
    background-size: cover;
    background-position: center;
    border-radius: 12px;
    position: relative;
}
.event-banner-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.7) 100%);
    border-radius: 12px;
}
.status-badge {
    position: absolute;
    top: 15px;
    right: 15px;
}
.stat-card {
    text-align: center;
    padding: 20px;
    border-radius: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
.stat-card.success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}
.stat-card.warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}
.stat-card.info {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?= base_url('organizer/events') ?>">Eventos</a></li>
                    <li class="breadcrumb-item active"><?= esc($event->title) ?></li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <?php if ($event->status === 'draft'): ?>
                <button type="button" class="btn btn-success" id="publishBtn">
                    <i class="bi bi-globe me-2"></i>Publicar
                </button>
            <?php endif; ?>
            <?php if ($event->canEdit()): ?>
                <a href="<?= base_url('organizer/events/' . $event->id . '/edit') ?>" class="btn btn-outline-primary">
                    <i class="bi bi-pencil me-2"></i>Editar
                </a>
            <?php endif; ?>
            <a href="<?= base_url('organizer/events/' . $event->id . '/seat-map') ?>" class="btn btn-outline-info">
                <i class="bi bi-grid-3x3 me-2"></i>Mapa de Assentos
            </a>
            <?php if ($event->status !== 'cancelled'): ?>
                <button type="button" class="btn btn-outline-danger" id="cancelBtn">
                    <i class="bi bi-x-circle me-2"></i>Cancelar
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if (session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= session('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= session('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Banner -->
            <div class="event-banner mb-4" style="background-image: url('<?= $event->getBannerUrl() ?>');">
                <div class="event-banner-overlay"></div>
                <div class="status-badge">
                    <?php
                    $statusClasses = [
                        'draft'     => 'bg-secondary',
                        'published' => 'bg-success',
                        'cancelled' => 'bg-danger',
                        'finished'  => 'bg-dark',
                    ];
                    ?>
                    <span class="badge <?= $statusClasses[$event->status] ?? 'bg-secondary' ?> fs-6">
                        <?= $event->getStatusLabel() ?>
                    </span>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h1 class="h3 mb-3"><?= esc($event->title) ?></h1>
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                    <i class="bi bi-geo-alt text-primary"></i>
                                </div>
                                <div>
                                    <strong><?= esc($event->venue_name) ?></strong><br>
                                    <small class="text-muted"><?= $event->getFullAddress() ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                    <i class="bi bi-calendar-event text-success"></i>
                                </div>
                                <div>
                                    <?php if ($nextDay): ?>
                                        <strong>Próxima data</strong><br>
                                        <small class="text-muted">
                                            <?= date('d/m/Y', strtotime($nextDay->event_date)) ?> às <?= date('H:i', strtotime($nextDay->start_time)) ?>
                                        </small>
                                    <?php else: ?>
                                        <strong>Sem próximas datas</strong><br>
                                        <small class="text-muted">Nenhuma data ativa</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($event->description): ?>
                        <h5>Descrição</h5>
                        <p class="text-muted"><?= nl2br(esc($event->description)) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Datas -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Datas do Evento</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($days)): ?>
                        <p class="text-muted mb-0">Nenhuma data cadastrada.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Horário</th>
                                        <th>Portões</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($days as $day): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($day->event_date)) ?></td>
                                            <td><?= date('H:i', strtotime($day->start_time)) ?></td>
                                            <td><?= $day->doors_open ? date('H:i', strtotime($day->doors_open)) : '-' ?></td>
                                            <td>
                                                <?php if ($day->is_active): ?>
                                                    <span class="badge bg-success">Ativo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inativo</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Setores -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-grid me-2"></i>Setores</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($sectors)): ?>
                        <p class="text-muted mb-0">Nenhum setor cadastrado.</p>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($sectors as $sector): ?>
                                <div class="col-md-6">
                                    <div class="card h-100" style="border-left: 4px solid <?= esc($sector->color) ?>;">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1"><?= esc($sector->name) ?></h6>
                                                    <p class="text-muted small mb-2">
                                                        <?= $sector->is_numbered ? 'Numerado' : 'Pista' ?>
                                                    </p>
                                                </div>
                                                <span class="badge bg-primary">
                                                    R$ <?= number_format($sector->price, 2, ',', '.') ?>
                                                </span>
                                            </div>
                                            <div class="d-flex text-muted small">
                                                <span class="me-3">
                                                    <i class="bi bi-grid-3x3"></i> <?= $sector->queue_count ?? 0 ?> filas
                                                </span>
                                                <span>
                                                    <i class="bi bi-person"></i> <?= $sector->seat_count ?? 0 ?> assentos
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Stats -->
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <div class="stat-card">
                        <h3 class="mb-1"><?= $stats['total_orders'] ?? 0 ?></h3>
                        <small>Pedidos</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="stat-card success">
                        <h3 class="mb-1"><?= $stats['tickets_sold'] ?? 0 ?></h3>
                        <small>Ingressos Vendidos</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="stat-card warning">
                        <h3 class="mb-1">R$ <?= number_format($stats['total_revenue'] ?? 0, 2, ',', '.') ?></h3>
                        <small>Receita Total</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="stat-card info">
                        <h3 class="mb-1"><?= $stats['pending_orders'] ?? 0 ?></h3>
                        <small>Pendentes</small>
                    </div>
                </div>
            </div>

            <!-- Info -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Informações</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <strong>Categoria:</strong> <?= ucfirst(esc($event->category)) ?>
                        </li>
                        <li class="mb-2">
                            <strong>Slug:</strong> <code><?= esc($event->slug) ?></code>
                        </li>
                        <li class="mb-2">
                            <strong>Máx. por compra:</strong> <?= $event->max_tickets_per_purchase ?>
                        </li>
                        <li class="mb-2">
                            <strong>Criado em:</strong> <?= $event->created_at->toLocalizedString('dd/MM/yyyy HH:mm') ?>
                        </li>
                        <li class="mb-0">
                            <strong>Atualizado:</strong> <?= $event->updated_at->toLocalizedString('dd/MM/yyyy HH:mm') ?>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Imagem -->
            <?php if ($event->image): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Imagem do Evento</h6>
                    </div>
                    <div class="card-body p-0">
                        <img src="<?= $event->getImageUrl() ?>" class="img-fluid" alt="<?= esc($event->title) ?>">
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Confirmar Publicação -->
<div class="modal fade" id="publishModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Publicar Evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja publicar este evento?</p>
                <p class="text-muted">Após publicado, o evento ficará visível para todos os usuários e poderá receber vendas.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="confirmPublish">Publicar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Cancelamento -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancelar Evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-danger"><strong>Atenção!</strong> Esta ação não pode ser desfeita.</p>
                <p>Ao cancelar o evento, todos os ingressos vendidos serão reembolsados automaticamente.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                <button type="button" class="btn btn-danger" id="confirmCancel">Cancelar Evento</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const eventId = <?= $event->id ?>;

// Publicar
document.getElementById('publishBtn')?.addEventListener('click', function() {
    new bootstrap.Modal(document.getElementById('publishModal')).show();
});

document.getElementById('confirmPublish')?.addEventListener('click', async function() {
    try {
        const response = await fetch(`<?= base_url('organizer/events/') ?>${eventId}/publish`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Erro ao publicar evento.');
        }
    } catch (error) {
        alert('Erro ao publicar evento.');
    }
});

// Cancelar
document.getElementById('cancelBtn')?.addEventListener('click', function() {
    new bootstrap.Modal(document.getElementById('cancelModal')).show();
});

document.getElementById('confirmCancel')?.addEventListener('click', async function() {
    try {
        const response = await fetch(`<?= base_url('organizer/events/') ?>${eventId}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Erro ao cancelar evento.');
        }
    } catch (error) {
        alert('Erro ao cancelar evento.');
    }
});
</script>
<?= $this->endSection() ?>
