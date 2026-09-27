<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $category = trim($_POST["category"]);
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $location = trim($_POST["location"]);

    $complaint_id = "CC" . date("YmdHis") . rand(100, 999);

    $stmt = $conn->prepare(
        "INSERT INTO complaints 
        (complaint_id, user_id, category, title, description, location, status)
        VALUES (?, ?, ?, ?, ?, ?, 'Submitted')"
    );

    $stmt->bind_param(
        "sissss",
        $complaint_id,
        $_SESSION["user_id"],
        $category,
        $title,
        $description,
        $location
    );

    if ($stmt->execute()) {
        $message = "Complaint submitted successfully! Your Complaint ID is: " . $complaint_id;
    } else {
        $message = "Something went wrong. Please try again.";
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Complaint | CivicConnect</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<header class="navbar">
    <div class="logo">
        Civic<span>Connect</span>
    </div>

    <nav>
        <a href="index.php">Home</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>

<section class="register-section">

    <div class="register-card">

        <h1>Submit a Complaint</h1>

        <p>Report a municipal problem to the concerned department.</p>

        <?php if ($message != ""): ?>
            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <label>Complaint Category</label>

            <select name="category" required>
                <option value="">Select Category</option>
                <option value="Road Problems">Road Problems</option>
                <option value="Water Supply">Water Supply</option>
                <option value="Waste Management">Waste Management</option>
                <option value="Street Lights">Street Lights</option>
                <option value="Drainage">Drainage</option>
                <option value="Other">Other</option>
            </select>

            <label>Complaint Title</label>

            <input
                type="text"
                name="title"
                placeholder="Example: Road damaged near market"
                required
            >

            <label>Description</label>

            <textarea
                name="description"
                placeholder="Describe the problem in detail"
                rows="5"
                required
            ></textarea>

            <label>Location</label>

            <input
                type="text"
                name="location"
                placeholder="Enter problem location"
                required
            >

            <button type="submit">Submit Complaint</button>

        </form>

    </div>

</section>

</main>

</body>
</html>