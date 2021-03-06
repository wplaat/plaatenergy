#!/usr/bin/python

# 
#  ===========
#  PlaatEnergy
#  ===========
#
#  Created by wplaat
#
#  For more information visit the following website.
#  Website : www.plaatsoft.nl 
#
#  Or send an email to the following address.
#  Email   : info@plaatsoft.nl
#
#  All copyrights reserved (c) 2008-2016 PlaatSoft
#

import sys
import os
import stat
import serial
import datetime
import locale
import time
import csv 
import _mysql
from time import strftime

ser          = serial.Serial()
ser.baudrate = 115200
ser.bytesize = serial.EIGHTBITS
ser.parity   = serial.PARITY_NONE
ser.stopbits = serial.STOPBITS_ONE
ser.xonxoff  = 1
ser.rtscts   = 0

ser.port     = "/dev/ttyUSB0"
ser.timeout  = 20

# ---------------------------------
# DO NOT CHANGE ANYTHING BELOW HERE
# ---------------------------------

p1_telegram  = False
p1_log       = True

lines = [line.rstrip('\n') for line in open('/var/www/html/plaatenergy/config.php')]
for line in lines:
   if line[:1]=='$':
     line = line.replace(' ','');
     line = line.replace(';','');
     line = line.replace('$','');
     line = line.replace('"','');
     key = line.split('=')
     if key[0]=='dbhost':
        dbhost=key[1]
     if key[0]=='dbname':
        dbname=key[1]
     if key[0]=='dbuser':
        dbuser=key[1]
     if key[0]=='dbpass':
        dbpass=key[1]

con = _mysql.connect(dbhost, dbname, dbuser, dbpass)

sql = "select value from config where token='energy_meter_present'"
con.query(sql)
result = con.use_result()
value = result.fetch_row()[0]
con.close

if value[0] == 'true':

  stack=[]

  #Open COM port
  try:
    ser.open()
  except:
    sys.exit ("Error during opening serial port %s"  % ser.name)      

  line = 0 
  while p1_log:
    p1_line = ''
    try:
        p1_raw = ser.readline()
    except:
        sys.exit ("Error reading from serial port %s" % ser.name )
        ser.close()

    p1_str  = p1_raw
    p1_str  = str(p1_raw)
    p1_line = p1_str.strip()
    stack.append(p1_line);
    line = line + 1

    if p1_line[0:1] == "/":
        p1_telegram = True
    elif p1_line[0:1] == "!":
        if p1_telegram:
            p1_log      = False	

  #Close port and show status
  try:
    ser.close()
  except:
    sys.exit ("Error closing serial port %s" % ser.name )      

  stack_teller=0
  while stack_teller < len(stack):
   if stack[stack_teller][0:9] == "1-0:1.8.1":
      low_used = float(stack[stack_teller][10:20])
   elif stack[stack_teller][0:9] == "1-0:1.8.2":
      normal_used = float(stack[stack_teller][10:20])
   elif stack[stack_teller][0:9] == "1-0:2.8.1":
      low_delivered = float(stack[stack_teller][10:20])
   elif stack[stack_teller][0:9] == "1-0:2.8.2":
      normal_delivered = float(stack[stack_teller][10:20])
   elif stack[stack_teller][0:9] == "1-0:1.7.0":
      power = int(float(stack[stack_teller][10:16])*1000)
   elif stack[stack_teller][0:9] == "1-0:2.7.0":
      power_delivered = int(float(stack[stack_teller][10:16])*1000)
      if power_delivered > 0:
         power = power_delivered * -1		
   elif stack[stack_teller][0:10] == "0-1:24.2.1":
      gas = float(stack[stack_teller][26:35])
   stack_teller = stack_teller + 1

  con = _mysql.connect(dbhost, dbname, dbuser, dbpass)

  sql = "insert into energy1 ( timestamp, low_used, normal_used, low_delivered, normal_delivered, power, gas_used) values (str_to_date('{0}','%d-%m-%Y %H:%i:%s'),{1},{2},{3},{4},{5},{6})".format( strftime('%d-%m-%Y %H:%M:00', time.localtime()), low_used, normal_used, low_delivered, normal_delivered, power, gas)

  con.query(sql)

#
# ---------------------
# THE END
# ---------------------
#
