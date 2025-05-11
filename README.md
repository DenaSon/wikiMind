# WikiData API (wikiMind) Laravel Package

📦
A robust and developer-friendly Laravel package for querying **Wikidata** using **SPARQL**. With this package, you can seamlessly connect your Laravel application to Wikidata's vast and structured knowledge base, enabling access to entities, properties, and their relations in a programmatic and efficient manner.

Whether you're building educational tools, conducting data analysis, or enriching content with real-time knowledge, this package provides a fluent interface to fetch structured data directly from Wikidata.

Say goodbye to raw API calls! With this package, you get full **Laravel Facade** support, **smart query enhancements**, and a streamlined interface for precise data retrieval.

## Features

* Retrieve **entity data** (labels, descriptions, properties, etc.) from Wikidata.
* Fluent interface for constructing and executing **SPARQL queries**.
* **Multi-language support** (English, Persian, French, etc.).
* Efficient data filtering and advanced querying (supports **DISTINCT** queries).
* Retrieve related **entity labels** and **descriptions**.
* Easily integrate into your **Laravel** applications with **Facade** and **Dependency Injection**.

## Requirements

* PHP >= 8.0
* Laravel >= 9.x

## Installation

```bash
composer require denason/wikimind
```

## Usage Examples

You can use the global `wikiMindQuery()` helper or dependency injection with the interface to access all features.


### Using Helper : 

```php
$streetEntity = \Denason\Wikimind\Facades\Wikimind::getEntityId('street'); // Q79007

return wikiMindQuery()
    ->lang('fa')
    ->where('street', 'P31', $streetEntity)
    ->where('street', 'P17', 'Q794') // ایران
    ->select(['street', 'streetLabel'])
    ->filter('!BOUND(?place)')
    ->limit(50)
    ->get('collection');
```

## Available Methods

All methods are accessible via the `wikiMindQuery()` helper or through dependency injection using the `WikimindInterface`.

| Method       | Description                                                                                       |
| ------------ | ------------------------------------------------------------------------------------------------- |
| `select()`   | Specifies the variables to select in the query.                                                   |
| `where()`    | Adds conditions to the query (subject, predicate, object).                                        |
| `optional()` | Adds optional parts to the query.                                                                 |
| `filter()`   | Adds a filter condition.                                                                          |
| `lang()`     | Sets the language for the query (e.g., 'fa', 'en').                                               |
| `limit()`    | Sets the number of results to return.                                                             |
| `distinct()` | Ensures the results are distinct (removes duplicates).                                            |
| `orderBy()`  | Specifies the variable and direction for ordering results.                                        |
| `get()`      | Executes the query and returns the results in the specified format (e.g., 'array', 'collection'). |

### Dependency Injection Example

Instead of using the global `wikiMindQuery()` helper, you can inject the `WikimindInterface` directly into your controller, service, or job.


This approach takes advantage of Laravel's service container to resolve the interface to its concrete implementation automatically.

* **Full support for Facades**: You can use `WikimindFacade` for quick access to methods.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
