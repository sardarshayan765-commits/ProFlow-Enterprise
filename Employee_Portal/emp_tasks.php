<?php
/**
 * ProFlow Enterprise - Employee Task Management
 * Fixed: Session ID, Database Table Mapping & Navigation
 */

require_once '../Core_System/db_connect.php';
session_start();

// 1. Session check - Login ID fix
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$emp_id = $_SESSION['user_id'];
$message = "";

// 2. Fetch Employee Details from USERS table (Kyunki employees table empty hai)
$stmt_emp = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt_emp->execute([$emp_id]);
$emp_data = $stmt_emp->fetch() ?: ['name' => 'Employee', 'email' => ''];

/**
 * 3. TASK UPDATE LOGIC
 */
if (isset($_POST['update_status'])) {
    $task_id = $_POST['task_id'];
    $new_status = $_POST['status'];
    
    // Updated to use 'emp_tasks' table and 'employee_id' column
    $stmt_upd = $pdo->prepare("UPDATE emp_tasks SET status = ? WHERE id = ? AND employee_id = ?");
    $stmt_upd->execute([$new_status, $task_id, $emp_id]);
    $message = "<div class='p-4 mb-6 bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 rounded-2xl animate-pulse'>
                    <i class='fas fa-check-circle mr-2'></i> Status updated to: <b>$new_status</b>
                </div>";
}

/**
 * 4. FETCH TASKS (Directly from emp_tasks)
 */
$stmt_tasks = $pdo->prepare("SELECT * FROM emp_tasks WHERE employee_id = ? ORDER BY id DESC");
$stmt_tasks->execute([$emp_id]);
$tasks = $stmt_tasks->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks | ProFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #0f172a; color: #e2e8f0; }
        .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .sidebar-link:hover { background: rgba(59, 130, 246, 0.1); color: #60a5fa; }
    </style>
</head>
<body class="min-h-screen flex">

    <nav class="w-64 border-r border-white/5 bg-slate-900/50 hidden md:flex flex-col sticky top-0 h-screen">
        <div class="p-8">
            <h1 class="text-2xl font-black tracking-tighter text-blue-500">PROFLOW</h1>
        </div>
        <div class="flex-1 px-4 space-y-2">
            <a href="emp_dashboard.php" class="sidebar-link flex items-center gap-3 p-3 text-slate-400 rounded-xl transition font-medium">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="emp_tasks.php" class="flex items-center gap-3 p-3 bg-blue-600/10 text-blue-400 rounded-xl font-bold">
                <i class="fas fa-tasks"></i> My Tasks
            </a>
            <a href="emp_leaves.php" class="sidebar-link flex items-center gap-3 p-3 text-slate-400 rounded-xl transition font-medium">
                <i class="fas fa-calendar-alt"></i> Leave Requests
            </a>
        </div>
       <div class="px-0 pb-6 mt-auto">
                <a href="logout.php" class="flex items-center justify-center gap-2 w-full py-3 bg-red-500/10 hover:bg-red-600 text-red-500 hover:text-white border border-red-500/20 rounded-xl transition-all duration-300 font-bold text-sm shadow-lg shadow-red-500/5">
                    <i class="fas fa-power-off"></i>
                    Logout System
                </a>
            </div>
    </nav>

    <main class="flex-1 p-6 md:p-10 overflow-y-auto">
        <div class="max-w-6xl mx-auto">
            <header class="flex justify-between items-center mb-10">
                <div>
                    <h1 class="text-3xl font-black text-white">Your Tasks</h1>
                    <p class="text-slate-500">Manage your daily assignments</p>
                </div>
                <div class="glass px-6 py-3 rounded-2xl border-white/10 hidden sm:block">
                    <span class="text-blue-400 font-bold"><?= htmlspecialchars($emp_data['name']) ?></span>
                </div>
            </header>

            <?= $message ?>

            <?php if (empty($tasks)): ?>
                <div class="glass p-12 rounded-[2rem] text-center">
                    <i class="fas fa-clipboard-list text-5xl text-slate-700 mb-4"></i>
                    <h2 class="text-xl font-bold text-slate-400">No tasks assigned yet.</h2>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($tasks as $task): 
                        $title = $task['task_title'] ?? $task['title'] ?? 'Untitled Task';
                        $desc = $task['task_description'] ?? $task['description'] ?? 'No description available.';
                        $status = $task['status'] ?? 'Pending';
                    ?>
                        <div class="glass rounded-[2rem] p-6 border-t-4 border-blue-500 hover:scale-[1.02] transition-transform flex flex-col">
                            <div class="flex justify-between mb-4">
                                <span class="text-[10px] bg-white/10 px-3 py-1 rounded-full uppercase font-bold tracking-widest text-slate-400">
                                    #<?= $task['id'] ?>
                                </span>
                                <span class="text-[11px] font-bold <?= $status == 'Completed' ? 'text-emerald-400' : 'text-blue-400' ?>">
                                    <?= $status ?>
                                </span>
                            </div>

                            <h3 class="text-xl font-bold text-white mb-3"><?= htmlspecialchars($title) ?></h3>
                            <p class="text-slate-400 text-sm mb-6 line-clamp-3"><?= htmlspecialchars($desc) ?></p>

                            <form method="POST" class="mt-auto pt-4 border-t border-white/5 flex gap-2">
                                <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                <select name="status" class="flex-grow bg-slate-800 border border-white/10 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-blue-500">
                                    <option value="Pending" <?= $status == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="In Progress" <?= $status == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="Completed" <?= $status == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                </select>
                                <button type="submit" name="update_status" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-xl transition">
                                    <i class="fas fa-save"></i>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>