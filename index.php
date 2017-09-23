<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

session_start();
require_once('functions.php');
require_once('mysql_helper.php');
require_once('init.php');

// устанавливаем часовой пояс в Московское время
date_default_timezone_set('Europe/Moscow');

$projects_list = [];
$tasks_list = [];
$filtered_tasks = [];
$page_content = null;

// параметры запроса
$project_id = isset($_GET['project']) ? (int) $_GET['project'] : 0;
$add = isset($_GET['add']);
$login = isset($_GET['login']);

$errors = [];
$show_modal = false; // показывать ли модальное окно

// проверки при создании задания
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
  $current_user = selectData($connection, "SELECT * FROM users WHERE `email` = ?", [$_SESSION["user"]["email"]]);
  $current_user = $current_user[0];
  $projects_list = selectData($connection, "SELECT * FROM projects WHERE user_id = ?", [$current_user["id"]]);


  if ($project_id) {
    $is_project_exists = false;
    foreach ($projects_list as $project) {
      if (isset($project["id"]) && $project["id"] == $project_id) {
        $is_project_exists = true;
        break;
      }
    }
  } else {
    $is_project_exists = true;
  }

  if (!$is_project_exists) {
    http_response_code(404);
    exit;
  }

  $tasks_list = selectData($connection, "SELECT * FROM tasks WHERE user_id = ?", [$current_user["id"]]);;
  $filtered_tasks = [];
  // показывать или нет выполненные задачи
  $show_complete_tasks = isset($_COOKIE['showCompleteTasks']) ? (int) $_COOKIE['showCompleteTasks'] === 1 : false;

  // данные для создания нового задания
  $new_task_data  = [
    "name" => (isset($_POST["name"]) ? htmlspecialchars($_POST["name"]) : ""),
    "project" => (isset($_POST["project"]) ? htmlspecialchars($_POST["project"]) : $projects_list[0]),
    "date" => (isset($_POST["date"]) ? $_POST["date"] : ""),
    "preview" => (isset($_POST["preview"]) ? $_POST["preview"] : ""),
    "completed" => false
  ];

  // сохранение новой задачи
  if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST)) {
    $errors = validateForm($required_task, $rules_task, $new_task_data);
    if (!count($errors)) {
      if (isset($_FILES["preview"])) {
         move_uploaded_file($_FILES["preview"]["tmp_name"],  __DIR__ . '/' . $_FILES["preview"]["name"]);
      }
      // array_unshift($tasks_list, $new_task_data);
    }
  }
  $filtered_tasks = find_project_tasks($connection, $project_id, $current_user, $tasks_list);
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
  'user' => $current_user,
  'connection' => $connection
]);
print($layout_content);

?>
