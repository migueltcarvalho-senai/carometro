<?php
// Endpoint para registrar o retorno de um aluno que havia saído
// Recebe o horário e motivo, calcula a aula atual e marca presença a partir dela
require_once '../config/conexao.php';

// Define que a resposta é JSON
header('Content-Type: application/json');

// Só aceita requisições POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['sucesso' => false, 'erro' => 'Método inválido']);
    exit;
}

// Pega os dados enviados pelo formulário
$aluno_id = isset($_POST['aluno_id']) ? (int)$_POST['aluno_id'] : 0;
$horario_retorno = isset($_POST['horario_retorno']) ? trim($_POST['horario_retorno']) : '';
$motivo = isset($_POST['motivo']) ? trim($_POST['motivo']) : '';

// Validação básica dos campos obrigatórios
if (!$aluno_id || !$horario_retorno) {
    echo json_encode(['sucesso' => false, 'erro' => 'Preencha o aluno e o horário de retorno']);
    exit;
}

try {
    // Busca os dados da turma do aluno (horário de início, duração da aula, quantidade de aulas)
    $sqlTurma = "SELECT t.id as turma_id, t.horario_inicio_chamada, t.duracao_aula, t.qtd_aulas_dia 
                 FROM alunos a 
                 JOIN turmas t ON a.turma_id = t.id 
                 WHERE a.id = :aluno_id";
    $stmtTurma = $conexao->prepare($sqlTurma);
    $stmtTurma->execute([':aluno_id' => $aluno_id]);
    $turma = $stmtTurma->fetch(PDO::FETCH_ASSOC);

    // Se não achou a turma, para aqui
    if (!$turma) {
        echo json_encode(['sucesso' => false, 'erro' => 'Aluno ou turma não encontrado']);
        exit;
    }

    $turma_id = $turma['turma_id'];
    $horario_inicio = $turma['horario_inicio_chamada']; // Ex: "07:00:00"
    $duracao_aula = (int)$turma['duracao_aula'];         // Ex: 50 (minutos)
    $qtd_aulas = (int)$turma['qtd_aulas_dia'];           // Ex: 5

    // Converte os horários para minutos desde meia-noite pra facilitar a conta
    $partes_inicio = explode(':', $horario_inicio);
    $minutos_inicio = ((int)$partes_inicio[0] * 60) + (int)$partes_inicio[1];

    $partes_retorno = explode(':', $horario_retorno);
    $minutos_retorno = ((int)$partes_retorno[0] * 60) + (int)$partes_retorno[1];

    // Calcula em qual aula o aluno está voltando
    // Fórmula: pega a diferença de minutos e divide pela duração de cada aula
    $diferenca = $minutos_retorno - $minutos_inicio;

    // Se o horário de retorno for antes do início das aulas, considera como aula 1
    if ($diferenca < 0) {
        $aula_atual = 1;
    } else {
        $aula_atual = floor($diferenca / $duracao_aula) + 1;
        // Garante que não passa do total de aulas
        if ($aula_atual > $qtd_aulas) {
            $aula_atual = $qtd_aulas;
        }
    }

    // Busca o diário de chamada do dia para essa turma
    $hoje = date('Y-m-d');
    $sqlDiario = "SELECT id FROM diarios_chamada WHERE turma_id = :turma_id AND data_referencia = :hoje ORDER BY id DESC LIMIT 1";
    $stmtDiario = $conexao->prepare($sqlDiario);
    $stmtDiario->execute([':turma_id' => $turma_id, ':hoje' => $hoje]);
    $diario = $stmtDiario->fetch(PDO::FETCH_ASSOC);

    // Se não tiver diário do dia, cria um automaticamente
    if (!$diario) {
        $sqlCriarDiario = "INSERT INTO diarios_chamada (turma_id, data_referencia, iniciada_em) VALUES (:turma_id, :hoje, NOW())";
        $stmtCriar = $conexao->prepare($sqlCriarDiario);
        $stmtCriar->execute([':turma_id' => $turma_id, ':hoje' => $hoje]);
        $diario_id = $conexao->lastInsertId();
    } else {
        $diario_id = $diario['id'];
    }

    // Marca presença nas aulas A PARTIR da aula de retorno
    // Exemplo: voltou na aula 3 → marca presença nas aulas 3, 4, 5
    $presencas_registradas = 0;
    for ($aula = $aula_atual; $aula <= $qtd_aulas; $aula++) {
        // Verifica se já existe um registro de presença pra essa aula
        $sqlCheck = "SELECT id, status_presenca FROM presencas WHERE aluno_id = :aluno AND diario_id = :diario AND aula_numero = :aula";
        $stmtCheck = $conexao->prepare($sqlCheck);
        $stmtCheck->execute([':aluno' => $aluno_id, ':diario' => $diario_id, ':aula' => $aula]);
        $existe = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existe) {
            // Se já existe registro, atualiza pra presença (status_presenca = 1)
            $sqlUpdate = "UPDATE presencas SET status_presenca = 1, horario_deteccao = :horario WHERE id = :id";
            $stmtUpdate = $conexao->prepare($sqlUpdate);
            $stmtUpdate->execute([':id' => $existe['id'], ':horario' => $horario_retorno . ':00']);
        } else {
            // Se não existe, insere como presença
            $sqlInsert = "INSERT INTO presencas (aluno_id, diario_id, aula_numero, status_presenca, horario_deteccao) 
                          VALUES (:aluno, :diario, :aula, 1, :horario)";
            $stmtInsert = $conexao->prepare($sqlInsert);
            $stmtInsert->execute([
                ':aluno' => $aluno_id,
                ':diario' => $diario_id,
                ':aula' => $aula,
                ':horario' => $horario_retorno . ':00'
            ]);
        }
        $presencas_registradas++;
    }

    // Salva o registro do retorno na tabela de retornos
    $sqlRetorno = "INSERT INTO retornos (aluno_id, diario_id, aula_retorno, horario_retorno, motivo) 
                   VALUES (:aluno, :diario, :aula_retorno, :horario, :motivo)";
    $stmtRetorno = $conexao->prepare($sqlRetorno);
    $stmtRetorno->execute([
        ':aluno' => $aluno_id,
        ':diario' => $diario_id,
        ':aula_retorno' => $aula_atual,
        ':horario' => $horario_retorno . ':00',
        ':motivo' => $motivo
    ]);

    // Retorna sucesso com os detalhes da operação
    echo json_encode([
        'sucesso' => true,
        'aula_retorno' => $aula_atual,
        'presencas_registradas' => $presencas_registradas,
        'mensagem' => "Retorno registrado na aula $aula_atual. Presença marcada nas aulas $aula_atual até $qtd_aulas."
    ]);

} catch (PDOException $e) {
    // Se deu erro no banco, retorna a mensagem
    echo json_encode(['sucesso' => false, 'erro' => 'Erro no banco: ' . $e->getMessage()]);
}
?>
