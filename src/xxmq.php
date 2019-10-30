<?php
namespace xxmq;
/***************************************************************************
 * xxmq
 * 小象学院阿里云RocketMQ工具类
 * Copyright (c) 2019 xiaoxiangxueyuan.com, Inc. All Rights Reserved
 *
 **************************************************************************/

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
    protected $endPoint=null;
    protected $accessId=null;
    protected $accessKey=null;
    private $mqType='rocketmq';

    public function __construct($mqType)
    {
        $config = $this->getConfig();
        $this->mqType = $mqType;
        if($mqType=='rocketmq'){
            $this->endPoint = $config['rocketmq']['endPoint'];
            $this->accessId = $config['rocketmq']['accessId'];
            $this->accessKey = $config['rocketmq']['accessKey'];
            $endPoint = $this->endPoint;
            $accessId = $this->accessId;
            $accessKey = $this->accessKey;
            $securityToken = null;
            try {
                $this->link= $this->init($mqType,$config);
                //parent::__construct($endPoint, $accessId, $accessKey);
                //$this->client = new HttpClient($endPoint, $accessId,
                    //$accessKey, $securityToken, $config);
            } catch (\Exception $e) {
                if ($e instanceof MQ\Exception\InvalidArgumentException) {
                    printf("Invalid Argument!RequestId:%s\n", $e->getRequestId());
                }
                echo $e->getMessage();
            }
        }
    }
    protected function init($mqType,$config){
        if(!is_array($config)){
            throw new \Exception('config lost!');
        }
        switch($mqType){
        case 'rocketmq':
            if(isset($config['rocketmq']['instanceId'])&&$config['rocketmq']['instanceId']!=''&&strlen($config['rocketmq']['instanceId'])>0){
                $this->instanceId = $config['rocketmq']['instanceId'];
            }else{
                $this->instanceId = '';
            }
            if(isset($config['rocketmq']['topic'])&&$config['rocketmq']['topic']!=''&&strlen($config['rocketmq']['topic'])>0){
                $this->topic= $config['rocketmq']['topic'];
            }else{
                $this->topic= '';
            }
            if(isset($config['rocketmq']['gid'])&&$config['rocketmq']['gid']!=''&&strlen($config['rocketmq']['gid'])>0){
                $this->gid= $config['rocketmq']['gid'];
            }else{
                $this->gid= '';
            }
            return $this->link = new MQClient($config['rocketmq']['endPoint'], $config['rocketmq']['accessId'], $config['rocketmq']['accessKey']);
            break;
        default:
            return $this->link = new MQClient($config['rocketmq']['endPoint'], $config['rocketmq']['accessId'], $config['rocketmq']['accessKey']);
            break;
        }
    }

    public function publish($topic,$message,$instanceId=null){
        $mqType = $this->mqType;
        if($topic==''||$topic==null||strlen($topic)<=0){
            throw new \Exception('topic can not be empty!');
        }
        if($message==''||$message==null||strlen($message)<=0){
            throw new \Exception('do not publish empty message!');
        }
        if($mqType=='rocketmq'){
            if($instanceId==null){
                throw new \Exception('RocketMQ need instanceId please administrator to get it!');
            }
            $message = $this->setMessage($message,$mqType);
            $producer = $this->getProducer();
            return $producer->publishMessage($message);
        }
    }

    public function getProducer(){
        if($this->mqType=='rocketmq'){
            return $this->link->getProducer($this->instanceId,$this->topic);
        }
    }

    public function getConsumer(){
        if($this->mqType=='rocketmq'){
            return $this->link->getConsumer($this->instanceId,$this->topic,$this->gid);
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
            }catch(\Exception $e){
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
        $config =  require_once(__DIR__.'/config.php');
        return $config;
    }

    //to be fixed
    public function listen(){

    }

}
