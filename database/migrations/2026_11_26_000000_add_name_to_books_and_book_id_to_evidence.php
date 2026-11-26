<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNameToBooksAndBookIdToEvidence extends Migration
{
    /**
     * Add the columns the models already use and database/schema.sql already defines.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('books', 'name')) {
            Schema::table('books', function (Blueprint $table) {
                $table->string('name')->nullable()->after('region_id');
            });
        }

        if (! Schema::hasColumn('evidence', 'book_id')) {
            Schema::table('evidence', function (Blueprint $table) {
                $table->foreignId('book_id')->nullable()->after('user_id')->constrained('books')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('evidence', function (Blueprint $table) {
            $table->dropConstrainedForeignId('book_id');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
}
