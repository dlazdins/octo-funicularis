<?php

namespace App\Views;

use App;
use App\Pages\LanguagePage;
use Arbory\Base\Nodes\ContentTypeRoutesRegister;
use Arbory\Base\Nodes\Node;
use Arbory\Base\Repositories\NodesRepository;
use Arbory\Base\Views\ViewComposer;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\RootController;

final class LayoutViewComposer implements ViewComposer
{
    /**
     * @var NodesRepository
     */
    protected $nodeRepository;

    /**
     * @var Node
     */
    protected $currentNode;

    /**
     * @var Node
     */
    protected $languageNode;

    /**
     * @param NodesRepository $nodeRepository
     */
    public function __construct(NodesRepository $nodeRepository)
    {
        $this->nodeRepository = $nodeRepository;
    }

    /**
     * @param View $view
     * @return void
     * @throws \InvalidArgumentException
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public function compose(View $view)
    {
        $view->with([
            'locale' => app()->getLocale(),
            'viewName' => $this->getViewName(),
            'jsControllerName' => $this->getJsControllerName(),
            'aboveTheFoldCss' => $this->getAboveTheFoldCss(),
        ]);
    }

    /**
     * @return bool|null|string
     */
    public function getAboveTheFoldCss()
    {
        $filePath = public_path() . '/front/css/abovethefold.css';
        return file_exists($filePath) ? file_get_contents($filePath) : null;
    }

    /**
     * @return string
     */
    protected function getViewName()
    {
        $parts = explode('@', $this->getCurrentActionName());
        return end($parts);
    }

    /**
     * @return string
     */
    protected function getCurrentActionName()
    {
        return \Route::getCurrentRoute() ?
            \Route::getCurrentRoute()->getActionName() : (string)null;
    }

    /**
     * @return Node|null
     */
    public function getCurrentNode()
    {
        if (!empty($this->currentNode)) {
            return $this->currentNode;
        }

        $this->currentNode = resolve(ContentTypeRoutesRegister::class)->getCurrentNode();

        if (!$this->currentNode) {
            /** @var RootController $rootController */
            $rootController = resolve(RootController::class);
            $this->currentNode = $rootController->getRootEndpointNode();
        }

        return $this->currentNode;
    }

    /**
     * @return Node
     */
    public function getLanguageNode()
    {
        if (!empty($this->languageNode)) {
            return $this->languageNode;
        }

        $node = $this->getCurrentNode();


        if ($node->getContentType() === LanguagePage::class) {
            $this->languageNode = $node;
        } else {
            $this->languageNode = $this->nodeRepository->findAbove(
                $node, 'content_type', LanguagePage::class
            )->first();
        }

        return $this->languageNode;
    }

    /**
     * @return string
     */
    protected function getJsControllerName()
    {
        if (($currentActionName = $this->getCurrentActionName())) {
            $parts = explode("\\", $this->getCurrentActionName());
            return end($parts);
        }
        return (string)null;
    }
}
