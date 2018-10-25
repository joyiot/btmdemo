#!/usr/bin/python
# coding=utf-8

# PPK JoyIOT DEMO based Bytom Blockchain 
#         PPkPub.org  20181017            
#    Released under the MIT License.   
#
#    获取温湿度传感器示例数据并上传到比原链上
#  
 
import RPi.GPIO as GPIO
import time
import json

import urllib
from urllib import quote
 
sencor_id = 'dht8'   #指定温湿度传感器ID

channel = 26      #引脚号37
data = []      #温湿度值
j = 0        #计数器

print "sencor_id=",sencor_id

#读取设备配置文件
dict_device_setting = json.loads(open('device.json').read())
print dict_device_setting

device_odin = dict_device_setting['device_odin']
device_prvkey_pem = dict_device_setting['prvkey_pem']

sencor_odin =  device_odin+"/"+sencor_id


GPIO.setmode(GPIO.BCM)    #以BCM编码格式
 
time.sleep(1)      #时延一秒
 
GPIO.setup(channel, GPIO.OUT)
 
GPIO.output(channel, GPIO.LOW)
time.sleep(0.02)    #给信号提示传感器开始工作
GPIO.output(channel, GPIO.HIGH)
 
GPIO.setup(channel, GPIO.IN)
 
while GPIO.input(channel) == GPIO.LOW:
  continue
 
while GPIO.input(channel) == GPIO.HIGH:
  continue
 
while j < 40:
  k = 0
  while GPIO.input(channel) == GPIO.LOW:
    continue
  
  while GPIO.input(channel) == GPIO.HIGH:
    k += 1
    if k > 100:
      break
  
  if k < 8:
    data.append(0)
  else:
    data.append(1)
 
  j += 1
 
print "sensor is working."
print data        #输出初始数据高低电平
 
humidity_bit = data[0:8]    #分组
humidity_point_bit = data[8:16]
temperature_bit = data[16:24]
temperature_point_bit = data[24:32]
check_bit = data[32:40]
 
humidity = 0
humidity_point = 0
temperature = 0
temperature_point = 0
check = 0
 
for i in range(8):
  humidity += humidity_bit[i] * 2 ** (7 - i)        #转换成十进制数据
  humidity_point += humidity_point_bit[i] * 2 ** (7 - i)
  temperature += temperature_bit[i] * 2 ** (7 - i)
  temperature_point += temperature_point_bit[i] * 2 ** (7 - i)
  check += check_bit[i] * 2 ** (7 - i)
 
tmp = humidity + humidity_point + temperature + temperature_point    #十进制的数据相加
 
if check == tmp:                #数据校验，相等则为有效数据
  print "temperature : ", temperature, ", humidity : " , humidity

  #按照PTTP数据包格式组织数据并上传到比原链上
  nowtime=int(time.time())
  record_odin=sencor_odin+"/#"+str(nowtime)
  print "New record odin = ",record_odin
  
  str_content="{\"temperature\":"+str(temperature)+",\"humidity\":"+str(humidity)+"}"
  dict_metainfo = { 'content_type' : 'text/json','chunk_index':0, 'chunk_count':1 } 
  
  dict_data =  { 'uri' : record_odin, 'utc' : nowtime, 'status_code' : '200', 'status_detail' : 'OK', 'metainfo': dict_metainfo, 'content': str_content} 
  str_data = json.dumps(dict_data);
  
  from Crypto.PublicKey import RSA
  from Crypto.Signature import PKCS1_v1_5
  from Crypto.Hash import SHA256
  import base64

  priKey = RSA.importKey(device_prvkey_pem)
  signer = PKCS1_v1_5.new(priKey)
  hash_obj = SHA256.new(str_data.encode('utf-8'))
  str_sign = 'SHA256withRSA:'+base64.b64encode(signer.sign(hash_obj))
  
  dict_pttp_data = { 'ver' : 1, 'data' : str_data, 'sign' : str_sign} 
  str_pttp_data = json.dumps(dict_pttp_data)

  print "str_pttp_data=",str_pttp_data

  upload_data_url='http://btmdemo.ppkpub.org/joy/iot/upload_iot.php?iot_pttp_data='+quote(str_pttp_data)
  print(upload_data_url)

  response = urllib.urlopen(upload_data_url)
  str_response=response.read().decode('utf-8')
  print "Response=",str_response
  #dict_repsonse=json.loads(str_response)
else:                    #错误输出错误信息，和校验数据
  print "wrong"
  print "temperature : ", temperature, ", humidity : " , humidity, " check : ", check, " tmp : ", tmp


  
GPIO.cleanup()  
