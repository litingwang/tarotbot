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
                	if($m_message != false)
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
            return false;
        }
    }

    function get_tarot($count) {
        $this->curl = new Curl();
        $url_api = "http://www.tarot.keepfight.net/card.php?d=".$count;
        // $output = $this->curl->curl_get($url_api);

        $output = '<html>
    <head>
        <title>Tarot</title>
        <meta http-equiv="cache-control" content="no-cache">
        <meta http-equiv="pragma" content="no-cache">
        <meta http-equiv="expires" content="0">
        <style type="text/css">
            <!--
.draw { float:left; padding: 10px; width:120px; text-align: center; }
-->
        </style>
    </head>
    <body>
        <table align="center" cellspacing="1">
            <tr>
                <td bgcolor="#FFFFFF">
                    <div class=\'draw\'>
                        <img src=\'pic/m00.jpg\' alt=\'\'/>愚人（正）
                    </div>
                    <div class=\'draw\'>
                        <img src=\'pic/s03.jpg\' alt=\'\'/>劍三（正）
                    </div>
                    <div class=\'draw\'>
                        <img src=\'pic/xm01.jpg\' alt=\'\'/>魔術師（逆）
                    </div>
                    <div class=\'draw\'>
                        <img src=\'pic/xs08.jpg\' alt=\'\'/>劍八（逆）
                    </div>
                    <div class=\'draw\'>
                        <img src=\'pic/xc06.jpg\' alt=\'\'/>杯六（逆）
                    </div>
                </td>
            </tr>
            <form>
                <input type="hidden" name="copy_card" value="愚人（正） 劍三（正） 魔術師（逆） 劍八（逆） 杯六（逆） ">
            </form>
            <center>您的編號是: 26774021</center>
            <script>
x = document.all.copy_card.createTextRange();
x.execCommand("Copy");
</script>
            <tr>
                <td>
                    <div align="center">
                        <a href="javascript:this.location.reload()" target="_self">
                            <img src="pic/reload.gif" width="280" height="80" border="0" align="absmiddle">
                        </a>
                    </div>
                </td>
            </tr>
        </table>
        <div align="center" style="color:#AAAAAA;">Ps: 如果您是使用 Internet Explorer, 此網址已經自動置入剪貼簿當中囉!
            <br />
將可在軟體中直接使用 Ctrl+V 貼上即可!
        </div>
    </body>
</html>';
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
        foreach ($arr_r as $key => $value) {
            if(!preg_match("/^N\/A/", $value)) {
                $message .= $value." ";
            }
        }
        
        return  $message." ".$str_number;
    }
}