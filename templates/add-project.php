<?php
 //  require_once('mysql_helper.php');
 //  require('init.php');
 //
 //  $errors = [];
 //  $new_project = [ "name" => (isset($_POST["name"]) ? htmlspecialchars($_POST["name"]) : ""),
 // ];
 //  // сохранение нового проекта
 //  if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_project"]) && isset($_SESSION["user"])) {
 //    $errors = validateForm(["name"], [], $new_project);
 //    if (!count($errors)) {
 //      $insert_result = insertData($connection, "projects", $new_project);
 //      if ($insert_result) {
 //        header("Location: /index.php");
 //      } else {
 //        $errors["name"] = "Ошибка сохранения. Повторите ещё раз.";
 //      }
 //    }
 //  }
?>

<div class="modal">
  <a href="/" class="modal__close"">Закрыть</a>

  <h2 class="modal__heading">Добавление проекта</h2>

  <form class="form" method="post" action="index.php">
    <div class="form__row">
      <label class="form__label" for="name">Название <sup>*</sup></label>

      <input class="form__input <?php if (isset($errors["name"])): ?> form__input--error <?php endif; ?>" type="text" name="name" id="project_name" value="<?=$data["name"]?>" placeholder="Введите название">
      <?php if (isset($errors["name"])): ?>
        <span class="form__message"><?=$errors["name"]?></span>
      <?php endif; ?>
    </div>

    <div class="form__row form__row--controls">
      <input class="button" type="submit" name="add_project" value="Добавить">
    </div>
  </form>
</div>
