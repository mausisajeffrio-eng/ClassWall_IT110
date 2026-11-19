
<?php
session_start();

require_once "db.php";

// SESSION: Count website visits
if (!isset($_SESSION['visits'])) {
    $_SESSION['visits'] = 1;
} else {
    $_SESSION['visits']++;
}

// COOKIE: Get remembered name
$savedName = $_COOKIE['visitor_name'] ?? "";

// POST: Save a new message
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $author = trim($_POST['author'] ?? "");
    $body = trim($_POST['body'] ?? "");

    if ($author !== "" && $body !== "") {

        // COOKIE: Remember name if checkbox is checked
        if (isset($_POST['remember'])) {

            setcookie("visitor_name", $author, [
                "expires" => time() + (30 * 24 * 60 * 60),
                "path" => "/",
                "samesite" => "Lax"
            ]);

        } else {

            // Remove previously saved name
            setcookie("visitor_name", "", [
                "expires" => time() - 3600,
                "path" => "/"
            ]);
        }

        // DATABASE: Insert message
        $sql = "INSERT INTO messages (author, body)
                VALUES (:author, :body)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':author' => $author,
            ':body' => $body
        ]);

        // Refresh page after posting
        header("Location: index.php");
        exit;
    }
}

// GET: Search messages
$search = trim($_GET['search'] ?? "");

if ($search !== "") {

    $sql = "SELECT * FROM messages
            WHERE author ILIKE :search
            OR body ILIKE :search
            ORDER BY created_at DESC";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':search' => '%' . $search . '%'
    ]);

} else {

    // Display all messages, newest first
    $sql = "SELECT * FROM messages
            ORDER BY created_at DESC";

    $stmt = $pdo->query($sql);
}

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// SECURITY: Escape output
function escape($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ClassWall - Message Board</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

    <header>
        <h1>ClassWall</h1>
        <p>Share your thoughts with everyone!</p>
    </header>

    <div class="visit-counter">
        You've visited this website
        <strong><?= escape($_SESSION['visits']) ?></strong>
        times.
    </div>

    <section class="post-section">

        <h2>Post a Message</h2>

        <form method="POST" action="index.php">

            <label for="author">Your Name</label>

            <input
                type="text"
                id="author"
                name="author"
                placeholder="Enter your name"
                value="<?= escape($savedName) ?>"
                required
            >

            <label for="body">Your Message</label>

            <textarea
                id="body"
                name="body"
                placeholder="Write your message here..."
                required
            ></textarea>

            <label class="remember-label">
                <input
                    type="checkbox"
                    name="remember"
                    value="1"
                    <?= $savedName !== "" ? "checked" : "" ?>
                >
                Remember my name
            </label>

            <button type="submit">Post Message</button>

        </form>

    </section>

    <section class="search-section">

        <h2>Search Messages</h2>

        <form method="GET" action="index.php">

            <input
                type="text"
                name="search"
                placeholder="Search messages..."
                value="<?= escape($search) ?>"
            >

            <button type="submit">Search</button>

        </form>

        <a href="index.php" class="show-all">Show All Messages</a>

    </section>

    <section class="wall-section">

        <h2>Message Wall</h2>

        <?php if (count($messages) > 0): ?>

            <?php foreach ($messages as $message): ?>

                <div class="message">

                    <h3><?= escape($message['author']) ?></h3>

                    <p><?= nl2br(escape($message['body'])) ?></p>

                    <span class="date">
                        <?= escape($message['created_at']) ?>
                    </span>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <p class="no-messages">No messages found.</p>

        <?php endif; ?>

    </section>

    <footer>
        <p>ClassWall | IT 110 Activity</p>
    </footer>

</div>

</body>
</html>
