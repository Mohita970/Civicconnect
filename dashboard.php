<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

require_once "../config/database.php";

$total = 0;
$pending = 0;
$in_progress = 0;
$resolved = 0;

/* Total Complaints */

$result = $conn->query(
    "SELECT COUNT(*) AS total FROM complaints"
);

if ($result) {
    $total = $result->fetch_assoc()["total"];
}


/* Pending Complaints */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM complaints
     WHERE status = 'Submitted'"
);

if ($result) {
    $pending = $result->fetch_assoc()["total"];
}


/* In Progress */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM complaints
     WHERE status = 'In Progress'"
);

if ($result) {
    $in_progress = $result->fetch_assoc()["total"];
}


/* Resolved */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM complaints
     WHERE status = 'Resolved'"
);

if ($result) {
    $resolved = $result->fetch_assoc()["total"];
}


/* Resolution Rate */

if ($total > 0) {
    $resolution_rate = round(
        ($resolved / $total) * 100
    );
} else {
    $resolution_rate = 0;
}


/* Category-wise complaints */

$category_names = [];
$category_counts = [];

$result = $conn->query(
    "SELECT category, COUNT(*) AS total
     FROM complaints
     GROUP BY category
     ORDER BY total DESC"
);

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $category_names[] = $row["category"];

        $category_counts[] =
            (int)$row["total"];
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>

<title>Admin Dashboard | CivicConnect</title>

<link
rel="stylesheet"
href="../css/style.css"
>

<!-- Chart.js -->

<script
src="https://cdn.jsdelivr.net/npm/chart.js">
</script>


<style>

.admin-section {

    min-height:
    calc(100vh - 80px);

    background:
    #f4f7fb;

    padding:
    50px 20px;
}


.admin-container {

    max-width:
    1150px;

    margin:
    auto;
}


.admin-container h1 {

    color:
    #102a43;

    font-size:
    38px;

    margin-bottom:
    8px;
}


.admin-intro {

    color:
    #627d98;

    margin-bottom:
    35px;
}


/* Stats */

.stats-grid {

    display:
    grid;

    grid-template-columns:
    repeat(4, 1fr);

    gap:
    20px;
}


.stat-card {

    background:
    white;

    padding:
    25px;

    border-radius:
    16px;

    box-shadow:
    0 8px 25px
    rgba(16, 42, 67, 0.08);

    border:
    1px solid #edf1f7;
}


.stat-card h3 {

    color:
    #627d98;

    font-size:
    15px;

    margin:
    0 0 15px;
}


.stat-number {

    font-size:
    36px;

    font-weight:
    700;

    color:
    #1769e0;
}


.stat-description {

    color:
    #8294aa;

    font-size:
    13px;

    margin-top:
    8px;
}


/* Analytics */

.analytics-grid {

    display:
    grid;

    grid-template-columns:
    1fr 1fr;

    gap:
    25px;

    margin-top:
    30px;
}


.analytics-card {

    background:
    white;

    padding:
    25px;

    border-radius:
    16px;

    box-shadow:
    0 8px 25px
    rgba(16, 42, 67, 0.08);

    border:
    1px solid #edf1f7;
}


.analytics-card h2 {

    color:
    #102a43;

    font-size:
    22px;

    margin-top:
    0;

    margin-bottom:
    5px;
}


.analytics-card p {

    color:
    #8294aa;

    font-size:
    14px;

    margin-bottom:
    20px;
}


.chart-container {

    height:
    280px;

    position:
    relative;
}


/* Resolution */

.resolution-box {

    text-align:
    center;

    padding:
    15px 0;
}


.rate {

    font-size:
    55px;

    font-weight:
    700;

    color:
    #1769e0;

    margin:
    10px 0;
}


.rate-label {

    color:
    #627d98;

    font-size:
    15px;
}


.progress-bar {

    width:
    100%;

    height:
    12px;

    background:
    #e8eef7;

    border-radius:
    20px;

    overflow:
    hidden;

    margin-top:
    20px;
}


.progress-fill {

    height:
    100%;

    width:
    <?php echo $resolution_rate; ?>%;

    background:
    #1769e0;

    border-radius:
    20px;
}


/* Actions */

.admin-actions {

    margin-top:
    30px;

    display:
    flex;

    gap:
    12px;

    flex-wrap:
    wrap;
}


.admin-btn {

    display:
    inline-block;

    padding:
    13px 22px;

    background:
    #1769e0;

    color:
    white;

    text-decoration:
    none;

    border-radius:
    8px;

    font-weight:
    600;

    transition:
    0.2s;
}


.admin-btn:hover {

    transform:
    translateY(-2px);

    box-shadow:
    0 6px 15px
    rgba(23,105,224,0.2);
}


@media (max-width: 900px) {

    .stats-grid {

        grid-template-columns:
        1fr 1fr;
    }

    .analytics-grid {

        grid-template-columns:
        1fr;
    }
}


@media (max-width: 550px) {

    .stats-grid {

        grid-template-columns:
        1fr;
    }

    .admin-container h1 {

        font-size:
        30px;
    }
}

</style>

</head>


<body>


<header class="navbar">

    <div class="logo">

        Civic<span>Connect</span>

    </div>


    <nav>

        <a href="../index.php">
            Home
        </a>

        <a href="dashboard.php">
            Admin Dashboard
        </a>

        <a href="logout.php">
            Logout
        </a>

    </nav>

</header>


<main>


<section class="admin-section">


<div class="admin-container">


<h1>
    Admin Dashboard
</h1>


<p class="admin-intro">

    Welcome,
    <?php
    echo htmlspecialchars(
        $_SESSION["admin_username"]
    );
    ?>.

    Manage complaints, monitor resolution
    progress and analyze complaint trends.

</p>


<!-- ================= STATS ================= -->


<div class="stats-grid">


<div class="stat-card">

    <h3>
        Total Complaints
    </h3>

    <div class="stat-number">
        <?php echo $total; ?>
    </div>

    <div class="stat-description">
        All citizen complaints
    </div>

</div>


<div class="stat-card">

    <h3>
        Pending
    </h3>

    <div class="stat-number">
        <?php echo $pending; ?>
    </div>

    <div class="stat-description">
        Waiting for action
    </div>

</div>


<div class="stat-card">

    <h3>
        In Progress
    </h3>

    <div class="stat-number">
        <?php echo $in_progress; ?>
    </div>

    <div class="stat-description">
        Currently being handled
    </div>

</div>


<div class="stat-card">

    <h3>
        Resolved
    </h3>

    <div class="stat-number">
        <?php echo $resolved; ?>
    </div>

    <div class="stat-description">
        Successfully resolved
    </div>

</div>


</div>


<!-- ================= ANALYTICS ================= -->


<div class="analytics-grid">


<!-- Category Chart -->

<div class="analytics-card">

    <h2>
        Complaint Categories
    </h2>

    <p>
        Distribution of complaints by category.
    </p>


    <div class="chart-container">

        <canvas id="categoryChart"></canvas>

    </div>

</div>


<!-- Resolution Rate -->

<div class="analytics-card">

    <h2>
        Resolution Performance
    </h2>

    <p>
        Percentage of complaints successfully resolved.
    </p>


    <div class="resolution-box">

        <div class="rate">
            <?php echo $resolution_rate; ?>%
        </div>

        <div class="rate-label">
            Overall Resolution Rate
        </div>


        <div class="progress-bar">

            <div class="progress-fill">
            </div>

        </div>


        <p style="margin-top:20px;">

            <?php echo $resolved; ?>
            of
            <?php echo $total; ?>
            complaints resolved.

        </p>

    </div>

</div>


</div>


<!-- ================= ACTIONS ================= -->


<div class="admin-actions">


<a
href="complaints.php"
class="admin-btn"
>

    View All Complaints

</a>


<a
href="heatmap.php"
class="admin-btn"
>

    📍 Complaint Heat Map

</a>


</div>


</div>


</section>


</main>


<script>

/*
|--------------------------------------------------------------------------
| Category Chart
|--------------------------------------------------------------------------
*/

const categoryNames =
<?php echo json_encode($category_names); ?>;


const categoryCounts =
<?php echo json_encode($category_counts); ?>;


const chart =
document.getElementById(
    "categoryChart"
);


new Chart(
    chart,
    {

        type:
        "doughnut",

        data: {

            labels:
            categoryNames,

            datasets: [

                {

                    data:
                    categoryCounts,

                    borderWidth:
                    2

                }

            ]

        },

        options: {

            responsive:
            true,

            maintainAspectRatio:
            false,

            plugins: {

                legend: {

                    position:
                    "bottom"

                }

            }

        }

    }
);

</script>


</body>

</html>