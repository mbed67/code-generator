<?php

namespace Mbed67\GeneratorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('Mbed67GeneratorBundle:Default:index.html.twig');
    }
}
