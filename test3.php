<?php
require 'config/conexao.php';
$stmt = $conexao->query("SELECT id, nome_completo, turma_id FROM alunos");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
