<?php

namespace App\Services;

use Spatie\Browsershot\Browsershot;
use App\Repositories\PdsRepository;
use Illuminate\Support\Facades\Storage;

class PdsPdfService
{
    private PdsRepository $repository;
    private static ?string $cachedChromePath = null;
    private static ?string $cachedNodePath = null;
    private static ?string $cachedNpmPath = null;
    private static bool $pathsResolved = false;

    public function __construct(PdsRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Build PDF data using repository for optimized data access.
     */
    public function buildPdfData(int $userId): array
    {
        $pdsData = $this->repository->getAllPdsData($userId);

        $family = $pdsData['family'];
        $education = $pdsData['education'];
        $training = $pdsData['training'];
        $signatureFiles = $pdsData['signatureFiles'];

        // Build derived data structures
        $extraEduTables = $this->repository->buildExtraEducationTables($education);
        $extraTrainingTables = $this->repository->buildExtraTrainingTables($training['added']);

        // Optimize images - don't load full content into memory
        $signatureUrl = $this->getOptimizedImageUrl($signatureFiles?->signature_file_path);
        $photoUrl = $this->getOptimizedImageUrl($signatureFiles?->photo_file_path);

        return [
            'personal' => $pdsData['personal'],
            'address' => $pdsData['address'],
            'contact' => $pdsData['contact'],
            'idInfo' => $pdsData['idInfo'],
            'declaration' => $pdsData['declaration'],
            'family' => $family,
            'spouse' => $family->where('type', 'spouse')->first(),
            'father' => $family->where('type', 'father')->first(),
            'mother' => $family->where('type', 'mother')->first(),
            'children' => $family->where('type', 'child')->values(),
            'education' => $education,
            'extraEduTables' => $extraEduTables,
            'eligibilities' => $pdsData['eligibilities'],
            'work' => $pdsData['workExperiences'],
            'voluntary' => $pdsData['voluntaryWorks'],
            'training' => $training['main'],
            'extraTrainingTables' => $extraTrainingTables,
            'otherInfo' => $pdsData['otherInfo'],
            'references' => $pdsData['references'],
            'remarks' => $pdsData['remarks'],
            'signatureUrl' => $signatureUrl,
            'photoUrl' => $photoUrl,
        ];
    }

    /**
     * Generate PDF with optimized memory usage.
     */
    public function generatePdf(array $data, string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(
            function () use ($data) {
                // Clear output buffer to prevent memory issues
                if (ob_get_level()) {
                    ob_end_clean();
                }

                $html = view('pds_form.pdf', $data + ['pdfMode' => true])->render();
                $pdfBinary = $this->makeShot($html)->pdf();

                echo $pdfBinary;
            },
            $filename,
            [
                'Content-Type' => 'application/pdf',
                'Cache-Control' => 'no-cache, must-revalidate',
            ]
        );
    }

    /**
     * Generate PDF from HTML string and return binary for streaming.
     */
    public function generateStreamedPdf(string $html): string
    {
        return $this->makeShot($html)->pdf();
    }

    /**
     * Get optimized image URL - use file path instead of base64 when possible.
     */
    private function getOptimizedImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $storagePath = storage_path('app/public/' . $path);
        if (!file_exists($storagePath)) {
            return null;
        }

        // For files under 50KB, use base64 inline
        $size = filesize($storagePath);
        if ($size < 50 * 1024) {
            $content = file_get_contents($storagePath);
            $mime = mime_content_type($storagePath) ?: 'image/png';
            return 'data:' . $mime . ';base64,' . base64_encode($content);
        }

        // For larger files, return public URL (Browsershot can access via file://)
        return $storagePath;
    }

    /**
     * Create Browsershot instance with cross-platform support.
     */
    private function makeShot(string $html): Browsershot
    {
        // Sanitize HTML to remove file:// references
        $html = str_ireplace([
            'file:///', 'file:\\', 'file://', 'file:/', 'file:\\/', 'file:\\', 'file:\\//'
        ], '', $html);
        $html = preg_replace('#file:[^\s"' . "\n" . '<>]+#i', '', $html);

        // Cross-platform binary detection
        $chromePath = $this->detectChromePath();
        $nodePath = $this->detectNodePath();
        $npmPath = $this->detectNpmPath();

        $shot = Browsershot::html($html)
            ->paperSize(8.5, 13, 'in')
            ->margins(10, 10, 10, 10)
            ->scale(0.56)
            ->emulateMedia('print')
            ->showBackground()
            ->setOption('printBackground', true)
            ->timeout(60)
            ->noSandbox()
            ->hideHeaderAndFooter()
            ->setOption('args', [
                '--disable-dev-shm-usage',
                '--disable-gpu',
                '--no-first-run',
                '--disable-extensions',
                '--disable-software-rasterizer',
                '--disable-background-networking',
            ]);

        if ($nodePath) {
            $shot->setNodeBinary($nodePath);
        }
        if ($npmPath) {
            $shot->setNpmBinary($npmPath);
        }
        if ($chromePath) {
            $shot->setChromePath($chromePath);
        }

        return $shot;
    }

    /**
     * Detect Chrome binary path across platforms.
     */
    private function resolvePathsOnce(): void
    {
        if (self::$pathsResolved) return;
        self::$cachedChromePath = $this->findChromePath();
        self::$cachedNodePath = $this->findNodePath();
        self::$cachedNpmPath = $this->findNpmPath();
        self::$pathsResolved = true;
    }

    private function detectChromePath(): ?string
    {
        $this->resolvePathsOnce();
        return self::$cachedChromePath;
    }

    private function detectNodePath(): ?string
    {
        $this->resolvePathsOnce();
        return self::$cachedNodePath;
    }

    private function detectNpmPath(): ?string
    {
        $this->resolvePathsOnce();
        return self::$cachedNpmPath;
    }

    private function findChromePath(): ?string
    {
        $envPath = env('BROWSERSHOT_CHROME_PATH');
        if ($envPath && is_file($envPath)) {
            return $envPath;
        }

        $possiblePaths = [];

        if (PHP_OS_FAMILY === 'Windows') {
            $possiblePaths = [
                'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
                'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
                getenv('LOCALAPPDATA') . '\\Google\\Chrome\\Application\\chrome.exe',
                getenv('PROGRAMFILES') . '\\Google\\Chrome\\Application\\chrome.exe',
            ];
        } elseif (PHP_OS_FAMILY === 'Darwin') {
            $possiblePaths = [
                '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
                '/usr/bin/google-chrome',
                '/usr/bin/chromium',
            ];
        } else {
            $possiblePaths = [
                '/usr/bin/google-chrome',
                '/usr/bin/google-chrome-stable',
                '/usr/bin/chromium',
                '/usr/bin/chromium-browser',
                '/snap/bin/chromium',
            ];
        }

        foreach ($possiblePaths as $path) {
            if ($path && is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Detect Node.js binary path across platforms.
     */
    private function findNodePath(): ?string
    {
        $envPath = env('BROWSERSHOT_NODE_PATH');
        if ($envPath && is_file($envPath)) {
            return $envPath;
        }

        $possiblePaths = [];

        if (PHP_OS_FAMILY === 'Windows') {
            $possiblePaths = [
                'C:\\Program Files\\nodejs\\node.exe',
                'C:\\Program Files (x86)\\nodejs\\node.exe',
                getenv('PROGRAMFILES') . '\\nodejs\\node.exe',
            ];
        } else {
            $possiblePaths = [
                '/usr/bin/node',
                '/usr/local/bin/node',
                '/opt/node/bin/node',
            ];
        }

        foreach ($possiblePaths as $path) {
            if ($path && is_file($path)) {
                return $path;
            }
        }

        // Try which command
        $which = shell_exec('which node 2>/dev/null');
        if ($which) {
            $trimmed = trim($which);
            if (is_file($trimmed)) {
                return $trimmed;
            }
        }

        return null;
    }

    /**
     * Detect NPM binary path across platforms.
     */
    private function findNpmPath(): ?string
    {
        $envPath = env('BROWSERSHOT_NPM_PATH');
        if ($envPath && is_file($envPath)) {
            return $envPath;
        }

        $possiblePaths = [];

        if (PHP_OS_FAMILY === 'Windows') {
            $possiblePaths = [
                'C:\\Program Files\\nodejs\\npm.cmd',
                'C:\\Program Files (x86)\\nodejs\\npm.cmd',
                getenv('PROGRAMFILES') . '\\nodejs\\npm.cmd',
            ];
        } else {
            $possiblePaths = [
                '/usr/bin/npm',
                '/usr/local/bin/npm',
                '/opt/node/bin/npm',
            ];
        }

        foreach ($possiblePaths as $path) {
            if ($path && is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
