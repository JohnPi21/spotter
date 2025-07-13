<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DeleteUserForm from "./Partials/DeleteUserForm.vue";
import UpdatePasswordForm from "./Partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "./Partials/UpdateProfileInformationForm.vue";

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();
</script>

<template>
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="mx-auto max-w-[768px] space-y-6">
                <div class="border border-layer-border bg-layer p-4 shadow sm:rounded-lg sm:p-8">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div class="border border-layer-border bg-layer p-4 shadow sm:rounded-lg sm:p-8">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div class="border border-layer-border bg-layer p-4 shadow sm:rounded-lg sm:p-8">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
