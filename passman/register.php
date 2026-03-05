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
    <title>Registration Form</title>
</head>
<body>
    <h3>New user registration</h3>

<?php
// Start a new session (or resume an existing one)
session_start();

// Check if the user is already logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SESSION['username'] !== '') {
    echo "<font color=red>You are already logged in!</font></br>";
    echo "Please <a href='logout.php'>logout</a> first";
    exit;
}

$login_message = ""; // Αρχικοποίηση μεταβλητής για αποφυγή warnings

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Έλεγχος αν υπάρχουν τα πεδία και δεν είναι κενά
    if(!isset($_POST['new_username'], $_POST['new_password']) || trim($_POST['new_username']) =='' || trim($_POST['new_password']) == '') {
        $login_message = "Missing username or password.";
    }
    else {
        // Get user submitted information
        $new_username = trim($_POST['new_username']);
        $plain_password = trim($_POST['new_password']);

        // --- ΒΗΜΑ ΑΣΦΑΛΕΙΑΣ 1: Connection ---
        // Χρησιμοποιούμε το κεντρικό αρχείο σύνδεσης
        require_once 'db_connect.php';

        // --- ΒΗΜΑ ΑΣΦΑΛΕΙΑΣ 2: Hashing ---
        // Δεν αποθηκεύουμε τον κωδικό σκέτο, αλλά το hash του
        $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

        // --- ΒΗΜΑ ΑΣΦΑΛΕΙΑΣ 3: Prepared Statements (SQL Injection Fix) ---
        // Αντί να βάλουμε τις μεταβλητές κατευθείαν στο string, βάζουμε ?
        $sql = "INSERT INTO login_users (username, password) VALUES (?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            // Συνδέουμε τις παραμέτρους: "ss" σημαίνει string, string
            $stmt->bind_param("ss", $new_username, $hashed_password);

            // Εκτελούμε το ερώτημα
            if ($stmt->execute()) {
                echo "<font color=red>Successful registration!</font>";
                echo "<p />You can now use the <a href='login.php'>login</a> page";
                
                // Καθαρισμός
                $stmt->close();
                $conn->close();
                exit;
            } else {
                // Συνήθως αποτυγχάνει αν υπάρχει ήδη το username (duplicate entry)
                $login_message = "Error: User likely already exists.";
            }
            $stmt->close();
        } else {
            $login_message = "Database error: Could not prepare statement.";
        }
        
        $conn->close();
    }
}
?>

    <p/>
    <form method="POST" action="register.php">
        <input type="text" name="new_username" placeholder="Username" required><br />
        <input type="password" name="new_password" placeholder="Password" required><br />
        <button type="submit">Register</button>
    </form>

    <br />

    <?php
        if (!empty($login_message)) { 
            echo "<font color=red>$login_message</font>";
            echo "<p />Go to the <a href='login.php'>login</a> page";
        }
    ?>

</body>
</html>