document.addEventListener('DOMContentLoaded', () => {
    const candidateCards = document.querySelectorAll('.candidate-card');
    candidateCards.forEach(card => {
        card.addEventListener('mouseover', () => {
            card.style.transform = 'translateY(-5px)';
            card.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.15)';
        });
        card.addEventListener('mouseout', () => {
            card.style.transform = 'translateY(0)';
            card.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.08)';
        });
    });

    const loginForm = document.querySelector('#loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            const username = loginForm.querySelector('input[name="username"]').value;
            const password = loginForm.querySelector('input[name="password"]').value;

            if (username.trim() === '' || password.trim() === '') {
                console.error('Username dan password harus diisi!');
            }
        });
    }

    const registerForm = document.querySelector('#registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            const namaLengkap = registerForm.querySelector('input[name="reg_nama_lengkap"]').value;
            const username = registerForm.querySelector('input[name="reg_username"]').value;
            const password = registerForm.querySelector('input[name="reg_password"]').value;

            if (namaLengkap.trim() === '' || username.trim() === '' || password.trim() === '') {
                console.error('Semua kolom registrasi harus diisi!');
            }
        });
    }
});
