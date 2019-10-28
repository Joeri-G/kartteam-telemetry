#!/bin/bash
if [ "$EUID" -ne 0 ]
  then printf "Please run as root\n"
  exit
fi
printf "Configuring and installing WiFi AP\n\n"
apt install dnsmasq hostapd;
printf "interface wlan0\n\tstatic ip_address=192.168.4.1/24\n\tnohook wpa_supplicant\n" >> /etc/dhcpcd.conf;
systemctl restart dhcpcd;
mv /etc/dnsmasq.conf /etc/dnsmasq.conf.orig;
printf "interface=wlan0      # Use the require wireless interface - usually wlan0\ndhcp-range=192.168.4.2,192.168.4.20,255.255.255.0,24h\n" >> /etc/dnsmasq.conf;
systemctl reload dnsmasq;
read -p "AP name: " apname;


read -sp "AP password: " appassword;
printf "\n";
read -sp "Repeat password: " appassword_repeat;
while [ $appassword != $appassword_repeat ]
do
  printf "\n\tPasswords did not match, please repeat\n\t"
  read -sp "AP password: " appassword;
  printf "\n\t";
  read -sp "Repeat password: " appassword_repeat;
done
printf "interface=wlan0\ndriver=nl80211\nssid=$apname\nhw_mode=g\nchannel=7\nwmm_enabled=0\nmacaddr_acl=0\nauth_algs=1\nignore_broadcast_ssid=0\nwpa=2\nwpa_passphrase=$appassword\nwpa_key_mgmt=WPA-PSK\nwpa_pairwise=TKIP\nrsn_pairwise=CCMP\n" >> /etc/hostapd/hostapd.conf;
printf "DAEMON_CONF=\"/etc/hostapd/hostapd.conf\"\n" >> /etc/default/hostapd;
systemctl unmask hostapd;
systemctl enable hostapd;
systemctl start hostapd;
printf "net.ipv4.ip_forward=1\n" >> /etc/sysctl.conf;
iptables -t nat -A  POSTROUTING -o eth0 -j MASQUERADE;
sh -c "iptables-save > /etc/iptables.ipv4.nat";
sed -i '/exit 0/d' /etc/rc.local;
printf "iptables-restore < /etc/iptables.ipv4.nat\nexit 0\n" >> /etc/rc.local;
