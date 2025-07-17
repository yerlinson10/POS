// optimized-forms.ts - Formularios optimizados
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'

// Composable para validación optimizada
export function useOptimizedForm<T extends Record<string, any>>(
    initialData: T,
    validationRules?: Record<keyof T, (value: any) => string | null>
) {
    const form = useForm(initialData)
    const errors = ref<Partial<Record<keyof T, string>>>({})
    const isValidating = ref(false)

    // Debounced validation
    let validationTimeout: number | null = null

    const validateField = (field: keyof T, value: any) => {
        if (validationTimeout) {
            clearTimeout(validationTimeout)
        }

        validationTimeout = setTimeout(() => {
            if (validationRules?.[field]) {
                const error = validationRules[field](value)
                if (error) {
                    errors.value[field] = error
                } else {
                    delete errors.value[field]
                }
            }
        }, 300)
    }

    const hasErrors = computed(() => {
        return Object.keys(errors.value).length > 0
    })

    const isValid = computed(() => {
        return !hasErrors.value && !isValidating.value
    })

    // Watch form changes for validation
    Object.keys(initialData).forEach(key => {
        watch(() => form[key], (newValue) => {
            validateField(key as keyof T, newValue)
        })
    })

    const reset = () => {
        form.reset()
        errors.value = {}
        if (validationTimeout) {
            clearTimeout(validationTimeout)
        }
    }

    return {
        form,
        errors,
        isValidating,
        hasErrors,
        isValid,
        validateField,
        reset
    }
}

// Optimized search composable
export function useOptimizedSearch<T>(
    searchFunction: (term: string) => Promise<T[]>,
    options: {
        debounceTime?: number
        minLength?: number
        maxResults?: number
    } = {}
) {
    const {
        debounceTime = 300,
        minLength = 2,
        maxResults = 10
    } = options

    const searchTerm = ref('')
    const results = ref<T[]>([])
    const isLoading = ref(false)
    const error = ref<string | null>(null)

    let searchTimeout: number | null = null

    const search = async (term: string) => {
        if (term.length < minLength) {
            results.value = []
            return
        }

        if (searchTimeout) {
            clearTimeout(searchTimeout)
        }

        searchTimeout = setTimeout(async () => {
            isLoading.value = true
            error.value = null

            try {
                const data = await searchFunction(term)
                results.value = data.slice(0, maxResults)
            } catch (err) {
                error.value = err instanceof Error ? err.message : 'Search error'
                results.value = []
            } finally {
                isLoading.value = false
            }
        }, debounceTime)
    }

    watch(searchTerm, (newTerm) => {
        search(newTerm)
    })

    const clearSearch = () => {
        searchTerm.value = ''
        results.value = []
        error.value = null
    }

    return {
        searchTerm,
        results,
        isLoading,
        error,
        search,
        clearSearch
    }
}
