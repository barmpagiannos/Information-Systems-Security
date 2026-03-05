<?php
// Ονοματεπώνυμο: Μπαρμπαγιάννος Βασίλειος
// ΑΕΜ: 10685
// Email: vmparmpg@ece.auth.gr
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
</head>

<?php
// Start a new session (or resume an existing one)
session_start();

// Check if the user is already logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SESSION['username'] !== '') {
    // Redirect to the dashboard page
    header("Location: dashboard.php");
    exit;
}

$login_message = ""; // Αρχικοποίηση

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Basic validation
    if(!isset($_POST['username'], $_POST['password']) || trim($_POST['username']) =='' || trim($_POST['password']) == '') {
        $login_message = "Missing username or password.";
    }
    else {
        // Get user submitted information
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        // --- ΒΗΜΑ ΑΣΦΑΛΕΙΑΣ 1: Connection ---
        require_once 'db_connect.php';

        // --- ΒΗΜΑ ΑΣΦΑΛΕΙΑΣ 2: Prepared Statement (SQL Injection Fix) ---
        // Ψάχνουμε ΜΟΝΟ με το username αρχικά. Δεν βάζουμε το password στο WHERE.
        $sql = "SELECT id, username, password FROM login_users WHERE username = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            // Αν βρέθηκε χρήστης με αυτό το όνομα
            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                
                // --- ΒΗΜΑ ΑΣΦΑΛΕΙΑΣ 3: Password Verification ---
                // Ελέγχουμε αν ο κωδικός που έδωσε ο χρήστης ταιριάζει με το hash στη βάση
                if (password_verify($password, $row['password'])) {
                    
                    // --- ΒΗΜΑ ΑΣΦΑΛΕΙΑΣ 4: Anti-Session Fixation ---
                    // Αλλάζουμε το session ID μόλις γίνει login για ασφάλεια
                    session_regenerate_id(true);

                    // Successfully logged in
                    $_SESSION['username'] = $row['username']; // Παίρνουμε το καθαρό username από τη βάση
                    $_SESSION['loggedin'] = true;

                    // Κλείνουμε τη σύνδεση και κάνουμε redirect
                    $stmt->close();
                    $conn->close();
                    
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $login_message = "Invalid username or password";
                }
            } else {
                $login_message = "Invalid username or password";
            }
            $stmt->close();
        } else {
            $login_message = "Database error: Could not prepare statement.";
        }

        $conn->close();
    }
}
?>

<body>
    <h3>Password Manager</h3>
    <form method="POST" action="login.php">
        <input type="text" name="username" placeholder="Username" required><br />
        <input type="password" name="password" placeholder="Password" required><br />
        <button type="submit">Login</button>
    </form>
    <br />
    <?php if (!empty($login_message)) { echo "<font color=red>$login_message</font>"; } ?>
    <p/>
    <a href="register.php">Register new user</a>
    <p/>
    <a href="index.html">Home page</a>
</body>
</html>