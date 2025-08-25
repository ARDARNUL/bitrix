<?php
header('Content-Type: application/json');
?>
{
  "openapi": "3.0.0",
  "info": {
    "title": "Balance API",
    "description": "API для работы с балансом пользователей",
    "version": "1.0.0"
  },
  "servers": [
    {
      "url": "https://<?= $_SERVER['HTTP_HOST'] ?>/local/api/balance.php",
      "description": "Основной сервер"
    }
  ],
  "paths": {
    "/": {
      "get": {
        "summary": "Информация о API",
        "parameters": [
          {
            "name": "action",
            "in": "query",
            "required": true,
            "schema": {
              "type": "string",
              "enum": ["balance", "transactions"]
            },
            "description": "Тип запроса"
          }
        ],
        "responses": {
          "200": {
            "description": "Успешный ответ"
          }
        }
      }
    }
  },
  "components": {
    "schemas": {
      "BalanceResponse": {
        "type": "object",
        "properties": {
          "user_id": {
            "type": "integer",
            "description": "ID пользователя"
          },
          "balance": {
            "type": "integer",
            "description": "Текущий баланс"
          },
          "currency": {
            "type": "string",
            "description": "Валюта баланса"
          }
        }
      },
      "Transaction": {
        "type": "object",
        "properties": {
          "DATE": {
            "type": "string",
            "format": "date-time",
            "description": "Дата операции"
          },
          "AMOUNT": {
            "type": "integer",
            "description": "Сумма операции"
          },
          "TYPE": {
            "type": "string",
            "description": "Тип операции (INCREMENT/DECREMENT)"
          },
          "NAME": {
            "type": "string",
            "description": "Название операции"
          }
        }
      },
      "TransactionsResponse": {
        "type": "object",
        "properties": {
          "user_id": {
            "type": "integer",
            "description": "ID пользователя"
          },
          "transactions": {
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/Transaction"
            }
          },
          "pagination": {
            "type": "object",
            "properties": {
              "page": {
                "type": "integer",
                "description": "Текущая страница"
              },
              "limit": {
                "type": "integer",
                "description": "Количество элементов на странице"
              },
              "total": {
                "type": "integer",
                "description": "Общее количество операций"
              },
              "pages": {
                "type": "integer",
                "description": "Общее количество страниц"
              }
            }
          }
        }
      },
      "Error": {
        "type": "object",
        "properties": {
          "error": {
            "type": "string",
            "description": "Сообщение об ошибке"
          }
        }
      }
    },
    "parameters": {
      "UserId": {
        "name": "user_id",
        "in": "query",
        "required": true,
        "schema": {
          "type": "integer"
        },
        "description": "ID пользователя"
      },
      "Page": {
        "name": "page",
        "in": "query",
        "required": false,
        "schema": {
          "type": "integer",
          "default": 1
        },
        "description": "Номер страницы"
      },
      "Limit": {
        "name": "limit",
        "in": "query",
        "required": false,
        "schema": {
          "type": "integer",
          "default": 10
        },
        "description": "Количество элементов на странице"
      }
    }
  }
}