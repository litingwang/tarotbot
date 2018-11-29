<?php
/**tarot: 1
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
                    if ($m_message == 'exit') {
                        $user = new user($event['source'],$channelAccessToken);
                        $client->replyMessage(array(
                            'replyToken' => $event['replyToken'],
                            'messages' => array(
                                array(
                                    'type' => 'text',
                                    'text' => 'byebye'
                                )
                            )
                        ));

                        $user->bot_leave();

                    } elseif($m_message != false) {
                        if($event['source']['type'] != 'user') {
                            $user = new user($event['source'],$channelAccessToken);
                            $m_message = '@'.$user->get_user()."\n".$m_message;
                        }
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
class user {
    public function __construct($arr_user,$channelAccessToken)
    {
        $this->arr_user = $arr_user;
        $this->channelAccessToken = $channelAccessToken;
    }

    public function get_user() {
        if($this->arr_user['type'] == 'group'){
            $url_api = "https://api.line.me/v2/bot/group/".$this->arr_user['groupId']."/member/".$this->arr_user['userId'];

        } else {
            $url_api = "https://api.line.me/v2/bot/room/".$this->arr_user['roomId']."/member/".$this->arr_user['userId'];
        }
        $this->curl = new Curl();

        //$data_url, $data_type,$data_userpwd, $authorization
        $output = $this->curl->curl_get($url_api,'auth',false,$this->channelAccessToken);
        $arr_result = json_decode($output,true);
        return $arr_result['displayName'];
    }

    public function bot_leave() {
        if($this->arr_user['type'] == 'room') {
            $url_api ="https://api.line.me/v2/bot/room/".$this->arr_user['roomId']."/leave";
            $output = $this->curl->curl_post($url_api,'auth',false,$this->channelAccessToken);
            return true;
        }
        return false;
    }
}
class tarot {
    public function is_tarot_message($message) {
        if(preg_match('/tarot:[1-9]$/', $message) ) {
            $count = (int) substr($message, -1);
            return $this->get_tarot($count);
        } elseif (preg_match('/tarot:help$/', $message)) {
            $message = "請輸入tarot:1(張數)\n牌數範圍為1~9\n二擇一占卜請輸入tarot:choices";
            return $message;

        } elseif (preg_match('/tarot:choices$/', $message)) {
            return $this->get_choices();

        } elseif (preg_match('/tarot:bye$/', $message)) {
            return 'exit';

        } else {
            return false;
        }
    }

    public function get_tarot($count) {
        $this->curl = new Curl();
        $url_api = "http://www.tarot.keepfight.net/card.php?d=".$count;
        $output = $this->curl->curl_get($url_api);
        $str_number = strstr($output, '<center>');
        $str_number = strstr($str_number, '</center>',true);
        $str_number = preg_replace('/<center>您的編號是:\s/', '', $str_number);
        $r = strstr($output, '<input type="hidden" name="copy_card" value="');
        $r = strstr($r, '</form>',true);
        $r = str_replace('<input type="hidden" name="copy_card" value="', '', $r);
        $r = preg_replace('/\s">[\s]*/', '', $r);
        $r = str_replace('（正）', '(+)', $r);
        $r = str_replace('（逆）', '(-)', $r);
        $arr_r = preg_split("/[\s,]+/", $r);
        $message = '';
        $card_count = 1;
        foreach ($arr_r as $key => $value) {
            if (!preg_match("/^N\/A/", $value)) {
                if($count <= 3) {
                    $message .= $card_count .": ".$value." ";
                } else {
                    $message .= $card_count .": ".$value." \n";
                }
                $card_count = $card_count+1;
            }
        }
        
        return  $message." \nhttp://tarot.keepfight.net/see.php?sn=".$str_number;
    }
    public function get_choices() {
        $this->curl = new Curl();
        $url_api = "http://www.tarot.keepfight.net/card.php?d=5";
        $output = $this->curl->curl_get($url_api);
        $str_number = strstr($output, '<center>');
        $str_number = strstr($str_number, '</center>',true);
        $str_number = preg_replace('/<center>您的編號是:\s/', '', $str_number);
        $r = strstr($output, '<input type="hidden" name="copy_card" value="');
        $r = strstr($r, '</form>',true);
        $r = str_replace('<input type="hidden" name="copy_card" value="', '', $r);
        $r = preg_replace('/\s">[\s]*/', '', $r);
        $r = str_replace('（正）', '(+)', $r);
        $r = str_replace('（逆）', '(-)', $r);
        $arr_r = preg_split("/[\s,]+/", $r);
        $arr_message = array(
            '問卜者的心態：',
            'A 的前期狀況：',
            'A 的結果：',
            'B 的前期狀況：',
            'B 的結果：'
        );
        $message = '';
        $card_count = 0;
        foreach ($arr_r as $key => $value) {
            if (!preg_match("/^N\/A/", $value)) {
                $message .= $arr_message[$card_count] .$value." \n";
                if($card_count ==2) {
                    $message .="\n";
                }
                $card_count = $card_count+1;
            }
        }
        return $message." \nhttp://tarot.keepfight.net/see.php?sn=".$str_number;
    }
}