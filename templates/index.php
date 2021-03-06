<main class="content__main">
  <h2 class="content__main-heading">Список задач</h2>

  <form class="search-form" action="index.php" method="post">
      <input class="search-form__input" type="text" name="task_search_text" value="<?=htmlspecialchars($task_search_text)?>" placeholder="Поиск по задачам">

      <input class="search-form__submit" type="submit" name="task_search" value="Искать">
  </form>

  <div class="tasks-controls">
      <div class="radio-button-group">
          <label class="radio-button">
              <input class="radio-button__input visually-hidden" type="radio" name="radio"
                <?php if ($tasks_type === "all"): ?> checked <?php endif; ?>>
              <a class="radio-button__text" href="/?show_tasks=all<?php if (isset($project_id)):?>&project=<?=$project_id?><?php endif;?>">
                Все задачи
              </a>
          </label>

          <label class="radio-button">
              <input class="radio-button__input visually-hidden" type="radio" name="radio"
                <?php if ($tasks_type === "today"): ?> checked <?php endif; ?>>
              <a class="radio-button__text" href="/?show_tasks=today<?php if (isset($project_id)):?>&project=<?=$project_id?><?php endif;?>">
                Повестка дня
              </a>
          </label>

          <label class="radio-button">
              <input class="radio-button__input visually-hidden" type="radio" name="radio"
                <?php if ($tasks_type === "tomorrow"): ?> checked <?php endif; ?>>
              <a class="radio-button__text" href="/?show_tasks=tomorrow<?php if (isset($project_id)):?>&project=<?=$project_id?><?php endif;?>">
                Завтра
              </a>
          </label>

          <label class="radio-button">
              <input class="radio-button__input visually-hidden" type="radio" name="radio"
              <?php if ($tasks_type === "overdue"): ?> checked <?php endif; ?>>
              <a class="radio-button__text" href="/?show_tasks=overdue<?php if (isset($project_id)):?>&project=<?=$project_id?><?php endif;?>">
                Просроченные
              </a>
          </label>
      </div>

      <?php if ($tasks_type !== "overdue"): ?>
        <label class="checkbox">
            <input id="show-complete-tasks" class="checkbox__input visually-hidden" type="checkbox" <?php if ($show_complete_tasks) : ?> checked <?php endif; ?> >
            <span class="checkbox__text">Показывать выполненные</span>
        </label>
      <?php endif; ?>
  </div>

  <table class="tasks">
    <?php foreach ($tasks_list as $task): ?>
      <?php if (!$task["completed_at"] or $show_complete_tasks) : ?>
        <tr class="tasks__item task <?php if ($task["completed_at"]):?> task--completed <?php endif;?> ">
            <td class="task__select">
                <label class="checkbox task__checkbox">
                    <a href="/?complete_task=<?=$task["id"]?><?php if (isset($project_id)):?>&project=<?=$project_id?><?php endif;?>" style="color: inherit; text-decoration: none;">
                      <input class="checkbox__input visually-hidden" type="checkbox" name="checked_task" value="<?=$task["id"]?>" <?php if ($task["completed_at"]):?> checked <?php endif;?> >
                      <span class="checkbox__text"><?=htmlspecialchars($task["name"])?></span>
                    </a>
                </label>
            </td>

            <td class="task__file">
              <?php
              $splitted_file_path = explode(DIRECTORY_SEPARATOR, $task["file"]);
              $filename = htmlspecialchars($splitted_file_path[count($splitted_file_path) - 1]);
              if (isset($task["file"])): ?>
                <a class="download-link" href="<?php print("/" . $filename); ?>">
                  <?php
                    print($filename);
                  ?>
                </a>
              <?php endif; ?>
            </td>

            <td class="task__date">
              <?php
                if (isset($task["complete_until"])) {
                  print(date_format(date_create($task["complete_until"]), 'd.m.Y'));
                }
              ?>
            </td>

            <td class="task__controls">
              <button class="expand-control" type="button" name="button">Открыть список команд</button>

              <ul class="expand-list hidden">
                  <li class="expand-list__item">
                      <a href="/?complete_task=<?=$task["id"]?><?php if (isset($project_id)):?>&project=<?=$project_id?><?php endif;?>">
                        Выполнить
                      </a>
                  </li>

                  <li class="expand-list__item">
                    <a href="/?delete_task=<?=$task["id"]?><?php if (isset($project_id)):?>&project=<?=$project_id?><?php endif;?>">
                      Удалить
                    </a>
                  </li>

                  <li class="expand-list__item">
                    <a href="/?duplicate_task=<?=$task["id"]?><?php if (isset($project_id)):?>&project=<?=$project_id?><?php endif;?>">
                      Дублировать
                    </a>
                  </li>
              </ul>
            </td>
        </tr>
      <?php endif; ?>
    <?php endforeach; ?>
  </table>
</main>
