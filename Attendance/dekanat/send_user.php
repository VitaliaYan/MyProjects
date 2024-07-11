<?php
// Подключаем файл config.php
require_once('../config.php');

// Создание подключения
$conn = new mysqli($dbhost, $dbuser, $dbpasswd, $dbname);

// Проверка подключения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_POST['username'];
$password = $_POST['password'];

$hashed_password = password_hash($password, PASSWORD_DEFAULT);
if ($username !== '' && $password !== '')
{
    // Проверяем, есть ли UserID
    $sqlUser = "SELECT UserID FROM attandance.users WHERE Username = '$username';";
    $resultUser = $conn->query($sqlUser);
    if ($resultUser->num_rows === 0) {
        $sqlInsertUser = "INSERT INTO attandance.users (Username, `Password`, `Key`) VALUES (?, ?, 'D');";
        $stmtInsertUser = $conn->prepare($sqlInsertUser);
        $stmtInsertUser->bind_param("ss", $username, $hashed_password);
        $stmtInsertUser->execute();
        $stmtInsertUser->close();
        echo json_encode(['message' => 'Пользователь успешно зарегистрирован']);
    } else {
        echo json_encode(['error' => 'Пользователь с такими данными уже сущесвует']);
    }
} else {
    echo json_encode(['error' => 'Введите все данные, они не могут быть пустыми']);
}


// Закрытие соединения с базой данных
$conn->close();
?>
