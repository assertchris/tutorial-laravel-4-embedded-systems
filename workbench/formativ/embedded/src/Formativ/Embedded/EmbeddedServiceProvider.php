<?php

namespace Formativ\Embedded;

use Evenement\EventEmitter;
use Illuminate\Support\ServiceProvider;

class EmbeddedServiceProvider
extends ServiceProvider
{
  protected $defer = true;

  public function register()
  {
    $this->app->bind("formativ.embedded.emitter", function()
    {
      return new EventEmitter();
    });

    $this->app->bind("formativ.embedded.command.serve", function()
    {
      return new Command\Serve(
        $this->app->make("formativ.embedded.socket")
      );
    });

    $this->app->bind("formativ.embedded.socket", function()
    {
      return new Socket(
        $this->app->make("formativ.embedded.emitter")
      );
    });

    $this->commands(
      "formativ.embedded.command.serve"
    );
  }

  public function provides()
  {
    return [
      "formativ.embedded.emitter",
      "formativ.embedded.command.serve",
      "formativ.embedded.socket"
    ];
  }
}