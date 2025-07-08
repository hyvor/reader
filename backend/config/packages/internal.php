<?php

return static function (\Symfony\Config\InternalConfig $internal): void {
    $internal->component('reader');
    $internal->fake('%env(bool:HYVOR_FAKE)%');
}; 