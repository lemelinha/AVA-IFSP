const THEME_STORAGE_KEY = 'ava-theme';

function isDarkTheme() {
    return document.documentElement.classList.contains('dark');
}

function syncThemeToggles() {
    const dark = isDarkTheme();

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-pressed', String(dark));
        button.setAttribute('aria-label', dark ? 'Ativar modo claro' : 'Ativar modo escuro');

        button.querySelector('[data-theme-sun]')?.classList.toggle('hidden', !dark);
        button.querySelector('[data-theme-moon]')?.classList.toggle('hidden', dark);
    });
}

function setTheme(theme) {
    const dark = theme === 'dark';

    document.documentElement.classList.toggle('dark', dark);

    try {
        localStorage.setItem(THEME_STORAGE_KEY, theme);
    } catch {
        // localStorage indisponível (ex.: modo privado) — apenas aplica o tema na sessão.
    }

    syncThemeToggles();
}

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-theme-toggle]');

    if (button) {
        setTheme(isDarkTheme() ? 'light' : 'dark');
    }
});

syncThemeToggles();

// Mostrar/ocultar senha na página de login.
document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    const input = document.getElementById(button.getAttribute('aria-controls'));

    if (!input) {
        return;
    }

    button.addEventListener('click', () => {
        const visible = input.type === 'text';

        input.type = visible ? 'password' : 'text';
        button.setAttribute('aria-label', visible ? 'Mostrar senha' : 'Ocultar senha');

        button.querySelector('[data-password-eye]')?.classList.toggle('hidden', !visible);
        button.querySelector('[data-password-eye-off]')?.classList.toggle('hidden', visible);

        input.focus();
    });
});
