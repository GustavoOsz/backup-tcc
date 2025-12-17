<?php
session_start();

// Verificação robusta de admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Conexão com tratamento de erro
try {
    $conn = new mysqli("localhost", "root", "", "tenda");
    $conn->set_charset("utf8mb4");
    
    // Verificar se há erro na conexão
    if ($conn->connect_error) {
        throw new Exception("Erro na conexão: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Validação e sanitização de datas
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d', strtotime('-30 days'));
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');

// Validar formato das datas
if (!validateDate($data_inicio) || !validateDate($data_fim)) {
    $data_inicio = date('Y-m-d', strtotime('-30 days'));
    $data_fim = date('Y-m-d');
}

// Garantir que data_inicio não seja maior que data_fim
if ($data_inicio > $data_fim) {
    $temp = $data_inicio;
    $data_inicio = $data_fim;
    $data_fim = $temp;
}

// Relatório de vendas por período
$relatorio_vendas = getRelatorioVendas($conn, $data_inicio, $data_fim);
$pedidos_recentes = getPedidosRecentes($conn, $data_inicio, $data_fim);
$produtos_mais_vendidos = getProdutosMaisVendidos($conn, $data_inicio, $data_fim);
$status_totais = getVendasPorStatus($conn, $data_inicio, $data_fim);

// Funções para organizar o código
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

function getRelatorioVendas($conn, $data_inicio, $data_fim) {
    $query_vendas = "SELECT 
        COUNT(*) as total_pedidos,
        SUM(total) as total_vendas,
        AVG(total) as ticket_medio,
        MIN(total) as menor_venda,
        MAX(total) as maior_venda
    FROM pedidos 
    WHERE DATE(data_pedido) BETWEEN ? AND ? 
    AND status != 'pendente'";
    
    $stmt = $conn->prepare($query_vendas);
    $stmt->bind_param("ss", $data_inicio, $data_fim);
    $stmt->execute();
    $result = $stmt->get_result();
    $dados = $result->fetch_assoc();
    
    return $dados ?: [
        'total_pedidos' => 0,
        'total_vendas' => 0,
        'ticket_medio' => 0,
        'menor_venda' => 0,
        'maior_venda' => 0
    ];
}

function getPedidosRecentes($conn, $data_inicio, $data_fim) {
    $query = "SELECT p.*, u.nome, u.email 
              FROM pedidos p 
              JOIN usuarios u ON p.usuario_id = u.id 
              WHERE DATE(p.data_pedido) BETWEEN ? AND ?
              ORDER BY p.data_pedido DESC 
              LIMIT 10";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $data_inicio, $data_fim);
    $stmt->execute();
    return $stmt->get_result();
}

function getProdutosMaisVendidos($conn, $data_inicio, $data_fim) {
    $query = "SELECT 
        pi.produto_nome,
        SUM(pi.quantidade) as total_vendido,
        SUM(pi.subtotal) as total_faturado
    FROM pedido_itens pi
    JOIN pedidos p ON pi.pedido_id = p.id
    WHERE DATE(p.data_pedido) BETWEEN ? AND ?
    AND p.status != 'pendente'
    GROUP BY pi.produto_id, pi.produto_nome
    ORDER BY total_vendido DESC
    LIMIT 10";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $data_inicio, $data_fim);
    $stmt->execute();
    return $stmt->get_result();
}

function getVendasPorStatus($conn, $data_inicio, $data_fim) {
    $query = "SELECT 
        status,
        COUNT(*) as total,
        SUM(total) as valor_total
    FROM pedidos 
    WHERE DATE(data_pedido) BETWEEN ? AND ?
    GROUP BY status";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $data_inicio, $data_fim);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $status_totais = [];
    while ($status = $result->fetch_assoc()) {
        $status_totais[$status['status']] = $status;
    }
    
    // Garantir que todos os status existam
    $status_default = ['pendente', 'processando', 'enviado', 'entregue', 'cancelado'];
    foreach ($status_default as $status) {
        if (!isset($status_totais[$status])) {
            $status_totais[$status] = ['total' => 0, 'valor_total' => 0];
        }
    }
    
    return $status_totais;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios de Vendas - Tenda das Marés</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">📊 Relatórios de Vendas</h1>
                    <p class="text-gray-600">Análise completa do desempenho de vendas</p>
                    <p class="text-sm text-gray-500 mt-1">Período: <?= date('d/m/Y', strtotime($data_inicio)) ?> - <?= date('d/m/Y', strtotime($data_fim)) ?></p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="painel_admin.php" class="bg-gray-500 text-white px-6 py-3 rounded-xl font-semibold hover:bg-gray-600 transition-colors inline-flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i>
                        Voltar ao Painel
                    </a>
                    <button onclick="showDevelopmentMessage()" class="bg-red-500 text-white px-6 py-3 rounded-xl font-semibold hover:bg-red-600 transition-colors inline-flex items-center gap-2">
                        <i class="fas fa-edit"></i>
                        Exportar PDF
                    </button>
                </div>
            </div>


        <!-- Filtros -->
        <div class="detail-card bg-white rounded-2xl shadow-lg p-6 border border-[#f5f1e6] mb-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 bg-gradient-to-br from-[#b85e2b] to-[#f7b95e] rounded-full flex items-center justify-center">
                    <i class="fas fa-filter text-white text-lg"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Filtros do Relatório</h2>
            </div>
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Data Início</label>
                    <input type="date" name="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>" 
                           class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 focus:border-[#b85e2b] focus:ring-2 focus:ring-[#b85e2b] focus:ring-opacity-20 transition-colors" 
                           max="<?= date('Y-m-d') ?>">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Data Fim</label>
                    <input type="date" name="data_fim" value="<?= htmlspecialchars($data_fim) ?>" 
                           class="w-full border-2 border-gray-300 rounded-xl px-4 py-3 focus:border-[#b85e2b] focus:ring-2 focus:ring-[#b85e2b] focus:ring-opacity-20 transition-colors" 
                           max="<?= date('Y-m-d') ?>">
                </div>
                <div class="flex flex-col gap-2 md:col-span-1">
                    <button type="submit" class="btn-primary text-white px-6 py-3 rounded-xl font-semibold transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-search"></i>
                        Gerar
                    </button>
                    <a href="relatorios.php" class="bg-gray-500 text-white px-6 py-3 rounded-xl font-semibold hover:bg-gray-600 transition-colors inline-flex items-center justify-center gap-2">
                        <i class="fas fa-redo"></i>
                        Limpar
                    </a>
                </div>
            </form>
        </div>

        <!-- Cards de Métricas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <?php 
            $metricas = [
                [
                    'titulo' => 'Total de Vendas',
                    'valor' => $relatorio_vendas['total_vendas'],
                    'cor' => 'text-green-600',
                    'icone' => 'fas fa-money-bill-wave',
                    'desc' => 'Período selecionado',
                    'formato' => 'monetario',
                    'bg' => 'bg-green-100'
                ],
                [
                    'titulo' => 'Pedidos Realizados',
                    'valor' => $relatorio_vendas['total_pedidos'],
                    'cor' => 'text-blue-600',
                    'icone' => 'fas fa-shopping-bag',
                    'desc' => 'Total de pedidos',
                    'formato' => 'numero',
                    'bg' => 'bg-blue-100'
                ],
                [
                    'titulo' => 'Ticket Médio',
                    'valor' => $relatorio_vendas['ticket_medio'],
                    'cor' => 'text-purple-600',
                    'icone' => 'fas fa-receipt',
                    'desc' => 'Valor médio por pedido',
                    'formato' => 'monetario',
                    'bg' => 'bg-purple-100'
                ],
                [
                    'titulo' => 'Maior Venda',
                    'valor' => $relatorio_vendas['maior_venda'],
                    'cor' => 'text-orange-600',
                    'icone' => 'fas fa-crown',
                    'desc' => 'Pedido de maior valor',
                    'formato' => 'monetario',
                    'bg' => 'bg-orange-100'
                ]
            ];
            
            foreach ($metricas as $metrica): 
            ?>
            <div class="detail-card bg-white rounded-2xl shadow-lg p-6 border border-[#f5f1e6]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-semibold"><?= $metrica['titulo'] ?></p>
                        <h3 class="text-2xl font-bold <?= $metrica['cor'] ?> mt-2">
                            <?php if ($metrica['formato'] === 'monetario'): ?>
                                R$ <?= number_format($metrica['valor'], 2, ',', '.') ?>
                            <?php else: ?>
                                <?= $metrica['valor'] ?>
                            <?php endif; ?>
                        </h3>
                        <p class="text-xs text-gray-500 mt-2"><?= $metrica['desc'] ?></p>
                    </div>
                    <div class="w-12 h-12 <?= $metrica['bg'] ?> rounded-full flex items-center justify-center">
                        <i class="<?= $metrica['icone'] ?> <?= $metrica['cor'] ?> text-lg"></i>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Gráficos e Produtos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Gráfico de Vendas por Status -->
            <div class="detail-card bg-white rounded-2xl shadow-lg p-6 border border-[#f5f1e6]">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 bg-gradient-to-br from-[#b85e2b] to-[#f7b95e] rounded-full flex items-center justify-center">
                        <i class="fas fa-chart-pie text-white text-lg"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Vendas por Status</h2>
                </div>
                <div class="h-80">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <!-- Produtos Mais Vendidos -->
            <div class="detail-card bg-white rounded-2xl shadow-lg p-6 border border-[#f5f1e6]">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 bg-gradient-to-br from-[#b85e2b] to-[#f7b95e] rounded-full flex items-center justify-center">
                        <i class="fas fa-trophy text-white text-lg"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Produtos Mais Vendidos</h2>
                </div>
                <div class="space-y-4 max-h-80 overflow-y-auto">
                    <?php if ($produtos_mais_vendidos->num_rows > 0): ?>
                        <?php while ($produto = $produtos_mais_vendidos->fetch_assoc()): ?>
                            <div class="flex justify-between items-center p-4 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-gray-900 truncate"><?= htmlspecialchars($produto['produto_nome']) ?></h3>
                                    <p class="text-sm text-gray-500 mt-1"><?= $produto['total_vendido'] ?> unidades vendidas</p>
                                </div>
                                <div class="text-right flex-shrink-0 ml-4">
                                    <p class="font-bold text-green-600 whitespace-nowrap">
                                        R$ <?= number_format($produto['total_faturado'], 2, ',', '.') ?>
                                    </p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <i class="fas fa-box-open text-gray-400 text-4xl mb-3"></i>
                            <p class="text-gray-500">Nenhuma venda no período</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Resumo por Status -->
        <div class="detail-card bg-white rounded-2xl shadow-lg p-6 border border-[#f5f1e6] mb-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 bg-gradient-to-br from-[#b85e2b] to-[#f7b95e] rounded-full flex items-center justify-center">
                    <i class="fas fa-chart-bar text-white text-lg"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Resumo por Status</h2>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                <?php 
                $status_list = [
                    'pendente' => ['Pendentes', 'bg-yellow-100 text-yellow-800 border-yellow-200', 'fas fa-clock'],
                    'processando' => ['Processando', 'bg-blue-100 text-blue-800 border-blue-200', 'fas fa-cog'],
                    'enviado' => ['Enviados', 'bg-purple-100 text-purple-800 border-purple-200', 'fas fa-shipping-fast'],
                    'entregue' => ['Entregues', 'bg-green-100 text-green-800 border-green-200', 'fas fa-check-circle'],
                    'cancelado' => ['Cancelados', 'bg-red-100 text-red-800 border-red-200', 'fas fa-times-circle']
                ];
                
                foreach ($status_list as $status_key => $status_info): 
                    $dados = $status_totais[$status_key] ?? ['total' => 0, 'valor_total' => 0];
                ?>
                    <div class="text-center p-4 border-2 rounded-xl <?= $status_info[1] ?>">
                        <div class="w-10 h-10 <?= str_replace('bg-', 'bg-', explode(' ', $status_info[1])[0]) ?> rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="<?= $status_info[2] ?> <?= str_replace('text-', 'text-', explode(' ', $status_info[1])[1]) ?>"></i>
                        </div>
                        <h3 class="font-semibold text-sm mb-1"><?= $status_info[0] ?></h3>
                        <p class="text-xl font-bold"><?= $dados['total'] ?></p>
                        <p class="text-xs text-gray-600 mt-1">R$ <?= number_format($dados['valor_total'], 2, ',', '.') ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Pedidos Recentes -->
        <div class="detail-card bg-white rounded-2xl shadow-lg p-6 border border-[#f5f1e6]">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-[#b85e2b] to-[#f7b95e] rounded-full flex items-center justify-center">
                        <i class="fas fa-history text-white text-lg"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Pedidos Recentes</h2>
                </div>
                <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">Últimos 10 pedidos</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pedido</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if ($pedidos_recentes->num_rows > 0): ?>
                            <?php while ($pedido = $pedidos_recentes->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="font-mono text-sm font-semibold">#<?= htmlspecialchars($pedido['numero_pedido']) ?></span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div>
                                        <div class="font-semibold text-gray-900"><?= htmlspecialchars($pedido['nome'] ?? $pedido['email']) ?></div>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($pedido['email']) ?></div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm"><?= date('d/m H:i', strtotime($pedido['data_pedido'])) ?></td>
                                <td class="px-4 py-3 whitespace-nowrap font-bold text-gray-900">R$ <?= number_format($pedido['total'], 2, ',', '.') ?></td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold 
                                        <?= getStatusClass($pedido['status']) ?>">
                                        <?= ucfirst($pedido['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-3xl text-gray-300 mb-2"></i>
                                    <p>Nenhum pedido no período selecionado</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

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
        // Gráfico de Vendas por Status
        const statusChart = new Chart(
            document.getElementById('statusChart'),
            {
                type: 'doughnut',
                data: {
                    labels: ['Pendentes', 'Processando', 'Enviados', 'Entregues', 'Cancelados'],
                    datasets: [{
                        data: [
                            <?= $status_totais['pendente']['total'] ?? 0 ?>,
                            <?= $status_totais['processando']['total'] ?? 0 ?>,
                            <?= $status_totais['enviado']['total'] ?? 0 ?>,
                            <?= $status_totais['entregue']['total'] ?? 0 ?>,
                            <?= $status_totais['cancelado']['total'] ?? 0 ?>
                        ],
                        backgroundColor: [
                            '#fbbf24', // Amarelo
                            '#3b82f6', // Azul
                            '#8b5cf6', // Roxo
                            '#10b981', // Verde
                            '#ef4444'  // Vermelho
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            }
        );

       

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

        function closeDevelopmentMessage() {
            const modal = document.getElementById('developmentModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

    </script>
</body>
</html>

<?php
// Fechar conexão
$conn->close();

// Função auxiliar para classes de status
function getStatusClass($status) {
    $classes = [
        'pendente' => 'bg-yellow-100 text-yellow-800',
        'processando' => 'bg-blue-100 text-blue-800',
        'enviado' => 'bg-purple-100 text-purple-800',
        'entregue' => 'bg-green-100 text-green-800',
        'cancelado' => 'bg-red-100 text-red-800'
    ];
    return $classes[$status] ?? 'bg-gray-100 text-gray-800';
}
?>