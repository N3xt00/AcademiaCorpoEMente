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
        $idTreino = isset($_GET['treino']) ? (int)$_GET['treino'] : null;
        if (!$idTreino) jsonResponse(['erro' => 'Parâmetro treino é obrigatório'], 400);
        $stmt = $pdo->prepare('
            SELECT it.*, a.nome AS nomeAparelho
            FROM item_treino it
            JOIN aparelho a ON a.idAparelho = it.idAparelho
            WHERE it.idTreino = ?
            ORDER BY it.idItemTreino');
        $stmt->execute([$idTreino]);
        jsonResponse($stmt->fetchAll());

    case 'POST':
        $d = getBody();
        if (empty($d['idTreino']) || empty($d['idAparelho']))
            jsonResponse(['erro' => 'idTreino e idAparelho são obrigatórios'], 422);
        $stmt = $pdo->prepare(
            'INSERT INTO item_treino (idTreino, idAparelho, series, repeticoes, carga) VALUES (?,?,?,?,?)'
        );
        $stmt->execute([
            $d['idTreino'], $d['idAparelho'],
            $d['series'] ?? 3, $d['repeticoes'] ?? 12, $d['carga'] ?? null
        ]);
        jsonResponse(['mensagem' => 'Item adicionado ao treino!', 'id' => $pdo->lastInsertId()], 201);

    case 'PUT':
        if (!$id) jsonResponse(['erro' => 'ID necessário'], 400);
        $d = getBody();
        $pdo->prepare('UPDATE item_treino SET series=?, repeticoes=?, carga=? WHERE idItemTreino=?')
            ->execute([$d['series'] ?? 3, $d['repeticoes'] ?? 12, $d['carga'] ?? null, $id]);
        jsonResponse(['mensagem' => 'Item atualizado!']);

    case 'DELETE':
        if (!$id) jsonResponse(['erro' => 'ID necessário'], 400);
        $pdo->prepare('DELETE FROM item_treino WHERE idItemTreino = ?')->execute([$id]);
        jsonResponse(['mensagem' => 'Item removido!']);

    default:
        jsonResponse(['erro' => 'Método não suportado'], 405);
}
