<?php
$user = $_SESSION['user'] ?? null;
?>
<div class="fixed-left-navbar">
    <h2>Test Bank</h2>
    <nav>
        <ul>
            <li><a href="/index.php?page=dashboard">Dashboard</a></li>
            <?php if ($user && $user['role'] === 'admin'): ?>
                <li><a href="/admin/manage_teachers.php">Manage Teachers</a></li>
                <li><a href="/admin/system_settings.php">System Settings</a></li>
            <?php endif; ?>
            <?php if ($user && $user['role'] === 'teacher'): ?>
                <li><a href="/teacher/question_bank.php">Question Bank</a></li>
                <li><a href="/teacher/exam_builder.php">Exam Builder</a></li>
                <li><a href="/teacher/reporting.php">Reporting</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
<div class="fixed-topbar">
    <div>Welcome, <?php echo htmlspecialchars($user['username']); ?> | <a href="/auth.php?action=logout" style="color: white;">Logout</a></div>
</div>
<div class="content-area">
