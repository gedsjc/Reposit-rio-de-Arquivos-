<?php
// Diretório de upload para documentos
$docDir = 'docs/';

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['doc'])) {
        $file = $_FILES['doc'];
        $allowedTypes = ['text/plain', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

        // Verifica o tipo do arquivo
        if (in_array($file['type'], $allowedTypes)) {
            $uploadFilePath = $docDir . basename($file['name']);
            if (move_uploaded_file($file['tmp_name'], $uploadFilePath)) {
                echo "<p>Documento enviado com sucesso!</p>";
            } else {
                echo "<p>Erro ao enviar o documento.</p>";
            }
        } else {
            echo "<p>Tipo de arquivo não permitido. Envie apenas documentos de texto.</p>";
        }
    }
}

// Lista os documentos na pasta
$docs = array_diff(scandir($docDir), ['.', '..']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload de Documentos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        h1 {
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Upload de Documentos</h1>
    <form action="docs.php" method="post" enctype="multipart/form-data">
        <label for="doc">Selecione um documento:</label>
        <input type="file" name="doc" id="doc" accept=".txt,.pdf,.doc,.docx" required>
        <button type="submit">Upload</button>
    </form>

    <h2>Documentos Disponíveis</h2>
    <ul>
        <?php foreach ($docs as $doc): ?>
            <li>
                <a href="<?= $docDir . $doc ?>" target="_blank"><?= $doc ?></a>
                <span>(Enviado em: <?= date("d/m/Y H:i:s", filemtime($docDir . $doc)) ?>)</span>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
