<?php

$pdo = new PDO('mysql:host=localhost;dbname=testecoinevoo', 'root', 'Madeira47#');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $erros = [];

        $_POST = json_decode(file_get_contents('php://input'), true);
        $primeiro_nome = $_POST['primeiro_nome'];
        $ultimo_nome = $_POST['ultimo_nome'];
        $cpf = $_POST['cpf'];
        $telefone = $_POST['telefone'];
        $email = $_POST['email'];
        $senha = $_POST['senha'];
        $confirm_senha = $_POST['confirm_senha'];

        if (empty($primeiro_nome)) {
            array_push($erros, ["parameter_name" => "nome", "message" => "Nome é obrigatório"]);
        }
        if (empty($ultimo_nome)) {
            array_push($erros, ["parameter_name" => "nome", "message" => "Segundo nome é obrigatório"]);
        }
        if (empty($cpf)) {
            array_push($erros, ["parameter_name" => "cpf", "message" => "CPF é obrigatório"]);
        } elseif (!validarCPF($cpf)) {
            array_push($erros, ["parameter_name" => "cpf", "message" => "CPF inválido"]);
        }
        if (empty($telefone)) {
            array_push($erros, ["parameter_name" => "telefone", "message" => "Telefone é obrigatório"]);
        } elseif (!validarTelefone($telefone)) {
            array_push($erros, ["parameter_name" => "telefone", "message" => "Telefone inválido"]);
        }
        if (empty($email)) {
            array_push($erros, ["parameter_name" => "email", "message" => "Email é obrigatório"]);
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            array_push($erros, ["parameter_name" => "email", "message" => "Email é invalido"]);
        }

        if ($senha !== $confirm_senha) {
            array_push($erros, ["parameter_name" => "senha", "message" => "As senhas não são iguais"]);
        }
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        $buscarEmail = $pdo->prepare('SELECT id FROM usuarios WHERE email=:email');
        $buscarEmail->execute(array(
            ':email' => $email
        ));
        if ($buscarEmail->rowCount() > 0) {
            array_push($erros, ["parameter_name" => "email", "message" => "Email já existente"]);
        }

        if (count($erros) > 0) {
            $response = ["status" => "erro", "erros" => $erros];
            returnJsonHttpResponse(400, $response);
        }
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('INSERT INTO usuarios (primeiro_nome,ultimo_nome,cpf,telefone,email,senha_hash) VALUES(:primeiro_nome, :ultimo_nome, :cpf, :telefone, :email, :senha_hash)');
        $stmt->execute(array(
            ':primeiro_nome' => $primeiro_nome,
            ':ultimo_nome' => $ultimo_nome,
            ':cpf' => $cpf,
            ':telefone' => $telefone,
            ':email' => $email,
            ':senha_hash' => $senha_hash
        ));

        $pdo->commit();
        returnJsonHttpResponse(201, null);
    } catch (Exception $e) {
        $pdo->rollBack();
        returnJsonHttpResponse(500, null);
    }
} else {
    returnJsonHttpResponse(405, null);
}

function validarTelefone($telefone)
{
    // Remover caracteres não numéricos do telefone
    $telefone = preg_replace('/[^0-9]/', '', $telefone);

    // Verificar se o telefone possui o formato correto (DDD + número)
    if (strlen($telefone) < 10 || strlen($telefone) > 11) {
        return false;
    }

    return true;
}
function validarCPF($cpf)
{
    // Extrai somente os números
    $cpf = preg_replace('/[^0-9]/is', '', $cpf);

    // Verifica se foi informado todos os digitos corretamente
    if (strlen($cpf) != 11) {
        return false;
    }
    // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    // Faz o calculo para validar o CPF
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    return true;
}
function returnJsonHttpResponse($httpCode, $data)
{

    ob_start();
    ob_clean();

    header_remove();

    header("Content-type: application/json; charset=utf-8");


    http_response_code($httpCode);



    if (!is_null($data)) {
        echo json_encode($data);
    }

    exit();
}
