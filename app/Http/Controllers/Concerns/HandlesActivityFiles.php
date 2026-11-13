<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Activity;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Shared, private delivery of activity attachments.
 *
 * Controllers must authorize the current user before calling these methods.
 */
trait HandlesActivityFiles
{
    protected function downloadActivityMaterial(Activity $activity): StreamedResponse
    {
        abort_unless($activity->activity_type === 'pdf' && $activity->url_activity, 404);

        return $this->downloadStoredFile($activity->url_activity, $this->friendlyFilename('enunciado', $activity));
    }

    protected function downloadActivityAnswer(Activity $activity): StreamedResponse
    {
        abort_unless($activity->answer_type === 'pdf' && $activity->url_answer, 404);

        return $this->downloadStoredFile($activity->url_answer, $this->friendlyFilename('resolucao', $activity));
    }

    private function friendlyFilename(string $prefix, Activity $activity): string
    {
        return $prefix.'-'.(Str::slug($activity->title) ?: 'atividade').'.pdf';
    }

    private function downloadStoredFile(?string $path, string $name): StreamedResponse
    {
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, $name);
    }
}
