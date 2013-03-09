<?php

namespace Tui\GeocoderBundle;

interface UKGeocodeInterface
{
    public function getPostcode();
    public function setLng();
    public function setLat();
}