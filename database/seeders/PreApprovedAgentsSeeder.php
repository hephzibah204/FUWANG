<?php

namespace Database\Seeders;

use App\Models\PreApprovedAgent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class PreApprovedAgentsSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = 'C:\Users\hephz\Documents\DOC-20260922-WA0011.xlsx';

        if (!file_exists($filePath)) {
            $this->command?->error("Excel file not found at {$filePath}");
            return;
        }

        try {
            $pythonScript = <<<PYTHON
import openpyxl, json, sys

filePath = r"C:\Users\hephz\Documents\DOC-20260922-WA0011.xlsx"
wb = openpyxl.load_workbook(filePath)
sheet = wb.active
rows = list(sheet.iter_rows(values_only=True))

output = []
for row in rows[3:]:
    if not row or len(row) < 6:
        continue
    sn, first_name, last_name, email, phone, agent_code = row[0], row[1], row[2], row[3], row[4], row[5]
    if agent_code and str(agent_code).strip():
        output.append({
            'first_name': str(first_name).strip() if first_name else '',
            'last_name': str(last_name).strip() if last_name else '',
            'email': str(email).strip().lower() if email else '',
            'phone_number': str(phone).strip() if phone else '',
            'agent_code': str(agent_code).strip()
        })

print(json.dumps(output))
PYTHON;

            $tmpScript = sys_get_temp_dir() . '/parse_agents.py';
            file_put_contents($tmpScript, $pythonScript);

            $output = shell_exec("python " . escapeshellarg($tmpScript));
            @unlink($tmpScript);

            $data = json_decode($output, true);

            if (!is_array($data) || empty($data)) {
                $this->command?->warn("No records extracted from Excel.");
                return;
            }

            $count = 0;
            foreach ($data as $item) {
                $fullName = trim(($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? ''));

                PreApprovedAgent::updateOrCreate(
                    ['agent_code' => $item['agent_code']],
                    [
                        'first_name' => $item['first_name'],
                        'last_name' => $item['last_name'],
                        'full_name' => $fullName,
                        'email' => $item['email'],
                        'phone_number' => $item['phone_number'],
                    ]
                );
                $count++;
            }

            $this->command?->info("Successfully seeded {$count} pre-approved existing enrollment agents.");
        } catch (\Throwable $e) {
            Log::error('PreApprovedAgentsSeeder failed: ' . $e->getMessage());
            $this->command?->error('Seeder failed: ' . $e->getMessage());
        }
    }
}
