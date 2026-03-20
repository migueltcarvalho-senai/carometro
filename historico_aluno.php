<?php
/**
 * Arquivo que exibe o Histórico do Aluno individual.
 * Permite filtrar por data e observar presenças/faltas de cada chamada
 * efetuada para a turma a qual ele pertence.
 */

// Carrega a conexão com o banco (e agora também tem o fuso horário de BR)
require_once 'config/conexao.php';

// Pega o ID na URL para buscar este aluno
$alunoId = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Sem ID não podemos listar, então voltamos.
if (!$alunoId) {
    header('Location: alunos.php');
    exit;
}

// Filtro de data enviado via GET, formato YYYY-MM-DD
$dataFiltro = isset($_GET['data']) ? $_GET['data'] : null;

try {
    // Busca os dados cadastrais do aluno
    $stmtAluno = $conexao->prepare("SELECT a.*, t.nome as nome_turma 
                                    FROM alunos a 
                                    JOIN turmas t ON a.turma_id = t.id 
                                    WHERE a.id = :id");
    $stmtAluno->bindParam(':id', $alunoId, PDO::PARAM_INT);
    $stmtAluno->execute();
    $aluno = $stmtAluno->fetch(PDO::FETCH_ASSOC);

    if (!$aluno) {
        die("Aluno não encontrado no banco de dados.");
    }

    // Busca todos os diários de chamada da turma e as presencas Deste Aluno (query inteligente)
    // O sub-select conta quantas aulas marcaram TRUE para presenca e associa à coluna presencas
    $sqlHist = "
        SELECT d.id as diario_id, d.data_referencia, d.iniciada_em, t.qtd_aulas_dia,
               (SELECT COUNT(*) 
                FROM presencas p 
                WHERE p.diario_id = d.id AND p.aluno_id = :aluno_id AND p.status_presenca = 1
               ) as presencas
        FROM diarios_chamada d
        JOIN turmas t ON d.turma_id = t.id
        WHERE d.turma_id = :turma_id
    ";

    // Adiciona o filtro de data dinamicamente no Query String SQL
    if (!empty($dataFiltro)) {
        $sqlHist .= " AND d.data_referencia = :data_filtro ";
    }
    
    // Mais as datas recentes vêm primeiro
    $sqlHist .= " ORDER BY d.data_referencia DESC, d.iniciada_em DESC";

    $stmtHist = $conexao->prepare($sqlHist);
    // Binda alunoId dentro do sub-select na query principal
    $stmtHist->bindParam(':aluno_id', $alunoId, PDO::PARAM_INT);
    $stmtHist->bindParam(':turma_id', $aluno['turma_id'], PDO::PARAM_INT);
    
    // Se o user quiser filtrar, binda o parâmetro Date
    if (!empty($dataFiltro)) {
        $stmtHist->bindParam(':data_filtro', $dataFiltro);
    }

    $stmtHist->execute();
    $historico = $stmtHist->fetchAll(PDO::FETCH_ASSOC);

    // Contadores para o quadro de sumário Global da conta dele
    $totalAulasGlobal = 0;
    $totalPresencasGlobal = 0;
    
    foreach ($historico as $h) {
        $totalAulasGlobal += $h['qtd_aulas_dia'];
        $totalPresencasGlobal += $h['presencas'];
    }
    
    $totalFaltasGlobal = $totalAulasGlobal - $totalPresencasGlobal;

} catch (PDOException $e) {
    die("Erro ao conectar com banco em historico aluno: " . $e->getMessage());
}

// Puxa o cabeçalho base e o CSS
require_once 'includes/header.php';
?>

<!-- Cabecalho padrão com botão 'Voltar' -->
<div class="cabecalho-pagina animacao-surgir">
    <div>
        <h1 class="titulo-pagina">Histórico de Faltas</h1>
        <p class="subtitulo-pagina">Aluno: <?= htmlspecialchars($aluno['nome_completo']) ?> (RM: <?= htmlspecialchars($aluno['registro_matricula'] ?? 'N/D') ?>)</p>
    </div>
    <a href="alunos.php" class="botao botao-secundario">
        <i class="ph ph-arrow-left"></i> Voltar
    </a>
</div>

<!-- Container do grid listando o historico e também filtro a esquerda/direita -->
<div class="grade-chamadas animacao-surgir">
    
    <div class="cartao">
        <div class="cabecalho-lista-chamadas">
            <h3 class="titulo-lista-chamadas">Filtrar por Dia</h3>
            
            <form action="historico_aluno.php" method="GET" class="flex-botoes">
                <!-- Mantém ID na URL via hidden object -->
                <input type="hidden" name="id" value="<?= $alunoId ?>">
                <input type="date" name="data" class="selecao-filtro input-texto select-pequeno" value="<?= htmlspecialchars($dataFiltro) ?>">
                <button type="submit" class="botao botao-quadrado-pequeno" title="Filtrar"><i class="ph ph-funnel"></i></button>
                
                <?php if (!empty($dataFiltro)): ?>
                    <a href="historico_aluno.php?id=<?= $alunoId ?>" class="botao botao-quadrado-pequeno botao-secundario" title="Limpar Filtro"><i class="ph ph-x"></i></a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (count($historico) > 0): ?>
            <!-- Lista o resultado dependendo do filtro em Tabela -->
            <div class="tabela-responsiva mt-3">
                <table class="tabela-simples">
                    <thead style="border-radius: 8px 8px 0px 0px;">
                        <tr>
                            <th>Data</th>
                            <th>Iniciada Em</th>
                            <th>Presenças</th>
                            <th>Faltas</th>
                            <th>Aulas Total</th>
                            <th class="celula-direita">Status do Dia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historico as $h): 
                            // Calcula as faltas no dia isolado (AulasT - PresT)
                            $faltas = $h['qtd_aulas_dia'] - $h['presencas'];
                            
                            // Define cores e label baseado em quanto de aulas perdeu
                            $statusCor = ($faltas == 0) ? 'var(--cor-sucesso)' : (($h['presencas'] == 0) ? 'var(--cor-erro)' : 'var(--cor-atencao)');
                            $statusTexto = ($faltas == 0) ? 'Presença Total' : (($h['presencas'] == 0) ? 'Falta Total' : 'Presença Parcial');
                        ?>
                            <tr>
                                <td class="texto-peso-medio">
                                    <?= date('d/m/Y', strtotime($h['data_referencia'])) ?>
                                </td>
                                <td class="texto-secundario">
                                    <?= date('H:i', strtotime($h['iniciada_em'])) ?>
                                </td>
                                <td>
                                    <!-- Verde presenças -->
                                    <span style="color: var(--cor-sucesso, #10b981); font-weight: bold;"><?= $h['presencas'] ?></span>
                                </td>
                                <td>
                                    <!-- Vermelho  faltas -->
                                    <span style="color: var(--cor-erro, #ef4444); font-weight: bold;"><?= $faltas ?></span>
                                </td>
                                <td>
                                    <!-- Cinza total -->
                                    <span style="color: gray; font-weight: bold;"><?= $h['qtd_aulas_dia'] ?></span>
                                </td>
                                <td class="celula-direita">
                                    <span class="etiqueta-pequena" style="background-color: transparent; border: 1px solid <?= $statusCor ?>; color: <?= $statusCor ?>; white-space:nowrap;">
                                        <?= $statusTexto ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="mensagem-vazia">
                <i class="ph ph-calendar-x icone-vazio-tabela"></i>
                Nenhum diário registrado para este filtro ou turma.
            </div>
        <?php endif; ?>
    </div>

    <!-- Container lateral com Estatísticas Agregadas ao longo de todos Filtros -->
    <div class="cartao" style="align-self: start; background: linear-gradient(135deg, rgba(255,255,255,0.05), rgba(0,0,0,0.2)); border: 1px solid var(--cor-borda);">
        <h4 class="titulo-calendario" style="margin-bottom: 20px;">Resumo Total (Filtrado)</h4>
        
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--cor-borda); padding-bottom: 10px;">
                <span class="texto-secundario">Aulas Totais:</span>
                <span style="font-size: 1.2rem; font-weight: bold;"><?= $totalAulasGlobal ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--cor-borda); padding-bottom: 10px;">
                <span class="texto-secundario">Total Presenças:</span>
                <span style="font-size: 1.2rem; font-weight: bold; color: var(--cor-sucesso, #10b981);"><?= $totalPresencasGlobal ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span class="texto-secundario">Total Faltas:</span>
                <span style="font-size: 1.2rem; font-weight: bold; color: var(--cor-erro, #ef4444);"><?= $totalFaltasGlobal ?></span>
            </div>
        </div>
    </div>
</div>

<?php 
// Puxa o rodapé padrão e o fechamento do HTML
require_once 'includes/footer.php'; 
?>
