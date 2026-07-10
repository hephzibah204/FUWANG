<?php

namespace App\Console\Commands;

use App\Models\LogisticsRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExportLogisticsPricingDataset extends Command
{
    protected $signature = 'logistics:ai-pricing:export {--path=logistics_ai/pricing_dataset.csv}';

    protected $description = 'Export logistics requests into a CSV dataset for AI pricing training.';

    public function handle(): int
    {
        $path = (string) $this->option('path');
        $tmp = fopen('php://temp', 'w+');
        if (! $tmp) {
            $this->error('Unable to open temp buffer');
            return self::FAILURE;
        }

        $header = [
            'tracking_id',
            'created_at',
            'delivery_type',
            'pickup_method',
            'delivery_method',
            'sender_state',
            'recipient_state',
            'weight',
            'distance_km',
            'amount_charged',
            'base_quote',
            'delta',
        ];
        fputcsv($tmp, $header);

        $query = LogisticsRequest::query()->orderBy('id');
        $total = $query->count();
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->chunk(500, function ($rows) use ($tmp, $bar) {
            foreach ($rows as $r) {
                $base = null;
                if (is_array($r->price_breakdown) && isset($r->price_breakdown['pre_ai_total'])) {
                    $base = (float) $r->price_breakdown['pre_ai_total'];
                } elseif (is_array($r->price_breakdown) && isset($r->price_breakdown['final_total'])) {
                    $base = (float) $r->price_breakdown['final_total'];
                }

                $charged = (float) ($r->amount ?? 0);
                $delta = $base !== null ? ($charged - $base) : null;

                fputcsv($tmp, [
                    $r->tracking_id,
                    $r->created_at?->toISOString(),
                    $r->delivery_type,
                    $r->pickup_method,
                    $r->delivery_method,
                    $r->sender_state,
                    $r->recipient_state,
                    (float) ($r->weight ?? 0),
                    $r->distance_km !== null ? (float) $r->distance_km : null,
                    $charged,
                    $base,
                    $delta,
                ]);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        rewind($tmp);
        $csv = stream_get_contents($tmp);
        fclose($tmp);
        if ($csv === false) {
            $this->error('Unable to read exported CSV');
            return self::FAILURE;
        }

        Storage::disk('local')->put($path, $csv);
        $this->info('Saved dataset to storage/app/' . $path);

        return self::SUCCESS;
    }
}

