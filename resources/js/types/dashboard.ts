export type TrainingWeek = {
    start: string;
    label: string;
    range: string;
    isCurrent: boolean;
    sets: number;
    sessions: number;
};

export type TrainingMuscleGroup = {
    id: number;
    name: string;
    sets: number[];
    plannedSets: number | null;
};

export type DashboardTraining = {
    weeks: TrainingWeek[];
    muscleGroups: TrainingMuscleGroup[];
    timezone: string;
    activePlan: {
        id: number;
        name: string;
        daysPerWeek: number;
        setsPerWeek: number | null;
        muscleGroupsCount: number | null;
    } | null;
    recentWorkouts: Array<{
        id: number;
        mesocycleId: number;
        mesocycleName: string;
        label: string;
        finishedAt: string;
        sets: number;
        exercises: number;
        muscleGroups: Array<{ name: string; sets: number }>;
        load: number;
        volume: number;
        unit: string;
    }>;
};
