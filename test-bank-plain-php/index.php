<?php
session_start();

// Load database config
$dbConfig = require __DIR__ . '/config/database.php';

// Database connection
try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Simple router
$page = $_GET['page'] ?? 'login';

// Authentication check
$publicPages = ['login', 'logout'];
if (!in_array($page, $publicPages) && !isset($_SESSION['user'])) {
    header('Location: ?page=login');
    exit;
}

// Handle login
if ($page === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND password = MD5(:password)");
    $stmt->execute(['username' => $username, 'password' => $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
        ];
        header('Location: ?page=dashboard');
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}

// Handle logout
if ($page === 'logout') {
    session_destroy();
    header('Location: ?page=login');
    exit;
}

// Include page templates
function renderHeader() {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>PHP Test Bank App</title>
        <link href="public/css/style.css" rel="stylesheet" />
    </head>
    <body>
    <?php
}

function renderFooter() {
    ?>
    </body>
    </html>
    <?php
}

// Render fixed navbar and topbar
function renderNavbar($user) {
    ?>
    <div class="fixed-left-navbar">
        <h2>Test Bank</h2>
        <nav>
            <ul>
                <li><a href="?page=dashboard">Dashboard</a></li>
                <?php if ($user['role'] === 'admin'): ?>
                    <li><a href="?page=manage_teachers">Manage Teachers</a></li>
                    <li><a href="?page=system_settings">System Settings</a></li>
                <?php endif; ?>
                <?php if ($user['role'] === 'teacher'): ?>
                    <li><a href="?page=question_bank">Question Bank</a></li>
                    <li><a href="?page=exam_builder">Exam Builder</a></li>
                    <li><a href="?page=reporting">Reporting</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <div class="fixed-topbar">
        <div>Welcome, <?php echo htmlspecialchars($user['username']); ?> | <a href="?page=logout" style="color: white;">Logout</a></div>
    </div>
    <div class="content-area">
    <?php
}

// Page controllers (simplified placeholders)
switch ($page) {
    case 'login':
        renderHeader();
        ?>
        <h1>Login</h1>
        <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="post" action="?page=login">
            <label>Username: <input type="text" name="username" required></label><br><br>
            <label>Password: <input type="password" name="password" required></label><br><br>
            <button type="submit">Login</button>
        </form>
        <?php
        renderFooter();
        break;

    case 'dashboard':
        renderHeader();
        renderNavbar($_SESSION['user']);
        echo "<h1>Dashboard</h1>";
        // TODO: Add dashboard content and analytics
        echo "</div>";
        renderFooter();
        break;

    case 'manage_teachers':
        if ($_SESSION['user']['role'] !== 'admin') {
            header('HTTP/1.1 403 Forbidden');
            exit('Access denied');
        }
        renderHeader();
        renderNavbar($_SESSION['user']);
        echo "<h1>Manage Teachers</h1>";
        // TODO: Add CRUD for teachers
        echo "</div>";
        renderFooter();
        break;

    case 'system_settings':
        if ($_SESSION['user']['role'] !== 'admin') {
            header('HTTP/1.1 403 Forbidden');
            exit('Access denied');
        }
        renderHeader();
        renderNavbar($_SESSION['user']);
        echo "<h1>System Settings</h1>";
        // TODO: Add system settings management
        echo "</div>";
        renderFooter();
        break;

    case 'question_bank':
        if ($_SESSION['user']['role'] !== 'teacher') {
            header('HTTP/1.1 403 Forbidden');
            exit('Access denied');
        }
        renderHeader();
        renderNavbar($_SESSION['user']);
        echo "<h1>Question Bank</h1>";
        // TODO: Add question bank CRUD
        echo "</div>";
        renderFooter();
        break;

    case 'exam_builder':
        if ($_SESSION['user']['role'] !== 'teacher') {
            header('HTTP/1.1 403 Forbidden');
            exit('Access denied');
        }
        renderHeader();
        renderNavbar($_SESSION['user']);
        echo "<h1>Exam Builder</h1>";
        // TODO: Add exam builder and PDF generation
        echo "</div>";
        renderFooter();
        break;

    case 'reporting':
        if ($_SESSION['user']['role'] !== 'teacher') {
            header('HTTP/1.1 403 Forbidden');
            exit('Access denied');
        }
        renderHeader();
        renderNavbar($_SESSION['user']);
        echo "<h1>Reporting</h1>";
        // TODO: Add reporting and past exam downloads
        echo "</div>";
        renderFooter();
        break;

    default:
        header('HTTP/1.1 404 Not Found');
        echo "Page not found";
        break;
}
