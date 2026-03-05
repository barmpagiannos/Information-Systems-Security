<?php
// Ονοματεπώνυμο: Μπαρμπαγιάννος Βασίλειος
// ΑΕΜ: 10685
// Email: vmparmpg@ece.auth.gr
?>

<?php
// db_connect.php

$db_host = "";
$db_user = ""; 
$db_pass = "";
$db_name = "";
$db_port = ; // Εδώ ορίζουμε την πόρτα

// Δημιουργία σύνδεσης με την ορισμένη πόρτα
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);

// Έλεγχος σύνδεσης
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>