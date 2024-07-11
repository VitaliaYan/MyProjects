<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <title>Авторизация</title>
    <link rel="stylesheet" href="css/authorize.css"> <!-- Подключаем CSS-файл -->
    <link rel="icon" href="images/icon.png" type="image/png">
</head>
<body>
    <!-- Форма входа -->
    <form id="login-form" class="login-form">
        <input type="text" placeholder="Имя пользователя" id="username" onclick="clearInput('username')" required>
        <input type="password" placeholder="Пароль" id="password" onclick="clearInput('password')" required>
        <input type="hidden" id="redirect_url" name="redirect_url" value="<?php echo isset($_GET['redirect_url']) ? $_GET['redirect_url'] : ''; ?>">
        <div id="error" class="error-message"></div> <!-- Для вывода сообщения об ошибке -->
        <input type="submit" value="Войти">
    </form>

<!-- Скрипт для обработки формы с помощью AJAX -->
<script>
    function clearInput(inputId) {
        document.getElementById(inputId).value = ""; // Очищаем содержимое поля ввода
        document.getElementById(inputId).style.borderColor = ""; // Устанавливаем цвет рамки
        document.getElementById(inputId).style.borderWidth = ""; // Устанавливаем ширину рамки
        document.getElementById("error").innerHTML = ""; // Очищаем сообщение об ошибке
    }
    
    document.getElementById("login-form").addEventListener("submit", function(event) {
        event.preventDefault(); // Предотвращаем отправку формы по умолчанию
        var username = document.getElementById('username').value;
        var password = document.getElementById('password').value;
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'authorize.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.redirect) {
                        window.location.href = response.url; // Перенаправление на указанный URL
                    } else {
                        // Обновляем сообщения об ошибках
                        var errorMessage = "";
                        if (response.username_error ) {
                            errorMessage = response.username_error;
                            document.getElementById("username").style.borderColor = "#c3201a"; // Устанавливаем цвет рамки
                            document.getElementById("username").style.borderWidth = "2px"; // Устанавливаем ширину рамки
                        }
                        else if (response.password_error) {
                            errorMessage = response.password_error;
                            document.getElementById("password").style.borderColor = "#c3201a"; // Устанавливаем цвет рамки
                            document.getElementById("password").style.borderWidth = "2px"; // Устанавливаем ширину рамки
                        }
                        document.getElementById("error").innerHTML = errorMessage;
                    }
                }
            }
        };
        var data = "username=" + encodeURIComponent(username) + "&password=" + encodeURIComponent(password) + "&redirect_url=" + encodeURIComponent(document.getElementById('redirect_url').value);
        xhr.send(data);
    });
    </script>
</body>
</html>
