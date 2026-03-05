<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { usePush } from '@/composables/usePush';
import http from '@/utils/http';


const { subscribed, toggle } = usePush();

type NotificationSetting = {
    id: number;
    days_before: number;
    email_enabled: boolean;
    push_enabled: boolean;
};

const props = defineProps<{ setting: NotificationSetting }>();

onMounted(() => {
    window.addEventListener('beforeunload', handlePageLeave);
    window.addEventListener('pagehide', handlePageLeave);
});

onBeforeUnmount(() => {
    window.removeEventListener('beforeunload', handlePageLeave);
    window.removeEventListener('pagehide', handlePageLeave);
    persistBeforeLeave();
});


const breadcrumbItems: BreadcrumbItem[] = [
    { title: $t('notifications.settings'), href: '/settings/notifications' },
];

const form = ref({
    days_before: props.setting.days_before,
    email_enabled: props.setting.email_enabled,
    push_enabled: props.setting.push_enabled,
});

const initialValues = { ...form.value };
const formLoading = ref(false);
const formErrors = ref<Record<string, string>>({});

const isDirty = computed(
    () =>
        form.value.days_before   !== initialValues.days_before ||
        form.value.email_enabled !== initialValues.email_enabled ||
        form.value.push_enabled  !== initialValues.push_enabled,
);


const page = usePage();

// Computed
const flash = computed(() => (page.props as any).flash as { success?: string; error?: string } | undefined);

// Methods
async function submit() {
    formLoading.value = true;
    formErrors.value = {};
    try {
        await http.put('/api/settings/notifications', form.value);
        Object.assign(initialValues, form.value);
        router.reload({ preserveState: true });
    } catch (e: any) {
        formErrors.value = e.response?.data?.errors ?? {};
    } finally {
        formLoading.value = false;
    }
}

function persistBeforeLeave() {
    if (!isDirty.value) return;

    http.put('/api/settings/notifications', form.value);
}

function handlePageLeave() {
    persistBeforeLeave();
}

function togglePush(value: boolean) {
    if (value !== subscribed.value) {
        toggle();
    }
    form.push_enabled = value;
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="$t('notifications.settings')" />

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    :title="$t('notifications.settings')"
                    :description="$t('notifications.settingsDescription')"
                />

                <!-- Flash -->
                <div
                    v-if="flash?.success"
                    class="rounded-md bg-green-50 px-4 py-2 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-300"
                >
                    {{ flash.success }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Days before -->
                    <div class="grid gap-2">
                        <Label for="days_before">{{ $t('notifications.daysBefore') }}</Label>
                        <Input
                            id="days_before"
                            type="number"
                            v-model.number="form.days_before"
                            min="1"
                            max="30"
                            class="max-w-[120px]"
                        />
                        <p class="text-sm text-muted-foreground">
                            {{ $t('notifications.daysBeforeHelp') }}
                        </p>
                        <InputError :message="formErrors.days_before" />
                    </div>

                    <!-- Email toggle -->
                    <div class="flex items-center justify-between rounded-lg border p-4">
                        <div class="space-y-0.5">
                            <Label for="email_enabled">{{ $t('notifications.emailEnabled') }}</Label>
                            <p class="text-sm text-muted-foreground">
                                {{ $t('notifications.emailEnabledHelp') }}
                            </p>
                        </div>
                        <Switch
                            id="email_enabled"
                            v-model="form.email_enabled"
                        />
                    </div>

                    <!-- Push toggle -->
                    <div class="flex items-center justify-between rounded-lg border p-4">
                        <div class="space-y-0.5">
                            <Label for="push_enabled">{{ $t('notifications.pushEnabled') }}</Label>
                            <p class="text-sm text-muted-foreground">
                                {{ $t('notifications.pushEnabledHelp') }}
                            </p>
                        </div>
                        <Switch
                            id="push_enabled"
                            v-model="form.push_enabled"
                            @update:model-value="togglePush"
                        />
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
