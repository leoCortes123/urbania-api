<?php

declare(strict_types=1);

$phpBinary = PHP_BINARY;
$phpDir = dirname($phpBinary);
$extDir = $phpDir.DIRECTORY_SEPARATOR.'ext';
$dllPath = $extDir.DIRECTORY_SEPARATOR.'php_xdebug.dll';
$iniPath = php_ini_loaded_file();

$url = 'https://xdebug.org/files/php_xdebug-3.5.3-8.5-nts-vs17-x86_64.dll';

if (! is_dir($extDir)) {
    mkdir($extDir, 0755, true);
}

if (is_file($dllPath)) {
    echo "Xdebug DLL already exists at {$dllPath}\n";
} else {
    echo "Downloading Xdebug DLL from {$url}...\n";
    $data = file_get_contents($url);
    if ($data === false || strlen($data) < 1024) {
        fwrite(STDERR, "Failed to download Xdebug DLL\n");
        exit(1);
    }
    file_put_contents($dllPath, $data);
    echo 'Saved Xdebug DLL ('.strlen($data)." bytes)\n";
}

if ($iniPath === false) {
    fwrite(STDERR, "No loaded php.ini found\n");
    exit(1);
}

$iniContent = file_get_contents($iniPath);
$extensionLine = 'zend_extension='.$dllPath;

if (str_contains($iniContent, 'php_xdebug.dll')) {
    echo "php.ini already references xdebug.dll\n";
} else {
    $lines = "\n; Xdebug installed by Urbania Session 8 coverage setup\n";
    $lines .= "zend_extension={$dllPath}\n";
    $lines .= "xdebug.mode=coverage\n";
    file_put_contents($iniPath, $lines, FILE_APPEND | LOCK_EX);
    echo "Updated {$iniPath}\n";
}

echo "Done. Verify with: php --ri xdebug\n";
