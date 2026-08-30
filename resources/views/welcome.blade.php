<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Snake Game</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        canvas {
            border: 4px solid #1f2937;
            background-color: #111827;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body class="bg-slate-900 text-white min-h-screen flex flex-col items-center justify-center font-sans">

    <div class="text-center max-w-md w-full px-4">
        <h1 class="text-4xl font-extrabold mb-2 text-emerald-400 tracking-wide uppercase">Snake Game</h1>
        <p class="text-slate-400 mb-6 text-sm">Built with PHP Laravel & JavaScript</p>

        <!-- Scoreboard -->
        <div class="flex justify-between bg-slate-800 p-4 rounded-lg mb-6 border border-slate-700">
            <div>
                <span class="text-xs uppercase tracking-wider text-slate-400 block">Score</span>
                <span id="score" class="text-2xl font-bold text-white">0</span>
            </div>
            <div>
                <span class="text-xs uppercase tracking-wider text-slate-400 block">High Score</span>
                <span id="highScore" class="text-2xl font-bold text-emerald-400">0</span>
            </div>
        </div>

        <!-- Game Canvas -->
        <div class="relative flex justify-center">
            <canvas id="gameCanvas" width="400" height="400" class="rounded-lg"></canvas>
            
            <!-- Game Over Overlay -->
            <div id="gameOverScreen" class="absolute inset-0 bg-black/80 flex flex-col items-center justify-center rounded-lg hidden">
                <h2 class="text-3xl font-black text-red-500 mb-2">GAME OVER</h2>
                <p class="text-slate-300 mb-4">Final Score: <span id="finalScore" class="font-bold text-white">0</span></p>
                <button onclick="resetGame()" class="bg-emerald-500 hover:bg-emerald-400 text-slate-900 font-bold py-2 px-6 rounded-full transition transform hover:scale-105 active:scale-95 shadow-lg">
                    Play Again
                </button>
            </div>
        </div>
<!-- View Scores Button -->
<div class="mt-5 text-center">

    <a href="{{ route('scores.index') }}"
       class="inline-block bg-blue-500 hover:bg-blue-400
              text-white font-bold py-3 px-8
              rounded-full transition shadow-lg">

        🏆 View High Scores

    </a>

</div>
        <!-- Instructions -->
        <div class="mt-6 text-xs text-slate-500 bg-slate-800/50 p-3 rounded border border-slate-800">
            Use <span class="bg-slate-700 text-slate-300 px-1.5 py-0.5 rounded font-mono">Arrow Keys</span> or 
            <span class="bg-slate-700 text-slate-300 px-1.5 py-0.5 rounded font-mono">W A S D</span> to navigate.
        </div>
    </div>

    <script>
        function saveGameScore(playerScore) {
    // 1. Prompt the player for their score card name profile
    let username = prompt("Game Over! Enter your name to save your score:", "Guest");
    if (!username) username = "Guest";

    // 2. Transmit the score payload straight to your Laravel Backend
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
        let changingDirection = false; // Prevents fast consecutive inputs from causing self-collision

        highScoreElement.innerText = highScore;

        // Handle Inputs
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
                ctx.fillStyle = index === 0 ? "#34d399" : "#10b981"; // Head is lighter green
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

            // Don't spawn food inside snake
            snake.forEach(part => {
                if (part.x === food.x && part.y === food.y) {
                    generateFood();
                }
            });
        }

        function drawFood() {
            ctx.fillStyle = "#f43f5e"; // Rose red
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
            // Self collision
            for (let i = 4; i < snake.length; i++) {
                if (snake[i].x === snake[0].x && snake[i].y === snake[0].y) return true;
            }
            // Boundary collision
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
    // 1. If they scored points, save it to MySQL before clearing out the variables
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


        // Initialize First Run
        generateFood();
        startGame();
    </script>
</body>
</html>