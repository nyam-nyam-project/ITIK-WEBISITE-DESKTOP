(() => {
    document.querySelectorAll('[data-password-toggle]').forEach(button => {
        const password = document.getElementById(button.dataset.passwordToggle);
        if (!password) return;

        button.addEventListener('click', function () {
            const showPassword = password.type === 'password';
            password.type = showPassword ? 'text' : 'password';
            button.textContent = showPassword
                ? button.dataset.passwordVisibleIcon
                : button.dataset.passwordHiddenIcon;
            button.setAttribute('aria-label', showPassword
                ? button.dataset.passwordVisibleLabel
                : button.dataset.passwordHiddenLabel);
        });
    });
})();
