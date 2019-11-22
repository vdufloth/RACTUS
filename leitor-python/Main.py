#!/usr/bin/python3
from ArduinoInterface import SerialReader
from time import sleep
import sys

sr = SerialReader()
try:
    arg1 = sys.argv[1]
except:
    arg1 = 'start'

if (arg1 == 'start'):
    if (sr.isAlive()):
        sr.restartReading()
    else:
        sr = SerialReader()
        sr.start()

elif (arg1 == 'stop'):
    if (sr.isAlive()):
        sr.stopReading()
elif (arg1 == 'time'):
    try:
        arg2 = sys.argv[2]
    except:
        arg2 = 2
        print('No time parameter. Using default, 2 seconds')
    if (sr.isAlive):
        sr.setReadingInterval(arg2)
