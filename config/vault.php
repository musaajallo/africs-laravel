<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Secrets vault
    |--------------------------------------------------------------------------
    */

    // Seconds a password confirmation keeps the vault unlocked for reveals.
    'unlock_ttl' => (int) env('VAULT_UNLOCK_TTL', 300),

    // Absolute path to a Python interpreter that has `pykeepass` installed.
    // Leave null to disable the .kdbx export (the XML export still works).
    // e.g. VAULT_KDBX_PYTHON=/home/forge/africs.gm/storage/app/.venv/bin/python
    'kdbx_python' => env('VAULT_KDBX_PYTHON'),

];
