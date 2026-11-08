function bukaModalPassword() {
    document.getElementById('modalPassword').classList.add('active');
}

function tutupModalPassword() {
    document.getElementById('modalPassword').classList.remove('active');
}

document.getElementById('buka-password').addEventListener('click', bukaModalPassword);

document.querySelectorAll('[data-close-password]').forEach(function(button) {
    button.addEventListener('click', tutupModalPassword);
});

document.getElementById('modalPassword').addEventListener('click', function(e) {
    if (e.target === this) {
        tutupModalPassword();
    }
});

if (document.getElementById('modalPassword').dataset.passwordErrors === '1') {
    bukaModalPassword();
}
