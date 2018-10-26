<?php
/* PPK JoyIOT DEMO based Bytom Blockchain */
require_once "ppk_joyiot.inc.php";

$receiver_odin=$_REQUEST['receiver_odin'];
if(stripos($receiver_odin,ODIN_JOYIOT_BTM_RESOURCE)!==0){
  echo '无效的接收端ODIN标识. Invalid receiver ODIN.';
  exit(-1);
}

$receiver_pubkey=@$_REQUEST['pubkey'];

$matrix_width = floor(@$_GET['width']);
$matrix_width = $matrix_width<1 ? 8:$matrix_width;

$matrix_height = floor(@$_GET['height']);
$matrix_height = $matrix_height<1 ? 8:$matrix_height;

$square_width_pixels=floor( 64*8  / $matrix_width );
$array_matrix_marked=array();
for($x=0;$x<$matrix_width;$x++){
  for($y=0;$y<$matrix_height;$y++){
      $array_matrix_marked[$x][$y]=1;
  }
}


?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>JoyIOT发送LED点阵设置数据</title>
<link rel="stylesheet" href="css/joyiot.css" />
<style type="text/css">
 
  #navibar {
      left: 600px;
      top:  0px;
      width: 400px;
      height: 50px;
      margin: 0px auto;
      position: absolute;
      font-size:9px;
  }
  
  #container {
      left: 80px;
      top:  80px;
      width: <?php echo $matrix_width*$square_width_pixels;?>px;
      height: <?php echo $matrix_height*$square_width_pixels;?>px;
      margin: 20px auto;
      position: absolute;
  }

  #container img:hover {
      box-shadow: 15px 15px 20px rgba(50, 50, 50, 0.4);
      transform: rotate(0deg) scale(1.20);
      -webkit-transform: rotate(0deg) scale(1.20);
      z-index: 2;
  }

  #container img {
      border: -2px solid #dddddd;
      box-shadow: 2px 2px 3px rgba(50, 50, 50, 0.4);
      -webkit-transition: all 0.5s ease-in;
      -moz-transition: all 0.5s ease-in;
      -ms-transition: all 0.5s ease-in;
      -o-transition: all 0.5s ease-in;
      transition: all 0.5s ease-in;
      position: absolute;
      z-index: 1;
  }

  .square {
            width: <?php echo $square_width_pixels-1;?>px;
            height: <?php echo $square_width_pixels-1;?>px;
            background-color: #000; 
            position: absolute;
        }

  #yourmark {
      left: <?php echo $matrix_width*$square_width_pixels+100;?>px;
      top:  80px;
      width: 300px;
      height: <?php echo $matrix_height*$square_width_pixels;?>px;
      margin: 20px auto;
      position: absolute;
  }
</style>
</head>
<body>

<div id="container">
<?php 
for($y=0;$y<$matrix_height;$y++){
  for($x=0;$x<$matrix_width;$x++){
      $led_color=$array_matrix_marked[$x][$y]>0 ? '#fff':'#000';
      
      echo '<a href="#"><div id="square_'.$x.'_'.$y.'" class="square" style="left: '.($x*$square_width_pixels).'px;top: '.($y*$square_width_pixels).'px;background-color:',$led_color,'" onclick="clickSquare('.$x.','.$y.');"></div></a>';
  }
}

?>
</div>
<div id="navibar">
<p>PPkPub.org 20181013 V0.1a , <?php echo  '(Bytom network id: ',$gStrBtmNetworkId,')';?></p>
</div>
<div id="yourmark">
<!--
<p>你的比原链账户:（待比原链类似Metamask浏览器插件出来完善）</p>
<p id='you_btm_address'>PPk2018...</p>
-->
<p>在左侧点击方块即可开始绘图。<br></p>
<hr>
<p>
生成比原链交易参数：<br>
<form name="form_pub" id="form_pub" action="send_led.php" method="post">
接收端ODIN：<input type=text name="receiver_odin"  id="receiver_odin" value="<?php echo $receiver_odin;?>" size=20 onchange="updateTransData();"  >
<br>
花费资产：<input type=text name="asset_id"  id="asset_id" value="<?PHP echo JOYBLOCK_TOEKN_ASSET_ID ;?>" size=20 onchange="updateTransData();"  >
<br><br>
转账GAS费用：<input type="text" name="trans_fee_btm" id="trans_fee_btm" value="<?php echo TX_GAS_AMOUNT_mBTM/1000; ?>" size=10 readonly="true" style="background:#CCCCCC"> BTM<br>
Retire附加数据：<input type="text" name="trans_data_hex" id="trans_data_hex" value="" size=20 readonly="true" style="background:#CCCCCC" ><br>
<br>
　　　　　<input type='button' id="game_send_trans_btn" value=' 确认发布到比原链上 ' onclick='sendBtmTX();'> 
</form>

控制消息的验证公钥：
<textarea  name="devive_pubkey_pem" id="devive_pubkey_pem"  rows=3 cols=40 readonly="true">
<?php
if(strlen($receiver_pubkey)>0)
    echo "-----BEGIN PUBLIC KEY\r\n",$receiver_pubkey,"\r\n-----END PUBLIC KEY";
?>
</textarea>
控制消息的签名私钥：
<textarea  name="devive_prvkey_pem" id="devive_prvkey_pem"  rows=5 cols=40>
<?php
if(strlen($receiver_pubkey)>0)
    echo "-----BEGIN PRIVATE KEY\r\n......\r\n-----END PRIVATE KEY";
else
    echo "该设备是开放的，不需要输入私钥对控制消息进行签名";
?>
</textarea>
</p>

<!--
<p>二维码（可使用比原链钱包APP来扫码发送交易）:</p>
<p><img id="game_trans_qrcode" border=0 width=250 height=250 src="star.png" title="qrcode"></p>
<p><input type=text id="qrcode_text" value="..." size=30></p>
<hr>
</p>
<p><a target="_blank" href="https://bytom.io/"><img src="https://bytom.io/wp-content/uploads/2018/04/logo-white-v.png" alt="下载比原链钱包" width=200 height=50></a>
</p> 
-->
<p>预览：</p>
<center>
<canvas id="cvs" width="128" height="128"></canvas>
</center>
</div>
<!--
<script src="https://cdn.jsdelivr.net/gh/ethereum/web3.js/dist/web3.min.js"></script>
-->
<script type="text/javascript">
var MATRIX_MAX_WIDTH = <?php echo $matrix_width;?>;
var MATRIX_MAX_HEIGHT = <?php echo $matrix_height;?>;
var SQUARE_WIDTH_PIXELS = <?php echo $square_width_pixels;?>;
var MATRIX_MARK = <?php  echo json_encode($array_matrix_marked)?>;
var lastClickSquareX=0;
var lastClickSquareY=0;

var canvas;
var canvasContext;

window.addEventListener('load', function() {
    //document.getElementById('game_send_trans_btn').disabled = false;
    canvas = document.getElementById('cvs');
    canvasContext = canvas.getContext('2d');
    
    canvasContext.fillStyle= "#FFFFFF";
    canvasContext.fillRect(0,0,128,128);
});

function drawPoint(x, y, blackOrWhite) {
    var scale=128/MATRIX_MAX_WIDTH;
    canvasContext.fillStyle= blackOrWhite>0 ? "#FFFFFF" : "#000000" ;
    canvasContext.fillRect(x*scale,y*scale,1*scale,1*scale);
}


function sendBtmTX() {
  if(document.getElementById('receiver_odin').value.length == 0 ){
    alert("请输入有效的设备ODIN标识！\n");
    return false;
  }
  if(document.getElementById('asset_id').value.length == 0 ){
    alert("请输入有效的资产ID！\n");
    return false;
  }
  if(document.getElementById('trans_fee_btm').value.length == 0 ){
    alert('请输入有效的转账GAS费用，缺省为 <?php echo TX_GAS_AMOUNT_mBTM/1000; ?> BTM！');
    return false;
  }
  updateTransData();
  document.getElementById('form_pub').submit();
}

function clickSquare(x,y){ 
  resetAll();
  
  lastClickSquareX=x;
  lastClickSquareY=y;
  
  MATRIX_MARK[x][y]=MATRIX_MARK[x][y]>0 ? 0:1;
  
  var div=document.getElementById('square_'+x+'_'+y);
  div.style.backgroundColor= MATRIX_MARK[x][y] ? '#fff':'#000';
  div.style.border = " 1px solid #f00 ";
  div.style.width  = ""+(SQUARE_WIDTH_PIXELS-3)+'px';
  div.style.height = ""+(SQUARE_WIDTH_PIXELS-3)+'px';
  
  drawPoint(x,y,MATRIX_MARK[x][y]);
  
  updateTransData();
}

function updateTransData(){
  var trans_fee_btm = <?php echo TX_GAS_AMOUNT_mBTM/1000; ?>;

  var receiver_odin=document.getElementById('receiver_odin').value;
  if(receiver_odin.length == 0 ){
    return false;
  }
  
  var asset_id=document.getElementById('asset_id').value;
  if(asset_id.length == 0 ){
    return false;
  }
  
  var str_matrix='';
  for( tmpx=0; tmpx<MATRIX_MAX_WIDTH;tmpx++ ){
    for(  tmpy=0 ; tmpy<MATRIX_MAX_HEIGHT;tmpy++ ){
      str_matrix += MATRIX_MARK[tmpx][tmpy];
    }
  }
  
  var setting=new Object();
  setting.width=8;
  setting.height=8;
  setting.matrix=str_matrix;
  setting.img_data_url=canvas.toDataURL("image/png");     
  
  var tmp_data=new Object();
  tmp_data.uri=receiver_odin;
  tmp_data.status_code="200";
  tmp_data.status_detail="OK";
  tmp_data.content=JSON.stringify(setting);
  
  var pttp_data=new Object(); 
  pttp_data.ver=1;
  pttp_data.data=JSON.stringify(tmp_data);
  pttp_data.sign=""; //待完善，增加通过JS生成SHA256withRSA签名

  /*
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.open("GET","sign_data.php?data_hex="+stringToHex(pttp_data.data)+"&prvkey_hex=";
  xmlhttp.send();
  xmlhttp.onreadystatechange=function()
  {
    if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
      console.log(xmlhttp.responseText);
      var obj_result = JSON.parse(xmlhttp.responseText);
      if( obj_result!=null && obj_result.status=='success'){
        var device_odin="<?php echo ODIN_JOYIOT_BTM_RESOURCE;?>"+obj_result.data.tx_id;
        alert("注册设备成功\nODIN标识："+device_odin);
      }else{
        alert("出错了！\n"+xmlhttp.responseText);
      }
    }
  }
  */
  var game_trans_data="<?php  echo PPK_JOYIOT_FLAG; ?>"+JSON.stringify(pttp_data);
  console.log("game_trans_data="+game_trans_data);
  
  var trans_data_hex = stringToHex(game_trans_data);

  document.getElementById('trans_data_hex').value= trans_data_hex;
  
  //var btm_uri='bytom:'+document.getElementById('guess_contract_uri').value+'?value='+trans_fee_btm+'&data='+trans_data_hex;
  //document.getElementById('qrcode_text').value= btm_uri;
  //document.getElementById('game_trans_qrcode').src='http://qr.liantu.com/api.php?text='+encodeURIComponent(btm_uri);

}
function resetAll(){
  document.getElementById('trans_fee_btm').value=<?php echo TX_GAS_AMOUNT_mBTM/1000; ?>;
  document.getElementById('trans_data_hex').value='';
  
  //document.getElementById('qrcode_text').value= '';
  //document.getElementById('game_trans_qrcode').src='star.png';

  var div=document.getElementById('square_'+lastClickSquareX+'_'+lastClickSquareY);
  div.style.border = " 0px solid #000 ";
  div.style.width  = ""+(SQUARE_WIDTH_PIXELS-1)+'px';
  div.style.height = ""+(SQUARE_WIDTH_PIXELS-1)+'px';

}


function stringToHex(str){
  var val="";
  for(var i = 0; i < str.length; i++){
      if(val == "")
          val = str.charCodeAt(i).toString(16);
      else
          val += str.charCodeAt(i).toString(16);
  }
  return val;
}


function utf16ToUtf8(s){
	if(!s){
		return;
	}
	
	var i, code, ret = [], len = s.length;
	for(i = 0; i < len; i++){
		code = s.charCodeAt(i);
		if(code > 0x0 && code <= 0x7f){
			//单字节
			//UTF-16 0000 - 007F
			//UTF-8  0xxxxxxx
			ret.push(s.charAt(i));
		}else if(code >= 0x80 && code <= 0x7ff){
			//双字节
			//UTF-16 0080 - 07FF
			//UTF-8  110xxxxx 10xxxxxx
			ret.push(
				//110xxxxx
				String.fromCharCode(0xc0 | ((code >> 6) & 0x1f)),
				//10xxxxxx
				String.fromCharCode(0x80 | (code & 0x3f))
			);
		}else if(code >= 0x800 && code <= 0xffff){
			//三字节
			//UTF-16 0800 - FFFF
			//UTF-8  1110xxxx 10xxxxxx 10xxxxxx
			ret.push(
				//1110xxxx
				String.fromCharCode(0xe0 | ((code >> 12) & 0xf)),
				//10xxxxxx
				String.fromCharCode(0x80 | ((code >> 6) & 0x3f)),
				//10xxxxxx
				String.fromCharCode(0x80 | (code & 0x3f))
			);
		}
	}
	
	return ret.join('');
}

function utf8ToUtf16(s){
	if(!s){
		return;
	}
	
	var i, codes, bytes, ret = [], len = s.length;
	for(i = 0; i < len; i++){
		codes = [];
		codes.push(s.charCodeAt(i));
		if(((codes[0] >> 7) & 0xff) == 0x0){
			//单字节  0xxxxxxx
			ret.push(s.charAt(i));
		}else if(((codes[0] >> 5) & 0xff) == 0x6){
			//双字节  110xxxxx 10xxxxxx
			codes.push(s.charCodeAt(++i));
			bytes = [];
			bytes.push(codes[0] & 0x1f);
			bytes.push(codes[1] & 0x3f);
			ret.push(String.fromCharCode((bytes[0] << 6) | bytes[1]));
		}else if(((codes[0] >> 4) & 0xff) == 0xe){
			//三字节  1110xxxx 10xxxxxx 10xxxxxx
			codes.push(s.charCodeAt(++i));
			codes.push(s.charCodeAt(++i));
			bytes = [];
			bytes.push((codes[0] << 4) | ((codes[1] >> 2) & 0xf));
			bytes.push(((codes[1] & 0x3) << 6) | (codes[2] & 0x3f));			
			ret.push(String.fromCharCode((bytes[0] << 8) | bytes[1]));
		}
	}
	return ret.join('');
}
</script>
</body>
</html>
