<?php
session_start(); // Start the session
include_once("db_conn.php"); // Include the database connection
include_once("phpqrcode/phpqrcode.php"); // Include QR code generator
include_once("TCPDF/tcpdf.php"); // Include TCPDF for PDF generation

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ob_start(); // Start output buffering to prevent any unwanted output

    // Get the input JSON
    $input = json_decode(file_get_contents("php://input"), true);

    // Sanitize the input
    $nin = isset($input['nin']) ? htmlspecialchars($input['nin']) : null;
    $email = isset($input['email']) ? htmlspecialchars($input['email']) : null;

    // Check if NIN and email are provided
    if (empty($nin) || empty($email)) {
        echo json_encode(['status' => false, 'message' => 'NIN or email missing.']);
        exit();
    }

    // Fetch user balance
    $stmt = $conn->prepare("SELECT user_balance FROM account_balance WHERE email = ?");
    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        echo json_encode(['status' => false, 'message' => 'Database error while fetching balance.']);
        exit();
    }
    $stmt->bind_result($user_balance);
    $stmt->fetch();
    $stmt->close();

    // Set the premium slip price
    $stmt = $conn->prepare("SELECT nin_premium_slip_price FROM id_verification_price LIMIT 1");
    $stmt->execute();
    $stmt->bind_result($nin_premium_slip_price);
    $stmt->fetch();
    $stmt->close();

    // Check if the user has enough balance
    if ($user_balance >= $nin_premium_slip_price) {
        // Deduct the balance
        $new_balance = $user_balance - $nin_premium_slip_price;
        $stmt = $conn->prepare("UPDATE account_balance SET user_balance = ? WHERE email = ?");
        $stmt->bind_param("ds", $new_balance, $email);
        if (!$stmt->execute()) {
            echo json_encode(['status' => false, 'message' => 'Failed to update balance.']);
            exit();
        }
        $stmt->close();

        // Fetch NIN details from seamfix_nin_history
        $sql = "SELECT response_data FROM seamfix_nin_history WHERE response_data LIKE ?";
        $stmt = $conn->prepare($sql);
        $like_nin = '%"nin":"' . $nin . '"%';
        $stmt->bind_param("s", $like_nin);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Output data of the first matching row
            $row = $result->fetch_assoc();
            $response_data = json_decode($row["response_data"], true);

            // Check if 'response' is an array and get the first item
            $response_array = isset($response_data['response']) ? $response_data['response'] : [];
            $first_response = isset($response_array[0]) ? $response_array[0] : [];

            // Accessing the photo data
            $photo_data = isset($first_response['photo']) ? $first_response['photo'] : '';

            // Ensure photo data includes the base64 prefix
            if ($photo_data && strpos($photo_data, 'data:image') === false) {
                $photo_data = 'data:image/jpeg;base64,' . $photo_data;
            }

            // Generate the PDF using TCPDF
            $pdf = new TCPDF();
            $pdf->AddPage();

            // Get page dimensions
            $pageWidth = $pdf->getPageWidth();
            $pageHeight = $pdf->getPageHeight();

            // Define new image dimensions (adjust these values as needed)
            $imageWidth = 105; // New width in mm
            $imageHeight = 115; // New height in mm

            // Calculate X and Y for center positioning
            $x = ($pageWidth - $imageWidth) / 2; // Center X
            $y = ($pageHeight - $imageHeight) / 2; // Center Y

            // Set background image at calculated position with new size
            $pdf->Image('images/premium_bg.png', $x, $y, $imageWidth, $imageHeight, '', '', '', false, 300, '', false, false, 0, false, false, false);

            $pdf->SetFont('helvetica', '', 12);

            
             $html .= '<div class="line" style="display:flex; margin:2%; text-align: center;">';
            $html .= '<img src="' . htmlspecialchars($photo_data) . '" style="width: 100px; height: 110px; position:absute; top:39.5%; left:38.5%; "/>';
            $html .= '</p>';
            $html .= '<div class="text">';
            $html .= '<p style="position: absolute; top:35.7%; left:38.5%; font-size:13px;">' . htmlspecialchars($first_response['surname']) . '</p>';
            $html .= '<p style="position: absolute; top:39.5%; left:38.5%; font-size:13px;">' . htmlspecialchars($first_response['firstname']) . ' ' . htmlspecialchars($first_response['middlename']) . '</p>';
            $html .= '</div>';
            $html .= '<div class="line" style="display:flex; margin:2%; text-align: center;">';
            $html .= '<p style="position: absolute; top:43.5%; left:38.4%; font-size:13px;">' . htmlspecialchars($first_response['birthdate']) . '</p>';
            $html .= '<p style="position: absolute; top:43.5%; left:55%; font-size:13px;">' . htmlspecialchars($first_response['gender']) . '</p>';
            $date = new DateTime($response_data["created_at"]);
            $formatted_date = $date->format('d M Y');
            $formatted_date_upper = strtoupper($formatted_date);
            $html .= '<p style="position: absolute; top:43.5%; left:63%; font-size:13px;">' . $formatted_date_upper . '</p>';
            $html .= '</div>';
            $html .= '<div class="footer">';
            $html .= '<center><p style="position: absolute; text-align:center; left:37%; justify-content: center; top:44%; font-size:18px; letter-spacing:3px;">';

            $nin = $response_data['response'][0]["nin"]; // Assuming 
            if (isset($nin) && strlen($nin) >= 11) {
                $formattedId = substr($nin, 0, 4) . ' ' . substr($nin, 4, 3) . ' ' . substr($nin, 7);
                $html .= $formattedId; // Output: formatted ID number with spaces
            } else {
                $html .= "Invalid ID number format"; // Handle error if necessary
            }
            $html .= '</p></center></div>';
            $html .= '</div>';

            // Add the QR code if necessary (replace with your QR code generation logic)
            $qrCodeData = "Fullname: " . htmlspecialchars($first_response['firstname']) . " " . htmlspecialchars($first_response['middlename']) . " " . htmlspecialchars($first_response['surname']) . " | NIN: " . htmlspecialchars($first_response['nin']);
            QRcode::png($qrCodeData, 'images/qrcode_' . $nin . '.png', QR_ECLEVEL_L, 10, 0);
            $pdf->Image('images/qrcode_' . $nin . '.png', 100, 10, 40, 40, '', '', '', false, 300, '', false, false, 0, false, false, false);

            // Write HTML to PDF
            $pdf->writeHTML($html, true, false, true, false, '');

            // Clear output buffer and set headers for PDF output
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="premium_slip.pdf"');
            ob_end_clean(); // End output buffering and clean output
            
            // Output the PDF (use 'I' for inline display or 'D' for download)
            $pdf->Output('premium_slip.pdf', 'D');
            exit();
        } else {
            echo json_encode(['status' => false, 'message' => 'NIN data not found.']);
            exit();
        }
    } else {
        // Respond with insufficient funds message
        echo json_encode(['status' => false, 'message' => 'Insufficient funds.']);
        exit();
    }
} else {
    // Handle invalid request method
    echo json_encode(['status' => false, 'message' => 'Invalid request method.']);
    exit();
}