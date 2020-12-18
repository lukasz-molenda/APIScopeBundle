<?php
/**
 * Created by PhpStorm.
 * User: bartb
 * Date: 01.09.2017
 * Time: 16:22
 */

//@formatter:off
declare(strict_types=1);
//@formatter:on

namespace BartB\APIScopeBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
	const ALWAYS_INCLUDED                 = 'always_included';
	const SUPPORTED_KEY_MAP               = 'supported_key_map';
	const SUPPORTED_KEY_MAP_INTERNAL_NAME = 'internal_name';
	const SUPPORTED_KEY_MAP_SECURITY      = 'security';

	/**
	 * {@inheritdoc}
	 */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder('api_scope');
		$rootNode    = \method_exists(TreeBuilder::class, 'getRootNode') ? $treeBuilder->getRootNode() : $treeBuilder->root('api_scope');

		/** @noinspection PhpUndefinedMethodInspection */
		//@formatter:off
		$rootNode
			->children()
				->arrayNode('scopes')
					->useAttributeAsKey('route')
					->prototype('array')
						->children()
							->arrayNode(self::ALWAYS_INCLUDED)
								->requiresAtLeastOneElement()
								->prototype('scalar')->end()
							->end()
							->arrayNode(self::SUPPORTED_KEY_MAP)
								->useAttributeAsKey('external_name')
								->prototype('array')
								->addDefaultsIfNotSet()
									->children()
										->scalarNode(self::SUPPORTED_KEY_MAP_INTERNAL_NAME)
										->cannotBeEmpty()
										->end()
									->scalarNode(self::SUPPORTED_KEY_MAP_SECURITY)
										->defaultValue('')
										->end()
									->end()
							->end()
					->end()
				->end()
			->end();
		//@formatter:on

		// Here you should define the parameters that are allowed to
		// configure your bundle. See the documentation linked above for
		// more information on that topic.

		return $treeBuilder;
	}
}