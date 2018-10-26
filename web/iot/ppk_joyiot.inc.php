<?php
/* PPK JoyIOT DEMO based Bytom Blockchain */
/*         PPkPub.org  20181013           */  
/*    Released under the MIT License.     */

ini_set("display_errors", "On"); 
error_reporting(E_ALL | E_STRICT);

require_once "../ppk_joyblock.inc.php";

define('ODIN_JOYIOT_BTM_RESOURCE',ODIN_JOYIOT_PREFIX.'bytom/');


//从交易详情中解析出趣吧定义数据
function parseIotDeviceRecordFromBtmTransaction($obj_tx_data){
  $str_hex=parseSpecHexFromBtmTransaction($obj_tx_data,PPK_JOYIOT_FLAG);
  if(strlen($str_hex)>0){
    $obj_set=json_decode(hexToStr($str_hex),true);
    //print_r($obj_set);
    if(isset($obj_set['@type']) && $obj_set['@type']=='PeerIOT' ){ //有效设备登记数据
      $obj_set['name']=@urldecode($obj_set['name']); //适配中文编码转换
      
      $obj_set['tx_id'] = $obj_tx_data['tx_id'];
      $obj_set['block_time'] = $obj_tx_data['block_time'];
      $obj_set['block_height'] = $obj_tx_data['block_height'];
      $obj_set['block_hash'] = $obj_tx_data['block_hash'];
      $obj_set['block_index'] = $obj_tx_data['block_index']; //position of the transaction in the block.

      return $obj_set;
    }
  }
  return null;
}


//从ETH交易详情中解析出趣吧消息的定义数据
function parsePubPostRecordFromEthTransaction($obj_tx_data){
  $tmp_input=$obj_tx_data['input'];
  $str_flag_hex=strtohex(PPK_JOYIOT_FLAG);
  $flag_posn=strpos($tmp_input,$str_flag_hex);
  //echo 'flag_posn=',$flag_posn;
  if($flag_posn>0){ //符合特征
    $str_hex=substr($tmp_input,$flag_posn+strlen($str_flag_hex));
    if(strlen($str_hex)>0){
      $obj_set=json_decode(hexToStr($str_hex),true);
      if(isset($obj_set['post_hex'])>0 && strlen($obj_set['post_hex'])>0){ //有效数据
        $obj_set['post']=@json_decode(hexToStr($obj_set['post_hex']),true);
        
        $obj_set['post']['text']=@urldecode($obj_set['post']['text']); //适配中文编码转换
        
        $obj_set['post_uri']=ODIN_JOYPUB_ETH_RESOURCE.$obj_tx_data['hash'];
        
        $obj_set['tx_id'] = $obj_tx_data['hash'];
        $obj_set['block_time'] = $obj_tx_data['timeStamp'];
        $obj_set['block_height'] = $obj_tx_data['blockNumber'];
        $obj_set['block_hash'] = $obj_tx_data['blockHash'];
        $obj_set['block_index'] = $obj_tx_data['transactionIndex']; //position of the transaction in the block.

        return $obj_set;
      }
    }
  }
  return null;
}

//按标识获取设备信息
function  getIotDeviceInfo($device_odin){
  $default_device_info=array(
    'device_odin'=> $device_odin,
    'name'=>"",
    'avtar'=>"image/iot.png"
  );
  
  if(stripos($device_odin,ODIN_JOYIOT_BTM_RESOURCE)!==0){
    return $default_device_info;
  }
  $tmp_chunks=explode( "#",substr($device_odin,strlen(ODIN_JOYIOT_BTM_RESOURCE)) );
  $odin_chunks=explode("/",$tmp_chunks[0]);
  $btm_tx_id=$odin_chunks[0];


  $tmp_tx_data=getBtmTransactionDetail($btm_tx_id);
  if($tmp_tx_data==null){
    return $default_device_info;
  } 
          
  $obj_set=parseIotDeviceRecordFromBtmTransaction($tmp_tx_data);
  if($obj_set==null){
    $obj_set = $default_device_info;
  } 
  
  $obj_set['device_odin']=$device_odin;
  $obj_set['name']=@urldecode($obj_set['name']); //适配中文编码转换

  return $obj_set;
}

