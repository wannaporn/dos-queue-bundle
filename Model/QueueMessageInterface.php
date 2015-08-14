<?php

namespace DoS\QueueBundle\Model;

use DoS\ResourceBundle\Model\SoftDeletableInterface;
use DoS\ResourceBundle\Model\TimestampableInterface;

interface QueueMessageInterface extends TimestampableInterface, SoftDeletableInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @return array
     */
    public function getBody();

    /**
     * @param array $body
     */
    public function setBody(array $body);

    /**
     * @return \DateTime
     */
    public function getReceivedAt();

    /**
     * @param \DateTime $receivedAt
     */
    public function setReceivedAt(\DateTime $receivedAt);
}