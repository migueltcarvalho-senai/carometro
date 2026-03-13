CREATE DATABASE carametro
USE carametro

CREATE TABLE turmas(
id INT PRIMARY KEY AUTO_INCREMENT,
nome VARCHAR(255),
horario_inicio_chamada TIME,
qtd_aulas_dia INT,
duracao_aula INT,
chamada_automatica BOOLEAN
)

CREATE TABLE alunos(
id INT PRIMARY KEY AUTO_INCREMENT,
turma_id INT,
registro_matricula VARCHAR(255) UNIQUE,
nome_completo VARCHAR(255),
caminho_foto VARCHAR(255),
vetor_facial JSON,
FOREIGN KEY (turma_id) REFERENCES turmas(id)
)

CREATE TABLE diarios_chamada(
id INT PRIMARY KEY AUTO_INCREMENT,
turma_id INT,
data_referencia DATE,
iniciada_em DATETIME,
FOREIGN KEY(turma_id) REFERENCES turmas(id)
)

CREATE TABLE presencas(
id INT PRIMARY KEY AUTO_INCREMENT,
aluno_id INT,
diario_id INT,
aula_numero INT,
status_presenca BOOLEAN,
horario_deteccao TIME,
FOREIGN KEY(aluno_id) REFERENCES alunos(id),
FOREIGN KEY(diario_id) REFERENCES diarios_chamada(id)
)




ALTER TABLE alunos AUTO_INCREMENT=1


ALTER TABLE turmas MODIFY COLUMN id INT AUTO_INCREMENT PRIMARY KEY;