// Importez uniquement les styles et autres ressources nécessaires
import './styles/app.css';
import './styles/first_page.scss';

// Log when app is loaded
console.log('Application assets loaded successfully');

// Initialisation simple sans Stimulus
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded');
    
    // Vous pouvez ajouter ici du code JavaScript simple pour remplacer les fonctionnalités Stimulus
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', function(event) {
            console.log('Button clicked:', this.textContent);
        });
    });
});

