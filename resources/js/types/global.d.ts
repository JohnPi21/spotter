import { PageProps as InertiaPageProps } from "@inertiajs/core";
import { AxiosInstance } from "axios";
import { route as ziggyRoute } from "ziggy-js";
import { PageProps as AppPageProps } from "./";

declare global {
    interface Window {
        axios: AxiosInstance;
    }

    var route: typeof ziggyRoute;

    type ExerciseSet = {
        id: number;
        day_exercise_id: number;
        reps?: number;
        weight?: number;
        target_reps?: number;
        target_weight?: number;
        finished_at?: string;
        status: boolean;
        created_at: string;
        updated_at: string;
    };

    type MuscleGroup = {
        id: number;
        name: string;
        exercises?: Exercise[];
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
        notes: string | null;
        created_at: string;
        updated_at: string;
        finished_at: string | null;
        day_exercises: DayExercise[];
        exercises?: Exercise[];
    };

    type CalendarEntry = {
        id: number;
        mesocycle_id: number;
        label: string;
        status: number;
        finished_at: string;
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
        last_day?: Day["id"];
    };

    type DropdownOption = {
        value: string | number | null;
        label: string | number | null;
    };

    type MuscleGroupsDropdown = {
        [key: number]: DropdownOption[];
    };
}

declare module "vue" {
    interface ComponentCustomProperties {
        route: typeof ziggyRoute;
    }
}

declare module "@inertiajs/core" {
    interface PageProps extends InertiaPageProps, AppPageProps {}
}
