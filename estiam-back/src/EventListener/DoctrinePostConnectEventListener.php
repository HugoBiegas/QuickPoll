<?php
// src/EventListener/DoctrinePostConnectEventListener.php

namespace App\EventListener;

use Doctrine\DBAL\Event\ConnectionEventArgs;

class DoctrinePostConnectEventListener
{
    public function postConnect(ConnectionEventArgs $args)
    {
        $conn = $args->getConnection();
        $conn->exec("SET NAMES 'utf8mb4'");
    }
}
