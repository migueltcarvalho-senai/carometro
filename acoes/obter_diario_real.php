<?php
// acoes/obter_diario_real.php
require_once '../config/conexao.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    die(json_encode(['sucesso' => false, 'erro' => 'Método inválido.']));
}

$diario_id = isset($_GET['diario_id']) ? (int)$_GET['diario_id'] : 0;

if (!$diario_id) {
    die(json_encode(['sucesso' => false, 'erro' => 'ID do diário não fornecido.']));
}

try {
    // 1. Verificar se o diário existe e pegar a turma
    $stmt = $conexao->prepare("SELECT turma_id FROM diarios_chamada WHERE id = :diario_id");
    $stmt->execute([':diario_id' => $diario_id]);
    $diario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$diario) {
        die(json_encode(['sucesso' => false, 'erro' => 'Diário não encontrado.']));
    }

    $turma_id = $diario['turma_id'];

    // 2. Buscar todos os alunos da turma
    $stmtAlunos = $conexao->prepare("SELECT id, nome_completo, caminho_foto FROM alunos WHERE turma_id = :turma_id ORDER BY nome_completo ASC");
    $stmtAlunos->execute([':turma_id' => $turma_id]);
    $todos_alunos = $stmtAlunos->fetchAll(PDO::FETCH_ASSOC);

    // 3. Buscar os registros de presença para este diário
    $stmtPresencas = $conexao->prepare("SELECT aluno_id, status_presenca, horario_deteccao FROM presencas WHERE diario_id = :diario_id");
    $stmtPresencas->execute([':diario_id' => $diario_id]);
    
    // Organizar presenças por aluno_id para busca rápida
    $presencas = [];
    while ($row = $stmtPresencas->fetch(PDO::FETCH_ASSOC)) {
        $presencas[$row['aluno_id']] = $row;
    }

    // 4. Separar em Presentes e Ausentes
    $lista_presentes = [];
    $lista_ausentes = [];

    foreach ($todos_alunos as $aluno) {
        if (isset($presencas[$aluno['id']]) && $presencas[$aluno['id']]['status_presenca'] == 1) {
            $aluno['hora_registro'] = date('H:i', strtotime($presencas[$aluno['id']]['horario_deteccao']));
            $lista_presentes[] = $aluno;
        } else {
            $lista_ausentes[] = $aluno;
        }
    }

    echo json_encode([
        'sucesso' => true,
        'estatisticas' => [
            'total' => count($todos_alunos),
            'presentes' => count($lista_presentes),
            'ausentes' => count($lista_ausentes)
        ],
        'presentes' => $lista_presentes,
        'ausentes'  => $lista_ausentes
    ]);

} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'erro' => 'Erro interno do servidor.']);
}
