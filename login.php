<?php
session_start();

if (isset($_SESSION["username"])) {
    header("Location: homepage.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["email"] ?? '';
    $password = $_POST["password"] ?? '';

    $conn = mysqli_connect('localhost', 'root', '', 'rentalcar','3307');

    if (!$conn) {
        echo "<div style='color: white; background-color: red; padding: 10px;'>Database connection failed.</div>";
        header("refresh: 2; url=index.html");
        exit();
    }

    $uname = mysqli_real_escape_string($conn, $username);
    $pass = mysqli_real_escape_string($conn, $password);

    $sql = "SELECT * FROM Registration WHERE Email = '$uname' AND Password = '$pass' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) === 1) 
    {
        $user = mysqli_fetch_assoc($result);
        if ($user['Status'] == 1) 
        {
            // Account is activated, proceed with login
            $_SESSION["username"] = $uname;

            echo "<div style='color: white; background-color: green; padding: 10px;'>Login successful. Redirecting...</div>";
            header("refresh: 2; url=homepage.html"); 
            exit();
        }
        else 
        {
            // Account is not activated
            echo "<div style='color: white; background-color: orange; padding: 10px;'>Your account is not activated yet. Please contact support. Redirecting...</div>";
            header("refresh: 3; url=login.html"); // Redirect back to login.html
            exit();
        }
    
        

    } else {
        echo "<div style='color: white; background-color: red; padding: 10px;'>Invalid email or password. Redirecting...</div>";
        header("refresh: 2; url=login.html");
        exit();
    }
    mysqli_close($conn);

} else {
    echo "<div style='color: white; background-color: orange; padding: 10px;'>Please fill in the login form.</div>";
    header("refresh: 2; url=login.html");
    exit();
}

?>
