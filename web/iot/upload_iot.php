<?php
/* PPK JoyPub DEMO baes Bytom Blockchain */
/* Send BTM Transaction in AJAX mode */
require_once "ppk_joyiot.inc.php";

$iot_pttp_data = $_REQUEST['iot_pttp_data'];
if(strlen($iot_pttp_data)==0){
  echo '{"status":"fail","code":"PPK001","msg":"无效输入 Invalid Input!","error_detail":"无效输入 Invalid Input!"}';
  exit(-1);
}
$iot_trans_data_hex = strToHex(PPK_JOYIOT_FLAG.$iot_pttp_data);

$current_account_info = getNextAccountInfo();

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
      "asset_id": "'.JOYBLOCK_TOEKN_ASSET_ID.'",
      "type": "spend_account"
    },
    {
      "amount": 1,
      "asset_id": "'.JOYBLOCK_TOEKN_ASSET_ID.'",
      "arbitrary": "'.$iot_trans_data_hex.'",
      "type": "retire"
    }
  ],
  "ttl": 0,
  "time_range": '.time().'
  
}';

$obj_resp=sendBtmTransaction($tmp_post_data,$current_account_info);

echo json_encode($obj_resp);
