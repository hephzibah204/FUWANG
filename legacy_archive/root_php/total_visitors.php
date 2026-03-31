<?php
// Include database connection
include_once("db_conn.php");
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="theme-color" content="#190F92">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Total visitor's</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.all.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
 <?php
 
 include_once("visitors_bar.php");
 ?>
 
<?php
$query = "SELECT * FROM visitors ORDER BY visit_time DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
?>

<table style="position: absolute; top: 21%;" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>IP address</th>
            <th>Location</th>
            <th>Device model</th>
            <th>Browser Type</th>
            <th>Visitor Name</th>
            <th>Visitor Time</th>
            <th>Visitor Time spent</th>
            <th>User refer visitor</th>
        </tr>
    </thead>
    <tbody id="myTable">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
        ?>
            <tr>
                <td><?php echo $row["id"]; ?></td>
                <td><?php echo $row["ip_address"]; ?></td>
                <td><?php echo $row["location"]; ?></td>
                <td><?php echo $row["device_model"]; ?></td>
                <td><?php echo $row["browser_type"]; ?></td>
                <td><?php echo $row["fullname"]; ?></td>
                <td><?php echo $row["visit_time"]; ?></td>
                <td><?php echo $row["time_on_site"]; ?></td>
                <td><?php echo $row["reseller_id"]; ?></td>
            </tr>
        <?php
            }
        } else {
            echo "<tr><td colspan='9'>No visitors found</td></tr>";
        }
        ?>
    </tbody>
</table>

</body>
</html>