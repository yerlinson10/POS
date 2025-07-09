<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, Transition } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

interface SettingOption {
    value: string;
    label?: string;
    default?: boolean;
    description?: string;
}

interface Setting {
    key: string;
    label: string;
    type: string;
    default?: string;
    value?: string;
    description?: string;
    options?: SettingOption[];
}

const breadcrumbs = [
    { title: 'System settings', href: '/settings/system' },
];

const page = usePage();
const settings = ref<Setting[]>(Array.isArray(page.props.settings) ? page.props.settings : []);

const form = useForm<Record<string, string>>(
    Object.fromEntries(settings.value.map(s => [s.key, s.value ?? s.default ?? '']))
);

const submit = () => {
    form.put(route('settings.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="System settings" />
        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Configuración del sistema" description="Ajusta las opciones principales del sistema" />
                <form @submit.prevent="submit" class="space-y-6">
                    <div v-for="setting in settings" :key="setting.key" class="grid gap-2">
                        <Label :for="setting.key">{{ setting.label }}</Label>
                        <Select v-if="setting.type === 'select'" v-model="form[setting.key]">
                            <SelectTrigger>
                                <SelectValue :placeholder="setting.label" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="option in setting.options" :key="option.value" :value="option.value">
                                    {{ option.label || option.value }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Input v-else :id="setting.key" v-model="form[setting.key]" :type="setting.type" :placeholder="setting.label" />
                        <InputError class="mt-2" :message="form.errors[setting.key]" />
                        <p v-if="setting.description" class="text-xs text-muted-foreground">{{ setting.description }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Guardar</Button>
                        <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Guardado.</p>
                        </Transition>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
