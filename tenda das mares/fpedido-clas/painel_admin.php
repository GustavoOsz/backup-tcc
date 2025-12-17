<?php
session_start();
$conn = new mysqli("localhost", "root", "", "tenda");

// Verifica se é admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Busca pedidos pendentes
$query = "SELECT p.*, u.email 
          FROM pedidos p 
          JOIN usuarios u ON p.usuario_id = u.id 
          WHERE p.status != 'entregue' 
          ORDER BY p.data_pedido DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Admin - Tenda das Marés</title>
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
                    <a href="painel_admin.php" class="hover:text-[#b85e2b] font-semibold border-b-2 border-[#b85e2b] pb-1">Painel Admin</a>
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
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">Painel Administrativo</h1>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Gerencie pedidos, visualize relatórios e acompanhe o desempenho da loja
                </p>
            </div>

            <!-- Cards de Resumo -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="admin-card bg-white rounded-2xl shadow-lg p-6 border border-[#f5f1e6] text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-orange-100 to-orange-200 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-clock text-2xl text-orange-500"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Pedidos Pendentes</h3>
                    <p class="text-3xl font-bold text-orange-500"><?= $result->num_rows ?></p>
                </div>

                <div class="admin-card bg-white rounded-2xl shadow-lg p-6 border border-[#f5f1e6] text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-blue-200 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chart-line text-2xl text-blue-500"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Relatórios</h3>
                    <p class="text-lg text-blue-500 font-semibold">Disponíveis</p>
                </div>

                <div class="admin-card bg-white rounded-2xl shadow-lg p-6 border border-[#f5f1e6] text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-green-100 to-green-200 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-box text-2xl text-green-500"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Ações Rápidas</h3>
                    <p class="text-lg text-green-500 font-semibold">Gerenciar</p>
                </div>
            </div>

            <!-- Pedidos Pendentes -->
            <div class="admin-card bg-white rounded-2xl shadow-lg overflow-hidden border border-[#f5f1e6]">
                <div class="bg-gradient-to-r from-[#b85e2b] to-[#e07a3f] px-6 py-4">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-clock text-white text-xl"></i>
                        <h2 class="text-xl font-bold text-white">Pedidos Pendentes</h2>
                    </div>
                </div>
                
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
                                                <span class="text-gray-900"><?= htmlspecialchars($pedido['usuario_email']) ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-600"><?= date('d/m/Y H:i', strtotime($pedido['data_pedido'])) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="font-bold text-green-600">R$ <?= number_format($pedido['total'], 2, ',', '.') ?></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="ver_pedido.php?id=<?= $pedido['id'] ?>" 
                                               class="btn-primary text-white px-4 py-2 rounded-lg font-semibold text-sm inline-flex items-center gap-2 transition-all">
                                                <i class="fas fa-eye"></i>
                                                Ver Detalhes
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <div class="w-24 h-24 bg-gradient-to-br from-[#f5f1e6] to-[#fde9c7] rounded-full flex items-center justify-center mx-auto mb-6">
                                <i class="fas fa-check text-3xl text-[#b85e2b]"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-700 mb-2">Todos os pedidos estão em dia!</h3>
                            <p class="text-gray-500 max-w-md mx-auto">
                                Não há pedidos pendentes no momento. Todos os pedidos foram processados ou entregues.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Ações Rápidas -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                <a href="relatorios.php" class="admin-card bg-white rounded-2xl shadow-lg p-6 border border-[#f5f1e6] hover:border-[#b85e2b] transition-colors group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-chart-bar text-blue-500 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-1">Relatórios de Vendas</h3>
                            <p class="text-gray-600">Acompanhe métricas e desempenho</p>
                        </div>
                    </div>
                </a>

                <a href="pedidos_finalizados.php" class="admin-card bg-white rounded-2xl shadow-lg p-6 border border-[#f5f1e6] hover:border-[#b85e2b] transition-colors group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-100 to-green-200 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-check-circle text-green-500 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-1">Pedidos Finalizados</h3>
                            <p class="text-gray-600">Histórico de pedidos entregues</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </main>

</body>
</html>