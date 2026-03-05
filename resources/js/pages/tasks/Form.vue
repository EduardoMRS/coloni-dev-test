<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import http from '@/utils/http';
import { useI18n } from 'vue-i18n';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';

export type TaskFormData = {
    id?: number;
    title?: string;
    description?: string | null;
    due_date?: string | null;
    notify_email?: boolean;
    notify_push?: boolean;
};

const props = withDefaults(
    defineProps<{ task?: TaskFormData; modelValue?: boolean }>(),
    { task: undefined, modelValue: undefined },
);
const emit = defineEmits<{ (e: 'update:modelValue', value: boolean): void }>();

const isEditing = computed(() => !!props.task?.id);
const isOpen = computed({
    get: () => props.modelValue ?? false,
    set: (value: boolean) => emit('update:modelValue', value),
});

function toDateInput(value: string | null | undefined): string {
    if (!value) return '';
    return value.substring(0, 10);
}

const loading = ref(false);
const errors = ref<Record<string, string>>({});

const form = ref({
    title:        props.task?.title        ?? '',
    description:  props.task?.description  ?? '',
    due_date:     toDateInput(props.task?.due_date),
    notify_email: props.task?.notify_email ?? true,
    notify_push:  props.task?.notify_push  ?? true,
});

function syncForm(task?: TaskFormData) {
    form.value.title = task?.title ?? '';
    form.value.description = task?.description ?? '';
    form.value.due_date = toDateInput(task?.due_date);
    form.value.notify_email = task?.notify_email ?? true;
    form.value.notify_push = task?.notify_push ?? true;
    errors.value = {};
}

watch(
    () => props.task,
    (task) => {
        if (isOpen.value) {
            syncForm(task);
        }
    },
    { immediate: true },
);

watch(
    () => props.modelValue,
    (open) => {
        if (open) {
            syncForm(props.task);
        }
    },
);

async function submit() {
    loading.value = true;
    errors.value = {};
    try {
        if (isEditing.value) {
            await http.post(`/api/tasks/${props.task!.id}`, form.value);
        } else {
            await http.post('/api/tasks', form.value);
        }
        isOpen.value = false;
        router.reload({ only: ['tasks'] });
    } catch (e: any) {
        errors.value = e.response?.data?.errors ?? {};
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle>{{ isEditing ? $t('tasks.editTask') : $t('tasks.createTask') }}</DialogTitle>
                <DialogDescription>{{ $t('tasks.formDescription') }}</DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Title -->
                <div class="grid gap-2">
                    <Label for="title">{{ $t('tasks.taskTitle') }}</Label>
                    <Input
                        id="title"
                        v-model="form.title"
                        :placeholder="$t('tasks.titlePlaceholder')"
                        required
                    />
                    <InputError :message="errors.title" />
                </div>
        
                <!-- Description -->
                <div class="grid gap-2">
                    <Label for="description">{{ $t('tasks.description') }}</Label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        :placeholder="$t('tasks.descriptionPlaceholder')"
                        rows="4"
                        class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                    />
                    <InputError :message="errors.description" />
                </div>
        
                <!-- Due date -->
                <div class="grid gap-2">
                    <Label for="due_date">{{ $t('tasks.dueDate') }}</Label>
                    <Input
                        id="due_date"
                        type="date"
                        v-model="form.due_date"
                        class="max-w-[200px]"
                    />
                    <InputError :message="errors.due_date" />
                </div>
        
                <!-- Per-task notification toggles -->
                <div class="space-y-3">
                    <Label>{{ $t('tasks.notifications') }}</Label>
                    <div class="flex items-center gap-3">
                        <Checkbox
                            id="notify_email"
                            :checked="form.notify_email"
                            @update:checked="form.notify_email = $event === true"
                        />
                        <Label for="notify_email" class="font-normal">{{ $t('tasks.notifyEmail') }}</Label>
                    </div>
                    <div class="flex items-center gap-3">
                        <Checkbox
                            id="notify_push"
                            :checked="form.notify_push"
                            @update:checked="form.notify_push = $event === true"
                        />
                        <Label for="notify_push" class="font-normal">{{ $t('tasks.notifyPush') }}</Label>
                    </div>
                </div>
        
                <!-- Actions -->
                <DialogFooter class="flex gap-3 sm:justify-end">
                    <Button type="button" variant="outline" @click="isOpen = false">
                        {{ $t('app.cancel') }}
                    </Button>
                    <Button type="submit" :disabled="loading">
                        {{ isEditing ? $t('app.save') : $t('tasks.createTask') }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
