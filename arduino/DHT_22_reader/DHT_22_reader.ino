#include "DHT.h"

#define DHTPIN 2
#define DHTTYPE DHT22
DHT dht(DHTPIN, DHTTYPE);

int chk;
float hum;
float temp;

void setup()
{
    Serial.begin(9600);
    dht.begin();

}

void loop()
{
    hum = dht.readHumidity();
    temp= dht.readTemperature();
    Serial.print(hum);
    Serial.print(";");
    Serial.print(temp);
    Serial.println(";");
    delay(300000); //Delay 5 min.
}
