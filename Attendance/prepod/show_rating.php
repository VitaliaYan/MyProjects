<?php
session_start();

// Проверяем, аутентифицирован ли пользователь
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['login_time']) || time() - $_SESSION['login_time'] >= 7200) {
    $redirect_url = 'prepod/show_rating.php'; // URL текущей страницы, куда пользователь хотел перейти
    header("Location: ../authorize_form.php?redirect_url=" . urlencode($redirect_url));
    exit;
}

// Если сессия действительна, продолжаем выполнение страницы
if ($_SESSION['role'] === 'P') {
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр рейтинга студентов</title>
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
                
            $("#disc, #terms, #termslbl, #cbdetail").hide();

            $("#switchbox").click(function() {
                // Проверяем состояние переключателя при клике
                if ($(this).is(':checked')) {
                    // Если переключатель включен, показываем элементы с классом "disc" и "terms"
                    $("#disc, #terms, #termslbl, #cbdetail").show();
                } else {
                    // Если переключатель выключен, скрываем элементы с классом "disc" и "terms"
                    $("#disc, #terms, #termslbl, #cbdetail").hide();
                }
                $("#detail").prop("disabled", false);
                $("#attandance-table-container").hide();
                $("#sendBtn").hide();
            });
            $("#from").datepicker({
                dateFormat: "dd.mm.yy",
                maxDate: 0, // Устанавливаем максимальную доступную дату как сегодняшний день (0)
                onSelect: function(selectedDate) {
                    $("#to").datepicker("option", "minDate", selectedDate);
                }
            });
            $("#to").datepicker({
                dateFormat: "dd.mm.yy",
                maxDate: 0, // Устанавливаем максимальную доступную дату как сегодняшний день (0)
                onSelect: function(selectedDate) {
                    $("#from").datepicker("option", "maxDate", selectedDate);
                }
            });

            $("#sendBtn").hide();

            // Обработчик клика по кнопке для загрузки таблицы посещаемости
            $("#loadBtn").click(function() {
                $("#detail").prop("disabled", true);
                var groupId = $("#groups").val();
                var disciplineId = $("#disciplines").val();
                var starttimeId = $("#from").val();
                var endtimeId = $("#to").val();
                var termId = $("#terms").val();

                if ($("#switchbox").is(':checked')) {
                    if (groupId !== "" && disciplineId !== "" && starttimeId !== "" && endtimeId !== "" && termId !== "") {
                        if ($("#detail").prop('checked')) {
                            $.ajax({
                                url: 'show_disc_detail_rating.php',
                                method: 'POST',
                                data: { groupId: groupId, disciplineId: disciplineId, starttimeId: starttimeId, endtimeId: endtimeId, termId: termId},
                                success: function(response) {
                                    // Парсим JSON ответ от сервера
                                    var datajson = JSON.parse(response);
                                    if (datajson.errors) {
                                        $("#attandance-table-container").hide();
                                        $("#sendBtn").hide(); // Скрываем кнопку после появления сообщения об ошибке
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
                                        $("#attandance-table-container").html(datajson.success).show();
                                        $("#sendBtn").show();
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error(xhr.responseText);
                                }
                            });
                        } else {
                            $.ajax({
                                url: 'show_disc_rating.php',
                                method: 'POST',
                                data: { groupId: groupId, disciplineId: disciplineId, starttimeId: starttimeId, endtimeId: endtimeId, termId: termId},
                                success: function(response) {
                                    // Парсим JSON ответ от сервера
                                    var datajson = JSON.parse(response);
                                    if (datajson.errors) {
                                        $("#attandance-table-container").hide();
                                        $("#sendBtn").hide(); // Скрываем кнопку после появления сообщения об ошибке
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
                                        $("#attandance-table-container").html(datajson.success).show();
                                        $("#sendBtn").show();
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error(xhr.responseText);
                                }
                            });
                        }
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
                else {
                    if (groupId !== "" && starttimeId !== "" && endtimeId !== "") {
                        $.ajax({
                            url: 'show_all_rating.php',
                            method: 'POST',
                            data: { groupId: groupId, starttimeId: starttimeId, endtimeId: endtimeId },
                            success: function(response) {
                                // Парсим JSON ответ от сервера
                                var datajson = JSON.parse(response);
                                    if (datajson.errors) {
                                        $("#attandance-table-container").hide();
                                        $("#sendBtn").hide(); // Скрываем кнопку после появления сообщения об ошибке
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
                                        $("#attandance-table-container").html(datajson.success).show();
                                        $("#sendBtn").show();
                                    }
                            },
                            error: function(xhr, status, error) {
                                console.error(xhr.responseText);
                            }
                        });
                    } else {
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

            // Обработчик клика по кнопке для загрузки таблицы посещаемости
            $("#sendBtn").click(function() {
                var groupId = $("#groups").val();
                var disciplineId = $("#disciplines").val();
                var starttimeId = $("#from").val();
                var endtimeId = $("#to").val();
                var termId = $("#terms").val();

                if ($("#switchbox").is(':checked')) {
                    if ($("#detail").prop('checked')) {
                        if (groupId !== "" && disciplineId !== "" && starttimeId !== "" && endtimeId !== "" && termId !== "") {
                            // Формируем URL для перенаправления с параметрами
                            var url = 'export_disc_detail_rating.php?groupId=' + groupId + '&disciplineId=' + disciplineId + '&termId=' + termId + '&starttimeId=' + starttimeId + '&endtimeId=' + endtimeId;
                            // Перенаправляем пользователя на страницу экспорта
                            window.location.href = url;
                        }
                    } else {
                        if (groupId !== "" && disciplineId !== "" && starttimeId !== "" && endtimeId !== "" && termId !== "") {
                            // Формируем URL для перенаправления с параметрами
                            var url = 'export_disc_rating.php?groupId=' + groupId + '&disciplineId=' + disciplineId + '&termId=' + termId + '&starttimeId=' + starttimeId + '&endtimeId=' + endtimeId;
                            // Перенаправляем пользователя на страницу экспорта
                            window.location.href = url;
                        }
                    }
                } else {
                    if (groupId !== "" && starttimeId !== "" && endtimeId !== "") {
                        // Формируем URL для перенаправления с параметрами
                        var url = 'export_all_rating.php?groupId=' + groupId + '&starttimeId=' + starttimeId + '&endtimeId=' + endtimeId;
                        // Перенаправляем пользователя на страницу экспорта
                        window.location.href = url;
                    }

                }
            });

            // Обработчик события change для элементов
            $('#groups, #terms, #disciplines').change(function() {
                $("#detail").prop("disabled", false);
            });


            // Объявляем переменные для хранения выбранных значений группы и семестра
            var groupId = null;
            var termId = null;

            // Обработчик изменения для выпадающего списка с группой
            $("#groups, #terms").change(function() {
                groupId = $("#groups").val();
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
                    }
                });
            }
        });
    });
    </script>
</head>
<body>
<form>
    <label class="switch" id="switchContainer">
        <input type="checkbox" id="switchbox">
        <span class="slider round"></span>
    </label>
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
    echo '<label for="groups">Выберите группу:</label>';
    echo '<select id="groups" name="groups">';

    // Выводим каждую группу в виде опции выпадающего списка
    while($row = $result->fetch_assoc()) {
        echo "<option value='" . $row["GroupID"] . "'>" . $row["Groups"] . "</option>";
    }

    // Закрываем выпадающий список и форму
    echo '</select>';
}

// Запрос к базе данных для получения списка групп
$sql2 = "SELECT * FROM attandance.terms";
$result2 = $conn->query($sql2);

// Проверяем, есть ли результаты
if ($result2->num_rows > 0) {
    // Начало формы с выпадающим списком
    echo '<label for="terms" id="termslbl">Выберите семестр:</label>';
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
    <div class="discipline-container" id="disc">
        <label for="disciplines">Выберите дисциплину:</label>
        <select id="disciplines" name="disciplines">
            <!-- Опции будут заполнены динамически с помощью JavaScript -->
        </select>
    </div>
<?php
// Закрытие соединения с базой данных
$conn->close();
?>
<!-- Контейнер третьего уровня -->
<div class="date-time-container">
    <label for="from">С</label>
    <input type="text" id="from" class="start-date" name="from">
    <label for="to">По</label>
    <input type="text" id="to" class="end-date" name="to">
    <div class="checkbox-container" id="cbdetail">
        <input type="checkbox" id="detail" class="demoCustomCheckbox">
        <label for="detail">Детально</label>
    </div>
</div>
<button id="loadBtn" type="button">Показать рейтинг группы</button>
</form>
<!-- Контейнер для таблицы с посещаемостью -->
<div id="attandance-table-container">
        <!-- Сюда будет загружаться содержимое таблицы -->
</div>
<button id="sendBtn">Выгрузить данные в .xlsx формате</button>
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
