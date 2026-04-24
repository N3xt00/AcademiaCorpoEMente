<?php
require_once __DIR__ . '/../includes/conexao.php';
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET,POST,DELETE,OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$pdo    = getConexao();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $idAluno  = isset($_GET['aluno'])  ? (int)$_GET['aluno']  : null;
        $idTreino = isset($_GET['treino']) ? (int)$_GET['treino'] : null;
        if ($idAluno) {
            $stmt = $pdo->prepare('
                SELECT at2.idAlunoTreino, t.idTreino, t.nomeTreino, p.nome AS nomeProfessor,
                       at2.vinculadoEm
                FROM aluno_treino at2
                JOIN treino t ON t.idTreino = at2.idTreino
                LEFT JOIN professor p ON p.idProfessor = t.idProfessor
                WHERE at2.idAluno = ? ORDER BY t.nomeTreino');
            $stmt->execute([$idAluno]);
        } elseif ($idTreino) {
            $stmt = $pdo->prepare('
                SELECT at2.idAlunoTreino, a.idAluno, a.nome AS nomeAluno, at2.vinculadoEm
                FROM aluno_treino at2
                JOIN aluno a ON a.idAluno = at2.idAluno
                WHERE at2.idTreino = ? ORDER BY a.nome');
            $stmt->execute([$idTreino]);
        } else {
            jsonResponse(['erro' => 'Informe ?aluno=ID ou ?treino=ID'], 400);
        }
        jsonResponse($stmt->fetchAll());

    case 'POST':
        $d = getBody();
        if (empty($d['idAluno']) || empty($d['idTreino']))
            jsonResponse(['erro' => 'idAluno e idTreino são obrigatórios'], 422);
        try {
            $stmt = $pdo->prepare('INSERT INTO aluno_treino (idAluno, idTreino) VALUES (?,?)');
            $stmt->execute([$d['idAluno'], $d['idTreino']]);
            jsonResponse(['mensagem' => 'Treino vinculado ao aluno!', 'id' => $pdo->lastInsertId()], 201);
        } catch (PDOException $e) {
            jsonResponse(['erro' => 'Vínculo já existe ou dados inválidos.'], 409);
        }

    case 'DELETE':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if (!$id) jsonResponse(['erro' => 'ID necessário'], 400);
        $pdo->prepare('DELETE FROM aluno_treino WHERE idAlunoTreino = ?')->execute([$id]);
        jsonResponse(['mensagem' => 'Vínculo removido!']);

    default:
        jsonResponse(['erro' => 'Método não suportado'], 405);
}
