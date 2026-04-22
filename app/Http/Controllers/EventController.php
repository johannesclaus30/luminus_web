<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Venue;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with(['images', 'admin', 'venue'])
            ->orderByDesc('start_date')
            ->paginate(5);
            
        return view('admin_events', compact('events'));
    }

    public function create()
    {
        $event = new Event();

        return view('events.create', compact('event'));
    }

    public function store(Request $request)
    {
        $this->prepareVenueAndPlatformPayload($request);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'max_capacity' => 'required|integer|min:1',
            'event_type' => 'required|in:In-Person,Online,Hybrid',
            'platform' => 'required_if:event_type,Online,Hybrid|nullable|string|max:255',
            'platform_url' => 'nullable|url|max:255',
            'venue_name' => 'nullable|string|max:255',
            'venue_address' => 'required_if:event_type,In-Person,Hybrid|nullable|string|max:255',
            'venue_latitude' => 'nullable|numeric|between:-90,90',
            'venue_longitude' => 'nullable|numeric|between:-180,180',
            'images' => 'nullable|array|max:5',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $adminId = $request->session()->get('admin_id');

        abort_unless($adminId, 403);

        $venueId = $this->syncVenue($request);

        $event = Event::create([
            'admin_id' => $adminId,
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'max_capacity' => $request->max_capacity,
            'event_type' => $request->event_type,
            'platform' => $request->platform,
            'platform_url' => $request->platform_url,
            'venue_id' => $venueId,
            'status' => 'Active',
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $this->storeEventImage($file);

                $event->images()->create([
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()->route('events.index')->with('success', 'Event published successfully!');
    }

    public function edit(Event $event)
    {
        $event->load(['images', 'admin', 'venue']);

        return view('events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $this->prepareVenueAndPlatformPayload($request);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'max_capacity' => 'required|integer|min:1',
            'event_type' => 'required|in:In-Person,Online,Hybrid',
            'platform' => 'required_if:event_type,Online,Hybrid|nullable|string|max:255',
            'platform_url' => 'nullable|url|max:255',
            'venue_name' => 'nullable|string|max:255',
            'venue_address' => 'required_if:event_type,In-Person,Hybrid|nullable|string|max:255',
            'venue_latitude' => 'nullable|numeric|between:-90,90',
            'venue_longitude' => 'nullable|numeric|between:-180,180',
            'images' => 'nullable|array|max:5',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        if ($request->has('deleted_media')) {
            foreach ($request->deleted_media as $mediaId) {
                $media = $event->images()->find($mediaId);
                if ($media) {
                    $this->deleteSupabaseObject($media->image_path);
                    $media->delete();
                }
            }
        }

        $venueId = $this->syncVenue($request, $event->venue_id);

        $event->update([
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'max_capacity' => $request->max_capacity,
            'event_type' => $request->event_type,
            'platform' => $request->platform,
            'platform_url' => $request->platform_url,
            'venue_id' => $venueId,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $this->storeEventImage($image);
                $event->images()->create([
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()
            ->route('events.index')
            ->with('success', 'Event updated successfully!');
    }

    public function destroy(Event $event)
    {
        $event->update(['status' => 'Archived']);

        return redirect()->route('events.index')->with('success', 'Event successfully archived!');
    }

    protected function syncVenue(Request $request, ?int $existingVenueId = null): ?int
    {
        $address = trim((string) $request->input('venue_address', ''));
        $latitude = $request->input('venue_latitude');
        $longitude = $request->input('venue_longitude');

        if ($address === '' || $latitude === null || $longitude === null) {
            return null;
        }

        $venue = $existingVenueId ? Venue::find($existingVenueId) : null;
        $venueData = [
            'name' => trim((string) $request->input('venue_name', $address)) ?: $address,
            'address' => $address,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];

        if ($venue) {
            $venue->update($venueData);

            return $venue->getKey();
        }

        return Venue::create($venueData)->getKey();
    }

    protected function prepareVenueAndPlatformPayload(Request $request): void
    {
        $eventType = $request->input('event_type');

        if ($eventType === 'Online') {
            $request->merge([
                'venue_name' => null,
                'venue_address' => null,
                'venue_latitude' => null,
                'venue_longitude' => null,
            ]);

            return;
        }

        if ($eventType === 'In-Person') {
            $request->merge([
                'platform' => null,
                'platform_url' => null,
            ]);
        }
    }

    protected function storeEventImage($file): string
    {
        $objectKey = 'events_images/' . $this->buildEventImageFileName($file);
        $body = file_get_contents($file->getRealPath());

        if ($body === false) {
            throw ValidationException::withMessages([
                'images' => 'Unable to read the uploaded event image.',
            ]);
        }

        $response = $this->putSupabaseObject($objectKey, $body, $file->getMimeType() ?: 'application/octet-stream');

        if (! $response->successful()) {
            throw ValidationException::withMessages([
                'images' => 'Unable to upload event image to Supabase storage: ' . $this->extractSupabaseError($response->body()),
            ]);
        }

        return $objectKey;
    }

    protected function deleteSupabaseObject(string $objectKey): void
    {
        $response = $this->sendSupabaseRequest('DELETE', $objectKey, '', 'application/octet-stream');

        if (! $response->successful() && $response->status() !== 404) {
            throw ValidationException::withMessages([
                'images' => 'Unable to delete event image from Supabase storage: ' . $this->extractSupabaseError($response->body()),
            ]);
        }
    }

    protected function putSupabaseObject(string $objectKey, string $body, string $contentType)
    {
        return $this->sendSupabaseRequest('PUT', $objectKey, $body, $contentType);
    }

    protected function sendSupabaseRequest(string $method, string $objectKey, string $body, string $contentType)
    {
        $bucket = (string) env('SUPABASE_ADMIN_BUCKET', 'luminus_assets');
        $endpoint = rtrim((string) env('AWS_ENDPOINT'), '/');
        $accessKey = (string) env('AWS_ACCESS_KEY_ID');
        $secretKey = (string) env('AWS_SECRET_ACCESS_KEY');
        $region = (string) env('AWS_DEFAULT_REGION', 'us-east-1');

        if ($endpoint === '' || $accessKey === '' || $secretKey === '') {
            throw ValidationException::withMessages([
                'images' => 'Supabase storage credentials are not configured.',
            ]);
        }

        $parsedEndpoint = parse_url($endpoint);
        $host = $parsedEndpoint['host'] ?? '';
        $endpointPath = trim((string) ($parsedEndpoint['path'] ?? ''), '/');
        $canonicalPrefix = $endpointPath !== '' ? '/' . $endpointPath : '';
        $url = $endpoint . '/' . rawurlencode($bucket) . '/' . $this->encodeSupabasePath($objectKey);
        $amzDate = gmdate('Ymd\THis\Z');
        $dateStamp = gmdate('Ymd');
        $payloadHash = hash('sha256', $body);
        $canonicalUri = $canonicalPrefix . '/' . rawurlencode($bucket) . '/' . $this->encodeSupabasePath($objectKey);

        $canonicalHeaders = [
            'host:' . $host,
            'x-amz-content-sha256:' . $payloadHash,
            'x-amz-date:' . $amzDate,
        ];

        sort($canonicalHeaders);

        $signedHeaders = 'host;x-amz-content-sha256;x-amz-date';
        $canonicalRequest = implode("\n", [
            strtoupper($method),
            $canonicalUri,
            '',
            implode("\n", $canonicalHeaders) . "\n",
            $signedHeaders,
            $payloadHash,
        ]);

        $credentialScope = $dateStamp . '/' . $region . '/s3/aws4_request';
        $stringToSign = implode("\n", [
            'AWS4-HMAC-SHA256',
            $amzDate,
            $credentialScope,
            hash('sha256', $canonicalRequest),
        ]);

        $signingKey = $this->getAwsSignatureKey($secretKey, $dateStamp, $region, 's3');
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        return Http::withHeaders([
            'Host' => $host,
            'x-amz-content-sha256' => $payloadHash,
            'x-amz-date' => $amzDate,
            'Authorization' => 'AWS4-HMAC-SHA256 Credential=' . $accessKey . '/' . $credentialScope . ', SignedHeaders=' . $signedHeaders . ', Signature=' . $signature,
            'Content-Type' => $contentType,
        ])->withBody($body, $contentType)->send($method, $url);
    }

    protected function getAwsSignatureKey(string $secretKey, string $dateStamp, string $regionName, string $serviceName): string
    {
        $kDate = hash_hmac('sha256', $dateStamp, 'AWS4' . $secretKey, true);
        $kRegion = hash_hmac('sha256', $regionName, $kDate, true);
        $kService = hash_hmac('sha256', $serviceName, $kRegion, true);

        return hash_hmac('sha256', 'aws4_request', $kService, true);
    }

    protected function encodeSupabasePath(string $path): string
    {
        return implode('/', array_map(static fn ($segment) => rawurlencode($segment), explode('/', ltrim($path, '/'))));
    }

    protected function extractSupabaseError(string $responseBody): string
    {
        $responseBody = trim($responseBody);

        if ($responseBody === '') {
            return 'No response body returned by Supabase.';
        }

        if (preg_match('/<Message>(.*?)<\/Message>/s', $responseBody, $matches)) {
            return trim(html_entity_decode($matches[1], ENT_QUOTES | ENT_XML1, 'UTF-8'));
        }

        return $responseBody;
    }

    protected function buildEventImageFileName($file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $name = pathinfo((string) $file->getClientOriginalName(), PATHINFO_FILENAME);
        $slug = Str::slug($name);

        if ($slug === '') {
            $slug = 'event-image';
        }

        return $slug . '-' . Str::random(12) . '.' . $extension;
    }
}