# Tui GeocoderBundle

Currently providers an event listener that geocodes any Doctrine entity that implements UKGeocodeInterface.


## Installation

* Download the bundle
* Add it to your `app/AppKernel.php`:
    ```php
    <?php
        public function registerBundles()
        {
            $bundles = array(
                …
                new Tui\GeocoderBundle\TuiGeocoderBundle(),
            );

            …

            return $bundles;
        }
    ```
* Implement UKGecodeInterface in your entity:
        ```php
        <?php

        namespace Acme\DemoBundle\Entity;

        use Doctrine\ORM\Mapping as ORM;
        use Tui\GeocoderBundle\UKGeocodeInterface;

        /**
         * Address
         * 
         * @ORM\Table(name="address")
         * @ORM\Entity()
         * @ORM\HasLifecycleCallbacks
         */
        class Clinic implements UKGeocodeInterface
        {
            /**
             * @ORM\Column(name="postcode", type="string", length=14, nullable=true)
             */
            private $postcode;

            /**
             * @ORM\Column(name="lat", type="float", nullable=true)
             */
            private $lat;

            /**
             * @ORM\Column(name="lng", type="float", nullable=true)
             */
            private $lng;
            
            public function getPostcode() { return $this->postcode; }
            public function setLng($lng) { $this->lng = $lng; }
            public function setLat($lat) { $this->lat = $lat; }
        }
        ```
* 