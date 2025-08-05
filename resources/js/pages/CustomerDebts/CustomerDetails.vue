<template>
    <AppLayout title="Customer Details">
        <div class="container mx-auto p-6 space-y-6">
            <!-- Customer Header -->
            <div class="flex items-start justify-between">
                <div class="space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight">{{ customer.full_name }}</h1>
                    <div class="space-y-1">
                        <p class="text-muted-foreground">Customer since {{ formatDate(customer.created_at) }}</p>
                        <div class="flex items-center space-x-4 text-sm text-muted-foreground">
                            <span v-if="customer.email" class="flex items-center space-x-1">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                                <span>{{ customer.email }}</span>
                            </span>
                            <span v-if="customer.phone" class="flex items-center space-x-1">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <span>{{ customer.phone }}</span>
                            </span>
                        </div>
                        <p v-if="customer.address" class="text-sm text-muted-foreground flex items-start space-x-1">
                            <svg class="h-4 w-4 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>{{ customer.address }}</span>
                        </p>
                    </div>
                </div>
                <div class="text-right space-y-1">
                    <div class="text-3xl font-bold" :class="{
                        'text-green-600': totalDebt <= 0,
                        'text-destructive': totalDebt > 0
                    }">
                        {{ totalDebt > 0 ? '-' : '' }}RD${{ formatCurrency(Math.abs(totalDebt)) }}
                    </div>
                    <p class="text-sm text-muted-foreground">
                        {{ totalDebt > 0 ? 'Outstanding Balance' : 'No Outstanding Debt' }}
                    </p>
                    <div v-if="statistics && statistics.overdue_debts_count > 0" class="mt-2">
                        <Badge variant="destructive" class="text-xs">
                            {{ statistics.overdue_debts_count }} overdue debt{{ statistics.overdue_debts_count > 1 ? 's' : '' }}
                        </Badge>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
                        <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <Card class="bg-gradient-to-br from-blue-50 to-blue-100 border-blue-200">
                    <CardContent class="flex items-center justify-between p-6">
                        <div>
                            <p class="text-sm font-medium text-blue-700">Total Invoices</p>
                            <p class="text-2xl font-bold text-blue-900">{{ customer.invoices?.length || 0 }}</p>
                        </div>
                        <div class="h-12 w-12 bg-blue-500 rounded-full flex items-center justify-center">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </CardContent>
                </Card>

                <Card class="bg-gradient-to-br from-green-50 to-green-100 border-green-200">
                    <CardContent class="flex items-center justify-between p-6">
                        <div>
                            <p class="text-sm font-medium text-green-700">Total Paid</p>
                            <p class="text-2xl font-bold text-green-900">
                                RD${{ formatCurrency(statistics?.total_paid_amount || 0) }}
                            </p>
                        </div>
                        <div class="h-12 w-12 bg-green-500 rounded-full flex items-center justify-center">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                        </div>
                    </CardContent>
                </Card>

                <Card class="bg-gradient-to-br from-red-50 to-red-100 border-red-200">
                    <CardContent class="flex items-center justify-between p-6">
                        <div>
                            <p class="text-sm font-medium text-red-700">Outstanding Debt</p>
                            <p class="text-2xl font-bold text-red-900">
                                RD${{ formatCurrency(Math.abs(totalDebt)) }}
                            </p>
                        </div>
                        <div class="h-12 w-12 bg-red-500 rounded-full flex items-center justify-center">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                    </CardContent>
                </Card>

                <Card class="bg-gradient-to-br from-purple-50 to-purple-100 border-purple-200">
                    <CardContent class="flex items-center justify-between p-6">
                        <div>
                            <p class="text-sm font-medium text-purple-700">Total Sales</p>
                            <p class="text-2xl font-bold text-purple-900">
                                RD${{ formatCurrency(statistics?.total_invoice_amount || 0) }}
                            </p>
                        </div>
                        <div class="h-12 w-12 bg-purple-500 rounded-full flex items-center justify-center">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content -->
            <Tabs v-model="activeTab" class="w-full">
                <TabsList class="grid w-full grid-cols-3">
                    <TabsTrigger value="invoices" class="relative">
                        Invoices
                        <Badge v-if="customer.invoices?.length" variant="secondary" class="ml-2">
                            {{ customer.invoices.length }}
                        </Badge>
                    </TabsTrigger>
                    <TabsTrigger value="debts" class="relative">
                        Debts
                        <Badge v-if="customer.customer_debts?.filter(d => d.status === 'pending').length"
                               variant="destructive" class="ml-2">
                            {{ customer.customer_debts.filter(d => d.status === 'pending').length }}
                        </Badge>
                    </TabsTrigger>
                    <TabsTrigger value="payments" class="relative">
                        Payments
                        <Badge v-if="customer.payments?.length" variant="secondary" class="ml-2">
                            {{ customer.payments.length }}
                        </Badge>
                    </TabsTrigger>
                </TabsList>

                <!-- Invoices Tab -->
                <TabsContent value="invoices" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium">Customer Invoices</h3>
                    </div>

                    <div v-if="customer.invoices && customer.invoices.length > 0" class="space-y-3">
                        <Card v-for="invoice in customer.invoices" :key="invoice.id" class="hover:shadow-md transition-shadow border-l-4" :class="{
                            'border-l-green-500': !invoice.customer_debts || invoice.customer_debts.length === 0,
                            'border-l-red-500': invoice.customer_debts && invoice.customer_debts.length > 0
                        }">
                            <CardHeader class="pb-3">
                                <div class="flex items-start justify-between">
                                    <div class="space-y-2">
                                        <div class="flex items-center space-x-2">
                                            <CardTitle class="text-base">Invoice #{{ invoice.invoice_number || invoice.id }}</CardTitle>
                                            <Badge :variant="getStatusVariant(invoice.status)" class="text-xs">
                                                {{ invoice.status || (invoice.customer_debts && invoice.customer_debts.length > 0 ? 'Has Debt' : 'Paid') }}
                                            </Badge>
                                        </div>
                                        <div class="flex items-center space-x-4 text-sm text-muted-foreground">
                                            <span class="flex items-center space-x-1">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0h6M6 7v10a2 2 0 002 2h8a2 2 0 002-2V7M6 7h12l-1 10H7L6 7z" />
                                                </svg>
                                                <span>{{ invoice.items?.length || 0 }} item{{ (invoice.items?.length || 0) !== 1 ? 's' : '' }}</span>
                                            </span>
                                            <span v-if="invoice.user" class="flex items-center space-x-1">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                <span>{{ invoice.user.name }}</span>
                                            </span>
                                            <Badge variant="outline" class="text-xs">
                                                {{ invoice.payment_method || 'Cash' }}
                                            </Badge>
                                        </div>
                                        <p class="text-sm text-muted-foreground">
                                            {{ formatDate(invoice.created_at) }} at {{ formatTime(invoice.created_at) }}
                                        </p>
                                    </div>
                                    <div class="text-right space-y-1">
                                        <p class="text-xl font-bold">RD${{ formatCurrency(invoice.total_amount) }}</p>
                                        <div v-if="invoice.items && invoice.items.length > 0" class="text-xs text-muted-foreground">
                                            {{ invoice.items.reduce((sum, item) => sum + item.quantity, 0) }} total items
                                        </div>
                                    </div>
                                </div>
                            </CardHeader>

                            <!-- Invoice Items Preview -->
                            <CardContent v-if="invoice.items && invoice.items.length > 0" class="pt-0 pb-3">
                                <div class="space-y-2">
                                    <p class="text-sm font-medium text-muted-foreground">Items:</p>
                                    <div class="space-y-1">
                                        <div v-for="item in invoice.items.slice(0, 3)" :key="item.id"
                                             class="flex justify-between text-sm bg-muted/30 dark:bg-muted/50 rounded px-3 py-2 border border-border">
                                            <span class="text-foreground">{{ item.product?.name || 'Product' }} × {{ item.quantity }}</span>
                                            <span class="font-medium text-foreground">RD${{ formatCurrency(item.unit_price * item.quantity) }}</span>
                                        </div>
                                        <div v-if="invoice.items.length > 3" class="text-xs text-muted-foreground text-center py-1">
                                            +{{ invoice.items.length - 3 }} more items...
                                        </div>
                                    </div>
                                </div>
                            </CardContent>

                            <!-- Outstanding Debts -->
                            <CardContent v-if="invoice.customer_debts && invoice.customer_debts.length > 0" class="pt-0">
                                <div class="rounded-lg border-l-4 border-l-destructive bg-destructive/5 p-3">
                                    <div class="flex items-center space-x-2">
                                        <svg class="h-4 w-4 text-destructive" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                        </svg>
                                        <p class="text-sm font-medium text-destructive">Outstanding Debt</p>
                                    </div>
                                    <div class="mt-2 space-y-1">
                                        <div v-for="debt in invoice.customer_debts" :key="debt.id" class="text-sm">
                                            <span class="font-medium">RD${{ formatCurrency(debt.remaining_amount) }}</span>
                                            <span class="text-muted-foreground"> • {{ debt.status }}</span>
                                            <span v-if="debt.due_date" class="text-destructive"> • Due {{ formatDate(debt.due_date) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <div v-else class="flex flex-col items-center justify-center py-12 text-center">
                        <svg class="h-12 w-12 text-muted-foreground/50 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="text-lg font-medium mb-1">No invoices found</h3>
                        <p class="text-muted-foreground">This customer hasn't made any purchases yet.</p>
                    </div>
                </TabsContent>

                <!-- Debts Tab -->
                <TabsContent value="debts" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium">Customer Debts</h3>
                    </div>

                    <div v-if="customer.customer_debts && customer.customer_debts.length > 0" class="space-y-3">
                        <Card v-for="debt in customer.customer_debts" :key="debt.id"
                              class="hover:shadow-md transition-shadow border-l-4"
                              :class="{
                                  'border-l-red-500 ring-2 ring-destructive/20': debt.status === 'pending',
                                  'border-l-green-500 ring-2 ring-green-500/20': debt.status === 'paid'
                              }">
                            <CardHeader class="pb-3">
                                <div class="flex items-start justify-between">
                                    <div class="space-y-2">
                                        <div class="flex items-center space-x-2">
                                            <CardTitle class="text-base">Debt #{{ debt.id }}</CardTitle>
                                            <Badge :variant="debt.status === 'pending' ? 'destructive' : 'success'" class="text-xs">
                                                {{ debt.status }}
                                            </Badge>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="flex items-center space-x-4 text-sm text-muted-foreground">
                                                <span class="flex items-center space-x-1">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0h6M6 7v10a2 2 0 002 2h8a2 2 0 002-2V7M6 7h12l-1 10H7L6 7z" />
                                                    </svg>
                                                    <span>{{ formatDate(debt.created_at) }}</span>
                                                </span>
                                                <span v-if="debt.due_date" class="flex items-center space-x-1" :class="{
                                                    'text-destructive': new Date(debt.due_date) < new Date() && debt.status === 'pending'
                                                }">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <span>Due {{ formatDate(debt.due_date) }}</span>
                                                </span>
                                            </div>
                                            <div class="text-sm">
                                                <span class="text-muted-foreground">Original: </span>
                                                <span class="font-medium">RD${{ formatCurrency(debt.original_amount) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right space-y-1">
                                        <p class="text-xl font-bold" :class="{
                                            'text-destructive': debt.status === 'pending',
                                            'text-green-600': debt.status === 'paid'
                                        }">
                                            RD${{ formatCurrency(debt.remaining_amount) }}
                                        </p>
                                        <div v-if="debt.payments && debt.payments.length > 0" class="text-xs text-muted-foreground">
                                            {{ debt.payments.length }} payment{{ debt.payments.length > 1 ? 's' : '' }} made
                                        </div>
                                    </div>
                                </div>
                            </CardHeader>

                            <CardContent class="space-y-3">
                                <!-- Notes -->
                                <div v-if="debt.notes" class="text-sm bg-muted/30 dark:bg-muted/50 rounded p-3 border border-border">
                                    <div class="flex items-start space-x-2">
                                        <svg class="h-4 w-4 mt-0.5 flex-shrink-0 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                        </svg>
                                        <span class="text-foreground">{{ debt.notes }}</span>
                                    </div>
                                </div>

                                <!-- Related Invoice -->
                                <div v-if="debt.invoice" class="rounded-lg border bg-card p-3 border-border">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <svg class="h-4 w-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="text-sm font-medium text-foreground">Related Invoice</p>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <p class="text-sm text-foreground">
                                            Invoice #{{ debt.invoice.invoice_number || debt.invoice.id }}
                                        </p>
                                        <span class="font-medium text-sm text-foreground">RD${{ formatCurrency(debt.invoice.total_amount) }}</span>
                                    </div>
                                    <div v-if="debt.invoice.user" class="text-xs text-muted-foreground mt-1">
                                        Cashier: {{ debt.invoice.user.name }}
                                    </div>
                                </div>

                                <!-- Related Payments -->
                                <div v-if="debt.payments && debt.payments.length > 0" class="space-y-2">
                                    <div class="flex items-center space-x-2">
                                        <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                        </svg>
                                        <p class="text-sm font-medium text-green-600">Payment History</p>
                                    </div>
                                    <div class="space-y-1">
                                        <div v-for="payment in debt.payments.slice(0, 3)" :key="payment.id"
                                             class="flex justify-between items-center text-sm bg-green-50 dark:bg-green-900/20 rounded px-3 py-2 border border-green-200 dark:border-green-800">
                                            <div class="space-y-0.5">
                                                <div class="flex items-center space-x-2">
                                                    <span class="font-medium text-green-800 dark:text-green-200">RD${{ formatCurrency(payment.amount) }}</span>
                                                    <Badge variant="outline" class="text-xs border-green-300 text-green-700 dark:border-green-600 dark:text-green-300">{{ payment.payment_method }}</Badge>
                                                </div>
                                                <div class="text-xs text-green-700 dark:text-green-300">
                                                    {{ formatDate(payment.payment_date) }}
                                                    <span v-if="payment.user">• by {{ payment.user.name }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="debt.payments.length > 3" class="text-xs text-muted-foreground text-center py-1">
                                            +{{ debt.payments.length - 3 }} more payments...
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Progress -->
                                <div v-if="debt.status === 'pending'" class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-muted-foreground">Payment Progress</span>
                                        <span class="font-medium">
                                            {{ Math.round(((debt.original_amount - debt.remaining_amount) / debt.original_amount) * 100) }}%
                                        </span>
                                    </div>
                                    <div class="w-full bg-muted rounded-full h-2">
                                        <div
                                            class="bg-primary h-2 rounded-full transition-all duration-300"
                                            :style="{
                                                width: `${Math.min(100, ((debt.original_amount - debt.remaining_amount) / debt.original_amount) * 100)}%`
                                            }"
                                        ></div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div v-if="debt.status === 'pending'" class="pt-3 border-t border-border">
                                    <button class="w-full bg-primary text-primary-foreground hover:bg-primary/90 px-4 py-2 rounded-md text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                                        Add Payment
                                    </button>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <div v-else class="flex flex-col items-center justify-center py-12 text-center">
                        <svg class="h-12 w-12 text-muted-foreground/50 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <h3 class="text-lg font-medium mb-1">No debts found</h3>
                        <p class="text-muted-foreground">This customer has no outstanding debts.</p>
                    </div>
                </TabsContent>

                <!-- Payments Tab -->
                <TabsContent value="payments" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium">Payment History</h3>
                    </div>

                    <div v-if="customer.payments && customer.payments.length > 0" class="space-y-3">
                        <Card v-for="payment in customer.payments" :key="payment.id" class="hover:shadow-md transition-shadow">
                            <CardHeader class="pb-3">
                                <div class="flex items-center justify-between">
                                    <div class="space-y-1">
                                        <CardTitle class="text-base">Payment #{{ payment.id }}</CardTitle>
                                        <div class="space-y-1">
                                            <p class="text-sm text-muted-foreground">{{ formatDate(payment.payment_date) }}</p>
                                            <div class="flex items-center space-x-2">
                                                <Badge variant="outline">{{ payment.payment_method || payment.type }}</Badge>
                                                <span v-if="payment.reference_number" class="text-xs text-muted-foreground">
                                                    Ref: {{ payment.reference_number }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right space-y-1">
                                        <p class="text-xl font-bold text-green-600">+RD${{ formatCurrency(payment.amount) }}</p>
                                        <p class="text-sm text-muted-foreground capitalize">{{ payment.category || 'Payment' }}</p>
                                    </div>
                                </div>
                            </CardHeader>
                        </Card>
                    </div>

                    <div v-else class="flex flex-col items-center justify-center py-12 text-center">
                        <svg class="h-12 w-12 text-muted-foreground/50 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                        <h3 class="text-lg font-medium mb-1">No payments found</h3>
                        <p class="text-muted-foreground">This customer hasn't made any payments yet.</p>
                    </div>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import AppLayout from '../../layouts/AppLayout.vue'
import Card from '../../components/ui/card/Card.vue'
import CardHeader from '../../components/ui/card/CardHeader.vue'
import CardTitle from '../../components/ui/card/CardTitle.vue'
import CardContent from '../../components/ui/card/CardContent.vue'
import Badge from '../../components/ui/badge/Badge.vue'
import Tabs from '../../components/ui/tabs/Tabs.vue'
import TabsList from '../../components/ui/tabs/TabsList.vue'
import TabsTrigger from '../../components/ui/tabs/TabsTrigger.vue'
import TabsContent from '../../components/ui/tabs/TabsContent.vue'

interface Customer {
    id: number
    first_name: string
    last_name: string
    full_name: string
    email?: string
    phone?: string
    address?: string
    created_at: string
    invoices?: Array<{
        id: number
        invoice_number: string
        total_amount: number
        paid_amount: number
        debt_amount: number
        status: string
        payment_status: string
        payment_method: string
        created_at: string
        due_date?: string
        subtotal: number
        discount_value: number
        customer_debts?: Array<{
            id: number
            remaining_amount: number
            original_amount: number
            paid_amount: number
            status: string
            due_date?: string
            created_at: string
        }>
        items?: Array<{
            id: number
            product_id: number
            quantity: number
            unit_price: number
            line_total: number
            product?: {
                id: number
                name: string
                sku?: string
            }
        }>
        user?: {
            id: number
            name: string
        }
    }>
    customer_debts?: Array<{
        id: number
        remaining_amount: number
        original_amount: number
        paid_amount: number
        status: string
        created_at: string
        due_date?: string
        debt_date: string
        notes?: string
        invoice?: {
            id: number
            invoice_number?: string
            total_amount: number
            created_at: string
            user?: {
                id: number
                name: string
            }
        }
        payments?: Array<{
            id: number
            amount: number
            payment_date: string
            payment_method: string
            user?: {
                id: number
                name: string
            }
        }>
        user?: {
            id: number
            name: string
        }
    }>
    payments?: Array<{
        id: number
        amount: number
        type: string
        category: string
        payment_method: string
        payment_date: string
        reference_number?: string
        description?: string
        customer_debt_id?: number
        user?: {
            id: number
            name: string
        }
    }>
}

interface Statistics {
    total_invoice_amount: number
    total_paid_amount: number
    total_debt_amount: number
    overdue_debts_count: number
    overdue_debts_amount: number
}

const props = defineProps<{
    customer: Customer
    statistics?: Statistics
}>()

const activeTab = ref('invoices')

const totalDebt = computed(() => {
    return props.customer.customer_debts?.reduce((sum, debt) => {
        return debt.status === 'pending' ? sum + debt.remaining_amount : sum
    }, 0) || 0
})

const getStatusVariant = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'paid':
            return 'success'
        case 'pending':
        case 'unpaid':
            return 'destructive'
        case 'partial':
            return 'warning'
        default:
            return 'secondary'
    }
}

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-DO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount)
}

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('es-DO', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const formatTime = (dateString: string) => {
    return new Date(dateString).toLocaleTimeString('es-DO', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    })
}
</script>
