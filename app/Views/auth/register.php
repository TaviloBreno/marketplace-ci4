<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?>Cadastro<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="auth-header">
    <h2><i class="bi bi-shop"></i> Marketplace</h2>
    <p>Crie sua conta gratuitamente</p>
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

    <form action="<?= url_to('register') ?>" method="post">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="username" class="form-label">Nome de usuário</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input type="text" class="form-control" id="username" name="username" 
                       placeholder="seunome" value="<?= old('username') ?>" required>
            </div>
        </div>

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
            <div class="form-text">Mínimo de 8 caracteres</div>
        </div>

        <div class="mb-4">
            <label for="password_confirm" class="form-label">Confirmar senha</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                <input type="password" class="form-control" id="password_confirm" name="password_confirm" 
                       placeholder="••••••••" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="bi bi-person-plus me-2"></i>Criar conta
        </button>
    </form>

    <div class="divider">
        <span>ou</span>
    </div>

    <div class="text-center">
        <p class="mb-0">Já tem uma conta? 
            <a href="<?= url_to('login') ?>" class="text-decoration-none fw-semibold">Faça login</a>
        </p>
    </div>
</div>
<?= $this->endSection() ?>
