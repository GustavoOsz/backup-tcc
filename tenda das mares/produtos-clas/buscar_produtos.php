<?php
// Conexão com o banco de dados
$conn = new mysqli("localhost", "root", "", "tenda");
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$pesquisa = isset($_GET['pesquisa']) ? $conn->real_escape_string($_GET['pesquisa']) : "";
$sql = "SELECT * FROM produto WHERE nome LIKE '%$pesquisa%' ORDER BY id DESC";
$resultado = $conn->query($sql);

if ($resultado->num_rows > 0) {
    while($produto = $resultado->fetch_assoc()) {
        echo '
        <a href="produtos-clas/detalhes.php?id='.$produto['id'].'" class="bg-[#fde9c7] rounded-xl shadow-md p-4 flex flex-col items-center text-center hover:shadow-lg transition no-underline text-[#4f2905]">
            <img src="'.$produto['imagem'].'" alt="'.$produto['nome'].'" class="w-full h-40 object-cover rounded mb-2">
            <h4 class="text-lg font-semibold mb-1">'.$produto['nome'].'</h4>
            <p class="text-sm mb-2">'.$produto['descricao'].'</p>
            <span class="text-[#4f2905] font-bold mb-2">R$ '.number_format($produto['preco'], 2, ',', '.').'</span>
        </a>
        ';
    }
} else {
    echo '<p class="col-span-3 text-center text-gray-500">Nenhum produto encontrado.</p>';
}
?>
