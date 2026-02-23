<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Response;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Str;
use \Carbon\Carbon;

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
            ->with('user')
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

    do {
        $username = $colours->random() . $animals->random() . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    } while (User::where('username', $username)->exists());

    return $username;
    }

    // Create Students
    public function storeStudents(Request $request, Classroom $classroom)
    {
        $this->ensureOwnsClassroom($classroom);
        $studentsCreated = 0;

        $now = now();
        $academicYearStart = $now->month >= 9 ? $now->year : $now->year - 1;
        $expectedAge = $classroom->year_group + 4;

        // Normal DOB
        $minDob = Carbon::create($academicYearStart - $expectedAge - 1, 9, 1)->format('Y-m-d');
        $maxDob = Carbon::create($academicYearStart - $expectedAge, 8, 31)->format('Y-m-d');

        // Expanded DOB (special students)
        $expandedMinDob = Carbon::create($academicYearStart - $expectedAge - 3, 9, 1)->format('Y-m-d');
        $expandedMaxDob = Carbon::create($academicYearStart - $expectedAge + 2, 8, 31)->format('Y-m-d');

        $validated = $request->validate([
            'students'              => 'required|array|min:1',
            'students.*.first_name' => 'required|string|min:2|max:255',
            'students.*.last_name'  => 'required|string|min:2|max:255',
            'students.*.level'      => 'nullable|integer',
            'students.*.is_special' => 'nullable',
        ]);

        // Validate DOB and check w/ special students
        foreach ($request->input('students') as $index => $studentData) {
            $isSpecial = isset($studentData['is_special']); // if checkbox is ticked

            $min = $isSpecial ? $expandedMinDob : $minDob;
            $max = $isSpecial ? $expandedMaxDob : $maxDob;

            $request->validate([
                "students.{$index}.dob" => "required|date|after_or_equal:{$min}|before_or_equal:{$max}",
            ]);
        }

        foreach ($validated['students'] as $index => $studentData) {
            // Merge DOB and special status when creating students
            $studentData['dob'] = $request->input("students.{$index}.dob");
            $studentData['is_special'] = $request->has("students.{$index}.is_special") ? 1 : 0;
            $this->createStudent($classroom, $studentData);
            $studentsCreated++;
        }

        $message = $studentsCreated === 1
            ? "1 new student added!"
            : "{$studentsCreated} new students added!";

        return redirect()
            ->route('teacher.classes.students', $classroom->id)
            ->with('success', $message);
    }

    // Show import form
    public function showImportForm(Classroom $classroom)
    {
        $this->ensureOwnsClassroom($classroom);
        $yearGroups = $this->yearGroupsForTeacher(auth()->id());

        return view('teacher.classes.importStudents', compact('classroom', 'yearGroups'));
    }

    // Store imported students from CSV
    public function importStudents(Request $request, Classroom $classroom)
    {
        // Longer time limit for larger file imports
        set_time_limit(300);
        ini_set('max_execution_time', 300);
        
        // Check if teacher owns classroom
        $this->ensureOwnsClassroom($classroom);
        
        // validate file
        $validated = $request->validate([
            'students_csv' => 'required|file|mimes:csv,txt|max:2048',
        ]);
        
        // CSV file
        try {
            $file = $request->file('students_csv');
            
            // Check if file is valid
            if (!$file) {
                return redirect()
                    ->back()
                    ->with('error', 'No file was uploaded.');
            }
            
            // Open file
            $handle = fopen($file->getRealPath(), 'r');
            
            // Check if file is successfully opened
            if ($handle === false) {
                return redirect()
                    ->back()
                    ->with('error', 'Could not open the CSV file.');
            }
            
            // Read header row
            $header = fgetcsv($handle, 1000, ',');
            
            // Validate header
            if ($header === false) {
                fclose($handle);
                return redirect()
                    ->back()
                    ->with('error', 'CSV file is empty or invalid.');
            }
            
            // Headers
            $studentsData = [];
            
            // Collect all student data
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                // Skip empty rows
                if (empty(array_filter($data))) {
                    continue;
                }
                
                $row = array_combine($header, $data);
                
                // Parse DOB
                $dob = null;
                if (!empty($row['Date of Birth'])) {
                    try {
                        $dob = date('Y-m-d', strtotime($row['Date of Birth']));
                    } catch (\Exception $e) {
                        $dob = null;
                    }
                }
                
                // create student data array
                $studentData = [
                    'first_name' => trim($row['First Name'] ?? ''),
                    'last_name'  => trim($row['Last Name'] ?? ''),
                    'username'   => trim($row['Username'] ?? ''),
                    'dob'        => $dob,
                    'level'      => !empty($row['Level']) ? (int)$row['Level'] : null,
                    'active'     => !empty($row['Active']) && strtolower(trim($row['Active'])) === 'yes',
                ];
                
                // skip if first name or last name is empty
                if (empty($studentData['first_name']) || empty($studentData['last_name'])) {
                    continue;
                }
                
                $studentsData[] = $studentData;
            }
            
            fclose($handle);
            
            // Use DB transaction
            \DB::beginTransaction();
            // Used to check if there are any students created/linked/skipped
            // If so, the transaction rolls back and 0 students are added = better than some added
            
            // Get each student
            try {
                $studentsCreated = 0;
                $studentsLinked = 0;
                $studentsSkipped = 0;
                
                // Loop through each student
                foreach ($studentsData as $studentData) {
                    // Check if username already exists
                    if (!empty($studentData['username'])) {
                        $existingUser = User::where('username', $studentData['username'])->first();
                        
                        // If user exists and is a student
                        if ($existingUser && $existingUser->role === 'Student') {
                            $existingStudent = Student::where('user_id', $existingUser->id)->first();
                            
                            if ($existingStudent) {
                                // Check if students are already in the classrtoom
                                if ($classroom->students()->where('student_id', $existingStudent->id)->exists()) {
                                    $studentsSkipped++;
                                    continue;
                                }
                                
                                // Link existing student to this classroom
                                $classroom->students()->attach($existingStudent->id, [
                                    'active' => $studentData['active'] ?? true,
                                ]);
                                $studentsLinked++;
                                continue;
                            }
                        }
                    }
                    
                    // Check if student with same name and DOB already exists in this school
                    $query = Student::where('school_id', $classroom->school_id)
                        ->where('first_name', $studentData['first_name'])
                        ->where('last_name', $studentData['last_name']);
                    
                    if (!empty($studentData['dob'])) {
                        $query->where('date_of_birth', $studentData['dob']);
                    }
                    
                    $existingStudent = $query->first();
                    
                    if ($existingStudent) {
                        // Check if already in this classroom
                        if ($classroom->students()->where('student_id', $existingStudent->id)->exists()) {
                            $studentsSkipped++;
                            continue;
                        }
                        
                        // Link existing student to this classroom
                        $classroom->students()->attach($existingStudent->id, [
                            'active' => $studentData['active'] ?? true,
                        ]);
                        $studentsLinked++;
                    } else {
                        // Create new student
                        $this->createStudent($classroom, $studentData);
                        $studentsCreated++;
                    }
                }
                
                \DB::commit();

                // Success messages
                $message = [];
                if ($studentsCreated > 0) {
                    if ($studentsCreated === 1) {
                        $message[] = "1 new student added!";;
                    } else {
                        $message[] = "{$studentsCreated} new students added!";
                    }
                }
                if ($studentsLinked > 0) {
                    if ($studentsLinked === 1) {
                        $message[] = "1 existing student joined the classroom!";
                    } else {
                        $message[] = "{$studentsLinked} existing students joined the classroom!";
                    }
                }
                if ($studentsSkipped > 0) {
                    if ($studentsSkipped === 1) {
                        $message[] = "1 student is already in the classroom!";
                    } else {
                        $message[] = "{$studentsSkipped} students are already in the classroom!";
                    }
                }
                
                return redirect()
                    ->route('teacher.classes.students', $classroom->id)
                    ->with('success', implode(', ', $message));
                    
            } catch (\Exception $e) {
                \DB::rollBack();
                throw $e;
            }
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error importing students: ' . $e->getMessage());
        }
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
            'name'          => 'nullable|string|max:255',
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

    // Remove student
    public function removeStudent(Classroom $classroom, int $studentId)
    {
        $this->ensureOwnsClassroom($classroom);
        
        // Find the student
        $student = $classroom->students()->findOrFail($studentId);
        // Detach from classroom and remove the pivot record too
        $classroom->students()->detach($studentId);

        return redirect()->back()->with('success', 'Student removed successfully.');
    }

    // Remove all students
    public function removeAllStudents(Classroom $classroom)
    {
        $this->ensureOwnsClassroom($classroom);
        
        // Detach all students from classroom
        $classroom->students()->detach();

        return redirect()->back()->with('success', 'All students removed successfully.');
    }


    // Create student
    protected function createStudent(Classroom $classroom, array $data): Student
    {
        // Create username and password
        $username = $this->createStudentUsername();
        $normalpassword = Str::password(10);
        
        $randomPfp = '/images/pfp/' . collect(['lamb.png','cat.png','dog.png','penguin.png','raccoon.png','owl.png','pig.png','wolf.png'])->random();

        // Create user
        $user = User::create([
            'name'      => $data['first_name'] . ' ' . $data['last_name'],
            'username'  => $username,
            'email'     => null,              
            'phone'     => null,
            'password'  => bcrypt($normalpassword),
            'role'      => 'Student',
            'pfp'       => $randomPfp,
            'school_id' => $classroom->school_id,
        ]);

        // Link user to student
        $student = Student::create([
            'user_id'        => $user->id,
            'school_id'      => $classroom->school_id,
            'first_name'     => $data['first_name'],
            'last_name'      => $data['last_name'],
            'date_of_birth'  => $data['dob'] ?? null,
            'level'          => $data['level'] ?? $classroom->year_group,
            'pfp'            => $randomPfp,
            'active'         => $data['active'] ?? true,
            'is_special'     => $data['is_special'] ?? false,
        ]);
        
        // Attach to classroom
        $classroom->students()->attach($student->id, [
            'active' => $data['active'] ?? true,
        ]);

        return $student;
    }
}
