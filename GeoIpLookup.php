<?php

namespace Acme\SomeBundle\Service;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class GeoIpLookup
{
    private function xmlSerialize($xml)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $result = $serializer->decode($xml, 'xml');

        return $result;
    }

    public function getInfo($ip)
    {
        $result = array(
            'ip' => '',
            'host' => '',
            'isp' => '',
            'city' => 'NO DATA',
            'countrycode' => '',
            'countryname' => '',
            'latitude' => '',
            'longitude' => ''
        );

        $ch = curl_init();
        curl_setopt_array(
            $ch,
            array(
                CURLOPT_URL => 'http://api.geoiplookup.net/?query=' . $ip,
                CURLOPT_VERBOSE => 0,
                CURLOPT_RETURNTRANSFER => true,
            )
        );

        try {
            $resp = curl_exec($ch);

            if (!$resp) return $result;
        } catch (\Exception $e) {
            return $result;
        }

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);


        if ($code === 200) {
            $resp = preg_replace('!&[^; ]{0}?!', ' und ', $resp);
            $preresult = $this->xmlSerialize($resp);
            $result = $preresult['results']['result'];
        }

        return $result;
    }
}