<?php

namespace PassManager;

class Validator
{

    /**
     * Проверяет по имени файл существует и доступен для чтения и записи
     *
     * @return array [статус проверки(true/false), сообщение об ошибке]
     */
    public static function isFileAvailable(string $fileName): array
    {
        $errorMsg = "";
        if (!$fileName) {
            return [false, "Incorrect file name"];
        }
        if (!file_exists($fileName)) {
            return [false, "File doesn't exists"];
        }
        if (!is_readable($fileName)) {
            $errorMsg = "File not readable";
        }
        if (!is_writable($fileName)) {
            $errorMsg = $errorMsg ? $errorMsg . " and writable" : "File not writable";
        }
        $errorMsg = $errorMsg ? "File error: {$errorMsg}. Try again.\n" : "";

        return $errorMsg ? [false, $errorMsg] : [true, $errorMsg];
    }


    /**
     * может ли введенная строка быть использованана как имя файла
     *
     * @param string $name
     * @return bool
     */
    public static function newFileNameValidate(string $name): bool
    {
        $isValid = true;
        if (!$name) {
            return false;
        }
//        $allowedChars = "_-@&*./ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
        $allowedChars = $_ENV['CHARS_SET'];
        foreach (str_split($name) as $char) {
            if (!strpos($allowedChars, $char)) {
                $isValid = false;
                break;
            }
        }
        return $isValid;
    }


    /**
     * может ли введенная строка быть использованана как имя файла
     *
     * @param string $name
     * @return bool
     */
    public static function newCategoryNameValidate(string $name): bool
    {
        $isValid = true;
        if (!$name) {
            return false;
        }
//        $allowedChars = "_-@&*.ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
        $allowedChars = $_ENV['CHARS_SET'];
        foreach (str_split($name) as $char) {
            if (!strpos($allowedChars, $char)) {
                $isValid = false;
                break;
            }
        }
        return $isValid;
    }


    /**
     * может ли введенная строка быть использованана как имя аккаунта
     *
     * @param string $name
     * @return bool
     */
    public static function accountFieldValidate(string $name): bool
    {
        $isValid = true;
        if (!$name) {
            return false;
        }
        $allowedChars = $_ENV['CHARS_SET'];
        foreach (str_split($name) as $char) {
            if (strpos($allowedChars, $char) === false) {
                $isValid = false;
                break;
            }
        }
        return $isValid;
    }


    /**
     *  Проверка пароля и текста для шифрования
     *
     * @param string $string
     * @return bool
     */
    public static function validateEncryptedString(string $string): bool
    {
        $isValid = true;
        if (!$string) {
            return false;
        }
        $allowedChars = $_ENV['CHARS_SET'];
        foreach (str_split($string) as $char) {
            if (!str_contains($allowedChars, $char)) {
                $isValid = false;
                break;
            }
        }
        return $isValid;
    }

}