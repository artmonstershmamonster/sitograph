<?php

/**
 * Register new leads
 * Database table: TABLE_FUNNELS_LEADS
 *
 * checks for required fields and correct values
 * $row["email"] is required, has to be valid email and not used
 * unverified emails will receive link to confirm email
 * message will be generated in case if email was sent
 *
 * @param array $row Associative array with data to be inserted
 * @param array $options Optional list of flags. Supported: EmailNotifyUser, EmailNotifyAdmin
 * @return array Result of a API call
 */
function api_funnels_lead_add($row, $options = array()) {
    $result = array(
        "ok" => false,
        "data" => array(),
        "msg" => "",
    );

    if (empty($row["member_email"])) {
        $result["msg"] = _t("msg.users.noemail");
        return $result;
    } elseif (!msv_check_email($row["member_email"])) {
        $result["msg"] = _t("msg.wrong_email");
        return $result;
    }

    if (empty($row["funnel_forms_id"])) {
        $result["msg"] = _t("msg.funnels.funnel_forms_id_exists");
        return $result;
    } else {
        $row["funnel_forms_id"] = (int)$row["funnel_forms_id"];
    }

    if (!empty($row["utm"])) {
        $utm_arr = json_decode($row["utm"], true);

        $row["utm_content"] = $utm_arr["utm_content"];
        $row["utm_medium"] = $utm_arr["utm_medium"];
        $row["utm_source"] = $utm_arr["utm_source"];
        $row["utm_term"] = $utm_arr["utm_term"];
        $row["utm_campaign"] = $utm_arr["utm_campaign"];
    }

    $resultCheckUser = db_get(
        TABLE_USERS,
        " `email` = '".db_escape($row["member_email"])."'"
    );

    if ($resultCheckUser["ok"] && !empty($resultCheckUser["data"])) {
        $userResult = $resultCheckUser["data"];
    } else {
        $userRow = array(
            "email" => $row["member_email"],
            "name" => $row["member_name"],
            "phone" => $row["member_phone"],
            "email_verified" => 1,
            "iss" => 'funnels',
        );

        $userResult = msv_add_user($userRow, array("EmailNotifyAdmin"));
        $userResult = $userResult["data"];
    }

    $row["member_user"] = $userResult["email"];
    $row["member_user_id"] = $userResult["id"];

    if (!empty($userResult["crm_id"])) {
        $row["crm_member_id"] = $userResult["crm_id"];
    }

    $resultCheck = db_get(
        TABLE_FUNNEL_LEADS,
        " `member_email` = '".db_escape($row["member_email"]).
        "' AND  `funnel_forms_id` = '".$row["funnel_forms_id"]."'"
    );

    if ($resultCheck["ok"] && !empty($resultCheck["data"])) {
        $result["msg"] = _t("msg.users.email_exists");
        return $result;
    }

    $resultCheckLeads = db_get(
        TABLE_FUNNEL_LEADS,
        " `member_email` = '".db_escape($row["member_email"]).
        "' AND  `funnels_id` = '".$row["funnels_id"]."' AND  `published` = '1'"
    );

    if ($resultCheckLeads["ok"] && !empty($resultCheckLeads["data"]["crm_lead_id"])) {
        $row["crm_lead_id"] = (int)$resultCheckLeads["data"]["crm_lead_id"];
    } 

    if (empty($resultCheckLeads["data"]["crm_lead_id"]) && $resultCheckLeads["data"]["status"] == "0" ) {
        $rowUpd = array(
                "id" => $resultCheckLeads["data"]['id'],
                "published" => 0,
            );
        $resultUpdateLead = db_update_row(TABLE_FUNNEL_LEADS, $rowUpd);
    }

    if (empty($row["published"])) {
        $row["published"] = 1;
    } else {
        $row["published"] = (int)$row["published"];
    }

    $result = db_add(TABLE_FUNNEL_LEADS, $row, "*");

    if ($result["ok"]) {
        $result["msg"] = _t("msg.funnels.funnel_leads_saved");
    }

    return $result;
}

function sendAmo($data = false) {

    $result = array(
        "ok" => false,
        "data" => array(),
        "msg" => "Ошибка запроса",
    );

    $path_amolib = ABS_INCLUDE."/custom/AmoCRM/amocrm.phar";
    require_once ($path_amolib);

    try {
        
        $amo = new \AmoCRM\Client(
            'karla',
            'alexrreva@gmail.com',
            'f8e25bc0dc1e21e565152c0baff799f2a7979362'
        );

        $result_data = array();

        $lead = $amo->lead;
        //$lead->debug(true);
        $lead['name'] = 'Сделка #' . $data['lead_id'];
        $lead['status_id'] = $data['lead_status_id'];
        $lead['price'] = $data['lead_sale'] ? (int)$data['lead_sale'] : 0;

        if (!empty($data['crm_lead_id']) )  {

            $responce_l = $lead->apiUpdate((int)$data['crm_lead_id'], 'now');
            $responce_lead = (int)$data['crm_lead_id'];

        } else {

            $lead['responsible_user_id'] = 0;
            $lead['date_create'] = $data['date_create'];
            $lead['tags'] = $data['lead_utm_source'] ? $data['lead_utm_source'] : $data['lead_name'];
            
            $lead->addCustomField(471125, $data['lead_id']);

            if ($data['lead_utm']) {

                $lead->addCustomField(460415, $data['lead_utm_source']);
                $lead->addCustomField(460417, $data['lead_utm_campaign']);
                $lead->addCustomField(460419, $data['lead_utm_medium']);
                $lead->addCustomField(460421, $data['lead_utm_content']);
                $lead->addCustomField(460423, $data['lead_utm_term']);

                $lead->addCustomField(460431, $data['lead_utm']);
            }

            $responce_lead = $lead->apiAdd();

        }

        $contact = $amo->contact;
        //$contact->debug(true);

        $contact['name'] = $data['contacts_name'];
        $contact['linked_leads_id'] = $responce_lead;

        if ($data['contacts_phone']) {

            $contact->addCustomField(266644, [[$data['contacts_phone'], 'WORK']]);

        }

        if ($data['crm_contacts_id']) {

            $contact['last_modified'] = $data['last_modified'];

            $responce_c = $contact->apiUpdate((int)$data['crm_contacts_id'], $data['last_modified']);
            $responce_contact = (int)$data['crm_contacts_id'];
        } else {

            $contact['date_create'] = $data['date_create'];
        
            $responce_c = $contact->addCustomField(266646, [[$data['contacts_email'], 'WORK']]);

            $responce_contact = $contact->apiAdd();

            

        }

        
        $result_data['crm_lead_id'] = $responce_lead;
        $result_data['crm_member_id'] = $responce_contact;

        $result = array(
            "ok" => true,
            "data" => $result_data,
            "msg" => $responce_l,
        );

    }

    catch (\AmoCRM\Exception $e) {
        $err = $e->getCode(). ' : ' .$e->getMessage();
        $result['msg'] = $err;
    }

    return $result;
}

function api_funnels_crm_lead_send_all() {
    $result = array(
        "ok" => false,
        "data" => array(),
        "msg" => "Ошибка запроса",
    );

    $resultQuery = db_get_list(
        TABLE_FUNNEL_LEADS, 
        "`status` = 0", 
        "`date` desc", 
        "10", 
        ""
    );

    if ($resultQuery["ok"]) {
            $list_lead = array();
            foreach ($resultQuery["data"] as $item) {
                $resultFunnelForms = db_get(TABLE_FUNNEL_FORMS,' `id` = '.$item['funnel_forms_id']);
                if ($resultFunnelForms['ok']) {
                    $item['funnel_forms_data'] = $resultFunnelForms['data'];
                }
                if ($item['funnel_forms_data']['crm_lead_method']) {
                    $list_lead[] = $item;
                }
            }
            foreach ($list_lead as $item) {
                api_funnels_crm_lead_send($item);
            }
        }

    return $result;
}

function api_funnels_crm_lead_send($row) {

    $resultFunnels = db_get(TABLE_FUNNELS,' `id` = '.$row['funnels_id']);
        if ($resultFunnels['ok']) {
            $row['funnels_data'] = $resultFunnels['data'];
        }
    $resultFunnelForms = db_get(TABLE_FUNNEL_FORMS,' `id` = '.$row['funnel_forms_id']);
        if ($resultFunnelForms['ok']) {
            $row['funnel_forms_data'] = $resultFunnelForms['data'];
        }

    $result = array(
        "ok" => false,
        "data" => array(),
        "msg" => "Ошибка запроса",
    );

    $send_data = array(

        'lead_method' => $row['funnel_forms_data']['crm_lead_method'],

        'lead_id' => $row['id'],
        'lead_status_id' => $row['funnel_forms_data']['pipeline_status_id'],
        'lead_name' => $row['funnel_forms_data']['title'],
        'lead_sale' => $row['funnel_forms_data']['price'],
        'lead_curency' => $row['funnel_forms_data']['сurrency'],
        

        'lead_utm' => $row['utm'],     
        'lead_utm_source' => $row['utm_source'],
        'lead_utm_campaign' => $row['utm_campaign'],
        'lead_utm_content' => $row['utm_content'],
        'lead_utm_term' => $row['utm_term'],
        'lead_utm_medium' => $row['utm_medium'],


        'contacts_name' => $row['member_name'],
        'contacts_email' => $row['member_email'],
        'contacts_phone' => $row['member_phone'],

        'crm_contacts_id' => $row['crm_member_id'],

        'crm_lead_id' => $row['crm_lead_id'],

        'date_create' => $row['date'],
        'last_modified' => $row['updated'],

    );

    $result = sendAmo($send_data);
    
    return $result;
}