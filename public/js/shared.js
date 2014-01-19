try {
  if (!WebSocket) {
      console.log("no websocket support");
  } else {
  
    var socket = new WebSocket("ws://127.0.0.1:8081/");
    var sensor = $("[name='sensor']");
    var led = $("[name='led']");
    var x = $("[name='x']");
    var y = $("[name='y']");
  
    socket.addEventListener("open", function (e) {
      socket.send("pinMode,6,output.");
      socket.send("pinMode,3,servo.");
      socket.send("pinMode,5,servo.");
    });
  
    socket.addEventListener("error", function (e) {
      // console.log("error: ", e);
    });
  
    socket.addEventListener("message", function (e) {
  
      var data  = e.data;
      var parts = data.split(",");
  
      if (parts[0] == "analogRead") {
        sensor.val(parseInt(parts[2], 10));
      }
  
    });
  
    window.socket = socket;
  
    led.on("change", function() {
      socket.send("analogWrite,6," + led.val() + ".");
    });
  
    x.on("change", function() {
      socket.send("servoWrite,5," + x.val() + ".");
    });
  
    y.on("change", function() {
      socket.send("analogWrite,3," + y.val() + ".");
    });
  
    setInterval(function() {
      socket.send("analogRead,0,void.");
    }, 1000);
  
  }
} catch (e) {
  // console.log("exception: " + e);
}