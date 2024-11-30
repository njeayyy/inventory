<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password
    $role = 'User'; // Default role for new accounts
    $status = 'Active'; // Default status

    // Connect to `inventory_db` to check if the email exists
    $inventoryDb = new mysqli('localhost', 'root', '', 'inventory_db');
    if ($inventoryDb->connect_error) {
        die("Connection to inventory_db failed: " . $inventoryDb->connect_error);
    }

    // Check if the email already exists in `inventory_db`
    $stmtInventoryCheck = $inventoryDb->prepare("SELECT password FROM users WHERE email = ?");
    $stmtInventoryCheck->bind_param("s", $email);
    $stmtInventoryCheck->execute();
    $stmtInventoryCheck->store_result();

    if ($stmtInventoryCheck->num_rows > 0) {
        // Email exists, check if the user already has a password
        $stmtInventoryCheck->bind_result($existingPassword);
        $stmtInventoryCheck->fetch();

        if (!empty($existingPassword)) {
            // If password already exists, show the error
            $error = "Email account already exists.";
        } else {
            // If password is empty, update the password in inventory_db
            $stmtUpdatePassword = $inventoryDb->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmtUpdatePassword->bind_param("ss", $password, $email);
            if ($stmtUpdatePassword->execute()) {
                // Successfully updated the password in inventory_db, now insert into login_db
                $loginDb = new mysqli('localhost', 'root', '', 'login_db');
                if ($loginDb->connect_error) {
                    die("Connection to login_db failed: " . $loginDb->connect_error);
                }

                // Insert into `login_db`
                $stmtLoginInsert = $loginDb->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
                $stmtLoginInsert->bind_param("ss", $email, $password);
                if ($stmtLoginInsert->execute()) {
                    $success = "Password updated successfully in inventory_db and account added to login_db. You can now log in.";
                } else {
                    $error = "Failed to insert data into login_db.";
                }

                $stmtLoginInsert->close();
                $loginDb->close();
            } else {
                $error = "Failed to update password in inventory_db.";
            }
            $stmtUpdatePassword->close();
        }
    } else {
        // Email doesn't exist, proceed with creating the account
        // Insert into `inventory_db`
        $stmtInventoryInsert = $inventoryDb->prepare("INSERT INTO users (email, password, role, status) VALUES (?, ?, ?, ?)");
        $stmtInventoryInsert->bind_param("ssss", $email, $password, $role, $status);

        if ($stmtInventoryInsert->execute()) {
            // Successfully added to inventory_db, now insert into login_db
            $loginDb = new mysqli('localhost', 'root', '', 'login_db');
            if ($loginDb->connect_error) {
                die("Connection to login_db failed: " . $loginDb->connect_error);
            }

            // Insert into `login_db`
            $stmtLoginInsert = $loginDb->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $stmtLoginInsert->bind_param("ss", $email, $password);
            if ($stmtLoginInsert->execute()) {
                $success = "Account created successfully in both databases! You can now log in.";
            } else {
                $error = "Failed to insert data into login_db.";
            }

            $stmtLoginInsert->close();
            $loginDb->close();
        } else {
            $error = "Failed to insert into inventory_db: " . $inventoryDb->error;
        }

        $stmtInventoryInsert->close();
    }

    $stmtInventoryCheck->close();
    $inventoryDb->close();
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
