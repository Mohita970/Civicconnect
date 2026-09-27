<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

require_once "../config/database.php";

$result = $conn->query(
    "SELECT complaints.*, users.name, users.email
     FROM complaints
     JOIN users ON complaints.user_id = users.id
     ORDER BY complaints.created_at DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>All Complaints | CivicConnect</title>

    <link rel="stylesheet" href="../css/style.css">

    <style>

        .admin-section {
            min-height: calc(100vh - 80px);
            background: #f4f7fb;
            padding: 50px 20px;
        }

        .admin-container {
            max-width: 1150px;
            margin: auto;
        }

        .admin-container h1 {
            color: #102a43;
            font-size: 38px;
            margin-bottom: 10px;
        }

        .admin-intro {
            color: #627d98;
            margin-bottom: 30px;
        }

        .complaint-table {
            background: white;
            border-radius: 15px;
            overflow-x: auto;
            box-shadow: 0 8px 25px rgba(16, 42, 67, 0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        th {
            background: #102a43;
            color: white;
            padding: 15px;
            text-align: left;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #e6edf5;
            color: #486581;
        }

        tr:hover {
            background: #f8fbff;
        }

        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            background: #e8f1ff;
            color: #1769e0;
            font-weight: 600;
        }

        .update-btn {
            display: inline-block;
            padding: 8px 13px;
            background: #1769e0;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
        }

    </style>

</head>

<body>

<header class="navbar">

    <div class="logo">
        Civic<span>Connect</span>
    </div>

    <nav>
        <a href="../index.php">Home</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="complaints.php">Complaints</a>
        <a href="logout.php">Logout</a>
    </nav>

</header>

<main>

<section class="admin-section">

<div class="admin-container">

    <h1>All Complaints</h1>

    <p class="admin-intro">
        View and manage complaints submitted by citizens.
    </p>

    <div class="complaint-table">

        <table>

            <thead>

                <tr>
                    <th>Complaint ID</th>
                    <th>Citizen</th>
                    <th>Category</th>
                    <th>Title</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

            </thead>

            <tbody>

                <?php if ($result->num_rows > 0): ?>

                    <?php while ($complaint = $result->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars($complaint["complaint_id"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($complaint["name"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($complaint["category"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($complaint["title"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($complaint["location"]); ?>
                            </td>

                            <td>
                                <span class="status">
                                    <?php echo htmlspecialchars($complaint["status"]); ?>
                                </span>
                            </td>

                            <td>
                                <a
                                    href="update-complaint.php?id=<?php echo $complaint["id"]; ?>"
                                    class="update-btn"
                                >
                                    Update
                                </a>
                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="7">
                            No complaints found.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</section>

</main>

</body>

</html>