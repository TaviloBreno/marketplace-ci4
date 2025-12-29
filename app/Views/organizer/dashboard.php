<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Painel do Organizador<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Painel do Organizador</h1>
            <p class="text-muted mb-0">Gerencie seus eventos e acompanhe suas vendas</p>
        </div>
        <a href="<?= url_to('events.create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Novo Evento
        </a>
    </div>

    <?php if (session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= session('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                <i class="bi bi-calendar-event text-primary" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Eventos Ativos</h6>
                            <h3 class="mb-0"><?= $stats['active_events'] ?? 0 ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                <i class="bi bi-ticket-perforated text-success" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Ingressos Vendidos</h6>
                            <h3 class="mb-0"><?= number_format($stats['tickets_sold'] ?? 0, 0, ',', '.') ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="rounded-circle bg-info bg-opacity-10 p-3">
                                <i class="bi bi-currency-dollar text-info" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Vendas Totais</h6>
                            <h3 class="mb-0">R$ <?= number_format($stats['total_sales'] ?? 0, 2, ',', '.') ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                                <i class="bi bi-wallet2 text-warning" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Saldo Disponível</h6>
                            <h3 class="mb-0">R$ <?= number_format($balance['available'] ?? 0, 2, ',', '.') ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Events -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Meus Eventos</h5>
                    <a href="<?= url_to('events.index') ?>" class="btn btn-sm btn-outline-primary">Ver Todos</a>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($events)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Evento</th>
                                        <th>Data</th>
                                        <th>Vendas</th>
                                        <th>Status</th>
                                        <th width="100"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($events as $event): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($event->image): ?>
                                                        <img src="<?= base_url('uploads/events/' . $event->image) ?>" 
                                                             class="rounded me-3" width="50" height="50" 
                                                             style="object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                                             style="width: 50px; height: 50px;">
                                                            <i class="bi bi-calendar-event text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-0"><?= esc($event->title) ?></h6>
                                                        <small class="text-muted"><?= esc($event->venue_name) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (isset($event->next_date)): ?>
                                                    <span class="small"><?= date('d/m/Y', strtotime($event->next_date)) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted small">Sem data</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    <?= $event->tickets_sold ?? 0 ?> vendidos
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($event->status === 'published'): ?>
                                                    <span class="badge bg-success">Publicado</span>
                                                <?php elseif ($event->status === 'draft'): ?>
                                                    <span class="badge bg-secondary">Rascunho</span>
                                                <?php elseif ($event->status === 'cancelled'): ?>
                                                    <span class="badge bg-danger">Cancelado</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info"><?= ucfirst($event->status) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                            type="button" data-bs-toggle="dropdown">
                                                        <i class="bi bi-three-dots"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <a class="dropdown-item" href="<?= url_to('events.show', $event->id) ?>">
                                                                <i class="bi bi-eye me-2"></i> Ver
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="<?= url_to('events.edit', $event->id) ?>">
                                                                <i class="bi bi-pencil me-2"></i> Editar
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="<?= url_to('events.seat_map', $event->id) ?>">
                                                                <i class="bi bi-grid-3x3 me-2"></i> Mapa de Assentos
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="#" 
                                                               onclick="cancelEvent(<?= $event->id ?>)">
                                                                <i class="bi bi-x-circle me-2"></i> Cancelar
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                            <p class="mt-3 mb-0 text-muted">Você ainda não criou nenhum evento.</p>
                            <a href="<?= url_to('events.create') ?>" class="btn btn-primary mt-3">
                                <i class="bi bi-plus-lg me-1"></i> Criar Primeiro Evento
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Recent Orders -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Pedidos Recentes</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($recentOrders)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recentOrders as $order): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>#<?= $order->order_number ?></strong><br>
                                        <small class="text-muted"><?= esc($order->customer_name) ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="text-success">R$ <?= number_format($order->total_amount, 2, ',', '.') ?></span><br>
                                        <small class="text-muted"><?= date('d/m H:i', strtotime($order->created_at)) ?></small>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">Nenhum pedido recente</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Ações Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= url_to('events.create') ?>" class="btn btn-outline-primary">
                            <i class="bi bi-plus-lg me-2"></i> Criar Evento
                        </a>
                        <a href="<?= url_to('organizer.stripe_dashboard') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-box-arrow-up-right me-2"></i> Dashboard Stripe
                        </a>
                        <a href="<?= url_to('organizer.account_status') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-person-gear me-2"></i> Configurações
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function cancelEvent(eventId) {
    if (confirm('Tem certeza que deseja cancelar este evento? Esta ação não pode ser desfeita.')) {
        fetch(`/organizer/events/${eventId}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || 'Erro ao cancelar evento');
            }
        });
    }
}
</script>
<?= $this->endSection() ?>
