<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Contato - Tenda das Marés</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    .contact-card {
      transition: all 0.3s ease;
    }
    .contact-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(184, 94, 43, 0.1);
    }
    .gradient-bg {
      background: linear-gradient(135deg, #b85e2b 0%, #f7b95e 50%, #fbc97f 100%);
    }
    .social-icon {
      transition: all 0.3s ease;
    }
    .social-icon:hover {
      transform: scale(1.1);
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
          <a href="sobre.php" class="hover:text-[#b85e2b] transition-colors">Sobre nós</a>
          <a href="contato.php" class="hover:text-[#b85e2b] font-semibold border-b-2 border-[#b85e2b] pb-1">Contato</a>
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
      <h1 class="text-5xl md:text-6xl font-bold mb-6">Entre em Contato</h1>
      <p class="text-xl md:text-2xl opacity-90 max-w-3xl mx-auto">
        Estamos aqui para responder suas dúvidas, receber suas encomendas e conectar com você
      </p>
    </div>
  </section>

  <!-- Conteúdo Principal -->
  <main class="container mx-auto px-6 py-16">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">

      <!-- Mídias Sociais -->
      <div class="contact-card bg-white rounded-2xl shadow-lg p-8 border border-[#f5f1e6] lg:col-span-2">
        <div class="flex items-center gap-3 mb-8">
          <div class="w-12 h-12 bg-gradient-to-br from-[#b85e2b] to-[#f7b95e] rounded-full flex items-center justify-center">
            <i class="fas fa-share-alt text-white text-lg"></i>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Nossas Redes Sociais</h2>
        </div>
        
        <p class="text-gray-600 mb-8 text-lg leading-relaxed">
          Siga nossas redes para ficar por dentro das novidades, inspirações e conteúdos especiais sobre espiritualidade e tradições.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Gustavo -->
          <div class="bg-[#fef7ed] rounded-xl p-6 border border-[#fde9c7]">
            <div class="flex items-center gap-4 mb-4">
              <div class="w-16 h-16 bg-gradient-to-br from-[#b85e2b] to-[#f7b95e] rounded-full flex items-center justify-center">
                <i class="fas fa-user text-white text-xl"></i>
              </div>
              <div>
                <h3 class="text-xl font-bold text-gray-900">Gustavo</h3>
                <p class="text-[#b85e2b] font-medium">Fundador</p>
              </div>
            </div>
            <a href="https://instagram.com/https.m4d" target="_blank" 
               class="social-icon bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-3 rounded-full font-semibold inline-flex items-center gap-3 w-full justify-center transition-all hover:shadow-lg">
              <i class="fab fa-instagram text-lg"></i>
              @https.m4d
            </a>
          </div>

          <!-- Maria -->
          <div class="bg-[#fef7ed] rounded-xl p-6 border border-[#fde9c7]">
            <div class="flex items-center gap-4 mb-4">
              <div class="w-16 h-16 bg-gradient-to-br from-[#b85e2b] to-[#f7b95e] rounded-full flex items-center justify-center">
                <i class="fas fa-user text-white text-xl"></i>
              </div>
              <div>
                <h3 class="text-xl font-bold text-gray-900">Maria Eduarda</h3>
                <p class="text-[#b85e2b] font-medium">Co-fundadora</p>
              </div>
            </div>
            <a href="https://instagram.com/guzzz.osz" target="_blank" 
               class="social-icon bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-3 rounded-full font-semibold inline-flex items-center gap-3 w-full justify-center transition-all hover:shadow-lg">
              <i class="fab fa-instagram text-lg"></i>
              @guzzz.osz
            </a>
          </div>
        </div>

        <div class="mt-8 p-4 bg-blue-50 rounded-lg border border-blue-200">
          <div class="flex items-center gap-3">
            <i class="fas fa-info-circle text-blue-500 text-xl"></i>
            <p class="text-blue-700 text-sm">
              <strong>Dica:</strong> Envie uma mensagem direta para tirar dúvidas sobre produtos e espiritualidade.
            </p>
          </div>
        </div>
      </div>

      <!-- Encomendas e WhatsApp -->
      <div class="space-y-8">
        <!-- Encomendas -->
        <div class="contact-card bg-white rounded-2xl shadow-lg p-8 border border-[#f5f1e6]">
          <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 bg-gradient-to-br from-[#b85e2b] to-[#f7b95e] rounded-full flex items-center justify-center">
              <i class="fas fa-shopping-bag text-white text-lg"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Encomendas</h2>
          </div>
          
          <p class="text-gray-600 mb-6 leading-relaxed">
            Faça seu pedido personalizado diretamente pelo WhatsApp. Teremos prazer em atendê-lo!
          </p>

          <a href="https://wa.me/5541999369485" target="_blank" 
             class="social-icon bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-4 rounded-full font-bold text-lg inline-flex items-center gap-3 w-full justify-center transition-all hover:shadow-lg mb-4">
            <i class="fab fa-whatsapp text-xl"></i>
            Fazer Encomenda
          </a>

          <div class="text-center">
            <p class="text-sm text-gray-500">
              <i class="fas fa-clock mr-1"></i>
              Atendimento: Segunda a Sábado, 9h às 18h
            </p>
          </div>
        </div>

        <!-- Informações de Contato -->
        <div class="contact-card bg-white rounded-2xl shadow-lg p-8 border border-[#f5f1e6]">
          <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 bg-gradient-to-br from-[#b85e2b] to-[#f7b95e] rounded-full flex items-center justify-center">
              <i class="fas fa-address-card text-white text-lg"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Informações</h2>
          </div>

          <div class="space-y-4">
            <div class="flex items-center gap-4 p-3 bg-[#fef7ed] rounded-lg">
              <div class="w-10 h-10 bg-[#fde9c7] rounded-full flex items-center justify-center">
                <i class="fas fa-phone text-[#b85e2b]"></i>
              </div>
              <div>
                <p class="text-sm text-gray-500">Telefone/WhatsApp</p>
                <p class="font-semibold text-gray-900">(41) 99936-9485</p>
              </div>
            </div>

            <div class="flex items-center gap-4 p-3 bg-[#fef7ed] rounded-lg">
              <div class="w-10 h-10 bg-[#fde9c7] rounded-full flex items-center justify-center">
                <i class="fas fa-envelope text-[#b85e2b]"></i>
              </div>
              <div>
                <p class="text-sm text-gray-500">E-mail</p>
                <p class="font-semibold text-gray-900">contato@tendadasmares.com</p>
              </div>
            </div>

            <div class="flex items-center gap-4 p-3 bg-[#fef7ed] rounded-lg">
              <div class="w-10 h-10 bg-[#fde9c7] rounded-full flex items-center justify-center">
                <i class="fas fa-map-marker-alt text-[#b85e2b]"></i>
              </div>
              <div>
                <p class="text-sm text-gray-500">Localização</p>
                <p class="font-semibold text-gray-900">Curitiba, PR</p>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- FAQ Rápido -->
    <div class="max-w-4xl mx-auto mt-16">
      <div class="bg-white rounded-2xl shadow-lg p-8 border border-[#f5f1e6]">
        <div class="text-center mb-8">
          <h2 class="text-3xl font-bold text-gray-900 mb-4">Perguntas Frequentes</h2>
          <p class="text-gray-600">Tire suas dúvidas rapidamente</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="bg-[#fef7ed] rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-2 flex items-center gap-2">
              <i class="fas fa-shipping-fast text-[#b85e2b]"></i>
              Qual o prazo de entrega?
            </h3>
            <p class="text-gray-600 text-sm">Entregas em até 7 dias úteis para Grande Curitiba.</p>
          </div>

          <div class="bg-[#fef7ed] rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-2 flex items-center gap-2">
              <i class="fas fa-credit-card text-[#b85e2b]"></i>
              Quais as formas de pagamento?
            </h3>
            <p class="text-gray-600 text-sm">Cartão, PIX e transferência bancária.</p>
          </div>

          <div class="bg-[#fef7ed] rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-2 flex items-center gap-2">
              <i class="fas fa-box text-[#b85e2b]"></i>
              Fazem entregas para todo Brasil?
            </h3>
            <p class="text-gray-600 text-sm">Sim, enviamos para todo o país via correios.</p>
          </div>

          <div class="bg-[#fef7ed] rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-2 flex items-center gap-2">
              <i class="fas fa-gem text-[#b85e2b]"></i>
              Produtos personalizados?
            </h3>
            <p class="text-gray-600 text-sm">Sim, fazemos encomendas especiais sob medida.</p>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-[#f5f1e6] py-12 mt-16">
    <div class="container mx-auto px-6 text-center">
      <div class="flex items-center justify-center gap-3 mb-4">
        <img src="img/logo.png" alt="Logo Tenda das Marés" class="h-10">
        <span class="text-2xl font-bold text-[#4f2905]">Tenda das Marés</span>
      </div>
      <p class="text-gray-600 mb-4">Sua loja de artigos religiosos e espirituais</p>
      <p class="text-gray-500 text-sm">
        &copy; <?= date("Y") ?> Tenda das Marés. Todos os direitos reservados.
      </p>
    </div>
  </footer>

</body>
</html>