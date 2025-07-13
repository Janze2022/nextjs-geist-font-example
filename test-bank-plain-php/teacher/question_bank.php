<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/functions.php';

requireTeacher();

$userId = $_SESSION['user']['id'];
$message = '';
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

// Fetch subjects for dropdown
$subjectsStmt = $pdo->query("SELECT * FROM subjects ORDER BY name");
$subjects = $subjectsStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_id = $_POST['subject_id'] ?? '';
    $question_text = $_POST['question_text'] ?? '';
    $question_type = $_POST['question_type'] ?? 'multiple_choice';
    $difficulty = $_POST['difficulty'] ?? 'easy';
    // For simplicity, media upload not implemented here

    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO questions (subject_id, teacher_id, question_text, question_type, difficulty) VALUES (:subject_id, :teacher_id, :question_text, :question_type, :difficulty)");
        $stmt->execute([
            'subject_id' => $subject_id,
            'teacher_id' => $userId,
            'question_text' => $question_text,
            'question_type' => $question_type,
            'difficulty' => $difficulty,
        ]);
        $message = "Question added successfully.";
    } elseif ($action === 'edit' && $id) {
        $stmt = $pdo->prepare("UPDATE questions SET subject_id = :subject_id, question_text = :question_text, question_type = :question_type, difficulty = :difficulty WHERE id = :id AND teacher_id = :teacher_id");
        $stmt->execute([
            'subject_id' => $subject_id,
            'question_text' => $question_text,
            'question_type' => $question_type,
            'difficulty' => $difficulty,
            'id' => $id,
            'teacher_id' => $userId,
        ]);
        $message = "Question updated successfully.";
    }
    header("Location: question_bank.php");
    exit;
}

if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = :id AND teacher_id = :teacher_id");
    $stmt->execute(['id' => $id, 'teacher_id' => $userId]);
    $message = "Question deleted successfully.";
    header("Location: question_bank.php");
    exit;
}

// Fetch questions for this teacher
$stmt = $pdo->prepare("SELECT q.id, q.question_text, q.question_type, q.difficulty, s.name AS subject_name FROM questions q JOIN subjects s ON q.subject_id = s.id WHERE q.teacher_id = :teacher_id ORDER BY q.created_at DESC");
$stmt->execute(['teacher_id' => $userId]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../templates/header.php';
include __DIR__ . '/../templates/navbar.php';
?>

<h1>Question Bank</h1>

<?php if ($message): ?>
    <p style="color: green;"><?php echo sanitize($message); ?></p>
<?php endif; ?>

<a href="question_bank.php?action=add">Add New Question</a>

<?php if ($action === 'add' || ($action === 'edit' && $id)): ?>
    <?php
    $question = [
        'subject_id' => '',
        'question_text' => '',
        'question_type' => 'multiple_choice',
        'difficulty' => 'easy',
    ];
    if ($action === 'edit' && $id) {
        $stmt = $pdo->prepare("SELECT * FROM questions WHERE id = :id AND teacher_id = :teacher_id");
        $stmt->execute(['id' => $id, 'teacher_id' => $userId]);
        $question = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$question) {
            echo "<p>Question not found.</p>";
            exit;
        }
    }
    ?>
    <form method="post" action="question_bank.php?action=<?php echo $action; ?><?php if ($id) echo '&id=' . $id; ?>">
        <label>Subject:
            <select name="subject_id" required>
                <option value="">Select Subject</option>
                <?php foreach ($subjects as $subject): ?>
                    <option value="<?php echo $subject['id']; ?>" <?php if ($subject['id'] == $question['subject_id']) echo 'selected'; ?>>
                        <?php echo sanitize($subject['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label><br><br>
        <label>Question Text:<br>
            <textarea name="question_text" rows="5" cols="60" required><?php echo sanitize($question['question_text']); ?></textarea>
        </label><br><br>
        <label>Question Type:
            <select name="question_type">
                <option value="multiple_choice" <?php if ($question['question_type'] === 'multiple_choice') echo 'selected'; ?>>Multiple Choice</option>
                <option value="true_false" <?php if ($question['question_type'] === 'true_false') echo 'selected'; ?>>True/False</option>
                <option value="short_answer" <?php if ($question['question_type'] === 'short_answer') echo 'selected'; ?>>Short Answer</option>
            </select>
        </label><br><br>
        <label>Difficulty:
            <select name="difficulty">
                <option value="easy" <?php if ($question['difficulty'] === 'easy') echo 'selected'; ?>>Easy</option>
                <option value="medium" <?php if ($question['difficulty'] === 'medium') echo 'selected'; ?>>Medium</option>
                <option value="hard" <?php if ($question['difficulty'] === 'hard') echo 'selected'; ?>>Hard</option>
            </select>
        </label><br><br>
        <button type="submit"><?php echo ucfirst($action); ?> Question</button>
    </form>
    <a href="question_bank.php">Back to list</a>
<?php else: ?>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Question Text</th>
                <th>Type</th>
                <th>Difficulty</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($questions as $q): ?>
                <tr>
                    <td><?php echo sanitize($q['subject_name']); ?></td>
                    <td><?php echo sanitize($q['question_text']); ?></td>
                    <td><?php echo sanitize(ucwords(str_replace('_', ' ', $q['question_type']))); ?></td>
                    <td><?php echo sanitize(ucwords($q['difficulty'])); ?></td>
                    <td>
                        <a href="question_bank.php?action=edit&id=<?php echo $q['id']; ?>">Edit</a> |
                        <a href="question_bank.php?action=delete&id=<?php echo $q['id']; ?>" onclick="return confirm('Are you sure you want to delete this question?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php
include __DIR__ . '/../templates/footer.php';
?>
