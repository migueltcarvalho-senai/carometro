<?php
// Carrega a conexão com o banco
require_once 'config/conexao.php';
require_once 'includes/header.php';

// Busca todas as turmas cadastradas para o usuário escolher uma
$stmt = $conexao->query("SELECT * FROM turmas ORDER BY nome ASC");
$turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header animate-fade-in">
    <div>
        <h1 class="page-title">Iniciar Nova Chamada</h1>
        <p style="color: var(--text-muted); margin-top: 0.25rem;">Selecione a turma para abrir o diário de presença e iniciar o reconhecimento.</p>
    </div>
    <a href="chamadas.php" class="btn" style="border-color: var(--border-color); color: var(--text-muted);">
        <i class="ph ph-arrow-left"></i> Histórico
    </a>
</div>

<div class="card animate-fade-in" style="max-width: 500px; margin: 0 auto;">
    <form action="acoes/salvar_diario.php" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
        
        <!-- Seleção de Turma -->
        <div>
            <label for="turma_id" style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.5rem;">Escolha a Turma</label>
            <select name="turma_id" id="turma_id" required 
                    style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-family: inherit; background-color: white;">
                <option value="">Selecione uma turma...</option>
                <?php foreach ($turmas as $turma): ?>
                    <option value="<?= $turma['id'] ?>">
                        <?= htmlspecialchars($turma['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Data de Referência (Padrão: Hoje) -->
        <div>
            <label for="data" style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.5rem;">Data da Chamada</label>
            <input type="date" name="data" id="data" value="<?= date('Y-m-d') ?>" required 
                   style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-family: inherit;">
        </div>

        <div style="margin-top: 1rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.875rem; font-size: 1rem;">
                <i class="ph ph-play-circle"></i> Abrir Diário e Iniciar Câmera
            </button>
        </div>

    </form>
</div>

<?php 
require_once 'includes/footer.php'; 
?>
