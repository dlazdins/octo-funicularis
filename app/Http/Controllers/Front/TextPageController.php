<?php

namespace App\Http\Controllers\Front;

use App;
use App\Http\Controllers\Controller;
use App\Pages\TextPage;
use Arbory\Base\Nodes\Node;
use Illuminate\Http\Request;

class TextPageController extends Controller
{
    /**
     * @param Request $request
     * @param Node $node
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request, Node $node)
    {
        /** @var TextPage $page */
        $page = $node->content;

        return view('public.controllers.text.index', [
            'html' => $page->html
        ]);
    }
}
