<?php

namespace PanKrok\ShoperAppstoreBundle\Controller\Twig\Extension;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Node;
use Twig\TwigFunction;

class PathFunction extends AbstractExtension
{
    protected $generator;
    protected $params = '';

    public function __construct(RequestStack $requestStack, UrlGeneratorInterface $generator)
    {
        $param_array = [];
        $this->generator = $generator;
        if (isset($requestStack->getCurrentRequest()->query)) {
            $params = $requestStack->getCurrentRequest()->query->all();
            if (is_array($params)) {
                foreach ($params as $k => $v) {
                    $param_array[] = $k.'='.$v;
                }
                $param_string = join('&', $param_array);
                $routeName = $requestStack->getCurrentRequest()->server->get('REQUEST_URI');
                if (false === strpos($routeName, '_profiler')) {
                    $this->params = '?'.$param_string;
                }
            }
        }
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('path', [$this, 'getPath'], ['is_safe_callback' => [$this, 'isUrlGenerationSafe']]),
        ];
    }

    public function getPath(string $name, array $parameters = [], bool $relative = false)
    {
        $path = $this->generator->generate($name, $parameters, $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH);

        return $path.$this->params;
    }

    public function isUrlGenerationSafe(Node $argsNode): array
    {
        // support named arguments
        $paramsNode = $argsNode->hasNode('parameters') ? $argsNode->getNode('parameters') : (
            $argsNode->hasNode(1) ? $argsNode->getNode(1) : null
        );

        if (null === $paramsNode || $paramsNode instanceof ArrayExpression && \count($paramsNode) <= 2 &&
            (!$paramsNode->hasNode(1) || $paramsNode->getNode(1) instanceof ConstantExpression)
        ) {
            return ['html'];
        }

        return [];
    }
}
