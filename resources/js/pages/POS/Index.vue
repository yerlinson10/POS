<template>
    <AppLayout title="Point of Sale">

        <!-- Session status notification (moved to top, margin optimized) -->
        <!-- <SessionStatus class="mx-4 my-4" /> -->

        <div class="flex flex-col lg:flex-row h-auto lg:h-[calc(100vh-4rem)] gap-4 p-2 md:p-4">
            <!-- Left Panel - Cart and Products -->
            <div class="w-full lg:w-2/3 flex flex-col gap-4">
                <!-- Cart Section -->
                <Card class="flex-1 p-3 md:p-6">
                    <div>
                        <SessionStatus/>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 md:mb-6 gap-3 sm:gap-0">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg md:text-xl font-semibold">Shopping Cart</h3>
                                <div v-if="isCartNavigationActive"
                                    class="px-2 py-1 bg-primary/10 text-primary text-xs rounded-full border border-primary/20">
                                    Navigation Mode
                                </div>
                            </div>
                            <p class="text-xs md:text-sm text-muted-foreground">
                                {{ itemCount }} {{ itemCount === 1 ? 'item' : 'items' }} selected
                                <span v-if="isCartNavigationActive" class="text-primary"> • Use F9 to toggle
                                    navigation</span>
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button @click="showProductModal = true"
                                class="h-9 md:h-10 cursor-pointer text-xs md:text-sm">
                                <Icon name="Plus" class="w-3 h-3 md:w-4 md:h-4 mr-1 md:mr-2" />
                                <span class="hidden sm:inline">Add Products (F2)</span>
                                <span class="sm:hidden">Add (F2)</span>
                            </Button>
                            <AlertDialog>
                                <AlertDialogTrigger as-child>
                                    <Button variant="outline" size="sm" class="h-9 md:h-10 cursor-pointer"
                                        title="Keyboard Shortcuts (F1)">
                                        <Icon name="Keyboard" class="w-3 h-3 md:w-4 md:h-4" />
                                    </Button>
                                </AlertDialogTrigger>
                                <AlertDialogContent
                                    class="max-w-[95vw] sm:max-w-xl md:max-w-2xl max-h-[90vh] overflow-hidden">
                                    <AlertDialogHeader class="pb-4">
                                        <AlertDialogTitle
                                            class="flex items-center gap-2 text-base sm:text-lg font-semibold">
                                            <Icon name="Keyboard" class="w-4 h-4 sm:w-5 sm:h-5 text-muted-foreground" />
                                            Keyboard Shortcuts
                                        </AlertDialogTitle>
                                        <AlertDialogDescription class="text-xs sm:text-sm text-muted-foreground">
                                            Navigate and control the POS system efficiently with these keyboard
                                            shortcuts.
                                        </AlertDialogDescription>
                                    </AlertDialogHeader>

                                    <div class="max-h-[65vh] overflow-y-auto pr-1 -mr-1 space-y-4 sm:space-y-6">
                                        <!-- Main Actions -->
                                        <div>
                                            <h3
                                                class="text-xs sm:text-sm font-medium text-muted-foreground mb-2 sm:mb-3 flex items-center gap-2">
                                                <Icon name="Zap" class="w-3 h-3 sm:w-4 sm:h-4" />
                                                Main Actions
                                            </h3>
                                            <div class="space-y-1 sm:space-y-2">
                                                <div
                                                    class="flex items-center justify-between p-2 sm:p-3 rounded-md hover:bg-accent/50 transition-colors">
                                                    <span class="text-xs sm:text-sm">Open Product List</span>
                                                    <kbd
                                                        class="px-2 py-1 bg-muted border border-border rounded text-xs font-mono">F2</kbd>
                                                </div>
                                                <div
                                                    class="flex items-center justify-between p-2 sm:p-3 rounded-md hover:bg-accent/50 transition-colors">
                                                    <span class="text-xs sm:text-sm">Open Customer Modal</span>
                                                    <kbd
                                                        class="px-2 py-1 bg-muted border border-border rounded text-xs font-mono">F3</kbd>
                                                </div>
                                                <div
                                                    class="flex items-center justify-between p-2 sm:p-3 rounded-md hover:bg-accent/50 transition-colors">
                                                    <span class="text-xs sm:text-sm">Process Invoice</span>
                                                    <kbd
                                                        class="px-2 py-1 bg-primary/10 border border-primary/20 text-primary rounded text-xs font-mono">F4</kbd>
                                                </div>
                                                <div
                                                    class="flex items-center justify-between p-2 sm:p-3 rounded-md hover:bg-accent/50 transition-colors">
                                                    <span class="text-xs sm:text-sm">Focus Customer Search</span>
                                                    <kbd
                                                        class="px-2 py-1 bg-muted border border-border rounded text-xs font-mono">F5</kbd>
                                                </div>
                                                <div
                                                    class="flex items-center justify-between p-2 sm:p-3 rounded-md hover:bg-accent/50 transition-colors">
                                                    <span class="text-xs sm:text-sm">Toggle Invoice Status</span>
                                                    <kbd
                                                        class="px-2 py-1 bg-muted border border-border rounded text-xs font-mono">F8</kbd>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Discounts -->
                                        <div>
                                            <h3
                                                class="text-xs sm:text-sm font-medium text-muted-foreground mb-2 sm:mb-3 flex items-center gap-2">
                                                <Icon name="Percent" class="w-3 h-3 sm:w-4 sm:h-4" />
                                                Discounts
                                            </h3>
                                            <div class="space-y-1 sm:space-y-2">
                                                <div
                                                    class="flex items-center justify-between p-2 sm:p-3 rounded-md hover:bg-accent/50 transition-colors">
                                                    <span class="text-xs sm:text-sm">Percentage Discount</span>
                                                    <kbd
                                                        class="px-2 py-1 bg-muted border border-border rounded text-xs font-mono">F6</kbd>
                                                </div>
                                                <div
                                                    class="flex items-center justify-between p-2 sm:p-3 rounded-md hover:bg-accent/50 transition-colors">
                                                    <span class="text-xs sm:text-sm">Fixed Amount Discount</span>
                                                    <kbd
                                                        class="px-2 py-1 bg-muted border border-border rounded text-xs font-mono">F7</kbd>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Payment Methods -->
                                        <div>
                                            <h3
                                                class="text-xs sm:text-sm font-medium text-muted-foreground mb-2 sm:mb-3 flex items-center gap-2">
                                                <Icon name="CreditCard" class="w-3 h-3 sm:w-4 sm:h-4" />
                                                Payment Methods
                                            </h3>
                                            <div class="space-y-1 sm:space-y-2">
                                                <div
                                                    class="flex items-center justify-between p-2 sm:p-3 rounded-md hover:bg-accent/50 transition-colors">
                                                    <span class="text-xs sm:text-sm">Cycle Payment Methods</span>
                                                    <div class="flex gap-1">
                                                        <kbd class="px-2 py-1 bg-muted border border-border rounded text-xs font-mono">F10</kbd>
                                                        <kbd class="px-2 py-1 bg-muted border border-border rounded text-xs font-mono">Alt+P</kbd>
                                                    </div>
                                                </div>
                                                <div
                                                    class="flex items-center justify-between p-2 sm:p-3 rounded-md hover:bg-accent/50 transition-colors">
                                                    <span class="text-xs sm:text-sm">Cash</span>
                                                    <div class="flex gap-1">
                                                        <kbd class="px-2 py-1 bg-muted border border-border rounded text-xs font-mono">F11</kbd>
                                                        <kbd class="px-2 py-1 bg-muted border border-border rounded text-xs font-mono">Ctrl+1</kbd>
                                                    </div>
                                                </div>
                                                <div
                                                    class="flex items-center justify-between p-2 sm:p-3 rounded-md hover:bg-accent/50 transition-colors">
                                                    <span class="text-xs sm:text-sm">Card</span>
                                                    <div class="flex gap-1">
                                                        <kbd class="px-2 py-1 bg-muted border border-border rounded text-xs font-mono">F12</kbd>
                                                        <kbd class="px-2 py-1 bg-muted border border-border rounded text-xs font-mono">Ctrl+2</kbd>
                                                    </div>
                                                </div>
                                                <div
                                                    class="flex items-center justify-between p-2 sm:p-3 rounded-md hover:bg-accent/50 transition-colors">
                                                    <span class="text-xs sm:text-sm">Transfer</span>
                                                    <kbd class="px-2 py-1 bg-muted border border-border rounded text-xs font-mono">Ctrl+3</kbd>
                                                </div>
                                                <div
                                                    class="flex items-center justify-between p-2 sm:p-3 rounded-md hover:bg-accent/50 transition-colors">
                                                    <span class="text-xs sm:text-sm">Other</span>
                                                    <kbd class="px-2 py-1 bg-muted border border-border rounded text-xs font-mono">Ctrl+4</kbd>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Cart Navigation -->
                                        <div>
                                            <h3
                                                class="text-xs sm:text-sm font-medium text-muted-foreground mb-2 sm:mb-3 flex items-center gap-2">
                                                <Icon name="ShoppingCart" class="w-3 h-3 sm:w-4 sm:h-4" />
                                                Cart Navigation
                                            </h3>
                                            <div class="space-y-1 sm:space-y-2">
                                                <div
                                                    class="flex items-center justify-between p-2 sm:p-3 rounded-md hover:bg-accent/50 transition-colors">
                                                    <span class="text-xs sm:text-sm">Toggle Cart Navigation</span>
                                                    <kbd
                                                        class="px-2 py-1 bg-primary/10 border border-primary/20 text-primary rounded text-xs font-mono">F9</kbd>
                                                </div>
                                                <div
                                                    class="flex items-center justify-between p-2 sm:p-3 rounded-md hover:bg-accent/50 transition-colors">
                                                    <span class="text-xs sm:text-sm">Navigate Cart Items</span>
                                                    <div class="flex gap-1">
                                                        <kbd
                                                            class="px-1.5 py-1 bg-muted border border-border rounded text-xs font-mono">↑</kbd>
                                                        <kbd
                                                            class="px-1.5 py-1 bg-muted border border-border rounded text-xs font-mono">↓</kbd>
                                                    </div>
                                                </div>
                                                <div
                                                    class="flex items-center justify-between p-2 sm:p-3 rounded-md hover:bg-accent/50 transition-colors">
                                                    <span class="text-xs sm:text-sm">Change Quantity</span>
                                                    <div class="flex gap-1">
                                                        <kbd
                                                            class="px-1.5 py-1 bg-muted border border-border rounded text-xs font-mono">+</kbd>
                                                        <kbd
                                                            class="px-1.5 py-1 bg-muted border border-border rounded text-xs font-mono">=</kbd>
                                                        <kbd
                                                            class="px-1.5 py-1 bg-muted border border-border rounded text-xs font-mono">-</kbd>
                                                    </div>
                                                </div>
                                                <div
                                                    class="flex items-center justify-between p-2 sm:p-3 rounded-md hover:bg-accent/50 transition-colors">
                                                    <span class="text-xs sm:text-sm">Remove Item</span>
                                                    <kbd
                                                        class="px-2 py-1 bg-destructive/10 border border-destructive/20 text-destructive rounded text-xs font-mono">Del</kbd>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Dialog Actions -->
                                        <div>
                                            <h3
                                                class="text-xs sm:text-sm font-medium text-muted-foreground mb-2 sm:mb-3 flex items-center gap-2">
                                                <Icon name="MessageSquare" class="w-3 h-3 sm:w-4 sm:h-4" />
                                                Dialog Actions
                                            </h3>
                                            <div class="space-y-1 sm:space-y-2">
                                                <div
                                                    class="flex items-center justify-between p-2 sm:p-3 rounded-md hover:bg-accent/50 transition-colors">
                                                    <span class="text-xs sm:text-sm">Confirm Payment</span>
                                                    <kbd
                                                        class="px-2 py-1 bg-muted border border-border rounded text-xs font-mono">Enter</kbd>
                                                </div>
                                                <div
                                                    class="flex items-center justify-between p-2 sm:p-3 rounded-md hover:bg-accent/50 transition-colors">
                                                    <span class="text-xs sm:text-sm">Confirm Remove Item</span>
                                                    <div class="flex gap-1">
                                                        <kbd class="px-2 py-1 bg-muted border border-border rounded text-xs font-mono">Enter</kbd>
                                                        <kbd class="px-2 py-1 bg-destructive/10 border border-destructive/20 text-destructive rounded text-xs font-mono">Del</kbd>
                                                    </div>
                                                </div>
                                                <div
                                                    class="flex items-center justify-between p-2 sm:p-3 rounded-md hover:bg-accent/50 transition-colors">
                                                    <span class="text-xs sm:text-sm">Confirm Clear Cart</span>
                                                    <div class="flex gap-1">
                                                        <kbd class="px-2 py-1 bg-destructive/10 border border-destructive/20 text-destructive rounded text-xs font-mono">Del</kbd>
                                                        <kbd class="px-2 py-1 bg-destructive/10 border border-destructive/20 text-destructive rounded text-xs font-mono">Backspace</kbd>
                                                    </div>
                                                </div>
                                                <div
                                                    class="flex items-center justify-between p-2 sm:p-3 rounded-md hover:bg-accent/50 transition-colors">
                                                    <span class="text-xs sm:text-sm">Cancel Dialog</span>
                                                    <kbd
                                                        class="px-2 py-1 bg-muted border border-border rounded text-xs font-mono">Esc</kbd>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- General Navigation -->
                                        <div>
                                            <h3
                                                class="text-xs sm:text-sm font-medium text-muted-foreground mb-2 sm:mb-3 flex items-center gap-2">
                                                <Icon name="Navigation" class="w-3 h-3 sm:w-4 sm:h-4" />
                                                Navigation & Search
                                            </h3>
                                            <div class="space-y-1 sm:space-y-2">
                                                <div
                                                    class="flex items-center justify-between p-2 sm:p-3 rounded-md hover:bg-accent/50 transition-colors">
                                                    <span class="text-xs sm:text-sm">Toggle Sidebar</span>
                                                    <kbd
                                                        class="px-2 py-1 bg-muted border border-border rounded text-xs font-mono">Ctrl+B</kbd>
                                                </div>
                                                <div
                                                    class="flex items-center justify-between p-2 sm:p-3 rounded-md hover:bg-accent/50 transition-colors">
                                                    <span class="text-xs sm:text-sm">Navigate Customer Results</span>
                                                    <div class="flex gap-1">
                                                        <kbd
                                                            class="px-1.5 py-1 bg-muted border border-border rounded text-xs font-mono">↑</kbd>
                                                        <kbd
                                                            class="px-1.5 py-1 bg-muted border border-border rounded text-xs font-mono">↓</kbd>
                                                    </div>
                                                </div>
                                                <div
                                                    class="flex items-center justify-between p-2 sm:p-3 rounded-md hover:bg-accent/50 transition-colors">
                                                    <span class="text-xs sm:text-sm">Show Shortcuts Help</span>
                                                    <kbd
                                                        class="px-2 py-1 bg-muted border border-border rounded text-xs font-mono">?</kbd>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Pro Tip -->
                                        <div class="p-3 sm:p-4 bg-accent/30 border border-border rounded-lg">
                                            <div class="flex items-start gap-2 sm:gap-3">
                                                <Icon name="Lightbulb"
                                                    class="w-4 h-4 text-muted-foreground mt-0.5 flex-shrink-0" />
                                                <div>
                                                    <p class="text-xs font-medium mb-1">Pro Tip</p>
                                                    <p class="text-xs text-muted-foreground">
                                                        Use <kbd
                                                            class="px-1 py-0.5 bg-primary/10 border border-primary/20 text-primary rounded text-xs">F9</kbd>
                                                        to enter cart navigation mode, then use arrow keys to navigate
                                                        and modify items quickly!
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <AlertDialogFooter class="border-t pt-3 sm:pt-4 mt-4">
                                        <AlertDialogCancel
                                            class="cursor-pointer hover:bg-accent transition-colors text-xs sm:text-sm">
                                            Close
                                        </AlertDialogCancel>
                                    </AlertDialogFooter>
                                </AlertDialogContent>
                            </AlertDialog>
                            <AlertDialog>
                                <AlertDialogTrigger as-child>
                                    <Button variant="destructive" size="sm" :disabled="!cart.length"
                                        class="h-9 md:h-10 cursor-pointer text-xs md:text-sm">
                                        <Icon name="Trash2" class="w-3 h-3 md:w-4 md:h-4 mr-1 md:mr-2" />
                                        <span class="hidden sm:inline">Clear Cart</span>
                                        <span class="sm:hidden">Clear</span>
                                    </Button>
                                </AlertDialogTrigger>
                                <AlertDialogContent data-clear-cart-dialog>
                                    <AlertDialogHeader>
                                        <AlertDialogTitle>Clear Shopping Cart?</AlertDialogTitle>
                                        <AlertDialogDescription>
                                            This action will remove all {{ itemCount }} item{{ itemCount !== 1 ? 's' :
                                                '' }} from your cart.
                                            This action cannot be undone.
                                        </AlertDialogDescription>
                                    </AlertDialogHeader>
                                    <AlertDialogFooter>
                                        <AlertDialogCancel class="cursor-pointer">Cancel</AlertDialogCancel>
                                        <AlertDialogAction variant="destructive"
                                            class="cursor-pointer text-white bg-red-500 hover:bg-red-400 focus:shadow-red-700 inline-flex h-[35px] items-center justify-center rounded-md px-[15px] font-semibold leading-none outline-none focus:shadow-[0_0_0_2px]"
                                            @click="clearCart">
                                            Clear Cart
                                            <span class="ml-2 text-xs opacity-75">(Del)</span>
                                        </AlertDialogAction>
                                    </AlertDialogFooter>
                                </AlertDialogContent>
                            </AlertDialog>
                        </div>
                    </div>

                    <!-- Cart Items -->
                    <div class="space-y-2 md:space-y-3 mb-4 md:mb-6 flex-1 overflow-y-auto"
                        style="max-height: calc(100vh - 16rem); min-height: 200px;">
                        <!-- Empty Cart State -->
                        <div v-if="!cart.length" class="text-center py-8 md:py-16">
                            <div class="flex flex-col items-center gap-3 md:gap-4">
                                <Icon name="ShoppingCart" class="w-12 h-12 md:w-16 md:h-16 text-muted-foreground/50" />
                                <div>
                                    <h4 class="font-medium text-base md:text-lg mb-2">Your cart is empty</h4>
                                    <p class="text-muted-foreground mb-3 md:mb-4 text-sm">Add products to get started
                                    </p>
                                    <Button @click="showProductModal = true" class="cursor-pointer">
                                        <Icon name="Plus" class="w-3 h-3 md:w-4 md:h-4 mr-1 md:mr-2" />
                                        Browse Products
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <!-- Cart Items List -->
                        <div v-for="(item, index) in cart" :key="item.product_id" :data-cart-item-index="index" :class="[
                            'flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 p-3 md:p-4 border rounded-lg bg-card transition-colors',
                            isCartNavigationActive && selectedCartItemIndex === index
                                ? 'bg-primary/10 border-primary shadow-md'
                                : 'hover:bg-accent/50'
                        ]">
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-sm md:text-base mb-1">{{ item.product_name }}</div>
                                <div class="text-xs md:text-sm text-muted-foreground mb-1 md:mb-2">{{ item.product_sku
                                }}</div>
                                <div class="text-xs md:text-sm font-medium text-primary">
                                    ${{ Number(item.unit_price).toFixed(2) }} per unit
                                </div>
                            </div>

                            <div class="flex items-center justify-between sm:justify-end gap-3">
                                <div class="flex items-center gap-2 bg-muted rounded-lg p-1">
                                    <Button size="sm" variant="ghost"
                                        @click="updateQuantity(item.product_id, item.quantity - 1)"
                                        :disabled="item.quantity <= 1" class="h-6 w-6 md:h-8 md:w-8 p-0 cursor-pointer">
                                        <Icon name="Minus" class="w-2 h-2 md:w-3 md:h-3" />
                                    </Button>
                                    <span class="w-8 md:w-12 text-center text-xs md:text-sm font-medium">{{
                                        item.quantity }}</span>
                                    <Button size="sm" variant="ghost"
                                        @click="updateQuantity(item.product_id, item.quantity + 1)"
                                        :disabled="item.quantity >= item.available_stock"
                                        class="h-6 w-6 md:h-8 md:w-8 p-0 cursor-pointer">
                                        <Icon name="Plus" class="w-2 h-2 md:w-3 md:h-3" />
                                    </Button>
                                </div>

                                <div class="text-right min-w-[60px] md:min-w-[80px]">
                                    <div class="text-base md:text-lg font-semibold">${{
                                        Number(item.line_total).toFixed(2) }}</div>
                                </div>

                                <AlertDialog>
                                    <AlertDialogTrigger as-child>
                                        <Button variant="ghost" size="sm"
                                            class="h-6 w-6 md:h-8 md:w-8 p-0 text-red-600 hover:text-red-700 cursor-pointer">
                                            <Icon name="Trash2" class="w-4 h-4" />
                                        </Button>
                                        <!-- <Button size="sm" variant="ghost"
                                            class="h-6 w-6 md:h-8 md:w-8 p-0 text-destructive hover:text-destructive hover:bg-destructive/10 ">
                                            <Icon name="X" class="w-3 h-3 md:w-4 md:h-4" />
                                        </Button> -->
                                    </AlertDialogTrigger>
                                    <AlertDialogContent data-remove-product-dialog :data-product-id="item.product_id">
                                        <AlertDialogHeader>
                                            <AlertDialogTitle>Remove Product?</AlertDialogTitle>
                                            <AlertDialogDescription>
                                                Are you sure you want to remove "{{ item.product_name }}" from your
                                                cart?
                                                This action cannot be undone.
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <AlertDialogCancel class="cursor-pointer">Cancel</AlertDialogCancel>
                                            <AlertDialogAction variant="destructive"
                                                class="cursor-pointer text-white bg-red-500 hover:bg-red-400 focus:shadow-red-700 inline-flex h-[35px] items-center justify-center rounded-md px-[15px] font-semibold leading-none outline-none focus:shadow-[0_0_0_2px]"
                                                @click="removeFromCart(item.product_id)">
                                                Remove Product
                                                <span class="ml-2 text-xs opacity-75">(Del)</span>
                                            </AlertDialogAction>
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                </AlertDialog>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Search Section (Optional) -->
                    <div v-if="cart.length > 0" class="border-t pt-3 md:pt-4">
                        <div class="flex gap-2">
                            <Input v-model="quickSearchTerm" placeholder="Quick add products..." class="flex-1 text-sm"
                                @keyup.enter="performQuickSearch" />
                            <Button @click="performQuickSearch" variant="outline" size="sm" class="px-2 md:px-3">
                                <Icon name="Search" class="w-3 h-3 md:w-4 md:h-4" />
                            </Button>
                        </div>

                        <!-- Quick Search Results -->
                        <div v-if="quickSearchResults.length"
                            class="mt-2 md:mt-3 space-y-1 md:space-y-2 max-h-24 md:max-h-32 overflow-y-auto">
                            <div v-for="product in quickSearchResults" :key="product.id"
                                class="flex items-center justify-between p-2 border rounded hover:bg-accent/50 cursor-pointer text-xs md:text-sm"
                                @click="addToCart(product)">
                                <div class="flex-1">
                                    <div class="font-medium">{{ product.name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ product.sku }} • ${{
                                        Number(product.price).toFixed(2) }}</div>
                                </div>
                                <Button size="sm" variant="ghost" class="h-5 w-5 md:h-6 md:w-6 p-0">
                                    <Icon name="Plus" class="w-2 h-2 md:w-3 md:h-3" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Right Panel - Customer and Checkout -->
            <div class="w-full lg:w-1/3 flex flex-col gap-4">
                <!-- Customer Selection -->
                <Card class="p-3 md:p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-3 md:mb-4 gap-2 sm:gap-0">
                        <h3 class="text-base md:text-lg font-medium">Customer</h3>
                        <Button @click="showNewCustomerDialog = true" variant="outline" size="sm"
                            class="text-xs md:text-sm">
                            <Icon name="Plus" class="w-3 h-3 md:w-4 md:h-4 mr-1 md:mr-2" />
                            <span class="hidden sm:inline">New Customer (F3)</span>
                            <span class="sm:hidden">New</span>
                        </Button>
                    </div>
                    <CustomerSelector v-model="selectedCustomer" @customer-selected="handleCustomerSelected" />
                </Card>

                <!-- Checkout Section -->
                <Card class="flex-1 p-3 md:p-4">
                    <h3 class="text-base md:text-lg font-medium mb-3 md:mb-4">Order Summary</h3>

                    <!-- Invoice Status Selection -->
                    <div class="border rounded-lg p-3 md:p-4 mb-3 md:mb-4">
                        <InvoiceStatusSelector v-model="invoiceStatus" />
                    </div>

                    <!-- Payment Method Selection -->
                    <div class="border rounded-lg p-3 md:p-4 mb-3 md:mb-4">
                        <div class="flex items-center justify-between mb-2 md:mb-3">
                            <span class="text-xs md:text-sm font-medium">Payment Method</span>
                            <div class="flex items-center gap-1 text-xs text-muted-foreground">
                                <Icon name="Keyboard" class="w-3 h-3" />
                                <span class="hidden sm:inline">F10/Alt+P cycle</span>
                                <span class="sm:hidden">F10</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <Button
                                @click="posStore.setPaymentMethod('cash')"
                                :variant="paymentMethod === 'cash' ? 'default' : 'outline'"
                                size="sm"
                                class="cursor-pointer h-9 text-xs md:text-sm relative group"
                                title="Cash - Shortcuts: F11, Ctrl+1"
                            >
                                <Icon name="Banknote" class="w-3 h-3 md:w-4 md:h-4 mr-1 md:mr-2" />
                                <span>Cash</span>
                            </Button>
                            <Button
                                @click="posStore.setPaymentMethod('card')"
                                :variant="paymentMethod === 'card' ? 'default' : 'outline'"
                                size="sm"
                                class="cursor-pointer h-9 text-xs md:text-sm relative group"
                                title="Card - Shortcuts: F12, Ctrl+2"
                            >
                                <Icon name="CreditCard" class="w-3 h-3 md:w-4 md:h-4 mr-1 md:mr-2" />
                                <span>Card</span>
                            </Button>
                            <Button
                                @click="posStore.setPaymentMethod('transfer')"
                                :variant="paymentMethod === 'transfer' ? 'default' : 'outline'"
                                size="sm"
                                class="cursor-pointer h-9 text-xs md:text-sm relative group"
                                title="Transfer - Shortcut: Ctrl+3"
                            >
                                <Icon name="ArrowRightLeft" class="w-3 h-3 md:w-4 md:h-4 mr-1 md:mr-2" />
                                <span>Transfer</span>
                            </Button>
                            <Button
                                @click="posStore.setPaymentMethod('other')"
                                :variant="paymentMethod === 'other' ? 'default' : 'outline'"
                                size="sm"
                                class="cursor-pointer h-9 text-xs md:text-sm relative group"
                                title="Other - Shortcut: Ctrl+4"
                            >
                                <Icon name="MoreHorizontal" class="w-3 h-3 md:w-4 md:h-4 mr-1 md:mr-2" />
                                <span>Other</span>
                            </Button>
                        </div>
                        <div class="mt-2 text-xs text-muted-foreground">
                            <div class="flex items-center justify-between">
                                <span>Selected: <span class="font-medium capitalize">{{ paymentMethod }}</span></span>
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="hidden md:inline">Quick access:</span>
                                    <div class="flex gap-1">
                                        <kbd class="px-1 py-0.5 bg-muted border rounded text-xs">Ctrl+1-4</kbd>
                                        <kbd class="px-1 py-0.5 bg-muted border rounded text-xs hidden sm:inline">Alt+P</kbd>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Discount Section -->
                    <div class="border rounded-lg p-3 md:p-4 mb-3 md:mb-4">
                        <div class="flex items-center justify-between mb-2 md:mb-3">
                            <span class="text-xs md:text-sm font-medium">Apply Discount</span>
                            <div class="flex items-center gap-2">
                                <div v-if="!discountType" class="flex items-center gap-1 text-xs text-muted-foreground">
                                    <Icon name="Keyboard" class="w-3 h-3" />
                                    <span class="hidden sm:inline">F6/F7</span>
                                </div>
                                <Button v-if="discountType" @click="clearDiscount" variant="ghost" size="sm"
                                    class="cursor-pointer h-6 w-6 md:h-8 md:w-8 p-0">
                                    <Icon name="X" class="w-3 h-3 md:w-4 md:h-4" />
                                </Button>
                            </div>
                        </div>

                        <div v-if="!discountType" class="flex flex-col sm:flex-row gap-2">
                            <Button @click="showDiscountDialog('percentage')" variant="outline" size="sm"
                                class="flex-1 cursor-pointer relative group"
                                title="Percentage Discount - Shortcut: F6">
                                <Icon name="Percent" class="w-3 h-3 md:w-4 md:h-4 mr-1 md:mr-2" />
                                <span>Percentage</span>
                                <div class="absolute -top-1 -right-1 bg-primary/10 text-primary text-xs px-1 rounded border border-primary/20">
                                    F6
                                </div>
                            </Button>
                            <Button @click="showDiscountDialog('fixed')" variant="outline" size="sm"
                                class="flex-1 cursor-pointer relative group"
                                title="Fixed Amount Discount - Shortcut: F7">
                                <Icon name="DollarSign" class="w-3 h-3 md:w-4 md:h-4 mr-1 md:mr-2" />
                                <span>Fixed Amount</span>
                                <div class="absolute -top-1 -right-1 bg-primary/10 text-primary text-xs px-1 rounded border border-primary/20">
                                    F7
                                </div>
                            </Button>
                        </div>

                        <div v-else class="bg-green-50 dark:bg-green-900/20 p-2 md:p-3 rounded-lg">
                            <div class="flex justify-between items-center">
                                <span class="text-xs md:text-sm font-medium text-green-800 dark:text-green-400">
                                    {{ discountType === 'percentage' ? `${Number(discountValue)}%` :
                                        `$${Number(discountValue).toFixed(2)}` }} Discount Applied
                                </span>
                                <span class="text-xs md:text-sm font-semibold text-green-800 dark:text-green-400">
                                    -${{ Number(discountAmount).toFixed(2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="space-y-2 md:space-y-3 border rounded-lg p-3 md:p-4 mb-4 md:mb-6">
                        <div class="flex justify-between text-xs md:text-sm">
                            <span>Subtotal:</span>
                            <span class="font-medium">${{ Number(subtotal).toFixed(2) }}</span>
                        </div>
                        <div v-if="discountAmount > 0"
                            class="flex justify-between text-xs md:text-sm text-green-600 dark:text-green-400">
                            <span>Discount:</span>
                            <span class="font-medium">-${{ Number(discountAmount).toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between text-lg md:text-xl font-bold border-t pt-2 md:pt-3">
                            <span>Total:</span>
                            <span class="text-primary">${{ Number(total).toFixed(2) }}</span>
                        </div>
                    </div>

                    <!-- Checkout Button -->
                    <Button @click="processCheckout" class="w-full h-10 md:h-12 cursor-pointer text-sm md:text-base"
                        size="lg" :disabled="!canProcessSale" :loading="isProcessingSale">
                        <Icon :name="getCheckoutIcon" class="w-4 h-4 md:w-5 md:h-5 mr-1 md:mr-2" />
                        <span class="hidden sm:inline">{{ isProcessingSale ? getProcessingText : getCheckoutText
                        }}</span>
                        <span class="sm:hidden">{{ isProcessingSale ? 'Processing...' : getCheckoutButtonShort }}</span>
                        <span v-if="!isProcessingSale" class="ml-2 text-xs opacity-75 hidden md:inline">(F4)</span>
                    </Button>

                    <!-- Additional Info -->
                    <div v-if="cart.length > 0" class="mt-3 md:mt-4 text-xs text-muted-foreground text-center">
                        <p class="hidden md:block">{{ itemCount }} item{{ itemCount !== 1 ? 's' : '' }} • Last updated:
                            {{ new
                                Date().toLocaleTimeString() }}</p>
                        <p class="md:hidden">{{ itemCount }} item{{ itemCount !== 1 ? 's' : '' }}</p>
                    </div>
                </Card>
            </div>
        </div>

        <!-- Product Selection Modal -->
        <ProductSelectionModal v-model:open="showProductModal" @product-selected="addToCart" />

        <!-- New Customer Dialog -->
        <NewCustomerDialog v-model:open="showNewCustomerDialog" @customer-created="handleCustomerCreated" />

        <!-- Discount Dialog -->
        <DiscountDialog v-model:open="showDiscountDialogRef" v-model:type="discountDialogType"
            @discount-applied="handleDiscountApplied" />

        <!-- Sale Success Dialog -->
        <SaleSuccessDialog v-model:open="showSaleSuccessDialog" :sale="lastSale" :is-printing="isPrintingInvoice" @print-invoice="printInvoice" />

        <!-- Payment Confirmation Dialog -->
        <AlertDialog v-model:open="showPaymentConfirmDialog">
            <AlertDialogContent class="max-w-md md:max-w-lg">
                <AlertDialogHeader>
                    <AlertDialogTitle>Confirm Invoice</AlertDialogTitle>
                    <AlertDialogDescription>
                        Please review the order details before processing the invoice. Press F4 again to print after processing.
                    </AlertDialogDescription>
                </AlertDialogHeader>

                <div class="py-4">
                    <!-- Customer Info -->
                    <div v-if="selectedCustomer" class="mb-4 p-3 bg-muted/50 rounded-lg">
                        <h4 class="font-medium text-sm mb-1">Customer:</h4>
                        <p class="text-sm">{{ selectedCustomer.full_name }}</p>
                        <p class="text-xs text-muted-foreground">{{ selectedCustomer.email }}</p>
                    </div>

                    <!-- Payment Method & Invoice Status -->
                    <div class="mb-4 p-3 bg-muted/50 rounded-lg">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <h4 class="font-medium text-sm mb-1">Payment Method:</h4>
                                <div class="flex items-center gap-1">
                                    <Icon :name="getPaymentMethodIcon(paymentMethod)" class="w-4 h-4" />
                                    <span class="text-sm capitalize">{{ paymentMethod }}</span>
                                </div>
                            </div>
                            <div>
                                <h4 class="font-medium text-sm mb-1">Invoice Status:</h4>
                                <span class="text-sm capitalize">{{ invoiceStatus }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Cart Items -->
                    <div class="space-y-2 mb-4 max-h-48 overflow-y-auto">
                        <h4 class="font-medium text-sm mb-2">Items ({{ itemCount }}):</h4>
                        <div v-for="item in cart" :key="item.product_id"
                            class="flex justify-between items-center text-sm p-2 bg-background rounded border">
                            <div class="flex-1 min-w-0">
                                <div class="font-medium truncate">{{ item.product_name }}</div>
                                <div class="text-xs text-muted-foreground">{{ item.product_sku }}</div>
                            </div>
                            <div class="text-right ml-2">
                                <div class="font-medium">{{ item.quantity }}x ${{ Number(item.unit_price).toFixed(2) }}
                                </div>
                                <div class="text-xs text-muted-foreground">${{ Number(item.line_total).toFixed(2) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="border-t pt-3">
                        <div class="flex justify-between text-sm mb-1">
                            <span>Subtotal:</span>
                            <span>${{ Number(subtotal).toFixed(2) }}</span>
                        </div>
                        <div v-if="discountAmount > 0" class="flex justify-between text-sm text-green-600 mb-1">
                            <span>Discount:</span>
                            <span>-${{ Number(discountAmount).toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between font-bold text-lg border-t pt-2">
                            <span>Total:</span>
                            <span class="text-primary">${{ Number(total).toFixed(2) }}</span>
                        </div>
                    </div>
                </div>

                <AlertDialogFooter>
                    <AlertDialogCancel class="cursor-pointer">Cancel</AlertDialogCancel>
                    <AlertDialogAction class="cursor-pointer bg-primary hover:bg-primary/90 text-primary-foreground"
                        @click="confirmPayment">
                        <Icon :name="getCheckoutIcon" class="w-4 h-4 mr-2" />
                        {{ getCheckoutText }}
                        <span class="ml-2 text-xs opacity-75">(Enter/F4)</span>
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <!-- Remove Item Confirmation Dialog -->
        <AlertDialog v-model:open="showRemoveItemDialog">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Remove Product?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to remove "{{ itemToRemove?.name }}" from your cart?
                        This action cannot be undone.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel class="cursor-pointer" @click="cancelRemoveItem">Cancel</AlertDialogCancel>
                    <AlertDialogAction variant="destructive"
                        class="cursor-pointer text-white bg-red-500 hover:bg-red-400 focus:shadow-red-700 inline-flex h-[35px] items-center justify-center rounded-md px-[15px] font-semibold leading-none outline-none focus:shadow-[0_0_0_2px]"
                        @click="confirmRemoveItem">
                        Remove Product
                        <span class="ml-2 text-xs opacity-75">(Del)</span>
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { storeToRefs } from 'pinia'
import { usePOSStore } from '../../stores/pos'
import { useProductStore } from '../../stores/products'
import type { Product, Customer } from '../../types/pos'
import AppLayout from '../../layouts/AppLayout.vue'
import { route } from 'ziggy-js'
import { Card } from '../../components/ui/card'
import { Button } from '../../components/ui/button'
import { Input } from '../../components/ui/input'
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '../../components/ui/alert-dialog'
import Icon from '../../components/Icon.vue'
// Make sure the file exists at this path, or update the path if needed
import CustomerSelector from './components/CustomerSelector.vue'
import ProductSelectionModal from './components/ProductSelectionModal.vue'
import NewCustomerDialog from './components/NewCustomerDialog.vue'
import DiscountDialog from './components/DiscountDialog.vue'
import SaleSuccessDialog from './components/SaleSuccessDialog.vue'
import InvoiceStatusSelector from './components/InvoiceStatusSelector.vue'
import { toast } from 'vue-sonner'
import SessionStatus from './components/SessionStatus.vue'

// Stores
const posStore = usePOSStore()
const productStore = useProductStore()

// Store refs
const {
    cart,
    selectedCustomer,
    discountType,
    discountValue,
    discountAmount,
    invoiceStatus,
    paymentMethod,
    subtotal,
    total,
    itemCount,
    canProcessSale,
    isProcessingSale,
    lastSale
} = storeToRefs(posStore)

// Local state
const showProductModal = ref(false)
const showNewCustomerDialog = ref(false)
const showDiscountDialogRef = ref(false)
const discountDialogType = ref<'percentage' | 'fixed'>('percentage')
const showSaleSuccessDialog = ref(false)
const quickSearchTerm = ref('')
const quickSearchResults = ref<Product[]>([])
const showPaymentConfirmDialog = ref(false)
const showRemoveItemDialog = ref(false)
const itemToRemove = ref<{ id: number, name: string } | null>(null)
const isPrintingInvoice = ref(false)

// States for cart navigation
const selectedCartItemIndex = ref(-1)
const isCartNavigationActive = ref(false)

// Refs for the search input in the product and customer modals
const productSearchInputRef = ref<HTMLInputElement | null>(null)
const customerSearchInputRef = ref<HTMLInputElement | null>(null)

// Auto-refresh interval for stock updates
let stockUpdateInterval: number | null = null

// Computed properties for checkout button
const getCheckoutIcon = computed(() => {
    switch (invoiceStatus.value) {
        case 'paid':
            return 'CreditCard'
        case 'pending':
            return 'Clock'
        default:
            return 'CreditCard'
    }
})

const getPaymentMethodIcon = (method: string) => {
    switch (method) {
        case 'cash':
            return 'Banknote'
        case 'card':
            return 'CreditCard'
        case 'transfer':
            return 'ArrowRightLeft'
        case 'other':
            return 'MoreHorizontal'
        default:
            return 'CreditCard'
    }
}

const getCheckoutText = computed(() => {
    switch (invoiceStatus.value) {
        case 'paid':
            return 'Process Payment'
        case 'pending':
            return 'Create Pending Invoice'
        default:
            return 'Process Sale'
    }
})

const getProcessingText = computed(() => {
    switch (invoiceStatus.value) {
        case 'paid':
            return 'Processing Payment...'
        case 'pending':
            return 'Creating Invoice...'
        default:
            return 'Processing Sale...'
    }
})

const getCheckoutButtonShort = computed(() => {
    switch (invoiceStatus.value) {
        case 'paid':
            return 'Pay'
        case 'pending':
            return 'Pending'
        default:
            return 'Pay'
    }
})

// Methods
const handleCustomerSelected = (customer: Customer | null) => {
    posStore.setCustomer(customer)
}

const handleCustomerCreated = (customer: Customer) => {
    posStore.setCustomer(customer)
    toast.success('Customer created successfully')
}

const addToCart = (product: Product, quantity: number = 1) => {
    try {
        posStore.addToCart(product, quantity)
        toast.success(`${product.name} added to cart`)
    } catch (error) {
        toast.error(error instanceof Error ? error.message : 'Error adding to cart')
    }
}

const updateQuantity = (productId: number, quantity: number) => {
    try {
        posStore.updateCartItemQuantity(productId, quantity)
    } catch (error) {
        toast.error(error instanceof Error ? error.message : 'Error updating quantity')
    }
}

const removeFromCart = (productId: number) => {
    posStore.removeFromCart(productId)
    toast.success('Item removed from cart')
}

const clearCart = () => {
    posStore.clearCart()
    toast.success('Cart cleared')
}

const showDiscountDialog = (type: 'percentage' | 'fixed') => {
    discountDialogType.value = type
    showDiscountDialogRef.value = true
    // Focus the dialog input after a short delay
    nextTick(() => {
        setTimeout(() => {
            const input = document.querySelector('.discount-dialog input[type="number"], .discount-modal input[type="number"]') as HTMLInputElement | null
            if (input) {
                input.focus()
                input.select()
            }
        }, 100)
    })
}

const handleDiscountApplied = (type: 'percentage' | 'fixed', value: number) => {
    posStore.setDiscount(type, value)
    toast.success('Discount applied')
}

const clearDiscount = () => {
    posStore.clearDiscount()
    toast.success('Discount removed')
}

const toggleInvoiceStatus = () => {
    // Toggle between 'paid' and 'pending'
    const newStatus = invoiceStatus.value === 'paid' ? 'pending' : 'paid'
    posStore.setInvoiceStatus(newStatus)

    const statusText = newStatus === 'paid' ? 'Paid' : 'Pending Payment'
    toast.success(`Invoice status switched to: ${statusText}`)
}

// Print functionality
const detectPrinters = async (): Promise<boolean> => {
    try {
        // Check if we're in a desktop environment where printing might be available
        const isDesktop = !(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent))

        if (isDesktop && typeof window.print === 'function') {
            return true
        }
        return false
    } catch (error) {
        console.error('Error detecting printers:', error)
        return false
    }
}

const printInvoice = async (invoiceId: number) => {
    if (isPrintingInvoice.value) return

    isPrintingInvoice.value = true

    try {
        // Use the more reliable method that opens PDF in new tab
        await printInvoiceWithOptions(invoiceId)
    } catch (error) {
        console.error('Error printing invoice:', error)
        toast.error('Error printing invoice, downloading PDF instead...')
        await downloadInvoicePDF(invoiceId)
    } finally {
        isPrintingInvoice.value = false
    }
}

const printInvoiceDirectly = async (invoiceId: number) => {
    try {
        // Strategy 1: Try to open PDF in new window and trigger print
        const printWindow = window.open(route('invoice.pdf', invoiceId), '_blank', 'width=800,height=600')

        if (printWindow) {
            // Wait for the PDF to load in the new window
            await new Promise((resolve, reject) => {
                const checkLoad = () => {
                    try {
                        // Check if window is ready
                        if (printWindow.document.readyState === 'complete') {
                            // Give a moment for PDF to render
                            setTimeout(() => {
                                try {
                                    printWindow.print()
                                    toast.success('Print dialog opened')
                                    resolve(true)
                                } catch (printError) {
                                    // If print fails, keep window open for manual printing
                                    toast.info('PDF opened in new window - use Ctrl+P to print')
                                    resolve(true)
                                }
                            }, 1500)
                        } else {
                            // Keep checking
                            setTimeout(checkLoad, 100)
                        }
                    } catch (error) {
                        // If we can't access the window (cross-origin), that's normal for PDFs
                        // Just wait a bit and try to print
                        setTimeout(() => {
                            try {
                                printWindow.print()
                                toast.success('Print dialog opened')
                                resolve(true)
                            } catch (printError) {
                                toast.info('PDF opened in new window - use Ctrl+P to print')
                                resolve(true)
                            }
                        }, 2000)
                    }
                }

                // Start checking
                setTimeout(checkLoad, 500)

                // Timeout after 10 seconds
                setTimeout(() => {
                    toast.info('PDF opened in new window - use Ctrl+P to print')
                    resolve(true)
                }, 10000)
            })
        } else {
            // Popup blocked, fallback to download
            throw new Error('Popup blocked')
        }

    } catch (error) {
        console.error('Error printing directly:', error)
        throw error
    }
}

const downloadInvoicePDF = async (invoiceId: number) => {
    try {
        // Create a temporary link to download the PDF
        const link = document.createElement('a')
        link.href = route('invoice.pdf', invoiceId)
        link.download = `invoice-${invoiceId}.pdf`
        link.target = '_blank'

        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)

        toast.success('Downloading invoice PDF...')
    } catch (error) {
        console.error('Error downloading PDF:', error)
        toast.error('Error downloading invoice PDF')
    }
}

// Alternative print method that gives user more control
const printInvoiceWithOptions = async (invoiceId: number) => {
    try {
        // Open PDF in new tab and show instructions
        const pdfUrl = route('invoice.pdf', invoiceId)
        const printWindow = window.open(pdfUrl, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes')

        if (printWindow) {
            // Show success message with instructions
            toast.success('PDF opened in new tab. Use Ctrl+P to print when ready.', {
                duration: 5000,
                action: {
                    label: 'Download instead',
                    onClick: () => downloadInvoicePDF(invoiceId)
                }
            })
        } else {
            // Popup was blocked, fallback to download
            toast.warning('Popup blocked. Downloading PDF instead...')
            await downloadInvoicePDF(invoiceId)
        }
    } catch (error) {
        console.error('Error opening PDF:', error)
        toast.error('Error opening PDF, downloading instead...')
        await downloadInvoicePDF(invoiceId)
    }
}

const handlePrintFromConfirmDialog = async () => {
    if (lastSale.value?.id) {
        await printInvoice(lastSale.value.id)
    }
}

// Cart navigation
const activateCartNavigation = () => {
    if (cart.value.length > 0) {
        isCartNavigationActive.value = true
        selectedCartItemIndex.value = 0
        scrollToCartItem(0)
    }
}

const deactivateCartNavigation = () => {
    isCartNavigationActive.value = false
    selectedCartItemIndex.value = -1
}

const navigateCartItem = (direction: 'up' | 'down') => {
    if (!isCartNavigationActive.value || cart.value.length === 0) return

    if (direction === 'down') {
        selectedCartItemIndex.value = Math.min(selectedCartItemIndex.value + 1, cart.value.length - 1)
    } else {
        selectedCartItemIndex.value = Math.max(selectedCartItemIndex.value - 1, 0)
    }

    scrollToCartItem(selectedCartItemIndex.value)
}

const scrollToCartItem = (index: number) => {
    nextTick(() => {
        const cartItem = document.querySelector(`[data-cart-item-index="${index}"]`)
        if (cartItem) {
            cartItem.scrollIntoView({ block: 'nearest', behavior: 'smooth' })
        }
    })
}

const incrementSelectedItemQuantity = () => {
    if (selectedCartItemIndex.value >= 0 && selectedCartItemIndex.value < cart.value.length) {
        const item = cart.value[selectedCartItemIndex.value]
        updateQuantity(item.product_id, item.quantity + 1)
    }
}

const decrementSelectedItemQuantity = () => {
    if (selectedCartItemIndex.value >= 0 && selectedCartItemIndex.value < cart.value.length) {
        const item = cart.value[selectedCartItemIndex.value]
        if (item.quantity > 1) {
            updateQuantity(item.product_id, item.quantity - 1)
        }
    }
}

const removeSelectedCartItem = () => {
    if (selectedCartItemIndex.value >= 0 && selectedCartItemIndex.value < cart.value.length) {
        const item = cart.value[selectedCartItemIndex.value]
        itemToRemove.value = { id: item.product_id, name: item.product_name }
        showRemoveItemDialog.value = true
    }
}

const confirmRemoveItem = () => {
    if (itemToRemove.value) {
        removeFromCart(itemToRemove.value.id)

        // Ajustar el índice seleccionado después de eliminar
        if (cart.value.length === 0) {
            deactivateCartNavigation()
        } else if (selectedCartItemIndex.value >= cart.value.length) {
            selectedCartItemIndex.value = cart.value.length - 1
        }

        showRemoveItemDialog.value = false
        itemToRemove.value = null
    }
}

const cancelRemoveItem = () => {
    showRemoveItemDialog.value = false
    itemToRemove.value = null
}

const processCheckout = async () => {
    // Always show confirmation regardless of invoice type
    showPaymentConfirmDialog.value = true
}

const executeCheckout = async () => {
    try {
        await posStore.processSale()
        showSaleSuccessDialog.value = true
        toast.success('Sale processed successfully!')
    } catch (error) {
        toast.error(error instanceof Error ? error.message : 'Error processing sale')
    }
}

const confirmPayment = async () => {
    showPaymentConfirmDialog.value = false
    await executeCheckout()
}

const performQuickSearch = async () => {
    if (!quickSearchTerm.value.trim()) {
        quickSearchResults.value = []
        return
    }

    try {
        await productStore.searchProducts(quickSearchTerm.value)
        quickSearchResults.value = productStore.products.slice(0, 5) // Show top 5 results
    } catch {
        toast.error('Error searching products')
    }
}

const updateStockPrices = async () => {
    if (cart.value.length === 0) return

    try {
        const productIds = cart.value.map((item: any) => item.product_id)
        await productStore.refreshProductUpdates(productIds)

        // Update cart with new stock info
        const updates: Record<number, { stock: number, price: number }> = {}
        productIds.forEach((id: number) => {
            const product = productStore.getProductById(id)
            if (product) {
                updates[id] = { stock: product.stock, price: product.price }
            }
        })

        posStore.updateProductStock(updates)
    } catch {
        console.error('Error updating stock prices')
    }
}

// Lifecycle
onMounted(() => {
    // Set up periodic stock updates every 30 seconds
    stockUpdateInterval = setInterval(updateStockPrices, 30000)

    // Global keyboard shortcuts
    const handleKeyDown = async (e: KeyboardEvent) => {
        // F9: Toggle cart navigation
        if (e.key === 'F9') {
            e.preventDefault()
            if (isCartNavigationActive.value) {
                deactivateCartNavigation()
                toast.info('Cart navigation deactivated')
            } else {
                activateCartNavigation()
                toast.info('Cart navigation activated - Use ↑↓ to navigate, +/- to change quantity')
            }
            return
        }

        // If cart navigation is active, handle special keys
        if (isCartNavigationActive.value) {
            switch (e.key) {
                case 'ArrowUp':
                    e.preventDefault()
                    navigateCartItem('up')
                    return
                case 'ArrowDown':
                    e.preventDefault()
                    navigateCartItem('down')
                    return
                case '+':
                case '=':
                    e.preventDefault()
                    incrementSelectedItemQuantity()
                    return
                case '-':
                    e.preventDefault()
                    decrementSelectedItemQuantity()
                    return
                case 'Delete':
                case 'Backspace':
                    e.preventDefault()
                    removeSelectedCartItem()
                    return
                case 'Escape':
                    e.preventDefault()
                    deactivateCartNavigation()
                    toast.info('Cart navigation deactivated')
                    return
            }
        }

        // F2: Open product modal and focus on search engine
        if (e.key === 'F2') {
            e.preventDefault()
            showProductModal.value = true
            await nextTick()
            // Try to focus the search input if it exists
            if (productSearchInputRef.value) {
                productSearchInputRef.value.focus()
            } else {
                // fallback: search input within the modal
                setTimeout(() => {
                    const el = document.querySelector(
                        '[data-product-search-input], .product-search input, .product-modal input[type="search"]'
                    ) as HTMLInputElement | null
                    if (el) el.focus()
                }, 100)
            }
        }
        // F3: Open customer modal and focus on search engine
        if (e.key === 'F3') {
            e.preventDefault()
            showNewCustomerDialog.value = true
            await nextTick()
            if (customerSearchInputRef.value) {
                customerSearchInputRef.value.focus()
            } else {
                setTimeout(() => {
                    const el = document.querySelector(
                        '[data-customer-search-input], .customer-search input, .customer-modal input[type="search"]'
                    ) as HTMLInputElement | null
                    if (el) el.focus()
                }, 100)
            }
        }
        // Handle Enter key in modal dialogs
        if (e.key === 'Enter') {
            // If payment confirmation dialog is open
            if (showPaymentConfirmDialog.value) {
                e.preventDefault()
                await confirmPayment()
                return
            }

            // If remove item dialog is open
            if (showRemoveItemDialog.value) {
                e.preventDefault()
                confirmRemoveItem()
                return
            }
        }

        // Handle Escape key in modal dialogs
        if (e.key === 'Escape') {
            // If payment confirmation dialog is open
            if (showPaymentConfirmDialog.value) {
                e.preventDefault()
                showPaymentConfirmDialog.value = false
                return
            }

            // If remove item dialog is open
            if (showRemoveItemDialog.value) {
                e.preventDefault()
                cancelRemoveItem()
                return
            }
        }

        // F4: Process invoice, confirm payment, or print invoice
        if (e.key === 'F4') {
            e.preventDefault()

            // If there's a sale success dialog open, print the invoice
            if (showSaleSuccessDialog.value && lastSale.value?.id) {
                await printInvoice(lastSale.value.id)
                return
            }

            // If the confirmation modal is open, confirm the payment
            if (showPaymentConfirmDialog.value) {
                await confirmPayment()
                return
            }

            // If no modal is open and it can be processed, process checkout
            if (canProcessSale.value && !isProcessingSale.value) {
                await processCheckout()
                return
            }
        }
        // F5: Focus customer search (CustomerSelector)
        if (e.key === 'F5') {
            e.preventDefault()
            // Search for the customer selector input
            setTimeout(() => {
                const el = document.querySelector(
                    '[data-customer-selector-input], .customer-selector input, input[placeholder*="customer"], input[placeholder*="Search customer"]'
                ) as HTMLInputElement | null
                if (el) {
                    el.focus()
                    el.click() // Also click to open the dropdown if necessary
                }
            }, 50)
        }
        // F6: Open percentage discount dialog
        if (e.key === 'F6') {
            e.preventDefault()
            showDiscountDialog('percentage')
        }
        // F7: Open fixed discount dialog
        if (e.key === 'F7') {
            e.preventDefault()
            showDiscountDialog('fixed')
        }
        // F8: Toggle invoice status
        if (e.key === 'F8') {
            e.preventDefault()
            toggleInvoiceStatus()
        }

        // F10: Cycle through payment methods
        if (e.key === 'F10') {
            e.preventDefault()
            const methods: Array<'cash' | 'card' | 'transfer' | 'other'> = ['cash', 'card', 'transfer', 'other']
            const currentIndex = methods.indexOf(paymentMethod.value)
            const nextIndex = (currentIndex + 1) % methods.length
            posStore.setPaymentMethod(methods[nextIndex])
            toast.success(`Payment method: ${methods[nextIndex].charAt(0).toUpperCase() + methods[nextIndex].slice(1)}`)
        }

        // F11: Set payment method to cash
        if (e.key === 'F11') {
            e.preventDefault()
            posStore.setPaymentMethod('cash')
            toast.success('Payment method: Cash')
        }

        // F12: Set payment method to card
        if (e.key === 'F12') {
            e.preventDefault()
            posStore.setPaymentMethod('card')
            toast.success('Payment method: Card')
        }

        // Additional shortcuts for payment methods (more intuitive)
        // Ctrl+1: Cash
        if (e.ctrlKey && e.key === '1') {
            e.preventDefault()
            posStore.setPaymentMethod('cash')
            toast.success('Payment method: Cash (Ctrl+1)')
        }

        // Ctrl+2: Card
        if (e.ctrlKey && e.key === '2') {
            e.preventDefault()
            posStore.setPaymentMethod('card')
            toast.success('Payment method: Card (Ctrl+2)')
        }

        // Ctrl+3: Transfer
        if (e.ctrlKey && e.key === '3') {
            e.preventDefault()
            posStore.setPaymentMethod('transfer')
            toast.success('Payment method: Transfer (Ctrl+3)')
        }

        // Ctrl+4: Other
        if (e.ctrlKey && e.key === '4') {
            e.preventDefault()
            posStore.setPaymentMethod('other')
            toast.success('Payment method: Other (Ctrl+4)')
        }

        // Alt+P: Cycle through payment methods (alternative)
        if (e.altKey && e.key === 'p') {
            e.preventDefault()
            const methods: Array<'cash' | 'card' | 'transfer' | 'other'> = ['cash', 'card', 'transfer', 'other']
            const currentIndex = methods.indexOf(paymentMethod.value)
            const nextIndex = (currentIndex + 1) % methods.length
            posStore.setPaymentMethod(methods[nextIndex])
            toast.success(`Payment method: ${methods[nextIndex].charAt(0).toUpperCase() + methods[nextIndex].slice(1)} (Alt+P)`)
        }

        // Handle Delete/Backspace in confirmation dialogs
        if (e.key === 'Delete' || e.key === 'Backspace') {
            // If the clear cart dialog is open
            const clearCartDialog = document.querySelector('[data-clear-cart-dialog]')
            if (clearCartDialog && clearCartDialog.closest('[data-state="open"]')) {
                e.preventDefault()
                clearCart()
                toast.info('Cart cleared via keyboard shortcut')
                return
            }

            // If the remove item dialog is open
            if (showRemoveItemDialog.value) {
                e.preventDefault()
                confirmRemoveItem()
                toast.info('Item removed via keyboard shortcut')
                return
            }

            // If any individual product removal dialog is open
            const removeProductDialog = document.querySelector('[data-remove-product-dialog]')
            if (removeProductDialog && removeProductDialog.closest('[data-state="open"]')) {
                e.preventDefault()
                const productId = removeProductDialog.getAttribute('data-product-id')
                if (productId) {
                    removeFromCart(parseInt(productId))
                    toast.info('Product removed via keyboard shortcut')
                }
                return
            }
        }
    }
    window.addEventListener('keydown', handleKeyDown)

    // Additional listener specific to modal dialogs
    const handleDialogKeyDown = (e: KeyboardEvent) => {
        if (e.key === 'Delete' || e.key === 'Backspace') {
            // Check if any removal dialog is visible
            const clearCartDialog = document.querySelector('[data-clear-cart-dialog]')
            const removeProductDialog = document.querySelector('[data-remove-product-dialog]')

            if (clearCartDialog && window.getComputedStyle(clearCartDialog.parentElement as Element).display !== 'none') {
                e.preventDefault()
                e.stopPropagation()
                clearCart()
                return
            }

            if (showRemoveItemDialog.value) {
                e.preventDefault()
                e.stopPropagation()
                confirmRemoveItem()
                return
            }

            if (removeProductDialog && window.getComputedStyle(removeProductDialog.parentElement as Element).display !== 'none') {
                e.preventDefault()
                e.stopPropagation()
                const productId = removeProductDialog.getAttribute('data-product-id')
                if (productId) {
                    removeFromCart(parseInt(productId))
                }
                return
            }
        }
    }

    // Add listener with capture to intercept before the modal
    document.addEventListener('keydown', handleDialogKeyDown, true)

    // Cleanup
    onUnmounted(() => {
        if (stockUpdateInterval) {
            clearInterval(stockUpdateInterval)
        }
        window.removeEventListener('keydown', handleKeyDown)
        document.removeEventListener('keydown', handleDialogKeyDown, true)
    })
})

// Clear quick search when search term is empty
watch(quickSearchTerm, (newValue) => {
    if (!newValue.trim()) {
        quickSearchResults.value = []
    }
})

// Deactivate cart navigation when empty
watch(cart, (newCart) => {
    if (newCart.length === 0 && isCartNavigationActive.value) {
        deactivateCartNavigation()
    }
    // Adjust index if necessary
    if (isCartNavigationActive.value && selectedCartItemIndex.value >= newCart.length) {
        selectedCartItemIndex.value = Math.max(0, newCart.length - 1)
    }
})
</script>
