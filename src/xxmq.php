<?php
namespace xxmq;
/***************************************************************************
 * xxmq
 * 小象学院阿里云RocketMQ工具类
 * Copyright (c) 2019 xiaoxiangxueyuan.com, Inc. All Rights Reserved
 *
 **************************************************************************/

require_once(__DIR__.'/../autoload.php');
use MQ\MQClient;
use MQ\Exception\InvalidArgumentException;
use MQ\Model\TopicMessage;
use PhpParser\Node\Scalar\MagicConst\Dir;
use test\Mockery\MockingVariadicArgumentsTest;
use MQ\Http\HttpClient;




/**
 * @file xxmq.php
 * @author wuqs(wuqisheng@xiaoxiangxueyuan.com)
 * @date 2019/10/12 17:19:32
 *
 **/
class xxmq 
{
    protected $link=null;
    protected $topic=null;
    protected $gid=null;
    protected $instanceId=null;
    private $mqType='rocketmq';

    public function __construct($mqType)
    {
        $this->mqType = $mqType;
        if($mqType=='rocketmq'){
            $endPoint = $this->endPoint;
            $accessId = $this->accessId;
            $accessKey = $this->accessKey;
            $securityToken = null;
            $config = null;
            try {
                self::$link= self::init($mqType,$config);
                //parent::__construct($endPoint, $accessId, $accessKey);
                //$this->client = new HttpClient($endPoint, $accessId,
                    //$accessKey, $securityToken, $config);
            } catch (Exception $e) {
                if ($e instanceof MQ\Exception\InvalidArgumentException) {
                    printf("Invalid Argument!RequestId:%s\n", $e->getRequestId());
                }
                echo $e->getMessage();
            }
        }
    }
    protected function init($mqType,$config){
        if(!is_array($config)){
            throw new Exception('config lost!');
        }
        switch($mqType){
        case 'rocketmq':
            if(isset($config['instanceId'])&&$config['instanceId']!=''&&strlen($config['instanceId'])>0){
                $this->instanceId = $config['instanceId'];
            }else{
                $this->instanceId = '';
            }
            if(isset($config['topic'])&&$config['topic']!=''&&strlen($config['topic'])>0){
                $this->topic= $config['topic'];
            }else{
                $this->topic= '';
            }
            if(isset($config['gid'])&&$config['gid']!=''&&strlen($config['gid'])>0){
                $this->gid= $config['gid'];
            }else{
                $this->gid= '';
            }
            return self::$link = new MQClient($config['endPoint'], $config['accessId'], $config['accessKey']);
            break;
        default:
            return self::$link = new MQClient($config['endPoint'], $config['accessId'], $config['accessKey']);
            break;
        }
    }

    public function publish($topic,$message,$instanceId=null){
        $mqType = $this->mqType;
        if($topic==''||$topic==null||strlen($topic)<=0){
            throw new Exception('topic can not be empty!');
        }
        if($message==''||$message==null||strlen($message)<=0){
            throw new Exception('do not publish empty message!');
        }
        if($mqType=='rocketmq'){
            if($instanceId==null){
                throw new Exception('RocketMQ need instanceId please administrator to get it!');
            }
            $message = $this->setMessage($message,$mqType);
            $producer = $this->getProducer($instanceId,$topic);
            return $producer->publishMessage($message);
        }
    }

    public function getProducer(){
        if($this->mqType=='rocketmq'){
            return self::$link->getProducer($this->instanceId,$this->topic);
        }
    }

    public function getConsumer(){
        if($this->mqType=='rocketmq'){
            return self::$link->getConsumer($this->instanceId,$this->topic,$this->gid);
        }
    }

    public function setMessage($message='',$tag='')
    {
        $mqType = $this->mqType;
        switch($mqType){
        case 'rocketmq':
            $messageObj = new TopicMessage($message);
            if($tag!=''){
                $messageObj->setMessageTag($tag);
            }
            break;
        default :
            $messageObj = '';
            break;
        }
        return $messageObj;
    }

    public function ackMessage($receiptHandles=array()){
        $mqType = $this->mqType;
        if($mqType=='rocketmq'){
            $consumer = $this->getConsumer();
            try{
                return $consumer->ackMessage($receiptHandles);
            }catch(Exception $e){
                if ($e instanceof MQ\Exception\AckMessageException) {
                   // 某些消息的句柄可能超时了会导致确认不成功
                    printf("Ack Error, RequestId:%s\n", $e->getRequestId());
                    foreach ($e->getAckMessageErrorItems() as $errorItem) {
                        printf("\tReceiptHandle:%s, ErrorCode:%s, ErrorMsg:%s\n", $errorItem->getReceiptHandle(), $errorItem->getErrorCode(), $errorItem->getErrorCode());
                    }
                    exit;
                }

            }
        }
    }

    private function getConfig(){
        require_once(__DIR__.'/config.php');

    }

}