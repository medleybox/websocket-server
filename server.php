<?php

namespace App;

require __DIR__.'/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\{WsConnection, WsServer};
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use SplObjectStorage;

class WebsocketServer implements MessageComponentInterface
{
    /**
     * @var \SplObjectStorage
     */
    private SplObjectStorage $clients;

    public function __construct()
    {
        $this->clients = new SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn): void
    {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg): void
    {
        // Don't log output if ping message
        if ('ping' !== $msg) {
            /**
             * @var \Ratchet\WebSocket\WsConnection $from
             */
            $numRecv = count($this->clients) - 1;
            echo sprintf(
                'Sending message "%s" to %d other connection%s' . "\n",
                $msg,
                $numRecv,
                $numRecv == 1 ? '' : 's'
            );
        }

        foreach ($this->clients as $client) {
            if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->sendText($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn): void
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}

$host = '0.0.0.0';
$port = 8089;

echo "host: {$host} \n";
echo "port: {$port} \n";

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new WebsocketServer()
        )
    ),
    $port
);

echo "Initializing websocket server";
$server->run();
