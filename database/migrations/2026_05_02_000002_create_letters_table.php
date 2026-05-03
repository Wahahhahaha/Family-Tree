<?php 
use Illuminate\Database\Migrations\Migration; 
use Illuminate\Database\Schema\Blueprint; 
use Illuminate\Support\Facades\Schema; 

return new class extends Migration { 
    public function up() { 
        if (!Schema::hasTable('letters')) {
            Schema::create('letters', function (Blueprint $table) { 
                $table->id(); 
                $table->integer('sender_id'); 
                $table->integer('receiver_id'); 
                $table->string('subject');
                $table->text('content'); 
                $table->timestamp('read_at')->nullable();
                $table->timestamps(); 
            }); 
        }
    } 
    public function down() { 
        Schema::dropIfExists('letters'); 
    } 
};
