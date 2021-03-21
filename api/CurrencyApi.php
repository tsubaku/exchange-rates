<?php
require_once 'Api.php';

/**
 * Class CurrencyApi
 * Методы для работы с курсами валют
 */
class CurrencyApi extends Api
{
    public $apiName = 'currency';

    /**
     * Метод GET
     * Вывод списка всех валют с возможностью пагинации
     * http://ДОМЕН/api/currency
     * http://ДОМЕН/api/currency/?per_page=5&page=2
     * @return string
     */
    public function indexAction()
    {
        $pdo = $this->connectToBase();
        $sql = "SELECT * FROM currency";

        #Формируем строку запроса
        if (array_key_exists('per_page', $this->requestParams)) {
            $sql .= " LIMIT ?";
            if (array_key_exists('page', $this->requestParams)) {
                $sql .= " OFFSET ?";
            }
        }

        #Подтягиваем плейсхолдеры
        $stmt = $pdo->prepare($sql);
        if (array_key_exists('per_page', $this->requestParams)) {
            $limit = $this->requestParams['per_page'];
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            if (array_key_exists('page', $this->requestParams)) {
                $offset = $this->requestParams['page'] * $limit - $limit;
                $stmt->bindValue(2, $offset, PDO::PARAM_INT);
            }
        }

        #Выполняем
        $stmt->execute();
        $currencies = $stmt->fetchAll();
        if ($currencies) {
            return $this->response($currencies, 200);
        }
        return $this->response('Data not found', 404);
    }


    /**
     * Метод GET
     * Просмотр отдельной записи (по id)
     * http://ДОМЕН//api/currency/12
     * @return string
     */
    public function viewAction()
    {
        #id должен быть первым параметром после /currency/{id}
        $id = array_shift($this->requestUri);
        if ($id) {
            $pdo = $this->connectToBase();
            $sql = "SELECT * FROM currency WHERE id= :id";
            $curr = [
                'id' => $id
            ];
            $stmt = $pdo->prepare($sql);
            $stmt->execute($curr);
            $currency = $stmt->fetch();
            $currencyRate = $currency['rate'];
            if ($currencyRate) {
                return $this->response($currencyRate, 200);
            }
        }
        return $this->response('Data not found', 404);

    }


}