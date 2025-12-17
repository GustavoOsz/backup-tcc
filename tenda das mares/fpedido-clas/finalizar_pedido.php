<?php
session_start();
$conn = new mysqli("localhost", "root", "", "tenda");

// Verifica se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

// Verifica se carrinho está vazio
if (empty($_SESSION['carrinho'])) {
    $_SESSION['erro'] = "Seu carrinho está vazio!";
    header('Location: ../carrinho.php');
    exit;
}

// Calcula total
$total = 0;
$totalItens = 0;
foreach ($_SESSION['carrinho'] as $item) {
    $total += $item['preco'] * $item['quantidade'];
    $totalItens += $item['quantidade'];
}

// Processa finalização do pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();
        
        // Gera número único do pedido
        $numero_pedido = 'PED' . date('YmdHis') . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        // Busca email do usuário
        $query_usuario = "SELECT email FROM usuarios WHERE id = ?";
        $stmt_user = $conn->prepare($query_usuario);
        $stmt_user->bind_param("i", $_SESSION['usuario_id']);
        $stmt_user->execute();
        $usuario = $stmt_user->get_result()->fetch_assoc();
        
        // Insere o pedido
        $query_pedido = "INSERT INTO pedidos (numero_pedido, usuario_id, usuario_email, total, status) 
                         VALUES (?, ?, ?, ?, 'pendente')";
        $stmt_pedido = $conn->prepare($query_pedido);
        $stmt_pedido->bind_param("sisd", $numero_pedido, $_SESSION['usuario_id'], $usuario['email'], $total);
        $stmt_pedido->execute();
        $pedido_id = $conn->insert_id;
        
        // Insere os itens do pedido
        $query_item = "INSERT INTO pedido_itens (pedido_id, produto_id, produto_nome, quantidade, preco_unitario, subtotal) 
                       VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_item = $conn->prepare($query_item);
        
        foreach ($_SESSION['carrinho'] as $item) {
            $subtotal = $item['preco'] * $item['quantidade'];
            $stmt_item->bind_param("iisidd", $pedido_id, $item['id'], $item['nome'], 
                                 $item['quantidade'], $item['preco'], $subtotal);
            $stmt_item->execute();
        }
        
        // Limpa o carrinho da sessão
        $_SESSION['carrinho'] = [];
        
        $conn->commit();
        
        $_SESSION['sucesso_pedido'] = "Pedido #$numero_pedido finalizado com sucesso!";
        header("Location: comprovante.php?pedido=$pedido_id");
        exit;
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['erro'] = "Erro ao finalizar pedido: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Finalizar Pedido - Tenda das Marés</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .checkout-card {
            transition: all 0.3s ease;
        }
        .checkout-card:hover {
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
        .step-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #f5f1e6;
            color: #4f2905;
            font-weight: bold;
            margin-right: 8px;
        }
        .step-indicator.active {
            background: linear-gradient(135deg, #b85e2b, #e07a3f);
            color: white;
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
                <a href="../produtos-clas/carrinho.php" class="hover:text-[#b85e2b] transition-colors">Carrinho</a>
                <span class="text-gray-400">/</span>
                <span class="text-[#b85e2b] font-medium">Finalizar Pedido</span>
            </nav>
        </div>
    </div>

    <!-- Conteúdo Principal -->
    <main class="container mx-auto px-6 py-8">
        <div class="max-w-6xl mx-auto">
            
            <!-- Cabeçalho e Progresso -->
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">Finalizar Pedido</h1>
                
                <!-- Indicador de Progresso -->
                <div class="flex justify-center items-center mb-8">
                    <div class="flex items-center">
                        <div class="step-indicator active">
                            <i class="fas fa-shopping-cart text-sm"></i>
                        </div>
                        <span class="text-sm font-medium text-[#b85e2b]">Carrinho</span>
                    </div>
                    
                    <div class="w-16 h-0.5 bg-[#b85e2b] mx-4"></div>
                    
                    <div class="flex items-center">
                        <div class="step-indicator active">
                            <i class="fas fa-clipboard-check text-sm"></i>
                        </div>
                        <span class="text-sm font-medium text-[#b85e2b]">Revisão</span>
                    </div>
                    
                    <div class="w-16 h-0.5 bg-gray-300 mx-4"></div>
                    
                    <div class="flex items-center">
                        <div class="step-indicator">
                            <i class="fas fa-receipt text-sm"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-500">Comprovante</span>
                    </div>
                </div>
            </div>

            <?php if (isset($_SESSION['erro'])): ?>
                <div class="bg-red-50 border border-red-200 rounded-xl p-6 mb-8">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-red-500"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-red-800">Erro no Processamento</h3>
                            <p class="text-red-700"><?= $_SESSION['erro']; unset($_SESSION['erro']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="grid lg:grid-cols-3 gap-8">
                
                <!-- Resumo do Pedido -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Itens do Pedido -->
                    <div class="checkout-card bg-white rounded-2xl shadow-lg p-8 border border-[#f5f1e6]">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-gradient-to-br from-[#b85e2b] to-[#f7b95e] rounded-full flex items-center justify-center">
                                <i class="fas fa-boxes text-white text-lg"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900">Itens do Pedido</h2>
                        </div>
                        
                        <div class="space-y-4">
                            <?php foreach ($_SESSION['carrinho'] as $item): 
                                $subtotal = $item['preco'] * $item['quantidade'];
                            ?>
                                <div class="flex items-center gap-4 p-4 bg-[#fef7ed] rounded-xl border border-[#fde9c7]">
                                    <img src="../<?= htmlspecialchars($item['imagem']) ?>" 
                                         alt="<?= htmlspecialchars($item['nome']) ?>" 
                                         class="w-16 h-16 object-cover rounded-lg bg-gray-100">
                                    
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-gray-900 text-lg"><?= htmlspecialchars($item['nome']) ?></h3>
                                        <div class="flex items-center gap-4 mt-2 text-sm text-gray-600">
                                            <span>Quantidade: <strong><?= $item['quantidade'] ?></strong></span>
                                            <span>Preço unitário: <strong>R$ <?= number_format($item['preco'], 2, ',', '.') ?></strong></span>
                                        </div>
                                    </div>
                                    
                                    <div class="text-right">
                                        <p class="text-lg font-bold text-[#b85e2b]">
                                            R$ <?= number_format($subtotal, 2, ',', '.') ?>
                                        </p>
                                        <p class="text-sm text-gray-500">Subtotal</p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Informações de Entrega -->
                    <div class="checkout-card bg-white rounded-2xl shadow-lg p-8 border border-[#f5f1e6]">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-gradient-to-br from-[#b85e2b] to-[#f7b95e] rounded-full flex items-center justify-center">
                                <i class="fas fa-truck text-white text-lg"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900">Entrega</h2>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
                                <div class="flex items-center gap-3 mb-3">
                                    <i class="fas fa-shipping-fast text-blue-500 text-xl"></i>
                                    <h3 class="font-semibold text-blue-900">Entrega Padrão</h3>
                                </div>
                                <p class="text-blue-700 text-sm">
                                    <strong>Prazo:</strong> 5-7 dias úteis<br>
                                    <strong>Valor:</strong> Grátis para Curitiba
                                </p>
                            </div>
                            
                            <div class="bg-green-50 rounded-xl p-4 border border-green-200">
                                <div class="flex items-center gap-3 mb-3">
                                    <i class="fas fa-bolt text-green-500 text-xl"></i>
                                    <h3 class="font-semibold text-green-900">Entrega Expressa</h3>
                                </div>
                                <p class="text-green-700 text-sm">
                                    <strong>Prazo:</strong> 2-3 dias úteis<br>
                                    <strong>Valor:</strong> Consulte disponibilidade
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumo Final -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-lg p-8 border border-[#f5f1e6] sticky top-32">
                        <h3 class="text-xl font-bold text-gray-900 mb-6">Resumo do Pedido</h3>
                        
                        <div class="space-y-4 mb-6">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal (<?= $totalItens ?> itens)</span>
                                <span>R$ <?= number_format($total, 2, ',', '.') ?></span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Frete</span>
                                <span class="text-green-600 font-semibold">Grátis</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-500">
                                <span>Descontos</span>
                                <span>R$ 0,00</span>
                            </div>
                            <div class="border-t pt-4">
                                <div class="flex justify-between text-xl font-bold text-gray-900">
                                    <span>Total</span>
                                    <span class="text-[#b85e2b]">R$ <?= number_format($total, 2, ',', '.') ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Formulário de Finalização -->
                        <form method="POST" id="checkoutForm">
                            <button type="submit" 
                                    class="btn-primary w-full text-white py-4 rounded-xl font-bold text-lg transition-all flex items-center justify-center gap-3 mb-4">
                                <i class="fas fa-credit-card"></i>
                                Finalizar Pedido
                            </button>
                            
                            <a href="../carrinho.php" 
                               class="border-2 border-[#b85e2b] text-[#b85e2b] hover:bg-[#b85e2b] hover:text-white py-3 rounded-xl font-bold transition-all flex items-center justify-center gap-3 w-full text-center">
                                <i class="fas fa-arrow-left"></i>
                                Voltar ao Carrinho
                            </a>
                        </form>

                        <!-- Informações de Segurança -->
                        <div class="mt-6 space-y-3 text-sm text-gray-500">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-shield-alt text-green-500"></i>
                                <span>Compra 100% segura</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-lock text-blue-500"></i>
                                <span>Dados protegidos</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-headset text-purple-500"></i>
                                <span>Suporte 24/7</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Confirmação de finalização
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            if (!confirm('Tem certeza que deseja finalizar o pedido?')) {
                e.preventDefault();
            }
        });

        // Animação de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.checkout-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('animate-fade-in');
            });
        });
    </script>

</body>
</html>