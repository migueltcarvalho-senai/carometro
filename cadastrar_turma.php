<?php
// Inclui o cabeçalho e a conexão com o banco
require_once 'config/conexao.php';
require_once 'includes/header.php';
?>

<div class="page-header animate-fade-in">
    <div>
        <h1 class="page-title">Cadastrar Nova Turma</h1>
        <p style="color: var(--text-muted); margin-top: 0.25rem;">Preencha os dados abaixo para criar uma nova turma no sistema.</p>
    </div>
    <a href="turmas.php" class="btn" style="border-color: var(--border-color); color: var(--text-muted);">
        <i class="ph ph-arrow-left"></i> Voltar
    </a>
</div>

<div class="card animate-fade-in" style="max-width: 600px; margin: 0 auto;">
    <form action="acoes/salvar_turma.php" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
        
        <!-- Nome da Turma -->
        <div>
            <label for="nome" style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.5rem;">Nome da Turma</label>
            <input type="text" name="nome" id="nome" placeholder="Ex: 1º Ano A - Informática" required 
                   style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-family: inherit;">
        </div>

        <!-- Grade de Horários -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div>
                <label for="horario_inicio" style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.5rem;">Horário de Início</label>
                <input type="time" name="horario_inicio" id="horario_inicio" required 
                       style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-family: inherit;">
            </div>
            <div>
                <label for="qtd_aulas" style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.5rem;">Aulas por Dia</label>
                <input type="number" name="qtd_aulas" id="qtd_aulas" value="5" min="1" max="15" required 
                       style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-family: inherit;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div>
                <label for="duracao" style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.5rem;">Duração Aula (min)</label>
                <input type="number" name="duracao" id="duracao" value="50" min="10" max="120" required 
                       style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-family: inherit;">
            </div>
            <div style="display: flex; align-items: flex-end; padding-bottom: 0.75rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; font-weight: 600; color: var(--text-muted); cursor: pointer;">
                    <input type="checkbox" name="automatica" value="1" style="width: 1.2rem; height: 1.2rem;">
                    Chamada Automática?
                </label>
            </div>
        </div>

        <div style="margin-top: 1rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.875rem;">
                <i class="ph ph-check"></i> Salvar Turma
            </button>
        </div>
    </form>
</div>

<?php 
require_once 'includes/footer.php'; 
?>
