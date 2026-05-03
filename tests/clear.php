<?php
echo shell_exec('php Family-Tree-main/hai/artisan optimize:clear 2>&1');
echo shell_exec('php Family-Tree-main/hai/artisan config:clear 2>&1');
echo shell_exec('php Family-Tree-main/hai/artisan cache:clear 2>&1');
echo 'DONE';