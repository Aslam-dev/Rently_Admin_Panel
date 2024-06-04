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
        Schema::table('articles', function (Blueprint $table) {
            //
            $table->text('meta_title');
            $table->text('meta_description')->default('Dive into our insightful property articles, where real estate expertise meets inspiration. Uncover trends, tips, and in-depth guides that empower you on your property journey, making informed decisions every step of the way.');
            $table->text('meta_keywords')->default('Real Estate Trends,Property Investment Insights,Home Buying Guides,Selling Strategies,Interior Design Inspirations,Neighborhood Spotlights,Financial Planning for Real Estate,Green and Sustainable Living,Market Analysis Reports,Success Stories in Real Estate');
            $table->string('meta_image')->nullable();
        });
        $rows = \DB::table('articles')->get();
        foreach ($rows as $row) {
            \DB::table('articles')
                ->where('id', $row->id)
                ->update(['meta_title' => $row->title]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('articles', function (Blueprint $table) {
            //
        });
    }
};
