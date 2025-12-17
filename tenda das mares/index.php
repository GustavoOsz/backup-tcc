<?php
session_start();

$conn = new mysqli("localhost", "root", "", "tenda");
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Buscar produtos em destaque (últimos 4 produtos)
$sql_produtos = "SELECT * FROM produto ORDER BY id DESC LIMIT 4";
$res_produtos = $conn->query($sql_produtos);
$produtos_destaque = [];
if ($res_produtos && $res_produtos->num_rows > 0) {
    while ($produto = $res_produtos->fetch_assoc()) {
        $produtos_destaque[] = $produto;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Tenda das Marés - Artigos Religiosos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-gradient {
            background: linear-gradient(135deg, #b85e2b 0%, #f7b95e 50%, #fbc97f 100%);
        }
        .feature-card {
            transition: all 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .product-card {
            transition: all 0.3s ease;
            transform: translateY(0);
        }
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .btn-primary {
            background: linear-gradient(135deg, #b85e2b, #e07a3f);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(184, 94, 43, 0.4);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #4f2905, #6d3a0f);
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 41, 5, 0.4);
        }
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .price-tag {
            background: linear-gradient(135deg, #b85e2b, #e07a3f);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
        }
    </style>
</head>
<body class="bg-white text-[#4f2905] font-sans">

    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <a href="index.php" class="flex items-center space-x-3">
                        <img src="img/logo.png" alt="Logo" class="h-12">
                        <span class="text-2xl font-bold text-[#4f2905] hidden md:block">Tenda das Marés</span>
                    </a>
                </div>
                <nav class="flex items-center gap-6 text-lg font-medium">
                    <a href="produtos.php" class="hover:text-[#b85e2b] transition-colors">Produtos</a>
                    <a href="pesquisas.php" class="hover:text-[#b85e2b] transition-colors">Pesquisas</a>
                    <a href="sobre.php" class="hover:text-[#b85e2b] transition-colors">Sobre nós</a>
                    <a href="contato.php" class="hover:text-[#b85e2b] transition-colors">Contato</a>
                    
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <!-- Usuário LOGADO -->
                        <div class="flex items-center gap-3 bg-green-50 px-4 py-2 rounded-full border border-green-200">
                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                            <a href="perfil.php" class="font-semibold text-green-800 hover:text-green-900">
                                <?= htmlspecialchars($_SESSION['usuario_email']) ?>
                            </a>
                            <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin'): ?>
                                <span class="text-xs bg-[#b85e2b] text-white px-2 py-1 rounded-full">ADMIN</span>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <!-- Usuário NÃO logado -->
                        <a href="login.php" class="btn-primary text-white px-6 py-3 rounded-full font-semibold flex items-center gap-2">
                            <i class="fas fa-sign-in-alt"></i>
                            Entrar
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-gradient relative overflow-hidden">
        <div class="container mx-auto px-6 py-20 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-5xl md:text-6xl font-bold text-white mb-6 leading-tight">
                    Bem-vindo à <span class="text-[#4f2905]">Tenda das Marés</span>
                </h1>
                <p class="text-xl md:text-2xl text-white mb-8 opacity-90">
                    Seu espaço sagrado para artigos religiosos de qualidade e significado
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="produtos.php" class="btn-primary text-white px-8 py-4 rounded-full font-bold text-lg flex items-center gap-3">
                        <i class="fas fa-shopping-bag"></i>
                        Explorar Produtos
                    </a>
                    <a href="sobre.php" class="bg-white text-[#4f2905] px-8 py-4 rounded-full font-bold text-lg flex items-center gap-3 hover:bg-gray-100 transition-colors">
                        <i class="fas fa-history"></i>
                        Nossa História
                    </a>
                </div>
            </div>
        </div>
        
    </section>

    
    <!-- Produtos em Destaque -->
    <?php if (!empty($produtos_destaque)): ?>
        <section class="py-20 bg-white">
            <div class="container mx-auto px-6">
                <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-[#4f2905] mb-4">🌟 Produtos em Destaque</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Confira nossas últimas adições especialmente selecionadas para você</p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach ($produtos_destaque as $produto): 
                    $imagem = !empty($produto['imagem']) ? $produto['imagem'] : 'img/placeholder.png';
                    $categoria = isset($produto['categoria']) ? $produto['categoria'] : 'Geral';
                ?>
                <div class="product-card bg-white rounded-2xl shadow-md overflow-hidden border border-[#f5f1e6]">
                    <a href="produtos-clas/detalhes.php?id=<?= $produto['id'] ?>" class="block relative">
                        <span class="absolute top-3 left-3 bg-[#f5f1e6] text-[#4f2905] px-2 py-1 rounded-full text-xs font-medium">
                            <?= $categoria ?>
                        </span>
                        <img src="<?= $imagem ?>" alt="<?= $produto['nome'] ?>" 
                        class="w-full h-48 object-cover bg-gray-100 transition-transform duration-300 hover:scale-105">
                    </a>
                    <div class="p-6">
                        <h4 class="text-lg font-semibold mb-2 line-clamp-2 h-14"><?= $produto['nome'] ?></h4>
                        <div class="flex justify-between items-center mt-4">
                            <span class="price-tag">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></span>
                            <a href="produtos-clas/detalhes.php?id=<?= $produto['id'] ?>" 
                            class="bg-[#4f2905] text-white px-4 py-2 rounded-lg hover:bg-[#b85e2b] transition-colors text-sm font-medium">
                            Ver Detalhes
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-12">
                <a href="produtos.php" class="btn-primary text-white px-8 py-4 rounded-full font-bold text-lg inline-flex items-center gap-3">
                    <i class="fas fa-eye"></i>
                    Ver Todos os Produtos
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Destaques Aprimorados -->
    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-[#4f2905] mb-4">✨ Por que Escolher a Tenda das Marés?</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Descubra os diferenciais que fazem da nossa loja seu destino espiritual</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="feature-card bg-white p-8 rounded-2xl border border-[#f5f1e6] text-center group hover:border-[#b85e2b]">
                    <div class="w-20 h-20 bg-gradient-to-br from-[#b85e2b] to-[#f7b95e] rounded-full flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-gem text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Produtos Exclusivos</h3>
                    <p class="text-gray-700 leading-relaxed">Artigos religiosos selecionados com cuidado e dedicação, cada peça com um significado especial para sua fé.</p>
                </div>
                
                <div class="feature-card bg-white p-8 rounded-2xl border border-[#f5f1e6] text-center group hover:border-[#b85e2b]">
                    <div class="w-20 h-20 bg-gradient-to-br from-[#b85e2b] to-[#f7b95e] rounded-full flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-shipping-fast text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Entrega Rápida</h3>
                    <p class="text-gray-700 leading-relaxed">Entregamos em todo o Brasil com agilidade e segurança, para que sua devoção não precise esperar.</p>
                </div>
                
                <div class="feature-card bg-white p-8 rounded-2xl border border-[#f5f1e6] text-center group hover:border-[#b85e2b]">
                    <div class="w-20 h-20 bg-gradient-to-br from-[#b85e2b] to-[#f7b95e] rounded-full flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-hands-helping text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Atendimento Personalizado</h3>
                    <p class="text-gray-700 leading-relaxed">Nossa equipe está sempre pronta para oferecer orientação espiritual e suporte em suas escolhas.</p>
                </div>
            </div>
        </div>
    </section>
    <!-- Agradecimento Especial -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-6">
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <div class="max-w-2xl mx-auto bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-2xl p-8 text-center shadow-sm">
                    
                    <h2 class="text-3xl font-bold text-green-800 mb-4"> Agradecimento Especial!</h2>
                    <p class="text-green-700 mb-6 text-lg"> ㅤㅤㅤㅤA Choupana da Cabloca Jurema e Maria Padilha das Almas, ㅤㅤㅤpor sempre ser nosso lar de fé, e um lugar de paz!</p>
                    <p class="text-green-700 mb-6 text-lg">  </p>
                    <a href="https://instagram.com/choupanadajurema" target="_blank" 
               class="social-icon bg-gradient-to-r from-green-600 to-yellow-500 text-white px-6 py-3 rounded-full font-semibold inline-flex items-center gap-3 w-full justify-center transition-all hover:shadow-lg">
              <i class="fab fa-instagram text-lg"></i>
              @choupanadajurema
            </a>
                </div>
            <?php else: ?>
                <div class="max-w-2xl mx-auto bg-gradient-to-r from-blue-50 to-cyan-50 border border-blue-200 rounded-2xl p-8 text-center shadow-sm">
                    <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-lock text-white text-2xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-blue-800 mb-4">Junte-se à Nossa Comunidade</h2>
                    <p class="text-blue-700 mb-6 text-lg">Faça login para uma experiência personalizada e acompanhe seus pedidos.</p>
                    <a href="login.php" class="bg-blue-500 text-white px-8 py-4 rounded-full hover:bg-blue-600 inline-flex items-center gap-2 font-bold transition-colors mb-4">
                        <i class="fas fa-sign-in-alt"></i>
                        Fazer Login
                    </a>
                    <p class="text-sm text-blue-600">
                        <a href="#" class="hover:underline font-semibold">Não possui uma conta? Cadastre-se aqui</a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Chamada para Ação Final -->
    <section class="py-20 bg-gradient-to-r from-[#4f2905] to-[#6d3a0f] text-white">
        <div class="container mx-auto px-6 text-center">
            <h2 class="text-4xl md:text-5xl font-bold mb-6">Pronto para Fortalecer sua Fé?</h2>
            <p class="text-xl mb-8 opacity-90 max-w-3xl mx-auto">
                Explore nossa coleção exclusiva de artigos religiosos e encontre peças que vão acompanhar sua jornada espiritual.
            </p>
            <a href="produtos.php" class="btn-primary text-white px-10 py-5 rounded-full font-bold text-lg inline-flex items-center gap-3">
                <i class="fas fa-star"></i>
                Descobrir Produtos
            </a>
            <div class="mt-8 flex justify-center gap-6 text-sm opacity-75">
                <div class="flex items-center gap-2">
                    <i class="fas fa-shield-alt"></i>
                    <span>Compra 100% Segura</span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fas fa-truck"></i>
                    <span>Entregamos no Brasil Todo</span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fas fa-heart"></i>
                    <span>Feito com Amor</span>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Rodapé -->
    <footer class="bg-[#f5f1e6] py-12">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Logo e Descrição -->
                <div class="md:col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        <img src="img/logo.png" alt="Logo Tenda das Marés" class="h-12">
                        <span class="text-2xl font-bold text-[#4f2905]">Tenda das Marés</span>
                    </div>
                    <p class="text-gray-600 mb-4 leading-relaxed">
                        Sua loja de confiança para artigos religiosos de qualidade.
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 bg-white rounded-full flex items-center justify-center hover:bg-[#b85e2b] hover:text-white transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-white rounded-full flex items-center justify-center hover:bg-[#b85e2b] hover:text-white transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-white rounded-full flex items-center justify-center hover:bg-[#b85e2b] hover:text-white transition-colors">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Links Rápidos -->
                <div>
                    <h3 class="font-bold text-lg mb-4 text-[#4f2905]">Links Rápidos</h3>
                    <ul class="space-y-2">
                        <li><a href="produtos.php" class="text-gray-600 hover:text-[#b85e2b] transition-colors">Produtos</a></li>
                        <li><a href="sobre.php" class="text-gray-600 hover:text-[#b85e2b] transition-colors">Sobre Nós</a></li>
                        <li><a href="contato.php" class="text-gray-600 hover:text-[#b85e2b] transition-colors">Contato</a></li>
                        <li><a href="pesquisas.php" class="text-gray-600 hover:text-[#b85e2b] transition-colors">Pesquisas</a></li>
                    </ul>
                </div>

                <!-- Suporte -->
                <div>
                    <h3 class="font-bold text-lg mb-4 text-[#4f2905]">Suporte</h3>
                    <ul class="space-y-2">
                        <li><a href="contato.php" class="text-gray-600 hover:text-[#b85e2b] transition-colors">Central de Ajuda</a></li>
                        <li><a href="contato.php" class="text-gray-600 hover:text-[#b85e2b] transition-colors">Política de Privacidade</a></li>
                        <li><a href="contato.php" class="text-gray-600 hover:text-[#b85e2b] transition-colors">Termos de Uso</a></li>
                        <li><a href="contato.php" class="text-gray-600 hover:text-[#b85e2b] transition-colors">Trocas e Devoluções</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-300 mt-8 pt-8 text-center">
                <p class="text-gray-500">
                    &copy; <?= date('Y'); ?> Tenda das Marés. Todos os direitos reservados.
                </p>
            </div>
        </div>
    </footer>
</body>
</html>