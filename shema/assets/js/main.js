document.addEventListener('DOMContentLoaded', function () {
    // Mobile navigation toggle
    const navToggle = document.getElementById('navToggle');
    const mainNav = document.getElementById('mainNav');

    if (navToggle && mainNav) {
        navToggle.addEventListener('click', function () {
            mainNav.classList.toggle('open');
            navToggle.classList.toggle('active');
        });
    }

    // Quantity controls
    document.querySelectorAll('.quantity-controls').forEach(function (control) {
        const decreaseBtn = control.querySelector('[data-action="decrease"]');
        const increaseBtn = control.querySelector('[data-action="increase"]');
        const input = control.querySelector('input[type="number"]');
        const form = control.closest('form');

        if (!input) return;

        if (decreaseBtn) {
            decreaseBtn.addEventListener('click', function () {
                const min = parseInt(input.min) || 1;
                const current = parseInt(input.value) || 1;
                if (current > min) {
                    input.value = current - 1;
                    if (form && form.classList.contains('cart-qty-form')) {
                        form.submit();
                    }
                }
            });
        }

        if (increaseBtn) {
            increaseBtn.addEventListener('click', function () {
                const max = parseInt(input.max) || 999;
                const current = parseInt(input.value) || 1;
                if (current < max) {
                    input.value = current + 1;
                    if (form && form.classList.contains('cart-qty-form')) {
                        form.submit();
                    }
                }
            });
        }

        if (form && form.classList.contains('cart-qty-form')) {
            input.addEventListener('change', function () {
                form.submit();
            });
        }
    });

    // Auto-dismiss alerts after 5 seconds
    document.querySelectorAll('.alert').forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(function () { alert.remove(); }, 500);
        }, 5000);
    });
});
