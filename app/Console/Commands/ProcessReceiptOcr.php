<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

final class ProcessReceiptOcr extends Command
{
    /** @var string */
    protected $signature = 'receipts:ocr {image : Pad naar bon/receipt afbeelding} {--lang=eng : Tesseract taal (bijv. eng, nld)} {--dry-run : Alleen OCR uitvoeren en resultaat tonen}';

    /** @var string */
    protected $description = 'Voer OCR uit op een bon/receipt afbeelding met Tesseract en extraheer velden';

    public function handle(): int
    {
        $imagePath = (string) $this->argument('image');
        $lang = (string) $this->option('lang');
        $dryRun = (bool) $this->option('dry-run');

        if (!is_file($imagePath)) {
            $this->error("Bestand niet gevonden: {$imagePath}");
            return Command::FAILURE;
        }

        // Controleer of tesseract aanwezig is
        $which = trim((string) shell_exec('which tesseract 2>/dev/null'));
        if ($which === '') {
            $this->error('Tesseract is niet geïnstalleerd of niet in PATH. Installeer via: brew install tesseract');
            return Command::FAILURE;
        }

        $this->info('OCR bezig...');
        $tmpTxt = sys_get_temp_dir() . '/ocr_' . Str::uuid()->toString();
        $cmd = sprintf('tesseract %s %s -l %s 2>/dev/null', escapeshellarg($imagePath), escapeshellarg($tmpTxt), escapeshellarg($lang));
        shell_exec($cmd);

        $textFile = $tmpTxt . '.txt';
        if (!is_file($textFile)) {
            $this->error('OCR mislukt: geen output ontvangen.');
            return Command::FAILURE;
        }

        $text = trim((string) file_get_contents($textFile));
        @unlink($textFile);

        $extracted = $this->extractFields($text);

        $this->line(json_encode([
            'ok' => true,
            'fields' => $extracted,
            'raw' => $dryRun ? $text : null,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return Command::SUCCESS;
    }

    private function extractFields(string $text): array
    {
        $lines = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $text))));

        // Heuristieken voor totaalbedrag (EUR varianten)
        $amountPattern = '/(?:(?:EUR|EURO|€)\s*)?([0-9]{1,3}(?:[\.,][0-9]{3})*[\.,][0-9]{2})/iu';
        $total = null;
        foreach ($lines as $line) {
            if (preg_match('/(totaal|total|sum)[:\s]/iu', $line) && preg_match($amountPattern, $line, $m)) {
                $total = $this->normalizeAmount($m[1]);
                break;
            }
        }
        if ($total === null) {
            // fallback: laatste bedrag in document
            foreach (array_reverse($lines) as $line) {
                if (preg_match($amountPattern, $line, $m)) {
                    $total = $this->normalizeAmount($m[1]);
                    break;
                }
            }
        }

        // Datum detectie (meerdere formaten)
        $date = null;
        $datePatterns = [
            '/\b(\d{2})[\-\.\/]?(\d{2})[\-\.\/]?(\d{2,4})\b/', // 01-10-2025, 01/10/25
            '/\b(\d{4})[\-\.\/]?(\d{2})[\-\.\/]?(\d{2})\b/',     // 2025-10-01
        ];
        foreach ($lines as $line) {
            foreach ($datePatterns as $dp) {
                if (preg_match($dp, $line, $m)) {
                    $date = $this->normalizeDate($m);
                    if ($date) break 2;
                }
            }
        }

        // Vendor/naam heuristiek: eerste niet-lege regel zonder bedrag
        $vendor = null;
        foreach ($lines as $line) {
            if (!preg_match($amountPattern, $line)) {
                $vendor = $line;
                break;
            }
        }

        return [
            'vendor' => $vendor,
            'date' => $date,
            'total' => $total,
        ];
    }

    private function normalizeAmount(string $raw): ?float
    {
        $raw = preg_replace('/[\s€eurEUR]/', '', $raw);
        $raw = str_replace(',', '.', $raw);
        // verwijder duizendtsepunt
        $raw = preg_replace('/\.(?=\d{3}(\D|$))/', '', $raw);
        return is_numeric($raw) ? (float) $raw : null;
    }

    private function normalizeDate(array $m): ?string
    {
        // Probeer diverse varianten naar YYYY-MM-DD
        if (strlen($m[1]) === 4) {
            // Y-m-d
            $y = (int) $m[1]; $mo = (int) $m[2]; $d = (int) $m[3];
        } else {
            // d-m-Y(YY)
            $d = (int) $m[1]; $mo = (int) $m[2]; $y = (int) $m[3];
            if ($y < 100) { $y += 2000; }
        }
        if (checkdate($mo, $d, $y)) {
            return sprintf('%04d-%02d-%02d', $y, $mo, $d);
        }
        return null;
    }
}
