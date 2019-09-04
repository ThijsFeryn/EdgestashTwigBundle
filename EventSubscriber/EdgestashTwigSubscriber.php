<?php
namespace ThijsFeryn\EdgestashTwigBundle\EventSubscriber;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class EdgestashTwigSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['request']
            ],
            KernelEvents::RESPONSE => [
                ['response',-10]
            ],
        ];
    }
    public function request(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if ($request->headers->has('Surrogate-Capability') &&
            false !== strpos(
                $request->headers->get('Surrogate-Capability'),
                'edgestash="EDGESTASH/2.1"'
            )
        ) {
            $request->attributes->set('edgestash',true);
        } else {
            $request->attributes->set('edgestash',false);
        }
    }

    public function response(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if($request->attributes->has('edgestash') && $request->attributes->get('edgestash') !== false) {
            $response->headers->set('Surrogate-Control','edgestash="EDGESTASH/2.1"',false);

            if($request->attributes->has('edgestash-json-urls')
                && count($request->attributes->get('edgestash-json-urls')) > 0) {
                $urls = array_unique($request->attributes->get('edgestash-json-urls'));
                foreach ($urls as $url) {
                    $response->headers->set('Link','<'.$url.'>; rel=edgestash',false);
                }
            }
        }
    }
}