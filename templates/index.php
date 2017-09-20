<main class="content__main">
  <h2 class="content__main-heading">Список задач</h2>

  <form class="search-form" action="index.php" method="post">
      <input class="search-form__input" type="text" name="" value="" placeholder="Поиск по задачам">

      <input class="search-form__submit" type="submit" name="" value="Искать">
  </form>

  <div class="tasks-controls">
      <div class="radio-button-group">
          <label class="radio-button">
              <input class="radio-button__input visually-hidden" type="radio" name="radio" checked="">
              <span class="radio-button__text">Все задачи</span>
          </label>

          <label class="radio-button">
              <input class="radio-button__input visually-hidden" type="radio" name="radio">
              <span class="radio-button__text">Повестка дня</span>
          </label>

          <label class="radio-button">
              <input class="radio-button__input visually-hidden" type="radio" name="radio">
              <span class="radio-button__text">Завтра</span>
          </label>

          <label class="radio-button">
              <input class="radio-button__input visually-hidden" type="radio" name="radio">
              <span class="radio-button__text">Просроченные</span>
          </label>
      </div>

      <label class="checkbox">
          <input id="show-complete-tasks" class="checkbox__input visually-hidden" type="checkbox" <?php if ($show_complete_tasks) : ?> checked <?php endif; ?> >
          <span class="checkbox__text">Показывать выполненные</span>
      </label>
  </div>

  <table class="tasks">
    <?php foreach ($tasks_list as $key => $task): ?>
      <?php if (!$task["completed"] or $show_complete_tasks) : ?>
        <tr class="tasks__item task <?php if ($task["completed"]):?> task--completed <?php endif;?> ">
            <td class="task__select">
                <label class="checkbox task__checkbox">
                    <input class="checkbox__input visually-hidden" type="checkbox" <?php if ($task["completed"]):?> checked <?php endif;?> >
                    <span class="checkbox__text"><?=htmlspecialchars($task["name"])?></span>
                </label>
            </td>
            <td class="task__date"><?=$task["date"]?></td>

            <td class="task__controls">
              <button class="expand-control" type="button" name="button">Выполнить первое задание</button>

              <ul class="expand-list hidden">
                  <li class="expand-list__item">
                      <a href="#">Выполнить</a>
                  </li>

                  <li class="expand-list__item">
                      <a href="#">Удалить</a>
                  </li>
              </ul>
            </td>
        </tr>
      <?php endif; ?>
    <?php endforeach; ?>
  </table>
</main>
