<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/functions.php';

requireAdmin();

// Fetch total number of teachers
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'");
$totalTeachers = $stmt->fetchColumn();

// Fetch total number of questions
$stmt = $pdo->query("SELECT COUNT(*) FROM questions");
$totalQuestions = $stmt->fetchColumn();

// Fetch total number of exams generated
$stmt = $pdo->query("SELECT COUNT(*) FROM exams");
$totalExams = $stmt->fetchColumn();

// Fetch questions count by subject
$stmt = $pdo->query("SELECT s.name, COUNT(q.id) as question_count FROM subjects s LEFT JOIN questions q ON s.id = q.subject_id GROUP BY s.id ORDER BY s.name");
$questionsBySubject = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../templates/header.php';
include __DIR__ . '/../templates/navbar.php';
?>

<h1>Analytics Dashboard</h1>

<ul>
    <li>Total Teachers: <?php echo $totalTeachers; ?></li>
    <li>Total Questions: <?php echo $totalQuestions; ?></li>
    <li>Total Exams Generated: <?php echo $totalExams; ?></li>
</ul>

<h2>Questions by Subject</h2>
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>Subject</th>
            <th>Number of Questions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($questionsBySubject as $row): ?>
            <tr>
                <td><?php echo sanitize($row['name']); ?></td>
                <td><?php echo $row['question_count']; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
include __DIR__ . '/../templates/footer.php';
?>
