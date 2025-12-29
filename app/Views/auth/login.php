<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?>Login<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="auth-header">
    <h2><i class="bi bi-shop"></i> Marketplace</h2>
    <p>Faça login para continuar</p>
</div>

<div class="card-body p-4">
    <?php if (session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session('message') ?>
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

    <form action="<?= url_to('login') ?>" method="post">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" class="form-control" id="email" name="email" 
                       placeholder="seu@email.com" value="<?= old('email') ?>" required>
            </div>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Senha</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control" id="password" name="password" 
                       placeholder="••••••••" required>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember" <?= old('remember') ? 'checked' : '' ?>>
                <label class="form-check-label" for="remember">Lembrar-me</label>
            </div>
            <?php if (setting('Auth.allowMagicLinkLogins')): ?>
                <a href="<?= url_to('magic-link') ?>" class="text-decoration-none">Esqueceu a senha?</a>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
        </button>
    </form>

    <?php if (setting('Auth.allowRegistration')): ?>
        <div class="divider">
            <span>ou</span>
        </div>

        <div class="text-center">
            <p class="mb-0">Não tem uma conta? 
                <a href="<?= url_to('register') ?>" class="text-decoration-none fw-semibold">Cadastre-se</a>
            </p>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
