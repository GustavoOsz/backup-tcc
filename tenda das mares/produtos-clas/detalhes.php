<?php 
session_start(); 
$conn = new mysqli("localhost", "root", "", "tenda"); 

if ($conn->connect_error) { 
    die("Erro na conexão: " . $conn->connect_error); 
} 

$id = isset($_GET['id']) ? intval($_GET['id']) : 0; 
if ($id <= 0) { 
    die("Produto inválido."); 
} 

// Busca dados do produto
$stmt = $conn->prepare("SELECT id, nome, preco, imagem, descricao FROM produto WHERE id = ?"); 
$stmt->bind_param("i", $id); 
$stmt->execute(); 
$produto = $stmt->get_result()->fetch_assoc(); 

if (!$produto) { 
    die("Produto não encontrado."); 
} 

// Busca imagens adicionais
$query_imagens = "SELECT caminho_imagem, ordem FROM produto_imagens WHERE produto_id = ? ORDER BY ordem ASC";
$stmt_imagens = $conn->prepare($query_imagens);
$stmt_imagens->bind_param("i", $id);
$stmt_imagens->execute();
$imagens_adicionais = $stmt_imagens->get_result();

// Cria array com todas as imagens (principal + adicionais)
$todas_imagens = [];

// Adiciona imagem principal como primeira
if (!empty($produto['imagem'])) {
    $todas_imagens[] = [
        'caminho' => $produto['imagem'],
        'ordem' => 0,
        'principal' => true
    ];
}

// Adiciona imagens adicionais
while ($imagem = $imagens_adicionais->fetch_assoc()) {
    $todas_imagens[] = [
        'caminho' => $imagem['caminho_imagem'],
        'ordem' => $imagem['ordem'],
        'principal' => false
    ];
}

$total_imagens = count($todas_imagens);
?> 

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($produto['nome']) ?> - Tenda das Marés</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .product-image {
            transition: all 0.3s ease;
        }
        .product-image:hover {
            transform: scale(1.02);
        }
        .thumbnail {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .thumbnail:hover, .thumbnail.active {
            border-color: #b85e2b;
            transform: scale(1.05);
        }
        .btn-primary {
            background: linear-gradient(135deg, #b85e2b, #e07a3f);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(184, 94, 43, 0.4);
        }
        .quantity-btn {
            transition: all 0.2s ease;
        }
        .quantity-btn:hover {
            background-color: #f5f1e6;
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .image-badge {
            position: absolute;
            top: 5px;
            left: 5px;
            background: #b85e2b;
            color: white;
            border-radius: 4px;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: bold;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }
        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 90%;
            text-align: center;
            animation: slideUp 0.4s ease;
        }
        @keyframes slideUp {
            from { 
                opacity: 0; 
                transform: translate(-50%, -40%); 
            }
            to { 
                opacity: 1; 
                transform: translate(-50%, -50%); 
            }
        }
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background: #f7b95e;
            border-radius: 50%;
            animation: confettiFall 3s ease-in-out infinite;
        }
        @keyframes confettiFall {
            0% { 
                transform: translateY(-100px) rotate(0deg); 
                opacity: 1;
            }
            100% { 
                transform: translateY(500px) rotate(360deg); 
                opacity: 0;
            }
        }
    </style>
</head>
<body class="bg-gray-50 text-[#4f2905] font-sans">

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
                    <a href="../login.php" class="p-2 hover:bg-[#f5f1e6] rounded-full transition-colors">
                        <i class="fas fa-user text-[#4f2905] text-lg"></i>
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-6 py-4">
            <nav class="flex space-x-2 text-sm text-gray-600">
                <a href="../index.php" class="hover:text-[#b85e2b] transition-colors">
                    <i class="fas fa-home mr-1"></i>Início
                </a>
                <span class="text-gray-400">/</span>
                <a href="../produtos.php" class="hover:text-[#b85e2b] transition-colors">Produtos</a>
                <span class="text-gray-400">/</span>
                <span class="text-[#b85e2b] font-medium"><?= htmlspecialchars($produto['nome']) ?></span>
            </nav>
        </div>
    </div>

    <!-- Detalhes do Produto -->
    <main class="container mx-auto px-6 py-8 fade-in">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 max-w-6xl mx-auto">
            
            <!-- Galeria de Imagens -->
            <div class="space-y-4">
                <!-- Imagem Principal -->
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-[#f5f1e6]">
                    <div class="relative">
                        <img src="<?= '../' . htmlspecialchars($todas_imagens[0]['caminho']) ?>" 
                             alt="<?= htmlspecialchars($produto['nome']) ?>" 
                             id="mainImage"
                             class="product-image w-full h-96 object-contain rounded-xl bg-gray-100">
                        <?php if ($total_imagens > 1): ?>
                            <div class="absolute top-4 right-4 bg-amber-500 text-white px-3 py-1 rounded-full text-sm font-semibold shadow-lg">
                                <i class="fas fa-camera mr-1"></i><?= $total_imagens ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Miniaturas -->
                <?php if ($total_imagens > 1): ?>
                    <div class="flex gap-4 overflow-x-auto pb-2">
                        <?php foreach ($todas_imagens as $index => $imagem): ?>
                            <div class="thumbnail w-20 h-20 bg-white rounded-lg shadow-sm flex-shrink-0 flex items-center justify-center border-2 border-transparent cursor-pointer relative"
                                 onclick="changeImage(<?= $index ?>, this)"
                                 data-image="<?= '../' . htmlspecialchars($imagem['caminho']) ?>">
                                <img src="<?= '../' . htmlspecialchars($imagem['caminho']) ?>" 
                                     alt="Imagem <?= $index + 1 ?>"
                                     class="w-full h-full object-cover rounded-lg">
                                <?php if ($imagem['principal']): ?>
                                    <div class="image-badge">Principal</div>
                                <?php endif; ?>
                                
                                <!-- Indicador de imagem ativa -->
                                <div class="absolute inset-0 border-2 border-transparent rounded-lg transition-all duration-200" 
                                     id="thumbIndicator<?= $index ?>"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <!-- Mostrar placeholder se não houver imagens adicionais -->
                    <div class="flex gap-4 overflow-x-auto pb-2">
                        <div class="thumbnail w-20 h-20 bg-[#f5f1e6] rounded-lg shadow-sm flex-shrink-0 flex items-center justify-center border-2 border-[#b85e2b] cursor-pointer relative">
                            <i class="fas fa-image text-[#b85e2b] text-lg"></i>
                            <div class="image-badge">Principal</div>
                        </div>
                        <?php for ($i = 1; $i < 4; $i++): ?>
                            <div class="thumbnail w-20 h-20 bg-[#f5f1e6] rounded-lg shadow-sm flex-shrink-0 flex items-center justify-center border-2 border-transparent cursor-pointer opacity-50">
                                <i class="fas fa-plus text-gray-400 text-lg"></i>
                            </div>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>

                <!-- Contador de Imagens -->
                <div class="text-center">
                    <p class="text-sm text-gray-500">
                        <?php if ($total_imagens > 1): ?>
                            <i class="fas fa-images text-amber-500 mr-1"></i>
                            <?= $total_imagens ?> imagem<?= $total_imagens > 1 ? 'ens' : '' ?> disponível<?= $total_imagens > 1 ? 'eis' : '' ?>
                        <?php else: ?>
                            <i class="fas fa-image text-amber-500 mr-1"></i>
                            1 imagem disponível
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <!-- Informações do Produto -->
            <div class="space-y-6">
                <div class="bg-white rounded-2xl shadow-lg p-8 border border-[#f5f1e6]">
                    <!-- Cabeçalho -->
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($produto['nome']) ?></h1>
                            <div class="flex items-center gap-2 mb-4">
                                <div class="flex text-yellow-400">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <i class="fas fa-star text-sm"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="text-sm text-gray-500">(12 avaliações)</span>
                            </div>
                        </div>
                        
                        <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
                            <div class="flex gap-2">
                                <button onclick="showDevelopmentMessage()"
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2 text-sm cursor-pointer">
                                    <i class="fas fa-edit"></i>
                                    Editar
                                </button>
                                <form method="POST" action="excluir_produto.php" 
                                      onsubmit="return confirm('Tem certeza que deseja excluir este produto?');">
                                    <input type="hidden" name="id" value="<?= $produto['id'] ?>">
                                    <button type="submit" 
                                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2 text-sm">
                                        <i class="fas fa-trash"></i>
                                        Excluir
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Preço -->
                    <div class="mb-6">
                        <p class="text-4xl font-bold text-[#b85e2b] mb-2">
                            R$ <?= number_format($produto['preco'], 2, ',', '.') ?>
                        </p>
                        <p class="text-sm text-gray-500">Em até 12x sem juros no cartão</p>
                    </div>

                    <!-- Formulário de Compra -->
                    <form method="POST" action="carrinho.php" class="space-y-6">
                        <input type="hidden" name="id" value="<?= $produto['id']; ?>">
                        <input type="hidden" name="nome" value="<?= htmlspecialchars($produto['nome']); ?>">
                        <input type="hidden" name="preco" value="<?= $produto['preco']; ?>">
                        <input type="hidden" name="imagem" value="<?= htmlspecialchars($produto['imagem']); ?>">

                        <!-- Quantidade -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Quantidade:</label>
                            <div class="flex items-center gap-3">
                                <button type="button" onclick="decreaseQuantity()" 
                                        class="quantity-btn w-10 h-10 border border-gray-300 rounded-lg flex items-center justify-center text-gray-600 hover:border-[#b85e2b]">
                                    <i class="fas fa-minus text-sm"></i>
                                </button>
                                <input type="number" name="quantidade" id="quantidade" value="1" min="1" 
                                       class="w-20 text-center border border-gray-300 rounded-lg py-2 px-3 focus:border-[#b85e2b] focus:ring-2 focus:ring-[#b85e2b] focus:ring-opacity-20">
                                <button type="button" onclick="increaseQuantity()" 
                                        class="quantity-btn w-10 h-10 border border-gray-300 rounded-lg flex items-center justify-center text-gray-600 hover:border-[#b85e2b]">
                                    <i class="fas fa-plus text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Botões de Ação -->
                        <div class="space-y-3">
                            <button type="submit" 
                                    class="btn-primary w-full text-white py-4 rounded-xl font-bold text-lg transition-all flex items-center justify-center gap-3">
                                <i class="fas fa-shopping-cart"></i>
                                Adicionar ao Carrinho
                            </button>
                            
                            <a href="http://wa.me/5541999369485?text=Olá! Gostaria de mais informações sobre: <?= urlencode($produto['nome']) ?>" 
                               target="_blank"
                               class="bg-green-500 hover:bg-green-600 text-white py-4 rounded-xl font-bold text-lg transition-all flex items-center justify-center gap-3 w-full">
                                <i class="fab fa-whatsapp"></i>
                                Comprar pelo WhatsApp
                            </a>
                        </div>
                    </form>

                    <!-- Informações de Entrega -->
                    <div class="mt-6 p-4 bg-blue-50 rounded-xl border border-blue-200">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-shipping-fast text-blue-500 text-lg"></i>
                            <div>
                                <p class="text-blue-700 font-medium text-sm">Entrega rápida</p>
                                <p class="text-blue-600 text-xs">Receba em até 7 dias úteis • Grátis para Curitiba</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Características -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white rounded-xl p-4 border border-[#f5f1e6] text-center">
                        <i class="fas fa-shield-alt text-[#b85e2b] text-xl mb-2"></i>
                        <p class="text-sm font-medium">Compra Segura</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 border border-[#f5f1e6] text-center">
                        <i class="fas fa-credit-card text-[#b85e2b] text-xl mb-2"></i>
                        <p class="text-sm font-medium">Parcele em 12x</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Descrição Detalhada -->
        <div class="max-w-6xl mx-auto mt-12">
            <div class="bg-white rounded-2xl shadow-lg p-8 border border-[#f5f1e6]">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                    <i class="fas fa-info-circle text-[#b85e2b]"></i>
                    Descrição do Produto
                </h2>
                <div class="prose max-w-none text-gray-700 leading-relaxed">
                    <p class="text-lg">
                        <?= !empty($produto['descricao']) 
                            ? nl2br(htmlspecialchars($produto['descricao'])) 
                            : "Este produto artesanal foi cuidadosamente selecionado para trazer energia positiva e significado espiritual ao seu espaço. Feito com materiais de qualidade e atenção aos detalhes, cada peça carrega a essência da tradição e da fé." ?>
                    </p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div class="space-y-2">
                            <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                                <i class="fas fa-check text-green-500"></i>
                                Características
                            </h3>
                            <ul class="text-gray-600 space-y-1">
                                <li>• Material: Artesanal natural</li>
                                <li>• Acabamento: Detalhado à mão</li>
                                <li>• Origem: Produção local</li>
                                <li>• Embalagem: Especial para presente</li>
                            </ul>
                        </div>
                        
                        <div class="space-y-2">
                            <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                                <i class="fas fa-gift text-[#b85e2b]"></i>
                                Ideal Para
                            </h3>
                            <ul class="text-gray-600 space-y-1">
                                <li>• Presentes especiais</li>
                                <li>• Altar espiritual</li>
                                <li>• Decoração sagrada</li>
                                <li>• Coleção pessoal</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Galeria Expandida (se houver múltiplas imagens) -->
                    <?php if ($total_imagens > 1): ?>
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-images text-[#b85e2b]"></i>
                            Galeria de Imagens
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <?php foreach ($todas_imagens as $index => $imagem): ?>
                                <div class="bg-gray-100 rounded-lg overflow-hidden cursor-pointer transition-all duration-200 hover:shadow-lg hover:transform hover:scale-105"
                                     onclick="changeImage(<?= $index ?>)">
                                    <img src="<?= '../' . htmlspecialchars($imagem['caminho']) ?>" 
                                         alt="Imagem <?= $index + 1 ?>"
                                         class="w-full h-32 object-cover">
                                    <?php if ($imagem['principal']): ?>
                                        <div class="bg-amber-500 text-white text-xs px-2 py-1 text-center">
                                            Principal
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal de Desenvolvimento -->
    <div id="developmentModal" class="modal">
        <div class="modal-content">
            <div class="relative">
                <!-- Confetti -->
                <div id="confettiContainer"></div>
                
                <!-- Ícone Fofo -->
                <div class="w-20 h-20 bg-gradient-to-br from-amber-400 to-orange-500 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <i class="fas fa-hammer text-white text-2xl"></i>
                </div>
                
                <h3 class="text-2xl font-bold text-gray-800 mb-3">Oops! 🛠️</h3>
                
                <p class="text-gray-600 mb-2 leading-relaxed">
                    Nossos duendes estão trabalhando duro nesta funcionalidade!
                </p>
                
                <p class="text-gray-500 text-sm mb-6">
                    A tela de edição estará disponível em breve com muitas novidades fofas!
                </p>

                <div class="flex items-center justify-center gap-2 text-amber-500 mb-4">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>

                <button onclick="closeDevelopmentMessage()" 
                        class="w-full bg-gradient-to-r from-amber-500 to-orange-500 text-white py-3 rounded-xl font-semibold hover:from-amber-600 hover:to-orange-600 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <i class="fas fa-heart mr-2"></i>
                    Entendi, obrigado!
                </button>

                <p class="text-xs text-gray-400 mt-4">
                    💡 Dica: Enquanto isso, você pode excluir e recriar o produto se precisar fazer alterações.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Controle de quantidade
        function increaseQuantity() {
            const input = document.getElementById('quantidade');
            input.value = parseInt(input.value) + 1;
        }

        function decreaseQuantity() {
            const input = document.getElementById('quantidade');
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        }

        // Trocar imagem principal
        function changeImage(index, element = null) {
            const mainImage = document.getElementById('mainImage');
            const todasImagens = <?= json_encode($todas_imagens) ?>;
            
            if (todasImagens[index]) {
                mainImage.src = '../' + todasImagens[index].caminho;
                
                // Atualizar miniaturas ativas
                document.querySelectorAll('.thumbnail').forEach((thumb, i) => {
                    const indicator = document.getElementById('thumbIndicator' + i);
                    if (indicator) {
                        if (i === index) {
                            indicator.classList.add('border-[#b85e2b]');
                            thumb.classList.add('border-[#b85e2b]');
                        } else {
                            indicator.classList.remove('border-[#b85e2b]');
                            thumb.classList.remove('border-[#b85e2b]');
                        }
                    }
                });
            }
        }

        // Inicializar primeira miniatura como ativa
        document.addEventListener('DOMContentLoaded', function() {
            const firstThumbnail = document.querySelector('.thumbnail');
            if (firstThumbnail) {
                const firstIndicator = document.getElementById('thumbIndicator0');
                if (firstIndicator) {
                    firstIndicator.classList.add('border-[#b85e2b]');
                }
                firstThumbnail.classList.add('border-[#b85e2b]');
            }
        });

        // Validação do formulário
        document.querySelector('form').addEventListener('submit', function(e) {
            const quantity = document.getElementById('quantidade').value;
            if (quantity < 1) {
                e.preventDefault();
                alert('A quantidade deve ser pelo menos 1.');
            }
        });

        // Mostrar mensagem de desenvolvimento
        function showDevelopmentMessage() {
            const modal = document.getElementById('developmentModal');
            const confettiContainer = document.getElementById('confettiContainer');
            
            // Criar confetti
            confettiContainer.innerHTML = '';
            for (let i = 0; i < 20; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.animationDelay = Math.random() * 2 + 's';
                confetti.style.background = ['#f7b95e', '#b85e2b', '#e07a3f', '#4f2905'][Math.floor(Math.random() * 4)];
                confettiContainer.appendChild(confetti);
            }
            
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        // Fechar mensagem de desenvolvimento
        function closeDevelopmentMessage() {
            const modal = document.getElementById('developmentModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Fechar modal clicando fora
        window.onclick = function(event) {
            const modal = document.getElementById('developmentModal');
            if (event.target === modal) {
                closeDevelopmentMessage();
            }
        }
    </script>

</body>
</html>