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
    <title>Dashboard</title>
    <style>
        table {
            border-collapse: collapse;
            width: 30%;
            border: 1px solid black;
        }
        td, tr {
            width: 50%;
            padding: 8px;
            text-align: left;
        }
    </style>
</head>

<?php
// Resume existing session (or start a new one)
session_start();

// If not logged in redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['username'] == '') {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

// --- ΒΗΜΑ ΑΣΦΑΛΕΙΑΣ 1: Connection ---
require_once 'db_connect.php';

// Check if 'Insert-new-website' button is selected
if(isset($_POST['new_website'], $_POST['new_username'], $_POST['new_password']) && 
   trim($_POST['new_website']) !='' && trim($_POST['new_username']) != '' && trim($_POST['new_password']) != '') {
    
    $new_website = trim($_POST["new_website"]);
    $new_username = trim($_POST["new_username"]);
    $new_password = trim($_POST["new_password"]);

    // --- ΒΗΜΑ ΑΣΦΑΛΕΙΑΣ 2: Prepared Statement (Insert) ---
    $sql_insert = "INSERT INTO websites (login_user_id,web_url,web_username,web_password) VALUES ((SELECT id FROM login_users WHERE username=?),?,?,?)";
    
    if ($stmt = $conn->prepare($sql_insert)) {
        // "ssss": 4 strings
        $stmt->bind_param("ssss", $username, $new_website, $new_username, $new_password);
        $stmt->execute();
        $stmt->close();
    }

    // After processing, redirect to the same page to clear the form
    unset($_POST['new_website']);
    unset($_POST['new_username']);
    unset($_POST['new_password']);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Check if 'Delete-website' button was selected
if(isset($_POST['delete_website']) && isset($_POST["websiteid"]) && trim($_POST["websiteid"] != '')) {
    $webid = trim($_POST["websiteid"]);

    // --- ΒΗΜΑ ΑΣΦΑΛΕΙΑΣ 3: Secure Delete (Prevent IDOR) ---
    // Διαγράφουμε ΜΟΝΟ αν το webid αντιστοιχεί στον τρέχοντα χρήστη.
    // Έτσι κανείς δεν μπορεί να σβήσει δεδομένα άλλου αλλάζοντας το ID στο HTML.
    $sql_delete = "DELETE FROM websites WHERE webid=? AND login_user_id=(SELECT id FROM login_users WHERE username=?)";
    
    if ($stmt = $conn->prepare($sql_delete)) {
        // "is": integer (webid), string (username)
        $stmt->bind_param("is", $webid, $username);
        $stmt->execute();
        $stmt->close();
    }

    // After processing, redirect to the same page
    unset($_POST['websiteid']);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Display list of user's web sites
// --- ΒΗΜΑ ΑΣΦΑΛΕΙΑΣ 4: Prepared Statement (Select) ---
$sql_select = "SELECT * FROM websites INNER JOIN login_users ON websites.login_user_id=login_users.id WHERE login_users.username=?";

$stmt = $conn->prepare($sql_select);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

echo "<h3>Entries of " . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . "</h3>";

if (!empty($result) && $result->num_rows >= 1) {
    while ($row = $result -> fetch_assoc()) {
        
        // --- ΒΗΜΑ ΑΣΦΑΛΕΙΑΣ 5: XSS Protection ---
        // Καθαρισμός δεδομένων πριν την εμφάνιση
        $s_url = htmlspecialchars($row["web_url"], ENT_QUOTES, 'UTF-8');
        $s_username = htmlspecialchars($row["web_username"], ENT_QUOTES, 'UTF-8');
        $s_password = htmlspecialchars($row["web_password"], ENT_QUOTES, 'UTF-8');
        $s_webid = htmlspecialchars($row["webid"], ENT_QUOTES, 'UTF-8');

        echo "<table border=0>";
        echo    "<tr style='background-color: #f4f4f4;'><td colspan=2>" . $s_url . "</td></tr>" . 
                "<tr><td>Username: " . $s_username . "</td><td>Password: " . $s_password . "</td></tr>";

        echo    "<tr><td><form method='POST' style='height: 3px'>" . 
                "<input type='hidden' name='websiteid' value='" . $s_webid . "'>" .
                "<button type='submit' name='delete_website'>Delete</button></form></td></tr>";

        echo    "<tr><td colspan=2 style=height: 20px;></td></tr>";
        echo "</table><p/>";
    }

    $stmt->close(); // Κλείσιμο του statement
} else {
    echo "<p><font color=red>No entries found.</font></p>";
}

$conn -> close();

?>

<body>
    <p/>
    <form method="POST" action="dashboard.php">
        <input type="text" name="new_website" placeholder="website" required><br />
        <input type="text" name="new_username" placeholder="Username" required><br />
        <input type="password" name="new_password" placeholder="Password" required><br />
        <button type="submit">Insert new website</button>
    </form>
    <p/>
    <a href="notes.php">Notes - announcements</a>
    <p/>
    <a href="logout.php">Logout</a>
    <p/>
    <a href="index.html">Home page</a>
</body>
</html>