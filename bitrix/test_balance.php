<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

echo "<h1>Тест BalanceManager</h1>";

if (class_exists('BalanceManager')) {
    echo "<p style='color:green'>✓ BalanceManager загружен</p>";
    
    $testUserId = 1;
    $balance = BalanceManager::getBalance($testUserId);
    echo "<p>Баланс пользователя $testUserId: <b>$balance баллов</b></p>";
    
    $result = BalanceManager::changeBalance($testUserId, 100, 'INCREMENT');
    if ($result) {
        $newBalance = BalanceManager::getBalance($testUserId);
        echo "<p>✓ Начисление работает: $newBalance баллов</p>";
    }
    
} else {
    echo "<p style='color:red'>✗ BalanceManager НЕ загружен</p>";
    echo "<p>Проверьте файл: /local/php_interface/init.php</p>";
}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
?>