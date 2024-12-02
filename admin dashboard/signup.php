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
    $stmt_inventory = $conn_inventory->prepare("SELECT id, password, role FROM users WHERE email = ?");
    $stmt_inventory->bind_param("s", $email);
    $stmt_inventory->execute();
    $stmt_inventory->store_result();

    if ($stmt_inventory->num_rows > 0) {
        // Email exists, check if password is empty
        $stmt_inventory->bind_result($user_id, $existing_password, $role);
        $stmt_inventory->fetch();

        if (empty($existing_password)) {
            // Update the password and username if it's empty in inventory_db
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_stmt = $conn_inventory->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $username, $hashed_password, $user_id);
            $update_stmt->execute();
            $update_stmt->close();

            $success = "User has been updated with the password!";
        }
    } else {
        // Email doesn't exist, insert a new user into inventory_db
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = "User";  // Default role

        // Insert into inventory_db
        $insert_inventory_stmt = $conn_inventory->prepare("INSERT INTO users (email, username, password, role) VALUES (?, ?, ?, ?)");
        $insert_inventory_stmt->bind_param("ssss", $email, $username, $hashed_password, $role);
        $insert_inventory_stmt->execute();

        $success = "User has been successfully registered!";
    }

    // Close the connection to inventory_db
    if ($stmt_inventory) $stmt_inventory->close();
    if (isset($insert_inventory_stmt)) $insert_inventory_stmt->close();
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
            width: 400px;
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