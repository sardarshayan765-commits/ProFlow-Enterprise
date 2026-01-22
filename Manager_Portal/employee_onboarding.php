<?php
/**
 * CORE_SYS - Employee Onboarding (ProFlow Enterprise)
 * Target Table: users (With Manager/Employee Role Selection)
 */

require_once '../Core_System/db_connect.php';
require_once '../Core_System/functions.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_emp'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password']; 
    $gender = $_POST['gender'];
    $shift = $_POST['shift'];
    $role = $_POST['role']; // Role now comes from the form

    try {
        // Email check in users table
        $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $checkEmail->execute([$email]);
        
        if ($checkEmail->rowCount() > 0) {
            $message = "<div class='mb-6 p-4 bg-rose-500/20 border border-rose-500/50 text-rose-200 rounded-2xl flex items-center gap-3'>
                            <i class='fas fa-exclamation-triangle'></i>
                            <span>Error: Ye email pehle se 'users' table mein maujood hai!</span>
                        </div>";
        } else {
            // INSERT query including the selected role
           // Is line ko replace karein (Temporary testing ke liye)
$stmt = $pdo->prepare("INSERT INTO users (`name`, `email`, `password`, `role`, `gender`, `shift`) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $email, $password, $role, $gender, $shift])) {
                $message = "<div class='mb-6 p-4 bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 rounded-2xl flex items-center gap-3'>
                                <i class='fas fa-check-circle'></i>
                                <span>Success: <b>$name</b> ko bator <b>$role</b> register kar diya gaya hai.</span>
                            </div>";
                            
            }
        }
    } catch (Exception $e) {
        $message = "<div class='mb-6 p-4 bg-rose-500/20 border border-rose-500/50 text-rose-200 rounded-2xl flex items-center gap-3'>
                        <i class='fas fa-bug'></i>
                        <span>Database Error: " . $e->getMessage() . "</span>
                        
                    </div>";
                    
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Onboarding | ProFlow Enterprise</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap');
        body { font-family: 'Outfit', sans-serif; background: #0f172a; color: #e2e8f0; }
        .glass-card { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .sidebar-active { background: linear-gradient(90deg, #3b82f6 0%, transparent 100%); border-left: 4px solid #3b82f6; }
        .input-field { background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.1); color: white; transition: all 0.3s ease; }
        .input-field:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2); }
    </style>
</head>
<body class="flex min-h-screen">

    <aside class="w-64 glass-card sticky top-0 h-screen flex flex-col shrink-0">
        <div class="p-8 text-center">
            <h2 class="text-xl font-bold bg-gradient-to-r from-blue-400 to-emerald-400 bg-clip-text text-transparent">PROFLOW</h2>
        </div>
        <nav class="flex-1 px-4 space-y-2">
            
            <a href="manager_dashboard.php" class="flex items-center gap-3 py-3 px-4 rounded-xl sidebar-active text-white">
                <i class="fas fa-chart-pie w-5"></i> Dashboard
            </a>
            <a href="task_management.php" class="flex items-center gap-3 py-3 px-4 rounded-xl sidebar-active text-white">
                        <i class="fas fa-tasks w-5 text-blue-500"></i> Task Manager
                    </a>
             <a href="leave_approvals.php" class="flex items-center gap-3 py-3 px-4 rounded-xl sidebar-active text-white">
                <i class="fas fa-calendar-check w-5"></i> Leave Requests
            </a>
            <a href="employee_onboarding.php" class="flex items-center gap-3 py-3 px-4 rounded-xl sidebar-active text-white transition">
                <i class="fas fa-user-plus w-5"></i> Onboarding
            </a>
        </nav>
    </aside>

    <main class="flex-1 p-10 flex flex-col items-center">
        <div class="w-full max-w-2xl">
            <header class="mb-10 text-center">
                <h1 class="text-3xl font-bold text-white tracking-tight">User Onboarding</h1>
                <p class="text-slate-400 mt-2">Add Managers or Employees to the System</p>
            </header>

            <?= $message ?>

            <div class="glass-card p-8 rounded-[2rem] shadow-2xl">
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Full Name</label>
                            <input type="text" name="name" placeholder="John Doe" required class="w-full input-field p-4 rounded-xl text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Email Address</label>
                            <input type="email" name="email" placeholder="john@company.com" required class="w-full input-field p-4 rounded-xl text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Password</label>
                            <input type="password" name="password" placeholder="••••••••" required class="w-full input-field p-4 rounded-xl text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">System Role</label>
                            <select name="role" required class="w-full input-field p-4 rounded-xl text-sm appearance-none">
                                <option value="employee">Employee</option>
                                <option value="manager">Manager</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Gender</label>
                            <select name="gender" class="w-full input-field p-4 rounded-xl text-sm appearance-none">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2">Preferred Shift</label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="cursor-pointer">
                                    <input type="radio" name="shift" value="Day" checked class="hidden peer">
                                    <div class="p-4 rounded-xl border border-white/10 bg-white/5 text-center peer-checked:border-blue-500 peer-checked:bg-blue-500/10 transition">
                                        <i class="fas fa-sun mb-2 block text-amber-400"></i>
                                        <span class="text-xs font-bold">Day</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="shift" value="Night" class="hidden peer">
                                    <div class="p-4 rounded-xl border border-white/10 bg-white/5 text-center peer-checked:border-indigo-500 peer-checked:bg-indigo-500/10 transition">
                                        <i class="fas fa-moon mb-2 block text-indigo-400"></i>
                                        <span class="text-xs font-bold">Night</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="add_emp" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold py-4 rounded-2xl shadow-lg shadow-blue-500/20 transition-all transform hover:-translate-y-1">
                        <i class="fas fa-user-plus mr-2"></i> Confirm Registration
                    </button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>