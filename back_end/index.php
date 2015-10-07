<?php
/*

    Kenny's Rework http://www.kennilun.com/
    CopyRight 2015 All Rights Reserved
*/

define("TOKEN", "YOUR TOKEN");

$wechatObj = new wechatCallbackapiTest();
if (!isset($_GET['echostr'])) {
    $wechatObj->responseMsg();
}else{
    $wechatObj->valid();
}

class wechatCallbackapiTest
{
    //验证签名
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if($tmpStr == $signature){
            echo $echoStr;
            exit;
        }
    }

    //响应消息
    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)){
            $this->logger("R \r\n".$postStr);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);

            if (($postObj->MsgType == "event") && ($postObj->Event == "subscribe" || $postObj->Event == "unsubscribe")){
                //过滤关注和取消关注事件
            }else{
                
            }
            
            //消息类型分离
            switch ($RX_TYPE)
            {
                case "event":
                    $result = $this->receiveEvent($postObj);
                    break;
                case "text":
                   if (strstr($postObj->Content, "第三方")){
                                              $result = $this->receiveText($postObj);
                      //  $result = $this->relayPart3("http://www.fangbei.org/test.php".'?'.$_SERVER['QUERY_STRING'], $postStr);
                    }else{
                        $result = $this->receiveText($postObj);
                    }
                    break;
                case "image":
                    $result = $this->receiveImage($postObj);
                    break;
                case "location":
                    $result = $this->receiveLocation($postObj);
                    break;
                case "voice":
                    $result = $this->receiveVoice($postObj);
                    break;
                case "video":
                    $result = $this->receiveVideo($postObj);
                    break;
                case "link":
                    $result = $this->receiveLink($postObj);
                    break;
                default:
                    $result = "unknown msg type: ".$RX_TYPE;
                    break;
            }
            $this->logger("T \r\n".$result);
            echo $result;
        }else {
            echo "";
            exit;
        }
    }

    //接收事件消息
    private function receiveEvent($object)
    {
        $content = "";
        switch ($object->Event)
        {
            case "subscribe":
                $content = "欢迎关注刘姗姗和她们的装置.装置正在制造中，目前装置的主要功能就是随机进行shuffle 你发我一个语音或者图片试试吧 目前不支持小视频。另外：我还加入了一个小装置 输入 装置 或者 zb查看";
                $content .= (!empty($object->EventKey))?("\n来自二维码场景 ".str_replace("qrscene_","",$object->EventKey)):"";
                break;
            case "unsubscribe":
                $content = "取消关注";
                break;
            case "CLICK":
                switch ($object->EventKey)
                {
                    case "COMPANY":
                        $content = array();
                        $content[] = array("Title"=>"刘姗姗工作室", "Description"=>"", "PicUrl"=>"http://discuz.comli.com/weixin/weather/icon/cartoon.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
                        break;
                    default:
                        $content = "点击菜单：".$object->EventKey;
                        break;
                }
                break;
            case "VIEW":
                $content = "跳转链接 ".$object->EventKey;
                break;
            case "SCAN":
                $content = "扫描场景 ".$object->EventKey;
                break;
            case "LOCATION":
                $content = "上传位置：纬度 ".$object->Latitude.";经度 ".$object->Longitude;
                break;
            case "scancode_waitmsg":
                if ($object->ScanCodeInfo->ScanType == "qrcode"){
                    $content = "扫码带提示：类型 二维码 结果：".$object->ScanCodeInfo->ScanResult;
                }else if ($object->ScanCodeInfo->ScanType == "barcode"){
                    $codeinfo = explode(",",strval($object->ScanCodeInfo->ScanResult));
                    $codeValue = $codeinfo[1];
                    $content = "扫码带提示：类型 条形码 结果：".$codeValue;
                }else{
                    $content = "扫码带提示：类型 ".$object->ScanCodeInfo->ScanType." 结果：".$object->ScanCodeInfo->ScanResult;
                }
                break;
            case "scancode_push":
                $content = "扫码推事件";
                break;
            case "pic_sysphoto":
                $content = "系统拍照";
                break;
            case "pic_weixin":
                $content = "相册发图：数量 ".$object->SendPicsInfo->Count;
                break;
            case "pic_photo_or_album":
                $content = "拍照或者相册：数量 ".$object->SendPicsInfo->Count;
                break;
            case "location_select":
                $content = "发送位置：标签 ".$object->SendLocationInfo->Label;
                break;
            default:
                $content = "receive a new event: ".$object->Event;
                break;
        }

        if(is_array($content)){
            if (isset($content[0]['PicUrl'])){
                $result = $this->transmitNews($object, $content);
            }else if (isset($content['MusicUrl'])){
                $result = $this->transmitMusic($object, $content);
            }
        }else{
            $result = $this->transmitText($object, $content);
        }
        return $result;
    }

    //接收文本消息
    private function receiveText($object)
    {
        //首先先回复客户 以防止五秒钟锁死    
        $keyword = trim($object->Content);
        //多客服人工回复模式
        if (strstr($keyword, "请问在吗") || strstr($keyword, "在线客服")){
            $result = $this->transmitService($object);
            return $result;
        }

        //自动回复模式
        if (strstr($keyword, "文本")){
            $content = "这是个文本消息";
        }else if (strstr($keyword, "表情")){
            $content = "中国：".$this->bytes_to_emoji(0x1F1E8).$this->bytes_to_emoji(0x1F1F3)."\n仙人掌：".$this->bytes_to_emoji(0x1F335);
        }else if (strstr($keyword, "装置")||strstr($keyword, "zb")){
            $content = array();
            $content[] = array("Title"=>"一个简单的乐器装置",  "Description"=>"根据公共号数据库多触点的装置", "PicUrl"=>"http://www.kennilun.com/shanshanliu/granular-gh-pages/zb.jpg", "Url" =>"http://www.kennilun.com/shanshanliu/granular-gh-pages/");
        }else if (strstr($keyword, "图文") || strstr($keyword, "多图文")){
            $content = array();
            $content[] = array("Title"=>"多图文1标题", "Description"=>"", "PicUrl"=>"http://discuz.comli.com/weixin/weather/icon/cartoon.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
            $content[] = array("Title"=>"多图文2标题", "Description"=>"", "PicUrl"=>"http://d.hiphotos.bdimg.com/wisegame/pic/item/f3529822720e0cf3ac9f1ada0846f21fbe09aaa3.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
            $content[] = array("Title"=>"多图文3标题", "Description"=>"", "PicUrl"=>"http://g.hiphotos.bdimg.com/wisegame/pic/item/18cb0a46f21fbe090d338acc6a600c338644adfd.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
        }else if (strstr($keyword, "音乐")){
            $content = array();
            $content = array("Title"=>"最炫太空民族风", "Description"=>"歌手：石羊传奇", "MusicUrl"=>"http://www.kennilun.com/shanshanliu/music/space_disco.mp3", "HQMusicUrl"=>"http://www.kennilun.com/shanshanliu/music/space_disco.mp3"); 
        }else if (strstr($keyword, "女神")){
            $content = array();
            $content = array("Title"=>"请快些洗澡吧，我的女神", "Description"=>"歌手：刘姗姗和她的第一次尝试", "MusicUrl"=>"http://www.kennilun.com/shanshanliu/music/shqnkdxi.mp3", "HQMusicUrl"=>"http://www.kennilun.com/shanshanliu/music/shqnkdxi.mp3"); 
        }
        else if (strstr($keyword, "洗澡")){
            $content = array();
            $content = array("Title"=>"请快些洗澡吧，我的女神", "Description"=>"歌手：刘姗姗和她的第一次尝试", "MusicUrl"=>"http://www.kennilun.com/shanshanliu/music/shqnkdxi.mp3", "HQMusicUrl"=>"http://www.kennilun.com/shanshanliu/music/shqnkdxi.mp3"); 
        } else if (strstr($keyword, "操逼")){
            $content = array();
            $content = array("Title"=>"请快些洗澡吧，我的女神", "Description"=>"歌手：刘姗姗和她的第一次尝试", "MusicUrl"=>"http://www.kennilun.com/shanshanliu/music/shqnkdxi.mp3", "HQMusicUrl"=>"http://www.kennilun.com/shanshanliu/music/shqnkdxi.mp3"); 
        }else{
            $content = date("Y-m-d H:i:s",time())."\nOpenID：".$object->FromUserName."\n技术支持 Kenny Ma";
        }

        if(is_array($content)){
            if (isset($content[0])){
                $result = $this->transmitNews($object, $content);
            }else if (isset($content['MusicUrl'])){
                $result = $this->transmitMusic($object, $content);
            }
        }else{
            //$result = $this->transmitText($object, $content);
            //＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊
            //＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊
            //＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊
            //＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊
            //＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊
            //＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊
            //＊＊＊＊＊＊＊＊＊此处应该连接数据库＊＊＊＊＊＊＊＊
            //＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊
            //＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊
            //＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊
            //＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊
            //＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊
            //＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊
            //＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊
            //＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊
            //＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊
            //＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊
            //＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊
            //$result = $this->pass_msg_to_open_id("操逼不给钱",$object->FromUserName);
            $result = $this->get_random_message(0,$object,$this->add_message_to_server($keyword,0,0,$object->FromUserName));
        }
        return $result;
        //开始连接数据库并保存信息
}

    //接收图片消息
    private function receiveImage($object)
    {
        $content = array("MediaId"=>$object->MediaId);
        $result = $this->get_random_message(1,$object,$this->add_message_to_server("picmessage",1,$object->MediaId,$object->FromUserName));
        $this->download_image($object->MediaId);
        return $result;
    }

    //接收位置消息
    private function receiveLocation($object)
    {
        $content = "你的位置经度：".$object->Location_Y."；纬度：".$object->Location_X."；位置为：".$object->Label."。请你再附上一张照片。你要是长得好看，我这就来过来看你";
        $this->add_message_to_server($content,2,"0",$object->FromUserName);
        $result = $this->transmitText($object, $content);
        return $result;
    }

    //接收语音消息
    private function receiveVoice($object)
    {
        if (isset($object->Recognition) && !empty($object->Recognition)){
            $content = "你刚才说的是：".$object->Recognition;
            $this->add_message_to_server($object->Recognition,3,$object->MediaId,$object->FromUserName);
            $this->download_voice($object->MediaId);
            $result = $this->transmitText($object, $content);
        }else{
            $content = array("MediaId"=>$object->MediaId);
            $temp_insert=$this->add_message_to_server("voice_msg",3,$object->MediaId,$object->FromUserName);
            $result = $this->get_random_message(3,$object,$temp_insert);
            //$result = $this->transmitVoice($object, $content);
            $this->download_voice($object->MediaId);
        }
        return $result;
    }

    //接收视频消息
    private function receiveVideo($object)
    {
        $content = array("MediaId"=>$object->MediaId, "ThumbMediaId"=>$object->ThumbMediaId, "Title"=>"", "Description"=>"");
        $result = $this->transmitVideo($object, $content);
        return $result;
    }

    //接收链接消息
    private function receiveLink($object)
    {
        $content = "你发送的是链接，标题为：".$object->Title."；内容为：".$object->Description."；链接地址为：".$object->Url;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    //回复文本消息
    private function transmitText($object, $content)
    {
        if (!isset($content) || empty($content)){
            return "";
        }

        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[text]]></MsgType>
    <Content><![CDATA[%s]]></Content>
</xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, 'gh_db773adfa195', time(), $content);

        return $result;
    }
    
    
    private function pass_msg_to_open_id($content,$open_id){
     if (!isset($content) || empty($content)){
            return "";
        }

        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[text]]></MsgType>
    <Content><![CDATA[%s]]></Content>
</xml>";
        $result = sprintf($xmlTpl, $open_id, 'gh_db773adfa195', time(), $content);

        return $result;
    
    }

    //回复图文消息
    private function transmitNews($object, $newsArray)
    {
        if(!is_array($newsArray)){
            return "";
        }
        $itemTpl = "        <item>
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <PicUrl><![CDATA[%s]]></PicUrl>
            <Url><![CDATA[%s]]></Url>
        </item>
";
        $item_str = "";
        foreach ($newsArray as $item){
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
        }
        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[news]]></MsgType>
    <ArticleCount>%s</ArticleCount>
    <Articles>
$item_str    </Articles>
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), count($newsArray));
        return $result;
    }

    //回复音乐消息
    private function transmitMusic($object, $musicArray)
    {
        if(!is_array($musicArray)){
            return "";
        }
        $itemTpl = "<Music>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        <MusicUrl><![CDATA[%s]]></MusicUrl>
        <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
    </Music>";

        $item_str = sprintf($itemTpl, $musicArray['Title'], $musicArray['Description'], $musicArray['MusicUrl'], $musicArray['HQMusicUrl']);

        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[music]]></MsgType>
    $item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复图片消息
    private function transmitImage($object, $imageArray)
    {
        $itemTpl = "<Image>
        <MediaId><![CDATA[%s]]></MediaId>
    </Image>";

        $item_str = sprintf($itemTpl, $imageArray['MediaId']);

        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[image]]></MsgType>
    $item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, "gh_db773adfa195", time());
        return $result;
    }

    //回复语音消息
    private function transmitVoice($object, $voiceArray)
    {
        $itemTpl = "<Voice>
        <MediaId><![CDATA[%s]]></MediaId>
    </Voice>";

        $item_str = sprintf($itemTpl, $voiceArray['MediaId']);
        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[voice]]></MsgType>
    $item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复视频消息
    private function transmitVideo($object, $videoArray)
    {
        $itemTpl = "<Video>
        <MediaId><![CDATA[%s]]></MediaId>
        <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
    </Video>";

        $item_str = sprintf($itemTpl, $videoArray['MediaId'], $videoArray['ThumbMediaId'], $videoArray['Title'], $videoArray['Description']);

        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[video]]></MsgType>
    $item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复多客服消息
    private function transmitService($object)
    {
        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[transfer_customer_service]]></MsgType>
</xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复第三方接口消息
    private function relayPart3($url, $rawData)
    {
        $headers = array("Content-Type: text/xml; charset=utf-8");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $rawData);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    //字节转Emoji表情
    function bytes_to_emoji($cp)
    {
        if ($cp > 0x10000){       # 4 bytes
            return chr(0xF0 | (($cp & 0x1C0000) >> 18)).chr(0x80 | (($cp & 0x3F000) >> 12)).chr(0x80 | (($cp & 0xFC0) >> 6)).chr(0x80 | ($cp & 0x3F));
        }else if ($cp > 0x800){   # 3 bytes
            return chr(0xE0 | (($cp & 0xF000) >> 12)).chr(0x80 | (($cp & 0xFC0) >> 6)).chr(0x80 | ($cp & 0x3F));
        }else if ($cp > 0x80){    # 2 bytes
            return chr(0xC0 | (($cp & 0x7C0) >> 6)).chr(0x80 | ($cp & 0x3F));
        }else{                    # 1 byte
            return chr($cp);
        }
    }

 function download_image($media_id){
require_once "jssdk.php";
$jssdk = new JSSDK("wxe90370e2554fa1d5", "862f2e0b551081b35c0887bcfd3fc978");
$accesstoken = $jssdk->getAccessToken();
copy('http://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$accesstoken.'&media_id='.$media_id,'pics/'.$media_id.'.jpg');
 }
 
 function download_voice($media_id){
require_once "jssdk.php";
$jssdk = new JSSDK("wxe90370e2554fa1d5", "862f2e0b551081b35c0887bcfd3fc978");
$accesstoken = $jssdk->getAccessToken();
copy('http://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$accesstoken.'&media_id='.$media_id,'voices/'.$media_id.'.amr');
exec('sox --norm voices/'.$media_id.'.amr granular-gh-pages/audio/'.$media_id.'.mp3');
exec('sox --norm voices/'.$media_id.'.amr granular-gh-pages/audio/sample.mp3');
 }
    
 function add_message_to_server($message_content,$msg_type,$media_id,$openid){
    
$servername = "";
$username = "";
$password = "";


// Create connection
$conn = new mysqli($servername, $username, $password,"liushanshan");

$laterbool=FALSE;

// Create connection

mysqli_select_db($conn, 'liushanshan');
mysqli_set_charset ( $conn , 'utf8' );
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "INSERT INTO `liushanshan`.`msgrecords` (`msg_type`, `msg_content`, `media_id`, `open_id`,`submission_time`) VALUES ('$msg_type', '$message_content', '$media_id','$openid',NOW());";


if ($conn->query($sql) === TRUE) {
 $last_id = $conn->insert_id;
   // echo "Table type created successfully";
} else {
  //  echo "Error creating table: " . $conn->error;
} 
$conn->close();
return $last_id;
}


function get_random_message($msg_type,$object,$sent_id){
$servername = "";
$username = "";
$password = "";
$content="dummy content";
$open_idd=$object->FromUserName;
$recived_msg_id=68;


// Create connection
$conn = new mysqli($servername, $username, $password,"liushanshan");

mysqli_select_db($conn, 'liushanshan');
mysqli_set_charset ( $conn , 'utf8' );
// Check connection

if ($conn->connect_error) {
echo "error";
    die("Connection failed: " . $conn->connect_error);
}

$sql ="SELECT * 
FROM msgrecords left join conversation_records on msgrecords.msg_id=conversation_records.sender_msg_id
WHERE conversation_records.sender_msg_id IS NULL
AND msg_type =$msg_type
AND open_id != '$open_idd'
AND submission_time >= DATE_SUB( CURRENT_DATE, INTERVAL 48 HOUR ) 
ORDER BY RAND( ) 
LIMIT 1
";

$result = $conn->query($sql);

while($row=$result->fetch_array())

{
if ($msg_type==0||$msg_type==2){
$content=$row['msg_content'];//$row["name"];
$recived_msg_id=$row['msg_id'];
} else {
$content=$row['media_id'];//$row["name"];
$recived_msg_id=$row['msg_id'];
}
}



if ($content=="dummy content"){
$content="你已经把所有新消息都刷光啦！刷光啦你就等等吧：D";
$result = $this->transmitText($object,$content);
} else {
if ($msg_type==0){
//$sent_id="asdasd";
//$content=$sent_id;
$result = $this->transmitText($object,$content);
} else if ($msg_type==1){
$contenttemp = array("MediaId"=>$content);
$result = $this->transmitImage($object, $contenttemp);
}  else if ($msg_type==3){
 $contenttemp = array("MediaId"=>$content);
$result = $this->transmitVoice($object, $contenttemp);
}

$sql2="INSERT INTO `liushanshan`.`conversation_records` (`sender_msg_id`,`receiver_msg_id`) VALUES ($recived_msg_id,$sent_id);";
$result2 = $conn->query($sql2);
$conn->close();
}

return $result;
        
}

    //日志记录
    private function logger($log_content)
    {
        if(isset($_SERVER['HTTP_APPNAME'])){   //SAE
            sae_set_display_errors(false);
            sae_debug($log_content);
            sae_set_display_errors(true);
        }else if($_SERVER['REMOTE_ADDR'] != "127.0.0.1"){ //LOCAL
            $max_size = 1000000;
            $log_filename = "log.xml";
            if(file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size)){unlink($log_filename);}
            file_put_contents($log_filename, date('Y-m-d H:i:s')." ".$log_content."\r\n", FILE_APPEND);
        }
    }
}

?>