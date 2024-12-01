<?php
session_start();  // Start session to store user data

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Connect to the inventory_db database
    $conn_inventory = new mysqli('localhost', 'root', '', 'inventory_db');

    // Check connection
    if ($conn_inventory->connect_error) {
        die("Connection failed: " . $conn_inventory->connect_error);
    }

    // Check user in the inventory_db database
    $stmt_inventory = $conn_inventory->prepare("SELECT id, password, username, role FROM users WHERE email = ?");
    $stmt_inventory->bind_param("s", $email);
    $stmt_inventory->execute();
    $stmt_inventory->store_result();

    if ($stmt_inventory->num_rows > 0) {
        $stmt_inventory->bind_result($user_id, $hashedPassword, $username, $role);
        $stmt_inventory->fetch();

        // Verify the password
        if (password_verify($password, $hashedPassword)) {
            // Store user data in session
            $_SESSION['email'] = $email;
            $_SESSION['username'] = $username;  // Store username from inventory_db in session
            $_SESSION['user_id'] = $user_id;    // Store id from inventory_db in session
            $_SESSION['role'] = $role;          // Store the user's role in session

            // Update the last_login field in inventory_db
            $current_date = date('Y-m-d H:i:s'); // Current date and time
            $stmt_update_login = $conn_inventory->prepare("UPDATE users SET last_login = ? WHERE email = ?");
            $stmt_update_login->bind_param("ss", $current_date, $email);
            $stmt_update_login->execute();
            $stmt_update_login->close();

            // Redirect based on the user role
            if ($role == 'Admin') {
                header("Location: ../admin dashboard/dashboard.php"); // For admin, go to dashboard.php
            } else {
                // Redirect to u_dashboard.php inside the 'user' folder
                header("Location: ../user/u_dashboard.php"); // For user, go to u_dashboard.php
            }
            exit();

        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "No account found with this email.";
    }

    // Close the inventory_db query statement and connection
    $stmt_inventory->close();
    $conn_inventory->close();
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
            width:  400px;
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        input {
            width: 75%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            width: 50%;
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
