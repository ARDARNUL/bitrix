<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

if (CModule::IncludeModule("iblock")) {
    $dbEnum = CPropertyEnum::GetList([], ["PROPERTY_ID" => 15]);
    
    echo "<h2>Значения свойства TYPE (ID: 15)</h2>";
    while ($arEnum = $dbEnum->Fetch()) {
        echo "<p>ID: {$arEnum['ID']}, XML_ID: '{$arEnum['XML_ID']}', VALUE: '{$arEnum['VALUE']}'</p>";
    }
}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
?>