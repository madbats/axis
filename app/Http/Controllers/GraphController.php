<?php

namespace App\Http\Controllers;

use App\Models\DataBase\Editor;
use App\Models\DataBase\Article;
use App\Models\DataBase\Category;
use App\Models\DataBase\Researcher;
use Illuminate\Http\Request;

/**
 * Generates the request graphs
 *
 * @category Controller
 * @package  App\Http\Controllers
 * @author   Matthieu Rochard <matthieu.rochard@etudiant.univ-rennes1.fr>
 * @author   Brett Becker <brett.becker@etudiant.univ-rennes1.fr>
 */
class GraphController extends Controller
{
    private $request = null;

    /**
     * Displays the requested graph
     *
     * @param Request $request The requested information
     *
     * @return View
     */
    public function drawGraph(Request $request)
    {
        $this->request = $request;
        if (!$this->request->has('type')) {
            $this->request->merge(
                [
                    'type' => 'AreaChart',
                    'Axis1' => 'Articles',
                    'Axis2' => 'Year'
                ]
            );
        }
        $type = $this->getType();
        $data = $this->getData();
        $option = $this->getOption();
        return View(
            'Analytics',
            [
                'graphType' => $type,
                'data' => $data,
                'option' => $option,
                'request' => $this->request
            ]
        );
    }

    /**
     * Returns the requested type
     *
     * @return str
     */
    private function getType()
    {
        return $this->request->input('type');
    }

    /**
     * Returns the requested options
     *
     * @return Array
     */
    private function getOption()
    {
        $option = array();
        $title = "'" . $this->request
            ->input('Axis1') . " per " . $this->request
            ->input('Axis2') . "'";
        $title = array("'title'", $title);
        array_push($option, $title);
        return $option;
    }

    /**
     * Returns the DataSet to be displayed on the graph
     *
     * @return Array
     */
    private function getData()
    {
        $data = array();

        $Ordinate = $this->ordinate();
        $Abscissa = $this->abscissa();
        $data = $this->cross($Ordinate, $Abscissa);

        ksort($data);
        $newData = array();
        array_push(
            $newData,
            array(
                $this->request
                    ->input('Axis2'),
                "'" . $this->request
                    ->input('Axis1') . "'"
            )
        );
        foreach ($data as $k => $v) {
            array_push(
                $newData,
                array(
                    $k,
                    $v
                )
            );
        }

        return $newData;
    }

    /**
     * Returns all the Articles corresponding to the requested ordinate data
     *
     * @return Array
     */
    private function ordinate()
    {
        if (!strcmp($this->request->input('Axis1'), 'MaleProportion') || !strcmp($this->request->input('Axis1'), 'FemaleProportion')) {
            if (!strcmp($this->request->input('Axis1'), 'FemaleProportion')) {
                $table = Researcher::whereIN(
                    "gender",
                    ['female']
                )->get();
            } elseif (!strcmp($this->request->input('Axis1'), 'MaleProportion')) {
                $table = Researcher::whereIN(
                    "gender",
                    ['male']
                )->get();
            } else {
                $table = Researcher::whereIN(
                    "gender",
                    [
                        'male',
                        'female'
                    ]
                )->get();
            }

            $articles = $table->map(
                function ($researcher) {
                    return $researcher->articles()->get()->map(
                        function ($article) {
                            return $article->id;
                        }
                    )->toArray();
                }
            );

            $articles = $articles->reduce(
                function ($carry, $item) {
                    return array_merge($carry, $item);
                },
                array()
            );
            return $articles;
        } elseif (!strcmp($this->request->input('Axis1'), 'Authors')) {
            $table = Researcher::whereIN("gender", ['male', 'female'])->get();

            $articles = $table->map(
                function ($researcher) {
                    return $researcher->articles()->get()->map(
                        function ($article) {
                            return $article->id;
                        }
                    )->toArray();
                }
            );
            $articles = $articles->reduce(
                function ($carry, $item) {
                    return array_merge($carry, $item);
                },
                array()
            );

            return $articles;
        } else {
            $table = Article::all();
            $articles = $table->map(
                function ($article) {
                    return $article->id;
                }
            );

            return $articles->toArray();
        }
    }

    /**
     * Sorts all the stored articles according the requested abscissa
     *
     * @return Array
     */
    private function abscissa()
    {
        switch ($this->request->input('Axis2')) {
            case 'Year':
                $table = Article::all();

                $years = $table->map(
                    function ($article) {
                        return [$article->published, $article->id];
                    }
                );
                $years = $years->reduce(
                    function ($carry, $item) {
                        if (isset($carry[$item[0]])) {
                            array_push($carry[$item[0]], $item[1]);
                        } else {
                            $carry[$item[0]] = array($item[1]);
                        }
                        return $carry;
                    },
                    array()
                );
                return $years;
                break;
            case 'Category':
                $table = Category::all();

                $categories = $table->map(
                    function ($category) {
                        return [$category->name, $category->articles()->get()->map(
                            function ($article) {
                                return $article->id;
                            }
                        )->toArray()];
                    }
                );

                $categories = $categories->reduce(
                    function ($carry, $item) {
                        if (isset($carry[$item[0]])) {
                            $carry[$item[0]] = array_merge(
                                $carry[$item[0]],
                                $item[1][0]
                            );
                        } else {
                            $carry[$item[0]] = $item[1];
                        }
                        return $carry;
                    },
                    array()
                );
                return $categories;
                break;
            case 'Editor':
                $table = Editor::all();

                $editors = $table->map(
                    function ($editor) {
                        return [
                            $editor->name,
                            $editor->articles()->get()->map(
                                function ($article) {
                                    return $article->id;
                                }
                            )->toArray()
                        ];
                    }
                );

                $editors = $editors->reduce(
                    function ($carry, $item) {
                        if (isset($carry[$item[0]])) {
                            $carry[$item[0]] = array_merge(
                                $carry[$item[0]],
                                $item[1][0]
                            );
                        } else {
                            $carry[$item[0]] = $item[1];
                        }
                        return $carry;
                    },
                    array()
                );

                return $editors;
                break;
        }
    }

    /**
     * Sorts the ordinate articles according to their position in the abscissa array
     *
     * @param Array $Ordinate List of article ids produced by the _ordinate() method
     * @param Array $Abscissa List of article ids produced by the _abscissa() method
     *
     * @return Array
     */
    private function cross($Ordinate, $Abscissa)
    {
        $data = array();
        //dd($Ordinate,$Abscissa);
        foreach ($Ordinate as $line) {
            foreach ($Abscissa as $k => $v) {
                if (in_array($line, $v)) {
                    if (isset($data[$k])) {
                        $data[$k]++;
                    } else {
                        $data[$k] = 1;
                    }
                    break;
                }
            }
        }

        if (!strcmp($this->request->input('Axis1'), 'MaleProportion') || !strcmp($this->request->input('Axis1'), 'FemaleProportion')) {
            $total = array();
            foreach ($Abscissa as $key => $line) {
                $articles = Article::find($line);
                $articles = $articles->reduce(
                    function ($carry, $article) {
                        return $carry + $article->authors()
                            ->whereIN(
                                'gender',
                                [
                                    'male',
                                    'female'
                                ]
                            )->count();
                    },
                    0
                );
                $total[$key] = $articles;
            }
            //dd($data,$total);
            foreach ($total as $absc => $ord) {
                if (isset($data[$absc])) {
                    $data[$absc] = $data[$absc] / $ord * 100;
                } else {
                    $data[$absc] = 0;
                }
            }
        }

        return $data;
    }
}
