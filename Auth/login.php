<?php
session_start();

// 1. Database Connection
$conn = new mysqli("localhost", "root", "", "proflow_enterprise_db");

// Connection Check
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";

// 2. Login Logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    // Query Updated: Hum users table se ID bhi uthayenge
    $sql = "SELECT id, email, role, name FROM users WHERE email='$email' AND password='$password' AND role='$role'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // --- YE CHAR LINES SAB SE ZAROORI HAIN ---
        $_SESSION['user_id'] = $user['id'];       // Ab employee ki apni ID save hogi
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        // ---------------------------------------

        // Redirect based on role
        if (strtolower($user['role']) == "manager") {
            header("Location: ../Manager_Portal/manager_dashboard.php");
        } else {
            header("Location: ../Employee_Portal/emp_dashboard.php");
        }
        exit();
    } else {
        $error_message = "Invalid Credentials or Role!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> PROFLOW</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #020617; font-family: sans-serif; }
        .glass { background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); }
        input:checked + div { border-color: #3b82f6; background: rgba(59, 130, 246, 0.1); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md glass p-8 rounded-[2rem] shadow-2xl">
        <h2 class="text-xl font-bold bg-gradient-to-r from-blue-400 to-emerald-400 bg-clip-text text-transparent" style="text-align: center;">
                            <b>PROFLOW</b>
                        </h2>
        <!-- <h2 class="text-2xl font-bold text-white mb-2 text-center">NEXUS LOGIN</h2> -->
        <p class="text-slate-400 text-sm text-center mb-8">Access your workspace</p>

        <?php if($error_message): ?>
            <div class="bg-red-500/10 border border-red-500/50 text-red-500 text-xs p-3 rounded-lg mb-6 text-center">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="flex gap-4 mb-6">
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="role" value="employee" checked class="hidden">
                    <div class="border border-white/10 p-3 rounded-xl text-center text-slate-400 text-xs font-bold uppercase tracking-wider transition-all">Employee</div>
                </label>
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="role" value="manager" class="hidden">
                    <div class="border border-white/10 p-3 rounded-xl text-center text-slate-400 text-xs font-bold uppercase tracking-wider transition-all">Manager</div>
                </label>
            </div>

            <div class="space-y-4 mb-6">
                <input type="email" name="email" placeholder="Email Address" required 
                       class="w-full bg-white/5 border border-white/10 p-4 rounded-xl text-white outline-none focus:border-blue-500 transition-all">
                
                <input type="password" name="password" placeholder="Password" required 
                       class="w-full bg-white/5 border border-white/10 p-4 rounded-xl text-white outline-none focus:border-blue-500 transition-all">
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 rounded-xl shadow-lg transition-all">
                AUTHORIZE ACCESS
            </button>
        </form>
    </div>
</body>
</html>
    
