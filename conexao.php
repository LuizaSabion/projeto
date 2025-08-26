<?php
$servidor = "localhost";
$usuario = "root"; // Usuário padrão do XAMPP/WAMP
$senha = ""; // Senha padrão do XAMPP/WAMP
$banco = "projeto1"; // Nome do seu banco de dados

// Criar a conexão
$conexao = mysqli_connect($servidor, $usuario, $senha, $banco);

// Checar a conexão
if (!$conexao) {
    die("Falha na conexão: " . mysqli_connect_error());
}
?>