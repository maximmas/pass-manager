<?php

require_once 'vendor/autoload.php';

\Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();

(new \PassManager\Manager())->run();



/***********************************************************/
//$password = 'Somebody';
//$text = 'Vivamus suscipit tortor eget felis porttitor volutpat.';
//$coder = new \PassManager\Encryptor();
//echo "Исходная строка: {$text}\n";
//$encrypted = $coder->encrypt($password, $text);
//if($encrypted){
//    echo "Зашифрованная строка: {$encrypted}\n";
//    $decrypted = $coder->decrypt($password, $encrypted);
//    echo "Расшифрованная строка: {$decrypted}\n";
//}



