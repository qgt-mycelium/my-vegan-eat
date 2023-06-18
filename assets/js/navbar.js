// Burger menus
document.addEventListener('DOMContentLoaded', function() {
    const burger = document.getElementById('navbar-burger');
    const menu = document.getElementById('navbar-menu');

    burger.addEventListener('click', function() {
        menu.classList.toggle('hidden');
    });
});