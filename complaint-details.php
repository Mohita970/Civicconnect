<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "config/database.php";

$user_id = $_SESSION["user_id"];

if (!isset($_GET["id"])) {
    header("Location: my-complaints.php");
    exit();
}

$complaint_id = $_GET["id"];

$message = "";
$error = "";


/* =========================
   CITIZEN VERIFICATION
========================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $verification_action = $_POST["verification_action"] ?? "";
    $verification_reason = trim($_POST["verification_reason"] ?? "");


    /* Check complaint belongs to logged-in citizen */

    $check = $conn->prepare(
        "SELECT id, status, verification_status
         FROM complaints
         WHERE complaint_id = ? AND user_id = ?"
    );

    $check->bind_param("si", $complaint_id, $user_id);
    $check->execute();

    $check_result = $check->get_result();

    if ($check_result->num_rows == 0) {

        header("Location: my-complaints.php");
        exit();

    }

    $existing_complaint = $check_result->fetch_assoc();

    $internal_id = $existing_complaint["id"];


    /* Citizen can verify only when proof is pending */

    if (
        $existing_complaint["verification_status"] != "Pending" ||
        $existing_complaint["status"] != "Awaiting Verification"
    ) {

        $error = "This complaint is not currently awaiting verification.";

    }


    /* =========================
       ISSUE RESOLVED
    ========================= */

    elseif ($verification_action == "resolved") {

        $new_status = "Resolved";
        $new_verification_status = "Verified";

        $stmt = $conn->prepare(
            "UPDATE complaints
             SET status = ?, verification_status = ?
             WHERE id = ? AND user_id = ?"
        );

        $stmt->bind_param(
            "ssii",
            $new_status,
            $new_verification_status,
            $internal_id,
            $user_id
        );

        if ($stmt->execute()) {

            $remark =
                "Citizen verified that the issue has been resolved.";

            $update = $conn->prepare(
                "INSERT INTO complaint_updates
                (complaint_id, status, remark)
                VALUES (?, ?, ?)"
            );

            $update->bind_param(
                "iss",
                $internal_id,
                $new_status,
                $remark
            );

            $update->execute();
            $update->close();

            $message =
                "Thank you! The complaint has been marked as Resolved.";

        } else {

            $error =
                "Something went wrong while verifying the complaint.";

        }

        $stmt->close();

    }


    /* =========================
       ISSUE STILL EXISTS
    ========================= */

    elseif ($verification_action == "reopened") {

        if ($verification_reason == "") {

            $error =
                "Please explain why the issue is still not resolved.";

        } else {

            $new_status = "Reopened";
            $new_verification_status = "Rejected";

            $stmt = $conn->prepare(
                "UPDATE complaints
                 SET status = ?, verification_status = ?
                 WHERE id = ? AND user_id = ?"
            );

            $stmt->bind_param(
                "ssii",
                $new_status,
                $new_verification_status,
                $internal_id,
                $user_id
            );

            if ($stmt->execute()) {

                $remark =
                    "Citizen reported that the issue is still not resolved. Reason: "
                    . $verification_reason;

                $update = $conn->prepare(
                    "INSERT INTO complaint_updates
                    (complaint_id, status, remark)
                    VALUES (?, ?, ?)"
                );

                $update->bind_param(
                    "iss",
                    $internal_id,
                    $new_status,
                    $remark
                );

                $update->execute();
                $update->close();

                $message =
                    "Your complaint has been reopened. The administration can review it again.";

            } else {

                $error =
                    "Something went wrong while reopening the complaint.";

            }

            $stmt->close();
        }

    } else {

        $error = "Invalid verification action.";

    }

    $check->close();
}


/* =========================
   GET COMPLAINT
========================= */

$stmt = $conn->prepare(
    "SELECT complaint_id,
            category,
            title,
            description,
            location,
            status,
            created_at,
            resolution_proof,
            verification_status
     FROM complaints
     WHERE complaint_id = ? AND user_id = ?"
);

$stmt->bind_param("si", $complaint_id, $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {

    header("Location: my-complaints.php");
    exit();

}

$complaint = $result->fetch_assoc();

$status = $complaint["status"];


/* =========================
   STATUS CLASS
========================= */

if ($status == "Resolved") {

    $status_class = "resolved";

} elseif ($status == "In Progress") {

    $status_class = "progress";

} elseif ($status == "Awaiting Verification") {

    $status_class = "verification";

} elseif ($status == "Reopened") {

    $status_class = "reopened";

} else {

    $status_class = "submitted";

}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Complaint Details | CivicConnect</title>

    <link rel="stylesheet" href="css/style.css">


    <style>

        body {
            margin: 0;
            background: #f4f7fc;
            font-family: Arial, Helvetica, sans-serif;
            color: #17365d;
        }


        .details-section {
            min-height: calc(100vh - 80px);
            padding: 50px 20px;
        }


        .details-container {
            max-width: 800px;
            margin: auto;
        }


        .details-card {
            background: white;
            border-radius: 22px;
            padding: 35px;
            border: 1px solid #e4ebf5;
            box-shadow: 0 12px 35px rgba(34, 70, 120, 0.09);
        }


        .details-header {
            text-align: center;
            margin-bottom: 30px;
        }


        .details-icon {
            width: 65px;
            height: 65px;
            margin: auto;
            margin-bottom: 15px;
            border-radius: 18px;
            background: #eaf2ff;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 30px;
        }


        .details-header h1 {
            margin: 0;
            font-size: 30px;
        }


        .details-header p {
            color: #718096;
            margin-top: 8px;
        }


        .complaint-id {
            text-align: center;
            background: #f4f8ff;
            padding: 14px;
            border-radius: 12px;
            color: #1769e0;
            font-weight: 700;
            margin-bottom: 25px;
        }


        .status-box {
            text-align: center;
            margin-bottom: 30px;
        }


        .status {
            display: inline-block;
            padding: 9px 20px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 14px;
        }


        .submitted {
            background: #fff4d6;
            color: #9a6700;
        }


        .progress {
            background: #e8f1ff;
            color: #1769e0;
        }


        .resolved {
            background: #e7f8ee;
            color: #20733b;
        }


        .verification {
            background: #fff4d6;
            color: #8a6500;
        }


        .reopened {
            background: #ffe9e9;
            color: #b42318;
        }


        .detail-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 18px;
        }


        .detail-item {
            background: #f8faff;
            border: 1px solid #edf1f7;
            border-radius: 12px;
            padding: 18px;
        }


        .detail-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #8294aa;
            margin-bottom: 8px;
        }


        .detail-value {
            color: #314e68;
            font-size: 15px;
            line-height: 1.5;
        }


        .description {
            background: #f8faff;
            border: 1px solid #edf1f7;
            border-radius: 12px;
            padding: 18px;
            margin-top: 18px;
        }


        /* =========================
           RESOLUTION PROOF
        ========================= */

        .proof-section {
            margin-top: 25px;
            padding: 22px;
            background: #f8faff;
            border: 1px solid #dfe8f5;
            border-radius: 16px;
        }


        .proof-title {
            font-size: 19px;
            font-weight: 700;
            color: #17365d;
            margin-bottom: 8px;
        }


        .proof-subtitle {
            color: #718096;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 18px;
        }


        .proof-image {
            width: 100%;
            max-height: 420px;
            object-fit: contain;
            border-radius: 12px;
            background: #eef3f9;
            border: 1px solid #dce5f0;
            display: block;
            margin-bottom: 20px;
        }


        /* =========================
           VERIFICATION
        ========================= */

        .verification-box {
            margin-top: 25px;
            padding: 25px;
            background: #fffaf0;
            border: 1px solid #f1dfae;
            border-radius: 16px;
        }


        .verification-box h2 {
            margin: 0 0 8px;
            font-size: 21px;
            color: #614b12;
        }


        .verification-box p {
            color: #6d5b28;
            font-size: 14px;
            line-height: 1.5;
            margin-top: 0;
            margin-bottom: 20px;
        }


        .verification-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }


        .verify-btn {
            border: none;
            border-radius: 12px;
            padding: 15px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s ease;
        }


        .verify-btn:hover {
            transform: translateY(-2px);
        }


        .yes-btn {
            background: #20733b;
            color: white;
            box-shadow: 0 7px 16px rgba(32, 115, 59, 0.18);
        }


        .no-btn {
            background: #b42318;
            color: white;
            box-shadow: 0 7px 16px rgba(180, 35, 24, 0.15);
        }


        .reason-box {
            margin-top: 18px;
        }


        .reason-box label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #614b12;
            margin-bottom: 8px;
        }


        .reason-box textarea {
            width: 100%;
            box-sizing: border-box;
            min-height: 100px;
            resize: vertical;
            border: 1px solid #d8e1ef;
            border-radius: 12px;
            padding: 13px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            background: white;
        }


        .reason-box textarea:focus {
            outline: none;
            border-color: #1769e0;
        }


        /* =========================
           MESSAGES
        ========================= */

        .success-message {
            background: #e7f8ee;
            border: 1px solid #b9e5c8;
            color: #20733b;
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 14px;
            font-weight: 600;
        }


        .error-message {
            background: #fff0f0;
            border: 1px solid #f1c4c4;
            color: #b42318;
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 14px;
            font-weight: 600;
        }


        .back-btn {
            display: block;
            width: fit-content;
            margin: 28px auto 0;
            text-decoration: none;
            color: #1769e0;
            font-weight: 700;
        }


        @media (max-width: 650px) {

            .details-card {
                padding: 25px 18px;
            }

            .detail-row {
                grid-template-columns: 1fr;
            }

            .verification-buttons {
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


<section class="details-section">

    <div class="details-container">

        <div class="details-card">


            <div class="details-header">

                <div class="details-icon">
                    📋
                </div>


                <h1>
                    Complaint Details
                </h1>


                <p>
                    View complete information about your complaint.
                </p>

            </div>


            <div class="complaint-id">

                Complaint ID:

                <?php
                echo htmlspecialchars(
                    $complaint["complaint_id"]
                );
                ?>

            </div>


            <?php if ($message != ""): ?>

                <div class="success-message">

                    ✓
                    <?php
                    echo htmlspecialchars($message);
                    ?>

                </div>

            <?php endif; ?>


            <?php if ($error != ""): ?>

                <div class="error-message">

                    ⚠
                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php endif; ?>


            <div class="status-box">

                <span class="status <?php echo $status_class; ?>">

                    <?php

                    if ($status == "Resolved") {

                        echo "✓ Resolved";

                    } elseif ($status == "In Progress") {

                        echo "● In Progress";

                    } elseif ($status == "Awaiting Verification") {

                        echo "⏳ Awaiting Citizen Verification";

                    } elseif ($status == "Reopened") {

                        echo "↻ Reopened";

                    } else {

                        echo "● Submitted";

                    }

                    ?>

                </span>

            </div>


            <div class="detail-row">


                <div class="detail-item">

                    <span class="detail-label">
                        Complaint Title
                    </span>


                    <div class="detail-value">

                        <?php
                        echo htmlspecialchars(
                            $complaint["title"]
                        );
                        ?>

                    </div>

                </div>


                <div class="detail-item">

                    <span class="detail-label">
                        Category
                    </span>


                    <div class="detail-value">

                        <?php
                        echo htmlspecialchars(
                            $complaint["category"]
                        );
                        ?>

                    </div>

                </div>

            </div>


            <div class="detail-row">


                <div class="detail-item">

                    <span class="detail-label">
                        Location
                    </span>


                    <div class="detail-value">

                        📍

                        <?php
                        echo htmlspecialchars(
                            $complaint["location"]
                        );
                        ?>

                    </div>

                </div>


                <div class="detail-item">

                    <span class="detail-label">
                        Submitted On
                    </span>


                    <div class="detail-value">

                        <?php
                        echo htmlspecialchars(
                            $complaint["created_at"]
                        );
                        ?>

                    </div>

                </div>

            </div>


            <div class="description">

                <span class="detail-label">
                    Description
                </span>


                <div class="detail-value">

                    <?php
                    echo nl2br(
                        htmlspecialchars(
                            $complaint["description"]
                        )
                    );
                    ?>

                </div>

            </div>


            <?php if (!empty($complaint["resolution_proof"])): ?>


                <!-- =========================
                     RESOLUTION PROOF
                ========================= -->

                <div class="proof-section">


                    <div class="proof-title">

                        📷 Resolution Proof

                    </div>


                    <div class="proof-subtitle">

                        The administration has uploaded this photo
                        as proof that the reported issue has been resolved.

                    </div>


                    <img
                        src="<?php echo htmlspecialchars($complaint["resolution_proof"]); ?>"
                        alt="Resolution Proof"
                        class="proof-image"
                    >


                </div>


                <?php if (
                    $complaint["status"] == "Awaiting Verification"
                    &&
                    $complaint["verification_status"] == "Pending"
                ): ?>


                    <!-- =========================
                         CITIZEN VERIFICATION
                    ========================= -->

                    <div class="verification-box">


                        <h2>
                            🔍 Please Verify the Resolution
                        </h2>


                        <p>

                            Please check the proof photo and confirm
                            whether the issue has actually been resolved
                            at the reported location.

                        </p>


                        <form method="POST">


                            <div class="verification-buttons">


                                <button
                                    type="submit"
                                    name="verification_action"
                                    value="resolved"
                                    class="verify-btn yes-btn"
                                >

                                    ✓ Yes, Issue Resolved

                                </button>


                                <button
                                    type="button"
                                    class="verify-btn no-btn"
                                    onclick="showReasonBox()"
                                >

                                    ✕ No, Issue Still Exists

                                </button>


                            </div>


                            <div
                                class="reason-box"
                                id="reasonBox"
                                style="display:none;"
                            >


                                <label for="verification_reason">

                                    Please explain why the issue
                                    is still not resolved

                                </label>


                                <textarea
                                    name="verification_reason"
                                    id="verification_reason"
                                    placeholder="Example: The water leakage is still present..."
                                ></textarea>


                                <button
                                    type="submit"
                                    name="verification_action"
                                    value="reopened"
                                    class="verify-btn no-btn"
                                    style="width:100%; margin-top:12px;"
                                >

                                    Reopen Complaint

                                </button>


                            </div>


                        </form>


                    </div>


                <?php endif; ?>


            <?php endif; ?>


            <a
                href="my-complaints.php"
                class="back-btn"
            >

                ← Back to My Complaints

            </a>


        </div>

    </div>

</section>


<script>

function showReasonBox() {

    const reasonBox =
        document.getElementById("reasonBox");

    reasonBox.style.display = "block";

    document
        .getElementById("verification_reason")
        .focus();

}

</script>


</body>

</html>