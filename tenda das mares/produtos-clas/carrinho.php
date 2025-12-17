<?php
session_start();

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Remover quantidade específica
if (isset($_GET['remover_quantidade']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $qtdRemover = (int)$_GET['remover_quantidade'];

    if (isset($_SESSION['carrinho'][$id])) {
        $_SESSION['carrinho'][$id]['quantidade'] -= $qtdRemover;
        if ($_SESSION['carrinho'][$id]['quantidade'] <= 0) {
            unset($_SESSION['carrinho'][$id]);
        }
    }
    header("Location: carrinho.php");
    exit;
}

// Adicionar itens ao carrinho
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $preco = $_POST['preco'];
    $imagem = $_POST['imagem'];
    $quantidade = $_POST['quantidade'];

    if (isset($_SESSION['carrinho'][$id])) {
        $_SESSION['carrinho'][$id]['quantidade'] += $quantidade;
    } else {
        $_SESSION['carrinho'][$id] = [
            'id' => $id,
            'nome' => $nome,
            'preco' => $preco,
            'imagem' => $imagem,
            'quantidade' => $quantidade
        ];
    }
}

$total = 0;
$totalItens = 0;
foreach ($_SESSION['carrinho'] as $item) {
    $total += $item['preco'] * $item['quantidade'];
    $totalItens += $item['quantidade'];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Carrinho - Tenda das Marés</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
    .cart-item {
        transition: all 0.3s ease;
    }
    .cart-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
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
    .empty-cart {
        animation: fadeIn 0.5s ease-in;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
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
                <span class="text-[#b85e2b] font-medium">Carrinho</span>
            </nav>
        </div>
    </div>

    <!-- Conteúdo Principal -->
    <main class="container mx-auto px-6 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Cabeçalho do Carrinho -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Seu Carrinho</h1>
                    <p class="text-gray-600">
                        <?php if ($totalItens > 0): ?>
                            <span class="font-semibold text-[#b85e2b]"><?= $totalItens ?></span> 
                            <?= $totalItens === 1 ? 'item' : 'itens' ?> no carrinho
                        <?php else: ?>
                            Seu carrinho está esperando por produtos especiais
                        <?php endif; ?>
                    </p>
                </div>
                
                <?php if ($totalItens > 0): ?>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-[#b85e2b]">
                            R$ <?= number_format($total, 2, ',', '.') ?>
                        </p>
                        <p class="text-sm text-gray-500">Total parcial</p>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (empty($_SESSION['carrinho'])): ?>
                <!-- Carrinho Vazio -->
                <div class="empty-cart bg-white rounded-2xl shadow-lg p-12 text-center border border-[#f5f1e6]">
                    <div class="w-24 h-24 bg-gradient-to-br from-[#f5f1e6] to-[#fde9c7] rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-shopping-cart text-3xl text-[#b85e2b]"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-700 mb-4">Seu carrinho está vazio</h2>
                    <p class="text-gray-500 mb-8 max-w-md mx-auto">
                        Explore nossos produtos especiais e encontre itens que vão trazer energia positiva para seu espaço.
                    </p>
                    <a href="../produtos.php" 
                       class="btn-primary text-white px-8 py-4 rounded-xl font-bold text-lg inline-flex items-center gap-3 transition-all">
                        <i class="fas fa-shopping-bag"></i>
                        Continuar Comprando
                    </a>
                </div>
            <?php else: ?>
                <!-- Itens do Carrinho -->
                <div class="grid lg:grid-cols-3 gap-8">
                    <!-- Lista de Itens -->
                    <div class="lg:col-span-2 space-y-4">
                        <?php foreach ($_SESSION['carrinho'] as $item): 
                            $subtotal = $item['preco'] * $item['quantidade'];
                        ?>
                            <div class="cart-item bg-white rounded-2xl shadow-lg p-6 border border-[#f5f1e6]">
                                <div class="flex gap-4">
                                    <!-- Imagem do Produto -->
                                    <div class="flex-shrink-0">
                                        <img src="../<?= $item['imagem'] ?>" 
                                             alt="<?= htmlspecialchars($item['nome']) ?>" 
                                             class="w-20 h-20 md:w-24 md:h-24 object-cover rounded-xl bg-gray-100">
                                    </div>
                                    
                                    <!-- Informações do Produto -->
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                            <?= htmlspecialchars($item['nome']) ?>
                                        </h3>
                                        <p class="text-[#b85e2b] font-bold text-lg mb-2">
                                            R$ <?= number_format($item['preco'], 2, ',', '.') ?>
                                        </p>
                                        
                                        <!-- Controles de Quantidade -->
                                        <div class="flex items-center gap-4">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm text-gray-600 font-medium">Quantidade:</span>
                                                <div class="flex items-center gap-2">
                                                    <form method="get" action="carrinho.php" class="flex items-center gap-2">
                                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                                        <input type="hidden" name="remover_quantidade" value="1">
                                                        <button type="submit" 
                                                                class="quantity-btn w-8 h-8 border border-gray-300 rounded-lg flex items-center justify-center text-gray-600 hover:border-red-500 hover:text-red-500 transition-colors">
                                                            <i class="fas fa-minus text-xs"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <span class="w-12 text-center font-semibold text-gray-900">
                                                        <?= $item['quantidade'] ?>
                                                    </span>
                                                    
                                                    <form method="post" action="carrinho.php" class="flex items-center gap-2">
                                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                                        <input type="hidden" name="nome" value="<?= htmlspecialchars($item['nome']) ?>">
                                                        <input type="hidden" name="preco" value="<?= $item['preco'] ?>">
                                                        <input type="hidden" name="imagem" value="<?= $item['imagem'] ?>">
                                                        <input type="hidden" name="quantidade" value="1">
                                                        <button type="submit" 
                                                                class="quantity-btn w-8 h-8 border border-gray-300 rounded-lg flex items-center justify-center text-gray-600 hover:border-green-500 hover:text-green-500 transition-colors">
                                                            <i class="fas fa-plus text-xs"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                            
                                            <!-- Remover Item -->
                                            <form method="get" action="carrinho.php" class="ml-auto">
                                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                                <input type="hidden" name="remover_quantidade" value="<?= $item['quantidade'] ?>">
                                                <button type="submit" 
                                                        class="text-red-500 hover:text-red-700 transition-colors flex items-center gap-2 text-sm font-medium">
                                                    <i class="fas fa-trash"></i>
                                                    Remover
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <!-- Subtotal -->
                                    <div class="text-right">
                                        <p class="text-lg font-bold text-gray-900">
                                            R$ <?= number_format($subtotal, 2, ',', '.') ?>
                                        </p>
                                        <p class="text-sm text-gray-500">Subtotal</p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Resumo do Pedido -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-2xl shadow-lg p-6 border border-[#f5f1e6] sticky top-32">
                            <h3 class="text-xl font-bold text-gray-900 mb-4">Resumo do Pedido</h3>
                            
                            <div class="space-y-3 mb-6">
                                <div class="flex justify-between text-gray-600">
                                    <span>Subtotal (<?= $totalItens ?> itens)</span>
                                    <span>R$ <?= number_format($total, 2, ',', '.') ?></span>
                                </div>
                                <div class="flex justify-between text-gray-600">
                                    <span>Frete</span>
                                    <span class="text-green-600">Grátis</span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-500">
                                    <span>Descontos</span>
                                    <span>R$ 0,00</span>
                                </div>
                                <div class="border-t pt-3">
                                    <div class="flex justify-between text-lg font-bold text-gray-900">
                                        <span>Total</span>
                                        <span>R$ <?= number_format($total, 2, ',', '.') ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Botões de Ação -->
                            <div class="space-y-3">
                                <a href="../fpedido-clas/finalizar_pedido.php" 
                                   class="btn-primary w-full text-white py-4 rounded-xl font-bold text-lg transition-all flex items-center justify-center gap-3">
                                    <i class="fas fa-credit-card"></i>
                                    Finalizar Compra
                                </a>
                                
                                <a href="http://wa.me/5541999369485?text=Olá! Gostaria de finalizar meu pedido com os seguintes itens: <?= urlencode(implode(', ', array_column($_SESSION['carrinho'], 'nome'))) ?>" 
                                   target="_blank"
                                   class="bg-green-500 hover:bg-green-600 text-white py-4 rounded-xl font-bold text-lg transition-all flex items-center justify-center gap-3 w-full">
                                    <i class="fab fa-whatsapp"></i>
                                    Comprar pelo WhatsApp
                                </a>
                                
                                <a href="../produtos.php" 
                                   class="border-2 border-[#b85e2b] text-[#b85e2b] hover:bg-[#b85e2b] hover:text-white py-3 rounded-xl font-bold transition-all flex items-center justify-center gap-3 w-full">
                                    <i class="fas fa-plus"></i>
                                    Continuar Comprando
                                </a>
                            </div>

                            <!-- Informações Adicionais -->
                            <div class="mt-6 space-y-3 text-sm text-gray-500">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-shield-alt text-green-500"></i>
                                    <span>Compra 100% segura</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-truck text-blue-500"></i>
                                    <span>Entrega para todo Brasil</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-credit-card text-purple-500"></i>
                                    <span>Parcele em até 12x</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Animação para itens do carrinho
        document.addEventListener('DOMContentLoaded', function() {
            const cartItems = document.querySelectorAll('.cart-item');
            cartItems.forEach((item, index) => {
                item.style.animationDelay = `${index * 0.1}s`;
                item.classList.add('animate-fade-in');
            });
        });

        // Confirmação para remover item
        document.querySelectorAll('form[action="carrinho.php"]').forEach(form => {
            const removeBtn = form.querySelector('button[type="submit"]');
            if (removeBtn && removeBtn.innerHTML.includes('fa-trash')) {
                form.addEventListener('submit', function(e) {
                    if (!confirm('Tem certeza que deseja remover este item do carrinho?')) {
                        e.preventDefault();
                    }
                });
            }
        });
    </script>

</body>
</html>