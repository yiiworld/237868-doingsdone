<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

session_start();
require_once('functions.php');
require_once('userdata.php');
require_once('mysql_helper.php');
require_once('init.php');
require_once('vendor/autoload.php');

// устанавливаем часовой пояс в Московское время
date_default_timezone_set('Europe/Moscow');

$days = rand(-3, 3);
$task_deadline_ts = strtotime("+" . $days . " day midnight"); // метка времени даты выполнения задачи
$current_ts = strtotime('now midnight'); // текущая метка времени

$date_deadline = date("d.m.Y", $task_deadline_ts);

$days_until_deadline = floor(($task_deadline_ts -$current_ts ) / 86400);

$projects_list = ["Все", "Входящие", "Учеба", "Работа", "Домашние дела", "Авто"];
$tasks_list = [
  ["name" => "Собеседование в IT компании", "date" => "01.06.2018", "project" => "Работа", "completed" => false ],
  ["name" => "Выполнить тестовое задание", "date" => "25.05.2018", "project" => "Работа", "completed" => false ],
  ["name" => "Сделать задание первого раздела", "date" => "21.04.2018", "project" => "Учеба", "completed" => true ],
  ["name" => "Встреча с другом", "date" => "22.04.2018", "project" => "Входящие", "completed" => false ],
  ["name" => "Купить корм для кота", "date" => null, "project" => "Домашние дела", "completed" => false ],
  ["name" => "Заказать пиццу", "date" => null, "project" => "Домашние дела", "completed" => false ]
];
$filtered_tasks = [];

$page_content = null;

// параметры запроса
$project_id = isset($_GET['project']) ? (int) $_GET['project'] : 0;
$add = isset($_GET['add']);
$login = isset($_GET['login']);

$errors = [];
$show_modal = false; // показывать ли модальное окно

// данные для создания нового задания
$new_task_data = [
  "name" => "",
  "project" => $projects_list[0],
  "date" => "",
  "preview" => ""
];
$required_task = ["name", "project", "date"];
$rules_task = ["date" => "validateDate"];

// данные для аутентификации
$user = [ "email" => "", "password" => ""];
$required_user = ["email", "password"];
$rules_user = ["email" => "validateEmail"];

if (isset($_GET['show_completed'])) {
  setcookie('showCompleteTasks', $_GET['show_completed']);
  $header_line = "Location: /index.php" . (isset($_GET['project']) ? '?project=' . $_GET['project'] : '');
  header($header_line);
  exit;
}

if (isset($_SESSION["user"])) {
  if (!array_key_exists($project_id, $projects_list)) {
    http_response_code(404);
    exit;
  }

  // показывать или нет выполненные задачи
  $show_complete_tasks = isset($_COOKIE['showCompleteTasks']) ? (int) $_COOKIE['showCompleteTasks'] === 1 : false;

  // сохранение новой задачи
  if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST)) {
    $new_task_data  = [
      "name" => (isset($_POST["name"]) ? htmlspecialchars($_POST["name"]) : ""),
      "project" => (isset($_POST["project"]) ? htmlspecialchars($_POST["project"]) : ""),
      "date" => (isset($_POST["date"]) ? $_POST["date"] : ""),
      "preview" => (isset($_POST["preview"]) ? $_POST["preview"] : ""),
      "completed" => false
    ];

    $errors = validateForm($required_task, $rules_task, $new_task_data);
    if (!count($errors)) {
      if (isset($_FILES["preview"])) {
         move_uploaded_file($_FILES["preview"]["tmp_name"],  __DIR__ . '/' . $_FILES["preview"]["name"]);
      }
      array_unshift($tasks_list, $new_task_data);
    }
  }
  $filtered_tasks = find_project_tasks($tasks_list, $projects_list[$project_id]);
  $page_content = renderTemplate('./templates/index.php', [
    'tasks_list' => $filtered_tasks,
    'show_complete_tasks' => $show_complete_tasks
  ]);

  // модальное окно добавления задачи
  $show_modal = $add || count($errors);
  if ($show_modal) {
    $modal_content = renderTemplate('./templates/modal.php', [
      'data' => $new_task_data,
      'projects_list' => $projects_list,
      'errors' => $errors
      ]);
    print($modal_content);
  }
} else {
  $page_content = renderTemplate('./templates/guest.php', []);

 // аутентификация
  if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST)) {
    $user = [
      "email" => isset($_POST["email"]) ? $_POST["email"] : "",
      "password" => isset($_POST["password"]) ? $_POST["password"] : ""
    ];
    $errors = validateForm($required_user, $rules_user, $user);
    if (!count($errors)) {
      $tmp_user = searchUserByEmail($user["email"], $users);
      if ($tmp_user && password_verify($user["password"], $tmp_user["password"])) {
        $_SESSION["user"] = $tmp_user;
        header("Location: /index.php");
      } else {
        $errors["email"] = "Вы ввели неверные данные";
        $errors["password"] = "Вы ввели неверные данные";
      }
    }
  }

  // модальное окно логина
  $show_modal = $login || count($errors);
  if ($show_modal) {
    $login_content = renderTemplate('./templates/login.php', [
      'data' => $user,
      'errors' => $errors
    ]);
    print($login_content);
  }
}

$layout_content = renderTemplate('./templates/layout.php', [
  'page_main_content' => $page_content,
  'page_title' => 'Дела в порядке!',
  'projects_list' => $projects_list,
  'tasks_list' => $tasks_list,
  'project_id' => $project_id,
  'overlay' => $show_modal,
  'user' => isset($_SESSION["user"]) ? $_SESSION["user"] : null
]);
print($layout_content);

?>
