<?php

namespace Tui\GeocoderBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Guzzle\Service\Client;
use Tui\GeocoderBundle\UKGeocodeInterface;

class UKGeocodeListener
{
    protected $guzzle;

    public function __construct(Client $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof UKGeocodeInterface) {
            list($lat, $lng) = $this->getLocation($entity->getPostcode());

            $entity->setLat($lat);
            $entity->setLng($lng);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof UKGeocodeInterface) {
            if ($args->hasChangedField('postcode')) {
                list($lat, $lng) = $this->getLocation($entity->getPostcode());

                /* An update-event only contains changed fields, so in order to add changes to the lat & long
                 * fields, we need to compute a new changeset once we've changed the entity.
                 */
                $em = $args->getEntityManager();
                $uow = $em->getUnitOfWork();

                $entity->setLat($lat);
                $entity->setLng($lng);

                $uow->recomputeSingleEntityChangeSet(
                    $em->getClassMetadata(get_class($entity)),
                    $entity
                );
            }
        }
    }

    protected function getLocation($postcode)
    {
        $postcode = trim(strtoupper(str_replace(' ', '', $postcode)));

        if (!$postcode) {
            return array(null,null);
        }

        try {
            $request = $this->guzzle->get('/doc/postcodeunit/'.$postcode.'.json');
            $response = $request->send();

            $data = json_decode($response->getBody(), true);

            $lat = null; $lng = null; $postcode = null;
            foreach ($data as $section) {
                if (isset($section['http://www.w3.org/2004/02/skos/core#notation'])) {
                    $postcode = $section['http://www.w3.org/2004/02/skos/core#notation'][0]['value'];
                }
                if (
                    isset($section['http://www.w3.org/2003/01/geo/wgs84_pos#lat'])
                    && isset($section['http://www.w3.org/2003/01/geo/wgs84_pos#long'])
                ) {
                    $lat = $section['http://www.w3.org/2003/01/geo/wgs84_pos#lat'][0]['value'];
                    $lng = $section['http://www.w3.org/2003/01/geo/wgs84_pos#long'][0]['value'];
                    break;
                }
            }
        } catch (\Exception $e) {
            return array(null,null);
        }

        // Because 0,0 is a valid lat & long
        if (!is_null($lat) && !is_null($lng) && !is_null($postcode)) {
            return array($lat, $lng);
        }

        return array(null,null);
    }
}
