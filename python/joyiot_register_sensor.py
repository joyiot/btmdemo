#!/usr/bin/env python
# -*- coding: utf-8 -*-
# PPK JoyIOT DEMO based Bytom Blockchain 
#         PPkPub.org  20181017            
#    Released under the MIT License.   
#
#    注册新的传感器标识
#  

import time
import argparse

import urllib
from urllib import quote

import json
import base64

#读取设备配置文件
dict_device_setting = json.loads(open('device.json').read())
print dict_device_setting

device_odin = dict_device_setting['device_odin']
device_prvkey_pem = dict_device_setting['prvkey_pem']

def new_sensor(sensor_id,sensor_type,ap_url,pubkey):
  pubkey=pubkey.replace('\\'+'r'+'\\'+'n',"\r\n")
  
  print "sensor_id : ", sensor_id, ", sensor_type : " , sensor_type
  print "pubkey : ", pubkey
  
  #在比原链上登记新的传感器标识
  nowtime=int(time.time())
  new_sensor_odin=device_odin+"/"+sensor_id+'#'+str(nowtime)
  
  dict_pttp_content = {"ver":1,"title":sensor_id,"@type":sensor_type}
  
  if ap_url !="" :
      dict_pttp_content["ap_set"] = {'0':{ 'url' : ap_url} } #指定设备标识托管服务AP节点      
  
  
  
  if pubkey != "SameToDevice" :
    dict_vdset = { }  #缺省为允许开放接受控制消息的传感器，不需要密钥签名验证
    if pubkey !="" :
      dict_vdset = { 'algo' : 'SHA256withRSA', 'pubkey' : pubkey}  #需要设置控制密钥的传感器
    dict_pttp_content["vd_set"] = dict_vdset
  
  str_pttp_content = json.dumps(dict_pttp_content);
  
  dict_metainfo = { 'content_type' : 'text/json','chunk_index':0, 'chunk_count':1 } 
  
  dict_data =  { 'uri' : new_sensor_odin, 'utc' : nowtime, 'status_code' : '200', 'status_detail' : 'OK', 'metainfo': dict_metainfo, 'content': str_pttp_content} 
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


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='joyiot_demo arguments',
        formatter_class=argparse.ArgumentDefaultsHelpFormatter)

    parser.add_argument('--id', type=str, default="myled1", help='The sensor id')
    parser.add_argument('--type', type=str, default="LED_MATRIX", help='The sensor type (DHT or LED_MATRIX)')
    parser.add_argument('--ap', type=str, default="", help='The AP node URL')
    parser.add_argument('--pubkey', type=str, default="SameToDevice", help='The public key for verifing controller.')

    args = parser.parse_args()

    try:
        new_sensor(args.id, args.type, args.ap,args.pubkey)
    except KeyboardInterrupt:
        pass
