<?php
session_start();

// Verifica se o usuário já está logado, redireciona para a página de upload
if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$loginError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Definir usuário e senha de exemplo
    $validUsername = 'admin';
    $validPassword = '123456';

    // Captura o nome de usuário e senha do formulário
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Verifica as credenciais
    if ($username === $validUsername && $password === $validPassword) {
        // Cria uma variável de sessão para o usuário
        $_SESSION['username'] = $username;
        header("Location: index.php"); // Redireciona para a página de upload
        exit();
    } else {
        $loginError = 'Usuário ou senha inválidos';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f7fc;
            color: #333;
        }
        h1 {
            text-align: center;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        form {
            margin: 0 auto;
            text-align: center;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        label {
            font-size: 1em;
            margin-bottom: 8px;
            display: block;
        }
        input[type="text"], input[type="password"] {
            padding: 8px;
            margin: 8px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 100%;
        }
        button {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h1>Login</h1>
    <form action="login.php" method="POST">
        <div class="error"><?php echo $loginError; ?></div>
        <label for="username">Nome de Usuário:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Senha:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Entrar</button>
    </form>
</body>
</html>
