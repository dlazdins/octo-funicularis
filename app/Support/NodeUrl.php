<?php

namespace App\Support;

use App\Views\LayoutViewComposer;
use Arbory\Base\Nodes\Node;
use Arbory\Base\Repositories\NodesRepository;

class NodeUrl
{
    /**
     * @var LayoutViewComposer
     */
    protected $composer;

    /**
     * @var NodesRepository
     */
    protected $nodeRepository;

    /**
     * @var array
     */
    protected $cachedUrls = [];

    /**
     * NodeUrl constructor
     *
     * @param LayoutViewComposer $composer
     * @param NodesRepository $nodeRepository
     */
    public function __construct(LayoutViewComposer $composer, NodesRepository $nodeRepository)
    {
        $this->composer = $composer;
        $this->nodeRepository = $nodeRepository;
    }

    /**
     * @param string $contentType
     * @param string $action
     * @param array $parameters
     * @param bool $absolute
     * @return null|string
     */
    public function get($contentType, $action = 'index', $parameters = [], $absolute = true)
    {
        $cacheKey = md5(json_encode([$contentType, $action, $parameters, $absolute]));
        if ($this->hasInCache($cacheKey)) {
            return $this->getFromCache($cacheKey);
        }

        $languageNode = $this->composer->getLanguageNode();
        if (!$languageNode) {
            return null;
        }

        /** @var Node $node */
        $node = $this->nodeRepository->findUnder(
            $languageNode,
            'content_type',
            $contentType
        )->first(['id']);

        $url = $node ? $node->getUrl($action, $parameters, $absolute) : null;
        $this->cache($cacheKey, $url);
        return $url;
    }

    /**
     * @param string $cacheKey
     * @return string|null mixed
     */
    protected function getFromCache($cacheKey)
    {
        return $this->hasInCache($cacheKey) ? $this->cachedUrls[$cacheKey] : null;
    }

    /**
     * @param string $cacheKey
     * @return string|null mixed
     */
    protected function hasInCache($cacheKey)
    {
        return array_key_exists($cacheKey, $this->cachedUrls);
    }

    /**
     * @param string $cacheKey
     * @param string $url
     */
    protected function cache($cacheKey, $url)
    {
        $this->cachedUrls[$cacheKey] = $url;
    }

}