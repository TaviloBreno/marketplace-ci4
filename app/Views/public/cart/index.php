<?= $this->extend('layouts/public') ?>

<?= $this->section('title') ?>Meu Carrinho<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .cart-item {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        transition: all 0.2s ease;
    }
    
    .cart-item:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    
    .cart-item-image {
        width: 100px;
        height: 70px;
        object-fit: cover;
        border-radius: 8px;
    }
    
    .seat-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #f1f5f9;
        padding: 8px 12px;
        border-radius: 8px;
        font-weight: 500;
    }
    
    .seat-badge .sector-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }
    
    .timer-warning {
        background: #fef3c7;
        border: 1px solid #fcd34d;
        border-radius: 8px;
        padding: 12px 16px;
        color: #92400e;
    }
    
    .empty-cart {
        text-align: center;
        padding: 60px 20px;
    }
    
    .empty-cart i {
        font-size: 80px;
        color: #cbd5e1;
    }
    
    .summary-card {
        position: sticky;
        top: 100px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container py-5">
    <h1 class="h3 fw-bold mb-4">
        <i class="bi bi-cart3 me-2 text-primary"></i>Meu Carrinho
    </h1>
    
    <?php if (empty($items)): ?>
        <!-- Empty Cart -->
        <div class="card border-0 shadow-sm">
            <div class="card-body empty-cart">
                <i class="bi bi-cart-x"></i>
                <h3 class="mt-4 mb-2">Seu carrinho está vazio</h3>
                <p class="text-muted mb-4">Explore nossos eventos e encontre experiências incríveis!</p>
                <a href="<?= base_url('eventos') ?>" class="btn btn-primary btn-lg">
                    <i class="bi bi-search me-2"></i>Explorar eventos
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <!-- Timer Warning -->
                <div class="timer-warning mb-4">
                    <i class="bi bi-clock me-2"></i>
                    <strong>Atenção:</strong> Seus ingressos estão reservados temporariamente. 
                    Complete a compra antes que a reserva expire.
                </div>
                
                <!-- Items List -->
                <?php 
                $groupedItems = [];
                foreach ($items as $item) {
                    $eventId = $item['event']['id'];
                    $dayId = $item['event_day']['id'];
                    $key = $eventId . '_' . $dayId;
                    
                    if (!isset($groupedItems[$key])) {
                        $groupedItems[$key] = [
                            'event' => $item['event'],
                            'event_day' => $item['event_day'],
                            'seats' => []
                        ];
                    }
                    $groupedItems[$key]['seats'][] = $item;
                }
                ?>
                
                <?php foreach ($groupedItems as $group): ?>
                    <div class="cart-item">
                        <div class="d-flex gap-4">
                            <!-- Event Image -->
                            <?php if ($group['event']['image']): ?>
                                <img src="<?= base_url('uploads/events/' . esc($group['event']['image'])) ?>" 
                                     alt="<?= esc($group['event']['title']) ?>"
                                     class="cart-item-image d-none d-md-block">
                            <?php else: ?>
                                <div class="cart-item-image bg-secondary d-none d-md-flex align-items-center justify-content-center">
                                    <i class="bi bi-calendar-event text-white fs-4"></i>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Event Details -->
                            <div class="flex-grow-1">
                                <h5 class="fw-bold mb-1">
                                    <a href="<?= base_url('evento/' . esc($group['event']['slug'])) ?>" class="text-decoration-none text-dark">
                                        <?= esc($group['event']['title']) ?>
                                    </a>
                                </h5>
                                <p class="text-muted mb-3">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    <?= date('d/m/Y', strtotime($group['event_day']['date'])) ?>
                                    <?php if ($group['event_day']['start_time']): ?>
                                        às <?= date('H:i', strtotime($group['event_day']['start_time'])) ?>
                                    <?php endif; ?>
                                    <span class="mx-2">•</span>
                                    <i class="bi bi-geo-alt me-1"></i>
                                    <?= esc($group['event']['venue']) ?>
                                </p>
                                
                                <!-- Seats -->
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($group['seats'] as $seat): ?>
                                        <div class="seat-badge">
                                            <span class="sector-dot" style="background: <?= esc($seat['sector']['color'] ?? '#6366f1') ?>"></span>
                                            <span><?= esc($seat['seat']['code']) ?></span>
                                            <span class="text-muted small">
                                                (<?= esc($seat['sector']['name']) ?> - Fila <?= esc($seat['seat']['queue']) ?>)
                                            </span>
                                            <span class="text-primary fw-bold ms-2">
                                                R$ <?= number_format($seat['price'], 2, ',', '.') ?>
                                            </span>
                                            <button class="btn btn-link btn-sm text-danger p-0 ms-2" 
                                                    onclick="removeItem(<?= $seat['booking_id'] ?>)"
                                                    title="Remover">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <!-- Actions -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <button class="btn btn-outline-danger" onclick="clearCart()">
                        <i class="bi bi-trash me-2"></i>Limpar carrinho
                    </button>
                    <a href="<?= base_url('eventos') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-plus-circle me-2"></i>Adicionar mais ingressos
                    </a>
                </div>
            </div>
            
            <!-- Summary -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm summary-card">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Resumo do pedido</h5>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Ingressos (<?= count($items) ?>)</span>
                            <span>R$ <?= number_format($subtotal, 2, ',', '.') ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Taxa de serviço (10%)</span>
                            <span>R$ <?= number_format($serviceFee, 2, ',', '.') ?></span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold">Total</span>
                            <span class="fw-bold text-primary fs-4">R$ <?= number_format($total, 2, ',', '.') ?></span>
                        </div>
                        
                        <?php if (auth()->loggedIn()): ?>
                            <a href="<?= base_url('checkout') ?>" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-lock me-2"></i>Finalizar compra
                            </a>
                        <?php else: ?>
                            <a href="<?= base_url('login?redirect=' . urlencode(current_url())) ?>" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-person me-2"></i>Entrar para continuar
                            </a>
                            <p class="text-center text-muted small mt-3 mb-0">
                                Não tem uma conta? 
                                <a href="<?= base_url('register?redirect=' . urlencode(current_url())) ?>">Cadastre-se</a>
                            </p>
                        <?php endif; ?>
                        
                        <div class="text-center mt-4">
                            <p class="text-muted small mb-2">
                                <i class="bi bi-shield-check me-1"></i>Pagamento 100% seguro
                            </p>
                            <div class="d-flex justify-content-center gap-2">
                                <i class="bi bi-credit-card-2-front fs-4 text-muted"></i>
                                <i class="bi bi-paypal fs-4 text-muted"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function removeItem(bookingId) {
    if (!confirm('Remover este ingresso do carrinho?')) return;
    
    fetch('<?= base_url('carrinho/remover') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ booking_id: bookingId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Erro ao remover item');
        }
    })
    .catch(() => alert('Erro ao processar solicitação'));
}

function clearCart() {
    if (!confirm('Tem certeza que deseja limpar o carrinho?')) return;
    
    fetch('<?= base_url('carrinho/limpar') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Erro ao limpar carrinho');
        }
    })
    .catch(() => alert('Erro ao processar solicitação'));
}
</script>
<?= $this->endSection() ?>
