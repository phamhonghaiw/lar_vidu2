<?php

// Validate with the same user that runs migrations; never print certificate contents.
$path = getenv('MYSQL_ATTR_SSL_CA');

if (! $path || ! is_file($path) || ! is_readable($path)) {
    fwrite(STDERR, "MySQL CA certificate is not readable by the application user.\n");
    exit(1);
}

$contents = file_get_contents($path);
if ($contents === false || ! str_contains($contents, '-----BEGIN CERTIFICATE-----')
    || @openssl_x509_read($contents) === false) {
    fwrite(STDERR, "Invalid MySQL CA certificate. Paste the complete PEM contents from Aiven into Render Secret File ca.pem.\n");
    exit(1);
}

fwrite(STDOUT, "MySQL CA certificate is readable and valid PEM.\n");
