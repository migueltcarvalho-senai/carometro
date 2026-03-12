<?php
// Carrega a conexão com o banco
require_once 'config/conexao.php';

// Puxa o cabeçalho base e o CSS
require_once 'includes/header.php';

// Consulta todas as turmas cadastradas
try {
    $stmt = $conexao->query("SELECT * FROM turmas ORDER BY nome ASC");
    $turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $erro = "Erro ao buscar turmas: " . $e->getMessage();
}
?>

<div class="page-header animate-fade-in">
    <div>
        <h1 class="page-title">Gerenciamento de Turmas</h1>
        <p style="color: var(--text-muted); margin-top: 0.25rem;">Visualize e gerencie as turmas cadastradas no sistema.</p>
    </div>
    <!-- Botão para cadastrar uma nova turma -->
    <a href="cadastrar_turma.php" class="btn btn-primary">
        <i class="ph ph-plus"></i> Nova Turma
    </a>
</div>

<?php if (isset($erro)): ?>
    <div class="card" style="border-left: 4px solid var(--danger); margin-bottom: 2rem;">
        <p class="text-danger"><?= $erro; ?></p>
    </div>
<?php endif; ?>

<div class="dashboard-grid animate-fade-in" style="animation-delay: 0.1s;">
    <?php if (count($turmas) > 0): ?>
        <?php foreach ($turmas as $index => $turma): ?>
            <div class="card animate-fade-in" style="animation-delay: <?= 0.1 + ($index * 0.05) ?>s;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                    <div style="background-color: #eff6ff; color: var(--primary-color); padding: 0.75rem; border-radius: var(--radius-md);">
                        <i class="ph ph-users-three" style="font-size: 1.5rem;"></i>
                    </div>
                    <?php if ($turma['chamada_automatica']): ?>
                        <span class="text-success" style="font-size: 0.75rem; font-weight: 600; background: #ecfdf5; padding: 0.25rem 0.5rem; border-radius: var(--radius-full);">
                            Automática
                        </span>
                    <?php endif; ?>
                </div>

                <h3 style="font-size: 1.25rem; margin-bottom: 0.25rem;"><?= htmlspecialchars($turma['nome']) ?></h3>
                
                <div style="color: var(--text-muted); font-size: 0.875rem; display: flex; flex-direction: column; gap: 0.5rem; margin-top: 1rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="ph ph-clock"></i>
                        Início: <?= date('H:i', strtotime($turma['horario_inicio_chamada'])) ?>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="ph ph-book-open"></i>
                        <?= $turma['qtd_aulas_dia'] ?> aulas de <?= $turma['duracao_aula'] ?> min
                    </div>
                </div>

                <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem;">
                    <a href="alunos.php?turma_id=<?= $turma['id'] ?>" class="btn btn-primary" style="flex: 1; font-size: 0.75rem;">
                        <i class="ph ph-identification-card"></i> Alunos
                    </a>
                    <a href="chamadas.php?turma_id=<?= $turma['id'] ?>" class="btn" style="border-color: var(--border-color); color: var(--text-main); font-size: 0.75rem;">
                        <i class="ph ph-calendar-check"></i> Chamada
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
            <i class="ph ph-folder-open" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
            <h3 style="color: var(--text-muted);">Nenhuma turma encontrada</h3>
            <p style="color: var(--text-muted);">As turmas cadastradas no banco aparecerão aqui.</p>
        </div>
    <?php endif; ?>
</div>

<?php 
// Puxa o rodapé e o fechamento do HTML
require_once 'includes/footer.php'; 
?>
