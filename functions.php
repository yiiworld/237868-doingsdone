<?php
require_once('db_functions.php');

/**
* Функция шаблонизации
*
* @param string $template_path Путь к шаблону
* @param array $template_data Данные для заполнения шаблона
*
* @return array $result Результирующий код шаблона
*/
function renderTemplate ($template_path, $template_data) {
  $result = '';
  if (file_exists($template_path)) {
    extract($template_data);
    ob_start();
    require_once($template_path);
    $result = ob_get_clean();
  }
  return $result;
}

/**
* Поиск задач в заданной категории
*
* @param resource $connection Ресурс соединения
* @param integer $project_id Id категории
* @param array $user Данные текущего пользователя
* @param array $tasks Массив со всеми заданиями
*
* @return array $result Результат запроса данных
*/
function find_project_tasks($connection, $project_id, $user, $tasks, $show_complete_tasks) {
  $result = [];
  if ($project_id) {
    $sql = "SELECT * FROM tasks WHERE project_id = ?" .
      " AND user_id = ?" .
       (!$show_complete_tasks ? " AND completed_at IS NULL" : "");
    $result = selectData($connection, $sql, [$project_id, $user["id"]]);
  } else {
    $result = $show_complete_tasks ? $tasks : array_filter($tasks, function ($var) {
      return is_null($var["completed_at"]);
    });
  }
  return $result;
}

/**
* Подстчет количества задач в заданной категории
*
* @param resource $connection Ресурс соединения
* @param integer $project_id Id категории
* @param array $user Данные текущего пользователя
* @param array $tasks Массив со всеми заданиями
*
* @return integer Количество задач в категории
*/
function calc_number_of_tasks($connection, $project_id, $user, $tasks, $show_complete_tasks) {
  return count(find_project_tasks($connection, $project_id, $user, $tasks, $show_complete_tasks));
}

/**
* Проверка корректности и соответствия формату ДД.ММ.ГГГГ введённой даты
*
* @param string $value Дата для проверки
*
* @return string Текст возникшей ошибки
*/
function validateDate($value) {
  $error = null;
  if ($value)  {
    $date = explode(" ", $value);
    $tmp = explode(".", $date[0]);
    if (count($tmp) !== 3 || !checkdate($tmp[1], $tmp[0], $tmp[2])) {
      $error = "Введите дату в формате ДД.ММ.ГГГГ";
    } else if (strtotime($value) < time()) {
      $error = "Нельзя указывать прошедшую дату";
    }
  }
  return $error;
}

/**
* Проверка корректности введённого email
*
* @param string $value Email для проверки
*
* @return string Текст возникшей ошибки
*/
function validateEmail($value) {
  if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
    return "E-mail введён некорректно";
  }
}

/**
* Проверка корректности заполнения формы
*
* @param array $required Список обязательных полей
* @param string $rules Правила проверки полей
* @param array $data Данные для проверки
*
* @return array $errors Список возникших ошибок
*/
function validateForm($required, $rules, $data) {
  $errors = [];
  foreach ($data as $key => $value) {
    if (in_array($key, $required) && $value == "") {
      $errors[$key] = "Заполните это поле";
    }
    if (key_exists($key, $rules)) {
      $error_text = call_user_func($rules[$key], $value);
      if ($error_text) {
        $errors[$key] = $error_text;
      }
    }
  }
  return $errors;
}

/**
* Поиск пользователя по email
*
* @param resource $connection Ресурс соединения
* @param string $email Email пользователя
*
* @return array Найденный пользователь
*/
function searchUserByEmail($connection, $email) {
  return selectData($connection,
    "SELECT * FROM users WHERE email = ?",
    [$email]);
}

/**
*  Проверка существования запрошенного проекта
*
* @param integer $project_id Id проекта
* @param array $projects_list Список проектов
*
* @return array Найденный пользователь
*/
function isProjectExists($project_id, $projects_list) {
  $is_project_exists = true;
  if ($project_id) {
    $is_project_exists = false;
    foreach ($projects_list as $project) {
      if (isset($project["id"]) && $project["id"] == $project_id) {
        $is_project_exists = true;
        break;
      }
    }
  }
  return $is_project_exists;
}


/**
*  Дублирование задачи
*
* @param resource $connection Ресурс соединения
* @param integer $task_id Id задачи
*
*/
function duplicateTask($connection, $task_id) {
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

/**
*  Удаление задачи
*
* @param resource $connection Ресурс соединения
* @param integer $task_id Id задачи
*
*/
function deleteTask($connection, $task_id) {
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

/**
*  Отметка задачи выполненой/невыполненной
*
* @param resource $connection Ресурс соединения
* @param integer $task_id Id задачи
*
*/
function markTaskAsComplete($connection, $task_id) {
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

/**
* Авторизация пользователя
*
* @param resource $connection Ресурс соединения
* @param array $required_user Список обязательных полей формы входа
* @param array $rules_user Правила проверки полей формы входа
* @param array $user Данные пользователя для входа
*
* @return array $errors Список возникших ошибок
*/
function authenticateUser ($connection, $required_user, $rules_user, $user) {
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
  return $errors;
}

/**
*  Создание списка проектов по умолчанию
*
* @param resource $connection Ресурс соединения
* @param integer $user_id Id пользователя
* @param array $default_projects_list Список проектов для создания
*
*/
function createProjectsByDefault($connection, $user_id, $default_projects_list) {
  if (count($default_projects_list) !== 0) {
    $new_user_projects_for_sql = '';
    $new_user_projects_data = [];
    foreach ($default_projects_list as $index => $project) {
      if ($index !== 0) {
        $new_user_projects_for_sql .= ",";
      }
      $new_user_projects_for_sql .= "('" . $project . "', ?". ")";
      $new_user_projects_data[] = $user_id;
    }
    execQuery($connection,
      "INSERT INTO projects (name, user_id) VALUES " .
      $new_user_projects_for_sql,
      $new_user_projects_data);
  }
}

/**
* Регистрация пользователя
*
* @param resource $connection Ресурс соединения
* @param array $rules_user Правила проверки полей формы регистрации
* @param array $user Данные пользователя для входа
* @param string $form_password Пароль пользователя
* @param array $default_projects_list Список проектов, создаваемых по умолчанию
*
* @return array $errors Список возникших ошибок
*/
function registerUser($connection, $rules_user, $user, $form_password, $default_projects_list) {
  $required_user = ["email", "password", "name"];
  $user["password"] = $form_password;
  $errors = validateForm($required_user, $rules_user, $user);
  if (!count($errors)) {
    if (!searchUserByEmail($connection, $user["email"])) {
      $user["password"] = password_hash($form_password, PASSWORD_DEFAULT);
      $insert_result = insertData($connection, "users", $user);
      if ($insert_result) {
        createProjectsByDefault($connection, $insert_result, $default_projects_list);
        header("Location: /index.php?login&just_registered");
      } else {
        $errors["email"] = "Ошибка сохранения. Повторите регистрацию ещё раз.";
      }
    } else {
      $errors["email"] = "Пользователь с таким email уже существует.";
    }
  }
  return $errors;
}

/**
* Добавление новой задачи
*
* @param resource $connection Ресурс соединения
* @param array $required_task Список обязательных полей формы добавления задачи
* @param array $rules_task Правила проверки полей формы добавления задачи
* @param array $new_task_data Данные новой задачи
* @param string $complete_until Дата окончания задачи
*
* @return array $errors Список возникших ошибок
*/
function addTask($connection, $required_task, $rules_task, $new_task_data, $complete_until) {
  $new_task_data["complete_until"] = ($complete_until !== "") ? $complete_until : null;
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
  return $errors;
}

/**
* Добавление нового проекта
*
* @param resource $connection Ресурс соединения
* @param array $new_project Название нового проекта
*
* @return array $errors Список возникших ошибок
*/
function addProject($connection, $new_project) {
  $errors = validateForm(["name"], [], $new_project);
  if (!count($errors)) {
    $insert_result = insertData($connection, "projects", $new_project);
    if ($insert_result) {
      header("Location: /index.php");
    } else {
      $errors["name"] = "Ошибка сохранения. Повторите ещё раз.";
    }
  }
  return $errors;
}
?>
