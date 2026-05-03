<?php 
use Illuminate\Database\Migrations\Migration; 
use Illuminate\Database\Schema\Blueprint; 
use Illuminate\Support\Facades\Schema; 

return new class extends Migration { 
    public function up() { 
        if (!Schema::hasTable('member_articles')) {
            Schema::create('member_articles', function (Blueprint $table) { 
                $table->integer('member_id')->primary(); 
                $table->text('biography')->nullable(); 
                $table->timestamps(); 
            }); 
        }
        if (!Schema::hasTable('member_documents')) {
            Schema::create('member_documents', function (Blueprint $table) { 
                $table->id(); 
                $table->integer('member_id'); 
                $table->string('doc_type'); 
                $table->string('file_path'); 
                $table->timestamps(); 
            }); 
        }
    } 
    public function down() { 
        Schema::dropIfExists('member_articles'); 
        Schema::dropIfExists('member_documents'); 
    } 
};
