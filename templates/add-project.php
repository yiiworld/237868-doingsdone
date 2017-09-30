<div class="modal">
  <a href="/" class="modal__close"">Закрыть</a>

  <h2 class="modal__heading">Добавление проекта</h2>

  <form class="form" method="post" action="index.php">
    <div class="form__row">
      <label class="form__label" for="name">Название <sup>*</sup></label>

      <input class="form__input <?php if (isset($errors["name"])): ?> form__input--error <?php endif; ?>" type="text" name="name" id="project_name" value="<?=htmlspecialchars($data["name"])?>" placeholder="Введите название">
      <?php if (isset($errors["name"])): ?>
        <span class="form__message"><?=$errors["name"]?></span>
      <?php endif; ?>
    </div>

    <div class="form__row form__row--controls">
      <input class="button" type="submit" name="add_project" value="Добавить">
    </div>
  </form>
</div>
