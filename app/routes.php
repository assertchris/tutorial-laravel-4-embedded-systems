<?php

Route::get("/", [
  "as"   => "index/index",
  "uses" => "IndexController@indexAction"
]);