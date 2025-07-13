<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/functions.php';

requireTeacher();

$userId = $_SESSION['user']['id'];
$message = '';

// Fetch past exams for this teacher
$stmt = $pdo->prepare("SELECT e.id, e.name, e.created_at, eh.pdf_path, eh.answer_key_pdf_path FROM exams e LEFT JOIN exam_history eh ON e.id = eh.exam_id WHERE e.teacher_id = :teacher_id ORDER BY e.created_at DESC");
$stmt->execute(['teacher_id' => $userId]);
$exams = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../templates/header.php';
include __DIR__ . '/../templates/navbar.php';
?>

<h1>Past Exams</h1>

<?php if ($message): ?>
    <p style="color: green;"><?php echo sanitize($message); ?></p>
<?php endif; ?>

<?php if (count($exams) === 0): ?>
    <p>No past exams found.</p>
<?php else: ?>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Exam Name</th>
                <th>Created At</th>
                <th>Exam PDF</th>
                <th>Answer Key PDF</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($exams as $exam): ?>
                <tr>
                    <td><?php echo sanitize($exam['name']); ?></td>
                    <td><?php echo sanitize($exam['created_at']); ?></td>
                    <td>
                        <?php if ($exam['pdf_path']): ?>
                            <a href="<?php echo sanitize($exam['pdf_path']); ?>" download>Download</a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($exam['answer_key_pdf_path']): ?>
                            <a href="<?php echo sanitize($exam['answer_key_pdf_path']); ?>" download>Download</a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php
include __DIR__ . '/../templates/footer.php';
?>
