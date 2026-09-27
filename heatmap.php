<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

require_once "../config/database.php";

$locations = [];

$sql = "SELECT location, COUNT(*) AS complaint_count
        FROM complaints
        WHERE location IS NOT NULL
        AND location != ''
        GROUP BY location";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $locations[] = [
            "location" => $row["location"],
            "count" => (int)$row["complaint_count"]
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Complaint Heat Map | CivicConnect</title>

<link
rel="stylesheet"
href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f7fb;
    color: #17365d;
}

.header {
    background: white;
    padding: 18px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e5eaf2;
}

.logo {
    font-size: 24px;
    font-weight: 700;
    color: #1769e0;
}

.back-btn {
    text-decoration: none;
    background: #1769e0;
    color: white;
    padding: 10px 18px;
    border-radius: 8px;
    font-weight: 600;
}

.container {
    max-width: 1200px;
    margin: 30px auto;
    padding: 0 20px;
}

.heading-card {
    background: white;
    padding: 25px;
    border-radius: 16px;
    margin-bottom: 20px;
    box-shadow: 0 8px 25px rgba(16,42,67,0.07);
}

.heading-card h1 {
    margin: 0 0 8px;
    font-size: 32px;
}

.heading-card p {
    margin: 0;
    color: #627d98;
}

.map-wrapper {
    position: relative;
}

#map {
    width: 100%;
    height: 560px;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(16,42,67,0.10);
    border: 1px solid #dce5f0;
}

/* Legend */

.legend {
    position: absolute;
    right: 20px;
    bottom: 20px;
    z-index: 999;
    background: white;
    padding: 15px 18px;
    border-radius: 12px;
    box-shadow: 0 5px 18px rgba(0,0,0,0.15);
    font-size: 13px;
}

.legend-title {
    font-weight: 700;
    margin-bottom: 10px;
}

.legend-row {
    display: flex;
    align-items: center;
    margin: 7px 0;
}

.legend-dot {
    width: 15px;
    height: 15px;
    border-radius: 50%;
    margin-right: 8px;
}

.low {
    background: #22c55e;
}

.medium {
    background: #f59e0b;
}

.high {
    background: #ef4444;
}

/* Location cards */

.locations {
    margin-top: 22px;
    background: white;
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 8px 25px rgba(16,42,67,0.07);
}

.locations h2 {
    margin-top: 0;
}

.location-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #edf1f7;
}

.location-item:last-child {
    border-bottom: none;
}

.location-name {
    font-weight: 600;
}

.count {
    padding: 6px 12px;
    border-radius: 20px;
    background: #e8f1ff;
    color: #1769e0;
    font-weight: 700;
}

.warning {
    margin-top: 15px;
    color: #9a6700;
    background: #fff8df;
    padding: 12px;
    border-radius: 8px;
    font-size: 13px;
}

</style>

</head>

<body>

<header class="header">

    <div class="logo">
        CivicConnect
    </div>

    <a href="dashboard.php" class="back-btn">
        ← Dashboard
    </a>

</header>


<div class="container">

    <div class="heading-card">

        <h1>📍 Complaint Heat Map</h1>

        <p>
            Identify complaint hotspots and areas with higher
            concentrations of citizen complaints.
        </p>

    </div>


    <div class="map-wrapper">

        <div id="map"></div>

        <div class="legend">

            <div class="legend-title">
                Complaint Intensity
            </div>

            <div class="legend-row">
                <span class="legend-dot low"></span>
                Low
            </div>

            <div class="legend-row">
                <span class="legend-dot medium"></span>
                Medium
            </div>

            <div class="legend-row">
                <span class="legend-dot high"></span>
                High
            </div>

        </div>

    </div>


    <div class="locations">

        <h2>Complaint Hotspots</h2>

        <?php if (count($locations) > 0): ?>

            <?php foreach ($locations as $item): ?>

                <div class="location-item">

                    <span class="location-name">
                        📍
                        <?php echo htmlspecialchars($item["location"]); ?>
                    </span>

                    <span class="count">

                        <?php echo $item["count"]; ?>

                        complaint<?php
                        echo $item["count"] != 1 ? "s" : "";
                        ?>

                    </span>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <p>No complaint locations available.</p>

        <?php endif; ?>

        <div id="unmapped"></div>

    </div>

</div>


<script
src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js">
</script>

<!-- Heatmap Plugin -->

<script
src="https://unpkg.com/leaflet.heat/dist/leaflet-heat.js">
</script>


<script>

const locations =
<?php echo json_encode($locations); ?>;


/*
|--------------------------------------------------------------------------
| Map
|--------------------------------------------------------------------------
*/

const map = L.map("map").setView(
    [30.7022, 76.2128],
    13
);


L.tileLayer(
    "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
    {
        attribution:
        '&copy; OpenStreetMap contributors'
    }
).addTo(map);


/*
|--------------------------------------------------------------------------
| Heatmap points
|--------------------------------------------------------------------------
*/

const heatPoints = [];

const markerPoints = [];

const unmapped = [];


/*
|--------------------------------------------------------------------------
| Geocoding
|--------------------------------------------------------------------------
*/

async function findLocation(location) {

    try {

        let query =
            encodeURIComponent(
                location +
                ", Khanna, Punjab, India"
            );

        let response =
            await fetch(
                "https://nominatim.openstreetmap.org/search" +
                "?format=json&limit=1&q=" +
                query
            );

        let data =
            await response.json();


        /*
         * If Khanna-specific search fails,
         * try the original location.
         */

        if (data.length === 0) {

            query =
                encodeURIComponent(
                    location +
                    ", Punjab, India"
                );

            response =
                await fetch(
                    "https://nominatim.openstreetmap.org/search" +
                    "?format=json&limit=1&q=" +
                    query
                );

            data =
                await response.json();
        }


        if (data.length > 0) {

            return {
                lat: parseFloat(data[0].lat),
                lon: parseFloat(data[0].lon)
            };

        }

    } catch (error) {

        console.log(
            "Location search failed:",
            location
        );

    }

    return null;
}


/*
|--------------------------------------------------------------------------
| Create heatmap
|--------------------------------------------------------------------------
*/

async function createHeatMap() {

    for (const item of locations) {

        const position =
            await findLocation(item.location);


        if (!position) {

            unmapped.push(item.location);

            continue;
        }


        /*
         * Heat intensity
         */

        const intensity =
            Math.min(
                item.count,
                10
            );


        heatPoints.push([
            position.lat,
            position.lon,
            intensity
        ]);


        /*
         * Marker
         */

        let marker =
            L.marker([
                position.lat,
                position.lon
            ]).addTo(map);


        marker.bindPopup(
            `
            <div style="min-width:160px">

                <b>
                    📍 ${item.location}
                </b>

                <br><br>

                <strong>
                    Total Complaints:
                </strong>

                ${item.count}

            </div>
            `
        );


        /*
         * Circle hotspot
         */

        let circleColor =
            "#22c55e";


        if (item.count >= 3) {

            circleColor =
                "#ef4444";

        } else if (item.count >= 2) {

            circleColor =
                "#f59e0b";
        }


        L.circle(
            [
                position.lat,
                position.lon
            ],
            {
                radius:
                    180 +
                    (item.count * 100),

                color: circleColor,

                fillColor: circleColor,

                fillOpacity: 0.25,

                weight: 2
            }
        ).addTo(map);


        markerPoints.push([
            position.lat,
            position.lon
        ]);
    }


    /*
     * Real heat layer
     */

    if (heatPoints.length > 0) {

        L.heatLayer(
            heatPoints,
            {
                radius: 45,
                blur: 30,
                maxZoom: 15,
                minOpacity: 0.35
            }
        ).addTo(map);

    }


    /*
     * Automatically fit map
     */

    if (markerPoints.length > 0) {

        const bounds =
            L.latLngBounds(markerPoints);

        map.fitBounds(
            bounds,
            {
                padding: [50, 50]
            }
        );

    }


    /*
     * Show locations that could not
     * be automatically located.
     */

    if (unmapped.length > 0) {

        document.getElementById(
            "unmapped"
        ).innerHTML =

            `
            <div class="warning">

                ⚠️ Could not automatically locate:

                <strong>
                    ${unmapped.join(", ")}
                </strong>

                <br>

                These locations are still counted
                in the complaint list.

            </div>
            `;

    }

}


createHeatMap();

</script>

</body>

</html>