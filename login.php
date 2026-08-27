
<?php

session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";

    // Temporary admin login
    if ($username === "admin" && $password === "admin123") {

        $_SESSION["admin_logged_in"] = true;

        header("Location: dashboard.php");
        exit;

    } else {

        $error = "Incorrect username or password.";

    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Admin Login | Emirates Butchery</title>

    <style>

        * {
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            margin: 0;
            background: #111;
            min-height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-box {
            width: 90%;
            max-width: 400px;

            background: white;

            padding: 35px;

            border-radius: 12px;

            box-shadow: 0 5px 25px rgba(0,0,0,0.3);
        }

        .logo {
            text-align: center;

            font-size: 28px;

            font-weight: bold;

            color: #e63946;

            margin-bottom: 10px;
        }

        h2 {
            text-align: center;

            margin-bottom: 25px;

            color: #222;
        }

        label {
            display: block;

            margin-bottom: 7px;

            font-weight: bold;
        }

        input {
            width: 100%;

            padding: 13px;

            margin-bottom: 18px;

            border: 1px solid #ccc;

            border-radius: 6px;

            font-size: 15px;
        }

        button {
            width: 100%;

            padding: 14px;

            border: none;

            border-radius: 6px;

            background: #e63946;

            color: white;

            font-size: 16px;

            font-weight: bold;

            cursor: pointer;
        }

        button:hover {
            background: #c1121f;
        }

        .error {
            background: #f8d7da;

            color: #842029;

            padding: 10px;

            border-radius: 5px;

            margin-bottom: 15px;

            text-align: center;
        }

        .back {
            display: block;

            text-align: center;

            margin-top: 20px;

            color: #555;

            text-decoration: none;
        }

    </style>

</head>

<body>

<div class="login-box">

    <div class="logo">
        Emirates Butchery
    </div>

    <h2>Admin Login</h2>

    <?php if ($error): ?>

        <div class="error">
            <?php echo $error; ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <label>
            Username
        </label>

        <input
            type="text"
            name="username"
            placeholder="Enter username"
            required>

        <label>
            Password
        </label>

        <input
            type="password"
            name="password"
            placeholder="Enter password"
            required>

        <button type="submit">
            Login
        </button>

    </form>

    <a
        href="../index.html"
        class="back">
        ← Back to Website
    </a>

</div>

</body>

</html>