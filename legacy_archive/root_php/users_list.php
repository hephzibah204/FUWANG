
<?php
include_once("whois_admin.php");
include_once("db_conn.php");

// Handle User Actions (Deduct, Reset Password, Add, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $email = mysqli_real_escape_string($conn, $_POST['email']);

        if ($action === 'deduct') {
            $amount = floatval($_POST['amount']);
            if ($amount > 0) {
                $conn->query("UPDATE account_balance SET user_balance = user_balance - $amount WHERE email = '$email'");
                log_admin_action("Deduct Funds", "Deducted ₦$amount from $email");
                $success_msg = "Deducted ₦$amount from $email";
            }
        } elseif ($action === 'reset_password') {
            $new_password = password_hash('123456', PASSWORD_DEFAULT);
            $conn->query("UPDATE customers2 SET password = '$new_password' WHERE email = '$email'");
            $conn->query("UPDATE customers1 SET password = '$new_password' WHERE email = '$email'");
            log_admin_action("Reset Password", "Reset password for $email to default");
            $success_msg = "Password reset to '123456' for $email";
        } elseif ($action === 'delete') {
            $conn->query("DELETE FROM customers2 WHERE email = '$email'");
            $conn->query("DELETE FROM customers1 WHERE email = '$email'");
            $conn->query("DELETE FROM account_balance WHERE email = '$email'");
            log_admin_action("Delete User", "Deleted user account $email");
            $success_msg = "User $email deleted successfully";
        } elseif ($action === 'send_alert') {
            $title = mysqli_real_escape_string($conn, $_POST['title']);
            $message = mysqli_real_escape_string($conn, $_POST['message']);
            $stmt = $conn->prepare("INSERT INTO user_notifications (user_email, title, message) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $title, $message);
            if ($stmt->execute()) {
                log_admin_action("Send Notification", "Sent notification to $email: $title");
                $success_msg = "Notification sent to $email";
            }
        } elseif ($action === 'add') {
            $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
            $username = mysqli_real_escape_string($conn, $_POST['username']);
            $number = mysqli_real_escape_string($conn, $_POST['number']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $pin = mysqli_real_escape_string($conn, $_POST['pin']);
            
            $stmt = $conn->prepare("INSERT INTO customers1 (fullname, username, email, number, password, transaction_pin) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $fullname, $username, $email, $number, $password, $pin);
            if ($stmt->execute()) {
                $conn->query("INSERT INTO account_balance (email, user_balance) VALUES ('$email', 0)");
                log_admin_action("Add User", "Created new user $email");
                $success_msg = "User $email added successfully";
            }
        }
    }
}

// Fetch all users with their balances
$sql = "
    SELECT c.fullname, c.username, c.email, c.number, b.user_balance, c.created_at
    FROM customers2 c
    LEFT JOIN account_balance b ON c.email = b.email
    ORDER BY c.created_at DESC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --primary: #190F92; }
        body { background: #f4f7f6; font-family: 'Outfit', sans-serif; }
        .header { background: var(--primary); color: white; padding: 2rem 0; margin-bottom: 2rem; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .btn-primary { background: var(--primary); border: none; }
        .btn-primary:hover { background: #140a7a; }
        .table thead { background: #f8f9fa; }
        .action-btn { width: 35px; height: 35px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; margin: 2px; }
    </style>
</head>
<body>

<div class="header">
    <div class="container d-flex justify-content-between align-items-center">
        <h2><i class="fas fa-users-cog me-2"></i> User Management</h2>
        <a href="admin_dashboard.php" class="btn btn-outline-light"><i class="fas fa-arrow-left"></i> Dashboard</a>
    </div>
</div>

<div class="container">
    <?php if (isset($success_msg)): ?>
        <script>
            Swal.fire({ icon: 'success', title: 'Success', text: '<?php echo $success_msg; ?>', timer: 3000 });
        </script>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-8">
            <input type="text" id="userSearch" class="form-control form-control-lg shadow-sm" placeholder="Search by name, email or phone...">
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-primary btn-lg shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-user-plus"></i> Add New User
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>User Details</th>
                            <th>Contact</th>
                            <th>Balance</th>
                            <th>Joined</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="userTable">
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($row['fullname']); ?></div>
                                <div class="text-muted small">@<?php echo htmlspecialchars($row['username']); ?></div>
                            </td>
                            <td>
                                <div><?php echo htmlspecialchars($row['email']); ?></div>
                                <div class="text-muted small"><?php echo htmlspecialchars($row['number']); ?></div>
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-success fs-6">₦<?php echo number_format($row['user_balance'], 2); ?></span>
                            </td>
                            <td class="text-muted small">
                                <?php echo date('d M, Y', strtotime($row['created_at'])); ?>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-primary action-btn" onclick="sendAlert('<?php echo $row['email']; ?>')" title="Send Notification">
                                    <i class="fas fa-bell"></i>
                                </button>
                                <button class="btn btn-warning action-btn" onclick="deductFunds('<?php echo $row['email']; ?>')" title="Deduct Funds">
                                    <i class="fas fa-minus-circle"></i>
                                </button>
                                <button class="btn btn-info action-btn text-white" onclick="resetPassword('<?php echo $row['email']; ?>')" title="Reset Password">
                                    <i class="fas fa-key"></i>
                                </button>
                                <button class="btn btn-danger action-btn" onclick="deleteUser('<?php echo $row['email']; ?>')" title="Delete User">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="fullname" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="number" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Transaction PIN</label>
                        <input type="text" name="pin" class="form-control" maxlength="4" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Create User Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="actionForm" method="POST" style="display:none;">
    <input type="hidden" name="action" id="actionInput">
    <input type="hidden" name="email" id="emailInput">
    <input type="hidden" name="amount" id="amountInput">
    <input type="hidden" name="title" id="titleInput">
    <input type="hidden" name="message" id="messageInput">
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function sendAlert(email) {
        Swal.fire({
            title: 'Send Notification',
            html: `
                <input id="swal-title" class="swal2-input" placeholder="Notification Title">
                <textarea id="swal-message" class="swal2-textarea" placeholder="Enter message..."></textarea>
            `,
            showCancelButton: true,
            confirmButtonText: 'Send Alert',
            preConfirm: () => {
                return {
                    title: document.getElementById('swal-title').value,
                    message: document.getElementById('swal-message').value
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                if (result.value.title && result.value.message) {
                    $('#actionInput').val('send_alert');
                    $('#emailInput').val(email);
                    $('#titleInput').val(result.value.title);
                    $('#messageInput').val(result.value.message);
                    $('#actionForm').submit();
                } else {
                    Swal.fire('Error', 'Both title and message are required!', 'error');
                }
            }
        });
    }

    function deductFunds(email) {
        Swal.fire({
            title: 'Deduct Funds',
            text: `Enter amount to deduct from ${email}:`,
            input: 'number',
            showCancelButton: true,
            confirmButtonText: 'Deduct',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed && result.value > 0) {
                $('#actionInput').val('deduct');
                $('#emailInput').val(email);
                $('#amountInput').val(result.value);
                $('#actionForm').submit();
            }
        });
    }

    function resetPassword(email) {
        Swal.fire({
            title: 'Reset Password?',
            text: `This will reset the password for ${email} to '123456'.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Reset'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#actionInput').val('reset_password');
                $('#emailInput').val(email);
                $('#actionForm').submit();
            }
        });
    }

    function deleteUser(email) {
        Swal.fire({
            title: 'Delete User?',
            text: `Are you sure you want to permanently delete ${email}? This cannot be undone!`,
            icon: 'error',
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete User',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#actionInput').val('delete');
                $('#emailInput').val(email);
                $('#actionForm').submit();
            }
        });
    }

    $(document).ready(function(){
        $("#userSearch").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#userTable tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });
</script>
</body>
</html>
