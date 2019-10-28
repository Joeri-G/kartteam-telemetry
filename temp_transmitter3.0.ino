#include <OneWire.h>
#include <SPI.h>
#include <RH_RF95.h>

#define RFM95_INT     3  // 
#define RFM95_CS      4  //
#define RFM95_RST     2  // "A"
#define LED           13
#define RF95_FREQ 868.0
RH_RF95 rf95(RFM95_CS, RFM95_INT);

char temperatuur;

OneWire  ds(8);  // on pin 10 (a 4.7K resistor is necessary)

void setup() {
  pinMode(RFM95_RST, OUTPUT);
  digitalWrite(RFM95_RST, HIGH);

  Serial.begin(9600);
  while (!Serial) {
    delay(100);
  }

  Serial.println("Feather LoRa TX Test!");

  // manual reset
  digitalWrite(RFM95_RST, LOW);
  delay(10);
  digitalWrite(RFM95_RST, HIGH);
  delay(10);

  while (!rf95.init()) {
    Serial.println("LoRa radio init failed");
    while (1);
  }
  Serial.println("LoRa radio init OK!");
  if (!rf95.setFrequency(RF95_FREQ)) {
    Serial.println("setFrequency failed");
    while (1);
  }
  rf95.setTxPower(23, false);

}

void loop() {
   delay(10);
   if (rf95.available()) {

    uint8_t buf[RH_RF95_MAX_MESSAGE_LEN];
    uint8_t len = sizeof(buf);
    if (rf95.recv(buf, &len)) {
//      Serial.print("Received: ");
//      Serial.println((char*)buf);

      if (strstr((char *)buf, "kart_calandlyceum")){ // verander dit naar je eigen naam

        byte i;
  byte present = 0;
  byte type_s;
  byte data[12];
  byte addr[8];
  float temperatuur;
  
  if ( !ds.search(addr)) {
//    Serial.println();
    ds.reset_search();
    return;
  }
  if (OneWire::crc8(addr, 7) != addr[7]) {
      Serial.println("CRC is not valid!");
      return;
  }
  
  ds.reset();
  ds.select(addr);
  ds.write(0x44, 1);       
  delay(100);    
  present = ds.reset();
  ds.select(addr);    
  ds.write(0xBE);       
  for ( i = 0; i < 9; i++) {           
    data[i] = ds.read();
  }
 
  int16_t raw = (data[1] << 8) | data[0];
  if (type_s) {
    raw = raw << 3; 
    if (data[7] == 0x10) {
      raw = (raw & 0xFFF0) + 12 - data[6];
    }
  } else {
    byte cfg = (data[4] & 0x60);
    if (cfg == 0x00) raw = raw & ~7;
    else if (cfg == 0x20) raw = raw & ~3;
    else if (cfg == 0x40) raw = raw & ~1;
  }

    temperatuur = (float)raw / 16.0;
    Serial.print("  Temperature = ");
    Serial.print(temperatuur);
    Serial.print(" graden celcius, ");

    char radiopacket[20] = "temperatuur";
    itoa(temperatuur,radiopacket,10);
    Serial.print("Sending "); Serial.println(temperatuur); 
    radiopacket[19] = 0;
    rf95.send((uint8_t *)radiopacket, 20);
    rf95.waitPacketSent();
      }
    }
    } 
  }    

