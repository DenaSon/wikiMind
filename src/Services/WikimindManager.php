<?php

namespace Denason\Wikimind\Services;

use Denason\Wikimind\Exceptions\WikimindException;
use Denason\Wikimind\Traits\WikimindBaseTrait;
use Denason\Wikimind\WikimindInterface;
use Illuminate\Support\Facades\Cache;


class WikimindManager implements WikimindInterface
{
    use WikimindBaseTrait;

    public function __construct()
    {
        $this->initWikimindConfig();
    }

    /**
     * Fetch labels in batch for a list of IDs (entities or properties).
     *
     * @param array $ids List of Qxxx or Pxxx IDs.
     * @param string $lang Language code.
     * @return array [id => label] pairs.
     */
    protected function getLabelsBatch(array $ids, string $lang = 'en'): array
    {
        $params = [
            'action' => 'wbgetentities',
            'ids' => implode('|', $ids),
            'props' => 'labels',
            'languages' => $lang,
            'format' => 'json'
        ];

        $response = $this->fetch($this->wikidataUrl, $params);

        $labels = [];

        foreach ($response['entities'] ?? [] as $id => $entity) {

            $labels[$id] =
                $entity['labels'][$lang]['value'] ??
                $entity['labels']['en']['value'] ??
                $id;
        }

        return $labels;
    }

    /**
     * {@inheritdoc}
     */
    public function entity(string $id): array
    {
        $url = $this->wikidataUrl;

        $params = [
            'action' => 'wbgetentities',
            'ids' => $id,
            'format' => 'json'
        ];

        $response = $this->fetch($url, $params);

        if (!isset($response['entities'][$id])) {
            throw new WikimindException("Entity ID '{$id}' not found in Wikidata response.");
        }

        return $response['entities'][$id];
    }

    /**
     * {@inheritdoc}
     */
    public function search(string $query, string $language = 'en', string $type = 'item', int $limit = 10): array
    {
        $params = [
            'action' => 'wbsearchentities',
            'search' => $query,
            'language' => $language,
            'type' => $type,
            'limit' => $limit,
            'format' => 'json',
        ];

        $response = $this->fetch($this->wikidataUrl, $params);

        if (!isset($response['search'])) {
            throw new WikimindException("Unexpected search result from Wikidata.");
        }

        return $response['search'];
    }

    /**
     * {@inheritdoc}
     */
    public function label(string $id, string $lang = 'en'): ?string
    {
        $entity = $this->entity($id);

        return $entity['labels'][$lang]['value'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function description(string $id, string $lang = 'en'): ?string
    {
        $entity = $this->entity($id);

        return $entity['descriptions'][$lang]['value'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function aliases(string $id, string $lang = 'en'): array
    {
        $entity = $this->entity($id);

        if (!isset($entity['aliases'][$lang])) {
            return [];
        }

        return array_map(fn($alias) => $alias['value'], $entity['aliases'][$lang]);
    }

    /**
     * {@inheritdoc}
     */
    public function properties(string $id): array
    {
        $entity = $this->entity($id);
        $claims = $entity['claims'] ?? [];

        $result = [];

        foreach ($claims as $property => $statements) {
            $values = [];

            foreach ($statements as $statement) {
                $datavalue = $statement['mainsnak']['datavalue']['value'] ?? null;

                if (is_array($datavalue) && isset($datavalue['id'])) {
                    $values[] = $datavalue['id']; // Q-Value
                } elseif (is_string($datavalue)) {
                    $values[] = $datavalue;
                } elseif (is_array($datavalue) && isset($datavalue['time'])) {
                    $values[] = $datavalue['time']; // for dates
                } elseif (!is_null($datavalue)) {
                    $values[] = $datavalue;
                }
            }

            $result[$property] = $values;
        }

        return $result;
    }

    public function propertyValue(string $id, string $propertyId): array
    {
        $entity = $this->entity($id);
        $claims = $entity['claims'][$propertyId] ?? [];

        $values = [];

        foreach ($claims as $claim) {
            $value = $claim['mainsnak']['datavalue']['value'] ?? null;

            if (is_array($value) && isset($value['id'])) {
                $values[] = $value['id'];
            } elseif (is_string($value)) {
                $values[] = $value;
            } elseif (is_array($value) && isset($value['time'])) {
                $values[] = $value['time'];
            } elseif (!is_null($value)) {
                $values[] = $value;
            }
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function sitelink(string $id, string $site = 'enwiki'): ?string
    {
        $entity = $this->entity($id);
        $sitelinks = $entity['sitelinks'][$site] ?? null;

        if (!$sitelinks || !isset($sitelinks['title'])) {
            return null;
        }

        $lang = substr($site, 0, 2); // مثلا en از enwiki
        $title = str_replace(' ', '_', $sitelinks['title']);

        return "https://{$lang}.wikipedia.org/wiki/{$title}";
    }

    /**
     * {@inheritdoc}
     */
    public function getImage(string $id): ?string
    {
        $images = $this->propertyValue($id, 'P18');

        if (empty($images)) {
            return null;
        }

        $filename = $images[0];
        $encoded = urlencode(str_replace(' ', '_', $filename));

        return "https://commons.wikimedia.org/wiki/Special:FilePath/{$encoded}";
    }

    /**
     * {@inheritdoc}
     */
    public function claims(string $id): array
    {
        $entity = $this->entity($id);
        return $entity['claims'] ?? [];
    }


    /**
     * {@inheritdoc}
     */
    public function structuredInfo(string $id, string $lang = 'en', int $maxDepth = 20): array
    {
        $claims = $this->claims($id);
        $info = [];
        $allIds = [];


        $limitedClaims = array_slice($claims, 0, $maxDepth, true);

        foreach ($limitedClaims as $propertyId => $statements) {
            $propertyId = trim($propertyId);
            if ($propertyId !== '') {
                $allIds[] = $propertyId;
            }

            foreach ($statements as $statement) {
                $value = $statement['mainsnak']['datavalue']['value'] ?? null;

                if (is_array($value) && isset($value['id'])) {
                    $valueId = trim($value['id']);
                    if ($valueId !== '') {
                        $allIds[] = $valueId;
                    }
                }
            }
        }

        $allIds = array_unique($allIds);


        $labels = [];
        foreach (array_chunk($allIds, 20) as $chunk) {
            $labels += Cache::remember("wikidata_labels_get_" . md5(json_encode($chunk)), 300, function () use ($chunk, $lang) {
                return $this->getLabelsBatch($chunk, $lang);
            });
        }


        foreach ($limitedClaims as $propertyId => $statements) {
            $propertyId = trim($propertyId);
            $propLabel = $labels[$propertyId] ?? $propertyId;
            $values = [];

            foreach ($statements as $statement) {
                $value = $statement['mainsnak']['datavalue']['value'] ?? null;

                if (is_array($value) && isset($value['id'])) {
                    $valueId = trim($value['id']);
                    $values[] = $labels[$valueId] ?? $valueId;
                } elseif (is_array($value) && isset($value['time'])) {
                    $values[] = substr($value['time'], 1, 10);
                } elseif (is_string($value)) {
                    $values[] = $value;
                }
            }

            if (!empty($values)) {
                $info[$propLabel] = array_unique($values);
            }
        }

        return Cache::remember("wikidata_structured_" . $id . "_$lang", 20, function () use ($info) {
            return $info;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function pickInfo(string $id, array $propertyIds, string $lang = 'en'): array
    {
        $info = [];
        $allIds = [];


        $claims = $this->claims($id);

        foreach ($propertyIds as $propertyId) {
            if (!isset($claims[$propertyId])) {
                continue;
            }

            $statements = $claims[$propertyId];
            $allIds[] = $propertyId;

            foreach ($statements as $statement) {
                $value = $statement['mainsnak']['datavalue']['value'] ?? null;

                if (is_array($value) && isset($value['id'])) {
                    $allIds[] = $value['id'];
                }
            }
        }

        $allIds = array_unique($allIds);


        $labels = [];
        foreach (array_chunk($allIds, 30) as $chunk) {
            $labels += Cache::remember(
                'wikidata_labels_' . md5(json_encode($chunk)),
                10,
                fn() => $this->getLabelsBatch($chunk, $lang)
            );
        }


        foreach ($propertyIds as $propertyId) {
            if (!isset($claims[$propertyId])) {
                continue;
            }

            $statements = $claims[$propertyId];
            $propLabel = $labels[$propertyId] ?? $propertyId;
            $values = [];

            foreach ($statements as $statement) {
                $value = $statement['mainsnak']['datavalue']['value'] ?? null;

                if (is_array($value) && isset($value['id'])) {
                    $values[] = $labels[$value['id']] ?? $value['id'];
                } elseif (is_array($value) && isset($value['time'])) {
                    $values[] = substr($value['time'], 1, 10);
                } elseif (is_string($value)) {
                    $values[] = $value;
                }
            }

            if (!empty($values)) {
                $info[$propLabel] = array_unique($values);
            }
        }

        return Cache::remember('pickInfo_get_' . $id . '_', 30, function () use ($info) {
            return $info;
        });

    }
    /**
     * {@inheritdoc}
     */
    public function pickInfoByName(string $name, array $propertyIds, string $lang = 'en', int $limit = 2): array
    {

        $searchResults = $this->search($name, $lang, 'item', $limit);

        if (empty($searchResults)) {
            return [];
        }

        $firstResult = $searchResults[0] ?? null;

        if (!$firstResult || empty($firstResult['id'])) {
            return [];
        }

        $foundId = $firstResult['id'];

        return $this->pickInfo($foundId, $propertyIds, $lang);
    }
    /**
     * {@inheritdoc}
     */
    public function shortProfile(string $id, string $lang = 'en'): array
    {
        $label = $this->label($id, $lang);
        $description = $this->description($id, $lang);
        $siteCode = $lang . 'wiki';
        $sitelink = $this->sitelink($id, $siteCode);

        return [
            'label' => $label,
            'description' => $description,
            'sitelink' => $sitelink
        ];
    }

    /**
     * {@inheritdoc}
     * @throws WikimindException
     */
    public function smartSuggest(string $query, string $lang = 'en', string $type = 'item', int $limit = 5): array
    {
        $searchResults = $this->search($query, $lang, $type, $limit);

        $suggestions = [];
        foreach ($searchResults as $result) {
            if (isset($result['label'])) {
                $suggestions[] = $result['label'];
            }
        }

        return $suggestions;
    }

    /**
     * {@inheritdoc}
     * @throws WikimindException
     */
    public function getEntityId(string $name): ?string
    {
        $searchResults = $this->search($name);

        if (empty($searchResults)) {
            return null;
        }

        $firstResult = $searchResults[0] ?? null;

        if (!$firstResult || empty($firstResult['id'])) {
            return null;
        }

        return $firstResult['id'];
    }





}
