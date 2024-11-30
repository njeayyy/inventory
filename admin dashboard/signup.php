<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password
    $role = 'User'; // Default role for new accounts
    $status = 'Active'; // Default status
    $name = 'Default Name'; // Placeholder for the name
    $username = explode('@', $email)[0]; // Use the part before '@' as the username

    // Connect to `login_db` for storing login credentials
    $loginDb = new mysqli('localhost', 'root', '', 'login_db');
    if ($loginDb->connect_error) {
        die("Connection to login_db failed: " . $loginDb->connect_error);
    }

    // Insert into `login_db`
    $stmtLogin = $loginDb->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    $stmtLogin->bind_param("ss", $email, $password);

    if ($stmtLogin->execute()) {
        // Successfully added to login_db, now add to inventory_db
        $inventoryDb = new mysqli('localhost', 'root', '', 'inventory_db');
        if ($inventoryDb->connect_error) {
            die("Connection to inventory_db failed: " . $inventoryDb->connect_error);
        }

        // Insert into `inventory_db` users table
        $stmtInventory = $inventoryDb->prepare("
            INSERT INTO users (name, email, username, password, role, status) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmtInventory->bind_param("ssssss", $name, $email, $username, $password, $role, $status);

        if ($stmtInventory->execute()) {
            $success = "Account created successfully in both databases! You can now log in.";
        } else {
            $error = "Failed to insert into inventory_db: " . $inventoryDb->error;
        }

        $stmtInventory->close();
        $inventoryDb->close();
    } else {
        $error = "Failed to insert into login_db: " . $loginDb->error;
    }

    $stmtLogin->close();
    $loginDb->close();
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
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Sign Up</button>
        <p>Already have an account? <a href="login.php">Login Now</a></p>
    </form>
</body>
</html>
