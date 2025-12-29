<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Seja um Organizador<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="bi bi-calendar-event-fill text-primary" style="font-size: 4rem;"></i>
                        </div>
                        <h1 class="h2 mb-3">Seja um Organizador de Eventos</h1>
                        <p class="text-muted">
                            Crie e gerencie seus próprios eventos, venda ingressos e receba pagamentos 
                            diretamente em sua conta bancária através da nossa parceria com o Stripe.
                        </p>
                    </div>

                    <?php if (session('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?= session('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row g-4 mb-5">
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 p-3 d-inline-block mb-3">
                                    <i class="bi bi-calendar-plus text-primary" style="font-size: 2rem;"></i>
                                </div>
                                <h5>Crie Eventos</h5>
                                <p class="text-muted small">Cadastre shows, festas, teatros, esportes e muito mais</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <div class="rounded-circle bg-success bg-opacity-10 p-3 d-inline-block mb-3">
                                    <i class="bi bi-ticket-perforated text-success" style="font-size: 2rem;"></i>
                                </div>
                                <h5>Venda Ingressos</h5>
                                <p class="text-muted small">Sistema completo com mapa de assentos interativo</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <div class="rounded-circle bg-info bg-opacity-10 p-3 d-inline-block mb-3">
                                    <i class="bi bi-cash-stack text-info" style="font-size: 2rem;"></i>
                                </div>
                                <h5>Receba Pagamentos</h5>
                                <p class="text-muted small">Transferência automática para sua conta via Stripe</p>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h4 class="mb-4">Cadastre suas informações</h4>

                    <form action="<?= url_to('organizer.register') ?>" method="post">
                        <?= csrf_field() ?>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nome da Empresa / Produtor *</label>
                                <input type="text" class="form-control" name="company_name" 
                                       value="<?= old('company_name') ?>" required>
                                <div class="form-text">Nome que aparecerá nos ingressos</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">CPF / CNPJ *</label>
                                <input type="text" class="form-control" name="document" 
                                       value="<?= old('document') ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Telefone *</label>
                                <input type="tel" class="form-control" name="phone" 
                                       value="<?= old('phone') ?>" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Endereço *</label>
                                <input type="text" class="form-control" name="address" 
                                       value="<?= old('address') ?>" required>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label">Cidade *</label>
                                <input type="text" class="form-control" name="city" 
                                       value="<?= old('city') ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Estado *</label>
                                <select class="form-select" name="state" required>
                                    <option value="">Selecione...</option>
                                    <option value="AC">Acre</option>
                                    <option value="AL">Alagoas</option>
                                    <option value="AP">Amapá</option>
                                    <option value="AM">Amazonas</option>
                                    <option value="BA">Bahia</option>
                                    <option value="CE">Ceará</option>
                                    <option value="DF">Distrito Federal</option>
                                    <option value="ES">Espírito Santo</option>
                                    <option value="GO">Goiás</option>
                                    <option value="MA">Maranhão</option>
                                    <option value="MT">Mato Grosso</option>
                                    <option value="MS">Mato Grosso do Sul</option>
                                    <option value="MG">Minas Gerais</option>
                                    <option value="PA">Pará</option>
                                    <option value="PB">Paraíba</option>
                                    <option value="PR">Paraná</option>
                                    <option value="PE">Pernambuco</option>
                                    <option value="PI">Piauí</option>
                                    <option value="RJ">Rio de Janeiro</option>
                                    <option value="RN">Rio Grande do Norte</option>
                                    <option value="RS">Rio Grande do Sul</option>
                                    <option value="RO">Rondônia</option>
                                    <option value="RR">Roraima</option>
                                    <option value="SC">Santa Catarina</option>
                                    <option value="SP">São Paulo</option>
                                    <option value="SE">Sergipe</option>
                                    <option value="TO">Tocantins</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">CEP *</label>
                                <input type="text" class="form-control" name="zip_code" 
                                       value="<?= old('zip_code') ?>" required>
                            </div>
                        </div>

                        <div class="alert alert-info mt-4">
                            <i class="bi bi-info-circle me-2"></i>
                            Ao continuar, você será redirecionado para o Stripe para completar seu cadastro 
                            e vincular sua conta bancária.
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="terms" required>
                            <label class="form-check-label" for="terms">
                                Li e aceito os <a href="#" target="_blank">Termos de Uso</a> e 
                                <a href="#" target="_blank">Política de Privacidade</a>
                            </label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-arrow-right-circle me-2"></i>
                                Continuar para o Stripe
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
// Máscara para CPF/CNPJ
document.querySelector('[name="document"]').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    } else {
        value = value.replace(/^(\d{2})(\d)/, '$1.$2');
        value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
        value = value.replace(/(\d{4})(\d)/, '$1-$2');
    }
    e.target.value = value;
});

// Máscara para telefone
document.querySelector('[name="phone"]').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 10) {
        value = value.replace(/(\d{2})(\d)/, '($1) $2');
        value = value.replace(/(\d{4})(\d)/, '$1-$2');
    } else {
        value = value.replace(/(\d{2})(\d)/, '($1) $2');
        value = value.replace(/(\d{5})(\d)/, '$1-$2');
    }
    e.target.value = value;
});

// Máscara para CEP
document.querySelector('[name="zip_code"]').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/(\d{5})(\d)/, '$1-$2');
    e.target.value = value;
});
</script>
<?= $this->endSection() ?>
