<?php
session_start();
$conn = new mysqli("localhost", "root", "", "tenda");

// Verifica se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Busca dados do usuário
$query_usuario = "SELECT * FROM usuarios WHERE id = ?";
$stmt_usuario = $conn->prepare($query_usuario);
$stmt_usuario->bind_param("i", $usuario_id);
$stmt_usuario->execute();
$usuario = $stmt_usuario->get_result()->fetch_assoc();

// Busca histórico de pedidos
$query_pedidos = "SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY data_pedido DESC";
$stmt_pedidos = $conn->prepare($query_pedidos);
$stmt_pedidos->bind_param("i", $usuario_id);
$stmt_pedidos->execute();
$pedidos = $stmt_pedidos->get_result();

// Atualiza dados do usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'atualizar_dados') {
    $nome = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $endereco = $_POST['endereco'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];
    $cep = $_POST['cep'];
    
    $query_update = "UPDATE usuarios SET nome = ?, telefone = ?, endereco = ?, cidade = ?, estado = ?, cep = ? WHERE id = ?";
    $stmt_update = $conn->prepare($query_update);
    $stmt_update->bind_param("ssssssi", $nome, $telefone, $endereco, $cidade, $estado, $cep, $usuario_id);
    
    if ($stmt_update->execute()) {
        $_SESSION['sucesso'] = "Dados atualizados com sucesso!";
    } else {
        $_SESSION['erro'] = "Erro ao atualizar dados.";
    }
    
    header("Location: perfil.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Tenda das Marés</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .rotate-180 {
            transform: rotate(180deg);
            transition: transform 0.2s ease-in-out;
        }

        #user-menu {
            transition: all 0.2s ease-in-out;
        }

        #user-menu.scale-95 {
            transform: scale(0.95);
        }

        #user-menu.scale-100 {
            transform: scale(1);
        }

        #chevron-icon {
            transition: transform 0.2s ease-in-out;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-amber-50 to-orange-50 min-h-screen">
    <!-- Navbar -->
    <header class="bg-white shadow-lg border-b-2 border-amber-200 sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="flex items-center space-x-3">
                        <img src="img/logo.png" alt="Logo" class="h-12">
                        <span class="text-2xl font-bold text-amber-800">Tenda das Marés</span>
                    </a>
                </div>
                <nav class="flex items-center space-x-6">
                    <a href="produtos.php" class="text-amber-700 hover:text-amber-900 font-medium transition-colors">Produtos</a>
                    <a href="pesquisas.php" class="text-amber-700 hover:text-amber-900 font-medium transition-colors">Pesquisas</a>
                    <a href="sobre.php" class="text-amber-700 hover:text-amber-900 font-medium transition-colors">Sobre nós</a>
                    <a href="contato.php" class="text-amber-700 hover:text-amber-900 font-medium transition-colors">Contato</a>
                    
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <div class="flex items-center space-x-4">
                            <a href="produtos-clas/carrinho.php" class="text-amber-700 hover:text-amber-900 relative">
                                <i class="fas fa-shopping-cart text-xl"></i>
                            </a>
                            <div class="relative" id="user-menu-container">
                                <button id="user-menu-button" class="flex items-center space-x-2 bg-amber-100 px-4 py-2 rounded-full hover:bg-amber-200 transition-colors">
                                    <i class="fas fa-user-circle text-amber-700"></i>
                                    <span class="font-medium text-amber-800"><?= htmlspecialchars($_SESSION['usuario_email']) ?></span>
                                    <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
                                        <span class="bg-amber-600 text-white px-2 py-1 rounded-full text-xs">ADMIN</span>
                                    <?php endif; ?>
                                    <i class="fas fa-chevron-down text-amber-600 text-sm transition-transform" id="chevron-icon"></i>
                                </button>
                                <div id="user-menu" class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-amber-200 py-2 hidden z-50 transform origin-top-right transition-all duration-200 ease-out scale-95 opacity-0">
                                    <a href="perfil.php" class="flex items-center space-x-3 px-4 py-3 hover:bg-amber-50 text-amber-700 transition-colors">
                                        <i class="fas fa-user text-amber-600 w-5"></i>
                                        <span>Meu Perfil</span>
                                    </a>
                                    <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
                                        <a href="fpedido-clas/painel_admin.php" class="flex items-center space-x-3 px-4 py-3 hover:bg-amber-50 text-amber-700 transition-colors">
                                            <i class="fas fa-cog text-amber-600 w-5"></i>
                                            <span>Painel Admin</span>
                                        </a>
                                    <?php endif; ?>
                                    <div class="border-t border-amber-100 my-1"></div>
                                    <a href="logout.php" class="flex items-center space-x-3 px-4 py-3 hover:bg-red-50 text-red-600 transition-colors">
                                        <i class="fas fa-sign-out-alt w-5"></i>
                                        <span>Sair</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="bg-amber-600 text-white px-6 py-2 rounded-full hover:bg-amber-700 transition-colors font-medium">
                            <i class="fas fa-sign-in-alt mr-2"></i>Entrar
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-6 py-8">
        <!-- Header do Perfil -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8 border border-amber-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-6">
                    <div class="bg-gradient-to-br from-amber-500 to-orange-500 w-20 h-20 rounded-full flex items-center justify-center shadow-lg">
                        <i class="fas fa-user text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Meu Perfil</h1>
                        <p class="text-gray-600 mt-1">Gerencie suas informações e acompanhe seus pedidos</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Membro desde</p>
                    <p class="font-semibold text-amber-600"><?= date('d/m/Y', strtotime($usuario['data_cadastro'] ?? 'now')) ?></p>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['sucesso'])): ?>
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 flex items-center space-x-3">
                <i class="fas fa-check-circle text-green-500 text-xl"></i>
                <div>
                    <p class="text-green-800 font-medium"><?= $_SESSION['sucesso']; unset($_SESSION['sucesso']); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['erro'])): ?>
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 flex items-center space-x-3">
                <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                <div>
                    <p class="text-red-800 font-medium"><?= $_SESSION['erro']; unset($_SESSION['erro']); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Coluna Principal -->
            <div class="xl:col-span-2 space-y-8">
                <!-- Formulário de Dados Pessoais -->
                <div class="bg-white rounded-2xl shadow-lg border border-amber-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center space-x-3">
                            <i class="fas fa-user-edit"></i>
                            <span>Informações Pessoais</span>
                        </h2>
                    </div>
                    <div class="p-6">
                        <form method="POST">
                            <input type="hidden" name="acao" value="atualizar_dados">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                                        <div class="relative">
                                            <input type="email" value="<?= htmlspecialchars($usuario['email']) ?>" 
                                                   class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-gray-600" readonly>
                                            <i class="fas fa-envelope absolute right-3 top-3 text-gray-400"></i>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">Email não pode ser alterado</p>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nome Completo</label>
                                        <div class="relative">
                                            <input type="text" name="nome" value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>" 
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors" 
                                                   placeholder="Seu nome completo">
                                            <i class="fas fa-user absolute right-3 top-3 text-gray-400"></i>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Telefone</label>
                                        <div class="relative">
                                            <input type="text" name="telefone" value="<?= htmlspecialchars($usuario['telefone'] ?? '') ?>" 
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors" 
                                                   placeholder="(11) 99999-9999">
                                            <i class="fas fa-phone absolute right-3 top-3 text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Endereço</label>
                                        <div class="relative">
                                            <textarea name="endereco" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors" rows="3" 
                                                      placeholder="Rua, número, bairro"><?= htmlspecialchars($usuario['endereco'] ?? '') ?></textarea>
                                            <i class="fas fa-map-marker-alt absolute right-3 top-3 text-gray-400"></i>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Cidade</label>
                                            <input type="text" name="cidade" value="<?= htmlspecialchars($usuario['cidade'] ?? '') ?>" 
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors" 
                                                   placeholder="Sua cidade">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Estado</label>
                                            <input type="text" name="estado" value="<?= htmlspecialchars($usuario['estado'] ?? '') ?>" 
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors" 
                                                   placeholder="UF" maxlength="2">
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">CEP</label>
                                        <div class="relative">
                                            <input type="text" name="cep" value="<?= htmlspecialchars($usuario['cep'] ?? '') ?>" 
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors" 
                                                   placeholder="00000-000">
                                            <i class="fas fa-mail-bulk absolute right-3 top-3 text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6">
                                <button type="submit" class="w-full bg-gradient-to-r from-amber-500 to-orange-500 text-white py-3 px-6 rounded-lg hover:from-amber-600 hover:to-orange-600 transition-all duration-300 font-semibold shadow-lg">
                                    <i class="fas fa-save mr-2"></i>Salvar Alterações
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Histórico de Pedidos -->
                <div class="bg-white rounded-2xl shadow-lg border border-amber-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center space-x-3">
                            <i class="fas fa-history"></i>
                            <span>Histórico de Pedidos</span>
                        </h2>
                    </div>
                    <div class="p-6">
                        <?php if ($pedidos->num_rows > 0): ?>
                            <div class="space-y-4">
                                <?php while ($pedido = $pedidos->fetch_assoc()): ?>
                                    <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow">
                                        <div class="flex justify-between items-start mb-3">
                                            <div>
                                                <h3 class="font-semibold text-gray-800">Pedido #<?= $pedido['numero_pedido'] ?></h3>
                                                <p class="text-sm text-gray-500"><?= date('d/m/Y H:i', strtotime($pedido['data_pedido'])) ?></p>
                                            </div>
                                            <span class="px-3 py-1 rounded-full text-xs font-bold 
                                                <?= $pedido['status'] == 'pendente' ? 'bg-yellow-100 text-yellow-800' : '' ?>
                                                <?= $pedido['status'] == 'processando' ? 'bg-blue-100 text-blue-800' : '' ?>
                                                <?= $pedido['status'] == 'enviado' ? 'bg-purple-100 text-purple-800' : '' ?>
                                                <?= $pedido['status'] == 'entregue' ? 'bg-green-100 text-green-800' : '' ?>">
                                                <?= ucfirst($pedido['status']) ?>
                                            </span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-lg font-bold text-amber-600">R$ <?= number_format($pedido['total'], 2, ',', '.') ?></span>
                                            <a href="fpedido-clas/comprovante.php?pedido=<?= $pedido['id'] ?>" 
                                               class="text-amber-600 hover:text-amber-700 font-medium text-sm flex items-center space-x-1">
                                                <i class="fas fa-receipt"></i>
                                                <span>Ver Comprovante</span>
                                            </a>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <div class="bg-amber-50 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-shopping-bag text-amber-500 text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-700 mb-2">Nenhum pedido encontrado</h3>
                                <p class="text-gray-500 mb-4">Ainda não há pedidos em sua conta.</p>
                                <a href="produtos.php" class="inline-flex items-center space-x-2 bg-amber-500 text-white px-6 py-2 rounded-lg hover:bg-amber-600 transition-colors">
                                    <i class="fas fa-shopping-cart"></i>
                                    <span>Fazer minha primeira compra</span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Coluna Lateral -->
            <div class="space-y-8">
                <!-- Cartão de Status -->
                <div class="bg-white rounded-2xl shadow-lg border border-amber-100 p-6">
                    <h3 class="font-semibold text-gray-800 mb-4 flex items-center space-x-2">
                        <i class="fas fa-chart-bar text-amber-500"></i>
                        <span>Resumo da Conta</span>
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Total de Pedidos</span>
                            <span class="font-semibold text-amber-600"><?= $pedidos->num_rows ?></span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Membro desde</span>
                            <span class="font-semibold text-amber-600"><?= date('m/Y', strtotime($usuario['data_cadastro'] ?? 'now')) ?></span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-600">Status da Conta</span>
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full font-semibold">Ativa</span>
                        </div>
                    </div>
                </div>

                <!-- Seção Admin -->
                <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
                <div class="bg-gradient-to-br from-amber-500 to-orange-500 rounded-2xl shadow-lg p-6 text-white">
                    <h3 class="font-bold text-lg mb-4 flex items-center space-x-2">
                        <i class="fas fa-crown"></i>
                        <span>Área do Administrador</span>
                    </h3>
                    <div class="space-y-3">
                        <a href="fpedido-clas/painel_admin.php" 
                           class="flex items-center space-x-3 p-3 bg-white bg-opacity-20 rounded-lg hover:bg-opacity-30 transition-all">
                            <i class="fas fa-tachometer-alt w-5"></i>
                            <span>Painel de Pedidos</span>
                        </a>
                        <a href="fpedido-clas/relatorios.php" 
                           class="flex items-center space-x-3 p-3 bg-white bg-opacity-20 rounded-lg hover:bg-opacity-30 transition-all">
                            <i class="fas fa-chart-line w-5"></i>
                            <span>Relatórios</span>
                        </a>
                        <a href="produtos.php?admin=1" 
                           class="flex items-center space-x-3 p-3 bg-white bg-opacity-20 rounded-lg hover:bg-opacity-30 transition-all">
                            <i class="fas fa-boxes w-5"></i>
                            <span>Gerenciar Produtos</span>
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Ajuda Rápida -->
                <div class="bg-white rounded-2xl shadow-lg border border-amber-100 p-6">
                    <h3 class="font-semibold text-gray-800 mb-4 flex items-center space-x-2">
                        <i class="fas fa-question-circle text-amber-500"></i>
                        <span>Ajuda Rápida</span>
                    </h3>
                    <div class="space-y-2">
                        <a href="#" class="flex items-center space-x-2 text-gray-600 hover:text-amber-600 transition-colors py-1">
                            <i class="fas fa-shipping-fast w-4"></i>
                            <span class="text-sm">Acompanhar entrega</span>
                        </a>
                        <a href="#" class="flex items-center space-x-2 text-gray-600 hover:text-amber-600 transition-colors py-1">
                            <i class="fas fa-exchange-alt w-4"></i>
                            <span class="text-sm">Política de trocas</span>
                        </a>
                        <a href="contato.php" class="flex items-center space-x-2 text-gray-600 hover:text-amber-600 transition-colors py-1">
                            <i class="fas fa-headset w-4"></i>
                            <span class="text-sm">Falar com suporte</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12">
        <div class="container mx-auto px-6 py-8">
            <div class="text-center">
                <p>&copy; <?= date('Y'); ?> Tenda das Marés. Todos os direitos reservados.</p>
                <p class="text-gray-400 text-sm mt-2">Sua loja de artigos religiosos de confiança</p>
            </div>
        </div>
    </footer>

    <script>
    // Controle do menu de usuário
    document.addEventListener('DOMContentLoaded', function() {
        const userMenuButton = document.getElementById('user-menu-button');
        const userMenu = document.getElementById('user-menu');
        const chevronIcon = document.getElementById('chevron-icon');
        let menuTimeout;

        // Abrir/fechar menu ao clicar
        userMenuButton.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleMenu();
        });

        // Fechar menu ao clicar fora
        document.addEventListener('click', function(e) {
            if (!userMenuButton.contains(e.target) && !userMenu.contains(e.target)) {
                closeMenu();
            }
        });

        // Fechar menu ao pressionar ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMenu();
            }
        });

        // Prevenir fechamento ao passar mouse sobre o menu
        userMenu.addEventListener('mouseenter', function() {
            clearTimeout(menuTimeout);
        });

        userMenu.addEventListener('mouseleave', function() {
            // Pequeno delay antes de fechar ao sair do menu
            menuTimeout = setTimeout(closeMenu, 300);
        });

        function toggleMenu() {
            const isOpen = !userMenu.classList.contains('hidden');
            
            if (isOpen) {
                closeMenu();
            } else {
                openMenu();
            }
        }

        function openMenu() {
            userMenu.classList.remove('hidden', 'scale-95', 'opacity-0');
            userMenu.classList.add('scale-100', 'opacity-100');
            chevronIcon.classList.add('rotate-180');
        }

        function closeMenu() {
            userMenu.classList.remove('scale-100', 'opacity-100');
            userMenu.classList.add('scale-95', 'opacity-0');
            chevronIcon.classList.remove('rotate-180');
            
            // Espera a animação terminar antes de esconder
            setTimeout(() => {
                if (userMenu.classList.contains('scale-95')) {
                    userMenu.classList.add('hidden');
                }
            }, 200);
        }

        // Fechar menu ao clicar em qualquer link dentro dele
        userMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function() {
                closeMenu();
            });
        });
    });
    </script>
</body>
</html>