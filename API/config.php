<?php
// config.php - Configuração segura sem credenciais hardcoded

// Proteção contra acesso direto
if (!defined('INCLUDED_BY_SCRIPT')) {
    http_response_code(403);
    die(json_encode(['error' => 'Acesso direto negado']));
}

// Função para carregar o arquivo .env
function loadEnv() {
    // Procura o .env na raiz do projeto (um nível acima da pasta api)
    $envPath = dirname(__DIR__) . '/.env';
    
    if (!file_exists($envPath)) {
        // Em produção, pode usar variáveis do servidor
        if (getenv('DB_HOST')) {
            define('DB_HOST', getenv('DB_HOST'));
            define('DB_USER', getenv('DB_USER'));
            define('DB_PASSWORD', getenv('DB_PASSWORD'));
            define('DB_NAME', getenv('DB_NAME'));
            return;
        }
        
        die(json_encode([
            'error' => 'Arquivo de configuração não encontrado. Copie .env.example para .env e configure.'
        ]));
    }
    
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorar comentários e linhas vazias
        if (empty(trim($line)) || strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Processar variável
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remover aspas se existirem
            $value = trim($value, '"\'');
            
            // Definir como constante
            if (!defined($key)) {
                define($key, $value);
            }
            
            // Também definir como variável de ambiente
            putenv("$key=$value");
        }
    }
}

// Carregar configurações
loadEnv();

// Validar configurações essenciais
$required = ['DB_HOST', 'DB_USER', 'DB_PASSWORD', 'DB_NAME'];
foreach ($required as $const) {
    if (!defined($const)) {
        die(json_encode([
            'error' => "Configuração ausente: $const. Verifique o arquivo .env"
        ]));
    }
}

// Definir configurações adicionais
if (!defined('APP_ENV')) {
    define('APP_ENV', 'production');
}

if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', APP_ENV === 'development');
}

// Configurações de erro baseadas no ambiente
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Configurações de sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

// Timezone
date_default_timezone_set('America/Sao_Paulo');
?>