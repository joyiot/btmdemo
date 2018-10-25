<?php
//ini_set("display_errors", "On"); 
//error_reporting(E_ALL | E_STRICT);

/*
  Response the game record in bytom transaction  
    PPkPub.org   20180915
  Released under the MIT License.
*/
require 'ap.common.inc.php';

define('JOYGUESS_BYTOM_TX_ODIN_PREFIX', PPK_URI_PREFIX.'527064.583/guessgame/bytom/' ); //含有猜图数据的比原交易对应的ODIN标识前缀
//ppk:JOY/guessgame/bytom/773958009d8b15ad6582aa727557b4a3b58b0c00c3e785d2a6cd5ee75e73105d
define('JOYPUB_BYTOM_TX_ODIN_PREFIX', PPK_URI_PREFIX.'527064.583/pub/bytom/' ); // 含有趣吧数据的比原交易对应的ODIN标识前缀

//在URL中指定例如 ?pttp_interest={"ver":1,"interest":{"uri":"ppk:513468.490/"}}  
//view-source:http://btmdemo.ppkpub.org/joy/ap/bytom_tx.php?pttp_interest={"ver":1,"interest":{"uri":"ppk:527064.583/pub/bytom/a48a26eff5c09510e79c6690e2dc860392794cd9c26ea8678b505d0501b659b3#"}}  
//view-source:http://btmdemo.ppkpub.org/joy/ap/bytom_tx.php?pttp_interest=%7B%22ver%22%3A1%2C%22interest%22%3A%7B%22uri%22%3A%22ppk%3A527064.583%2Fpub%2Fbytom%2Fc0aed9bd5b984a882bf4b6e569d4578d8f168495fb9958f818bdefff9080a0a7%23%22%7D%7D
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

if( 0==strncasecmp($str_pttp_uri,JOYGUESS_BYTOM_TX_ODIN_PREFIX,strlen(JOYGUESS_BYTOM_TX_ODIN_PREFIX))
  ){
  $str_flag = PPK_JOY_FLAG;
  $str_bytom_tx_odin_prefix=JOYGUESS_BYTOM_TX_ODIN_PREFIX;
}else if( 0==strncasecmp($str_pttp_uri,JOYPUB_BYTOM_TX_ODIN_PREFIX,strlen(JOYPUB_BYTOM_TX_ODIN_PREFIX))
  ){
  $str_flag = PPK_JOYPUB_FLAG;
  $str_bytom_tx_odin_prefix=JOYPUB_BYTOM_TX_ODIN_PREFIX;
}else{
  respPttpStatus4XX( '400',"Bad Request : Invalid joyblock-bytom-tx-uri " );
  exit(-1);
}

$odin_chunks=array();
$parent_odin_path="";
$resource_id="";
$req_resource_versoin="";
$resource_filename="";

$tmp_chunks=explode("#",substr($str_pttp_uri,strlen($str_bytom_tx_odin_prefix)));
$parent_odin_path="";
$bytom_tx_id=$tmp_chunks[0];

if(count($tmp_chunks)>=2){
  $req_resource_versoin=$tmp_chunks[1];
}

//echo "str_pttp_uri=$str_pttp_uri\n";
//echo "parent_odin_path=$parent_odin_path , resource_id=$resource_id , req_resource_versoin=$req_resource_versoin \n";
//echo "bytom_tx_id=$bytom_tx_id\n";

$tmp_tx_data=getBtmTransactionDetail($bytom_tx_id);
//print_r($tmp_tx_data);
if($tmp_tx_data==null){
  respPttpStatus4XX( '404',"Bad Request : resource not exists. " );
  exit(-1);
} 

$str_hex=parseSpecHexFromBtmTransaction($tmp_tx_data,$str_flag);

if(strlen($str_hex)>0){
  $str_resp_content=hexToStr($str_hex);
}else{
  $str_resp_content="";
}

$str_content_type='text/json';
$resp_resource_versoin='1';//区块链上的交易数据版本缺省都是1

$str_resp_uri=$str_bytom_tx_odin_prefix.$bytom_tx_id."#".$resp_resource_versoin;

$str_pttp_data=generatePTTPData( $str_resp_uri,'200','OK',$str_content_type,$str_resp_content );

//输出数据正文
header('Content-Type: text/json');
echo $str_pttp_data;
