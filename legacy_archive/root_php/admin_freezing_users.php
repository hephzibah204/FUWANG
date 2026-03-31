<?php
include_once("db_conn.php");

// Query to fetch emails from the users table that are not in the referral table
$sql = "SELECT * FROM customers1 WHERE email NOT IN (SELECT email FROM customers2)";
$result = mysqli_query($conn, $sql);

// Initialize an array to store the results
$users_data = array();

// Check if the query was successful
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users_data[] = $row;
    }
} else {
    echo "Error: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NIN API Review</title>
    <link rel="icon" href="images/logo2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="theme-color" content="#190F92">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="styles.css"> <!-- Your stylesheet -->
    <script src="scripts.js" defer></script> <!-- Your script for copy functionality -->
      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Include jQuery -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .table {
            margin-top: 20px;
        }
        .no-results {
            text-align: center;
            color: #777;
        }
        .header {
            color: white;
            width: 100%;
            top: 0;
            left: -0.1%;
            position: fixed;
            background-color: #190F92;
            padding: 10px 0;
        }
        .header h2 {
            font-size: 30px;
            margin: 0;
        }
         .dropdown {
            position: relative;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            font-size: 10px;
            opacity: 1;
            border-radius: 10px;
            margin-top: 21%;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            right: 0; /* Position the dropdown content to the right */
        }
        .menu-container:hover .dropdown-content {
            display: block;
        }
        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }
             .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-success.disabled {
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="dropdown">
            <div class="menu-container">
                <i style="position: absolute; right: 4%; margin-top: 0; opacity: 0.9; z-index: 111; font-size: 30px; color: white;" class="fa fa-ellipsis-v" id="menu-icon"></i>
                <div class="dropdown-content">
                    <a href="admin_account_settings" class="fa fa-home"> Back To Account settings</a>
                 
                    <div style="border: 1px solid #f1f1f1;">
                        <a href="resellers_logout" class="fa fa-sign-out"> Logout</a>
                    </div>
                </div>
            </div>
        </div>
        <center>
            <h2>Restricted Users</h2>
        </center>
    </div>
    <br><br><br><br>
   
    
    <div class="container mt-5">
     
        <?php if (empty($users_data)): ?>
            <p>No freezing user's are found </p>
        <?php else: ?>
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users_data as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td>
                                <button class="btn btn-primary add-btn" data-id="<?php echo htmlspecialchars($user['id']); ?>"
                                        data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                        data-fullname="<?php echo htmlspecialchars($user['fullname']); ?>"
                                        data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                        data-password="<?php echo htmlspecialchars($user['password']); ?>">
                                    <i class="fa fa-plus"></i> Add
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
        document.querySelectorAll('.add-btn').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                const email = this.getAttribute('data-email');
                const fullname = this.getAttribute('data-fullname');
                const username = this.getAttribute('data-username');
                const password = this.getAttribute('data-password');

                Swal.fire({
                    title: 'Unfreeze User?',
                    text: `Do you want to unfreeze the user with email ${email}?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, unfreeze!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('admin_unfreez_users', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                id: userId,
                                email: email,
                                fullname: fullname,
                                username: username,
                                password: password
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire(
                                    'Unfrozen!',
                                    'User has been unfreeze successfully.',
                                    'success'
                                ).then(() => {
                                    button.classList.add('btn-success');
                                    button.classList.add('disabled');
                                    button.innerHTML = '<i class="fa fa-check"></i> Added';
                                    setTimeout(() => {
                                        location.reload();
                                    }, 1000);
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    'There was an issue adding the user.',
                                    'error'
                                );
                            }
                        })
                        .catch(error => {
                            Swal.fire(
                                'Error!',
                                'An error occurred while adding the user.',
                                'error'
                            );
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>