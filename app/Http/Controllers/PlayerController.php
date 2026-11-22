<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Report;
use App\Models\User;
use App\Notifications\PlayerReported;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class PlayerController extends Controller
{
    public function show(User $user): View
    {
        return view('players.show', [
            'player' => $user,
        ]);
    }

    public function report(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 403);

        $validated = $request->validate([
            'reason' => ['required', Rule::in(array_keys(Report::REASONS))],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'reason.required' => 'Pick a reason.',
            'reason.in' => 'Pick a reason.',
            'note.max' => 'Keep the note under 1000 characters.',
        ]);

        $note = trim((string) ($validated['note'] ?? ''));

        $open = Report::query()
            ->where('reporter_id', $request->user()->id)
            ->where('reported_id', $user->id)
            ->where('status', Report::OPEN)
            ->exists();

        if ($open) {
            return back()
                ->withErrors(['report' => 'You already reported them.'])
                ->withInput();
        }

        try {
            $report = Report::query()->create([
                'reporter_id' => $request->user()->id,
                'reported_id' => $user->id,
                'reason' => $validated['reason'],
                'note' => $note === '' ? null : $note,
                'status' => Report::OPEN,
            ]);
        } catch (UniqueConstraintViolationException) {
            return back()
                ->withErrors(['report' => 'You already reported them.'])
                ->withInput();
        }

        $this->emailReport($report);

        return back()->with('status', 'Report sent.');
    }

    public function block(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 403);

        try {
            Block::query()->firstOrCreate([
                'blocker_id' => $request->user()->id,
                'blocked_id' => $user->id,
            ]);
        } catch (UniqueConstraintViolationException) {
        }

        return back()->with('status', 'Blocked.');
    }

    public function unblock(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 403);

        Block::query()
            ->where('blocker_id', $request->user()->id)
            ->where('blocked_id', $user->id)
            ->delete();

        return back()->with('status', 'Unblocked.');
    }

    private function emailReport(Report $report): void
    {
        $email = config('app.contact_email');

        if (! is_string($email) || trim($email) === '') {
            return;
        }

        try {
            Notification::route('mail', trim($email))->notify(new PlayerReported($report));
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
