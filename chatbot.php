<?php
// chatbot.php - API endpoint for the KnowWay chatbot
header('Content-Type: application/json');

// Enable error reporting for debugging (remove in production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Get the message from the request
$data = json_decode(file_get_contents('php://input'), true);
$message = $data['message'] ?? '';

if (!$message) {
    echo json_encode(['reply' => "I'm sorry, I didn't understand that. Could you please rephrase your question?", 'status' => 'error']);
    exit;
}

// Log the incoming message (optional, for debugging)
// file_put_contents('chatbot_log.txt', date('Y-m-d H:i:s') . " - User: $message\n", FILE_APPEND);

// Simple keyword-based responses for common questions
$keywords = [
    'hello' => "Hello! Welcome to KnowWay. How can I help you with your learning journey today?",
    'hi' => "Hi there! I'm KnowWay's assistant. What would you like to learn about?",
    'courses' => "We offer a wide range of courses including Web Development, Digital Marketing, Photography, Data Science, and more. Would you like me to recommend something specific?",
    'price' => "Many of our courses are free to start. Premium courses range from $19.99 to $199.99 depending on the content and instructor. We also offer subscription plans starting at $29.99/month for unlimited access.",
    'account' => "You can manage your account by clicking on your profile icon after logging in. From there, you can update your profile, change password, and manage your course enrollments.",
    'certificate' => "Yes, we provide certificates of completion for all our courses. These can be downloaded from your account dashboard after finishing a course.",
    'login' => "You can log in by clicking the 'Sign In' button at the top right of the page. If you've forgotten your password, there's a recovery option on the login page.",
    'register' => "To create a new account, click the 'Sign Up' button at the top right of the page. You'll need to provide your email address and create a password.",
    'help' => "I'm here to help! You can ask me about courses, account management, technical issues, or learning recommendations. What do you need assistance with?",
    'contact' => "You can reach our support team at support@knowway.com or through the Contact Us page. Our team is available Monday through Friday, 9 AM to 6 PM EST."
];

// Check for keyword matches first
$response = null;
foreach ($keywords as $key => $reply) {
    if (stripos($message, $key) !== false) {
        $response = $reply;
        break;
    }
}

// If no keyword match, use the Python script with OpenAI
if (!$response) {
    try {
        // Escape the message for shell safety
        $escaped = escapeshellarg($message);
        
        // Call the Python script with the message
        $command = "python3 chatbot.py $escaped";
        $output = shell_exec($command);
        
        if ($output) {
            $response = trim($output);
        } else {
            // Fallback response if Python script fails
            $response = "I'm currently experiencing some technical difficulties. Please try again later or contact our support team for assistance.";
        }
    } catch (Exception $e) {
        $response = "Sorry, I encountered an error processing your request. Please try again.";
    }
}

// Log the response (optional, for debugging)
// file_put_contents('chatbot_log.txt', date('Y-m-d H:i:s') . " - Bot: $response\n", FILE_APPEND);

// Return the response
echo json_encode([
    'reply' => $response,
    'status' => 'success'
]);
