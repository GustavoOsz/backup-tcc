<?php
session_start();

// Conexão com o banco
$conn = new mysqli("localhost", "root", "", "tenda");

// Verifica conexão
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Verifica se é admin
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$mensagem = "";

// Se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];

    // Inicia transação
    $conn->begin_transaction();

    try {
        // Upload da imagem principal
        $imagem_principal = "";
        if (isset($_FILES['imagem_principal']) && $_FILES['imagem_principal']['error'] === 0) {
            $nomeImg = uniqid() . "-" . basename($_FILES['imagem_principal']['name']);
            $caminho = "../img/" . $nomeImg;
            if (move_uploaded_file($_FILES['imagem_principal']['tmp_name'], $caminho)) {
                $imagem_principal = "img/" . $nomeImg;
            }
        }

        // Insere o produto principal
        $sql = "INSERT INTO produto (nome, descricao, preco, imagem) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssds", $nome, $descricao, $preco, $imagem_principal);
        
        if ($stmt->execute()) {
            $produto_id = $conn->insert_id;
            
            // Processa imagens adicionais
            if (isset($_FILES['imagens_adicionais']) && !empty($_FILES['imagens_adicionais']['name'][0])) {
                $imagens_adicionais = [];
                
                // Cria a tabela de imagens se não existir
                $createTable = "
                    CREATE TABLE IF NOT EXISTS produto_imagens (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        produto_id INT,
                        caminho_imagem VARCHAR(255),
                        ordem INT DEFAULT 0,
                        data_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (produto_id) REFERENCES produto(id) ON DELETE CASCADE
                    )
                ";
                $conn->query($createTable);
                
                // Processa cada imagem adicional
                foreach ($_FILES['imagens_adicionais']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['imagens_adicionais']['error'][$key] === 0) {
                        $nomeImg = uniqid() . "-" . basename($_FILES['imagens_adicionais']['name'][$key]);
                        $caminho = "../img/" . $nomeImg;
                        
                        if (move_uploaded_file($tmp_name, $caminho)) {
                            $caminho_banco = "img/" . $nomeImg;
                            
                            // Insere na tabela de imagens
                            $sql_imagem = "INSERT INTO produto_imagens (produto_id, caminho_imagem, ordem) VALUES (?, ?, ?)";
                            $stmt_imagem = $conn->prepare($sql_imagem);
                            $ordem = $key + 1;
                            $stmt_imagem->bind_param("isi", $produto_id, $caminho_banco, $ordem);
                            $stmt_imagem->execute();
                            $stmt_imagem->close();
                            
                            $imagens_adicionais[] = $caminho_banco;
                        }
                    }
                }
            }
            
            $conn->commit();
            $mensagem = "success:Produto adicionado com sucesso!" . 
                       (isset($imagens_adicionais) ? " " . count($imagens_adicionais) . " imagens adicionais salvas." : "");
            
        } else {
            throw new Exception("Erro ao inserir produto: " . $conn->error);
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        $mensagem = "error:" . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Produto - Tenda das Marés</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #fef7ed 0%, #fffbeb 50%, #fef3c7 100%);
        }
        .border-amber-custom {
            border-color: #d97706;
        }
        .shadow-amber {
            box-shadow: 0 10px 25px -5px rgba(217, 119, 6, 0.1), 0 4px 6px -2px rgba(217, 119, 6, 0.05);
        }
        .input-focus:focus {
            border-color: #d97706;
            box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.1);
        }
        .file-upload {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }
        .file-upload input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        .preview-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        .preview-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            height: 100px;
        }
        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255, 0, 0, 0.7);
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
        .image-counter {
            background: #d97706;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            position: absolute;
            top: -8px;
            right: -8px;
        }
    </style>
</head>
<body class="gradient-bg min-h-screen">

  <!-- Header Moderno -->
  <header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="container mx-auto px-6 py-4">
        <div class="flex justify-between items-center">
            <div>
                <a href="../index.php" class="flex items-center space-x-3">
                    <img src="../img/logo.png" alt="Logo" class="h-12">
                    <span class="text-2xl font-bold text-[#4f2905] hidden md:block">Tenda das Marés</span>
                </a>
            </div>
            <nav class="flex items-center gap-6 text-lg font-medium">
                <a href="../produtos.php" class="hover:text-[#b85e2b] transition-colors">Produtos</a>
                <a href="../pesquisas.php" class="hover:text-[#b85e2b] transition-colors">Pesquisas</a>
                <a href="../sobre.php" class="hover:text-[#b85e2b] transition-colors">Sobre nós</a>
                <a href="../contato.php" class="hover:text-[#b85e2b] transition-colors">Contato</a>
                <a href="../perfil.php" class="p-2 hover:bg-[#f5f1e6] rounded-full transition-colors">
                    <i class="fas fa-user text-[#4f2905] text-lg"></i>
                </a>
            </nav>
        </div>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="bg-gradient-to-r from-[#b85e2b] to-[#f7b95e] py-12 text-white">
    <div class="container mx-auto px-6 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Adicionar Produto</h1>
        <p class="text-xl opacity-90 max-w-3xl mx-auto">
            Cadastre novos produtos com múltiplas imagens
        </p>
    </div>
  </section>

  <!-- Conteúdo Principal -->
  <div class="container mx-auto px-6 py-8">
    <div class="max-w-4xl mx-auto">
        
        <!-- Card do Formulário -->
        <div class="bg-white rounded-2xl shadow-amber border border-amber-200 overflow-hidden">
            
            <!-- Cabeçalho do Card -->
            <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-8 py-6 text-white">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <i class="fas fa-plus text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">Novo Produto</h2>
                        <p class="text-amber-100 text-sm">Adicione informações e imagens do produto</p>
                    </div>
                </div>
            </div>

            <!-- Mensagem de Status -->
            <?php if ($mensagem): 
                $tipo = explode(':', $mensagem)[0];
                $texto = explode(':', $mensagem)[1];
            ?>
                <div class="<?= $tipo === 'success' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' ?> border-l-4 <?= $tipo === 'success' ? 'border-green-500' : 'border-red-500' ?> p-4 mx-8 mt-6 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <i class="fas <?= $tipo === 'success' ? 'fa-check-circle text-green-500' : 'fa-exclamation-circle text-red-500' ?> text-lg"></i>
                        <p class="<?= $tipo === 'success' ? 'text-green-800' : 'text-red-800' ?> font-medium"><?= $texto ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Formulário -->
            <form method="POST" enctype="multipart/form-data" class="p-8 space-y-8">
                
                <!-- Informações Básicas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Campo Nome -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-tag text-amber-500 mr-2"></i>
                            Nome do Produto *
                        </label>
                        <input type="text" name="nome" required 
                               class="w-full border border-gray-300 rounded-xl p-4 input-focus transition-all duration-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                               placeholder="Ex: Vela Ritualística de 7 Dias">
                    </div>

                    <!-- Campo Preço -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-dollar-sign text-amber-500 mr-2"></i>
                            Preço *
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500">R$</span>
                            <input type="number" step="0.01" name="preco" required 
                                   class="w-full border border-gray-300 rounded-xl p-4 pl-12 input-focus transition-all duration-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                   placeholder="0,00" min="0">
                        </div>
                    </div>
                </div>

                <!-- Campo Descrição -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700">
                        <i class="fas fa-align-left text-amber-500 mr-2"></i>
                        Descrição *
                    </label>
                    <textarea name="descricao" required rows="4"
                              class="w-full border border-gray-300 rounded-xl p-4 input-focus transition-all duration-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                              placeholder="Descreva o produto, seus benefícios, características e uso..."></textarea>
                </div>

                <!-- Seção de Imagens -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2">
                        <i class="fas fa-images text-amber-500 mr-2"></i>
                        Imagens do Produto
                    </h3>

                    <!-- Imagem Principal -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">
                            Imagem Principal *
                            <span class="text-amber-600 ml-2 text-xs">(Imagem de destaque)</span>
                        </label>
                        <div class="file-upload">
                            <div class="border-2 border-dashed border-amber-300 rounded-xl p-6 text-center transition-all duration-200 hover:border-amber-400 hover:bg-amber-50 bg-amber-25">
                                <i class="fas fa-star text-2xl text-amber-500 mb-3"></i>
                                <p class="text-amber-700 font-medium">Imagem Principal</p>
                                <p class="text-amber-600 text-sm mt-1">Esta será a imagem de capa do produto</p>
                                <p class="text-gray-400 text-xs mt-2">Clique para selecionar (PNG, JPG, JPEG até 5MB)</p>
                                <input type="file" name="imagem_principal" accept="image/*" required class="cursor-pointer">
                            </div>
                        </div>
                    </div>

                    <!-- Imagens Adicionais -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">
                            Imagens Adicionais
                            <span class="text-gray-500 ml-2 text-xs">(Opcional - máximo 5 imagens)</span>
                        </label>
                        <div class="file-upload">
                            <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center transition-all duration-200 hover:border-amber-400 hover:bg-amber-50 relative">
                                <i class="fas fa-layer-group text-2xl text-gray-400 mb-3"></i>
                                <p class="text-gray-600 font-medium">Imagens Adicionais</p>
                                <p class="text-gray-500 text-sm mt-1">Adicione mais fotos do produto (diferentes ângulos)</p>
                                <p class="text-gray-400 text-xs mt-2">Clique para selecionar múltiplas imagens</p>
                                <input type="file" name="imagens_adicionais[]" accept="image/*" multiple class="cursor-pointer">
                                <div class="image-counter hidden">0</div>
                            </div>
                        </div>
                        
                        <!-- Preview das Imagens Adicionais -->
                        <div class="preview-container hidden" id="preview-container"></div>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200">
                    <button type="submit" 
                            class="flex-1 bg-gradient-to-r from-amber-500 to-orange-500 text-white py-4 px-6 rounded-xl font-semibold hover:from-amber-600 hover:to-orange-600 transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center space-x-2">
                        <i class="fas fa-plus"></i>
                        <span>Adicionar Produto</span>
                    </button>
                    
                    <a href="../produtos.php" 
                       class="flex-1 bg-gradient-to-r from-gray-500 to-gray-600 text-white py-4 px-6 rounded-xl font-semibold hover:from-gray-600 hover:to-gray-700 transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center space-x-2">
                        <i class="fas fa-arrow-left"></i>
                        <span>Voltar aos Produtos</span>
                    </a>
                </div>

            </form>

            <!-- Rodapé do Card -->
            <div class="bg-amber-50 px-8 py-4 border-t border-amber-200">
                <div class="flex flex-col md:flex-row items-center justify-between text-sm text-amber-700 space-y-2 md:space-y-0">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-info-circle"></i>
                        <span>* Campos obrigatórios</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-camera"></i>
                        <span>Imagem principal + até 5 imagens adicionais</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-shield-alt"></i>
                        <span>Cadastro seguro</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Informações Adicionais -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
            <div class="bg-white rounded-xl p-6 text-center border border-amber-200 shadow-sm">
                <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-camera text-amber-600"></i>
                </div>
                <h3 class="font-semibold text-gray-800 mb-2">Múltiplas Imagens</h3>
                <p class="text-gray-600 text-sm">Até 6 imagens por produto</p>
            </div>

            <div class="bg-white rounded-xl p-6 text-center border border-amber-200 shadow-sm">
                <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-star text-amber-600"></i>
                </div>
                <h3 class="font-semibold text-gray-800 mb-2">Imagem Principal</h3>
                <p class="text-gray-600 text-sm">Destaque para sua loja</p>
            </div>

            <div class="bg-white rounded-xl p-6 text-center border border-amber-200 shadow-sm">
                <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-sync text-amber-600"></i>
                </div>
                <h3 class="font-semibold text-gray-800 mb-2">Fácil Gerenciamento</h3>
                <p class="text-gray-600 text-sm">Adicione ou remova depois</p>
            </div>
        </div>

    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Preview para imagem principal
        const fileInputPrincipal = document.querySelector('input[name="imagem_principal"]');
        const uploadAreaPrincipal = document.querySelector('input[name="imagem_principal"]').parentElement;
        
        fileInputPrincipal.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const fileName = this.files[0].name;
                uploadAreaPrincipal.innerHTML = `
                    <i class="fas fa-check-circle text-green-500 text-3xl mb-3"></i>
                    <p class="text-green-600 font-medium">Imagem Principal Selecionada</p>
                    <p class="text-green-500 text-sm mt-1">${fileName}</p>
                    <p class="text-gray-400 text-xs mt-2">Clique para alterar</p>
                    <input type="file" name="imagem_principal" accept="image/*" required class="cursor-pointer">
                `;
                uploadAreaPrincipal.classList.remove('hover:border-amber-400', 'hover:bg-amber-50');
                uploadAreaPrincipal.classList.add('border-green-300', 'bg-green-50');
            }
        });

        // Preview para imagens adicionais
        const fileInputAdicionais = document.querySelector('input[name="imagens_adicionais[]"]');
        const uploadAreaAdicionais = document.querySelector('input[name="imagens_adicionais[]"]').parentElement;
        const previewContainer = document.getElementById('preview-container');
        const imageCounter = uploadAreaAdicionais.querySelector('.image-counter');
        let selectedFiles = [];

        fileInputAdicionais.addEventListener('change', function() {
            const files = Array.from(this.files);
            
            // Limita a 5 imagens
            if (files.length > 5) {
                alert('Máximo de 5 imagens adicionais permitidas.');
                this.value = '';
                return;
            }

            selectedFiles = files;
            updatePreview();
        });

        function updatePreview() {
            previewContainer.innerHTML = '';
            
            if (selectedFiles.length > 0) {
                previewContainer.classList.remove('hidden');
                imageCounter.classList.remove('hidden');
                imageCounter.textContent = selectedFiles.length;
                
                selectedFiles.forEach((file, index) => {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'preview-item';
                        previewItem.innerHTML = `
                            <img src="${e.target.result}" alt="Preview">
                            <button type="button" class="remove-image" data-index="${index}">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                        previewContainer.appendChild(previewItem);
                    };
                    
                    reader.readAsDataURL(file);
                });

                uploadAreaAdicionais.classList.remove('hover:border-amber-400', 'hover:bg-amber-50');
                uploadAreaAdicionais.classList.add('border-blue-300', 'bg-blue-50');
            } else {
                previewContainer.classList.add('hidden');
                imageCounter.classList.add('hidden');
                uploadAreaAdicionais.classList.remove('border-blue-300', 'bg-blue-50');
            }
        }

        // Remover imagem do preview
        previewContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-image')) {
                const index = parseInt(e.target.closest('.remove-image').getAttribute('data-index'));
                selectedFiles.splice(index, 1);
                
                // Atualiza o input file
                const dataTransfer = new DataTransfer();
                selectedFiles.forEach(file => dataTransfer.items.add(file));
                fileInputAdicionais.files = dataTransfer.files;
                
                updatePreview();
            }
        });

        // Reset do estilo se o usuário cancelar
        fileInputPrincipal.addEventListener('click', function() {
            uploadAreaPrincipal.classList.add('hover:border-amber-400', 'hover:bg-amber-50');
            uploadAreaPrincipal.classList.remove('border-green-300', 'bg-green-50');
        });

        fileInputAdicionais.addEventListener('click', function() {
            uploadAreaAdicionais.classList.add('hover:border-amber-400', 'hover:bg-amber-50');
            uploadAreaAdicionais.classList.remove('border-blue-300', 'bg-blue-50');
        });
    });
  </script>

</body>
</html>