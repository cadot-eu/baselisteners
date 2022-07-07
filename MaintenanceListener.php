<?php

namespace App\EventListener\base;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;
use App\Service\base\ToolsHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Security\Core\Security;

//onp asse par un listener pour avoir la security 
class MaintenanceListener
{
    private ParameterBagInterface $parameters;
    private Environment $twig;
    private EntityManagerInterface $em;
    private $security;


    public function __construct(ParameterBagInterface $parameters,  Environment $twig, EntityManagerInterface $em, Security $security)
    {
        $this->parameters = $parameters;
        $this->twig = $twig;
        $this->em = $em;
        $this->security = $security;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (!($this->security->getUser() && $this->security->getUser()->getEmail() == 'm@cadot.eu') && $request->getRequestUri() != '/connexion' && isset($_ENV['MAINTENANCE']) && $_ENV['MAINTENANCE']) {
            $template = $this->twig->render(
                'maintenance.html.twig',
                [
                    'TBparametres' => ToolsHelper::params($this->em)
                ]
            );
            $event->setResponse(new Response($template, 503));
            $event->stopPropagation();
        }
    }
}
