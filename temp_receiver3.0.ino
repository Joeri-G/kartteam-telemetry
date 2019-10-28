#include <SPI.h>
#include <RH_RF95.h>

#define RFM95_CS      8
#define RFM95_IRQ     7
#define RFM95_RST     4
#define RFM95_INT     7
#define RF95_FREQ 868.0
RH_RF95 rf95(RFM95_CS, RFM95_INT);
#define LED 13

int16_t packetnum = 0;
int tel=1;
void setup() {
pinMode(LED, OUTPUT);     
  pinMode(RFM95_RST, OUTPUT);
  digitalWrite(RFM95_RST, HIGH);

  while (!Serial);
  Serial.begin(9600);
  delay(100);

  Serial.println("Feather LoRa RX KART TELEMENTRIE!");
  
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
    while (1);
  }
  rf95.setTxPower(23, false);
}

void loop() {
      delay(500);
      char radiopacket[20] = "kart_calandlyceum #"; // verander dit naar je eigen naam
      itoa(packetnum++, radiopacket+17, 10);
      radiopacket[19] = 0;
      rf95.send((uint8_t *)radiopacket, 20);
      rf95.waitPacketSent();
      
      uint8_t buf[RH_RF95_MAX_MESSAGE_LEN];
      uint8_t len = sizeof(buf);
      if (rf95.waitAvailableTimeout(500))  {        
        if (rf95.recv(buf, &len)) {
        Serial.print((char*)buf);
        Serial.println(" graden celsius");
        }       
          tel++;
          if (tel == 4) {
            tel=1;  
            Serial.println(""); }
          }
            
}
