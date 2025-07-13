<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/functions.php';
require_once __DIR__ . '/../src/import_export.php';

requireTeacher();

$userId = $_SESSION['user']['id'];
$message = '';

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'export') {
        exportQuestionBank($userId);
    } elseif ($action === 'import' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] === UPLOAD_ERR_OK) {
            $jsonData = file_get_contents($_FILES['import_file']['tmp_name']);
            if (importQuestionBank($userId, $jsonData)) {
                $message = "Question bank imported successfully.";
            } else {
                $message = "Failed to import question bank. Invalid file format.";
            }
        } else {
            $message = "File upload error.";
        }
    }
}

include __DIR__ . '/../templates/header.php';
include __DIR__ . '/../templates/navbar.php';
?>

<h1>Import / Export Question Bank</h1>

<?php if ($message): ?>
    <p style="color: green;"><?php echo sanitize($message); ?></p>
<?php endif; ?>

<h2>Export</h2>
<p><a href="import_export.php?action=export">Download your question bank as JSON</a></p>

<h2>Import</h2>
<form method="post" action="import_export.php?action=import" enctype="multipart/form-data">
    <input type="file" name="import_file" accept=".json" required>
    <button type="submit">Import Question Bank</button>
</form>

<?php
include __DIR__ . '/../templates/footer.php';
?>
