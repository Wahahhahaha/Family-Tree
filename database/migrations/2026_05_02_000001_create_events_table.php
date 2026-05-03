<?php 
use Illuminate\Database\Migrations\Migration; 
use Illuminate\Database\Schema\Blueprint; 
use Illuminate\Support\Facades\Schema; 

return new class extends Migration { 
    public function up() { 
        if (!Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table) { 
                $table->id(); 
                $table->string('title'); 
                $table->text('description')->nullable(); 
                $table->dateTime('event_date'); 
                $table->string('location')->nullable(); 
                $table->integer('created_by'); 
                $table->timestamps(); 
                $table->softDeletes(); 
            }); 
        }
        if (!Schema::hasTable('event_responses')) {
            Schema::create('event_responses', function (Blueprint $table) { 
                $table->id(); 
                $table->foreignId('event_id')->constrained('events')->onDelete('cascade'); 
                $table->integer('member_id'); 
                $table->enum('status', ['going', 'not_going', 'maybe']); 
                $table->timestamps(); 
                $table->unique(['event_id', 'member_id']); 
            }); 
        }
    } 
    public function down() { 
        Schema::dropIfExists('event_responses'); 
        Schema::dropIfExists('events'); 
    } 
};
