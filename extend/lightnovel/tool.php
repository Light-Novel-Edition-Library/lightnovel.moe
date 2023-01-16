<?php
namespace lightnovel;

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;

class Tool{
    public static function formatClientTime(String $serverTime){
        if(@\timezone_open(\cookie('timezone')) == false){
            try{
                $reader = new Reader(__DIR__.'/bin/GeoLite2-City.mmdb');
                $record = $reader->city($_SERVER['REMOTE_ADDR']);
                return date_format(date_timezone_set(date_create($serverTime), timezone_open($record->location->timeZone)), 'Y-m-d H:i:s P');
            }catch(AddressNotFoundException $e){
                return date_format(date_timezone_set(date_create($serverTime), timezone_open("UTC")), 'Y-m-d H:i:s P');
            }
        }
        return date_format(date_timezone_set(date_create($serverTime), timezone_open(cookie('timezone'))), 'Y-m-d H:i:s P');
    }
}
