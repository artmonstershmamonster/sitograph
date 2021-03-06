<?php
// load default handler to process common functions
$handlerPath = ABS_MODULE."/sitograph/module-table.php";
if (file_exists($handlerPath)) {
    include($handlerPath);
} else {
    msv_message_error("Module handler file not found <b>$handlerPath</b>");
}
// USERS custom functions
if (!empty($_REQUEST["send_to_crm"])) {
    $resultCheck = db_get(TABLE_FUNNEL_LEADS,' `id` = '.$_REQUEST["send_to_crm"]);
    if (count($resultCheck["data"]) === 0) {
        msv_message_error($resultCheck["msg"]);
    } else {
        
        $lead_data = $resultCheck['data'];

        //var_dump($lead_data);

        $result = api_funnels_crm_lead_send($lead_data);
        //echo "<pre>";
        //var_dump($result);
        //echo "</pre>";
        

        if ($result['ok'] && !empty($result["data"])) {
            msv_message_ok('ok');
            $rowUpd = array();

            $rowUpd['crm_member_id'] = $result['data']['crm_member_id'];
            $rowUpd['crm_lead_id'] = $result['data']['crm_lead_id'];

            $rowUpd['id'] = $lead_data['id'];
            $rowUpd['status'] = 1;
            $rowUpd['status_info'] = $result["msg"] ? $result["msg"] : 'CREAT';
            $rowUpd['published'] = 0;
            
            $resultUpdateLead = db_update_row(TABLE_FUNNEL_LEADS, $rowUpd);

            if (!empty($rowUpd['crm_member_id'])) {
                $rowUpdUser = array();
                $rowUpdUser['crm_id'] = $rowUpd['crm_member_id'];
                $rowUpdUser['id'] = $lead_data['member_user_id'];

                $resultUpdateUser = db_update_row(TABLE_USERS, $rowUpdUser);

            }
            //echo "<pre>";
            //var_dump($resultUpdateUser);


        } else {
            $rowUpd = array(
                "id" => $lead_data['id'],
                "status_info" => $result["msg"],
            );

            db_update_row(TABLE_FUNNEL_LEADS, $rowUpd);
            //var_dump($lead_data);
            msv_message_error($result["msg"]);
        }
        
    }
}