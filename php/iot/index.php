<?php
/* PPK JoyIOT DEMO based Bytom Blockchain */
/*         PPkPub.org  20181015           */  
/*    Released under the MIT License.     */
require_once "ppk_joyiot.inc.php";

//查询带有设备注册数据的retire交易
$array_sets=array();

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
          $obj_set=parseIotDeviceRecordFromBtmTransaction($tmp_tx_data);
          if($obj_set!=null)
            $array_sets['blocktime-'.$obj_set['block_time']]=$obj_set;
        } 
      }
    }
  }
}

//print_r($array_sets);

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>JoyIOT-趣联网（融合多链的物联网DAPP Demo）</title>
<link rel="stylesheet" href="css/joyiot.css" />
<style type="text/css">
  .square {
      width: 280px;
      height: 300px;
      background-color: #203060; 
      margin: 10px auto;
      font-size:9px;
      position: absolute;
      
      border: -2px solid #dddddd;
      box-shadow: 2px 2px 3px rgba(50, 50, 50, 0.4);
      -webkit-transition: all 0.5s ease-in;
      -moz-transition: all 0.5s ease-in;
      -ms-transition: all 0.5s ease-in;
      -o-transition: all 0.5s ease-in;
      transition: all 0.5s ease-in;
  }
  
  .square a:link,.square a:visited{color:#fff;text-decoration:underline;}
</style>
</head>
<body>
<div id="web_bg"></div>
<img src="image/joyiot.png" width=550 height=80 >
<div id="navibar">
<p>PPkPub.org 20181015 V0.1a , <?php echo  '(Bytom network id: ',$gStrBtmNetworkId,')';?></p>
<br>
<h2><a href="new_device.php">请点击这里注册一个新设备，获得跨链跨平台的ODIN标识</a></h2>
</div>

<?php
krsort($array_sets);

$ss=0;
foreach($array_sets as $obj_set){
  $str_title=$obj_set['name'];
  //$array_gas_asset_uris=$obj_set['gas_asset_uris'];
  $str_device_odin=ODIN_JOYIOT_BTM_RESOURCE.$obj_set['tx_id'];
  
  $str_img_data_url= (isset($obj_set['avtar']) && strlen($obj_set['avtar'])>0 ) ? $obj_set['avtar'] :'image/iot.png';

  $str_pub_time = formatTimestampForView($obj_set['block_time'],false);
   
  $leftx = 55+($ss % 3)*300 ;
  $topy  = 65+floor($ss / 3)*310;
  
  echo '<div class="square" style="left: ',$leftx,'px;top: ',$topy,'px;" ><center>',$str_pub_time,'<br><a href="device.php?device_odin=',$str_device_odin,'"><img width="256" height="256" src="',$str_img_data_url,'" border=0><br>';
  
  echo '<h2>',$str_title,'</h2></a>';

  echo '</center></div>';
  
  $ss++;
}

echo "<!--当前接入比原网络ID：",$gStrBtmNetworkId,"\n";
print_r($btm_netinfo);
echo "\n-->";
?>

<script type="text/javascript">

</script>
</body>
</html>
