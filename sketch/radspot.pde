/**
 *  RadSpot Sketch v1.0a
 */
#include <NewSoftSerial.h>
#include <TinyGPS.h>

// Define which pins you will use on the Arduino to communicate with your 
// GPS. In this case, the GPS module's TX pin will connect to the 
// Arduino's RXPIN which is pin 3.
#define RXPIN 0
#define TXPIN 1

// Signal pin
#define SIGPIN 2

//Set this value equal to the baud rate of your GPS
#define GPSBAUD 4800

float latitude;
float longitude;
float altitude;
float course;
float speed;

// Keep track of event times
unsigned long time = 0;
unsigned long lasttime = 0;

// Create an instance of the TinyGPS object
TinyGPS gps;

// Initialize the NewSoftSerial library to the pins you defined above
NewSoftSerial uart_gps(RXPIN, TXPIN);

// This is where you declare prototypes for the functions that will be 
// using the TinyGPS library.
void getgps(TinyGPS &gps);

// In the setup function, you need to initialize two serial ports; the 
// standard hardware serial port (Serial()) to communicate with your 
// terminal program an another serial port (NewSoftSerial()) for your 
// GPS.
void setup() {
  Serial.begin(115200);
  uart_gps.begin(GPSBAUD);
  pinMode(SIGPIN, INPUT);
  attachInterrupt(0, event, LOW);
}

// This is the main loop of the code. All it does is check for data on 
// the RX pin of the ardiuno, makes sure the data is valid NMEA sentences, 
// then jumps to the getgps() function.
void loop () {
  while (uart_gps.available()) {
    int c = uart_gps.read();
    if(gps.encode(c)) {
      getgps(gps);
    }
  }
}

/**
 * Process event interrupt
 */
void event () {
  time = millis ();
  if ((time - lasttime) < 2) {
    // In event; No Display
  } 
  else {
    // New event; Display on leading edge. First event is discarded.
    Serial.println (time - lasttime);
    Serial.println (latitude*100000,0); 
    Serial.println (longitude*100000,0);
    Serial.println (altitude*100000,0); 
    Serial.println (course*100000,0);
    Serial.println (speed*100000,0);
  }
  lasttime = time;
}

/**
 * Update GPS
 */
void getgps (TinyGPS &gps) {

  gps.f_get_position (&latitude, &longitude);
  altitude = gps.f_altitude();
  course = gps.f_course();
  speed = gps.f_speed_kmph();

}

