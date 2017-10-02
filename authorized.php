<?php
  // параметры запроса залогиненного пользователя
  $add = isset($_GET['add']) || isset($_POST['add']);;
  $add_project = isset($_GET['add_project']) || isset($_POST['add_project']);
  $task_search = isset($_POST['task_search']);

  // проверки при создании задачи
  $required_task = ["name", "project_id"];
  $rules_task = ["complete_until" => "validateDate"];

  $current_user = selectData($connection, "SELECT * FROM users WHERE email = ?", [$_SESSION["user"]["email"]])[0];
  $projects_list = selectData($connection, "SELECT * FROM projects WHERE user_id = ?", [$current_user["id"]]);
  $default_project = isset($projects_list[0]) ? $projects_list[0] : null;
  $default_project_id = isset($default_project) ? $default_project["id"] : null;

  $filtered_tasks = [];

  // проверка существования запрошенной категории
  if (!isProjectExists($project_id, $projects_list) ) {
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
    $errors = addTask($connection, $required_task, $rules_task, $new_task_data, $complete_until);
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
    $errors = addProject($connection, $new_project);
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
    markTaskAsComplete($connection, $task_id);
  }

  // Удаление задачи
  if (isset($_GET["delete_task"])) {
    $task_id = intval($_GET["delete_task"]);
    deleteTask($connection, $task_id);
  }

  // Дублирование задачи
  if (isset($_GET["duplicate_task"])) {
    $task_id = intval($_GET["duplicate_task"]);
    duplicateTask($connection, $task_id);
  }
 ?>
