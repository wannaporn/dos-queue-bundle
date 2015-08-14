<?php

namespace DoS\QueueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ReceiveController extends Controller
{
    public function qpushAction()
    {
        return new Response();
    }
}
