<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$siti = \App\Models\User::where('name', 'like', '%siti nurbaya%')->first();
$rio = \App\Models\User::where('name', 'like', '%rio%')->first();

if (!$siti || !$rio) {
    echo "Siti or Rio not found.\n";
    exit;
}

echo "Siti: " . $siti->id . " | Rio: " . $rio->id . "\n";

$guru = $siti;
$siswa = $rio;

// Find subject
$subject_name = 'Agama';
if ($guru->subjectSmp) {
    $subject_name = $guru->subjectSmp->subject_name;
}

echo "Subject: $subject_name\n";

// Find assignment by Siti
$assignment = \App\Models\Assignment::where('guru_id', $guru->id)->first();
if (!$assignment) {
    echo "Creating Assignment...\n";
    $assignment = \App\Models\Assignment::create([
        'guru_id' => $guru->id,
        'tingkat' => 7,
        'subject_name' => $subject_name,
        'title' => 'Tugas Agama 1',
        'description' => 'Kerjakan halaman 10',
    ]);
}

// Find submission
$submission = \App\Models\AssignmentSubmission::where('assignment_id', $assignment->id)
    ->where('siswa_id', $siswa->id)->first();

if (!$submission) {
    echo "Creating Submission...\n";
    $submission = \App\Models\AssignmentSubmission::create([
        'assignment_id' => $assignment->id,
        'siswa_id' => $siswa->id,
        'content' => 'Ini tugas saya',
        'status' => 'graded',
        'grade' => 90
    ]);
} else {
    $submission->grade = 90;
    $submission->status = 'graded';
    $submission->save();
}

// Check StudentScore
$score = \App\Models\StudentScore::where('siswa_id', $siswa->id)
    ->where('guru_id', $guru->id)
    ->where('subject_name', $subject_name)
    ->first();

echo "Current Student Score: " . ($score ? json_encode($score->toArray()) : 'Not found') . "\n";

// Simulate sync
\App\Models\StudentScore::updateOrCreate(
    [
        'siswa_id' => $submission->siswa_id,
        'guru_id' => $guru->id,
        'subject_name' => $subject_name,
    ],
    [
        'tingkat' => $assignment->tingkat,
        'nilai_tugas' => 90,
    ]
);

$score = \App\Models\StudentScore::where('siswa_id', $siswa->id)
    ->where('guru_id', $guru->id)
    ->where('subject_name', $subject_name)
    ->first();
echo "After sync Score: " . ($score ? json_encode($score->toArray()) : 'Not found') . "\n";
