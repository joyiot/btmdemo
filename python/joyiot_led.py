#!/usr/bin/env python
# -*- coding: utf-8 -*-
# PPK JoyIOT DEMO based Bytom Blockchain 
#         PPkPub.org  20181017            
#    Released under the MIT License.   
#
#    在8×8的LED屏上显示从比原链上读取的信息示例
#  


import time
import argparse

import urllib
from urllib import urlencode

import json
import base64

from luma.led_matrix.device import max7219
from luma.core.interface.serial import spi, noop
from luma.core.render import canvas
from luma.core.legacy import text
from luma.core.legacy.font import proportional, LCD_FONT


def demo(data_odin,ap_url):
    # create matrix device
    serial = spi(port=0, device=0, gpio=noop())
    device = max7219(serial, width=8, height=8, rotate=0, block_orientation=-90)
    print("Created device")
    
    dict_pttp_interest={"ver":1,"interest":{"uri":data_odin}}
    str_pttp_interest = json.dumps(dict_pttp_interest)
    print(str_pttp_interest)
    
    device_data_url=ap_url+'?'+urlencode({'pttp_interest':str_pttp_interest})
    print(device_data_url)
    
    
    response = urllib.urlopen(device_data_url)
    str_response=response.read().decode('utf-8')
    print "PTTP response=",str_response
    dict_pttp_repsonse=json.loads(str_response)
    dict_data=json.loads( dict_pttp_repsonse['data'] )
    
    #str_content=base64.b64decode(dict_data['content'])
    str_content=dict_data['content'];
    print "content=",str_content
    
    dict_content=json.loads(str_content)
    matrix=dict_content['matrix']
    print "matrix=",matrix
    
    with canvas(device) as draw:
        for x in range(0,8): 
            for y in range(0,8):
              draw.point((x, y), (int(matrix[x*8+y:x*8+y+1])+1)%2)


    time.sleep(300)


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='joyiot_demo arguments',
        formatter_class=argparse.ArgumentDefaultsHelpFormatter)

    #读取设备配置文件
    dict_device_setting = json.loads(open('device.json').read())
    device_odin = dict_device_setting['device_odin']

    parser.add_argument('--id', type=str, default="myled1", help='The led ID')
    parser.add_argument('--ap', type=str, default="http://btmdemo.ppkpub.org:8088/", help='The ap service')

    args = parser.parse_args()
    
    record_odin = device_odin+"/"+args.id+"/#"
    print "record_odin = ",record_odin

    try:
        demo(record_odin, args.ap)
    except KeyboardInterrupt:
        pass
