<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Student;

class ClassroomController extends Controller
{
    // Delete class
    public function removeClassroom(Classroom $classroom)
    {
        // check if teacher owns the class
        if($classroom->teacher_id !== auth()->id()){
            abort(403, "Unauthorised action");
        }
        
        // unassign students from students table
        $classroom->students()->update(['classroom_id' => null]);

        // clear classroom_student
        $classroom->students()->detach();

        // delete classroom's history from archive
        DB::table('archive_classrooms')->where('classroom_id', $classroom->id)->delete();

        // delete class
        $classroom->delete();

        return redirect()->route('teacher.index')->with('success', 'Classroom and archive have been deleted');
    }

    // Archive classroom
    public function archiveClassroom(Request $request, $id)
    {
        // get classroom
        $classroom = Classroom::findOrFail($id);

        // check if classroom is already archived
        if (!$classroom->active) {
            return redirect()->back()->with('error', 'This classroom is already archived.');
        }

        // get student ids part of the classroom
        $studentIds = $classroom->students()->pluck('students.id')->toArray();

        // database transaction
        DB::transaction(function () use ($classroom, $studentIds) {
            
            // insert data into archive classrooms table
            DB::table('archive_classrooms')->insert([
                'school_id'     => $classroom->school_id,
                'classroom_id'  => $classroom->id,
                'student_ids'   => json_encode($studentIds),
                'academic_year' => $classroom->academic_year,
                'year_group'    => $classroom->year_group,
                'stage'         => $classroom->stage,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            // make the classroom inactive
            $classroom->update(['active' => 0]);

            // update pivot table and set end date
            DB::table('classroom_student')
                ->where('classroom_id', $classroom->id)
                ->update([
                    'active'  => 0,
                    'ends_on' => now(),
                ]);

            // unassign classroom id for students -> will help for placing students in a new classrom new year
            if (!empty($studentIds)) {
                Student::whereIn('id', $studentIds)->update([
                    'classroom_id' => null
                ]);
            }
        });

        return redirect()->route('teacher.index')->with(
            'success', 
            'The classroom is now archived. Progress the students to the next year.'
        );
    }

    // Restore classroom
    public function restoreClassroom(Request $request, $id)
    {
        // find classroom
        $classroom = Classroom::findOrFail($id);

        // prevent duplicate (check if its already active)
        if ($classroom->active) {
            return redirect()->back()->with('error', 'This classroom is already active.');
        }

        // DB transaction
        DB::transaction(function () use ($classroom) {
            
            // reactive classroom
            $classroom->update(['active' => 1]);

            // find archive record and find classroom students
            $archive = DB::table('archive_classrooms')
                ->where('classroom_id', $classroom->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($archive && $archive->student_ids) {
                $studentIds = json_decode($archive->student_ids, true);

                if (!empty($studentIds)) {
                    // reassign classroom_id to students
                    Student::whereIn('id', $studentIds)->update([
                        'classroom_id' => $classroom->id
                    ]);

                    // update classroom_student and make em active
                    DB::table('classroom_student')
                        ->where('classroom_id', $classroom->id)
                        ->whereIn('student_id', $studentIds)
                        ->update([
                            'active'  => 1,
                            'ends_on' => null,
                        ]);
                }

                // delete archive record as the class is back
                DB::table('archive_classrooms')->where('id', $archive->id)->delete();
            }
        });

        return redirect()->route('teacher.index')->with(
            'success', 
            'Classroom restored! Students have been placed back into the class.'
        );
    }
    
    // Progress classroom
    public function progressClassroom(Request $request, $id)
    {
        // find old archived class
        $oldClassroom = Classroom::findOrFail($id);

        // dont progress an active classroom
        if ($oldClassroom->active) {
            return redirect()->back()->with('error', 'You must archive this classroom before progressing it.');
        }

        // year 6 cant be progressed
        if ($oldClassroom->year_group >= 6) {
            return redirect()->back()->with('error', 'Year 6 classes cannot be progressed further.');
        }

        // db transaction
        DB::transaction(function () use ($oldClassroom) {
            
            // get new values for the year
            $currentStart = (int) $oldClassroom->academic_start;
            $currentEnd   = (int) $oldClassroom->academic_end;

            // if its missing
            if ($currentStart < 2000 || $currentEnd < 2000) {
                
                // try get the date
                preg_match_all('/\d{4}/', $oldClassroom->academic_year, $matches);
                
                if (!empty($matches[0]) && count($matches[0]) >= 2) {
                    $currentStart = (int) $matches[0][0];
                    $currentEnd   = (int) $matches[0][1];
                } else {
                    // if it fails, make a guess on the current date
                    $currentStart = date('m') >= 9 ? (int) date('Y') : (int) date('Y') - 1;
                    $currentEnd   = $currentStart + 1;
                }

                // fix old classrooms data
                $oldClassroom->update([
                    'academic_start' => $currentStart,
                    'academic_end'   => $currentEnd,
                    'academic_year'  => $currentStart . '-' . $currentEnd
                ]);
            }

            $nextYearGroup    = $oldClassroom->year_group + 1;
            $nextStart        = $currentStart + 1;
            $nextEnd          = $currentEnd + 1;
            $nextAcademicYear = $nextStart . '-' . $nextEnd;
            $nextStage        = $nextYearGroup <= 2 ? 'KS1' : 'KS2';

            // create new classroom
            $newClassroom = Classroom::create([
                'school_id'      => $oldClassroom->school_id,
                'teacher_id'     => $oldClassroom->teacher_id,
                'name'           => $oldClassroom->name, // Keeping the same name
                'year_group'     => $nextYearGroup,
                'stage'          => $nextStage,
                'academic_year'  => $nextAcademicYear,
                'academic_start' => $nextStart,
                'academic_end'   => $nextEnd,
                'active'         => true,
            ]);

            // update old classroom as progressed
            $oldClassroom->update(['is_progressed' => true]);

            // C. Find the archive record to figure out who was in the old class
            $archive = DB::table('archive_classrooms')
                ->where('classroom_id', $oldClassroom->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($archive && $archive->student_ids) {
                $studentIds = json_decode($archive->student_ids, true);

                if (!empty($studentIds)) {
                    // D. Update the students table to place them in the NEW classroom
                    Student::whereIn('id', $studentIds)->update([
                        'classroom_id' => $newClassroom->id
                    ]);

                    // E. Insert brand new records into the classroom_student pivot table
                    $pivotData = [];
                    foreach ($studentIds as $studentId) {
                        $pivotData[] = [
                            'school_id'    => $newClassroom->school_id,
                            'classroom_id' => $newClassroom->id,
                            'student_id'   => $studentId,
                            'active'       => 1,
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ];
                    }
                    DB::table('classroom_student')->insert($pivotData);
                }
            }
            
        });

        return redirect()->route('teacher.index')->with(
            'success', 
            'Classroom progressed! A new classroom has been created for Year ' . ($oldClassroom->year_group + 1) . '.'
        );
    }

     // Create announcement view
    public function createAnnouncement(Classroom $classroom)
    {
        // verify ownership
        if ($classroom->teacher_id !== auth()->id()) {
            abort(403, "Unauthorised action");
        }

        // get all active students for dropdown menu
        $students = $classroom->students()->orderBy('first_name')->get();

        return view('teacher.classes.create-announcement', compact('classroom', 'students'));
    }

    // Store announcement
    public function storeAnnouncement(Request $request, Classroom $classroom)
    {
        // verify ownership
        if ($classroom->teacher_id !== auth()->id()) {
            abort(403, "Unauthorised action");
        }

        // validation
        $request->validate([
            'message' => 'required|string|max:1000',
            'entire_class' => 'nullable|boolean',
            'student_id' => 'nullable|exists:students,id'
        ]);

        // if entire class is unchecked, validate a student has been selected
        if (!$request->entire_class && empty($request->student_id)) {
            return back()->withErrors(['student_id' => 'Please select a student or check the entire class'])->withInput();
        }

        // insert into db
        DB::table('announcements')->insert([
            'school_id'    => $classroom->school_id,
            'classroom_id' => $classroom->id,
            'student_id'   => $request->entire_class ? null : $request->student_id,
            'message'      => $request->message,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return redirect()->route('teacher.classes.view', $classroom->id)->with('success', 'Announcement posted successfully!');
    }

    // Hide an announcement for individual students
    public function hideAnnouncement(Request $request, $id)
    {
        // get student
        $student = auth()->user()->student;
        
        if ($student) {
            DB::table('hidden_announcements')->insertOrIgnore([
                'school_id'       => $student->school_id,
                'student_id'      => $student->id,
                'announcement_id' => $id,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
        
        return back();
    }

    // Restore announcements from the last 30 days
    public function restoreAnnouncements(Request $request)
    {
        $student = auth()->user()->student;
        
        if ($student) {
            // find the ids of announcements created in the last month
            $recentAnnouncementIds = DB::table('announcements')
                ->where('created_at', '>=', now()->subMonth())
                ->pluck('id');

            // unhide them for the student
            DB::table('hidden_announcements')
                ->where('student_id', $student->id)
                ->whereIn('announcement_id', $recentAnnouncementIds)
                ->delete();
        }
        
        return back();
    }
}