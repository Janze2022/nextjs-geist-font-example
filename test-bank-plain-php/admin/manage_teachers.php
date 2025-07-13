<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/functions.php';

requireAdmin();

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = 'teacher'; // Only teachers managed here

    if ($action === 'add') {
        // Add new teacher
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:username, MD5(:password), :role)");
        $stmt->execute(['username' => $username, 'password' => $password, 'role' => $role]);
        $message = "Teacher added successfully.";
    } elseif ($action === 'edit' && $id) {
        // Update teacher
        if (!empty($password)) {
            $stmt = $pdo->prepare("UPDATE users SET username = :username, password = MD5(:password) WHERE id = :id AND role = 'teacher'");
            $stmt->execute(['username' => $username, 'password' => $password, 'id' => $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username = :username WHERE id = :id AND role = 'teacher'");
            $stmt->execute(['username' => $username, 'id' => $id]);
        }
        $message = "Teacher updated successfully.";
    }
    header("Location: manage_teachers.php");
    exit;
}

if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND role = 'teacher'");
    $stmt->execute(['id' => $id]);
    $message = "Teacher deleted successfully.";
    header("Location: manage_teachers.php");
    exit;
}

// Fetch all teachers
$stmt = $pdo->query("SELECT id, username FROM users WHERE role = 'teacher' ORDER BY username");
$teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../templates/header.php';
include __DIR__ . '/../templates/navbar.php';
?>

<h1>Manage Teachers</h1>

<?php if ($message): ?>
    <p style="color: green;"><?php echo sanitize($message); ?></p>
<?php endif; ?>

<a href="manage_teachers.php?action=add">Add New Teacher</a>

<?php if ($action === 'add' || ($action === 'edit' && $id)): ?>
    <?php
    $teacher = ['username' => ''];
    if ($action === 'edit' && $id) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id AND role = 'teacher'");
        $stmt->execute(['id' => $id]);
        $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$teacher) {
            echo "<p>Teacher not found.</p>";
            exit;
        }
    }
    ?>
    <form method="post" action="manage_teachers.php?action=<?php echo $action; ?><?php if ($id) echo '&id=' . $id; ?>">
        <label>Username: <input type="text" name="username" value="<?php echo sanitize($teacher['username']); ?>" required></label><br><br>
        <label>Password: <input type="password" name="password" <?php if ($action === 'add') echo 'required'; ?>></label><br><br>
        <button type="submit"><?php echo ucfirst($action); ?> Teacher</button>
    </form>
    <a href="manage_teachers.php">Back to list</a>
<?php else: ?>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Username</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($teachers as $teacher): ?>
                <tr>
                    <td><?php echo sanitize($teacher['username']); ?></td>
                    <td>
                        <a href="manage_teachers.php?action=edit&id=<?php echo $teacher['id']; ?>">Edit</a> |
                        <a href="manage_teachers.php?action=delete&id=<?php echo $teacher['id']; ?>" onclick="return confirm('Are you sure you want to delete this teacher?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php
include __DIR__ . '/../templates/footer.php';
?>
