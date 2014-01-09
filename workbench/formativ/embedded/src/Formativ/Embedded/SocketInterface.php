<?php

namespace Formativ\Embedded;

use Evenement\EventEmitterInterface;
use Ratchet\MessageComponentInterface;

interface SocketInterface
extends MessageComponentInterface
{
    public function getEmitter();
    public function setEmitter(EventEmitterInterface $emitter);
}