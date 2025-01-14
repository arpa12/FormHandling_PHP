<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $server = "localhost";
    $username = "root";
    $password = "";
    $dbname = "travel_booking";

    // Connect to the database
    $conn = new mysqli($server, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection error: " . $conn->connect_error);
    }

    // Collect form data and validate
    $fullName = $_POST['fullName'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $destination = $_POST['destination'] ?? '';
    $departureDate = $_POST['departureDate'] ?? '';
    $returnDate = $_POST['returnDate'] ?? '';
    $travelMode = $_POST['travelMode'] ?? '';
    $travelers = $_POST['travelers'] ?? '';
    $accommodation = $_POST['accommodation'] ?? '';
    $roomType = $_POST['roomType'] ?? '';
    $specialRequests = $_POST['specialRequests'] ?? '';
    $emergencyName = $_POST['emergencyName'] ?? '';
    $emergencyPhone = $_POST['emergencyPhone'] ?? '';
    $agreement = isset($_POST['agreement']) && $_POST['agreement'] === "on" ? 1 : 0;

    // Check if required fields are provided
    if (empty($fullName) || empty($email) || empty($phone) || empty($dob)) {
        die("Please fill in all required fields.");
    }

    // Insert user information
    $userSql = "INSERT INTO users (full_name, email, phone, date_of_birth) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($userSql);
    if ($stmt === false) {
        die('MySQL Error: ' . $conn->error);
    }
    $stmt->bind_param("ssss", $fullName, $email, $phone, $dob);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $user_id = $stmt->insert_id; // Get the last inserted user_id
    } else {
        die("Error inserting user: " . $stmt->error);
    }

    // Insert travel details
    $travelSql = "INSERT INTO travel_details (user_id, destination, departure_date, return_date, travel_mode, travelers) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($travelSql);
    $stmt->bind_param("issssi", $user_id, $destination, $departureDate, $returnDate, $travelMode, $travelers);
    $stmt->execute();
    if ($stmt->affected_rows == 0) {
        die("Error inserting travel details: " . $stmt->error);
    }

    // Insert accommodation details
    $accommodationSql = "INSERT INTO accommodations (user_id, required, room_type) VALUES (?, ?, ?)";
    $required = $accommodation === 'yes' ? 'yes' : 'no'; // Convert value to 'yes' or 'no'
    $stmt = $conn->prepare($accommodationSql);
    $stmt->bind_param("iss", $user_id, $required, $roomType);
    $stmt->execute();
    if ($stmt->affected_rows == 0) {
        die("Error inserting accommodation details: " . $stmt->error);
    }

    // Insert special requests
    $specialRequestsSql = "INSERT INTO special_requests (user_id, special_request) VALUES (?, ?)";
    $stmt = $conn->prepare($specialRequestsSql);
    $stmt->bind_param("is", $user_id, $specialRequests);
    $stmt->execute();
    if ($stmt->affected_rows == 0) {
        die("Error inserting special requests: " . $stmt->error);
    }

    // Insert emergency contact
    $emergencyContactSql = "INSERT INTO emergency_contacts (user_id, contact_name, contact_phone) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($emergencyContactSql);
    $stmt->bind_param("iss", $user_id, $emergencyName, $emergencyPhone);
    $stmt->execute();
    if ($stmt->affected_rows == 0) {
        die("Error inserting emergency contact: " . $stmt->error);
    }

    // Insert agreement (optional field)
    if ($agreement) {
        $agreementSql = "INSERT INTO agreements (user_id, agreed) VALUES (?, 'yes')";
        $stmt = $conn->prepare($agreementSql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            die("Error inserting agreement: " . $stmt->error);
        }
    }

    $stmt->close();
    $conn->close();

    echo "Booking details submitted successfully!";
}
?>
