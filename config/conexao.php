<?php
// Define o fuso horário padrão como o de Brasília
date_default_timezone_set('America/Sao_Paulo');

// Arquivo de configuração para conexão com o banco de dados
// Usamos a biblioteca PDO (PHP Data Objects) que é a forma mais segura e moderna de conectar ao banco
// Ela nos protege de ataques como Injeção de SQL (SQL Injection)

// Variáveis com os dados do nosso banco de dados
$host = 'localhost'; // O endereço onde o banco está rodando (na nossa própria máquina)
$banco = 'carometrodb'; // O nome do banco de dados que criamos
$usuario = 'root'; // O usuário padrão do banco de dados (no XAMPP costuma ser root)
$senha = ''; // A senha do banco (no XAMPP, a senha do usuário root por padrão é vazia)
$port = '3307';
// Vamos tentar fazer a conexão
try {
    // Montamos a string de conexão (DSN) que diz pro PHP qual tipo de banco é (mysql), onde está (host), qual a porta e qual o nome dele (dbname)
    // Também configuramos para usar o padrão de caracteres UTF-8, assim acentos e cedilhas funcionam perfeitamente
    $conexao = new PDO("mysql:host=$host;port=$port;dbname=$banco;charset=utf8", $usuario, $senha);

    // Configuramos o PDO para mostrar os erros caso algo dê errado
    // O modo de erro 'ERRMODE_EXCEPTION' faz com que qualquer erro no banco pare o código e mostre o que aconteceu
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Descomente a linha abaixo caso queira testar se a conexão deu certo (vai aparecer a mensagem na tela)
// echo "Conexão com o banco de dados realizada com sucesso!";

}
catch (PDOException $erro) {
    // Se a conexão falhar (cair no bloco try), o PHP pula para cá (catch)
    // Aqui mostramos uma mensagem amigável e o detalhe do erro para sabermos o que arrumar
    echo " <h2>Poxa, falhou ao conectar com o banco de dados. <br> <h2>";
    echo "<h2>O erro foi esse: " . $erro->getMessage() . "</h2>";
}
?>
