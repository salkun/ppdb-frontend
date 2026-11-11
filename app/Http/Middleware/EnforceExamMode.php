<?php

namespace App\Http\Middleware;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\PpdbAccount;
use App\Services\ExamEligibilityService;
use Closure;
use Illuminate\Http\Request;

class EnforceExamMode
{
    public function __construct(private ExamEligibilityService $eligibility)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $accountId = $request->session()->get('account_id');
        $account = $accountId
            ? PpdbAccount::with('registration')
                ->where('id', $accountId)
                ->orWhere('remote_id', $accountId)
                ->first()
            : null;

        if ($account && $this->hasCompletedRequiredExams($account)) {
            $request->session()->forget('exam_mode');
            return $next($request);
        }

        $attempt = $account
            ? ExamAttempt::where('account_id', $account->id)
                ->whereIn('status', ['in_progress', 'suspended'])
                ->orderByDesc('started_at')
                ->first()
            : null;

        $inExamWindow = false;
        if (!$attempt && $account) {
            $registration = $account?->registration;
            if ($registration && !$this->eligibility->missingRequirements($registration)) {
                $types = $this->requiredExamTypes($registration->form_data['major'] ?? null);
                $inExamWindow = Exam::whereIn('type', $types)
                    ->where('published', true)
                    ->whereHas('questions')
                    ->whereNotNull('starts_at')
                    ->whereNotNull('ends_at')
                    ->where('starts_at', '<=', now())
                    ->where('ends_at', '>=', now())
                    ->exists();
            }
        }

        if (!$attempt && !$inExamWindow) {
            $request->session()->forget('exam_mode');
            return $next($request);
        }

        $request->session()->put('exam_mode', true);
        if ($request->routeIs('exams.*')) {
            if ($attempt && $request->routeIs('exams.index') && $attempt->status === 'in_progress') {
                return redirect()->route('exams.take', $attempt->exam_id);
            }
            if ($attempt && $attempt->ends_at->isPast() && $request->routeIs('exams.index')) {
                return redirect()->route('exams.take', $attempt->exam_id);
            }
            return $next($request);
        }

        return $attempt
            ? redirect()->route('exams.take', $attempt->exam_id)
                ->with('warning', 'Anda sedang berada dalam sesi ujian. Fitur portal lainnya dikunci sampai ujian selesai.')
            : redirect()->route('exams.index')
                ->with('warning', 'Jadwal ujian Anda sedang dibuka. Portal dibatasi ke fitur ujian.');
    }

    private function hasCompletedRequiredExams(PpdbAccount $account): bool
    {
        $types = $this->requiredExamTypes($account->registration?->form_data['major'] ?? null);
        if (!$types) {
            return false;
        }

        $exams = Exam::whereIn('type', $types)->get();
        if ($exams->count() !== count($types)) {
            return false;
        }

        return $exams->every(fn (Exam $exam) => ExamAttempt::where('exam_id', $exam->id)
            ->where('account_id', $account->id)
            ->where('status', 'submitted')
            ->exists());
    }

    private function requiredExamTypes(?string $major): array
    {
        return match ($major) {
            'reguler', 'tahfidz' => ['general'],
            'bahasa' => ['general', 'language'],
            'ict' => ['general', 'ict'],
            default => [],
        };
    }
}
