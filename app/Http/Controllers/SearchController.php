<?php

namespace App\Http\Controllers;

use App\Models\API\ApiArticle;
use App\Models\API\CoreArticle;
use App\Models\API\CrossRefArticle;
use App\Models\API\DblpArticles;
use App\Models\API\S2Article;
use App\Models\API\Gender;
use App\Models\API\ApiCaller;
use App\Models\DataBase\Article;
use App\Models\DataBase\Author;
use App\Models\DataBase\Category;
use App\Models\DataBase\Colloque;
use App\Models\DataBase\Editor;
use App\Models\DataBase\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class SearchController.
 * Controller used to generate the response to a querry
 *
 * @category Controller
 * @package  App\Http\Controllers
 * @author   Matthieu Rochard <matthieu.rochard@etudiant.univ-rennes1.fr>
 * @author   Brett Becker <brett.becker@etudiant.univ-rennes1.fr>
 */
class SearchController extends Controller
{
    public function home()
    {
        return View('PrototypeHome');
    }

    /**
     * Search
     *
     * @return View
     */
    public function search()
    {
        //Will contain a list of every article
        $searchResults = array();

        request()->validate(
            [
                'q' => 'required'
            ]
        );

        $page = request('page');
        $page = ($page - 1) * 10;

        switch (request('SearchBy')) {
            case "All":
                $output = DblpArticles::call(request("q"), $page);

                $articleList = $output[0];
                //dd($articleList);
                $totalResults = $output[1];

                if ($articleList == false) {
                    return view("ApiSearchError", ["api" => "dblp"]);
                } else {
                    foreach ($articleList->data as $item) {
                        $article = $this->getArticle($item, 1);

                        if (is_array($article)) {
                            array_push(
                                $searchResults,
                                $this->storeInfo($article)
                            );
                        } else {
                            array_push(
                                $searchResults,
                                $article
                            );
                        }
                    }
                }

                break;

            case "Title":
                $output = DblpArticles::call(
                    request("q"),
                    $page
                );

                $articleList = $output[0];

                if ($articleList == false) {
                    return view(
                        "ApiSearchError",
                        [
                            "api" => "dblp"
                        ]
                    );
                } else {
                    foreach ($articleList->data as $item) {
                        if (strpos(strtolower($item["article"]["title"]), strtolower(request("q"))) !== false) {
                            $article = $this->getArticle($item);
                            if (is_array($article)) {
                                array_push(
                                    $searchResults,
                                    $this->storeInfo($article)
                                );
                            } else {
                                array_push(
                                    $searchResults,
                                    $article
                                );
                            }
                        }
                    }
                }

                $totalResults = count($searchResults);
                $searchResults = array_slice(
                    $searchResults,
                    $page,
                    10
                );

                break;

            case "Author":
                $output = DblpArticles::callAuthors(
                    request("q"),
                    $page
                );

                $articleList = $output[0];

                $totalResults = $output[1];
                if ($articleList == false) {
                    return view(
                        "ApiSearchError",
                        [
                            "api" => "dblp"
                        ]
                    );
                } else {
                    foreach ($articleList->data as $item) {
                        $article = $this->getArticle($item);
                        if (is_array($article)) {
                            array_push(
                                $searchResults,
                                $this->storeInfo($article)
                            );
                        } else {
                            array_push(
                                $searchResults,
                                $article
                            );
                        }
                    }
                }

                break;

            case "Origin":
                $output = DblpArticles::callVenues(
                    request("q"),
                    $page
                );

                $articleList = $output[0];

                $totalResults = $output[1];
                if ($articleList == false) {
                    return view(
                        "ApiSearchError",
                        [
                            "api" => "dblp"
                        ]
                    );
                } else {
                    foreach ($articleList->data as $item) {
                        $article = $this->getArticle(
                            $item,
                            3
                        );
                        if (is_array($article)) {
                            array_push(
                                $searchResults,
                                $this->storeInfo($article)
                            );
                        } else {
                            array_push(
                                $searchResults,
                                $article
                            );
                        }
                    }
                }

                break;
        }

        return DisplayController::displayResults($searchResults, $totalResults);
    }

    /**
     * Retrieves the information for each Article that is returned by dblp.
     * Once an article is parsed it is checked against the existing articles in the database.
     * If it is found then the id is retrieved and returned.
     * Should the id not be found then an instance of CrossRefArticle is called for
     * and the pre-existing article is merged with the newly gathered data.
     * This process is repeted with CoreArticle and S2Article.
     * Finally a few ajustments are made, if the 'doi' value is still an array then
     * the doi given by the S2Article instance is recorded as the 'doi'.
     * If this cannot be donne because their is no instance of S2Article then the first
     * recorded 'doi' is recorded as the 'doi', and if this cannot be donne then the 'doi' is set to "".
     * The 'authors' value is set to the value stored by the instance of CrossRefArticle, when possible.
     * The 'references' value is set to the value stored by the instance of S2Article, when possible.
     * The 'citations' value is set to the value stored by the instance of S2Article, when possible.
     * If the 'pdf' field is an array but one if these is an arXiv download link then it is recorded.
     * Otherwise is a link ends in .pdf then it is recorded. If none of these case are found then
     * the first download link is recorded.
     * Finally the entire 'authors' value of the article is sent to the Gender Class
     * and is then returned and recorded with, when possible, their gender having been determined.
     *
     * @param Article $item            The article array containing the information gathered by dblp
     * @param Integer $recursion_level Levels of recurion from the article
     *
     * @return int $id The database id of the parsed article
     */
    private function getArticle($item, $recursion_level = 250)
    {
        $article = $item;
        $exists = $this->checkArticle($article);

        if ($exists != false) {
            return $exists;
        }
        $article['article']['language'] = 'en';

        //CrossReff API calling
        //DBLP may return a title where an english version of the title is between()
        //In this case we begin by calling the api with the second title as it is usualy in english
        $crossArticle = CrossRefArticle::call(
            $article['article']['title'],
            $article['authors'],
            (isset($article['article']['doi'][0])) ? $article['article']['doi'][0] : null
        );

        if ($crossArticle != false) {
            $article = array_merge_recursive_distinct(
                $crossArticle->data,
                $article
            );
        }

        // Core API calling
        $coreArticle = CoreArticle::call($article['article']['title']);

        if ($coreArticle != false) {
            $article = array_merge_recursive(
                $coreArticle->data,
                $article
            );
        }

        $possibleS2 = ($article['article']['doi']);
        if (isset($article['arXiv'])) {
            array_merge(
                $possibleS2,
                $article['arXiv']
            );
        }
        if (isset($n)) {
            array_push(
                $possibleS2,
                'arXiv:' . $n
            );
        }
        //Multiple doi can be retrieved for a given article but not all of them are in the Semantic Scolar DB
        $s2Article = S2Article::call($possibleS2);

        if ($s2Article != false) {
            $article = array_merge_recursive_distinct(
                $article,
                $s2Article->data
            );
        }


        //if no doi was selected by semantic Scolar then the first recorded doi or a "" is recorded
        if (is_array($article['article']['doi'])) {
            if (isset($s2Article['article']['doi'][0])) {
                $article['article']['doi'] = $s2Article['article']['doi'][0];
            } elseif (isset($article['article']['doi'][0])) {
                $article['article']['doi'] = $article['article']['doi'][0];
            } else {
                $article['article']['doi'] = "";
            }
        }

        //CrossRef offers the best data on authors because it also provides affiliations.
        //Therefore when possible we chose to get data from Crossref
        if (isset($crossArticle->data['authors'][0])) {
            $article['authors'] = $crossArticle->data['authors'];
        }

        //S2 offers the best data on references therefore it's data is prioritized
        if (isset($s2Article->data['references']) && $recursion_level > 0) {
            $article['reference'] = $s2Article->data['references'];
        } elseif (isset($crossArticle->data['references']) && $recursion_level > 0) {
            $article['reference'] = array();
            foreach ($crossArticle->data['references'] as $crossReference) {
                if (isset($crossReference['doi'])) {
                    $crossReference = CrossRefArticle::callDOI($crossArticle['doi']);
                    if ($crossReference != false) {
                        array_push(
                            $article['reference'],
                            $crossReference->data
                        );
                    }
                }
            }
        } else {
            $article['reference'] = array();
        }

        //S2 offert the best data on citations
        if (isset($s2Article->data['citations'][0])) {
            $article['citations'] = $s2Article->data['citations'];
        }

        //If multiple pdfs have been recorded then we must choose the best one
        if (isset($article['article']['pdf'])) {
            if (is_array($article['article']['pdf'])) {
                foreach ($article['article']['pdf'] as $pdf) {
                    if (strpos($pdf, 'http://arxiv.org/pdf') !== false || strpos($pdf, 'https://arxiv.org/pdf') !== false) {
                        $article['article']['pdf'] = $pdf;
                        break;
                    }

                    $end = explode('.', $pdf);
                    $end = end($end);
                    $end = strtolower($end);

                    if (strcmp($end, 'pdf') == 0) {
                        $article['article']['pdf'] = $pdf;
                        break;
                    }

                    if (strpos($pdf, 'https://api.elsevier.com/content/article') !== false) {
                        $pii = ApiArticle::getStringBetween($pdf, 'content/', '?http');
                        $link = 'https://www.sciencedirect.com/science/' . $pii . '/pdf';
                        $article['article']['pdf'] = $link;
                        break;
                    }
                }

                if (is_array($article['article']['pdf'])) {
                    $article['article']['pdf'] = $article['article']['pdf'][0];
                }
            } else {
                $pdf = $article['article']['pdf'];
                if (strpos($pdf, 'https://api.elsevier.com/content/article') !== false) {
                    $pii = ApiArticle::getStringBetween($pdf, 'PII:', '?http');
                    $pdf = 'https://www.sciencedirect.com/science/article/pii/' . $pii . '/pdf';
                    $article['article']['pdf'] = $pdf;
                }
            }
        }

        //The Gender Model is called to gather the gender of each author
        if (isset($article['authors'][0])) {
            $article['authors'] = Gender::call($article['authors']);
        }


        $references_id = array();
        foreach ($article['reference'] as $article_ref) {
            $output = DblpArticles::call($article_ref["title"], 0, 1);

            if ($output != false) {
                $reference = $output[0];
                if (isset($article_ref['doi'])) {
                    $reference->data[0]['article']['doi'][0] = $article_ref['doi'];
                }
                //dd($reference);
                $reference = $this->getArticle(
                    $reference->data[0],
                    $recursion_level - 1
                );

                if (is_array($reference)) {
                    $reference_id = $this->storeInfo($reference);
                } else {
                    $reference_id = $reference;
                }
                array_push($references_id, $reference_id);
            }
        }
        $article['references'] = $references_id;

        return $article;
    }

    /**
     * Diplays Details about requested article
     *
     * @param Request $request Reuqested data
     *
     * @return void
     */
    public function dispDetails(Request $request)
    {
        $details = $request->data;

        return view('article')->with("data", $details);
    }

    /**
     * Checks if the article is in the database.
     * Checks is an article with the same title, date of publication and author is in the database.
     * If it's the case then the id is returned.
     * Otherwise false is returned.
     *
     * @param Article $article The information returned by dblp
     *
     * @return Integer|Bool $id If the article is in the database then it's id is retruned otherwise false is returned
     */
    protected function checkArticle($article)
    {
        $id = false;
        $articleDB = Article::where(
            'title',
            $article['article']['title']
        )->where(
            'published',
            $article['article']['published']
        );

        if ($articleDB->exists()) {
            $id = $articleDB->first()->id;
        }
        return $id;
    }

    /**
     * Stores the parsed article in the database.
     * Calculates the completion score and then calles each individual function to  insert each element.
     *
     * @param Article $article The article that must be scored
     *
     * @return int $id The id of the newly inserted article
     */
    private function storeInfo($article)
    {
        $score = 0;
        if (isset($article['origine'])) {
            $score += 10;
        }
        if (isset($article['article']['published'])) {
            $score += 9;
        }
        if (isset($article['article']['abstract'])) {
            $score += 10;
        }
        if (isset($article['article']['language'])) {
            $score += 2;
        }
        if (isset($article['authors'])) {
            $score += 15;
        }
        if (isset($article['publisher'])) {
            $score += 5;
        }
        if (isset($article['categories'])) {
            $score += 5;
        }
        if (isset($article['article']['pdf'])) {
            $score += 12;
        }
        if (isset($article['citations'])) {
            $score += 10;
        }
        if (isset($article['references'])) {
            $score += 10;
        }
        if (isset($article['article']['doi'])) {
            $score += 12;
        }

        $article['article']['score'] = $score;
        //dd($article['origine']);
        $origineId = $this->storeOrigine($article['origine']);

        $article_id = $this->storeArticle($article['article'], $origineId);

        if ($article_id != false) {
            $this->storeAuthors($article['authors'], $article_id);
            if (isset($article['publisher'])) {
                $this->storeEditors($article['publisher'], $article_id);
            }
            if (isset($article['categories'])) {
                $this->storeCategories($article['categories'], $article_id);
            }
            if (isset($article['citations'])) {
                $this->storeCitations($article['citations'], $article_id);
            }
            if (isset($article['references'])) {
                $this->storeReferences($article['references'], $article_id);
            }
        } else {
            $article_id = Article::where(
                'title',
                $article['article']['title']
            )->where(
                'published',
                $article['article']['published']
            )->first()->id;
        }
        return $article_id;
    }

    /**
     * Inserts all the authors into the database.
     * Inserts each Author into the _researchers, _authors and _affiliations database.
     *
     * @param Array   $authors    The authors to be inserted.
     * @param Integer $article_id The id of the associated article.
     *
     * @return void
     */
    private function storeAuthors($authors, $article_id)
    {
        Author::insertAuthors($authors, $article_id);
    }

    /**
     * Inserts the origine into the database.
     * Inserts the proper origine ever a Colloque or a Journal into the database
     * and creates the associated origine if the origine is not already present in the database.
     * Finaly it returns the id of the new Origine.
     *
     * @param Array $origine The origines data
     *
     * @return Integer $id The id of the Origine
     */
    private function storeOrigine($origine)
    {
        if (strcmp($origine['type'], "Conference and Workshop Papers") == 0) {
            if (!isset($origine['name'])) {
                $origine['name'] = "";
            }
            $origine['location'] = "";

            if (!Colloque::where('name', $origine['name'])->exists()) {
                $id = DB::table('_origines')
                    ->insertGetId(
                        [
                            'origine_type' => 'colloque'
                        ]
                    );

                $colloque = array('id' => $id);

                $colloque['name'] = $origine['name'];

                if (isset($origine['location'])) {
                    $colloque['location'] = $origine['location'];
                }

                $colloque['type'] = "conference";

                //Add more info acronym & url into table _colloques
                $caller = new ApiCaller('https://dblp.org/search/venue/api?format=json');
                $venuQuery = preg_replace(
                    '/[“”"]/',
                    '',
                    $colloque['name']
                ); // list of unwanted characters
                $venuQuery = urlencode($venuQuery);
                $caller->addToUrl("&q={$venuQuery}");
                $caller->addToUrl("&h=1");
                $caller->addToUrl("&f=0");
                $response = $caller->callApi();
                $jsonArray = json_decode($response, true);

                //check data before insert */
                if ($jsonArray["result"]["status"]["@code"] != 200 || array_key_exists("hit", $jsonArray["result"]["hits"]) == false || array_key_exists("acronym", $jsonArray["result"]["hits"]["hit"][0]["info"]) == false) {
                    $colloque['acronym'] = "Others";

                    $colloque['url'] = "https://dblp.org/db/conf/";
                } else {
                    $colloque['acronym'] = $jsonArray["result"]["hits"]["hit"][0]["info"]["acronym"];

                    $colloque['url'] = $jsonArray["result"]["hits"]["hit"][0]["info"]["url"];
                }

                DB::table('_colloques')
                    ->insert($colloque);
            } else {
                $id = Colloque::where(
                    'name',
                    $origine['name']
                )
                    ->first()
                    ->id;
            }
        } elseif (strcmp($origine['type'], "Conference and Workshop Papers") != 0) {
            if (!isset($origine['name'])) {
                $origine['name'] = "";
            }
            $origine['volume'] = "";
            $origine['number'] = "";
            $origine['pages'] = "";
            if (!Journal::where('name', $origine['name'])->exists()) {
                $id = DB::table('_origines')
                    ->insertGetId(
                        [
                            'origine_type' => 'journal'
                        ]
                    );
                $journal = array('id' => $id);
                $journal['name'] = $origine['name'];
                if (isset($origine['volume'])) {
                    $journal['volume'] = $origine['volume'];
                }
                if (isset($origine['number'])) {
                    $journal['number'] = $origine['number'];
                }
                if (isset($origine['pages'])) {
                    $journal['pages'] = $origine['pages'];
                }

                DB::table('_journals')
                    ->insert($journal);
            } else {
                $id = Journal::where(
                    'name',
                    $origine['name']
                )
                    ->first()->id;
            }
        }

        return $id;
    }

    /**
     * Inserts the Categorie into the database
     *
     * @param Array   $categories The categories data
     * @param Integer $article_id The articles id
     *
     * @return void
     */
    private function storeCategories($categories, $article_id)
    {
        foreach ($categories as $category) {
            $category = strtolower($category);
            if (Category::where('name', $category)->exists()) {
                $category_id = Category::where(
                    'name',
                    $category
                )->first()->id;
            } else {
                $category_id = DB::table('_categories')
                    ->insertGetId(
                        [
                            'name' => $category
                        ]
                    );
            }
            DB::table('_articles_categories')
                ->insert(
                    [
                        'article_id' => $article_id,
                        'category_id' => $category_id
                    ]
                );
        }
    }

    /**
     * Inserts the Editor into the database
     *
     * @param Array   $editors    The categories data
     * @param Integer $article_id The articles id
     *
     * @return void
     */
    private function storeEditors($editors, $article_id)
    {
        if (is_array($editors)) {
            $editors = $editors[0];
        }
        if (Editor::where('name', 'LIKE', '%' . $editors . '%')->exists()) {
            $editors_id = Editor::where(
                'name',
                'LIKE',
                '%' . $editors . '%'
            )->first()->id;
        } else {
            $editors_id = DB::table('_editors')
                ->insertGetId(
                    [
                        'name' => $editors
                    ]
                );
        }
        DB::table('_articles_editors')
            ->insert(
                [
                    'article_id' => $article_id,
                    'editor_id' => $editors_id
                ]
            );
    }

    /**
     * Inserts the Reference into the database
     *
     * @param Array   $references The Reference data
     * @param Integer $article_id The articles id
     *
     * @return void
     */
    private function storeReferences($references, $article_id)
    {
        foreach ($references as $reference) {
            if (!DB::table('_references')->where('reference_id', $reference)->where('citation_id', $article_id)->exists()) {
                DB::table('_references')
                    ->insert(
                        [
                            'reference_id' => $reference,
                            'citation_id' => $article_id
                        ]
                    );
            }
        }
    }

    /**
     * Inserts the Citations into the database
     *
     * @param Array   $citations  The Citations data
     * @param Integer $article_id The articles id
     *
     * @return void
     */
    private function storeCitations($citations, $article_id)
    {
    }

    /**
     * Inserts the Article into the database
     *
     * @param Array   $article    The Article data
     * @param Integer $origine_id The origine id
     *
     * @return void
     */
    private function storeArticle($article, $origine_id)
    {
        if (!Article::where('title', $article['title'])->where('published', $article['published'])->exists()) {
            $article['origine_id'] = $origine_id;
            $id = DB::table('_articles')
                ->insertGetId($article);
        } else {
            return false;
        }
        return $id;
    }
}
