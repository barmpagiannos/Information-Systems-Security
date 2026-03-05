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
    <title>Notes - Comments</title>
    <style>
        form {
            max-width: 500px;
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
            text-align: left;
        }
        label {
            font-size: 1.1em;
            margin-bottom: 10px;
            display: inline-block;
        }
        textarea {
            width: 100%;
            height: 150px;
            padding: 10px;
            font-size: 1em;
            border: 1px solid #ccc;
            resize: vertical;
            text-align: left;
        }
        button {
            padding: 10px 20px;
            font-size: 1em;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
        }

        .note {
            width: 510px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .note-content {
            font-size: 1.2em;
            color: #333;
            word-wrap: break-word; /* Προσθήκη για να μην σπάνε το layout μεγάλα strings */
        }
        .note-signature {
            text-align: right;
            font-size: 0.9em;
            color: #666;
            margin-top: 10px;
            font-style: italic;
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

// Check if new note is entered and add it
if(isset($_POST['new_note']) && trim($_POST['new_note']) !='') {
    $new_note = trim($_POST["new_note"]);

    // --- ΒΗΜΑ ΑΣΦΑΛΕΙΑΣ 2: Prepared Statement (SQL Injection Fix) ---
    // Χρησιμοποιούμε prepared statement ΚΑΙ για το insert ΚΑΙ για το subquery του id
    $sql_insert = "INSERT INTO notes (login_user_id, note) VALUES ((SELECT id FROM login_users WHERE username=?), ?)";
    
    if ($stmt = $conn->prepare($sql_insert)) {
        // "ss": string (username), string (note)
        $stmt->bind_param("ss", $username, $new_note);
        $stmt->execute();
        $stmt->close();
    } else {
        // Handle error silently or log it
    }

    $conn->close();

    // After processing, redirect to the same page to clear the form
    unset($_POST['new_note']);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Display list of all notes/comments
$sql_query = "SELECT notes.note, login_users.username FROM notes INNER JOIN login_users ON notes.login_user_id=login_users.id;";
$result = $conn->query($sql_query);

echo "<h3>List of notes/comments</h3>";

if (!empty($result) && $result->num_rows >= 1) {
    while ($row = $result -> fetch_assoc()) {
        // --- ΒΗΜΑ ΑΣΦΑΛΕΙΑΣ 3: XSS Protection (Output Encoding) ---
        // Εδώ "καθαρίζουμε" τα δεδομένα πριν τα εμφανίσουμε στην οθόνη.
        // Οποιοδήποτε script tag μετατρέπεται σε απλό κείμενο.
        $safe_note = htmlspecialchars($row["note"], ENT_QUOTES, 'UTF-8');
        $safe_username = htmlspecialchars($row["username"], ENT_QUOTES, 'UTF-8');

        echo "<div class='note'>";
        echo    "<div class='note-content'>" . $safe_note . "</div>";
        echo    "<div class='note-signature'> by " . $safe_username . "</div>";
        echo "</div>";
    }

    // Free result set
    $result -> free_result();
} else {
    echo "<p><font color=red>No entries found.</font></p>";
}

$conn -> close();
?>

<body>
    <p/>
    <form method="POST">
        <label for="note">Enter your note:</label><br>
        <textarea id="note" name="new_note" placeholder="Write your note here..." required></textarea><br><br>
        <button type="submit">Submit Note</button>
    </form>

    <a href="dashboard.php">Dashboard</a>
    <p/>
    <a href="logout.php">Logout</a>
</body>
</html>