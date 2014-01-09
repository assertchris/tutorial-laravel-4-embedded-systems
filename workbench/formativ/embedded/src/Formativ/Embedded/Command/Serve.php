<?php

namespace Formativ\Embedded\Command;

use Cache;
use Formativ\Embedded\SocketInterface;
use Illuminate\Console\Command;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Serve
extends Command
{
  protected $name = "embedded:serve";

  protected $description = "Creates a firmata socket server.";

  public function __construct(SocketInterface $socket)
  {
    parent::__construct();

    $this->socket = $socket;

    $this->socket->getEmitter()->on("message", function($message) {

      $this->info("to proxy: " . $message . ".");

      $toProxy = Cache::get("to-proxy");

      if (!is_array($toProxy))
      {
        $toProxy = [];
      }

      $toProxy[] = $message;

      Cache::put("to-proxy", json_encode($toProxy), 999);

    });

    $this->socket->getEmitter()->on("error", function($exception) {
      $this->line("exception: " . $exception->getMessage() . ".");
    });
  }

  public function fire()
  {
    $port = (integer) $this->option("port");

    $server = IoServer::factory(
      new HttpServer(
        new WsServer(
          $this->socket
        )
      ),
      $port
    );

    $tick = function() use (&$server, &$tick) {

      $fromProxy = json_decode(Cache::get("from-proxy", "null"));

      if (!is_array($fromProxy))
      {
        $fromProxy = [];
      }
      
      foreach ($fromProxy as $message)
      {
        $this->info("from proxy: " . $message . ".");

        $this->socket->send($message);
      }

      Cache::put("from-proxy", json_encode([]), 999);

      $server->loop->addTimer(250 / 1000, $tick);

    };

    $server->loop->addTimer(250 / 1000, $tick);

    $this->info("Listening on port " . $port . ".");
    $server->run();
  }

  protected function getOptions()
  {
    return [
      ["port", null, InputOption::VALUE_REQUIRED, "Port to listen on.", 8081]
    ];
  }
}