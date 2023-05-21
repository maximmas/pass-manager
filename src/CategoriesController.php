<?php

namespace PassManager;

class CategoriesController
{

    private Encryptor $encryptor;
    private MenuController $menuController;

    public function __construct(Encryptor $encryptor, MenuController $menuController)
    {
        $this->encryptor = $encryptor;
        $this->menuController = $menuController;
    }


    /**
     * Возвращает значения категорий, сохраненные в файле
     *
     * @param array $fileData все данные, получуенные из файла
     * @return false|array
     */
    public function getSavedCategories(array $fileData): false|array
    {
        if (!count($fileData)) {
            return [];
        }

        // категории сохранены в 1-й строке
        $catsEncrypted = $fileData[0];
        $catsDecrypted = $this->encryptor->decrypt($catsEncrypted);

        if(strpos($catsDecrypted, $_ENV['TIME_HASH'])){
            $data = explode($_ENV['TIME_HASH'], $catsDecrypted);
            Storage::$lastTimestamp = $data[0];
            $catsDecrypted = $data[1];
        }

        return @unserialize($catsDecrypted);
    }


    /**
     * Добавляет в текущий массив категорий новое значение
     *
     * @return bool
     */
    public function addNewCategory(): bool
    {
        system('clear');
        View::showCategoriesTable();
        $input = $this->menuController->addCategoryMenu();

        if ($input->getCommand() === UserCommands::Back) {
            return false;
        }

        if ($input->getCommand() === UserCommands::AddCategory) {
            Storage::$categories[] = $input->getProperty('categoryName');
            echo "Новая категория сохранена успешно.\n";
        }

        return true;
    }


    /**
     * Удаляет из массива категорий значение
     *
     * @return false|string string - название удаленной категории, false - ошибка
     */
    public function deleteCategory(): false|string
    {
        system('clear');
        $input = $this->menuController->deleteCategoryMenu();

        if($input->getCommand() === UserCommands::Back){
            return false;
        }

        if($input->getCommand() === UserCommands::DeleteCategory){
            $indexToDelete = $input->getProperty('categoryIndex');
            if (array_key_exists($indexToDelete, Storage::$categories)) {
                $categoryName = Storage::$categories[$indexToDelete];
                unset(Storage::$categories[$indexToDelete]);
                // меняем (упорядочиваем) ключи в массиве категорий
                if(count(Storage::$categories)) {
                    Storage::$categories = array_combine(
                        range(0, count(Storage::$categories) - 1),
                        Storage::$categories
                    );
                }
            }
        }
        return $categoryName ?? false;
    }
}