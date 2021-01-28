<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ApiArticle.
 * This abstract Class represents the data returned by  API on a specific article
 *
 * @category Model
 * @package App\Models\API
 * @author   Matthieu Rochard <matthieu.rochard@etudiant.univ-rennes1.fr>
 * @author   Brett Becker <brett.becker@etudiant.univ-rennes1.fr>
 */
abstract class ApiArticle extends Model
{
    /**
     * Returns an array of titles.
     * DBLP may store an article with a title in two languages as such
     *      Title in First Language. (Title in second language).
     * This funcition devides the two title and returns them in an array
     *
     * @param str $title The article title, given by dblp
     *
     * @return Array $possibleTitles Liste of possible titles for the article
     */
    public static function getPossibleTitle($title)
    {
        $titles = explode(
            '.',
            $title
        );
        $values = array();
        if ((isset($titles[1])) && (strcmp($titles[1], "")!=0)) {
            $sub = ApiArticle::getStringBetween(
                $titles[1],
                '(',
                ')'
            );
            if (strcmp($sub, "")!=0) {
                array_push(
                    $values,
                    $sub
                );
                array_push(
                    $values,
                    $titles[0]
                );
            }
        }
        array_push(
            $values,
            $titles[0]
        );
        return $values;
    }

    /**
     * Returns the string between the start and and end string.
     * Cuts the parsed string and returns only the sub-string between the $start and $end strings.
     *      getStringBetween('<up>My Name</down>,'<up>','</down>') => 'My Name'
     *
     * @param str $string the string to be cut
     * @param str $start  the string that defines the begining of the string to be cut
     * @param str $end    the string that defines the ending of the string to be cut
     *
     * @return str $subStr The string between
     */
    public static function getStringBetween($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos(
            $string,
            $start
        );
        if ($ini == 0) {
            return '';
        }
        $ini += strlen($start);
        $len = strpos(
            $string,
            $end,
            $ini
        ) - $ini;
        return substr(
            $string,
            $ini,
            $len
        );
    }
    
    /**
     * Gets the data about the authors.
     * Some APIs return a single string containing the first name and the last name of each author.
     * In this case they must be seperated, this is done allong the lines of the given seperator
     *      getAuthor('John, Smith',',') => [['first_name']=>'John',['last_name']=>'Smith']
     *
     * @param str $data      The full name of the author
     * @param str $separator The character seperating the first name from the last name in the name string of the api
     *
     * @return Array $author The formated author
     * Gets the data about the authors
     */
    public function getAuthor($data, $separator)
    {
        $data = explode(
            $separator,
            $data
        );
        $author = array(
            'first_name'=>"",
            'last_name' =>"");
        $author['first_name'] = array_shift($data);
        
        $author['last_name'] = implode(
            $separator,
            $data
        );
        
        return $author;
    }
}
