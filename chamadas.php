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

<div class="page-header animate-fade-in">
    <div>
        <h1 class="page-title">Histórico de Chamadas</h1>
        <p style="color: var(--text-muted); margin-top: 0.25rem;">Consulte os registros de presença e diários iniciados.</p>
    </div>
    <button class="btn btn-primary" onclick="alert('Iniciando sistema de detecção facial para nova chamada...')">
        <i class="ph ph-video-camera"></i> Nova Chamada Facial
    </button>
</div>

<!-- Filtros e Calendário Simples -->
<div style="display: grid; grid-template-columns: 1fr 300px; gap: 2rem; align-items: start;" class="animate-fade-in">
    
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.125rem;">Registros Recentes</h3>
            
            <form action="chamadas.php" method="GET" style="display: flex; gap: 0.5rem;">
                <select name="turma_id" style="padding: 0.4rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 0.875rem;">
                    <option value="">Todas</option>
                    <?php foreach ($listaTurmas as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= ($turmaId == $t['id']) ? 'selected' : '' ?>><?= htmlspecialchars($t['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn" style="padding: 0.4rem 0.75rem; border-color: var(--border-color);"><i class="ph ph-funnel"></i></button>
            </form>
        </div>

        <?php if (count($diarios) > 0): ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <th style="padding: 1rem 0.5rem; color: var(--text-muted); font-weight: 600;">Data</th>
                            <th style="padding: 1rem 0.5rem; color: var(--text-muted); font-weight: 600;">Turma</th>
                            <th style="padding: 1rem 0.5rem; color: var(--text-muted); font-weight: 600;">Iniciada Em</th>
                            <th style="padding: 1rem 0.5rem; color: var(--text-muted); font-weight: 600; text-align: right;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($diarios as $d): ?>
                            <tr style="border-bottom: 1px solid var(--border-color); transition: var(--transition);" onmouseover="this.style.backgroundColor='#f8fafc'" onmouseout="this.style.backgroundColor='transparent'">
                                <td style="padding: 1rem 0.5rem; font-weight: 500;">
                                    <?= date('d/m/Y', strtotime($d['data_referencia'])) ?>
                                </td>
                                <td style="padding: 1rem 0.5rem;">
                                    <span style="background: #eff6ff; color: var(--primary-color); padding: 0.2rem 0.5rem; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 600;">
                                        <?= htmlspecialchars($d['nome_turma']) ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem 0.5rem; color: var(--text-muted);">
                                    <?= date('H:i', strtotime($d['iniciada_em'])) ?>
                                </td>
                                <td style="padding: 1rem 0.5rem; text-align: right;">
                                    <button class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.75rem; border-color: var(--border-color);" onclick="alert('Visualizando detalhes da presença...')">
                                        <i class="ph ph-eye"></i> Detalhes
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                <i class="ph ph-calendar-x" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                Nenhuma chamada encontrada.
            </div>
        <?php endif; ?>
    </div>

    <!-- Mini Widget de Calendário (Simulativo para o Design) -->
    <div class="card" style="padding: 1.25rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h4 style="font-size: 0.875rem; font-weight: 700;"><?= date('F Y') ?></h4>
            <div style="display: flex; gap: 0.25rem;">
                <button class="btn" style="padding: 0.25rem; border: none; background: none;"><i class="ph ph-caret-left"></i></button>
                <button class="btn" style="padding: 0.25rem; border: none; background: none;"><i class="ph ph-caret-right"></i></button>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.25rem; text-align: center; font-size: 0.7rem; color: var(--text-muted); font-weight: 700; margin-bottom: 0.5rem;">
            <span>D</span><span>S</span><span>T</span><span>Q</span><span>Q</span><span>S</span><span>S</span>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.25rem; text-align: center;">
            <?php 
            // Loop simples para gerar um grid de calendário ilustrativo
            for ($i = 1; $i <= 31; $i++) {
                $isToday = ($i == (int)date('d'));
                $hasRecord = ($i == 5 || $i == 12 || $i == 18); // Simulação de dias com chamada
                
                $style = "font-size: 0.75rem; padding: 0.4rem; border-radius: 4px; border: 1px solid transparent;";
                if ($isToday) $style .= "background: var(--primary-color); color: white;";
                elseif ($hasRecord) $style .= "background: #ecfdf5; color: var(--success); border-color: #d1fae5; cursor: pointer;";
                else $style .= "color: var(--text-main);";
                
                echo "<span style='$style'>$i</span>";
            }
            ?>
        </div>

        <div style="margin-top: 1.5rem; border-top: 1px dotted var(--border-color); padding-top: 1rem;">
            <h5 style="font-size: 0.75rem; margin-bottom: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Legenda</h5>
            <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.7rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--primary-color);"></div> Hoje
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--success);"></div> Chamada Realizada
                </div>
            </div>
        </div>
    </div>

</div>

<?php 
// Puxa o rodapé e o fechamento do HTML
require_once 'includes/footer.php'; 
?>
