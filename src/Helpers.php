<?php



use Denason\Wikimind\Interfaces\MindQueryInterface;
use Denason\Wikimind\WikimindInterface;


if (!function_exists('wikiMind')) {
    /**
     * Get the WikimindInterface instance from the container.
     *
     * @return WikimindInterface
     */
    function wikiMind(): WikimindInterface
    {
        return app(WikimindInterface::class);
    }
}

if (!function_exists('wikiMindAdvanced')) {
    /**
     * Get the MindQurty instance from the container.
     *
     * @return MindQueryInterface
     */
    function wikiMindQuery(): MindQueryInterface
    {
        return app(MindQueryInterface::class);
    }

}
