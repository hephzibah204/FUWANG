<?php
session_start();
include_once("db_conn.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: resselers_login_page.php");
    exit();
}

// Function to fetch transaction history
function fetchTransactionHistory($conn) {
    // SQL query to fetch data from multiple tables
    $sql = "
        (SELECT id, purchase_datetime, amount, type, 'Data Transaction' AS category FROM data_transactions_history)
        UNION
        (SELECT id, date, amount, type, 'Airtime Transaction' AS category FROM data_transactions_history)
        UNION
        (SELECT id, date, amount, type, 'Cable Transaction' AS category FROM data_transactions_history)
        ORDER BY date DESC
    ";

    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}

// Fetch transaction history
$transaction_history = fetchTransactionHistory($conn);

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Transaction History</h1>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Type</th>
                    <th>Category</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($transaction_history)): ?>
                    <?php foreach ($transaction_history as $transaction): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($transaction['date']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['amount']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['type']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['category']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No transactions found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>