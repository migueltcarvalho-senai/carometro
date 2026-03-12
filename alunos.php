<?php
// Carrega a conexão com o banco
require_once 'config/conexao.php';

// Puxa o cabeçalho base e o CSS
require_once 'includes/header.php';

// Verifica se veio um filtro de turma por ID na URL
$turmaId = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : null;

// Busca os dados das turmas para o filtro
$stmtTurmas = $conexao->query("SELECT id, nome FROM turmas ORDER BY nome ASC");
$listaTurmas = $stmtTurmas->fetchAll(PDO::FETCH_ASSOC);

// Monta a consulta de alunos
try {
    $sql = "SELECT a.*, t.nome as nome_turma 
            FROM alunos a 
            JOIN turmas t ON a.turma_id = t.id";
    
    // Se houver filtro de turma, adiciona o WHERE
    if ($turmaId) {
        $sql .= " WHERE a.turma_id = :turma_id";
    }
    
    $sql .= " ORDER BY a.nome_completo ASC";
    
    $stmt = $conexao->prepare($sql);
    if ($turmaId) {
        $stmt->bindParam(':turma_id', $turmaId, PDO::PARAM_INT);
    }
    $stmt->execute();
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $erro = "Erro ao buscar alunos: " . $e->getMessage();
}
?>

<div class="page-header animate-fade-in">
    <div>
        <h1 class="page-title">Carômetro de Alunos</h1>
        <p style="color: var(--text-muted); margin-top: 0.25rem;">Identificação visual de todos os alunos cadastrados.</p>
    </div>
    <a href="cadastrar_aluno.php" class="btn btn-primary">
        <i class="ph ph-user-plus"></i> Novo Aluno
    </a>
</div>

<!-- Barra de Filtros -->
<div class="card animate-fade-in" style="margin-bottom: 2rem; padding: 1rem;">
    <form action="alunos.php" method="GET" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 200px;">
            <label for="turma_id" style="font-size: 0.875rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 0.5rem;">Filtrar por Turma</label>
            <select name="turma_id" id="turma_id" style="width: 100%; padding: 0.55rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); font-family: inherit; background-color: white;">
                <option value="">Todas as Turmas</option>
                <?php foreach ($listaTurmas as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= ($turmaId == $t['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="ph ph-funnel"></i> Aplicar Filtro
        </button>
        <?php if ($turmaId): ?>
            <a href="alunos.php" class="btn" style="border-color: var(--border-color); color: var(--text-muted);">
                Limpar
            </a>
        <?php endif; ?>
    </form>
</div>

<?php if (isset($erro)): ?>
    <div class="card" style="border-left: 4px solid var(--danger); margin-bottom: 2rem;">
        <p class="text-danger"><?= $erro; ?></p>
    </div>
<?php endif; ?>

<!-- Grid do Carômetro -->
<div class="dashboard-grid animate-fade-in" style="animation-delay: 0.1s; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));">
    <?php if (count($alunos) > 0): ?>
        <?php foreach ($alunos as $index => $aluno): ?>
            <div class="card animate-fade-in" style="padding: 1rem; text-align: center; animation-delay: <?= 0.1 + ($index * 0.05) ?>s;">
                <!-- Foto do Aluno com fallback caso não exista -->
                <div style="width: 120px; height: 120px; margin: 0 auto 1rem; border-radius: var(--radius-full); overflow: hidden; border: 3px solid #eff6ff; background-color: #f1f5f9; display: flex; align-items: center; justify-content: center;">
                    <?php if (!empty($aluno['caminho_foto']) && file_exists($aluno['caminho_foto'])): ?>
                        <img src="<?= htmlspecialchars($aluno['caminho_foto']) ?>" alt="<?= htmlspecialchars($aluno['nome_completo']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <i class="ph ph-user" style="font-size: 3rem; color: #cbd5e1;"></i>
                    <?php endif; ?>
                </div>

                <h3 style="font-size: 1rem; margin-bottom: 0.25rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($aluno['nome_completo']) ?>">
                    <?= htmlspecialchars($aluno['nome_completo']) ?>
                </h3>
                
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.75rem;">
                    RM: <?= htmlspecialchars($aluno['registro_matricula'] ?? 'N/D') ?>
                </p>

                <span style="font-size: 0.7rem; font-weight: 600; color: var(--primary-color); background: #eff6ff; padding: 0.2rem 0.5rem; border-radius: var(--radius-full);">
                    <?= htmlspecialchars($aluno['nome_turma']) ?>
                </span>

                <div style="margin-top: 1rem; border-top: 1px solid var(--border-color); padding-top: 0.75rem;">
                    <button class="btn" style="width: 100%; padding: 0.35rem; font-size: 0.7rem; border-color: var(--border-color); color: var(--text-muted);" onclick="alert('Histórico de presença em desenvolvimento!')">
                        <i class="ph ph-clock-counter-clockwise"></i> Histórico
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 4rem;">
            <i class="ph ph-users" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1.5rem;"></i>
            <h3 style="color: var(--text-muted); margin-bottom: 0.5rem;">Nenhum aluno encontrado</h3>
            <p style="color: var(--text-muted);">Tente mudar o filtro ou cadastre novos alunos no banco de dados.</p>
        </div>
    <?php endif; ?>
</div>

<?php 
// Puxa o rodapé e o fechamento do HTML
require_once 'includes/footer.php'; 
?>
