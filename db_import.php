<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

$user = new CUser;
$userData = [
    'LOGIN' => 'test_user',
    'EMAIL' => 'test@example.com',
    'PASSWORD' => 'password123',
    'CONFIRM_PASSWORD' => 'password123',
];

$userId = $user->Add($userData);
if ($userId) {
    echo "Test user created: ID $userId<br>";
}

$initPath = $_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/init.php";
if (file_exists($initPath)) {
    require_once($initPath);
    BalanceManager::changeBalance($userId, 500, 'INCREMENT');
    BalanceManager::changeBalance($userId, 200, 'DECREMENT');
    BalanceManager::changeBalance($userId, 300, 'INCREMENT');
    
    echo "Test transactions created<br>";
}

echo "Database setup complete!";
?>