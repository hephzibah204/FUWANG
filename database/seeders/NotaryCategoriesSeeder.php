<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotaryCategoriesSeeder extends Seeder
{
    public function run(): void
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

        foreach ($docs as $doc) {
            DB::table('notary_settings')->updateOrInsert(
                ['document_type' => $doc['document_type']],
                array_merge($doc, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
