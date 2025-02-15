// resources/js/types/global.d.ts
export {}; // Ensure this file is treated as a module

declare global {
    type ExerciseSet = {
        id: number;
        day_exercise_id: number;
        reps: number | null;
        weight: number | null;
        target_reps: number | null;
        target_weight: number | null;
        status: boolean;
        created_at: string;
        updated_at: string;
    };

    type MuscleGroup = {
        id: number;
        name: string;
    };

    type Exercise = {
        id: number;
        name: string;
        muscle_group_id: number;
        user_id: number | null;
        youtube_id: string;
        exercise_type: string;
        deleted_at: string | null;
        created_at: string | null;
        updated_at: string | null;
        muscle_group: MuscleGroup;
    };

    type DayExercise = {
        id: number;
        meso_day_id: number;
        exercise_id: number;
        position: number;
        created_at: string;
        updated_at: string;
        exercise: Exercise;
        sets: ExerciseSet[];
    };

    type Day = {
        id: number;
        mesocycle_id: number;
        week: number;
        day_order: number;
        body_weight: number | null;
        label: string;
        position: number;
        status: number;
        notes: string | null;
        created_at: string;
        updated_at: string;
        day_exercises: DayExercise[];
    };

    type CalendarEntry = {
        id: number;
        mesocycle_id: number;
        label: string;
    };

    type Calendar = Record<number, CalendarEntry[]>;

    type Mesocycle = {
        id: number;
        name: string;
        unit: string;
        days_per_week: number;
        weeks_duration: number;
        user_id: number;
        notes: string | null;
        status: number;
        meso_template_id: number | null;
        started_at: string | null;
        finished_at: string | null;
        deleted_at: string | null;
        created_at: string;
        updated_at: string;
        calendar: Calendar;
        days: Day[];
        day: Day;
    };
}
