<?php
session_start();
$conn = new mysqli("localhost", "root", "", "tenda");

// Verifica se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

// Pega o ID do pedido
if (!isset($_GET['pedido'])) {
    header('Location: ../index.php');
    exit;
}

$pedido_id = intval($_GET['pedido']);

// Busca dados do pedido (apenas do usuário logado ou admin)
if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin') {
    // Admin pode ver qualquer pedido
    $query_pedido = "SELECT * FROM pedidos WHERE id = ?";
} else {
    // Usuário comum só vê seus próprios pedidos
    $query_pedido = "SELECT * FROM pedidos WHERE id = ? AND usuario_id = ?";
}

$stmt_pedido = $conn->prepare($query_pedido);

if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin') {
    $stmt_pedido->bind_param("i", $pedido_id);
} else {
    $stmt_pedido->bind_param("ii", $pedido_id, $_SESSION['usuario_id']);
}

$stmt_pedido->execute();
$pedido = $stmt_pedido->get_result()->fetch_assoc();

if (!$pedido) {
    header('Location: ../index.php');
    exit;
}

// Busca dados do usuário
$query_usuario = "SELECT nome, email, telefone, endereco, cidade, estado, cep FROM usuarios WHERE id = ?";
$stmt_usuario = $conn->prepare($query_usuario);
$stmt_usuario->bind_param("i", $pedido['usuario_id']);
$stmt_usuario->execute();
$usuario = $stmt_usuario->get_result()->fetch_assoc();

// Busca itens do pedido
$query_itens = "SELECT * FROM pedido_itens WHERE pedido_id = ?";
$stmt_itens = $conn->prepare($query_itens);
$stmt_itens->bind_param("i", $pedido_id);
$stmt_itens->execute();
$itens = $stmt_itens->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Comprovante - Pedido #<?= $pedido['numero_pedido'] ?> - Tenda das Marés</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { 
                display: none !important; 
            }
            body { 
                background: white !important;
                font-size: 12px;
            }
            .print-shadow { 
                box-shadow: none !important; 
                border: 1px solid #e5e7eb !important;
            }
            .print-break {
                page-break-inside: avoid;
            }
            .print-mt-0 {
                margin-top: 0 !important;
            }
        }
        
        .watermark {
            position: relative;
        }
        .watermark::before {
            content: "COMPROVANTE - TENDA DAS MARÉS";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 4rem;
            color: rgba(120, 53, 15, 0.05);
            font-weight: bold;
            z-index: 0;
            white-space: nowrap;
            pointer-events: none;
        }
        .watermark > * {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-amber-50 to-orange-50 print:bg-white">
    <div class="max-w-4xl mx-auto my-8 bg-white rounded-2xl shadow-2xl p-8 print-shadow watermark print:my-0 print:rounded-none print:shadow-none">
        
        <!-- Cabeçalho Profissional -->
        <div class="text-center mb-8 print-break print-mt-0">
            <div class="flex justify-between items-center mb-6 no-print">
                <div class="text-left">
                    <p class="text-gray-600 text-sm">Emitido em: <?= date('d/m/Y H:i') ?></p>
                </div>
                <div class="text-right">
                    <p class="text-gray-600 text-sm">ID: <?= uniqid() ?></p>
                </div>
            </div>
            
            <div class="flex items-center justify-center space-x-4 mb-4">
                <div class="w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-500 rounded-full flex items-center justify-center shadow-lg">
                    <i class="fas fa-store text-white text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-amber-800">TENDA DAS MARÉS</h1>
                    <p class="text-gray-600 text-sm">Artigos Religiosos & Acessórios Espirituais</p>
                </div>
            </div>
            
            <div class="inline-flex items-center space-x-2 bg-amber-100 px-4 py-2 rounded-full">
                <i class="fas fa-file-invoice text-amber-600"></i>
                <span class="font-semibold text-amber-800">COMPROVANTE DE PEDIDO</span>
            </div>
        </div>

        <?php if (isset($_SESSION['sucesso_pedido'])): ?>
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 flex items-center space-x-3">
                <i class="fas fa-check-circle text-green-500 text-xl"></i>
                <div>
                    <p class="text-green-800 font-medium"><?= $_SESSION['sucesso_pedido']; unset($_SESSION['sucesso_pedido']); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Grid de Informações -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 print-break">
            <!-- Dados do Pedido -->
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-amber-800 mb-4 flex items-center space-x-2">
                    <i class="fas fa-receipt text-amber-600"></i>
                    <span>Dados do Pedido</span>
                </h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 font-medium">Nº do Pedido:</span>
                        <span class="font-bold text-amber-700">#<?= $pedido['numero_pedido'] ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 font-medium">Data:</span>
                        <span class="font-semibold"><?= date('d/m/Y H:i', strtotime($pedido['data_pedido'])) ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 font-medium">Status:</span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold 
                            <?= $pedido['status'] == 'pendente' ? 'bg-yellow-100 text-yellow-800 border border-yellow-200' : '' ?>
                            <?= $pedido['status'] == 'processando' ? 'bg-blue-100 text-blue-800 border border-blue-200' : '' ?>
                            <?= $pedido['status'] == 'enviado' ? 'bg-purple-100 text-purple-800 border border-purple-200' : '' ?>
                            <?= $pedido['status'] == 'entregue' ? 'bg-green-100 text-green-800 border border-green-200' : '' ?>
                            <?= $pedido['status'] == 'cancelado' ? 'bg-red-100 text-red-800 border border-red-200' : '' ?>">
                            <?= ucfirst($pedido['status']) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Dados do Cliente -->
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-amber-800 mb-4 flex items-center space-x-2">
                    <i class="fas fa-user text-amber-600"></i>
                    <span>Dados do Cliente</span>
                </h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-gray-600 font-medium mb-1">Nome:</p>
                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($usuario['nome'] ?? 'Não informado') ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600 font-medium mb-1">Email:</p>
                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($usuario['email']) ?></p>
                    </div>
                    <?php if ($usuario['telefone']): ?>
                    <div>
                        <p class="text-gray-600 font-medium mb-1">Telefone:</p>
                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($usuario['telefone']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Endereço de Entrega -->
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-amber-800 mb-4 flex items-center space-x-2">
                    <i class="fas fa-truck text-amber-600"></i>
                    <span>Endereço de Entrega</span>
                </h2>
                <div class="space-y-3">
                    <?php if ($usuario['endereco']): ?>
                        <div>
                            <p class="text-gray-600 font-medium mb-1">Endereço:</p>
                            <p class="font-semibold text-gray-800 text-sm"><?= htmlspecialchars($usuario['endereco']) ?></p>
                        </div>
                        <div class="flex space-x-4">
                            <div>
                                <p class="text-gray-600 font-medium mb-1">Cidade:</p>
                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($usuario['cidade']) ?></p>
                            </div>
                            <div>
                                <p class="text-gray-600 font-medium mb-1">Estado:</p>
                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($usuario['estado']) ?></p>
                            </div>
                        </div>
                        <?php if ($usuario['cep']): ?>
                        <div>
                            <p class="text-gray-600 font-medium mb-1">CEP:</p>
                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($usuario['cep']) ?></p>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-gray-500 text-sm">Endereço não informado</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Itens do Pedido -->
        <div class="mb-8 print-break">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-amber-800 flex items-center space-x-3">
                    <i class="fas fa-shopping-basket"></i>
                    <span>Itens do Pedido</span>
                </h2>
                <div class="text-right no-print">
                    <p class="text-sm text-gray-600"><?= $itens->num_rows ?> item(ns)</p>
                </div>
            </div>
            
            <div class="overflow-hidden rounded-xl border border-amber-200 shadow-sm">
                <table class="min-w-full table-auto">
                    <thead class="bg-gradient-to-r from-amber-500 to-orange-500 text-white">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold uppercase text-sm">Produto</th>
                            <th class="px-6 py-4 text-center font-semibold uppercase text-sm">Qtd</th>
                            <th class="px-6 py-4 text-right font-semibold uppercase text-sm">Preço Unit.</th>
                            <th class="px-6 py-4 text-right font-semibold uppercase text-sm">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber-100">
                        <?php while ($item = $itens->fetch_assoc()): ?>
                        <tr class="hover:bg-amber-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-box text-amber-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($item['produto_nome']) ?></p>
                                        <p class="text-sm text-gray-500">Código: <?= $item['produto_id'] ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-amber-100 text-amber-800 px-3 py-1 rounded-full font-semibold">
                                    <?= $item['quantidade'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-gray-700">
                                R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?>
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-gray-700">
                                R$ <?= number_format($item['subtotal'], 2, ',', '.') ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot class="bg-amber-50 border-t-2 border-amber-200">
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right font-bold text-gray-700 text-lg">
                                Total do Pedido:
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-green-600 text-lg">
                                R$ <?= number_format($pedido['total'], 2, ',', '.') ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Informações Adicionais -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8 print-break">
            <!-- Método de Pagamento -->
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-6">
                <h3 class="font-bold text-amber-800 mb-3 flex items-center space-x-2">
                    <i class="fas fa-credit-card text-amber-600"></i>
                    <span>Informações de Pagamento</span>
                </h3>
                <div class="space-y-2">
                    <p class="text-gray-600"><strong>Método:</strong> <?= $pedido['metodo_pagamento'] ?? 'Não especificado' ?></p>
                    <p class="text-gray-600"><strong>Status do Pagamento:</strong> 
                        <span class="px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                            <?= $pedido['status_pagamento'] ?? 'Pendente' ?>
                        </span>
                    </p>
                </div>
            </div>

            <!-- Observações -->
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-6">
                <h3 class="font-bold text-amber-800 mb-3 flex items-center space-x-2">
                    <i class="fas fa-info-circle text-amber-600"></i>
                    <span>Observações</span>
                </h3>
                <p class="text-gray-600 text-sm">
                    Este comprovante serve como recibo de sua compra. Guarde-o para eventuais consultas.
                    Para dúvidas, entre em contato conosco através dos canais oficiais.
                </p>
            </div>
        </div>

        <!-- Mensagem de Agradecimento -->
        <div class="bg-gradient-to-r from-amber-500 to-orange-500 rounded-2xl p-6 text-center text-white mb-8 print-break">
            <div class="flex items-center justify-center space-x-3 mb-3">
                <i class="fas fa-heart text-2xl"></i>
                <h3 class="text-2xl font-bold">Obrigado pela sua confiança!</h3>
                <i class="fas fa-heart text-2xl"></i>
            </div>
            <p class="text-amber-100">
                Sua compra foi processada com sucesso e já está em preparação.<br>
                Acompanhe o status do seu pedido através do seu perfil ou entre em contato conosco.
            </p>
        </div>

        <!-- Rodapé Profissional -->
        <div class="border-t border-amber-200 pt-6 text-center">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 text-sm text-gray-600">
                <div>
                    <p class="font-semibold text-amber-800">📞 Atendimento</p>
                    <p>(11) 9999-9999</p>
                </div>
                <div>
                    <p class="font-semibold text-amber-800">✉️ Email</p>
                    <p>contato@tendadasmares.com</p>
                </div>
                <div>
                    <p class="font-semibold text-amber-800">🌐 Site</p>
                    <p>www.tendadasmares.com</p>
                </div>
            </div>
            <p class="text-xs text-gray-500 border-t border-amber-100 pt-3">
                Este é um documento gerado automaticamente em <?= date('d/m/Y \à\s H:i:s') ?>.<br>
                Tenda das Marés © <?= date('Y') ?> - Todos os direitos reservados.
            </p>
        </div>

        <!-- Botões de Ação -->
        <div class="flex justify-center gap-4 mt-8 no-print print-break">
            <button onclick="window.print()" class="bg-gradient-to-r from-amber-500 to-orange-500 text-white px-8 py-3 rounded-xl hover:from-amber-600 hover:to-orange-600 transition-all shadow-lg flex items-center space-x-2">
                <i class="fas fa-print"></i>
                <span>Imprimir Comprovante</span>
            </button>
            <a href="../perfil.php" class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-8 py-3 rounded-xl hover:from-green-600 hover:to-emerald-700 transition-all shadow-lg flex items-center space-x-2">
                <i class="fas fa-user"></i>
                <span>Voltar ao Perfil</span>
            </a>
            <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin'): ?>
                <a href="painel_admin.php" class="bg-gradient-to-r from-purple-500 to-indigo-600 text-white px-8 py-3 rounded-xl hover:from-purple-600 hover:to-indigo-700 transition-all shadow-lg flex items-center space-x-2">
                    <i class="fas fa-cog"></i>
                    <span>Painel Admin</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Melhorar a experiência de impressão
        document.addEventListener('DOMContentLoaded', function() {
            // Adicionar watermark apenas na impressão
            const style = document.createElement('style');
            style.textContent = `
                @media print {
                    @page {
                        margin: 0.5cm;
                        size: A4;
                    }
                    body {
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                }
            `;
            document.head.appendChild(style);
        });
    </script>
</body>
</html>