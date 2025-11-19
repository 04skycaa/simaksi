document.addEventListener('DOMContentLoaded', () => {
        const passwordInput = document.getElementById('password');
        const toggleButton = document.querySelector('.toggle-password');
        const eyeOpen = document.getElementById('eye-open');
        const eyeClose = document.getElementById('eye-close');

        if (toggleButton && passwordInput && eyeOpen && eyeClose) {
            toggleButton.addEventListener('click', () => {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                if (type === 'text') {
                    eyeOpen.style.display = 'none';
                    eyeClose.style.display = 'block';
                } else {
                    eyeOpen.style.display = 'block';
                    eyeClose.style.display = 'none';
                }
            });

            toggleButton.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    toggleButton.click();
                }
            });
        }
    });