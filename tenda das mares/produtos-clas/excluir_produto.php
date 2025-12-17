<?php
session_start();
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    die("Acesso negado.");
}

$conn = new mysqli("localhost", "root", "", "tenda");
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    die("Produto inválido.");
}

// Excluir produto
$stmt = $conn->prepare("DELETE FROM produto WHERE id = ?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    header("Location: ../produtos.php?msg=Produto+excluído+com+sucesso");
    exit;
} else {
    die("Erro ao excluir produto.");
}
?>
