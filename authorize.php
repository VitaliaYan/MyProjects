<?php
// Подключаем файл config.php
require_once('config.php');

// Создание подключения
$conn = new mysqli($dbhost, $dbuser, $dbpasswd, $dbname);

// Проверка подключения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Установка времени жизни сессии на 2 часа (7200 секунд)
$session_lifetime = 7200; // 2 часа в секундах
session_set_cookie_params($session_lifetime);
ini_set('session.gc_maxlifetime', $session_lifetime);

// Начало сессии
session_start();

$username_error = ""; // Переменная для хранения ошибки имени пользователя
$password_error = ""; // Переменная для хранения ошибки пароля

// Обработка отправленной формы
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получение данных из формы входа
    $username = $_POST['username'];
    $userpswd = $_POST['password'];
    // Получаем параметр redirect_url из данных формы
    $redirect_url = $_POST['redirect_url']; // По умолчанию перенаправляем на главную страницу

    // Защита от SQL инъекций
    $username = mysqli_real_escape_string($conn, $username);
    $userpswd = mysqli_real_escape_string($conn, $userpswd);

    // Поиск пользователя в базе данных
    $sql = "SELECT * FROM attandance.users WHERE Username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        // Пользователь найден
        $row = $result->fetch_assoc();
        // Проверка введенного пароля
        if (password_verify($userpswd, $row['Password'])) {
            // Аутентификация успешна
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['login_time'] = time(); // Записываем время входа
            $_SESSION['role'] = $row['Key']; // Сохраняем ключ пользователя в сессии
            $_SESSION['userid'] = $row['UserID']; // Сохраняем ключ пользователя в сессии
            // Подготовка ответа
            $response = [
                'redirect' => true,
                'url' => $redirect_url
            ];
        } else {
            // Неправильный пароль
            $password_error = "Неверный пароль";
            // Подготовка ответа с ошибкой
            $response = [
                'redirect' => false,
                'username_error' => "",
                'password_error' => $password_error
            ];
        }
    } else {
        // Пользователь не найден
        $username_error = "Пользователь не найден";
        // Подготовка ответа с ошибкой
        $response = [
            'redirect' => false,
            'username_error' => $username_error,
            'password_error' => ""
        ];
    }
}

// Закрытие соединения с базой данных
$conn->close();

// Отправляем JSON-ответ
header('Content-Type: application/json');
echo json_encode($response);
?>
