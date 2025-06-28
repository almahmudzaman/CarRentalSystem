<?php
ob_start();
session_start();

// ========== STYLES ========== //
echo '<style>
    .confirmation-container {
        display: flex;
        flex-direction: column;
        margin-top: 50px;
        padding-left: 40px;
        padding-right: 40px;
        border: 2px solid #ddd;
        border-radius: 10px;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
        font-size: 1.2rem;
        background-color: #f9f9f9;
    }
    .confirmation-container p {
        padding-left: 70px;
    }
    .confirmation-container h2 {
        text-align: center;
    }
    .button-group {
        display: flex;
        gap: 400px;
        margin-top: 20px;
    }
    .confirm-btn, .cancel-btn {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
        text-decoration: none;
    }
    .confirm-btn {
        background-color: #4CAF50;
        color: white;
    }
    .cancel-btn {
        background-color: #f44336;
        color: white;
    }
</style>';


// ========== HANDLE POST REQUEST ========== //
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST['action']) && $_POST['action'] === 'confirm') {
        handleDatabaseOperations();
    } else {
        validateAndStoreFormData();
    }
} else {
    echo "<h3 style='color:red;'>Invalid access. Redirecting...</h3>";
    header("Refresh: 2; url=signup.html");
    exit();
}

// ========== VALIDATE AND STORE FORM ========== //
function validateAndStoreFormData() {
    $firstName = htmlspecialchars(trim($_POST["firstName"] ?? ''));
    $lastName = htmlspecialchars(trim($_POST["lastName"] ?? ''));
    $dob = htmlspecialchars(trim($_POST["dob"] ?? ''));
    $usertype = htmlspecialchars(trim($_POST["usertype"] ?? ''));
    $address = htmlspecialchars(trim($_POST["address"] ?? ''));
    $phone = htmlspecialchars(trim($_POST["phone"] ?? ''));
    $email = htmlspecialchars(trim($_POST["email"] ?? ''));
    $password = htmlspecialchars(trim($_POST["password"] ?? ''));
    $confirmPassword = $_POST["confirmPassword"] ?? '';
    $terms = isset($_POST["terms"]);

    $errors = [];

    if (!preg_match("/^[a-zA-Z.\-\s]{3,}$/", $firstName)) {
        $errors[] = "First name must be valid and at least 3 characters.";
    }

    if (!preg_match("/^[a-zA-Z.\-\s]{3,}$/", $lastName)) {
        $errors[] = "Last name must be valid and at least 3 characters.";
    }

    if (!preg_match("/^[a-z0-9.-]+@(gmail|hotmail|yahoo)\.com$/", $email)) {
        $errors[] = "Email must end with gmail, hotmail, or yahoo.";
    }

    if (!preg_match("/^\d{11}$/", $phone)) {
        $errors[] = "Phone number must be 11 digits long.";
    }

    if (strlen($address) < 5) {
        $errors[] = "Address must be at least 5 characters.";
    }

    if (!$dob || !strtotime($dob)) {
        $errors[] = "Invalid date of birth.";
    } else {
        $birthYear = date("Y", strtotime($dob));
        $age = date("Y") - $birthYear;
        if ($age < 18) {
            $errors[] = "You must be at least 18 years old.";
        }
    }

    if (empty($usertype)) {
        $errors[] = "Please select a user type.";
    }

    if (
        strlen($password) < 8 ||
        !preg_match("/[a-z]/", $password) ||
        !preg_match("/[A-Z]/", $password) ||
        !preg_match("/\d/", $password) ||
        !preg_match("/[^A-Za-z0-9]/", $password)
    ) {
        $errors[] = "Password must include upper, lower, number and special character.";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (!$terms) {
        $errors[] = "You must accept the terms and conditions.";
    }

    if (count($errors) > 0) {
        echo "<h3 style='color:red;'>Form Validation Failed:</h3><ul>";
        foreach ($errors as $e) {
            echo "<li>" . htmlspecialchars($e) . "</li>";
        }
        echo "</ul><a href='signup.html'>Go Back</a>";
        exit();
    }

    // Save data in session
    $_SESSION['form_data'] = [
        'firstName' => $firstName,
        'lastName' => $lastName,
        'dob' => $dob,
        'usertype' => $usertype,
        'address' => $address,
        'phone' => $phone,
        'email' => $email,
        'password' => $password
    ];

    showConfirmationPage();
}

// ========== CONFIRMATION VIEW ========== //
function showConfirmationPage() {
    $data = $_SESSION['form_data'];

    echo "<div class='confirmation-container'>
        <h2>Please confirm your information</h2>
        <p><strong>First Name:</strong> {$data['firstName']}</p>
        <p><strong>Last Name:</strong> {$data['lastName']}</p>
        <p><strong>Date of Birth:</strong> {$data['dob']}</p>
        <p><strong>User Type:</strong> {$data['usertype']}</p>
        <p><strong>Address:</strong> {$data['address']}</p>
        <p><strong>Phone:</strong> {$data['phone']}</p>
        <p><strong>Email:</strong> {$data['email']}</p>

        <form method='post' action='register.php'>
            <input type='hidden' name='action' value='confirm'>
            <div class='button-group'>
                <button type='submit' class='confirm-btn'>Confirm</button>
                <a href='signup.html' class='cancel-btn'>Cancel</a>
            </div>
        </form>
    </div>";
}

// ========== HANDLE DB INSERT ========== //
function handleDatabaseOperations() {
    $data = $_SESSION['form_data'] ?? [];

    if (empty($data)) {
        echo "<h3>No session data found. Please resubmit the form.</h3>";
        header("Refresh: 2; url=signup.html");
        return;
    }

    $name     = $data['firstName'] . ' ' . $data['lastName'];
    $dob      = $data['dob'];
    $usertype = $data['usertype'];
    $address  = $data['address'];
    $phone    = $data['phone'];
    $email    = $data['email'];
    $password = $data['password']; 

    $con = mysqli_connect('localhost', 'root', '', 'rentalcar', 3307);
    if (!$con) {
        echo "<div class='content error'>Connection failed: " . mysqli_connect_error() . "</div>";
        return;
    }

    // Check email uniqueness
    $emailCheck = mysqli_query($con, "SELECT * FROM Registration WHERE Email = '$email'");
    if (mysqli_num_rows($emailCheck) > 0) {
        echo "<h3 style='color:red;'>Email already exists!</h3>";
        header("Refresh: 2; url=signup.html");
        return;
    }

    // Insert into Registration
    $sql = "INSERT INTO Registration (`Name`, `Date of Birth`, `Role`, `Address`, `Contact No`, `Email`, `Password`) 
        VALUES ('$name', '$dob', '$usertype', '$address', '$phone', '$email', '$password')";


    if (mysqli_query($con, $sql)) {
        showSuccessMessage();
        unset($_SESSION['form_data']);
    } else {
        echo "<h3 style='color:red;'>Database Error: " . mysqli_error($con) . "</h3>";
    }

    mysqli_close($con);
}

// ========== SUCCESS ========== //
function showSuccessMessage() {
    echo '<div class="confirmation-container" style="background-color: #d4edda;">
        <h2 style="color: green;">Registration Successful!</h2>
        <p>You will be redirected to login page shortly.</p>
    </div>';
    header("Refresh: 3; url=login.html");
}

?>
