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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    $quiz_completed = true;
    
    // Check for submitted answers
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'answer_') === 0) {
            $question_id = substr($key, 7); // Get the question_id part
            $user_answers[$question_id] = $value;
        }
    }
    
    // Validate that all questions were answered
    if (count($user_answers) !== $total_questions) {
        $error_message = 'Please answer all questions before submitting.';
        $quiz_completed = false;
    } else {
        // Process answers and calculate score
        $questions_stmt->execute(); // Re-execute to reset the result set
        $questions_result = $questions_stmt->get_result();
        
        while ($question = $questions_result->fetch_assoc()) {
            $question_id = $question['id'];
            if (isset($user_answers[$question_id]) && $user_answers[$question_id] === $question['correct_answer']) {
                $user_score++;
            }
        }
        
        // Calculate percentage score
        $score_percentage = ($user_score / $total_questions) * 100;
        
        // Store quiz attempt in database
        $attempt_sql = "INSERT INTO quiz_attempts (user_id, quiz_id, score, completed_at) VALUES (?, ?, ?, NOW())";
        $attempt_stmt = $conn->prepare($attempt_sql);
        $attempt_stmt->bind_param("iid", $user_id, $quiz_id, $score_percentage);
        $attempt_stmt->execute();
        
        $success_message = "Quiz completed! Your score: $user_score out of $total_questions (" . number_format($score_percentage, 1) . "%)";
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
        .quiz-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 40px 20px;
            background-color: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: none;
            border: none;
            cursor: pointer;
            color: #4f46e5;
            padding: 8px 12px;
            font-weight: 500;
            margin-bottom: 24px;
            border-radius: 6px;
            transition: all 0.2s;
        }
        
        .back-btn:hover {
            background-color: rgba(79, 70, 229, 0.05);
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
        
        .alert {
            padding: 16px 20px;
            margin-bottom: 24px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
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
        
        .quiz-header {
            margin-bottom: 32px;
        }
        
        .quiz-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: #111827;
        }
        
        .quiz-description {
            font-size: 1.1rem;
            color: #4b5563;
            margin-bottom: 16px;
            line-height: 1.6;
        }
        
        .quiz-info {
            display: flex;
            gap: 24px;
            margin-top: 12px;
            color: #6b7280;
            font-size: 0.95rem;
        }
        
        .quiz-info span {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .quiz-info span:before {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
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
        
        .question-container {
            background-color: #f9fafb;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            border: 1px solid #e5e7eb;
            transition: all 0.3s;
        }
        
        .question-container:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }
        
        .question-text {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: #111827;
        }
        
        .options-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .option-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            background-color: white;
        }
        
        .option-item:hover {
            border-color: #d1d5db;
            background-color: #f3f4f6;
        }
        
        .option-item input[type="radio"] {
            margin-right: 12px;
        }
        
        .option-item.selected {
            border-color: #4f46e5;
            background-color: rgba(79, 70, 229, 0.05);
        }
        
        .option-item.correct {
            border-color: #10b981;
            background-color: rgba(16, 185, 129, 0.05);
        }
        
        .option-item.incorrect {
            border-color: #ef4444;
            background-color: rgba(239, 68, 68, 0.05);
        }
        
        .option-label {
            font-size: 1rem;
            color: #4b5563;
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
        }
        
        .option-marker {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background-color: #e5e7eb;
            color: #4b5563;
            font-weight: 600;
            flex-shrink: 0;
        }
        
        .submit-btn {
            background-color: #4f46e5;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 14px 28px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 24px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .submit-btn:hover {
            background-color: #4338ca;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.1);
        }
        
        .results-container {
            background-color: #f9fafb;
            padding: 32px;
            border-radius: 12px;
            margin-top: 32px;
            border: 1px solid #e5e7eb;
            text-align: center;
        }
        
        .result-score {
            font-size: 3rem;
            font-weight: 700;
            color: #4f46e5;
            margin-bottom: 16px;
        }
        
        .result-message {
            font-size: 1.2rem;
            margin-bottom: 24px;
            color: #374151;
        }
        
        .result-detail {
            font-size: 1rem;
            color: #6b7280;
            margin-bottom: 32px;
        }
        
        .action-btns {
            display: flex;
            justify-content: center;
            gap: 16px;
        }
        
        .retry-btn {
            background-color: #f3f4f6;
            color: #4b5563;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .retry-btn:hover {
            background-color: #e5e7eb;
        }
        
        .feedback-indicator {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            margin-left: 12px;
        }
        
        .correct-indicator {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }
        
        .correct-indicator:before {
            content: "✓";
            font-weight: bold;
        }
        
        .incorrect-indicator {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }
        
        .incorrect-indicator:before {
            content: "✕";
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .quiz-container {
                padding: 20px 16px;
            }
            
            .quiz-title {
                font-size: 1.5rem;
            }
            
            .quiz-info {
                flex-direction: column;
                gap: 8px;
            }
            
            .question-container {
                padding: 16px;
            }
            
            .question-text {
                font-size: 1.1rem;
            }
            
            .option-item {
                padding: 10px 12px;
            }
            
            .results-container {
                padding: 24px 16px;
            }
            
            .result-score {
                font-size: 2.5rem;
            }
            
            .action-btns {
                flex-direction: column;
            }
            
            .submit-btn, .retry-btn {
                width: 100%;
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
        
        <?php if ($quiz_completed && empty($error_message)): ?>
            <div class="results-container">
                <div class="result-score"><?= number_format(($user_score / $total_questions) * 100, 1) ?>%</div>
                <div class="result-message">
                    <?php if (($user_score / $total_questions) >= 0.7): ?>
                        Congratulations! You've passed the quiz.
                    <?php else: ?>
                        You didn't pass this time. Try again!
                    <?php endif; ?>
                </div>
                <div class="result-detail">You got <?= $user_score ?> out of <?= $total_questions ?> questions correct.</div>
                
                <div class="action-btns">
                    <button class="retry-btn" onclick="location.reload()">Try Again</button>
                    <button class="submit-btn" onclick="location.href='course-details.php?id=<?= $course_id ?>'">Back to Course</button>
                </div>
            </div>
            
            <!-- Display question results -->
            <h2 style="margin-top: 40px; margin-bottom: 20px;">Your Answers</h2>
            
            <?php 
            $questions_stmt->execute(); // Re-execute to reset the result set
            $questions_result = $questions_stmt->get_result();
            $question_num = 1;
            
            while ($question = $questions_result->fetch_assoc()): 
                $question_id = $question['id'];
                $is_correct = isset($user_answers[$question_id]) && $user_answers[$question_id] === $question['correct_answer'];
            ?>
                <div class="question-container">
                    <div class="question-text">
                        Question <?= $question_num ?>: <?= htmlspecialchars($question['question_text']) ?>
                        <?php if ($is_correct): ?>
                            <span class="feedback-indicator correct-indicator"></span>
                        <?php else: ?>
                            <span class="feedback-indicator incorrect-indicator"></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="options-list">
                        <?php 
                        $options = [
                            'A' => $question['option_a'],
                            'B' => $question['option_b'],
                            'C' => $question['option_c'],
                            'D' => $question['option_d']
                        ];
                        
                        foreach ($options as $option_key => $option_text): 
                            $is_user_answer = isset($user_answers[$question_id]) && $user_answers[$question_id] === $option_key;
                            $is_correct_answer = $question['correct_answer'] === $option_key;
                            $option_class = "";
                            
                            if ($is_user_answer && $is_correct_answer) {
                                $option_class = "selected correct";
                            } elseif ($is_user_answer && !$is_correct_answer) {
                                $option_class = "selected incorrect";
                            } elseif (!$is_user_answer && $is_correct_answer) {
                                $option_class = "correct";
                            }
                        ?>
                            <div class="option-item <?= $option_class ?>">
                                <label class="option-label">
                                    <span class="option-marker"><?= $option_key ?></span>
                                    <?= htmlspecialchars($option_text) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php 
                $question_num++;
            endwhile; 
            ?>
            
        <?php else: ?>
            <form method="POST" action="">
                <?php 
                $questions_stmt->execute(); // Re-execute to reset the result set
                $questions_result = $questions_stmt->get_result();
                $question_num = 1;
                
                while ($question = $questions_result->fetch_assoc()): 
                ?>
                    <div class="question-container" id="question-<?= $question['id'] ?>">
                        <div class="question-text">Question <?= $question_num ?>: <?= htmlspecialchars($question['question_text']) ?></div>
                        
                        <div class="options-list">
                            <?php 
                            $options = [
                                'A' => $question['option_a'],
                                'B' => $question['option_b'],
                                'C' => $question['option_c'],
                                'D' => $question['option_d']
                            ];
                            
                            foreach ($options as $option_key => $option_text): 
                            ?>
                                <div class="option-item" onclick="selectOption(this, '<?= $question['id'] ?>', '<?= $option_key ?>')">
                                    <label class="option-label">
                                        <input type="radio" name="answer_<?= $question['id'] ?>" value="<?= $option_key ?>" style="display: none;" 
                                            <?= (isset($user_answers[$question['id']]) && $user_answers[$question['id']] === $option_key) ? 'checked' : '' ?>>
                                        <span class="option-marker"><?= $option_key ?></span>
                                        <?= htmlspecialchars($option_text) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
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
            document.getElementById(alertId).style.display = 'none';
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