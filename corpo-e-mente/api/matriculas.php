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
                SELECT m.*, a.nome AS nomeAluno, p.nome AS nomePlano, p.valor
                FROM matricula m
                JOIN aluno a ON a.idAluno = m.idAluno
                JOIN plano  p ON p.idPlano = m.idPlano
                WHERE m.idMatricula = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) jsonResponse(['erro' => 'Matrícula não encontrada'], 404);
            jsonResponse($row);
        }
        $idAluno = isset($_GET['aluno']) ? (int)$_GET['aluno'] : null;
        if ($idAluno) {
            $stmt = $pdo->prepare('
                SELECT m.*, a.nome AS nomeAluno, p.nome AS nomePlano, p.valor
                FROM matricula m
                JOIN aluno a ON a.idAluno = m.idAluno
                JOIN plano  p ON p.idPlano = m.idPlano
                WHERE m.idAluno = ? ORDER BY m.dataInicio DESC');
            $stmt->execute([$idAluno]);
        } else {
            $stmt = $pdo->query('
                SELECT m.*, a.nome AS nomeAluno, p.nome AS nomePlano, p.valor
                FROM matricula m
                JOIN aluno a ON a.idAluno = m.idAluno
                JOIN plano  p ON p.idPlano = m.idPlano
                ORDER BY m.ativo DESC, m.dataInicio DESC');
        }
        jsonResponse($stmt->fetchAll());

    case 'POST':
        $d = getBody();
        if (empty($d['idAluno']) || empty($d['idPlano']) || empty($d['dataInicio']))
            jsonResponse(['erro' => 'Campos obrigatórios: idAluno, idPlano, dataInicio'], 422);

        // Busca duração do plano
        $stmt = $pdo->prepare('SELECT duracaoMeses FROM plano WHERE idPlano = ?');
        $stmt->execute([$d['idPlano']]);
        $plano = $stmt->fetch();
        if (!$plano) jsonResponse(['erro' => 'Plano não encontrado'], 404);

        $dataInicio = new DateTime($d['dataInicio']);
        $dataFim    = (clone $dataInicio)->modify("+{$plano['duracaoMeses']} months");

        try {
            $stmt = $pdo->prepare(
                'INSERT INTO matricula (idAluno, idPlano, dataInicio, dataFim) VALUES (?,?,?,?)'
            );
            $stmt->execute([
                $d['idAluno'], $d['idPlano'],
                $dataInicio->format('Y-m-d'), $dataFim->format('Y-m-d')
            ]);
            jsonResponse(['mensagem' => 'Matrícula realizada com sucesso!', 'id' => $pdo->lastInsertId()], 201);
        } catch (PDOException $e) {
            jsonResponse(['erro' => $e->getMessage()], 409);
        }

    case 'PUT':
        if (!$id) jsonResponse(['erro' => 'ID necessário'], 400);
        $d = getBody();
        // Renovar / desativar
        if (isset($d['ativo'])) {
            $pdo->prepare('UPDATE matricula SET ativo=? WHERE idMatricula=?')->execute([$d['ativo'], $id]);
            jsonResponse(['mensagem' => 'Matrícula atualizada!']);
        }
        jsonResponse(['erro' => 'Nenhum campo para atualizar'], 400);

    case 'DELETE':
        if (!$id) jsonResponse(['erro' => 'ID necessário'], 400);
        $pdo->prepare('DELETE FROM matricula WHERE idMatricula = ?')->execute([$id]);
        jsonResponse(['mensagem' => 'Matrícula removida!']);

    default:
        jsonResponse(['erro' => 'Método não suportado'], 405);
}
