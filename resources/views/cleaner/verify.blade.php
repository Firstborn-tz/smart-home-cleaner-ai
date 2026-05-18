<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verification - SmartClean AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4" x-data="verificationSystem()">

    <div class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-shield-alt text-white text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Service Verification</h2>
            <p class="text-gray-500 mt-1">Enter the 6-digit code from the homeowner</p>
        </div>

        <!-- Booking Info -->
        <div class="bg-blue-50 rounded-2xl p-4 mb-6">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Booking</span>
                <span class="font-mono font-bold" x-text="bookingNumber"></span>
            </div>
            <div class="flex justify-between text-sm mt-1">
                <span class="text-gray-600">Status</span>
                <span class="font-semibold text-blue-700" x-text="bookingStatus"></span>
            </div>
        </div>

        <!-- Code Input -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Verification Code</label>
            <div class="flex justify-center space-x-3 mb-4">
                <template x-for="(digit, index) in codeDigits" :key="index">
                    <input type="text" 
                           :id="'digit-' + index"
                           maxlength="1"
                           x-model="codeDigits[index]"
                           @input="handleDigitInput($event, index)"
                           @keydown.backspace="handleBackspace($event, index)"
                           @paste="handlePaste($event)"
                           class="w-12 h-16 text-center text-2xl font-bold rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all"
                           :class="codeDigits[index] ? 'border-blue-300 bg-blue-50' : ''">
                </template>
            </div>
        </div>

        <!-- Attempts Info -->
        <div class="bg-yellow-50 rounded-xl p-3 mb-6 text-center" x-show="remainingAttempts < 3">
            <p class="text-sm text-yellow-700">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                <span x-text="remainingAttempts"></span> attempt(s) remaining
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-3">
            <button @click="verifyCode()" 
                    :disabled="!isCodeComplete() || submitting"
                    class="w-full px-6 py-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-2xl font-bold text-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="!submitting">Verify & Complete Service</span>
                <span x-show="submitting"><i class="fas fa-spinner fa-spin mr-2"></i> Verifying...</span>
            </button>
            
            <button @click="generateNewCode()" 
                    :disabled="!canRegenerate || generating"
                    class="w-full px-6 py-4 border-2 border-gray-200 rounded-2xl font-medium text-gray-600 hover:bg-gray-50 transition-all disabled:opacity-50">
                <span x-show="!generating">Generate New Code</span>
                <span x-show="generating"><i class="fas fa-spinner fa-spin mr-2"></i> Generating...</span>
            </button>
        </div>

        <!-- Generated Code Display -->
        <div x-show="generatedCode" class="mt-6 p-4 bg-green-50 rounded-2xl text-center">
            <p class="text-sm text-green-700 mb-2">New verification code generated:</p>
            <p class="text-4xl font-bold text-green-700 tracking-widest font-mono" x-text="generatedCode"></p>
            <p class="text-xs text-green-500 mt-2">Share this code with the homeowner</p>
            <p class="text-xs text-gray-500 mt-1">Code expires in 30 minutes</p>
        </div>

        <!-- Success Message -->
        <div x-show="completed" class="mt-6 p-6 bg-green-50 rounded-2xl text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-check-circle text-green-500 text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-green-700">Service Completed!</h3>
            <p class="text-sm text-green-600 mt-1">The booking has been marked as complete</p>
            <a href="/cleaner/dashboard" class="inline-block mt-4 px-6 py-2 bg-green-500 text-white rounded-xl hover:bg-green-600">
                Back to Dashboard
            </a>
        </div>

        <!-- Error Message -->
        <div x-show="errorMessage" class="mt-4 p-4 bg-red-50 rounded-xl text-center">
            <p class="text-sm text-red-700" x-text="errorMessage"></p>
        </div>
    </div>

    <script>
        function verificationSystem() {
            return {
                bookingId: '{{ $booking->id ?? "" }}',
                bookingNumber: '{{ $booking->booking_number ?? "N/A" }}',
                bookingStatus: '{{ $booking->status ?? "N/A" }}',
                codeDigits: ['', '', '', '', '', ''],
                remainingAttempts: 3,
                canRegenerate: true,
                generatedCode: '',
                submitting: false,
                generating: false,
                completed: false,
                errorMessage: '',

                isCodeComplete() {
                    return this.codeDigits.every(d => d !== '');
                },

                getCode() {
                    return this.codeDigits.join('');
                },

                handleDigitInput(event, index) {
                    const value = event.target.value.replace(/[^0-9]/g, '');
                    this.codeDigits[index] = value.slice(-1);
                    
                    if (value && index < 5) {
                        document.getElementById('digit-' + (index + 1))?.focus();
                    }
                },

                handleBackspace(event, index) {
                    if (!this.codeDigits[index] && index > 0) {
                        document.getElementById('digit-' + (index - 1))?.focus();
                    }
                },

                handlePaste(event) {
                    event.preventDefault();
                    const paste = (event.clipboardData || window.clipboardData).getData('text');
                    const digits = paste.replace(/[^0-9]/g, '').slice(0, 6).split('');
                    
                    digits.forEach((digit, i) => {
                        if (i < 6) this.codeDigits[i] = digit;
                    });
                    
                    // Fill remaining with empty
                    for (let i = digits.length; i < 6; i++) {
                        this.codeDigits[i] = '';
                    }
                    
                    if (digits.length === 6) {
                        this.verifyCode();
                    }
                },

                async verifyCode() {
                    this.submitting = true;
                    this.errorMessage = '';

                    try {
                        const res = await fetch(`/cleaner/bookings/${this.bookingId}/verify`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ code: this.getCode() })
                        });

                        const data = await res.json();

                        if (data.success) {
                            this.completed = true;
                        } else {
                            this.errorMessage = data.message;
                            this.remainingAttempts = data.remaining_attempts || 0;
                            this.canRegenerate = data.can_regenerate || false;
                            this.codeDigits = ['', '', '', '', '', ''];
                            document.getElementById('digit-0')?.focus();
                        }
                    } catch (e) {
                        this.errorMessage = 'An error occurred. Please try again.';
                    } finally {
                        this.submitting = false;
                    }
                },

                async generateNewCode() {
                    this.generating = true;
                    this.errorMessage = '';

                    try {
                        const res = await fetch(`/cleaner/bookings/${this.bookingId}/generate-code`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            }
                        });

                        const data = await res.json();

                        if (data.success) {
                            this.generatedCode = data.code;
                            this.remainingAttempts = 3;
                            this.codeDigits = ['', '', '', '', '', ''];
                            document.getElementById('digit-0')?.focus();
                        } else {
                            this.errorMessage = data.message;
                        }
                    } catch (e) {
                        this.errorMessage = 'Failed to generate code.';
                    } finally {
                        this.generating = false;
                    }
                }
            };
        }
    </script>
</body>
</html>