import './styles/app.css';

// import JS files
import './js/dropdown-toggle.js';

// Sidenav
document.addEventListener('DOMContentLoaded', function () {
    let sidenavToggle = document.querySelector('[data-drawer-target]');
    let sidenav = sidenavToggle ? document.getElementById(sidenavToggle.dataset.drawerTarget) : null;
    if (sidenavToggle && sidenav) {
        sidenavToggle.addEventListener('click', function () {
            sidenav.classList.toggle('sm:translate-x-0');
            sidenav.classList.toggle('-translate-x-full');
        });
    }
});