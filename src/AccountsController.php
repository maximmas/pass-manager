<?php

namespace PassManager;

/**
 * Контроллер аккаунтов
 */
class AccountsController
{

    private Encryptor $encryptor;
    private MenuController $menuController;
    private string $oldValueMsg;
    private string $returnMsg;
    private string $dividerStr;


    public function __construct(Encryptor $encryptor, MenuController $menuController)
    {
        $this->encryptor = $encryptor;
        $this->menuController = $menuController;
        $this->oldValueMsg = "Нажмите 'Enter' для сохранения предыдущего значение\n";
        $this->returnMsg = "Или нажмите 'q' для возврата в предыдущее меню.\n";
        $this->dividerStr = "----------------------------------------------\n";
    }


    /**
     * Создание нового аккаунта
     *
     * @return false|array
     */
    public function createAccount(): false|array
    {
        $account = [];
        system('clear');

        if (!count(Storage::$categories)) {
            echo "Нет категорий.\n";
            echo "Перед созданием аккаунта необходимо создать категории в меню 'Добавить категорию'.\n";
            echo $this->returnMsg;
            if (KeyboardInput::getInputKey(['q']) === 'q') {
                return false;
            };
        }

        echo "Необходимо ввести данные аккаунта: имя, логин, пароль, категория, адрес.\n";
        echo $this->returnMsg;
        echo $this->dividerStr;
        echo "Шаг 1. Введите имя нового аккаунта. \n";
        $name = $this->getFieldValue('name');
        if ($name !== 'q') {
            $account['name'] = $name;
        } else {
            return false;
        }

        echo $this->dividerStr;
        echo "Шаг 2. Введите логин. \n";
        $login = $this->getFieldValue('login');
        if ($login !== 'q') {
            $account['login'] = $login;
        } else {
            return false;
        }


        $suggestedPassword = $this->generatePassword();
        $msg = $this->dividerStr;
        $msg .= "Шаг 3. Введите пароль. \n";
        $msg .= "Нажмите 'Enter' что бы сохранить автоматически созданный пароль : {$suggestedPassword}\n";
        echo $msg;
        $password = $this->getFieldValue('password', true);
        if ($password === 'q') {
            return false;
        }
        if ($password === '') {
            $account['password'] = $suggestedPassword;
        } else {
            $account['password'] = $password;
        }

        echo $this->dividerStr;
        echo "Шаг 4. Введите имя категории. \n";
        $key = 1;
        foreach (Storage::$categories as $category) {
            echo "{$key} - {$category}\n";
            $key++;
        }
        $keys = range(1, count(Storage::$categories));
        $allowedKeys = array_map(fn($key) => (string)$key, $keys);
        $allowedKeys[] = 'q';
        $input = KeyboardInput::getInputKey($allowedKeys);
        if ($input === 'q') {
            return false;
        }
        $categoryIndex = (int)$input - 1;
        $account['category'] = Storage::$categories[$categoryIndex];


        echo $this->dividerStr;
        echo "Шаг 4. Введите URL сервиса.\n";
        echo "Это необязательное поле, нажмите 'Enter' что бы оставить его пустым.\n";
        echo $this->returnMsg;
        $url = $this->getFieldValue('url', true);

        if ($url === 'q') {
            return false;
        } else {
            $account['url'] = $url === '' ? false : $url;
        }

        return $account;
    }


    private function getFieldValue(string $fieldName, bool $isOptional = false): string
    {
        while (true) {
            echo "> ";
            $value = KeyboardInput::getInputString();

            // если поле необязательное - возвращаем пустую строку
            if (!$value and $isOptional) {
                break;
            }

            if (Validator::accountFieldValidate($value)) {
                break;
            } else {
                echo "Эта строка не может быть использована как {$fieldName}. Try again.\n";
            }
        }

        return $value;
    }


    /**
     * Вывод таблицы аккаунтов
     *
     * @return void
     */
    public function showAccounts(): void
    {
        system('clear');

        echo "Дата последнего сохранения: " . date('Y-m-d H:i:s',  Storage::$lastTimestamp) . PHP_EOL;

        if (count(Storage::$accounts)) {
            View::showAccountsTable();
        } else {
            echo "Нет аккаунтов \n";
        }
    }


    /**
     * Удаление аккунта
     *
     * @param int $index
     * @return void
     */
    public function deleteAccount(int $index): void
    {
        if (array_key_exists($index, Storage::$accounts)) {
            unset(Storage::$accounts[$index]);

            if(count(Storage::$accounts)) {
                // меняем (упорядочиваем) ключи в массиве категорий
                Storage::$accounts = array_combine(range(0, count(Storage::$accounts) - 1), Storage::$accounts);
            }
        }
    }


    /**
     * Возвращает значения аккаунтов, сохраненных в файле
     *
     * @param array $fileData все данные, получуенные из файла
     * @return false|array
     */
    public function getSavedAccounts(array $fileData): false|array
    {

        if (!count($fileData) ) {
            return [];
        }

        // пропускаем массив с 1-й строкой, в первой строке всегда категории
        if (!count($fileData) || count($fileData) === 1) {
            return [];
        }

        $decryptedAccounts = [];

        $encryptedAccountsArray = array_slice($fileData, 1);
        foreach ($encryptedAccountsArray as $encryptedAccount) {
            $decryptedAccount = $this->encryptor->decrypt($encryptedAccount);
            $accountArray = @unserialize($decryptedAccount);
            if ($accountArray === false) {
                return false;
            } else {
                $decryptedAccounts[] = $accountArray;
            }
        }
        return $decryptedAccounts;
    }


    /**
     * Редактирование аккунта
     *
     * @param int $index
     * @return bool
     */
    public function editAccount(int $index): bool
    {
        if (!array_key_exists($index, Storage::$accounts)) {
            return false;
        }

        if (!count(Storage::$accounts)) {
            $msg = "Нет аккаунтов, которые можно редактировать.\n";
            $msg .=  "Перед созданием аккаунта необходимо создать категории в меню 'Добавить категорию'.\n";
            $msg .=  "Нажмите 'q' для выхода в предыдущее меню.\n";
            echo $msg;
            if (KeyboardInput::getInputKey(['q']) === 'q') {
                return false;
            };
        }

        $account = Storage::$accounts[$index];
        system('clear');

        $msg = "Пошаговое редактирование данных аккаунта.\n";
        $msg .= $this->returnMsg;
        $msg .= "Шаг 1. Редактирование имени. \n";
        $msg .= "Текущее значение: " . $account['name'] . "\n";
        $msg .= "Нажмите 'Enter' что бы сохранить его или введите новое значение': \n";
        echo $msg;
        $name = $this->getFieldValue('name', true);
        if ($name === 'q') {
            return false;
        }
        if ($name !== '') {
            $account['name'] = $name;
        }

        $msg = $this->dividerStr;
        $msg .= "Шаг 2. Редактирование логина. \n";
        $msg .= "Текущее значение: " . $account['login'] . "\n";
        $msg .= "Нажмите 'Enter' что бы сохранить его или введите новое значение': \n";
        echo $msg;
        $login = $this->getFieldValue('login', true);
        if ($login === 'q') {
            return false;
        }
        if ($login !== '') {
            $account['login'] = $login;
        }

        $msg = "---------------------------------------\n";
        $msg .= "Шаг 3. Редактирование пароля. \n";
        $msg .= "Текущее значение: " . $account['password'] . "\n";
        $msg .= "Нажмите 'Enter' что бы сохранить его или введите новое значение': \n";
        echo $msg;
        $password = $this->getFieldValue('password', true);
        if ($password === 'q') {
            return false;
        }
        if ($password !== '') {
            $account['password'] = $password;
        }

        $msg = "---------------------------------------\n";
        $msg .= "Шаг 4. Редактирование категории. \n";
        $msg .= "Текущее значение: " . $account['category'] . "\n";
        $msg .= "Нажмите 'Enter' что бы сохранить его или введите новое значение': \n";
        echo $msg;
        View::showCategoriesTable();
        $keys = range(1, count(Storage::$categories));
        $allowedKeys = array_map(fn($key) => (string)$key, $keys);
        $allowedKeys[] = '';
        $input = KeyboardInput::getInputKey($allowedKeys);
        if ($input === 'q') {
            return false;
        }
        if ($input !== '') {
            $categoryIndex = (int)$input - 1;
            $account['category'] = Storage::$categories[$categoryIndex];
        }

        $msg = "---------------------------------------\n";
        $msg .= "Шаг 5. Редактирование ссылки.\n";
        $msg .= "Текущее значение: " . $account['url'] . "\n";
        $msg .= "Нажмите 'Enter' что бы сохранить его или введите новое значение': \n";
        echo $msg;
        $url = $this->getFieldValue('url', true);
        if ($url === 'q') {
            return false;
        }
        if ($url !== '') {
            $account['url'] = $url;
        }

        Storage::$accounts[$index] = $account;

        return true;
    }


    /**
     *
     *
     * @param string $categoryName
     * @return bool
     */
    public function deleteAccountsWithCategory(string $categoryName): bool
    {
        if (!count(Storage::$accounts)) {
            return false;
        }
        $accounts = array_filter(Storage::$accounts, fn($account) => $account['category'] !== $categoryName);
        Storage::$accounts = array_combine(range(0, count($accounts) - 1), $accounts);
        return true;
    }


    /**
     * Сортировка аккаунтов по полям
     *
     * @return bool
     */
    public function sortAccounts(): bool
    {
        if(!count(Storage::$accounts)){
            echo "Нет аккаунтов для сортировки. \n";
            return false;
        }
        $input = $this->menuController->accountsSortingMenu();
        if($input->getCommand() === UserCommands::Back){
            return false;
        }
        $columnName = $input->getProperty('column');
        usort(Storage::$accounts, fn($item1,$item2) => strcmp($item1[$columnName], $item2[$columnName]));
        View::showAccountsTable();

        return true;
    }


    /**
     * Поиск аккаунта
     *
     * @return bool
     */
    public function findAccount(): bool
    {
        if(!count(Storage::$accounts)){
            echo "Нет аккаунтов для поиска. \n";
            return false;
        }
        $input = $this->menuController->accountFindMenu();
        if($input->getCommand() === UserCommands::Back){
            return false;
        }

        $columnName = $input->getProperty('column');
        $valueToFind = $input->getProperty('value');

        $foundAccounts = array_filter(Storage::$accounts, fn($account) => str_contains($account[$columnName], $valueToFind));

        $msg = "По запросу '$valueToFind' в поле {$columnName} найдены аккаунты:\n";
        $msg .= $this->returnMsg;
        echo $msg;
        View::showFoundAccountsTable($foundAccounts);
        $input = KeyboardInput::getInputKey(['q']);
        if ($input === 'q') {
            return false;
        }
        return true;
    }


    public function generatePassword(): string
    {
        $chars =  str_replace(' ', '', $_ENV['CHARS_SET']);
        $arr = str_split($chars);
        shuffle($arr);
        return implode('', array_slice($arr,0,10));
    }
}