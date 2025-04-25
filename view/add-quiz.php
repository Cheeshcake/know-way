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

// Get the course ID from the URL
if (!isset($_GET['course_id']) || empty($_GET['course_id'])) {
    // Redirect to dashboard
    header('Location: dashboard.php');
    exit;
}

$course_id = $_GET['course_id'];
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Query to get course details
$course_sql = "SELECT * FROM courses WHERE id = ?";
$stmt = $conn->prepare($course_sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Course not found, redirect
    header('Location: dashboard.php?error=' . urlencode('Course not found'));
    exit;
}

$course = $result->fetch_assoc();

// Check if user is allowed to add a quiz (course creator or admin)
if ($course['creator_id'] !== $user_id && $user_role !== 'admin') {
    // Not authorized, redirect
    header('Location: course-details.php?id=' . $course_id . '&error=' . urlencode('You are not authorized to add quizzes to this course'));
    exit;
}

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    if (empty($_POST['title']) || empty($_POST['description'])) {
        $error_message = 'Quiz title and description are required.';
    } elseif (empty($_POST['question_1']) || empty($_POST['question_2']) || empty($_POST['question_3']) || 
              empty($_POST['question_4']) || empty($_POST['question_5'])) {
        $error_message = 'All questions are required.';
    } elseif (empty($_POST['q1_option_a']) || empty($_POST['q1_option_b']) || empty($_POST['q1_option_c']) || empty($_POST['q1_option_d']) ||
              empty($_POST['q2_option_a']) || empty($_POST['q2_option_b']) || empty($_POST['q2_option_c']) || empty($_POST['q2_option_d']) ||
              empty($_POST['q3_option_a']) || empty($_POST['q3_option_b']) || empty($_POST['q3_option_c']) || empty($_POST['q3_option_d']) ||
              empty($_POST['q4_option_a']) || empty($_POST['q4_option_b']) || empty($_POST['q4_option_c']) || empty($_POST['q4_option_d']) ||
              empty($_POST['q5_option_a']) || empty($_POST['q5_option_b']) || empty($_POST['q5_option_c']) || empty($_POST['q5_option_d'])) {
        $error_message = 'All answer options are required.';
    } elseif (empty($_POST['q1_correct']) || empty($_POST['q2_correct']) || empty($_POST['q3_correct']) || 
              empty($_POST['q4_correct']) || empty($_POST['q5_correct'])) {
        $error_message = 'Correct answers must be selected for all questions.';
    } else {
        // All validation passed, insert quiz
        $conn->begin_transaction();
        
        try {
            // Insert quiz
            $quiz_sql = "INSERT INTO course_quizzes (course_id, title, description, created_at) VALUES (?, ?, ?, NOW())";
            $quiz_stmt = $conn->prepare($quiz_sql);
            if (!$quiz_stmt) {
                throw new Exception("Error preparing quiz statement: " . $conn->error);
            }
            $quiz_stmt->bind_param("iss", $course_id, $_POST['title'], $_POST['description']);
            $result = $quiz_stmt->execute();
            if (!$result) {
                throw new Exception("Error executing quiz statement: " . $quiz_stmt->error);
            }
            
            $quiz_id = $conn->insert_id;
            
            // Insert questions and answers
            for ($i = 1; $i <= 5; $i++) {
                $question_text = $_POST['question_' . $i];
                $question_type = 'multiple_choice'; // Default type
                
                // Insert question
                $question_sql = "INSERT INTO quiz_questions (quiz_id, question, type, created_at) VALUES (?, ?, ?, NOW())";
                $question_stmt = $conn->prepare($question_sql);
                $question_stmt->bind_param("iss", $quiz_id, $question_text, $question_type);
                $question_stmt->execute();
                
                $question_id = $conn->insert_id;
                
                // Get the correct answer option
                $correct_answer = $_POST['q' . $i . '_correct'];
                
                // Insert answer options
                $option_values = [
                    'A' => $_POST['q' . $i . '_option_a'],
                    'B' => $_POST['q' . $i . '_option_b'],
                    'C' => $_POST['q' . $i . '_option_c'],
                    'D' => $_POST['q' . $i . '_option_d']
                ];
                
                foreach ($option_values as $option_key => $answer_text) {
                    $is_correct = ($option_key == $correct_answer) ? 1 : 0;
                    
                    $answer_sql = "INSERT INTO quiz_answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)";
                    $answer_stmt = $conn->prepare($answer_sql);
                    $answer_stmt->bind_param("isi", $question_id, $answer_text, $is_correct);
                    $answer_stmt->execute();
                }
            }
            
            $conn->commit();
            
            // Redirect to course details with success message
            header('Location: course-details.php?id=' . $course_id . '&success=' . urlencode('Quiz added successfully!'));
            exit;
            
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = 'Error creating quiz: ' . $e->getMessage();
        }
    }
}

// Success or error messages from URL parameters
if (empty($success_message)) {
    $success_message = $_GET['success'] ?? '';
}
if (empty($error_message)) {
    $error_message = $_GET['error'] ?? '';
}

// Check if there are existing quizzes for this course
$quiz_sql = "SELECT id, title FROM course_quizzes WHERE course_id = ?";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Quiz - KnowWay</title>
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
        }
        
        .quiz-form-container {
            max-width: 1200px;
            width: 100%;
            margin: 40px auto;
            padding: 40px;
            background-color: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
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
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            animation: slideDown 0.3s ease-out;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
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
        
        .form-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--gray-900);
            line-height: 1.2;
        }
        
        .form-course-title {
            color: var(--primary);
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 32px;
            display: block;
        }
        
        .form-subtitle {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 40px 0 16px;
            color: var(--gray-800);
            padding-bottom: 12px;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-subtitle:before {
            content: '';
            display: inline-block;
            width: 24px;
            height: 24px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%234f46e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
        
        .form-description {
            color: var(--gray-600);
            margin-bottom: 24px;
            font-size: 1rem;
            line-height: 1.6;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--gray-700);
            font-size: 0.95rem;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            font-size: 1rem;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            transition: all 0.3s;
            color: var(--gray-800);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }
        
        .form-control::placeholder {
            color: var(--gray-400);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
            line-height: 1.6;
        }
        
        .question-container {
            background-color: var(--gray-50);
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 30px;
            border: 1px solid var(--gray-200);
            transition: all 0.3s;
            position: relative;
        }
        
        .question-container:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border-color: var(--gray-300);
        }
        
        .question-header {
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1rem;
        }
        
        .question-number {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background-color: var(--primary);
            color: white;
            border-radius: 50%;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .options-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        
        .option-container {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            transition: all 0.2s;
        }
        
        .option-container:hover .option-label {
            background-color: var(--primary-light);
            color: var(--primary);
        }
        
        .option-label {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background-color: var(--gray-200);
            border-radius: 50%;
            font-weight: 600;
            color: var(--gray-600);
            flex-shrink: 0;
            transition: all 0.2s;
        }
        
        .option-input {
            flex-grow: 1;
        }
        
        .radio-group {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-top: 20px;
        }
        
        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s;
            cursor: pointer;
            border: 1px solid var(--gray-200);
        }
        
        .radio-option:hover {
            background-color: var(--gray-100);
        }
        
        .radio-option input[type="radio"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
            cursor: pointer;
        }
        
        .radio-option label {
            font-weight: 500;
            cursor: pointer;
        }
        
        .correct-answer-label {
            display: block;
            margin-bottom: 12px;
            font-weight: 500;
            color: var(--gray-700);
            font-size: 0.95rem;
        }
        
        .correct-answer-label:before {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            margin-right: 8px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%2310b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            vertical-align: middle;
        }
        
        .submit-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 14px 28px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .submit-btn:before {
            content: '';
            display: inline-block;
            width: 20px;
            height: 20px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
        
        .submit-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.1);
        }
        
        .cancel-btn {
            background-color: var(--gray-100);
            color: var(--gray-600);
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: 14px 28px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            margin-right: 16px;
        }
        
        .cancel-btn:hover {
            background-color: var(--gray-200);
            color: var(--gray-700);
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--gray-200);
        }
        
        .progress-indicator {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            position: relative;
        }
        
        .progress-indicator:before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background-color: var(--gray-200);
            transform: translateY(-50%);
            z-index: 0;
        }
        
        .progress-step {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: white;
            border: 2px solid var(--gray-300);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--gray-500);
            position: relative;
            z-index: 1;
        }
        
        .progress-step.active {
            background-color: var(--primary);
            border-color: var(--primary);
            color: white;
        }
        
        .progress-step.completed {
            background-color: var(--success);
            border-color: var(--success);
            color: white;
        }
        
        @media (max-width: 768px) {
            .quiz-form-container {
                padding: 20px;
                margin: 20px;
                border-radius: 12px;
            }
            
            .options-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            
            .form-actions {
                flex-direction: column-reverse;
                gap: 12px;
            }
            
            .cancel-btn {
                margin-right: 0;
            }
            
            .submit-btn, .cancel-btn {
                width: 100%;
                justify-content: center;
            }
            
            .radio-group {
                flex-direction: column;
                gap: 8px;
            }
            
            .radio-option {
                width: 100%;
            }
            
            .form-title {
                font-size: 1.6rem;
            }
            
            .form-subtitle {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="quiz-form-container">
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
        
        <h1 class="form-title">Create New Quiz</h1>
        <span class="form-course-title">For course: "<?= htmlspecialchars($course['title']) ?>"</span>
        
        <form method="POST" action="" style="max-width: 1200px; width: 100%; margin: 0 auto;">
            <div class="form-group">
                <label for="title" class="form-label">Quiz Title</label>
                <input type="text" id="title" name="title" class="form-control" required maxlength="255" 
                       placeholder="Enter a descriptive title for your quiz">
            </div>
            
            <div class="form-group">
                <label for="description" class="form-label">Quiz Description</label>
                <textarea id="description" name="description" class="form-control" required
                          placeholder="Provide a brief description of what this quiz covers and what students will learn"></textarea>
            </div>
            
            <h2 class="form-subtitle">Quiz Questions</h2>
            <p class="form-description">Create 5 multiple choice questions to test the learner's understanding of the course content. Each question must have one correct answer.</p>
            
            
            <?php for ($i = 1; $i <= 5; $i++): ?>
            <div class="question-container" id="question-<?= $i ?>">
                <div class="question-header">
                    <span class="question-number"><?= $i ?></span>
                    <label for="question_<?= $i ?>">Question <?= $i ?></label>
                </div>
                
                <div class="form-group">
                    <input type="text" id="question_<?= $i ?>" name="question_<?= $i ?>" class="form-control" 
                           placeholder="Enter your question here" required maxlength="500">
                </div>
                
                <div class="options-grid">
                    <div class="option-container">
                        <span class="option-label">A</span>
                        <input type="text" name="q<?= $i ?>_option_a" class="form-control option-input" 
                               placeholder="Option A" required maxlength="255">
                    </div>
                    
                    <div class="option-container">
                        <span class="option-label">B</span>
                        <input type="text" name="q<?= $i ?>_option_b" class="form-control option-input" 
                               placeholder="Option B" required maxlength="255">
                    </div>
                    
                    <div class="option-container">
                        <span class="option-label">C</span>
                        <input type="text" name="q<?= $i ?>_option_c" class="form-control option-input" 
                               placeholder="Option C" required maxlength="255">
                    </div>
                    
                    <div class="option-container">
                        <span class="option-label">D</span>
                        <input type="text" name="q<?= $i ?>_option_d" class="form-control option-input" 
                               placeholder="Option D" required maxlength="255">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="correct-answer-label">Select the correct answer</label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" id="q<?= $i ?>_correct_a" name="q<?= $i ?>_correct" value="A" required>
                            <label for="q<?= $i ?>_correct_a">Option A is correct</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" id="q<?= $i ?>_correct_b" name="q<?= $i ?>_correct" value="B">
                            <label for="q<?= $i ?>_correct_b">Option B is correct</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" id="q<?= $i ?>_correct_c" name="q<?= $i ?>_correct" value="C">
                            <label for="q<?= $i ?>_correct_c">Option C is correct</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" id="q<?= $i ?>_correct_d" name="q<?= $i ?>_correct" value="D">
                            <label for="q<?= $i ?>_correct_d">Option D is correct</label>
                        </div>
                    </div>
                </div>
            </div>
            <?php endfor; ?>
            
            <div class="form-actions">
                <button type="button" class="cancel-btn" onclick="location.href='course-details.php?id=<?= $course_id ?>'">Cancel</button>
                <button type="submit" class="submit-btn">Save Quiz</button>
            </div>
        </form>
    </div>

    <script>
        function closeAlert(alertId) {
            const alert = document.getElementById(alertId);
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        }
        
        // Track active question for progress indicator
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.question-container input, .question-container textarea');
            const progressSteps = document.querySelectorAll('.progress-step');
            
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    const questionContainer = this.closest('.question-container');
                    const questionId = questionContainer.id;
                    const stepNumber = questionId.split('-')[1];
                    
                    // Update active step
                    progressSteps.forEach(step => {
                        step.classList.remove('active');
                        if (step.dataset.step === stepNumber) {
                            step.classList.add('active');
                        }
                    });
                });
            });
            
            // Mark completed steps when all required fields in a question are filled
            function checkQuestionCompletion() {
                for (let i = 1; i <= 5; i++) {
                    const questionInputs = document.querySelectorAll(`#question-${i} input[required]`);
                    const radioInputs = document.querySelectorAll(`input[name="q${i}_correct"]`);
                    
                    let isComplete = true;
                    let hasCheckedRadio = false;
                    
                    questionInputs.forEach(input => {
                        if (!input.value) {
                            isComplete = false;
                        }
                    });
                    
                    radioInputs.forEach(radio => {
                        if (radio.checked) {
                            hasCheckedRadio = true;
                        }
                    });
                    
                    if (isComplete && hasCheckedRadio) {
                        progressSteps[i-1].classList.add('completed');
                    } else {
                        progressSteps[i-1].classList.remove('completed');
                    }
                }
            }
            
            // Check completion on input change
            inputs.forEach(input => {
                input.addEventListener('input', checkQuestionCompletion);
                input.addEventListener('change', checkQuestionCompletion);
            });
        });
    </script>
</body>
</html>