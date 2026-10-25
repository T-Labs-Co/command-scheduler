<?php

namespace TLabsCo\CommandScheduler\Core\Scheduling\Application\Handlers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use TLabsCo\CommandScheduler\Events\SchedulerFailureEvent;

/**
 * Listener for notifying admins about scheduler failures.
 */
class NotifyAdminOnSchedulerFailure
{
    /**
     * Handle the event.
     */
    public function handle(SchedulerFailureEvent $event): void
    {
        if (! $event->shouldNotifyAdmin()) {
            return;
        }

        $this->logFailure($event);

        $channels = config('command-scheduler.notifications.channels', []);

        if (in_array('email', $channels)) {
            $this->sendEmailNotification($event);
        }

        if (in_array('slack', $channels)) {
            $this->sendSlackNotification($event);
        }
    }

    /**
     * Log the failure.
     */
    protected function logFailure(SchedulerFailureEvent $event): void
    {
        Log::channel('command_scheduler')->error('[CommandScheduler] Scheduled command failed', [
            'task_name' => $event->task->name,
            'signature' => $event->task->signature,
            'failure_count' => $event->task->failure_count,
            'exit_code' => $event->exitCode,
            'output' => $event->output,
        ]);
    }

    /**
     * Send email notification.
     */
    protected function sendEmailNotification(SchedulerFailureEvent $event): void
    {
        $recipients = config('command-scheduler.notifications.email.recipients', []);

        if (empty($recipients)) {
            return;
        }

        try {
            Mail::send(['markdown' => 'command-scheduler::emails.failure'], [
                'task' => $event->task,
                'output' => $event->output,
                'exitCode' => $event->exitCode,
            ], function ($message) use ($recipients, $event): void {
                $message->to($recipients)
                    ->subject("Scheduler Failure: {$event->task->name}");
            });
        } catch (\Throwable $e) {
            report($e); // send to crash guard
            Log::channel('command_scheduler')->error('[CommandScheduler] Failed to send scheduler failure email', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send Slack notification via webhook.
     */
    protected function sendSlackNotification(SchedulerFailureEvent $event): void
    {
        try {
            $webhookUrl = config('command-scheduler.notifications.slack.webhook_url');
            if (empty($webhookUrl)) {
                return;
            }

            $payload = $this->formatSlackPayload($event);
            Http::post($webhookUrl, $payload);
        } catch (\Throwable $e) {
            report($e); // send to crash guard
            Log::channel('command_scheduler')->error('[CommandScheduler] Failed to send scheduler failure slack notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Format the Slack webhook payload.
     *
     * @return array<string, mixed>
     */
    protected function formatSlackPayload(SchedulerFailureEvent $event): array
    {
        $fields = [
            ['title' => 'Task', 'value' => $event->task->name, 'short' => true],
            ['title' => 'Signature', 'value' => "`{$event->task->signature}`", 'short' => true],
            ['title' => 'Failures', 'value' => (string) $event->task->failure_count, 'short' => true],
            ['title' => 'Last Run', 'value' => $event->task->last_run_at?->format('Y-m-d H:i:s') ?? '-', 'short' => true],
        ];

        if ($event->exitCode !== null) {
            $fields[] = ['title' => 'Exit Code', 'value' => (string) $event->exitCode, 'short' => true];
        }

        if ($event->output) {
            $output = str($event->output)->limit(200);
            $fields[] = ['title' => 'Output', 'value' => "```\n{$output}\n```", 'short' => false];
        }

        return [
            'blocks' => [
                [
                    'type' => 'header',
                    'text' => ['type' => 'plain_text', 'text' => '🚨 Scheduler Command Failed'],
                ],
                [
                    'type' => 'section',
                    'fields' => $fields,
                ],
            ],
        ];
    }
}
