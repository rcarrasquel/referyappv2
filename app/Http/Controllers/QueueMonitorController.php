<?php

namespace App\Http\Controllers;

use App\Jobs\SyncAppointmentToCalJob;
use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class QueueMonitorController extends Controller
{
    public function index(): Response
    {
        $pendingAppointments = Appointment::query()
            ->with(['card:id,name,username', 'user:id,name,email'])
            ->where('cal_sync_status', 'pending')
            ->latest('created_at')
            ->limit(100)
            ->get()
            ->map(fn (Appointment $item): array => $this->serializeAppointment($item))
            ->values()
            ->all();

        $syncedAppointments = Appointment::query()
            ->with(['card:id,name,username', 'user:id,name,email'])
            ->where('cal_sync_status', 'synced')
            ->latest('cal_synced_at')
            ->limit(100)
            ->get()
            ->map(fn (Appointment $item): array => $this->serializeAppointment($item))
            ->values()
            ->all();

        $failedAppointments = Appointment::query()
            ->with(['card:id,name,username', 'user:id,name,email'])
            ->where('cal_sync_status', 'failed')
            ->latest('updated_at')
            ->limit(100)
            ->get()
            ->map(fn (Appointment $item): array => $this->serializeAppointment($item))
            ->values()
            ->all();

        $queuedJobs = DB::table('jobs')
            ->select(['id', 'queue', 'attempts', 'reserved_at', 'available_at', 'created_at'])
            ->orderBy('id')
            ->limit(200)
            ->get()
            ->map(static fn ($item): array => [
                'id' => (int) $item->id,
                'queue' => (string) $item->queue,
                'attempts' => (int) $item->attempts,
                'reserved_at' => $item->reserved_at ? now()->createFromTimestamp((int) $item->reserved_at)->toIso8601String() : null,
                'available_at' => now()->createFromTimestamp((int) $item->available_at)->toIso8601String(),
                'created_at' => now()->createFromTimestamp((int) $item->created_at)->toIso8601String(),
            ])
            ->values()
            ->all();

        $failedJobs = DB::table('failed_jobs')
            ->select(['id', 'uuid', 'queue', 'failed_at'])
            ->orderByDesc('id')
            ->limit(200)
            ->get()
            ->map(static fn ($item): array => [
                'id' => (int) $item->id,
                'uuid' => (string) $item->uuid,
                'queue' => (string) $item->queue,
                'failed_at' => (string) $item->failed_at,
            ])
            ->values()
            ->all();

        return Inertia::render('Modules/QueueMonitor', [
            'appointments' => [
                'pending' => $pendingAppointments,
                'synced' => $syncedAppointments,
                'failed' => $failedAppointments,
            ],
            'jobs' => [
                'queued' => $queuedJobs,
                'failed' => $failedJobs,
            ],
        ]);
    }

    public function retryAppointment(Appointment $appointment): RedirectResponse
    {
        $appointment->update([
            'cal_sync_status' => 'pending',
            'cal_sync_error' => null,
            'cal_synced_at' => null,
        ]);

        SyncAppointmentToCalJob::dispatch((string) $appointment->id, 'create');

        return back()->with('status', 'Appointment sync queued again.');
    }

    public function deleteQueuedJob(int $jobId): RedirectResponse
    {
        DB::table('jobs')->where('id', $jobId)->delete();
        return back()->with('status', 'Queued job removed.');
    }

    public function retryFailedJob(int $failedJobId): RedirectResponse
    {
        Artisan::call('queue:retry', ['id' => [$failedJobId]]);
        return back()->with('status', 'Failed job re-queued.');
    }

    public function deleteFailedJob(int $failedJobId): RedirectResponse
    {
        DB::table('failed_jobs')->where('id', $failedJobId)->delete();
        return back()->with('status', 'Failed job removed.');
    }

    private function serializeAppointment(Appointment $item): array
    {
        return [
            'id' => $item->id,
            'full_name' => $item->full_name,
            'card_name' => $item->card?->name,
            'card_username' => $item->card?->username,
            'owner_name' => $item->user?->name,
            'owner_email' => $item->user?->email,
            'starts_at' => optional($item->starts_at)->toIso8601String(),
            'status' => $item->status,
            'cal_sync_status' => $item->cal_sync_status,
            'cal_sync_error' => $item->cal_sync_error,
            'cal_synced_at' => optional($item->cal_synced_at)->toIso8601String(),
            'created_at' => optional($item->created_at)->toIso8601String(),
        ];
    }
}

