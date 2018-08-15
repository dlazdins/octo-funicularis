<?php

namespace App\Http\Controllers\Front;

use App;
use App\Http\Controllers\Controller;
use App\Pages\LanguagePage;
use Arbory\Base\Nodes\Node;
use Illuminate\Http\Request;

class LanguagePageController extends Controller
{
    /**
     * @param Request $request
     * @param Node $node
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request, Node $node)
    {
        /** @var LanguagePage $page */
        $page = $node->content;

        return view('public.controllers.language.index', [
            'content' => $page
        ]);
    }
}
