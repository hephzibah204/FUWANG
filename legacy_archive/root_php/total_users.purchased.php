<!DOCTYPE html>
<html>
<head>
    <title>Airtime Purchases by User</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom"></script>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            overflow: hidden;
        }
        canvas {
            display: block;
            width: 100%;
            height: 100%;
        }
    </style>
</head>
<body>
    <canvas id="airtimeChart"></canvas>

    <?php
    include_once("db_conn.php");
    // Fetch distinct user emails
    $user_query = "SELECT DISTINCT email FROM data_transactions_history";
    $user_query_run = mysqli_query($conn, $user_query);

    // Fetch distinct networks for airtime
    $network_query = "SELECT DISTINCT network FROM data_transactions_history";
    $network_query_run = mysqli_query($conn, $network_query);
    $networks = mysqli_fetch_all($network_query_run, MYSQLI_ASSOC);

    // Initialize an array to store the data for the chart
    $data = array();

    // Loop through each user email
    while($user_row = mysqli_fetch_assoc($user_query_run)) {
        $user_email = $user_row['email'];
        
        // Initialize an array to store the airtime purchases for the current user
        $user_airtime_data = array();
        
        // Loop through each network
        foreach($networks as $network) {
            $network_name = $network['network'];
            
            // Fetch total amount of airtime purchased by the user for the current network
            $airtime_query = "SELECT SUM(amount) AS total_amount FROM data_transactions_history WHERE email='$user_email' AND network='$network_name'";
            $airtime_query_run = mysqli_query($conn, $airtime_query);
            $airtime_row = mysqli_fetch_assoc($airtime_query_run);
            
            // Store the total amount of airtime purchased for the current network
            $user_airtime_data[$network_name] = $airtime_row['total_amount'];
        }
        
        // Store the airtime data for the current user
        $data[$user_email] = $user_airtime_data;
    }
    ?>

    <script>
        var data = <?php echo json_encode($data); ?>;
        
        var emails = Object.keys(data);
        var networks = Object.keys(data[emails[0]]);
        var airtimeData = [];
        
        // Format data for Chart.js
        for (var i = 0; i < emails.length; i++) {
            var userData = data[emails[i]];
            var userDataArray = [];
            for (var j = 0; j < networks.length; j++) {
                userDataArray.push(userData[networks[j]]);
            }
            airtimeData.push(userDataArray);
        }
        
        var ctx = document.getElementById('airtimeChart').getContext('2d');
        var airtimeChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: emails,
                datasets: []
            },
            options: {
                scales: {
                    xAxes: [{ stacked: true }],
                    yAxes: [{ stacked: true }]
                },
                plugins: {
                    zoom: {
                        pan: {
                            enabled: true,
                            mode: 'x',
                            speed: 10,
                            threshold: 10
                        },
                        zoom: {
                            enabled: true,
                            mode: 'x',
                            sensitivity: 0.1
                        }
                    }
                }
            }
        });
        
        // Define custom colors for each network
        var backgroundColors = ['rgba(54, 162, 235, 0.6)', // Dark blue
                                'rgba(75, 192, 192, 0.6)', // Dark green
                                'rgba(153, 102, 255, 0.6)', // Dark purple
                                'rgba(255, 159, 64, 0.6)', // Orange
                                'rgba(255, 99, 132, 0.6)', // Red
                                'rgba(255, 206, 86, 0.6)', // Yellow
                                'rgba(50, 205, 50, 0.6)', // Lime green
                                'rgba(210, 105, 30, 0.6)', // Chocolate
                                'rgba(128, 128, 128, 0.6)', // Light grey
                                'rgba(139, 69, 19, 0.6)' // Saddle brown
                                ]; 

        // Add datasets to the chart
        for (var i = 0; i < networks.length; i++) {
            airtimeChart.data.datasets.push({
                label: networks[i],
                data: airtimeData.map(function(arr) {
                    return arr[i];
                }),
                backgroundColor: backgroundColors[i],
                borderColor: backgroundColors[i],
                borderWidth: 1
            });
        }
        
        airtimeChart.update();
    </script>
</body>
</html>
