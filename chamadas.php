<?php
// Carrega a conexão com o banco
require_once 'config/conexao.php';

// Puxa o cabeçalho base e o CSS
require_once 'includes/header.php';

// Filtro por Turma
$turmaId = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : null;

// Busca as turmas para o filtro
$stmtTurmas = $conexao->query("SELECT id, nome FROM turmas ORDER BY nome ASC");
$listaTurmas = $stmtTurmas->fetchAll(PDO::FETCH_ASSOC);

// Monta a consulta de Diários de Chamada
try {
    $sql = "SELECT d.*, t.nome as nome_turma 
            FROM diarios_chamada d 
            JOIN turmas t ON d.turma_id = t.id";
    
    if ($turmaId) {
        $sql .= " WHERE d.turma_id = :turma_id";
    }
    
    $sql .= " ORDER BY d.data_referencia DESC, d.iniciada_em DESC LIMIT 20";
    
    $stmt = $conexao->prepare($sql);
    if ($turmaId) {
        $stmt->bindParam(':turma_id', $turmaId, PDO::PARAM_INT);
    }
    $stmt->execute();
    $diarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $erro = "Erro ao buscar diários: " . $e->getMessage();
}
?>

<div class="cabecalho-pagina animacao-surgir">
    <div>
        <h1 class="titulo-pagina">Histórico de Chamadas</h1>
        <p class="subtitulo-pagina">Consulte os registros de presença e diários iniciados.</p>
    </div>
    <a href="iniciar_chamada.php" class="botao botao-principal">
        <i class="ph ph-video-camera"></i> Nova Chamada
    </a>
</div>

<!-- Filtros e Calendário Simples -->
<div class="grade-chamadas animacao-surgir">
    
    <div class="cartao">
        <div class="cabecalho-lista-chamadas">
            <h3 class="titulo-lista-chamadas">Registros Recentes</h3>
            
            <form action="chamadas.php" method="GET" class="flex-botoes">
                <select name="turma_id" class="selecao-filtro select-pequeno">
                    <option value="">Todas</option>
                    <?php foreach ($listaTurmas as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= ($turmaId == $t['id']) ? 'selected' : '' ?>><?= htmlspecialchars($t['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="botao botao-quadrado-pequeno"><i class="ph ph-funnel"></i></button>
            </form>
        </div>

        <?php if (count($diarios) > 0): ?>
            <div class="tabela-responsiva">
                <table class="tabela-simples">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Turma</th>
                            <th>Iniciada Em</th>
                            <th class="celula-direita">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($diarios as $d): ?>
                            <tr>
                                <td class="texto-peso-medio">
                                    <?= date('d/m/Y', strtotime($d['data_referencia'])) ?>
                                </td>
                                <td>
                                    <span class="etiqueta-pequena">
                                        <?= htmlspecialchars($d['nome_turma']) ?>
                                    </span>
                                </td>
                                <td class="texto-secundario">
                                    <?= date('H:i', strtotime($d['iniciada_em'])) ?>
                                </td>
                                <td class="celula-direita">
                                    <button class="botao botao-tabela" onclick="alert('Visualizando detalhes da presença...')">
                                        <i class="ph ph-eye"></i> Detalhes
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="mensagem-vazia">
                <i class="ph ph-calendar-x icone-vazio-tabela"></i>
                Nenhuma chamada encontrada.
            </div>
        <?php endif; ?>
    </div>

    <!-- Mini Widget de Calendário (Simulativo para o Design) -->
    <div class="cartao cartao-calendario">
        <div class="cabecalho-calendario">
            <h4 class="titulo-calendario"><?= date('F Y') ?></h4>
            <div class="flex-botoes">
                <button class="botao botao-icone-limpo"><i class="ph ph-caret-left"></i></button>
                <button class="botao botao-icone-limpo"><i class="ph ph-caret-right"></i></button>
            </div>
        </div>
        
        <div class="grade-dias-semana">
            <span>D</span><span>S</span><span>T</span><span>Q</span><span>Q</span><span>S</span><span>S</span>
        </div>
        
        <div class="grade-dias-mes">
            <?php 
            // Loop simples para gerar um grid de calendário ilustrativo
            for ($i = 1; $i <= 31; $i++) {
                $isToday = ($i == (int)date('d'));
                $hasRecord = ($i == 5 || $i == 12 || $i == 18); // Simulação de dias com chamada
                
                $classeDia = "dia-calendario";
                if ($isToday) $classeDia .= " dia-hoje";
                elseif ($hasRecord) $classeDia .= " dia-com-registro";
                
                echo "<span class='$classeDia'>$i</span>";
            }
            ?>
        </div>

        <div class="legenda-calendario-conteiner">
            <h5 class="titulo-legenda">Legenda</h5>
            <div class="flex-coluna-espacada fonte-pequena">
                <div class="item-legenda">
                    <div class="ponto-hoje"></div> Hoje
                </div>
                <div class="item-legenda">
                    <div class="ponto-registro"></div> Chamada Realizada
                </div>
            </div>
        </div>
    </div>

</div>

<?php 
// Puxa o rodapé e o fechamento do HTML
require_once 'includes/footer.php'; 
?>
