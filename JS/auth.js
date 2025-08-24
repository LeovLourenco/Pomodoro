const loginForm = document.getElementById('login-form');
const registerForm = document.getElementById('register-form');
const showRegisterLink = document.getElementById('show-register');
const showLoginLink = document.getElementById('show-login');
const loginContainer = document.getElementById('login-form-container');
const registerContainer = document.getElementById('register-form-container');
const messageDisplay = document.getElementById('message-display');

// URLs da API
const REGISTER_URL = 'orchid-termite-672668.hostingersite.com/register.php';
const LOGIN_URL = 'orchid-termite-672668.hostingersite.com/login.php';

showRegisterLink.addEventListener('click', (e) => {
    e.preventDefault();
    loginContainer.style.display = 'none';
    registerContainer.style.display = 'block';
});

showLoginLink.addEventListener('click', (e) => {
    e.preventDefault();
    loginContainer.style.display = 'block';
    registerContainer.style.display = 'none';
});

loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const username = document.getElementById('login-username').value;
    const password = document.getElementById('login-password').value;

    const response = await fetch(LOGIN_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
    });

    const data = await response.json();
    if (data.status === 'success') {
        // Armazenar o user_id e redirecionar
        localStorage.setItem('user_id', data.user_id);
        window.location.href = 'index.html'; // Redireciona para o timer
    } else {
        messageDisplay.textContent = data.error;
    }
});

registerForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const username = document.getElementById('register-username').value;
    const password = document.getElementById('register-password').value;

    const response = await fetch(REGISTER_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
    });

    const data = await response.json();
    if (data.status === 'success') {
        messageDisplay.textContent = 'Cadastro realizado com sucesso! Faça login.';
        loginContainer.style.display = 'block';
        registerContainer.style.display = 'none';
    } else {
        messageDisplay.textContent = data.error;
    }
});