<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ApiCaller.
 * This Class is used to create requests for the different API
 *
 * @category Model
 * @package App\Models\API
 * @author   Matthieu Rochard <matthieu.rochard@etudiant.univ-rennes1.fr>
 * @author   Brett Becker <brett.becker@etudiant.univ-rennes1.fr>
 */
class ApiCaller extends Model
{
    protected $baseUrl; // contains the Api base url that must always be called */
    public $currentUrl; // the url that must be called */
    private $nbParameters = 0;


    /**
     * ApiCaller Class construct.
     * The construct will set the BaseUrl and CurrentUrl of an API.
     *
     * @param string $url The Base Url of an API
     */
    public function __construct($url)
    {
        $this->baseUrl = $url;
        $this->currentUrl = $url;
        $this->nbParameters = 0;
    }
    
    /**
     * Add a parameter to the Current Url
     *
     * @param str $param the paremeter to be added
     *
     * @return void
     */
    public function addToUrl($value) /**accepts a new parameter for the url to take */
    {
        $this->currentUrl = $this->currentUrl.$value;
        $this->nbParameters++;
    }
    
    /**
     * Call the Current Url of an Api and return the json file as an array that contain the data of an article
     *
     * @return array An array that contains all the data of an article
     */
    public function callApi() /** calls the url and returns the json file as an Array */
    {
        //gets json
        $response = file_get_contents(
            str_replace(
                '&amp;',
                '&',
                $this->currentUrl
            )
        );
        
        $this->clear();
        return $response;
    }
    
    /**
     * Gets the number of parameters in the current URL
     *
     * @return Integer The number of parameters in the current URL
     */
    public function getNbParameters()
    {
        return $this->nbParameters;
    }
            
    /**
     * Clear the current Url of an API of all his parameters
     *
     * @return void
     */
    public function clear()
    {
        //reinitalise the url

        $this->currentUrl = $this->baseUrl;
        $this->nbParameters = 0;
    }
}
