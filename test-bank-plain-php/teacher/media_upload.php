<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/functions.php';

requireTeacher();

$userId = $_SESSION['user']['id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['media_file']['tmp_name'];
        $fileName = basename($_FILES['media_file']['name']);
        $fileSize = $_FILES['media_file']['size'];
        $fileType = $_FILES['media_file']['type'];

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'audio/mpeg'];
        if (!in_array($fileType, $allowedTypes)) {
            $message = "Unsupported file type.";
        } else {
            $uploadDir = __DIR__ . '/../public/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $destPath = $uploadDir . $fileName;
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                // Save media path to database or associate with question as needed
                $message = "File uploaded successfully: " . $fileName;
            } else {
                $message = "Error moving uploaded file.";
            }
        }
    } else {
        $message = "File upload error.";
    }
}

include __DIR__ . '/../templates/header.php';
include __DIR__ . '/../templates/navbar.php';
?>

<h1>Media Upload</h1>

<?php if ($message): ?>
    <p style="color: green;"><?php echo sanitize($message); ?></p>
<?php endif; ?>

<form method="post" action="media_upload.php" enctype="multipart/form-data">
    <label>Select media file (images, video, audio):</label><br>
    <input type="file" name="media_file" required><br><br>
    <button type="submit">Upload</button>
</form>

<?php
include __DIR__ . '/../templates/footer.php';
?>
