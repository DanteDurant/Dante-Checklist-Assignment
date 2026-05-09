<?php

namespace App\Application\Pdf;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

final class PdfExportService
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, bool|string|int|float|null>  $dompdfOptions
     */
    public function download(string $view, array $data, string $filename, array $dompdfOptions = []): Response
    {
        $filename = $this->sanitizeFilename($filename);

        $payload = array_merge([
            'appName' => (string) config('app.name'),
            'generatedAt' => now()->timezone(config('app.timezone')),
        ], $data);

        $pdf = Pdf::loadView($view, $payload)
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', false)
            ->setOption('isPhpEnabled', false);

        foreach ($dompdfOptions as $key => $value) {
            $pdf->setOption((string) $key, $value);
        }

        return $pdf->download($filename);
    }

    private function sanitizeFilename(string $name): string
    {
        $name = preg_replace('/[^\p{L}\p{N}\._\-]+/u', '-', $name) ?? $name;
        $name = trim($name, '-');

        if ($name === '') {
            $name = 'export';
        }

        if (! str_ends_with(strtolower($name), '.pdf')) {
            $name .= '.pdf';
        }

        return mb_substr($name, 0, 180);
    }
}
