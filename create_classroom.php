<?php
session_start();
$conn = new mysqli("localhost", "root", "", "quiz_system");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_name = $_POST['class_name'];
    $class_code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
    $teacher_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO classrooms (teacher_id, class_name, class_code) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $teacher_id, $class_name, $class_code);
    $stmt->execute();

    $success = "Classroom created! Code: $class_code";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Classroom</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f4f8;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .form-card {
            background-color: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        .form-card h2 {
            margin-bottom: 20px;
            color: #333;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            transition: border 0.3s;
        }

        input[type="text"]:focus {
            border-color: #007BFF;
            outline: none;
        }

        button {
            background-color: #007BFF;
            color: #fff;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .success-message {
            margin-top: 20px;
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="form-card">
        <h2>Create a New Classroom</h2>
        <form method="POST">
            <input type="text" name="class_name" placeholder="Enter class name" required>
            <button type="submit">Create Classroom</button>
        </form>

        <?php if (isset($success)): ?>
            <div class="success-message">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
