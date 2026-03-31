<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncLegalCatalog extends Command
{
    protected $signature = 'legal:sync-catalog {--dry-run}';

    protected $description = 'Sync legacy AI Legal Hub hard-coded document types into notary_settings catalog.';

    public function handle(): int
    {
        $docs = [
            ['document_type' => 'sales_agreement', 'category' => 'Business Agreements', 'price' => 3500, 'requires_court_stamp' => false, 'description' => 'Sales agreement / contract of sale.'],
            ['document_type' => 'partnership_deed', 'category' => 'Business Agreements', 'price' => 5000, 'requires_court_stamp' => false, 'description' => 'Partnership deed / agreement.'],
            ['document_type' => 'service_level_agreement', 'category' => 'Business Agreements', 'price' => 4500, 'requires_court_stamp' => false, 'description' => 'Service level agreement.'],
            ['document_type' => 'offer_letter', 'category' => 'Employment & HR', 'price' => 1500, 'requires_court_stamp' => false, 'description' => 'Employment offer letter.'],
            ['document_type' => 'termination_notice', 'category' => 'Employment & HR', 'price' => 2000, 'requires_court_stamp' => false, 'description' => 'Termination notice / letter.'],
            ['document_type' => 'deed_of_assignment', 'category' => 'Property & Rental', 'price' => 15000, 'requires_court_stamp' => true, 'description' => 'Deed of assignment.'],
            ['document_type' => 'affidavit_loss_of_items', 'category' => 'Personal Legal', 'price' => 2500, 'requires_court_stamp' => true, 'description' => 'Affidavit for loss of items.'],
            ['document_type' => 'affidavit_change_of_name', 'category' => 'Personal Legal', 'price' => 2500, 'requires_court_stamp' => true, 'description' => 'Affidavit for change of name.'],
            ['document_type' => 'will_and_testament', 'category' => 'Personal Legal', 'price' => 10000, 'requires_court_stamp' => false, 'description' => 'Last will and testament.'],
        ];

        $dry = (bool) $this->option('dry-run');
        $created = 0;
        $updated = 0;

        foreach ($docs as $doc) {
            $existing = DB::table('notary_settings')->where('document_type', $doc['document_type'])->first();
            if ($existing) {
                if ($dry) {
                    $this->line('Would update: ' . $doc['document_type']);
                } else {
                    DB::table('notary_settings')->where('document_type', $doc['document_type'])->update([
                        'category' => $doc['category'],
                        'price' => $doc['price'],
                        'description' => $doc['description'],
                        'requires_court_stamp' => $doc['requires_court_stamp'],
                        'updated_at' => now(),
                    ]);
                }
                $updated++;
            } else {
                if ($dry) {
                    $this->line('Would insert: ' . $doc['document_type']);
                } else {
                    DB::table('notary_settings')->insert([
                        'document_type' => $doc['document_type'],
                        'category' => $doc['category'],
                        'price' => $doc['price'],
                        'description' => $doc['description'],
                        'requires_court_stamp' => $doc['requires_court_stamp'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $created++;
            }
        }

        $this->info('Catalog sync done.');
        $this->line('Created: ' . $created);
        $this->line('Updated: ' . $updated);
        if ($dry) {
            $this->warn('Dry-run mode: no changes were written.');
        }

        return self::SUCCESS;
    }
}

