<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);

$func = new ClassForm;//чтобы воспользоваться функцией getPathDirecories для отображения пути в котором лежит файл, в колонке Directories
?>

<div class="block1">
    <form action="<?=POST_FORM_ACTION_URI?>" name="table" method="POST">
        <table border="1" bgcolor="#999999">
            <tr>
                <th>Preview</th>
                <th>Name</th>
                <th>Delete/Copy</th>
                <th>Directories</th>
            </tr>
            <tbody>
            <?php foreach($arResult as $elem): ?><!-- отображаем список всех файлов на диске (кроме папок) -->
                <tr>
                    <td width="100px"><img width="150" src="<?=$elem['preview']?>"/></td>
                    <td width="200px"><a class="linkstyle" href="<?=$elem['docviewer']?>"><?=$elem['name']?></a></td>
                    <td width="50px"><input type="checkbox" name="pathfordelcop[]" value="<?=$elem['path']?>" /></td>
                    <td><?=$func->getPathDirecories($elem['path'])?></td>
                </tr>
            <?endforeach;?>
            <tr>
                <td colspan="2">Удалите выбранные файлы (Удалить выбранные папки нельзя)</td>
                <td colspan="2"><input class="btn" type="submit" name="buttondel" value="Удалить выбранные" /></td>
            </tr>
            <?php foreach($arParams['SELECT_FOLDER'] as $folder): ?><!-- Отображаем список папок для копирования туда файлов -->
                <tr>
                    <td colspan="2"><?=$folder?></td>
                    <td colspan="2"><input type="radio" name="pathtofolder" value="<?=$folder?>" /></td>
                </tr>
            <?endforeach;?>
            <tr>
                <td colspan="2">Скопируйте выбранные файлы в папки выше (Пустые папки здесь не отображены, копирование происходит
                    только в те папки, в которых уже существуют файлы. Одинаковые файлы будут перезаписаны.)</td>
                <td colspan="2"><input class="btn" type="submit" name="buttoncopy" value="Скопировать выбранные" /></td>
            </tr>
            </tbody>
        </table>
    </form>
    <br/>Загрузить файл (если такой файл уже существует, он будет перезаписан) <br/><br/>
    <form action="<?=POST_FORM_ACTION_URI?>" name="add" method="POST" enctype='multipart/form-data'>
        <input class="btn" type="file" name="pathtofile" /><br/>
        <input class="btn" type="submit" name="buttonadd" value="Добавить файл" />
    </form>
</div>
<?=$arParams['CHANGE_ACC']   // отображать ссылку на получение токена (если например нужно сменить пользователя)?>