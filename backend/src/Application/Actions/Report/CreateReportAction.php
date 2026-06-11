<?php

declare(strict_types=1);

namespace App\Application\Actions\Report;

use App\Infrastructure\Persistence\PdoItemRepository;
use App\Infrastructure\Persistence\PdoReportRepository;
use App\Infrastructure\Persistence\PdoUserRepository;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CreateReportAction
{
    private array  $uploadSettings;
    private string $appUrl;

    public function __construct(
        private PdoReportRepository $reports,
        private PdoItemRepository   $items,
        private PdoUserRepository   $users,
        array                       $uploadSettings,
        string                      $appUrl
    ) {
        $this->uploadSettings = $uploadSettings;
        $this->appUrl         = $appUrl;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface      $response
    ): ResponseInterface {
        $jwt  = $request->getAttribute('jwt');
        $uuid = (int)$jwt->sub;

        // ── Parse multipart body ─────────────────────────────────────────────
        $body       = (array)$request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();

        // ── Allow-list fields ────────────────────────────────────────────────
        $itemname   = trim((string)($body['itemname']    ?? ''));
        $reporttype = trim((string)($body['reporttype']  ?? ''));
        $date       = trim((string)($body['date']        ?? ''));
        $categoryid = (int)($body['categoryid'] ?? 0);
        $locationid = (int)($body['locationid'] ?? 0);
        $contactno  = trim((string)($body['contactno']   ?? ''));

        // ── Validation ───────────────────────────────────────────────────────
        $errors = [];

        if (empty($itemname)) {
            $errors[] = 'Item name is required.';
        }
        if (!in_array($reporttype, ['lost', 'found'], true)) {
            $errors[] = 'Report type must be "lost" or "found".';
        }
        if (empty($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $errors[] = 'Date is required and must be in YYYY-MM-DD format.';
        }
        if ($categoryid <= 0) {
            $errors[] = 'A valid category is required.';
        }
        if ($locationid <= 0) {
            $errors[] = 'A valid location is required.';
        }

        if (!empty($errors)) {
            return $this->json($response, 422, [
                'success' => false,
                'error'   => 'Validation failed.',
                'message' => implode(' ', $errors),
            ]);
        }

        // ── Handle file upload ───────────────────────────────────────────────
        $pngPath = null;

        if (isset($uploadedFiles['image']) && $uploadedFiles['image']->getError() === UPLOAD_ERR_OK) {
            $file    = $uploadedFiles['image'];
            $result  = $this->handleUpload($file);

            if (isset($result['error'])) {
                return $this->json($response, 422, [
                    'success' => false,
                    'error'   => 'File upload failed.',
                    'message' => $result['error'],
                ]);
            }

            $pngPath = $result['path'];
        }

        // ── Transaction: insert item + report ────────────────────────────────
        try {
            // 1. Insert item
            $itemData = [
                'uuid'       => $uuid,
                'categoryid' => $categoryid,
                'locationid' => $locationid,
                'itemname'   => $itemname,
                'totalitems' => 1,
            ];
            if ($pngPath !== null) {
                $itemData['png'] = $pngPath;
            }
            $itemid = $this->items->create($itemData);

            // 2. Insert report
            $reportid = $this->reports->create([
                'uuid'       => $uuid,
                'categoryid' => $categoryid,
                'locationid' => $locationid,
                'itemid'     => $itemid,
                'reporttype' => $reporttype,
                'date'       => $date,
                'status'     => 'open',
            ]);

            // 3. Update user contactno if provided
            if (!empty($contactno)) {
                $this->users->update($uuid, ['contactno' => $contactno]);
            }
        } catch (\Throwable $e) {
            // Roll back the uploaded file if DB insert fails
            if ($pngPath !== null) {
                $this->deleteFile($pngPath);
            }
            return $this->json($response, 500, [
                'success' => false,
                'error'   => 'Database error.',
                'message' => 'Could not create report. Please try again.',
            ]);
        }

        // ── Return full report detail ────────────────────────────────────────
        $report = $this->reports->findById($reportid);

        return $this->json($response, 201, [
            'success' => true,
            'data'    => ['report' => $report],
            'message' => 'Report created successfully.',
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function handleUpload(\Psr\Http\Message\UploadedFileInterface $file): array
    {
        $uploadDir = $this->uploadSettings['dir'];
        $maxSize   = $this->uploadSettings['max_size'];

        // Resolve absolute path
        if (!str_starts_with($uploadDir, '/') && !preg_match('/^[A-Z]:/i', $uploadDir)) {
            $uploadDir = __DIR__ . '/../../../../' . $uploadDir;
        }
        $uploadDir = rtrim(realpath($uploadDir) ?: $uploadDir, '/\\');

        // Size check
        if ($file->getSize() > $maxSize) {
            return ['error' => 'Image must not exceed ' . ($maxSize / 1048576) . 'MB.'];
        }

        // Write to temp and check MIME
        $tmpPath = sys_get_temp_dir() . '/' . uniqid('sf_', true);
        $file->moveTo($tmpPath);

        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($tmpPath);
        $allowed  = ['image/jpeg', 'image/jpg', 'image/png'];

        if (!in_array($mimeType, $allowed, true)) {
            unlink($tmpPath);
            return ['error' => 'Only JPEG and PNG images are allowed.'];
        }

        $ext      = $mimeType === 'image/png' ? 'png' : 'jpg';
        $filename = uniqid('', true) . '.' . $ext;
        $destPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;

        if (!rename($tmpPath, $destPath)) {
            @unlink($tmpPath);
            return ['error' => 'Could not save uploaded file.'];
        }

        return ['path' => 'uploads/' . $filename];
    }

    private function deleteFile(string $relativePath): void
    {
        $uploadDir = $this->uploadSettings['dir'];
        if (!str_starts_with($uploadDir, '/') && !preg_match('/^[A-Z]:/i', $uploadDir)) {
            $uploadDir = __DIR__ . '/../../../../' . $uploadDir;
        }
        $filename = basename($relativePath);
        $fullPath = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }
    }

    private function json(ResponseInterface $r, int $status, array $data): ResponseInterface
    {
        $r = $r->withStatus($status)->withHeader('Content-Type', 'application/json');
        $r->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $r;
    }
}
