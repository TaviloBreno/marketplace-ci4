<?= $this->extend('layouts/public') ?>

<?= $this->section('title') ?>Finalizar Compra<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .checkout-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    }
    
    .order-item {
        display: flex;
        gap: 15px;
        padding: 15px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .order-item:last-child {
        border-bottom: none;
    }
    
    .order-item-image {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        object-fit: cover;
    }
    
    #payment-element {
        margin-bottom: 20px;
    }
    
    .payment-secure {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        color: #64748b;
        font-size: 0.85rem;
        margin-top: 20px;
    }
    
    .summary-sticky {
        position: sticky;
        top: 100px;
    }
    
    #payment-message {
        color: #ef4444;
        padding: 12px;
        background: #fef2f2;
        border-radius: 8px;
        margin-bottom: 15px;
        display: none;
    }
    
    #payment-message.visible {
        display: block;
    }
    
    .btn-pay {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border: none;
        height: 50px;
        font-size: 1.1rem;
    }
    
    .btn-pay:hover {
        background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
    }
    
    .btn-pay:disabled {
        background: #94a3b8;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('carrinho') ?>">Carrinho</a></li>
                    <li class="breadcrumb-item active">Finalizar Compra</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold">
                <i class="bi bi-credit-card me-2 text-primary"></i>Finalizar Compra
            </h1>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Payment Form -->
        <div class="col-lg-7">
            <!-- Customer Info -->
            <div class="card checkout-card mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="bi bi-person me-2 text-primary"></i>Seus dados
                    </h5>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Nome</label>
                            <p class="fw-medium mb-0"><?= esc($user->username) ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">E-mail</label>
                            <p class="fw-medium mb-0"><?= esc(auth()->user()->email) ?></p>
                        </div>
                    </div>
                    
                    <p class="text-muted small mt-3 mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Os ingressos serão enviados para este e-mail
                    </p>
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="card checkout-card mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="bi bi-ticket-perforated me-2 text-primary"></i>Seus ingressos
                    </h5>
                    
                    <?php foreach ($items as $item): ?>
                        <div class="order-item">
                            <?php if ($item['event']->image): ?>
                                <img src="<?= base_url('uploads/events/' . esc($item['event']->image)) ?>" 
                                     alt="<?= esc($item['event']->title) ?>"
                                     class="order-item-image">
                            <?php else: ?>
                                <div class="order-item-image bg-secondary d-flex align-items-center justify-content-center">
                                    <i class="bi bi-calendar-event text-white"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1"><?= esc($item['event']->title) ?></h6>
                                <p class="text-muted small mb-1">
                                    <?= date('d/m/Y', strtotime($item['event_day']->event_date)) ?>
                                    <?php if ($item['event_day']->start_time): ?>
                                        às <?= date('H:i', strtotime($item['event_day']->start_time)) ?>
                                    <?php endif; ?>
                                </p>
                                <p class="text-muted small mb-0">
                                    <strong><?= esc($item['seat']->code) ?></strong> - 
                                    <?= esc($item['sector']->name) ?> 
                                    (Fila <?= esc($item['queue']->name ?? '') ?>)
                                </p>
                            </div>
                            
                            <div class="text-end">
                                <span class="fw-bold text-primary">
                                    R$ <?= number_format($item['price'], 2, ',', '.') ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Payment Form -->
            <div class="card checkout-card">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="bi bi-credit-card-2-front me-2 text-primary"></i>Pagamento
                    </h5>
                    
                    <form id="payment-form">
                        <div id="payment-message"></div>
                        
                        <div id="payment-element"></div>
                        
                        <button id="submit-btn" class="btn btn-primary btn-pay w-100" type="submit">
                            <span id="button-text">
                                <i class="bi bi-lock me-2"></i>
                                Pagar R$ <?= number_format($totals['total'], 2, ',', '.') ?>
                            </span>
                            <span id="spinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
                    </form>
                    
                    <div class="payment-secure">
                        <i class="bi bi-shield-check"></i>
                        <span>Pagamento processado com segurança pelo Stripe</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div class="col-lg-5">
            <div class="summary-sticky">
                <div class="card checkout-card">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Resumo do pedido</h5>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Ingressos (<?= $totals['items_count'] ?>)</span>
                            <span>R$ <?= number_format($totals['subtotal'], 2, ',', '.') ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Taxa de serviço</span>
                            <span>R$ <?= number_format($totals['service_fee'], 2, ',', '.') ?></span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold fs-5">Total</span>
                            <span class="fw-bold text-primary fs-4">
                                R$ <?= number_format($totals['total'], 2, ',', '.') ?>
                            </span>
                        </div>
                        
                        <div class="bg-light rounded-3 p-3">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-check-circle text-success me-2"></i>O que você receberá
                            </h6>
                            <ul class="list-unstyled text-muted small mb-0">
                                <li class="mb-2">
                                    <i class="bi bi-envelope me-2"></i>
                                    Confirmação por e-mail
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-qr-code me-2"></i>
                                    Ingressos com QR Code
                                </li>
                                <li>
                                    <i class="bi bi-phone me-2"></i>
                                    Acesso pelo app ou site
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="card checkout-card mt-3">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Precisa de ajuda?</h6>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-question-circle me-2"></i>
                            Acesse nossa <a href="#">Central de Ajuda</a> ou entre em contato pelo 
                            <a href="mailto:suporte@eventhub.com">suporte@eventhub.com</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://js.stripe.com/v3/"></script>
<script>
const stripe = Stripe('<?= esc($stripePublicKey) ?>');
const clientSecret = '<?= esc($clientSecret) ?>';

let elements;

initialize();

async function initialize() {
    elements = stripe.elements({ clientSecret });
    
    const paymentElement = elements.create('payment', {
        layout: 'tabs'
    });
    paymentElement.mount('#payment-element');
}

const form = document.getElementById('payment-form');
form.addEventListener('submit', handleSubmit);

async function handleSubmit(e) {
    e.preventDefault();
    setLoading(true);
    
    const { error, paymentIntent } = await stripe.confirmPayment({
        elements,
        confirmParams: {
            return_url: '<?= base_url('checkout/process') ?>',
        },
        redirect: 'if_required'
    });
    
    if (error) {
        showMessage(error.message);
        setLoading(false);
        return;
    }
    
    if (paymentIntent && paymentIntent.status === 'succeeded') {
        // Confirmar pedido no backend
        const response = await fetch('<?= base_url('api/checkout/process') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                payment_intent_id: paymentIntent.id
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            window.location.href = result.redirect_url;
        } else {
            showMessage(result.error || 'Erro ao processar pedido');
            setLoading(false);
        }
    } else {
        showMessage('Pagamento não foi concluído');
        setLoading(false);
    }
}

function showMessage(messageText) {
    const messageContainer = document.getElementById('payment-message');
    messageContainer.classList.add('visible');
    messageContainer.textContent = messageText;
    
    setTimeout(() => {
        messageContainer.classList.remove('visible');
        messageContainer.textContent = '';
    }, 10000);
}

function setLoading(isLoading) {
    const submitBtn = document.getElementById('submit-btn');
    const buttonText = document.getElementById('button-text');
    const spinner = document.getElementById('spinner');
    
    if (isLoading) {
        submitBtn.disabled = true;
        buttonText.classList.add('d-none');
        spinner.classList.remove('d-none');
    } else {
        submitBtn.disabled = false;
        buttonText.classList.remove('d-none');
        spinner.classList.add('d-none');
    }
}
</script>
<?= $this->endSection() ?>
