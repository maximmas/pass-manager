<?php

namespace PassManager;

require_once 'Console/Table.php';

class View
{

    /**
     * Вывод таблицы аккаунтов
     *
     * @return bool
     */
    public static function showAccountsTable(): bool
    {
        $table = new \Console_Table();
        if (!count(Storage::$accounts)) {
            echo "Нет сохраненных ранее аккаунтов\n";
            return false;
        }
        $table->setHeaders(['№', 'Категория', 'Имя', 'Логин', 'Пароль', 'Ссылка']);
        foreach (Storage::$accounts as $index => $account) {
            $nr = $index + 1;
            extract($account);
            $row = [
                $nr,
                $category,
                $name,
                $login,
                $password,
                $url ?: '-'
            ];
            $table->addRow($row);
        }
        echo $table->getTable();

        return true;
    }


    /**
     * Вывод таблицы аккаунтов
     *
     * @return bool
     */
    public static function showFoundAccountsTable(array $accounts): bool
    {
        $table = new \Console_Table();
        if (!count($accounts)) {
            return false;
        }
        $table->setHeaders(['№', 'Категория', 'Имя', 'Логин', 'Пароль', 'Ссылка']);
        foreach ($accounts as $index => $account) {
            $nr = $index + 1;
            extract($account);
            $row = [
                $nr,
                $category,
                $name,
                $login,
                $password,
                $url ?: '-'
            ];
            $table->addRow($row);
        }
        echo $table->getTable();
        return true;
    }


    /**
     * Вывод таблицы категорий
     *
     * @return void
     */
    public static function showCategoriesTable(): void
    {
        if(count(Storage::$categories)) {
            $table = new \Console_Table();
            $table->setHeaders(['№', 'Категория']);
            foreach (Storage::$categories as $index => $category) {
                $nr = $index + 1;
                $row = [$nr, $category];
                $table->addRow($row);
            }
            echo $table->getTable();
        }
    }


    public static function showColumnsNameTable(): void
    {
        $table = new \Console_Table();
        $table->setHeaders(['№', 'Поле']);
        $table->addRow([1, 'Имя']);
        $table->addRow([2, 'Логин']);
        $table->addRow([3, 'Пароль']);
        $table->addRow([4, 'Ссылка']);
        $table->addRow([5, 'Категория']);
        echo $table->getTable();
    }

}