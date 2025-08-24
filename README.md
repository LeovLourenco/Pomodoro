# Pomodoro Timer

Um aplicativo web de timer Pomodoro com sistema de autenticação e rastreamento de sessões de estudo.

## Funcionalidades

- **Timer Pomodoro Personalizável**: Opções de 25, 45 ou 60 minutos, ou tempo customizado
- **Pausas Opcionais**: Pausas curtas (5 min) e longas (10 min) após cada pomodoro
- **Controles de Timer**: Iniciar, pausar e reiniciar
- **Contador de Pomodoros**: Acompanhe quantos pomodoros foram completados
- **Tempo Total de Estudo**: Visualize o tempo total acumulado
- **Sistema de Autenticação**: Login e registro de usuários
- **Persistência de Dados**: Armazena sessões no banco de dados

## Estrutura do Projeto

```
pomodoro/
├── index.html          # Página principal do timer
├── login.html          # Página de login
├── CSS/
│   └── style.css       # Estilos da aplicação
├── JS/
│   ├── script.js       # Lógica principal do timer
│   └── auth.js         # Lógica de autenticação
└── API/
    ├── api.php         # Endpoints da API
    ├── config.php      # Configurações do banco
    ├── login.php       # Autenticação de login
    └── register.php    # Registro de usuários
```

## Tecnologias Utilizadas

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **Banco de Dados**: MySQL
- **Autenticação**: Sistema próprio com localStorage

## Como Usar

1. Acesse `login.html` para fazer login ou criar uma conta
2. Após autenticado, você será redirecionado para a página principal
3. Escolha a duração do pomodoro (25, 45, 60 min ou customizado)
4. Clique em "Iniciar" para começar a sessão
5. Quando o timer terminar, escolha se deseja fazer uma pausa ou pular
6. Acompanhe seu progresso no contador de pomodoros e tempo total

## Configuração

1. Configure as credenciais do banco de dados em `API/config.php`
2. Atualize as URLs da API em `JS/script.js` para apontar para seu domínio
3. Certifique-se de que o servidor PHP esteja configurado corretamente

## Recursos

- Interface responsiva em português brasileiro
- Modo de descanso opcional (conforme commit: "alteracao do mododescanso para opcional")
- Personalização de minutagem (conforme commit: "aumento de minutagem e personalizacao")
- Sistema robusto com correções de bugs implementadas