<?php
/* PPK JoyIOT DEMO based Bytom Blockchain */
require_once "ppk_joyiot.inc.php";

if(isset($_REQUEST['backurl']))
  $back_url=$_REQUEST['backurl'];
else
  $back_url='./';

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>JoyIOT注册一个新设备</title>
<link rel="stylesheet" href="css/joyiot.css" />
<style type="text/css">
  #yourinfo {
      left: 100px;
      top:  100px;
      width: 300px;
      height: 500px;
      margin: 20px auto;
      position: absolute;
  }
</style>
</head>
<body>
<div id="web_bg"></div>
<div id="navibar">
<p align="right">JoyIOT@PPkPub.org 20181015 V0.1a , <?php echo  '(Bytom network id: ',$gStrBtmNetworkId,')';?></p>
<h2 align="right">返回<a href="./">趣联网主页</a></h2>
</div>
<div id="yourinfo">
<!--
<p>你的比原链账户:（待比原链类似Metamask浏览器插件出来完善）</p>
<p id='you_btm_address'>PPk2018...</p>
-->
<h2>注册一个新设备ODIN标识</h2>
<hr>
<p>设备属性定义：</p>
<p align='center'>
<textarea id="device_setting_json" rows=20 cols=50 onchange="updateTransData();"  >
{
  "@context": [
        "https://schema.org/",
        "https://w3id.org/security/v1"
    ],
  "@type": "PeerIOT",
  
  "name": "Test1",
  "avtar": "http://btmdemo.ppkpub.org/joy/iot/image/iot.png",  
  
  "gas_asset_uris":[
    "ppk:BTM/asset/<?php echo JOYBLOCK_TOEKN_ASSET_ID;?>"
  ],

  "authenticationCredential": [
      {
          "type": "RsaCryptographicKey",
          "publicKeyPem": "-----BEGIN PUBLIC KEY\r\n..........\r\nEND PUBLIC KEY-----"
      }
  ]
}</textarea>
</p>
<p>转账GAS费用：<input type="text" name="iot_trans_fee_btm" id="iot_trans_fee_btm" value="<?php echo TX_GAS_AMOUNT_mBTM/1000; ?>" size=10 readonly="true" style="background:#CCCCCC"> BTM</p>
<p>Retire附加数据：<input type="text" name="iot_trans_data_hex" id="iot_trans_data_hex" value="" size=20 readonly="true" style="background:#CCCCCC" ></p>

<p><br>
　　　　　<input type='button' id="send_trans_btn" value=' 确认发布到比原链上 ' onclick='sendBtmTX();'> 
</p>

<!--
<p>二维码（可使用比原链钱包APP来扫码发送交易）:</p>
<p><img id="game_trans_qrcode" border=0 width=250 height=250 src="image/star.png" title="qrcode"></p>
<p><input type=text id="qrcode_text" value="..." size=30></p>
<hr>
</p>
<p><a target="_blank" href="https://bytom.io/"><img src="https://bytom.io/wp-content/uploads/2018/04/logo-white-v.png" alt="下载比原链钱包" width=200 height=50></a>
</p> 
-->

<!--
<script src="https://cdn.jsdelivr.net/gh/ethereum/web3.js/dist/web3.min.js"></script>
-->
<script src="../js/common_func.js"></script>
<script type="text/javascript">
function sendBtmTX() {
  if(document.getElementById('device_setting_json').value.length == 0 ){
    alert("请输入有效的设备定义！");
    return false;
  }

  if(document.getElementById('iot_trans_fee_btm').value.length == 0 ){
    alert('请输入有效的转账GAS费用，缺省为 <?php echo TX_GAS_AMOUNT_mBTM/1000; ?> BTM！');
    return false;
  }
  
  updateTransData();
  
  document.getElementById("send_trans_btn").disabled=true;
  document.getElementById("send_trans_btn").value="正在自动注册设备标识,请稍候...";
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.open("GET","send_tx.php?iot_trans_data_hex="+document.getElementById("iot_trans_data_hex").value);
  xmlhttp.send();
  xmlhttp.onreadystatechange=function()
  {
    if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
      document.getElementById("send_trans_btn").value=" 确认发布到比原链上 ";
      document.getElementById("send_trans_btn").disabled=false;
      console.log(xmlhttp.responseText);
      var obj_result = JSON.parse(xmlhttp.responseText);
      if( obj_result!=null && obj_result.status=='success'){
        var device_odin="<?php echo ODIN_JOYIOT_BTM_RESOURCE;?>"+obj_result.data.tx_id;
        alert("注册设备成功\nODIN标识："+device_odin);
        self.location="<?php echo $back_url;?>";
      }else{
        alert("出错了！\n"+xmlhttp.responseText);
      }
    }
  }
}


function updateTransData(){
  var iot_trans_fee_btm = <?php echo TX_GAS_AMOUNT_mBTM/1000; ?>;
  

  var str_setting=document.getElementById('device_setting_json').value;
  if(str_setting.length == 0 ){
    return false;
  }
 
  str_setting=str_setting.replace(/\r/g,"").replace(/\n/g,"").replace(/\t/g," ");

  var game_trans_data="<?php  echo PPK_JOYIOT_FLAG; ?>"+str_setting;
  console.log("game_trans_data="+game_trans_data);
  
  var iot_trans_data_hex = stringToHex(game_trans_data);

  document.getElementById('iot_trans_data_hex').value= iot_trans_data_hex;
  
  //var btm_uri='bytom:'+document.getElementById('guess_contract_uri').value+'?value='+iot_trans_fee_btm+'&data='+iot_trans_data_hex;
  //document.getElementById('qrcode_text').value= btm_uri;
  //document.getElementById('game_trans_qrcode').src='http://qr.liantu.com/api.php?text='+encodeURIComponent(btm_uri);

}

function resetAll(){
  document.getElementById('iot_trans_fee_btm').value=<?php echo TX_GAS_AMOUNT_mBTM/1000; ?>;
  document.getElementById('iot_trans_data_hex').value='';
  
  //document.getElementById('qrcode_text').value= '';
  //document.getElementById('game_trans_qrcode').src='star.png';

}


</script>
</body>
</html>
