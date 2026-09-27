<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "config/database.php";

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT complaint_id, category, title, description, location, status, created_at
     FROM complaints
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Track Complaints | CivicConnect</title>

    <link rel="stylesheet" href="css/style.css">

    <style>

        body {
            margin: 0;
            background: #f4f7fc;
            font-family: Arial, Helvetica, sans-serif;
            color: #17365d;
        }

        .tracking-section {
            min-height: calc(100vh - 80px);
            padding: 50px 20px;
        }

        .tracking-container {
            max-width: 1050px;
            margin: auto;
        }

        /* Header */

        .tracking-header {
            text-align: center;
            margin-bottom: 38px;
        }

        .tracking-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 15px;
            background: #eaf2ff;
            border-radius: 18px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 29px;
        }

        .tracking-header h1 {
            margin: 0;
            font-size: 32px;
            color: #17365d;
        }

        .tracking-header p {
            margin-top: 10px;
            color: #718096;
            font-size: 15px;
        }

        /* Complaint Card */

        .complaint-card {
            background: white;
            border-radius: 20px;
            margin-bottom: 25px;
            padding: 28px;

            border: 1px solid #e4ebf5;

            box-shadow:
                0 10px 30px rgba(34, 70, 120, 0.08);
        }

        /* Top */

        .complaint-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;

            padding-bottom: 20px;
            border-bottom: 1px solid #edf1f7;
        }

        .complaint-title {
            margin: 0 0 8px;
            font-size: 21px;
            color: #17365d;
        }

        .complaint-id {
            margin: 0;
            color: #1769e0;
            font-size: 14px;
            font-weight: 700;
        }

        /* Status */

        .status {
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-submitted {
            background: #fff4d6;
            color: #9a6700;
        }

        .status-progress {
            background: #e8f1ff;
            color: #1769e0;
        }

        .status-resolved {
            background: #e7f8ee;
            color: #20733b;
        }

        /* Information Grid */

        .complaint-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
            margin-top: 22px;
        }

        .info-item {
            background: #f8faff;
            border: 1px solid #edf1f7;
            border-radius: 12px;
            padding: 16px;
        }

        .info-label {
            display: block;
            color: #8294aa;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 7px;
        }

        .info-value {
            color: #314e68;
            font-size: 14px;
            line-height: 1.5;
        }

        /* Description */

        .description-box {
            margin-top: 18px;
            background: #f8faff;
            border: 1px solid #edf1f7;
            border-radius: 12px;
            padding: 18px;
        }

        .description-box .info-label {
            margin-bottom: 8px;
        }

        /* Empty State */

        .no-complaints {
            background: white;
            text-align: center;
            padding: 55px 25px;
            border-radius: 20px;
            border: 1px solid #e4ebf5;
            box-shadow: 0 10px 30px rgba(34, 70, 120, 0.07);
        }

        .empty-icon {
            font-size: 45px;
            margin-bottom: 12px;
        }

        .no-complaints h2 {
            color: #17365d;
            margin-bottom: 8px;
        }

        .no-complaints p {
            color: #718096;
            margin-bottom: 20px;
        }

        .submit-link {
            display: inline-block;
            padding: 12px 22px;
            border-radius: 10px;
            background: #1769e0;
            color: white;
            text-decoration: none;
            font-weight: 700;
        }

        .submit-link:hover {
            background: #1258c4;
        }

        /* Back button */

        .back-dashboard {
            display: block;
            width: fit-content;
            margin: 30px auto 0;

            text-decoration: none;
            color: #55708f;
            font-size: 14px;
            font-weight: 700;
        }

        .back-dashboard:hover {
            color: #1769e0;
        }

        /* Mobile */

        @media (max-width: 700px) {

            .tracking-section {
                padding: 30px 14px;
            }

            .tracking-header h1 {
                font-size: 27px;
            }

            .complaint-card {
                padding: 20px;
            }

            .complaint-top {
                flex-direction: column;
            }

            .complaint-info {
                grid-template-columns: 1fr;
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
        <a href="index.php">Home</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="my-complaints.php">My Complaints</a>
        <a href="logout.php">Logout</a>
    </nav>

</header>


<main>

<section class="tracking-section">

<div class="tracking-container">


    <!-- Page Header -->

    <div class="tracking-header">

        <div class="tracking-icon">
            🔎
        </div>

        <h1>Track Your Complaints</h1>

        <p>
            View the current status and details of your submitted complaints.
        </p>

    </div>


    <?php if ($result->num_rows > 0): ?>


        <?php while ($complaint = $result->fetch_assoc()): ?>


            <?php

                $status = $complaint["status"];

                if ($status == "Resolved") {
                    $status_class = "status-resolved";
                } elseif ($status == "In Progress") {
                    $status_class = "status-progress";
                } else {
                    $status_class = "status-submitted";
                }

            ?>


            <div class="complaint-card">


                <!-- Complaint Header -->

                <div class="complaint-top">

                    <div>

                        <h2 class="complaint-title">
                            <?php echo htmlspecialchars($complaint["title"]); ?>
                        </h2>

                        <p class="complaint-id">
                           Complaint ID:

                         <a href="complaint-details.php?id=<?php echo urlencode($complaint["complaint_id"]); ?>">
                            <?php echo htmlspecialchars($complaint["complaint_id"]); ?>
                         </a>
                        </p>

                    </div>


                    <span class="status <?php echo $status_class; ?>">

                        <?php

                        if ($status == "Submitted") {
                            echo "● Submitted";
                        } elseif ($status == "In Progress") {
                            echo "● In Progress";
                        } elseif ($status == "Resolved") {
                            echo "✓ Resolved";
                        } else {
                            echo htmlspecialchars($status);
                        }

                        ?>

                    </span>

                </div>


                <!-- Information -->

                <div class="complaint-info">


                    <div class="info-item">

                        <span class="info-label">
                            Category
                        </span>

                        <div class="info-value">

                            <?php
                            echo htmlspecialchars($complaint["category"]);
                            ?>

                        </div>

                    </div>


                    <div class="info-item">

                        <span class="info-label">
                            Location
                        </span>

                        <div class="info-value">

                            📍
                            <?php
                            echo htmlspecialchars($complaint["location"]);
                            ?>

                        </div>

                    </div>


                    <div class="info-item">

                        <span class="info-label">
                            Submitted On
                        </span>

                        <div class="info-value">

                            <?php
                            echo htmlspecialchars($complaint["created_at"]);
                            ?>

                        </div>

                    </div>


                    <div class="info-item">

                        <span class="info-label">
                            Current Status
                        </span>

                        <div class="info-value">

                            <?php
                            echo htmlspecialchars($complaint["status"]);
                            ?>

                        </div>

                    </div>


                </div>


                <!-- Description -->

                <div class="description-box">

                    <span class="info-label">
                        Complaint Description
                    </span>

                    <div class="info-value">

                        <?php
                        echo htmlspecialchars($complaint["description"]);
                        ?>

                    </div>

                </div>


            </div>


        <?php endwhile; ?>


    <?php else: ?>


        <div class="no-complaints">

            <div class="empty-icon">
                📋
            </div>

            <h2>No Complaints Yet</h2>

            <p>
                You haven't submitted any complaints yet.
            </p>

            <a
                href="submit-complaint.php"
                class="submit-link"
            >
                Submit Your First Complaint
            </a>

        </div>


    <?php endif; ?>


    <a
        href="dashboard.php"
        class="back-dashboard"
    >
        ← Back to Dashboard
    </a>


</div>

</section>

</main>


</body>

</html>