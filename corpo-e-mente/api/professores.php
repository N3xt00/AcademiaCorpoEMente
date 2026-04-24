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
            $stmt = $pdo->prepare('SELECT * FROM professor WHERE idProfessor = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) jsonResponse(['erro' => 'Professor não encontrado'], 404);
            jsonResponse($row);
        }
        jsonResponse($pdo->query('SELECT * FROM professor ORDER BY nome')->fetchAll());

    case 'POST':
        $d = getBody();
        if (empty($d['nome'])) jsonResponse(['erro' => 'Nome é obrigatório'], 422);
        $stmt = $pdo->prepare('INSERT INTO professor (nome) VALUES (?)');
        $stmt->execute([$d['nome']]);
        jsonResponse(['mensagem' => 'Professor cadastrado!', 'id' => $pdo->lastInsertId()], 201);

    case 'PUT':
        if (!$id) jsonResponse(['erro' => 'ID necessário'], 400);
        $d = getBody();
        $pdo->prepare('UPDATE professor SET nome=? WHERE idProfessor=?')->execute([$d['nome'], $id]);
        jsonResponse(['mensagem' => 'Professor atualizado!']);

    case 'DELETE':
        if (!$id) jsonResponse(['erro' => 'ID necessário'], 400);
        $pdo->prepare('DELETE FROM professor WHERE idProfessor = ?')->execute([$id]);
        jsonResponse(['mensagem' => 'Professor removido!']);

    default:
        jsonResponse(['erro' => 'Método não suportado'], 405);
}
