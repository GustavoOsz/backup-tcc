<?php
session_start();
$conn = new mysqli("localhost", "root", "", "tenda");

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Verifica se é admin
$usuario_id = $_SESSION['usuario_id'] ?? 0;
$sqlTipo = "SELECT tipo FROM usuarios WHERE id = $usuario_id";
$resTipo = $conn->query($sqlTipo);
$tipoUsuario = $resTipo->fetch_assoc()['tipo'] ?? '';

if ($tipoUsuario !== 'admin') {
    die("Acesso negado.");
}

// Verifica se veio o id
if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM pesquisas WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// Redireciona de volta para a lista de pesquisas
header("Location: ../pesquisas.php");
exit;
?>
