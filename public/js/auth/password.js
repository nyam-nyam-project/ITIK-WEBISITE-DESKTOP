(() => {
    document.querySelectorAll('[data-password-toggle]').forEach(button => {
        const password = document.getElementById(button.dataset.passwordToggle);
        if (!password) return;

        button.addEventListener('click', function () {
            const showPassword = password.type === 'password';
            password.type = showPassword ? 'text' : 'password';
            button.querySelectorAll('[data-password-icon]').forEach(icon => {
                icon.toggleAttribute('hidden', icon.dataset.passwordIcon === 'hidden' ? showPassword : !showPassword);
            });
            button.setAttribute('aria-pressed', String(showPassword));
            button.setAttribute('aria-label', showPassword
                ? button.dataset.passwordVisibleLabel
                : button.dataset.passwordHiddenLabel);
        });
    });
})();
