<?php
require_once('../config.php'); // Подключение к базе данных
$conn = new mysqli($dbhost, $dbuser, $dbpasswd, $dbname); // Установление соединения

// Получаем ID студента из тела запроса
$fio = $_POST['fio'];
$key = $_POST['key'];

if ($key == 'студент') {
    $sql = "SELECT students.`E-mail` FROM attandance.students WHERE students.FIO = '$fio';";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $headEmail = $row["E-mail"];
} else if ($key == 'преподаватель') {
    $sql = "SELECT teachers.`E-mail` FROM attandance.teachers WHERE teachers.FIO = '$fio';";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $headEmail = $row["E-mail"];
}

// Проверяем, есть ли UserID
$sqlCheckUserID = "SELECT UserID FROM attandance.users WHERE Username = '$headEmail';";
$resultCheckUserID = $conn->query($sqlCheckUserID);
if ($resultCheckUserID->num_rows === 0) {
    // Пользователь не существует, генерируем пароль
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*";
    $password = "";
    for ($i = 0; $i < 10; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }

    // Записываем пароль и другую информацию в CSV-файл
    $csvData = array($fio, $headEmail, $password, $key);
    $file = fopen('users.csv', 'a'); // Открываем файл для записи, добавляя данные в конец файла
    fputcsv($file, $csvData);
    fclose($file);

    // Хешируем пароль и добавляем пользователя в базу данных
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    if ($key == 'студент') {
        $sqlInsertUser = "INSERT INTO attandance.users (Username, `Password`, `Key`) VALUES (?, ?, 'S');";
    } else if ($key == 'преподаватель') {
        $sqlInsertUser = "INSERT INTO attandance.users (Username, `Password`, `Key`) VALUES (?, ?, 'P');";
    }
    $stmtInsertUser = $conn->prepare($sqlInsertUser);
    $stmtInsertUser->bind_param("ss", $headEmail, $hashed_password);
    $stmtInsertUser->execute();
    $stmtInsertUser->close();

    echo json_encode(['message' => 'Пользователь успешно зарегистрирован']);
} else {
    echo json_encode(['error' => 'Пользователь с таким адресом электронной почты уже существует']);
}
?>
