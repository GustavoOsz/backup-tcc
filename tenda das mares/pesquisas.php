<?php
session_start();
$conn = new mysqli("localhost", "root", "", "tenda");

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Pega todos os tópicos
$pesquisas = $conn->query("SELECT id, nome, data, imagem FROM pesquisas ORDER BY data DESC");

// Verifica se clicaram em algum tópico
$selectedId = isset($_GET['id']) ? intval($_GET['id']) : null;
$selected = null;

if ($selectedId) {
    $stmt = $conn->prepare("SELECT * FROM pesquisas WHERE id = ?");
    $stmt->bind_param("i", $selectedId);
    $stmt->execute();
    $result = $stmt->get_result();
    $selected = $result->fetch_assoc();
}

// Pega o tipo de usuário logado
$tipoUsuario = 'visitante';
if (isset($_SESSION['usuario_id'])) {
    $usuario_id = $_SESSION['usuario_id'];
    $sqlTipo = "SELECT tipo FROM usuarios WHERE id = $usuario_id";
    $resTipo = $conn->query($sqlTipo);
    if ($resTipo && $resTipo->num_rows > 0) {
        $tipoUsuario = $resTipo->fetch_assoc()['tipo'];
    }
}

// DEBUG: Verificar dados das imagens
echo "<!-- DEBUG: Selected ID: $selectedId -->";
if ($selected) {
    echo "<!-- DEBUG: Selected imagem: " . ($selected['imagem'] ?? 'NULL') . " -->";
    echo "<!-- DEBUG: Selected nome: " . ($selected['nome'] ?? 'NULL') . " -->";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Ensinamentos - Tenda das Marés</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    .research-card {
        transition: all 0.3s ease;
    }
    .research-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .active-topic {
        background: linear-gradient(135deg, #b85e2b, #e07a3f);
        color: white;
    }
    .content-fade {
        animation: fadeIn 0.5s ease-in;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .img-placeholder {
        background: linear-gradient(135deg, #f5f1e6, #fde9c7);
    }
  </style>
</head>
<body class="bg-gray-50 text-[#4f2905] font-sans">

  <!-- Header Moderno -->
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
                <a href="pesquisas.php" class="hover:text-[#b85e2b] font-semibold border-b-2 border-[#b85e2b] pb-1">Pesquisas</a>
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
  <section class="bg-gradient-to-r from-[#b85e2b] to-[#f7b95e] py-16 text-white">
    <div class="container mx-auto px-6 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Ensinamentos & Pesquisas</h1>
        <p class="text-xl opacity-90 max-w-3xl mx-auto">
            Explore nossos estudos e reflexões sobre fé, espiritualidade e tradições religiosas
        </p>
    </div>
  </section>

  <!-- Botão Admin -->
  <?php if ($tipoUsuario === 'admin'): ?>
  <div class="container mx-auto px-6 py-6">
    <a href="pesquisas-clas/cadastrar_pesquisa.php" 
       class="bg-[#b85e2b] hover:bg-[#4f2905] text-white px-6 py-3 rounded-full font-semibold inline-flex items-center gap-2 transition-colors shadow-lg">
        <i class="fas fa-plus"></i>
        Nova Pesquisa
    </a>
  </div>
  <?php endif; ?>

  <!-- Conteúdo Principal -->
  <div class="container mx-auto px-6 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
      
      <!-- Coluna esquerda - Lista de Pesquisas -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-[#f5f1e6] p-6 sticky top-32">
          <div class="flex items-center gap-3 mb-6">
            <i class="fas fa-book-open text-[#b85e2b] text-xl"></i>
            <h2 class="text-xl font-bold">Tópicos de Estudo</h2>
          </div>
          
          <div class="space-y-3 max-h-96 overflow-y-auto">
            <?php 
            $pesquisas->data_seek(0);
            while ($row = $pesquisas->fetch_assoc()): 
                $isActive = $selected && $selected['id'] == $row['id'];
                
                // CORREÇÃO: Caminho correto para as imagens
                $imagemPath = $row['imagem'] ?? '';
                $temImagem = !empty($imagemPath) && $imagemPath !== 'NULL' && $imagemPath !== 'null';
                
                // CORREÇÃO: Se o caminho for relativo, apontar para pesquisas-clas/uploads/
                if ($temImagem) {
                    // Se já começar com pesquisas-clas/uploads/, manter como está
                    if (str_starts_with($imagemPath, 'pesquisas-clas/')) {
                        // Já está correto
                    }
                    // Se for apenas o nome do arquivo, adicionar o caminho completo
                    elseif (!str_starts_with($imagemPath, 'http') && !str_starts_with($imagemPath, '/')) {
                        $imagemPath = 'pesquisas-clas/' . $imagemPath;
                    }
                }
            ?>
              <a href="?id=<?= $row['id'] ?>" 
                 class="research-card block p-4 rounded-xl border transition-all <?= $isActive ? 'active-topic border-[#b85e2b]' : 'border-gray-200 hover:border-[#b85e2b] hover:bg-[#fef7ed]' ?>">
                <div class="flex items-start gap-3">
                  <?php if ($temImagem): ?>
                    <img src="<?= htmlspecialchars($imagemPath) ?>" 
                         alt="<?= htmlspecialchars($row['nome']) ?>" 
                         class="w-12 h-12 rounded-lg object-cover flex-shrink-0"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="w-12 h-12 img-placeholder rounded-lg flex items-center justify-center flex-shrink-0 hidden">
                      <i class="fas fa-file-alt text-[#b85e2b] text-sm"></i>
                    </div>
                  <?php else: ?>
                    <div class="w-12 h-12 img-placeholder rounded-lg flex items-center justify-center flex-shrink-0">
                      <i class="fas fa-file-alt text-[#b85e2b] text-sm"></i>
                    </div>
                  <?php endif; ?>
                  
                  <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-sm leading-tight mb-1 <?= $isActive ? 'text-white' : 'text-gray-800' ?>">
                      <?= htmlspecialchars($row['nome']) ?>
                    </h3>
                    <p class="text-xs <?= $isActive ? 'text-gray-200' : 'text-gray-500' ?>">
                      <?= date("d/m/Y", strtotime($row['data'])) ?>
                    </p>
                  </div>
                </div>
              </a>
            <?php endwhile; ?>
            
            <?php if ($pesquisas->num_rows === 0): ?>
              <div class="text-center py-8 text-gray-500">
                <i class="fas fa-inbox text-4xl mb-3 opacity-50"></i>
                <p>Nenhuma pesquisa disponível</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Coluna central - Conteúdo da Pesquisa -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-[#f5f1e6] p-8 content-fade">
          <?php if ($selected): ?>
            <div class="flex justify-between items-start mb-6">
              <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-3">
                  <?= htmlspecialchars($selected['nome']) ?>
                </h1>
                <div class="flex items-center gap-4 text-sm text-gray-500">
                  <div class="flex items-center gap-1">
                    <i class="far fa-calendar"></i>
                    <span>Publicado em <?= date("d/m/Y", strtotime($selected['data'])) ?></span>
                  </div>
                  <?php if (isset($selected['autor']) && !empty($selected['autor'])): ?>
                    <div class="flex items-center gap-1">
                      <i class="far fa-user"></i>
                      <span><?= htmlspecialchars($selected['autor']) ?></span>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
              
              <?php if ($tipoUsuario === 'admin'): ?>
                <form action="pesquisas-clas/excluir_pesquisa.php" method="POST" 
                      onsubmit="return confirm('Tem certeza que deseja excluir esta pesquisa?');">
                  <input type="hidden" name="id" value="<?= $selected['id'] ?>">
                  <button type="submit" 
                          class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
                    <i class="fas fa-trash"></i>
                    Excluir
                  </button>
                </form>
              <?php endif; ?>
            </div>

            <div class="prose max-w-none text-gray-700 leading-relaxed">
              <?= nl2br(htmlspecialchars($selected['conteudo'])) ?>
            </div>

            <?php if (isset($selected['referencias']) && !empty($selected['referencias'])): ?>
              <div class="mt-8 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                  <i class="fas fa-book"></i>
                  Referências
                </h3>
                <div class="text-sm text-gray-600 leading-relaxed">
                  <?= nl2br(htmlspecialchars($selected['referencias'])) ?>
                </div>
              </div>
            <?php endif; ?>

          <?php else: ?>
            <div class="text-center py-16">
              <div class="w-24 h-24 bg-gradient-to-br from-[#f5f1e6] to-[#fde9c7] rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-search text-3xl text-[#b85e2b]"></i>
              </div>
              <h3 class="text-2xl font-bold text-gray-700 mb-3">Bem-vindo aos Estudos</h3>
              <p class="text-gray-500 max-w-md mx-auto">
                Selecione um tópico ao lado para explorar nossos ensinamentos e pesquisas sobre fé e espiritualidade.
              </p>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Coluna direita - Imagem e Informações -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-[#f5f1e6] p-6 sticky top-32">
          <?php 
          // IMAGEM PRINCIPAL
          $imagemPrincipal = $selected['imagem'] ?? '';
          $temImagemPrincipal = !empty($imagemPrincipal) && $imagemPrincipal !== 'NULL' && $imagemPrincipal !== 'null';
          
          if ($temImagemPrincipal) {
              if (str_starts_with($imagemPrincipal, 'pesquisas-clas/')) {
              }

              elseif (!str_starts_with($imagemPrincipal, 'http') && !str_starts_with($imagemPrincipal, '/')) {
                  $imagemPrincipal = 'pesquisas-clas/' . $imagemPrincipal;
              }
          }
          
          if ($selected && $temImagemPrincipal): 
          ?>
            <div class="mb-6">
              <img src="<?= htmlspecialchars($imagemPrincipal) ?>" 
                   alt="<?= htmlspecialchars($selected['nome']) ?>" 
                   class="w-full h-64 object-cover rounded-xl shadow-sm"
                   onerror="this.style.display='none'; document.getElementById('placeholder-imagem').style.display='block';">
            </div>
            <div id="placeholder-imagem" class="hidden text-center py-12 text-gray-500 border-2 border-dashed border-gray-300 rounded-xl">
              <i class="fas fa-image text-4xl mb-3 opacity-50"></i>
              <p class="text-sm">Imagem não carregou</p>
              <p class="text-xs mt-2">Caminho: <?= htmlspecialchars($imagemPrincipal) ?></p>
            </div>
          <?php else: ?>
            <div class="text-center py-12 text-gray-500 border-2 border-dashed border-gray-300 rounded-xl">
              <i class="fas fa-image text-4xl mb-3 opacity-50"></i>
              <p class="text-sm">
                <?= $selected ? 'Nenhuma imagem disponível' : 'Imagem ilustrativa' ?>
              </p>
              <?php if ($selected && $selected['imagem']): ?>
                <p class="text-xs mt-2 text-gray-400">
                  Caminho no banco: <?= htmlspecialchars($selected['imagem']) ?>
                </p>
                <p class="text-xs mt-1 text-gray-400">
                  Caminho corrigido: <?= htmlspecialchars($imagemPrincipal) ?>
                </p>
              <?php endif; ?>
            </div>
          <?php endif; ?>
          
          <!-- Informações Adicionais -->
          <div class="space-y-4">
            <div class="bg-blue-50 rounded-lg p-4">
              <h4 class="font-semibold text-blue-900 mb-2 flex items-center gap-2">
                <i class="fas fa-lightbulb"></i>
                Dica de Estudo
              </h4>
              <p class="text-sm text-blue-700">
                Reserve um momento tranquilo para refletir sobre estes ensinamentos.
              </p>
            </div>
            
            <div class="bg-green-50 rounded-lg p-4">
              <h4 class="font-semibold text-green-900 mb-2 flex items-center gap-2">
                <i class="fas fa-share-alt"></i>
                Compartilhe
              </h4>
              <p class="text-sm text-green-700">
                Converse sobre estes temas com sua comunidade espiritual.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Debug no console
    console.log('Página de pesquisas carregada');
    
    // Verificar se as imagens carregaram
    document.addEventListener('DOMContentLoaded', function() {
        const images = document.querySelectorAll('img');
        images.forEach(img => {
            img.addEventListener('error', function() {
                console.log('Erro ao carregar imagem:', this.src);
                // Tentar carregar caminho alternativo
                const currentSrc = this.src;
                if (!currentSrc.includes('pesquisas-clas/') && !currentSrc.includes('http')) {
                    const newSrc = 'pesquisas-clas/' + currentSrc.split('/').pop();
                    console.log('Tentando caminho alternativo:', newSrc);
                    this.src = newSrc;
                }
            });
            img.addEventListener('load', function() {
                console.log('Imagem carregada com sucesso:', this.src);
            });
        });
    });

    // Smooth scroll para o topo quando selecionar um novo tópico
    document.querySelectorAll('a[href*="id="]').forEach(link => {
      link.addEventListener('click', function() {
        setTimeout(() => {
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }, 100);
      });
    });

    // Destacar pesquisa selecionada
    const currentId = new URLSearchParams(window.location.search).get('id');
    if (currentId) {
      const activeCard = document.querySelector(`a[href*="id=${currentId}"]`);
      if (activeCard) {
        activeCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }
  </script>

</body>
</html>