<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            //
            $table->text('meta_title');
            $table->text('meta_description')->default('Explore our diverse range of properties, curated for every lifestyle. From luxurious residences to charming investment opportunities, discover the perfect category that speaks to your unique vision of home.');
            $table->text('meta_keywords')->default('Lifestyle Homes,Luxury Residences,Investment Properties,Family-Friendly Homes,Urban Living Spaces,Coastal Retreats,Countryside Estates,Modern Apartments,Historic Properties,Sustainable Living Spaces');
            $table->string('meta_image');
        });
        $rows = \DB::table('categories')->get();
        foreach ($rows as $row) {
            \DB::table('categories')
                ->where('id', $row->id)
                ->update(['meta_title' => $row->category]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            //
        });
    }
};
