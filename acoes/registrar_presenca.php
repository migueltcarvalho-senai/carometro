<?php
// Endpoint de registro de presença individual
require_once '../config/conexao.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aluno_id = (int)$_POST['aluno_id'];
    $diario_id = (int)$_POST['diario_id'];
    // Recebe o número da aula via POST; se não for enviado, assume NULL
    $aula_numero = isset($_POST['aula_numero']) ? (int)$_POST['aula_numero'] : null;
    $horario = date('H:i:s'); // Horário exato em que a detecção facial aconteceu

    try {
        // Busca a quantidade de aulas do dia para este diário
        $sqlTurma = "SELECT t.qtd_aulas_dia FROM diarios_chamada d JOIN turmas t ON d.turma_id = t.id WHERE d.id = :diario";
        $stmtTurma = $conexao->prepare($sqlTurma);
        $stmtTurma->execute([':diario' => $diario_id]);
        $qtdAulasDia = $stmtTurma->fetchColumn();
        if (!$qtdAulasDia) $qtdAulasDia = 1;

        // Se uma aula específica for enviada (ex: manual), registramos só ela.
        // Se vier null (da IA facial, 1 chamada por período), damos presença em todas as aulas.
        $aulas_a_registrar = [];
        if ($aula_numero !== null) {
            $aulas_a_registrar[] = $aula_numero;
        } else {
            for ($i = 1; $i <= $qtdAulasDia; $i++) {
                $aulas_a_registrar[] = $i;
            }
        }

        $alguma_inserida = false;

        foreach ($aulas_a_registrar as $aulaIdx) {
            // Verifica se já existe presença confirmada para essa aula específica
            $sqlCheck = "SELECT id FROM presencas WHERE aluno_id = :a AND diario_id = :d AND aula_numero = :aula";
            $check = $conexao->prepare($sqlCheck);
            $check->execute([':a' => $aluno_id, ':d' => $diario_id, ':aula' => $aulaIdx]);
            
            if ($check->rowCount() == 0) {
                // Insere a presença se não existia
                $sql = "INSERT INTO presencas (aluno_id, diario_id, aula_numero, status_presenca, horario_deteccao) 
                        VALUES (:aluno, :diario, :aula, 1, :horario)";
                $stmt = $conexao->prepare($sql);
                $stmt->execute([
                    ':aluno' => $aluno_id,
                    ':diario' => $diario_id,
                    ':aula' => $aulaIdx,
                    ':horario' => $horario
                ]);
                $alguma_inserida = true;
            }
        }

        if ($alguma_inserida) {
            echo json_encode(['sucesso' => true]);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'Já registrado para todas as aulas do dia']);
        }

    } catch (PDOException $e) {
        echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
    }
} else {
    echo json_encode(['sucesso' => false, 'erro' => 'Método inválido']);
}
?>
