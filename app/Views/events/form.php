<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= $isEdit ? 'Editar' : 'Criar' ?> Evento<?= $this->endSection() ?>

<?= $this->section('styles') ?>
.sector-card {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    background: #fafafa;
}
.queue-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 10px;
    margin-top: 10px;
}
.remove-btn {
    cursor: pointer;
    color: #dc3545;
}
.remove-btn:hover {
    color: #b02a37;
}
.color-preview {
    width: 30px;
    height: 30px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
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
                    <li class="breadcrumb-item active"><?= $isEdit ? 'Editar' : 'Novo Evento' ?></li>
                </ol>
            </nav>
            <h1 class="h3 mb-0"><?= $isEdit ? 'Editar Evento' : 'Criar Novo Evento' ?></h1>
        </div>
    </div>

    <?php if (session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= session('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="<?= $isEdit ? base_url('organizer/events/' . $event->id . '/update') : base_url('organizer/events/store') ?>" 
          method="post" 
          enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Informações Básicas -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informações Básicas</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Título do Evento *</label>
                            <input type="text" class="form-control" name="title" 
                                   value="<?= old('title', $event->title ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descrição</label>
                            <textarea class="form-control" name="description" rows="4"><?= old('description', $event->description ?? '') ?></textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Categoria *</label>
                                <select class="form-select" name="category" required>
                                    <option value="">Selecione...</option>
                                    <?php
                                    $categories = ['show', 'teatro', 'esporte', 'festival', 'conferencia', 'workshop', 'outros'];
                                    $currentCategory = old('category', $event->category ?? '');
                                    foreach ($categories as $cat):
                                    ?>
                                        <option value="<?= $cat ?>" <?= $currentCategory === $cat ? 'selected' : '' ?>>
                                            <?= ucfirst($cat) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Máx. Ingressos por Compra</label>
                                <input type="number" class="form-control" name="max_tickets_per_purchase" 
                                       value="<?= old('max_tickets_per_purchase', $event->max_tickets_per_purchase ?? 10) ?>" 
                                       min="1" max="20">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Local do Evento -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Local do Evento</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Nome do Local *</label>
                            <input type="text" class="form-control" name="venue_name" 
                                   value="<?= old('venue_name', $event->venue_name ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Endereço *</label>
                            <input type="text" class="form-control" name="venue_address" 
                                   value="<?= old('venue_address', $event->venue_address ?? '') ?>" required>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label">Cidade *</label>
                                <input type="text" class="form-control" name="venue_city" 
                                       value="<?= old('venue_city', $event->venue_city ?? '') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estado *</label>
                                <select class="form-select" name="venue_state" required>
                                    <option value="">Selecione...</option>
                                    <?php
                                    $states = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
                                    $currentState = old('venue_state', $event->venue_state ?? '');
                                    foreach ($states as $state):
                                    ?>
                                        <option value="<?= $state ?>" <?= $currentState === $state ? 'selected' : '' ?>><?= $state ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">CEP *</label>
                                <input type="text" class="form-control" name="venue_zip_code" 
                                       value="<?= old('venue_zip_code', $event->venue_zip_code ?? '') ?>" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Datas do Evento -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Datas do Evento</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addDate">
                            <i class="bi bi-plus-lg"></i> Adicionar Data
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="datesContainer">
                            <?php if (!empty($days)): ?>
                                <?php foreach ($days as $index => $day): ?>
                                    <div class="row g-3 mb-3 date-row">
                                        <div class="col-md-4">
                                            <label class="form-label">Data *</label>
                                            <input type="date" class="form-control" name="event_dates[]" 
                                                   value="<?= $day->date ?>" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Horário *</label>
                                            <input type="time" class="form-control" name="event_times[]" 
                                                   value="<?= $day->start_time ?>" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Abertura Portões</label>
                                            <input type="time" class="form-control" name="doors_open[]" 
                                                   value="<?= $day->doors_open ?>">
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-outline-danger remove-date">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="row g-3 mb-3 date-row">
                                    <div class="col-md-4">
                                        <label class="form-label">Data *</label>
                                        <input type="date" class="form-control" name="event_dates[]" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Horário *</label>
                                        <input type="time" class="form-control" name="event_times[]" value="19:00" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Abertura Portões</label>
                                        <input type="time" class="form-control" name="doors_open[]" value="18:00">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-outline-danger remove-date">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Setores e Assentos -->
                <?php if (!$isEdit): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-grid-3x3 me-2"></i>Setores e Assentos</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addSector">
                            <i class="bi bi-plus-lg"></i> Adicionar Setor
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="sectorsContainer">
                            <div class="sector-card" data-sector-index="0">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">Setor 1</h6>
                                    <span class="remove-btn remove-sector"><i class="bi bi-x-lg"></i></span>
                                </div>
                                
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Nome do Setor *</label>
                                        <input type="text" class="form-control" name="sectors[0][name]" placeholder="Ex: Pista" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Preço (R$) *</label>
                                        <input type="number" class="form-control" name="sectors[0][price]" step="0.01" min="0" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Cor</label>
                                        <input type="color" class="form-control form-control-color" name="sectors[0][color]" value="#3498db">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Numerado?</label>
                                        <select class="form-select sector-numbered" name="sectors[0][is_numbered]">
                                            <option value="1">Sim</option>
                                            <option value="0">Não</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Filas (para setores numerados) -->
                                <div class="queues-container mt-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong>Filas</strong>
                                        <button type="button" class="btn btn-sm btn-outline-secondary add-queue">
                                            <i class="bi bi-plus"></i> Fila
                                        </button>
                                    </div>
                                    <div class="queues-list">
                                        <div class="queue-card">
                                            <div class="row g-2 align-items-center">
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control form-control-sm" 
                                                           name="sectors[0][queues][0][name]" placeholder="Nome (ex: A)" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="number" class="form-control form-control-sm" 
                                                           name="sectors[0][queues][0][total_seats]" placeholder="Nº assentos" min="1" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <span class="remove-btn remove-queue"><i class="bi bi-x"></i> Remover</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Capacidade (para setores não numerados) -->
                                <div class="capacity-container mt-3" style="display: none;">
                                    <label class="form-label">Capacidade Total</label>
                                    <input type="number" class="form-control" name="sectors[0][capacity]" min="1" placeholder="Ex: 500">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Imagem -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-image me-2"></i>Imagem</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($isEdit && $event->image): ?>
                            <img src="<?= $event->getImageUrl() ?>" class="img-fluid rounded mb-3" alt="Imagem atual">
                        <?php endif; ?>
                        <input type="file" class="form-control" name="image" accept="image/*">
                        <small class="text-muted">Recomendado: 800x600px, JPG ou PNG</small>
                    </div>
                </div>

                <!-- Banner -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-card-image me-2"></i>Banner</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($isEdit && $event->banner): ?>
                            <img src="<?= $event->getBannerUrl() ?>" class="img-fluid rounded mb-3" alt="Banner atual">
                        <?php endif; ?>
                        <input type="file" class="form-control" name="banner" accept="image/*">
                        <small class="text-muted">Recomendado: 1920x400px, JPG ou PNG</small>
                    </div>
                </div>

                <!-- Ações -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-lg me-2"></i><?= $isEdit ? 'Salvar Alterações' : 'Criar Evento' ?>
                            </button>
                            <a href="<?= base_url('organizer/events') ?>" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let sectorIndex = 1;

// Adicionar data
document.getElementById('addDate')?.addEventListener('click', function() {
    const container = document.getElementById('datesContainer');
    const row = document.createElement('div');
    row.className = 'row g-3 mb-3 date-row';
    row.innerHTML = `
        <div class="col-md-4">
            <label class="form-label">Data *</label>
            <input type="date" class="form-control" name="event_dates[]" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Horário *</label>
            <input type="time" class="form-control" name="event_times[]" value="19:00" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Abertura Portões</label>
            <input type="time" class="form-control" name="doors_open[]" value="18:00">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="button" class="btn btn-outline-danger remove-date">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(row);
});

// Remover data
document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-date')) {
        const rows = document.querySelectorAll('.date-row');
        if (rows.length > 1) {
            e.target.closest('.date-row').remove();
        } else {
            alert('O evento precisa ter pelo menos uma data.');
        }
    }
});

// Adicionar setor
document.getElementById('addSector')?.addEventListener('click', function() {
    const container = document.getElementById('sectorsContainer');
    const sectorHtml = `
        <div class="sector-card" data-sector-index="${sectorIndex}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Setor ${sectorIndex + 1}</h6>
                <span class="remove-btn remove-sector"><i class="bi bi-x-lg"></i></span>
            </div>
            
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Nome do Setor *</label>
                    <input type="text" class="form-control" name="sectors[${sectorIndex}][name]" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Preço (R$) *</label>
                    <input type="number" class="form-control" name="sectors[${sectorIndex}][price]" step="0.01" min="0" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cor</label>
                    <input type="color" class="form-control form-control-color" name="sectors[${sectorIndex}][color]" value="#3498db">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Numerado?</label>
                    <select class="form-select sector-numbered" name="sectors[${sectorIndex}][is_numbered]">
                        <option value="1">Sim</option>
                        <option value="0">Não</option>
                    </select>
                </div>
            </div>

            <div class="queues-container mt-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>Filas</strong>
                    <button type="button" class="btn btn-sm btn-outline-secondary add-queue">
                        <i class="bi bi-plus"></i> Fila
                    </button>
                </div>
                <div class="queues-list">
                    <div class="queue-card">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-4">
                                <input type="text" class="form-control form-control-sm" 
                                       name="sectors[${sectorIndex}][queues][0][name]" placeholder="Nome (ex: A)" required>
                            </div>
                            <div class="col-md-4">
                                <input type="number" class="form-control form-control-sm" 
                                       name="sectors[${sectorIndex}][queues][0][total_seats]" placeholder="Nº assentos" min="1" required>
                            </div>
                            <div class="col-md-4">
                                <span class="remove-btn remove-queue"><i class="bi bi-x"></i> Remover</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="capacity-container mt-3" style="display: none;">
                <label class="form-label">Capacidade Total</label>
                <input type="number" class="form-control" name="sectors[${sectorIndex}][capacity]" min="1" placeholder="Ex: 500">
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', sectorHtml);
    sectorIndex++;
});

// Remover setor
document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-sector')) {
        const sectors = document.querySelectorAll('.sector-card');
        if (sectors.length > 1) {
            e.target.closest('.sector-card').remove();
        } else {
            alert('O evento precisa ter pelo menos um setor.');
        }
    }
});

// Toggle numerado/não numerado
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('sector-numbered')) {
        const sectorCard = e.target.closest('.sector-card');
        const queuesContainer = sectorCard.querySelector('.queues-container');
        const capacityContainer = sectorCard.querySelector('.capacity-container');
        
        if (e.target.value === '1') {
            queuesContainer.style.display = 'block';
            capacityContainer.style.display = 'none';
        } else {
            queuesContainer.style.display = 'none';
            capacityContainer.style.display = 'block';
        }
    }
});

// Adicionar fila
document.addEventListener('click', function(e) {
    if (e.target.closest('.add-queue')) {
        const sectorCard = e.target.closest('.sector-card');
        const sectorIdx = sectorCard.dataset.sectorIndex;
        const queuesList = sectorCard.querySelector('.queues-list');
        const queueCount = queuesList.querySelectorAll('.queue-card').length;
        
        const queueHtml = `
            <div class="queue-card">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm" 
                               name="sectors[${sectorIdx}][queues][${queueCount}][name]" placeholder="Nome (ex: A)" required>
                    </div>
                    <div class="col-md-4">
                        <input type="number" class="form-control form-control-sm" 
                               name="sectors[${sectorIdx}][queues][${queueCount}][total_seats]" placeholder="Nº assentos" min="1" required>
                    </div>
                    <div class="col-md-4">
                        <span class="remove-btn remove-queue"><i class="bi bi-x"></i> Remover</span>
                    </div>
                </div>
            </div>
        `;
        queuesList.insertAdjacentHTML('beforeend', queueHtml);
    }
});

// Remover fila
document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-queue')) {
        const queuesContainer = e.target.closest('.queues-list');
        const queues = queuesContainer.querySelectorAll('.queue-card');
        if (queues.length > 1) {
            e.target.closest('.queue-card').remove();
        } else {
            alert('O setor precisa ter pelo menos uma fila.');
        }
    }
});
</script>
<?= $this->endSection() ?>
