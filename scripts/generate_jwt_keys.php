<?php

declare(strict_types=1);

$dir = dirname(__DIR__).'/storage/jwt';

if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
    throw new RuntimeException("Failed to create directory: {$dir}");
}

$configFile = $dir.'/openssl.cnf';
file_put_contents($configFile, "[req]\ndistinguished_name = req_distinguished_name\n[req_distinguished_name]\n");

$previousOpenSSLConf = getenv('OPENSSL_CONF');
putenv('OPENSSL_CONF='.$configFile);

$config = [
    'private_key_bits' => 4096,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
    'config' => $configFile,
];

$privateKey = openssl_pkey_new($config);
if ($privateKey === false) {
    throw new RuntimeException('Failed to generate private key: '.openssl_error_string());
}

$privateExport = '';
if (! openssl_pkey_export($privateKey, $privateExport, null, $config)) {
    throw new RuntimeException('Failed to export private key: '.openssl_error_string());
}

if ($previousOpenSSLConf !== false) {
    putenv('OPENSSL_CONF='.$previousOpenSSLConf);
} else {
    putenv('OPENSSL_CONF');
}

$publicKey = openssl_pkey_get_details($privateKey);
if ($publicKey === false) {
    throw new RuntimeException('Failed to extract public key: '.openssl_error_string());
}

file_put_contents($dir.'/private.pem', $privateExport);
file_put_contents($dir.'/public.pem', $publicKey['key']);

echo "JWT RSA keys generated successfully in {$dir}\n";
