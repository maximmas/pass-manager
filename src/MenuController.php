<?php

namespace PassManager;

/**
 * Вывод на экран различных меню
 *
 */
class MenuController
{

    private string $returnMsg;

    public function __construct(){
        $this->returnMsg = "Или нажмите 'q' для возврата в предыдущее меню.\n";
    }

    /**
     * Меню выбора файла если есть файлы
     *
     * @param string $fileName имя существуещого файла
     * @return MenuResponse
     */
    public function existFileMenu(string $fileName): MenuResponse
    {
        $msg = "Нажмите цифру для выбора источника данных:\n";
        $msg .= "1 - Открыть файл {$fileName}\n";
        $msg .= "2 - Создать новый файл\n";
        $msg .= "3 - Ввести путь к файлу.\n";
        $msg .= "4 - Выйти из приложения.\n";
        echo $msg;

        $allowedKeys = array_map(fn($key) => (string)$key, range(1, 4));
        $input = KeyboardInput::getInputKey($allowedKeys);
        $response = new MenuResponse();

        switch ($input) {
            case '1':
                $response->setCommand(UserCommands::OpenFile);
                $response->setProperty('fileName', $fileName);
                break;
            case '2':
                $response->setCommand(UserCommands::CreateFile);
                break;
            case '3':
                $response->setCommand(UserCommands::OpenFile);
                $response->setProperty('fileName', $this->customFileInput());
                break;
            case '4':
                $response->setCommand(UserCommands::ExitOnly);
                break;
            default:
                $response->setCommand(UserCommands::ExitOnly);
        }

        return $response;
    }


    /**
     * Меню выбора файла если нет файла
     *
     * @return MenuResponse
     */
    public function notExistFileMenu(): MenuResponse
    {

        $msg = "Нет созданных ранее файлов данных\n";
        $msg .= "Нажмите цифру для выбора источника данных:\n";
        $msg .= "1 - Создать новый файл\n";
        $msg .= "2 - Ввести путь к файлу.\n";
        $msg .= "3 - Выйти из приложения.\n";
        echo $msg;

        $allowedKeys = array_map(fn($key) => (string)$key, range(1, 3));
        $input = KeyboardInput::getInputKey($allowedKeys);
        $response = new MenuResponse();

        switch ($input) {
            case '1':
                $response->setCommand(UserCommands::CreateFile);
                break;
            case '2':
                $response->setCommand(UserCommands::OpenFile);
                $response->setProperty('fileName', $this->customFileInput());
                break;
            case '3':
                $response->setCommand(UserCommands::ExitOnly);
                break;
            default:
                $response->setCommand(UserCommands::ExitOnly);
        }

        return $response;
    }


    /**
     * Create new file menu
     *
     * @return MenuResponse
     */
    public function createNewFileMenu(): MenuResponse
    {
        $msg = "--------------------------------------------\n";
        $msg .= "Insert new file name with optional path.\n";
        $msg .= "Name must contain only English letters,digits and symbols: _-@&*./\n";
        $msg .= "Examples: data.txt, data/file.txt\n";
        $msg .= "Or press 'q' to exit to previous menu.\n";
        $msg .= "--------------------------------------------\n";
        $msg .= "Your input:\n";
        echo $msg;

        $response = new MenuResponse();

        while (true) {
            $input = KeyboardInput::getInputString();
            if ($input === 'q') {
                $response->setCommand(UserCommands::Back);
                break;
            }
            // проверяем имя файла на допустимые символы
            if (!Validator::newFileNameValidate($input)) {
                echo "Incorrect file name, try again\n";
            } else {
                $response->setCommand(UserCommands::OpenFile);
                $response->setProperty('fileName', $input);
                break;
            }
        }

        return $response;
    }


    /**
     * Ввод пути к кастомному файлу данных
     *
     * @return string
     */
    private function customFileInput(): string
    {
        echo "Insert path to the data file:\n";
        return KeyboardInput::getInputString();
    }


    /**
     * Основное меню на странице вывода аккаунтов
     *
     * @return MenuResponse
     */
    public function mainMenu(): MenuResponse
    {
        $msg = "Введите цифру для выбора действия:\n";
        $msg .= "1 - Найти аккаунт.\n";
        $msg .= "2 - Отсортировать аккаунты.\n";
        $msg .= "3 - Добавить аккаунт.\n";
        $msg .= "4 - Редактировать аккаунт.\n";
        $msg .= "5 - Удалить аккаунт.\n";
        $msg .= "6 - Добавить категорию.\n";
        $msg .= "7 - Удалить категорию.\n";
        $msg .= "8 - Сохранить данные и выйти из приложения.\n";
        $msg .= "9 - Выйти без сохранения.\n";
        echo $msg;

        $allowedKeys = array_map(fn($key) => (string)$key, range(1, 9));
        $input = KeyboardInput::getInputKey($allowedKeys);
        $response = new MenuResponse();

        match ($input) {
            '1' => $response->setCommand(UserCommands::FindAccount),
            '2' => $response->setCommand(UserCommands::SortAccounts),
            '3' => $response->setCommand(UserCommands::AddAccount),
            '4' => $response->setCommand(UserCommands::EditAccount),
            '5' => $response->setCommand(UserCommands::DeleteAccount),
            '6' => $response->setCommand(UserCommands::AddCategory),
            '7' => $response->setCommand(UserCommands::DeleteCategory),
            '8' => $response->setCommand(UserCommands::SaveAndExit),
            '9' => $response->setCommand(UserCommands::ExitOnly),
            default => $response->setCommand(UserCommands::ExitOnly)
        };

        return $response;
    }


    /**
     * Меню выбора аккаунта для удаления
     *
     * @return int
     */
    public function deleteAccountMenu(): string
    {
        $msg = "Введите номер аккаунта, который необходимо удалить:\n";
        $msg .= $this->returnMsg;
        echo $msg;

        $allowedKeys = array_map(fn($key) => (string)$key, range(1, count(Storage::$accounts)));
        $allowedKeys[] = 'q';

        return KeyboardInput::getInputKey($allowedKeys);
    }


    /**
     * Меню выбора аккаунта для редактирования
     *
     * @return int
     */
    public function editAccountMenu(): string
    {
        $msg = "Введите номер аккаунта, который необходимо отредактировать:\n";
        $msg .= $this->returnMsg;
        echo $msg;

        $allowedKeys = array_map(fn($key) => (string)$key, range(1, count(Storage::$accounts)));
        $allowedKeys[] = 'q';

        return KeyboardInput::getInputKey($allowedKeys);
    }


    /**
     * Ввод пароля при входе
     *
     * @return MenuResponse
     */
    public function passwordMenu(): MenuResponse
    {
        $msg = "Введите пароль:\n";
        $msg .= $this->returnMsg;
        echo $msg;

        $response = new MenuResponse();
        while (true) {
            $input = KeyboardInput::getInputString();
            if ($input === 'q') {
                $response->setCommand(UserCommands::ExitOnly);
                return $response;
            }
            $isPasswordValid = Validator::validateEncryptedString($input);
            if (!$isPasswordValid) {
                echo "Пароль содержит некорректные символы, введите еще раз\n";
            } else {
                $response->setCommand(UserCommands::InputPassword);
                $response->setProperty('password', $input);
                break;
            }
        }
        return $response;
    }


    /**
     * Меню ввода имени новой категории
     *
     * @return MenuResponse
     */
    public function addCategoryMenu(): MenuResponse
    {
        $msg = "Введите имя новой категории: \n";
        $msg .= $this->returnMsg;
        echo $msg;

        $response = new MenuResponse();
        while (true) {
            $input = KeyboardInput::getInputString();
            if ($input === 'q') {
                $response->setCommand(UserCommands::Back);
                break;
            }
            $isValid = Validator::newCategoryNameValidate($input) && !in_array($input, Storage::$categories);
            if ($isValid) {
                $response->setCommand(UserCommands::AddCategory);
                $response->setProperty('categoryName', $input);
                break;
            } else {
                echo "Эта строка не может быть использована как имя категории. Попробуйте еще раз.\n";
            }
        }

        return $response;
    }


    /**
     * Меню удаления категории
     *
     * @return MenuResponse
     */
    public function deleteCategoryMenu(): MenuResponse
    {
        $response = new MenuResponse();

        if (!count(Storage::$categories)) {
            $msg = "Нет созданных категорий\n";
            $msg .= $this->returnMsg;
            echo $msg;
            $allowedKeys = ['q'];
            KeyboardInput::getInputKey($allowedKeys);
            $response->setCommand(UserCommands::Back);
            return $response;
        }

        View::showCategoriesTable();
        $msg = "Введите номер категории, которую необходимо удалить:\n";
        $msg .= $this->returnMsg;
        echo $msg;

        $keys = range(1, count(Storage::$categories));
        $keys[] = 'q';
        $allowedKeys = array_map(fn($key) => (string)$key, $keys);
        $input = KeyboardInput::getInputKey($allowedKeys);

        if ($input === 'q') {
            $response->setCommand(UserCommands::Back);
            return $response;
        }

        $index = $input - 1;
        $response->setCommand(UserCommands::DeleteCategory);
        $response->setProperty('categoryIndex', $index);

        return $response;
    }


//    public function accountEditingStep(int $step, string $fieldName){
//
//        $msg =  "Шаг {$step}. Редактирование поля аккаунта: {$fieldName}. \n";
//        $msg .=  "Текущее значение: " . $account['name'] . "\n";
//        $msg .= "Нажмите 'Enter' что бы сохранить его или введите новое значение': \n";
//        echo $msg;
//        $name = $this->getFieldValue('name');
//    }


    /**
     * Меню сортровки аккаунтов
     *
     * @return MenuResponse
     */
    public function accountsSortingMenu(): MenuResponse
    {
        $response = new MenuResponse();

        $msg = "Выберите поле для сортировки:\n";
        $msg .=  $this->returnMsg;
        echo $msg;
        View::showColumnsNameTable();
        $input = KeyboardInput::getInputKey(['1','2','3','4','5','q']);
        if ( $input === 'q') {
            $response->setCommand(UserCommands::Back);
            return $response;
        }
        $columnName = match($input){
            '1' => 'name',
            '2' => 'login',
            '3' => 'password',
            '4' => 'url',
            '5' => 'category',
            default => 'name'
        };

        $response->setCommand(UserCommands::SortAccounts);
        $response->setProperty('column', $columnName);

        return $response;
    }


    /**
     * Меню поиска аккаунта
     *
     * @return MenuResponse
     */
    public function accountFindMenu(): MenuResponse
    {
        $response = new MenuResponse();

        $msg = "Выберите поле по которому осуществляется поиск:\n";
        $msg .=  $this->returnMsg;
        echo $msg;
        View::showColumnsNameTable();
        $input = KeyboardInput::getInputKey(['1','2','3','4','5','q']);
        if ( $input === 'q') {
            $response->setCommand(UserCommands::Back);
            return $response;
        }
        $columnName = match($input){
            '1' => 'name',
            '2' => 'login',
            '3' => 'password',
            '4' => 'url',
            '5' => 'category',
            default => 'name'
        };

        $msg = "Введите строку или символ, которые нужно найти:\n";
        $msg .= $this->returnMsg;
        echo $msg;
        $value = KeyboardInput::getInputString();
        if ( $input === 'q') {
            $response->setCommand(UserCommands::Back);
            return $response;
        }

        $response->setCommand(UserCommands::FindAccount);
        $response->setProperty('column', $columnName);
        $response->setProperty('value', $value);

        return $response;
    }


}