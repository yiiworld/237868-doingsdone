<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

session_start();
require_once('functions.php');
require_once('userdata.php');

// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);

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

$project_id = isset($_GET['project']) ? $_GET['project'] : 0;

$add = isset($_GET['add']);
$login = isset($_GET['login']);
$errors = [];
$show_modal = false;

$new_task_data = [
  "name" => "",
  "project" => $projects_list[0],
  "date" => "",
  "preview" => ""
];
$required_task = ["name", "project", "date"];
$rules_task = ["date" => "validateDate"];

$user = null;
$userdata = [ "email" => "", "password" => ""];
$required_user = ["email", "password"];
$rules_user = ["email" => "validateEmail"];

if (isset($_SESSION["user"])) {
  $user = $_SESSION["user"];
  if (!array_key_exists($project_id, $projects_list)) {
    http_response_code(404);
  } else {
    $filtered_tasks = find_project_tasks($tasks_list, $projects_list[$project_id]);

    $page_content = renderTemplate('./templates/index.php', [
      'tasks_list' => $filtered_tasks,
      'show_complete_tasks' => $show_complete_tasks
    ]);

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

    $show_modal = $add || count($errors);
    if ($show_modal) {
      $modal_content = renderTemplate('./templates/modal.php', [
        'data' => $new_task_data,
        'projects_list' => $projects_list,
        'errors' => $errors
        ]);
      print($modal_content);
    }
  }
} else {
  $page_content = renderTemplate('./templates/guest.php', []);

  if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST)) {
    $userdata = [
      "email" => isset($_POST["email"]) ? $_POST["email"] : "",
      "password" => isset($_POST["password"]) ? $_POST["password"] : ""
    ];
    $errors = validateForm($required_user, $rules_user, $userdata);
    if (!count($errors)) {
      if ($tmp_user = searchUserByEmail($userdata["email"], $users)) {
        if (password_verify($userdata["password"], $tmp_user["password"])) {
          $_SESSION["user"] = $tmp_user;
          $user = $tmp_user;
          header("Location: /index.php");
        } else {
          $errors["password"] = "Вы ввели неверный пароль";
        }
      }
    }
  }

  $show_modal = $login || count($errors);
  if ($show_modal) {
    $login_content = renderTemplate('./templates/login.php', [
      'data' => $userdata,
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
  'user' => $user
]);
print($layout_content);

?>
