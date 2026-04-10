<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ActiveIngredient;
use Illuminate\Support\Facades\DB;

class ImportActiveIngredients extends Command
{
    protected $signature = 'import:active-ingredients {file}';
    protected $description = 'Import active ingredients from CSV';

    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File không tồn tại!");
            return;
        }

        DB::beginTransaction();

        try {
            $file = fopen($filePath, 'r');

            // bỏ header
            fgetcsv($file);

            while (($row = fgetcsv($file)) !== false) {

                ActiveIngredient::create([
                    'stt' => $row[0] ?? null,
                    'name' => $row[1] ?? null,
                    'dosage_form' => $row[2] ?? null,
                    'hospital_level' => $row[3] ?? null,
                    'note' => $row[4] ?? null,
                    'drug_group' => $row[5] ?? null,
                ]);
            }

            fclose($file);

            DB::commit();
            $this->info("Import thành công!");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Lỗi: " . $e->getMessage());
        }
    }
}
