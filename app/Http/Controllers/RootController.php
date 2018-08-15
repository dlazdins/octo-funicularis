<?php

namespace App\Http\Controllers;

use App;
use App\Http\Controllers\Front\LanguagePageController;
use App\Pages\LanguagePage;
use Arbory\Base\Nodes\Node;
use Arbory\Base\Repositories\NodesRepository;
use Arbory\Base\Support\Translate\Language;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;

class RootController extends Controller
{
    /**
     * @var NodesRepository
     */
    protected $nodesRepository;

    /**
     * @var Node
     */
    protected $currentLanguageNode;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @param NodesRepository $nodesRepository
     * @param Router $router
     */
    public function __construct(NodesRepository $nodesRepository, Router $router)
    {
        $this->nodesRepository = $nodesRepository;
        $this->router = $router;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $node = $this->getLanguageNode();

        if (!$node) {
            throw new \BadMethodCallException('Node for ' . app()->getLocale() . ' language not found');
        }

        $controller = $this->getDefaultController();
        return $controller->index($request, $node);
    }

    /**
     * @return Node|\Illuminate\Database\Eloquent\Model|null|object|static
     */
    protected function getLanguageNode()
    {
        if (!$this->currentLanguageNode) {
            $locale = app()->getLocale();
            $language = Language::query()->where('locale', $locale)->first();

            $this->currentLanguageNode = $this->nodesRepository->newQuery()->where([
                'content_type' => LanguagePage::class,
            ])->leftJoin(with(new LanguagePage)->getTable() . ' as c', 'c.id', '=', 'nodes.content_id')
                ->where('c.language_id', $language->id)
                ->first(['nodes.*']);
        }
        return $this->currentLanguageNode;
    }

    /**
     * @return LanguagePageController|mixed
     */
    protected function getDefaultController()
    {
        return resolve(LanguagePageController::class);
    }

    /**
     * @return Node
     */
    public function getRootEndpointNode()
    {
        return $this->getLanguageNode();
    }
}