<?php
// Carrega a conexão com o banco
require_once 'config/conexao.php';
require_once 'includes/header.php';

// Busca todas as turmas cadastradas para o usuário escolher uma
$stmt = $conexao->query("SELECT * FROM turmas ORDER BY nome ASC");
$turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="cabecalho-pagina animacao-surgir">
    <div>
        <h1 class="titulo-pagina">Iniciar Nova Chamada</h1>
        <p class="subtitulo-pagina">Selecione a turma para abrir o diário de presença e iniciar o reconhecimento.</p>
    </div>
    <a href="chamadas.php" class="botao botao-secundario-texto">
        <i class="ph ph-arrow-left"></i> Histórico
    </a>
</div>

<div class="cartao animacao-surgir cartao-centralizado-500">
    <form action="acoes/salvar_diario.php" method="POST" class="formulario-coluna">
        
        <!-- Seleção de Turma -->
        <div>
            <label for="turma_id" class="rotulo-formulario">Escolha a Turma</label>
            <select name="turma_id" id="turma_id" required class="controle-formulario bg-branco">
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
            <label for="data" class="rotulo-formulario">Data da Chamada</label>
            <input type="date" name="data" id="data" value="<?= date('Y-m-d') ?>" required class="controle-formulario">
        </div>

        <div class="divisor-topo-formulario">
            <button type="submit" class="botao botao-principal botao-largo">
                <i class="ph ph-play-circle"></i> Abrir Diário e Iniciar Câmera
            </button>
        </div>

    </form>
</div>

<?php 
require_once 'includes/footer.php'; 
?>
