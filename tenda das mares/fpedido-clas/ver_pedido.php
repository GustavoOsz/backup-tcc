<?php
session_start();
$conn = new mysqli("localhost", "root", "", "tenda");

// Verifica se é admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Pega o ID do pedido
if (!isset($_GET['id'])) {
    header('Location: painel_admin.php');
    exit;
}

$pedido_id = intval($_GET['id']);

// Busca dados do pedido
$query_pedido = "SELECT p.*, u.email, u.nome, u.telefone, u.endereco, u.cidade, u.estado, u.cep 
                 FROM pedidos p 
                 JOIN usuarios u ON p.usuario_id = u.id 
                 WHERE p.id = ?";
$stmt_pedido = $conn->prepare($query_pedido);
$stmt_pedido->bind_param("i", $pedido_id);
$stmt_pedido->execute();
$pedido = $stmt_pedido->get_result()->fetch_assoc();

if (!$pedido) {
    header('Location: painel_admin.php');
    exit;
}

// Busca itens do pedido
$query_itens = "SELECT * FROM pedido_itens WHERE pedido_id = ?";
$stmt_itens = $conn->prepare($query_itens);
$stmt_itens->bind_param("i", $pedido_id);
$stmt_itens->execute();
$itens = $stmt_itens->get_result();

// Atualiza status do pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['status'])) {
        $novo_status = $_POST['status'];
        $query_update = "UPDATE pedidos SET status = ? WHERE id = ?";
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bind_param("si", $novo_status, $pedido_id);
        $stmt_update->execute();
        
        $_SESSION['sucesso'] = "Status do pedido atualizado para: " . $novo_status;
    }
    
    // Atualiza método de pagamento
    if (isset($_POST['metodo_pagamento'])) {
        $metodo_pagamento = $_POST['metodo_pagamento'];
        $query_update = "UPDATE pedidos SET metodo_pagamento = ? WHERE id = ?";
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bind_param("si", $metodo_pagamento, $pedido_id);
        $stmt_update->execute();
        
        $_SESSION['sucesso'] = "Método de pagamento atualizado para: " . $metodo_pagamento;
    }
    
    // Atualiza status do pagamento
    if (isset($_POST['status_pagamento'])) {
        $status_pagamento = $_POST['status_pagamento'];
        $query_update = "UPDATE pedidos SET status_pagamento = ? WHERE id = ?";
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bind_param("si", $status_pagamento, $pedido_id);
        $stmt_update->execute();
        
        $_SESSION['sucesso'] = "Status do pagamento atualizado para: " . $status_pagamento;
    }
    
    // Upload de comprovante
    if (isset($_FILES['comprovante']) && $_FILES['comprovante']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/comprovantes/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['comprovante']['name'], PATHINFO_EXTENSION);
        $file_name = 'comprovante_' . $pedido_id . '_' . time() . '.' . $file_extension;
        $file_path = $upload_dir . $file_name;
        
        // Move o arquivo
        if (move_uploaded_file($_FILES['comprovante']['tmp_name'], $file_path)) {
            // Remove comprovante antigo se existir
            if (!empty($pedido['comprovante_pagamento']) && file_exists('../' . $pedido['comprovante_pagamento'])) {
                unlink('../' . $pedido['comprovante_pagamento']);
            }
            
            $query_update = "UPDATE pedidos SET comprovante_pagamento = ? WHERE id = ?";
            $stmt_update = $conn->prepare($query_update);
            $db_path = 'uploads/comprovantes/' . $file_name;
            $stmt_update->bind_param("si", $db_path, $pedido_id);
            $stmt_update->execute();
            
            $_SESSION['sucesso'] = "Comprovante de pagamento enviado com sucesso!";
            // Atualiza o pedido com o novo caminho do comprovante
            $pedido['comprovante_pagamento'] = $db_path;
        } else {
            $_SESSION['erro'] = "Erro ao fazer upload do comprovante.";
        }
    }
    
    header("Location: ver_pedido.php?id=$pedido_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Detalhes do Pedido - Tenda das Marés</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .detail-card {
            transition: all 0.3s ease;
        }
        .detail-card:hover {
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
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
        }
        .payment-method-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        .item-card {
            transition: all 0.3s ease;
        }
        .item-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
                    <a href="painel_admin.php" class="hover:text-[#b85e2b] transition-colors">Painel Admin</a>
                    <a href="relatorios.php" class="hover:text-[#b85e2b] transition-colors">Relatórios</a>
                    <a href="pedidos_finalizados.php" class="hover:text-[#b85e2b] transition-colors">Pedidos Finalizados</a>
                    <a href="../perfil.php" class="p-2 hover:bg-[#f5f1e6] rounded-full transition-colors">
                        <i class="fas fa-user text-[#4f2905] text-lg"></i>
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="container mx-auto px-6 py-8">
        <div class="max-w-7xl mx-auto">
            
            <!-- Cabeçalho -->
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-8">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Detalhes do Pedido</h1>
                    <p class="text-gray-600">Gerencie e acompanhe os detalhes deste pedido</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="painel_admin.php" class="bg-gray-500 text-white px-6 py-3 rounded-xl font-semibold hover:bg-gray-600 transition-colors inline-flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i>
                        Voltar
                    </a>
                    <a href="pedidos_finalizados.php" class="bg-green-500 text-white px-6 py-3 rounded-xl font-semibold hover:bg-green-600 transition-colors inline-flex items-center gap-2">
                        <i class="fas fa-check-circle"></i>
                        Pedidos Finalizados
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['sucesso'])): ?>
                <div class="bg-green-50 border border-green-200 rounded-2xl p-6 mb-8">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-check text-green-500"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-green-800">Sucesso!</h3>
                            <p class="text-green-700"><?= $_SESSION['sucesso']; unset($_SESSION['sucesso']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['erro'])): ?>
                <div class="bg-red-50 border border-red-200 rounded-2xl p-6 mb-8">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-red-500"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-red-800">Erro!</h3>
                            <p class="text-red-700"><?= $_SESSION['erro']; unset($_SESSION['erro']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Informações do Pedido -->
                <div class="detail-card bg-white rounded-2xl shadow-lg p-6 border border-[#f5f1e6] lg:col-span-2">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-gradient-to-br from-[#b85e2b] to-[#f7b95e] rounded-full flex items-center justify-center">
                            <i class="fas fa-receipt text-white text-lg"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Informações do Pedido</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Nº do Pedido</label>
                                <p class="font-mono text-lg bg-gray-100 px-3 py-2 rounded-lg"><?= $pedido['numero_pedido'] ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Data do Pedido</label>
                                <p class="text-gray-900 font-medium"><?= date('d/m/Y H:i', strtotime($pedido['data_pedido'])) ?></p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Total do Pedido</label>
                                <p class="text-2xl font-bold text-green-600">R$ <?= number_format($pedido['total'], 2, ',', '.') ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Status Atual</label>
                                <?php
                                $status_colors = [
                                    'pendente' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                    'processando' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'enviado' => 'bg-purple-100 text-purple-800 border-purple-200',
                                    'entregue' => 'bg-green-100 text-green-800 border-green-200'
                                ];
                                ?>
                                <span class="status-badge border <?= $status_colors[$pedido['status']] ?>">
                                    <?= ucfirst($pedido['status']) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Formulário para alterar status -->
                    <form method="POST" class="mt-8 p-6 bg-gray-50 rounded-xl">
                        <label class="block text-lg font-semibold text-gray-900 mb-4">Alterar Status do Pedido</label>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <select name="status" class="flex-1 border-2 border-gray-300 rounded-xl px-4 py-3 focus:border-[#b85e2b] focus:ring-2 focus:ring-[#b85e2b] focus:ring-opacity-20 transition-colors">
                                <option value="pendente" <?= $pedido['status'] == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                <option value="processando" <?= $pedido['status'] == 'processando' ? 'selected' : '' ?>>Processando</option>
                                <option value="enviado" <?= $pedido['status'] == 'enviado' ? 'selected' : '' ?>>Enviado</option>
                                <option value="entregue" <?= $pedido['status'] == 'entregue' ? 'selected' : '' ?>>Entregue</option>
                            </select>
                            <button type="submit" class="btn-primary text-white px-8 py-3 rounded-xl font-semibold transition-all flex items-center gap-2">
                                <i class="fas fa-sync-alt"></i>
                                Atualizar Status
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Informações do Cliente -->
                <div class="detail-card bg-white rounded-2xl shadow-lg p-6 border border-[#f5f1e6]">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-gradient-to-br from-[#b85e2b] to-[#f7b95e] rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white text-lg"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Informações do Cliente</h2>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <i class="fas fa-user-circle text-[#b85e2b]"></i>
                            <div>
                                <p class="text-sm text-gray-600">Nome</p>
                                <p class="font-semibold text-gray-900"><?= htmlspecialchars($pedido['nome'] ?? 'Não informado') ?></p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <i class="fas fa-envelope text-[#b85e2b]"></i>
                            <div>
                                <p class="text-sm text-gray-600">Email</p>
                                <p class="font-semibold text-gray-900"><?= htmlspecialchars($pedido['email']) ?></p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <i class="fas fa-phone text-[#b85e2b]"></i>
                            <div>
                                <p class="text-sm text-gray-600">Telefone</p>
                                <p class="font-semibold text-gray-900"><?= htmlspecialchars($pedido['telefone'] ?? 'Não informado') ?></p>
                            </div>
                        </div>

                        <div class="p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-start gap-3 mb-2">
                                <i class="fas fa-map-marker-alt text-[#b85e2b] mt-1"></i>
                                <div>
                                    <p class="text-sm text-gray-600">Endereço</p>
                                    <p class="font-semibold text-gray-900"><?= htmlspecialchars($pedido['endereco'] ?? 'Não informado') ?></p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 mt-2">
                                <div>
                                    <p class="text-xs text-gray-500">Cidade</p>
                                    <p class="text-sm font-medium"><?= htmlspecialchars($pedido['cidade'] ?? 'Não informada') ?></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Estado</p>
                                    <p class="text-sm font-medium"><?= htmlspecialchars($pedido['estado'] ?? 'Não informado') ?></p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-xs text-gray-500">CEP</p>
                                    <p class="text-sm font-medium"><?= htmlspecialchars($pedido['cep'] ?? 'Não informado') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Coluna Direita: Pagamento e Itens -->
                <div class="lg:col-span-3 grid grid-cols-1 lg:grid-cols-2 gap-8">
                    
                    <!-- Informações de Pagamento -->
                    <div class="detail-card bg-white rounded-2xl shadow-lg p-6 border border-[#f5f1e6]">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 bg-gradient-to-br from-[#b85e2b] to-[#f7b95e] rounded-full flex items-center justify-center">
                                <i class="fas fa-credit-card text-white text-lg"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900">Informações de Pagamento</h2>
                        </div>

                        <div class="space-y-6">
                            <!-- Método de Pagamento -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">Método de Pagamento</label>
                                <form method="POST" class="space-y-3">
                                    <div class="flex gap-3">
                                        <label class="flex-1 cursor-pointer">
                                            <input type="radio" name="metodo_pagamento" value="pix" 
                                                   <?= ($pedido['metodo_pagamento'] ?? 'pix') == 'pix' ? 'checked' : '' ?> 
                                                   class="hidden peer">
                                            <div class="p-3 border-2 border-gray-200 rounded-xl peer-checked:border-green-500 peer-checked:bg-green-50 transition-all flex items-center gap-3">
                                                <div class="payment-method-icon bg-green-100 text-green-600">
                                                    <i class="fas fa-qrcode"></i>
                                                </div>
                                                <span class="font-medium">PIX</span>
                                            </div>
                                        </label>
                                        <label class="flex-1 cursor-pointer">
                                            <input type="radio" name="metodo_pagamento" value="cartao" 
                                                   <?= ($pedido['metodo_pagamento'] ?? '') == 'cartao' ? 'checked' : '' ?> 
                                                   class="hidden peer">
                                            <div class="p-3 border-2 border-gray-200 rounded-xl peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all flex items-center gap-3">
                                                <div class="payment-method-icon bg-blue-100 text-blue-600">
                                                    <i class="fas fa-credit-card"></i>
                                                </div>
                                                <span class="font-medium">Cartão</span>
                                            </div>
                                        </label>
                                    </div>
                                    <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition-colors font-medium">
                                        <i class="fas fa-save mr-2"></i>Salvar Método
                                    </button>
                                </form>
                            </div>

                            <!-- Status do Pagamento -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">Status do Pagamento</label>
                                <form method="POST" class="space-y-3">
                                    <select name="status_pagamento" class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 focus:border-[#b85e2b] focus:ring-2 focus:ring-[#b85e2b] focus:ring-opacity-20 transition-colors">
                                        <option value="pendente" <?= ($pedido['status_pagamento'] ?? 'pendente') == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                        <option value="aprovado" <?= ($pedido['status_pagamento'] ?? '') == 'aprovado' ? 'selected' : '' ?>>Aprovado</option>
                                        <option value="recusado" <?= ($pedido['status_pagamento'] ?? '') == 'recusado' ? 'selected' : '' ?>>Recusado</option>
                                    </select>
                                    <button type="submit" class="w-full bg-purple-500 text-white py-2 rounded-lg hover:bg-purple-600 transition-colors font-medium">
                                        <i class="fas fa-sync-alt mr-2"></i>Atualizar Status
                                    </button>
                                </form>
                            </div>

                            <!-- Comprovante de Pagamento -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">Comprovante</label>
                                <?php if (!empty($pedido['comprovante_pagamento'])): ?>
                                    <div class="mb-3 p-3 bg-green-50 border border-green-200 rounded-xl">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <i class="fas fa-file-invoice text-green-600 text-xl"></i>
                                                <div>
                                                    <p class="font-medium text-green-800">Comprovante enviado</p>
                                                    <p class="text-sm text-green-600">Clique para visualizar</p>
                                                </div>
                                            </div>
                                            <a href="../<?= $pedido['comprovante_pagamento'] ?>" target="_blank" 
                                               class="bg-green-500 text-white px-3 py-1 rounded-lg hover:bg-green-600 transition-colors">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="mb-3 p-3 bg-yellow-50 border border-yellow-200 rounded-xl">
                                        <div class="flex items-center gap-3">
                                            <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                                            <p class="text-yellow-800">Nenhum comprovante enviado</p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="POST" enctype="multipart/form-data" class="space-y-3">
                                    <div>
                                        <input type="file" name="comprovante" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" 
                                               class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 focus:border-[#b85e2b] focus:ring-2 focus:ring-[#b85e2b] focus:ring-opacity-20 transition-colors">
                                        <p class="text-xs text-gray-500 mt-1">Formatos: JPG, PNG, PDF, DOC (Max: 5MB)</p>
                                    </div>
                                    <button type="submit" class="w-full bg-[#b85e2b] text-white py-2 rounded-lg hover:bg-[#9a4c23] transition-colors font-medium">
                                        <i class="fas fa-upload mr-2"></i>Enviar Comprovante
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Itens do Pedido -->
                    <div class="detail-card bg-white rounded-2xl shadow-lg p-6 border border-[#f5f1e6]">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 bg-gradient-to-br from-[#b85e2b] to-[#f7b95e] rounded-full flex items-center justify-center">
                                <i class="fas fa-boxes text-white text-lg"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900">Itens do Pedido</h2>
                            <span class="bg-[#b85e2b] text-white px-3 py-1 rounded-full text-sm font-semibold">
                                <?= $itens->num_rows ?> item(ns)
                            </span>
                        </div>

                        <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
                            <?php while ($item = $itens->fetch_assoc()): ?>
                            <div class="item-card bg-gray-50 border border-gray-200 rounded-xl p-4 hover:bg-white transition-all">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($item['produto_nome']) ?></h3>
                                        <p class="text-sm text-gray-500 mt-1">Código: <?= $item['produto_id'] ?></p>
                                    </div>
                                    <span class="bg-[#b85e2b] text-white px-3 py-1 rounded-full text-sm font-semibold">
                                        <?= $item['quantidade'] ?>x
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-sm text-gray-600">Preço unitário</p>
                                        <p class="font-semibold text-gray-900">R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-600">Subtotal</p>
                                        <p class="text-lg font-bold text-[#b85e2b]">R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>

                        <!-- Total -->
                        <div class="border-t border-gray-200 mt-6 pt-6">
                            <div class="flex justify-between items-center">
                                <span class="text-xl font-bold text-gray-900">Total do Pedido:</span>
                                <span class="text-2xl font-bold text-green-600">R$ <?= number_format($pedido['total'], 2, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ações Rápidas -->
            <div class="detail-card bg-white rounded-2xl shadow-lg p-6 border border-[#f5f1e6] mt-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Ações Rápidas</h2>
                <div class="flex flex-wrap gap-4">
                    <a href="../fpedido-clas/comprovante.php?pedido=<?= $pedido_id ?>" 
                       class="bg-blue-500 text-white px-6 py-3 rounded-xl font-semibold hover:bg-blue-600 transition-colors inline-flex items-center gap-2" target="_blank">
                        <i class="fas fa-receipt"></i>
                        Ver Comprovante
                    </a>
                    <button onclick="window.print()" class="bg-gray-500 text-white px-6 py-3 rounded-xl font-semibold hover:bg-gray-600 transition-colors inline-flex items-center gap-2">
                        <i class="fas fa-print"></i>
                        Imprimir
                    </button>
                    <?php if ($pedido['status'] !== 'entregue'): ?>
                        <form method="POST" class="inline">
                            <input type="hidden" name="status" value="entregue">
                            <button type="submit" class="bg-green-500 text-white px-6 py-3 rounded-xl font-semibold hover:bg-green-600 transition-colors inline-flex items-center gap-2"
                                    onclick="return confirm('Tem certeza que deseja marcar este pedido como ENTREGUE?')">
                                <i class="fas fa-check-circle"></i>
                                Marcar como Entregue
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

</body>
</html>