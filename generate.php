<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$sql = '';
foreach (\App\Models\PreApprovedAgent::all() as $a) {
    $sql .= "INSERT IGNORE INTO pre_approved_agents (agent_code, first_name, last_name, full_name, email, phone_number, is_claimed, created_at, updated_at) VALUES ('" . addslashes($a->agent_code) . "', '" . addslashes($a->first_name) . "', '" . addslashes($a->last_name) . "', '" . addslashes($a->full_name) . "', '" . addslashes($a->email) . "', '" . addslashes($a->phone_number) . "', 0, NOW(), NOW());\n";
}
file_put_contents('pre_approved_agents_export.sql', $sql);
echo 'Done';
