<?php
// Endpoint de registro de presença individual
require_once '../config/conexao.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aluno_id = (int)$_POST['aluno_id'];
    $diario_id = (int)$_POST['diario_id'];
    $horario = date('H:i:s');

    try {
        // Verifica se já existe registro de presença desse aluno nesse diário hoje
        // Evita duplicidade
        $check = $conexao->prepare("SELECT id FROM presencas WHERE aluno_id = :a AND diario_id = :d");
        $check->execute([':a' => $aluno_id, ':d' => $diario_id]);
        
        if ($check->rowCount() > 0) {
            echo json_encode(['sucesso' => false, 'erro' => 'Já registrado']);
            exit;
        }

        // Insere a presença
        $sql = "INSERT INTO presencas (aluno_id, diario_id, status_presenca, horario_deteccao) 
                VALUES (:aluno, :diario, 1, :horario)";
        
        $stmt = $conexao->prepare($sql);
        $stmt->execute([
            ':aluno' => $aluno_id,
            ':diario' => $diario_id,
            ':horario' => $horario
        ]);

        echo json_encode(['sucesso' => true]);

    } catch (PDOException $e) {
        echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
    }
} else {
    echo json_encode(['sucesso' => false, 'erro' => 'Método inválido']);
}
?>
