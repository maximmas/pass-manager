<?php

namespace PassManager;

/**
 *  Класс для шифрования строк методом Виженера
 *
 */
class Encryptor
{


    /**
     * Набор символов, которые могут быть в пароле. Каждый символ уникальный.
     *
     * @var string
     */
    private string $charSet;

    /**
     * Массив символов, которые могут быть в пароле. Каждый символ уникальный.
     *
     * @var array
     */
    private array $charSetArr;

    /**
     * Пароль - ключ для шифрования
     *
     * @var string
     */
    private string $keyPassword;

    private array $charsShift;

    public function __construct()
    {
        //$base = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890!@%&{}?.,[]-_)($;:*"=/~';
        //$this->charSet = "abcdefghijklmnopqrstuvwxyz";
//        self::$charSet = ' "=/WZ,sjMzEJ-m3:9rOfd;4}~(TRHp!lx2qvk$Q.bALKGUF[7CXyI_{aDnB@tc?Ng106P&Y]5VS%i)wh*ou8e';

        $this->charSet = $_ENV['CHARS_SET'];
        $this->charSetArr = str_split($this->charSet);
    }



    public function setPassword(string $password): void
    {
        $this->keyPassword = $password;
        $passwordArr = str_split($this->keyPassword);
        $this->charsShift = array_map(fn($char) => strpos($this->charSet, $char), $passwordArr);
    }


    /**
     * Шифрование строки
     *
     * @param string $text Строка для шифрования
     */
    public function encrypt(string $text): false|string
    {

        $textValidation = Validator::validateEncryptedString($text);
        if($textValidation !== true){
            echo "Error. String can't be encrypted: {$textValidation}\n";
            return false;
        }

        // преобразуем входящие строки в массивы
        $textArr = str_split($text);
        $shifts = $this->charsShift;

        // перебираем символы строки для шифрования
        $shiftPosition = 0; // индекс сдвига для текущего символа, инкрементируется на каждом симовле строки

        for ($k = 0; $k < count($textArr); $k++) {
            $char = $textArr[$k];

            // проверяем индекс текущего сдвига
            // шфируемая строка может быть длиннее ключа, в этом случае обнуляем, т.е. проходим по ключу с начала
            $shiftPosition = ($shiftPosition > array_key_last($shifts)) ? 0 : $shiftPosition;

            // определяем значение текущего сдвига
            $charShiftValue = $shifts[$shiftPosition];

            // находим позицию текущего символа
            $charPosition = strpos($this->charSet, $char);

            // находим позиция нового символа - сдвиг вправо на величину сдвиго
            $newCharPosition = $charPosition + $charShiftValue;

            // если новая позиция (индекс) выходит справа за пределы набора символов - остаток добираем с начала набора
            if ($newCharPosition > array_key_last($this->charSetArr)) {
                $newCharPosition = $newCharPosition - count($this->charSetArr);
            }

            // находим новый символ и добавляем в массив
            $encodedStringArr[] = $this->charSetArr[$newCharPosition];

            // инкрементируем индекс сдвига
            $shiftPosition++;
        }

        // собираем массив в строку
        return implode('', $encodedStringArr);
    }


    /**
     * Расшифровка строки
     *
     * @param string $text Строка для расшифровки
     */
    public function decrypt(string $text): string
    {

        $textArr = str_split($text);
        $encodedStringArr = [];
        $shifts = $this->charsShift;

        $shiftPosition = 0;
        for ($k = 0; $k < count($textArr); $k++) {
            $char = $textArr[$k];
            $shiftPosition = ($shiftPosition > array_key_last($shifts)) ? 0 : $shiftPosition;
            $charPosition = strpos($this->charSet, $char);
            $charShiftValue = $shifts[$shiftPosition];

            // позиция нового символа - сдвиг влево
            $newCharPosition = $charPosition - $charShiftValue;

            // если новая позиция (индекс) выходит за пределы набора символов слева - остаток добираем с конца
            if ($newCharPosition < array_key_first($this->charSetArr)) {
                // "+" - потому что $newCharPosition отрицательный
                $newCharPosition = count($this->charSetArr) + $newCharPosition;
            }
            $encodedStringArr[] = $this->charSetArr[$newCharPosition];
            $shiftPosition++;
        }

        return implode('', $encodedStringArr);
    }


//
//    /**
//     * собираем массив значений сдвигов символов ключа (пароля)
//     *
//     * @param array $passwordArr
//     * @return array
//     */

//    private function getShifts(array $passwordArr): array
//    {
//        return array_map(fn($char) => strpos(self::$charSet, $char), $passwordArr);
//    }

}