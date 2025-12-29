<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Mapa de Assentos - <?= esc($event->title) ?><?= $this->endSection() ?>

<?= $this->section('styles') ?>
.seat-map-wrapper {
    background: #1a1a2e;
    border-radius: 12px;
    padding: 30px;
    overflow: auto;
    min-height: 500px;
}
.seat-map-container {
    min-width: 800px;
}
.seat-map {
    position: relative;
    min-height: 400px;
}
.stage {
    background: linear-gradient(180deg, #2c3e50 0%, #1a252f 100%);
    color: #fff;
    text-align: center;
    padding: 20px;
    border-radius: 8px 8px 50% 50%;
    margin-bottom: 30px;
    font-weight: bold;
    letter-spacing: 2px;
}
.sector {
    position: relative;
    padding: 15px;
    margin-bottom: 20px;
    border: 2px solid;
    border-radius: 8px;
}
.sector-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid rgba(255,255,255,0.2);
}
.sector-name {
    font-weight: bold;
    color: #fff;
}
.sector-price {
    background: rgba(255,255,255,0.2);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    color: #fff;
}
.queue {
    margin-bottom: 15px;
}
.queue-name {
    display: block;
    color: rgba(255,255,255,0.7);
    font-size: 0.75rem;
    margin-bottom: 5px;
}
.seats-row {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}
.seat {
    width: 30px;
    height: 30px;
    border-radius: 6px 6px 10px 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.65rem;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    color: #fff;
}
.seat:hover {
    transform: scale(1.15);
    z-index: 10;
}
.seat-number {
    font-size: 0.6rem;
}
.seat-icon {
    position: absolute;
    bottom: 2px;
    font-size: 0.5rem;
}
.seat-available {
    background: #27ae60;
}
.seat-reserved {
    background: #f39c12;
}
.seat-sold {
    background: #e74c3c;
    cursor: not-allowed;
}
.seat-blocked {
    background: #7f8c8d;
    cursor: not-allowed;
}
.seat-accessible {
    border: 2px solid #3498db;
}
.sector-general-area {
    text-align: center;
    padding: 30px;
    background: rgba(255,255,255,0.1);
    border-radius: 8px;
    color: #fff;
}
.seat-legend {
    display: flex;
    justify-content: center;
    gap: 20px;
    padding: 20px;
    background: rgba(0,0,0,0.3);
    border-radius: 8px;
    margin-top: 20px;
}
.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #fff;
    font-size: 0.85rem;
}
.legend-item .seat {
    width: 20px;
    height: 20px;
    cursor: default;
}
.legend-item .seat:hover {
    transform: none;
}
.seat-stats {
    display: flex;
    justify-content: center;
    gap: 30px;
    margin-top: 15px;
    color: #fff;
}
.stat-item {
    text-align: center;
}
.stat-item strong {
    display: block;
    font-size: 1.5rem;
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
                    <li class="breadcrumb-item"><a href="<?= base_url('organizer/events/' . $event->id) ?>"><?= esc($event->title) ?></a></li>
                    <li class="breadcrumb-item active">Mapa de Assentos</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">Mapa de Assentos</h1>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <label class="form-label mb-0 me-2">Selecionar Data:</label>
            <select class="form-select" id="daySelect" style="width: auto;">
                <?php foreach ($days as $day): ?>
                    <option value="<?= $day->id ?>" <?= $selectedDay == $day->id ? 'selected' : '' ?>>
                        <?= date('d/m/Y', strtotime($day->date)) ?> - <?= date('H:i', strtotime($day->start_time)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="button" class="btn btn-outline-primary" id="refreshLayout">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>
    </div>

    <!-- Seat Map -->
    <div class="seat-map-wrapper">
        <div id="seatMapContainer">
            <?= $seatMapHtml ?>
        </div>
    </div>

    <!-- Ações -->
    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col">
                    <h6 class="mb-1">Gerenciar Assentos</h6>
                    <p class="text-muted mb-0 small">Selecione assentos para bloquear, desbloquear ou marcar como acessíveis.</p>
                </div>
                <div class="col-auto">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-secondary" id="blockSelected" disabled>
                            <i class="bi bi-lock"></i> Bloquear
                        </button>
                        <button type="button" class="btn btn-outline-success" id="unblockSelected" disabled>
                            <i class="bi bi-unlock"></i> Desbloquear
                        </button>
                        <button type="button" class="btn btn-outline-info" id="toggleAccessible" disabled>
                            <i class="bi bi-wheelchair"></i> Acessibilidade
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Info Assento -->
<div class="modal fade" id="seatInfoModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Assento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <dl class="mb-0">
                    <dt>Assento</dt>
                    <dd id="seatLabel">-</dd>
                    <dt>Status</dt>
                    <dd id="seatStatus">-</dd>
                    <dt>Preço</dt>
                    <dd id="seatPrice">-</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const eventId = <?= $event->id ?>;
let layoutData = <?= $layoutJson ?>;
let selectedSeats = [];

// Mudar data
document.getElementById('daySelect').addEventListener('change', function() {
    loadLayout(this.value);
});

// Atualizar layout
document.getElementById('refreshLayout').addEventListener('click', function() {
    const dayId = document.getElementById('daySelect').value;
    loadLayout(dayId);
});

async function loadLayout(dayId) {
    try {
        const response = await fetch(`<?= base_url('organizer/events/') ?>${eventId}/layout?day=${dayId}`);
        const data = await response.json();
        
        if (data.success) {
            layoutData = data.layout;
            // Recarregar a página com o novo dia
            window.location.href = `<?= base_url('organizer/events/' . $event->id . '/seat-map') ?>?day=${dayId}`;
        }
    } catch (error) {
        console.error('Erro ao carregar layout:', error);
    }
}

// Click em assento
document.addEventListener('click', function(e) {
    const seat = e.target.closest('.seat');
    if (!seat) return;
    
    const seatId = seat.dataset.seatId;
    const status = seat.dataset.status;
    
    if (status === 'sold') {
        // Mostrar info
        showSeatInfo(seat);
        return;
    }
    
    // Toggle seleção
    if (seat.classList.contains('selected')) {
        seat.classList.remove('selected');
        seat.style.outline = '';
        selectedSeats = selectedSeats.filter(id => id !== seatId);
    } else {
        seat.classList.add('selected');
        seat.style.outline = '3px solid #fff';
        selectedSeats.push(seatId);
    }
    
    updateActionButtons();
});

function showSeatInfo(seat) {
    document.getElementById('seatLabel').textContent = seat.dataset.seatLabel;
    document.getElementById('seatStatus').textContent = seat.dataset.status.charAt(0).toUpperCase() + seat.dataset.status.slice(1);
    document.getElementById('seatPrice').textContent = 'R$ ' + parseFloat(seat.dataset.price).toFixed(2).replace('.', ',');
    new bootstrap.Modal(document.getElementById('seatInfoModal')).show();
}

function updateActionButtons() {
    const hasSelection = selectedSeats.length > 0;
    document.getElementById('blockSelected').disabled = !hasSelection;
    document.getElementById('unblockSelected').disabled = !hasSelection;
    document.getElementById('toggleAccessible').disabled = !hasSelection;
}

// Ações em massa
document.getElementById('blockSelected').addEventListener('click', function() {
    // TODO: Implementar bloqueio de assentos
    alert('Funcionalidade em desenvolvimento');
});

document.getElementById('unblockSelected').addEventListener('click', function() {
    // TODO: Implementar desbloqueio de assentos
    alert('Funcionalidade em desenvolvimento');
});

document.getElementById('toggleAccessible').addEventListener('click', function() {
    // TODO: Implementar toggle acessibilidade
    alert('Funcionalidade em desenvolvimento');
});
</script>
<?= $this->endSection() ?>
