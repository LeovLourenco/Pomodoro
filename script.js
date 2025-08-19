// Selecionar os elementos do DOM
const timerElement = document.getElementById('timer');
const startBtn = document.getElementById('start-btn');
const pauseBtn = document.getElementById('pause-btn');
const resetBtn = document.getElementById('reset-btn');
const statusElement = document.getElementById('status');
const pomodoroCounterElement = document.getElementById('pomodoro-counter');

// Configurações e estado inicial
const POMODORO_DURATION = 25 * 60; // 25 minutos em segundos
const SHORT_BREAK_DURATION = 5 * 60; // 5 minutos em segundos
const LONG_BREAK_DURATION = 15 * 60; // 15 minutos em segundos

let pomodoroCount = 0;
let isStudyMode = true;

let timerId = null;
let isRunning = false;
let remainingTime = POMODORO_DURATION;
let startTime = null;

// Funções
function updateTimerDisplay() {
    const minutes = Math.floor(remainingTime / 60);
    const seconds = Math.floor(remainingTime % 60);
    timerElement.textContent =
        `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

function startTimer() {
    if (isRunning) return;
    isRunning = true;
    startTime = Date.now();

    timerId = setInterval(() => {
        const elapsedTime = (Date.now() - startTime) / 1000;
        const newRemainingTime = (isStudyMode ?
                                 (pomodoroCount % 4 === 0 && pomodoroCount > 0 ? LONG_BREAK_DURATION : SHORT_BREAK_DURATION)
                                 : POMODORO_DURATION) - elapsedTime;
        
        remainingTime = (isStudyMode ? POMODORO_DURATION : (pomodoroCount % 4 === 0 && pomodoroCount > 0) ? LONG_BREAK_DURATION : SHORT_BREAK_DURATION) - elapsedTime;

        if (remainingTime <= 0) {
            clearInterval(timerId);
            isRunning = false;
            remainingTime = 0;
            updateTimerDisplay();
            
            // LÓGICA DE ATUALIZAÇÃO E MUDANÇA DE MODO
            if (isStudyMode) {
                pomodoroCount++;
                pomodoroCounterElement.textContent = `Pomodoros: ${pomodoroCount}`;
                isStudyMode = false;
                statusElement.textContent = "Descanso Curto";
                if (pomodoroCount % 4 === 0) {
                    remainingTime = LONG_BREAK_DURATION;
                    statusElement.textContent = "Descanso Longo";
                } else {
                    remainingTime = SHORT_BREAK_DURATION;
                }
            } else {
                isStudyMode = true;
                remainingTime = POMODORO_DURATION;
                statusElement.textContent = "Estudo";
            }
            
            updateTimerDisplay();
            alert('Tempo esgotado! O cronômetro mudou para o próximo modo.');
            return;
        }
        
        updateTimerDisplay();
    }, 100);
}

function pauseTimer() {
    if (!isRunning) return;
    clearInterval(timerId);
    isRunning = false;
    // O remainingTime já está sendo atualizado no setInterval
}

function resetTimer() {
    pauseTimer();
    remainingTime = POMODORO_DURATION;
    isStudyMode = true;
    statusElement.textContent = "Estudo";
    pomodoroCount = 0;
    pomodoroCounterElement.textContent = `Pomodoros: 0`;
    updateTimerDisplay();
}

// Event Listeners
startBtn.addEventListener('click', startTimer);
pauseBtn.addEventListener('click', pauseTimer);
resetBtn.addEventListener('click', resetTimer);

// Inicializar a exibição
updateTimerDisplay();