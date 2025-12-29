<?= $this->extend('layouts/public') ?>

<?= $this->section('title') ?><?= esc($event->title) ?><?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .event-banner {
        position: relative;
        height: 400px;
        background-size: cover;
        background-position: center;
        background-color: #1e293b;
    }
    
    .event-banner::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.3) 100%);
    }
    
    .event-banner-content {
        position: relative;
        z-index: 1;
    }
    
    .sector-card {
        border-left: 4px solid var(--sector-color, #6366f1);
        transition: all 0.3s ease;
    }
    
    .sector-card:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .date-selector {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding-bottom: 10px;
    }
    
    .date-option {
        flex: 0 0 auto;
        min-width: 80px;
        padding: 15px;
        border-radius: 12px;
        text-align: center;
        cursor: pointer;
        border: 2px solid #e2e8f0;
        transition: all 0.3s ease;
        background: white;
    }
    
    .date-option:hover {
        border-color: #6366f1;
    }
    
    .date-option.selected {
        border-color: #6366f1;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
    }
    
    .date-option .day {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
    }
    
    .date-option .month {
        font-size: 0.75rem;
        text-transform: uppercase;
    }
    
    .date-option .weekday {
        font-size: 0.7rem;
        opacity: 0.7;
    }
    
    .sticky-purchase {
        position: sticky;
        top: 80px;
    }
    
    .share-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e2e8f0;
        color: #64748b;
        transition: all 0.2s;
    }
    
    .share-btn:hover {
        background: #f1f5f9;
        color: #1e293b;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Event Banner -->
<div class="event-banner" 
     style="<?= $event->banner ? 'background-image: url(' . base_url('uploads/events/' . esc($event->banner)) . ')' : '' ?>">
    <div class="container h-100 d-flex align-items-end pb-5">
        <div class="event-banner-content text-white">
            <?php if ($event->category): ?>
                <span class="badge bg-primary mb-3"><?= esc(ucfirst($event->category)) ?></span>
            <?php endif; ?>
            <h1 class="display-5 fw-bold mb-2"><?= esc($event->title) ?></h1>
            <p class="lead mb-0">
                <i class="bi bi-geo-alt me-2"></i><?= esc($event->venue) ?>
                <?php if ($event->city): ?> - <?= esc($event->city) ?><?php endif; ?>
            </p>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row g-5">
        <!-- Event Details -->
        <div class="col-lg-8">
            <!-- Dates Section -->
            <?php if (!empty($eventDays)): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="bi bi-calendar3 me-2 text-primary"></i>Escolha a data
                    </h5>
                    
                    <div class="date-selector">
                        <?php foreach ($eventDays as $index => $day): ?>
                            <?php 
                            $date = new DateTime($day->event_date);
                            $months = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
                            $weekdays = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
                            ?>
                            <div class="date-option <?= $index === 0 ? 'selected' : '' ?>" 
                                 data-day-id="<?= $day->id ?>"
                                 onclick="selectDate(this, <?= $day->id ?>)">
                                <div class="weekday"><?= $weekdays[$date->format('w')] ?></div>
                                <div class="day"><?= $date->format('d') ?></div>
                                <div class="month"><?= $months[$date->format('n') - 1] ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (!empty($eventDays[0])): ?>
                        <div id="selectedDateTime" class="mt-3 text-muted">
                            <i class="bi bi-clock me-1"></i>
                            <span id="timeDisplay">
                                <?php if ($eventDays[0]->start_time): ?>
                                    <?= date('H:i', strtotime($eventDays[0]->start_time)) ?>
                                    <?php if ($eventDays[0]->end_time): ?>
                                        - <?= date('H:i', strtotime($eventDays[0]->end_time)) ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Description -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="bi bi-info-circle me-2 text-primary"></i>Sobre o evento
                    </h5>
                    <div class="event-description">
                        <?= nl2br(esc($event->description)) ?>
                    </div>
                </div>
            </div>
            
            <!-- Sectors / Prices -->
            <?php if (!empty($sectors)): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="bi bi-ticket-perforated me-2 text-primary"></i>Setores e preços
                    </h5>
                    
                    <div class="row g-3">
                        <?php foreach ($sectors as $sector): ?>
                            <div class="col-md-6">
                                <div class="card sector-card h-100" 
                                     style="--sector-color: <?= esc($sector->color ?? '#6366f1') ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="fw-bold mb-1"><?= esc($sector->name) ?></h6>
                                                <p class="text-muted small mb-0">
                                                    <?= $sector->available_seats ?> de <?= $sector->total_seats ?> disponíveis
                                                </p>
                                            </div>
                                            <div class="text-end">
                                                <span class="price-tag">
                                                    R$ <?= number_format($sector->price, 2, ',', '.') ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Availability Bar -->
                                        <div class="progress mt-3" style="height: 6px;">
                                            <?php 
                                            $percentage = $sector->total_seats > 0 
                                                ? ($sector->available_seats / $sector->total_seats) * 100 
                                                : 0;
                                            $colorClass = $percentage > 50 ? 'bg-success' : ($percentage > 20 ? 'bg-warning' : 'bg-danger');
                                            ?>
                                            <div class="progress-bar <?= $colorClass ?>" 
                                                 style="width: <?= $percentage ?>%"></div>
                                        </div>
                                        
                                        <?php if ($sector->available_seats <= 10 && $sector->available_seats > 0): ?>
                                            <small class="text-danger mt-2 d-block">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                Últimas unidades!
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Location -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="bi bi-geo-alt me-2 text-primary"></i>Local
                    </h5>
                    
                    <div class="d-flex">
                        <div class="me-4">
                            <div class="bg-light rounded-3 p-3 text-center" style="width: 80px;">
                                <i class="bi bi-building fs-1 text-primary"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1"><?= esc($event->venue) ?></h6>
                            <?php if ($event->address): ?>
                                <p class="text-muted mb-1"><?= esc($event->address) ?></p>
                            <?php endif; ?>
                            <?php if ($event->city): ?>
                                <p class="text-muted mb-0">
                                    <?= esc($event->city) ?>
                                    <?php if ($event->state): ?>, <?= esc($event->state) ?><?php endif; ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($event->address || $event->venue): ?>
                                <a href="https://www.google.com/maps/search/<?= urlencode($event->venue . ' ' . ($event->address ?? '') . ' ' . ($event->city ?? '')) ?>" 
                                   target="_blank" 
                                   class="btn btn-outline-primary btn-sm mt-3">
                                    <i class="bi bi-map me-1"></i>Ver no mapa
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Related Events -->
            <?php if (!empty($relatedEvents)): ?>
            <div class="mt-5">
                <h5 class="fw-bold mb-4">Eventos relacionados</h5>
                <div class="row g-4">
                    <?php foreach ($relatedEvents as $related): ?>
                        <div class="col-md-6 col-lg-3">
                            <a href="<?= base_url('evento/' . esc($related->slug)) ?>" class="text-decoration-none">
                                <div class="card event-card h-100">
                                    <?php if ($related->image): ?>
                                        <img src="<?= base_url('uploads/events/' . esc($related->image)) ?>" 
                                             class="card-img-top" 
                                             alt="<?= esc($related->title) ?>"
                                             style="height: 120px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" 
                                             style="height: 120px;">
                                            <i class="bi bi-calendar-event text-white fs-3"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body p-3">
                                        <h6 class="card-title fw-bold text-dark small"><?= esc($related->title) ?></h6>
                                        <p class="text-primary small mb-0 fw-bold">
                                            R$ <?= number_format($related->base_price, 2, ',', '.') ?>
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Purchase Sidebar -->
        <div class="col-lg-4">
            <div class="sticky-purchase">
                <div class="card border-0 shadow-sm">
                    <?php if ($event->image): ?>
                        <img src="<?= base_url('uploads/events/' . esc($event->image)) ?>" 
                             class="card-img-top" 
                             alt="<?= esc($event->title) ?>"
                             style="height: 180px; object-fit: cover;">
                    <?php endif; ?>
                    
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">A partir de</span>
                            <div class="d-flex gap-2">
                                <button class="share-btn" onclick="shareEvent()" title="Compartilhar">
                                    <i class="bi bi-share"></i>
                                </button>
                                <button class="share-btn" onclick="favoriteEvent()" title="Favoritar">
                                    <i class="bi bi-heart"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <span class="display-6 fw-bold text-primary">
                                <?php if ($event->base_price > 0): ?>
                                    R$ <?= number_format($event->base_price, 2, ',', '.') ?>
                                <?php else: ?>
                                    Gratuito
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <?php if (!empty($eventDays)): ?>
                            <a href="<?= base_url('evento/' . esc($event->slug) . '/assentos/' . $eventDays[0]->id) ?>" 
                               class="btn btn-primary btn-lg w-100 mb-3"
                               id="buyBtn">
                                <i class="bi bi-ticket-perforated me-2"></i>Comprar ingressos
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-lg w-100 mb-3" disabled>
                                Sem datas disponíveis
                            </button>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-center gap-4 text-muted small">
                            <span><i class="bi bi-shield-check me-1"></i>Compra segura</span>
                            <span><i class="bi bi-credit-card me-1"></i>Em até 12x</span>
                        </div>
                    </div>
                </div>
                
                <!-- Event Info Summary -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-body p-4">
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex mb-3">
                                <i class="bi bi-calendar3 text-primary me-3 fs-5"></i>
                                <div>
                                    <small class="text-muted d-block">Data</small>
                                    <span id="selectedDateText">
                                        <?php if (!empty($eventDays[0])): ?>
                                            <?= date('d/m/Y', strtotime($eventDays[0]->event_date)) ?>
                                        <?php else: ?>
                                            A definir
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </li>
                            <li class="d-flex mb-3">
                                <i class="bi bi-clock text-primary me-3 fs-5"></i>
                                <div>
                                    <small class="text-muted d-block">Horário</small>
                                    <span>
                                        <?php if (!empty($eventDays[0]) && $eventDays[0]->start_time): ?>
                                            <?= date('H:i', strtotime($eventDays[0]->start_time)) ?>
                                        <?php else: ?>
                                            A definir
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </li>
                            <li class="d-flex">
                                <i class="bi bi-geo-alt text-primary me-3 fs-5"></i>
                                <div>
                                    <small class="text-muted d-block">Local</small>
                                    <span><?= esc($event->venue) ?></span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden data for JS -->
<script>
    const eventSlug = '<?= esc($event->slug) ?>';
    const eventDays = <?= json_encode(array_map(function($day) {
        return [
            'id' => $day->id,
            'date' => $day->event_date,
            'start_time' => $day->start_time,
            'end_time' => $day->end_time
        ];
    }, $eventDays ?? [])) ?>;
</script>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function selectDate(element, dayId) {
    // Remove selected from all
    document.querySelectorAll('.date-option').forEach(el => el.classList.remove('selected'));
    
    // Add selected to clicked
    element.classList.add('selected');
    
    // Update buy button link
    const buyBtn = document.getElementById('buyBtn');
    if (buyBtn) {
        buyBtn.href = `<?= base_url('evento/' . esc($event->slug) . '/assentos/') ?>${dayId}`;
    }
    
    // Find and display the selected date info
    const dayData = eventDays.find(d => d.id == dayId);
    if (dayData) {
        const date = new Date(dayData.date + 'T00:00:00');
        document.getElementById('selectedDateText').textContent = date.toLocaleDateString('pt-BR');
        
        if (dayData.start_time) {
            let timeText = dayData.start_time.substring(0, 5);
            if (dayData.end_time) {
                timeText += ' - ' + dayData.end_time.substring(0, 5);
            }
            document.getElementById('timeDisplay').textContent = timeText;
        }
    }
}

function shareEvent() {
    if (navigator.share) {
        navigator.share({
            title: '<?= esc($event->title) ?>',
            text: 'Confira este evento: <?= esc($event->title) ?>',
            url: window.location.href
        });
    } else {
        navigator.clipboard.writeText(window.location.href);
        alert('Link copiado para a área de transferência!');
    }
}

function favoriteEvent() {
    // TODO: Implement favorite functionality
    alert('Funcionalidade em desenvolvimento');
}
</script>
<?= $this->endSection() ?>
