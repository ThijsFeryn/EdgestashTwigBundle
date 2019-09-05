<?php
namespace ThijsFeryn\Bundle\EdgestashTwigBundle;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('edgestash', [$this, 'edgestashFilter']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('isEdgestash', [$this, 'isEdgestash']),
            new TwigFunction('edgestash', [$this, 'edgestashFunction']),
        ];
    }

    public function edgestashFilter(string $value, string $name, ?string $url = null): string
    {
        if(!$this->isEdgestash()) {
            return $value;
        }
        if(null !== $url) {
            $urls = $this->requestStack->getCurrentRequest()->attributes->get('edgestash-json-urls',[]);
            $urls[] = $url;
            $this->requestStack->getCurrentRequest()->attributes->set('edgestash-json-urls',$urls);
        }

        return '{{ '.$name.' }}';
    }

    public function edgestashFunction(string $name, ?string $url = null): string
    {
        return $this->edgestashFilter('',$name,$url);
    }

    public function isEdgestash(): bool
    {
        return (bool)$this->requestStack->getCurrentRequest()->attributes->get('edgestash');
    }
}