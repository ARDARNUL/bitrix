<?php
// test_structure.php
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Текущий путь: " . __FILE__ . "<br>";

// Проверим существование различных путей
$paths_to_check = [
    $_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/init.php",
    $_SERVER["DOCUMENT_ROOT"] . "/../local/php_interface/init.php", 
    $_SERVER["DOCUMENT_ROOT"] . "/bitrix/../local/php_interface/init.php",
    dirname(__FILE__) . "/../../local/php_interface/init.php"
];

echo "<h3>Проверка путей:</h3>";
foreach ($paths_to_check as $path) {
    $exists = file_exists($path) ? "✅ СУЩЕСТВУЕТ" : "❌ НЕ СУЩЕСТВУЕТ";
    echo $path . " - " . $exists . "<br>";
}

// Посмотрим содержимое корневой папки
echo "<h3>Содержимое корня:</h3>";
$root = $_SERVER["DOCUMENT_ROOT"];
$files = scandir($root);
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        $full_path = $root . '/' . $file;
        $type = is_dir($full_path) ? "📁 ДИРЕКТОРИЯ" : "📄 ФАЙЛ";
        echo $type . ": " . $file . "<br>";
    }
}
?>