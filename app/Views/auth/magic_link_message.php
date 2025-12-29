<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?>Link Enviado<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="auth-header" style="background: linear-gradient(135deg, #198754 0%, #0d6e3d 100%);">
    <h2><i class="bi bi-check-circle"></i> E-mail Enviado!</h2>
    <p>Verifique sua caixa de entrada</p>
</div>

<div class="card-body p-4 text-center">
    <div class="mb-4">
        <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
            <i class="bi bi-envelope-check text-success" style="font-size: 2.5rem;"></i>
        </div>
    </div>

    <h5 class="mb-3">Quase lá!</h5>
    
    <p class="text-muted mb-4">
        Enviamos um link de acesso para o seu e-mail. 
        Clique no link para entrar na sua conta automaticamente.
    </p>

    <div class="alert alert-warning text-start">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Importante:</strong>
        <ul class="mb-0 mt-2">
            <li>O link expira em <strong>1 hora</strong></li>
            <li>Verifique a pasta de spam se não encontrar o e-mail</li>
            <li>Cada link só pode ser usado uma vez</li>
        </ul>
    </div>

    <hr class="my-4">

    <p class="text-muted small mb-3">Não recebeu o e-mail?</p>
    
    <a href="<?= url_to('magic-link') ?>" class="btn btn-outline-primary">
        <i class="bi bi-arrow-repeat me-2"></i>Reenviar Link
    </a>

    <div class="mt-4">
        <a href="<?= url_to('login') ?>" class="text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i>Voltar para o login
        </a>
    </div>
</div>
<?= $this->endSection() ?>
