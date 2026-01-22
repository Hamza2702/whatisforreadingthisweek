<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Response;
use App\Models\User;
use Illuminate\Support\Str;

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

    // Add Students
    public function addStudents(Classroom $classroom)
    {
        $this->ensureOwnsClassroom($classroom);
        $yearGroups = $this->yearGroupsForTeacher(auth()->id());

        return view('teacher.classes.addStudents', compact('classroom', 'yearGroups'));
    }

    // Create student usernames
    protected function createStudentUsername(): string
    {
        $colours = collect([
            'Red', 'Blue', 'Green', 'Yellow', 'Purple', 'Orange', 'Pink', 'Brown', 'Black', 'White', 'Grey',
            'Gold', 'Silver', 'Cyan', 'Magenta', 'Lime', 'Teal', 'Navy', 'Maroon', 'Olive', 'Coral', 'Turquoise',
            'Violet', 'Indigo', 'Amber', 'Crimson', 'Azure', 'Beige', 'Lavender', 'Mint', 'Peach', 'Salmon', 'Tan',
            'Chocolate', 'Plum', 'Rose', 'Sapphire', 'Emerald',
        ]);

        $animals = collect([
            'Lion', 'Tiger', 'Bear', 'Wolf', 'Fox', 'Eagle', 'Hawk', 'Shark', 'Dolphin', 'Whale', 'Penguin',
            'Kangaroo', 'Panda', 'Giraffe', 'Zebra', 'Elephant', 'Cheetah', 'Leopard', 'Rabbit', 'Deer',
            'Otter', 'Raccoon', 'Squirrel', 'Badger', 'Hedgehog', 'Turtle', 'Frog', 'Toad', 'Snake', 'Lizard',
            'Butterfly', 'Bee', 'Ant', 'Dragonfly', 'Ladybug', 'Cat', 'Dog', 'Mouse', 'Rat', 'Hamster', 'Raccoon',
            'Owl', 'Parrot', 'Flamingo', 'Peacock',
        ]);

    $username = $colours->random() . $animals->random() . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

    return $username;
    }

    // Create Students
    public function storeStudents(Request $request, Classroom $classroom)
    {
        $this->ensureOwnsClassroom($classroom);

        // check validation
        $validated = $request->validate([
            'students' => 'required|array|min:1',
            'students.*.first_name' => 'required|string|max:255',
            'students.*.last_name'  => 'required|string|max:255',
            'students.*.dob'        => 'nullable|date',
            'students.*.level'      => 'nullable|integer',
        ]);

        foreach ($validated['students'] as $studentData) {

            // username
            $username = $this->createStudentUsername();

            $normalpassword = Str::password(10);
            $hashedpassword = bcrypt($normalpassword);

            $user = \App\Models\User::create([
                'name'      => $studentData['first_name'] . ' ' . $studentData['last_name'],
                'username'  => $username,
                'email'     => null,              
                'phone'     => null,
                'password'  => hashedpassword,
                'role'      => 'Student',
                'pfp'       => '/images/pfp/' . collect(['lamb.png','cat.png','dog.png','penguin.png','raccoon.png','owl.png','pig.png','wolf.png'])->random(),
                'school_id' => $classroom->school_id,
            ]);

            // create student linked to user and classroom
            $classroom->students()->create([
                'user_id'        => $user->id,
                'school_id'      => $classroom->school_id,
                'first_name'     => $studentData['first_name'],
                'last_name'      => $studentData['last_name'],
                'date_of_birth'  => $studentData['dob'] ?? null,
                'level'          => $studentData['level'] ?? $classroom->year_group,
                'pfp'            => '/images/pfp/' . collect(['lamb.png','cat.png','dog.png','penguin.png','raccoon.png','owl.png','pig.png','wolf.png'])->random(),
                'active'         => true,
            ]);
        }


        return redirect()
            ->route('teacher.classes.students', $classroom->id)
            ->with('success', 'Students added successfully.');
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
