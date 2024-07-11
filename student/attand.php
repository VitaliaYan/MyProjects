<?php
session_start();

// Проверяем, аутентифицирован ли пользователь
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['login_time']) || time() - $_SESSION['login_time'] >= 7200) {
    // Сессия не действительна, перенаправляем пользователя на страницу авторизации с параметром redirect_url
    $redirect_url = 'student/attand.php'; // URL текущей страницы, куда пользователь хотел перейти
    header("Location: ../authorize_form.php?redirect_url=" . urlencode($redirect_url));
    exit;
}

// Если сессия действительна, продолжаем выполнение страницы
if ($_SESSION['role'] === 'S') {
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Учет посещаемости</title>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" type="text/css" href="../css/attand.css">
    <link rel="icon" href="../images/icon.png" type="image/png">
    <script>
        $(document).ready(function() {
            $("#datepicker").datepicker({
                dateFormat: "dd.mm.yy",
                maxDate: 0 // Устанавливаем максимальную доступную дату как сегодняшний день (0)
            });
            
            $('#myModal').modal('hide');
            $("#loadBtn").prop("disabled", true);

            // Обработчик клика по кнопке "Добавить"
            $("#sendBtn").click(function() {
                validateData();
            });

            // Обработчик клика по кнопке "Подтвердить" в модальном окне
            $("#confirmBtn").click(function() {
                // Создаем массив для хранения данных о посещаемости
                var attendanceData = [];
                var groupId = $("#groups").data('id');
                var disciplineId = $("#disciplines").val(); // Значение выбранной дисциплины
                var classId = $("#classes").val(); // Значение выбранного номера пары
                var date = $("#datepicker").val(); // Значение выбранной даты
                var typeId = $("#types").val();

                // Перебираем каждую строку таблицы
                $("#attandance-table tbody tr").slice(1).each(function(index) {
                    // Получаем ФИО студента из первой ячейки строки
                    var fio = $(this).find("td:first").text();
                    // Получаем значение пропусков из выпадающего списка
                    var pass = $(this).find("select[name='pass']").val();
                    // Создаем объект с данными о посещаемости текущего студента
                    var studentAttendance = {
                        FIO: fio,
                        pass: pass
                    };
                    // Добавляем объект в массив посещаемости
                    attendanceData.push(studentAttendance);
                });

                // Отправляем массив данных на сервер для сохранения
                $.ajax({
                    url: 'add_attandance.php',
                    method: 'POST',
                    data: { attendanceData: JSON.stringify(attendanceData), groupId: groupId, disciplineId: disciplineId, classId: classId, date:date, typeId: typeId},
                    success: function(response) {
                        $("#attandance-table-container").hide();
                        $("#sendBtn").hide();
                        $('#myModal').modal('hide');
                        // Парсим JSON ответ от сервера
                        var datajson = JSON.parse(response);
                        if (datajson.errors) {
                            Swal.fire({
                                title: 'Ошибка',
                                text: datajson.errors,
                                confirmButtonText: 'ОК',
                                customClass: {
                                    popup: 'swal-custom',
                                    confirmButton: 'swal-button'
                                },
                                buttonsStyling: false, // Отключает стилизацию по умолчанию для использования кастомных классов
                            });
                        } else if (datajson.success) {
                            Swal.fire({
                                icon: 'success',
                                text: datajson.success,
                                confirmButtonText: 'ОК',
                                customClass: {
                                    popup: 'swal-custom',
                                    confirmButton: 'swal-button'
                                },
                                buttonsStyling: false, // Отключает стилизацию по умолчанию для использования кастомных классов
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        // Обработка ошибки
                        console.error(xhr.responseText);
                    }
                });
            });

            // Обработчик клика по кнопке для загрузки таблицы посещаемости
            $("#loadBtn").click(function() {
                groupId = $("#groups").data('id');
                var disciplineId = $("#disciplines").val();
                var classId = $("#classes").val();
                var date = $("#datepicker").val();
                var typeId = $("#types").val();

                if (groupId !== "" && disciplineId !== "" && classId !== "" && date !== "" && typeId !== "" ) {
                    $.ajax({
                        url: 'get_students.php',
                        method: 'POST',
                        data: { groupId: groupId, disciplineId: disciplineId, classId: classId, date: date, typeId: typeId },
                        success: function(response) {
                            $("#attandance-table-container").html(response).show();
                            $("#sendBtn").show(); // Показываем кнопку после успешной загрузки таблицы
                        },
                        error: function(xhr, status, error) {
                            console.error(xhr.responseText);
                        }
                    });
                } else {
                    // Не все данные заполнены, показываем сообщение
                    Swal.fire({
                        title: 'Ошибка',
                        text: 'Пожалуйста, выберите все данные.',
                        confirmButtonText: 'ОК',
                        customClass: {
                            popup: 'swal-custom',
                            confirmButton: 'swal-button'
                        },
                        buttonsStyling: false, // Отключает стилизацию по умолчанию для использования кастомных классов
                    });
                }
            });

            // Объявляем переменные для хранения выбранных значений группы и семестра
            var groupId = null;
            var termId = null;

            // Обработчик изменения для выпадающего списка с группой
            $("#terms").change(function() {
                groupId = $("#groups").data('id');
                termId = $("#terms").val();
                // Проверяем, выбраны ли оба элемента
                if (groupId !== null && termId !== null) {
                    // Отправляем AJAX-запрос на сервер для получения дисциплин
                    $.ajax({
                    url: "get_disciplines.php", // Путь к PHP-скрипту на сервере
                    type: "POST",
                    data: { groupId: groupId, termId: termId },
                    dataType: "json",
                        success: function(response) {
                            // Очистка и заполнение выпадающего списка с дисциплинами
                            var disciplinesSelect = $("#disciplines");
                            disciplinesSelect.empty();
                            $.each(response, function(index, discipline) {
                                // Добавляем каждую дисциплину как новую опцию в выпадающий список
                                disciplinesSelect.append('<option value="' + discipline.DisciplineID + '">' + discipline.Discipline + '</option>');
                            });
                            $("#loadBtn").prop("disabled", false);
                        }
                    });
                }
            });

        function validateData() {
                var groupId = $("#groups").data('id');
                var disciplineId = $("#disciplines").val();
                var classId = $("#classes").val();
                var date = $("#datepicker").val();
                var typeId = $("#types").val();

                // Проверка заполненности всех данных
                if (groupId && disciplineId && classId && date && typeId) {
                    // Все данные заполнены, выводим строку с данными
                    var groupName = $("#groups").text();
                    var disciplineName = $("#disciplines option:selected").text();
                    var classNumber = $("#classes option:selected").text().split(":")[0];
                    var classTime = $("#classes option:selected").text().split(":")[1].trim();
                    var type = $("#types option:selected").text();
                    var output = '<p><b>Группа</b> ' + groupName + '. <b>Дисциплина:</b> ' + disciplineName + '. <b>Дата:</b> ' + date + '. <b>' + classNumber + ' пара:</b> ' + classTime + '. <b>' +  type + '</b></p>';
                    // Отображаем строку
                    $("#msg").html(output).show();
                    // Открываем модальное окно
                    $("#myModal").modal("show");
                } else {
                    // Не все данные заполнены, показываем сообщение
                    Swal.fire({
                        title: 'Ошибка',
                        text: 'Пожалуйста, выберите все данные',
                        confirmButtonText: 'ОК',
                        customClass: {
                            popup: 'swal-custom',
                            confirmButton: 'swal-button'
                        },
                        buttonsStyling: false, // Отключает стилизацию по умолчанию для использования кастомных классов
                    });
                }
        }
    });
    </script>
</head>
<body>
<form>

<?php
// Подключаем файл config.php
require_once('../config.php');

// Создание подключения
$conn = new mysqli($dbhost, $dbuser, $dbpasswd, $dbname);

// Проверка подключения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
<!-- Контейнер первого уровня -->
<div class="group-container">
<?php
// Запрос к базе данных для получения списка групп
$sql = "SELECT * FROM attandance.groups";
$result = $conn->query($sql);

// Проверяем, есть ли результаты
if ($result->num_rows > 0) {
    // Начало формы с выпадающим списком
    echo '<label>Ваша группа:</label>';
    $userId=$_SESSION['userid'];
    $sqlGroup = "SELECT `groups`.GroupID, `groups`.Groups FROM attandance.students LEFT JOIN attandance.users ON users.Username = students.`E-mail` 
    INNER JOIN attandance.groups ON `groups`.GroupID = students.GroupID WHERE UserID='$userId';";
    $resultGroup = $conn->query($sqlGroup);
    $rowGroup = $resultGroup->fetch_assoc();
    echo '<label style="font-size: 25px;" id="groups" data-id="' . $rowGroup['GroupID'] . '">' . $rowGroup['Groups'] . '</label>';
}

// Запрос к базе данных для получения списка групп
$sql2 = "SELECT * FROM attandance.terms";
$result2 = $conn->query($sql2);

// Проверяем, есть ли результаты
if ($result2->num_rows > 0) {
    // Начало формы с выпадающим списком
    echo '<label for="terms">Выберите семестр:</label>';
    echo '<select id="terms" name="terms">';

    // Выводим каждую группу в виде опции выпадающего списка
    while($row2 = $result2->fetch_assoc()) {
        echo "<option value='" . $row2["TermID"] . "'>" . $row2["Terms"] . "</option>";
    }

    // Закрываем выпадающий список и форму
    echo '</select>';
}
?>
    </div>
    <!-- Выпадающий список с дисциплинами -->
    <!-- Контейнер второго уровня -->
    <div class="discipline-container">
        <label for="disciplines">Выберите дисциплину:</label>
        <select id="disciplines" name="disciplines">
            <!-- Опции будут заполнены динамически с помощью JavaScript -->
        </select>
    </div>
    <!-- Контейнер третьего уровня -->
    <div class="date-time-container">
<?php

// Запрос к базе данных для получения списка пар
$sql4 = "SELECT * FROM attandance.classes;";
$result4 = $conn->query($sql4);

// Проверяем, есть ли результаты
if ($result4->num_rows > 0) {
    // Начало формы с выпадающим списком
    echo '<label for="classes">Выберите номер пары:</label>';
    echo '<select id="classes" name="classes">';

    // Выводим каждую группу в виде опции выпадающего списка
    while($row4 = $result4->fetch_assoc()) {
        echo "<option value='" . $row4["ClassID"] . "'>" . $row4["ClassNumber"] . ": " . $row4["ClassTime"] . "</option>";
    }

    // Закрываем выпадающий список и форму
    echo '</select>';
}

// Запрос к базе данных для получения списка групп
$sql5 = "SELECT * FROM attandance.classtypes;";
$result5 = $conn->query($sql5);

// Проверяем, есть ли результаты
if ($result5->num_rows > 0) {
    // Начало формы с выпадающим списком
    echo '<label for="types">Тип пары:</label>';
    echo '<select id="types" name="types">';

    // Выводим каждую группу в виде опции выпадающего списка
    while($row5 = $result5->fetch_assoc()) {
        echo "<option value='" . $row5["TypeID"] . "'>" . $row5["Type"] . "</option>";
    }

    // Закрываем выпадающий список и форму
    echo '</select>';
}

// Закрытие соединения с базой данных
$conn->close();
?>
    
    <!-- Поле ввода с datepicker -->
    <label for="datepicker">Выберите дату:</label>
    <input type="text" id="datepicker" name="datepicker">
</div>
<button id="loadBtn" type="button">Показать посещаемость</button>
</form>
<!-- Контейнер для таблицы с посещаемостью -->
<div id="attandance-table-container">
        <!-- Сюда будет загружаться содержимое таблицы -->
</div>
<button id="sendBtn">Добавить</button>
<!-- Модальное окно -->
<div class="modal" id="myModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <!-- Заголовок модального окна -->
      <div class="modal-header">
        <h4 class="modal-title">Подтверждение данных</h4>
      </div>
      
      <!-- Тело модального окна -->
      <div class="modal-body">
        <p>Проверьте корректность выбранных данных</p>
        <p id="msg"></p>
      </div>
      
      <!-- Футер модального окна -->
      <div class="modal-footer">
        <button type="button" class="btn-success" id="confirmBtn">Подтвердить</button>
        <button type="button" class="btn-danger" data-dismiss="modal">Отмена</button>
      </div>
      
    </div>
  </div>
</div>
</body>
</html>
<?php
} else {
    // Роль пользователя не 'S', выводим сообщение и перенаправляем на index.php с использованием JavaScript
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ошибка доступа</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../css/attand.css">
    <style>
        /* Стили для центрирования контейнера и элемента внутри */
        html, body {
            height: 100%;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        #errmsg {
            text-align: center; /* Центрируем текст внутри блока */
        }
        #errmsg label {
            font-size: 30px; /* Размер текста */
        }
    </style>
    <script>
        setTimeout(function() {
            window.location.href = "../index.php";
        }, 2000); // Перенаправляем на index.php через 2 секунды
    </script>
</head>
<body>
    <div id="errmsg">
        <label>У вас нет прав доступа к этой странице. Пожалуйста, обратитесь к администратору.</label>
    </div>
</body>
</html>
<?php
}
?>