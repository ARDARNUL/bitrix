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

        $current = self::getBalance($userId);
        if ($type === 'DECREMENT' && $current < $amount) {
            return false;
        }

        $new = ($type === 'INCREMENT') ? $current + $amount : $current - $amount;
        Option::set('transactions', 'balance_' . $userId, $new);

        if (\CModule::IncludeModule('iblock')) {
            \Bitrix\Main\Diag\Debug::writeToFile("Начинаем добавлять транзакцию. userId: " . $userId . ", amount: " . $amount . ", type: " . $type, "", "transactions.log");
            $iblockId = self::getTransactionsIblockId();
            if ($iblockId) {
                $el = new \CIBlockElement();
                $props = [
                    'AMOUNT'  => $amount,
                    'TYPE'    => $type, 
                ];
        		$arLoadProductArray = Array(
					"MODIFIED_BY"    => $GLOBALS["USER"]->GetID(), 
					"IBLOCK_SECTION_ID" => false,       
					"IBLOCK_ID"      => $iblockId,
					"PROPERTY_VALUES"=> $props,
					"NAME"           => ($type === 'INCREMENT' ? 'Начисление ' : 'Списание ') . $amount,
					"ACTIVE"         => "Y",          
					);

				$el->SetPropertyValuesEx(false, false, array("USER_ID" => $userId));

                if($PRODUCT_ID = $el->Add($arLoadProductArray))
  					echo "New ID: ".$PRODUCT_ID;
				else
  					echo "Error: ".$el->LAST_ERROR;
            }
        }

        return true;
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