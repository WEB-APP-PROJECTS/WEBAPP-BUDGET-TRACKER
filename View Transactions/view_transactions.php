<?php
session_start();
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;


$conn = new mysqli("localhost", "root", "", "budgettracker");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'], $_POST['amount'], $_POST['description'])) {
    $id = $_POST['id'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $stmt = $conn->prepare("UPDATE transactions SET amount = ?, description = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("dsii", $amount, $description, $id, $user_id);
    $stmt->execute();
}


if (isset($_GET['delete_id'])) {
    $del_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $del_id, $user_id);
    $stmt->execute();
}


$filter_type = isset($_GET['type']) ? $_GET['type'] : '';
$filter_cat  = isset($_GET['category']) ? intval($_GET['category']) : 0;


$type_result = $conn->query("SELECT DISTINCT type FROM categories WHERE user_id = $user_id");
$category_result = $conn->query("SELECT id, type FROM categories WHERE user_id = $user_id");


$sql = "SELECT t.id, t.date, c.type AS category_type, t.amount, t.description
        FROM transactions t
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ?";
$params = array($user_id);
$types  = "i";

if ($filter_type !== '') {
    $sql .= " AND c.type = ?";
    $params[] = $filter_type;
    $types   .= "s";
}
if ($filter_cat) {
    $sql .= " AND c.id = ?";
    $params[] = $filter_cat;
    $types   .= "i";
}
$sql .= " ORDER BY t.date DESC";


$stmt = $conn->prepare($sql);

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Transactions</title>
    <link rel="stylesheet" href="view-transactions.css?v=2">
</head>
<body>
    <div class="wrapper">
        <button class="btn-primary">VIEW TRANSACTIONS</button>
        <form method="GET" class="filters">
            <div class="filter-group">
                <label for="type">Type:</label>
                <select id="type" name="type">
                    <option value="">All Types</option>
                    <?php while ($row = $type_result->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($row['type']) ?>" <?= ($row['type'] === $filter_type) ? 'selected' : '' ?>>
                            <?= ucfirst(htmlspecialchars($row['type'])) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="category">Category:</label>
                <select id="category" name="category">
                    <option value="">All Categories</option>
                    <?php while ($row = $category_result->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>" <?= ($row['id'] === $filter_cat) ? 'selected' : '' ?>>
                            <?= ucfirst(htmlspecialchars($row['type'])) ?> #<?= $row['id'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="action-btn">Filter</button>
        </form>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <form method="POST" action="">
                                <td><?= htmlspecialchars($row['date']) ?></td>
                                <td><?= htmlspecialchars($row['category_type']) ?></td>
                                <td>
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <input type="number" name="amount" step="0.01" value="<?= htmlspecialchars($row['amount']) ?>" required>
                                </td>
                                <td>
                                    <input type="text" name="description" value="<?= htmlspecialchars($row['description']) ?>" required>
                                </td>
                                <td>
                                    <button type="submit" class="action-btn">Save</button>
                                    <a href="?delete_id=<?= $row['id'] ?>&type=<?= urlencode($filter_type) ?>&category=<?= $filter_cat ?>" onclick="return confirm('Delete this transaction?');">
                                        <button type="button" class="action-btn">Delete</button>
                                    </a>
                                </td>
                            </form>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">No transactions found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
