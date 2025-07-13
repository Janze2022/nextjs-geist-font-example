<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/functions.php';

requireTeacher();

$userId = $_SESSION['user']['id'];
$message = '';
$action = $_GET['action'] ?? '';
$examId = $_GET['exam_id'] ?? null;

// Fetch subjects for dropdown
$subjectsStmt = $pdo->query("SELECT * FROM subjects ORDER BY name");
$subjects = $subjectsStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'generate') {
    $exam_name = $_POST['exam_name'] ?? '';
    $subject_id = $_POST['subject_id'] ?? '';
    $num_questions = intval($_POST['num_questions'] ?? 0);

    if ($exam_name && $subject_id && $num_questions > 0) {
        // Create exam record
        $stmt = $pdo->prepare("INSERT INTO exams (teacher_id, name) VALUES (:teacher_id, :name)");
        $stmt->execute(['teacher_id' => $userId, 'name' => $exam_name]);
        $newExamId = $pdo->lastInsertId();

        // Select random questions from question bank for the subject
        $stmt = $pdo->prepare("SELECT id FROM questions WHERE subject_id = :subject_id AND teacher_id = :teacher_id ORDER BY RAND() LIMIT :limit");
        $stmt->bindValue(':subject_id', $subject_id, PDO::PARAM_INT);
        $stmt->bindValue(':teacher_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $num_questions, PDO::PARAM_INT);
        $stmt->execute();
        $questions = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Insert into exam_questions
        $insertStmt = $pdo->prepare("INSERT INTO exam_questions (exam_id, question_id) VALUES (:exam_id, :question_id)");
        foreach ($questions as $qid) {
            $insertStmt->execute(['exam_id' => $newExamId, 'question_id' => $qid]);
        }

require_once __DIR__ . '/../src/pdf_generator.php';

// Generate PDFs for exam and answer key
$pdfDir = __DIR__ . '/../pdfs';
if (!is_dir($pdfDir)) {
    mkdir($pdfDir, 0755, true);
}
$pdfPath = $pdfDir . "/{$exam_name}_Qs.pdf";
$answerKeyPath = $pdfDir . "/{$exam_name}_AnswerKey.pdf";

// Fetch questions details for PDF generation
$placeholders = implode(',', array_fill(0, count($questions), '?'));
$stmt = $pdo->prepare("SELECT id, question_text, answer FROM questions WHERE id IN ($placeholders)");
$stmt->execute($questions);
$questionsDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

generateExamPDF($exam_name, $questionsDetails, $pdfPath);
generateAnswerKeyPDF($exam_name, $questionsDetails, $answerKeyPath);

// Save exam history
$stmt = $pdo->prepare("INSERT INTO exam_history (exam_id, pdf_path, answer_key_pdf_path) VALUES (:exam_id, :pdf_path, :answer_key_pdf_path)");
$stmt->execute(['exam_id' => $newExamId, 'pdf_path' => $pdfPath, 'answer_key_pdf_path' => $answerKeyPath]);

$message = "Exam generated successfully. Download your PDFs from the Reporting section.";
    } else {
        $message = "Please fill all fields correctly.";
    }
}

include __DIR__ . '/../templates/header.php';
include __DIR__ . '/../templates/navbar.php';
?>

<h1>Exam Builder</h1>

<?php if ($message): ?>
    <p style="color: green;"><?php echo sanitize($message); ?></p>
<?php endif; ?>

<form method="post" action="exam_builder.php?action=generate">
    <label>Exam Name: <input type="text" name="exam_name" required></label><br><br>
    <label>Subject:
        <select name="subject_id" required>
            <option value="">Select Subject</option>
            <?php foreach ($subjects as $subject): ?>
                <option value="<?php echo $subject['id']; ?>"><?php echo sanitize($subject['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </label><br><br>
    <label>Number of Questions: <input type="number" name="num_questions" min="1" required></label><br><br>
    <button type="submit">Generate PDF</button>
</form>

<?php
include __DIR__ . '/../templates/footer.php';
?>
