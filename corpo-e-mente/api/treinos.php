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
            $stmt = $pdo->prepare('
                SELECT t.*, p.nome AS nomeProfessor
                FROM treino t LEFT JOIN professor p ON p.idProfessor = t.idProfessor
                WHERE t.idTreino = ?');
            $stmt->execute([$id]);
            $treino = $stmt->fetch();
            if (!$treino) jsonResponse(['erro' => 'Treino não encontrado'], 404);

            // Itens do treino
            $stmt2 = $pdo->prepare('
                SELECT it.*, a.nome AS nomeAparelho
                FROM item_treino it JOIN aparelho a ON a.idAparelho = it.idAparelho
                WHERE it.idTreino = ?');
            $stmt2->execute([$id]);
            $treino['itens'] = $stmt2->fetchAll();

            // Alunos vinculados
            $stmt3 = $pdo->prepare('
                SELECT al.idAluno, al.nome FROM aluno_treino at2
                JOIN aluno al ON al.idAluno = at2.idAluno
                WHERE at2.idTreino = ?');
            $stmt3->execute([$id]);
            $treino['alunos'] = $stmt3->fetchAll();

            jsonResponse($treino);
        }
        $idProfessor = isset($_GET['professor']) ? (int)$_GET['professor'] : null;
        if ($idProfessor) {
            $stmt = $pdo->prepare('
                SELECT t.*, p.nome AS nomeProfessor,
                       COUNT(DISTINCT at2.idAluno) AS totalAlunos
                FROM treino t
                LEFT JOIN professor p ON p.idProfessor = t.idProfessor
                LEFT JOIN aluno_treino at2 ON at2.idTreino = t.idTreino
                WHERE t.idProfessor = ?
                GROUP BY t.idTreino ORDER BY t.nomeTreino');
            $stmt->execute([$idProfessor]);
        } else {
            $stmt = $pdo->query('
                SELECT t.*, p.nome AS nomeProfessor,
                       COUNT(DISTINCT at2.idAluno) AS totalAlunos
                FROM treino t
                LEFT JOIN professor p ON p.idProfessor = t.idProfessor
                LEFT JOIN aluno_treino at2 ON at2.idTreino = t.idTreino
                GROUP BY t.idTreino ORDER BY t.nomeTreino');
        }
        jsonResponse($stmt->fetchAll());

    case 'POST':
        $d = getBody();
        if (empty($d['nomeTreino'])) jsonResponse(['erro' => 'nomeTreino é obrigatório'], 422);
        $stmt = $pdo->prepare(
            'INSERT INTO treino (nomeTreino, idProfessor, dataCriacao) VALUES (?,?,?)'
        );
        $stmt->execute([
            $d['nomeTreino'],
            $d['idProfessor'] ?? null,
            $d['dataCriacao'] ?? date('Y-m-d')
        ]);
        jsonResponse(['mensagem' => 'Treino criado!', 'id' => $pdo->lastInsertId()], 201);

    case 'PUT':
        if (!$id) jsonResponse(['erro' => 'ID necessário'], 400);
        $d = getBody();
        $pdo->prepare('UPDATE treino SET nomeTreino=?, idProfessor=? WHERE idTreino=?')
            ->execute([$d['nomeTreino'], $d['idProfessor'] ?? null, $id]);
        jsonResponse(['mensagem' => 'Treino atualizado!']);

    case 'DELETE':
        if (!$id) jsonResponse(['erro' => 'ID necessário'], 400);
        $pdo->prepare('DELETE FROM treino WHERE idTreino = ?')->execute([$id]);
        jsonResponse(['mensagem' => 'Treino removido!']);

    default:
        jsonResponse(['erro' => 'Método não suportado'], 405);
}
