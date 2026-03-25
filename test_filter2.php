<?php
$_GET['turma_id'] = '1';
require 'config/conexao.php';
$turmaId = (isset($_GET['turma_id']) && $_GET['turma_id'] !== '') ? (int)$_GET['turma_id'] : null;
$buscaNome = isset($_GET['busca_nome']) ? trim($_GET['busca_nome']) : '';
$sql = "SELECT a.*, t.nome as nome_turma FROM alunos a LEFT JOIN turmas t ON a.turma_id = t.id WHERE 1=1";
if ($turmaId !== null) {
    $sql .= " AND a.turma_id = :turma_id";
}
if ($buscaNome !== '') {
    $sql .= " AND a.nome_completo LIKE :busca_nome";
}
$sql .= " ORDER BY a.nome_completo ASC";

$stmt = $conexao->prepare($sql);
if ($turmaId !== null) {
    $stmt->bindParam(':turma_id', $turmaId, PDO::PARAM_INT);
}
if ($buscaNome !== '') {
    $buscaNomeParam = '%' . $buscaNome . '%';
    $stmt->bindParam(':busca_nome', $buscaNomeParam, PDO::PARAM_STR);
}
$stmt->execute();
$alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "turmaId: " . var_export($turmaId, true) . "\n";
echo "SQL: " . $sql . "\n";
echo "Count: " . count($alunos) . "\n";
