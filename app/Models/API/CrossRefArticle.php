<?php

namespace App\Models\API;

use App\Models\API\ApiCaller;
use App\Models\API\ApiArticle;
use ErrorException;

/**
 * Class CrossRefArticle.
 * This Class represents the data returned by the CrossRef API on a specific article
 *
 * @category Model
 * @package App\Models\API
 * @author   Matthieu Rochard <matthieu.rochard@etudiant.univ-rennes1.fr>
 * @author   Brett Becker <brett.becker@etudiant.univ-rennes1.fr>
 */
class CrossRefArticle extends ApiArticle
{
    public $data = null;
    
    /**
     * CrossRefArticle Class construct.
     * The construct will turn the raw data into the format used by
     * the application and assigne it to the $data instance variable
     *
     * @param array[] $data Raw data returned from the api as json and turned into array
     */
    public function __construct($data)
    {
        $this->data = $this->format($data);
    }

    /**
     * Formats the raw data sent by CrossRef API
     * This function tests the various fields returned in the response to
     * the Core API and assignes the correct field to a field in a new array.
     * This array is aranged in the same way as every other array containing Article data.
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
     *                           0 => [
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
     *                                                                                   ]
     *                                                            ]
     *                                                    ],
     *                                ]
     *                       ]
     *       ]
     * @param Array $results Raw data returned from the api as json and turned into array
     *
     * @return Array $article  the newly formed article
     */
    protected function format($results)
    {
        $article = array();
        if (isset($results['publisher'])) {
            $article['publisher'] = $results['publisher'];
        }

        $article['article']['doi'] = array();
        if (isset($results['DOI'])) {
            array_push($article['article']['doi'], $results['DOI']);
        }
        if (isset($results['doi'])) {
            array_push($article['article']['doi'], $results['doi']);
        }
        if (isset($results['description'])) {
            $article['article']['abstract'] = $results['description'];
        }
        if (isset($results['language'])) {
            $article['article']['language'] = $results['language'];
        }
        if (isset($results['link'][0]['URL'])) {
            $article['article']['pdf'] = $results['link'][0]['URL'];
        }
        if (isset($results['event'])) {
            if (isset($results['event']['name'])) {
                $article['origine']['name'] = $results['event']['name'];
            }
            if (isset($results['event']['location'])) {
                $article['origine']['location'] = $results['event']['location'];
            }
        } else {
            if (isset($results['container-title'][0])) {
                $article['origine']['name'] = $results['container-title'][0];
            }
            if (isset($results['journal-issue']['issue'])) {
                $article['origine']['number'] = $results['journal-issue']['issue'];
            }
        }
        if (isset($results['author'])) {
            $article['authors'] = array();
            foreach ($results['author'] as $a) {
                if (isset($a['given'])) {
                    $author = array( 'first_name' => $a['given'], 'last_name' => $a['family']);

                    $first = explode(" ", $author['first_name'])[0];
                } elseif (!isset($a['name'])) {
                    $author = array( 'first_name' => "", 'last_name' => $a['family']);
                }
                $author['affiliation'] = array();
                foreach ($a['affiliation'] as $f) {
                    array_push($author['affiliation'], $f['name']);
                }
                
                array_push($article['authors'], $author);
            }
        }
        
        if (isset($results['subject'])) {
            $article['categories'] = $results['subject'];
        }
        if (isset($results['publisher'])) {
            $article['publisher'] = [$results['publisher']];
        }

        if (isset($results['reference'])) {
            $article['references'] = array();
            foreach ($results['reference'] as $r) {
                $reference = array();
                if (isset($r['DOI'])) {
                    $reference['doi'] = $r['DOI'];
                }
                array_push($article['references'], $reference);
            }
        }
        return $article;
    }

    /**
     * Calls the CrossRef Api by using the title of an article.
     * Once the API has responded the returned json is parsed into array
     * and an instance of the CrossRef Class is formed.
     *
     * @param str        $title   The title of an article
     * @param Array|Null $authors The Articles authors
     * @param Array|Null $doi     The Articles doi
     *
     * @return CrossRefArticle $article  The new Article
     */
    public static function call($title, $authors = null, $doi = null)
    {
        $possibleTitles = ApiArticle::getPossibleTitle($title);
        if ($doi != null) {
            $doi = strtolower($doi);
        } else {
            $doi = "";
        }

        $i=0;
        $found = false;
        //Each possible title is tested
        while ($i<count($possibleTitles) && !$found) {
            $t = urlencode($possibleTitles[$i]);
            $caller = new ApiCaller('https://api.crossref.org/works?');
            $caller->addToUrl("query.bibliographic={$t}");
            if ($authors!=null) {
                $as = array_chunk($authors, 3)[0];
                $str = "&query.author=";
                foreach ($as as $a) {
                    $str = $str.$a['last_name'].'+';
                }
                $str = urlencode($str);
                $caller->addToUrl($str);
            }
            
            $response = $caller->callApi();
            $jsonArray = json_decode($response, true);
            
            if ($jsonArray["status"] == "ok") {
                $article = $jsonArray["message"]["items"][0];
                //once a responce is sent the returned article is tested to ensure it is the right article
                
                if (isset($article['doi'])) {
                    $doiCross = strtolower($article['doi']);
                } elseif (isset($article['DOI'])) {
                    $doiCross = strtolower($article['DOI']);
                } else {
                    $doiCross="";
                }
                
                //if the doi are the same then we have found the right article
                if (strcmp($doiCross, $doi) == 0 && strcmp($doi, "") != 0) {
                    $found = true;
                } else {
                    //otherwise we compare their titles
                    if (isset($article['subtitle'][0])) {
                        $t1 = $article['title'][0].' - '.$article['subtitle'][0];
                    } else {
                        $t1 = $article['title'][0];
                    }
                    $t1 =preg_replace("/[^a-zA-Z0-9\s]/", "", $t1);
                    $t2 =preg_replace("/[^a-zA-Z0-9\s]/", "", $title);
                    $t1 = explode(' ', strtolower($t1));
                    $t2 = explode(' ', strtolower($t2));
                    
                    $found = strcmp($t1[0], $t2[0])==0 ;//&& strcmp($t1[1],$t2[1])==0 ;
                }
            }
            
            if ($found) {
                return new CrossRefArticle($article);
            }
            $i++;
        }
        try {
            $output = self::callDOI($doi);
        } catch (ErrorException $e) {
            $error = explode('):', $e->getMessage());
            $error = end($error);
            //dd($error);
            if (strcmp($error, " failed to open stream: HTTP request failed! HTTP/1.1 404 Not Found\r\n") == 0) {
                return false;
            } else {
                dd("CA:278", $error);
            }
        }
        if ($output != false) {
            return $output;
        }

        return false;
    }

    /**
     * Calls the API using the doi
     *
     * @param Integer $doi The article doi
     *
     * @return CrossRefArticle|False The corresponding Article or false
     */
    public static function callDOI($doi)
    {
        $caller = new ApiCaller('https://api.crossref.org/works/');
        $caller->addToUrl($doi);
        
        $response = $caller->callApi();
        $jsonArray = json_decode($response, true);
        
        if ($jsonArray["status"] == "ok" && isset($jsonArray["message"]['title'])) {
            return new CrossRefArticle($jsonArray["message"]);
        } else {
            return false;
        }
    }
}
