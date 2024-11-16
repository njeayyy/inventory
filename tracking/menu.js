document.addEventListener('DOMContentLoaded', function() {
    const menuButton = document.querySelector('.settings');
    const dropdownMenu = document.querySelector('.dropdown-menu');

    // Ensure the dropdown menu is hidden initially
    dropdownMenu.style.display = 'none';

    // Toggle visibility of the dropdown menu when the button is clicked
    menuButton.addEventListener('click', function(event) {
        event.stopPropagation();  // Prevent the click event from bubbling
        if (dropdownMenu.style.display === 'none' || dropdownMenu.style.display === '') {
            dropdownMenu.style.display = 'block'; // Show menu
        } else {
            dropdownMenu.style.display = 'none'; // Hide menu
        }
    });

    // Close the dropdown if the user clicks outside of it
    document.addEventListener('click', function(event) {
        if (!menuButton.contains(event.target)) {
            dropdownMenu.style.display = 'none';
        }
    });
});
