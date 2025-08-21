<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
require_once($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/init.php");

use Bitrix\Main\UI\PageNavigation;

if (!$USER->IsAuthorized()) {
    $APPLICATION->AuthForm('Для доступа к личному кабинету необходимо авторизоваться');
    exit;
}

$userId = $USER->GetID();

$balance = BalanceManager::getBalance($userId);

$pageSize = 10;
$nav = new PageNavigation("transactions");
$nav->allowAllRecords(false)
    ->setPageSize($pageSize)
    ->initFromUri();

?>
<h1>Личный кабинет</h1>

<div style="background: #f5f5f5; padding: 20px; margin: 20px 0; border-radius: 10px;">
    <h2>Ваш баланс: <?= $balance ?> баллов</h2>
    <p>ID пользователя: <?= $userId ?></p>
</div>

<h3>Операции с баллами</h3>
<form method="POST" style="margin: 20px 0; padding: 20px; border: 1px solid #ccc; border-radius: 5px;">
    <input type="hidden" name="USER_ID" value="<?= $userId ?>">
    <div style="margin-bottom: 15px;">
        <label>Сумма: </label>
        <input type="number" name="amount" required min="1" style="padding: 5px;">
    </div>
    
    <button type="submit" name="action" value="increment" style="background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; margin-right: 10px;">
        Начислить
    </button>
    
    <button type="submit" name="action" value="decrement" style="background: #f44336; color: white; padding: 10px 20px; border: none; border-radius: 5px;">
        Списать
    </button>
</form>
<?php var_dump(method_exists('BalanceManager', 'getTransactionsIblockId'), get_class_methods('BalanceManager')); ?>
<?php
$rc = new ReflectionClass('BalanceManager');
echo 'CLASS FROM: '.$rc->getFileName();
?>
<h3>История операций (последние 10)</h3>
<?php
if (CModule::IncludeModule("iblock")) {
    try {
        $iblockId = BalanceManager::getTransactionsIblockId();
        
        if ($iblockId) {
            $arFilter = array(
                "IBLOCK_ID" => $iblockId,
                "PROPERTY_USER_ID" => $userId
            );
            
            $arSelect = array(
                "ID",
                "DATE_CREATE",
                "PROPERTY_AMOUNT",
                "PROPERTY_TYPE"
            );
            
            $rsElements = CIBlockElement::GetList(
                array("DATE_CREATE" => "DESC"), 
                $arFilter,
                false,
                array(
                    "nPageSize" => $nav->getPageSize(),
                    "iNumPage" => $nav->getCurrentPage(),
                    "bShowAll" => false,
                ),
                $arSelect
            );

            $nav->setRecordCount(CIBlockElement::GetList(array(), $arFilter, array()));
            
            echo '<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">';
            echo '<tr style="background: #f5f5f5;">
                    <th style="padding: 10px; border: 1px solid #ddd;">Дата</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Тип</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Сумма</th>
                  </tr>';
            
            $hasTransactions = false;
            echo "<pre>";
var_dump($iblockId);
var_dump($arFilter);
var_dump($arSelect);
var_dump($rsElements->SelectedRowsCount());
echo "</pre>";
\Bitrix\Main\Diag\Debug::writeToFile("Начинаем получать транзакции. userId: " . $userId . ", iblockId: " . $iblockId, "", "transactions.log");
            while ($arElement = $rsElements->Fetch()) {
                $hasTransactions = true;
                $type = $arElement["PROPERTY_TYPE_VALUE"];
                $amount = $arElement["PROPERTY_AMOUNT_VALUE"];
                $date = FormatDate("d.m.Y H:i", MakeTimeStamp($arElement["DATE_CREATE"]));
                
                echo '<tr>
                        <td style="padding: 10px; border: 1px solid #ddd;">'.$date.'</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">'.($arElement["PROPERTY_TYPE_VALUE"] == "INCREMENT" ? "Начисление" : "Списание").'</td>
                        <td style="padding: 10px; border: 1px solid #ddd; color: '.($type == "INCREMENT" ? "green" : "red").'">
                            '.($type == "INCREMENT" ? "+" : "-").$amount.'
                        </td>
                      </tr>';
            }
            
            if (!$hasTransactions) {
                echo '<tr><td colspan="3" style="padding: 20px; text-align: center; border: 1px solid #ddd;">Нет операций</td></tr>';
            }
            
            echo '</table>';

            // echo $nav->GetPageNavString("", "transactions");

        } else {
            echo '<p>Инфоблок транзакций не найден</p>';
        }
    } catch (Exception $e) {
        echo '<p>Ошибка при загрузке истории операций</p>';
    }
} else {
    echo '<p>Модуль инфоблоков не доступен</p>';
}
?>


<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'], $_POST['action'])) {
    $amount = (int)$_POST['amount'];
    $action = $_POST['action'];
    
    if ($amount <= 0) {
        ShowError('Сумма должна быть положительной');
    } else {
        if ($action === 'decrement') {
            $result = BalanceManager::changeBalance($userId, $amount, 'DECREMENT');
            if ($result) {
                ShowMessage('Успешно списано ' . $amount . ' баллов');
            } else {
                ShowError('Ошибка: недостаточно средств');
            }
        } else {
            BalanceManager::changeBalance($userId, $amount, 'INCREMENT');
            ShowMessage('Успешно начислено ' . $amount . ' баллов');
        }
        
        $balance = BalanceManager::getBalance($userId);
        echo '<script>document.querySelector("h2").innerText = "Ваш баланс: ' . $balance . ' баллов";</script>';
        
        echo '<script>setTimeout(function(){ location.reload(); }, 1000);</script>';
    }
}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
?>