<?php
// Configurações do Banco de Dados no Hostinger
$servername = "seu_servidor_mysql_no_hostinger";
$username = "seu_usuario_do_banco_de_dados";
$password = "sua_senha_do_banco_de_dados";
$dbname = "seu_nome_de_banco_de_dados";

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    http_response_code(500);
    die("Connection failed: " . $conn->connect_error);
}

header('Content-Type: application/json');

// Lógica de roteamento da API
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Receber dados do JavaScript
    $data = json_decode(file_get_contents("php://input"));
    $duration = $data->duration;
    $type = $data->type;

    // Inserir dados no banco de dados
    $stmt = $conn->prepare("INSERT INTO pomodoro_sessions (session_date, duration_minutes, session_type) VALUES (NOW(), ?, ?)");
    $stmt->bind_param("is", $duration, $type);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Sessão registrada com sucesso."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erro ao registrar a sessão."]);
    }
    $stmt->close();

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Retornar o tempo total de estudo
    $sql = "SELECT SUM(duration_minutes) AS total_time FROM pomodoro_sessions WHERE session_type = 'estudo'";
    
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $total_time = $row['total_time'] ? $row['total_time'] : 0;
        echo json_encode(["total_study_time" => $total_time]);
    } else {
        echo json_encode(["total_study_time" => 0]);
    }
}

$conn->close();
?>