<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?>Recuperar Senha<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="auth-header">
    <h2><i class="bi bi-key"></i> Recuperar Senha</h2>
    <p>Enviaremos um link de acesso para seu e-mail</p>
</div>

<div class="card-body p-4">
    <?php if (session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                <?php foreach (session('errors') as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="alert alert-info mb-4">
        <i class="bi bi-info-circle me-2"></i>
        Digite seu e-mail abaixo e enviaremos um link mágico para você acessar sua conta sem precisar de senha.
    </div>

    <form action="<?= url_to('magic-link') ?>" method="post">
        <?= csrf_field() ?>

        <div class="mb-4">
            <label for="email" class="form-label">E-mail</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" class="form-control" id="email" name="email" 
                       placeholder="seu@email.com" value="<?= old('email') ?>" required autofocus>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="bi bi-send me-2"></i>Enviar Link de Acesso
        </button>
    </form>

    <div class="text-center mt-4">
        <a href="<?= url_to('login') ?>" class="text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i>Voltar para o login
        </a>
    </div>
</div>
<?= $this->endSection() ?>
