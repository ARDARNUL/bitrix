<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

// Проверка авторизации
if (!$USER->IsAuthorized()) {
    $APPLICATION->AuthForm('Для доступа к личному кабинету необходимо авторизоваться');
    exit;
}

$APPLICATION->SetTitle('Личный кабинет');
$userId = $USER->GetID();

// Получаем баланс
$balance = BalanceManager::getBalance($userId);

?>
<h1>Личный кабинет</h1>

<div style="background: #f5f5f5; padding: 20px; margin: 20px 0; border-radius: 10px;">
    <h2>Ваш баланс: <?= $balance ?> баллов</h2>
    <p>ID пользователя: <?= $userId ?></p>
</div>

<h3>Операции с баллами</h3>
<form method="POST" style="margin: 20px 0; padding: 20px; border: 1px solid #ccc; border-radius: 5px;">
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

<?php
// Обработка операций
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
        
        // Обновляем баланс
        $balance = BalanceManager::getBalance($userId);
        echo '<script>document.querySelector("h2").innerText = "Ваш баланс: ' . $balance . ' баллов";</script>';
    }
}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
?>