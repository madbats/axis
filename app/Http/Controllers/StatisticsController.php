<?php

namespace App\Http\Controllers;

use App;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('Statistics');
    }

    /**
     * Get request of report type from Statistics view.
     * Return the corresponding JSON data.
     */
    /* public function createChart(Request $request)
    {
        // getting type of report
        $type = $request->get('reporttype');

        if ($type == "Articles"){
            return $this->getArticalsByConference();
        }

        return $this->getAuthorsbyConference();
    } */

    /**
     * Select the colloque type = conference from _colloques.
     * Finaly it returns all the authors in the articles by conferences.
     */
    public function getAuthorsbyConference()
    {
        $acronym = DB::table('_colloques')
                                ->select('_colloques.acronym AS name')
                                ->distinct()
                                ->where('_colloques.type', 'conference')
                                ->get()
                                ->toArray();
        
        for ($k=0; $k<count($acronym); $k++) {
            $conferences = DB::table('_colloques')
                                ->select('_colloques.id', '_colloques.name AS name')
                                ->where('_colloques.acronym', $acronym[$k]->name)
                                ->get()
                                ->toArray();

            for ($i=0; $i<count($conferences); $i++) {
                $articles = DB::table('_articles')
                                ->select('_articles.id', '_articles.title')
                                ->where('_articles.origine_id', $conferences[$i]->id)
                                ->get()->toArray();
                $noAuthor = 0;
                for ($j = 0; $j < count($articles); $j++) {
                    $authors = DB::table('_authors')
                    ->select('_authors.researcher_id')
                    ->where('_authors.article_id', $articles[$j]->id)
                    ->get()->toArray();
                    $noAuthor += count($authors);
                }
                
                $conferences[$i]->value = $noAuthor;
            };

            $acronym[$k]->children = $conferences;
        };
        
        $return_data = array('name' => 'Total', 'children' => $acronym);
        return $return_data;
    }
    /**
     * Select data from function getAuthorsbyConference().
     * Finaly it returns the number of authors by conferences.
     */
    public function getArticlesByConference()
    {
        $acronym = DB::table('_colloques')
                                ->select('_colloques.acronym AS name')
                                ->distinct()
                                ->where('_colloques.type', 'conference')
                                ->get()
                                ->toArray();
        
        for ($k=0; $k<count($acronym); $k++) {
            $conferences = DB::table('_colloques')
                                ->select('_colloques.id', '_colloques.name AS name')
                                ->where('_colloques.acronym', $acronym[$k]->name)
                                ->get()
                                ->toArray();
            $noArticles = 0;
            for ($i=0; $i<count($conferences); $i++) {
                $articles = DB::table('_articles')
                                ->select('_articles.id', '_articles.title')
                                ->where('_articles.origine_id', $conferences[$i]->id)
                                ->get()->toArray();
                $noArticles += count($articles);
                $conferences[$i]->value = $noArticles;
            };
            $acronym[$k]->children = $conferences;
        };
        $return_data = array('name' => 'Total', 'children' => $acronym);
        return $return_data;
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
