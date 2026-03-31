<?php
$host = 'mail.futuredigitaltechltd.net.ng';
$port = 587;
$timeout = 10;

$fp = @fsockopen($host, $port, $errno, $errstr, $timeout);

if (!$fp) {
    echo "CONNECTION_FAILED: $errstr ($errno)\n";
} else {
    echo "CONNECTION_SUCCESSFUL\n";
    fclose($fp);
}
?>