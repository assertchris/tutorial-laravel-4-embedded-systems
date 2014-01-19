<?php

namespace Formativ\Embedded\Command;

use React;

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

  protected $device;
  
  public function __construct(SocketInterface $socket)
  {
    parent::__construct();
    
    $this->socket = $socket;
    
    $socket->getEmitter()->on("message", function($message)
    { 
      fwrite($this->device, $message);
    
      $data = trim(stream_get_contents($this->device));
      $this->info($data);
    
      $this->socket->send($data);
    });
    
    $socket->getEmitter()->on("error", function($e)
    {
      $this->line("exception: " . $e->getMessage() . ".");
    });
  }
    
  public function fire()
  {
    $this->device = fopen($this->argument("device"), "r+");
    stream_set_blocking($this->device, 0);
    
    $port = (integer) $this->option("port");
    
    $server = IoServer::factory(
      new HttpServer(
        new WsServer(
          $this->socket
        )
      ),
      $port
    );
    
    $this->info("Listening on port " . $port . ".");
    $server->run();
  }
    
  protected function getArguments()
  {
    return [
      [
        "device",
        InputArgument::REQUIRED,
        "Device to use."
      ]
    ];
  }
    
  public function __destruct()
  {
    if (is_resource($this->device)) {
      fclose($this->device); 
    }
  }

  protected function getOptions()
  {
    return [
      [
        "port",
        null,
        InputOption::VALUE_REQUIRED,
        "Port to listen on.",
        8081
      ]
    ];
  }
}