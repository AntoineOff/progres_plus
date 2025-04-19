// DOM Elements
const navToggle = document.getElementById('navToggle');
const mainNav = document.getElementById('mainNav');
const loginContainer = document.getElementById('loginContainer');
const signupContainer = document.getElementById('signupContainer');
const forgotPasswordContainer = document.getElementById('forgotPasswordContainer');
const resetPasswordContainer = document.getElementById('resetPasswordContainer');
const switchToSignup = document.getElementById('switchToSignup');
const switchToLogin = document.getElementById('switchToLogin');
const forgotPassword = document.getElementById('forgotPassword');
const backToLogin = document.getElementById('backToLogin');
const loginForm = document.getElementById('loginForm');
const signupForm = document.getElementById('signupForm');
const forgotPasswordForm = document.getElementById('forgotPasswordForm');
const resetPasswordForm = document.getElementById('resetPasswordForm');
const loginError = document.getElementById('loginError');
const loginSuccess = document.getElementById('loginSuccess');
const signupError = document.getElementById('signupError');
const signupSuccess = document.getElementById('signupSuccess');
const forgotPasswordError = document.getElementById('forgotPasswordError');
const forgotPasswordSuccess = document.getElementById('forgotPasswordSuccess');
const resetPasswordError = document.getElementById('resetPasswordError');
const resetPasswordSuccess = document.getElementById('resetPasswordSuccess');

// Check if user is already logged in
if (localStorage.getItem('isLoggedIn') === 'true') {
    // Redirect to home page
    window.location.href = 'index.html';
}

// Check URL params to determine which form to show
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('reset_token')) {
    // Show reset password form
    showResetPasswordForm(urlParams.get('reset_token'));
} else if (urlParams.get('signup') === 'true') {
    showSignupForm();
} else {
    showLoginForm();
}

// Toggle mobile menu
navToggle.addEventListener('click', () => {
    mainNav.classList.toggle('active');
});

// Switch between forms
switchToSignup.addEventListener('click', (e) => {
    e.preventDefault();
    showSignupForm();
});

switchToLogin.addEventListener('click', (e) => {
    e.preventDefault();
    showLoginForm();
});

forgotPassword.addEventListener('click', (e) => {
    e.preventDefault();
    showForgotPasswordForm();
});

backToLogin.addEventListener('click', (e) => {
    e.preventDefault();
    showLoginForm();
});

// Form submissions
loginForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    
    // Send login request to PHP backend
    loginUser(email, password);
});

signupForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const firstName = document.getElementById('signupFirstName').value;
    const lastName = document.getElementById('signupLastName').value;
    const email = document.getElementById('signupEmail').value;
    const password = document.getElementById('signupPassword').value;
    const confirmPassword = document.getElementById('signupConfirmPassword').value;
    
    // Check if passwords match
    if (password !== confirmPassword) {
        showError(signupError, 'Les mots de passe ne correspondent pas.');
        return;
    }
    
    // Send signup request to PHP backend
    registerUser(firstName, lastName, email, password, confirmPassword);
});

forgotPasswordForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const email = document.getElementById('forgotPasswordEmail').value;
    
    // Send forgot password request to PHP backend
    sendPasswordResetEmail(email);
});

resetPasswordForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const token = document.getElementById('resetToken').value;
    const password = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmNewPassword').value;
    
    // Check if passwords match
    if (password !== confirmPassword) {
        showError(resetPasswordError, 'Les mots de passe ne correspondent pas.');
        return;
    }
    
    // Send reset password request to PHP backend
    resetPassword(token, password, confirmPassword);
});

// Helper functions
function showLoginForm() {
    loginContainer.style.display = 'block';
    signupContainer.style.display = 'none';
    forgotPasswordContainer.style.display = 'none';
    resetPasswordContainer.style.display = 'none';
    resetLoginForm();
}

function showSignupForm() {
    loginContainer.style.display = 'none';
    signupContainer.style.display = 'block';
    forgotPasswordContainer.style.display = 'none';
    resetPasswordContainer.style.display = 'none';
    resetSignupForm();
}

function showForgotPasswordForm() {
    loginContainer.style.display = 'none';
    signupContainer.style.display = 'none';
    forgotPasswordContainer.style.display = 'block';
    resetPasswordContainer.style.display = 'none';
    resetForgotPasswordForm();
}

function showResetPasswordForm(token) {
    loginContainer.style.display = 'none';
    signupContainer.style.display = 'none';
    forgotPasswordContainer.style.display = 'none';
    resetPasswordContainer.style.display = 'block';
    document.getElementById('resetToken').value = token;
}

function resetLoginForm() {
    loginForm.reset();
    loginError.style.display = 'none';
    loginSuccess.style.display = 'none';
}

function resetSignupForm() {
    signupForm.reset();
    signupError.style.display = 'none';
    signupSuccess.style.display = 'none';
}

function resetForgotPasswordForm() {
    forgotPasswordForm.reset();
    forgotPasswordError.style.display = 'none';
    forgotPasswordSuccess.style.display = 'none';
}

function showError(element, message) {
    element.textContent = message;
    element.style.display = 'block';
    setTimeout(() => {
        element.style.display = 'none';
    }, 5000);
}

function showSuccess(element, message) {
    element.textContent = message;
    element.style.display = 'block';
    setTimeout(() => {
        element.style.display = 'none';
    }, 3000);
}

// API Calls
function loginUser(email, password) {
    // Afficher un message de chargement
    showSuccess(loginSuccess, 'Connexion en cours...');
    
    // Préparer les données pour l'envoi
    const formData = new FormData();
    formData.append('email', email);
    formData.append('password', password);
    
    // Envoyer la requête au backend
    fetch('login_process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Connexion réussie
            showSuccess(loginSuccess, data.message);
            
            // Stocker les informations de l'utilisateur
            localStorage.setItem('user', JSON.stringify(data.user));
            localStorage.setItem('isLoggedIn', 'true');
            
            // Rediriger vers la page d'accueil après délai
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 1500);
        } else {
            // Erreur de connexion
            showError(loginError, data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showError(loginError, 'Une erreur est survenue. Veuillez réessayer plus tard.');
    });
}

function registerUser(firstName, lastName, email, password, confirmPassword) {
    // Afficher un message de chargement
    showSuccess(signupSuccess, 'Inscription en cours...');
    
    // Préparer les données pour l'envoi
    const formData = new FormData();
    formData.append('firstName', firstName);
    formData.append('lastName', lastName);
    formData.append('email', email);
    formData.append('password', password);
    formData.append('confirmPassword', confirmPassword);
    
    // Envoyer la requête au backend
    fetch('register_process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Inscription réussie
            showSuccess(signupSuccess, data.message);
            
            // Stocker les informations de l'utilisateur
            localStorage.setItem('user', JSON.stringify(data.user));
            localStorage.setItem('isLoggedIn', 'true');
            
            // Rediriger vers la page d'accueil après délai
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 1500);
        } else {
            // Erreur d'inscription
            showError(signupError, data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showError(signupError, 'Une erreur est survenue. Veuillez réessayer plus tard.');
    });
}

function sendPasswordResetEmail(email) {
    // Afficher un message de chargement
    showSuccess(forgotPasswordSuccess, 'Envoi en cours...');
    
    // Préparer les données pour l'envoi
    const formData = new FormData();
    formData.append('email', email);
    
    // Envoyer la requête au backend
    fetch('forgot_password.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Email envoyé avec succès
            showSuccess(forgotPasswordSuccess, data.message);
            
            // Réinitialiser le formulaire
            document.getElementById('forgotPasswordEmail').value = '';
        } else {
            // Erreur lors de l'envoi de l'email
            showError(forgotPasswordError, data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showError(forgotPasswordError, 'Une erreur est survenue. Veuillez réessayer plus tard.');
    });
}

function resetPassword(token, password, confirmPassword) {
    // Afficher un message de chargement
    showSuccess(resetPasswordSuccess, 'Réinitialisation en cours...');
    
    // Préparer les données pour l'envoi
    const formData = new FormData();
    formData.append('token', token);
    formData.append('password', password);
    formData.append('confirmPassword', confirmPassword);
    
    // Envoyer la requête au backend
    fetch('reset_password.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mot de passe réinitialisé avec succès
            showSuccess(resetPasswordSuccess, data.message);
            
            // Rediriger vers la page de connexion après délai
            setTimeout(() => {
                window.location.href = 'auth.html';
            }, 3000);
        } else {
            // Erreur lors de la réinitialisation
            showError(resetPasswordError, data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showError(resetPasswordError, 'Une erreur est survenue. Veuillez réessayer plus tard.');
    });
}