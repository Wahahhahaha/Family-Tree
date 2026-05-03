<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

// Load Laravel environment
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "Starting migration for Additional Data System...<br>";

try {
    // 1. Create custom_fields table
    if (!Schema::hasTable('custom_fields')) {
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->string('field_name');
            $table->string('field_type')->default('text'); // text, number, date, select, textarea
            $table->text('field_options')->nullable(); // For select type, store comma-separated or JSON
            $table->boolean('is_required')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
        echo "Table 'custom_fields' created.<br>";
    } else {
        echo "Table 'custom_fields' already exists.<br>";
    }

    // 2. Add additional_fields (JSON) to family_member table
    if (!Schema::hasColumn('family_member', 'additional_fields')) {
        Schema::table('family_member', function (Blueprint $table) {
            $table->json('additional_fields')->nullable()->after('picture');
        });
        echo "Column 'additional_fields' added to 'family_member' table.<br>";
    } else {
        echo "Column 'additional_fields' already exists in 'family_member'.<br>";
    }

    echo "Migration finished successfully.";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
