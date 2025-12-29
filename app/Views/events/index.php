<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Meus Eventos<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Meus Eventos</h1>
            <p class="text-muted mb-0">Gerencie seus eventos e acompanhe as vendas</p>
        </div>
        <a href="<?= base_url('organizer/events/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Novo Evento
        </a>
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

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card card-stat">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                            <i class="bi bi-file-earmark-text text-primary fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Rascunhos</h6>
                            <h3 class="mb-0"><?= $eventCounts['draft'] ?? 0 ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stat">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                            <i class="bi bi-calendar-check text-success fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Publicados</h6>
                            <h3 class="mb-0"><?= $eventCounts['published'] ?? 0 ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stat">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-secondary bg-opacity-10 p-3 me-3">
                            <i class="bi bi-calendar-x text-secondary fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Finalizados</h6>
                            <h3 class="mb-0"><?= $eventCounts['finished'] ?? 0 ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stat">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                            <i class="bi bi-x-circle text-danger fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Cancelados</h6>
                            <h3 class="mb-0"><?= $eventCounts['cancelled'] ?? 0 ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Events List -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">Lista de Eventos</h5>
                </div>
                <div class="col-auto">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" placeholder="Buscar evento..." id="searchEvent">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($events)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-calendar-event text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3">Nenhum evento cadastrado</h5>
                    <p class="text-muted">Comece criando seu primeiro evento</p>
                    <a href="<?= base_url('organizer/events/create') ?>" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Criar Evento
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Evento</th>
                                <th>Local</th>
                                <th>Categoria</th>
                                <th>Status</th>
                                <th>Criado em</th>
                                <th width="120">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?= $event->getImageUrl() ?>" 
                                                 class="rounded me-3" 
                                                 style="width: 50px; height: 50px; object-fit: cover;"
                                                 alt="<?= esc($event->title) ?>">
                                            <div>
                                                <h6 class="mb-0"><?= esc($event->title) ?></h6>
                                                <small class="text-muted"><?= esc($event->slug) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <small><?= esc($event->venue_name) ?></small><br>
                                        <small class="text-muted"><?= esc($event->venue_city) ?>/<?= esc($event->venue_state) ?></small>
                                    </td>
                                    <td><?= esc($event->category) ?></td>
                                    <td>
                                        <?php
                                        $statusClasses = [
                                            'draft'     => 'bg-secondary',
                                            'published' => 'bg-success',
                                            'cancelled' => 'bg-danger',
                                            'finished'  => 'bg-dark',
                                        ];
                                        ?>
                                        <span class="badge <?= $statusClasses[$event->status] ?? 'bg-secondary' ?>">
                                            <?= $event->getStatusLabel() ?>
                                        </span>
                                    </td>
                                    <td><?= $event->created_at->toLocalizedString('dd/MM/yyyy') ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?= base_url('organizer/events/' . $event->id) ?>" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="Ver detalhes">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if ($event->canEdit()): ?>
                                                <a href="<?= base_url('organizer/events/' . $event->id . '/edit') ?>" 
                                                   class="btn btn-sm btn-outline-secondary"
                                                   title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="<?= base_url('organizer/events/' . $event->id . '/seat-map') ?>" 
                                               class="btn btn-sm btn-outline-info"
                                               title="Mapa de assentos">
                                                <i class="bi bi-grid-3x3"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.getElementById('searchEvent')?.addEventListener('input', function(e) {
    const search = e.target.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(search) ? '' : 'none';
    });
});
</script>
<?= $this->endSection() ?>
