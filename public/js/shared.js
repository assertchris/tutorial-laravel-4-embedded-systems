try {
  if (!WebSocket) {
      console.log("no websocket support");
  } else {

    var socket = new WebSocket("ws://127.0.0.1:8081/");

    socket.addEventListener("open", function (e) {
      socket.send("pinMode,6,pwm");
      socket.send("pinMode,3,servo");
      socket.send("pinMode,5,servo");
    });

    socket.addEventListener("error", function (e) {
      // console.log("error: ", e);
    });

    socket.addEventListener("message", function (e) {

      var data  = e.data;
      var parts = data.split(",");

      if (parts[0] == "analogRead") {
        $("[name='sensor']").val(parts[2]);
      }

    });

    // console.log("socket:", socket);

    window.socket = socket;

    $("[name='led']").on("change", function() {
      socket.send("analogWrite,6," + $(this).val());
    });

    $("[name='x']").on("change", function() {
      socket.send("servoWrite,5," + $(this).val());
    });

    $("[name='y']").on("change", function() {
      socket.send("analogWrite,3," + $(this).val());
    });

    setInterval(function() {
      socket.send("analogRead,0");
    }, 1000);

    setInterval(function() {
      $("[name='photo']").attr("src", "[path to photo]");
    }, 1000);

  }
} catch (e) {
  // console.log("exception: " + e);
}