<?php
//ini_set("display_errors", "On"); 
//error_reporting(E_ALL | E_STRICT);

/*
  Response the iot record in bytom transaction  
    PPkPub.org   20181013
  Released under the MIT License.
*/
require 'ap.common.inc.php';

require '../iot/ppk_joyiot.inc.php';

define('JOYIOT_DEFAULT_BYTOM_KEY_FILE', AP_KEY_PATH.'527064.583/iot/bytom.json' ); //缺省的PTTP签名私钥数据文件
define('JOYIOT_DEFAULT_BYTOM_AP_DATA_FILE', AP_RESOURCE_PATH.'527064.583/iot/bytom#1.0#' ); //缺省的PTTP签名私钥数据文件

//在URL中指定例如 ?pttp_interest={"ver":1,"interest":{"uri":"ppk:513468.490/"}}  
//view-source:http://btmdemo.ppkpub.org/joy/ap/iot_bytom.php?pttp_interest={"ver":1,"interest":{"uri":"ppk:527064.583/iot/bytom/tm1qzymnxuzlt6e8sjf4vc0ct6f6vkk25y27dtzdwe#"}}  
//view-source:http://btmdemo.ppkpub.org/joy/ap/iot_bytom.php?pttp_interest=%7B%22ver%22%3A1%2C%22interest%22%3A%7B%22uri%22%3A%22ppk%3A527064.583%2Fiot%2Fbytom%2Ftm1qzymnxuzlt6e8sjf4vc0ct6f6vkk25y27dtzdwe%23%22%7D%7D
//http://btmdemo.ppkpub.org/joy/ap/iot_bytom.php?pttp_interest=%7B%22ver%22%3A1%2C%22interest%22%3A%7B%22uri%22%3A%22ppk%3A527064.583%2Fiot%2Fbytom%2F48115216e50ed76135ee1eb186aed9bcee9f2f9b0ae9522ea7b062a3fcfb3c14%2Fdht%23%22%7D%7D

//或者从POST FORM中提取
$array_pttp_interest=array();
$str_pttp_interest='';
$str_pttp_uri='';

if($_GET['pttp_interest']!=null){ 
  $str_pttp_interest=trim($_GET['pttp_interest']);
}elseif($_POST['pttp_interest']!=null){ 
  $str_pttp_interest=trim($_POST['pttp_interest']);
}

if(strlen($str_pttp_interest)>0){
  //提取出兴趣uri
  $array_pttp_interest=json_decode($str_pttp_interest,true);
  $str_pttp_uri=$array_pttp_interest['interest']['uri'];
}

if(!isset($str_pttp_uri)){
  respPttpStatus4XX( '400',"Bad Request : no valid uri " );
  exit(-1);
}

require_once "../ppk_joyblock.inc.php";
if( 0==strncasecmp($str_pttp_uri,ODIN_JOYIOT_BTM_RESOURCE,strlen(ODIN_JOYIOT_BTM_RESOURCE))
  ){
  $str_flag = PPK_JOYIOT_FLAG;
  $str_bytom_tx_odin_prefix=ODIN_JOYIOT_BTM_RESOURCE;
}else{
  respPttpStatus4XX( '400',"Bad Request : Invalid joyiot-bytom-address-uri ".$str_pttp_uri );
  exit(-1);
}

$odin_chunks=array();
$parent_odin_path="";
$resource_id="";
$req_resource_versoin="";
$resource_filename="";

$tmp_chunks=explode("#",substr($str_pttp_uri,strlen($str_bytom_tx_odin_prefix)));
$odin_chunks=explode("/",$tmp_chunks[0]);
$device_reg_bytom_tx=$odin_chunks[0];

if(count($odin_chunks)==1){
  //返回设备AP访问参数
  $sub_resource_id=null;
  $str_resp_content=file_get_contents(JOYIOT_DEFAULT_BYTOM_AP_DATA_FILE);
            
  $str_content_type='text/json';
  $resp_resource_versoin='1';//区块链上的交易数据版本目前缺省都是1

  $device_odin=$str_bytom_tx_odin_prefix.$device_reg_bytom_tx."#".$resp_resource_versoin;
  
  $tmp_device_info=getIotDeviceInfo($device_odin);
  
  $device_pubkey=@$tmp_device_info['authenticationCredential'][0]['publicKeyPem'];
  
  $tmp_posn=strpos($device_pubkey,"BEGIN PUBLIC KEY\r\n");
  if($tmp_posn===0 || $tmp_posn>0 ){
     $device_pubkey=substr($device_pubkey,$tmp_posn+strlen("BEGIN PUBLIC KEY\r\n"));
  }
  
  $tmp_posn=strpos($device_pubkey,"\r\nEND PUBLIC KEY");
  if($tmp_posn===0 || $tmp_posn>0 ){
     $device_pubkey=substr($device_pubkey,0,$tmp_posn);
  }
  $device_pubkey=str_replace("\r\n",'\r\n',$device_pubkey);
  //echo '$device_pubkey=',$device_pubkey;
  
  $str_content_type='text/json';
  $str_resp_content='{"ver":1,"auth":"0","vd_set":{"cert_uri":"","algo":"SHA256withRSA","pubkey":"'.$device_pubkey.'"},"ap_set":{"0":{"url":"http://btmdemo.ppkpub.org/joy/ap/iot_bytom.php"}}}';
  
  respPttpData( $device_odin,$str_content_type,$str_resp_content);
  exit(0);
}else if(count($odin_chunks)==2){
  $sub_resource_id=$odin_chunks[1];
}else if(count($odin_chunks)==3){
  $sub_resource_id=$odin_chunks[1].'/';
}else{
  respPttpStatus4XX( '400',"Bad Request : Invalid joyiot-bytom-address-uri : ".$str_pttp_uri );
  exit(-1);
}

//echo "str_pttp_uri=$str_pttp_uri\n";
//echo "device_reg_bytom_tx=$device_reg_bytom_tx,  sub_resource_id=$sub_resource_id, req_resource_versoin=$req_resource_versoin \n";

if(count($tmp_chunks)>=2){
  $req_resource_versoin=$tmp_chunks[1];
}

$dest_record_odin = ODIN_JOYIOT_BTM_RESOURCE . $device_reg_bytom_tx .'/'.$sub_resource_id.'#'.$req_resource_versoin;
//echo "dest_record_odin=$dest_record_odin\n";

//查询带JOYIOT数据的retire交易
$tmp_url=BTM_NODE_API_URL.'list-transactions';
//$tmp_post_data='{"account_id": "'.BTM_NODE_API_ACCOUNT_ID_PUB.'"}';
$tmp_post_data='{"unconfirmed":true,"count":100}';

$obj_resp=commonCallBtmApi($tmp_url,$tmp_post_data);
//print_r($obj_resp);
if(strcmp($obj_resp['status'],'success')===0){
  for($aa=0;$aa<count($obj_resp['data']);$aa++){
    //echo "<!-- ",$aa," - ",$obj_resp['data'][$aa]['tx_id'],"-->\n";
    for($pp=0;$pp<count($obj_resp['data'][$aa]['outputs']);$pp++){
      $tmp_out=$obj_resp['data'][$aa]['outputs'][$pp];
      if($tmp_out['type']=='retire'){
        $bytom_tx_id = $obj_resp['data'][$aa]['tx_id'];
        $tmp_tx_data=getBtmTransactionDetail( $bytom_tx_id );
        //print_r($tmp_tx_data);
        if($tmp_tx_data!=null){
          $str_hex=parseSpecHexFromBtmTransaction($tmp_tx_data,$str_flag);

          if(strlen($str_hex)>0){
            $str_retired_content=hexToStr($str_hex);
            //echo "str_retired_content=$str_retired_content \n";
            $obj_retired_content = @json_decode($str_retired_content,true);
            //print_r( $obj_retired_content );
            if(isset($obj_retired_content['data']) && isset($obj_retired_content['sign'])){ //PTTP DATA格式
              $obj_pttp_data = @json_decode($obj_retired_content['data'],true);
              //print_r( $obj_pttp_data );
              if( isset( $obj_pttp_data['uri'] ) ){
                $record_odin=$obj_pttp_data['uri'];
                if( 0==strncasecmp($record_odin,$dest_record_odin,strlen($dest_record_odin)) ){
                  //输出数据正文
                  header('Content-Type: text/json');
                  echo $str_retired_content;
                  exit(0);
                }
              }
            } 
          }
        } 
      }
    }
  }
}


//echo "str_pttp_uri=$str_pttp_uri\n";
//echo "parent_odin_path=$parent_odin_path , resource_id=$resource_id , req_resource_versoin=$req_resource_versoin \n";
//echo "bytom_tx_id=$bytom_tx_id\n";


respPttpStatus4XX( '404',"Bad Request : resource not exists. " );
exit(-1);

function respPttpData( $str_resp_uri,$str_content_type,$str_resp_content){
  
  $str_pttp_data=generatePTTPData( $str_resp_uri,'200','OK',$str_content_type,$str_resp_content,'public',JOYIOT_DEFAULT_BYTOM_KEY_FILE );

  //输出数据正文
  header('Content-Type: text/json');
  echo $str_pttp_data;
}
