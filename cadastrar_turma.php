<?php
// Inclui o cabeçalho e a conexão com o banco
require_once 'config/conexao.php';
require_once 'includes/header.php';
?>

<div class="cabecalho-pagina animacao-surgir">
    <div>
        <h1 class="titulo-pagina">Cadastrar Nova Turma</h1>
        <p class="subtitulo-pagina">Preencha os dados abaixo para criar uma nova turma no sistema.</p>
    </div>
    <a href="turmas.php" class="botao botao-secundario-texto">
        <i class="ph ph-arrow-left"></i> Voltar
    </a>
</div>

<div class="cartao animacao-surgir cartao-centralizado-600">
    <form action="acoes/salvar_turma.php" method="POST" class="formulario-coluna">
        
        <!-- Nome da Turma -->
        <div>
            <label for="nome" class="rotulo-formulario">Nome da Turma</label>
            <input type="text" name="nome" id="nome" placeholder="Ex: 1º Ano A - Informática" required class="controle-formulario">
        </div>

        <!-- Grade de Horários -->
        <div class="grade-duas-colunas">
            <div>
                <label for="horario_inicio" class="rotulo-formulario">Horário de Início</label>
                <input type="time" name="horario_inicio" id="horario_inicio" required class="controle-formulario">
            </div>
            <div>
                <label for="qtd_aulas" class="rotulo-formulario">Aulas por Dia</label>
                <input type="number" name="qtd_aulas" id="qtd_aulas" value="5" min="1" max="15" required class="controle-formulario">
            </div>
        </div>

        <div class="grade-duas-colunas">
            <div>
                <label for="duracao" class="rotulo-formulario">Duração Aula (min)</label>
                <input type="number" name="duracao" id="duracao" value="45" min="10" max="120" required class="controle-formulario">
            </div>
            <div class="caixa-selecao-alinhada">
                <label class="rotulo-clicavel">
                    <input type="checkbox" name="automatica" value="1" style="width: 1.2rem; height: 1.2rem;">
                    Chamada Automática?
                </label>
            </div>
        </div>

        <div class="rodape-formulario">
            <button type="submit" class="botao botao-principal botao-largo">
                <i class="ph ph-check"></i> Salvar Turma
            </button>
        </div>
    </form>
</div>

<?php

require_once 'includes/footer.php';

?>
