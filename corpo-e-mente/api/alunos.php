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
    // ----- LISTAR / BUSCAR -----
    case 'GET':
        if ($id) {
            $stmt = $pdo->prepare('SELECT * FROM aluno WHERE idAluno = ?');
            $stmt->execute([$id]);
            $aluno = $stmt->fetch();
            if (!$aluno) jsonResponse(['erro' => 'Aluno não encontrado'], 404);
            jsonResponse($aluno);
        }
        $search = $_GET['search'] ?? '';
        if ($search) {
            $stmt = $pdo->prepare('SELECT * FROM aluno WHERE nome LIKE ? OR cpf LIKE ? ORDER BY nome');
            $like = "%$search%";
            $stmt->execute([$like, $like]);
        } else {
            $stmt = $pdo->query('SELECT * FROM aluno ORDER BY nome');
        }
        jsonResponse($stmt->fetchAll());

    // ----- CRIAR -----
    case 'POST':
        $d = getBody();
        if (empty($d['nome']) || empty($d['cpf']) || empty($d['dataNascimento']))
            jsonResponse(['erro' => 'Campos obrigatórios: nome, cpf, dataNascimento'], 422);
        $stmt = $pdo->prepare(
            'INSERT INTO aluno (nome, cpf, dataNascimento, telefone) VALUES (?,?,?,?)'
        );
        $stmt->execute([$d['nome'], $d['cpf'], $d['dataNascimento'], $d['telefone'] ?? null]);
        jsonResponse(['mensagem' => 'Aluno cadastrado com sucesso!', 'id' => $pdo->lastInsertId()], 201);

    // ----- ATUALIZAR -----
    case 'PUT':
        if (!$id) jsonResponse(['erro' => 'ID necessário'], 400);
        $d = getBody();
        $stmt = $pdo->prepare(
            'UPDATE aluno SET nome=?, cpf=?, dataNascimento=?, telefone=? WHERE idAluno=?'
        );
        $stmt->execute([$d['nome'], $d['cpf'], $d['dataNascimento'], $d['telefone'] ?? null, $id]);
        jsonResponse(['mensagem' => 'Aluno atualizado com sucesso!']);

    // ----- DELETAR -----
    case 'DELETE':
        if (!$id) jsonResponse(['erro' => 'ID necessário'], 400);
        $pdo->prepare('DELETE FROM aluno WHERE idAluno = ?')->execute([$id]);
        jsonResponse(['mensagem' => 'Aluno removido com sucesso!']);

    default:
        jsonResponse(['erro' => 'Método não suportado'], 405);
}
