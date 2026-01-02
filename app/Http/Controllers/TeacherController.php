<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    private function yearGroupsForTeacher(int $teacherId): array
    {
        return Classroom::query()
            ->where('teacher_id', $teacherId)
            ->withCount('students')
            ->orderBy('year_group')
            ->get()
            ->map(fn ($c) => [
                'year'     => "Year {$c->year_group}",
                'students' => $c->students_count,
                'slug'     => $c->id,
            ])
            ->toArray();
    }

    private function ensureOwnsClassroom(Classroom $classroom): void
    {
        abort_unless($classroom->teacher_id === auth()->id(), 403);
    }

    public function index()
    {
        $teacherId  = auth()->id();
        $yearGroups = $this->yearGroupsForTeacher($teacherId);

        return view('teacher.index', compact('yearGroups'));
    }

    public function classView(Classroom $classroom)
    {
        $this->ensureOwnsClassroom($classroom);

        $yearGroups = $this->yearGroupsForTeacher(auth()->id());

        // preload count for display
        $classroom->loadCount('students');

        return view('teacher.classes.view', compact('classroom', 'yearGroups'));
    }

    public function classStudents(Classroom $classroom)
    {
        $this->ensureOwnsClassroom($classroom);

        $yearGroups = $this->yearGroupsForTeacher(auth()->id());

        $students = $classroom->students()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('teacher.classes.students', compact('classroom', 'students', 'yearGroups'));
    }

    public function classReadingList(Classroom $classroom)
    {
        $this->ensureOwnsClassroom($classroom);

        $yearGroups = $this->yearGroupsForTeacher(auth()->id());

        return view('teacher.classes.reading-list', compact('classroom', 'yearGroups'));
    }
}
