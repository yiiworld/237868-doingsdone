<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

session_start();
require_once('functions.php');
require_once('mysql_helper.php');
require_once('init.php');
require_once('vendor/autoload.php');

// устанавливаем часовой пояс в Московское время
date_default_timezone_set('Europe/Moscow');

$projects_list = [];
$tasks_list = [];
$filtered_tasks = [];
$page_content = null;
$current_user = null;

// параметры запроса
$project_id = isset($_GET['project']) ? intval($_GET['project']) : null;
$add = isset($_GET['add']) || isset($_POST['add']);;
$add_project = isset($_GET['add_project']) || isset($_POST['add_project']);
$login = isset($_GET['login']);
$register = isset($_GET['register']) || isset($_POST['register']);
$task_search = isset($_POST['task_search']);

$errors = [];
$show_modal = false; // показывать ли модальное окно

// проверки при создании задания
$required_task = ["name", "project_id"];
$rules_task = ["complete_until" => "validateDate"];

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

// показывать или нет выполненные задачи
$show_complete_tasks = isset($_COOKIE['showCompleteTasks']) ? (int) $_COOKIE['showCompleteTasks'] === 1 : false;

if (isset($_SESSION["user"])) {
  $current_user = selectData($connection, "SELECT * FROM users WHERE email = ?", [$_SESSION["user"]["email"]])[0];
  $projects_list = selectData($connection, "SELECT * FROM projects WHERE user_id = ?", [$current_user["id"]]);
  $default_project = isset($projects_list[0]) ? $projects_list[0] : null;
  $default_project_id = isset($default_project) ? $default_project["id"] : null;

  // проверка существования запрошенной категории
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
  // данные для создания нового задания
  $new_task_data  = [
    "name" => (isset($_POST["name"]) ? htmlspecialchars($_POST["name"]) : ""),
    "project_id" => (isset($_POST["project"]) ? intval($_POST["project"]) : $default_project_id),
    "file" => (isset($_POST["preview"]) ? $_POST["preview"] : null),
    "user_id" => $current_user["id"]
  ];
  $complete_until = (isset($_POST["date"]) ? $_POST["date"] : "");
  // сохранение новой задачи
  if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add"])) {
    if ($complete_until !== "") {
      $new_task_data["complete_until"] = $complete_until;
    }
    $errors = validateForm($required_task, $rules_task, $new_task_data);
    if (!count($errors)) {
      if (isset($new_task_data["complete_until"])) {
        $new_task_data["complete_until"] = date_format(date_create($new_task_data["complete_until"]), 'Y-m-d H:i:s');
      }

      if (isset($_FILES["preview"]["name"]) && $_FILES["preview"]["name"] !== "") {
        $new_file_path = __DIR__ . DIRECTORY_SEPARATOR . $_FILES["preview"]["name"];
        move_uploaded_file($_FILES["preview"]["tmp_name"], $new_file_path);
        $new_task_data["file"] = $new_file_path;
      }
      $insert_result = insertData($connection, "tasks", $new_task_data);
      if ($insert_result) {
        header("Location: /index.php");
      } else {
        $errors["name"] = "Ошибка сохранения. Повторите ещё раз.";
      }
    }
  }

  // признак, какие задачи показывать:
  // все (all), сегодняшние (today), завтрашние (tomorrow), просроченные (overdue)
  $tasks_type = isset($_GET["show_tasks"]) ? $_GET["show_tasks"] : "all";
  $tasks_type_sql = "SELECT * FROM tasks WHERE user_id = ? " .
          (isset($project_id) ? " AND project_id = ?" : "");
  $tasks_type_data = isset($project_id) ? [$current_user["id"], $project_id] : [$current_user["id"]];

  switch ($tasks_type) {
    case "today":
      $tasks_type_sql .= " AND DATE_FORMAT(complete_until, '%Y-%m-%d') = CURDATE()";
      $filtered_tasks = selectData($connection, $tasks_type_sql, $tasks_type_data);
      break;
    case "tomorrow":
      $tasks_type_sql .= " AND DATE_FORMAT(complete_until, '%Y-%m-%d') = DATE_ADD(CURDATE(), INTERVAL 1 DAY)";
      $filtered_tasks = selectData($connection, $tasks_type_sql, $tasks_type_data);
      break;
    case "overdue":
      $tasks_type_sql .= " AND DATE_FORMAT(complete_until, '%Y-%m-%d') < CURDATE() AND completed_at IS NULL";
      $filtered_tasks = selectData($connection, $tasks_type_sql, $tasks_type_data);
      break;
    default:
      $filtered_tasks = find_project_tasks($connection, $project_id, $current_user, $tasks_list, $show_complete_tasks);
  }

  $task_search_text = isset($_POST["task_search_text"]) ? $_POST["task_search_text"] : "";
  if ($task_search && trim($task_search_text) !== "") {
    $filtered_tasks = selectData($connection,
      "SELECT * FROM tasks WHERE name LIKE ? AND user_id = ?",
      ['%' . trim($task_search_text) . '%', $current_user["id"]]);
  }

  $page_content = renderTemplate('./templates/index.php', [
    'tasks_list' => $filtered_tasks,
    'show_complete_tasks' => $show_complete_tasks,
    'project_id' => $project_id,
    'tasks_type' => $tasks_type,
    'task_search_text' => $task_search_text
  ]);

  // модальное окно добавления задачи
  if ($add) {
    $show_modal = $add;
    $modal_content = renderTemplate('./templates/modal.php', [
      'data' => $new_task_data,
      'complete_until' => $complete_until,
      'projects_list' => $projects_list,
      'errors' => $errors
      ]);
    print($modal_content);
  }

  $new_project = [
    "name" => (isset($_POST["name"]) ? htmlspecialchars($_POST["name"]) : ""),
    "user_id" => $current_user["id"]];
  // сохранение нового проекта
  if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_project"])) {
    $errors = validateForm(["name"], [], $new_project);
    if (!count($errors)) {
      $insert_result = insertData($connection, "projects", $new_project);
      if ($insert_result) {
        header("Location: /index.php");
      } else {
        $errors["name"] = "Ошибка сохранения. Повторите ещё раз.";
      }
    }
  }

  // модальное окно добавления проекта
  if ($add_project) {
    $show_modal = $add_project;
    $modal_content = renderTemplate('./templates/add-project.php', [
      'data' => $new_project,
      'errors' => $errors
      ]);
    print($modal_content);
  }

  // Отметка выполнения задачи
  if (isset($_GET["complete_task"])) {
    $task_id = intval($_GET["complete_task"]);
    if ($task_id) {
      $task = selectData($connection,
        "SELECT completed_at FROM tasks WHERE id = ?",
        [$task_id])[0];
      $new_completed_at = !isset($task["completed_at"]) ? date('Y-m-d H:i:s', time()) : NULL;
      $update_result = execQuery($connection,
        "UPDATE tasks SET completed_at = ? WHERE id = ?",
        [$new_completed_at, $task_id]);
      if ($update_result) {
        $header_line = "Location: /index.php" . (isset($_GET['project']) ? '?project=' . $_GET['project'] : '');
        header($header_line);
      } else {
        $error_content = renderTemplate('templates/error.php', [
          "error" => "Ошибка обновления статуса задачи"
        ]);
      	print($error_content);
      	exit();
      }
    }
  }

  // Удаление задачи
  if (isset($_GET["delete_task"])) {
    $task_id = intval($_GET["delete_task"]);
    if ($task_id) {
      $delete_result = execQuery($connection,
        "DELETE FROM tasks WHERE id = ?",
        [$task_id]);
      if ($delete_result) {
        $header_line = "Location: /index.php" . (isset($_GET['project']) ? '?project=' . $_GET['project'] : '');
        header($header_line);
      } else {
        $error_content = renderTemplate('templates/error.php', [
          "error" => "Ошибка удаления задачи"
        ]);
      	print($error_content);
      	exit();
      }
    }
  }

  // Дублирование задачи
  if (isset($_GET["duplicate_task"])) {
    $task_id = intval($_GET["duplicate_task"]);
    if ($task_id) {
      $duplicate_result = execQuery($connection,
        "INSERT INTO tasks (name, complete_until, completed_at, project_id, user_id) " .
        "SELECT name, complete_until, completed_at, project_id, user_id " .
        "FROM tasks WHERE id = ?",
        [$task_id]);
      if ($duplicate_result) {
        $header_line = "Location: /index.php" . (isset($_GET['project']) ? '?project=' . $_GET['project'] : '');
        header($header_line);
      } else {
        $error_content = renderTemplate('templates/error.php', [
          "error" => "Ошибка дублирования задачи"
        ]);
      	print($error_content);
      	exit();
      }
    }
  }

} else {
  if ($register) {
    $default_projects_list = ["Входящие", "Учеба", "Работа", "Домашние дела", "Авто"];
    $user = [
      "email" => isset($_POST["email"]) ? htmlspecialchars($_POST["email"]) : "",
      "name" => isset($_POST["name"]) ?  htmlspecialchars($_POST["name"]) : ""
    ];
    $form_password = isset($_POST["form_password"]) ? htmlspecialchars($_POST["form_password"]) : "";
    // регистрация нового пользователя
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["register"])) {
      $required_user = ["email", "password", "name"];
      $user["password"] = $form_password;
      $errors = validateForm($required_user, $rules_user, $user);
      if (!count($errors)) {
        if (!searchUserByEmail($connection, $user["email"])) {
          $user["password"] = password_hash($form_password, PASSWORD_DEFAULT);
          $insert_result = insertData($connection, "users", $user);
          if ($insert_result) {
            if (count($default_projects_list) !== 0) {
              $new_user_projects_for_sql = '';
              $new_user_projects_data = [];
              foreach ($default_projects_list as $index => $project) {
                if ($index !== 0) {
                  $new_user_projects_for_sql .= ",";
                }
                $new_user_projects_for_sql .= "('" . $project . "', ?". ")";
                $new_user_projects_data[] = $insert_result;
              }
              execQuery($connection,
                "INSERT INTO projects (name, user_id) VALUES " .
                $new_user_projects_for_sql,
                $new_user_projects_data);
            }
            header("Location: /index.php?login&just_registered");
          } else {
            $errors["email"] = "Ошибка сохранения. Повторите регистрацию ещё раз.";
          }
        } else {
          $errors["email"] = "Пользователь с таким email уже существует.";
        }
      }
    }

    $page_content = renderTemplate('./templates/register.php', [
      'data' => $user,
      'form_password' => $form_password,
      'errors' => $errors
    ]);
  } else {
    $page_content = renderTemplate('./templates/guest.php', []);

   // аутентификация
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["login"])) {
      $user = [
        "email" => isset($_POST["email"]) ? htmlspecialchars($_POST["email"]) : "",
        "password" => isset($_POST["password"]) ? htmlspecialchars($_POST["password"]) : ""
      ];
      $errors = validateForm($required_user, $rules_user, $user);
      if (!count($errors)) {
        $tmp_user = searchUserByEmail($connection, $user["email"])[0];
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
    if ($login || count($errors)) {
      $login_content = renderTemplate('./templates/login.php', [
        'data' => $user,
        'errors' => $errors,
        'just_registered' => isset($_GET["just_registered"])
      ]);
      print($login_content);
    }
  }
}

$layout_content = renderTemplate('./templates/layout.php', [
  'page_main_content' => $page_content,
  'page_title' => 'Дела в порядке!',
  'projects_list' => $projects_list,
  'tasks_list' => $tasks_list,
  'show_complete_tasks' => $show_complete_tasks,
  'project_id' => $project_id,
  'overlay' => $show_modal,
  'user' => $current_user,
  'connection' => $connection,
  'register' => $register
]);
print($layout_content);

?>
