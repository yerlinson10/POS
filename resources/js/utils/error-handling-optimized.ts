// error-handling-optimized.ts - Manejo de errores optimizado
import { ref, onErrorCaptured } from 'vue'
import { toast } from 'vue-sonner'

// Error boundary utility
export function useErrorBoundary() {
  const hasError = ref(false)
  const error = ref<Error | null>(null)

  onErrorCaptured((err: Error, instance, info) => {
    console.error('Error captured:', err, info)

    hasError.value = true
    error.value = err

    // Log error to service
    logError(err, { instance, info })

    // Show user-friendly message
    toast.error('Something went wrong. Please try again.')

    // Prevent error from propagating
    return false
  })

  const resetError = () => {
    hasError.value = false
    error.value = null
  }

  return {
    hasError,
    error,
    resetError
  }
}

// Global error logger
class ErrorLogger {
  private static instance: ErrorLogger
  private errors: Array<{ error: Error; context: any; timestamp: Date }> = []

  static getInstance(): ErrorLogger {
    if (!ErrorLogger.instance) {
      ErrorLogger.instance = new ErrorLogger()
    }
    return ErrorLogger.instance
  }

  log(error: Error, context?: any) {
    const entry = {
      error,
      context,
      timestamp: new Date()
    }

    this.errors.push(entry)

    // Keep only last 100 errors
    if (this.errors.length > 100) {
      this.errors.shift()
    }

    // Send to logging service in production
    if (import.meta.env.PROD) {
      this.sendToLoggingService(entry)
    }
  }

  private async sendToLoggingService(entry: any) {
    try {
      await fetch('/api/errors', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          message: entry.error.message,
          stack: entry.error.stack,
          context: entry.context,
          timestamp: entry.timestamp,
          url: window.location.href,
          userAgent: navigator.userAgent
        })
      })
    } catch (err) {
      console.error('Failed to send error to logging service:', err)
    }
  }

  getErrors() {
    return this.errors
  }

  clear() {
    this.errors = []
  }
}

export const logError = (error: Error, context?: any) => {
  ErrorLogger.getInstance().log(error, context)
}

// Retry utility
export function useRetry() {
  const retryOperation = async <T>(
    operation: () => Promise<T>,
    maxRetries: number = 3,
    delay: number = 1000
  ): Promise<T> => {
    let lastError: Error

    for (let i = 0; i < maxRetries; i++) {
      try {
        return await operation()
      } catch (error) {
        lastError = error as Error

        if (i === maxRetries - 1) {
          throw lastError
        }

        // Exponential backoff
        const waitTime = delay * Math.pow(2, i)
        await new Promise(resolve => setTimeout(resolve, waitTime))
      }
    }

    throw lastError!
  }

  return {
    retryOperation
  }
}

// Network error handler
export function useNetworkErrorHandler() {
  const isOnline = ref(navigator.onLine)
  const networkError = ref<string | null>(null)

  const handleNetworkError = (error: any) => {
    if (!navigator.onLine) {
      networkError.value = 'You are offline. Please check your internet connection.'
      toast.error('Connection lost. Please check your internet connection.')
      return
    }

    if (error.response?.status === 422) {
      // Validation errors
      const errors = error.response.data.errors
      Object.keys(errors).forEach(field => {
        toast.error(errors[field][0])
      })
    } else if (error.response?.status === 500) {
      // Server errors
      toast.error('Server error. Please try again later.')
    } else if (error.code === 'ECONNABORTED') {
      // Timeout
      toast.error('Request timeout. Please try again.')
    } else {
      // Generic error
      toast.error('Something went wrong. Please try again.')
    }

    logError(error)
  }

  // Listen for online/offline events
  window.addEventListener('online', () => {
    isOnline.value = true
    networkError.value = null
    toast.success('Connection restored')
  })

  window.addEventListener('offline', () => {
    isOnline.value = false
    networkError.value = 'You are offline'
    toast.error('Connection lost')
  })

  return {
    isOnline,
    networkError,
    handleNetworkError
  }
}

// Form validation error handler
export function useFormErrorHandler() {
  const errors = ref<Record<string, string[]>>({})

  const handleValidationErrors = (errorResponse: any) => {
    if (errorResponse.status === 422) {
      errors.value = errorResponse.data.errors

      // Show first error for each field
      Object.keys(errors.value).forEach(field => {
        if (errors.value[field].length > 0) {
          toast.error(errors.value[field][0])
        }
      })
    }
  }

  const clearErrors = () => {
    errors.value = {}
  }

  const clearFieldError = (field: string) => {
    if (errors.value[field]) {
      delete errors.value[field]
    }
  }

  return {
    errors,
    handleValidationErrors,
    clearErrors,
    clearFieldError
  }
}
