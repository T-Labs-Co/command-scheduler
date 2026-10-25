@component('mail::message')
# Scheduler Command Failed

The scheduled command **{{ $task->name }}** has failed.

**Task details:**
- **Signature:** `{{ $task->signature }}`
- **Consecutive Failures:** {{ $task->failure_count }}
- **Last Run:** {{ $task->last_run_at?->format('Y-m-d H:i:s') }}
@if($exitCode !== null)
- **Exit Code:** {{ $exitCode }}
@endif

@if($output)
**Output:**
@component('mail::panel')
```
{{ $output }}
```
@endcomponent
@endif

@component('mail::button', ['url' => config('app.url')])
Go to Dashboard
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
