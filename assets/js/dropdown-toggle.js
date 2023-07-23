document.addEventListener('DOMContentLoaded', function () {
    let dropdownElements = document.querySelectorAll('[data-dropdown-toggle]');
    dropdownElements.forEach(function (dropdownElement) {
        dropdownElement.addEventListener('click', function () {
            let dropdownTarget = document.getElementById(dropdownElement.dataset.dropdownToggle);
            if (dropdownTarget) {

                // Add class to dropdown target
                dropdownTarget.classList.add('absolute');

                // Hide all dropdowns
                dropdownElements.forEach(function (element) {
                    let target = document.getElementById(element.dataset.dropdownToggle);
                    if (target != dropdownTarget) {
                        target.classList.add('hidden');
                    }
                });

                // Toggle dropdown
                dropdownTarget.classList.toggle('hidden');
                dropdownTarget.style.top = dropdownElement.offsetTop + dropdownElement.offsetHeight + 'px';
            }
        });
    });
});