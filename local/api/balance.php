<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Web\Json;

class BalanceAPI
{
    public function getBalance()
    {
        $request = Application::getInstance()->getContext()->getRequest();
        $userId = $request->get('user_id');
        
        if (!$userId) {
            $this->sendResponse(['error' => 'User ID is required'], 400);
            return;
        }
        
        $userId = (int)$userId;
        
        $initPath = $_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/init.php";
        if (file_exists($initPath)) {
            require_once($initPath);
        } else {
            $this->sendResponse(['error' => 'Balance manager not found'], 500);
            return;
        }
        
        $balance = BalanceManager::getBalance($userId);
        
        $this->sendResponse([
            'user_id' => $userId,
            'balance' => $balance,
            'currency' => 'points'
        ]);
    }
    
    public function getTransactions()
    {
        $request = Application::getInstance()->getContext()->getRequest();
        $userId = $request->get('user_id');
        $page = $request->get('page') ?: 1;
        $limit = $request->get('limit') ?: 10;
        
        if (!$userId) {
            $this->sendResponse(['error' => 'User ID is required'], 400);
            return;
        }
        
        $userId = (int)$userId;
        $page = (int)$page;
        $limit = (int)$limit;
        $offset = ($page - 1) * $limit;
        
        $initPath = $_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/init.php";
        if (file_exists($initPath)) {
            require_once($initPath);
        } else {
            $this->sendResponse(['error' => 'Balance manager not found'], 500);
            return;
        }
        
        $transactions = BalanceManager::getUserTransactions($userId, $limit, $offset);
        $total = BalanceManager::getTransactionsCount($userId);
        
        $this->sendResponse([
            'user_id' => $userId,
            'transactions' => $transactions,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }
    
    private function sendResponse($data, $httpCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($httpCode);
        echo Json::encode($data);
        exit;
    }
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$api = new BalanceAPI();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'balance':
        $api->getBalance();
        break;
    case 'transactions':
        $api->getTransactions();
        break;
    default:
        $api->sendResponse(['error' => 'Invalid action'], 400);
        break;
}
?>