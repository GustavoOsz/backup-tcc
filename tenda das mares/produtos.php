<?php
session_start();

$conn = new mysqli("localhost", "root", "", "tenda");
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Pesquisa
$pesquisa = isset($_GET['pesquisa']) ? $conn->real_escape_string($_GET['pesquisa']) : "";


$sql = "SELECT * FROM produto WHERE nome LIKE '%$pesquisa%' ORDER BY id DESC";
$res = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Produtos - Tenda das Marés</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
    .product-card {
        transition: all 0.3s ease;
        transform: translateY(0);
    }
    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    .search-container {
        position: relative;
        max-width: 400px;
    }
    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #4f2905;
    }
    .loading-spinner {
        display: none;
        width: 24px;
        height: 24px;
        border: 2px solid #f3f4f6;
        border-top: 2px solid #b85e2b;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
    }
    @keyframes spin {
        0% { transform: translateY(-50%) rotate(0deg); }
        100% { transform: translateY(-50%) rotate(360deg); }
    }
    .price-tag {
        background: linear-gradient(135deg, #b85e2b, #e07a3f);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: bold;
        font-size: 0.9rem;
    }
    .category-badge {
        background: #f5f1e6;
        color: #4f2905;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        position: absolute;
        top: 12px;
        left: 12px;
    }
</style>
</head>
<body class="bg-gray-50 text-[#4f2905] font-sans">

<!-- Header -->
<header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="container mx-auto px-6 py-4">
        <div class="flex justify-between items-center">
            <div>
                <a href="index.php" class="flex items-center space-x-2">
                    <img src="img/logo.png" alt="Logo" class="h-12">
                    <span class="text-xl font-bold text-[#4f2905]">Tenda das Marés</span>
                </a>
            </div>
            <nav class="flex items-center gap-8 text-lg font-medium">
                <a href="produtos.php" class="hover:text-[#b85e2b] font-semibold border-b-2 border-[#b85e2b] pb-1">Produtos</a>
                <a href="pesquisas.php" class="hover:text-[#b85e2b] transition-colors">Pesquisas</a>
                <a href="sobre.php" class="hover:text-[#b85e2b] transition-colors">Sobre nós</a>
                <a href="contato.php" class="hover:text-[#b85e2b] transition-colors">Contato</a>
                <a href="login.php" class="p-2 hover:bg-[#f5f1e6] rounded-full transition-colors">
                    <i class="fas fa-user text-[#4f2905] text-lg"></i>
                </a>
            </nav>
        </div>
    </div>
</header>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-[#fbc97f] to-[#f7b95e] py-12">
    <div class="container mx-auto px-6 text-center">
        <h1 class="text-4xl md:text-5xl font-bold text-[#4f2905] mb-4">Nossos Produtos</h1>
        <p class="text-xl text-[#4f2905] opacity-90 max-w-2xl mx-auto">
            Descubra uma seleção exclusiva de produtos artesanais feitos com amor e qualidade
        </p>
    </div>
</section>

<!-- Search Section -->
<section class="py-8 bg-white">
    <div class="container mx-auto px-6">
        <div class="flex justify-center">
            <div class="search-container w-full max-w-2xl">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="pesquisa" placeholder="Buscar produtos..." 
                       class="w-full border-2 border-[#f5f1e6] bg-[#fafafa] rounded-full py-3 px-12 focus:outline-none focus:border-[#b85e2b] focus:bg-white transition-colors text-lg"
                       value="<?php echo htmlspecialchars($pesquisa); ?>">
                <div class="loading-spinner" id="loadingSpinner"></div>
            </div>
        </div>
    </div>
</section>

<!-- Products Section -->
<section class="py-12">
    <div class="container mx-auto px-6">
        <div id="resultado" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            <?php
            if ($res && $res->num_rows > 0) {
                while ($produto = $res->fetch_assoc()) {
                    $imagem = !empty($produto['imagem']) ? $produto['imagem'] : 'img/placeholder.png';
                    $categoria = isset($produto['categoria']) ? $produto['categoria'] : 'Geral';
                    
                    echo '
                    <div class="product-card bg-white rounded-2xl shadow-md overflow-hidden border border-[#f5f1e6]">
                        <a href="produtos-clas/detalhes.php?id='.$produto['id'].'" class="block relative">
                            <span class="category-badge">'.$categoria.'</span>
                            <img src="'.$imagem.'" alt="'.$produto['nome'].'" 
                                 class="w-full h-64 object-cover bg-gray-100 transition-transform duration-300 hover:scale-105">
                        </a>
                        <div class="p-6">
                            <h4 class="text-lg font-semibold mb-2 line-clamp-2 h-14">'.$produto['nome'].'</h4>
                            <div class="flex justify-between items-center mt-4">
                                <span class="price-tag">R$ '.number_format($produto['preco'], 2, ',', '.').'</span>
                                <a href="produtos-clas/detalhes.php?id='.$produto['id'].'" 
                                   class="bg-[#4f2905] text-white px-4 py-2 rounded-lg hover:bg-[#b85e2b] transition-colors text-sm font-medium">
                                    Ver Detalhes
                                </a>
                            </div>
                        </div>
                    </div>
                    ';
                }
            } else {
                echo '
                <div class="col-span-full text-center py-16">
                    <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-2xl font-semibold text-gray-500 mb-2">Nenhum produto encontrado</h3>
                    <p class="text-gray-400">Tente ajustar os termos da sua busca</p>
                </div>
                ';
            }
            ?>
        </div>
    </div>
</section>

<?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
<a href="produtos-clas/adicionar_produto.php" 
   class="fixed bottom-8 right-8 bg-[#b85e2b] hover:bg-[#4f2905] text-white px-6 py-4 rounded-full shadow-xl flex items-center gap-3 font-semibold transition-all hover:scale-105 z-40">
    <i class="fas fa-plus text-lg"></i>
    <span>Novo Produto</span>
</a>
<?php endif; ?>

<script>
const input = document.getElementById('pesquisa');
const resultado = document.getElementById('resultado');
const loadingSpinner = document.getElementById('loadingSpinner');

let searchTimeout;

input.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    loadingSpinner.style.display = 'block';
    
    searchTimeout = setTimeout(() => {
        const pesquisa = input.value;
        fetch('produtos-clas/buscar_produtos.php?pesquisa=' + encodeURIComponent(pesquisa))
        .then(res => res.text())
        .then(html => {
            resultado.innerHTML = html;
            loadingSpinner.style.display = 'none';
        })
        .catch(() => {
            loadingSpinner.style.display = 'none';
        });
    }, 500);
});

// Focus no input de pesquisa se houver termo de pesquisa
window.addEventListener('load', () => {
    if(input.value) {
        input.focus();
    }
});
</script>

</body>
</html>