<?php
// Proteção e headers
define('INCLUDED_BY_SCRIPT', true);
require_once 'config.php';

header('Content-Type: application/json');

// CORS restritivo
$allowed_origins = ['https://seusite.com'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
}

// Apenas POST permitido
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(["error" => "Método não permitido"]));
}

// Funções de validação
function validateUsername($username) {
    if (empty($username)) {
        return ["Username é obrigatório"];
    }
    
    $errors = [];
    
    if (strlen($username) < 3 || strlen($username) > 20) {
        $errors[] = "Username deve ter entre 3 e 20 caracteres";
    }
    
    if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $username)) {
        $errors[] = "Username deve começar com letra e conter apenas letras, números e underscore";
    }
    
    // Lista de usernames proibidos
    $reserved = ['admin', 'root', 'system', 'user', 'test'];
    if (in_array(strtolower($username), $reserved)) {
        $errors[] = "Este username não está disponível";
    }
    
    return $errors;
}

function validatePassword($password) {
    if (empty($password)) {
        return ["Senha é obrigatória"];
    }
    
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = "Senha deve ter no mínimo 8 caracteres";
    }
    
    if (strlen($password) > 128) {
        $errors[] = "Senha muito longa";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Senha deve conter pelo menos uma letra maiúscula";
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Senha deve conter pelo menos uma letra minúscula";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Senha deve conter pelo menos um número";
    }
    
    // Verificar senhas comuns
    $common_passwords = ['password', '12345678', 'qwerty', 'abc12345'];
    if (in_array(strtolower($password), $common_passwords)) {
        $errors[] = "Senha muito comum, escolha outra";
    }
    
    return $errors;
}

function checkRegistrationRateLimit($conn) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $time_limit = time() - 3600; // 1 hora
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM registration_attempts 
                            WHERE ip_address = ? AND attempt_time > ?");
    $stmt->bind_param("si", $ip, $time_limit);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result['count'] >= 3) {
        return false; // Máximo 3 registros por hora por IP
    }
    
    return true;
}

function logRegistrationAttempt($conn, $username, $success) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $stmt = $conn->prepare("INSERT INTO registration_attempts 
                            (username, ip_address, user_agent, attempt_time, success) 
                            VALUES (?, ?, ?, ?, ?)");
    $time = time();
    $stmt->bind_param("sssii", $username, $ip, $user_agent, $time, $success);
    $stmt->execute();
}

// Processar requisição
$input = file_get_contents("php://input");
$data = json_decode($input);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    die(json_encode(["error" => "Dados inválidos"]));
}

// Extrair e limpar dados
$username = isset($data->username) ? trim($data->username) : '';
$password = isset($data->password) ? $data->password : '';
$email = isset($data->email) ? filter_var(trim($data->email), FILTER_VALIDATE_EMAIL) : null;

// Validar dados
$errors = [];
$username_errors = validateUsername($username);
$password_errors = validatePassword($password);

if (!empty($username_errors)) {
    $errors = array_merge($errors, $username_errors);
}

if (!empty($password_errors)) {
    $errors = array_merge($errors, $password_errors);
}

if (!empty($errors)) {
    http_response_code(400);
    die(json_encode(["errors" => $errors]));
}

// Conectar ao banco
$conn = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    http_response_code(500);
    error_log("Database connection failed: " . $conn->connect_error);
    die(json_encode(["error" => "Serviço temporariamente indisponível"]));
}

// Verificar rate limit
if (!checkRegistrationRateLimit($conn)) {
    http_response_code(429);
    die(json_encode(["error" => "Muitos registros. Tente novamente mais tarde."]));
}

// Verificar se username já existe
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    logRegistrationAttempt($conn, $username, false);
    http_response_code(409);
    die(json_encode(["error" => "Username já está em uso"]));
}

// Hash da senha com algoritmo mais seguro
$password_hash = password_hash($password, PASSWORD_ARGON2ID, [
    'memory_cost' => 65536,
    'time_cost' => 4,
    'threads' => 1
]);

// Inserir novo usuário
$stmt = $conn->prepare("INSERT INTO users (username, password, email, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("sss", $username, $password_hash, $email);

if ($stmt->execute()) {
    logRegistrationAttempt($conn, $username, true);
    
    // Não inicie sessão automaticamente - force login
    echo json_encode([
        "status" => "success",
        "message" => "Conta criada com sucesso! Faça login para continuar."
    ]);
} else {
    error_log("Registration failed for user $username: " . $stmt->error);
    http_response_code(500);
    echo json_encode(["error" => "Erro ao criar conta. Tente novamente."]);
}

$stmt->close();
$conn->close();
?>