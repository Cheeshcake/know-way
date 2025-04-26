<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirect to login page
    header('Location: index.html');
    exit;
}

// Get user information
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get avatar path and initials
$user_avatar = '';
$stmt = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$avatar_result = $stmt->get_result();
if ($avatar_result && $avatar_result->num_rows > 0) {
    $user_avatar = $avatar_result->fetch_assoc()['avatar'];
}
$stmt->close();

// Generate initials from username
$initials = '';
$name_parts = explode(' ', $username);
foreach ($name_parts as $part) {
    $initials .= substr($part, 0, 1);
}
if (empty($initials)) {
    $initials = substr($username, 0, 1);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KnowWay - Game Center</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <style>
        .games-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
            margin: 24px 0;
        }
        
        .game-card {
            background-color: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            color: var(--dark);
        }
        
        .game-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .game-image {
            height: 180px;
            overflow: hidden;
            background-color: #f7f9fc;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .game-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .game-content {
            padding: 16px;
        }
        
        .game-title {
            margin: 0 0 8px;
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .game-description {
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 16px;
            line-height: 1.5;
        }
        
        .difficulty-options {
            display: flex;
            gap: 8px;
        }
        
        .difficulty-btn {
            flex: 1;
            padding: 8px 12px;
            border-radius: 6px;
            text-align: center;
            font-weight: 500;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .easy-btn {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        
        .easy-btn:hover {
            background-color: #badbcc;
        }
        
        .hard-btn {
            background-color: #f8d7da;
            color: #842029;
        }
        
        .hard-btn:hover {
            background-color: #f5c2c7;
        }
        
        /* Game styles */
        .game-container {
            display: none;
            max-width: 600px;
            margin: 0 auto;
            padding: 24px;
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .game-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .game-header h2 {
            margin: 0;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 6px;
            background-color: var(--light-gray);
            color: var(--dark-gray);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .back-btn:hover {
            background-color: #e2e6ea;
        }
        
        .game-board {
            margin-bottom: 24px;
        }
        
        /* Connect Four styles */
        #connect-four-board {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
            background-color: #4361ee;
            padding: 16px;
            border-radius: 8px;
        }
        
        .connect-four-cell {
            width: 100%;
            aspect-ratio: 1/1;
            background-color: var(--white);
            border-radius: 50%;
            cursor: pointer;
        }
        
        .connect-four-red {
            background-color: #e63946;
        }
        
        .connect-four-yellow {
            background-color: #ffb703;
        }
        
        /* Tic Tac Toe styles */
        #tic-tac-toe-board {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            max-width: 300px;
            margin: 0 auto;
            position: relative;
            background-color: var(--white);
        }
        
        .tic-tac-toe-cell {
            width: 100%;
            aspect-ratio: 1/1;
            background-color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            border: none;
        }
        
        /* Grid lines */
        .tic-tac-toe-cell:nth-child(1), .tic-tac-toe-cell:nth-child(2), .tic-tac-toe-cell:nth-child(4), .tic-tac-toe-cell:nth-child(5) {
            border-right: 3px solid #333;
            border-bottom: 3px solid #333;
        }
        
        .tic-tac-toe-cell:nth-child(3), .tic-tac-toe-cell:nth-child(6) {
            border-bottom: 3px solid #333;
        }
        
        .tic-tac-toe-cell:nth-child(7), .tic-tac-toe-cell:nth-child(8) {
            border-right: 3px solid #333;
        }
        
        .tic-tac-toe-cell:hover {
            background-color: #f0f0f0;
        }
        
        .x-marker {
            color: #e63946;
            text-shadow: 0 2px 2px rgba(0, 0, 0, 0.1);
        }
        
        .o-marker {
            color: #4361ee;
            text-shadow: 0 2px 2px rgba(0, 0, 0, 0.1);
        }
        
        .game-status {
            text-align: center;
            margin-bottom: 24px;
            font-weight: 600;
            height: 24px;
        }
        
        .restart-btn {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-weight: 500;
            margin-top: 30px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .restart-btn:hover {
            opacity: 0.9;
        }
        
        /* Game info styles */
        .game-info {
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.9rem;
            color: var(--gray);
        }
        
        .easy-mode-info, .hard-mode-info {
            display: none;
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" id="mobile-menu-toggle">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-menu"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
    </button>
    
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php" class="logo">
                <img src="assets/logo-white-bg.png" alt="KnowWay Logo" class="logo-image">
                <div class="logo-text">KnowWay</div>
            </a>
            <button class="mobile-toggle" id="closeSidebar">
                &times;
            </button>
        </div>
        
        <div class="sidebar-content">
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <ul class="nav-links">
                    <li>
                        <a href="dashboard.php" class="nav-link">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="my-courses.php" class="nav-link">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                <line x1="8" y1="6" x2="15" y2="6"></line>
                                <line x1="8" y1="10" x2="15" y2="10"></line>
                                <line x1="8" y1="14" x2="11" y2="14"></line>
                            </svg>
                            My Courses
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Learning</div>
                <ul class="nav-links">
                    <li>
                        <a href="courses.php" class="nav-link">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                            Courses
                        </a>
                    </li>
                   <li>
                    <a href="game-center.php" class="nav-link active">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                            <circle cx="12" cy="12" r="2"></circle>
                            <path d="M6 12h.01"></path>
                            <path d="M18 12h.01"></path>
                            <path d="M12 6v.01"></path>
                            <path d="M12 18v.01"></path>
                        </svg>
                        Game Center
                    </a>
                </ul>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Account</div>
                <ul class="nav-links">
                    <li>
                        <a href="user-settings.php" class="nav-link">
                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="3"></circle>
                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                            </svg>
                            Settings
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="user-avatar" style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center; margin-right: 8px; font-weight: 600; flex-shrink: 0; text-align: center;">
                    <?php if (!empty($user_avatar) && file_exists($user_avatar)): ?>
                        <img src="<?php echo htmlspecialchars($user_avatar); ?>" alt="Avatar" style="width: 40px; height: 40px; object-fit: cover; display: block; margin: 0; padding: 0;">
                    <?php else: ?>
                        <span style="font-size: 16px;"><?php echo htmlspecialchars($initials); ?></span>
                    <?php endif; ?>
                </div>
                <div class="user-info">
                    <div class="user-name"><?php echo $username; ?></div>
                    <div class="user-role">Learner</div>
                </div>
            </div>
            <a href="../controller/logout.php" class="logout-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                Logout
            </a>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h1 class="greeting">Game Center</h1>
            <p class="subheading">Take a break and have some fun with these games!</p>
        </div>
        
        <!-- Game Selection -->
        <div id="game-selection">
            <div class="games-container">
                <!-- Connect Four -->
                <div class="game-card">
                    <div class="game-image">
                        <img src="assets/images/connect-four.png" alt="Connect Four" onerror="this.src='https://placehold.co/600x400/4361ee/ffffff?text=Connect+Four'">
                    </div>
                    <div class="game-content">
                        <h3 class="game-title">Connect Four</h3>
                        <p class="game-description">Connect four of your colored discs in a row while preventing your opponent from doing the same.</p>
                        <div class="difficulty-options">
                            <a href="#" class="difficulty-btn easy-btn" onclick="startConnectFour('easy'); return false;">Easy Mode</a>
                            <a href="#" class="difficulty-btn hard-btn" onclick="startConnectFour('hard'); return false;">Hard Mode</a>
                        </div>
                    </div>
                </div>
                
                <!-- Tic Tac Toe -->
                <div class="game-card">
                    <div class="game-image">
                        <img src="assets/images/tic-tac-toe.png" alt="Tic Tac Toe" onerror="this.src='https://placehold.co/600x400/4361ee/ffffff?text=Tic+Tac+Toe'">
                    </div>
                    <div class="game-content">
                        <h3 class="game-title">Tic Tac Toe</h3>
                        <p class="game-description">Get three of your marks in a horizontal, vertical, or diagonal row before your opponent.</p>
                        <div class="difficulty-options">
                            <a href="#" class="difficulty-btn easy-btn" onclick="startTicTacToe('easy'); return false;">Easy Mode</a>
                            <a href="#" class="difficulty-btn hard-btn" onclick="startTicTacToe('hard'); return false;">Hard Mode</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Connect Four Game -->
        <div id="connect-four-game" class="game-container">
            <div class="game-header">
                <h2>Connect Four <span id="connect-four-difficulty"></span></h2>
                <a href="#" class="back-btn" onclick="showGameSelection(); return false;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    Back
                </a>
            </div>
            <div class="game-info">
                <p id="connect-four-algorithm-info">
                    <span class="easy-mode-info" >Playing in Easy Mode: Using Minimax algorithm with reduced depth.</span>
                    <span class="hard-mode-info" >Playing in Hard Mode: Using Alpha-Beta pruning for optimal strategy.</span>
                </p>
            </div>
            <div class="game-status" id="connect-four-status">Your turn</div>
            <div class="game-board" id="connect-four-board"></div>
            <button class="restart-btn" onclick="restartConnectFour()">Restart Game</button>
        </div>
        
        <!-- Tic Tac Toe Game -->
        <div id="tic-tac-toe-game" class="game-container">
            <div class="game-header">
                <h2>Tic Tac Toe <span id="tic-tac-toe-difficulty"></span></h2>
                <a href="#" class="back-btn" onclick="showGameSelection(); return false;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    Back
                </a>
            </div>
            <div class="game-info">
                <p id="tic-tac-toe-algorithm-info">
                    <span class="easy-mode-info">Playing in Easy Mode: Using Minimax algorithm with reduced depth.</span>
                    <span class="hard-mode-info">Playing in Hard Mode: Using Alpha-Beta pruning for optimal strategy.</span>
                </p>
            </div>
            <div class="game-status" id="tic-tac-toe-status">Your turn</div>
            <div class="game-board" id="tic-tac-toe-board"></div>
            <button class="restart-btn" onclick="restartTicTacToe()">Restart Game</button>
        </div>
    </main>
    
    <script>
        // Mobile menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('mobile-menu-toggle');
            const sidebar = document.querySelector('.sidebar');
            
            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
            });
        });
        
        // Game selection
        function showGameSelection() {
            document.getElementById('game-selection').style.display = 'block';
            document.getElementById('connect-four-game').style.display = 'none';
            document.getElementById('tic-tac-toe-game').style.display = 'none';
        }
        
        // API functions
        async function callGameApi(gameType, data) {
            try {
                const response = await fetch('../scripts/game_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        game: gameType,
                        ...data
                    })
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return await response.json();
            } catch (error) {
                console.error('API call failed:', error);
                alert('Failed to communicate with the game server. Please try again later.');
                return { error: error.message };
            }
        }
        
        // Connect Four Game
        let connectFourBoard = [];
        let connectFourCurrentPlayer = 1; // 1 for player, 2 for computer
        let connectFourDifficulty = 'easy';
        let connectFourGameActive = false;
        
        function startConnectFour(difficulty) {
            connectFourDifficulty = difficulty;
            document.getElementById('connect-four-difficulty').textContent = 
                `(${difficulty.charAt(0).toUpperCase() + difficulty.slice(1)} Mode)`;
            document.getElementById('game-selection').style.display = 'none';
            document.getElementById('connect-four-game').style.display = 'block';
            
            // Show the appropriate algorithm info
            const easyModeInfo = document.querySelector('#connect-four-algorithm-info .easy-mode-info');
            const hardModeInfo = document.querySelector('#connect-four-algorithm-info .hard-mode-info');
            
            if (difficulty === 'easy') {
                easyModeInfo.style.display = 'inline';
                hardModeInfo.style.display = 'none';
            } else {
                easyModeInfo.style.display = 'none';
                hardModeInfo.style.display = 'inline';
            }
            
            // Initialize the board
            connectFourBoard = Array(6).fill().map(() => Array(7).fill(0));
            connectFourCurrentPlayer = 1;
            connectFourGameActive = true;
            
            // Create the board UI
            const board = document.getElementById('connect-four-board');
            board.innerHTML = '';
            
            for (let row = 0; row < 6; row++) {
                for (let col = 0; col < 7; col++) {
                    const cell = document.createElement('div');
                    cell.className = 'connect-four-cell';
                    cell.dataset.col = col;
                    cell.addEventListener('click', () => makeConnectFourMove(col));
                    board.appendChild(cell);
                }
            }
            
            updateConnectFourStatus('Your turn');
        }
        
        async function makeConnectFourMove(col) {
            if (!connectFourGameActive || connectFourCurrentPlayer !== 1) return;
            
            // Find the lowest empty row in the selected column
            let row = -1;
            for (let r = 5; r >= 0; r--) {
                if (connectFourBoard[r][col] === 0) {
                    row = r;
                    break;
                }
            }
            
            if (row === -1) return; // Column is full
            
            // Update UI to show move is processing
            updateConnectFourStatus('Processing move...');
            connectFourCurrentPlayer = null; // Prevent additional clicks
            
            // Call API to make the move
            const response = await callGameApi('connect-four', {
                board: connectFourBoard,
                difficulty: connectFourDifficulty,
                make_move: col
            });
            
            if (response.error) {
                connectFourCurrentPlayer = 1; // Re-enable player moves
                updateConnectFourStatus('Error: ' + response.error);
                return;
            }
            
            // Update board with API response
            connectFourBoard = response.board;
            updateConnectFourUI();
            
            // Check for game end
            if (response.game_over) {
                if (response.winner === 'player') {
                    updateConnectFourStatus('You win!');
                } else if (response.winner === 'ai') {
                    updateConnectFourStatus('Computer wins!');
                } else {
                    updateConnectFourStatus("It's a draw!");
                }
                connectFourGameActive = false;
                return;
            }
            
            connectFourCurrentPlayer = 1;
            updateConnectFourStatus('Your turn');
        }
        
        function updateConnectFourUI() {
            const cells = document.querySelectorAll('.connect-four-cell');
            for (let row = 0; row < 6; row++) {
                for (let col = 0; col < 7; col++) {
                    const index = row * 7 + col;
                    cells[index].className = 'connect-four-cell';
                    if (connectFourBoard[row][col] === 1) {
                        cells[index].classList.add('connect-four-red');
                    } else if (connectFourBoard[row][col] === 2) {
                        cells[index].classList.add('connect-four-yellow');
                    }
                }
            }
        }
        
        function updateConnectFourStatus(message) {
            document.getElementById('connect-four-status').textContent = message;
        }
        
        function restartConnectFour() {
            startConnectFour(connectFourDifficulty);
        }
        
        // Tic Tac Toe Game
        let ticTacToeBoard = ['', '', '', '', '', '', '', '', ''];
        let ticTacToeCurrentPlayer = 'X'; // X for player, O for computer
        let ticTacToeDifficulty = 'easy';
        let ticTacToeGameActive = false;
        
        function startTicTacToe(difficulty) {
            ticTacToeDifficulty = difficulty;
            document.getElementById('tic-tac-toe-difficulty').textContent = 
                `(${difficulty.charAt(0).toUpperCase() + difficulty.slice(1)} Mode)`;
            document.getElementById('game-selection').style.display = 'none';
            document.getElementById('tic-tac-toe-game').style.display = 'block';
            
            // Show the appropriate algorithm info
            const easyModeInfo = document.querySelector('#tic-tac-toe-algorithm-info .easy-mode-info');
            const hardModeInfo = document.querySelector('#tic-tac-toe-algorithm-info .hard-mode-info');
            
            if (difficulty === 'easy') {
                easyModeInfo.style.display = 'inline';
                hardModeInfo.style.display = 'none';
            } else {
                easyModeInfo.style.display = 'none';
                hardModeInfo.style.display = 'inline';
            }
            
            // Initialize the board
            ticTacToeBoard = ['', '', '', '', '', '', '', '', ''];
            ticTacToeCurrentPlayer = 'X';
            ticTacToeGameActive = true;
            
            // Create the board UI
            const board = document.getElementById('tic-tac-toe-board');
            board.innerHTML = '';
            
            for (let i = 0; i < 9; i++) {
                const cell = document.createElement('div');
                cell.className = 'tic-tac-toe-cell';
                cell.dataset.index = i;
                cell.addEventListener('click', () => makeTicTacToeMove(i));
                board.appendChild(cell);
            }
            
            updateTicTacToeStatus('Your turn (X)');
        }
        
        async function makeTicTacToeMove(index) {
            if (!ticTacToeGameActive || ticTacToeBoard[index] !== '' || ticTacToeCurrentPlayer !== 'X') return;
            
            // Update UI to show move is processing
            updateTicTacToeStatus('Processing move...');
            ticTacToeCurrentPlayer = null; // Prevent additional clicks
            
            // Call API to make the move
            const response = await callGameApi('tic-tac-toe', {
                board: ticTacToeBoard,
                difficulty: ticTacToeDifficulty,
                make_move: index
            });
            
            console.log('API Response:', response); // Debug logging
            
            if (response.error) {
                ticTacToeCurrentPlayer = 'X'; // Re-enable player moves
                updateTicTacToeStatus('Error: ' + response.error);
                return;
            }
            
            // Update board with API response
            ticTacToeBoard = response.board;
            console.log('Updated board:', ticTacToeBoard); // Debug logging
            
            updateTicTacToeUI();
            
            // Check for game end
            if (response.game_over) {
                if (response.winner === 'human') {
                    updateTicTacToeStatus('You win!');
                } else if (response.winner === 'ai') {
                    updateTicTacToeStatus('Computer wins!');
                } else {
                    updateTicTacToeStatus("It's a draw!");
                }
                ticTacToeGameActive = false;
                return;
            }
            
            ticTacToeCurrentPlayer = 'X';
            updateTicTacToeStatus('Your turn (X)');
        }
        
        function updateTicTacToeUI() {
            const cells = document.querySelectorAll('.tic-tac-toe-cell');
            for (let i = 0; i < 9; i++) {
                cells[i].textContent = ticTacToeBoard[i];
                // Reset any existing marker classes
                cells[i].classList.remove('x-marker', 'o-marker');
                
                // Add marker classes based on board state
                if (ticTacToeBoard[i] === 'X') {
                    cells[i].classList.add('x-marker');
                } else if (ticTacToeBoard[i] === 'O') {
                    cells[i].classList.add('o-marker');
                }
            }
        }
        
        function updateTicTacToeStatus(message) {
            document.getElementById('tic-tac-toe-status').textContent = message;
        }
        
        function restartTicTacToe() {
            startTicTacToe(ticTacToeDifficulty);
        }
        
        // Initialize
        showGameSelection();
    </script>
</body>
</html>
