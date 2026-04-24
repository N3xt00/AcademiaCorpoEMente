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
            $stmt = $pdo->prepare('SELECT * FROM plano WHERE idPlano = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) jsonResponse(['erro' => 'Plano não encontrado'], 404);
            jsonResponse($row);
        }
        $apenasAtivos = isset($_GET['ativos']);
        $sql = 'SELECT * FROM plano' . ($apenasAtivos ? ' WHERE ativo = 1' : '') . ' ORDER BY valor';
        jsonResponse($pdo->query($sql)->fetchAll());

    case 'POST':
        $d = getBody();
        if (empty($d['nome']) || !isset($d['valor']) || empty($d['duracaoMeses']))
            jsonResponse(['erro' => 'Campos obrigatórios: nome, valor, duracaoMeses'], 422);
        $stmt = $pdo->prepare(
            'INSERT INTO plano (nome, valor, duracaoMeses) VALUES (?,?,?)'
        );
        $stmt->execute([$d['nome'], $d['valor'], $d['duracaoMeses']]);
        jsonResponse(['mensagem' => 'Plano cadastrado!', 'id' => $pdo->lastInsertId()], 201);

    case 'PUT':
        if (!$id) jsonResponse(['erro' => 'ID necessário'], 400);
        $d = getBody();
        $stmt = $pdo->prepare(
            'UPDATE plano SET nome=?, valor=?, duracaoMeses=?, ativo=? WHERE idPlano=?'
        );
        $stmt->execute([$d['nome'], $d['valor'], $d['duracaoMeses'], $d['ativo'] ?? 1, $id]);
        jsonResponse(['mensagem' => 'Plano atualizado!']);

    case 'DELETE':
        if (!$id) jsonResponse(['erro' => 'ID necessário'], 400);
        $pdo->prepare('UPDATE plano SET ativo = 0 WHERE idPlano = ?')->execute([$id]);
        jsonResponse(['mensagem' => 'Plano desativado!']);

    default:
        jsonResponse(['erro' => 'Método não suportado'], 405);
}
