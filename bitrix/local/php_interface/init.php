<?php
use Bitrix\Main\Config\Option;

AddEventHandler('main', 'OnAfterUserRegister', function($arFields) {
    if (!empty($arFields['USER_ID'])) {
        Option::set('transactions', 'balance_' . (int)$arFields['USER_ID'], 1000);
    }
});

class BalanceManager
{
    

    public static function changeBalance($userId, $amount, $type = 'INCREMENT')
{
    $amount = (int)$amount;
    $userId = (int)$userId;

    $current = self::getBalance($userId);
    if ($type === 'DECREMENT' && $current < $amount) {
        return false;
    }

    $new = ($type === 'INCREMENT') ? $current + $amount : $current - $amount;
    Option::set('transactions', 'balance_' . $userId, $new);

    if (\CModule::IncludeModule('iblock')) {
        $iblockId = self::getTransactionsIblockId();
        if ($iblockId) {
            $el = new \CIBlockElement();
            
            $typeEnumId = self::getTypeEnumId($type);
            
            $props = [
                'USER_ID' => ['VALUE' => $userId],
                'AMOUNT'  => ['VALUE' => $amount],
                'TYPE'    => ['VALUE' => $typeEnumId], 
                'DATE'    => ['VALUE' => \Bitrix\Main\Type\DateTime::createFromTimestamp(time())]
            ];

            $arLoadProductArray = [
                "IBLOCK_ID" => $iblockId,
                "NAME" => ($type === 'INCREMENT' ? 'Начисление ' : 'Списание ') . $amount,
                "ACTIVE" => "Y",
                "PROPERTY_VALUES" => $props
            ];

            if ($elementId = $el->Add($arLoadProductArray)) {
                return true;
            }
        }
    }

    return true;
}


public static function getTypeEnumId($xmlId)
{
    if (!\CModule::IncludeModule('iblock')) {
        return false;
    }
    
    $dbEnum = \CIBlockPropertyEnum::GetList(
        [],
        [
            "PROPERTY_ID" => 15,    
            "XML_ID" => $xmlId       
        ]
    );
    
    if ($arEnum = $dbEnum->Fetch()) {
        return $arEnum['ID']; 
    }
    
    return false;
}

public static function getBalance($userId)
    {
        return (int)Option::get('transactions', 'balance_' . (int)$userId, 1000);
    }

    public static function getTransactionsIblockId()
    {
        if (!\CModule::IncludeModule('iblock')) {
            return false;
        }
        $row = \Bitrix\Iblock\IblockTable::getList([
            'filter' => ['=CODE' => 'user_transactions'],
            'select' => ['ID'],
            'limit'  => 1,
        ])->fetch();

        return $row ? (int)$row['ID'] : false;
    }
}