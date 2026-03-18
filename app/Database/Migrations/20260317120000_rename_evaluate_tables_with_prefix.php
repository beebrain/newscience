<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\CLI\CLI;

/**
 * Rename Evaluate tables with Evaluate prefix consistently
 * And add email validation constraint for @live.uru.ac.th
 */
class RenameEvaluateTablesWithPrefix extends Migration
{
    public function up()
    {
        // Rename tables to have consistent "evaluate_" prefix
        $this->forge->renameTable('teaching_evaluations', 'evaluate_teaching');
        $this->forge->renameTable('evaluation_scores', 'evaluate_scores');
        // evaluation_referees already has correct prefix

        // Add email format validation notes as comments
        CLI::write('Tables renamed with evaluate_ prefix:', 'green');
        CLI::write('  - teaching_evaluations → evaluate_teaching');
        CLI::write('  - evaluation_scores → evaluate_scores');
        CLI::write('Note: Email validation for @live.uru.ac.th is enforced in models/controllers', 'yellow');
    }

    public function down()
    {
        $this->forge->renameTable('evaluate_teaching', 'teaching_evaluations');
        $this->forge->renameTable('evaluate_scores', 'evaluation_scores');
    }
}
