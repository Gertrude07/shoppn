document.addEventListener('DOMContentLoaded', function () {
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    var phoneRegex = /^[0-9+\-\s]{7,15}$/;
    var passRegex = /^(?=.*\d).{8,}$/;

    function showError(id, message) {
        var el = document.getElementById(id);
        if (el) el.textContent = message;
    }

    function clearErrors(form) {
        var errorEls = form.querySelectorAll('.field-error');
        for (var i = 0; i < errorEls.length; i++) {
            errorEls[i].textContent = '';
        }
    }

    function setLoading(button) {
        if (button) {
            button.disabled = true;
            button.textContent = 'Please wait...';
        }
    }

    var registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            clearErrors(registerForm);
            var valid = true;

            var name = document.getElementById('name').value.trim();
            var email = document.getElementById('email').value.trim();
            var pass = document.getElementById('pass').value;
            var country = document.getElementById('country').value;
            var city = document.getElementById('city').value.trim();
            var contact = document.getElementById('contact').value.trim();

            if (name.length < 2) {
                showError('name-error', 'Name must be at least 2 characters.');
                valid = false;
            }
            if (!emailRegex.test(email)) {
                showError('email-error', 'Enter a valid email address.');
                valid = false;
            }
            if (!passRegex.test(pass)) {
                showError('pass-error', 'Password needs 8+ characters and a digit.');
                valid = false;
            }
            if (!country) {
                showError('country-error', 'Select a country.');
                valid = false;
            }
            if (!city) {
                showError('city-error', 'City is required.');
                valid = false;
            }
            if (!phoneRegex.test(contact)) {
                showError('contact-error', 'Enter a valid contact number.');
                valid = false;
            }

            if (!valid) {
                e.preventDefault();
            } else {
                setLoading(document.getElementById('register-submit'));
            }
        });
    }

    var loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            clearErrors(loginForm);
            var valid = true;

            var email = document.getElementById('email').value.trim();
            var pass = document.getElementById('pass').value;

            if (!emailRegex.test(email)) {
                showError('email-error', 'Enter a valid email address.');
                valid = false;
            }
            if (pass.length === 0) {
                showError('pass-error', 'Password is required.');
                valid = false;
            }

            if (!valid) {
                e.preventDefault();
            } else {
                setLoading(document.getElementById('login-submit'));
            }
        });
    }
});
