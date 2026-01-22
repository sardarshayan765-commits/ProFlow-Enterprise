<?php
/**
 * ProFlow Enterprise - Employee Dashboard
 * Version 2.2 - User Table Bridge Integration
 */

// Error reporting on (Debugging ke liye)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../Core_System/db_connect.php';
session_start();

/** * IMPORTANT: 
 * Kyuki employees table empty hai, isliye hum $_SESSION['user_id'] use karenge 
 * jo users table ki primary ID hai.
 */
$emp_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : (isset($_SESSION['emp_id']) ? $_SESSION['emp_id'] : 1);

// 1. Fetch Details from USERS table (Kyunki employees table khali hai)
$employee = ['name' => 'Employee', 'shift' => 'General', 'email' => 'contact@proflow.com'];
$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->execute([$emp_id]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);

if ($res) {
    $employee['name'] = $res['name'];
    $employee['email'] = $res['email'];
    // Shift column users table mein nahi hai, toh default 'General' rakha hai
    $employee['shift'] = 'General'; 
}

// 2. Task Count (Directly from emp_tasks using users.id)
$stmt_tasks = $pdo->prepare("SELECT COUNT(*) FROM emp_tasks WHERE employee_id = ? AND status != 'Completed'");
$stmt_tasks->execute([$emp_id]);
$taskCount = $stmt_tasks->fetchColumn();

// 3. Leave Status (Directly from emp_leaves)
$stmt_leave = $pdo->prepare("SELECT status FROM emp_leaves WHERE employee_id = ? ORDER BY id DESC LIMIT 1");
$stmt_leave->execute([$emp_id]);
$lastLeave = $stmt_leave->fetchColumn() ?: "No History";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProFlow | Employee Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; background: #0f172a; color: #f8fafc; }
        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: transform 0.3s ease;
        }
        .glass-card:hover { transform: translateY(-5px); }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    </style>
</head>
<body class="min-h-screen flex">

    <nav class="w-64 border-r border-white/5 bg-slate-900/50 hidden md:flex flex-col">
        <div class="p-8">
            <h1 class="text-2xl font-black tracking-tighter text-blue-500">PROFLOW</h1>
        </div>
        <div class="flex-1 px-4 space-y-2">
            <a href="#" class="flex items-center gap-3 p-3 bg-blue-600/10 text-blue-400 rounded-xl font-semibold">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="emp_tasks.php" class="flex items-center gap-3 p-3 text-slate-400 hover:text-white transition">
                <i class="fas fa-tasks"></i> My Tasks
            </a>
            <a href="emp_leaves.php" class="flex items-center gap-3 p-3 text-slate-400 hover:text-white transition">
                <i class="fas fa-calendar-alt"></i> Leave Requests
            </a>
        </div>
        <div class="p-6 border-t border-white/5">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center font-bold">
                    <?php echo strtoupper(substr($employee['name'], 0, 1)); ?>
                </div>
                <div>
                    <p class="text-xs font-bold"><?php echo htmlspecialchars($employee['name']); ?></p>
                    <p class="text-[10px] text-slate-500">User ID: #<?php echo $emp_id; ?></p>
                </div>
            </div>
            <div class="px-0 pb-6 mt-auto">
                <a href="logout.php" class="flex items-center justify-center gap-2 w-full py-3 bg-red-500/10 hover:bg-red-600 text-red-500 hover:text-white border border-red-500/20 rounded-xl transition-all duration-300 font-bold text-sm shadow-lg shadow-red-500/5">
                    <i class="fas fa-power-off"></i>
                    Logout System
                </a>
            </div>
        </div>
    </nav>

    <main class="flex-1 p-6 md:p-10 overflow-y-auto">
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
            <div>
                <h2 class="text-3xl font-extrabold tracking-tight">Dashboard Overview</h2>
                <p class="text-slate-400">Welcome back, <?php echo htmlspecialchars(explode(' ', $employee['name'])[0]); ?>.</p>
            </div>
            <div class="flex items-center gap-4 bg-slate-800/40 p-2 rounded-2xl border border-white/5">
                <div class="px-4 py-2">
                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Server Status</p>
                    <p class="text-xs text-emerald-400 flex items-center gap-2">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span> Operational
                    </p>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
            <div class="glass-card p-6 rounded-3xl">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-3 bg-blue-500/10 rounded-2xl text-blue-500"><i class="fas fa-briefcase fa-lg"></i></div>
                    <span class="text-xs font-bold bg-blue-500/20 text-blue-400 px-2 py-1 rounded-md">ACTIVE</span>
                </div>
                <p class="text-slate-400 text-sm font-medium">Pending Tasks</p>
                <h4 class="text-4xl font-black mt-1"><?php echo $taskCount; ?></h4>
            </div>

            <div class="glass-card p-6 rounded-3xl">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-3 bg-emerald-500/10 rounded-2xl text-emerald-500"><i class="fas fa-clock fa-lg"></i></div>
                    <span class="text-xs font-bold bg-emerald-500/20 text-emerald-400 px-2 py-1 rounded-md">LIVE</span>
                </div>
                <p class="text-slate-400 text-sm font-medium">Work Shift</p>
                <h4 class="text-2xl font-black mt-2"><?php echo htmlspecialchars($employee['shift']); ?></h4>
            </div>

            <div class="glass-card p-6 rounded-3xl">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-3 bg-purple-500/10 rounded-2xl text-purple-500"><i class="fas fa-file-signature fa-lg"></i></div>
                    <span class="text-xs font-bold bg-purple-500/20 text-purple-400 px-2 py-1 rounded-md">LATEST</span>
                </div>
                <p class="text-slate-400 text-sm font-medium">Leave Status</p>
                <h4 class="text-2xl font-black mt-2"><?php echo htmlspecialchars($lastLeave); ?></h4>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 glass-card p-8 rounded-[2.5rem]">
                <h3 class="text-xl font-bold mb-6 flex items-center gap-3">
                    <i class="fas fa-rocket text-blue-500"></i> Project Announcement
                </h3>
                <div class="bg-blue-600/5 border border-blue-500/10 p-6 rounded-2xl">
                    <h4 class="font-bold text-blue-400 mb-2">Notice for Employees</h4>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        ProFlow Enterprise is moving to a new cloud-based infrastructure this weekend. 
                        Please ensure all your local task logs are synced with the dashboard before Friday, 6:00 PM.
                    </p>
                </div>
            </div>
            
            <div class="glass-card p-8 rounded-[2.5rem] flex flex-col justify-center items-center text-center">
                <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mb-4 shadow-xl shadow-blue-600/20">
                    <i class="fas fa-user-plus text-white"></i>
                </div>
                <h3 class="font-bold">Need Help?</h3>
                <p class="text-xs text-slate-500 mt-2 mb-6 px-4">Contact HR if you face any issues with your shift or payroll.</p>
                <a href="mailto:sardarshayan765@gmail.com?subject=Support%20Request%20-%20ProFlow&body=Hello%20Manager,%20I%20need%20help%20with..." class="w-full">
    <button class="w-full py-3 bg-white text-slate-900 font-bold text-xs rounded-xl hover:bg-slate-200 transition">
        SUPPORT PORTAL
    </button>
            </a>
            </div>
        </div>
    </main>

</body>
</html>