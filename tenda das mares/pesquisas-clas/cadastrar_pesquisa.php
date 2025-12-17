<?php
session_start();
$conn = new mysqli("localhost", "root", "", "tenda");

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Verifica se é admin
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Se o formulário for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $conteudo = $_POST['conteudo'];
    $data = $_POST['data'];
    $usuario_id = $_SESSION['usuario_id'];

    $caminho = null;

    // Upload da imagem
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
        $nomeImg = uniqid() . "-" . basename($_FILES['imagem']['name']);
        $caminho_destino = "../pesquisas-clas/uploads/" . $nomeImg;
        
        // Cria a pasta se não existir
        if (!is_dir('../pesquisas-clas/uploads/')) {
            mkdir('../pesquisas-clas/uploads/', 0777, true);
        }
        
        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho_destino)) {
            $caminho = "pesquisas-clas/uploads/" . $nomeImg;
        }
    }

    $sql = "INSERT INTO pesquisas (usuario_id, nome, conteudo, data, imagem) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $usuario_id, $nome, $conteudo, $data, $caminho);

    if ($stmt->execute()) {
        header("Location: ../pesquisas.php?msg=sucesso");
        exit();
    } else {
        echo "Erro: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Pesquisa - Tenda das Marés</title>
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
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .preview-item {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            height: 120px;
            border: 2px solid #e5e7eb;
        }
        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .character-count {
            font-size: 0.75rem;
            color: #6b7280;
        }
        .character-count.warning {
            color: #d97706;
        }
        .character-count.error {
            color: #dc2626;
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
                    <a href="../pesquisas.php" class="hover:text-[#b85e2b] font-semibold border-b-2 border-[#b85e2b] pb-1">Pesquisas</a>
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
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Cadastrar Nova Pesquisa</h1>
            <p class="text-xl opacity-90 max-w-3xl mx-auto">
                Compartilhe seus conhecimentos e estudos espirituais com a comunidade
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
                            <i class="fas fa-book text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold">Nova Pesquisa</h2>
                            <p class="text-amber-100 text-sm">Preencha os dados da sua pesquisa abaixo</p>
                        </div>
                    </div>
                </div>

                <!-- Formulário -->
                <form method="POST" enctype="multipart/form-data" class="p-8 space-y-8">
                    
                    <!-- Informações Básicas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Campo Nome -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                <i class="fas fa-heading text-amber-500 mr-2"></i>
                                Título da Pesquisa *
                            </label>
                            <input type="text" name="nome" required 
                                   class="w-full border border-gray-300 rounded-xl p-4 input-focus transition-all duration-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                   placeholder="Ex: Estudo sobre Simbolismo nas Velas Ritualísticas"
                                   maxlength="200">
                            <div class="character-count text-right" id="nomeCount">0/200</div>
                        </div>

                        <!-- Campo Data -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                <i class="fas fa-calendar text-amber-500 mr-2"></i>
                                Data da Pesquisa *
                            </label>
                            <input type="date" name="data" required 
                                   value="<?= date('Y-m-d') ?>"
                                   class="w-full border border-gray-300 rounded-xl p-4 input-focus transition-all duration-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        </div>
                    </div>

                    <!-- Campo Conteúdo -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-align-left text-amber-500 mr-2"></i>
                            Conteúdo da Pesquisa *
                        </label>
                        <textarea name="conteudo" required rows="8"
                                  class="w-full border border-gray-300 rounded-xl p-4 input-focus transition-all duration-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 resize-vertical"
                                  placeholder="Descreva sua pesquisa, metodologia, resultados e conclusões..."
                                  maxlength="5000"
                                  id="conteudoTextarea"></textarea>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">
                                <i class="fas fa-info-circle text-amber-500 mr-1"></i>
                                Use parágrafos claros e organizados
                            </span>
                            <div class="character-count" id="conteudoCount">0/5000</div>
                        </div>
                    </div>

                    <!-- Seção de Imagem -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2">
                            <i class="fas fa-image text-amber-500 mr-2"></i>
                            Imagem da Pesquisa
                        </h3>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                Imagem de Capa
                                <span class="text-gray-500 ml-2 text-xs">(Opcional - PNG, JPG, JPEG até 5MB)</span>
                            </label>
                            <div class="file-upload">
                                <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center transition-all duration-200 hover:border-amber-400 hover:bg-amber-50" id="uploadArea">
                                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-3"></i>
                                    <p class="text-gray-600 font-medium">Clique para selecionar uma imagem</p>
                                    <p class="text-gray-500 text-sm mt-1">Recomendado: 800x400px</p>
                                    <p class="text-gray-400 text-xs mt-2">PNG, JPG, JPEG até 5MB</p>
                                    <input type="file" name="imagem" accept="image/*" class="cursor-pointer">
                                </div>
                            </div>
                            
                            <!-- Preview da Imagem -->
                            <div class="preview-container hidden" id="previewContainer"></div>
                        </div>
                    </div>

                    <!-- Botões -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200">
                        <button type="submit" 
                                class="flex-1 bg-gradient-to-r from-amber-500 to-orange-500 text-white py-4 px-6 rounded-xl font-semibold hover:from-amber-600 hover:to-orange-600 transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center space-x-2">
                            <i class="fas fa-plus"></i>
                            <span>Cadastrar Pesquisa</span>
                        </button>
                        
                        <a href="../pesquisas.php" 
                           class="flex-1 bg-gradient-to-r from-gray-500 to-gray-600 text-white py-4 px-6 rounded-xl font-semibold hover:from-gray-600 hover:to-gray-700 transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center space-x-2">
                            <i class="fas fa-arrow-left"></i>
                            <span>Voltar às Pesquisas</span>
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
                            <i class="fas fa-clock"></i>
                            <span>Salvamento automático de rascunho</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-shield-alt"></i>
                            <span>Cadastro seguro</span>
                        </div>
                    </div>
                </div>

            </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Contadores de caracteres
            const nomeInput = document.querySelector('input[name="nome"]');
            const conteudoTextarea = document.getElementById('conteudoTextarea');

            function updateCharacterCount(element, countElement, maxLength) {
                const count = element.value.length;
                countElement.textContent = `${count}/${maxLength}`;
                
                if (count > maxLength * 0.9) {
                    countElement.classList.add('error');
                    countElement.classList.remove('warning');
                } else if (count > maxLength * 0.7) {
                    countElement.classList.add('warning');
                    countElement.classList.remove('error');
                } else {
                    countElement.classList.remove('warning', 'error');
                }
            }

            // Inicializar contadores
            updateCharacterCount(nomeInput, document.getElementById('nomeCount'), 200);
            updateCharacterCount(conteudoTextarea, document.getElementById('conteudoCount'), 5000);

            // Event listeners para contadores
            nomeInput.addEventListener('input', () => updateCharacterCount(nomeInput, document.getElementById('nomeCount'), 200));
            conteudoTextarea.addEventListener('input', () => updateCharacterCount(conteudoTextarea, document.getElementById('conteudoCount'), 5000);

            // Preview de imagem
            const fileInput = document.querySelector('input[name="imagem"]');
            const uploadArea = document.getElementById('uploadArea');
            const previewContainer = document.getElementById('previewContainer');

            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    const fileName = file.name;
                    const fileSize = (file.size / 1024 / 1024).toFixed(2); // MB
                    
                    // Verificar tamanho do arquivo
                    if (fileSize > 5) {
                        alert('O arquivo é muito grande. Máximo permitido: 5MB');
                        this.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        uploadArea.innerHTML = `
                            <i class="fas fa-check-circle text-green-500 text-3xl mb-3"></i>
                            <p class="text-green-600 font-medium">Imagem Selecionada</p>
                            <p class="text-green-500 text-sm mt-1">${fileName}</p>
                            <p class="text-gray-400 text-xs mt-2">${fileSize} MB - Clique para alterar</p>
                            <input type="file" name="imagem" accept="image/*" class="cursor-pointer">
                        `;
                        uploadArea.classList.remove('hover:border-amber-400', 'hover:bg-amber-50');
                        uploadArea.classList.add('border-green-300', 'bg-green-50');

                        // Mostrar preview
                        previewContainer.innerHTML = `
                            <div class="preview-item">
                                <img src="${e.target.result}" alt="Preview da imagem">
                                <div class="absolute top-2 right-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded">
                                    Preview
                                </div>
                            </div>
                        `;
                        previewContainer.classList.remove('hidden');
                    };
                    
                    reader.readAsDataURL(file);
                }
            });

            // Reset do estilo se o usuário cancelar
            fileInput.addEventListener('click', function() {
                uploadArea.classList.add('hover:border-amber-400', 'hover:bg-amber-50');
                uploadArea.classList.remove('border-green-300', 'bg-green-50');
            });

            // Auto-save simples (localStorage)
            function saveDraft() {
                const draft = {
                    nome: nomeInput.value,
                    conteudo: conteudoTextarea.value,
                    data: document.querySelector('input[name="data"]').value,
                    timestamp: new Date().getTime()
                };
                localStorage.setItem('pesquisaDraft', JSON.stringify(draft));
            }

            // Carregar rascunho se existir
            const draft = localStorage.getItem('pesquisaDraft');
            if (draft) {
                const draftData = JSON.parse(draft);
                // Só carrega se for recente (menos de 1 hora)
                if (new Date().getTime() - draftData.timestamp < 3600000) {
                    if (confirm('Encontramos um rascunho não salvo. Deseja restaurar?')) {
                        nomeInput.value = draftData.nome || '';
                        conteudoTextarea.value = draftData.conteudo || '';
                        document.querySelector('input[name="data"]').value = draftData.data || '<?= date('Y-m-d') ?>';
                        
                        // Atualizar contadores
                        updateCharacterCount(nomeInput, document.getElementById('nomeCount'), 200);
                        updateCharacterCount(conteudoTextarea, document.getElementById('conteudoCount'), 5000);
                    }
                }
            }

            // Salvar rascunho a cada 30 segundos
            setInterval(saveDraft, 30000);

            // Limpar rascunho quando o formulário for enviado com sucesso
            document.querySelector('form').addEventListener('submit', function() {
                localStorage.removeItem('pesquisaDraft');
            });
        });
    </script>

</body>
</html>