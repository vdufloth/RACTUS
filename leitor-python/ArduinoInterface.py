from threading import Thread
import serial
from datetime import datetime
import requests
import json


class SerialReader(Thread):
    def __init__(self):
        Thread.__init__(self)

        try:
            data = json.load(open('config.json'))
            self.ARDUINO_LOCATION = data["arduino_location"]
            self.SERIAL_PORT = data["serial_port"]
            self.POST_URL = data["server_url"]
        except Exception as error:
            print('Error at opening config file:', error)

        try:
            self.ser = serial.Serial(
                port=self.ARDUINO_LOCATION,
                baudrate=self.SERIAL_PORT,
                parity=serial.PARITY_ODD,
                stopbits=serial.STOPBITS_TWO,
                bytesize=serial.SEVENBITS
            )
            self.keepReading = True
        except Exception as error:
            print('Error at conecting to arduino:', error)
            self.keepReading = False

    def run(self):
        print('Starting reading')
        while self.keepReading:
            if self.ser.isOpen():
                lineRead = self.ser.readline().decode("utf-8").split(sep=";")
                time = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
                register = json.dumps({'readtime': time,
                                       'humidity': lineRead[0],
                                       'temperature': lineRead[1],
                                       })
                print('Posting: ', register, 'on:', self.POST_URL)
                try:
                    result = requests.post(self.POST_URL, data=register)
                    print('Post result:', result.text)
                except Exception as error:
                    print('Erro at post:', error)
            else:
                print('Serial dor not open. Time:', datetime.now())

    def stopReading(self):
        print('Stoping reading')
        self.keepReading = False

    def restartReading(self):
        print('Restarting reading')
        self.keepReading = True

    def setReadingInterval(self, seconds):
        print('Changing reading interval on Arduino to', seconds, 'seconds')
        # Pass to arduino
