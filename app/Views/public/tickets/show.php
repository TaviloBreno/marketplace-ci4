<?= $this->extend('layouts/public') ?>

<?= $this->section('title') ?>Ingresso <?= esc($ticket->code) ?><?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .ticket-container {
        max-width: 500px;
        margin: 0 auto;
    }
    
    .ticket-card {
        border: none;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    }
    
    .ticket-header {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
        padding: 30px;
        text-align: center;
    }
    
    .ticket-body {
        padding: 30px;
        background: white;
    }
    
    .qr-container {
        background: white;
        padding: 20px;
        border-radius: 16px;
        display: inline-block;
        margin-bottom: 20px;
    }
    
    .ticket-code {
        font-family: monospace;
        font-size: 1.5rem;
        letter-spacing: 3px;
        background: #f1f5f9;
        padding: 12px 20px;
        border-radius: 8px;
        display: inline-block;
    }
    
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 15px 0;
        border-bottom: 1px dashed #e2e8f0;
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-label {
        color: #64748b;
        font-size: 0.85rem;
    }
    
    .info-value {
        font-weight: 600;
        text-align: right;
    }
    
    .seat-display {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
        width: 80px;
        height: 80px;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }
    
    .seat-display .seat-code {
        font-size: 1.5rem;
        font-weight: 700;
    }
    
    .seat-display .seat-label {
        font-size: 0.7rem;
        opacity: 0.8;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('meus-ingressos') ?>">Meus Ingressos</a></li>
            <li class="breadcrumb-item active"><?= esc($ticket->code) ?></li>
        </ol>
    </nav>
    
    <div class="ticket-container">
        <div class="card ticket-card">
            <!-- Header -->
            <div class="ticket-header">
                <h2 class="fw-bold mb-2"><?= esc($event->title ?? 'Evento') ?></h2>
                <p class="mb-0 opacity-75">
                    <i class="bi bi-calendar3 me-1"></i>
                    <?= date('d/m/Y', strtotime($eventDay->event_date ?? 'now')) ?>
                    <?php if ($eventDay && $eventDay->start_time): ?>
                        às <?= date('H:i', strtotime($eventDay->start_time)) ?>
                    <?php endif; ?>
                </p>
            </div>
            
            <!-- Body -->
            <div class="ticket-body text-center">
                <!-- Seat -->
                <div class="seat-display">
                    <span class="seat-label">ASSENTO</span>
                    <span class="seat-code"><?= esc($seat->code ?? 'N/A') ?></span>
                </div>
                
                <!-- QR Code -->
                <div class="qr-container">
                    <div id="qrcode"></div>
                </div>
                
                <!-- Ticket Code -->
                <div class="mb-4">
                    <span class="ticket-code"><?= esc($ticket->code) ?></span>
                </div>
                
                <!-- Info -->
                <div class="text-start">
                    <div class="info-row">
                        <span class="info-label">Local</span>
                        <span class="info-value"><?= esc($event->venue ?? 'N/A') ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Setor</span>
                        <span class="info-value"><?= esc($sector->name ?? 'N/A') ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Fila</span>
                        <span class="info-value"><?= esc($queue->name ?? 'N/A') ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span class="badge <?= $ticket->status === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $ticket->status === 'active' ? 'Válido' : ucfirst($ticket->status) ?>
                            </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="d-flex gap-3 justify-content-center mt-4">
            <a href="<?= base_url('ingresso/' . esc($ticket->code) . '/imprimir') ?>" 
               class="btn btn-primary btn-lg" target="_blank">
                <i class="bi bi-printer me-2"></i>Imprimir
            </a>
            <a href="<?= base_url('meus-ingressos') ?>" class="btn btn-outline-secondary btn-lg">
                <i class="bi bi-arrow-left me-2"></i>Voltar
            </a>
        </div>
        
        <!-- Warning -->
        <div class="alert alert-info mt-4">
            <i class="bi bi-info-circle me-2"></i>
            Apresente este QR Code na entrada do evento. Tenha o documento de identidade em mãos.
        </div>
    </div>
</div>

<!-- QR Code Library -->
<script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Generate QR Code
const qrData = JSON.stringify({
    code: '<?= esc($ticket->code) ?>',
    event: <?= $event ? $event->id : 0 ?>,
    day: <?= $eventDay ? $eventDay->id : 0 ?>,
    seat: <?= $seat ? $seat->id : 0 ?>
});

QRCode.toCanvas(document.createElement('canvas'), qrData, { width: 180 }, function (error, canvas) {
    if (!error) {
        document.getElementById('qrcode').appendChild(canvas);
    }
});
</script>
<?= $this->endSection() ?>
