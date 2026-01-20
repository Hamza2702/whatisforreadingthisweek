<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Response;


class TeacherController extends Controller
{
    // Get year groups for a teacher
    private function yearGroupsForTeacher(int $teacherId): array
    {
        return Classroom::query()
            ->where('teacher_id', $teacherId)
            ->withCount('students')
            ->orderBy('year_group')
            ->get()
            ->map(fn ($c) => [
                'year'     => "{$c->year_group}",
                'name'     => $c->name,
                'students' => $c->students_count,
                // classroom id as slug
                'slug'     => $c->id,
            ])
            ->toArray();
    }

    // Teacher must own the classroom
    private function ensureOwnsClassroom(Classroom $classroom): void
    {
        abort_unless($classroom->teacher_id === auth()->id(), 403);
    }

    // Display teacher dashboard with year groups
    public function index()
    {
        $teacherId  = auth()->id();
        $yearGroups = $this->yearGroupsForTeacher($teacherId);

        return view('teacher.index', compact('yearGroups'));
    }

    // Display class overview
    public function classView(Classroom $classroom)
    {
        $this->ensureOwnsClassroom($classroom);

        $yearGroups = $this->yearGroupsForTeacher(auth()->id());

        // student count
        $classroom->loadCount('students');

        return view('teacher.classes.view', compact('classroom', 'yearGroups'));
    }

    // Display list of students in the class
    public function classStudents(Classroom $classroom)
    {
        $this->ensureOwnsClassroom($classroom);

        // student count
        $classroom->loadCount('students');

        $yearGroups = $this->yearGroupsForTeacher(auth()->id());

        $students = $classroom->students()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('teacher.classes.students', compact('classroom', 'students', 'yearGroups'));
    }

    // Display reading list for the class
    public function classReadingList(Classroom $classroom)
    {
        $this->ensureOwnsClassroom($classroom);

        // student count
        $classroom->loadCount('students');

        $yearGroups = $this->yearGroupsForTeacher(auth()->id());

        return view('teacher.classes.reading-list', compact('classroom', 'yearGroups'));
    }

    // Export student list CSV
    public function exportStudents(Classroom $classroom)
    {
        $fileName = "Year_" . $classroom->year_group . "_". $classroom->name . '_StudentsList.csv';

        $headers = [
            'Content-Type' => 'text/csv',
        ];

        // get students 
        return response()->streamDownload(function () use ($classroom) {
            $handle = fopen('php://output', 'w');

            // header row
            fputcsv($handle, [
                'First Name',
                'Last Name',
                'Username',
                'Level',
                'Date of Birth',
                'Active',
            ]);

            // data rows
            foreach ($classroom->students as $student) {
                fputcsv($handle, [
                    $student->first_name,
                    $student->last_name,
                    optional($student->user)->username,
                    $student->level,
                    optional($student->date_of_birth)->format('Y-m-d'),
                    $student->active ? 'Yes' : 'No',
                ]);
            }

            fclose($handle);
        }, $fileName, $headers);
    }

    // Create class
    public function createClass()
    {
        $yearGroups = $this->yearGroupsForTeacher(auth()->id());

        return view('teacher.classes.create', compact('yearGroups'));
    }

    // Store class
    public function storeClass(Request $request)
    {   
        // validate inputs
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'year_group'    => 'required|numeric|min:0|max:6',
            'academic_start'    => 'required|integer|min:0|max:99',
            'academic_end'      => 'required|integer|min:0|max:99',
        ]);

        // stage and academic year
        $stage = $validated['year_group'] <= 2 ? 'KS1' : 'KS2';
        $academicYear = $validated['academic_start'] . '-' . $validated['academic_end'];

        // create classroom
        Classroom::create([
            'school_id'     => auth()->user()->school_id,
            'teacher_id'    => auth()->id(),
            'name'          => $validated['name'],
            'year_group'    => $validated['year_group'],
            'stage'         => $stage,
            'academic_year' => $academicYear,
            'academic_start' => $validated['academic_start'],
            'academic_end'   => $validated['academic_end'],
            'active'        => true,
        ]);

        return redirect()->route('teacher.index')->with('success', 'Class created successfully.');
    }

}
