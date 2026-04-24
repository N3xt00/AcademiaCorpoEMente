<?php
require_once __DIR__ . '/../includes/conexao.php';
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$pdo    = getConexao();
$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch ($method) {
    case 'GET':
        if ($id) {
            $stmt = $pdo->prepare('SELECT * FROM aparelho WHERE idAparelho = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) jsonResponse(['erro' => 'Aparelho não encontrado'], 404);
            jsonResponse($row);
        }
        jsonResponse($pdo->query('SELECT * FROM aparelho ORDER BY nome')->fetchAll());

    case 'POST':
        $d = getBody();
        if (empty($d['nome'])) jsonResponse(['erro' => 'Nome é obrigatório'], 422);
        $stmt = $pdo->prepare('INSERT INTO aparelho (nome, situacaoConservacao) VALUES (?,?)');
        $stmt->execute([$d['nome'], $d['situacaoConservacao'] ?? 'Bom']);
        jsonResponse(['mensagem' => 'Aparelho cadastrado!', 'id' => $pdo->lastInsertId()], 201);

    case 'PUT':
        if (!$id) jsonResponse(['erro' => 'ID necessário'], 400);
        $d = getBody();
        $stmt = $pdo->prepare('UPDATE aparelho SET nome=?, situacaoConservacao=? WHERE idAparelho=?');
        $stmt->execute([$d['nome'], $d['situacaoConservacao'] ?? 'Bom', $id]);
        jsonResponse(['mensagem' => 'Aparelho atualizado!']);

    case 'DELETE':
        if (!$id) jsonResponse(['erro' => 'ID necessário'], 400);
        $pdo->prepare('DELETE FROM aparelho WHERE idAparelho = ?')->execute([$id]);
        jsonResponse(['mensagem' => 'Aparelho removido!']);

    default:
        jsonResponse(['erro' => 'Método não suportado'], 405);
}
