<script setup lang="ts">
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import DeleteUserForm from './Partials/DeleteUserForm.vue';
    import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
    import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
    import { Head } from '@inertiajs/vue3';

    defineProps<{
        mustVerifyEmail?: boolean;
        status?: string;
    }>();
</script>

<template>

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="mx-auto max-w-[768px] space-y-6">
                <div class="p-4 shadow sm:rounded-lg sm:p-8 bg-layer border border-layer-border">
                    <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status"
                        class="max-w-xl" />
                </div>

                <div class="p-4 shadow sm:rounded-lg sm:p-8 bg-layer border border-layer-border">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div class="p-4 shadow sm:rounded-lg sm:p-8 bg-layer border border-layer-border">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
