<?php
include_once("db_conn.php");
include_once("whois_admin.php");

// Fetch Audit Logs
$sql = "SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 100";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #190F92; }
        body { background: #f4f7f6; font-family: 'Outfit', sans-serif; }
        .header { background: var(--primary); color: white; padding: 2rem 0; margin-bottom: 2rem; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="header">
    <div class="container d-flex justify-content-between align-items-center">
        <h2><i class="fas fa-history me-2"></i> System Audit Logs</h2>
        <a href="admin_dashboard.php" class="btn btn-outline-light"><i class="fas fa-arrow-left"></i> Dashboard</a>
    </div>
</div>

<div class="container mb-5">
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Admin</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>IP Address</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-bold text-primary"><?= htmlspecialchars($row['admin_username']) ?></td>
                                <td><span class="badge bg-info-subtle text-info fs-6"><?= htmlspecialchars($row['action']) ?></span></td>
                                <td style="max-width: 300px;" class="text-truncate"><?= htmlspecialchars($row['details']) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($row['ip_address']) ?></td>
                                <td class="text-muted small"><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4">No audit logs found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>