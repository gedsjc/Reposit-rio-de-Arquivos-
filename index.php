<?php
session_start();

// Função para mover os arquivos para o diretório correto e salvar metadados
function handleFileUpload($fileType) {
    $targetDir = ($fileType == 'image') ? 'uploads/images/' : 'uploads/documents/';
    if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $tempName = $_FILES['file']['tmp_name'];
        $fileName = basename($_FILES['file']['name']);
        $targetPath = $targetDir . $fileName;

        // Move o arquivo para o diretório apropriado
        if (move_uploaded_file($tempName, $targetPath)) {
            // Salva os metadados (nome do usuário e data de upload)
            $metadata = [
                'username' => $_SESSION['username'],
                'date' => date("d/m/Y H:i:s")
            ];

            // Salva os metadados em um arquivo JSON
            $metadataFile = $targetDir . $fileName . '.json';
            file_put_contents($metadataFile, json_encode($metadata));

            echo "Arquivo enviado com sucesso!";
        } else {
            echo "Erro ao enviar o arquivo.";
        }
    } else {
        echo "Erro no envio do arquivo.";
    }
}

// Função para excluir arquivos antigos na pasta 'deleted'
function deleteExpiredFiles($dir, $days = 7) {
    // Obtém todos os arquivos no diretório
    $files = array_diff(scandir($dir), array('.', '..'));

    foreach ($files as $file) {
        $filePath = $dir . $file;
        $metadataFile = $filePath . '.json';

        // Verifica se o arquivo tem metadados e se a data de exclusão passou
        if (file_exists($metadataFile)) {
            $metadata = json_decode(file_get_contents($metadataFile), true);
            $deleteDate = strtotime($metadata['date']);
            $currentDate = time();

            // Calcula a diferença em dias
            $diffDays = ($currentDate - $deleteDate) / (60 * 60 * 24);

            // Se passaram mais de 7 dias, exclui o arquivo
            if ($diffDays > $days) {
                unlink($filePath);
                unlink($metadataFile);  // Remove também os metadados
                echo "Arquivo $file excluído automaticamente após $diffDays dias.<br>";
            }
        }
    }
}

// Função para excluir um arquivo
function deleteFile($fileType, $fileName) {
    $dir = ($fileType == 'image') ? 'uploads/images/' : 'uploads/documents/';
    $filePath = $dir . $fileName;
    $metadataFile = $filePath . '.json';

    if (file_exists($filePath)) {
        unlink($filePath); // Deleta o arquivo
    }

    if (file_exists($metadataFile)) {
        unlink($metadataFile); // Deleta os metadados
    }
}

// Função para mover o arquivo para a pasta de excluídos
function moveToDeleted($fileType, $fileName) {
    $sourceDir = ($fileType == 'image') ? 'uploads/images/' : 'uploads/documents/';
    $targetDir = 'uploads/deleted/';

    // Mover o arquivo
    if (rename($sourceDir . $fileName, $targetDir . $fileName)) {
        // Mover os metadados do arquivo
        $metadataFile = $sourceDir . $fileName . '.json';
        if (file_exists($metadataFile)) {
            rename($metadataFile, $targetDir . $fileName . '.json');
        }
    }
}

// Verifica se o usuário está logado
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Função de logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Diretórios de arquivos
$imageDir = 'uploads/images/';
$docDir = 'uploads/documents/';
$deletedDir = 'uploads/deleted/';

// Inicializa as variáveis de arquivos
$imageFiles = [];
$docFiles = [];
$deletedFiles = [];

// Verifica se os diretórios existem e obtém os arquivos
if (is_dir($imageDir)) {
    $imageFiles = array_diff(scandir($imageDir), array('.', '..'));
}

if (is_dir($docDir)) {
    $docFiles = array_diff(scandir($docDir), array('.', '..'));
}

// Verifica se o diretório de exclusão existe, caso contrário, cria
if (!is_dir($deletedDir)) {
    mkdir($deletedDir, 0777, true);
}

// Obtém os arquivos do diretório de exclusão
if (is_dir($deletedDir)) {
    // Deleta arquivos antigos
    deleteExpiredFiles($deletedDir);

    $deletedFiles = array_diff(scandir($deletedDir), array('.', '..'));
}

// Verifica se o formulário foi enviado para upload de arquivos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $fileType = $_POST['fileType'];
    handleFileUpload($fileType);
}

// Verifica se foi solicitado para excluir um arquivo
if (isset($_POST['delete_file'])) {
    $fileType = $_POST['file_type'];
    $fileName = $_POST['file_name'];
    deleteFile($fileType, $fileName);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repositório de Arquivos</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 30px;
            color: #333;
        }

        h1 {
            color: #007bff;
            text-align: center;
            margin-bottom: 20px;
        }

        .header {
            text-align: center;
            background-color: #c3e383;
            padding: 20px;
            border-radius: 8px;
            color: white;
            margin-bottom: 30px;
        }

        .tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 40px;
        }

        .tabs button {
            padding: 12px 25px;
            margin: 0 15px;
            background-color: #6c757d;
            color: white;
            border: none;
            font-size: 18px;
            cursor: pointer;
            border-radius: 30px;
            transition: background-color 0.3s ease;
        }

        .tabs button:hover,
        .tabs button.active {
            background-color: #0056b3;
        }

        .tabs button:focus {
            outline: none;
        }

        .content {
            display: none;
        }

        .content.active {
            display: block;
        }

        .file-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .file-item {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .file-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
        }

        .file-item a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        .file-item a:hover {
            text-decoration: underline;
        }

        .no-files {
            color: #868e96;
            font-size: 18px;
            text-align: center;
        }

        .upload-section {
            background-color: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin: 30px auto;
            max-width: 500px;
        }

        .upload-section select,
        .upload-section input[type="file"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 16px;
        }

        .upload-section button {
            background-color: #28a745;
            color: white;
            padding: 15px 25px;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }

        .logout-button {
            text-align: center;
            margin-top: 30px;
        }

        .logout-button button {
            background-color: #dc3545;
            color: white;
            padding: 15px 25px;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }

        .logout-button button:hover {
            background-color: #c82333;
        }

        .delete-button {
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }

        .delete-button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Bem-vindo, <?php echo htmlspecialchars($_SESSION['username']); ?>! Consulte ou anexe os documentos e imagens</h1>
    </div>

    <div class="tabs">
        <button onclick="toggleSection('uploadData')" class="active">Anexar Dados</button>
        <button onclick="toggleSection('images')">Imagens</button>
        <button onclick="toggleSection('documents')">Documentos</button>
        <button onclick="toggleSection('deletedDocuments')">Documentos Excluídos</button>
    </div>

    <!-- Formulário de upload dentro da aba 'Anexar Dados' -->
    <div id="uploadData" class="content active">
        <h2>Anexar Dados</h2>
        <div class="upload-section">
            <form action="index.php" method="POST" enctype="multipart/form-data">
                <select name="fileType" required>
                    <option value="image">Imagem</option>
                    <option value="document">Documento</option>
                </select><br><br>
                <input type="file" name="file" required><br><br>
                <button type="submit">Enviar</button>
            </form>
        </div>
    </div>

    <!-- Imagens -->
    <div id="images" class="content">
        <h2>Imagens</h2>
        <div class="file-list">
            <?php if (count($imageFiles) > 0): ?>
                <?php foreach ($imageFiles as $file): ?>
                    <div class="file-item">
                        <img src="<?php echo $imageDir . $file; ?>" alt="<?php echo $file; ?>" style="width: 100%; max-height: 150px; object-fit: cover;">
                        <a href="<?php echo $imageDir . $file; ?>" download>Baixar <?php echo $file; ?></a>
                        <form method="POST" style="margin-top: 10px;">
                            <input type="hidden" name="file_type" value="image">
                            <input type="hidden" name="file_name" value="<?php echo $file; ?>">
                            <button type="submit" name="delete_file" class="delete-button">Excluir</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-files">Nenhuma imagem foi carregada ainda.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Documentos -->
    <div id="documents" class="content">
        <h2>Documentos</h2>
        <div class="file-list">
            <?php if (count($docFiles) > 0): ?>
                <?php foreach ($docFiles as $file): ?>
                    <div class="file-item">
                        <a href="<?php echo $docDir . $file; ?>" download>Baixar <?php echo $file; ?></a>
                        <form method="POST" style="margin-top: 10px;">
                            <input type="hidden" name="file_type" value="document">
                            <input type="hidden" name="file_name" value="<?php echo $file; ?>">
                            <button type="submit" name="delete_file" class="delete-button">Excluir</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-files">Nenhum documento foi carregado ainda.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Documentos Excluídos -->
    <div id="deletedDocuments" class="content">
        <h2>Documentos Excluídos</h2>
        <div class="deleted-info">
            <p>Documentos que estiverem nesta pasta serão excluídos automaticamente após 7 dias.</p>
        </div>
        <div class="file-list">
            <?php if (count($deletedFiles) > 0): ?>
                <?php foreach ($deletedFiles as $file): ?>
                    <div class="file-item">
                        <a href="<?php echo $deletedDir . $file; ?>" download>Baixar <?php echo $file; ?></a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-files">Nenhum documento excluído foi encontrado.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Botão de logout -->
    <div class="logout-button">
        <form method="POST" action="index.php">
            <button type="submit" name="logout">Sair</button>
        </form>
    </div>

    <script>
        // Função para alternar entre as seções
        function toggleSection(section) {
            // Esconde todas as seções
            document.querySelectorAll('.content').forEach(content => content.classList.remove('active'));
            document.querySelectorAll('.tabs button').forEach(button => button.classList.remove('active'));

            // Exibe a seção correspondente
            document.getElementById(section).classList.add('active');
            document.querySelector(`[onclick="toggleSection('${section}')"]`).classList.add('active');
        }
    </script>
</body>
</html>
