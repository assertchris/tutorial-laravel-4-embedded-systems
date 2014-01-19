#include <Servo.h>
  
String buffer = "";
String parts[3];
Servo servos[13];
int index = 0;
  
void setup()
{
  Serial.begin(9600);
  buffer.reserve(200);
}
  
void loop()
{
   
}
  
void handle()
{ 
  int pin = parts[1].toInt();
  int value = parts[2].toInt();
  
  if (parts[0] == "pinMode")
  {
    if (parts[2] == "output")
    {
      pinMode(pin, OUTPUT);
    }
    
    if (parts[2] == "servo")
    {
      servos[pin].attach(pin);
    }
  }
  
  if (parts[0] == "digitalWrite")
  {
    if (parts[2] == "high")
    {
      digitalWrite(pin, HIGH);
    }
    else
    {
      digitalWrite(pin, LOW);
    }
  }
  
  if (parts[0] == "analogWrite")
  {
    analogWrite(pin, value);
  }
  
  if (parts[0] == "servoWrite")
  {
    servos[pin].write(value);
  }
  
  if (parts[0] == "analogRead")
  {
    value = analogRead(pin);
  }
  
  Serial.print(parts[0] + "," + parts[1] + "," + value + ".\n");
}
   
void serialEvent()
{
  while (Serial.available())
  {
    char in = (char) Serial.read();
    
    if (in == '.' || in == ',')
    {
      parts[index] = String(buffer);
      buffer = "";
      
      index++;
      
      if (index > 2)
      {
        index = 0;
      }
    }
    else
    {
      buffer += in;
    }
    
    if (in == '.')
    {
      index = 0;
      buffer = "";
      handle();
    }
  }
}