// Selecciona los elementos del DOM
const openLogin = document.getElementById('open-login');
const closeLogin = document.getElementById('close-login');
const loginModal = document.getElementById('login-modal');

const openRegister = document.getElementById('open-register');
const closeRegister = document.getElementById('close-register');
const registerModal = document.getElementById('register-modal');
const registerForm = document.querySelector('.register-form');

// Abre el modal de login
openLogin.addEventListener('click', (e) => {
    e.preventDefault();
    loginModal.style.display = 'flex';
});

// Cierra el modal de login
closeLogin.addEventListener('click', () => {
    loginModal.style.display = 'none';
});

// Abre el modal de registro
openRegister.addEventListener('click', (e) => {
    e.preventDefault();
    loginModal.style.display = 'none'; // Cierra el modal de login
    registerModal.style.display = 'flex';
});

// Cierra el modal de registro
closeRegister.addEventListener('click', () => {
    registerModal.style.display = 'none';
});

// Cierra los modales al hacer clic fuera del contenido
window.addEventListener('click', (e) => {
    if (e.target === loginModal) {
        loginModal.style.display = 'none';
    }
    if (e.target === registerModal) {
        registerModal.style.display = 'none';
    }
});

// Maneja el envío del formulario de registro
registerForm.addEventListener('submit', (e) => {
    e.preventDefault(); // Evita el envío automático del formulario

    // Obtén los valores de los campos del formulario
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const meterId = document.getElementById('meter-id').value;

    // Simula el guardado de datos en localStorage
    const userData = {
        name,
        email,
        password,
        meterId,
    };

    // Guarda los datos en localStorage
    localStorage.setItem('user', JSON.stringify(userData));

    // Muestra un mensaje de éxito
    alert('¡Registro exitoso! Ahora puedes iniciar sesión.');

    // Cierra el modal de registro
    registerModal.style.display = 'none';

    // Limpia el formulario
    registerForm.reset();
});