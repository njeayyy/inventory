<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $email = $_POST['email'];
    $password = $_POST['password'];
    $username = $_POST['username'];
    
    // Connect to the inventory_db database
    $conn_inventory = new mysqli('localhost', 'root', '', 'inventory_db');
    
    // Check connection
    if ($conn_inventory->connect_error) {
        die("Connection failed: " . $conn_inventory->connect_error);
    }

    // Check if the email already exists in inventory_db
    $stmt_inventory = $conn_inventory->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt_inventory->bind_param("s", $email);
    $stmt_inventory->execute();
    $stmt_inventory->store_result();

    if ($stmt_inventory->num_rows > 0) {
        // Email exists, check if password is empty
        $stmt_inventory->bind_result($user_id, $existing_password);
        $stmt_inventory->fetch();

        if (empty($existing_password)) {
            // Update the password if it's empty
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_stmt = $conn_inventory->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            $update_stmt->execute();
            $update_stmt->close();

            // Now insert the user into login_db
            $conn_login = new mysqli('localhost', 'root', '', 'login_db');

            // Check connection
            if ($conn_login->connect_error) {
                die("Connection failed: " . $conn_login->connect_error);
            }

            // Insert into login_db
            $insert_stmt = $conn_login->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $insert_stmt->bind_param("ss", $email, $hashed_password);
            $insert_stmt->execute();
            $insert_stmt->close();
            $conn_login->close();

            $success = "User has been updated and is now added to the login database!";
        }
    } else {
        // Email doesn't exist, insert a new user into both databases
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert into inventory_db
        $insert_inventory_stmt = $conn_inventory->prepare("INSERT INTO users (email, username, password) VALUES (?, ?, ?)");
        $insert_inventory_stmt->bind_param("sss", $email, $username, $hashed_password);
        $insert_inventory_stmt->execute();
        $insert_inventory_stmt->close();

        // Insert into login_db
        $conn_login = new mysqli('localhost', 'root', '', 'login_db');
        if ($conn_login->connect_error) {
            die("Connection failed: " . $conn_login->connect_error);
        }

        $insert_login_stmt = $conn_login->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        $insert_login_stmt->bind_param("ss", $email, $hashed_password);
        $insert_login_stmt->execute();
        $insert_login_stmt->close();
        $conn_login->close();

        $success = "User has been successfully registered and added to the login database!";
    }

    // Close the connection to inventory_db
    $stmt_inventory->close();
    $conn_inventory->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
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
        .success {
            color: green;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<form method="POST" action="signup.php">
        <h2>Sign Up</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <p class="success"><?= $success ?></p>
        <?php endif; ?>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Sign Up</button>
        <p>Already have an account? <a href="login.php">Login Now</a></p>
    </form>
</body>
</html>
