<?= $this->extend('layouts/public') ?>

<?= $this->section('title') ?>Selecionar Assentos - <?= esc($event->title) ?><?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .seat-map-container {
        background: #1e293b;
        border-radius: 16px;
        padding: 30px;
        min-height: 500px;
        overflow: auto;
    }
    
    .stage {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 40px;
        border-radius: 8px;
        text-align: center;
        margin-bottom: 30px;
        font-weight: 600;
    }
    
    .sector-container {
        margin-bottom: 30px;
    }
    
    .sector-label {
        color: white;
        font-weight: 600;
        margin-bottom: 15px;
        padding: 8px 15px;
        border-radius: 8px;
        display: inline-block;
    }
    
    .queue-row {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
        gap: 8px;
    }
    
    .queue-label {
        color: #94a3b8;
        width: 30px;
        text-align: center;
        font-size: 0.8rem;
    }
    
    .seats-row {
        display: flex;
        gap: 6px;
        justify-content: center;
    }
    
    .seat {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px solid transparent;
    }
    
    .seat.available {
        background: #22c55e;
        color: white;
    }
    
    .seat.available:hover {
        transform: scale(1.1);
        box-shadow: 0 0 10px rgba(34, 197, 94, 0.5);
    }
    
    .seat.occupied {
        background: #475569;
        color: #94a3b8;
        cursor: not-allowed;
    }
    
    .seat.selected {
        background: #eab308;
        color: #1e293b;
        border-color: white;
        transform: scale(1.1);
    }
    
    .seat.reserved {
        background: #ef4444;
        color: white;
        cursor: not-allowed;
    }
    
    .legend {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 20px;
        flex-wrap: wrap;
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: white;
        font-size: 0.85rem;
    }
    
    .legend-color {
        width: 24px;
        height: 24px;
        border-radius: 4px;
    }
    
    .selection-summary {
        position: sticky;
        top: 80px;
    }
    
    .selected-seat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px;
        background: #f1f5f9;
        border-radius: 8px;
        margin-bottom: 8px;
    }
    
    .selected-seat-item .remove-btn {
        background: none;
        border: none;
        color: #ef4444;
        cursor: pointer;
        padding: 0;
    }
    
    .timer-badge {
        background: #fef3c7;
        color: #92400e;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 500;
    }
    
    .sector-price-card {
        background: #f8fafc;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 10px;
        border-left: 4px solid var(--sector-color, #6366f1);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('eventos') ?>">Eventos</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('evento/' . esc($event->slug)) ?>"><?= esc($event->title) ?></a></li>
                    <li class="breadcrumb-item active">Selecionar Assentos</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 fw-bold mb-1"><?= esc($event->title) ?></h1>
                    <p class="text-muted mb-0">
                        <i class="bi bi-calendar3 me-2"></i>
                        <?= date('d/m/Y', strtotime($eventDay->event_date)) ?>
                        <?php if ($eventDay->start_time): ?>
                            às <?= date('H:i', strtotime($eventDay->start_time)) ?>
                        <?php endif; ?>
                    </p>
                </div>
                
                <div class="timer-badge" id="reservationTimer" style="display: none;">
                    <i class="bi bi-clock me-1"></i>
                    Tempo restante: <span id="timerDisplay">10:00</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Seat Map -->
        <div class="col-lg-8">
            <!-- Sector Prices -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-3">Setores disponíveis</h6>
                    <div class="row g-2">
                        <?php foreach ($sectors as $sector): ?>
                            <div class="col-md-4">
                                <div class="sector-price-card" style="--sector-color: <?= esc($sector->color ?? '#6366f1') ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-medium"><?= esc($sector->name) ?></span>
                                        <span class="fw-bold text-primary">R$ <?= number_format($sector->price, 2, ',', '.') ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="seat-map-container">
                <!-- Stage -->
                <div class="stage">
                    <i class="bi bi-mic-fill me-2"></i>PALCO
                </div>
                
                <!-- Sectors -->
                <?php foreach ($layout as $sectorData): ?>
                    <div class="sector-container" data-sector-id="<?= $sectorData['sector']->id ?>">
                        <div class="sector-label" style="background: <?= esc($sectorData['sector']->color ?? '#6366f1') ?>">
                            <?= esc($sectorData['sector']->name) ?> - R$ <?= number_format($sectorData['sector']->price, 2, ',', '.') ?>
                        </div>
                        
                        <?php foreach ($sectorData['queues'] as $queueData): ?>
                            <div class="queue-row">
                                <span class="queue-label"><?= esc($queueData['queue']->name) ?></span>
                                <div class="seats-row">
                                    <?php foreach ($queueData['seats'] as $seat): ?>
                                        <div class="seat <?= $seat->is_available ? 'available' : 'occupied' ?>"
                                             data-seat-id="<?= $seat->id ?>"
                                             data-seat-code="<?= esc($seat->code) ?>"
                                             data-sector-id="<?= $sectorData['sector']->id ?>"
                                             data-sector-name="<?= esc($sectorData['sector']->name) ?>"
                                             data-queue-name="<?= esc($queueData['queue']->name) ?>"
                                             data-price="<?= $sectorData['sector']->price ?>"
                                             <?= $seat->is_available ? 'onclick="toggleSeat(this)"' : '' ?>
                                             title="<?= $seat->is_available ? esc($seat->code) : 'Ocupado' ?>">
                                            <?= esc($seat->code) ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <span class="queue-label"><?= esc($queueData['queue']->name) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
                
                <!-- Legend -->
                <div class="legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background: #22c55e;"></div>
                        <span>Disponível</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #eab308;"></div>
                        <span>Selecionado</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #475569;"></div>
                        <span>Ocupado</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Selection Summary -->
        <div class="col-lg-4">
            <div class="selection-summary">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-cart3 me-2 text-primary"></i>Sua seleção
                        </h5>
                        
                        <div id="selectedSeatsList">
                            <div class="text-center text-muted py-4" id="emptySelection">
                                <i class="bi bi-hand-index-thumb display-4"></i>
                                <p class="mt-2 mb-0">Clique nos assentos para selecioná-los</p>
                            </div>
                        </div>
                        
                        <hr class="my-4" id="summaryDivider" style="display: none;">
                        
                        <div id="summarySection" style="display: none;">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Ingressos (<span id="ticketCount">0</span>x)</span>
                                <span id="subtotalDisplay">R$ 0,00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Taxa de serviço</span>
                                <span id="feeDisplay">R$ 0,00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4">
                                <span class="fw-bold">Total</span>
                                <span class="fw-bold text-primary fs-4" id="totalDisplay">R$ 0,00</span>
                            </div>
                            
                            <button class="btn btn-primary btn-lg w-100" id="addToCartBtn" onclick="addToCart()">
                                <i class="bi bi-cart-plus me-2"></i>Adicionar ao carrinho
                            </button>
                            
                            <p class="text-muted small text-center mt-3 mb-0">
                                <i class="bi bi-shield-check me-1"></i>
                                Pagamento 100% seguro
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Informações importantes</h6>
                        <ul class="list-unstyled text-muted small mb-0">
                            <li class="mb-2">
                                <i class="bi bi-clock text-primary me-2"></i>
                                Assentos selecionados ficam reservados por 10 minutos
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-ticket-perforated text-primary me-2"></i>
                                Limite de 6 ingressos por compra
                            </li>
                            <li>
                                <i class="bi bi-arrow-return-left text-primary me-2"></i>
                                Você pode cancelar até 7 dias antes do evento
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden data -->
<script>
const eventId = <?= $event->id ?>;
const eventDayId = <?= $eventDay->id ?>;
const eventSlug = '<?= esc($event->slug) ?>';
const maxTickets = 6;
const serviceFeePercent = 0.1; // 10%
</script>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let selectedSeats = [];
let reservationTimer = null;
let timerSeconds = 600; // 10 minutes

function toggleSeat(element) {
    const seatId = element.dataset.seatId;
    const isSelected = element.classList.contains('selected');
    
    if (isSelected) {
        // Remove seat
        element.classList.remove('selected');
        element.classList.add('available');
        selectedSeats = selectedSeats.filter(s => s.id !== seatId);
    } else {
        // Check max tickets
        if (selectedSeats.length >= maxTickets) {
            alert(`Você pode selecionar no máximo ${maxTickets} assentos por compra.`);
            return;
        }
        
        // Add seat
        element.classList.remove('available');
        element.classList.add('selected');
        selectedSeats.push({
            id: seatId,
            code: element.dataset.seatCode,
            sectorId: element.dataset.sectorId,
            sectorName: element.dataset.sectorName,
            queueName: element.dataset.queueName,
            price: parseFloat(element.dataset.price)
        });
    }
    
    updateSummary();
}

function removeSeat(seatId) {
    const element = document.querySelector(`.seat[data-seat-id="${seatId}"]`);
    if (element) {
        element.classList.remove('selected');
        element.classList.add('available');
    }
    selectedSeats = selectedSeats.filter(s => s.id !== seatId);
    updateSummary();
}

function updateSummary() {
    const listContainer = document.getElementById('selectedSeatsList');
    const emptySelection = document.getElementById('emptySelection');
    const summarySection = document.getElementById('summarySection');
    const summaryDivider = document.getElementById('summaryDivider');
    const timerBadge = document.getElementById('reservationTimer');
    
    if (selectedSeats.length === 0) {
        listContainer.innerHTML = `
            <div class="text-center text-muted py-4" id="emptySelection">
                <i class="bi bi-hand-index-thumb display-4"></i>
                <p class="mt-2 mb-0">Clique nos assentos para selecioná-los</p>
            </div>
        `;
        summarySection.style.display = 'none';
        summaryDivider.style.display = 'none';
        timerBadge.style.display = 'none';
        stopTimer();
        return;
    }
    
    // Show timer
    timerBadge.style.display = 'block';
    startTimer();
    
    // Build seat list
    let html = '';
    let subtotal = 0;
    
    selectedSeats.forEach(seat => {
        subtotal += seat.price;
        html += `
            <div class="selected-seat-item">
                <div>
                    <strong>${seat.code}</strong>
                    <br>
                    <small class="text-muted">${seat.sectorName} - Fila ${seat.queueName}</small>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-primary fw-bold">R$ ${seat.price.toFixed(2).replace('.', ',')}</span>
                    <button class="remove-btn" onclick="removeSeat('${seat.id}')">
                        <i class="bi bi-x-circle fs-5"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    listContainer.innerHTML = html;
    summarySection.style.display = 'block';
    summaryDivider.style.display = 'block';
    
    // Calculate totals
    const fee = subtotal * serviceFeePercent;
    const total = subtotal + fee;
    
    document.getElementById('ticketCount').textContent = selectedSeats.length;
    document.getElementById('subtotalDisplay').textContent = `R$ ${subtotal.toFixed(2).replace('.', ',')}`;
    document.getElementById('feeDisplay').textContent = `R$ ${fee.toFixed(2).replace('.', ',')}`;
    document.getElementById('totalDisplay').textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
}

function startTimer() {
    if (reservationTimer) return;
    
    reservationTimer = setInterval(() => {
        timerSeconds--;
        
        const minutes = Math.floor(timerSeconds / 60);
        const seconds = timerSeconds % 60;
        document.getElementById('timerDisplay').textContent = 
            `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        if (timerSeconds <= 0) {
            stopTimer();
            alert('Tempo de reserva expirado. Seus assentos foram liberados.');
            clearSelection();
        }
    }, 1000);
}

function stopTimer() {
    if (reservationTimer) {
        clearInterval(reservationTimer);
        reservationTimer = null;
        timerSeconds = 600;
    }
}

function clearSelection() {
    selectedSeats.forEach(seat => {
        const element = document.querySelector(`.seat[data-seat-id="${seat.id}"]`);
        if (element) {
            element.classList.remove('selected');
            element.classList.add('available');
        }
    });
    selectedSeats = [];
    updateSummary();
}

function addToCart() {
    if (selectedSeats.length === 0) {
        alert('Selecione pelo menos um assento.');
        return;
    }
    
    const btn = document.getElementById('addToCartBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adicionando...';
    
    fetch('<?= base_url('api/cart/add') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            event_id: eventId,
            event_day_id: eventDayId,
            seats: selectedSeats.map(s => ({
                seat_id: s.id,
                sector_id: s.sectorId,
                price: s.price
            }))
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '<?= base_url('carrinho') ?>';
        } else {
            alert(data.message || 'Erro ao adicionar ao carrinho');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-cart-plus me-2"></i>Adicionar ao carrinho';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro ao processar sua solicitação.');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-cart-plus me-2"></i>Adicionar ao carrinho';
    });
}

// Update cart count on page load
updateCartCount();
</script>
<?= $this->endSection() ?>
