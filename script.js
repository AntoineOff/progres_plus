// DOM Elements
const navToggle = document.getElementById('navToggle');
const mainNav = document.getElementById('mainNav');
const mainCTA = document.getElementById('mainCTA');
const authButtons = document.getElementById('authButtons');

// Check if user is logged in (from localStorage)
let isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
let user = JSON.parse(localStorage.getItem('user')) || {
    firstName: '',
    lastName: '',
    email: ''
};

// Initialize UI based on login status
initializeUI();

// Toggle mobile menu
navToggle.addEventListener('click', () => {
    mainNav.classList.toggle('active');
});

// CTA button click handler
mainCTA.addEventListener('click', () => {
    if (isLoggedIn) {
        // Redirect to courses page
        alert('Redirection vers les cours...');
    } else {
        // Redirect to signup page
        window.location.href = 'auth.html?signup=true';
    }
});

// Handle logout
function handleLogout() {
    localStorage.removeItem('isLoggedIn');
    localStorage.removeItem('user');
    isLoggedIn = false;
    initializeUI();
}

// Initialize UI based on login status
function initializeUI() {
    if (isLoggedIn && user) {
        // Update UI for logged in user
        authButtons.innerHTML = `
            <div style="display: flex; align-items: center;">
                <span style="margin-right: 10px;">Bonjour, ${user.firstName}</span>
                <div class="dropdown" style="position: relative;">
                    <button class="btn btn-outline">
                        <i class="fas fa-user-circle"></i> Mon Profil
                    </button>
                    <a href="#" class="btn btn-outline" id="logoutBtn" style="margin-left: 10px;">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </div>
        `;
        
        // Add event listener to logout button
        document.getElementById('logoutBtn').addEventListener('click', (e) => {
            e.preventDefault();
            handleLogout();
        });
    } else {
        // Show default auth buttons
        authButtons.innerHTML = `
            <a href="auth.html" class="btn btn-outline">Connexion</a>
            <a href="auth.html?signup=true" class="btn btn-primary">Inscription</a>
        `;
    }
}