-- ============================================================
-- SCRIPT PARA MONTAR O BANCO DE DADOS DO CARÔMETRO
-- Este arquivo serve APENAS para criar manualmente a estrutura
-- do banco. Não deve ser acessado via navegador.
-- ============================================================

-- Cria o banco de dados caso não exista
CREATE DATABASE IF NOT EXISTS carometrodb;

-- Seleciona o banco para uso
USE carometrodb;

-- ============================================================
-- TABELA: turmas
-- Armazena as turmas cadastradas no sistema
-- ============================================================
CREATE TABLE turmas(
    id INT PRIMARY KEY AUTO_INCREMENT,                 -- ID único da turma
    nome VARCHAR(255),                                  -- Nome da turma (ex: "1º Ano A - Informática")
    horario_inicio_chamada TIME,                        -- Horário que a chamada começa
    qtd_aulas_dia INT,                                  -- Quantidade de aulas por dia
    duracao_aula INT,                                   -- Duração de cada aula em minutos
    chamada_automatica BOOLEAN                          -- Se a chamada é disparada automaticamente
);

-- ============================================================
-- TABELA: alunos
-- Armazena os dados dos alunos, incluindo a biometria facial
-- O campo vetor_facial guarda um JSON com os descritores
-- extraídos pela face-api.js no momento do cadastro
-- ============================================================
CREATE TABLE alunos(
    id INT PRIMARY KEY AUTO_INCREMENT,                 -- ID único do aluno
    turma_id INT,                                       -- ID da turma a que pertence
    registro_matricula VARCHAR(255) UNIQUE,             -- Registro de matrícula (RM) único
    nome_completo VARCHAR(255),                         -- Nome completo do aluno
    caminho_foto VARCHAR(255),                          -- Caminho relativo da foto salva no servidor
    vetor_facial JSON,                                  -- JSON com os vetores faciais (descritores da IA)
    FOREIGN KEY (turma_id) REFERENCES turmas(id)        -- Chave estrangeira ligando à turma
);

-- ============================================================
-- TABELA: diarios_chamada
-- Registra cada sessão de chamada iniciada (um diário por dia/turma)
-- ============================================================
CREATE TABLE diarios_chamada(
    id INT PRIMARY KEY AUTO_INCREMENT,                 -- ID único do diário
    turma_id INT,                                       -- ID da turma
    data_referencia DATE,                               -- Data da chamada
    iniciada_em DATETIME,                               -- Data e hora exata que a chamada foi aberta
    FOREIGN KEY(turma_id) REFERENCES turmas(id)         -- Chave estrangeira ligando à turma
);

-- ============================================================
-- TABELA: presencas
-- Registra a presença individual de cada aluno em cada aula
-- O campo aula_numero permite controle por aula específica
-- ============================================================
CREATE TABLE presencas(
    id INT PRIMARY KEY AUTO_INCREMENT,                 -- ID único do registro de presença
    aluno_id INT,                                       -- ID do aluno
    diario_id INT,                                      -- ID do diário de chamada
    aula_numero INT,                                    -- Número da aula (1ª, 2ª, etc.)
    status_presenca BOOLEAN,                            -- true = presente, false = ausente
    horario_deteccao TIME,                              -- Hora exata que o rosto foi detectado
    FOREIGN KEY(aluno_id) REFERENCES alunos(id),        -- Chave estrangeira ligando ao aluno
    FOREIGN KEY(diario_id) REFERENCES diarios_chamada(id) -- Chave estrangeira ligando ao diário
);