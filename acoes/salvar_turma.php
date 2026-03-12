<?php
// Arquivo responsável por processar o salvamento da turma no banco
require_once '../config/conexao.php';

// Verificamos se os dados vieram via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pegamos os valores e limpamos para segurança básica
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $horario_inicio = $_POST['horario_inicio'];
    $qtd_aulas = (int)$_POST['qtd_aulas'];
    $duracao = (int)$_POST['duracao'];
    $automatica = isset($_POST['automatica']) ? 1 : 0;

    try {
        // Preparamos a query para inserir a nova turma
        $sql = "INSERT INTO turmas (nome, horario_inicio_chamada, qtd_aulas_dia, duracao_aula, chamada_automatica) 
                VALUES (:nome, :horario, :aulas, :duracao, :automatica)";
        
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':horario', $horario_inicio);
        $stmt->bindParam(':aulas', $qtd_aulas);
        $stmt->bindParam(':duracao', $duracao);
        $stmt->bindParam(':automatica', $automatica);

        if ($stmt->execute()) {
            // Se der certo, volta pra lista com mensagem de sucesso (um alerta simples)
            echo "<script>alert('Turma cadastrada com sucesso!'); window.location.href = '../turmas.php';</script>";
        }

    } catch (PDOException $e) {
        // Se der erro, mostramos o que aconteceu
        echo "Erro ao salvar: " . $e->getMessage();
    }
} else {
    // Se tentarem acessar o arquivo direto, mandamos de volta
    header("Location: ../turmas.php");
}
?>
