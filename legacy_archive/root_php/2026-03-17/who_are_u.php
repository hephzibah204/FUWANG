<?php
// domain.php - Utility function to ensure the correct domain is always fetched

// Function to ensure $currentHost is always the live domain
function ensureCorrectHost(&$currentHost) {
    // Get the live server domain
    $liveDomain = $_SERVER['HTTP_HOST'];
    
    // If $currentHost is empty or contains a hardcoded domain (e.g., 'sadeeqdata.com.ng')
    // or if it does not match the live server domain, redirect to who_is.php
    if (empty($currentHost) || $currentHost !== $liveDomain) {
        // Redirect to who_is.php if domain does not match the live server domain
        header('Location: who_is.php');
        exit();  // Ensure the script stops execution after the redirect
    }
}
?>