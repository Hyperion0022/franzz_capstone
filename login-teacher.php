<?php
session_start();

// Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "quiz_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Login Processing
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please enter both email and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM teachers WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['username'];
                $_SESSION['role'] = "teacher";
                $_SESSION['success'] = "Successfully logged in!";
                header("Location: teacher_dashboard.php");
                exit();
            } else {
                $_SESSION['error'] = "Wrong password. Try again.";
            }
        } else {
            $_SESSION['error'] = "No account found with that email or username.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Teacher Login</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    body {
      background: url('choosing-bg11.jpg') center/cover no-repeat;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
    }

    body::before {
      content: "";
      position: absolute;
      inset: 0;
      background: rgba(0, 0, 0, 0.4);
      z-index: 0;
    }

    .login-card {
      position: relative;
      z-index: 1;
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
      width: 380px;
      padding: 3rem;
      text-align: center;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .login-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    }

    .login-card h2 {
      font-weight: 700;
      color: #333;
      margin-bottom: 1.5rem;
      font-size: 24px;
    }

    .form-label {
      font-weight: 600;
      color: #555;
    }

    .form-control {
      border-radius: 30px;
      padding: 12px 18px;
      font-size: 16px;
      border: 1px solid #ddd;
      transition: border 0.3s ease;
    }

    .form-control:focus {
      border-color: #4facfe;
      box-shadow: 0 0 5px rgba(79, 172, 254, 0.6);
    }

    .input-group .form-control {
      border-top-right-radius: 0;
      border-bottom-right-radius: 0;
    }

    .input-group .btn {
      border-top-left-radius: 0;
      border-bottom-left-radius: 0;
    }

    .btn-primary {
      border-radius: 30px;
      background: linear-gradient(to right, #4facfe, #00f2fe);
      border: none;
      font-weight: bold;
      padding: 12px;
      transition: 0.3s ease;
      width: 100%;
    }

    .btn-primary:hover {
      background: linear-gradient(to right, #00f2fe, #4facfe);
      transform: scale(1.03);
    }

    .text-center a {
      font-size: 14px;
      color: #007bff;
      font-weight: 500;
      transition: color 0.3s ease;
    }

    .text-center a:hover {
      text-decoration: underline;
      color: #004b7d;
    }

    .modal-header {
      background: linear-gradient(to right, #4facfe, #00f2fe);
      color: #fff;
      border-top-left-radius: 12px;
      border-top-right-radius: 12px;
    }

    .btn-outline-primary {
      border-radius: 30px;
      font-weight: 600;
      padding: 10px;
      transition: 0.3s ease;
    }

    .btn-outline-primary:hover {
      background: #4facfe;
      color: #fff;
    }

    .alert {
      font-size: 14px;
      margin-bottom: 1rem;
      background: rgba(255, 0, 0, 0.1);
      color: #d9534f;
    }

    .alert-success {
      background: rgba(40, 167, 69, 0.1);
      color: #28a745;
    }
  </style>
</head>
<body>

  <div class="login-card">
    <h2>Welcome, Teacher</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3 text-start">
        <label for="email" class="form-label">Email or Username</label>
        <input type="text" class="form-control" id="email" name="email" required>
      </div>
      <div class="mb-3 text-start">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
          <input type="password" class="form-control" id="password" name="password" required>
          <button class="btn btn-outline-secondary" type="button" id="togglePassword">
            <i class="bi bi-eye-slash"></i>
          </button>
        </div>
      </div>
      <button type="submit" class="btn btn-primary mt-3">Login</button>
    </form>

    <div class="text-center mt-4">
      <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal">Don't have an account? Register here</a>
    </div>
  </div>

  <!-- Register Modal -->
  <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="registerModalLabel">Register as</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <a href="register_teacher.php" class="btn btn-outline-primary w-100 mb-2">Register as Teacher</a>
          <a href="register_student.php" class="btn btn-outline-primary w-100">Register as Student</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap Bundle JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const togglePassword = document.querySelector('#togglePassword');
    const passwordField = document.querySelector('#password');

    togglePassword.addEventListener('click', function () {
      const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordField.setAttribute('type', type);
      this.querySelector('i').classList.toggle('bi-eye');
      this.querySelector('i').classList.toggle('bi-eye-slash');
    });
  </script>

</body>
</html>
