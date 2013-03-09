<?php

namespace Tui\GeocoderBundle;

interface UKGeocodeInterface
{
    public function getPostcode();
    public function setLng($lng);
    public function setLat($lat);
}