<?php
// Arquivo de configuração para conexão com o banco de dados
// Usamos a biblioteca PDO (PHP Data Objects) que é a forma mais segura e moderna de conectar ao banco
// Ela nos protege de ataques como Injeção de SQL (SQL Injection)

// Variáveis com os dados do nosso banco de dados
$host = 'localhost'; // O endereço onde o banco está rodando (na nossa própria máquina)
$banco = 'carametro'; // O nome do banco de dados que criamos
$usuario = 'root'; // O usuário padrão do banco de dados (no XAMPP costuma ser root)
$senha = ''; // A senha do banco (no XAMPP, a senha do usuário root por padrão é vazia)

// Vamos tentar fazer a conexão
try {
    // Montamos a string de conexão (DSN) que diz pro PHP qual tipo de banco é (mysql), onde está (host), qual a porta e qual o nome dele (dbname)
    // Também configuramos para usar o padrão de caracteres UTF-8, assim acentos e cedilhas funcionam perfeitamente
    $conexao = new PDO("mysql:host=$host;port=3307;dbname=$banco;charset=utf8", $usuario, $senha);

    // Configuramos o PDO para mostrar os erros caso algo dê errado
    // O modo de erro 'ERRMODE_EXCEPTION' faz com que qualquer erro no banco pare o código e mostre o que aconteceu
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Descomente a linha abaixo caso queira testar se a conexão deu certo (vai aparecer a mensagem na tela)
    // echo "Conexão com o banco de dados realizada com sucesso!";

} catch (PDOException $erro) {
    // Se a conexão falhar (cair no bloco try), o PHP pula para cá (catch)
    // Aqui mostramos uma mensagem amigável e o detalhe do erro para sabermos o que arrumar
    echo "Poxa, falhou ao conectar com o banco de dados. <br>";
    echo "O erro foi esse: " . $erro->getMessage();
}
?>
