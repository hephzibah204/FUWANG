 <?php
 function getNinPremiumSlipPrice($conn) {
                    $query = "SELECT nin_by_nin_price FROM verification_price";
                    $stmt = $conn->prepare($query);
                    $stmt->execute();
                    $stmt->bind_result($nin_by_nin_price);
                    $stmt->fetch();
                    $stmt->close();
                    return $nin_by_nin_price;
                }
