<?php

namespace App\Models\API;

use App\Models\API\ApiCaller;
use App\Models\API\ApiArticle;
use ErrorException;

/**
 * Class S2Article.
 * This Class represents the data returned by the Semantic Scolar API on a specific article
 *
 * @category Model
 * @package  App\Models\API
 * @author   Matthieu Rochard <matthieu.rochard@etudiant.univ-rennes1.fr>
 * @author   Brett Becker <brett.becker@etudiant.univ-rennes1.fr>
 */
class S2Article extends ApiArticle
{
    public $data = null;

    public function __construct($data)
    {
        $this->data = $this->format($data);
    }

    protected function format($result)
    {
        $article = array();
        $article['authors'] = array();
        foreach ($result['authors'] as $a) {
            $author = $this->getAuthor($a['name'], ' ');
            array_push($article['authors'], $author);
        }

        if (isset($result['fieldsOfStudy'])) {
            $article['categories'] = $result['fieldsOfStudy'];
        }
        if (isset($result['abstract'])) {
            $article['article']['abstract'] = $result['abstract'];
        }
        
        if (isset($result['doi'])) {
            $article['article']['doi'] = $result['doi'];
        }

        $article['citations'] = array();
        if (isset($result['citations'])) {
            foreach ($result['citations'] as $c) {
                $citation = array();
                $citation['title'] = $c['title'];
                $citation['published'] = $c['year'];
                $citation['doi'] = $c['doi'];
                $citation['authors'] = array();
                foreach ($c['authors'] as $a) {
                    $author = $this->getAuthor($a['name'], ' ');
                    array_push($citation['authors'], $author);
                }
                array_push($article['citations'], $citation);
            }
        }

        $article['references'] = array();
        if (isset($result['references'])) {
            foreach ($result['references'] as $r) {
                $reference = array();
                $reference['title'] = $r['title'];
                $reference['published'] = $r['year'];
                $reference['doi'] = $r['doi'];
                $reference['authors'] = array();
                foreach ($r['authors'] as $a) {
                    $author = $this->getAuthor($a['name'], ' ');
                    array_push($reference['authors'], $author);
                }
                array_push($article['references'], $reference);
            }
        }
        return $article;
    }

    public static function call($dois)
    {
        $possible = array();
        foreach ($dois as $doi) {
            array_push($possible, $doi);
            $up = strtoupper($doi);
            $low = strtoupper($doi);
            array_push($possible, $up);
            array_push($possible, $low);
        }
        
        $i=0;
        $found = false;

        while (!$found && $i<count($possible)) {
            $d = $possible[$i];
            $caller = new ApiCaller('https://api.semanticscholar.org/v1/paper/');
            $caller->addToUrl($d);
            
            try {
                $response = $caller->callApi();
            } catch (ErrorException $ex) {
            }
            if (isset($response)) {
                // converts json to array
                $jsonArray = json_decode($response, true);
                return new S2Article($jsonArray);
            }
            
            $i++;
        }
        return false;
    }
}
