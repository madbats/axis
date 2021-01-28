<?php

namespace App\Models\API;

use App\Models\API\ApiCaller;
use App\Models\API\ApiArticle;
use Exception;

/**
 * Class DblpArticles.
 * This Class represents the data returned by the DBLP API on a specific article
 *
 * @category Model
 * @package App\Models\API
 * @author   Matthieu Rochard <matthieu.rochard@etudiant.univ-rennes1.fr>
 * @author   Brett Becker <brett.becker@etudiant.univ-rennes1.fr>
 */
class DblpArticles extends ApiArticle
{
    public $data = array();

    /**
     * DBLPArticles Class construct.
     * The construct will turn the raw data into the format used by
     * the application and assigne it to the $data instance variable
     * @param array[] $data  Raw data returned from the api as json and turned into array
     */
    public function __construct($dataSet = null)
    {
        if ($dataSet != null) {
            if (!is_array($dataSet)) {
                $this->data = $this->format($dataSet);
            } else {
                foreach ($dataSet as $article) {
                    if (!isset($article['info']['title'])) {
                        continue;
                    }
                    $a = $this->format($article);
                    
                    array_push($this->data, $a);
                }
            }
        }
    }

    /**
     * format
     *
     * Formats the raw data sent by DBLP API
     *
     * This function tests the various fields returned in the response to
     * the Core API and assignes the correct field to a field in a new array.
     * This array is aranged in the same way as every other array containing Article data.
     *
     *    article = [
     *           'article'=>
     *                       [
     *                           'title' => string,
     *                           'doi' => array,
     *                           'downloadurl' => string
     *                       ],
     *           'authors'=>
     *                       [
     *                           0=>
     *                               [
     *                                   'first_name' => string,
     *                                   'last_name' => string,
     *                                   'affiliation' => [
     *                                                           0=>[
     *                                                                  'name'=> string
     *                                                                ]
     *
     *                                                    ]
     *                               ]
     *                       ],
     *           'origine'=>
     *                       [
     *                           'name' => string,
     *                           'type' => string
     *                       ],
     *           'categories'=>
     *                       [
     *                           0 => string
     *                       ],
     *           'publisher'=>
     *                       [
     *                           0 => string
     *                       ],
     *           'references'=>
     *                       [
     *                           0 =>
     *                                [
     *                                   'title' => string,
     *                                   'published' => int,
     *                                   'doi' => array,
     *                                   'authors'=>
     *                                                    [
     *                                                        [0]=>
     *                                                            [
     *                                                                'first_name' => string,
     *                                                                'last_name' => string,
     *                                                                'affiliation' => [
     *                                                                                        0=>[
     *                                                                                                     'name'=> string
     *                                                                                             ]
     *
     *                                                                                   ]
     *                                                            ]
     *                                                    ],
     *                                ]
     *                       ],
     *           'citations'=>
     *                       [
     *                           0 =>
     *                                [
     *                                   'title' => string,
     *                                   'published' => int,
     *                                   'doi' => array,
     *                                   'authors'=>
     *                                                    [
     *                                                        [0]=>
     *                                                            [
     *                                                                'first_name' => string,
     *                                                                'last_name' => string,
     *                                                                'affiliation' => [
     *                                                                                        0=>[
     *                                                                                                     'name'=> string
     *                                                                                             ]
     *
     *                                                                                   ]
     *                                                            ]
     *                                                    ],
     *                                ]
     *                       ]
     *       ]
     *
     * @param array $result  Raw data returned from the api as json and turned into array
     *
     * @return array $article  the newly formed article
     */
    protected function format($results)
    {
        $results = $results['info'];
        $article = array();
        $article['article']['title'] =html_entity_decode(urldecode($results['title']), ENT_QUOTES | ENT_HTML5);
        $article['article']['title'] = str_replace('&apos;', "'", $article['article']['title']);
        $article['origine']['type'] = $results['type'];
        $article['article']['doi'] = array();
        if (isset($results['doi'])) {
            array_push($article['article']['doi'], $results['doi']);
        }
        if (isset($results['year'])) {
            $article['article']['published'] = $results['year'];
        }
        if (strcmp($article['origine']['type'], "Journal Articles")==0) {
            if (isset($results['volume'])) {
                $article['origine']['name'] = $results['venue'];
            }
            if (isset($results['volume'])) {
                $article['origine']['volume'] = $results['volume'];
            }
            if (isset($results['number'])) {
                $article['origine']['number'] = $results['number'];
            }
            if (isset($results['pages'])) {
                $article['origine']['pages'] = $results['pages'];
            }
            $article['origine']['type'] = "journal";
        }
        $article['authors'] = array();
        if (isset($results['authors'])) {
            if (isset($results['authors']['author'][0])) {
                foreach ($results['authors']['author'] as $a) {
                    $author = $this->getAuthor($a['text'], ' ');
                    array_push($article['authors'], $author);
                }
            } else {
                $a = $results['authors']['author']['text'];
                
                $author = $this->getAuthor($a, ' ');
                array_push($article['authors'], $author);
            }
        }
        
        return $article;
    }

    /**
     * Calls the DBLP Api
     *
     * Calls the DBLP Api by using the title.
     * Once the API has responded the returned json is parsed into array and an instance of the DBLP Class is formed.
     *
     * @param  string $query The title of an article
     * @param  int $first The offset
     * @param  int $send The number of article returned
     * @return DBLP $article  The new Articles
     */
    public static function call($query, $first = 0, $send = 10)
    {
        $caller = new ApiCaller('https://dblp.org/search/publ/api?format=json');
        $query = preg_replace('/[“”]/', '', $query);
        $query = rawurlencode($query);
        $caller->addToUrl("&q={$query}");
        $caller->addToUrl("&h={$send}");
        $caller->addToUrl("&f={$first}");
        try {
            $response = $caller->callApi();
        } catch (Exception $e) {
            return false;
        }
        
        /** converts json to array */
        $jsonArray = json_decode($response, true);
        
        if ($jsonArray["result"]["status"]["@code"] != 200 || !isset($jsonArray["result"]["hits"]["hit"][0])) {
            return false;
        } else {
            return [
                new DblpArticles($jsonArray["result"]["hits"]["hit"]),
                $jsonArray["result"]['hits']['@total']
            ];
        }
    }

    /**
     * Calls the DBLP Api on authors
     *
     * Calls the DBLP Api by using the name of an author.
     * Once the API has responded the returned json contains a list of authors.
     * The DBLP Call method is called for each article of each author and the returned values are merged.
     *
     * @param  string $query The name of an author
     * @param  int $first The offset
     * @param  int $send The number of article returned
     * @return DBLP $article  The new Articles
     */
    public static function callAuthors($query, $page = 0, $send = 10)
    {
        $caller = new ApiCaller('https://dblp.org/search/author/api?format=json');
        $query = preg_replace('/[“”"]/', '', $query);
        // list of unwanted characters
        $query = urlencode($query);
        $caller->addToUrl("&q={$query}");
        $caller->addToUrl("&h=30");
        $caller->addToUrl("&f=0");
        $response = $caller->callApi();
        
        //converts json to array
        $jsonArray = json_decode($response, true);
        
        if ($jsonArray["result"]["status"]["@code"] != 200 && !isset($jsonArray["result"]["hits"]["hit"][0])) {
            return false;
        } else {
            $mainReturn = new DblpArticles();
            
            foreach ($jsonArray["result"]["hits"]["hit"] as $author) {
                $author = str_replace(
                    '&apos;',
                    "'",
                    str_replace(
                        "&amp;",
                        "&",
                        $author['info']['author']
                    )
                );
                $author = explode(' ', $author);
                $author = implode('_', $author);
                
                $query = "author:{$author}:";

                $answer = self::call($query, 0, 1000);
                
                $mainReturn->data = array_merge(
                    $mainReturn->data,
                    $answer[0]->data
                );
            }
            $nb = count($mainReturn->data);
            $mainReturn->data = array_slice(
                $mainReturn->data,
                $page,
                $send
            );
            return [
                $mainReturn,
                $nb];
        }
    }

    /**
     * Calls the DBLP Api on venues
     *
     * Calls the DBLP Api by using the name of aa journal or conference.
     * Once the API has responded the returned json contains a list of venues.
     * The DBLP Call method is called for each article of each venue and the returned values are merged.
     *
     * @param  string $query The name of a venue
     * @param  int $first The offset
     * @param  int $send The number of article returned
     * @return DBLP $article  The new Articles
     */
    public static function callVenues($query, $page = 0, $send = 10)
    {
        $searchResults = array();
        $caller = new ApiCaller('https://dblp.org/search/venue/api?q=acm&format=json');
        $query = preg_replace('/[“”"]/', '', $query);
        // list of unwanted characters
        $query = urlencode($query);
        $caller->addToUrl("&q={$query}");
        $caller->addToUrl("&h=30");
        $caller->addToUrl("&f=0");
        $response = $caller->callApi();
        // converts json to array
        $jsonArray = json_decode($response, true);

        if ($jsonArray["result"]["status"]["@code"] != 200 && !isset($jsonArray["result"]["hits"]["hit"][0])) {
            return false;
        } else {
            $mainReturn = new DblpArticles();
            
            foreach ($jsonArray['result']['hits']['hit'] as $conference) {
                $tmp = explode("/", $conference['info']['url']);

                $search = "stream:streams/".$tmp[count($tmp)-3]."/".$tmp[count($tmp)-2].":";
                
                $answer = self::call($search, 0, 1000);
                
                $mainReturn->data = array_merge($mainReturn->data, $answer[0]->data);
            }
            $nb = count($mainReturn->data);
            $mainReturn->data = array_slice($mainReturn->data, $page, $send);
            return [$mainReturn,$nb];
        }
    }
}
