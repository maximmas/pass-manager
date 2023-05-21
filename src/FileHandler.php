<?php

namespace PassManager;

class FileHandler
{

    /**
     * Имя директории, в которой хранятся файлы данных
     *
     * @var string|mixed
     */
    private string $directoryName;

    public function __construct()
    {
        $this->directoryName = $_ENV['DATA_DIRNAME'] ?? 'data';
    }


    /**
     * Возвращает имя первого файла в директории
     *
     * @return false|string
     */
    public function getFileName(): false|string
    {
        $allDirItems = scandir($this->directoryName);
        $fileNames = $allDirItems ? array_diff($allDirItems, ['..', '.']) : [];
        $filesWithPath = array_map(fn($file) => $this->directoryName . '/' . $file, $fileNames);
        return count($filesWithPath) ? reset($filesWithPath) : false;
    }


    /**
     * Возвращает содержимое файла в виде массива
     *
     * @param string $fileName имя файла данных
     * @return array
     */
    public function getFileContent(string $fileName): array
    {
        $content = [];
        $fp = fopen($fileName, "r");
        if ($fp) {
            while (($buffer = fgets($fp)) !== false) {
                $content[] = $buffer;
            }
            if (!feof($fp)) {
                echo "Ошибка чтения файла.\n";
            }
            fclose($fp);
        }
        return $content;
    }


    /**
     * Создает новый файл с заданным именем
     *
     * @param string $fileName
     * @return bool
     */
    public function createNewFile(string $fileName): bool
    {
        try {
            $handler = fopen($fileName, 'w+');
        } catch (\Exception $e) {
            echo("Ошибка создания файла: " . $e->getMessage());
            $handler = false;
        }
        return (bool)$handler;
    }


    /**
     * Запись данных в файл
     *
     * @return bool
     */
    public static function saveData(string $fileName, Encryptor $encryptor): bool
    {
        // стираем содержимое файла
        $handle = fopen($fileName, 'w+');
        fclose($handle);

        // массив строк файла
        $fileData = [];

        $timestamp = (string)time() . $_ENV['TIME_HASH'];

        // категории записываем в первую строку
        if (count(Storage::$categories)) {
            $fileData[] = $encryptor->encrypt($timestamp . serialize(Storage::$categories));
            $timestamp = '';
        }

        if (count(Storage::$accounts)) {
            foreach (Storage::$accounts as $key => $account) {
                if($key === 0) {
                    $str = $timestamp ? $timestamp . serialize($account) : serialize($account);
                    $fileData[] = $encryptor->encrypt($str);
                }
                $fileData[] = $encryptor->encrypt(serialize($account));
            }
        }

        if (!$fileData) {
            return false;
        }
        $result = true;

        try {
            $handle = fopen($fileName, 'w+');
            foreach ($fileData as $string) {
                $result = (bool)fwrite($handle, $string . PHP_EOL);
            }
            fclose($handle);
        } catch (\Exception $e) {
            $result = false;
            echo "Ошибка сохранения: " . $e->getMessage();
        }

        return $result;
    }
}