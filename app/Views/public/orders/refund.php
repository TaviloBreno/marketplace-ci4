<?= $this->extend('layouts/public') ?>

<?= $this->section('title') ?>Solicitar Reembolso<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('meus-pedidos') ?>">Meus Pedidos</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('meus-pedidos/' . $order->id) ?>">#<?= str_pad($order->id, 6, '0', STR_PAD_LEFT) ?></a></li>
                    <li class="breadcrumb-item active">Reembolso</li>
                </ol>
            </nav>
            
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-exclamation-triangle text-warning display-5"></i>
                        </div>
                        <h2 class="h4 fw-bold">Solicitar Reembolso</h2>
                        <p class="text-muted">Pedido #<?= str_pad($order->id, 6, '0', STR_PAD_LEFT) ?></p>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        O evento acontecerá em <strong><?= $eligibility['days_until_event'] ?> dias</strong>. 
                        Você pode solicitar reembolso até 7 dias antes do evento.
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="bg-light rounded p-3 mb-4">
                        <h6 class="fw-bold mb-2"><?= esc($event->title) ?></h6>
                        <p class="text-muted small mb-2">
                            <i class="bi bi-geo-alt me-1"></i><?= esc($event->venue) ?>
                        </p>
                        <div class="d-flex justify-content-between">
                            <span>Valor a ser reembolsado:</span>
                            <span class="fw-bold text-primary">R$ <?= number_format($order->total, 2, ',', '.') ?></span>
                        </div>
                    </div>
                    
                    <form id="refundForm">
                        <div class="mb-4">
                            <label class="form-label fw-medium">Motivo do reembolso (opcional)</label>
                            <textarea name="reason" class="form-control" rows="3" placeholder="Conte-nos por que você está solicitando o reembolso..."></textarea>
                        </div>
                        
                        <div class="alert alert-warning mb-4">
                            <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle me-2"></i>Atenção</h6>
                            <ul class="mb-0 small">
                                <li>Esta ação não pode ser desfeita</li>
                                <li>Seus ingressos serão cancelados imediatamente</li>
                                <li>O reembolso será processado na mesma forma de pagamento</li>
                                <li>O valor pode levar até 10 dias úteis para aparecer na sua conta</li>
                            </ul>
                        </div>
                        
                        <div class="d-flex gap-3">
                            <a href="<?= base_url('meus-pedidos/' . $order->id) ?>" class="btn btn-outline-secondary flex-grow-1">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-danger flex-grow-1" id="submitBtn">
                                <i class="bi bi-arrow-return-left me-2"></i>Confirmar Reembolso
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.getElementById('refundForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (!confirm('Tem certeza que deseja solicitar o reembolso? Esta ação não pode ser desfeita.')) {
        return;
    }
    
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processando...';
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('<?= base_url("api/pedidos/{$order->id}/reembolso") ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Reembolso processado com sucesso!');
            window.location.href = result.redirect_url || '<?= base_url('meus-pedidos/' . $order->id) ?>';
        } else {
            alert(result.error || 'Erro ao processar reembolso');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-return-left me-2"></i>Confirmar Reembolso';
        }
    } catch (error) {
        alert('Erro ao processar solicitação');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-arrow-return-left me-2"></i>Confirmar Reembolso';
    }
});
</script>
<?= $this->endSection() ?>
