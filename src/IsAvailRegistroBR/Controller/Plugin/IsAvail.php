<?php

/**
 * IsAvail
 * @file IsAvail.php
 * @date 15/12/2014
 * @author Fábio Paiva <paiva.fabiofelipe@gmail.com>
 * @project sig-registro
 */

namespace IsAvailRegistroBR\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use IsAvailRegistroBR\Helper\AvailClient;

class IsAvail extends AbstractPlugin {

    public function __invoke($fqdn) {
        $client = new AvailClient();
        $response = $client->send_query($fqdn);
        return $response;
    }

}
