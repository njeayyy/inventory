<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Map</title>
    <link rel="stylesheet" href="../admin dashboard/dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.6.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>

<body>
    <div class="dashboard">
        <header class="dashboard-header">
            <div class="navbar">
                <div class="dropdown">
                    <button class="dropbtn">
                        <i class="ri-more-2-fill"></i>
                    </button>
                    <div class="dropdown-content">
                        <a href="dashboard.php">Inventory Management System</a>
                        <a href="tracking.php">Vehicle Tracking</a>
                    </div>
                </div>
            </div>
            <div class="title">
                <h1>VEHICLE TRACKING</h1>
            </div>
            <div class="logout">
                <a href="logout.php">Logout</a>
            </div>
        </header>

        <div class="main-content">
            <aside class="sidebar">
                <ul>
                    <li><button class="active"><a href="">GPS TRACKING</a></button></li>
                    <li><button><a href=""></a></button></li>
                    <li><button><a href=""></a></button></li>
                    <li><button><a href=""></a></button></li>
                    <li><button><a href=""></a></button></li>
                </ul>
            </aside>
            <section class="dashboard-content">


                <div id="map" style="height: 500px;"></div>
            </section>
        </div>
    </div>

    <!-- External JavaScript files -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="../admin dashboard/map.js"></script>
</body>

</html>