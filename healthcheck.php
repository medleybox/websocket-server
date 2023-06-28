<?php

namespace App;

require __DIR__.'/vendor/autoload.php';

\Ratchet\Client\connect('ws://localhost:8089')->then(function($conn) {
   $conn->send('ping');
   $conn->close();
}, function ($e) {
    echo "Could not connect:\n{$e->getMessage()}\n";
    exit(1);
});