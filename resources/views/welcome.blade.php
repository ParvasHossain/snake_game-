<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Snake Game</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-white min-h-screen flex flex-col items-center justify-center font-sans py-6">

    <div class="text-center max-w-md w-full px-4">
        <h1 class="text-3xl md:text-4xl font-extrabold mb-1 text-emerald-400 tracking-wide uppercase">Snake Game</h1>
        <p class="text-slate-400 mb-4 text-sm">Built with PHP Laravel & JavaScript</p>

        <!-- Scoreboard -->
        <div class="flex justify-between bg-slate-800 p-4 rounded-lg mb-4 border border-slate-700 shadow-md">
            <div>
                <span class="text-xs uppercase tracking-wider text-slate-400 block">Score</span>
                <span id="score" class="text-2xl font-bold text-white">0</span>
            </div>
            <div>
                <span class="text-xs uppercase tracking-wider text-slate-400 block">High Score</span>
                <span id="highScore" class="text-2xl font-bold text-emerald-400">0</span>
            </div>
        </div>

        <!-- Game Screen Container -->
        <div class="relative w-full aspect-square max-w-[400px] mx-auto">
            <canvas id="gameCanvas" width="400" height="400" class="w-full h-full rounded-lg border-4 border-slate-700 bg-slate-900 shadow-xl object-contain"></canvas>
            
            <!-- Game Over Overlay -->
            <div id="gameOverScreen" class="absolute inset-0 bg-black/80 flex flex-col items-center justify-center rounded-lg hidden z-10">
                <h2 class="text-3xl font-black text-red-500 mb-2">GAME OVER</h2>
                <p class="text-slate-300 mb-4">Final Score: <span id="finalScore" class="font-bold text-white">0</span></p>
                <button onclick="resetGame()" class="bg-emerald-500 hover:bg-emerald-400 text-slate-900 font-bold py-2 px-6 rounded-full transition transform hover:scale-105 active:scale-95 shadow-lg">
                    Play Again
                </button>
            </div>
        </div>

        <!-- Mobile Arrow Buttons (Strictly Below Canvas) -->
        <div id="mobile-controls" class="flex flex-col items-center justify-center mt-5 mb-3 select-none w-full">
            <div class="flex justify-center">
                <button type="button" id="btn-up" class="w-12 h-12 m-1 bg-slate-800 hover:bg-slate-700 active:bg-slate-600 rounded-lg text-xl font-bold border border-slate-600 text-white shadow-md active:scale-95">▲</button>
            </div>
            <div class="flex justify-center">
                <button type="button" id="btn-left" class="w-12 h-12 m-1 bg-slate-800 hover:bg-slate-700 active:bg-slate-600 rounded-lg text-xl font-bold border border-slate-600 text-white shadow-md active:scale-95">◀</button>
                <div class="w-12 h-12 m-1"></div>
                <button type="button" id="btn-right" class="w-12 h-12 m-1 bg-slate-800 hover:bg-slate-700 active:bg-slate-600 rounded-lg text-xl font-bold border border-slate-600 text-white shadow-md active:scale-95">▶</button>
            </div>
            <div class="flex justify-center">
                <button type="button" id="btn-down" class="w-12 h-12 m-1 bg-slate-800 hover:bg-slate-700 active:bg-slate-600 rounded-lg text-xl font-bold border border-slate-600 text-white shadow-md active:scale-95">▼</button>
            </div>
        </div>

        <!-- View Scores Button -->
        <div class="mt-4 text-center">
            <a href="{{ route('scores.index') }}" class="inline-block bg-blue-600 hover:bg-blue-500 text-white font-bold py-2.5 px-6 rounded-full transition shadow-lg text-sm">
                🏆 View High Scores
            </a>
        </div>

        <!-- Instructions -->
        <div class="mt-4 text-xs text-slate-500 bg-slate-800/50 p-3 rounded border border-slate-800">
            Use <span class="bg-slate-700 text-slate-300 px-1.5 py-0.5 rounded font-mono">Arrow Keys</span> or 
            <span class="bg-slate-700 text-slate-300 px-1.5 py-0.5 rounded font-mono">W A S D</span> to navigate.
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const bindBtn = (id, keyName) => {
                const btn = document.getElementById(id);
                if (btn) {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        document.dispatchEvent(new KeyboardEvent('keydown', { key: keyName }));
                    });
                }
            };

            bindBtn('btn-up', 'ArrowUp');
            bindBtn('btn-down', 'ArrowDown');
            bindBtn('btn-left', 'ArrowLeft');
            bindBtn('btn-right', 'ArrowRight');
        });

        function saveGameScore(playerScore) {
            let username = prompt("Game Over! Enter your name to save your score:", "Guest");
            if (!username) username = "Guest";

            fetch('/save-score', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    username: username,
                    score: playerScore
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    console.log("Database updated successfully!", data);
                    alert("Score saved! " + username + ": " + playerScore);
                }
            })
            .catch(error => console.error('Error saving score to database:', error));
        }

        const canvas = document.getElementById("gameCanvas");
        const ctx = canvas.getContext("2d");
        const scoreElement = document.getElementById("score");
        const highScoreElement = document.getElementById("highScore");
        const gameOverScreen = document.getElementById("gameOverScreen");
        const finalScoreElement = document.getElementById("finalScore");

        const gridSize = 20;
        const tileCount = canvas.width / gridSize;

        let snake = [{ x: 10, y: 10 }];
        let food = { x: 5, y: 5 };
        let dx = 1;
        let dy = 0;
        let score = 0;
        let highScore = localStorage.getItem("snakeHighScore") || 0;
        let gameInterval;
        let gameRunning = true;
        let changingDirection = false;

        highScoreElement.innerText = highScore;

        document.addEventListener("keydown", changeDirection);

        function main() {
            if (hasGameEnded()) {
                endGame();
                return;
            }

            changingDirection = false;
            clearCanvas();
            drawFood();
            moveSnake();
            drawSnake();
        }

        function startGame() {
            gameInterval = setInterval(main, 100);
        }

        function clearCanvas() {
            ctx.fillStyle = "#111827";
            ctx.fillRect(0, 0, canvas.width, canvas.height);
        }

        function drawSnake() {
            snake.forEach((part, index) => {
                ctx.fillStyle = index === 0 ? "#34d399" : "#10b981";
                ctx.strokeStyle = "#111827";
                ctx.fillRect(part.x * gridSize, part.y * gridSize, gridSize - 2, gridSize - 2);
            });
        }

        function moveSnake() {
            const head = { x: snake[0].x + dx, y: snake[0].y + dy };
            snake.unshift(head);

            if (snake[0].x === food.x && snake[0].y === food.y) {
                score += 10;
                scoreElement.innerText = score;
                generateFood();
            } else {
                snake.pop();
            }
        }

        function generateFood() {
            food.x = Math.floor(Math.random() * tileCount);
            food.y = Math.floor(Math.random() * tileCount);

            snake.forEach(part => {
                if (part.x === food.x && part.y === food.y) {
                    generateFood();
                }
            });
        }

        function drawFood() {
            ctx.fillStyle = "#f43f5e";
            ctx.fillRect(food.x * gridSize, food.y * gridSize, gridSize - 2, gridSize - 2);
        }

        function changeDirection(event) {
            const keyPressed = event.key.toLowerCase();
            if (changingDirection) return;

            const goingUp = dy === -1;
            const goingDown = dy === 1;
            const goingRight = dx === 1;
            const goingLeft = dx === -1;

            if ((keyPressed === "arrowup" || keyPressed === "w") && !goingDown) {
                dx = 0; dy = -1; changingDirection = true;
            }
            if ((keyPressed === "arrowdown" || keyPressed === "s") && !goingUp) {
                dx = 0; dy = 1; changingDirection = true;
            }
            if ((keyPressed === "arrowleft" || keyPressed === "a") && !goingRight) {
                dx = -1; dy = 0; changingDirection = true;
            }
            if ((keyPressed === "arrowright" || keyPressed === "d") && !goingLeft) {
                dx = 1; dy = 0; changingDirection = true;
            }
        }

        function hasGameEnded() {
            for (let i = 4; i < snake.length; i++) {
                if (snake[i].x === snake[0].x && snake[i].y === snake[0].y) return true;
            }
            return (
                snake[0].x < 0 ||
                snake[0].x >= tileCount ||
                snake[0].y < 0 ||
                snake[0].y >= tileCount
            );
        }

        function endGame() {
            clearInterval(gameInterval);
            gameRunning = false;
            finalScoreElement.innerText = score;
            gameOverScreen.classList.remove("hidden");

            if (score > highScore) {
                highScore = score;
                localStorage.setItem("snakeHighScore", highScore);
                highScoreElement.innerText = highScore;
            }
        }

        function resetGame() {
            if (score > 0) {
                saveGameScore(score);
            }

            snake = [{ x: 10, y: 10 }];
            dx = 1;
            dy = 0;
            score = 0;
            scoreElement.innerText = score;
            gameOverScreen.classList.add("hidden");
            generateFood();
            gameRunning = true;
            startGame();
        }

        generateFood();
        startGame();
    </script>
</body>
</html>