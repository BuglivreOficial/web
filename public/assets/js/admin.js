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

    /* ---------- Categorias (CRUD via API, sem recarregar a grade) ---------- */
    var categoryTable = document.getElementById('categoryTable');

    if (categoryTable) {
        var CATEGORY_API = '/api/categories';

        var categoryModal = document.getElementById('categoryModal');
        var categoryForm = document.getElementById('categoryForm');
        var categoryBody = document.getElementById('categoryBody');
        var categoryAlert = document.getElementById('categoryFormAlert');
        var categoryTitle = document.getElementById('categoryModalTitle');
        var categoryNew = document.getElementById('categoryNew');
        var categoryProgress = document.getElementById('pageProgress');
        var pageAlert = document.getElementById('pageAlert');
        var pageSuccess = document.getElementById('pageSuccess');

        var idField = categoryForm.querySelector('[name="id"]');
        var nameField = categoryForm.querySelector('[name="name"]');
        var slugField = categoryForm.querySelector('[name="slug"]');
        var descriptionField = categoryForm.querySelector('[name="description"]');
        var statusField = categoryForm.querySelector('[name="status"]');
        var categorySubmit = categoryForm.querySelector('button[type="submit"]');

        var alertTimer = null;

        function slugify(text) {
            return String(text)
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        function setCategoryBusy(loading) {
            if (categoryProgress) categoryProgress.classList.toggle('is-active', loading);

            if (categorySubmit) {
                categorySubmit.disabled = loading;
                categorySubmit.classList.toggle('is-loading', loading);
            }

            categoryForm.querySelectorAll('input, textarea, button[type="button"]').forEach(function (el) {
                el.disabled = loading;
            });
        }

        function showFormError(message) {
            if (!categoryAlert) return;
            categoryAlert.textContent = message;
            categoryAlert.classList.add('is-visible');
        }

        function hideFormError() {
            if (categoryAlert) categoryAlert.classList.remove('is-visible');

            categoryForm.querySelectorAll('.field--invalid').forEach(function (field) {
                field.classList.remove('field--invalid');
            });
            categoryForm.querySelectorAll('.field__error').forEach(function (box) {
                box.textContent = '';
            });
        }

        function flash(element, message, timeout) {
            if (!element) return;

            if (alertTimer) clearTimeout(alertTimer);
            if (pageAlert) pageAlert.classList.remove('is-visible');
            if (pageSuccess) pageSuccess.classList.remove('is-visible');

            element.textContent = message;
            element.classList.add('is-visible');

            if (timeout) {
                alertTimer = setTimeout(function () {
                    element.classList.remove('is-visible');
                }, timeout);
            }
        }

        function openModal(category) {
            categoryForm.reset();
            hideFormError();

            if (category) {
                categoryTitle.textContent = 'Editar categoria';
                idField.value = category.id;
                nameField.value = category.name;
                slugField.value = category.slug;
                slugField.dataset.manual = '1';
                descriptionField.value = category.description || '';
                statusField.checked = String(category.status) === '1';
            } else {
                categoryTitle.textContent = 'Nova categoria';
                idField.value = '';
                delete slugField.dataset.manual;
                statusField.checked = true;
            }

            categoryModal.classList.add('is-open');
            categoryModal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('no-scroll');

            setTimeout(function () { nameField.focus(); }, 60);
        }

        function closeModal() {
            categoryModal.classList.remove('is-open');
            categoryModal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('no-scroll');
        }

        function readRow(row) {
            return {
                id: row.dataset.id,
                name: row.dataset.name,
                slug: row.dataset.slug,
                description: row.dataset.description,
                status: row.dataset.status
            };
        }

        function applyRow(row, category) {
            row.dataset.id = category.id;
            row.dataset.name = category.name;
            row.dataset.slug = category.slug;
            row.dataset.description = category.description || '';
            row.dataset.status = category.status;

            row.querySelector('[data-field="name"]').textContent = category.name;
            row.querySelector('[data-field="slug"]').textContent = category.slug;
            row.querySelector('[data-field="description"]').textContent = category.description || '—';
            row.querySelector('[data-field="status"]').innerHTML = Number(category.status) === 1
                ? '<span class="badge badge--success">Ativa</span>'
                : '<span class="badge badge--danger">Inativa</span>';
        }

        function makeRow(category) {
            var template = document.getElementById('categoryRowTemplate');
            var row = template.content.firstElementChild.cloneNode(true);
            applyRow(row, category);

            return row;
        }

        function ensureEmptyState() {
            if (categoryBody.querySelector('tr[data-id]') || categoryBody.querySelector('.table__empty')) return;

            var row = document.createElement('tr');
            row.className = 'table__empty';
            row.innerHTML = '<td colspan="5">Nenhuma categoria cadastrada. Clique em "Nova categoria" para começar.</td>';
            categoryBody.appendChild(row);
        }

        function findRow(id) {
            return categoryBody.querySelector('tr[data-id="' + id + '"]');
        }

        function handleUnauthorized(status) {
            if (status !== 401) return false;

            window.location.href = '/admin/auth';
            return true;
        }

        if (categoryNew) {
            categoryNew.addEventListener('click', function () { openModal(null); });
        }

        categoryModal.querySelectorAll('[data-close]').forEach(function (el) {
            el.addEventListener('click', closeModal);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && categoryModal.classList.contains('is-open')) closeModal();
        });

        // Slug gerado automaticamente a partir do nome até ser editado manualmente.
        nameField.addEventListener('input', function () {
            if (slugField.dataset.manual) return;
            slugField.value = slugify(nameField.value);
        });

        slugField.addEventListener('input', function () {
            slugField.dataset.manual = '1';
        });

        categoryForm.addEventListener('submit', function (e) {
            e.preventDefault();
            hideFormError();

            if (!validateForm(categoryForm)) return;

            var id = idField.value;
            var payload = {
                name: nameField.value.trim(),
                slug: slugField.value.trim(),
                description: descriptionField.value.trim(),
                status: statusField.checked ? 1 : 0
            };

            setCategoryBusy(true);

            fetch(id ? CATEGORY_API + '/' + id : CATEGORY_API, {
                method: id ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            })
                .then(function (res) {
                    return res.json().then(function (data) {
                        return { status: res.status, ok: res.ok, data: data };
                    });
                })
                .then(function (result) {
                    setCategoryBusy(false);

                    if (handleUnauthorized(result.status)) return;

                    var data = result.data || {};

                    if (!result.ok) {
                        showFormError(data.message || 'Não foi possível salvar a categoria.');
                        return;
                    }

                    var row = id ? findRow(id) : null;

                    if (row) {
                        applyRow(row, data.category);
                    } else {
                        var empty = categoryBody.querySelector('.table__empty');
                        if (empty) empty.remove();
                        categoryBody.appendChild(makeRow(data.category));
                    }

                    closeModal();
                    flash(pageSuccess, data.message || 'Categoria salva com sucesso.', 4000);
                })
                .catch(function () {
                    setCategoryBusy(false);
                    showFormError('Falha de conexão. Tente novamente em instantes.');
                });
        });

        categoryBody.addEventListener('click', function (e) {
            var button = e.target.closest ? e.target.closest('[data-action]') : null;
            if (!button) return;

            var row = button.closest('tr');
            if (!row) return;

            var category = readRow(row);

            if (button.dataset.action === 'edit') {
                openModal(category);
                return;
            }

            if (button.dataset.action === 'delete') {
                if (!window.confirm('Remover a categoria "' + category.name + '"? Esta ação não pode ser desfeita.')) return;

                row.classList.add('is-busy');

                fetch(CATEGORY_API + '/' + category.id, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json' }
                })
                    .then(function (res) {
                        return res.json().then(function (data) {
                            return { status: res.status, ok: res.ok, data: data };
                        });
                    })
                    .then(function (result) {
                        if (handleUnauthorized(result.status)) return;

                        if (!result.ok) {
                            row.classList.remove('is-busy');
                            flash(pageAlert, (result.data && result.data.message) || 'Não foi possível remover a categoria.');
                            return;
                        }

                        row.remove();
                        ensureEmptyState();
                        flash(pageSuccess, (result.data && result.data.message) || 'Categoria removida com sucesso.', 4000);
                    })
                    .catch(function () {
                        row.classList.remove('is-busy');
                        flash(pageAlert, 'Falha de conexão. Tente novamente em instantes.');
                    });
            }
        });
    }

    /* ---------- Ano dinâmico ---------- */
    document.querySelectorAll('#year').forEach(function (el) {
        el.textContent = new Date().getFullYear();
    });
})();
