<?php
AddEventHandler('main', 'OnAfterUserRegister', function($arFields) {
    if ($arFields['USER_ID'] > 0) {
        \Bitrix\Main\Config\Option::set('transactions', 'balance_' . $arFields['USER_ID'], 1000);
    }
});

class BalanceManager {
    public static function changeBalance($userId, $amount, $type = 'INCREMENT') {
        $currentBalance = self::getBalance($userId);
        
        if ($type === 'DECREMENT' && $currentBalance < $amount) {
            return false;
        }
        
        $newBalance = ($type === 'INCREMENT') ? $currentBalance + $amount : $currentBalance - $amount;
        \Bitrix\Main\Config\Option::set('transactions', 'balance_' . $userId, $newBalance);
        
        return true;
    }

    public static function getBalance($userId) {
        return (int)\Bitrix\Main\Config\Option::get('transactions', 'balance_' . $userId, 1000);
    }
}
?>
