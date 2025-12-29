<?= $this->extend('layouts/public') ?>

<?= $this->section('title') ?>Eventos<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold mb-4">
                        <i class="bi bi-funnel me-2"></i>Filtros
                    </h5>
                    
                    <form action="<?= base_url('eventos') ?>" method="get" id="filterForm">
                        <!-- Busca -->
                        <div class="mb-4">
                            <label class="form-label fw-medium">Buscar</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                <input type="text" 
                                       name="busca" 
                                       class="form-control" 
                                       placeholder="Nome, artista, local..."
                                       value="<?= esc($filters['busca'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <!-- Categoria -->
                        <div class="mb-4">
                            <label class="form-label fw-medium">Categoria</label>
                            <select name="categoria" class="form-select">
                                <option value="">Todas as categorias</option>
                                <option value="show" <?= ($filters['categoria'] ?? '') === 'show' ? 'selected' : '' ?>>Shows</option>
                                <option value="teatro" <?= ($filters['categoria'] ?? '') === 'teatro' ? 'selected' : '' ?>>Teatro</option>
                                <option value="esporte" <?= ($filters['categoria'] ?? '') === 'esporte' ? 'selected' : '' ?>>Esportes</option>
                                <option value="festival" <?= ($filters['categoria'] ?? '') === 'festival' ? 'selected' : '' ?>>Festivais</option>
                                <option value="conferencia" <?= ($filters['categoria'] ?? '') === 'conferencia' ? 'selected' : '' ?>>Conferências</option>
                            </select>
                        </div>
                        
                        <!-- Cidade -->
                        <?php if (!empty($cities)): ?>
                        <div class="mb-4">
                            <label class="form-label fw-medium">Cidade</label>
                            <select name="cidade" class="form-select">
                                <option value="">Todas as cidades</option>
                                <?php foreach ($cities as $city): ?>
                                    <option value="<?= esc($city) ?>" <?= ($filters['cidade'] ?? '') === $city ? 'selected' : '' ?>>
                                        <?= esc($city) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Ordenação -->
                        <div class="mb-4">
                            <label class="form-label fw-medium">Ordenar por</label>
                            <select name="ordem" class="form-select">
                                <option value="recentes" <?= ($filters['ordem'] ?? '') === 'recentes' ? 'selected' : '' ?>>Mais recentes</option>
                                <option value="preco_menor" <?= ($filters['ordem'] ?? '') === 'preco_menor' ? 'selected' : '' ?>>Menor preço</option>
                                <option value="preco_maior" <?= ($filters['ordem'] ?? '') === 'preco_maior' ? 'selected' : '' ?>>Maior preço</option>
                                <option value="nome" <?= ($filters['ordem'] ?? '') === 'nome' ? 'selected' : '' ?>>Nome A-Z</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-2"></i>Aplicar Filtros
                        </button>
                        
                        <?php if (!empty(array_filter($filters))): ?>
                            <a href="<?= base_url('eventos') ?>" class="btn btn-outline-secondary w-100 mt-2">
                                <i class="bi bi-x-circle me-2"></i>Limpar Filtros
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Events Grid -->
        <div class="col-lg-9">
            <!-- Results Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h4 fw-bold mb-1">Eventos</h1>
                    <p class="text-muted mb-0">
                        <?php 
                        $totalResults = count($events);
                        echo $totalResults . ' ' . ($totalResults === 1 ? 'evento encontrado' : 'eventos encontrados');
                        ?>
                    </p>
                </div>
                
                <!-- View Toggle -->
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-secondary active" id="gridViewBtn">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="listViewBtn">
                        <i class="bi bi-list"></i>
                    </button>
                </div>
            </div>
            
            <!-- Active Filters -->
            <?php if (!empty(array_filter($filters))): ?>
                <div class="mb-4">
                    <?php if (!empty($filters['busca'])): ?>
                        <span class="badge bg-primary me-1">
                            Busca: <?= esc($filters['busca']) ?>
                            <a href="<?= base_url('eventos?' . http_build_query(array_diff_key($filters, ['busca' => '']))) ?>" class="text-white ms-1">×</a>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($filters['categoria'])): ?>
                        <span class="badge bg-primary me-1">
                            <?= esc(ucfirst($filters['categoria'])) ?>
                            <a href="<?= base_url('eventos?' . http_build_query(array_diff_key($filters, ['categoria' => '']))) ?>" class="text-white ms-1">×</a>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($filters['cidade'])): ?>
                        <span class="badge bg-primary me-1">
                            <?= esc($filters['cidade']) ?>
                            <a href="<?= base_url('eventos?' . http_build_query(array_diff_key($filters, ['cidade' => '']))) ?>" class="text-white ms-1">×</a>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Events -->
            <?php if (empty($events)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x display-1 text-muted"></i>
                    <h3 class="mt-3">Nenhum evento encontrado</h3>
                    <p class="text-muted">Tente ajustar os filtros ou fazer uma nova busca.</p>
                    <a href="<?= base_url('eventos') ?>" class="btn btn-primary">Ver todos os eventos</a>
                </div>
            <?php else: ?>
                <div class="row g-4" id="eventsGrid">
                    <?php foreach ($events as $event): ?>
                        <div class="col-md-6 col-xl-4 event-item">
                            <a href="<?= base_url('evento/' . esc($event->slug)) ?>" class="text-decoration-none">
                                <div class="card event-card h-100">
                                    <div class="position-relative">
                                        <?php if ($event->image): ?>
                                            <img src="<?= base_url('uploads/events/' . esc($event->image)) ?>" 
                                                 class="card-img-top" 
                                                 alt="<?= esc($event->title) ?>">
                                        <?php else: ?>
                                            <div class="card-img-top bg-gradient d-flex align-items-center justify-content-center" 
                                                 style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                <i class="bi bi-calendar-event text-white display-4"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($event->category): ?>
                                            <span class="category-badge"><?= esc(ucfirst($event->category)) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="card-body">
                                        <h5 class="card-title fw-bold text-dark"><?= esc($event->title) ?></h5>
                                        
                                        <div class="d-flex align-items-center text-muted small mb-2">
                                            <i class="bi bi-geo-alt me-1"></i>
                                            <span><?= esc($event->venue) ?></span>
                                            <?php if ($event->city): ?>
                                                <span class="mx-1">•</span>
                                                <span><?= esc($event->city) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if ($event->description): ?>
                                            <p class="card-text text-muted small mb-3">
                                                <?= esc(substr($event->description, 0, 100)) ?>...
                                            </p>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="price-tag">
                                                <?php if ($event->base_price > 0): ?>
                                                    R$ <?= number_format($event->base_price, 2, ',', '.') ?>
                                                <?php else: ?>
                                                    <span class="text-success">Gratuito</span>
                                                <?php endif; ?>
                                            </span>
                                            <span class="btn btn-sm btn-primary">
                                                Ver detalhes
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($pager): ?>
                    <div class="d-flex justify-content-center mt-5">
                        <?= $pager->links('events', 'default_full') ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// View Toggle
document.getElementById('gridViewBtn')?.addEventListener('click', function() {
    this.classList.add('active');
    document.getElementById('listViewBtn').classList.remove('active');
    document.querySelectorAll('.event-item').forEach(el => {
        el.className = 'col-md-6 col-xl-4 event-item';
    });
});

document.getElementById('listViewBtn')?.addEventListener('click', function() {
    this.classList.add('active');
    document.getElementById('gridViewBtn').classList.remove('active');
    document.querySelectorAll('.event-item').forEach(el => {
        el.className = 'col-12 event-item';
    });
});
</script>
<?= $this->endSection() ?>
