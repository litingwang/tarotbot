<?php

/**
 * Copyright 2016 LINE Corporation
 *
 * LINE Corporation licenses this file to you under the Apache License,
 * version 2.0 (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at:
 *
 *   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

require_once('./LINEBotTiny.php');



$channelAccessToken = getenv('LINE_CHANNEL_ACCESSTOKEN');
$channelSecret = getenv('LINE_CHANNEL_SECRET');

$client = new LINEBotTiny($channelAccessToken, $channelSecret);
$tarot = new tarot;
foreach ($client->parseEvents() as $event) {
    switch ($event['type']) {
        case 'message':
            $message = $event['message'];
            switch ($message['type']) {
                case 'text':
                	$m_message = $tarot->is_tarot_message( $message['text'] );
                	if($m_message!="")
                	{
                		$client->replyMessage(array(
                        'replyToken' => $event['replyToken'],
                        'messages' => array(
                            array(
                                'type' => 'text',
                                'text' => $m_message
                            )
                        )
                    	));
                	}
                    break;
                
            }
            break;
        default:
            error_log("Unsupporeted event type: " . $event['type']);
            break;
    }
};

class tarot {
    function is_tarot_message($message) {
        if(preg_match('/tarot:[0-5]/', $message) ) {
            $count = strstr($message, 'tarot:');
            $count = mb_ereg_replace('tarot:', '', $count);
            $count = (int) substr($count, 0,1);

            return $this->get_tarot($count);
        } else {
            return $message;
        }
    }

    function get_tarot($count) {
        $url_api = "http://www.tarot.keepfight.net/card.php?d=".$count;
        $this->load->library('curl');
        $output =  $this->curl->simple_get($url_api);

        $str_number = strstr($output, '<center>');
        $str_number = strstr($str_number, '</center>',true);
        $str_number = mb_ereg_replace('<center>您的編號是: ', '', $str_number);
        

        $message = 'http://tarot.keepfight.net/see.php?sn='.$str_number;
        return $message;
    }

}