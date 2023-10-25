<?php

$pdo = new PDO('mysql:host=localhost;dbname=testecoinevoo', 'root', 'Madeira47#');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $erros = [];

        $_POST = json_decode(file_get_contents('php://input'), true);

        $email = filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL);
        $senha = trim($_POST["senha"]);

        if (empty($email)) {
            array_push($erros, ["parameter_name" => "email", "message" => "O email é obrigatório"]);
        }

        if (empty($senha)) {
            array_push($erros, ["parameter_name" => "senha", "message" => "Senha é obrigatória"]);
        }

        if (count($erros) > 0) {
            $response = ["status" => "erro", "erros" => $erros];
            returnJsonHttpResponse(400, $response);
        }

        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || !password_verify($senha, $row['senha_hash'])) {
            array_push($erros, ["parameter_name" => "senha", "message" => "Credenciais inválidas"]);
            $response = ["status" => "erro", "erros" => $erros];
            returnJsonHttpResponse(401, $response);
        }

        session_start();
        $_SESSION['nome'] = $row['primeiro_nome'] . " " . $row['ultimo_nome'];
        $_SESSION['email'] = $row['email'];

        // Autenticação bem-sucedida
        $response = ["status" => "success", "message" => "Login bem-sucedido"];
        returnJsonHttpResponse(200, $response);
    } catch (Exception $e) {
        returnJsonHttpResponse(500, null);
    }
} else {
    returnJsonHttpResponse(405, null);
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
