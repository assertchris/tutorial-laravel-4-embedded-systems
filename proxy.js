var firmata   = require("firmata");
var memcached = require("memcached");

var Cache = {
  "connect" : function() {
    Cache.instance = new memcached("127.0.0.1:11211");
  },
  "get" : function(key, callback) {
    Cache.instance.get(key, function(error, value) {
      callback(error, JSON.parse(value || "null"));
    });
  },
  "put" : function(key, value, minutes, callback) {
    callback = callback || function(){};
    Cache.instance.set(key, JSON.stringify(value), minutes * 60, callback);
  }
};

var pinModes;
var analog = [];

var tick = function() {

  var out = [];

  Cache.get("to-proxy", function(error, data) {

    if (data == null) {
      return;
    }

    for (var key in data) {
      if (data.hasOwnProperty(key)) {

        var parts = data[key].split(",");

        if (parts[0] == "pinMode") {
          board.pinMode(parts[1], pinModes[parts[2]]);
          out[out.length] = "pinMode," + parts[1] + "," + parts[2] + ",ok";
        } 

        if (parts[0] == "analogWrite") {
          board.analogWrite(parts[1], parts[2]);
          out[out.length] = "analogWrite," + parts[1] + "," + parts[2] + ",ok";
        } 

        if (parts[0] == "servoWrite") {
          board.servoWrite(parts[1], parts[2]);
          out[out.length] = "servoWrite," + parts[1] + "," + parts[2] + ",ok";
        } 

        if (parts[0] == "analogRead") {
          out[out.length] = "analogRead," + parts[1] + "," + analog[parts[1]];
        } 

      }
    }

    Cache.put("to-proxy", [], 999);
    Cache.put("from-proxy", out, 999);

    setTimeout(tick, 250);

  });

};

var board = new firmata.Board("/dev/tty.usbmodem1411",function() {

  pinModes = {
    "pwm"   : board.MODES.PWM,
    "servo" : board.MODES.SERVO
  };

  for (var i = 0; i < 6; i++) {
    (function(j){
      board.analogRead(j, function(value) {
        analog[j] = value;
      });
    })(i);
  } 

  Cache.connect();
  setTimeout(tick, 250);

  Cache.put("to-proxy", null);

});