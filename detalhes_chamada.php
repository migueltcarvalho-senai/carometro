<?php
/**
 * Arquivo resposável por mostrar os detalhes de uma chamada.
 * Lista todos os alunos da sala e exibe quadradinhos coloridos 
 * representando faltas ou presenças nas aulas do diário.
 * Resposta a requisição: Mostra o registro de alunos, presença em quadradinhos
 */

// Carrega a conexão com o banco
require_once 'config/conexao.php';

// Verifica se veio o ID do diário na URL
$diarioId = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$diarioId) {
    // Redireciona para chamadas caso não haja id
    header('Location: chamadas.php');
    exit;
}

// Busca os detalhes do diário e da turma
try {
    $stmtDiario = $conexao->prepare("SELECT d.*, t.nome as nome_turma, t.qtd_aulas_dia 
                                     FROM diarios_chamada d 
                                     JOIN turmas t ON d.turma_id = t.id 
                                     WHERE d.id = :id");
    $stmtDiario->bindParam(':id', $diarioId, PDO::PARAM_INT);
    $stmtDiario->execute();
    $diario = $stmtDiario->fetch(PDO::FETCH_ASSOC);

    if (!$diario) {
        die("Diário de chamada não encontrado.");
    }

    // Busca os alunos da turma e suas presenças neste diário
    // Usa LEFT JOIN para não perder o aluno caso não tenha registro em presenças
    $sqlAlunos = "
        SELECT a.id as aluno_id, a.nome_completo, a.registro_matricula, a.caminho_foto,
               p.aula_numero, p.status_presenca, p.horario_deteccao
        FROM alunos a
        LEFT JOIN presencas p ON a.id = p.aluno_id AND p.diario_id = :diario_id
        WHERE a.turma_id = :turma_id
        ORDER BY a.nome_completo ASC, p.aula_numero ASC
    ";
    $stmtAlunos = $conexao->prepare($sqlAlunos);
    $stmtAlunos->bindParam(':diario_id', $diarioId, PDO::PARAM_INT);
    $stmtAlunos->bindParam(':turma_id', $diario['turma_id'], PDO::PARAM_INT);
    $stmtAlunos->execute();
    $resultados = $stmtAlunos->fetchAll(PDO::FETCH_ASSOC);

    // Agrupa as linhas pois pode haver mais de uma aula por aluno
    $alunos_agrupados = [];
    foreach ($resultados as $row) {
        $aid = $row['aluno_id'];
        if (!isset($alunos_agrupados[$aid])) {
            $alunos_agrupados[$aid] = [
                'aluno_id' => $aid,
                'nome_completo' => $row['nome_completo'],
                'registro_matricula' => $row['registro_matricula'],
                'caminho_foto' => $row['caminho_foto'],
                'presencas' => []
            ];
        }
        if ($row['aula_numero'] !== null) {
            $alunos_agrupados[$aid]['presencas'][$row['aula_numero']] = $row['status_presenca'];
        }
    }

} catch (PDOException $e) {
    die("Erro ao conectar com banco: " . $e->getMessage());
}

// Puxa o cabeçalho base e o CSS
require_once 'includes/header.php';
?>

<div class="cabecalho-pagina animacao-surgir">
    <div>
        <h1 class="titulo-pagina">Detalhes da Chamada</h1>
        <p class="subtitulo-pagina">Turma: <?= htmlspecialchars($diario['nome_turma']) ?> | Data: <?= date('d/m/Y', strtotime($diario['data_referencia'])) ?></p>
    </div>
    <a href="chamadas.php" class="botao botao-secundario">
        <i class="ph ph-arrow-left"></i> Voltar
    </a>
</div>

<!-- Container com os cards de alunos, semelhante ao que está em alunos.php -->
<div class="grade-painel grade-painel-pequena animacao-surgir atraso-animacao-1">
    <?php if (count($alunos_agrupados) > 0): ?>
        <?php $idx = 0; foreach ($alunos_agrupados as $aluno): $idx++; ?>
            <!-- Card de aluno com delay de animação limitando a 10 classes de atraso -->
            <div class="cartao cartao-aluno animacao-surgir cartao-aluno-atraso-<?= min($idx, 10) ?>">
                <!-- Foto do Aluno predefinido do sistema -->
                <div class="foto-perfil-recipiente">
                    <?php if (!empty($aluno['caminho_foto']) && file_exists($aluno['caminho_foto'])): ?>
                        <img src="<?= htmlspecialchars($aluno['caminho_foto']) ?>" alt="<?= htmlspecialchars($aluno['nome_completo']) ?>" class="foto-perfil-imagem">
                    <?php else: ?>
                        <i class="ph ph-user icone-grande"></i>
                    <?php endif; ?>
                </div>

                <h3 class="titulo-cartao-menor texto-truncado" title="<?= htmlspecialchars($aluno['nome_completo']) ?>">
                    <?= htmlspecialchars($aluno['nome_completo']) ?>
                </h3>
                
                <p class="texto-detalhe-secundario">
                    RM: <?= htmlspecialchars($aluno['registro_matricula'] ?? 'N/D') ?>
                </p>

                <!-- Rodape do cartão do aluno com quadrados para visualização de presença -->
                <div class="rodape-cartao-aluno" style="margin-top: 1rem; display: flex; flex-direction: column; gap: 0.5rem; width: 100%;">
                    <p class="texto-pequeno" style="font-weight: 500; align-self: flex-start; margin-bottom: 2px;">Presenças (<?= $diario['qtd_aulas_dia'] ?> aulas):</p>
                    
                    <!-- Exibe quadradinhos coloridos da presença / falta por aula -->
                    <div style="display: flex; gap: 5px; flex-wrap: wrap; justify-content: center;">
                        <?php 
                        $total_presencas = 0;
                        $total_faltas = 0;
                        for($i = 1; $i <= $diario['qtd_aulas_dia']; $i++) {
                            // Se existe registro daquela 'aula' e marcando true (presente)
                            if (isset($aluno['presencas'][$i]) && $aluno['presencas'][$i] == 1) {
                                $total_presencas++;
                                echo '<div title="Aula '.$i.': Presente" style="width: 25px; height: 25px; background-color: var(--cor-sucesso, #10b981); border-radius: 4px; display:flex; align-items:center; justify-content:center; color:white; font-size:10px; font-weight:bold;">A'.$i.'</div>';
                            } else {
                                $total_faltas++;
                                // Ausente ou não detectado na chamada (considerado falta)
                                echo '<div title="Aula '.$i.': Falta" style="width: 25px; height: 25px; background-color: var(--cor-erro, #ef4444); border-radius: 4px; display:flex; align-items:center; justify-content:center; color:white; font-size:10px; font-weight:bold;">A'.$i.'</div>';
                            }
                        }
                        ?>
                    </div>
                    
                    <!-- Textos sumarizando o total desta chamada -->
                    <div style="display: flex; justify-content: space-between; font-size: 0.85rem; padding-top: 5px; border-top: 1px solid var(--cor-borda); width: 100%;">
                        <span style="color: var(--cor-sucesso);"><i class="ph-fill ph-check-circle"></i> <?= $total_presencas ?> P</span>
                        <span style="color: var(--cor-erro);"><i class="ph-fill ph-x-circle"></i> <?= $total_faltas ?> F</span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <!-- Mensagem de lista de alunos vazia -->
        <div class="cartao cartao-vazio">
            <i class="ph ph-users icone-extra-grande"></i>
            <h3 class="texto-secundario margem-base-pequena">Nenhum aluno cadastrado nesta turma</h3>
        </div>
    <?php endif; ?>
</div>

<?php 
// Puxa o rodapé e encerra a pagina
require_once 'includes/footer.php'; 
?>
