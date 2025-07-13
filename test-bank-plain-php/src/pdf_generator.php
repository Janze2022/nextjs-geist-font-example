<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Assuming TCPDF or FPDF installed via composer

use TCPDF;

function generateExamPDF($examName, $questions, $filePath) {
    $pdf = new TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Test Bank App');
    $pdf->SetTitle($examName);
    $pdf->SetMargins(15, 20, 15);
    $pdf->AddPage();

    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, $examName, 0, 1, 'C');
    $pdf->Ln(10);

    $pdf->SetFont('helvetica', '', 12);
    $qNum = 1;
    foreach ($questions as $q) {
        $pdf->MultiCell(0, 0, $qNum . '. ' . strip_tags($q['question_text']), 0, 'L', 0, 1, '', '', true);
        $pdf->Ln(3);
        $qNum++;
    }

    $pdf->Output($filePath, 'F');
}

function generateAnswerKeyPDF($examName, $questions, $filePath) {
    $pdf = new TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Test Bank App');
    $pdf->SetTitle($examName . ' - Answer Key');
    $pdf->SetMargins(15, 20, 15);
    $pdf->AddPage();

    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, $examName . ' - Answer Key', 0, 1, 'C');
    $pdf->Ln(10);

    $pdf->SetFont('helvetica', '', 12);
    $qNum = 1;
    foreach ($questions as $q) {
        $pdf->MultiCell(0, 0, $qNum . '. ' . strip_tags($q['question_text']) . "\nAnswer: " . ($q['answer'] ?? 'N/A'), 0, 'L', 0, 1, '', '', true);
        $pdf->Ln(3);
        $qNum++;
    }

    $pdf->Output($filePath, 'F');
}
