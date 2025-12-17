<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Sobre Nós - Tenda das Marés</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    .typing {
      border-right: 2px solid #b85e2b;
      white-space: pre-wrap;
      overflow: hidden;
      animation: blink 0.7s infinite;
    }
    @keyframes blink {
      0%, 100% { border-color: #b85e2b; }
      50% { border-color: transparent; }
    }
    .fade-in {
      animation: fadeIn 1s ease-in;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .team-card {
      transition: all 0.3s ease;
    }
    .team-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(184, 94, 43, 0.1);
    }
    .gradient-bg {
      background: linear-gradient(135deg, #b85e2b 0%, #f7b95e 50%, #fbc97f 100%);
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
          <a href="pesquisas.php" class="hover:text-[#b85e2b] transition-colors">Pesquisas</a>
          <a href="sobre.php" class="hover:text-[#b85e2b] font-semibold border-b-2 border-[#b85e2b] pb-1">Sobre nós</a>
          <a href="contato.php" class="hover:text-[#b85e2b] transition-colors">Contato</a>
          <a href="login.php" class="p-2 hover:bg-[#f5f1e6] rounded-full transition-colors">
            <i class="fas fa-user text-[#4f2905] text-lg"></i>
          </a>
        </nav>
      </div>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="gradient-bg py-20 text-white">
    <div class="container mx-auto px-6 text-center">
      <h1 class="text-5xl md:text-6xl font-bold mb-6">Nossa História</h1>
      <p class="text-xl md:text-2xl opacity-90 max-w-3xl mx-auto">
        Conheça a jornada da Tenda das Marés e as pessoas por trás deste sonho
      </p>
    </div>
  </section>

  <!-- Conteúdo Principal -->
  <main class="container mx-auto px-6 py-16">
    
    <!-- História da Loja -->
    <section class="mb-20 fade-in">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        <div>
          <div class="bg-white rounded-2xl shadow-lg p-8 border border-[#f5f1e6]">
            <div class="flex items-center gap-3 mb-6">
              <div class="w-12 h-12 bg-gradient-to-br from-[#b85e2b] to-[#f7b95e] rounded-full flex items-center justify-center">
                <i class="fas fa-store text-white text-lg"></i>
              </div>
              <h2 class="text-3xl font-bold text-gray-900">Nossa Missão</h2>
            </div>
            <p id="historia" class="typing text-lg leading-relaxed text-gray-700 min-h-[120px]"></p>
          </div>
        </div>
        
        <div class="flex justify-center">
          <div class="relative">
            <div class="w-80 h-80 bg-gradient-to-br from-[#fde9c7] to-[#fbc97f] rounded-2xl flex items-center justify-center">
              <i class="fas fa-heart text-[#b85e2b] text-6xl"></i>
            </div>
            <div class="absolute -bottom-6 -right-6 w-24 h-24 bg-[#4f2905] rounded-2xl flex items-center justify-center">
              <i class="fas fa-anchor text-white text-2xl"></i>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Time -->
    <section class="mb-20">
      <div class="text-center mb-16">
        <h2 class="text-4xl md:text-5xl font-bold text-[#4f2905] mb-4">Nosso Time</h2>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto">
          Conheça as pessoas dedicadas que tornam a Tenda das Marés especial
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
        
        <!-- Gustavo -->
        <div class="team-card bg-white rounded-2xl shadow-lg p-8 border border-[#f5f1e6] fade-in">
          <div class="flex items-center gap-4 mb-6">
            <div class="w-16 h-16 bg-gradient-to-br from-[#b85e2b] to-[#f7b95e] rounded-full flex items-center justify-center">
              <i class="fas fa-user text-white text-xl"></i>
            </div>
            <div>
              <h3 class="text-2xl font-bold text-gray-900">Gustavo</h3>
              <p class="text-[#b85e2b] font-medium">Fundador & Espiritualista</p>
            </div>
          </div>
          <p id="pessoal1" class="typing text-gray-700 leading-relaxed min-h-[100px]"></p>
          <div class="mt-6 flex gap-3">
            <div class="bg-[#f5f1e6] px-3 py-1 rounded-full text-sm font-medium text-[#4f2905]">
              <i class="fas fa-gem mr-1"></i> Tradições
            </div>
            <div class="bg-[#f5f1e6] px-3 py-1 rounded-full text-sm font-medium text-[#4f2905]">
              <i class="fas fa-book mr-1"></i> Cultura
            </div>
            <div class="bg-[#f5f1e6] px-3 py-1 rounded-full text-sm font-medium text-[#4f2905]">
              <i class="fas fa-heart mr-1"></i> Espiritualidade
            </div>
          </div>
        </div>

        <!-- Maria -->
        <div class="team-card bg-white rounded-2xl shadow-lg p-8 border border-[#f5f1e6] fade-in">
          <div class="flex items-center gap-4 mb-6">
            <div class="w-16 h-16 bg-gradient-to-br from-[#b85e2b] to-[#f7b95e] rounded-full flex items-center justify-center">
              <i class="fas fa-user text-white text-xl"></i>
            </div>
            <div>
              <h3 class="text-2xl font-bold text-gray-900">Maria Eduarda</h3>
              <p class="text-[#b85e2b] font-medium">Co-fundadora & Visionária</p>
            </div>
          </div>
          <p id="pessoal2" class="typing text-gray-700 leading-relaxed min-h-[100px]"></p>
          <div class="mt-6 flex gap-3">
            <div class="bg-[#f5f1e6] px-3 py-1 rounded-full text-sm font-medium text-[#4f2905]">
              <i class="fas fa-star mr-1"></i> Inovação
            </div>
            <div class="bg-[#f5f1e6] px-3 py-1 rounded-full text-sm font-medium text-[#4f2905]">
              <i class="fas fa-users mr-1"></i> Juventude
            </div>
            <div class="bg-[#f5f1e6] px-3 py-1 rounded-full text-sm font-medium text-[#4f2905]">
              <i class="fas fa-bolt mr-1"></i> Energia
            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- Valores -->
    <section class="bg-white rounded-2xl shadow-lg p-12 border border-[#f5f1e6] fade-in">
      <div class="text-center mb-12">
        <h2 class="text-4xl font-bold text-[#4f2905] mb-4">Nossos Valores</h2>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto">
          Princípios que guiam cada decisão e ação na Tenda das Marés
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="text-center p-6">
          <div class="w-20 h-20 bg-gradient-to-br from-[#fde9c7] to-[#fbc97f] rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-hand-holding-heart text-[#b85e2b] text-2xl"></i>
          </div>
          <h3 class="text-xl font-bold mb-4 text-gray-900">Respeito às Tradições</h3>
          <p class="text-gray-600 leading-relaxed">
            Honramos e preservamos as tradições culturais e espirituais que nos foram passadas.
          </p>
        </div>

        <div class="text-center p-6">
          <div class="w-20 h-20 bg-gradient-to-br from-[#fde9c7] to-[#fbc97f] rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-users text-[#b85e2b] text-2xl"></i>
          </div>
          <h3 class="text-xl font-bold mb-4 text-gray-900">Comunidade</h3>
          <p class="text-gray-600 leading-relaxed">
            Acreditamos no poder da união e no compartilhamento de saberes entre gerações.
          </p>
        </div>

        <div class="text-center p-6">
          <div class="w-20 h-20 bg-gradient-to-br from-[#fde9c7] to-[#fbc97f] rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-seedling text-[#b85e2b] text-2xl"></i>
          </div>
          <h3 class="text-xl font-bold mb-4 text-gray-900">Crescimento</h3>
          <p class="text-gray-600 leading-relaxed">
            Incentivamos o desenvolvimento espiritual e pessoal de todos que nos acompanham.
          </p>
        </div>
      </div>
    </section>

  </main>

  <!-- Call to Action -->
  <section class="bg-gradient-to-r from-[#4f2905] to-[#6d3a0f] py-16 text-white">
    <div class="container mx-auto px-6 text-center">
      <h2 class="text-4xl font-bold mb-6">Faça Parte Desta Jornada</h2>
      <p class="text-xl mb-8 opacity-90 max-w-2xl mx-auto">
        Junte-se à nossa comunidade e descubra um mundo de tradição, espiritualidade e conexão.
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="produtos.php" class="bg-[#b85e2b] hover:bg-[#e07a3f] text-white px-8 py-4 rounded-full font-bold text-lg transition-colors inline-flex items-center gap-3">
          <i class="fas fa-shopping-bag"></i>
          Conhecer Produtos
        </a>
        <a href="contato.php" class="bg-transparent hover:bg-white/10 text-white border border-white px-8 py-4 rounded-full font-bold text-lg transition-colors inline-flex items-center gap-3">
          <i class="fas fa-envelope"></i>
          Entrar em Contato
        </a>
      </div>
    </div>
  </section>

  <!-- Script efeito digitação -->
  <script>
    function typeEffect(elementId, text, speed) {
      let i = 0;
      const element = document.getElementById(elementId);
      element.textContent = '';
      
      function typing() {
        if (i < text.length) {
          element.textContent += text.charAt(i);
          i++;
          setTimeout(typing, speed);
        } else {
          // Remove o cursor quando termina
          setTimeout(() => {
            element.classList.remove('typing');
          }, 1000);
        }
      }
      typing();
    }

    // Textos
    const historiaTxt = "A Tenda das Marés nasceu do sonho de criar um espaço onde tradição e espiritualidade se encontram. Fundada com amor e dedicação, nossa missão é oferecer produtos que fortaleçam a fé e conectem pessoas às suas raízes culturais. Acreditamos que cada item carrega uma história e uma energia especial, capaz de transformar vidas e ambientes.";
    const pessoal1Txt = "Como fundador, dedico minha vida ao estudo das tradições espirituais. Minha paixão é compartilhar conhecimentos que possam guiar pessoas em suas jornadas pessoais. A Tenda das Marés é a materialização desse propósito - um espaço onde cultura, fé e comunidade se encontram em harmonia.";
    const pessoal2Txt = "Como co-fundadora, meu objetivo é mostrar que espiritualidade e tradição podem ser vibrantes e acessíveis para todos, especialmente para os jovens. A Tenda das Marés é mais que uma loja - é um movimento que celebra nossa cultura de forma moderna e acolhedora, provando que fé e contemporaneidade podem caminhar juntas.";

    // Iniciar efeitos com delay
    document.addEventListener('DOMContentLoaded', function() {
      setTimeout(() => typeEffect("historia", historiaTxt, 30), 500);
      setTimeout(() => typeEffect("pessoal1", pessoal1Txt, 30), 900);
      setTimeout(() => typeEffect("pessoal2", pessoal2Txt, 30), 900);
    });
  </script>

</body>
</html>