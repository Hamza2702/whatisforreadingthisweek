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
use App\Models\Book;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Exceptions\Handler;

class TeacherController extends Controller
{
    // ===================================================================
    // Kitten username words
    // ===================================================================
    protected array $kittenAdjectives = [
        'Fluffy', 'Tiny', 'Soft', 'Fuzzy', 'Cuddly', 'Silky', 'Velvet', 'Plump', 'Sleek', 'Striped',
        'Spotted', 'Patchy', 'Ginger', 'Snowy', 'Ebony', 'Creamy', 'Dusty', 'Misty', 'Smoky', 'Tabby',
        'Cute', 'Sweet', 'Playful', 'Sleepy', 'Purring', 'Lazy', 'Bouncy', 'Curious', 'Sneaky', 'Grumpy',
        'Cheeky', 'Jolly', 'Witty', 'Brave', 'Shy', 'Dainty', 'Sassy', 'Dizzy', 'Clumsy', 'Peppy',
        'Magic', 'Cosmic', 'Starry', 'Dreamy', 'Lucky', 'Sparkly', 'Rainbow', 'Bubbly', 'Zesty', 'Glittery',
        'Funky', 'Jazzy', 'Snazzy', 'Fancy', 'Royal', 'Mighty', 'Speedy', 'Nifty', 'Cozy', 'Hyper',
        'Wobbly', 'Scruffy', 'Shaggy', 'Perky', 'Jumpy', 'Loopy', 'Zippy', 'Dozy', 'Nosy', 'Quirky',
    ];

    protected array $kittenNames = [
        'Kitten', 'Kitty', 'Cat', 'Paws', 'Whiskers', 'Mittens', 'Furball', 'Tabby', 'Moggy', 'Tomcat',
        'Mochi', 'Biscuit', 'Waffle', 'Pancake', 'Muffin', 'Cookie', 'Brownie', 'Pudding', 'Custard', 'Toffee',
        'Butterscotch', 'Caramel', 'Cheddar', 'Pretzel', 'Nugget', 'Noodle', 'Dumpling', 'Pickle', 'Peanut', 'Cocoa',
        'Petal', 'Blossom', 'Meadow', 'Clover', 'Acorn', 'Hazel', 'Willow', 'Daisy', 'Fern', 'Briar',
        'Snuggle', 'Cuddle', 'Bubble', 'Doodle', 'Sprinkle', 'Twinkle', 'Dimple', 'Freckle', 'Marble', 'Pebble',
        'Meow', 'Purr', 'Mrow', 'Nap', 'Zoomie', 'Floof', 'Boop', 'Bonk', 'Chirp', 'Trill',
    ];

    // Get year groups for a teacher
    private function yearGroupsForTeacher(int $teacherId): array
    {
        return Classroom::query()
            ->where('teacher_id', $teacherId)
            ->withCount(['students' => fn ($q) => $q->where('classroom_student.active', 1)])
            ->orderBy('year_group')
            ->get()
            ->map(fn ($c) => [
                'year'     => "{$c->year_group}",
                'name'     => $c->name,
                'students' => $c->students_count,
                // classroom id as slug
                'slug'     => $c->id,
                'active'   => $c->active,
                'academic_year' => $c->academic_year,
                'is_progressed' => $c->is_progressed,
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
        $user = auth()->user();

        $teacherId  = auth()->id();
        $yearGroups = $this->yearGroupsForTeacher($teacherId);

        $headteacherStats = null;

        if ($user->role === 'headteacher') {
            $schoolId = $user->school_id;

            // get all teachers in the school with their classrooms
            $teachers = User::where('school_id', $schoolId)
                ->whereIn('role', ['teacher', 'headteacher'])
                ->with('classrooms') 
                ->get();

            // get stats for headteacher dashboard
            $headteacherStats = [
                'total_teachers' => $teachers->count(),
                'total_classrooms' => Classroom::where('school_id', $schoolId)->count(),
                'teachers_data' => $teachers,
            ];
        }

        return view('teacher.index', compact('yearGroups', 'headteacherStats'));
    }

    // Display list of students in the class
    public function classStudents(Classroom $classroom)
    {
        $this->ensureOwnsClassroom($classroom);

        // student count
        $classroom->loadCount('students');

        $yearGroups = $this->yearGroupsForTeacher(auth()->id());

        $students = $classroom->students()
            ->wherePivot('active' , 1)
            ->with('user')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('teacher.classes.students', compact('classroom', 'students', 'yearGroups'));
    }

    // Display reading list for the class
    public function classReadingList(Classroom $classroom)
    {   
        // make sure they are a teacher that owns the classroom
        $this->ensureOwnsClassroom($classroom);
        $students = $classroom->students()->wherePivot('active', 1)->get();

        // loop through students
        foreach ($students as $student) {
        $student->ort_colour = $this->getOxfordColour($student->level);
        
        // get 10 random books at students reading level
        $student->recommendedBooks = Book::where('ort_level', $student->level)
            ->wherePivot('active', 1)
            ->inRandomOrder()
            ->take(10)
            ->get();
        }

        // student count
        $classroom->loadCount('students');

        $yearGroups = $this->yearGroupsForTeacher(auth()->id());

        return view('teacher.classes.reading-list', compact('classroom', 'yearGroups', 'students'));
    }

    // Convert ort to colour
    private function getOxfordColour($level) {
        return match((int)$level) {
            0 => 'Light Purple',
            1 => 'Pink',
            2 => 'Red',
            3 => 'Yellow', 
            4 => 'Light Blue',
            5 => 'Green',
            6 => 'Orange',
            7 => 'Turquoise', 
            8 => 'Purple',
            9 => 'Gold',
            10 => 'White', 
            11 => 'Lime', 
            12 => 'Lime+',
            13, 14 => 'Grey',
            15, 16 => 'Dark Blue', 
            17, 18, 19, 20 => 'Dark Red',
            default => 'Dark Red',
        };
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
        do {
            $adjective = $this->kittenAdjectives[array_rand($this->kittenAdjectives)];
            $name      = $this->kittenNames[array_rand($this->kittenNames)];
            $number    = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
            $username  = $adjective . $name . $number;
        } while (User::where('username', $username)->exists());

        return $username;
    }

    // ===================================================================
    // Download and store PFP from Robohash
    // stored at storage/app/public/pfp
    // ===================================================================
    protected function downloadKittenPfp(string $username): string
    {
        // create directory
        Storage::disk('public')->makeDirectory('pfp/kittens');

        // set path w/ image
        $storagePath = 'pfp/kittens/' . $username . '.png';

        // Reuse if already downloaded
        if (Storage::disk('public')->exists($storagePath)) {
            return Storage::url($storagePath);
        }

        // set=set4 = robohash kittens
        // bgset=bg1 = coloured background
        // size = 200x200
        $robohashUrl = sprintf(
            'https://robohash.org/%s?set=set4&bgset=bg1&size=200x200',
            urlencode($username)
        );

        try {
            $response = Http::timeout(15)->get($robohashUrl);
            
            // set
            if ($response->successful()) {
                Storage::disk('public')->put($storagePath, $response->body());
                return Storage::url($storagePath);
            }

        } catch (\Exception $e) {
            //
        }

        // just set to cat if it doesnt work
        return '/images/pfp/cat.png';
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

        // Expanded DOB (exceptional students)
        $expandedMinDob = Carbon::create($academicYearStart - $expectedAge - 3, 9, 1)->format('Y-m-d');
        $expandedMaxDob = Carbon::create($academicYearStart - $expectedAge + 2, 8, 31)->format('Y-m-d');

        $validated = $request->validate([
            'students'                  => 'required|array|min:1',
            'students.*.first_name'     => ['required', 'string', 'min:2', 'max:255', 'regex:/^[\p{L}\s\'\-]+$/u'],
            'students.*.last_name'      => ['required', 'string', 'min:2', 'max:255', 'regex:/^[\p{L}\s\'\-]+$/u'],
            'students.*.level'          => 'nullable|integer|min:0|max:20',
            'students.*.is_exceptional' => 'nullable',
        ], [
            // Custom error messages so users see something friendly instead of "regex format invalid"
            'students.*.first_name.regex' => 'First name can only contain letters, spaces, hyphens, and apostrophes.',
            'students.*.last_name.regex'  => 'Last name can only contain letters, spaces, hyphens, and apostrophes.',
        ]);

        // Validate DOB and check w/ exceptional students
        foreach ($request->input('students') as $index => $studentData) {
            $isExceptional = isset($studentData['is_exceptional']);

            $min = $isExceptional ? $expandedMinDob : $minDob;
            $max = $isExceptional ? $expandedMaxDob : $maxDob;

            $request->validate([
                "students.{$index}.dob" => "required|date|after_or_equal:{$min}|before_or_equal:{$max}",
            ]);
        }

        // ====================================================
        // check for active elsewhere before creating any students
        $conflicts = [];
        foreach ($validated['students'] as $index => $studentData) {
            $dob = $request->input("students.{$index}.dob");

            // existing
            $existing = Student::where('school_id', $classroom->school_id)
                ->whereRaw('LOWER(first_name) = ?', [strtolower($studentData['first_name'])])
                ->whereRaw('LOWER(last_name) = ?', [strtolower($studentData['last_name'])])
                ->whereDate('date_of_birth', $dob)
                ->first();

            if ($existing) {
                $activeElsewhere = DB::table('classroom_student')
                    ->where('student_id', $existing->id)
                    ->where('classroom_id', '!=', $classroom->id)
                    ->where('active', 1)
                    ->exists();
                
                if ($activeElsewhere) {
                    $conflicts[] = "{$existing->first_name} {$existing->last_name}";
                }
            }
        }

        if (!empty($conflicts)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'These students are already active in another classroom and must be transferred out first: ' . implode(', ', $conflicts));
        }

        // ====================================================
        // create students
        foreach ($validated['students'] as $index => $studentData) {
            $studentData['dob']            = $request->input("students.{$index}.dob");
            $studentData['is_exceptional'] = $request->has("students.{$index}.is_exceptional") ? 1 : 0;
            $studentData['classroom_id']   = $classroom->id;
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
        $request->validate(['students_csv' => 'required|file|mimes:csv,txt|max:2048']);

        // name validation
        $nameRegex = '/^[\p{L}\s\'\-]+$/u';
        
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
            if ($handle === false) return redirect()->back()->with('error', 'Could not open the CSV file.');
            
            $firstLine = fgets($handle);
            $delimiter = strpos($firstLine, ';') !== false ? ';' : ',';
            rewind($handle);
            
            // Read header row
            $header = fgetcsv($handle, 1000, $delimiter);
            if ($header === false) {
                fclose($handle);
                return redirect()->back()->with('error', 'CSV file is empty or invalid.');
            }

            // make everything lowercase and remove extra spaces etc
            $normalizedHeader = array_map(function($col) {
                return strtolower(trim(str_replace("\xEF\xBB\xBF", '', $col)));
            }, $header);
            
            $studentsData = [];
            $invalidRows = [];
            $rowNumber = 1;
            
            // Collect all student data
            while (($data = fgetcsv($handle, 1000, $delimiter)) !== false) {
                $rowNumber++;
                // Skip empty rows
                if (empty(array_filter($data))) {
                    continue;
                }
                // skip any broken rows
                if (count($normalizedHeader) !== count($data)) continue;

                $row = array_combine($normalizedHeader, $data);

                // Parse DOB
                $dob = null;
                if (!empty($row['date of birth'])) {
                    try {
                        $dobStr = str_replace('/', '-', trim($row['date of birth']));
                        $dob = date('Y-m-d', strtotime($dobStr));
                    } catch (\Exception $e) {
                        $dob = null;
                    }
                }

                // create student data array
                $studentData = [
                    'first_name' => trim($row['first name'] ?? ''),
                    'last_name'  => trim($row['last name'] ?? ''),
                    'dob'        => $dob,
                    'level'      => !empty($row['level']) ? (int)$row['level'] : null,
                    'active'     => !empty($row['active']) && strtolower(trim($row['active'])) === 'yes',
                ];
                
                // skip if first name or last name is empty
                if (empty($studentData['first_name']) || empty($studentData['last_name'])) {
                    continue;
                }

                // skip rows with names that have numbers/symbols
                if (!preg_match($nameRegex, $studentData['first_name']) || 
                    !preg_match($nameRegex, $studentData['last_name'])) {
                    $invalidRows[] = "Row {$rowNumber} ({$studentData['first_name']} {$studentData['last_name']})";
                    continue;
                }
                
                $studentsData[] = $studentData;
            }
            
            fclose($handle);

            $message = [];

            if (!empty($invalidRows)) {
                $message[] = count($invalidRows) === 1
                    ? "1 row was skipped due to invalid name format: " . $invalidRows[0]
                    : count($invalidRows) . " rows were skipped due to invalid name format: " . implode(', ', $invalidRows);
            }

            // if no valid rows and no invalid rows, file empty
            if (empty($studentsData) && empty($invalidRows)) {
                return redirect()->back()->with('error', 'No valid students found in CSV. Please ensure headers are exactly: First Name, Last Name, Level, Date of Birth, Active.');
            }

            // show invalid rows skipped
            if (empty($studentsData)) {
                return redirect()->back()->with('error', implode(' ', $message));
            }

            \DB::beginTransaction();
            // Used to check if there are any students created/linked/skipped
            // If so, the transaction rolls back and 0 students are added = better than some added
            // Get each student
            try {
                $studentsCreated  = 0;
                $studentsLinked   = 0;
                $studentsSkipped  = 0;
                $studentsConflict = 0;

                // Avoid duplicate students                
                foreach ($studentsData as $studentData) {
                    $query = Student::where('school_id', $classroom->school_id)
                        // match names
                        ->whereRaw('LOWER(first_name) = ?', [strtolower($studentData['first_name'])])
                        ->whereRaw('LOWER(last_name) = ?', [strtolower($studentData['last_name'])]);
                    
                    if (!empty($studentData['dob'])) {
                        // wheredate() = ignores weird dobs
                        $query->whereDate('date_of_birth', $studentData['dob']);
                    }
                    
                    $existingStudent = $query->first();

                    if ($existingStudent) {
                        // Check if student is active in this classroom
                        $activeElsewhere = DB::table('classroom_student')
                            ->where('student_id', $existingStudent->id)
                            ->where('classroom_id', '!=', $classroom->id)
                            ->where('active', 1)
                            ->exists();
                        
                        if ($activeElsewhere) {
                            $studentsConflict++;
                            continue;
                        }

                        // check pivot record for THIS classroom
                        $existingPivot = $classroom->students()
                            ->where('student_id', $existingStudent->id)
                            ->first();

                        if ($existingPivot) {
                            // already linked - check if active
                            if ($existingPivot->pivot->active) {
                                $studentsSkipped++;
                                continue;
                            }

                            // reactivate the previously transferred student
                            $classroom->students()->updateExistingPivot($existingStudent->id, [
                                'active'     => 1,
                                'starts_on'  => now()->toDateString(),
                                'ends_on'    => null,
                                'updated_at' => now(),
                            ]);
                            $studentsLinked++;
                            continue;
                        }

                        // student exists in school but not in this classroom
                        $classroom->students()->attach($existingStudent->id, [
                            'active'    => $studentData['active'] ?? true,
                            'school_id' => $classroom->school_id,
                            'starts_on' => now()->toDateString(),
                            'ends_on'   => null,
                        ]);
                        $studentsLinked++;
                    } else {
                        // new student
                        $this->createStudent($classroom, $studentData);
                        $studentsCreated++;
                    }
                }
                
                \DB::commit();

                // result messages
                if ($studentsCreated > 0) {
                    $message[] = $studentsCreated === 1 
                        ? "1 new student added!" 
                        : "{$studentsCreated} new students added!";
                }
                if ($studentsLinked > 0) {
                    $message[] = $studentsLinked === 1 
                        ? "1 existing student joined the classroom!" 
                        : "{$studentsLinked} existing students joined the classroom!";
                }
                if ($studentsSkipped > 0) {
                    $message[] = $studentsSkipped === 1 
                        ? "1 student was already in the classroom!" 
                        : "{$studentsSkipped} students were already in the classroom!";
                }
                if ($studentsConflict > 0) {
                    $message[] = $studentsConflict === 1
                        ? "1 student was skipped because they are still active in another classroom, please transfer them out first!"
                        : "{$studentsConflict} students were skipped because they are still active in another classroom, please transfer them out first!";
                }

                return redirect()
                    ->route('teacher.classes.students', $classroom->id)
                    ->with('success', implode(' ', $message));
                    
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
        $this->ensureOwnsClassroom($classroom);

        $fileName = "Year_" . $classroom->year_group . "_" . $classroom->name . '_Students_List.csv';

        $headers = [
            'Content-Type' => 'text/csv',
        ];

        // get only active students
        $students = $classroom->students()
            ->wherePivot('active', 1)
            ->with('user')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return response()->streamDownload(function () use ($students) {
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
            foreach ($students as $student) {
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
            'academic_start'    => 'required|integer|min:2020|max:9999',
            'academic_end'      => 'required|integer|min:2020|max:9999',
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

        DB::beginTransaction();
        try{
            // Detach from classroom and remove the pivot record too
            $classroom->students()->detach($studentId);

            // get linked user before deleting student
            $user = User::find($student->user_id);

            // delete pfp file if exists
            if ($student->pfp && str_starts_with($student->pfp, '/storage/')){
                $path = str_replace('/storage/', '', $student->pfp);
                Storage::disk('public')->delete($path);
            }

            // delete student record
            $student->delete();

            // delete as user
            if ($user){
                $user->delete();
            }

            DB::commit();
        } catch(Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error removing student: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Student removed successfully.');
    }

    // Transfer students w/ export CSV
    public function transferStudent(Classroom $classroom, int $studentId)
    {
        $this->ensureOwnsClassroom($classroom);

        // Find the student
        $student = $classroom->students()->findOrFail($studentId);

        $fileName = "Transfer_" . str_replace(' ', '_', $student->first_name) . "_" . str_replace(' ', '_', $student->last_name) . "_" . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        return response()->streamDownload(function () use ($classroom, $student) {
        $handle = fopen('php://output', 'w');

        // header row
        fputcsv($handle, ['First Name', 'Last Name', 'Username', 'Level', 'Date of Birth', 'Active',]);

        // student data row
        fputcsv($handle, [ $student->first_name, $student->last_name, optional($student->user)->username, $student->level, optional($student->date_of_birth)->format('Y-m-d'), $student->active ? 'Yes' : 'No',]);

        // flush output so that the csv is sent before removing
        fclose($handle);
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();

        // update classroom_student record // active set to 0
        $classroom->students()->updateExistingPivot($student->id, [
            'active'     => 0,
            'ends_on'    => now()->toDateString(),
            'updated_at' => now(),
        ]);

        }, $fileName, $headers);
    }

    // Create student
    protected function createStudent(Classroom $classroom, array $data): Student
    {
        // Create username and password
        $username = $this->createStudentUsername();
        $normalpassword = $this->createStudentPassword($data['first_name'], $data['last_name']);

        // Download and store kitten pfp from Robohash set4
        $pfpPath = $this->downloadKittenPfp($username);

        // Create user
        $user = User::create([
            'name'      => $data['first_name'] . ' ' . $data['last_name'],
            'username'  => $username,
            'email'     => null,              
            'phone'     => null,
            'password'  => bcrypt($normalpassword),
            'role'      => 'Student',
            'pfp'       => $pfpPath,
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
            'pfp'            => $pfpPath,
            'active'         => $data['active'] ?? true,
            'is_exceptional' => $data['is_exceptional'] ?? false,
            'classroom_id'   => $classroom->id,
        ]);
        
        // Attach to classroom
        $classroom->students()->attach($student->id, [
            'active'    => $data['active'] ?? true,
            'school_id' => $classroom->school_id,
            'starts_on' => now()->toDateString(),
            'ends_on'   => null,                  
        ]);

        return $student;
    }

    // Create password
    protected function createStudentPassword(string $firstName, string $lastName): string
    {
        // get first and last names
        $firstClean = preg_replace('/[^A-Za-z]/', '', $firstName);
        $lastClean  = preg_replace('/[^A-Za-z]/', '', $lastName);

        // first 3 letters of both, if less than 3, use x
        $firstPart = str_pad(substr($firstClean, 0, 3), 3, 'x');
        $lastPart  = str_pad(substr($lastClean, 0, 3), 3, 'x');

        // capital first letter
        $firstPart = ucfirst(strtolower($firstPart));
        $lastPart  = strtolower($lastPart);

        // get year
        $year = now()->format('y');

        return $firstPart . $lastPart . $year;
    }
}