<?php

// Fonction d'évaluation simple pour Tic Tac Toe
function evaluate($board) {
    // Vérifier lignes, colonnes et diagonales
    for ($row = 0; $row < 3; $row++) {
        if ($board[$row][0] !== '' && $board[$row][0] === $board[$row][1] && $board[$row][1] === $board[$row][2]) {
            return ($board[$row][0] === 'X') ? 10 : -10;
        }
    }
    for ($col = 0; $col < 3; $col++) {
        if ($board[0][$col] !== '' && $board[0][$col] === $board[1][$col] && $board[1][$col] === $board[2][$col]) {
            return ($board[0][$col] === 'X') ? 10 : -10;
        }
    }
    if ($board[0][0] !== '' && $board[0][0] === $board[1][1] && $board[1][1] === $board[2][2]) {
        return ($board[0][0] === 'X') ? 10 : -10;
    }
    if ($board[0][2] !== '' && $board[0][2] === $board[1][1] && $board[1][1] === $board[2][0]) {
        return ($board[0][2] === 'X') ? 10 : -10;
    }

    return 0;
}

// Vérifier s'il reste des coups
function isMovesLeft($board) {
    for ($i = 0; $i < 3; $i++) {
        for ($j = 0; $j < 3; $j++) {
            if ($board[$i][$j] === '') {
                return true;
            }
        }
    }
    return false;
}

// Implémentation de Minimax (Easy mode)
function minimax($board, $depth, $isMaximizingPlayer) {
    $score = evaluate($board);

    if ($score === 10 || $score === -10) {
        return $score;
    }
    if (!isMovesLeft($board) || $depth === 0) {
        return 0;
    }

    if ($isMaximizingPlayer) {
        $best = -INF;
        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 3; $j++) {
                if ($board[$i][$j] === '') {
                    $board[$i][$j] = 'X';
                    $best = max($best, minimax($board, $depth - 1, false));
                    $board[$i][$j] = '';
                }
            }
        }
        return $best;
    } else {
        $best = INF;
        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 3; $j++) {
                if ($board[$i][$j] === '') {
                    $board[$i][$j] = 'O';
                    $best = min($best, minimax($board, $depth - 1, true));
                    $board[$i][$j] = '';
                }
            }
        }
        return $best;
    }
}

// Implémentation de Alpha-Beta Pruning (Hard mode)
function alphabeta($board, $depth, $alpha, $beta, $isMaximizingPlayer) {
    $score = evaluate($board);

    if ($score === 10 || $score === -10) {
        return $score;
    }
    if (!isMovesLeft($board) || $depth === 0) {
        return 0;
    }

    if ($isMaximizingPlayer) {
        $best = -INF;
        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 3; $j++) {
                if ($board[$i][$j] === '') {
                    $board[$i][$j] = 'X';
                    $best = max($best, alphabeta($board, $depth - 1, $alpha, $beta, false));
                    $board[$i][$j] = '';
                    $alpha = max($alpha, $best);
                    if ($beta <= $alpha) {
                        break 2; // Coupure Beta
                    }
                }
            }
        }
        return $best;
    } else {
        $best = INF;
        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 3; $j++) {
                if ($board[$i][$j] === '') {
                    $board[$i][$j] = 'O';
                    $best = min($best, alphabeta($board, $depth - 1, $alpha, $beta, true));
                    $board[$i][$j] = '';
                    $beta = min($beta, $best);
                    if ($beta <= $alpha) {
                        break 2; // Coupure Alpha
                    }
                }
            }
        }
        return $best;
    }
}

// Fonction pour tester les performances
function performance_test($board, $depth) {
    echo "Début du test de performance...\n\n";

    // Minimax
    $start_time_minimax = microtime(true);
    minimax($board, $depth, true);
    $end_time_minimax = microtime(true);
    $minimax_duration = $end_time_minimax - $start_time_minimax;

    // Alpha-Beta
    $start_time_alphabeta = microtime(true);
    alphabeta($board, $depth, -INF, INF, true);
    $end_time_alphabeta = microtime(true);
    $alphabeta_duration = $end_time_alphabeta - $start_time_alphabeta;

    // Affichage
    echo "Résultats du test :\n";
    echo "-------------------\n";
    echo "Easy Mode (Minimax)   : " . number_format($minimax_duration, 6) . " secondes\n";
    echo "Hard Mode (Alpha-Beta): " . number_format($alphabeta_duration, 6) . " secondes\n";

   
}

// Exemple de plateau (un peu rempli pour un test plus intéressant)
$initialBoard = [
    ['X', 'O', 'X'],
    ['', 'O', ''],
    ['', '', '']
];

// Profondeur pour le test
$depth = 5;

// Lancer le test
performance_test($initialBoard, $depth);

?>
