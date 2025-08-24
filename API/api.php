<?php
// Inclui o arquivo de configuração seguro
require_once 'config.php';

// Conectar ao banco de dados
$conn = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar a conexão
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Permite requisições de qualquer origem
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Tratar a requisição POST (Salvar uma nova sessão)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $user_id = $data->user_id; // Recebe o user_id do JavaScript
    $duration = $data->duration;
    $type = $data->type;

    if (empty($user_id) || empty($duration) || empty($type)) {
        http_response_code(400);
        echo json_encode(["error" => "Dados incompletos."]);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO pomodoro_sessions (user_id, session_date, duration_minutes, session_type) VALUES (?, NOW(), ?, ?)");
    $stmt->bind_param("iis", $user_id, $duration, $type);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Sessão registrada com sucesso."]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Erro ao registrar a sessão."]);
    }
    $stmt->close();

// Tratar a requisição GET (Buscar o tempo total de estudo)
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $_GET['user_id'] ?? null; // Recebe o user_id da URL

    if (empty($user_id)) {
        http_response_code(400);
        echo json_encode(["error" => "ID do usuário não fornecido."]);
        exit();
    }

    $stmt = $conn->prepare("SELECT SUM(duration_minutes) AS total_time FROM pomodoro_sessions WHERE user_id = ? AND session_type = 'estudo'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $total_time = $row['total_time'] ? $row['total_time'] : 0;
        echo json_encode(["total_study_time" => $total_time]);
    } else {
        echo json_encode(["total_study_time" => 0]);
    }
    $stmt->close();
}

$conn->close();
?>