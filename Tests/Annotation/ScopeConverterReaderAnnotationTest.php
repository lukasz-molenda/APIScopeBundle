<?php
/**
 * Created by PhpStorm.
 * User: bartb
 * Date: 02.09.2017
 * Time: 09:34
 */

//@formatter:off
declare(strict_types=1);
//@formatter:on

namespace BartB\APIScopeBundle\Tests\Annotation;


use BartB\APIScopeBundle\Annotation\Reader\ScopeConverterReader;
use BartB\APIScopeBundle\Data\ScopeCollection;
use BartB\APIScopeBundle\Service\Config\ApiScopeConfigReader;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class ScopeConverterReaderAnnotationTest extends \PHPUnit_Framework_TestCase
{
	/** @var ScopeConverterReader */
	private $scopeConverterReader;

	private function initScopeReader(array $config)
	{
		$authenticationManager = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface')->getMock();
		$accessDecisionManager = $this->getMockBuilder('Symfony\Component\Security\Core\Authorization\AccessDecisionManager')->getMock();

		$tokenStorage = new TokenStorage();
		$token        = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')->getMock();
		$token
			->method('isAuthenticated')
			->will($this->returnValue(true));

		$tokenStorage->setToken($token);

		$authorizationChecker = new AuthorizationChecker(
			$tokenStorage,
			$authenticationManager,
			$accessDecisionManager
		);

		$apiScopeConfigReader       = new ApiScopeConfigReader($config);
		$annotationReader           = new AnnotationReader();
		$this->scopeConverterReader = new ScopeConverterReader($annotationReader, $apiScopeConfigReader, $authorizationChecker);
	}

	/**
	 * @dataProvider dataProviderTestReaderWithBasicOptions
	 */
	public function testReaderWithBasicOptions(string $value, array $queryStringKey, array $config)
	{
		$this->initScopeReader($config);
		$dummyController = new DummyController();
		$request         = new Request();
		$mockKernel      = $this->getMockForAbstractClass('Symfony\Component\HttpKernel\Kernel', ['', '']);

		$request->query->set($value, $queryStringKey);
		$request->attributes->set('_route', key($config));

		$filterControllerEvent = new FilterControllerEvent($mockKernel, [$dummyController, 'getDummyAction'], $request, HttpKernelInterface::MASTER_REQUEST);

		$this->scopeConverterReader->onKernelController($filterControllerEvent);

		/** @var ScopeCollection $scopeCollection */
		$scopeCollection = $request->attributes->get('scopeCollection');

		self::assertInstanceOf(ScopeCollection::class, $scopeCollection);

		$scopes         = $scopeCollection->getScopes();
		$alwaysIncluded = $config[key($config)]['always_included'];
		$supportedMap   = $config[key($config)]['supported_key_map'];

		foreach ($alwaysIncluded as $item)
		{
			self::assertContains($item, $scopes);
		}

		foreach ($supportedMap as $externalName => $item)
		{
			self::assertContains($item['internal_name'], $scopes);
		}
	}

	public function dataProviderTestReaderWithBasicOptions(): array
	{
		return [
			[
				'with', ['external'], ['api.get_item' => ['always_included' => ['always_included_group'], 'supported_key_map' => ['external' => ['internal_name' => 'internal','security'=>'can-add-external-scope']]]],
				['with', ['external1', 'external2'], ['api.get_item' => ['always_included' => ['always_included_group1', 'always_included_group2'], 'supported_key_map' => ['external1' => ['internal_name' => 'internal1'], 'external2' => ['internal_name' => 'internal2']]]]]
			]
		];
	}

	/**
	 * @dataProvider dataProviderTestReaderWithConfiguration
	 */
	public function testReaderWithConfiguration(string $value, array $queryStringKey, array $config)
	{
		$this->initScopeReader($config);
		$dummyController = new DummyWithConfigurationController();
		$request         = new Request();
		$mockKernel      = $this->getMockForAbstractClass('Symfony\Component\HttpKernel\Kernel', ['', '']);

		$request->query->set($value, $queryStringKey);
		$request->attributes->set('_route', key($config));

		$filterControllerEvent = new FilterControllerEvent($mockKernel, [$dummyController, 'getDummyAction'], $request, HttpKernelInterface::MASTER_REQUEST);

		$this->scopeConverterReader->onKernelController($filterControllerEvent);

		/** @var ScopeCollection $scopeCollection */
		$scopeCollection = $request->attributes->get('scopes');

		self::assertInstanceOf(ScopeCollection::class, $scopeCollection);

		$scopes         = $scopeCollection->getScopes();
		$alwaysIncluded = $config[key($config)]['always_included'];
		$supportedMap   = $config[key($config)]['supported_key_map'];

		foreach ($alwaysIncluded as $item)
		{
			self::assertContains($item, $scopes);
		}

		foreach ($supportedMap as $externalName => $item)
		{
			self::assertContains($item['internal_name'], $scopes);
		}
	}

	public function dataProviderTestReaderWithConfiguration(): array
	{
		return [
			['scope', ['external'], ['api.get_item' => ['always_included' => ['always_included_group'], 'supported_key_map' => ['external' => ['internal_name' => 'internal']]]]],
			['scope', ['external1', 'external2'], ['api.get_item' => ['always_included' => ['always_included_group1', 'always_included_group2'], 'supported_key_map' => ['external1' => ['internal_name' => 'internal1'], 'external2' => ['internal_name' => 'internal2']]]]]
		];
	}


	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessageRegExp /scope is not supported/
	 *
	 * @dataProvider                   dataProviderTestReaderWithNotSupportedScope
	 */
	public function testReaderWithNotSupportedScope(string $value, array $queryStringKey, array $config)
	{
		$this->initScopeReader($config);
		$dummyController = new DummyController();
		$request         = new Request();
		$mockKernel      = $this->getMockForAbstractClass('Symfony\Component\HttpKernel\Kernel', ['', '']);

		$request->query->set($value, $queryStringKey);
		$request->attributes->set('_route', key($config));

		$filterControllerEvent = new FilterControllerEvent($mockKernel, [$dummyController, 'getDummyAction'], $request, HttpKernelInterface::MASTER_REQUEST);

		$this->scopeConverterReader->onKernelController($filterControllerEvent);
	}

	public function dataProviderTestReaderWithNotSupportedScope(): array
	{
		return [
			['with', ['no_supported_external'], ['api.get_item' => ['always_included' => ['always_included_group'], 'supported_key_map' => ['external' => 'internal']]]],
			['with', ['no_supported_external1', 'no_supported_external2'], ['api.get_item' => ['always_included' => ['always_included_group1', 'always_included_group2'], 'supported_key_map' => ['external1' => ['internal_name' => 'internal1'], 'external2' => ['internal_name' => 'internal2']]]]]
		];
	}
}