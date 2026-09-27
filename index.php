
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CivicConnect | Municipal Complaint System</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<!-- Navigation Bar -->
<header class="navbar">
    <div class="logo">Civic<span>Connect</span></div>

    <nav>
        <a href="index.php">Home</a>
        <a href="#services">Services</a>
        <a href="#how-it-works">How It Works</a>
        <a href="login.php">Login</a>
        <a href="register.php" class="nav-btn">Register</a>
    </nav>
</header>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <p class="tagline">YOUR CITY, YOUR VOICE</p>

        <h1>Make Your City <span>Better</span></h1>

        <p>
            Report municipal problems, track your complaints,
            and help make your community cleaner, safer,
            and better for everyone.
        </p>

        <div class="hero-buttons">
            <a href="submit_complaint.php" class="btn primary-btn">
                Submit a Complaint
            </a>

            <a href="#track" class="btn secondary-btn">
                Track Complaint
            </a>
        </div>
    </div>

    <div class="hero-image">
        <div class="city-icon">🏙️</div>
        <h3>Building Better Communities</h3>
        <p>One complaint at a time.</p>
    </div>
</section>

<!-- Services Section -->
<section class="services" id="services">
    <div class="section-heading">
        <p class="tagline">OUR SERVICES</p>
        <h2>What Can You Report?</h2>
        <p>
            Help us identify and resolve problems in your locality.
        </p>
    </div>

    <div class="service-container">

        <div class="service-card">
            <div class="service-icon">🛣️</div>
            <h3>Road Problems</h3>
            <p>Report damaged roads and potholes.</p>
        </div>

        <div class="service-card">
            <div class="service-icon">💧</div>
            <h3>Water Supply</h3>
            <p>Report water leakage and supply issues.</p>
        </div>

        <div class="service-card">
            <div class="service-icon">🗑️</div>
            <h3>Waste Management</h3>
            <p>Report garbage and sanitation problems.</p>
        </div>

        <div class="service-card">
            <div class="service-icon">💡</div>
            <h3>Street Lights</h3>
            <p>Report damaged or non-working street lights.</p>
        </div>

    </div>
</section>

<!-- How It Works -->
<section class="how-it-works" id="how-it-works">
    <div class="section-heading">
        <p class="tagline">SIMPLE AND EASY</p>
        <h2>How It Works</h2>
    </div>

    <div class="steps-container">

        <div class="step-card">
            <div class="step-number">01</div>
            <h3>Register</h3>
            <p>Create your account on CivicConnect.</p>
        </div>

        <div class="step-card">
            <div class="step-number">02</div>
            <h3>Submit</h3>
            <p>Describe the problem and its location.</p>
        </div>

        <div class="step-card">
            <div class="step-number">03</div>
            <h3>Track</h3>
            <p>Check the status of your complaint.</p>
        </div>

        <div class="step-card">
            <div class="step-number">04</div>
            <h3>Resolve</h3>
            <p>Receive updates as your complaint is handled.</p>
        </div>

    </div>
</section>

<!-- Track Complaint -->
<section class="track-section" id="track">
    <div class="track-box">
        <p class="tagline">STAY UPDATED</p>
        <h2>Track Your Complaint</h2>

        <p>
            Enter your complaint ID to check its current status.
        </p>

        <form class="track-form" onsubmit="trackComplaint(event)">
            <input
                type="text"
                id="complaintId"
                placeholder="Enter Complaint ID"
                required
            >

            <button type="submit" class="btn primary-btn">
                Track Now
            </button>
        </form>

        <p id="trackMessage" class="track-message"></p>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-section">
    <h2>Let's Make Our City Better Together</h2>

    <p>
        Your complaint can help improve your neighbourhood.
    </p>

    <a href="register.php" class="btn primary-btn">
        Get Started
    </a>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="logo">Civic<span>Connect</span></div>

    <p>
        Connecting citizens with municipal services.
    </p>

    <p class="copyright">
        © 2026 CivicConnect. College Project.
    </p>
</footer>

<script>
function trackComplaint(event) {
    event.preventDefault();

    const complaintId =
        document.getElementById("complaintId").value.trim();

    const message =
        document.getElementById("trackMessage");

    if (complaintId !== "") {
        message.textContent =
            "Complaint tracking will be available once the database is connected.";
    }
}
</script>

</body>
</html>