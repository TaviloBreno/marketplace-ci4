<?= $this->extend('layouts/public') ?>

<?= $this->section('title') ?>Encontre os melhores eventos<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Encontre experiências incríveis</h1>
                <p class="lead mb-4">Shows, teatros, esportes e muito mais. Compre seus ingressos de forma rápida e segura.</p>
                
                <form action="<?= base_url('eventos') ?>" method="get" class="d-flex gap-2">
                    <div class="search-box flex-grow-1 d-flex align-items-center">
                        <i class="bi bi-search me-2"></i>
                        <input type="text" name="busca" placeholder="Buscar eventos, artistas, locais..." class="flex-grow-1">
                    </div>
                    <button type="submit" class="btn btn-primary px-4">
                        Buscar
                    </button>
                </form>
            </div>
            <div class="col-lg-6 text-center d-none d-lg-block">
                <img src="https://undraw.co/api/illustrations/undraw_party_re_nmwj" alt="Events" style="max-height: 350px;" onerror="this.src='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/calendar-event.svg'; this.style.maxHeight='200px';">
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5">
    <div class="container">
        <h2 class="h4 fw-bold mb-4">Navegue por categoria</h2>
        <div class="row g-3">
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?= base_url('eventos?categoria=show') ?>" class="text-decoration-none">
                    <div class="card text-center p-4 h-100 event-card">
                        <i class="bi bi-music-note-beamed fs-1 text-primary mb-2"></i>
                        <span class="fw-medium text-dark">Shows</span>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?= base_url('eventos?categoria=teatro') ?>" class="text-decoration-none">
                    <div class="card text-center p-4 h-100 event-card">
                        <i class="bi bi-mask fs-1 text-primary mb-2"></i>
                        <span class="fw-medium text-dark">Teatro</span>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?= base_url('eventos?categoria=esporte') ?>" class="text-decoration-none">
                    <div class="card text-center p-4 h-100 event-card">
                        <i class="bi bi-trophy fs-1 text-primary mb-2"></i>
                        <span class="fw-medium text-dark">Esportes</span>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?= base_url('eventos?categoria=festival') ?>" class="text-decoration-none">
                    <div class="card text-center p-4 h-100 event-card">
                        <i class="bi bi-stars fs-1 text-primary mb-2"></i>
                        <span class="fw-medium text-dark">Festivais</span>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?= base_url('eventos?categoria=conferencia') ?>" class="text-decoration-none">
                    <div class="card text-center p-4 h-100 event-card">
                        <i class="bi bi-people fs-1 text-primary mb-2"></i>
                        <span class="fw-medium text-dark">Conferências</span>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?= base_url('eventos') ?>" class="text-decoration-none">
                    <div class="card text-center p-4 h-100 event-card">
                        <i class="bi bi-grid fs-1 text-primary mb-2"></i>
                        <span class="fw-medium text-dark">Ver Todos</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Featured Events -->
<?php if (!empty($featuredEvents)): ?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 fw-bold mb-0">Eventos em destaque</h2>
            <a href="<?= base_url('eventos') ?>" class="text-primary text-decoration-none">
                Ver todos <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        
        <div class="row g-4">
            <?php foreach ($featuredEvents as $event): ?>
                <div class="col-md-6 col-lg-4">
                    <a href="<?= base_url('evento/' . esc($event->slug)) ?>" class="text-decoration-none">
                        <div class="card event-card h-100">
                            <div class="position-relative">
                                <?php if ($event->image): ?>
                                    <img src="<?= base_url('uploads/events/' . esc($event->image)) ?>" class="card-img-top" alt="<?= esc($event->title) ?>">
                                <?php else: ?>
                                    <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="bi bi-calendar-event text-white fs-1"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($event->category): ?>
                                    <span class="category-badge"><?= esc(ucfirst($event->category)) ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body">
                                <h5 class="card-title fw-bold text-dark"><?= esc($event->title) ?></h5>
                                <p class="text-muted small mb-2">
                                    <i class="bi bi-geo-alt me-1"></i><?= esc($event->venue) ?>
                                    <?php if ($event->city): ?>
                                        - <?= esc($event->city) ?>
                                    <?php endif; ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="price-tag">
                                        <?php if ($event->base_price > 0): ?>
                                            A partir de R$ <?= number_format($event->base_price, 2, ',', '.') ?>
                                        <?php else: ?>
                                            Gratuito
                                        <?php endif; ?>
                                    </span>
                                    <span class="btn btn-sm btn-primary">
                                        Comprar
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Upcoming Events -->
<?php if (!empty($upcomingEvents)): ?>
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 fw-bold mb-0">Próximos eventos</h2>
            <a href="<?= base_url('eventos?ordem=data') ?>" class="text-primary text-decoration-none">
                Ver todos <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        
        <div class="row g-4">
            <?php foreach ($upcomingEvents as $event): ?>
                <div class="col-md-6 col-lg-3">
                    <a href="<?= base_url('evento/' . esc($event->slug)) ?>" class="text-decoration-none">
                        <div class="card event-card h-100">
                            <div class="position-relative">
                                <?php if ($event->image): ?>
                                    <img src="<?= base_url('uploads/events/' . esc($event->image)) ?>" class="card-img-top" alt="<?= esc($event->title) ?>" style="height: 150px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 150px;">
                                        <i class="bi bi-calendar-event text-white fs-1"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body p-3">
                                <h6 class="card-title fw-bold text-dark mb-1" style="font-size: 0.9rem;"><?= esc($event->title) ?></h6>
                                <p class="text-muted small mb-0">
                                    <i class="bi bi-geo-alt me-1"></i><?= esc($event->city) ?>
                                </p>
                                <p class="price-tag small mb-0 mt-2">
                                    <?php if ($event->base_price > 0): ?>
                                        R$ <?= number_format($event->base_price, 2, ',', '.') ?>
                                    <?php else: ?>
                                        Gratuito
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<section class="py-5" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);">
    <div class="container text-center text-white">
        <h2 class="fw-bold mb-3">Você organiza eventos?</h2>
        <p class="lead mb-4">Crie sua conta de organizador e comece a vender ingressos hoje mesmo!</p>
        <a href="<?= base_url('organizer/become') ?>" class="btn btn-light btn-lg px-5">
            Quero ser organizador <i class="bi bi-arrow-right ms-2"></i>
        </a>
    </div>
</section>

<?= $this->endSection() ?>
