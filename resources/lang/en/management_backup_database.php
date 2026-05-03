<?php

return [
    'title' => 'Backup Database',
    'description' => 'Manage SQL database backups: export the latest data or import a backup file.',
    'export_database' => 'Export Database',
    'export_description' => 'Generate a .sql file from the current database. Recommended before major updates.',
    'export_sql' => 'Export .sql',
    'import_database' => 'Import Database',
    'import_description' => 'Restore the database from a .sql backup file. Make sure the file is valid before importing.',
    'sql_file' => 'SQL File',
    'import_sql' => 'Import .sql',
    'import_confirm' => 'Importing a backup may overwrite existing data based on the SQL file. Continue?',
    'valid_sql_file' => 'Use a valid .sql file, maximum 50MB.',
];
