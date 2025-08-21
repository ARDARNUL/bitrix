<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

echo "<h1>Тест BalanceManager</h1>";

if (class_exists('BalanceManager')) {
    echo "<p style='color:green'>✓ BalanceManager загружен!</p>";
    
    $testUserId = 1;
    $balance = BalanceManager::getBalance($testUserId);
    echo "<p>Баланс пользователя $testUserId: $balance баллов</p>";
    
} else {
    echo "<p style='color:red'>✗ BalanceManager НЕ загружен</p>";
}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
?>