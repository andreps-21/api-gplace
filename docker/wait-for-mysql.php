#!/usr/bin/env php
<?php
/**
 * Aguarda o MySQL ficar aceitando conexões (uso no container Docker).
 * Uso: php docker/wait-for-mysql.php [host] [port] [max_tries]
 */
$host = $argv[1] ?? (getenv('DB_HOST') ?: 'mysql');
$port = (int) ($argv[2] ?? (getenv('DB_PORT') ?: 3306));
$maxTries = (int) ($argv[3] ?? 60);
$tries = 0;

echo "Aguardando MySQL em {$host}:{$port}...\n";

while ($tries < $maxTries) {
    $errno = 0;
    $errstr = '';
    $fp = @fsockopen($host, $port, $errno, $errstr, 2);
    if ($fp) {
        fclose($fp);
        echo "MySQL disponível.\n";
        exit(0);
    }
    $tries++;
    if ($tries % 5 === 0) {
        echo "Tentativa {$tries}/{$maxTries}...\n";
    }
    sleep(1);
}

echo "Timeout: MySQL não respondeu em {$host}:{$port}\n";
exit(1);
