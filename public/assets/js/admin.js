/* ==========================================================================
   Manga Admin — Scripts do painel
   Interações: tema (dark/light), sidebar, menu do usuário, senha.
   Cada bloco só é ativado se o elemento correspondente existir na página.
   ========================================================================== */
(function () {
    'use strict';

    var root = document.documentElement;

    /* ---------- Tema (dark / light) ---------- */
    function applyTheme(theme) {
        root.setAttribute('data-theme', theme);
        try { localStorage.setItem('admin-theme', theme); } catch (e) {}
    }

    document.querySelectorAll('#themeToggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var current = root.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
            applyTheme(current === 'light' ? 'dark' : 'light');
        });
    });

    /* ---------- Sidebar (recolher / off-canvas) ---------- */
    var app = document.getElementById('app');
    var sidebarToggle = document.getElementById('sidebarToggle');
    var backdrop = document.getElementById('sidebarBackdrop');

    function isMobile() {
        return window.matchMedia('(max-width: 1024px)').matches;
    }

    function closeSidebar() {
        if (app) app.classList.remove('is-open');
    }

    if (sidebarToggle && app) {
        sidebarToggle.addEventListener('click', function () {
            if (isMobile()) {
                app.classList.toggle('is-open');
            } else {
                app.classList.toggle('is-collapsed');
            }
        });
    }

    if (backdrop) backdrop.addEventListener('click', closeSidebar);

    /* ---------- Menu do usuário ---------- */
    var userMenu = document.getElementById('userMenu');
    var userBtn = userMenu ? userMenu.querySelector('.user__btn') : null;

    function closeUserMenu() {
        if (userMenu) userMenu.classList.remove('is-open');
        if (userBtn) userBtn.setAttribute('aria-expanded', 'false');
    }

    if (userMenu && userBtn) {
        userBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            var open = userMenu.classList.toggle('is-open');
            userBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        document.addEventListener('click', function (e) {
            if (!userMenu.contains(e.target)) closeUserMenu();
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeSidebar();
            closeUserMenu();
        }
    });

    /* ---------- Mostrar / ocultar senha ---------- */
    var pwdToggle = document.getElementById('passwordToggle');
    var pwd = document.getElementById('password');

    if (pwdToggle && pwd) {
        pwdToggle.addEventListener('click', function () {
            var show = pwd.type === 'password';
            pwd.type = show ? 'text' : 'password';
            pwdToggle.classList.toggle('is-visible', show);
            pwdToggle.setAttribute('aria-label', show ? 'Ocultar senha' : 'Mostrar senha');
        });
    }

    /* ---------- Validação de formulários (declarativa via data-rules) ---------- */
    var RULES = {
        required: function (value) { return value.trim() !== ''; },
        min: function (value, size) { return value.trim().length >= size; },
        email: function (value) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim()); }
    };

    function ruleMessage(rule, param) {
        if (rule === 'required') return 'Campo obrigatório.';
        if (rule === 'min') return 'Use no mínimo ' + param + ' caracteres.';
        if (rule === 'email') return 'Informe um e-mail válido.';
        return 'Valor inválido.';
    }

    function fieldOf(input) {
        return input.closest ? input.closest('.field') : null;
    }

    /**
     * Valida um campo conforme data-rules (ex.: "required|min:3") e reflete o
     * resultado na interface. Retorna true quando o valor é válido.
     */
    function validateInput(input) {
        var rules = (input.getAttribute('data-rules') || '').split('|');
        var value = input.value;
        var message = '';
        var field = fieldOf(input);
        var errorBox = field ? field.querySelector('.field__error') : null;

        for (var i = 0; i < rules.length && message === ''; i++) {
            var parts = rules[i].split(':');
            var rule = parts[0];
            var param = parts.length > 1 ? parts[1] : null;
            var check = RULES[rule];

            if (check && !check(value, param)) {
                message = ruleMessage(rule, param);
            }
        }

        if (field) field.classList.toggle('field--invalid', message !== '');
        if (errorBox) errorBox.textContent = message;
        input.setAttribute('aria-invalid', message === '' ? 'false' : 'true');

        return message === '';
    }

    function validateForm(form) {
        var invalid = null;

        form.querySelectorAll('[data-rules]').forEach(function (input) {
            if (!validateInput(input) && !invalid) invalid = input;
        });

        if (invalid) invalid.focus();

        return invalid === null;
    }

    document.querySelectorAll('[data-rules]').forEach(function (input) {
        input.addEventListener('blur', function () { validateInput(input); });
        input.addEventListener('input', function () {
            var field = fieldOf(input);
            if (field && field.classList.contains('field--invalid')) validateInput(input);
        });
    });

    /* ---------- Login (integração com a API) ---------- */
    var loginForm = document.getElementById('loginForm');

    if (loginForm) {
        var loginAlert = document.getElementById('loginAlert');
        var loginProgress = document.getElementById('loginProgress');
        var submitBtn = loginForm.querySelector('button[type="submit"]');

        function showLoginError(message) {
            if (!loginAlert) return;
            loginAlert.textContent = message;
            loginAlert.classList.add('is-visible');
        }

        /**
         * Liga/desliga o feedback de carregamento: barra de progresso,
         * spinner no botão e bloqueio dos campos durante a requisição.
         */
        function setLoginLoading(loading) {
            loginForm.setAttribute('aria-busy', loading ? 'true' : 'false');

            if (loginProgress) loginProgress.classList.toggle('is-active', loading);

            if (submitBtn) {
                submitBtn.disabled = loading;
                submitBtn.classList.toggle('is-loading', loading);
            }

            loginForm.querySelectorAll('input, .field__action').forEach(function (el) {
                el.disabled = loading;
            });
        }

        loginForm.addEventListener('input', function () {
            if (loginAlert) loginAlert.classList.remove('is-visible');
        });

        loginForm.addEventListener('submit', function (e) {
            e.preventDefault();

            if (loginAlert) loginAlert.classList.remove('is-visible');

            if (!validateForm(loginForm)) return;

            var payload = {
                username: loginForm.username.value.trim(),
                password: loginForm.password.value,
                remember: loginForm.remember ? loginForm.remember.checked : false
            };

            setLoginLoading(true);

            fetch('/api/auth/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            })
                .then(function (res) {
                    return res.json().then(function (data) {
                        return { ok: res.ok, data: data };
                    });
                })
                .then(function (result) {
                    var data = result.data || {};

                    if (result.ok && data.panel) {
                        // Mantém o carregamento ativo até a navegação concluir.
                        window.location.href = '/admin';
                        return;
                    }

                    setLoginLoading(false);

                    if (result.ok && data.panel === false) {
                        showLoginError('Sua conta não tem permissão para acessar o painel administrativo.');
                        return;
                    }

                    showLoginError(
                        data.message
                            ? data.message
                            : 'Não foi possível entrar. Verifique suas credenciais.'
                    );
                })
                .catch(function () {
                    setLoginLoading(false);
                    showLoginError('Falha de conexão. Tente novamente em instantes.');
                });
        });
    }

    /* ---------- Ano dinâmico ---------- */
    document.querySelectorAll('#year').forEach(function (el) {
        el.textContent = new Date().getFullYear();
    });
})();
