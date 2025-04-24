<?php
include '../config/db.php';
$success = '';
$error = '';


if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $username = $_POST["username"] ?? '';
  $email = $_POST["email"] ?? '';
  $password = $_POST["password"] ?? '';
  $confirmPassword = $_POST["confirm-password"] ?? '';
  $terms = $_POST["terms"] ?? null;

  if (!$terms) {
    $error = "You must accept the terms and conditions.";
  } elseif ($password !== $confirmPassword) {
    $error = "Passwords do not match.";
  } else {
    // Vérifie si l'email existe déjà
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
      $error = "Email already registered.";
    } else {
      $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
      $stmt->bind_param("sss", $username, $email, $hashedPassword);

      if ($stmt->execute()) {
        $success = "Account created successfully!";
      } else {
        $error = "Error: " . $stmt->error;
      }

      $stmt->close();
    }

    $checkStmt->close();
  }

  $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>KnowWay - Sign Up</title>
    <link rel="stylesheet" href="styles.css" />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
    />
  </head>
  <body>
    <div class="container">
      <div class="form-container sign-up">
        <div class="brand-panel">
          <div class="brand-content">
            <h1 class="logo">KnowWay</h1>
            <p class="tagline">Join our community of learners and creators</p>
            <div class="decoration">
              <div class="circle circle-1"></div>
              <div class="circle circle-2"></div>
              <div class="circle circle-3"></div>
            </div>
          </div>
        </div>
        <div class="form-panel">
          <form method="post" action="signup.php">
            <h2>Create Account</h2>
            <p class="form-subtitle">
              Start your personalized learning journey today
            </p>

            <?php if($error): ?>
            <div class="error-message">
              <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <?php if($success): ?>
            <div class="success-message">
              <?php echo $success; ?>
            </div>
            <?php endif; ?>

            <div class="input-group">
              <input type="text" id="username" name="username" required />
              <label for="username">Full Name</label>
              <div class="line"></div>
            </div>

            <div class="input-group">
              <input type="email" id="email" name="email" required />
              <label for="email">Email</label>
              <div class="line"></div>
            </div>

            <div class="input-group">
              <input type="password" id="password" name="password" required />
              <label for="password">Password</label>
              <div class="line"></div>
            </div>

            <div class="input-group">
              <input type="password" id="confirm-password" name="confirm-password" required />
              <label for="confirm-password">Confirm Password</label>
              <div class="line"></div>
            </div>

            <div class="terms">
              <input type="checkbox" id="terms" name="terms" />
              <label for="terms"
                >I agree to the
                <a href="#terms-modal" class="terms-link">Terms & Conditions</a></label
              >
            </div>
            <div class="modal-overlay" id="terms-modal">
              <div class="modal-container">
                <div class="modal-header">
                  <h3>Terms & Conditions</h3>
                  <button class="modal-close">&times;</button>
                </div>
                <div class="modal-content">
                  <h4>1. Acceptance of Terms</h4>
                  <p>
                    By using KnowWay, you agree to these terms and conditions.
                    If you do not agree, please do not use our services.
                  </p>

                  <h4>2. User Responsibilities</h4>
                  <p>
                    You are responsible for maintaining the confidentiality of
                    your account and password and for restricting access to your
                    device.
                  </p>

                  <h4>3. Content</h4>
                  <p>
                    All content provided on KnowWay is for informational
                    purposes only. We make no representations as to accuracy or
                    completeness.
                  </p>

                  <h4>4. Modifications</h4>
                  <p>
                    We reserve the right to modify these terms at any time. Your
                    continued use constitutes acceptance of those changes.
                  </p>
                </div>
                <div class="modal-footer">
                  <button class="btn modal-agree">I Agree</button>
                </div>
              </div>
            </div>

            <button type="submit" class="btn">Create Account</button>

            <div class="divider">
              <span>or</span>
            </div>

            <div class="social-login">
              <button type="button" class="social-btn google">
                <span>Sign up with Google</span>
              </button>
            </div>

            <p class="signin-link">
              Already have an account? <a href="index.php">Sign In</a>
            </p>
          </form>
        </div>
      </div>
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const termsLink = document.querySelector('.terms-link');
        const termsModal = document.getElementById('terms-modal');
        const modalClose = document.querySelector('.modal-close');
        const modalAgree = document.querySelector('.modal-agree');
        const termsCheckbox = document.getElementById('terms');
        
        if (termsLink && termsModal) {
          termsLink.addEventListener('click', function(e) {
            e.preventDefault();
            termsModal.classList.add('active');
          });
          
          if (modalClose) {
            modalClose.addEventListener('click', function() {
              termsModal.classList.remove('active');
            });
          }
          
          if (modalAgree) {
            modalAgree.addEventListener('click', function() {
              termsCheckbox.checked = true;
              termsModal.classList.remove('active');
            });
          }
          
          window.addEventListener('click', function(e) {
            if (e.target === termsModal) {
              termsModal.classList.remove('active');
            }
          });
        }
      });
    </script>
  </body>
</html>
