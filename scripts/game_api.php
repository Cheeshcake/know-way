<?php
/**
 * Game API for KnowWay Game Center
 * This file handles API requests for the game center and connects to the Python game engines
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

// Set headers for JSON response
header('Content-Type: application/json');

// Get request data
$request_data = json_decode(file_get_contents('php://input'), true);
if (!$request_data) {
    echo json_encode(['error' => 'Invalid request data']);
    exit;
}

// Process request based on game type
$game_type = isset($request_data['game']) ? $request_data['game'] : '';

switch ($game_type) {
    case 'tic-tac-toe':
        handle_tic_tac_toe($request_data);
        break;
    case 'connect-four':
        handle_connect_four($request_data);
        break;
    default:
        echo json_encode(['error' => 'Invalid game type']);
        exit;
}

/**
 * Handle Tic Tac Toe game requests
 */
function handle_tic_tac_toe($data) {
    $script_path = __DIR__ . '/tic_tac_toe.py';
    
    // Check if the Python script exists
    if (!file_exists($script_path)) {
        echo json_encode(['error' => 'Game engine not found']);
        exit;
    }
    
    // Call the Python script
    $result = call_python_script($script_path, $data);
    echo $result;
}

/**
 * Handle Connect Four game requests
 */
function handle_connect_four($data) {
    $script_path = __DIR__ . '/connect_four.py';
    
    // Check if the Python script exists
    if (!file_exists($script_path)) {
        echo json_encode(['error' => 'Game engine not found']);
        exit;
    }
    
    // Call the Python script
    $result = call_python_script($script_path, $data);
    echo $result;
}

/**
 * Call a Python script with the given data and return the result
 */
function call_python_script($script_path, $data) {
    // Prepare command
    $python_cmd = 'python'; // Use 'python3' if on Linux/Mac
    
    // Encode data as JSON
    $json_data = json_encode($data);
    
    // Create descriptors for proc_open
    $descriptors = [
        0 => ["pipe", "r"],  // stdin
        1 => ["pipe", "w"],  // stdout
        2 => ["pipe", "w"]   // stderr
    ];
    
    // Open process
    $process = proc_open("$python_cmd $script_path", $descriptors, $pipes);
    
    if (is_resource($process)) {
        // Write to stdin
        fwrite($pipes[0], $json_data);
        fclose($pipes[0]);
        
        // Read from stdout
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        
        // Read from stderr
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        
        // Close process
        $return_value = proc_close($process);
        
        // Check for errors
        if ($return_value !== 0 || !empty($error)) {
            return json_encode([
                'error' => 'Python error: ' . $error,
                'code' => $return_value
            ]);
        }
        
        return $output;
    } else {
        return json_encode(['error' => 'Failed to start Python process']);
    }
}
?> 