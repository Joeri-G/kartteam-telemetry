#!/usr/bin/env python3
#IMPORTANT
#remove the serial library and install the pySerial library or it WILL mess up everything.
#   pip3 uninstall serial; pip3 install pyserial
import serial, time, datetime;

#fix log files
date_time = str(datetime.datetime.now())[:-7];

date_time = date_time.replace(' ', '_')

snapshotfile = "snapshot_" + date_time + ".snapshot";
open('snapshots/snapshot_newest.snapshot', 'w').write('');



try:
    #with serial.Serial('/dev/ttyACM0') as ser:
    while True:
        for x in range(10):
            #write in /run because that file-system live in RAM
            #this reduces response time and drive wear
            # data = ser.readline().decode('utf-8');
            #testdata
            data = '{\n"sens_temp_1": {"value": ["666", "420"], "average": true},\n"tire_temp_1": {"value": ["71", "58", "90", "81"], "average": true},\n"speed": {"value": "70", "average": false},\n"battery_1": {"value": ["34", "3", "45", "76", "78"], "average": false},\n"battery_2": {"value": ["223", "5", "23", "67", "45"], "average": false},\n"something": {"value": "5", "average": false}\n}';
            print('\n--------------\n'+data.replace('\n', '').replace('\t', '').replace(' ', ''));
            f = open('/run/com-data.json', 'w');
            f.write(data);
            f.close();
            time.sleep(0.5);
        #log snapshot
        data_oneline = data.replace('\n', '').replace('\t', '').replace(' ', '');
        entry = '\n--------------\n'+str(datetime.datetime.now())[:-7] + " = " + data_oneline + "\n";
        print('\n\n\tLogging%s' % entry);
        with open('snapshots/snapshot_newest.snapshot', 'a') as g:
            g.write(entry);
        with open('snapshots/'+snapshotfile, 'a') as g:
            g.write(entry);

except Exception as e:
    entry = "===ERROR===\n" + e;
    print('Logging %s' % entry);
    with open('snapshots/snapshot_newest.snapshot', 'a') as g:
        g.write(entry);
    with open('snapshots/'+snapshotfile, 'a') as g:
        g.write(entry);
