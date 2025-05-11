# Wikimind Laravel Package

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

### 1. Get entity labels by SPARQL

```php
$entity = 'Q42'; // Example: Q42 is the Wikidata entity ID for Douglas Adams

$results = wikiMindQuery()
    ->lang('en')
    ->where('item', 'P31', $entity)
    ->select(['itemLabel'])
    ->limit(5)
    ->get('array');
```

### 2. Get full description of an entity (Wikidata)

```php
$desc = wikiMindQuery()
    ->lang('fa')
    ->where('item', 'P31', 'Q79007') // Example: Q79007 represents "street"
    ->select(['itemLabel', 'description'])
    ->limit(5)
    ->get('array');
```

### 3. Filter entities with a condition (e.g., distinct results)

```php
$results = wikiMindQuery()
    ->lang('fa')
    ->where('item', 'P31', 'Q42')
    ->distinct()
    ->select(['itemLabel'])
    ->limit(5)
    ->get('collection');
```

### 4. Use the fluent interface to build complex queries

```php
$entity = 'Q42'; // Entity ID for "Douglas Adams"

$results = wikiMindQuery()
    ->lang('en')
    ->where('item', 'P31', $entity)
    ->optional('item', 'P2044', 'elevation')
    ->select(['itemLabel', 'elevation'])
    ->limit(10)
    ->distinct()
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

#### Example: Using WikimindInterface in a Controller

```php
use Denason\Wikimind\WikimindInterface;

class ArticleController extends Controller
{
    public function show(WikimindInterface $wiki)
    {
        $summary = $wiki->summary('Iran');

        return view('article.show', compact('summary'));
    }
}
```

This approach takes advantage of Laravel's service container to resolve the interface to its concrete implementation automatically.

* **Full support for Facades**: You can use `WikimindFacade` for quick access to methods.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
