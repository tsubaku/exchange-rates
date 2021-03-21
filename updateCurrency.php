<?php
require_once('./repository.php');

$ch = curl_init();
$url = 'http://www.cbr.ru/scripts/XML_daily.asp';


//Не то что бы все эти параметры были обязательно нужны...
//но когда начинаешь работать с курлом, то уже не можешь остановиться.
curl_setopt($ch, CURLOPT_URL, $url); // отправляем на url
curl_setopt($ch, CURLOPT_HEADER, 0); // пустые заголовки
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // возвратить то что вернул сервер
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // следовать за редиректами
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);// таймаут4
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// отключаем проверку сертификата
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 3);

$rawXml = curl_exec($ch);
$xml = new SimpleXMLElement($rawXml);

try {
    $pdo = connectToBase();

    #Создать таблицу, если её нет.
    $sql = "CREATE TABLE IF NOT EXISTS currency
            (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100),
            rate FLOAT NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
    $pdo->exec($sql);

    #Очистить таблицу
    $sql = "TRUNCATE TABLE currency";
    $pdo->exec($sql);
    echo "TRUNCATE currency table: OK \n";

    #Сформировать строку запроса с плейсхолдерами и сами плейсхолдеры
    #https://dev.mysql.com/doc/refman/8.0/en/insert-optimization.html
    $command = "INSERT INTO currency (name, rate) VALUES ";
    $curr = [];
    $i = 0;
    foreach ($xml as $key) {
        $name = (String)$key->Name;
        $valueRaw = (String)$key->Value; //Привести к float так не получится
        $value = str_replace(',', '.', $valueRaw); //Меняем точку на запятую

        $curr['placeholderName' . $i] = $name;
        $curr['placeholderValue' . $i] = $value;
        $command .= '(:placeholderName' . $i . ', :placeholderValue' . $i . '), ';

        $i++;
    }
    $command = mb_substr($command, 0, -2); //удаляем оконечные ", "

    #Вставить в таблицу новые курсы
    $stmt = $pdo->prepare($command);
    $stmt->execute($curr);

} catch (PDOException $e) {
    $output = 'Error update currency table: ' . $e->getMessage();
    echo $output;
    exit();
}

echo "Updating currency table: OK \n";