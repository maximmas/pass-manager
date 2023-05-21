<?php

namespace PassManager;

class Manager
{

    private string $dataFileName;
    private string $keyPassword;

    private Encryptor $encryptor;
    private FileHandler $fileHandler;
    private MenuController $menuController;
    private CategoriesController $categoriesController;
    private AccountsController $accountsController;

    public function __construct()
    {
        $this->encryptor = new Encryptor();
        $this->fileHandler = new FileHandler();
        $this->menuController = new MenuController();
        $this->categoriesController = new CategoriesController($this->encryptor, $this->menuController);
        $this->accountsController = new AccountsController($this->encryptor, $this->menuController);

        Storage::$lastTimestamp = 0;
      }


    /**
     * Запуск приложения
     *
     * @return void
     */
    public function run(): void
    {
        $this->requestPassword();
        $this->setupData();
        $this->process();
        exit();
    }


    /**
     * Получение пароля
     *
     * @return void
     */
    private function requestPassword(): void
    {
        $input = $this->menuController->passwordMenu();
        if ($input->getCommand() === UserCommands::ExitOnly) {
            $this->stopApp();
        }
        $this->keyPassword = $input->getProperty('password');
    }


    /**
     * Получение данных из файла
     *
     * @return void
     */
    private function setupData(): void
    {
        $this->encryptor->setPassword($this->keyPassword);

        // получить имя файла
        $this->dataFileName = $this->getDataFileName();

        // получить массив строк файла
        $fileEncryptedContent = $this->fileHandler->getFileContent($this->dataFileName);
        $fileContent = $fileEncryptedContent;

        // достать из содержимого файла массив категорий
        $categories = $this->categoriesController->getSavedCategories($fileContent);
        if ($categories === false) {
            $this->wrongPassword();
        }
        Storage::$categories = $categories;

       $accounts = $this->accountsController->getSavedAccounts($fileContent);
        if ($accounts === false) {
            $this->wrongPassword();
        }
        Storage::$accounts = $accounts;
    }


    /**
     * Основной цикл.
     * Вывод главного меню и таблицы аккаунтов.
     *
     * @return mixed
     */
    private function process(): mixed
    {
        while (true) {


            $this->accountsController->showAccounts();
            $userInput = $this->menuController->mainMenu();

            if ($userInput->getCommand() === UserCommands::FindAccount) {
                $this->accountsController->findAccount();
            }

            if ($userInput->getCommand() === UserCommands::SortAccounts) {
                $this->accountsController->sortAccounts();
            }

            if ($userInput->getCommand() === UserCommands::AddAccount) {
                $newAccount = $this->accountsController->createAccount();
                if ($newAccount) {
                    Storage::$accounts[] = $newAccount;
                }
            }

            if ($userInput->getCommand() === UserCommands::EditAccount) {
                $input = $this->menuController->editAccountMenu();
                if ($input !== 'q') {
                    $accountIndex = --$input;
                    $this->accountsController->editAccount($accountIndex);
                }
            }

            if ($userInput->getCommand() === UserCommands::DeleteAccount) {
                $input = $this->menuController->deleteAccountMenu();
                if ($input !== 'q') {
                    $accountIndex = --$input;
                    $this->accountsController->deleteAccount($accountIndex);
                }
            }

            if ($userInput->getCommand() === UserCommands::AddCategory) {
                $this->categoriesController->addNewCategory();
            }

            if ($userInput->getCommand() === UserCommands::DeleteCategory) {
                $categoryName = $this->categoriesController->deleteCategory();
                if($categoryName){
                    $this->accountsController->deleteAccountsWithCategory($categoryName);
                }
            }

            if ($userInput->getCommand() === UserCommands::SaveAndExit) {
                $this->saveAndExit();
            }

            if ($userInput->getCommand() === UserCommands::ExitOnly) {
                $this->stopApp();
            }
        }
    }


    /**
     * Получаем имя файла данных
     *
     * @return string
     */
    private function getDataFileName(): string
    {
        // имя файла данных
        $name = '';

        // получаем все файлы в папке /data
        $existedFileName = $this->fileHandler->getFileName();

        // выводим разные меню, в зависимости от того, существует уже файл данных или нет
        $userInput = $existedFileName
            ? $this->menuController->existFileMenu($existedFileName)
            : $this->menuController->notExistFileMenu();

        // разбираем ответы меню
        $actionType = $userInput->getCommand();
        $fileName = $userInput->getProperty('fileName');

        // выбрано открыть существующий файл
        if ($actionType === UserCommands::OpenFile) {
            list($isFileValid, $errorMsg) = Validator::isFileAvailable($fileName);
            if ($isFileValid) {
                $name = $fileName;
            } else {
                echo $errorMsg;
                $this->run();
            }
        }

        // выбрано создать новый файл
        if ($actionType === UserCommands::CreateFile) {
            $newName = $this->createNewFile();

            // выбран возврат в предыдущее меню
            if ($newName === false) {
                $this->run();
            }

            list($isFileValid, $errorMsg) = Validator::isFileAvailable($newName);
            if ($isFileValid) {
                $name = $newName;
            } else {
                echo $errorMsg;
                $this->run();
            }
        }

        if ($actionType === UserCommands::ExitOnly) {
            $this->stopApp();
        }

        return $name;
    }


    /**
     * Создание нового файла данных
     * Возвращает строку - имя файла или false - возврат в предыдущее меню
     *
     * @return false|string
     */
    private function createNewFile(): false|string
    {
        // получаем имя нового файла из меню
        $menuResponse = $this->menuController->createNewFileMenu();

        if ($menuResponse->getCommand() === UserCommands::Back) {
            return false;
        }

        $newFileName = $menuResponse->getProperty('fileName');

        // создем файл на диске
        $isNewFileCreated = $this->fileHandler->createNewFile($newFileName);

        if (!$isNewFileCreated) {
            echo "Ошибка: Файл не может быть создан. Попробуйте еще раз. \n";
            echo "---------------------------------------------";
            return false;
        }

        return $newFileName;
    }


    /**
     * Выход из приложения без сохранения данных
     *
     * @return void
     */
    private function stopApp(): void
    {
        die("До свидания!\n");
    }


    /**
     * Сохранение данных и выход из приложения
     *
     * @return void
     */
    private function saveAndExit(): void
    {
        FileHandler::saveData($this->dataFileName, $this->encryptor);
        $this->stopApp();
    }


    private function wrongPassword(){
        echo "Неправильный пароль. Введите новый. \n";
        $this->run();
    }


}