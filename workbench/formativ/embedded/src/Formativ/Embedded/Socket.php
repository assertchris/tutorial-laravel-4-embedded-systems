<?php

namespace Formativ\Embedded;

use Evenement\EventEmitterInterface;
use Exception;
use Ratchet\ConnectionInterface;

class Socket
implements SocketInterface
{
  protected $emitter;

  protected $connection;

  public function getEmitter()
  {
    return $this->emitter;
  }

  public function setEmitter(EventEmitterInterface $emitter)
  {
    $this->emitter = $emitter;
  }

  public function __construct(EventEmitterInterface $emitter)
  {
    $this->emitter = $emitter;
  }

  public function onOpen(ConnectionInterface $connection)
  {
    $this->connection = $connection;
    $this->emitter->emit("open");
  }

  public function onMessage(ConnectionInterface $connection, $message)
  {
    $this->emitter->emit("message", [$message]);
  }

  public function onClose(ConnectionInterface $connection)
  {
    $this->connection = null;
  }

  public function onError(ConnectionInterface $connection, Exception $exception)
  {
    $this->emitter->emit("error", [$exception]);
  }

  public function send($message)
  {
    if ($this->connection)
    {
      $this->connection->send($message);
    }
  }
}