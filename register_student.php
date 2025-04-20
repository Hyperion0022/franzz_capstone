<?php
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $dob = $_POST['dob'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $current_year = date("Y");

    // Validate birthdate
    $dob_year = date("Y", strtotime($dob));
    if ($dob_year > $current_year) {
        echo "<script>alert('Invalid birth year! Cannot be in the future.'); window.history.back();</script>";
        exit;
    }

    // Validate email domain
    if (!preg_match("/^[a-zA-Z0-9._%+-]+@gmail\.com$/", $email)) {
        echo "<script>alert('Email must end with @gmail.com'); window.history.back();</script>";
        exit;
    }

    // Validate password strength
    if (strlen($password) < 8 || !preg_match('/\d.*\d/', $password)) {
        echo "<script>alert('Password must be at least 8 characters long and contain at least 2 numbers.'); window.history.back();</script>";
        exit;
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit;
    }

    // Check if username exists
    $checkUser = $conn->prepare("SELECT username FROM students WHERE username = ?");
    $checkUser->bind_param("s", $username);
    $checkUser->execute();
    $checkUser->store_result();
    if ($checkUser->num_rows > 0) {
        echo "<script>alert('Username already exists! Choose another.'); window.history.back();</script>";
        exit;
    }
    $checkUser->close();

    // Check if email exists
    $checkEmail = $conn->prepare("SELECT email FROM students WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();
    if ($checkEmail->num_rows > 0) {
        echo "<script>alert('Email is already registered! Use another email.'); window.history.back();</script>";
        exit;
    }
    $checkEmail->close();

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into database
    $sql = "INSERT INTO students (first_name, last_name, dob, username, email, password) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $first_name, $last_name, $dob, $username, $email, $hashed_password);

    if ($stmt->execute()) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Successfully Registered!',
                        text: 'You can now log in.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = 'login-student.php';
                    });
                });
              </script>";
    } else {
        echo "<script>alert('Registration failed! Please try again.');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Register as Teacher</title>
    <style>
      /* General Styles */
body {
    font-family: 'Poppins', sans-serif;
    background: url('choosing-bg11.jpg') center/cover;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
    color: #f1f1f1;;
}

/* Container */
.container {
    background:rgb(18, 44, 85);
    width: 600px; /* Dati 500px, mas malapad para may space */
    min-height: 500px; /* Mas mahaba ang form */
    padding: 50px; /* Para may mas maraming space sa loob */
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.25);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    width: 320px; /* Medyo pinaliit */
    backdrop-filter: blur(10px);
    position: relative;
}

h2 {
    text-align: center;
    color: #f1f1f1;
    font-weight: 600;
}

/* Input Fields */
input {
    width: 90%;
    padding: 10px;
    margin: 5px 0;
    border: 2px solid #ccc;
    border-radius: 6px;
    transition: 0.3s;
    font-size: 15px;
}

input:focus {
    border-color:rgb(255, 157, 29);
    outline: none;
    box-shadow: 0 0 5px rgba(226, 178, 74, 0.5);
}

.input-group {
    position: relative;
}

.input-group i {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #555;
}

/* Error Messages */
.error {
    color: red;
    font-size: 0.85rem;
    margin-top: -5px;
    display: block;
}

/* Button */
button {
    width: 100%;
    background:rgb(255, 166, 0);
    color: white;
    padding: 10px;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
    display: block;
    margin: 10px auto 0; /* Nagdagdag ng 20px space sa itaas */
}

button:hover {
    background:rgb(201, 123, 7);
}


.success {
    color: green;
    font-weight: bold;
    text-align: center;
}

@media (max-width: 400px) {
    .container {
        width: 90%;
        padding: 15px;
    }

    h2 {
        font-size: 1.3rem;
    }

    input {
        font-size: 0.95rem;
    }

    button {
        font-size: 0.95rem;
    }
}
.back-btn {
    position: absolute;
    bottom: 10px; /* Increase this value to move the button down */
    left: 50%;
    transform: translateX(-50%);
    color: white;
    text-decoration: none;
    font-size: 18px;
    background: rgba(255, 255, 255, 0.2);
    padding: 8px 12px;
    border-radius: 8px;
    transition: background 0.3s ease;
}

    </style>
</head>
<body>
<div class="container">
    <h2>Register as Teacher</h2>
    
    <?php if (!empty($success)) echo "<div class='success'>$success</div>"; ?>
    <form method="POST">
        <label>First Name</label>
        <input type="text" name="first_name" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
        <span class="error"><?= $errors['first_name'] ?? '' ?></span>

        <label>Last Name</label>
        <input type="text" name="last_name" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>


        <label>Date of Birth</label>
        <input type="date" name="dob" value="<?= htmlspecialchars($_POST['dob'] ?? '') ?>" required>

        <span class="error"><?= $errors['last_name'] ?? '' ?></span>

        <label>Username</label>
        <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
        <span class="error"><?= $errors['username'] ?? '' ?></span>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        <span class="error"><?= $errors['email'] ?? '' ?></span>

        <label>Password</label>
        <div class="input-group">
            <input type="password" name="password" id="password" value="<?= htmlspecialchars($_POST['password'] ?? '') ?>" required>
            <i onclick="togglePassword('password', this)">👁️</i>
        </div>
        <span class="error"><?= $errors['password'] ?? '' ?></span>

        <label>Confirm Password</label>
        <div class="input-group">
            <input type="password" name="confirm_password" id="confirm_password" value="<?= htmlspecialchars($_POST['password'] ?? '') ?>" required>
            <i onclick="togglePassword('confirm_password', this)">👁️</i>
        </div>
        <span class="error"><?= $errors['confirm_password'] ?? '' ?></span>

        <button type="submit">Register</button>
    </form>

    <!-- Login Question -->
    <p style="text-align: center; margin-top: 10px;">Already have an account? <a href="login-teacher.php" style="color:rgb(255, 153, 0); font-weight: bold;">Login here</a></p>

    <!-- Clickable Back Text -->
    <a href="javascript:history.back()" class="back-btn">← Back</a>
</div>


<script>
    function togglePassword(id, eyeIcon) {
        let input = document.getElementById(id);
        if (input.type === "password") {
            input.type = "text";
            eyeIcon.textContent = "👁️‍🗨️";
        } else {
            input.type = "password";
            eyeIcon.textContent = "👁️";
        }
    }
</script>

</body>
</html>
