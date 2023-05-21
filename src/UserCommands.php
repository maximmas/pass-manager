<?php

namespace PassManager;

/**
 * Команды пользователя, которые возвращаются из меню в менеджер процесса
 *
 */
enum UserCommands
{
    case OpenFile;
    case CreateFile;
    case FindAccount;
    case SortAccounts;
    case AddAccount;
    case AddCategory;
    case EditAccount;
    case DeleteAccount;
    case DeleteCategory;
    case ExitOnly;
    case SaveAndExit;
    case Back; // выход в предыдущее меню
    case InputPassword;
}