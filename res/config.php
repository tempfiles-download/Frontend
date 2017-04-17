<?php

/* NOTE:
 * For storing database credentials I use system environment variables.
 * If you'd prefer to just store the credentials in this document then,
 * replace my variables definitions with the ones I've commented.
 */

return array(
    # Allowed formats <n>MB, <n>GB, <n>TB, <n>PB.
    'max-file-size' => '2MB',
    #'mysql-url' => 'localhost',
    'mysql-url' => getenv('ag44jc7aqs2rsup2bb6cx7utc'),
    #'mysql-user => 'tempfiles',
    'mysql-user' => getenv('hp7wz20wu4qfbfcmqywfai1j4'),
    #'mysql-password' => 'password',
    'mysql-password' => getenv('mom8c5hrbn8c1r5lro1imfyax'),
    #'mysql-db' => 'tempfiles',
    'mysql-db' => getenv('qb1yi60nrz3tjjjqqb7l2yqra'),
    #'mysql-table' => 'files',
    'mysql-table' => getenv('rb421p9wniz81ttj7bdgrg0ub'),
    # Encryption algorithm to use for encrypting uploads.
    'Encryption-Method' => 'AES-256-CBC',
    # Display version hash.
    'display-git-hash' => true
);
