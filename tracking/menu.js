document.addEventListener('DOMContentLoaded', function() {
    const menuButton = document.querySelector('.settings');
    const dropdownMenu = document.createElement('div');
    dropdownMenu.classList.add('dropdown-menu');

    dropdownMenu.innerHTML = `
        <ul>
            <li><a href="admin dashboard/dashboard.html">Inventory Management System</a></li>
            <li><a href="tracking.html">Tracking Map</a></li>
        </ul>
    `;

    // No more inline styles, just append the menu
    menuButton.appendChild(dropdownMenu);

    // Toggle dropdown visibility on click
    menuButton.addEventListener('click', function() {
        dropdownMenu.style.display = dropdownMenu.style.display === 'none' || dropdownMenu.style.display === '' ? 'block' : 'none';
    });

    // Hide the dropdown if the user clicks outside the menu
    document.addEventListener('click', function(event) {
        if (!menuButton.contains(event.target)) {
            dropdownMenu.style.display = 'none';
        }
    });
});
