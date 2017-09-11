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
use BartB\APIScopeBundle\DependencyInjection\Configuration;
use BartB\APIScopeBundle\Exception\ScopeReaderException;
use BartB\APIScopeBundle\Exception\ScopeSecurityException;
use BartB\APIScopeBundle\Service\Config\ApiScopeConfigReader;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ScopeConverterReader
{
	/** @var Reader */
	private $annotationReader;

	/** @var ApiScopeConfigReader */
	private $configReader;

	/** @var AuthorizationCheckerInterface */
	private $authorizationChecker;

	public function __construct(Reader $annotationReader, ApiScopeConfigReader $configReader, AuthorizationCheckerInterface $authorizationChecker)
	{
		$this->annotationReader     = $annotationReader;
		$this->configReader         = $configReader;
		$this->authorizationChecker = $authorizationChecker;
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
		$mapForRoute    = $this->configReader->getMapForRoute($route);

		array_walk($passedCollection, function (&$scope) use ($mapForRoute, $route) {
			if (false === array_key_exists($scope, $mapForRoute))
			{
				throw ScopeReaderException::scopeIsNotSupported($scope);
			}

			$securityVoterForScopeName = $this->configReader->getMapSecurityForMapRoute($route, $scope);

			if (empty($securityVoterForScopeName))
			{
				$scope = $mapForRoute[$scope][Configuration::SUPPORTED_KEY_MAP_INTERNAL_NAME];

			}

			if (false === $this->authorizationChecker->isGranted($securityVoterForScopeName))
			{
				throw ScopeSecurityException::authorizationFails($scope);
			}

			$scope = $mapForRoute[$scope][Configuration::SUPPORTED_KEY_MAP_INTERNAL_NAME];
		});

		$scopes          = new ScopeCollection();
		$supportedScopes = array_merge($alwaysIncluded, $passedCollection);

		$scopes->setScopes($supportedScopes);

		return $scopes;
	}
}