<?php

namespace App\Models\API;

use App\Models\API\ApiCaller;
use Exception;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Gender
 * This Class contains methods to read the name.csv and the Genderize.io
 *
 * @category Model
 * @package  App\Models\API
 * @author   Matthieu Rochard <matthieu.rochard@etudiant.univ-rennes1.fr>
 * @author   Brett Becker <brett.becker@etudiant.univ-rennes1.fr>
 */
class Gender extends Model
{
    public static $names = null;
    public static $nbName = 0;
    
    /**
     * loadNames
     *
     * load the names of the names.csv file in the statis variable $names.
     *
     * @return void
     */
    private static function loadNames()
    {
        if (self::$names === null) {
            $filename = "names.csv";
            if (file_exists($filename)) {
                //dd ("The file $filename exists");
            } else {
                dd("The file $filename does not exist");
            }

            
            if (($handle = fopen("names.csv", "r")) !== false) {
                self::$names = array();
                $csv = fgetcsv($handle);
                while (($csv = fgetcsv($handle)) !== false) {
                    if (($csv = fgetcsv($handle)) !== false) {
                        //dd ($csv);
                        self::$names[$csv[1]]= $csv[2];
                        self::$nbName ++;
                    }
                }
            }
        }
    }
    
    /**
     * addName
     *
     * Add a name with it's gender in the name.csv file
     *
     * @param  string $name
     * @param  string $gender
     * @return void
     */
    private static function addName($name, $gender)
    {
        if (($handle = fopen("names.csv", "a")) !== false) {
            $line = array(self::$nbName, $name, $gender);
            fputcsv($handle, $line);
            self::$nbName ++;
        }
    }

    /**
     * Calls the Genderize.io Api
     *
     * Calls the Genderize.io Api by using the names of an article parsed,
     * these names are compared to thoses in the names.csv file (loaded in self::$names) or is searched by the API.
     *
     * @param array $dois  All the names of an article
     *
     * @return array $names All the names of an article with their gender associated
     */
    public static function call($names)
    {
        self::loadNames();
        
        $genderize = array();
        $i=0;
        
        while ($i<count($names)) {
            if (!array_key_exists('first_name', $names[$i]) && !array_key_exists('last_name', $names[$i])) {
                unset($names[$i]);
            } else {
                $names[$i]['gender'] = 'To Be Determined';
                if (array_key_exists('first_name', $names[$i])) {
                    if (array_key_exists($names[$i]['first_name'], self::$names)) {
                        $names[$i]['gender'] = self::$names[$names[$i]['first_name']];
                    } else {
                        array_push($genderize, $i);
                    }
                    //dd($names[$i]['gender']);
                } else {
                    $names[$i]['first_name'] = "";
                }
                if (!array_key_exists('last_name', $names[$i])) {
                    $names[$i]['last_name'] = "";
                }
            }
            
            $i++;
        }

        if (isset($genderize[0])) {
            $caller = new ApiCaller('https://api.genderize.io/?');

            foreach ($genderize as $i) {
                if (array_key_exists('first_name', $names[$i])) {
                    $n = explode(' ', $names[$i]['first_name'])[0];
                    $caller->addToUrl("name[]={$n}&");
                }
            }
            try {
                $response = $caller->callApi();
            } catch (Exception $e) {
                return $names;
            }
            
            $jsonArray = json_decode($response, true);

            for ($i=0; $i<count($jsonArray); $i++) {
                if (!strcmp($jsonArray[$i]['gender'], 'male') || !strcmp($jsonArray[$i]['gender'], 'female')) {
                    if ($jsonArray[$i]['probability']>0.8) {
                        $names[$genderize[$i]]['gender'] = $jsonArray[$i]['gender'];
                    } else {
                        $names[$genderize[$i]]['gender'] = null;
                    }
                }
                self::addName($names[$i]['first_name'], $names[$i]['gender']);
                
                $i++;
            }
        }
        return $names;
    }
}
