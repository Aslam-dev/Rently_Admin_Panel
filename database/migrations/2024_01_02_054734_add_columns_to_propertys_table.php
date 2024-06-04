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
        Schema::table('propertys', function (Blueprint $table) {
            //
            $table->text('meta_title');
            $table->text('meta_description')->default('Explore the epitome of real estate excellence with eBroker. Discover a seamless property buying and selling experience backed by expert guidance. Your key to unlocking the doors to dream homes awaits!');
            $table->text('meta_keywords')->default('Real Estate Brokerage,Property Listings,Home Buying,Home Selling,Real Estate Deals,Expert Realtors,House Hunting,Property Investments,Residential Properties,Commercial Real Estate');
            $table->string('meta_image')->nullable();
        });
        $rows = \DB::table('propertys')->get();
        foreach ($rows as $row) {
            \DB::table('propertys')
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
        Schema::table('propertys', function (Blueprint $table) {
            //
        });
    }
};
