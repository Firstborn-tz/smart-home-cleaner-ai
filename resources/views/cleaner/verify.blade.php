@extends('layouts.app')

@section('title', 'Service Verification')
@section('user_role', 'Cleaner')
@section('page_title', 'Service Verification')
@section('page_subtitle', '#' . ($booking->booking_number ?? 'N/A'))

@section('content')
<div x-data="verificationSystem()" x-init="init()">
    
    <div class="max-w-lg mx-auto">
        {{-- ============================================ --}}
        {{-- HEADER CARD --}}
        {{-- ============================================ --}}
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            
            {{-- Header --}}
            <div class="bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 px-6 py-8 text-center text-white relative overflow-hidden">
                <div class="absolute inset-0 opacity-10">
                    <svg width="100%" height="100%">
                        <defs><pattern id="verifyDots" x="0" y="0" width="30" height="30" patternUnits="userSpaceOnUse"><circle cx="15" cy="15" r="1.5" fill="white"/></pattern></defs>
                        <rect width="100%" height="100%" fill="url(#verifyDots)"/>
                    </svg>
                </div>
                <div class="relative z-10">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4 backdrop-blur shadow-2xl">
                        <i class="fas fa-shield-halved text-white text-2xl"></i>
                    </div>
                    <h2 class="text-2xl font-black tracking-tight">Service Verification</h2>
                    <p class="text-white/70 text-sm mt-1.5">Enter the 6-digit code from the homeowner</p>
                </div>
            </div>

            {{-- Body --}}
            <div class="p-6 space-y-6">
                
                {{-- Booking Info --}}
                <div class="bg-blue-50 dark:bg-blue-500/10 rounded-2xl p-4 border border-blue-100 dark:border-blue-500/20">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-muted flex items-center gap-1.5">
                            <i class="fas fa-hashtag text-blue-400 text-xs"></i> Booking
                        </span>
                        <span class="font-mono font-bold text-heading" x-text="bookingNumber"></span>
                    </div>
                    <div class="flex items-center justify-between text-sm mt-2 pt-2 border-t border-blue-100 dark:border-blue-500/10">
                        <span class="text-muted flex items-center gap-1.5">
                            <i class="fas fa-flag text-blue-400 text-xs"></i> Status
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300" x-text="bookingStatus"></span>
                    </div>
                </div>

                {{-- Verification Code Input --}}
                <div>
                    <label class="block text-sm font-bold text-heading mb-4 text-center">
                        <i class="fas fa-key text-blue-500 mr-1.5"></i> Verification Code
                    </label>
                    
                    <div class="flex justify-center gap-2 sm:gap-3 mb-4">
                        <template x-for="(digit, index) in codeDigits" :key="index">
                            <input type="text" 
                                   :id="'digit-' + index"
                                   maxlength="1"
                                   x-model="codeDigits[index]"
                                   @input="handleDigitInput($event, index)"
                                   @keydown.backspace="handleBackspace($event, index)"
                                   @paste="handlePaste($event)"
                                   class="w-12 h-16 sm:w-14 sm:h-18 text-center text-2xl sm:text-3xl font-black rounded-2xl border-2 transition-all duration-200 outline-none"
                                   :class="codeDigits[index] ? 'border-blue-500 bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 shadow-lg shadow-blue-500/10' : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-heading'">
                        </template>
                    </div>
                    
                    <p class="text-xs text-muted text-center">
                        Enter the 6-digit code provided by the homeowner
                    </p>
                </div>

                {{-- Attempts Warning --}}
                <div x-show="remainingAttempts < 3" x-transition class="bg-yellow-50 dark:bg-yellow-500/10 rounded-xl p-3 border border-yellow-200 dark:border-yellow-500/20 text-center">
                    <p class="text-sm text-yellow-700 dark:text-yellow-300 flex items-center justify-center gap-2">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span><strong x-text="remainingAttempts"></strong> attempt(s) remaining</span>
                    </p>
                </div>

                {{-- Action Buttons --}}
                <div class="space-y-3">
                    <button @click="verifyCode()" 
                            :disabled="!isCodeComplete() || submitting"
                            class="w-full px-6 py-4 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 text-white rounded-2xl font-bold text-base shadow-xl shadow-blue-500/25 hover:shadow-blue-500/40 hover:scale-[1.01] transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100">
                        <span x-show="!submitting"><i class="fas fa-check-circle mr-2"></i> Verify & Complete Service</span>
                        <span x-show="submitting"><i class="fas fa-spinner fa-spin mr-2"></i> Verifying...</span>
                    </button>
                    
                    <button @click="generateNewCode()" 
                            :disabled="!canRegenerate || generating"
                            class="w-full px-6 py-4 border-2 border-gray-200 dark:border-gray-600 text-body rounded-2xl font-semibold text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!generating"><i class="fas fa-sync-alt mr-2"></i> Generate New Code</span>
                        <span x-show="generating"><i class="fas fa-spinner fa-spin mr-2"></i> Generating...</span>
                    </button>
                </div>

                {{-- Generated Code Display --}}
                <div x-show="generatedCode" x-transition class="bg-linear-to-br from-green-50 to-emerald-50 dark:from-green-500/5 dark:to-emerald-500/5 rounded-2xl p-6 text-center border border-green-200 dark:border-green-500/20">
                    <p class="text-sm text-green-700 dark:text-green-300 mb-3 font-semibold">
                        <i class="fas fa-key mr-1.5"></i> New verification code generated
                    </p>
                    <div class="bg-white dark:bg-gray-800 rounded-xl py-4 px-6 inline-block shadow-md">
                        <p class="text-4xl sm:text-5xl font-black text-green-600 dark:text-green-400 tracking-[0.3em] font-mono" x-text="generatedCode"></p>
                    </div>
                    <p class="text-xs text-green-600 dark:text-green-400 mt-3 flex items-center justify-center gap-1">
                        <i class="fas fa-share"></i> Share this code with the homeowner
                    </p>
                    <p class="text-xs text-muted mt-1 flex items-center justify-center gap-1">
                        <i class="fas fa-clock"></i> Code expires in 30 minutes
                    </p>
                </div>

                {{-- Success Message --}}
                <div x-show="completed" x-transition class="bg-linear-to-br from-green-50 to-emerald-50 dark:from-green-500/5 dark:to-emerald-500/5 rounded-2xl p-8 text-center border border-green-200 dark:border-green-500/20">
                    <div class="w-20 h-20 bg-linear-to-br from-green-400 to-emerald-600 rounded-3xl flex items-center justify-center mx-auto mb-5 shadow-xl shadow-green-500/25">
                        <i class="fas fa-check-circle text-white text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-black text-green-700 dark:text-green-300">Service Completed!</h3>
                    <p class="text-green-600 dark:text-green-400 text-sm mt-2">The booking has been marked as complete</p>
                    <a href="/cleaner/dashboard" 
                       class="inline-flex items-center gap-2 mt-6 px-6 py-3.5 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-2xl font-bold text-sm shadow-xl shadow-green-500/25 hover:shadow-green-500/40 hover:scale-105 transition-all duration-300">
                        <i class="fas fa-th-large"></i> Back to Dashboard
                    </a>
                </div>

                {{-- Error Message --}}
                <div x-show="errorMessage" x-transition class="bg-red-50 dark:bg-red-500/10 rounded-xl p-4 border border-red-200 dark:border-red-500/20 text-center">
                    <p class="text-sm text-red-700 dark:text-red-300 flex items-center justify-center gap-2">
                        <i class="fas fa-exclamation-circle"></i>
                        <span x-text="errorMessage"></span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
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

            init() {
                // Focus first input on load
                this.$nextTick(() => {
                    document.getElementById('digit-0')?.focus();
                });
            },

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
                    this.$nextTick(() => {
                        document.getElementById('digit-' + (index + 1))?.focus();
                    });
                }
            },

            handleBackspace(event, index) {
                if (!this.codeDigits[index] && index > 0) {
                    this.$nextTick(() => {
                        document.getElementById('digit-' + (index - 1))?.focus();
                    });
                }
            },

            handlePaste(event) {
                event.preventDefault();
                const paste = (event.clipboardData || window.clipboardData).getData('text');
                const digits = paste.replace(/[^0-9]/g, '').slice(0, 6).split('');
                
                digits.forEach((digit, i) => {
                    if (i < 6) this.codeDigits[i] = digit;
                });
                
                for (let i = digits.length; i < 6; i++) {
                    this.codeDigits[i] = '';
                }
                
                if (digits.length === 6) {
                    this.$nextTick(() => this.verifyCode());
                }
            },

            async verifyCode() {
                if (!this.isCodeComplete()) return;
                
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
                        this.errorMessage = data.message || 'Invalid verification code';
                        this.remainingAttempts = data.remaining_attempts || 0;
                        this.canRegenerate = data.can_regenerate || false;
                        this.codeDigits = ['', '', '', '', '', ''];
                        this.$nextTick(() => {
                            document.getElementById('digit-0')?.focus();
                        });
                    }
                } catch (e) {
                    this.errorMessage = 'Network error. Please try again.';
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
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        }
                    });

                    const data = await res.json();

                    if (data.success) {
                        this.generatedCode = data.code;
                        this.remainingAttempts = 3;
                        this.codeDigits = ['', '', '', '', '', ''];
                        this.$nextTick(() => {
                            document.getElementById('digit-0')?.focus();
                        });
                    } else {
                        this.errorMessage = data.message || 'Failed to generate code';
                    }
                } catch (e) {
                    this.errorMessage = 'Network error. Please try again.';
                } finally {
                    this.generating = false;
                }
            }
        };
    }
</script>
@endpush
