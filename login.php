<?php

session_start();

require_once "../config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare(
    "SELECT id, username, password FROM admins WHERE LOWER(TRIM(username)) = LOWER(TRIM(?))"
);

    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $admin = $result->fetch_assoc();

        if (password_verify($password, $admin["password"])) {

            $_SESSION["admin_id"] = $admin["id"];
            $_SESSION["admin_username"] = $admin["username"];

            header("Location: dashboard.php");
            exit();

        } else {

            $message = "Incorrect password.";

        }

    } else {

        $message = "Admin account not found.";

    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login | CivicConnect</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

<header class="navbar">

    <div class="logo">
        Civic<span>Connect</span>
    </div>

    <nav>
        <a href="../index.php">Home</a>
        <a href="../login.php">Citizen Login</a>
    </nav>

</header>

<main>

<section class="register-section">

    <div class="register-card">

        <h1>Admin Login</h1>

        <p>Login to manage citizen complaints.</p>

        <?php if ($message != ""): ?>

            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <label>Username</label>

            <input
                type="text"
                name="username"
                placeholder="Enter admin username"
                required
            >

            <label>Password</label>

            <input
                type="password"
                name="password"
                placeholder="Enter admin password"
                required
            >

            <button type="submit">
                Admin Login
            </button>

        </form>

    </div>

</section>

</main>

</body>

</html>