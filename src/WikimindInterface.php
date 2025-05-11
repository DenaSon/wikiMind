<?php

namespace Denason\Wikimind;

interface WikimindInterface
{
    /**
     * Get the full entity data from Wikidata by its ID (e.g. Q937).
     *
     * @param string $id
     * @return array The full entity data including labels, descriptions, claims, etc.
     */
    public function entity(string $id): array;

    /**
     * Search for entities by a keyword.
     *
     * @param string $query The search term.
     * @param string $language The language code (e.g., 'en', 'fa').
     * @param string $type Type of entity: 'item', 'property', etc.
     * @param int $limit Number of results to return.
     * @return array A list of search results with entity IDs and labels.
     */
    public function search(string $query, string $language = 'fa', string $type = 'item', int $limit = 10): array;

    /**
     * Get the label (human-readable title) of an entity or property in a given language.
     *
     * @param string $id Entity or property ID (e.g., Q937, P569).
     * @param string $lang Language code.
     * @return string|null The label, or null if not found.
     */
    public function label(string $id, string $lang = 'en'): ?string;

    /**
     * Get the short description of an entity in a given language.
     *
     * @param string $id
     * @param string $lang
     * @return string|null
     */
    public function description(string $id, string $lang = 'en'): ?string;

    /**
     * Get alternative names (aliases) of an entity in a given language.
     *
     * @param string $id
     * @param string $lang
     * @return array List of aliases, or an empty array.
     */
    public function aliases(string $id, string $lang = 'en'): array;

    /**
     * Get all values for a specific property from an entity.
     *
     * @param string $id Entity ID (e.g. Q937).
     * @param string $propertyId Property ID (e.g. P27 for citizenship).
     * @return array Array of values (could be entity IDs, dates, or strings).
     */
    public function propertyValue(string $id, string $propertyId): array;

    /**
     * Get all property IDs (Pxxx) available for an entity.
     *
     * @param string $id
     * @return array List of property IDs like ['P31', 'P27', ...].
     */
    public function properties(string $id): array;

    /**
     * Get the Wikipedia link (or any Wikimedia site link) for an entity.
     *
     * @param string $id Entity ID.
     * @param string $site Site code (e.g. 'enwiki', 'fawiki').
     * @return string|null Full URL to the article or null if not found.
     */
    public function sitelink(string $id, string $site = 'enwiki'): ?string;

    /**
     * Get the main image (commons file path) of the entity, if available.
     *
     * @param string $id
     * @return string|null URL to the image.
     */
    public function getImage(string $id): ?string;

    /**
     * Get all claims (property-value pairs) of an entity.
     *
     * @param string $id
     * @return array Raw claims data.
     */
    public function claims(string $id): array;

    /**
     * Get structured and human-readable information from claims,
     * optimized for display or AI use.
     *
     * @param string $id
     * @param string $lang
     * @return array An associative array: [label => [values]]
     */
    public function structuredInfo(string $id, string $lang = 'en'): array;

    /**
     * Filter and retrieve a fixed set of properties for an entity.
     *
     * @param string $id The Wikidata Q-ID of the entity.
     * @param string[] $propertyIds List of property IDs (e.g. ['P31','P27','P106']).
     * @param string $lang Language code for labels.
     * @return array An associative array [propertyLabel => array(values)].
     */
    public function pickInfo(string $id, array $propertyIds, string $lang = 'en'): array;

    /**
     * Retrieve structured information for a Wikidata entity by its human-readable name.
     *
     * This method searches for the given name in Wikidata, fetches the first matching entity (Q-ID),
     * and extracts the specified properties' values.
     *
     * @param string $name Human-readable name of the entity (e.g., 'Iran', 'Albert Einstein', 'Tehran').
     * @param array $propertyIds List of Wikidata property IDs (e.g., ['P31', 'P18', 'P856']) to retrieve.
     * @param string $lang Language code for searching and labeling (default 'en').
     * @param int $limit Maximum number of search results to fetch for matching (default 2).
     *
     * @return array Associative array of property labels and their extracted values.
     *
     * @throws \Denason\Wikimind\Exceptions\WikimindException If no entity is found or the entity is invalid.
     *
     * @example
     * $mind->pickInfoByName('Iran', ['P31', 'P17', 'P18', 'P856'], 'fa');
     * // Returns structured info about Iran in Persian language
     */
    public function pickInfoByName(string $name, array $propertyIds, string $lang = 'en', int $limit = 5): array;
    /**
     * Generate a brief profile summary for a given Wikidata entity.
     *
     * This method retrieves the human-readable label (title), a short description,
     * and a Wikipedia sitelink (if available) for the entity in the specified language.
     *
     * The resulting profile is useful for quick displays, previews, or
     * lightweight representations of the entity without fetching all detailed properties.
     *
     * @param string $id The Wikidata Q-ID of the entity (e.g., 'Q937' for Albert Einstein).
     * @param string $lang Language code for fetching the label and description (e.g., 'en', 'fa').
     *
     * @return array{
     *     label: string|null,
     *     description: string|null,
     *     sitelink: string|null
     * } Associative array containing the entity's label, description, and Wikipedia sitelink URL.
     *
     * @example
     * $mind->shortProfile('Q937', 'en');
     * // Returns:
     * // [
     * //   'label' => 'Albert Einstein',
     * //   'description' => 'German-born physicist and mathematician',
     * //   'sitelink' => 'https://en.wikipedia.org/wiki/Albert_Einstein'
     * // ]
     *
     * @see label()
     * @see description()
     * @see sitelink()
     */
    public function shortProfile(string $id, string $lang = 'en'): array;


    /**
     * Provide smart label suggestions for a given query by searching Wikidata entities.
     *
     * This method searches for entities matching the provided query string and returns
     * a list of suggested labels (titles), suitable for autocomplete fields, search assistants,
     * or smart UI hints.
     *
     * It leverages the `search()` method internally and extracts only human-readable labels.
     *
     * @param string $query The search term or partial input provided by the user.
     * @param string $lang Language code for label retrieval (default 'en').
     * @param string $type Type of entity to search for ('item', 'property', etc.; default 'item').
     * @param int $limit Maximum number of suggestions to return (default 5).
     *
     * @return string[] List of suggested entity labels matching the query.
     *
     * @example
     * $mind->smartSuggest('Ains', 'en');
     * // Returns: ['Albert Einstein', 'Ainsworth', 'Ainsdale', ...]
     *
     * @see search()
     */
    public function smartSuggest(string $query, string $lang = 'en', string $type = 'item', int $limit = 5): array;

    /**
     * Get the Wikidata entity ID by a given name.
     *
     * @param string $name
     * @return string|null
     */
    public function getEntityId(string $name): ?string;


}
