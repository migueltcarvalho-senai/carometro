<?php
// Carrega a conexão
require_once '../config/conexao.php';

// Verifica se é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = filter_input(INPUT_POST, 'nome_completo', FILTER_SANITIZE_SPECIAL_CHARS);
    $rm = filter_input(INPUT_POST, 'registro_matricula', FILTER_SANITIZE_SPECIAL_CHARS);
    $turma_id = (int)$_POST['turma_id'];
    $foto_base64 = $_POST['foto_base64'];
    $vetor_facial = $_POST['vetor_facial']; // JSON contendo os 3 descritores faciais

    // Valida que tanto a foto quanto os vetores faciais foram enviados
    if (empty($foto_base64) || empty($vetor_facial)) {
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

        // Criar diretório se não existir
        if (!is_dir(dirname($caminhoAbsoluto))) {
            mkdir(dirname($caminhoAbsoluto), 0777, true);
        }

        file_put_contents($caminhoAbsoluto, $data);

        // 2. Salvar dados no banco (Incluindo os vetores JSON)
        // Monta o INSERT usando os nomes de colunas do novo schema (vetor_facial ao invés de vetores_json)
        $sql = "INSERT INTO alunos (turma_id, registro_matricula, nome_completo, caminho_foto, vetor_facial) 
                VALUES (:turma, :rm, :nome, :foto, :vetores)";

        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(':turma', $turma_id);
        $stmt->bindParam(':rm', $rm);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':foto', $caminhoRelativo);
        $stmt->bindParam(':vetores', $vetor_facial); // Envia o JSON dos vetores faciais para o banco

        if ($stmt->execute()) {
            echo "<script>alert('Aluno cadastrado com sucesso com biometria facial!'); window.location.href = '../alunos.php';</script>";
        }

    }
    catch (PDOException $e) {
        echo "Erro no banco: " . $e->getMessage();
    }
}
else {
    header("Location: ../alunos.php");
}
?>
