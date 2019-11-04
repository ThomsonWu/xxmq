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
use MQ\MQConsumer;
use MQ\MQProducer;
use mysql_xdevapi\Warning;
use PhpParser\Node\Scalar\MagicConst\Dir;
use test\Mockery\MockingVariadicArgumentsTest;
use MQ\Http\HttpClient;
use xxmq\XxMQException as XException;




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

    public function __construct($mqType,$config = array())
    {
        if($this->is_emp_array($config)){
            $config = $this->getConfig();
        }
        $this->mqType = $mqType;
        if($mqType=='rocketmq'){
            $this->endPoint = $config['rocketmq']['endPoint'];
            $this->accessId = $config['rocketmq']['accessId'];
            $this->accessKey = $config['rocketmq']['accessKey'];
            $securityToken = null;
            try {
                $this->link= $this->init($mqType,$config);
            } catch (\Exception $e) {
                if ($e instanceof MQ\Exception\InvalidArgumentException) {
                    printf("Invalid Argument!RequestId:%s\n", $e->getRequestId());
                }
//                echo $e->getMessage();
                throw new XxMQException($e->getMessage());
            }
        }
    }
    protected function init($mqType,$config){
        if(!is_array($config)){
            throw new XxMQException('config lost!');
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

    public function publish($producer=null, $message = ''){
        $mqType = $this->mqType;
        if ($this->isempstr($message)) {
            throw new XxMQException('do not publish empty message!');
        }
        try {
            if ($producer == null) {
                $producer = $this->getProducer();
            }
            if ($mqType == 'rocketmq') {
                $message = $this->setMessage($message, $mqType);
                return $producer->publishMessage($message);
            }
        } catch (\Exception $e) {
            throw new XxMQException($e->getMessage());
        }
    }
    public function getProducer($topic='',$instanceId=''){
        if($this->mqType=='rocketmq'){
            if($this->isempstr($instanceId)){
                if(!$this->isempstr($this->instanceId)){
                    $instanceId = $this->instanceId;
                }else{
                    throw new XxMQException('instanceId lost!');
                }
            }
            if($this->isempstr($topic)){
                if(!$this->isempstr($this->topic)){
                    $topic = $this->topic;
                }else{
                    throw new XxMQException('topic lost!');
                }
            }
            try {
                return $this->link->getProducer($instanceId, $topic);
            } catch (\Exception $e) {
                throw new XxMQException($e->getMessage());
            }
        }
    }

    public function getConsumer($topic='',$gid='',$instanceId=''){
        if($this->mqType=='rocketmq'){
            if($this->isempstr($instanceId)){
                if(!$this->isempstr($this->instanceId)){
                    $instanceId = $this->instanceId;
                }else{
                    throw new XxMQException('instanceId lost!');
                }
            }
            if($this->isempstr($gid)){
                if(!$this->isempstr($this->gid)){
                    $topic = $this->gid;
                }else{
                    throw new XxMQException('gid lost!');
                }
            }
            if($this->isempstr($topic)){
                if(!$this->isempstr($this->topic)){
                    $topic = $this->topic;
                }else{
                    throw new XxMQException('topic lost!');
                }
            }
            try {
                return $this->link->getConsumer($instanceId, $topic, $gid);
            } catch (\Exception $e) {
                throw new XxMQException($e->getMessage());
            }
        }
    }

    public function setMessage($message='',$tag='')
    {
        $mqType = $this->mqType;
        $messageObj = null;
        switch($mqType){
        case 'rocketmq':
            try {
                $messageObj = new TopicMessage($message);
                if (strlen($tag)>0) {
                    $messageObj->setMessageTag($tag);
                }
            } catch (\Exception $e) {
                throw new XxMQException($e->getMessage());
            }
            break;
        default :
            $messageObj = '';
            break;
        }
        return $messageObj;
    }

    public function ackMessage($receiptHandles=array(),$consumer = null){
        $mqType = $this->mqType;
        if($mqType=='rocketmq'){
            try {
                if ($consumer == null) {
                    $consumer = $this->getConsumer();
                }
                return $consumer->ackMessage($receiptHandles);
            } catch (\Exception $e) {
                if ($e instanceof MQ\Exception\AckMessageException) {
                    // 某些消息的句柄可能超时了会导致确认不成功
                    $errMsg = 'Ack Error, RequestId:' . $e->getRequestId() . "\n";
                    foreach ($e->getAckMessageErrorItems() as $errorItem) {
                        $errMsg = $errMsg . "\tReceiptHandle: " . $errorItem->getReceiptHandle() . "ErrorCode:" . $errorItem->getErrorCode() . " ErrorMsg:" . $errorItem->getErrorCode() . "\n";
                    }
                    throw new XxMQException($errMsg);
                }
                throw new XxMQException($e->getMessage());
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

    private function isempstr($str){
        if(!is_string($str)){
            return true;
        }
        if($str===''||strlen($str)<=0||$str==null){
            return true;
        }
        return false;
    }

    private function is_emp_array($array,$times=1){
        if(!is_array($array)&&$times=1){
            return true;
        }
        $r = true;
        if(count($array)>0){
            foreach($array as $item){
                $times ++;
                if(is_array($item)){
                    $this->is_emp_array($item,$times);
                }else{
                    $r = $this->isempstr($item);
                }
            }
        }
        if($r&&count($array)>0){
            return false;
        }
        return $r;
    }

}
