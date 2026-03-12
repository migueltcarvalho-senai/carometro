<?php
// Carrega a conexão
require_once '../config/conexao.php';

// Verifica se é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = filter_input(INPUT_POST, 'nome_completo', FILTER_SANITIZE_SPECIAL_CHARS);
    $rm = filter_input(INPUT_POST, 'registro_matricula', FILTER_SANITIZE_SPECIAL_CHARS);
    $turma_id = (int)$_POST['turma_id'];
    $foto_base64 = $_POST['foto_base64'];
    $vetores_json = $_POST['vetores_json']; // JSON contendo os 3 descritores

    if (empty($foto_base64) || empty($vetores_json)) {
        die("A foto e os vetores de reconhecimento são obrigatórios.");
    }

    try {
        // 1. Processar a imagem Base64 e salvar no servidor (Foto Principal)
        $img = str_replace('data:image/jpeg;base64,', '', $foto_base64);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);

        $nomeArquivo = 'aluno_' . $rm . '_' . time() . '.jpg';
        $caminhoRelativo = 'assets/uploads/alunos/' . $nomeArquivo;
        $caminhoAbsoluto = '../' . $caminhoRelativo;

        file_put_contents($caminhoAbsoluto, $data);

        // 2. Salvar dados no banco (Incluindo os vetores JSON)
        $sql = "INSERT INTO alunos (turma_id, registro_matricula, nome_completo, caminho_foto, vetores_json) 
                VALUES (:turma, :rm, :nome, :foto, :vetores)";
        
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(':turma', $turma_id);
        $stmt->bindParam(':rm', $rm);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':foto', $caminhoRelativo);
        $stmt->bindParam(':vetores', $vetores_json);

        if ($stmt->execute()) {
            echo "<script>alert('Aluno cadastrado com sucesso com biometria facial!'); window.location.href = '../alunos.php';</script>";
        }

    } catch (PDOException $e) {
        echo "Erro no banco: " . $e->getMessage();
    }
} else {
    header("Location: ../alunos.php");
}
?>
