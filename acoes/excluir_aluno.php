<?php
require_once '../config/conexao.php';

$alunoId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$turmaFiltro = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : null;

// Monta a URL de redirecionamento para voltar pra página onde o user estava
$redirectUrl = '../alunos.php';
if ($turmaFiltro) {
    $redirectUrl .= '?turma_id=' . $turmaFiltro;
}

if ($alunoId) {
    try {
        // Inicia a transação, pois vamos apagar registros em múltiplas tabelas
        $conexao->beginTransaction();
        
        // 1. Exclui primeiro as presenças associadas ao aluno (evita erro de Foreign Key)
        $stmtPresencas = $conexao->prepare("DELETE FROM presencas WHERE aluno_id = :id");
        $stmtPresencas->execute([':id' => $alunoId]);
        
        // 2. Busca o caminho da foto para deletar o arquivo do servidor (se houver)
        $stmtFoto = $conexao->prepare("SELECT caminho_foto FROM alunos WHERE id = :id");
        $stmtFoto->execute([':id' => $alunoId]);
        $foto = $stmtFoto->fetchColumn();
        
        if ($foto) {
            $caminhoAbsoluto = __DIR__ . '/../' . $foto;
            if (file_exists($caminhoAbsoluto) && strpos($foto, 'assets') !== false) {
                unlink($caminhoAbsoluto);
            }
        }

        // 3. Finalmente, exclui o aluno da tabela alunos
        $stmtAluno = $conexao->prepare("DELETE FROM alunos WHERE id = :id");
        $stmtAluno->execute([':id' => $alunoId]);
        
        // Confirma a exclusão de tudo
        $conexao->commit();
    } catch (PDOException $e) {
        $conexao->rollBack();
        // Em um sistema real, poderíamos salvar $e->getMessage() em um log de erro
        // ou redirecionar com uma mensagem de $_SESSION['erro']
    }
}

// Redireciona de volta
header('Location: ' . $redirectUrl);
exit;
