<?php
session_start();

require_once 'config.php';

$conn = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar conexão
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Tratar a requisição POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $username = $data->username;
    $password = $data->password;

    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(["error" => "Usuário e senha são obrigatórios."]);
        exit();
    }

    // Buscar o usuário pelo username
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $hashed_password = $user['password'];

        // Verificar a senha
        if (password_verify($password, $hashed_password)) {
            // Senha correta, inicia a sessão
            $_SESSION['user_id'] = $user['id'];
            echo json_encode(["status" => "success", "message" => "Login bem-sucedido!", "user_id" => $user['id']]);
        } else {
            http_response_code(401); // Não autorizado
            echo json_encode(["error" => "Senha incorreta."]);
        }
    } else {
        http_response_code(401);
        echo json_encode(["error" => "Usuário não encontrado."]);
    }

    $stmt->close();
}

$conn->close();
?>