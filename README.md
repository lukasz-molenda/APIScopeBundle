# APIScopeBundle

This Symfony bundle aims to provide simple serialization group recognize from query string.

### Installation

##### 1. Install via composer:
```
composer require bartlomiejbeta/api-scope-bundle
```


##### 2. Register bundle in `AppKernel`:

```PHP
public function registerBundles()
{
    $bundles = array(
        // ...
		new BartB\APIScopeBundle\APIScopeBundle(),
    );
}
```

### Usage
1. Simple

```PHP
/**
* @ScopeConverter()
* @Rest\Route("/api/item", name="api.get_item")
*/
public function getCarCollection(Request $request, ScopeCollection $scopeCollection): Response
{
	$view = $this->view($scopeCollection, Response::HTTP_OK);
	
	$scopesFromQueryString = $scopeCollection->getScopes()
	
	return $this->handleView($view);
}
```

2. Configured

```PHP
/**
* @ScopeConverter(value="scopes",queryString="scope")
* @Rest\Route("/api/item", name="api.get_item")
*/
public function getCarCollection(Request $request, ScopeCollection $scopes): Response
{
	$view = $this->view($scope,Response::HTTP_OK);
		
	scopesFromQueryString = $scopeCollection->getScopes()
		
	return $this->handleView($view);
}
```


### TODO
- tests
- provide example app