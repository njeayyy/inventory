document.addEventListener('DOMContentLoaded', function() {
    const menuButton = document.querySelector('.settings');
    const dropdownMenu = document.createElement('div');
    dropdownMenu.classList.add('dropdown-menu');

    dropdownMenu.innerHTML = `
        <ul>
            <li><a href="dashboard.html">Go to Inventory Page</a></li>
            <li><a href="tracking.html">Go to Tracking</a></li>
        </ul>
    `;

    dropdownMenu.style.position = 'absolute';
    dropdownMenu.style.background = '#fff';
    dropdownMenu.style.border = '1px solid #ddd';
    dropdownMenu.style.padding = '10px';
    dropdownMenu.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
    dropdownMenu.style.display = 'none';

    menuButton.appendChild(dropdownMenu);

    menuButton.addEventListener('click', function() {
        dropdownMenu.style.display = dropdownMenu.style.display === 'none' || dropdownMenu.style.display === '' ? 'block' : 'none';
    });

    document.addEventListener('click', function(event) {
        if (!menuButton.contains(event.target)) {
            dropdownMenu.style.display = 'none';
        }
    });
});
