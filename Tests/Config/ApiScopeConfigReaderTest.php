<?php
/**
 * Created by PhpStorm.
 * User: bartb
 * Date: 02.09.2017
 * Time: 10:39
 */

//@formatter:off
declare(strict_types=1);
//@formatter:on

namespace BartB\APIScopeBundle\Tests\Config;


use BartB\APIScopeBundle\Service\Config\ApiScopeConfigReader;

class ApiScopeConfigReaderTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider dataProviderTestReaderWithBasicOptions
	 */
	public function testConfigReader(array $config)
	{
		$apiScopeConfigReader = new ApiScopeConfigReader($config);

		$alwaysIncludedFromConfig = $apiScopeConfigReader->getAlwaysIncludedForRoute(key($config));
		$supportedMapFromConfig   = $apiScopeConfigReader->getMapForRoute(key($config));

		$expectedAlwaysIncluded = $config[key($config)]['always_included'];
		$expectedSupportedMap   = $config[key($config)]['supported_key_map'];

		foreach ($expectedAlwaysIncluded as $item)
		{
			self::assertContains($item, $alwaysIncludedFromConfig);
		}

		foreach ($expectedSupportedMap as $externalScopeName => $item)
		{
			if (key_exists('security', $item))
			{
				$securityName = $apiScopeConfigReader->getMapSecurityForMapRoute(key($config), $externalScopeName);
				self::assertSame($item['security'], $securityName);
			}

			self::assertContains($item, $supportedMapFromConfig);
		}
	}

	public function dataProviderTestReaderWithBasicOptions(): array
	{
		return [
			[['api.get_item' => ['always_included' => ['always_included_group'], 'supported_key_map' => ['external' => ['internal_name' => 'internal']]]]],
			[['api.get_item' => ['always_included' => ['always_included_group1', 'always_included_group2'], 'supported_key_map' => ['external1' => ['internal_name' => 'internal1', 'security' => 'can-add-exetrnal1-scope'], 'external2' => ['internal_name' => 'internal2']]]]]
		];
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessageRegExp /No scope config found for route/
	 * @dataProvider                   dataProviderTestConfigReaderAlwaysIncludedWithNotConfiguredRoute
	 */
	public function testConfigReaderAlwaysIncludedWithNotConfiguredRoute(array $config, string $notConfiguredRouteName)
	{
		$apiScopeConfigReader = new ApiScopeConfigReader($config);
		$apiScopeConfigReader->getAlwaysIncludedForRoute($notConfiguredRouteName);
	}

	public function dataProviderTestConfigReaderAlwaysIncludedWithNotConfiguredRoute(): array
	{
		return [
			[['api.get_item' => ['always_included' => ['always_included_group'], 'supported_key_map' => ['external' => ['internal_name' => 'internal']]]], 'api.no_existing_route'],
		];
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessageRegExp /No scope config found for route/
	 * @dataProvider                   dataProviderTestConfigReaderMapWithNotConfiguredRoute
	 */
	public function testConfigReaderMapWithNotConfiguredRoute(array $config, string $notConfiguredRouteName)
	{
		$apiScopeConfigReader = new ApiScopeConfigReader($config);
		$apiScopeConfigReader->getMapForRoute($notConfiguredRouteName);
	}

	public function dataProviderTestConfigReaderMapWithNotConfiguredRoute(): array
	{
		return [
			[['api.get_item' => ['always_included' => ['always_included_group'], 'supported_key_map' => ['external' => ['internal_name' => 'internal']]]], 'api.no_existing_route'],
		];
	}
}