<?php
session_start();  // Start session to store user data

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Connect to the login_db database
    $conn = new mysqli('localhost', 'root', '', 'login_db');

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check user in the login_db database
    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashedPassword);
        $stmt->fetch();

        // Verify the password
        if (password_verify($password, $hashedPassword)) {
            // After successful login, connect to the inventory_db to get the username and id based on email
            $conn_inventory = new mysqli('localhost', 'root', '', 'inventory_db');

            // Check connection to inventory_db
            if ($conn_inventory->connect_error) {
                die("Connection failed: " . $conn_inventory->connect_error);
            }

            // Updated query (using 'id' instead of 'user_id')
            $stmt_inventory = $conn_inventory->prepare("SELECT username, id FROM users WHERE email = ?");
            $stmt_inventory->bind_param("s", $email);
            $stmt_inventory->execute();
            $stmt_inventory->store_result();

            if ($stmt_inventory->num_rows > 0) {
                $stmt_inventory->bind_result($username, $id);
                $stmt_inventory->fetch();

                // Store user data in session
                $_SESSION['email'] = $email;
                $_SESSION['username'] = $username;  // Store username from inventory_db in session
                $_SESSION['user_id'] = $id;         // Store id from inventory_db in session
            }

            // Close inventory_db query statement
            $stmt_inventory->close();
            $conn_inventory->close();
            
            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "No account found with this email.";
    }

    // Close the login_db query statement and connection
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        form {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #6c63ff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #5750d4;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
        a {
            color: #6c63ff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <form action="login.php" method="POST">
        <h2>Login</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
        <p>Don't have an account? <a href="signup.php">Sign Up Now</a></p>
    </form>
</body>
</html>
