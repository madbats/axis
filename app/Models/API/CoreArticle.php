<?php

namespace App\Models\API;

use App\Models\API\ApiCaller;
use App\Models\API\ApiArticle;
use Exception;

/**
 * Class CoreArticle.
 * This Class is used to call the Core API and format the data returned by the API.
 *
 * @category Model
 * @package App\Models\API
 * @author   Matthieu Rochard <matthieu.rochard@etudiant.univ-rennes1.fr>
 * @author   Brett Becker <brett.becker@etudiant.univ-rennes1.fr>
 */
class CoreArticle extends ApiArticle
{
    public $data = null;

        
    /**
     * CoreArticle Class construct.
     * The construct will turn the raw data into the format used by the
     * application and assigne it to the $data instance variable
     *
     * @param Array $data Raw data returned from the api as json and turned into array
     */
    public function __construct($data)
    {
        $this->data = $this->format($data);
    }
    
    
    /**
     * Formats the raw data sent by Semantic Scholar API
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
     * @param Array $result Raw data returned from the api as json and turned into array
     *
     * @return Array|Bool $article The newly formed article or "false" to specify an error
     */
    protected function format($result)
    {
        $article = array();
        $article['article']['doi'] = array();
        if (isset($result['doi'])) {
            array_push($article['article']['doi'], $result['doi']);
        }
        
        if (isset($result['publisher'])) {
            $article['publisher'] = [$result['publisher']];
        }
        
        $article['arXiv'] = array();
        if (!isset($result['urls'])) {
            if (isset($result['URL'])) {
                $result['urls'] = [$result['URL']];
            }
        } else {
            $result['urls'] = array();
        }

        foreach ($result['urls'] as $url) {
            if (strpos($url, 'http://arxiv.org/abs')!==false || strpos($url, 'https://arxiv.org/abs')!==false) {
                $n =explode('/', $url);
                    
                $n = end($n);
                array_push($article['arXiv'], "arXiv:".$n);
            }
        }
        
        if (isset($result['language']['name'])) {
            $article['article']['language'] = $result['language']['name'];
        }
        if (isset($result['description'])) {
            $article['article']['abstract'] = $result['description'];
        }
        
        if (isset($result['downloadUrl'])) {
            $article['article']['pdf'] = $result['downloadUrl'];
        }
        
        if (isset($article['article']['pdf'])) {
            //core does not return a link to download the pdf from arxiv but only a link to its page we must manifacture in a link.
            if (strpos($article['article']['pdf'], 'http://arxiv.org/abs')!==false || strpos($article['article']['pdf'], 'https://arxiv.org/abs')!==false) {
                $n =explode('/', $article['article']['pdf']);
                   
                $n = end($n);
                $article['article']['pdf'] = 'https://arxiv.org/pdf/'.$n;
            }
        }
        return $article;
    }
    
   
    /**
     * Calls the Core Api
     *
     * Calls the Core Api by using the title of an article.
     * Once the API has responded the returned json is parsed into array and
     * an instance of the CrossRef Class is formed.
     *
     * @param str $title The title of an article
     *
     * @return CoreArticle|False $article  The new Article or false
     */
    public static function call($title)
    {
        $possibleTitles = ApiArticle::getPossibleTitle($title);
        
        $i=0;
        $found = false;
        //Each possible title is tested
        while ($i<count($possibleTitles) && !$found) {
            $t = $possibleTitles[$i];
            $caller = new ApiCaller('https://core.ac.uk/api-v2/search/');
            $caller->addToUrl(urlencode($t));
            $caller->addToUrl("?page=1&pageSize=10&apiKey=XnOURqMIBGLAEi9gC78bdaY50WH3F2Jt");
            try {
                $response = $caller->callApi();
            } catch (Exception $e) {
                $response= false;
            }
            if (!$response) {
                //converts json to array
                $jsonArray = json_decode($response, true);
                //we compare their titles
                if ($jsonArray["status"] == "OK") {
                    $article = $jsonArray["data"][0]['_source'];
                    if (is_array($article['title'])) {
                        $wordsCore = preg_replace(
                            "/[^a-zA-Z0-9\s]/",
                            "",
                            $article['title'][0]
                        );
                    } else {
                        $wordsCore = preg_replace(
                            "/[^a-zA-Z0-9\s]/",
                            "",
                            $article['title']
                        );
                    }
                    
                    $wordsArt = preg_replace(
                        "/[^a-zA-Z0-9\s]/",
                        "",
                        $title
                    );
                    $wordsCore = explode(
                        ' ',
                        strtolower($wordsCore)
                    );
                    $wordsArt = explode(
                        ' ',
                        strtolower($wordsArt)
                    );
                    $found = strcmp(
                        $wordsCore[0],
                        $wordsArt[0]
                    )==0 && strcmp($wordsCore[1], $wordsArt[1])==0;
                }

                if ($found) {
                    return new CoreArticle($article);
                }
            }
            $i++;
        }
        
        return false;
    }
}
