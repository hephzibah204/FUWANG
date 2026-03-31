 <?php
 function getNinPremiumSlipPrice($conn) {
                    $query = "SELECT verify_by_tracking_id FROM verification_price";
                    $stmt = $conn->prepare($query);
                    $stmt->execute();
                    $stmt->bind_result($verify_by_tracking_id);
                    $stmt->fetch();
                    $stmt->close();
                    return $verify_by_tracking_id;
                }
