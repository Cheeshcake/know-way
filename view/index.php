<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>KnowWay - Sign In</title>
    <link rel="stylesheet" href="styles.css" />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
    />
    <style>
      .error-message {
        background-color: #ffebee;
        color: #c62828;
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 15px;
        text-align: center;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="form-container sign-in">
        <div class="brand-panel">
          <div class="brand-content">
            <h1 class="logo">KnowWay</h1>
            <p class="tagline">
              Your personalized learning journey starts here
            </p>
            <div class="decoration">
              <div class="circle circle-1"></div>
              <div class="circle circle-2"></div>
              <div class="circle circle-3"></div>
            </div>
          </div>
        </div>
        <div class="form-panel">
          <form method="post" action="../controller/login.php">
            <h2>Welcome back</h2>
            <p class="form-subtitle">
              Sign in to continue your learning journey
            </p>

            <?php if(isset($_SESSION['login_error'])): ?>
            <div class="error-message">
              <?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?>
            </div>
            <?php endif; ?>

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

            <div class="remember-forgot">
              <div class="remember">
                <input type="checkbox" id="remember" name="remember" />
                <label for="remember">Remember me</label>
              </div>
              <a href="#" class="forgot">Forgot password?</a>
            </div>

            <button type="submit" class="btn">Sign In</button>

            <div class="divider">
              <span>or</span>
            </div>

            <div class="social-login">
              <button type="button" class="social-btn google">
                <span>Continue with Google</span>
              </button>
            </div>

            <p class="signup-link">
              Don't have an account? <a href="signup.php">Sign Up</a>
            </p>
          </form>
        </div>
      </div>
    </div>
  </body>
</html>
