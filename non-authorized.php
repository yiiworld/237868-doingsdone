<?php

  // обязательные для заполнения поля форм
  $required_user = ["email", "password"];
  $rules_user = ["email" => "validateEmail"];

  $login = isset($_GET['login']) || isset($_POST['login']);

  if ($register) {
    $default_projects_list = ["Входящие", "Учеба", "Работа", "Домашние дела", "Авто"];
    $form_password = isset($_POST["form_password"]) ? htmlspecialchars($_POST["form_password"]) : "";
    $user = [
      "email" => isset($_POST["email"]) ? htmlspecialchars($_POST["email"]) : "",
      "name" => isset($_POST["name"]) ?  htmlspecialchars($_POST["name"]) : ""
    ];

    // регистрация нового пользователя
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["register"])) {
      $errors = registerUser($connection, $rules_user, $user, $form_password, $default_projects_list);
    }

    $page_content = renderTemplate('./templates/register.php', [
      'data' => $user,
      'form_password' => $form_password,
      'errors' => $errors
    ]);
  } else {
    $page_content = renderTemplate('./templates/guest.php', []);
    $user = [
      "email" => isset($_POST["email"]) ? htmlspecialchars($_POST["email"]) : "",
      "password" => isset($_POST["password"]) ? htmlspecialchars($_POST["password"]) : ""
    ];
    // аутентификация
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["login"])) {
      $errors = authenticateUser($connection, $required_user, $rules_user, $user);
    }

    // модальное окно логина
    if ($login) {
      $login_content = renderTemplate('./templates/login.php', [
        'data' => $user,
        'errors' => $errors,
        'just_registered' => isset($_GET["just_registered"])
      ]);
      print($login_content);
    }
  }
 ?>
