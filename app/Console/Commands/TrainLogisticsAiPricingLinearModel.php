<?php

namespace App\Console\Commands;

use App\Models\LogisticsAiPricingModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TrainLogisticsAiPricingLinearModel extends Command
{
    protected $signature = 'logistics:ai-pricing:train-linear 
        {--path=logistics_ai/pricing_dataset.csv}
        {--activate}
        {--version=}
        {--learning_rate=0.000001}
        {--epochs=800}';

    protected $description = 'Train a simple linear model (gradient descent) for AI pricing adjustments and store the model version.';

    public function handle(): int
    {
        $path = (string) $this->option('path');
        if (! Storage::disk('local')->exists($path)) {
            $this->error('Dataset not found: storage/app/' . $path);
            return self::FAILURE;
        }

        $csv = Storage::disk('local')->get($path);
        $lines = preg_split("/\r\n|\n|\r/", (string) $csv);
        if (! $lines || count($lines) < 2) {
            $this->error('Dataset is empty');
            return self::FAILURE;
        }

        $header = str_getcsv(array_shift($lines));
        $idx = array_flip($header);
        foreach (['distance_km', 'weight', 'delivery_type', 'pickup_method', 'delivery_method', 'delta'] as $col) {
            if (! array_key_exists($col, $idx)) {
                $this->error('Missing column: ' . $col);
                return self::FAILURE;
            }
        }

        $featureKeys = [
            'distance_km',
            'weight',
            'is_express',
            'is_overnight',
            'is_same_day',
            'is_home_pickup',
            'is_home_delivery',
        ];

        $X = [];
        $y = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $row = str_getcsv($line);
            $delta = $row[$idx['delta']] ?? null;
            if ($delta === null || $delta === '' || ! is_numeric($delta)) {
                continue;
            }
            $distance = is_numeric($row[$idx['distance_km']] ?? null) ? (float) $row[$idx['distance_km']] : 0.0;
            $weight = is_numeric($row[$idx['weight']] ?? null) ? (float) $row[$idx['weight']] : 0.0;
            $deliveryType = (string) ($row[$idx['delivery_type']] ?? 'standard');
            $pickupMethod = (string) ($row[$idx['pickup_method']] ?? 'center_dropoff');
            $deliveryMethod = (string) ($row[$idx['delivery_method']] ?? 'home_delivery');

            $vec = [
                'distance_km' => $distance,
                'weight' => $weight,
                'is_express' => $deliveryType === 'express' ? 1.0 : 0.0,
                'is_overnight' => $deliveryType === 'overnight' ? 1.0 : 0.0,
                'is_same_day' => $deliveryType === 'same_day' ? 1.0 : 0.0,
                'is_home_pickup' => $pickupMethod === 'home_pickup' ? 1.0 : 0.0,
                'is_home_delivery' => $deliveryMethod === 'home_delivery' ? 1.0 : 0.0,
            ];

            $X[] = $vec;
            $y[] = (float) $delta;
        }

        $n = count($X);
        if ($n < 50) {
            $this->error('Not enough labeled rows with delta (need at least 50). Export more data first.');
            return self::FAILURE;
        }

        $weights = ['bias' => 0.0];
        foreach ($featureKeys as $k) {
            $weights[$k] = 0.0;
        }

        $lr = (float) $this->option('learning_rate');
        $epochs = (int) $this->option('epochs');
        if ($epochs < 1) {
            $epochs = 1;
        }

        $bar = $this->output->createProgressBar($epochs);
        $bar->start();

        for ($e = 0; $e < $epochs; $e++) {
            $grad = ['bias' => 0.0];
            foreach ($featureKeys as $k) {
                $grad[$k] = 0.0;
            }

            $mse = 0.0;
            for ($i = 0; $i < $n; $i++) {
                $pred = $weights['bias'];
                foreach ($featureKeys as $k) {
                    $pred += $weights[$k] * (float) ($X[$i][$k] ?? 0);
                }
                $err = $pred - $y[$i];
                $mse += $err * $err;

                $grad['bias'] += $err;
                foreach ($featureKeys as $k) {
                    $grad[$k] += $err * (float) ($X[$i][$k] ?? 0);
                }
            }

            $invN = 1.0 / $n;
            $weights['bias'] -= $lr * (2.0 * $invN) * $grad['bias'];
            foreach ($featureKeys as $k) {
                $weights[$k] -= $lr * (2.0 * $invN) * $grad[$k];
            }

            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        $mse = $this->mse($X, $y, $weights, $featureKeys);
        $rmse = sqrt($mse);

        $version = (string) ($this->option('version') ?: now()->format('YmdHis'));

        $model = LogisticsAiPricingModel::query()->create([
            'version' => $version,
            'feature_keys' => $featureKeys,
            'weights' => $weights,
            'multiplier' => 1.0,
            'metrics' => [
                'rows' => $n,
                'rmse_delta' => $rmse,
                'learning_rate' => $lr,
                'epochs' => $epochs,
            ],
            'trained_at' => now(),
            'is_active' => false,
        ]);

        if ($this->option('activate')) {
            LogisticsAiPricingModel::query()->where('id', '!=', $model->id)->update(['is_active' => false]);
            $model->update(['is_active' => true]);
        }

        $this->info('Stored model version: ' . $model->version);
        $this->info('Delta RMSE: ' . number_format($rmse, 4));
        $this->info('Active: ' . ($model->is_active ? 'yes' : 'no'));

        return self::SUCCESS;
    }

    private function mse(array $X, array $y, array $weights, array $keys): float
    {
        $n = count($X);
        $sum = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $pred = (float) ($weights['bias'] ?? 0);
            foreach ($keys as $k) {
                $pred += (float) ($weights[$k] ?? 0) * (float) ($X[$i][$k] ?? 0);
            }
            $err = $pred - (float) $y[$i];
            $sum += $err * $err;
        }
        return $n > 0 ? $sum / $n : 0.0;
    }
}

