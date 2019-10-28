<?php
namespace XxMQ;
/***************************************************************************
 * XxMQ
 * 小象学院阿里云RocketMQ工具类
 * Copyright (c) 2019 xiaoxiangxueyuan.com, Inc. All Rights Reserved
 *
 **************************************************************************/

require_once(__DIR__.'/../autoload.php');

use MQ\MQClient;
use MQ\Exception\InvalidArgumentException;
use MQ\Model\TopicMessage;
use PhpParser\Node\Scalar\MagicConst\Dir;use test\Mockery\MockingVariadicArgumentsTest;
use MQ\Http\HttpClient;



/**
 * @file XxMQ.php
 * @author wuqs(wuqisheng@xiaoxiangxueyuan.com)
 * @date 2019/10/12 17:19:32
 *
 **/
class XxMQ extends MQClient
{
    private $endPoint = 'http://1042200050238966.mqrest.cn-qingdao-internal.aliyuncs.com';
    private $accessId = 'LTAIYmBKM9mUP6PP';
    private $accessKey = '1agdCM5oDUNOt0Ul66KDFgjaRuxNiO';
    private $client = '';

    public function __construct()
    {
        echo 11111;
        $endPoint = $this->endPoint;
        $accessId = $this->accessId;
        $accessKey = $this->accessKey;
        $securityToken = null;
        $config = null;
        try {
            parent::__construct($endPoint, $accessId, $accessKey);
            $this->client = new HttpClient($endPoint, $accessId,
                $accessKey, $securityToken, $config);
        } catch (Exception $e) {
            if ($e instanceof MQ\Exception\InvalidArgumentException) {
                printf("Invalid Argument!RequestId:%s\n", $e->getRequestId());
            }
            echo $e->getMessage();
        }
    }

    public function setMessage($message='')
    {
        return new TopicMessage($message);
    }

}