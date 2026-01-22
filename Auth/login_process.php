<?php
session_start();

// 1. Database connection (Apne details yahan likhen)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "proflow_enterprise_db"; // Apne database ka naam yahan likhen

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $pass  = $_POST['password'];
    $role  = $_POST['role']; // 'employee' ya 'manager'

    // 2. Query to check user
    // Note: Asli project mein password_hash() use karna chahiye, ye simple check hai:
    $sql = "SELECT * FROM users WHERE email='$email' AND password='$pass' AND role='$role'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Login Successful
        $row = $result->fetch_assoc();
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_role'] = $row['role'];

        // 3. Role-based Redirect Logic
        if ($user['role'] == "manager") {
    // ../ ka matlab hai 'Auth' folder se bahar nikal kar 'Manager_Portal' mein jao
    header("Location: ../Manager_Portal/manager_dashboard.php");
    exit();
} else {
    header("Location: ../Employee_Portal/emp_dashboard.php");
    exit();
}
     else {
        // Agar login galat ho
        echo "<script>alert('Invalid Email, Password or Role!'); window.location='index.php';</script>";
    }
}
$conn->close();
?>
