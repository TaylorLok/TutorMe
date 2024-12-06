<?php
// app/Utils/RtcTokenBuilder.php

namespace App\Utils;

class RtcTokenBuilder
{
    const ROLE_PUBLISHER = 1;
    const ROLE_SUBSCRIBER = 2;
    
    public static function buildTokenWithUid($appId, $appCertificate, $channelName, $uid, $role, $expireTimestamp)
    {
        $token = new AccessToken2($appId, $appCertificate, $expireTimestamp);
        $serviceRtc = new ServiceRtc($channelName, $uid);

        $serviceRtc->addPrivilege($role, $expireTimestamp);
        $token->addService($serviceRtc);

        return $token->build();
    }
}

class AccessToken2
{
    public $appId;
    public $appCertificate;
    public $expire;
    private $services = [];

    public function __construct($appId, $appCertificate, $expire)
    {
        $this->appId = $appId;
        $this->appCertificate = $appCertificate;
        $this->expire = $expire;
    }

    public function addService($service)
    {
        $this->services[] = $service;
    }

    public function build()
    {
        $signing = $this->appId . $this->expire;
        foreach ($this->services as $service) {
            $signing .= $service->getServiceId();
        }

        $signature = hash_hmac('sha256', $signing, $this->appCertificate, true);
        return base64_encode($signature . $signing);
    }
}

class ServiceRtc
{
    const SERVICE_TYPE = 1;
    private $channelName;
    private $uid;
    private $privileges = [];

    public function __construct($channelName, $uid)
    {
        $this->channelName = $channelName;
        $this->uid = $uid;
    }

    public function addPrivilege($privilege, $expire)
    {
        $this->privileges[$privilege] = $expire;
    }

    public function getServiceId()
    {
        return self::SERVICE_TYPE;
    }
}