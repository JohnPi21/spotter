<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int $meso_day_id
 * @property int $exercise_id
 * @property int $position
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MesoDay $day
 * @property-read \App\Models\Exercise|null $exercise
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ExerciseSet> $sets
 * @property-read int|null $sets_count
 * @method static \Database\Factories\DayExerciseFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DayExercise newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DayExercise newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DayExercise ownedBy(int $userID)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DayExercise query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DayExercise whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DayExercise whereExerciseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DayExercise whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DayExercise whereMesoDayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DayExercise wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DayExercise whereUpdatedAt($value)
 */
	class DayExercise extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property int $muscle_group_id
 * @property int|null $user_id
 * @property string|null $youtube_id
 * @property string $exercise_type
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MuscleGroup|null $muscleGroup
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exercise newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exercise newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exercise query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exercise whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exercise whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exercise whereExerciseType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exercise whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exercise whereMuscleGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exercise whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exercise whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exercise whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exercise whereYoutubeId($value)
 */
	class Exercise extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $day_exercise_id
 * @property int|null $reps
 * @property int|null $weight
 * @property string|null $finished_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\DayExercise $dayExercise
 * @method static \Database\Factories\ExerciseSetFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExerciseSet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExerciseSet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExerciseSet ownedBy(int $userID)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExerciseSet query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExerciseSet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExerciseSet whereDayExerciseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExerciseSet whereFinishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExerciseSet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExerciseSet whereReps($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExerciseSet whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExerciseSet whereWeight($value)
 */
	class ExerciseSet extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $mesocycle_id
 * @property int $week
 * @property int $day_order
 * @property int|null $body_weight
 * @property string $label
 * @property int $position
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DayExercise> $dayExercises
 * @property-read int|null $day_exercises_count
 * @property-read \App\Models\Mesocycle $mesocycle
 * @method static \Database\Factories\MesoDayFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MesoDay newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MesoDay newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MesoDay ownedBy(int $userID)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MesoDay query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MesoDay whereBodyWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MesoDay whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MesoDay whereDayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MesoDay whereFinishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MesoDay whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MesoDay whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MesoDay whereMesocycleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MesoDay whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MesoDay wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MesoDay whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MesoDay whereWeek($value)
 */
	class MesoDay extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $sex
 * @property int|null $user_id
 * @property int $frequency
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\MesoTemplateFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MesoTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MesoTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MesoTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MesoTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MesoTemplate whereFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MesoTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MesoTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MesoTemplate whereSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MesoTemplate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MesoTemplate whereUserId($value)
 */
	class MesoTemplate extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $unit
 * @property int $days_per_week
 * @property int $weeks_duration
 * @property int $user_id
 * @property string|null $notes
 * @property int $status
 * @property int|null $meso_template_id
 * @property string|null $started_at
 * @property string|null $finished_at
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MesoDay> $days
 * @property-read int|null $days_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mesocycle active()
 * @method static \Database\Factories\MesocycleFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mesocycle mine()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mesocycle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mesocycle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mesocycle ownedBy(?int $userID)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mesocycle query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mesocycle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mesocycle whereDaysPerWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mesocycle whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mesocycle whereFinishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mesocycle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mesocycle whereMesoTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mesocycle whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mesocycle whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mesocycle whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mesocycle whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mesocycle whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mesocycle whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mesocycle whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mesocycle whereWeeksDuration($value)
 */
	class Mesocycle extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Exercise> $exercises
 * @property-read int|null $exercises_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MuscleGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MuscleGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MuscleGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MuscleGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MuscleGroup whereName($value)
 */
	class MuscleGroup extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 */
	class User extends \Eloquent implements \Illuminate\Contracts\Auth\MustVerifyEmail {}
}

