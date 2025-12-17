<?php
session_start();
$conn = new mysqli("localhost", "root", "", "tenda");

// Verifica se é admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Busca pedidos finalizados (entregues)
$query = "SELECT p.*, u.email, u.nome 
          FROM pedidos p 
          JOIN usuarios u ON p.usuario_id = u.id 
          WHERE p.status = 'entregue' 
          ORDER BY p.data_pedido DESC";
$result = $conn->query($query);
$total_pedidos = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Pedidos Finalizados - Tenda das Marés</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-card {
            transition: all 0.3s ease;
        }
        .admin-card:hover {
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
                    <a href="pedidos_finalizados.php" class="hover:text-[#b85e2b] font-semibold border-b-2 border-[#b85e2b] pb-1">Pedidos Finalizados</a>
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
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Pedidos Finalizados</h1>
                    <p class="text-gray-600">Histórico completo de pedidos entregues com sucesso</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="painel_admin.php" class="btn-primary text-white px-6 py-3 rounded-xl font-semibold transition-all inline-flex items-center gap-2">
                        <i class="fas fa-clipboard-list"></i>
                        Pedidos Pendentes
                    </a>
                    <a href="../perfil.php" class="bg-gray-500 text-white px-6 py-3 rounded-xl font-semibold hover:bg-gray-600 transition-colors inline-flex items-center gap-2">
                        <i class="fas fa-user"></i>
                        Meu Perfil
                    </a>
                </div>
            </div>

            <!-- Resumo -->
            <div class="admin-card bg-gradient-to-r from-green-500 to-green-600 text-white rounded-2xl shadow-lg p-6 mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold mb-2">Pedidos Entregues com Sucesso</h2>
                        <p class="text-green-100">Total: <?= $total_pedidos ?> pedidos finalizados</p>
                    </div>
                    <div class="text-4xl">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>

            <!-- Tabela de Pedidos -->
            <div class="admin-card bg-white rounded-2xl shadow-lg overflow-hidden border border-[#f5f1e6]">
                <div class="p-6">
                    <?php if ($result->num_rows > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full table-auto">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase">Nº Pedido</th>
                                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase">Cliente</th>
                                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase">Data</th>
                                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase">Total</th>
                                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php while ($pedido = $result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded"><?= $pedido['numero_pedido'] ?></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 bg-gradient-to-br from-[#f5f1e6] to-[#fde9c7] rounded-full flex items-center justify-center">
                                                    <i class="fas fa-user text-[#b85e2b] text-sm"></i>
                                                </div>
                                                <div>
                                                    <div class="font-semibold text-gray-900"><?= htmlspecialchars($pedido['nome'] ?? $pedido['email']) ?></div>
                                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($pedido['email']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-600"><?= date('d/m/Y H:i', strtotime($pedido['data_pedido'])) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="font-bold text-green-600">R$ <?= number_format($pedido['total'], 2, ',', '.') ?></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex gap-2">
                                                <a href="ver_pedido.php?id=<?= $pedido['id'] ?>" 
                                                   class="bg-blue-500 text-white px-4 py-2 rounded-lg font-semibold text-sm inline-flex items-center gap-2 hover:bg-blue-600 transition-colors">
                                                    <i class="fas fa-eye"></i>
                                                    Detalhes
                                                </a>
                                                <a href="../fpedido-clas/comprovante.php?pedido=<?= $pedido['id'] ?>" 
                                                   class="bg-green-500 text-white px-4 py-2 rounded-lg font-semibold text-sm inline-flex items-center gap-2 hover:bg-green-600 transition-colors">
                                                    <i class="fas fa-receipt"></i>
                                                    Comprovante
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <div class="w-24 h-24 bg-gradient-to-br from-[#f5f1e6] to-[#fde9c7] rounded-full flex items-center justify-center mx-auto mb-6">
                                <i class="fas fa-inbox text-3xl text-[#b85e2b]"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-700 mb-2">Nenhum pedido finalizado</h3>
                            <p class="text-gray-500 max-w-md mx-auto mb-6">
                                Todos os pedidos entregues aparecerão aqui. No momento, não há pedidos marcados como entregues.
                            </p>
                            <a href="painel_adm.php" class="btn-primary text-white px-6 py-3 rounded-xl font-semibold inline-flex items-center gap-2 transition-all">
                                <i class="fas fa-clipboard-list"></i>
                                Ver Pedidos Pendentes
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Estatísticas -->
            <?php if ($total_pedidos > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                <div class="admin-card bg-white rounded-2xl shadow-lg p-6 border border-[#f5f1e6] text-center">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-box text-green-500 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Total Entregues</h3>
                    <p class="text-2xl font-bold text-green-600"><?= $total_pedidos ?></p>
                    <p class="text-sm text-gray-500">Pedidos finalizados</p>
                </div>

                <div class="admin-card bg-white rounded-2xl shadow-lg p-6 border border-[#f5f1e6] text-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chart-line text-blue-500 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Último Pedido</h3>
                    <p class="text-sm text-gray-600">
                        <?php 
                        $result->data_seek(0);
                        $ultimo_pedido = $result->fetch_assoc();
                        echo $ultimo_pedido ? date('d/m/Y', strtotime($ultimo_pedido['data_pedido'])) : 'N/A';
                        ?>
                    </p>
                </div>

                <div class="admin-card bg-white rounded-2xl shadow-lg p-6 border border-[#f5f1e6] text-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-trophy text-purple-500 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Satisfação</h3>
                    <p class="text-2xl font-bold text-purple-600">100%</p>
                    <p class="text-sm text-gray-500">Taxa de entrega</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>