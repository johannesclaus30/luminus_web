<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PerkController extends Controller
{
    private const PERK_STATUS_ACTIVE = 1;

    private function resolveImageUrl(Request $request, ?string $imagePath): string
    {
        $trimmedPath = trim((string) $imagePath);

        if ($trimmedPath === '') {
            return $trimmedPath;
        }

        if (preg_match('/^https?:\/\//i', $trimmedPath)) {
            if (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?/i', $trimmedPath)) {
                $parsedPath = parse_url($trimmedPath, PHP_URL_PATH) ?: '';
                $parsedQuery = parse_url($trimmedPath, PHP_URL_QUERY);

                $resolvedUrl = rtrim($request->getSchemeAndHttpHost(), '/') . '/' . ltrim($parsedPath, '/');

                if ($parsedQuery) {
                    $resolvedUrl .= '?' . $parsedQuery;
                }

                return $resolvedUrl;
            }

            return $trimmedPath;
        }

        $normalizedPath = ltrim($trimmedPath, '/');

        $publicBaseUrl = rtrim((string) config('filesystems.disks.s3.url', ''), '/');
        $bucketName = trim((string) config('filesystems.disks.s3.bucket', ''), '/');

        if ($publicBaseUrl !== '' && $bucketName !== '') {
            return $publicBaseUrl . '/' . $bucketName . '/' . ltrim($normalizedPath, '/');
        }

        if (str_starts_with($normalizedPath, 'storage/')) {
            $normalizedPath = substr($normalizedPath, strlen('storage/'));
        }

        return rtrim($request->getSchemeAndHttpHost(), '/') . '/storage/' . ltrim($normalizedPath, '/');
    }

    public function index(Request $request)
    {
        $imagesTable = 'images_perks';

        $perks = DB::table('perks')
            ->where(function ($query) {
                $query->whereNull('status')->orWhere('status', self::PERK_STATUS_ACTIVE);
            })
            ->orderByDesc('created_at')
            ->get();

        $images = collect();

        if (Schema::hasTable($imagesTable)) {
            $images = DB::table($imagesTable)
            ->orderBy('created_at')
            ->orderBy('id')
                ->get()
                ->groupBy('perk_id');
        }

        $payload = $perks->map(function ($perk) use ($images) {
            $perksImages = $images->get($perk->id, collect())->map(function ($image) {
                $imagePath = is_string($image->image_path) ? trim($image->image_path) : null;

                return [
                    'id' => $image->id,
                    'image_path' => $imagePath,
                    'image_url' => $this->resolveImageUrl(request(), $imagePath),
                ];
            })->values();

            return [
                'id' => $perk->id,
                'title' => $perk->title,
                'description' => $perk->description,
                'valid_until' => $perk->valid_until,
                'status' => (int) ($perk->status ?? self::PERK_STATUS_ACTIVE),
                'images' => $perksImages,
            ];
        })->values();

        return response()->json([
            'perks' => $payload,
        ]);
    }
}