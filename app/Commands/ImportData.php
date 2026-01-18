<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ImportData extends BaseCommand
{
    protected $group = 'Database';
    protected $name = 'import:data';
    protected $description = 'Import scraped data from sci.uru.ac.th into the database';
    protected $usage = 'import:data [options]';
    protected $arguments = [];
    protected $options = [];

    public function run(array $params)
    {
        CLI::write('Starting data import...', 'yellow');
        CLI::newLine();

        $controller = new \App\Controllers\Utility\ImportData();
        $results = $controller->runImport();

        if ($results['success']) {
            CLI::write('Import completed successfully!', 'green');
        } else {
            CLI::write('Import failed!', 'red');
        }

        CLI::newLine();
        CLI::write('Messages:', 'white');
        foreach ($results['messages'] as $message) {
            CLI::write('  - ' . $message, 'light_gray');
        }

        CLI::newLine();
        CLI::write('Import Summary:', 'white');
        CLI::write('  Site Settings: ' . $results['counts']['site_settings'], 'light_gray');
        CLI::write('  Departments: ' . $results['counts']['departments'], 'light_gray');
        CLI::write('  Programs: ' . $results['counts']['programs'], 'light_gray');
        CLI::write('  News Articles: ' . $results['counts']['news'], 'light_gray');
    }
}
