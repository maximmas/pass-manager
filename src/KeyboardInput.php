<?php

namespace PassManager;


/**
 *  Class for getting keyboard input
 *
 */
class KeyboardInput
{

    /**
     * Keyboard input listener
     *
     * @param array $expectedKeys Array of string values with expected keys
     * @return string
     */
    public static function getInputKey(array $expectedKeys): string
    {
        echo "Введите значение:\n";
        $res = fopen("php://stdin", "r");
        do {
            echo "> ";
            $input = rtrim(fgets($res));
            $isUserInputListen = !in_array($input, $expectedKeys, true);
            if (in_array($input, $expectedKeys, true)) {
                $isUserInputListen = false;
            } else {
                echo "Неправильное значение, попробуйте еще раз \n";
            }
        } while ($isUserInputListen);
        fclose($res);
        return $input;
    }


    /**
     * Ввод произвольной строки
     *
     * @return string
     */
    public static function getInputString(): string
    {
        $res = fopen("php://stdin", "r");
        $input = rtrim(fgets($res));
        fclose($res);
        return $input;
    }
}