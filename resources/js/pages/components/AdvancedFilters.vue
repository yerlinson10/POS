<script setup lang="ts">
import { computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '@/components/ui/select';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Plus, Trash2, Copy } from 'lucide-vue-next';
import type { AdvancedFilter, AdvancedFilterGroup, FilterOptions } from '../types/dashboard';
import { FILTER_OPERATORS } from '../types/dashboard';
import { useFilterOptions } from '@/composables/useFilterOptions';

interface Props {
    modelValue: AdvancedFilterGroup[];
    filterOptions?: FilterOptions;
    widgetType?: string;
}

interface Emits {
    (e: 'update:modelValue', value: AdvancedFilterGroup[]): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// Usar el composable para cargar opciones específicas
const { filterOptions: dynamicFilterOptions, loadFilterOptions } = useFilterOptions();

// Usar las opciones específicas si están disponibles, sino usar las props
const currentFilterOptions = computed(() => {
    const options = props.widgetType ? dynamicFilterOptions.value : (props.filterOptions || {});
    return {
        available_fields: [],
        ...options
    };
});

// Cargar opciones cuando cambia el tipo de widget
watch(() => props.widgetType, (newWidgetType) => {
    if (newWidgetType) {
        loadFilterOptions(newWidgetType);
    }
}, { immediate: true });

const filterGroups = computed({
    get: () => Array.isArray(props.modelValue) ? props.modelValue : [],
    set: (value) => emit('update:modelValue', value)
});

const availableFields = computed<{ field: string; label: string; type: string }[]>(() => currentFilterOptions.value.available_fields || []);

const getOperators = (type: string) => {
    switch (type) {
        case 'string':
            return FILTER_OPERATORS.STRING;
        case 'number':
            return FILTER_OPERATORS.NUMBER;
        case 'date':
            return FILTER_OPERATORS.DATE;
        case 'boolean':
            return FILTER_OPERATORS.BOOLEAN;
        default:
            return FILTER_OPERATORS.STRING;
    }
};

const addFilterGroup = () => {
    const newGroup: AdvancedFilterGroup = {
        operator: 'AND',
        filters: [{
            field: '',
            operator: '',
            value: '',
            type: 'string'
        }]
    };

    filterGroups.value = [...filterGroups.value, newGroup];
};

const removeFilterGroup = (groupIndex: number) => {
    filterGroups.value = filterGroups.value.filter((_, index) => index !== groupIndex);
};

const addFilter = (groupIndex: number) => {
    const newFilter: AdvancedFilter = {
        field: '',
        operator: '',
        value: '',
        type: 'string'
    };

    const updated = [...filterGroups.value];
    updated[groupIndex].filters.push(newFilter);
    filterGroups.value = updated;
};

const removeFilter = (groupIndex: number, filterIndex: number) => {
    const updated = [...filterGroups.value];
    updated[groupIndex].filters = updated[groupIndex].filters.filter((_, index) => index !== filterIndex);

    // Si no quedan filtros en el grupo, remover el grupo
    if (updated[groupIndex].filters.length === 0) {
        updated.splice(groupIndex, 1);
    }

    filterGroups.value = updated;
};

const updateGroupOperator = (groupIndex: number, operator: 'AND' | 'OR') => {
    const updated = [...filterGroups.value];
    updated[groupIndex].operator = operator;
    filterGroups.value = updated;
};

const updateFilter = (groupIndex: number, filterIndex: number, field: keyof AdvancedFilter, value: any) => {
    const updated = [...filterGroups.value];
    updated[groupIndex].filters[filterIndex][field] = value;

    // Si se cambió el campo, actualizar el tipo y resetear operador
    if (field === 'field') {
        const fieldDef = availableFields.value.find(f => f.field === value);
        if (fieldDef) {
            updated[groupIndex].filters[filterIndex].type = fieldDef.type as 'string' | 'number' | 'boolean' | 'date';
            updated[groupIndex].filters[filterIndex].operator = '';
            updated[groupIndex].filters[filterIndex].value = '';
        }
    }

    filterGroups.value = updated;
};

const duplicateGroup = (groupIndex: number) => {
    const groupToDuplicate = filterGroups.value[groupIndex];
    const duplicated = JSON.parse(JSON.stringify(groupToDuplicate));

    const updated = [...filterGroups.value];
    updated.splice(groupIndex + 1, 0, duplicated);
    filterGroups.value = updated;
};

const getFieldType = (fieldName: string): string => {
    const field = availableFields.value.find(f => f.field === fieldName);
    return field?.type || 'string';
};

const needsValue = (operator: string): boolean => {
    return !['is_empty', 'is_not_empty', 'is_null', 'is_not_null', 'is_true', 'is_false'].includes(operator);
};

const isBetweenOperator = (operator: string): boolean => {
    return operator === 'between';
};

// Inicializar con un grupo vacío si no hay filtros
if (filterGroups.value.length === 0) {
    addFilterGroup();
}
</script>

<template>
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <Label class="text-base font-semibold">Filtros Avanzados</Label>
            <Button @click="addFilterGroup" size="sm" variant="outline" class="self-start">
                <Plus class="h-4 w-4 mr-2" />
                Agregar Grupo
            </Button>
        </div>

        <div v-for="(group, groupIndex) in filterGroups" :key="groupIndex" class="space-y-3">
            <Card>
                <CardHeader class="pb-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <CardTitle class="text-sm flex flex-col sm:flex-row sm:items-center gap-2">
                            <span>Grupo {{ groupIndex + 1 }}</span>
                            <Select
                                :model-value="group.operator"
                                @update:model-value="(value) => updateGroupOperator(groupIndex, value as 'AND' | 'OR')"
                            >
                                <SelectTrigger class="w-20 h-7">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent class="z-50">
                                    <SelectItem value="AND">Y</SelectItem>
                                    <SelectItem value="OR">O</SelectItem>
                                </SelectContent>
                            </Select>
                        </CardTitle>

                        <div class="flex gap-1 self-start sm:self-auto">
                            <Button @click="duplicateGroup(groupIndex)" size="sm" variant="ghost" title="Duplicar grupo">
                                <Copy class="h-4 w-4" />
                            </Button>
                            <Button @click="removeFilterGroup(groupIndex)" size="sm" variant="ghost" title="Eliminar grupo">
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </CardHeader>

                <CardContent class="space-y-3">
                    <div v-for="(filter, filterIndex) in group.filters" :key="filterIndex"
                         class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-end">

                        <!-- Campo -->
                        <div class="lg:col-span-3">
                            <Label class="text-xs">Campo</Label>
                            <Select
                                :model-value="filter.field"
                                @update:model-value="(value) => updateFilter(groupIndex, filterIndex, 'field', value)"
                            >
                                <SelectTrigger class="h-8 w-full">
                                    <SelectValue placeholder="Seleccionar campo" />
                                </SelectTrigger>
                                <SelectContent class="z-50">
                                    <SelectItem
                                        v-for="field in availableFields"
                                        :key="field.field"
                                        :value="field.field"
                                    >
                                        {{ field.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Operador -->
                        <div class="lg:col-span-3">
                            <Label class="text-xs">Operador</Label>
                            <Select
                                :model-value="filter.operator"
                                @update:model-value="(value) => updateFilter(groupIndex, filterIndex, 'operator', value)"
                                :disabled="!filter.field"
                            >
                                <SelectTrigger class="h-8 w-full">
                                    <SelectValue placeholder="Operador" />
                                </SelectTrigger>
                                <SelectContent class="z-50">
                                    <SelectItem
                                        v-for="(label, op) in getOperators(getFieldType(filter.field))"
                                        :key="op"
                                        :value="op"
                                    >
                                        {{ label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Valor -->
                        <div class="lg:col-span-5" v-if="needsValue(filter.operator)">
                            <Label class="text-xs">Valor</Label>
                            <div v-if="isBetweenOperator(filter.operator)" class="flex flex-col sm:flex-row gap-1">
                                <Input
                                    :type="getFieldType(filter.field) === 'date' ? 'date' :
                                           getFieldType(filter.field) === 'number' ? 'number' : 'text'"
                                    :model-value="Array.isArray(filter.value) ? filter.value[0] : filter.value"
                                    @update:model-value="(value) => {
                                        const currentValue = Array.isArray(filter.value) ? filter.value : ['', ''];
                                        updateFilter(groupIndex, filterIndex, 'value', [value, currentValue[1]]);
                                    }"
                                    placeholder="Desde"
                                    class="h-8 text-xs"
                                />
                                <Input
                                    :type="getFieldType(filter.field) === 'date' ? 'date' :
                                           getFieldType(filter.field) === 'number' ? 'number' : 'text'"
                                    :model-value="Array.isArray(filter.value) ? filter.value[1] : ''"
                                    @update:model-value="(value) => {
                                        const currentValue = Array.isArray(filter.value) ? filter.value : ['', ''];
                                        updateFilter(groupIndex, filterIndex, 'value', [currentValue[0], value]);
                                    }"
                                    placeholder="Hasta"
                                    class="h-8 text-xs"
                                />
                            </div>
                            <Input
                                v-else
                                :type="getFieldType(filter.field) === 'date' ? 'date' :
                                       getFieldType(filter.field) === 'number' ? 'number' : 'text'"
                                :model-value="filter.value"
                                @update:model-value="(value) => updateFilter(groupIndex, filterIndex, 'value', value)"
                                placeholder="Valor"
                                class="h-8 text-xs"
                            />
                        </div>

                        <!-- Acciones -->
                        <div class="lg:col-span-1 flex justify-end">
                            <div class="flex gap-1">
                                <Button @click="addFilter(groupIndex)" size="sm" variant="ghost" class="h-8 w-8 p-0 flex-shrink-0">
                                    <Plus class="h-3 w-3" />
                                </Button>
                                <Button
                                    @click="removeFilter(groupIndex, filterIndex)"
                                    size="sm"
                                    variant="ghost"
                                    class="h-8 w-8 p-0 flex-shrink-0"
                                    v-if="group.filters.length > 1 || filterGroups.length > 1"
                                >
                                    <Trash2 class="h-3 w-3" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div v-if="filterGroups.length === 0" class="text-center py-4 text-muted-foreground">
            <p>No hay filtros configurados</p>
            <Button @click="addFilterGroup" size="sm" variant="outline" class="mt-2">
                <Plus class="h-4 w-4 mr-2" />
                Agregar Primer Filtro
            </Button>
        </div>
    </div>
</template>
