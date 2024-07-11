<?php
// Подключаем файл config.php
require_once('config.php');

// Создание подключения
$conn = new mysqli($dbhost, $dbuser, $dbpasswd, $dbname);

// Проверка подключения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Путь к вашему SQL-скрипту
$sqlFile = 'dumpscript.sql';

// Чтение содержимого файла
$sql = file_get_contents($sqlFile);

// Выполнение SQL-скрипта
if ($conn->multi_query($sql)) {
    do {
        // Хранение первого результата
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    echo "SQL скрипт выполнен успешно";
} else {
    echo "Ошибка при выполнении SQL скрипта: " . $conn->error;
}

// Закрытие соединения
$conn->close();
?>
