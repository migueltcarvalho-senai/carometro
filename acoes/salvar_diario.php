<?php
// Processa o início de um novo diário de chamada
require_once '../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $turma_id = (int)$_POST['turma_id'];
    $data_referencia = $_POST['data'];
    $agora = date('Y-m-d H:i:s');

    try {
        // Criamos o registro do diário
        $sql = "INSERT INTO diarios_chamada (turma_id, data_referencia, iniciada_em) 
                VALUES (:turma, :data, :agora)";
        
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(':turma', $turma_id);
        $stmt->bindParam(':data', $data_referencia);
        $stmt->bindParam(':agora', $agora);

        if ($stmt->execute()) {
            $diario_id = $conexao->lastInsertId();
            // Redireciona para a tela da câmera passando o ID do diário novo
            header("Location: ../chamada_facial.php?diario_id=$diario_id");
            exit;
        }

    } catch (PDOException $e) {
        echo "Erro ao iniciar diário: " . $e->getMessage();
    }
} else {
    header("Location: ../index.php");
}
?>
