// Check if geolocation is available
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
        (position) => {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;

            // Log coordinates to the console
            console.log('Latitude:', latitude);
            console.log('Longitude:', longitude);

            // Initialize the Leaflet map
            const map = L.map('map').setView([latitude, longitude], 13);

            // Add a tile layer to the map
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // Add a marker at the current location
            L.marker([latitude, longitude]).addTo(map)
                .bindPopup('You are here!')
                .openPopup();
        },
        (error) => {
            console.error('Error getting location:', error.message);
            alert('Unable to retrieve your location. Please enable location access.');
        }
    );
} else {
    alert('Geolocation is not supported by this browser.');
}
