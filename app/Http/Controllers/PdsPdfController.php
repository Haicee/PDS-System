<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PdsPdfService;
use App\Repositories\PdsRepository;

class PdsPdfController extends Controller
{
    public function __construct(
        private PdsPdfService $pdfService,
        private PdsRepository $repository,
    ) {}

    public function preview1(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            abort(403, 'Unauthorized');
        }

        // If debug=1, show raw HTML for troubleshooting
        if ($request->boolean('debug')) {
            return $this->renderPdfView($userId);
        }

        $data = $this->pdfService->buildPdfData($userId);
        $html = view('pds_form.pdf', $data + ['pdfMode' => true])->render();

        try {
            // Use streaming response for memory efficiency
            return response()->stream(function () use ($html) {
                if (ob_get_level()) {
                    ob_end_clean();
                }
                echo $this->pdfService->generateStreamedPdf($html);
            }, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="PDS_preview.pdf"',
                'Cache-Control' => 'no-cache, must-revalidate',
            ]);
        } catch (\Exception $e) {
            return response()->view('pds_form.pdf', $data + ['pdfMode' => true, 'pdfError' => $e->getMessage()]);
        }
    }

    public function preview(Request $request)
    {
        $userId = $request->input('user_id', Auth::id());
        if (!$userId) {
            abort(403, 'Unauthorized');
        }

        return $this->renderPdfView($userId);
    }

    public function previewForAdmin(int $user)
    {
        $data = $this->pdfService->buildPdfData($user);
        return view('pds_form.pdf', $data + ['pdfMode' => true]);
    }

    public function downloadForAdmin(int $user)
    {
        $data = $this->pdfService->buildPdfData($user);
        $filename = $this->pdfFilename($data['personal']);
        return $this->pdfService->generatePdf($data, $filename);
    }

    public function download()
    {
        $userId = Auth::id();
        if (!$userId) {
            abort(403, 'Unauthorized');
        }

        $data = $this->pdfService->buildPdfData($userId);
        $filename = $this->pdfFilename($data['personal']);
        return $this->pdfService->generatePdf($data, $filename);
    }

    private function renderPdfView(int $userId)
    {
        $data = $this->pdfService->buildPdfData($userId);
        return view('pds_form.pdf', $data + ['pdfMode' => true]);
    }

    private function pdfFilename(?object $personal): string
    {
        return 'PDS_' . ($personal->surname ?? 'user') . '_' . now()->format('Y-m-d') . '.pdf';
    }
}