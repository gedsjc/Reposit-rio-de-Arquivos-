<?php
// Diretório de upload para imagens
$imageDir = 'images/';

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['image'])) {
        $file = $_FILES['image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif']; // Tipos permitidos

        // Verifica o tipo do arquivo
        if (in_array($file['type'], $allowedTypes)) {
            $uploadFilePath = $imageDir . basename($file['name']);
            if (move_uploaded_file($file['tmp_name'], $uploadFilePath)) {
                echo "<p>Imagem enviada com sucesso!</p>";
            } else {
                echo "<p>Erro ao enviar a imagem.</p>";
            }
        } else {
            echo "<p>Tipo de arquivo não permitido. Envie apenas imagens.</p>";
        }
    }
}

// Lista as imagens na pasta
$images = array_diff(scandir($imageDir), ['.', '..']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload de Imagens</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        h1 {
            text-align: center;
        }
        img {
            max-width: 200px;
            display: block;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h1>Upload de Imagens</h1>
    <form action="images.php" method="post" enctype="multipart/form-data">
        <label for="image">Selecione uma imagem:</label>
        <input type="file" name="image" id="image" accept="image/*" required>
        <button type="submit">Upload</button>
    </form>

    <h2>Imagens Disponíveis</h2>
    <ul>
        <?php foreach ($images as $image): ?>
            <li>
                <img src="<?= $imageDir . $image ?>" alt="<?= $image ?>">
                <span><?= $image ?> - <?= date("d/m/Y H:i:s", filemtime($imageDir . $image)) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
