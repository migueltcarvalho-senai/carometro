<?php
require 'config/conexao.php';
$turmaId = 1;
$sql = "SELECT a.*, t.nome as nome_turma FROM alunos a JOIN turmas t ON a.turma_id = t.id WHERE a.turma_id = :turma_id ORDER BY a.nome_completo ASC";
$stmt = $conexao->prepare($sql);
$stmt->bindParam(':turma_id', $turmaId, PDO::PARAM_INT);
if (!$stmt->execute()) {
    print_r($stmt->errorInfo());
} else {
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
}
