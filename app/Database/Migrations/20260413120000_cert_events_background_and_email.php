<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CertEventsBackgroundAndEmail extends Migration
{
    public function up(): void
    {
        $db = $this->db;
        if ($db->tableExists('cert_events')) {
            if (! $db->fieldExists('background_file', 'cert_events')) {
                $this->forge->addColumn('cert_events', [
                    'background_file' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 500,
                        'null'       => true,
                        'comment'    => 'Relative path under writable or public for PDF/JPG/PNG background',
                    ],
                ]);
            }
            if (! $db->fieldExists('background_kind', 'cert_events')) {
                $this->forge->addColumn('cert_events', [
                    'background_kind' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 16,
                        'null'       => true,
                        'comment'    => 'pdf or image',
                    ],
                ]);
            }
            if (! $db->fieldExists('layout_json', 'cert_events')) {
                $this->forge->addColumn('cert_events', [
                    'layout_json' => [
                        'type'    => 'TEXT',
                        'null'    => true,
                        'comment' => 'Optional JSON: field_mapping + signature/qr coords override',
                    ],
                ]);
            }
        }

        if ($db->tableExists('cert_event_recipients')) {
            if (! $db->fieldExists('email_sent_at', 'cert_event_recipients')) {
                $this->forge->addColumn('cert_event_recipients', [
                    'email_sent_at' => [
                        'type' => 'DATETIME',
                        'null' => true,
                    ],
                ]);
            }
            if (! $db->fieldExists('email_error', 'cert_event_recipients')) {
                $this->forge->addColumn('cert_event_recipients', [
                    'email_error' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 500,
                        'null'       => true,
                    ],
                ]);
            }
        }
    }

    public function down(): void
    {
        $db = $this->db;
        if ($db->tableExists('cert_events')) {
            foreach (['layout_json', 'background_kind', 'background_file'] as $col) {
                if ($db->fieldExists($col, 'cert_events')) {
                    $this->forge->dropColumn('cert_events', $col);
                }
            }
        }
        if ($db->tableExists('cert_event_recipients')) {
            foreach (['email_error', 'email_sent_at'] as $col) {
                if ($db->fieldExists($col, 'cert_event_recipients')) {
                    $this->forge->dropColumn('cert_event_recipients', $col);
                }
            }
        }
    }
}
