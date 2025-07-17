// optimized-watchers.ts - Watchers optimizados
import { watch, onUnmounted } from 'vue'

// Debounced watcher utility
export function useDebouncedWatch<T>(
    source: () => T,
    callback: (value: T, oldValue: T) => void,
    delay: number = 300
) {
    let timeoutId: number | null = null

    const stopWatcher = watch(source, (newValue, oldValue) => {
        if (timeoutId) {
            clearTimeout(timeoutId)
        }

        timeoutId = setTimeout(() => {
            callback(newValue, oldValue)
        }, delay)
    })

    const cleanup = () => {
        if (timeoutId) {
            clearTimeout(timeoutId)
        }
        stopWatcher()
    }

    onUnmounted(cleanup)

    return cleanup
}

// Throttled watcher utility
export function useThrottledWatch<T>(
    source: () => T,
    callback: (value: T, oldValue: T) => void,
    delay: number = 100
) {
    let lastCallTime = 0
    let timeoutId: number | null = null

    const stopWatcher = watch(source, (newValue, oldValue) => {
        const now = Date.now()

        if (now - lastCallTime >= delay) {
            lastCallTime = now
            callback(newValue, oldValue)
        } else {
            if (timeoutId) {
                clearTimeout(timeoutId)
            }

            timeoutId = setTimeout(() => {
                lastCallTime = Date.now()
                callback(newValue, oldValue)
            }, delay - (now - lastCallTime))
        }
    })

    const cleanup = () => {
        if (timeoutId) {
            clearTimeout(timeoutId)
        }
        stopWatcher()
    }

    onUnmounted(cleanup)

    return cleanup
}

// Optimized lifecycle manager
export function useOptimizedLifecycle() {
    const intervals: number[] = []
    const timeouts: number[] = []
    const eventListeners: Array<{ element: EventTarget, event: string, handler: EventListener }> = []

    const addInterval = (callback: () => void, delay: number) => {
        const id = setInterval(callback, delay)
        intervals.push(id)
        return id
    }

    const addTimeout = (callback: () => void, delay: number) => {
        const id = setTimeout(callback, delay)
        timeouts.push(id)
        return id
    }

    const addEventListener = (element: EventTarget, event: string, handler: EventListener) => {
        element.addEventListener(event, handler)
        eventListeners.push({ element, event, handler })
    }

    const cleanup = () => {
        intervals.forEach(id => clearInterval(id))
        timeouts.forEach(id => clearTimeout(id))
        eventListeners.forEach(({ element, event, handler }) => {
            element.removeEventListener(event, handler)
        })

        intervals.length = 0
        timeouts.length = 0
        eventListeners.length = 0
    }

    onUnmounted(cleanup)

    return {
        addInterval,
        addTimeout,
        addEventListener,
        cleanup
    }
}
