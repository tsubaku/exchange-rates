<?php

/**
 * Class Api
 * Общие методы REST API
 */
abstract class Api
{
    public $apiName = ''; //currency

    protected $method = ''; //GET|POST|PUT|DELETE

    public $requestUri = [];
    public $requestParams = [];

    protected $action = ''; //Название метода для выполнения

    protected $hostname = 'localhost'; //Данные для коннекта к БД
    protected $username = 'root';
    protected $password = '';
    protected $dbName = 'exchange';
    protected $charset = 'utf8';


    /**
     * Api constructor. Разбираем реквест.
     * @throws Exception
     */
    public function __construct()
    {
        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");

        #Массив GET параметров разделенных слешем
        $uriWithoutParameters = trim(strtok($_SERVER['REQUEST_URI'], '?'), '/');
        $this->requestUri = explode('/', $uriWithoutParameters);
        $this->requestParams = $_REQUEST;

        #Определение метода запроса
        $this->method = $_SERVER['REQUEST_METHOD'];
        if ($this->method === 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] === 'DELETE') {
                $this->method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] === 'PUT') {
                $this->method = 'PUT';
            } else {
                throw new Exception("Unexpected Header");
            }
        }
    }


    /**
     * Точка входа
     *
     * @return mixed
     */
    public function run()
    {
        #Убираю из Uri первые два элемента, потому что это подкаталоги XAMPP-а
        array_splice($this->requestUri, 0, 2);

        #Первые 2 элемента массива URI должны быть "api" и "currency"
        if (array_shift($this->requestUri) !== 'api' || array_shift($this->requestUri) !== $this->apiName) {
            throw new RuntimeException('API Not Found', 404);
        }

        #Определение действия для обработки
        $this->action = $this->getAction();

        #Если метод(действие) определен в дочернем классе API, то выполняем его
        if (method_exists($this, $this->action)) {
            return $this->{$this->action}();
        } else {
            throw new RuntimeException('Invalid Method', 405);
        }
    }


    /**
     * Собираем ответ
     *
     * @param $data
     * @param int $status
     * @return false|string
     */
    protected function response($data, $status = 500)
    {
        header("HTTP/1.1 " . $status . " " . $this->requestStatus($status));
        return json_encode($data);
    }


    /**
     * Определяем статус ответа
     *
     * @param $code
     * @return mixed
     */
    private function requestStatus($code)
    {
        $status = array(
            200 => 'OK',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        );
        return ($status[$code]) ? $status[$code] : $status[500];
    }


    /**
     * Опрделяем, какой метод API запросили
     * @return string|null
     */
    protected function getAction()
    {
        $method = $this->method;
        switch ($method) {
            case 'GET':
                if ($this->requestUri) {
                    return 'viewAction';
                } else {
                    return 'indexAction';
                }
                break;
            default:
                return null;
        }
    }


    /**
     * Соединение с БД
     *
     * @return PDO
     */
    protected function connectToBase()
    {
        # Cоздать соединение
        $dsn = "mysql:host=$this->hostname;dbname=$this->dbName;charset=$this->charset";
        $opt = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        );
        $pdo = new PDO($dsn, $this->username, $this->password, $opt);

        return $pdo;
    }


    #Методы API
    abstract protected function indexAction();

    abstract protected function viewAction();

}