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

// Check if we're resetting the quiz
if (isset($_POST['reset_quiz'])) {
    $quiz_id = $_GET['id'] ?? 0;
    // Remove session data for this quiz
    if (isset($_SESSION['quiz_answers'][$quiz_id])) {
        unset($_SESSION['quiz_answers'][$quiz_id]);
    }
    if (isset($_SESSION['quiz_score'][$quiz_id])) {
        unset($_SESSION['quiz_score'][$quiz_id]);
    }
    // Redirect to the quiz without the completed parameter
    header("Location: take-quiz.php?id=" . $quiz_id);
    exit;
}

// Get the quiz ID from the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Redirect to dashboard
    header('Location: dashboard.php');
    exit;
}

$quiz_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Query to get quiz details
$quiz_sql = "SELECT q.*, c.id as course_id, c.title as course_title 
             FROM course_quizzes q 
             JOIN courses c ON q.course_id = c.id 
             WHERE q.id = ?";
$stmt = $conn->prepare($quiz_sql);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Quiz not found, redirect
    header('Location: dashboard.php?error=' . urlencode('Quiz not found'));
    exit;
}

$quiz = $result->fetch_assoc();
$course_id = $quiz['course_id'];

// Get questions for this quiz
$questions_sql = "SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY id ASC";
$questions_stmt = $conn->prepare($questions_sql);
$questions_stmt->bind_param("i", $quiz_id);
$questions_stmt->execute();
$questions_result = $questions_stmt->get_result();

// Initialize variables
$success_message = '';
$error_message = '';
$total_questions = $questions_result->num_rows;
$user_score = 0;
$quiz_completed = false;
$user_answers = [];

// Process quiz submission
if (isset($_POST['submit_quiz'])) {
    $submitted_answers = $_POST['answers'] ?? [];
    $score = 0;
    $total_questions = 0;
    $user_answers = [];
    
    // Get all questions for this quiz
    $questions_sql = "SELECT * FROM quiz_questions WHERE quiz_id = ?";
    $questions_stmt = $conn->prepare($questions_sql);
    $questions_stmt->bind_param("i", $quiz_id);
    $questions_stmt->execute();
    $questions_result = $questions_stmt->get_result();
    
    // Set up attempt tracking (with fallback if tables don't exist)
    $has_attempt_tracking = true;
    $attempt_id = 0;
    
    try {
        // Check if quiz_attempts table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'quiz_attempts'");
        if ($table_check->num_rows > 0) {
            // Insert a new quiz attempt
            $attempt_sql = "INSERT INTO quiz_attempts (user_id, quiz_id, completed, started_at, completed_at) VALUES (?, ?, 0, NOW(), NOW())";
            $attempt_stmt = $conn->prepare($attempt_sql);
            if ($attempt_stmt) {
                $attempt_stmt->bind_param("ii", $user_id, $quiz_id);
                $attempt_stmt->execute();
                $attempt_id = $conn->insert_id;
            } else {
                $has_attempt_tracking = false;
            }
        } else {
            $has_attempt_tracking = false;
        }
    } catch (Exception $e) {
        $has_attempt_tracking = false;
    }
    
    while ($question = $questions_result->fetch_assoc()) {
        $total_questions++;
        $question_id = $question['id'];
        
        // Get the correct answer for this question
        $correct_answer_sql = "SELECT id FROM quiz_answers WHERE question_id = ? AND is_correct = 1";
        $correct_answer_stmt = $conn->prepare($correct_answer_sql);
        $correct_answer_stmt->bind_param("i", $question_id);
        $correct_answer_stmt->execute();
        $correct_answer_result = $correct_answer_stmt->get_result();
        $correct_answer_row = $correct_answer_result->fetch_assoc();
        $correct_answer_id = $correct_answer_row['id'] ?? null;
        
        // Check if the user's answer is correct
        $user_answer_id = $submitted_answers[$question_id] ?? null;
        $is_correct = ($user_answer_id == $correct_answer_id) ? 1 : 0;
        
        if ($is_correct) {
            $score++;
        }
        
        // Store user's answer for both display and database
        $user_answers[$question_id] = [
            'user_answer_id' => $user_answer_id,
            'correct_answer_id' => $correct_answer_id,
            'is_correct' => $is_correct
        ];
        
        // Attempt to store in database if tracking is available
        if ($has_attempt_tracking && $attempt_id > 0) {
            try {
                $table_check = $conn->query("SHOW TABLES LIKE 'quiz_attempt_answers'");
                if ($table_check->num_rows > 0) {
                    $save_answer_sql = "INSERT INTO quiz_attempt_answers 
                                    (attempt_id, user_id, quiz_id, question_id, user_answer_id, is_correct) 
                                    VALUES (?, ?, ?, ?, ?, ?)";
                    $save_answer_stmt = $conn->prepare($save_answer_sql);
                    if ($save_answer_stmt) {
                        $save_answer_stmt->bind_param("iiiiii", $attempt_id, $user_id, $quiz_id, $question_id, $user_answer_id, $is_correct);
                        $save_answer_stmt->execute();
                    }
                }
            } catch (Exception $e) {
                // Silently continue if this fails
            }
        }
    }
    
    // Calculate percentage
    $percentage = ($total_questions > 0) ? round(($score / $total_questions) * 100) : 0;
    
    // Update the attempt with the score if applicable
    if ($has_attempt_tracking && $attempt_id > 0) {
        try {
            $update_attempt_sql = "UPDATE quiz_attempts SET score = ?, completed = 1, completed_at = NOW() WHERE id = ?";
            $update_attempt_stmt = $conn->prepare($update_attempt_sql);
            if ($update_attempt_stmt) {
                $update_attempt_stmt->bind_param("ii", $score, $attempt_id);
                $update_attempt_stmt->execute();
            }
        } catch (Exception $e) {
            // Silently continue if this fails
        }
    }
    
    // Store the answers in the session for display
    $_SESSION['quiz_answers'][$quiz_id] = $user_answers;
    $_SESSION['quiz_score'][$quiz_id] = [
        'score' => $score,
        'total' => $total_questions,
        'percentage' => $percentage
    ];
    
    // Set completed flag
    $_GET['completed'] = 1;
    $completed = 1;
    $user_score = $score;
}

// Display quiz content
if (isset($_GET['completed']) && $_GET['completed'] == 1) {
    // If we have score in session, use it
    if (isset($_SESSION['quiz_score'][$quiz_id])) {
        $user_score = $_SESSION['quiz_score'][$quiz_id]['score'];
        $total_questions = $_SESSION['quiz_score'][$quiz_id]['total'];
        $percentage = $_SESSION['quiz_score'][$quiz_id]['percentage'];
    } else {
        // Try to get from attempts table
        $score_sql = "SELECT score FROM quiz_attempts WHERE user_id = ? AND quiz_id = ? ORDER BY completed_at DESC LIMIT 1";
        $score_stmt = $conn->prepare($score_sql);
        $score_stmt->bind_param("ii", $user_id, $quiz_id);
        $score_stmt->execute();
        $score_result = $score_stmt->get_result();
        $score_row = $score_result->fetch_assoc();
        $user_score = $score_row['score'] ?? 0;
        
        // Get total questions
        $count_sql = "SELECT COUNT(*) as total FROM quiz_questions WHERE quiz_id = ?";
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param("i", $quiz_id);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $count_row = $count_result->fetch_assoc();
        $total_questions = $count_row['total'] ?? 0;
        
        // Calculate percentage
        $percentage = ($total_questions > 0) ? round(($user_score / $total_questions) * 100) : 0;
    }
    
    // Check if we have answers stored in session
    if (isset($_SESSION['quiz_answers'][$quiz_id])) {
        $user_answers = $_SESSION['quiz_answers'][$quiz_id];
    }
}

// Get existing attempts for this user
$attempts = [];
if ($user_id) {
    $attempts_sql = "SELECT * FROM quiz_attempts WHERE user_id = ? AND quiz_id = ? ORDER BY completed_at DESC";
    $attempts_stmt = $conn->prepare($attempts_sql);
    $attempts_stmt->bind_param("ii", $user_id, $quiz_id);
    $attempts_stmt->execute();
    $attempts_result = $attempts_stmt->get_result();
    
    while ($attempt = $attempts_result->fetch_assoc()) {
        $attempts[] = $attempt;
    }
}

// Success or error messages from URL parameters
if (empty($success_message)) {
    $success_message = $_GET['success'] ?? '';
}
if (empty($error_message)) {
    $error_message = $_GET['error'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($quiz['title']) ?> - KnowWay</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --primary-light: rgba(79, 70, 229, 0.1);
            --primary-lighter: rgba(79, 70, 229, 0.05);
            --success: #10b981;
            --success-light: rgba(16, 185, 129, 0.1);
            --danger: #ef4444;
            --danger-light: rgba(239, 68, 68, 0.1);
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
        }
        
        body {
            background-color: var(--gray-50);
            font-family: 'Inter', sans-serif;
            color: var(--gray-800);
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        
        .quiz-container {
            max-width: 1200px;
            width: 100%;
            margin: 40px auto;
            padding: 40px;
            background-color: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--primary);
            padding: 8px 12px;
            font-weight: 500;
            margin-bottom: 24px;
            border-radius: 6px;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .back-btn:hover {
            background-color: var(--primary-lighter);
            transform: translateX(-3px);
        }

        .back-icon {
            width: 18px;
            height: 18px;
            display: inline-block;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%234f46e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
        
        /* Enhanced Alert Styling */
        .alert {
            padding: 16px 20px;
            margin-bottom: 24px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            animation: slideDown 0.4s ease-out;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-success {
            background-color: #ecfdf5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-danger {
            background-color: #fef2f2;
            color: #b91c1c;
            border-left: 4px solid #ef4444;
        }

        .alert-close {
            background: none;
            border: none;
            color: inherit;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0;
            margin-left: 10px;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .alert-close:hover {
            opacity: 1;
        }
        
        /* Enhanced Quiz Header */
        .quiz-header {
            margin-bottom: 40px;
            position: relative;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .quiz-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 12px;
            color: var(--gray-900);
            line-height: 1.2;
            background: linear-gradient(to right, var(--gray-900), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-fill-color: transparent;
        }
        
        .quiz-description {
            font-size: 1.1rem;
            color: var(--gray-600);
            margin-bottom: 20px;
            line-height: 1.6;
            max-width: 800px;
        }
        
        .quiz-info {
            display: flex;
            gap: 24px;
            margin-top: 16px;
            color: var(--gray-500);
            font-size: 0.95rem;
        }
        
        .quiz-info span {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            background-color: var(--gray-100);
            border-radius: 20px;
            transition: all 0.3s;
        }
        
        .quiz-info span:hover {
            background-color: var(--gray-200);
            transform: translateY(-2px);
        }
        
        .quiz-info span:before {
            content: '';
            display: inline-block;
            width: 18px;
            height: 18px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
        
        .questions-count:before {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%236b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>');
        }
        
        .course-name:before {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%236b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>');
        }
        
        /* Enhanced Question Container */
        .question-container {
            background-color: white;
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 30px;
            border: 1px solid var(--gray-200);
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }
        
        .question-container:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            transform: translateY(-5px);
            border-color: var(--primary-light);
        }
        
        .question-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background-color: var(--primary);
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .question-container:hover::before {
            opacity: 1;
        }
        
        .question-text {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 25px;
            color: var(--gray-800);
            line-height: 1.4;
            position: relative;
            padding-left: 40px;
        }
        
        .question-text::before {
            content: 'Q';
            position: absolute;
            left: 0;
            top: 0;
            width: 30px;
            height: 30px;
            background-color: var(--primary-light);
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
        }
        
        /* Enhanced Options List */
        .options-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-left: 40px;
        }
        
        .option-item {
            display: flex;
            align-items: center;
            padding: 16px;
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            background-color: white;
            position: relative;
            overflow: hidden;
        }
        
        .option-item:hover {
            border-color: var(--primary);
            background-color: var(--primary-lighter);
            transform: translateX(8px);
        }
        
        .option-item input[type="radio"] {
            margin-right: 12px;
            width: 20px;
            height: 20px;
            accent-color: var(--primary);
            cursor: pointer;
        }
        
        .option-item.selected {
            border-color: var(--primary);
            background-color: var(--primary-light);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
        }
        
        .option-item.correct {
            border-color: var(--success);
            background-color: var(--success-light);
        }
        
        .option-item.incorrect {
            border-color: var(--danger);
            background-color: var(--danger-light);
        }
        
        .option-label {
            font-size: 1.05rem;
            color: var(--gray-700);
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            cursor: pointer;
        }
        
        .option-marker {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: var(--gray-100);
            color: var(--gray-600);
            font-weight: 600;
            flex-shrink: 0;
            transition: all 0.3s;
        }
        
        .option-item:hover .option-marker {
            background-color: var(--primary-light);
            color: var(--primary);
        }
        
        .option-item.selected .option-marker {
            background-color: var(--primary);
            color: white;
        }
        
        /* Enhanced Submit Button */
        .submit-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 16px 32px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        }
        
        .submit-btn::before {
            content: '';
            display: inline-block;
            width: 22px;
            height: 22px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
        
        .submit-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(79, 70, 229, 0.25);
        }
        
        /* Enhanced Results Container */
        .results-container {
            background-color: white;
            padding: 40px;
            border-radius: 16px;
            margin-top: 40px;
            border: 1px solid var(--gray-200);
            text-align: center;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
        }
        
        .results-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(to right, var(--primary), var(--success));
        }
        
        .result-score {
            font-size: 4rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 20px;
            line-height: 1;
            position: relative;
            display: inline-block;
        }
        
        .result-score::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background-color: var(--primary-light);
            border-radius: 2px;
        }
        
        .result-message {
            font-size: 1.4rem;
            margin-bottom: 24px;
            color: var(--gray-800);
            font-weight: 600;
        }
        
        .result-detail {
            font-size: 1.1rem;
            color: var(--gray-600);
            margin-bottom: 40px;
            padding: 12px 24px;
            background-color: var(--gray-50);
            border-radius: 30px;
            display: inline-block;
        }
        
        .action-btns {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
            align-items: center;
        }
        
        .action-btns form {
            margin: 0;
        }
        
        .retry-btn {
            background-color: white;
            color: var(--gray-700);
            border: 2px solid var(--gray-300);
            border-radius: 12px;
            padding: 14px 28px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .retry-btn::before {
            content: '';
            display: inline-block;
            width: 20px;
            height: 20px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%236b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"></path></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
        
        .retry-btn:hover {
            background-color: var(--gray-100);
            border-color: var(--gray-400);
            transform: translateY(-2px);
        }
        
        /* Enhanced Feedback Indicators */
        .feedback-indicator {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            margin-left: 12px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .correct-indicator {
            background-color: var(--success-light);
            color: var(--success);
        }
        
        .correct-indicator:before {
            content: "✓";
            font-weight: bold;
        }
        
        .incorrect-indicator {
            background-color: var(--danger-light);
            color: var(--danger);
        }
        
        .incorrect-indicator:before {
            content: "✕";
            font-weight: bold;
        }
        
        /* Enhanced Quiz Review */
        .quiz-review {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px dashed var(--gray-300);
        }

        .quiz-review h3 {
            font-size: 1.6rem;
            margin-bottom: 30px;
            color: var(--gray-800);
            text-align: center;
            font-weight: 700;
            position: relative;
            display: inline-block;
        }
        
        .quiz-review h3::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: var(--primary-light);
            border-radius: 2px;
        }

        .options-container {
            margin-top: 20px;
        }

        .options-container .option {
            padding: 16px;
            border-radius: 10px;
            margin-bottom: 12px;
            position: relative;
            background-color: var(--gray-50);
            border-left: 4px solid transparent;
            transition: all 0.3s;
        }
        
        .options-container .option:hover {
            transform: translateX(5px);
        }

        .correct-answer {
            background-color: rgba(16, 185, 129, 0.1) !important;
            border-left-color: var(--success) !important;
        }

        .incorrect-answer {
            background-color: rgba(239, 68, 68, 0.1) !important;
            border-left-color: var(--danger) !important;
        }

        .correct-option {
            background-color: rgba(79, 70, 229, 0.05) !important;
            border-left-color: var(--primary) !important;
        }

        .badge {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.85rem;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .bg-success {
            background-color: var(--success-light);
            color: var(--success);
        }
        
        .bg-danger {
            background-color: var(--danger-light);
            color: var(--danger);
        }
        
        .bg-info {
            background-color: var(--primary-light);
            color: var(--primary);
        }
        
        .card-title {
            font-weight: 600;
            font-size: 1.2rem;
            color: var(--gray-800);
            margin-bottom: 20px;
            position: relative;
            padding-left: 30px;
        }
        
        .card-title::before {
            content: 'Q';
            position: absolute;
            left: 0;
            top: 0;
            width: 24px;
            height: 24px;
            background-color: var(--primary-light);
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
        }
        
        .card-body {
            padding: 20px;
        }
        
        /* Progress Bar */
        .progress-container {
            width: 100%;
            height: 8px;
            background-color: var(--gray-200);
            border-radius: 4px;
            margin: 30px 0;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            background: linear-gradient(to right, var(--primary), var(--success));
            border-radius: 4px;
            transition: width 0.5s ease;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .quiz-container {
                padding: 20px;
                margin: 20px;
            }
            
            .quiz-title {
                font-size: 1.8rem;
            }
            
            .quiz-info {
                flex-direction: column;
                gap: 10px;
            }
            
            .question-container {
                padding: 20px;
            }
            
            .question-text {
                font-size: 1.1rem;
                padding-left: 30px;
            }
            
            .options-list {
                margin-left: 20px;
            }
            
            .option-item {
                padding: 12px;
            }
            
            .results-container {
                padding: 30px 20px;
            }
            
            .result-score {
                font-size: 3rem;
            }
            
            .result-message {
                font-size: 1.2rem;
            }
            
            .action-btns {
                flex-direction: column;
                gap: 15px;
            }
            
            .submit-btn, .retry-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="quiz-container">
        <a class="back-btn" href="course-details.php?id=<?= $course_id ?>">
            <span class="back-icon"></span>
            Back to Course
        </a>
        
        <?php if (!empty($success_message)): ?>
        <div class="alert alert-success" id="successAlert">
            <span><?= htmlspecialchars($success_message) ?></span>
            <button class="alert-close" onclick="closeAlert('successAlert')">&times;</button>
        </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger" id="errorAlert">
            <span><?= htmlspecialchars($error_message) ?></span>
            <button class="alert-close" onclick="closeAlert('errorAlert')">&times;</button>
        </div>
        <?php endif; ?>
        
        <div class="quiz-header">
            <h1 class="quiz-title"><?= htmlspecialchars($quiz['title']) ?></h1>
            <p class="quiz-description"><?= htmlspecialchars($quiz['description']) ?></p>
            <div class="quiz-info">
                <span class="questions-count"><?= $total_questions ?> Questions</span>
                <span class="course-name">Course: <?= htmlspecialchars($quiz['course_title']) ?></span>
            </div>
        </div>
        
        <?php if (isset($_GET['completed']) && $_GET['completed'] == 1): ?>
            <div class="results-container">
                <div class="result-score"><?= number_format($percentage, 1) ?>%</div>
                <div class="result-message">
                    <?php if ($percentage >= 70): ?>
                        Congratulations! You've passed the quiz.
                    <?php else: ?>
                        You didn't pass this time. Try again!
                    <?php endif; ?>
                </div>
                <div class="result-detail">You got <?= $user_score ?> out of <?= $total_questions ?> questions correct.</div>
                
                <div class="progress-container">
                    <div class="progress-bar" style="width: <?= $percentage ?>%;"></div>
                </div>
                
                <div class="action-btns">
                    <form method="post" action="take-quiz.php?id=<?= $quiz_id ?>" style="width: 100%; height: fit-content;">
                        <input type="hidden" name="reset_quiz" value="1">
                        <button type="submit" class="retry-btn">Try Again</button>
                    </form>
                    <button class="submit-btn" onclick="location.href='course-details.php?id=<?= $course_id ?>'">Back to Course</button>
                </div>
            </div>
            
            <!-- Questions Review -->
            <div class="quiz-review">
                <h3>Review Your Answers</h3>
                
                <?php
                // Get all questions for this quiz
                $questions_sql = "SELECT * FROM quiz_questions WHERE quiz_id = ?";
                $questions_stmt = $conn->prepare($questions_sql);
                $questions_stmt->bind_param("i", $quiz_id);
                $questions_stmt->execute();
                $questions_result = $questions_stmt->get_result();
                
                // Set up answer tracking from either database or session
                $has_attempt_tracking = true;
                $latest_attempt_id = 0;
                
                try {
                    // Check if quiz_attempts table exists and get latest attempt ID
                    $table_check = $conn->query("SHOW TABLES LIKE 'quiz_attempts'");
                    if ($table_check->num_rows > 0) {
                        $attempt_id_sql = "SELECT id FROM quiz_attempts WHERE user_id = ? AND quiz_id = ? ORDER BY completed_at DESC LIMIT 1";
                        $attempt_id_stmt = $conn->prepare($attempt_id_sql);
                        if ($attempt_id_stmt) {
                            $attempt_id_stmt->bind_param("ii", $user_id, $quiz_id);
                            $attempt_id_stmt->execute();
                            $attempt_id_result = $attempt_id_stmt->get_result();
                            $attempt_id_row = $attempt_id_result->fetch_assoc();
                            $latest_attempt_id = $attempt_id_row['id'] ?? 0;
                        } else {
                            $has_attempt_tracking = false;
                        }
                    } else {
                        $has_attempt_tracking = false;
                    }
                } catch (Exception $e) {
                    $has_attempt_tracking = false;
                }
                
                $question_num = 1;
                while ($question = $questions_result->fetch_assoc()):
                    $question_id = $question['id'];
                    
                    // Get user's answer data (try from database first, then session)
                    $user_answer_data = null;
                    
                    if ($has_attempt_tracking && $latest_attempt_id > 0) {
                        try {
                            $table_check = $conn->query("SHOW TABLES LIKE 'quiz_attempt_answers'");
                            if ($table_check->num_rows > 0) {
                                $user_attempt_sql = "SELECT user_answer_id, is_correct FROM quiz_attempt_answers 
                                                WHERE user_id = ? AND question_id = ? AND attempt_id = ?";
                                $user_attempt_stmt = $conn->prepare($user_attempt_sql);
                                if ($user_attempt_stmt) {
                                    $user_attempt_stmt->bind_param("iii", $user_id, $question_id, $latest_attempt_id);
                                    $user_attempt_stmt->execute();
                                    $user_attempt_result = $user_attempt_stmt->get_result();
                                    $user_answer_data = $user_attempt_result->fetch_assoc();
                                }
                            }
                        } catch (Exception $e) {
                            // Silently continue and fall back to session data
                        }
                    }
                    
                    // If not found in database, try session
                    if (!$user_answer_data && isset($_SESSION['quiz_answers'][$quiz_id][$question_id])) {
                        $user_answer_data = [
                            'user_answer_id' => $_SESSION['quiz_answers'][$quiz_id][$question_id]['user_answer_id'],
                            'is_correct' => $_SESSION['quiz_answers'][$quiz_id][$question_id]['is_correct']
                        ];
                    }
                    
                    // Get all options for this question
                    $options_sql = "SELECT * FROM quiz_answers WHERE question_id = ?";
                    $options_stmt = $conn->prepare($options_sql);
                    $options_stmt->bind_param("i", $question_id);
                    $options_stmt->execute();
                    $options_result = $options_stmt->get_result();
                ?>
                
                <div class="question-container card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Question <?= $question_num ?>: <?= htmlspecialchars($question['question']) ?></h5>
                        
                        <div class="options-container">
                            <?php 
                            // Get the correct answer ID for this question
                            $correct_answer_sql = "SELECT id FROM quiz_answers WHERE question_id = ? AND is_correct = 1";
                            $correct_answer_stmt = $conn->prepare($correct_answer_sql);
                            $correct_answer_stmt->bind_param("i", $question_id);
                            $correct_answer_stmt->execute();
                            $correct_answer_result = $correct_answer_stmt->get_result();
                            $correct_answer_row = $correct_answer_result->fetch_assoc();
                            $correct_answer_id = $correct_answer_row['id'] ?? null;
                            
                            while ($option = $options_result->fetch_assoc()): 
                                $is_user_answer = isset($user_answer_data) && $user_answer_data['user_answer_id'] == $option['id'];
                                $is_correct_answer = $option['is_correct'] == 1;
                                
                                // Determine the class for styling
                                $option_class = "";
                                if ($is_user_answer && $is_correct_answer) {
                                    $option_class = "correct-answer";
                                } else if ($is_user_answer && !$is_correct_answer) {
                                    $option_class = "incorrect-answer";
                                } else if (!$is_user_answer && $is_correct_answer) {
                                    $option_class = "correct-option";
                                }
                            ?>
                            <div class="option <?= $option_class ?>">
                                <?= htmlspecialchars($option['answer_text']) ?>
                                
                                <?php if ($is_user_answer && $is_correct_answer): ?>
                                    <span class="badge bg-success">Correct</span>
                                <?php elseif ($is_user_answer && !$is_correct_answer): ?>
                                    <span class="badge bg-danger">Incorrect</span>
                                <?php elseif (!$is_user_answer && $is_correct_answer): ?>
                                    <span class="badge bg-info">Correct Answer</span>
                                <?php endif; ?>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                <?php 
                    $question_num++;
                endwhile; 
                ?>
            </div>
        <?php else: ?>
            <form method="POST" action="" style="width: 100%; max-width: 100%;">
                <?php 
                $questions_stmt->execute(); // Re-execute to reset the result set
                $questions_result = $questions_stmt->get_result();
                $question_num = 1;
                
                while ($question = $questions_result->fetch_assoc()): 
                    $question_id = $question['id'];
                ?>
                    <div class="question-container" id="question-<?= $question_id ?>">
                        <div class="question-text">Question <?= $question_num ?>: <?= htmlspecialchars($question['question']) ?></div>
                        
                        <div class="options-list">
                            <?php 
                            // Get all answer options for this question
                            $options_sql = "SELECT * FROM quiz_answers WHERE question_id = ?";
                            $options_stmt = $conn->prepare($options_sql);
                            $options_stmt->bind_param("i", $question_id);
                            $options_stmt->execute();
                            $options_result = $options_stmt->get_result();
                            
                            $option_count = 0;
                            $option_labels = ['A', 'B', 'C', 'D'];
                            
                            while ($option = $options_result->fetch_assoc()):
                            ?>
                                <div class="option-item" onclick="selectOption(this, '<?= $question_id ?>', '<?= $option['id'] ?>')">
                                    <label class="option-label">
                                        <input type="radio" name="answers[<?= $question_id ?>]" value="<?= $option['id'] ?>" required>
                                        <span class="option-marker"><?= $option_labels[$option_count] ?></span>
                                        <?= htmlspecialchars($option['answer_text']) ?>
                                    </label>
                                </div>
                            <?php 
                                $option_count++;
                            endwhile; 
                            ?>
                        </div>
                    </div>
                <?php 
                    $question_num++;
                endwhile; 
                ?>
                
                <div style="text-align: center;">
                    <button type="submit" name="submit_quiz" class="submit-btn">Submit Quiz</button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
        function closeAlert(alertId) {
            const alert = document.getElementById(alertId);
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-15px)';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        }
        
        function selectOption(element, questionId, optionKey) {
            // Remove selected class from all options in this question
            const questionContainer = document.getElementById('question-' + questionId);
            const options = questionContainer.querySelectorAll('.option-item');
            options.forEach(option => {
                option.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            element.classList.add('selected');
            
            // Select the radio button
            const radio = element.querySelector('input[type="radio"]');
            radio.checked = true;
        }
    </script>
</body>
</html>
