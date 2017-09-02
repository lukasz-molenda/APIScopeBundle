<?php
/**
 * Created by PhpStorm.
 * User: bartb
 * Date: 01.09.2017
 * Time: 17:05
 */

//@formatter:off
declare(strict_types=1);
//@formatter:on

namespace BartB\APIScopeBundle\Annotation\Reader;


use BartB\APIScopeBundle\Annotation\ScopeConverter;
use BartB\APIScopeBundle\Data\ScopeCollection;
use BartB\APIScopeBundle\Exception\ScopeReaderException;
use BartB\APIScopeBundle\Service\Config\ApiScopeConfigReader;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class ScopeConverterReader
{
	/** @var Reader */
	private $annotationReader;

	/** @var ApiScopeConfigReader */
	private $configReader;

	public function __construct(Reader $annotationReader, ApiScopeConfigReader $configReader)
	{
		$this->annotationReader = $annotationReader;
		$this->configReader     = $configReader;
	}

	public function onKernelController(FilterControllerEvent $event)
	{
		$controller = $event->getController();
		$request    = $event->getRequest();

		if (false === is_array($controller))
		{
			return;
		}

		list($controllerObject, $methodName) = $controller;

		$scopeConverterAnnotation   = ScopeConverter::class;
		$controllerReflectionObject = new \ReflectionObject($controllerObject);
		$reflectionMethod           = $controllerReflectionObject->getMethod($methodName);

		/** @var ScopeConverter $classAnnotation */
		$classAnnotation = $this->annotationReader->getMethodAnnotation($reflectionMethod, $scopeConverterAnnotation);

		if (false === ($classAnnotation instanceof ScopeConverter))
		{
			return;
		}

		$queryString      = $classAnnotation->getQueryString();
		$query            = $event->getRequest()->query;
		$passedCollection = $query->get($queryString, []);
		$passedCollection = is_array($passedCollection) ? $passedCollection : [];
		$route            = $request->get('_route');
		$mappedCollection = $this->mapPassedCollection($passedCollection, $route);

		$request->attributes->set($classAnnotation->getValue(), $mappedCollection);

	}

	private function mapPassedCollection(array $passedCollection, string $route): ScopeCollection
	{
		$alwaysIncluded = $this->configReader->getAlwaysIncludedForRoute($route);
		$mappedScopes   = $this->configReader->getMapForRoute($route);

		array_walk($passedCollection, function (&$scope) use ($mappedScopes) {
			if (false === array_key_exists($scope, $mappedScopes))
			{
				throw ScopeReaderException::scopeIsNotSupported($scope);
			}

			$scope = $mappedScopes[$scope];
		});

		$scopes          = new ScopeCollection();
		$supportedScopes = array_merge($alwaysIncluded, $passedCollection);

		$scopes->setScopes($supportedScopes);

		return $scopes;
	}
}