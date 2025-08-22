<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

echo "<h2>Проверка загрузки BalanceManager</h2>";

// Проверим, загружен ли класс
if (class_exists('BalanceManager')) {
    echo "<p style='color:green'>✓ BalanceManager загружен</p>";
    
    // Проверим методы
    $methods = get_class_methods('BalanceManager');
    echo "<p>Методы BalanceManager: " . implode(', ', $methods) . "</p>";
    
    // Проверим конкретно наш метод
    if (method_exists('BalanceManager', 'getTypeEnumId')) {
        echo "<p style='color:green'>✓ Метод getTypeEnumId существует</p>";
        
        // Тестируем метод
        echo "<p>INCREMENT ID: " . BalanceManager::getTypeEnumId('INCREMENT') . "</p>";
        echo "<p>DECREMENT ID: " . BalanceManager::getTypeEnumId('DECREMENT') . "</p>";
    } else {
        echo "<p style='color:red'>✗ Метод getTypeEnumId не найден</p>";
    }
    
} else {
    echo "<p style='color:red'>✗ BalanceManager не загружен</p>";
    echo "<p>Проверьте файл: /local/php_interface/init.php</p>";
}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
?>