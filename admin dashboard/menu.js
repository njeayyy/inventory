// menu.js

function toggleDropdown() {
    // Select the dropdown menu element
    const dropdownMenu = document.querySelector('.dropdown-menu');

    // Toggle the visibility of the dropdown
    if (dropdownMenu) {
        dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
    }
}
