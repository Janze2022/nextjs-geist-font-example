<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/functions.php';

requireAdmin();

$message = '';
$action = $_GET['action'] ?? '';
$key = $_GET['key'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $setting_key = $_POST['setting_key'] ?? '';
    $setting_value = $_POST['setting_value'] ?? '';

    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (:key, :value)");
        $stmt->execute(['key' => $setting_key, 'value' => $setting_value]);
        $message = "Setting added successfully.";
    } elseif ($action === 'edit' && $key) {
        $stmt = $pdo->prepare("UPDATE system_settings SET setting_value = :value WHERE setting_key = :key");
        $stmt->execute(['value' => $setting_value, 'key' => $key]);
        $message = "Setting updated successfully.";
    }
    header("Location: system_settings.php");
    exit;
}

if ($action === 'delete' && $key) {
    $stmt = $pdo->prepare("DELETE FROM system_settings WHERE setting_key = :key");
    $stmt->execute(['key' => $key]);
    $message = "Setting deleted successfully.";
    header("Location: system_settings.php");
    exit;
}

// Fetch all settings
$stmt = $pdo->query("SELECT * FROM system_settings ORDER BY setting_key");
$settings = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../templates/header.php';
include __DIR__ . '/../templates/navbar.php';
?>

<h1>System Settings</h1>

<?php if ($message): ?>
    <p style="color: green;"><?php echo sanitize($message); ?></p>
<?php endif; ?>

<a href="system_settings.php?action=add">Add New Setting</a>

<?php if ($action === 'add' || ($action === 'edit' && $key)): ?>
    <?php
    $setting = ['setting_key' => '', 'setting_value' => ''];
    if ($action === 'edit' && $key) {
        $stmt = $pdo->prepare("SELECT * FROM system_settings WHERE setting_key = :key");
        $stmt->execute(['key' => $key]);
        $setting = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$setting) {
            echo "<p>Setting not found.</p>";
            exit;
        }
    }
    ?>
    <form method="post" action="system_settings.php?action=<?php echo $action; ?><?php if ($key) echo '&key=' . urlencode($key); ?>">
        <label>Key: <input type="text" name="setting_key" value="<?php echo sanitize($setting['setting_key']); ?>" <?php if ($action === 'edit') echo 'readonly'; ?> required></label><br><br>
        <label>Value: <input type="text" name="setting_value" value="<?php echo sanitize($setting['setting_value']); ?>" required></label><br><br>
        <button type="submit"><?php echo ucfirst($action); ?> Setting</button>
    </form>
    <a href="system_settings.php">Back to list</a>
<?php else: ?>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Key</th>
                <th>Value</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($settings as $setting): ?>
                <tr>
                    <td><?php echo sanitize($setting['setting_key']); ?></td>
                    <td><?php echo sanitize($setting['setting_value']); ?></td>
                    <td>
                        <a href="system_settings.php?action=edit&key=<?php echo urlencode($setting['setting_key']); ?>">Edit</a> |
                        <a href="system_settings.php?action=delete&key=<?php echo urlencode($setting['setting_key']); ?>" onclick="return confirm('Are you sure you want to delete this setting?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php
include __DIR__ . '/../templates/footer.php';
?>
