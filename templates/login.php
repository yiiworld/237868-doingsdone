<div class="modal">
  <a href="/" class="modal__close">Закрыть</a>

  <h2 class="modal__heading">Вход на сайт</h2>

  <?php if ($just_registered): ?>
    <p>Теперь вы можете войти, используя свой email и пароль</p>
  <?php endif; ?>

  <form class="form" class="" action="index.php" method="post">
    <div class="form__row">
      <label class="form__label" for="email">E-mail <sup>*</sup></label>

      <input class="form__input <?php if (isset($errors["email"])): ?> form__input--error <?php endif; ?>" type="text" name="email" id="email" value="<?=htmlspecialchars($data["email"])?>" placeholder="Введите e-mail">
      <?php if (isset($errors["email"])): ?>
        <p class="form__message"><?=$errors["email"]?></p>
      <?php endif; ?>
    </div>

    <div class="form__row">
      <label class="form__label" for="password">Пароль <sup>*</sup></label>

      <input class="form__input <?php if (isset($errors["password"])): ?> form__input--error <?php endif; ?>"  type="password" name="password" id="password" value="<?=htmlspecialchars($data["password"]);?>" placeholder="Введите пароль">
      <?php if (isset($errors["password"])): ?>
        <p class="form__message"><?=$errors["password"]?></p>
      <?php endif; ?>
    </div>

    <div class="form__row">
      <label class="checkbox">
        <input class="checkbox__input visually-hidden" type="checkbox" checked name="remember">
        <span class="checkbox__text">Запомнить меня</span>
      </label>
    </div>

    <div class="form__row form__row--controls">
      <input class="button" type="submit" name="login" value="Войти">
    </div>
  </form>
</div>
