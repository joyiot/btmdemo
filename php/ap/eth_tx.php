<?php
ini_set("display_errors", "On"); 
error_reporting(E_ALL | E_STRICT);

/*
  Response the game record in bytom transaction  
    PPkPub.org   20180915
  Released under the MIT License.
*/
require 'ap.common.inc.php';

define('JOYPUB_ETH_RINKEBY_TX_ODIN_PREFIX', PPK_URI_PREFIX.'527064.583/pub/eth-rinkeby/' );// 含有趣吧数据的以太坊rinkeby测试网络交易对应的ODIN标识前缀
define('JOYPUB_ETH_MAINNET_TX_ODIN_PREFIX', PPK_URI_PREFIX.'527064.583/pub/eth/' );// 含有趣吧数据的以太坊主网交易对应的ODIN标识前缀

//在URL中指定例如 ?pttp_interest={"ver":1,"interest":{"uri":"ppk:513468.490/"}}  
//view-source:http://btmdemo.ppkpub.org/joy/ap/bytom_tx.php?pttp_interest={"ver":1,"interest":{"uri":"ppk:JOY/pub/eth-rinkeby/0x003357d7c5b5ecb70d3b18f3bc7fc5e162579377982fd4e6b223ab93a4c8c490#"}}  
//view-source:http://btmdemo.ppkpub.org/joy/ap/bytom_tx.php?pttp_interest=%7B%22ver%22%3A1%2C%22interest%22%3A%7B%22uri%22%3A%22ppk%3A527064.583%2Fpub%2Feth-rinkeby%2F0x003357d7c5b5ecb70d3b18f3bc7fc5e162579377982fd4e6b223ab93a4c8c490%23%22%7D%7D
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

require_once "../pub/ppk_joypub.inc.php";


if( 0==strncasecmp($str_pttp_uri,JOYPUB_ETH_RINKEBY_TX_ODIN_PREFIX,strlen(JOYPUB_ETH_RINKEBY_TX_ODIN_PREFIX))
  ){
  $str_flag = PPK_JOYPUB_FLAG;
  $str_eth_net_id = 'rinkeby';
  $str_bytom_tx_odin_prefix=JOYPUB_ETH_RINKEBY_TX_ODIN_PREFIX;
}else if( 0==strncasecmp($str_pttp_uri,JOYPUB_ETH_MAINNET_TX_ODIN_PREFIX,strlen(JOYPUB_ETH_MAINNET_TX_ODIN_PREFIX))
  ){
  $str_flag = PPK_JOYPUB_FLAG;
  $str_eth_net_id = 'mainnet';
  $str_bytom_tx_odin_prefix=JOYPUB_ETH_MAINNET_TX_ODIN_PREFIX;
}else{
  respPttpStatus4XX( '400',"Bad Request : Invalid joyblock-eth-tx-uri " );
  exit(-1);
}

$odin_chunks=array();
$parent_odin_path="";
$resource_id="";
$req_resource_versoin="";
$resource_filename="";

$tmp_chunks=explode("#",substr($str_pttp_uri,strlen($str_bytom_tx_odin_prefix)));
$parent_odin_path="";
$eth_tx_id=$tmp_chunks[0];

if(count($tmp_chunks)>=2){
  $req_resource_versoin=$tmp_chunks[1];
}

//echo "str_pttp_uri=$str_pttp_uri\n";
//echo "parent_odin_path=$parent_odin_path , resource_id=$resource_id , req_resource_versoin=$req_resource_versoin \n";
//echo "eth_tx_id=$eth_tx_id\n";

$tmp_tx_data=getEthTransactionDetail($str_eth_net_id,$eth_tx_id);
//print_r($tmp_tx_data);
if($tmp_tx_data==null){
  respPttpStatus4XX( '404',"Bad Request : resource not exists. " );
  exit(-1);
} 

$tmp_input=$tmp_tx_data['input'];
$str_flag_hex=strtohex($str_flag);
$flag_posn=strpos($tmp_input,$str_flag_hex);
//echo 'flag_posn=',$flag_posn;
if($flag_posn>0){ //符合特征
  $str_hex=substr($tmp_input,$flag_posn+strlen($str_flag_hex));
}


if(strlen($str_hex)>0){
  $str_resp_content=hexToStr($str_hex);
}else{
  $str_resp_content="";
}

$str_content_type='text/json';
$resp_resource_versoin='1';//区块链上的交易数据版本缺省都是1

$str_resp_uri=$str_bytom_tx_odin_prefix.$eth_tx_id."#".$resp_resource_versoin;

$str_pttp_data=generatePTTPData( $str_resp_uri,'200','OK',$str_content_type,$str_resp_content );

//输出数据正文
header('Content-Type: text/json');
echo $str_pttp_data;


//获取以太坊交易详情
function getEthTransactionDetail($network_id,$tx_id){
  $tmp_url='https://api.infura.io/v1/jsonrpc/'.$network_id.'/eth_getTransactionByHash?params=[%22'.$tx_id.'%22]';
  
  $obj_resp=@json_decode(file_get_contents($tmp_url),true);

  if( isset($obj_resp) && isset($obj_resp['result']) ){
    return $obj_resp['result'];
  }
  
  return null;
}