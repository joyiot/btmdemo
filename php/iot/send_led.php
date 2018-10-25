<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>JoyPub发送BTM交易处理结果</title>
<?php
/* PPK JoyPub DEMO baes Bytom Blockchain */
/* Send BTM Transaction */
require_once "ppk_joyiot.inc.php";

$asset_id=$_REQUEST['asset_id'];
$trans_data_hex = $_REQUEST['trans_data_hex'];
if( strlen($asset_id)==0 || strlen($trans_data_hex)==0){
  echo '无效输入 Invalid Input!';
  exit(-1);
}

$current_account_info=getNextAccountInfo();

$tmp_url=BTM_NODE_API_URL.'build-transaction';
$tmp_post_data='{
  "base_transaction": null,
  "actions": [
    {
      "account_id": "'.$current_account_info['id'].'",
      "amount": '.TX_GAS_AMOUNT_mBTM.'00000,
      "asset_id": "ffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff",
      "type": "spend_account"
    },
    {
      "account_id": "'.$current_account_info['id'].'",
      "amount": 1,
      "asset_id": "'.$asset_id.'",
      "type": "spend_account"
    },
    {
      "amount": 1,
      "asset_id": "'.$asset_id.'",
      "arbitrary": "'.$trans_data_hex.'",
      "type": "retire"
    }
  ],
  "ttl": 0,
  "time_range": '.time().'
  
}';

$obj_resp=sendBtmTransaction($tmp_post_data,$current_account_info);

if(strcmp($obj_resp['status'],'success')!==0){
    echo "发送比原交易失败，请稍候重试！Failed to send transaction to Bytom blockchain!\n",json_encode($obj_resp);
    echo "Debug Account:", $current_account_info['id'];
    exit(-1);
}

echo '发送比原交易成功，交易ID：<a href="https://blockmeta.com/tx/',$obj_resp['data']['tx_id'],'" target="_blank">',$obj_resp['data']['tx_id'],'</a>',"<br><br>\n";
echo '请等待2-3分钟得到比原链出块确认，返回<a href="./">主页</a>查看。';
