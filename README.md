# APIScopeBundle

This Symfony bundle aims to provide simple serialization group recognize from query string (with basic security check if you need it).

[![Build Status](https://travis-ci.org/bartlomiejbeta/APIScopeBundle.png?branch=master)](https://travis-ci.org/bartlomiejbeta/APIScopeBundle)

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

##### 3. Configure in `config.yml`:

```PHP
api_scope:
    scopes:
        api.get_item: #route name
            always_included: #will be always included to scopes bag
                - 'first_always_included_group'
                - 'second_always_included_group'
            supported_key_map:
                external1: { internal_name: 'scope.internal_name1'} #if `external1` will be in the query string than `scope.internal_name1` will be in the scopes bag
                external2:
                    internal_name: 'scope.internal_name2'
                    security: 'can-add-external2-scope' # security voter (check symfony security voter) attribution name (to check if scope can be applied)
```

### Usage
#### 1. Simple

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
##### example request:

```
.../api/item?with[]=external1
```

##### example response:

```
{"scopes":["first_always_included_group","second_always_included_group","scope.internal_name1"]}
```

#### 2. Configured

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
##### example request:

```
.../api/item?scopes[]=external1&scopes[]=external2
```
##### example response:

```
{"scopes":["first_always_included_group","second_always_included_group","scope.internal_name1","scope.internal_name2"]}
```