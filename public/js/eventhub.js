/**
 * EventHub - Utilitários JavaScript
 * 
 * Funções auxiliares para CSRF, Loading States, Notificações e AJAX
 */

const EventHub = {
    /**
     * Token CSRF atual
     */
    csrfToken: null,
    csrfTokenName: null,

    /**
     * Inicializa o módulo
     */
    init() {
        this.csrfTokenName = document.querySelector('meta[name="csrf-token-name"]')?.content || 'csrf_test_name';
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        
        // Interceptar responses para atualizar CSRF token
        this.setupAjaxInterceptor();
        
        // Configurar loading states em formulários
        this.setupFormLoadingStates();
        
        // Configurar botões com loading
        this.setupButtonLoadingStates();
    },

    /**
     * Configurar interceptor para atualizar CSRF token
     */
    setupAjaxInterceptor() {
        const originalFetch = window.fetch;
        const self = this;

        window.fetch = async function(...args) {
            const response = await originalFetch.apply(this, args);
            
            // Atualizar CSRF token do header se presente
            const newToken = response.headers.get('X-CSRF-TOKEN');
            if (newToken) {
                self.csrfToken = newToken;
                self.updateCsrfMetaTag(newToken);
            }
            
            return response;
        };
    },

    /**
     * Atualizar meta tag do CSRF
     */
    updateCsrfMetaTag(token) {
        let meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) {
            meta.content = token;
        }
    },

    /**
     * Fazer requisição AJAX com CSRF
     */
    async ajax(url, options = {}) {
        const defaults = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.csrfToken
            }
        };

        const config = { ...defaults, ...options };
        
        // Merge headers
        if (options.headers) {
            config.headers = { ...defaults.headers, ...options.headers };
        }

        try {
            const response = await fetch(url, config);
            
            // Verificar erro de CSRF
            if (response.status === 403) {
                const data = await response.json();
                if (data.csrf_error) {
                    this.showNotification('Sessão expirada. Recarregando página...', 'warning');
                    setTimeout(() => window.location.reload(), 1500);
                    throw new Error('CSRF token expired');
                }
            }

            // Atualizar token do header
            const newToken = response.headers.get('X-CSRF-TOKEN');
            if (newToken) {
                this.csrfToken = newToken;
                this.updateCsrfMetaTag(newToken);
            }

            return response;
        } catch (error) {
            console.error('AJAX Error:', error);
            throw error;
        }
    },

    /**
     * POST request simplificado
     */
    async post(url, data = {}) {
        const response = await this.ajax(url, {
            method: 'POST',
            body: JSON.stringify(data)
        });
        return response.json();
    },

    /**
     * GET request simplificado
     */
    async get(url) {
        const response = await this.ajax(url, {
            method: 'GET'
        });
        return response.json();
    },

    /**
     * Configurar loading states em formulários
     */
    setupFormLoadingStates() {
        document.querySelectorAll('form[data-loading]').forEach(form => {
            form.addEventListener('submit', (e) => {
                const submitBtn = form.querySelector('[type="submit"]');
                if (submitBtn) {
                    this.setButtonLoading(submitBtn, true);
                }
            });
        });
    },

    /**
     * Configurar botões com loading automático
     */
    setupButtonLoadingStates() {
        document.querySelectorAll('[data-loading-text]').forEach(btn => {
            btn.addEventListener('click', () => {
                if (!btn.disabled) {
                    this.setButtonLoading(btn, true);
                }
            });
        });
    },

    /**
     * Definir estado de loading em um botão
     */
    setButtonLoading(button, isLoading) {
        if (isLoading) {
            button.dataset.originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                ${button.dataset.loadingText || 'Processando...'}
            `;
        } else {
            button.disabled = false;
            button.innerHTML = button.dataset.originalText || button.innerHTML;
        }
    },

    /**
     * Mostrar notificação toast
     */
    showNotification(message, type = 'info', duration = 5000) {
        // Criar container se não existir
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }

        // Mapear tipos para classes Bootstrap
        const typeClasses = {
            'success': 'bg-success text-white',
            'error': 'bg-danger text-white',
            'warning': 'bg-warning text-dark',
            'info': 'bg-info text-white'
        };

        // Mapear tipos para ícones
        const typeIcons = {
            'success': 'bi-check-circle-fill',
            'error': 'bi-exclamation-triangle-fill',
            'warning': 'bi-exclamation-circle-fill',
            'info': 'bi-info-circle-fill'
        };

        const toastId = 'toast-' + Date.now();
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center ${typeClasses[type] || typeClasses.info}" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi ${typeIcons[type] || typeIcons.info} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', toastHtml);
        
        const toastEl = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastEl, { delay: duration });
        toast.show();

        // Remover do DOM após esconder
        toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
        });

        return toast;
    },

    /**
     * Mostrar modal de confirmação
     */
    confirm(message, options = {}) {
        return new Promise((resolve) => {
            const defaults = {
                title: 'Confirmação',
                confirmText: 'Confirmar',
                cancelText: 'Cancelar',
                confirmClass: 'btn-primary',
                cancelClass: 'btn-secondary'
            };

            const config = { ...defaults, ...options };
            const modalId = 'confirm-modal-' + Date.now();

            const modalHtml = `
                <div class="modal fade" id="${modalId}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">${config.title}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>${message}</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn ${config.cancelClass}" data-bs-dismiss="modal">${config.cancelText}</button>
                                <button type="button" class="btn ${config.confirmClass}" id="${modalId}-confirm">${config.confirmText}</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            const modalEl = document.getElementById(modalId);
            const modal = new bootstrap.Modal(modalEl);
            
            document.getElementById(`${modalId}-confirm`).addEventListener('click', () => {
                modal.hide();
                resolve(true);
            });

            modalEl.addEventListener('hidden.bs.modal', () => {
                modalEl.remove();
                resolve(false);
            });

            modal.show();
        });
    },

    /**
     * Formatar moeda
     */
    formatCurrency(value) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value);
    },

    /**
     * Formatar data
     */
    formatDate(dateString, options = {}) {
        const defaults = {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        };
        
        return new Date(dateString).toLocaleDateString('pt-BR', { ...defaults, ...options });
    },

    /**
     * Formatar data e hora
     */
    formatDateTime(dateString) {
        return new Date(dateString).toLocaleString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
};

// Inicializar quando DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    EventHub.init();
});

// Exportar para uso global
window.EventHub = EventHub;
