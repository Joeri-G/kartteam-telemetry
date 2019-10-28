#this file will disscect the snapshot files in the `snapshts` folder
import os
while True:
    print('please select one of the following files');
    files = os.listdir('snapshots');
    for x in files:
        print(x, end = '\t')
    print('\n\tselect file');

    file = input('Filename: ').replace('/', '');

    try:
        f = open('snapshots/'+file, 'r');
        break;
    except Exception as e:
        print('\n\nThat file does not exist...')
data = f.read();
f.close();
lines = data.split('\n');

data = []

for x in lines:
    if '=' in x:
        data.append(x[22:]);

file = "digested_"+file+".json"
#empty file in case it exists

f = open('snapshots/'+file, 'w');
f.write('');
f.close();

with open('snapshots/'+file, 'a') as f:
    f.write('{"digested_data": [\n')
    f.write('{"Data_Processor_By": "Joeri Geuzinge"}')
    for x in data:
        f.write(',\n'+x);
    f.write(']}')
print('done, stored in snapshts/'+file)
