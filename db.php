
<?php

$host = "localhost";
$port = "5432";
$dbname = "classwall";
$username = "postgres";
$password = "01438";

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Database connected successfully!";

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
