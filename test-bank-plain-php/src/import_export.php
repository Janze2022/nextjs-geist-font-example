<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

function exportQuestionBank($teacherId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT q.id, s.name as subject, q.question_text, q.question_type, q.difficulty FROM questions q JOIN subjects s ON q.subject_id = s.id WHERE q.teacher_id = :teacher_id");
    $stmt->execute(['teacher_id' => $teacherId]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="question_bank_' . $teacherId . '.json"');
    echo json_encode($questions, JSON_PRETTY_PRINT);
    exit;
}

function importQuestionBank($teacherId, $jsonData) {
    global $pdo;
    $questions = json_decode($jsonData, true);
    if (!is_array($questions)) {
        return false;
    }
    $insertStmt = $pdo->prepare("INSERT INTO questions (subject_id, teacher_id, question_text, question_type, difficulty) VALUES ((SELECT id FROM subjects WHERE name = :subject LIMIT 1), :teacher_id, :question_text, :question_type, :difficulty)");

    foreach ($questions as $q) {
        $insertStmt->execute([
            'subject' => $q['subject'],
            'teacher_id' => $teacherId,
            'question_text' => $q['question_text'],
            'question_type' => $q['question_type'],
            'difficulty' => $q['difficulty'],
        ]);
    }
    return true;
}
