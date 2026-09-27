<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

require_once "../config/database.php";

if (!isset($_GET["id"])) {
    header("Location: complaints.php");
    exit();
}

$id = intval($_GET["id"]);
$message = "";


/* =========================
   UPDATE COMPLAINT
========================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $status = $_POST["status"];
    $remark = trim($_POST["remark"]);

    $resolution_proof = null;

    /* =========================
       HANDLE PROOF UPLOAD
    ========================= */

    if (isset($_FILES["resolution_proof"]) && $_FILES["resolution_proof"]["error"] == 0) {

        $file_name = $_FILES["resolution_proof"]["name"];
        $file_tmp = $_FILES["resolution_proof"]["tmp_name"];
        $file_size = $_FILES["resolution_proof"]["size"];

        $allowed_extensions = ["jpg", "jpeg", "png", "webp"];

        $file_extension = strtolower(
            pathinfo($file_name, PATHINFO_EXTENSION)
        );

        if (!in_array($file_extension, $allowed_extensions)) {

            $message = "Only JPG, JPEG, PNG and WEBP images are allowed.";

        } elseif ($file_size > 5 * 1024 * 1024) {

            $message = "Image size must be less than 5 MB.";

        } else {

            /* Create uploads folder if it does not exist */

            $upload_folder = "../uploads/resolution_proofs/";

            if (!is_dir($upload_folder)) {
                mkdir($upload_folder, 0777, true);
            }

            /* Generate unique filename */

            $new_file_name =
                "proof_" . $id . "_" . time() . "." . $file_extension;

            $upload_path = $upload_folder . $new_file_name;

            if (move_uploaded_file($file_tmp, $upload_path)) {

                $resolution_proof =
                    "uploads/resolution_proofs/" . $new_file_name;

                /* Proof uploaded means citizen verification is required */

                $status = "Awaiting Verification";

            } else {

                $message = "Failed to upload resolution proof.";

            }
        }
    }


    /* =========================
       UPDATE DATABASE
    ========================= */

    if ($message == "") {

        if ($resolution_proof != null) {

            $stmt = $conn->prepare(
                "UPDATE complaints
                 SET status = ?, resolution_proof = ?, verification_status = 'Pending'
                 WHERE id = ?"
            );

            $stmt->bind_param(
                "ssi",
                $status,
                $resolution_proof,
                $id
            );

        } else {

            $stmt = $conn->prepare(
                "UPDATE complaints
                 SET status = ?
                 WHERE id = ?"
            );

            $stmt->bind_param(
                "si",
                $status,
                $id
            );
        }


        if ($stmt->execute()) {

            /* Save update history */

            $update = $conn->prepare(
                "INSERT INTO complaint_updates
                (complaint_id, status, remark)
                VALUES (?, ?, ?)"
            );

            $update->bind_param(
                "iss",
                $id,
                $status,
                $remark
            );

            $update->execute();
            $update->close();

            $message = "Complaint updated successfully!";

        } else {

            $message = "Something went wrong.";

        }

        $stmt->close();
    }
}


/* =========================
   GET COMPLAINT
========================= */

$stmt = $conn->prepare(
    "SELECT complaint_id, category, title, description,
            location, status, resolution_proof,
            verification_status
     FROM complaints
     WHERE id = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: complaints.php");
    exit();
}

$complaint = $result->fetch_assoc();

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Update Complaint | CivicConnect</title>

    <link rel="stylesheet" href="../css/style.css">

    <style>

        body {
            margin: 0;
            background: #f4f7fc;
            font-family: Arial, Helvetica, sans-serif;
            color: #17365d;
        }

        .update-section {
            min-height: calc(100vh - 80px);
            padding: 45px 20px;
        }

        .update-container {
            max-width: 850px;
            margin: auto;
        }

        .update-card {
            background: white;
            border-radius: 22px;
            padding: 35px;
            border: 1px solid #e4ebf5;
            box-shadow: 0 12px 35px rgba(34, 70, 120, 0.09);
        }

        .page-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .page-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 15px;
            border-radius: 18px;
            background: #eaf2ff;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 29px;
        }

        .page-header h1 {
            margin: 0;
            font-size: 30px;
            color: #17365d;
        }

        .page-header p {
            color: #718096;
            margin-top: 9px;
        }

        .complaint-id {
            background: #f4f8ff;
            border: 1px solid #e0eaff;
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            color: #1769e0;
            font-weight: 700;
            margin-bottom: 25px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 25px;
        }

        .info-box {
            background: #f8faff;
            border: 1px solid #edf1f7;
            border-radius: 12px;
            padding: 17px;
        }

        .info-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #8294aa;
            margin-bottom: 7px;
        }

        .info-value {
            color: #314e68;
            font-size: 14px;
            line-height: 1.5;
        }

        .description-box {
            background: #f8faff;
            border: 1px solid #edf1f7;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 30px;
        }

        .update-area {
            border-top: 1px solid #edf1f7;
            padding-top: 28px;
        }

        .update-area h2 {
            margin: 0 0 20px;
            color: #17365d;
            font-size: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            color: #314e68;
            font-size: 14px;
        }

        .form-select,
        .form-textarea,
        .form-file {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #d8e1ef;
            background: #fbfdff;
            border-radius: 12px;
            padding: 14px 15px;
            font-size: 15px;
            color: #243b53;
        }

        .form-select {
            cursor: pointer;
        }

        .form-textarea {
            resize: vertical;
            min-height: 120px;
            line-height: 1.5;
        }

        .form-file {
            cursor: pointer;
        }

        .proof-info {
            background: #fff8e8;
            border: 1px solid #f2dfaa;
            color: #765d16;
            padding: 13px 15px;
            border-radius: 12px;
            font-size: 13px;
            margin-top: 8px;
        }

        .update-btn {
            width: 100%;
            border: none;
            border-radius: 12px;
            padding: 16px;
            background: linear-gradient(135deg, #1769e0, #287ff0);
            color: white;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 8px 18px rgba(23, 105, 224, 0.20);
            transition: 0.2s ease;
        }

        .update-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 22px rgba(23, 105, 224, 0.28);
        }

        .success-message {
            background: #eaf8ef;
            border: 1px solid #b9e5c8;
            color: #20733b;
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 14px;
            font-weight: 600;
        }

        .back-link {
            display: block;
            width: fit-content;
            margin: 25px auto 0;
            text-decoration: none;
            color: #55708f;
            font-size: 14px;
            font-weight: 700;
        }

        .back-link:hover {
            color: #1769e0;
        }

        @media (max-width: 650px) {

            .update-card {
                padding: 25px 18px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .page-header h1 {
                font-size: 26px;
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

        <a href="dashboard.php">Dashboard</a>

        <a href="complaints.php">Complaints</a>

        <a href="logout.php">Logout</a>

    </nav>

</header>


<main>

<section class="update-section">

<div class="update-container">

<div class="update-card">


    <div class="page-header">

        <div class="page-icon">
            🛠️
        </div>

        <h1>Update Complaint</h1>

        <p>
            Review the complaint and update its current status.
        </p>

    </div>


    <div class="complaint-id">

        Complaint ID:
        <?php echo htmlspecialchars($complaint["complaint_id"]); ?>

    </div>


    <?php if ($message != ""): ?>

        <div class="success-message">

            ✓ <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <div class="info-grid">


        <div class="info-box">

            <span class="info-label">
                Category
            </span>

            <div class="info-value">

                <?php
                echo htmlspecialchars($complaint["category"]);
                ?>

            </div>

        </div>


        <div class="info-box">

            <span class="info-label">
                Complaint Title
            </span>

            <div class="info-value">

                <?php
                echo htmlspecialchars($complaint["title"]);
                ?>

            </div>

        </div>


        <div class="info-box">

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


        <div class="info-box">

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


    <div class="update-area">

        <h2>
            Update Complaint
        </h2>


        <form method="POST"
              enctype="multipart/form-data">


            <div class="form-group">

                <label for="status">
                    Complaint Status
                </label>

                <select
                    name="status"
                    id="status"
                    class="form-select"
                    required
                >

                    <option value="Submitted"
                        <?php
                        if ($complaint["status"] == "Submitted")
                            echo "selected";
                        ?>>
                        Submitted
                    </option>


                    <option value="In Progress"
                        <?php
                        if ($complaint["status"] == "In Progress")
                            echo "selected";
                        ?>>
                        In Progress
                    </option>


                    <option value="Resolved"
                        <?php
                        if ($complaint["status"] == "Resolved")
                            echo "selected";
                        ?>>
                        Resolved
                    </option>


                    <option value="Awaiting Verification"
                        <?php
                        if ($complaint["status"] == "Awaiting Verification")
                            echo "selected";
                        ?>>
                        Awaiting Citizen Verification
                    </option>

                </select>

            </div>


            <!-- Resolution Proof -->

            <div class="form-group">

                <label for="resolution_proof">

                    📷 Resolution Proof

                </label>

                <input
                    type="file"
                    name="resolution_proof"
                    id="resolution_proof"
                    class="form-file"
                    accept=".jpg,.jpeg,.png,.webp"
                >

                <div class="proof-info">

                    Upload a photo showing that the reported issue
                    has been resolved. Maximum size: 5 MB.

                </div>

            </div>


            <!-- Admin Remark -->

            <div class="form-group">

                <label for="remark">
                    Admin Remark
                </label>

                <textarea
                    name="remark"
                    id="remark"
                    class="form-textarea"
                    rows="5"
                    placeholder="Enter an update or remark for the citizen..."
                ></textarea>

            </div>


            <button
                type="submit"
                class="update-btn"
            >

                Update Complaint

            </button>


        </form>


    </div>


    <a
        href="complaints.php"
        class="back-link"
    >

        ← Back to All Complaints

    </a>


</div>

</div>

</section>

</main>


</body>

</html>