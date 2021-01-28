<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\API\ApiCaller;

class AddAcronymUrlToColloquesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('_colloques', function (Blueprint $table) {
            $table->string('acronym', 20)->after('name');
            $table->string('url', 100)->after('acronym');
        });
        $caller = new ApiCaller('https://dblp.org/search/venue/api?format=json');
        $query = DB::table('_colloques')
                    ->select('_colloques.id', '_colloques.name')
                    ->where('_colloques.type', 'conference')
                    ->get()->toArray();
        for ($i=0; $i < count($query); $i++) {
            $apiQuery = preg_replace('/[“”"]/', '', $query[$i]->name); // list of unwanted characters
            $colloque_id = $query[$i]->id;
            $apiQuery = urlencode($apiQuery);
            $caller->addToUrl("&q={$apiQuery}");
            $caller->addToUrl("&h=1");
            $caller->addToUrl("&f=0");
            $response = $caller->callApi();
            /** converts json to array */
            $jsonArray = json_decode($response, true);
            /** check data before insert */
            if ($jsonArray["result"]["status"]["@code"] != 200 || array_key_exists("hit", $jsonArray["result"]["hits"]) == false || array_key_exists("acronym", $jsonArray["result"]["hits"]["hit"][0]["info"]) == false) {
                DB::table('_colloques')
                    ->where('id', $colloque_id)
                    ->update(['acronym' => 'Others']);

                DB::table('_colloques')
                    ->where('id', $colloque_id)
                    ->update(['url'=> 'https://dblp.org/db/conf/']);
            } else {
                $update = [
                    'acronym' => $jsonArray["result"]["hits"]["hit"][0]["info"]["acronym"],
                    'url' => $jsonArray["result"]["hits"]["hit"][0]["info"]["url"]
                ];
                DB::table('_colloques')
                    ->where('id', $colloque_id)
                    ->update($update);
            }
        }
    }

    public function updateColloqueTable()
    {
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('_colloques', function (Blueprint $table) {
            $table->dropColumn('acronym');
            $table->dropColumn('url');
        });
    }
}
