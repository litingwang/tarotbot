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
require_once('./Curl.php');


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
            $count = preg_replace('/tarot:/', '', $message);
            $count = substr($count, 0,1);

            return $this->get_tarot($count);
        } else {
            return '參數錯誤:'.$message;
        }
    }

    function get_tarot($count) {
        $this->curl = new Curl();
        $url_api = "http://www.tarot.keepfight.net/card.php?d=".$count;
        $output = $this->curl->curl_get($url_api);

        $str_number = strstr($output, '<center>');
        $str_number = strstr($str_number, '</center>',true);
        $str_number = preg_replace('<center>您的編號是: ', '', $str_number);

        // $sn = 26713693;
        // $url_api = "http://tarot.keepfight.net/see.php?sn=".$sn;
        // $output =  $this->curl->curl_get($url_api);

        // $r = strstr($output, '<form>');
        // $r = strstr($r, '</form>',true);
        // $r = str_replace('<form><input type="hidden" name="copy_card" value="', '', $r);
        // $r = str_replace(' ">', '', $r);
        // $r = str_replace('（正）', '(+)', $r);
        // $r = str_replace('（逆）', '(-)', $r);
        // $arr_r = preg_split("/[\s,]+/", $r);
        // $message = '';
        // foreach ($arr_r as $key => $value) {
        //     if(!preg_match("/^N\/A/", $value)) {
        //         $message.= $value." ";
        //     }
        // }
        return "http://tarot.keepfight.net/see.php?sn=".$str_number;
    }
}