<?php
/* PPK JoyIOT DEMO based Bytom Blockchain */
/*         PPkPub.org  20181015           */  
/*    Released under the MIT License.     */
require_once "ppk_joyiot.inc.php";

$device_odin=$_REQUEST['device_odin'];
if(stripos($device_odin,ODIN_JOYIOT_BTM_RESOURCE)!==0){
  echo '无效的设备ODIN标识. Invalid Device ODIN.';
  exit(-1);
}

$tmp_chunks=explode( "#",$device_odin );
$device_odin_prefix=$tmp_chunks[0]."/";
//echo 'device_odin_prefix=',$device_odin_prefix,"\n";

$tmp_device_info=getIotDeviceInfo($device_odin);

$str_created_time = formatTimestampForView($tmp_device_info['block_time'],false);

//查询该设备下属传感器标识关联数据的retire交易
$array_sensors=array();

$tmp_url=BTM_NODE_API_URL.'list-transactions';
//$tmp_post_data='{"account_id": "'.BTM_NODE_API_ACCOUNT_ID_PUB.'"}';
$tmp_post_data='{"unconfirmed":true}';

$obj_resp=commonCallBtmApi($tmp_url,$tmp_post_data);

if(strcmp($obj_resp['status'],'success')===0){
  for($kk=0;$kk<count($obj_resp['data']);$kk++){
    //echo "<!-- ",$obj_resp['data'][$kk]['tx_id'],"-->\n";
    for($pp=0;$pp<count($obj_resp['data'][$kk]['outputs']);$pp++){
      $tmp_out=$obj_resp['data'][$kk]['outputs'][$pp];
      if($tmp_out['type']=='retire' && $tmp_out['asset_id']==JOYBLOCK_TOEKN_ASSET_ID ){
        //echo "<!-- ",$obj_resp['data'][$kk]['tx_id'],"-->\n";
        $tmp_tx_data=getBtmTransactionDetail($obj_resp['data'][$kk]['tx_id']);
        //print_r($tmp_tx_data);
        if($tmp_tx_data!=null){
          $str_hex=parseSpecHexFromBtmTransaction($tmp_tx_data,PPK_JOYIOT_FLAG);

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
                //echo '$record_odin=',$record_odin,"\n";
                if( 0==strncasecmp($record_odin,$device_odin_prefix,strlen($device_odin_prefix)) ){
                  $tmp_chunks=explode("#",substr($record_odin,strlen($device_odin_prefix)));
                  $odin_chunks=explode("/",$tmp_chunks[0]);
                  if(count($odin_chunks)==1){ //传感器登记消息
                    $tmp_sensor_id=$odin_chunks[0];
                    //echo '$tmp_sensor_id=',$tmp_sensor_id,"\n";
                    if(!array_key_exists($tmp_sensor_id,$array_sensors)){
                      $obj_sensor_set = @json_decode($obj_pttp_data['content'],true);
                      $obj_sensor_set['last_record_odin']=$record_odin;
                      $array_sensors[$tmp_sensor_id]=$obj_sensor_set;
                    }
                  }
                }
              }
            } 
          }
        } 
      }
    }
  }
}

//print_r($array_sensors);

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>JoyIOT-趣联网（融合多链的物联网DAPP Demo）</title>
<link rel="stylesheet" href="css/joyiot.css" />
<style type="text/css">

  
  #device_info {
      left: 80px;
      top:  100px;
      width: 90%;
      height: 80%;
      margin: 0px auto;
      position: absolute;
      overflow:auto;
      color: #000;
      background-color: #eee;
  }
  
</style>
</head>
<body>
<div id="web_bg"></div>

<div id='pub_top'>
  <table width="100%" border="0">
  <tr>
  <td align="left" width="100">
  <img  style="float:left"  src="<?php echo $tmp_device_info['avtar'];?>" width=64 height=64>
  </td>
  <td>
  <h1><?php echo $tmp_device_info['name']?></h1>
  </td>
  </tr>
  </table>
</div>

<div id='device_info'>
<h2>设备信息</h2>
<hr>
<P>ODIN标识: <?php echo $tmp_device_info['device_odin']; ?></p>
<P>传感器列表: </p>

<?php 
if(count($array_sensors)>0){
  foreach( $array_sensors as $tmp_sensor_id => $tmp_sensor_record){
    $tmp_sensor_type = @$tmp_sensor_record['@type'];
    $tmp_sensor_odin = $device_odin_prefix.$tmp_sensor_id.'/#';
    
    if($tmp_sensor_type=='DHT'){
        echo '<li>[温湿度传感器]',$tmp_sensor_id,' : <a href="',PPK_DEFAULT_AP_GATEWAY,'?ppk-uri=',urlencode($tmp_sensor_odin),'">',$tmp_sensor_odin,'</a></li>';
    }else if($tmp_sensor_type=='LED_MATRIX'){
       $tmp_pubkey=@$tmp_sensor_record['vd_set']['pubkey'];
       echo '<li>[LED点阵]',$tmp_sensor_id,' : <a href="led.php?width=8&height=8&receiver_odin=',urlencode($tmp_sensor_odin),'&pubkey=',urlencode($tmp_pubkey),'">发送控制信息</a><br>　　查看最近的控制数据 <a href="',PPK_DEFAULT_AP_GATEWAY,'?ppk-uri=',urlencode($tmp_sensor_odin),'">',$tmp_sensor_odin,'</a></li>';
    }else{
      echo '<li>[未知类型:',$tmp_sensor_type,']',$tmp_sensor_id,' : <a href="',PPK_DEFAULT_AP_GATEWAY,'?ppk-uri=',urlencode($tmp_sensor_odin),'">',$tmp_sensor_odin,'</a></li>';
    }
  }
}
?>


<P>关联数字资产: </p>
<textarea rows=5 cols=80>
<?php print_r(@$tmp_device_info['gas_asset_uris']); ?>
</textarea>

<P>验证参数: </p>
<textarea rows=5 cols=80>
<?php print_r(@$tmp_device_info['authenticationCredential']); ?>
</textarea>


<P>注册时间: <?php echo $str_created_time; ?></p>
<?php
echo '<p><br><a href="./">回到首页</a></p>';
?>
</div>

<script src="../js/common_func.js"></script>
<script type="text/javascript">
window.addEventListener('load', function() {
  var device_info = document.getElementById("device_info");
  device_info.scrollTop = device_info.scrollHeight;   
});
</script>
</body>
</html>
