<script setup lang="ts">
import ButtonPrimary from "@/Components/Button/Primary.vue";
import ButtonSecondary from "@/Components/Button/Secondary.vue";
import Checkbox from "@/Components/Input/Checkbox.vue";
import InputError from "@/Components/Input/InputError.vue";
import InputLabel from "@/Components/Input/InputLabel.vue";
import InputPassword from "@/Components/Input/password.vue";
import TextInput from "@/Components/TextInput.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

defineOptions({
    layout: GuestLayout,
});

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => {
            form.reset("password");
        },
    });
};
</script>

<template>
    <Head title="Log in" />

    <div v-if="status" class="text-green-600 mb-4 text-sm font-medium">
        {{ status }}
    </div>

    <a :href="route('demo.login')">
        <ButtonPrimary class="w-full">Login as Guest</ButtonPrimary>
    </a>

    <div class="relative m-5 flex items-center justify-center">
        <p class="z-10 bg-layer p-1 font-bold">OR</p>
        <div class="absolute w-full border border-input"></div>
    </div>

    <form @submit.prevent="submit">
        <div>
            <InputLabel for="email" value="Email" />

            <TextInput
                id="email"
                type="email"
                class="mt-1 block w-full"
                v-model="form.email"
                required
                autofocus
                autocomplete="username"
            />

            <InputError class="mt-2" :message="form.errors.email" />
        </div>

        <div class="mt-4">
            <InputLabel for="password" value="Password" />

            <!-- <InputText id="password" :type="viewPassword ? 'text' : 'password'" class="mt-1 block w-full" v-model="form.password" required autocomplete="current-password" /> -->

            <InputPassword v-model="form.password" autocomplete="current-password" id="password" placeholder="" />

            <InputError class="mt-2" :message="form.errors.password" />
        </div>

        <div class="mt-4 flex justify-between">
            <label class="flex items-center">
                <Checkbox name="remember" :true-value="true" :false-value="false" v-model="form.remember" />
                <span class="ms-2 text-sm dark:text-gray-400">Remember me</span>
            </label>

            <Link
                v-if="canResetPassword"
                :href="route('password.request')"
                class="rounded-md text-sm text-gray-400 underline hover:text-gray-100 focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2 dark:focus:ring-offset-gray-800"
            >
                Forgot your password?
            </Link>
        </div>

        <div class="mt-4 flex justify-center">
            <ButtonSecondary
                class="flex-1 justify-center"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
            >
                Log in
            </ButtonSecondary>
        </div>

        <div class="flex-column mt-5">
            <div class="flex w-full items-center justify-center">
                <p class="mr-1">Don't have an account?</p>

                <Link
                    :href="route('register')"
                    class="rounded-md text-sm text-gray-400 underline hover:text-gray-100 focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2 focus:ring-offset-gray-800"
                >
                    Register
                </Link>
            </div>
        </div>
    </form>
</template>
