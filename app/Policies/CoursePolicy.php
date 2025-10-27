<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CoursePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        // Anyone can view the course list (even guests)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Course $course): bool
    {
        // Anyone can view individual courses (even guests)
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Instructors and admins can create courses
        return $user->role === 'instructor' || $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Course $course): bool
    {
        // Admins can update any course, instructors can only update their own
        return $user->role === 'admin' || $course->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Course $course): bool
    {
        // Admins can delete any course, instructors can only delete their own
        return $user->role === 'admin' || $course->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Course $course): bool
    {
        // Admins can restore any course, instructors can only restore their own
        return $user->role === 'admin' || $course->user_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Course $course): bool
    {
        // Only admins can force delete courses
        return $user->role === 'admin';
    }
}
