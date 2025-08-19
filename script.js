// Selecionar os elementos do DOM
const timerElement = document.getElementById('timer');
const startBtn = document.getElementById('start-btn');
const pauseBtn = document.getElementById('pause-btn');
const resetBtn = document.getElementById('reset-btn');
const statusElement = document.getElementById('status');
const pomodoroCounterElement = document.getElementById('pomodoro-counter');
const totalTimeElement = document.getElementById('total-time-display');
const timeOptions = document.querySelectorAll('.time-option');
const customMinutesInput = document.getElementById('custom-minutes');
const setCustomTimeBtn = document.getElementById('set-custom-time-btn');

// URL da API hospedada no Hostinger
const API_URL = 'https://seu-dominio.com/api.php'; // SUBSTITUA PELO SEU DOMÍNIO

// Configurações e estado inicial
let POMODORO_DURATION = 25 * 60; // Duração inicial
const SHORT_BREAK_DURATION = 5 * 60;
const LONG_BREAK_DURATION = 15 * 60;

let pomodoroCount = 0;
let isStudyMode = true;
let currentDuration = POMODORO_DURATION;

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

async function fetchTotalStudyTime() {
    try {
        const response = await fetch(API_URL);
        const data = await response.json();
        const totalMinutes = data.total_study_time || 0;
        const hours = Math.floor(totalMinutes / 60);
        const minutes = totalMinutes % 60;
        totalTimeElement.textContent = `${hours}h ${minutes}min`;
    } catch (error) {
        console.error('Erro ao buscar tempo total de estudo:', error);
        totalTimeElement.textContent = 'Erro ao carregar';
    }
}

async function sendSessionData(duration, type) {
    const payload = { duration: duration, type: type };
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        await response.json();
        await fetchTotalStudyTime();
    } catch (error) {
        console.error('Erro ao salvar a sessão:', error);
    }
}

function startTimer() {
    if (isRunning) return;
    isRunning = true;
    startTime = Date.now();

    timerId = setInterval(() => {
        const elapsedTime = (Date.now() - startTime) / 1000;
        const totalTime = currentDuration;
        
        remainingTime = totalTime - elapsedTime;

        if (remainingTime <= 0) {
            clearInterval(timerId);
            isRunning = false;
            remainingTime = 0;
            updateTimerDisplay();
            
            if (isStudyMode) {
                pomodoroCount++;
                pomodoroCounterElement.textContent = `Pomodoros: ${pomodoroCount}`;
                sendSessionData(currentDuration / 60, 'estudo');
                isStudyMode = false;
                if (pomodoroCount % 4 === 0) {
                    currentDuration = LONG_BREAK_DURATION;
                    statusElement.textContent = "Descanso Longo";
                } else {
                    currentDuration = SHORT_BREAK_DURATION;
                    statusElement.textContent = "Descanso Curto";
                }
            } else {
                isStudyMode = true;
                currentDuration = POMODORO_DURATION;
                statusElement.textContent = "Estudo";
            }
            
            remainingTime = currentDuration;
            updateTimerDisplay();
            return;
        }
        
        updateTimerDisplay();
    }, 100);
}

function pauseTimer() {
    if (!isRunning) return;
    clearInterval(timerId);
    isRunning = false;
}

function resetTimer() {
    pauseTimer();
    isStudyMode = true;
    statusElement.textContent = "Estudo";
    pomodoroCount = 0;
    pomodoroCounterElement.textContent = `Pomodoros: 0`;
    currentDuration = POMODORO_DURATION; // ESSA LINHA FOI ADICIONADA
    remainingTime = currentDuration;
    updateTimerDisplay();
}

function setPomodoroTime(minutes) {
    POMODORO_DURATION = minutes * 60;
    resetTimer(); // ESSA FUNÇÃO É AGORA CHAMADA AQUI
    timeOptions.forEach(btn => btn.classList.remove('active'));
    document.querySelector(`[data-minutes="${minutes}"]`).classList.add('active');
}

// Event Listeners
startBtn.addEventListener('click', startTimer);
pauseBtn.addEventListener('click', pauseTimer);
resetBtn.addEventListener('click', resetTimer);

timeOptions.forEach(button => {
    button.addEventListener('click', () => {
        setPomodoroTime(parseInt(button.dataset.minutes));
    });
});

setCustomTimeBtn.addEventListener('click', () => {
    const minutes = parseInt(customMinutesInput.value);
    if (!isNaN(minutes) && minutes > 0) {
        setPomodoroTime(minutes);
        timeOptions.forEach(btn => btn.classList.remove('active'));
    }
});

// Inicialização
updateTimerDisplay();
fetchTotalStudyTime();