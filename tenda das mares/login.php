<?php
session_start();
$conn = new mysqli("localhost", "root", "", "tenda");

// Se usuário está logado, redireciona para o perfil
if (isset($_SESSION['usuario_id'])) {
    header('Location: perfil.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_tipo'] = $usuario['tipo'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_nome'] = $usuario['nome'];

        header("Location: index.php");
        exit;
    } else {
        $erro = "Email ou senha incorretos!";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar'])) {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $endereco = $_POST['endereco'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];
    $cep = $_POST['cep'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    // Verifica se o email já existe
    $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $stmt_check->store_result();
    
    if ($stmt_check->num_rows > 0) {
        $erro = "Este email já está cadastrado. Tente fazer login.";
    } else {
        $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, telefone, endereco, cidade, estado, cep, senha, tipo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'user')"); 
        $stmt->bind_param("ssssssss", $nome, $email, $telefone, $endereco, $cidade, $estado, $cep, $senha);

        if ($stmt->execute()) {
            $sucesso = "Usuário cadastrado com sucesso! Faça login para continuar.";
        } else {
            $erro = "Erro ao cadastrar. Tente novamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Login - Tenda das Marés</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    .gradient-bg {
      background: linear-gradient(135deg, #b85e2b 0%, #f7b95e 50%, #fbc97f 100%);
    }
    .login-card {
      transition: all 0.3s ease;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
    }
    .login-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 40px rgba(184, 94, 43, 0.15);
    }
    .input-field {
      transition: all 0.3s ease;
      border: 2px solid #f5f1e6;
    }
    .input-field:focus {
      border-color: #b85e2b;
      box-shadow: 0 0 0 3px rgba(184, 94, 43, 0.1);
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
    .toast {
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 16px 24px;
      border-radius: 12px;
      color: white;
      font-weight: 500;
      z-index: 9999;
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
      animation: slideIn 0.5s, slideOut 0.5s 2.5s;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .toast.success { 
      background: linear-gradient(135deg, #4CAF50, #45a049);
    }
    .toast.error { 
      background: linear-gradient(135deg, #e53935, #d32f2f);
    }
    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateX(100px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }
    @keyframes slideOut {
      from {
        opacity: 1;
        transform: translateX(0);
      }
      to {
        opacity: 0;
        transform: translateX(100px);
      }
    }
    .password-toggle {
      cursor: pointer;
      transition: color 0.3s ease;
    }
    .password-toggle:hover {
      color: #b85e2b;
    }
    .form-step {
      display: none;
    }
    .form-step.active {
      display: block;
      animation: fadeIn 0.5s ease;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .step-indicator {
      display: flex;
      justify-content: center;
      gap: 8px;
      margin-bottom: 24px;
    }
    .step-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #e5e7eb;
      transition: all 0.3s ease;
    }
    .step-dot.active {
      background: #b85e2b;
      transform: scale(1.2);
    }
  </style>
</head>
<body class="bg-gray-50 min-h-screen">

  <!-- Header -->
  <header class="bg-white shadow-sm">
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
          <a href="login.php" class="p-2 bg-[#f5f1e6] rounded-full transition-colors">
            <i class="fas fa-user text-[#b85e2b] text-lg"></i>
          </a>
        </nav>
      </div>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="gradient-bg py-12 text-white">
    <div class="container mx-auto px-6 text-center">
      <h1 class="text-4xl md:text-5xl font-bold mb-4">Bem-vindo de Volta</h1>
      <p class="text-xl opacity-90">Acesse sua conta ou crie uma nova para uma experiência personalizada</p>
    </div>
  </section>

  <!-- Conteúdo Principal -->
  <main class="container mx-auto px-6 py-12">
    <div class="grid lg:grid-cols-2 gap-8 max-w-6xl mx-auto">
      
      <!-- Login -->
      <div class="login-card rounded-2xl shadow-xl p-8 border border-[#f5f1e6]">
        <div class="flex items-center gap-3 mb-8">
          <div class="w-12 h-12 bg-gradient-to-br from-[#b85e2b] to-[#f7b95e] rounded-full flex items-center justify-center">
            <i class="fas fa-sign-in-alt text-white text-lg"></i>
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Fazer Login</h2>
            <p class="text-gray-600">Entre na sua conta existente</p>
          </div>
        </div>
        
        <form method="POST" class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              <i class="fas fa-envelope mr-2 text-[#b85e2b]"></i>
              Email
            </label>
            <input type="email" name="email" placeholder="seu@email.com" required
                class="input-field w-full px-4 py-3 rounded-xl bg-white focus:outline-none transition-all">
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              <i class="fas fa-lock mr-2 text-[#b85e2b]"></i>
              Senha
            </label>
            <div class="relative">
              <input type="password" name="senha" id="loginPassword" placeholder="Sua senha" required
                  class="input-field w-full px-4 py-3 rounded-xl bg-white focus:outline-none transition-all pr-12">
              <span class="password-toggle absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400"
                    onclick="togglePassword('loginPassword', this)">
                <i class="fas fa-eye"></i>
              </span>
            </div>
          </div>
          
          <button type="submit" name="login"
              class="btn-primary w-full text-white py-3 rounded-xl font-bold text-lg transition-all">
            <i class="fas fa-sign-in-alt mr-2"></i>
            Entrar na Conta
          </button>
        </form>

        <div class="mt-6 p-4 bg-blue-50 rounded-xl border border-blue-200">
          <div class="flex items-start gap-3">
            <i class="fas fa-info-circle text-blue-500 text-lg mt-0.5"></i>
            <div>
              <p class="text-blue-700 font-medium text-sm">Benefícios do login:</p>
              <ul class="text-blue-600 text-sm mt-1 space-y-1">
                <li>• Acompanhe seus pedidos</li>
                <li>• Salve seus produtos favoritos</li>
                <li>• Receba ofertas exclusivas</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Cadastro -->
      <div class="login-card rounded-2xl shadow-xl p-8 border border-[#f5f1e6]">
        <div class="flex items-center gap-3 mb-8">
          <div class="w-12 h-12 bg-gradient-to-br from-[#4f2905] to-[#6d3a0f] rounded-full flex items-center justify-center">
            <i class="fas fa-user-plus text-white text-lg"></i>
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Criar Conta</h2>
            <p class="text-gray-600">Junte-se à nossa comunidade</p>
          </div>
        </div>
        
        <!-- Indicador de Passos -->
        <div class="step-indicator">
          <div class="step-dot active" data-step="1"></div>
          <div class="step-dot" data-step="2"></div>
          <div class="step-dot" data-step="3"></div>
        </div>
        
        <form method="POST" id="cadastroForm" class="space-y-6">
          
          <!-- Passo 1: Informações Pessoais -->
          <div class="form-step active" id="step1">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
              <i class="fas fa-user text-[#b85e2b]"></i>
              Informações Pessoais
            </h3>
            
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-user-circle mr-2 text-[#b85e2b]"></i>
                  Nome Completo *
                </label>
                <input type="text" name="nome" placeholder="Seu nome completo" required
                    class="input-field w-full px-4 py-3 rounded-xl bg-white focus:outline-none transition-all">
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-envelope mr-2 text-[#b85e2b]"></i>
                  Email *
                </label>
                <input type="email" name="email" placeholder="seu@email.com" required
                    class="input-field w-full px-4 py-3 rounded-xl bg-white focus:outline-none transition-all">
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-phone mr-2 text-[#b85e2b]"></i>
                  Telefone *
                </label>
                <input type="tel" name="telefone" placeholder="(11) 99999-9999" required
                    class="input-field w-full px-4 py-3 rounded-xl bg-white focus:outline-none transition-all">
              </div>
            </div>
            
            <button type="button" onclick="nextStep(2)" 
                    class="btn-secondary w-full text-white py-3 rounded-xl font-bold text-lg transition-all mt-6">
              <i class="fas fa-arrow-right mr-2"></i>
              Continuar
            </button>
          </div>

          <!-- Passo 2: Endereço -->
          <div class="form-step" id="step2">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
              <i class="fas fa-home text-[#b85e2b]"></i>
              Endereço
            </h3>
            
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-map-marker-alt mr-2 text-[#b85e2b]"></i>
                  Endereço Completo *
                </label>
                <input type="text" name="endereco" placeholder="Rua, número, bairro" required
                    class="input-field w-full px-4 py-3 rounded-xl bg-white focus:outline-none transition-all">
              </div>
              
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Cidade *
                  </label>
                  <input type="text" name="cidade" placeholder="Sua cidade" required
                      class="input-field w-full px-4 py-3 rounded-xl bg-white focus:outline-none transition-all">
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Estado *
                  </label>
                  <select name="estado" required
                      class="input-field w-full px-4 py-3 rounded-xl bg-white focus:outline-none transition-all">
                    <option value="">Selecione</option>
                    <option value="AC">Acre</option>
                    <option value="AL">Alagoas</option>
                    <option value="AP">Amapá</option>
                    <option value="AM">Amazonas</option>
                    <option value="BA">Bahia</option>
                    <option value="CE">Ceará</option>
                    <option value="DF">Distrito Federal</option>
                    <option value="ES">Espírito Santo</option>
                    <option value="GO">Goiás</option>
                    <option value="MA">Maranhão</option>
                    <option value="MT">Mato Grosso</option>
                    <option value="MS">Mato Grosso do Sul</option>
                    <option value="MG">Minas Gerais</option>
                    <option value="PA">Pará</option>
                    <option value="PB">Paraíba</option>
                    <option value="PR">Paraná</option>
                    <option value="PE">Pernambuco</option>
                    <option value="PI">Piauí</option>
                    <option value="RJ">Rio de Janeiro</option>
                    <option value="RN">Rio Grande do Norte</option>
                    <option value="RS">Rio Grande do Sul</option>
                    <option value="RO">Rondônia</option>
                    <option value="RR">Roraima</option>
                    <option value="SC">Santa Catarina</option>
                    <option value="SP">São Paulo</option>
                    <option value="SE">Sergipe</option>
                    <option value="TO">Tocantins</option>
                  </select>
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-mail-bulk mr-2 text-[#b85e2b]"></i>
                  CEP *
                </label>
                <input type="text" name="cep" placeholder="00000-000" required
                    class="input-field w-full px-4 py-3 rounded-xl bg-white focus:outline-none transition-all">
              </div>
            </div>
            
            <div class="flex gap-4 mt-6">
              <button type="button" onclick="prevStep(1)" 
                      class="flex-1 bg-gray-500 text-white py-3 rounded-xl font-bold text-lg transition-all hover:bg-gray-600">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar
              </button>
              <button type="button" onclick="nextStep(3)" 
                      class="flex-1 btn-secondary text-white py-3 rounded-xl font-bold text-lg transition-all">
                <i class="fas fa-arrow-right mr-2"></i>
                Continuar
              </button>
            </div>
          </div>

          <!-- Passo 3: Senha -->
          <div class="form-step" id="step3">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
              <i class="fas fa-lock text-[#b85e2b]"></i>
              Segurança da Conta
            </h3>
            
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-lock mr-2 text-[#b85e2b]"></i>
                  Senha *
                </label>
                <div class="relative">
                  <input type="password" name="senha" id="registerPassword" placeholder="Crie uma senha segura" required
                      class="input-field w-full px-4 py-3 rounded-xl bg-white focus:outline-none transition-all pr-12">
                  <span class="password-toggle absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400"
                        onclick="togglePassword('registerPassword', this)">
                    <i class="fas fa-eye"></i>
                  </span>
                </div>
                <p class="text-xs text-gray-500 mt-2">Use pelo menos 6 caracteres</p>
              </div>
              
              <div class="p-4 bg-amber-50 rounded-xl border border-amber-200">
                <div class="flex items-start gap-3">
                  <i class="fas fa-shield-alt text-amber-500 text-lg mt-0.5"></i>
                  <div>
                    <p class="text-amber-700 font-medium text-sm">Sua segurança é importante:</p>
                    <ul class="text-amber-600 text-sm mt-1 space-y-1">
                      <li>• Dados protegidos e criptografados</li>
                      <li>• Não compartilhamos suas informações</li>
                      <li>• Ambiente 100% seguro</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="flex gap-4 mt-6">
              <button type="button" onclick="prevStep(2)" 
                      class="flex-1 bg-gray-500 text-white py-3 rounded-xl font-bold text-lg transition-all hover:bg-gray-600">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar
              </button>
              <button type="submit" name="cadastrar"
                      class="flex-1 btn-secondary text-white py-3 rounded-xl font-bold text-lg transition-all">
                <i class="fas fa-user-plus mr-2"></i>
                Criar Conta
              </button>
            </div>
          </div>
        </form>
      </div>

    </div>
  </main>

  <!-- Mensagem Toast -->
  <?php if (isset($sucesso)): ?>
      <div class="toast success">
        <i class="fas fa-check-circle text-white"></i>
        <?= $sucesso ?>
      </div>
      <script>
          setTimeout(() => {
              const toast = document.querySelector('.toast');
              if (toast) toast.style.display = 'none';
          }, 3000);
      </script>
  <?php endif; ?>

  <?php if (isset($erro)): ?>
      <div class="toast error">
        <i class="fas fa-exclamation-triangle text-white"></i>
        <?= $erro ?>
      </div>
      <script>
          setTimeout(() => {
              const toast = document.querySelector('.toast');
              if (toast) toast.style.display = 'none';
          }, 3000);
      </script>
  <?php endif; ?>

  <script>
    // Controle do formulário multi-step
    let currentStep = 1;

    function nextStep(step) {
      // Valida campos obrigatórios do passo atual
      const currentStepElement = document.getElementById(`step${currentStep}`);
      const inputs = currentStepElement.querySelectorAll('input[required], select[required]');
      let valid = true;

      inputs.forEach(input => {
        if (!input.value.trim()) {
          valid = false;
          input.classList.add('border-red-500');
        } else {
          input.classList.remove('border-red-500');
        }
      });

      if (!valid) {
        alert('Por favor, preencha todos os campos obrigatórios antes de continuar.');
        return;
      }

      // Avança para o próximo passo
      document.getElementById(`step${currentStep}`).classList.remove('active');
      document.getElementById(`step${step}`).classList.add('active');
      
      // Atualiza indicador de passos
      document.querySelectorAll('.step-dot').forEach(dot => {
        dot.classList.remove('active');
      });
      document.querySelector(`.step-dot[data-step="${step}"]`).classList.add('active');
      
      currentStep = step;
    }

    function prevStep(step) {
      document.getElementById(`step${currentStep}`).classList.remove('active');
      document.getElementById(`step${step}`).classList.add('active');
      
      document.querySelectorAll('.step-dot').forEach(dot => {
        dot.classList.remove('active');
      });
      document.querySelector(`.step-dot[data-step="${step}"]`).classList.add('active');
      
      currentStep = step;
    }

    // Toggle password visibility
    function togglePassword(inputId, toggleElement) {
      const input = document.getElementById(inputId);
      const icon = toggleElement.querySelector('i');
      
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    }

    // Formatação do telefone
    document.addEventListener('DOMContentLoaded', function() {
      const telefoneInput = document.querySelector('input[name="telefone"]');
      const cepInput = document.querySelector('input[name="cep"]');

      telefoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 11) value = value.substring(0, 11);
        
        if (value.length > 10) {
          value = value.replace(/^(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        } else if (value.length > 6) {
          value = value.replace(/^(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
        } else if (value.length > 2) {
          value = value.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
        } else if (value.length > 0) {
          value = value.replace(/^(\d{0,2})/, '($1');
        }
        
        e.target.value = value;
      });

      cepInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 8) value = value.substring(0, 8);
        
        if (value.length > 5) {
          value = value.replace(/^(\d{5})(\d{0,3})/, '$1-$2');
        }
        
        e.target.value = value;
      });
    });
  </script>

</body>
</html>