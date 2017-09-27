<div class="modal">
    <a href="/" class="modal__close">Закрыть</a>

    <h2 class="modal__heading">Добавление задачи</h2>

    <form class="form" class="" action="index.php" method="post" enctype="multipart/form-data">
        <div class="form__row">
            <label class="form__label" for="name">Название <sup>*</sup></label>

            <input class="form__input <?php if (isset($errors["name"])): ?> form__input--error <?php endif; ?>" type="text" name="name" id="name" value="<?=$data["name"]?>" placeholder="Введите название">
            <?php if (isset($errors["name"])): ?>
              <span class="form__message"><?=$errors["name"]?></span>
            <?php endif; ?>
        </div>

        <div class="form__row">
            <label class="form__label" for="project">Проект <sup>*</sup></label>

            <select class="form__input form__input--select  <?php if (isset($errors["project_id"])): ?> form__input--error <?php endif; ?>" name="project" id="project">
              <?php foreach($projects_list as $project):?>
                 <option value="<?=$project["id"]?>" <?php if ($project["id"] === $data["project_id"]): ?> selected <?php endif; ?>>
                   <?=$project["name"]?>
                 </option>
               <?php endforeach;?>
            </select>
            <?php if (isset($errors["project_id"])): ?>
              <span class="form__message"><?=$errors["project_id"]?></span>
            <?php endif; ?>
        </div>

        <div class="form__row">
            <label class="form__label" for="date">Дата выполнения <sup>*</sup></label>
            <input class="form__input form__input--date <?php if (isset($errors["complete_until"])): ?> form__input--error <?php endif; ?>" type="text" name="date" id="date" value="<?=$data["complete_until"]?>" placeholder="Введите дату в формате ДД.ММ.ГГГГ">
            <?php if (isset($errors["complete_until"])): ?>
              <span class="form__message"><?=$errors["complete_until"]?></span>
            <?php endif; ?>
        </div>

        <div class="form__row">
            <label class="form__label" for="file">Файл</label>

            <div class="form__input-file">
                <input class="visually-hidden" type="file" name="preview" id="preview" value="<?=$data["file"]?>">

                <label class="button button--transparent" for="preview">
                    <span>Выберите файл</span>
                </label>
            </div>
        </div>

        <div class="form__row form__row--controls">
            <input class="button" type="submit" name="add" value="Добавить">
        </div>
    </form>
</div>
