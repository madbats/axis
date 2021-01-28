<?php

namespace App\Http\Controllers;

use App\Models\DataBase\Article;

/**
 * DisplayController
 *
 * @category Controller
 * @package  App\Http\Controllers
 * @author   Matthieu Rochard <matthieu.rochard@etudiant.univ-rennes1.fr>
 * @author   Brett Becker <brett.becker@etudiant.univ-rennes1.fr>
 */
class DisplayController extends Controller
{
    /**
     * DisplayResults displays a list of articles, with a paging of 10 items per page
     *
     * @param Array   $ids   List of article ids to be displayed
     * @param Integer $total The total number of articles that can be displayed
     *
     * @return View
     */
    public static function displayResults($ids, $total)
    {
        $searchResults = Article::whereIn(
            'id',
            $ids
        )->get();

        $pages = $total / 10;
        if ($pages < 1) {
            $pages = 1;
        }
        $pages = range(1, intval($pages));
        $pages = array_slice(
            $pages,
            max(
                request('page') - 6,
                0
            ),
            11
        );
        return view(
            'SearchResults',
            [
                "data" => $searchResults,
                'total' => $pages
            ]
        );
    }
    /**
     * Displays the requested article
     *
     * @param Integer $id Id of Article to be displayed
     *
     * @return View
     */
    public function displayArticle($id)
    {
        return view(
            'Article',
            [
                'data' => Article::find($id)
            ]
        );
    }
}
