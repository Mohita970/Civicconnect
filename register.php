<?php

require_once "config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if ($password !== $confirm_password) {

        $message = "Passwords do not match.";

    } else {

        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "This email is already registered.";

        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, phone, password)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssss",
                $name,
                $email,
                $phone,
                $hashed_password
            );

            if ($stmt->execute()) {

                $message = "Registration successful! You can now login.";

            } else {

                $message = "Registration failed. Please try again.";

            }

            $stmt->close();
        }

        $check->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register | CivicConnect</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <header class="navbar">

        <div class="logo">
            Civic<span>Connect</span>
        </div>

        <nav>

            <a href="index.php">Home</a>

            <a href="index.php#services">Services</a>

            <a href="index.php#how-it-works">How It Works</a>

            <a href="login.php">Login</a>

            <a href="register.php" class="nav-btn">Register</a>

        </nav>

    </header>


    <main>

        <section class="register-section">

            <div class="register-card">

                <h1>Create Your Account</h1>

                <p>
                    Join CivicConnect and help improve your community.
                </p>

                <?php if ($message != ""): ?>

                    <div class="message">
                        <?php echo htmlspecialchars($message); ?>
                    </div>

                <?php endif; ?>


                <form method="POST" action="">

                    <label>Full Name</label>

                    <input
                        type="text"
                        name="name"
                        placeholder="Enter your full name"
                        required
                    >


                    <label>Email Address</label>

                    <input
                        type="email"
                        name="email"
                        placeholder="Enter your email"
                        required
                    >


                    <label>Phone Number</label>

                    <input
                        type="text"
                        name="phone"
                        placeholder="Enter your phone number"
                    >


                    <label>Password</label>

                    <input
                        type="password"
                        name="password"
                        placeholder="Create a password"
                        required
                    >


                    <label>Confirm Password</label>

                    <input
                        type="password"
                        name="confirm_password"
                        placeholder="Confirm your password"
                        required
                    >


                    <button type="submit">
                        Create Account
                    </button>

                </form>


                <p class="login-link">
                    Already have an account?
                    <a href="login.php">Login here</a>
                </p>

            </div>

        </section>

    </main>

</body>

</html>