<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

$initPath = $_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/init.php";
if (file_exists($initPath)) {
    require_once($initPath);
} else {
    class BalanceManager {
        public static function getBalance($userId) { return 1000; }
        public static function changeBalance($userId, $amount, $type) { return true; }
        public static function getUserTransactions($userId, $limit = 10, $offset = 0) { return []; }
        public static function getTransactionsCount($userId) { return 0; }
    }
}

use Bitrix\Main\UI\PageNavigation;

if (!$USER->IsAuthorized()) {
    $APPLICATION->AuthForm('Для доступа к личному кабинету необходимо авторизоваться');
    exit;
}

$userId = $USER->GetID();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'], $_POST['action'])) {
    $amount = (int)$_POST['amount'];
    $action = $_POST['action'];
    
    if ($amount > 0) {
        if ($action === 'decrement') {
            $result = BalanceManager::changeBalance($userId, $amount, 'DECREMENT');
            if ($result) {
                ShowMessage('Успешно списано ' . $amount . ' баллов');
            } else {
                ShowError('Ошибка: недостаточно средств');
            }
        } else {
            $result = BalanceManager::changeBalance($userId, $amount, 'INCREMENT');
            if ($result) {
                ShowMessage('Успешно начислено ' . $amount . ' баллов');
            } else {
                ShowError('Ошибка при начислении');
            }
        }
        
        LocalRedirect($APPLICATION->GetCurPage());
    } else {
        ShowError('Сумма должна быть положительной');
    }
}

// Пагинация
$pageSize = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $pageSize;

$balance = BalanceManager::getBalance($userId);
$transactions = BalanceManager::getUserTransactions($userId, $pageSize, $offset);
$totalTransactions = BalanceManager::getTransactionsCount($userId);
$totalPages = ceil($totalTransactions / $pageSize);

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

<h3>История операций (всего: <?= $totalTransactions ?>)</h3>
<?php if (!empty($transactions)): ?>
<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
    <tr style="background: #f5f5f5;">
        <th style="padding: 10px; border: 1px solid #ddd;">Дата</th>
        <th style="padding: 10px; border: 1px solid #ddd;">Операция</th>
        <th style="padding: 10px; border: 1px solid #ddd;">Сумма</th>
        <th style="padding: 10px; border: 1px solid #ddd;">Тип</th>
    </tr>
    <?php foreach ($transactions as $transaction): ?>
    <tr>
        <td style="padding: 10px; border: 1px solid #ddd;">
            <?= FormatDate("d.m.Y H:i", MakeTimeStamp($transaction['DATE'])) ?>
        </td>
        <td style="padding: 10px; border: 1px solid #ddd;">
            <?= $transaction['NAME'] ?>
        </td>
        <td style="padding: 10px; border: 1px solid #ddd;">
            <?= $transaction['AMOUNT'] ?>
        </td>
        <td style="padding: 10px; border: 1px solid #ddd;">
            <?= $transaction['TYPE'] ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- Пагинация -->
<div style="margin: 20px 0; text-align: center;">
    <?php if ($currentPage > 1): ?>
        <a href="?page=<?= $currentPage - 1 ?>" style="margin: 0 5px; padding: 5px 10px; border: 1px solid #ccc; text-decoration: none;">← Назад</a>
    <?php endif; ?>
    
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($i == $currentPage): ?>
            <span style="margin: 0 5px; padding: 5px 10px; background: #007bff; color: white; border: 1px solid #007bff;"><?= $i ?></span>
        <?php else: ?>
            <a href="?page=<?= $i ?>" style="margin: 0 5px; padding: 5px 10px; border: 1px solid #ccc; text-decoration: none;"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    
    <?php if ($currentPage < $totalPages): ?>
        <a href="?page=<?= $currentPage + 1 ?>" style="margin: 0 5px; padding: 5px 10px; border: 1px solid #ccc; text-decoration: none;">Вперед →</a>
    <?php endif; ?>
</div>

<?php else: ?>
<p>Нет операций</p>
<?php endif; ?>

<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
?>