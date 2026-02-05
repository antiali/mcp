/**
 * AI Site Builder Pro - Admin JavaScript
 *
 * Features:
 * - Multi-step wizard with smooth animations
 * - Real-time live preview
 * - AI chat integration
 * - Progressive generation with phases
 * - Dark mode toggle
 * - Drag & drop file upload
 *
 * @package AISiteBuilderPro
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Main App Controller
    const AISBP = {
        // State
        currentStep: 1,
        totalSteps: 6,
        selectedModel: 'deepseek',
        selectedType: 'business',
        selectedIndustry: 'technology',
        creationMode: 'full_site', // full_site, section, layout, theme_builder, divi5_section, divi5_layout
        colors: { primary: '#4F46E5', secondary: '#10B981', accent: '#F59E0B' },
        fonts: { heading: 'Inter', body: 'Inter' },
        uploadedImages: [],
        uploadedDocs: [],
        projectId: null,
        isGenerating: false,
        isDarkMode: false,

        // Initialize the app
        init: function() {
            this.isGenerating = false; // Reset on refresh

            // CRITICAL: Move chat widget to body FIRST for proper fixed positioning
            this.moveChatWidgetToBody();

            this.bindEvents();
            this.initTheme();
            this.initUploadZones();
            this.initTooltips();

            // Restore saved progress
            try {
                this.restoreProgress();
            } catch (e) {
                console.error('Failed to restore progress:', e);
                // Optional: Clear corrupt data
                localStorage.removeItem('aisbp_autosave');
            }

            // Initialize chat and log events
            this.initChatEvents();

            console.log('✨ AI Site Builder Pro initialized | Version: 1.0.5');
        },

        // Move chat widget to body for proper fixed positioning
        moveChatWidgetToBody: function() {
            const chatWidget = document.querySelector('.aisbp-chat-widget-container');
            if (chatWidget && chatWidget.parentElement !== document.body) {
                document.body.appendChild(chatWidget);
                // Force proper styling - bottom-right for LTR, bottom-left for RTL
                const isRtl = document.documentElement.dir === 'rtl' || document.documentElement.getAttribute('dir') === 'rtl';
                if (isRtl) {
                    chatWidget.style.cssText = 'position: fixed !important; bottom: 30px !important; left: 30px !important; right: auto !important; z-index: 1000 !important; display: flex !important;';
                } else {
                    chatWidget.style.cssText = 'position: fixed !important; bottom: 30px !important; right: 30px !important; left: auto !important; z-index: 1000 !important; display: flex !important;';
                }
                console.log('✅ Chat widget moved to body (corner position)');
            }
        },

        // Toggle Progress Drawer (hamburger menu style)
        toggleProgressDrawer: function() {
            const drawer = $('#aisbp-progress-drawer');
            const overlay = $('#aisbp-drawer-overlay');

            if (drawer.hasClass('active')) {
                this.closeProgressDrawer();
            } else {
                drawer.addClass('active');
                overlay.addClass('active');
                console.log('📊 Progress drawer opened');
            }
        },

        // Close Progress Drawer
        closeProgressDrawer: function() {
            $('#aisbp-progress-drawer').removeClass('active');
            $('#aisbp-drawer-overlay').removeClass('active');
        },

        // Bind all event handlers
        bindEvents: function() {
            const self = this;

            // Auto-Save Triggers
            $(document).on('input change',
                'input, textarea, select',
                function() { self.saveProgress(); });

            $(document).on('click',
                '.aisbp-model-card, .aisbp-type-card, .aisbp-creation-mode-card, .aisbp-palette-card, .aisbp-repeater-remove, #aisbp-add-ref-url',
                function() { self.saveProgress(); });

            // Step changes are helpful to save too so we know where we left off
            // (handled by input saves mostly, but good to ensure latest state)

            // Wizard navigation
            $(document).on('click', '[data-action="next-step"]', function() {
                self.nextStep();
                self.saveProgress();
            });

            $(document).on('click', '[data-action="prev-step"]', function() {
                self.prevStep();
            });

            // Theme toggle
            $('#aisbp-theme-toggle').on('click', function() {
                self.toggleTheme();
            });

            // Progress Drawer Toggle
            $(document).on('click', '#aisbp-progress-toggle', function() {
                self.toggleProgressDrawer();
            });

            $(document).on('click', '#aisbp-drawer-close, #aisbp-drawer-overlay', function() {
                self.closeProgressDrawer();
            });

            // Generate button (THE MISSING LINK)
            $(document).on('click', '[data-action="generate"]', function() {
                console.log('🚀 Generate button clicked');
                self.startGeneration();
            });

            // Device toggle
            $(document).on('click', '.aisbp-device-btn', function() {
                const device = $(this).data('device');
                $('.aisbp-device-btn').removeClass('active');
                $(this).addClass('active');
                self.setPreviewDevice(device);
            });

            // Model selection
            $(document).on('click', '.aisbp-model-card', function() {
                $('.aisbp-model-card').removeClass('selected');
                $(this).addClass('selected');
                self.selectedModel = $(this).data('model');
                self.updateReview();
            });

            // Progress Step Navigation (Clicking on numbers)
            $(document).on('click', '.aisbp-wizard-step', function() {
                const step = parseInt($(this).data('step'));
                if (self.isGenerating) return;

                if (step < self.currentStep) {
                    self.goToStep(step);
                    self.saveProgress();
                } else if (step > self.currentStep) {
                    let canGo = true;
                    for (let i = self.currentStep; i < step; i++) {
                        if (!self.validateStep(i)) {
                            canGo = false;
                            break;
                        }
                    }
                    if (canGo) {
                        self.goToStep(step);
                        self.saveProgress();
                    } else {
                        // Silent fail or brief toast for UX
                    }
                }
            });

            // Website type selection
            $(document).on('click', '.aisbp-type-card', function() {
                $('.aisbp-type-card').removeClass('selected');
                $(this).addClass('selected');
                self.selectedType = $(this).data('type');
                self.updateReview();
            });

            // Color preset selection
            $(document).on('click', '.aisbp-color-preset', function() {
                $('.aisbp-color-preset').removeClass('selected');
                $(this).addClass('selected');

                // Get colors from DOM or data attributes
                // The PHP loop renders swatches, we can grab colors from there if needed
                // Or better, define them in data-colors attribute in PHP template
                const $swatches = $(this).find('.aisbp-color-swatch');
                if ($swatches.length >= 2) {
                    const primary = $swatches.eq(0).css('background-color'); // Returns rgb(...)
                    const secondary = $swatches.eq(1).css('background-color');
                    const accent = $swatches.length > 2 ? $swatches.eq(2).css('background-color') : '#F59E0B';

                    // Convert RGB to Hex helper
                    const rgb2hex = (rgb) => {
                        if (rgb.search("rgb") === -1) return rgb; // already hex
                        rgb = rgb.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+))?\)$/);

                        function hex(x) { return ("0" + parseInt(x).toString(16)).slice(-2); }
                        return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
                    };

                    $('#aisbp-primary-color').val(rgb2hex(primary)).trigger('input');
                    $('#aisbp-secondary-color').val(rgb2hex(secondary)).trigger('input');
                    $('#aisbp-accent-color').val(rgb2hex(accent)).trigger('input');
                }

                self.updateReview();
            });

            // Industry selection
            $('#aisbp-industry').on('change', function() {
                self.selectedIndustry = $(this).val();
                self.updateReview();
            });

            // Color inputs - SYNC Text & Picker
            // Primary
            $('#aisbp-primary-color').on('input', function() {
                const color = $(this).val();
                $('#aisbp-primary-picker').val(color);
                self.colors.primary = color;
            });
            $('#aisbp-primary-picker').on('input', function() {
                const color = $(this).val();
                $('#aisbp-primary-color').val(color);
                self.colors.primary = color;
            });

            // Secondary
            $('#aisbp-secondary-color').on('input', function() {
                const color = $(this).val();
                $('#aisbp-secondary-picker').val(color);
                self.colors.secondary = color;
            });
            $('#aisbp-secondary-picker').on('input', function() {
                const color = $(this).val();
                $('#aisbp-secondary-color').val(color);
                self.colors.secondary = color;
            });

            // Accent
            $('#aisbp-accent-color').on('input', function() {
                const color = $(this).val();
                $('#aisbp-accent-picker').val(color);
                self.colors.accent = color;
            });
            $('#aisbp-accent-picker').on('input', function() {
                const color = $(this).val();
                $('#aisbp-accent-color').val(color);
                self.colors.accent = color;
            });

            // Website Type Selection
            $(document).on('click', '.aisbp-type-card', function() {
                $('.aisbp-type-card').removeClass('selected');
                $(this).addClass('selected');
                self.selectedType = $(this).data('type');
            });

            // Creation Mode Selection (Section, Layout, Theme Builder, Divi 5)
            $(document).on('click', '.aisbp-creation-mode-card', function() {
                $('.aisbp-creation-mode-card').removeClass('selected');
                $(this).addClass('selected');
                self.creationMode = $(this).data('mode');

                // Update UI based on selected mode
                self.updateCreationModeUI();
            });
        },

        // Update UI based on creation mode
        updateCreationModeUI: function() {
            const mode = this.creationMode;

            // Show/hide phases based on mode
            const modePhases = {
                'full_site': [1, 2, 3, 4, 5],
                'section': [2, 3],
                'layout': [1, 2, 3, 4],
                'theme_builder': [2, 3],
                'divi5_section': [2, 3],
                'divi5_layout': [1, 2, 3, 4]
            };

            const activePhases = modePhases[mode] || modePhases['full_site'];

            // Update phase visibility
            $('.aisbp-progress-phase').each(function() {
                const phaseNum = parseInt($(this).data('phase'));
                if (activePhases.includes(phaseNum)) {
                    $(this).removeClass('skipped');
                } else {
                    $(this).addClass('skipped');
                }
            });

            // Update description placeholder based on mode
            const placeholders = {
                'full_site': 'صف موقعك الكامل... مثال: موقع شركة محاماة يحتوي على صفحة رئيسية، خدمات، فريق العمل...',
                'section': 'صف السكشن المطلوب... مثال: قسم شهادات العملاء مع تصميم كارد حديث...',
                'layout': 'صف تخطيط الصفحة... مثال: صفحة هبوط لمنتج تقني مع CTA قوي...',
                'theme_builder': 'صف الـ Header أو Footer... مثال: هيدر شفاف مع ميجا منيو...',
                'divi5_section': 'صف السكشن لـ Divi 5... سيتم التصدير بتنسيق JSON...',
                'divi5_layout': 'صف التخطيط لـ Divi 5... سيتم التصدير بتنسيق JSON...'
            };

            $('#aisbp-description').attr('placeholder', placeholders[mode] || placeholders['full_site']);
        },

        // Navigate to next step
        nextStep: function() {
            if (this.currentStep < this.totalSteps) {
                this.goToStep(this.currentStep + 1);
            }
        },

        // Navigate to previous step
        prevStep: function() {
            if (this.currentStep > 1) {
                this.goToStep(this.currentStep - 1);
            }
        },

        // Go to specific step
        goToStep: function(step) {
            // Validate current step before proceeding
            if (step > this.currentStep && !this.validateStep(this.currentStep)) {
                return;
            }

            // Update step indicators
            $('.aisbp-wizard-step').each(function() {
                const stepNum = parseInt($(this).data('step'));
                $(this).removeClass('active completed');
                if (stepNum < step) {
                    $(this).addClass('completed');
                } else if (stepNum === step) {
                    $(this).addClass('active');
                }
            });

            // Update progress line
            const progress = ((step - 1) / (this.totalSteps - 1)) * 100;
            $('.aisbp-wizard-progress-fill').css('width', progress + '%');

            // Show/hide panels with animation
            $('.aisbp-wizard-panel').removeClass('active');
            $(`.aisbp-wizard-panel[data-panel="${step}"]`).addClass('active');

            this.currentStep = step;

            // Update review when reaching step 5
            if (step === 5) {
                this.updateReview();
            }
        },

        // Validate current step
        validateStep: function(step) {
            switch (step) {
                case 1:
                    return !!this.selectedModel;
                case 2:
                    const desc = $('#aisbp-description').val().trim();
                    if (!desc && this.uploadedImages.length === 0) {
                        this.showNotification('Please provide a description or upload images', 'warning');
                        return false;
                    }
                    return true;
                case 3:
                    return !!this.selectedType;
                case 4:
                    return true;
                default:
                    return true;
            }
        },

        // Update review panel
        updateReview: function() {
            const modelNames = {
                'deepseek': 'DeepSeek V3.2',
                'openai': 'GPT-4o',
                'gemini': 'Gemini 2.5 Pro',
                'claude': 'Claude Sonnet'
            };

            $('#aisbp-review-model').text(modelNames[this.selectedModel] || this.selectedModel);
            $('#aisbp-review-type').text(this.capitalizeFirst(this.selectedType));
            $('#aisbp-review-industry').text(this.capitalizeFirst(this.selectedIndustry));
            $('#aisbp-review-color').text(this.colors.primary);
            $('#aisbp-review-description').text($('#aisbp-description').val() || 'No description provided');

            const urls = this.getRefUrls();
            if (urls.length > 0) {
                $('#aisbp-review-urls').text(urls.length + ' ' + (urls.length === 1 ? 'URL' : 'URLs'));
            } else {
                $('#aisbp-review-urls').text('None');
            }
        },

        // Helper: Get phase message
        getPhaseMessage: function(phase) {
            const messages = {
                1: 'جاري بناء الهيكل (HTML)...',
                2: 'جاري تصميم التخطيط...',
                3: 'جاري تطبيق الأنماط (CSS)...',
                4: 'جاري كتابة المحتوى...',
                5: 'جاري التحسين النهائي...'
            };
            return messages[phase] || 'جاري المعالجة...';
        },

        // Execute a single phase
        executePhase: function(phaseNum, context = '', accumulatedCode = '') {
            const self = this;
            const creationMode = this.creationMode || 'full_site';

            // Define active phases based on mode
            const modePhases = {
                'full_site': [1, 2, 3, 4, 5],
                'section': [2, 3],
                'layout': [1, 2, 3, 4],
                'theme_builder': [2, 3],
                'divi5_section': [2, 3],
                'divi5_layout': [1, 2, 3, 4]
            };
            const activePhases = modePhases[creationMode] || [1, 2, 3, 4, 5];

            // If this phase is not in active phases, skip to next or finish
            if (!activePhases.includes(phaseNum)) {
                if (phaseNum < 5) {
                    this.executePhase(phaseNum + 1, context, accumulatedCode);
                } else {
                    this.finishGeneration(accumulatedCode);
                }
                return;
            }

            // Update UI
            this.updatePhase(phaseNum, 'active', this.getPhaseMessage(phaseNum));

            // Ensure we have a session ID
            if (!this.logSessionId) {
                this.logSessionId = 'build_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                if (window.AISBPLiveLog) {
                    AISBPLiveLog.init(this.logSessionId);
                }
            }

            console.log(`🚀 Executing Phase ${phaseNum} | Session: ${this.logSessionId}`);

            // Prepare Request Data
            const data = {
                action: 'aisbp_generate',
                nonce: aisbpData.nonce,
                model: this.selectedModel,
                creation_mode: creationMode,
                description: $('#aisbp-description').val(),
                instructions: $('#aisbp-instructions').val(),
                site_info: $('#aisbp-site-info').val(),
                website_type: this.selectedType,
                industry: $('#aisbp-industry').val(),
                colors: this.colors,
                fonts: this.fonts,
                reference_urls: this.getRefUrls(),
                blueprint: $('#aisbp-blueprint').val(),
                phase: phaseNum, // CRITICAL: Execute only this phase
                previous_context: context, // Pass context
                project_id: this.projectId || 0,
                session_id: this.logSessionId, // Pass session ID
                images: this.uploadedImages,
                docs: this.uploadedDocs
            };

            // Send AJAX Request with enhanced error handling
            $.ajax({
                url: aisbpData.ajaxUrl,
                type: 'POST',
                data: data,
                dataType: 'json', // CRITICAL: Force JSON parsing
                timeout: 300000, // 5 minutes timeout per phase (matches server)
                beforeSend: function() {
                    // Clear any previous error states
                    self.updatePhase(phaseNum, 'active', self.getPhaseMessage(phaseNum));
                },
                success: function(response) {
                    if (response.success && response.data) {
                        const result = response.data;

                        // Save Project ID if first run
                        if (result.project_id) self.projectId = result.project_id;

                        // Update UI for completion
                        self.updatePhase(phaseNum, 'completed', 'تم بنجاح ✓');

                        // Extract code/context for next phase
                        // The backend returns results indexed by phase number OR a unified structure
                        const phaseResult = result.phases ? result.phases[phaseNum] : null;

                        let nextPhaseContext = context;
                        let updatedCode = accumulatedCode;

                        if (phaseResult && phaseResult.code) {
                            nextPhaseContext = phaseResult.code;
                            updatedCode = phaseResult.code;
                        } else if (result.full_code) {
                            nextPhaseContext = result.full_code;
                            updatedCode = result.full_code;
                        }

                        console.log(`✅ Phase ${phaseNum} completed. Context length: ${nextPhaseContext.length}`);

                        // Log phase completion
                        const phaseNames = { 1: 'الهيكل', 2: 'التخطيط', 3: 'الأنماط', 4: 'المحتوى', 5: 'التحسين' };
                        self.addLogEntry(`✓ اكتملت المرحلة ${phaseNum}: ${phaseNames[phaseNum]}`, 'success');

                        // Move to next phase
                        if (phaseNum < 5) {
                            // Get active phases for mode
                            let phasesForMode = [1, 2, 3, 4, 5];
                            if (aisbpData.modes && aisbpData.modes[creationMode] && aisbpData.modes[creationMode].phases) {
                                phasesForMode = aisbpData.modes[creationMode].phases;
                            }

                            // Find next active phase
                            let nextPhaseId = phaseNum + 1;
                            while (nextPhaseId <= 5 && !phasesForMode.includes(nextPhaseId)) {
                                nextPhaseId++;
                            }

                            if (nextPhaseId <= 5) {
                                self.addLogEntry(`بدء المرحلة ${nextPhaseId}: ${phaseNames[nextPhaseId]}`, 'phase');
                                self.updatePhase(nextPhaseId, 'active', self.getPhaseMessage(nextPhaseId));
                                setTimeout(() => {
                                    self.executePhase(nextPhaseId, nextPhaseContext, updatedCode);
                                }, 800);
                            } else {
                                self.finishGeneration(updatedCode);
                            }
                        } else {
                            self.finishGeneration(updatedCode);
                        }
                    } else {
                        const msg = (response.data && response.data.message) ? response.data.message : 'Unknown generation error';
                        self.handleGenerationError(phaseNum, msg, context, accumulatedCode);
                    }
                },
                error: function(xhr, status, error) {
                    let errorMsg = 'خطأ في الاتصال بالخادم';
                    let detailedError = '';

                    if (status === 'timeout') {
                        errorMsg = 'انتهت مهلة الطلب (5 دقائق). حاول مرة أخرى.';
                    } else if (status === 'parsererror') {
                        // Try to extract useful info from malformed response
                        const responseText = xhr.responseText || '';

                        // Check for PHP errors/warnings in response
                        if (responseText.includes('Fatal error:') || responseText.includes('Parse error:')) {
                            errorMsg = 'خطأ PHP في الخادم. تحقق من سجلات الأخطاء.';
                            const phpErrorMatch = responseText.match(/(Fatal error:|Parse error:)[^<]*/);
                            if (phpErrorMatch) detailedError = phpErrorMatch[0].substring(0, 150);
                        } else if (responseText.includes('Warning:') || responseText.includes('Notice:')) {
                            errorMsg = 'تحذير PHP تسبب في فشل الاستجابة.';
                            const warningMatch = responseText.match(/(Warning:|Notice:)[^<]*/);
                            if (warningMatch) detailedError = warningMatch[0].substring(0, 150);
                        } else if (responseText.trim().startsWith('{') || responseText.trim().startsWith('[')) {
                            // Might be valid JSON that failed to parse for some reason
                            try {
                                const jsonStart = responseText.indexOf('{');
                                const possibleJson = responseText.substring(jsonStart);
                                const parsed = JSON.parse(possibleJson);
                                if (parsed.data && parsed.data.message) {
                                    errorMsg = parsed.data.message;
                                }
                            } catch (e) {
                                errorMsg = 'خطأ في تحليل الاستجابة (JSON غير صالح)';
                            }
                        } else {
                            errorMsg = 'خطأ في معالجة البيانات من الخادم';
                            detailedError = responseText.substring(0, 100);
                        }
                    }

                    // Try standard JSON parsing as fallback
                    try {
                        const resp = xhr.responseJSON || (xhr.responseText && xhr.responseText.includes('{') ? JSON.parse(xhr.responseText.substring(xhr.responseText.indexOf('{'))) : null);
                        if (resp && resp.data && resp.data.message) {
                            errorMsg = resp.data.message;
                            if (resp.data.file) errorMsg += ` (${resp.data.file}:${resp.data.line})`;
                        }
                    } catch (e) {
                        // Already handled above
                    }

                    console.error(`❌ Phase ${phaseNum} AJAX Error:`, {
                        status: status,
                        error: error,
                        detailedError: detailedError,
                        responseText: xhr.responseText ? xhr.responseText.substring(0, 500) : 'none'
                    });

                    // Include detailed error for debugging if available
                    if (detailedError && !errorMsg.includes(detailedError)) {
                        console.warn('Detailed:', detailedError);
                    }

                    self.handleGenerationError(phaseNum, errorMsg, context, accumulatedCode);
                }
            });
        },

        // Handle Generation Success
        finishGeneration: function(finalCode) {
            this.isGenerating = false;
            this.showNotification('✅ تم إنشاء الموقع بنجاح!', 'success');

            // Store code
            this.generatedCode = finalCode;

            // Log success
            this.addLogEntry('✓✓ اكتمل البناء بنجاح!', 'success');
            this.addLogEntry(`حجم الكود: ${finalCode.length} حرف`, 'info');

            // Show preview modal
            if (window.AISBPPreview) {
                AISBPPreview.show(finalCode, this.projectId);
                if (window.AISBPUltraEffects) AISBPUltraEffects.showConfetti();
            } else {
                this.updatePreview(finalCode);
            }

            // Show Chat
            this.showChat();
        },

        // Handle Generation Error
        handleGenerationError: function(phaseNum, message, context = '', accumulatedCode = '') {
            this.isGenerating = false;

            // Stop all phase timers
            for (let i = 1; i <= 5; i++) {
                this.stopPhaseTimer(i);
            }

            // Save state for resume functionality
            this.lastContext = context;
            this.lastAccumulatedCode = accumulatedCode;

            // Update phase UI to show error
            this.updatePhase(phaseNum, 'error', message);

            // Mark remaining phases as skipped
            for (let i = phaseNum + 1; i <= 5; i++) {
                this.updatePhase(i, '', 'تم التخطي');
            }

            // Show notification with phase number
            const phaseNames = { 1: 'الهيكل', 2: 'التخطيط', 3: 'الأنماط', 4: 'المحتوى', 5: 'التحسين' };
            const phaseName = phaseNames[phaseNum] || `مرحلة ${phaseNum}`;
            this.showNotification(`❌ فشلت مرحلة ${phaseName}: ${message}`, 'error');

            // Add to build log
            this.addLogEntry(`[خطأ] ${phaseName}: ${message}`, 'error');

            // Add detailed error to chat
            this.addChatMessage('ai', `⚠️ **فشلت مرحلة ${phaseName}**\n\n${message}\n\n**الحلول الممكنة:**\n- تأكد من صلاحية مفتاح API\n- جرب تقليل طول الوصف\n- انتظر دقيقة ثم حاول مرة أخرى\n- جرب استخدام نموذج AI آخر`);

            // Show resume button
            this.showResumeButton(phaseNum);

            // Log to console for debugging
            console.error(`❌ Generation Failed at Phase ${phaseNum}:`, {
                phase: phaseNum,
                phaseName: phaseName,
                message: message,
                projectId: this.projectId,
                sessionId: this.logSessionId,
                hasContext: !!context,
                hasAccumulatedCode: !!accumulatedCode
            });
        },

        // Start generation process
        startGeneration: function() {
            if (this.isGenerating) {
                console.warn('Generation already in progress.');
                return;
            }

            try {
                this.isGenerating = true;

                const self = this;
                this.goToStep(6);

                // Show initialization placeholder immediately
                this.showBuildingPlaceholder('initializing');


                // Simulate AI Chat Interaction about Time
                this.showChat('🚀 لقد بدأت في تحليل متطلباتك... سأقوم ببناء موقعك في عدة مراحل.');
                $('#aisbp-chat-messages').empty(); // Clear previous chat

                // Add initial greeting
                setTimeout(() => {
                    self.addChatMessage('ai', 'سأبدأ الآن ببناء الهيكل الأساسي للموقع (Phase 1).');
                }, 1000);

                // Simulate Question regarding time
                setTimeout(() => {
                    // Determine time based on complexity (simple logic for now)
                    let estTime = '60-90 ثانية';
                    if (self.creationMode === 'full_site') estTime = '90-120 ثانية';

                    self.addChatMessage('user', 'كم من الوقت سيستغرق بناء الموقع؟');

                    setTimeout(() => {
                        const replies = [
                            `بناءً على الوصف، هذا مشروع ${self.selectedType} لقطاع ${self.selectedIndustry}.`,
                            `أتوقع أن يستغرق الأمر حوالي **${estTime}** لإكمال الهيكل، المحتوى، والتصميم.`
                        ];
                        self.addChatMessage('ai', replies.join(' '));
                    }, 1000);
                }, 1500);

                // Create backup first, then start generation phases
                this.addLogEntry('جاري إنشاء نسخة احتياطية...', 'info');

                $.post(aisbpData.ajaxUrl, {
                    action: 'aisbp_create_backup',
                    nonce: aisbpData.nonce
                }).done(function(response) {
                    console.log('Backup created:', response.data);
                    self.addLogEntry('✓ اكتملت النسخة الاحتياطية', 'success');

                    // Start generation chain
                    self.startGenerationChain();
                }).fail(function() {
                    self.addLogEntry('⚠️ فشل إنشاء نسخة احتياطية، البدء على أي حال...', 'warning');
                    self.startGenerationChain();
                });

                // Reset all phases to waiting
                for (let i = 1; i <= 5; i++) {
                    this.updatePhase(i, '', 'في انتظار...');
                }

                // Clear and initialize build log
                this.clearBuildLog();
                this.hideResumeButton();
                this.addLogEntry('بدء عملية البناء الجديدة', 'phase');
                this.addLogEntry(`النموذج: ${this.selectedModel}`, 'info');
                this.addLogEntry(`وضع الإنشاء: ${this.creationMode}`, 'info');

            } catch (e) {
                console.error('Generation initialization error:', e);
                this.isGenerating = false;
                this.showNotification('حدث خطأ غير متوقع عند البدء. يرجى المحاولة مرة أخرى.', 'error');
            }
        },

        // Helper to start the generation after checks/backups
        startGenerationChain: function() {
            // Show progress
            this.updatePhase(1, 'active', 'جاري بناء الهيكل...');

            // Get creation mode
            const creationMode = this.creationMode || 'full_site';

            // Start generation with enhanced parameters
            // Reset Session ID for new generation
            this.logSessionId = null;

            // Add to log
            this.addLogEntry('بدء المرحلة 1: الهيكل', 'phase');

            // Start phase chain with Phase 1
            this.executePhase(1);
        },

        // Animate phases completion
        animatePhases: function(phases) {
            const self = this;
            let delay = 0;

            phases.forEach(function(phase, index) {
                setTimeout(function() {
                    self.updatePhase(phase.phase, 'completed', 'Done! ✓');
                    if (index < phases.length - 1) {
                        self.updatePhase(phase.phase + 1, 'active', 'Processing...');
                    }
                }, delay);
                delay += 800;
            });
        },

        // Update phase status enhanced
        updatePhase: function(phaseNum, status, message, percentage = null) {
            const $phase = $(`.aisbp-progress-phase[data-phase="${phaseNum}"]`);
            const previousStatus = $phase.hasClass('active') ? 'active' : ($phase.hasClass('completed') ? 'completed' : 'waiting');

            // Handle status change
            if (status !== previousStatus) {
                $phase.removeClass('active completed waiting error').addClass(status);

                if (status === 'active') {
                    this.startPhaseTimer(phaseNum);
                    // Close other details, open this one
                    $('.aisbp-progress-phase').not($phase).find('.aisbp-phase-details').css('max-height', '0');
                    $phase.find('.aisbp-phase-details').css('max-height', '100px');

                    // Update Iframe Placeholder
                    const stagesMap = {
                        1: 'structure',
                        2: 'style', // Layout/Style usually overlap in UI feeling
                        3: 'style',
                        4: 'content',
                        5: 'polishing'
                    };
                    this.showBuildingPlaceholder(stagesMap[phaseNum] || 'initializing');

                } else if (status === 'completed') {
                    this.stopPhaseTimer(phaseNum);
                    percentage = 100;
                    $phase.find('.aisbp-phase-details').css('max-height', '0');
                }
            }

            // Update message text
            if (message) {
                $phase.find('.aisbp-phase-status').text(message);

                // Add to micro log if active
                if (status === 'active') {
                    const $log = $phase.find('.aisbp-phase-micro-log');
                    $log.text(`> ${message}`);
                }
            }

            // Update percentage bar
            if (percentage !== null) {
                $phase.find('.aisbp-phase-progress-fill').css('width', percentage + '%');
            } else if (status === 'active') {
                // Auto-increment progress for active phase if no specific percentage
                const $progressFill = $phase.find('.aisbp-phase-progress-fill');
                if ($progressFill.length && $progressFill[0]) {
                    const currentWidth = parseFloat($progressFill[0].style.width) || 0;
                    if (currentWidth < 90) {
                        const increment = Math.random() * 5 + 1; // Random increment 1-6%
                        $progressFill.css('width', (currentWidth + increment) + '%');
                    }
                }
            }
        },

        // Timer state
        phaseTimers: {},

        // Start timer for a phase
        startPhaseTimer: function(phaseNum) {
            if (this.phaseTimers[phaseNum]) clearInterval(this.phaseTimers[phaseNum]);

            const startTime = Date.now();
            const $timer = $(`.aisbp-progress-phase[data-phase="${phaseNum}"] .aisbp-phase-time`);

            this.phaseTimers[phaseNum] = setInterval(() => {
                const elapsed = Date.now() - startTime;
                $timer.text(this.formatTime(elapsed));
            }, 1000);
        },

        // Stop timer
        stopPhaseTimer: function(phaseNum) {
            if (this.phaseTimers[phaseNum]) {
                clearInterval(this.phaseTimers[phaseNum]);
                delete this.phaseTimers[phaseNum];
            }
        },

        // Format milliseconds to MM:SS
        formatTime: function(ms) {
            const seconds = Math.floor(ms / 1000);
            const m = Math.floor(seconds / 60).toString().padStart(2, '0');
            const s = (seconds % 60).toString().padStart(2, '0');
            return `${m}:${s}`;
        },

        // Update preview iframe
        updatePreview: function(code) {
            const iframe = document.getElementById('aisbp-preview-iframe');
            iframe.srcdoc = code;
        },

        // Set preview device size
        setPreviewDevice: function(device) {
            const $iframe = $('#aisbp-preview-iframe');
            $iframe.removeClass('tablet mobile');
            if (device !== 'desktop') {
                $iframe.addClass(device);
            }
        },

        // Show chat panel
        showChat: function(initialMessage) {
            // FIXED: Ensure chat widget is visible when showing chat
            $('.aisbp-chat-widget-container').addClass('chat-open');
            $('#aisbp-chat').fadeIn(300);
            const msg = initialMessage || '🎉 لقد تم إنشاء موقعك بنجاح! كيف يمكنني مساعدتك في تحسينه؟';
            this.addChatMessage('ai', msg);
        },


        // Note: sendChatMessage, sendChatToBackend, and addChatMessage are defined in the 
        // ENHANCED CHAT FUNCTIONALITY section at the bottom of this file.

        // Undo last action
        undo: function() {
            if (!this.projectId) return;

            const self = this;
            $.post(aisbpData.ajaxUrl, {
                action: 'aisbp_undo',
                nonce: aisbpData.nonce,
                project_id: this.projectId
            }).done(function(response) {
                if (response.success && response.data.state) {
                    self.updatePreview(response.data.state.generated_code);
                    $('#aisbp-redo').prop('disabled', false);
                    self.showNotification('Undone: ' + response.data.action_type, 'success');
                }
            });
        },

        // Redo last undone action
        redo: function() {
            if (!this.projectId) return;

            const self = this;
            $.post(aisbpData.ajaxUrl, {
                action: 'aisbp_redo',
                nonce: aisbpData.nonce,
                project_id: this.projectId
            }).done(function(response) {
                if (response.success && response.data.state) {
                    self.updatePreview(response.data.state.generated_code);
                    self.showNotification('Redone: ' + response.data.action_type, 'success');
                }
            });
        },

        // Show export modal
        showExportModal: function() {
            // Detect available builders
            $.post(aisbpData.ajaxUrl, {
                action: 'aisbp_detect_builders',
                nonce: aisbpData.nonce
            }).done(function(response) {
                if (response.success) {
                    console.log('Available formats:', response.data.formats);
                    // Show modal with options
                }
            });
        },

        // Initialize upload zones
        initUploadZones: function() {
            const self = this;

            // Image upload
            const $imageZone = $('#aisbp-image-upload');
            const $imageInput = $('#aisbp-image-input');

            $imageZone.on('click', function(e) {
                if (e.target !== $imageInput[0]) {
                    $imageInput.trigger('click');
                }
            });

            $imageInput.on('click', function(e) {
                e.stopPropagation();
            });

            // Document upload
            const $docZone = $('#aisbp-doc-upload');
            const $docInput = $('#aisbp-doc-input');

            $docZone.on('click', function(e) {
                if (e.target !== $docInput[0]) {
                    $docInput.trigger('click');
                }
            });

            $docInput.on('click', function(e) {
                e.stopPropagation();
            });

            $imageZone.on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('dragover');
            });

            $imageZone.on('dragleave drop', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
            });

            $imageZone.on('drop', function(e) {
                e.preventDefault(); // Prevent default to allow custom handling
                const files = e.originalEvent.dataTransfer.files; // Correct for drag and drop
                self.handleImageUpload(files);
            });

            $imageInput.on('change', function() {
                self.handleImageUpload(this.files);
            });

            $docInput.on('change', function(e) {
                const files = e.target.files;
                self.handleDocFiles(files);
            });

            // Logo upload
            $('#aisbp-logo-upload').on('click', function(e) {
                const $input = $('#aisbp-logo-input');
                if (e.target !== $input[0]) {
                    $input.trigger('click');
                }
            });

            $('#aisbp-logo-input').on('click', function(e) {
                e.stopPropagation();
            });
        },

        // Handle image upload
        handleImageUpload: function(files) {
            const self = this;
            const $preview = $('#aisbp-image-preview');

            Array.from(files).forEach(function(file) {
                if (!file.type.startsWith('image/')) return;
                if (file.size > 5 * 1024 * 1024) {
                    self.showNotification('Image too large (max 5MB)', 'warning');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    self.uploadedImages.push(e.target.result);

                    const $img = $('<div class="aisbp-uploaded-image"></div>')
                        .css({
                            width: '80px',
                            height: '80px',
                            borderRadius: '8px',
                            backgroundImage: 'url(' + e.target.result + ')',
                            backgroundSize: 'cover',
                            backgroundPosition: 'center'
                        });

                    $preview.append($img);
                };
                reader.readAsDataURL(file);
            });
        },

        // Handle document files
        handleDocFiles: function(files) {
            const self = this;
            Array.from(files).forEach(file => {
                this.uploadedDocs.push(file.name);
                const $item = $(`
                    <div class="aisbp-doc-item">
                        <span class="dashicons dashicons-media-document"></span>
                        <span class="aisbp-doc-name">${file.name}</span>
                        <span class="aisbp-doc-remove" style="cursor:pointer;margin-left:auto;"><span class="dashicons dashicons-no-alt"></span></span>
                    </div>
                `);

                $item.find('.aisbp-doc-remove').on('click', function() {
                    self.uploadedDocs = self.uploadedDocs.filter(d => d !== file.name);
                    $item.remove();
                });

                $('#aisbp-doc-preview').append($item);
            });
        },

        // Theme handling
        initTheme: function() {
            const savedTheme = localStorage.getItem('aisbp_theme');
            if (savedTheme === 'dark') {
                this.enableDarkMode();
            }
        },

        toggleTheme: function() {
            if (this.isDarkMode) {
                this.disableDarkMode();
            } else {
                this.enableDarkMode();
            }
        },

        enableDarkMode: function() {
            $('body').addClass('aisbp-dark');
            $('#aisbp-theme-toggle').addClass('active');
            localStorage.setItem('aisbp_theme', 'dark');
            this.isDarkMode = true;
        },

        disableDarkMode: function() {
            $('body').removeClass('aisbp-dark');
            $('#aisbp-theme-toggle').removeClass('active');
            localStorage.setItem('aisbp_theme', 'light');
            this.isDarkMode = false;
        },

        // Show notification
        showNotification: function(message, type) {
            const $toast = $('<div class="aisbp-toast ' + type + '"></div>')
                .text(message)
                .css({
                    position: 'fixed',
                    bottom: '20px',
                    right: '20px',
                    padding: '12px 24px',
                    background: type === 'error' ? '#EF4444' : (type === 'warning' ? '#F59E0B' : '#22C55E'),
                    color: '#fff',
                    borderRadius: '8px',
                    boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                    zIndex: 9999,
                    animation: 'fadeIn 0.3s ease-out'
                });

            $('body').append($toast);
            setTimeout(function() {
                $toast.fadeOut(300, function() { $(this).remove(); });
            }, 4000);
        },

        // Initialize tooltips
        initTooltips: function() {
            // Simple tooltip implementation
        },

        // Helper: Capitalize first letter
        capitalizeFirst: function(str) {
            return str.charAt(0).toUpperCase() + str.slice(1).replace(/_/g, ' ');
        },

        // Helper: Get all reference URLs
        // Helper: Get all reference URLs
        getRefUrls: function() {
            const urls = [];
            $('.aisbp-ref-url').each(function() {
                const val = $(this).val().trim();
                if (val) urls.push(val);
            });
            return urls;
        },

        /* ===== ENHANCED AUTO SAVE SYSTEM ===== */

        // Save progress to localStorage
        saveProgress: function() {
            const data = {
                step: this.currentStep,
                model: this.selectedModel,
                type: this.selectedType,
                mode: this.creationMode,
                industry: $('#aisbp-industry').val(),
                description: $('#aisbp-description').val(),
                instructions: $('#aisbp-instructions').val(),
                site_info: $('#aisbp-site-info').val(),
                colors: this.colors,
                fonts: this.fonts,
                ref_urls: this.getRefUrls(),
                projectId: this.projectId,
                generatedCode: this.generatedCode,
                uploadedImages: this.uploadedImages,
                uploadedDocs: this.uploadedDocs,
                timestamp: Date.now()
            };

            localStorage.setItem('aisbp_autosave', JSON.stringify(data));
            this.showSaveIndicator();
        },

        // Restore progress from localStorage
        restoreProgress: function() {
            const saved = localStorage.getItem('aisbp_autosave');
            if (!saved) return;

            try {
                const data = JSON.parse(saved);

                // Only restore if data is less than 48 hours old (relaxed from 24)
                if (Date.now() - data.timestamp > 48 * 60 * 60 * 1000) return;

                console.log('🔄 Restoring autosaved progress...', data);

                // Restore Project ID
                if (data.projectId) {
                    this.projectId = data.projectId;
                    console.log('📌 Restored Project ID:', this.projectId);
                }

                // Restore Generated Code & Update Preview
                if (data.generatedCode) {
                    this.generatedCode = data.generatedCode;
                    this.updatePreview(data.generatedCode);
                    // Open chat automatically if we have code
                    this.showChat('🎉 أهلاً بك من جديد! لقد استعدت تصميمك السابق. كيف يمكنني مساعدتك اليوم؟');
                }

                // Restore Model
                if (data.model) {
                    this.selectedModel = data.model;
                    $('.aisbp-model-card').removeClass('selected');
                    $(`.aisbp-model-card[data-model="${data.model}"]`).addClass('selected');
                }

                // Restore Mode
                if (data.mode) {
                    this.creationMode = data.mode;
                    $('.aisbp-creation-mode-card').removeClass('selected');
                    $(`.aisbp-creation-mode-card[data-mode="${data.mode}"]`).addClass('selected');
                    this.updateCreationModeUI();
                }

                // Restore Inputs
                if (data.description) $('#aisbp-description').val(data.description);
                if (data.instructions) $('#aisbp-instructions').val(data.instructions);
                if (data.site_info) $('#aisbp-site-info').val(data.site_info);
                if (data.industry) $('#aisbp-industry').val(data.industry);

                // Restore Type
                if (data.type) {
                    this.selectedType = data.type;
                    $('.aisbp-type-card').removeClass('selected');
                    $(`.aisbp-type-card[data-type="${data.type}"]`).addClass('selected');
                }

                // Restore Colors (Inputs & Pickers & Preview)
                if (data.colors) {
                    this.colors = data.colors;
                    if (data.colors.primary) {
                        $('#aisbp-primary-color').val(data.colors.primary);
                        $('#aisbp-primary-picker').val(data.colors.primary);
                        $('#aisbp-primary-preview').css('background', data.colors.primary);
                    }
                    if (data.colors.secondary) {
                        $('#aisbp-secondary-color').val(data.colors.secondary);
                        $('#aisbp-secondary-picker').val(data.colors.secondary);
                        $('#aisbp-secondary-preview').css('background', data.colors.secondary);
                    }
                    if (data.colors.accent) {
                        $('#aisbp-accent-color').val(data.colors.accent);
                        $('#aisbp-accent-picker').val(data.colors.accent);
                    }
                    this.updateReview();
                }

                // Restore Ref URLs
                if (data.ref_urls && data.ref_urls.length) {
                    // Clear existing except first
                    $('.aisbp-ref-url').not(':first').parent().remove();
                    $('.aisbp-ref-url:first').val(data.ref_urls[0]);

                    // Add others
                    for (let i = 1; i < data.ref_urls.length; i++) {
                        const $container = $('#aisbp-refs-container');
                        const $newItem = $container.find('.aisbp-repeater-item').first().clone();
                        $newItem.find('input').val(data.ref_urls[i]);
                        $container.append($newItem);
                    }
                }

                // Restore Uploaded Images
                if (data.uploadedImages && data.uploadedImages.length > 0) {
                    this.uploadedImages = data.uploadedImages;
                    const $preview = $('#aisbp-images-preview');
                    $preview.empty(); // Clear default empty state

                    data.uploadedImages.forEach(imgId => {
                        // We assume we don't have URL, just ID. 
                        // UX Compromise: Just show a generic file icon or try to fetch via AJAX if needed.
                        // For now, let's show a "File Recovered" placeholder
                        const html = `
                            <div class="aisbp-file-preview-item" data-id="${imgId}">
                                <div class="aisbp-file-icon">🖼️</div>
                                <div class="aisbp-file-info">
                                    <span class="aisbp-file-name">Recovered Image (${imgId})</span>
                                </div>
                            </div>
                        `;
                        $preview.append(html);
                    });
                }

                // Restore Uploaded Docs
                if (data.uploadedDocs && data.uploadedDocs.length > 0) {
                    this.uploadedDocs = data.uploadedDocs;
                    const $preview = $('#aisbp-docs-preview');
                    $preview.empty();

                    data.uploadedDocs.forEach(docId => {
                        const html = `
                            <div class="aisbp-file-preview-item" data-id="${docId}">
                                <div class="aisbp-file-icon">📄</div>
                                <div class="aisbp-file-info">
                                    <span class="aisbp-file-name">Recovered Document (${docId})</span>
                                </div>
                            </div>
                        `;
                        $preview.append(html);
                    });
                }

                // Show notification
                this.showNotification('تم استعادة بياناتك المحفوظة تلقائياً', 'success');

            } catch (e) {
                console.error('Error restoring autosave:', e);
            }
        },

        // Show subtle save indicator
        showSaveIndicator: function() {
            let $indicator = $('#aisbp-save-indicator');
            if (!$indicator.length) {
                $indicator = $('<div id="aisbp-save-indicator">تم الحفظ</div>').css({
                    position: 'fixed',
                    bottom: '10px',
                    left: '10px',
                    background: 'rgba(0,0,0,0.5)',
                    color: '#fff',
                    padding: '4px 8px',
                    borderRadius: '4px',
                    fontSize: '10px',
                    opacity: 0,
                    transition: 'opacity 0.3s'
                }).appendTo('body');
            }

            $indicator.css('opacity', 1);
            clearTimeout(this.saveTimer);
            this.saveTimer = setTimeout(() => $indicator.css('opacity', 0), 2000);
        },

        // Clear autosave
        clearAutosave: function() {
            localStorage.removeItem('aisbp_autosave');
        },

        // Show building placeholder in iframe
        showBuildingPlaceholder: function(stage = 'initializing') {
            const iframe = document.getElementById('aisbp-preview-iframe');
            if (!iframe) return;

            const stages = {
                'initializing': { text: 'تهيئة بيئة العمل...', icon: '⚙️' },
                'structure': { text: 'بناء هيكل الصفحة (HTML)...', icon: '🏗️' },
                'style': { text: 'تطبيق التنسيقات (CSS)...', icon: '🎨' },
                'content': { text: 'كتابة المحتوى الذكي...', icon: '✍️' },
                'polishing': { text: 'تحسين المسافات والألوان...', icon: '✨' }
            };

            const current = stages[stage] || stages['initializing'];

            const html = `
                <!DOCTYPE html>
                <html dir="rtl">
                <head>
                    <style>
                        body {
                            font-family: system-ui, -apple-system, sans-serif;
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            justify-content: center;
                            height: 100vh;
                            margin: 0;
                            background: #f8fafc;
                            color: #334155;
                        }
                        .loader-container {
                            text-align: center;
                            background: white;
                            padding: 40px;
                            border-radius: 20px;
                            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
                            width: 300px;
                        }
                        .icon { font-size: 48px; margin-bottom: 20px; display: block; animation: bounce 2s infinite; }
                        .text { font-size: 18px; font-weight: 600; margin-bottom: 10px; }
                        .subtext { font-size: 14px; color: #94a3b8; }
                        .progress {
                            height: 6px;
                            background: #e2e8f0;
                            border-radius: 10px;
                            margin-top: 20px;
                            overflow: hidden;
                        }
                        .bar {
                            height: 100%;
                            background: #6366f1;
                            width: 0%;
                            animation: progress 2s ease-in-out infinite;
                            border-radius: 10px;
                        }
                        @keyframes bounce {
                            0%, 100% { transform: translateY(0); }
                            50% { transform: translateY(-10px); }
                        }
                        @keyframes progress {
                            0% { width: 0%; transform: translateX(-100%); }
                            50% { width: 50%; transform: translateX(0); }
                            100% { width: 100%; transform: translateX(100%); }
                        }
                        .skeleton {
                            margin-top: 30px;
                            width: 100%;
                            display: flex;
                            flex-direction: column;
                            gap: 10px;
                            opacity: 0.5;
                        }
                        .sk-line { height: 10px; background: #e2e8f0; border-radius: 4px; }
                        .sk-w-full { width: 100%; }
                        .sk-w-80 { width: 80%; }
                        .sk-w-60 { width: 60%; }
                    </style>
                </head>
                <body>
                    <div class="loader-container">
                        <span class="icon">${current.icon}</span>
                        <div class="text">جاري التصميم...</div>
                        <div class="subtext">${current.text}</div>
                        <div class="progress"><div class="bar"></div></div>
                        
                        <div class="skeleton">
                            <div class="sk-line sk-w-full"></div>
                            <div class="sk-line sk-w-80"></div>
                            <div class="sk-line sk-w-60"></div>
                        </div>
                    </div>
                </body>
                </html>
            `;

            iframe.srcdoc = html;
        },

        // ===================================
        // BUILD LOG FUNCTIONALITY
        // ===================================

        // Add log entry to build log
        addLogEntry: function(message, type = 'info') {
            const now = new Date();
            const time = now.toTimeString().split(' ')[0]; // HH:MM:SS
            const $log = $('#aisbp-build-log');

            const $entry = $(`
                <div class="aisbp-log-entry ${type}">
                    <span class="aisbp-log-time">${time}</span>
                    <span class="aisbp-log-msg">${message}</span>
                </div>
            `);

            $log.append($entry);

            // Auto-scroll to bottom
            $log.scrollTop($log[0].scrollHeight);
        },

        // Clear build log
        clearBuildLog: function() {
            $('#aisbp-build-log').html(`
                <div class="aisbp-log-entry info">
                    <span class="aisbp-log-time">--:--:--</span>
                    <span class="aisbp-log-msg">في انتظار بدء البناء...</span>
                </div>
            `);
        },

        // Copy build log to clipboard
        copyBuildLog: function() {
            const self = this;
            const $log = $('#aisbp-build-log');
            let logText = '';

            $log.find('.aisbp-log-entry').each(function() {
                const time = $(this).find('.aisbp-log-time').text();
                const msg = $(this).find('.aisbp-log-msg').text();
                const type = $(this).hasClass('error') ? '[ERROR]' :
                    $(this).hasClass('success') ? '[SUCCESS]' :
                    $(this).hasClass('warning') ? '[WARNING]' :
                    $(this).hasClass('phase') ? '[PHASE]' : '[INFO]';
                logText += `${time} ${type} ${msg}\n`;
            });

            // Add session info
            logText = `=== AI Site Builder Pro - Build Log ===\n` +
                `Session: ${this.logSessionId || 'N/A'}\n` +
                `Project: ${this.projectId || 'N/A'}\n` +
                `Date: ${new Date().toISOString()}\n` +
                `===================================\n\n` +
                logText;

            navigator.clipboard.writeText(logText).then(() => {
                self.showNotification('✅ تم نسخ السجل بنجاح', 'success');
                $('#aisbp-copy-log span').text('تم النسخ!');
                setTimeout(() => {
                    $('#aisbp-copy-log span').text('نسخ');
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy:', err);
                self.showNotification('❌ فشل نسخ السجل', 'error');
            });
        },

        // ===================================
        // RESUME GENERATION FUNCTIONALITY
        // ===================================

        // Store failed phase for resume
        failedPhase: 0,
        lastAccumulatedCode: '',
        lastContext: '',

        // Show resume button
        showResumeButton: function(phaseNum) {
            this.failedPhase = phaseNum;
            $('#aisbp-resume-container').show();
            this.addLogEntry(`يمكنك استكمال البناء من المرحلة ${phaseNum}`, 'warning');
        },

        // Hide resume button  
        hideResumeButton: function() {
            this.failedPhase = 0;
            $('#aisbp-resume-container').hide();
        },

        // Resume generation from failed phase
        resumeGeneration: function() {
            if (!this.failedPhase || this.failedPhase < 1 || this.failedPhase > 5) {
                this.showNotification('لا توجد مرحلة فاشلة لاستكمالها', 'warning');
                return;
            }

            const phaseNum = this.failedPhase;
            this.hideResumeButton();

            this.addLogEntry(`استئناف البناء من المرحلة ${phaseNum}...`, 'info');
            this.addChatMessage('ai', `⏳ جاري استئناف البناء من المرحلة ${phaseNum}...`);

            // Reset phase status
            this.updatePhase(phaseNum, 'active', 'جاري إعادة المحاولة...');

            // Resume from the failed phase
            this.isGenerating = true;
            this.executePhase(phaseNum, this.lastContext, this.lastAccumulatedCode);
        },

        // ===================================
        // ENHANCED CHAT FUNCTIONALITY
        // ===================================

        // Initialize chat events
        initChatEvents: function() {
            const self = this;

            // Move chat widget to body for proper fixed positioning
            const chatWidget = document.querySelector('.aisbp-chat-widget-container');
            if (chatWidget && chatWidget.parentElement !== document.body) {
                document.body.appendChild(chatWidget);
                // Ensure proper styling - respect RTL/LTR
                const isRtl = document.documentElement.dir === 'rtl' || document.documentElement.getAttribute('dir') === 'rtl';
                chatWidget.style.position = 'fixed';
                chatWidget.style.bottom = '30px';
                if (isRtl) {
                    chatWidget.style.left = '30px';
                    chatWidget.style.right = 'auto';
                } else {
                    chatWidget.style.right = '30px';
                    chatWidget.style.left = 'auto';
                }
                chatWidget.style.zIndex = '1000';
                console.log('✅ Chat widget moved to body for proper positioning');
            }

            // Toggle Chat Modal
            $(document).on('click', '#aisbp-chat-toggle', function() {
                const $modal = $('#aisbp-chat-modal');
                const $container = $('.aisbp-chat-widget-container');

                $modal.toggleClass('active');
                $(this).toggleClass('chat-open');
                // FIXED: Always add chat-open class to container when modal is active
                if ($modal.hasClass('active')) {
                    $container.addClass('chat-open');
                } else {
                    $container.removeClass('chat-open');
                }

                // Hide badge when opened
                $('.aisbp-chat-fab-badge').fadeOut();
            });

            // Minimize Chat Modal
            $(document).on('click', '#aisbp-chat-minimize', function() {
                $('#aisbp-chat-modal').removeClass('active');
                $('#aisbp-chat-toggle').removeClass('chat-open');
                $('.aisbp-chat-widget-container').removeClass('chat-open');
            });

            // Close modal when clicking outside (optional but good for UX)
            $(document).mousedown(function(e) {
                const container = $(".aisbp-chat-widget-container");
                if (!container.is(e.target) && container.has(e.target).length === 0) {
                    $('#aisbp-chat-modal').removeClass('active');
                    $('#aisbp-chat-toggle').removeClass('chat-open');
                    container.removeClass('chat-open');
                }
            });

            // Copy log button
            $(document).on('click', '#aisbp-copy-log', function() {
                self.copyBuildLog();
            });

            // Resume button
            $(document).on('click', '#aisbp-resume-generation', function() {
                self.resumeGeneration();
            });

            // Clear chat button
            $(document).on('click', '#aisbp-clear-chat', function() {
                $('#aisbp-chat-messages').html(`
                    <div class="aisbp-chat-msg ai welcome">
                        <div class="aisbp-chat-avatar"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="2"/><circle cx="12" cy="5" r="2"/><path d="M12 7v4"/></svg></div>
                        <div class="aisbp-chat-content">مرحباً! أنا مساعدك الذكي. يمكنك سؤالي أي شيء أو طلب تعديلات على الموقع المولّد.</div>
                    </div>
                `);
            });

            // Quick actions
            $(document).on('click', '.aisbp-quick-action', function() {
                const prompt = $(this).data('prompt');
                $('#aisbp-chat-input').val(prompt);
                self.sendChatMessage(prompt);
            });

            // Send on Enter (but Shift+Enter for new line)
            $(document).on('keydown', '#aisbp-chat-input', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    const message = $(this).val().trim();
                    if (message) {
                        self.sendChatMessage(message);
                        $(this).val('');
                    }
                }
            });

            // Auto-resize textarea
            $(document).on('input', '#aisbp-chat-input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            });

            // Send button click handler
            $(document).on('click', '#aisbp-chat-send', function() {
                const message = $('#aisbp-chat-input').val().trim();
                if (message) {
                    self.sendChatMessage(message);
                    $('#aisbp-chat-input').val('').trigger('input');
                }
            });
        },

        // Send chat message to AI
        sendChatMessage: function(message) {
            if (!message) {
                this.showNotification('يرجى كتابة رسالة', 'warning');
                return;
            }

            const self = this;

            // Add user message to UI
            this.addChatMessage('user', message);

            // Add thinking indicator
            const thinkingId = 'thinking-' + Date.now();
            this.addChatMessage('ai', '🤔 جاري التفكير...', thinkingId, 'thinking');

            // Prepare request data
            const requestData = {
                action: 'aisbp_chat',
                nonce: aisbpData.nonce,
                message: message,
                project_id: this.projectId || 0,
                context: this.generatedCode || ''
            };

            console.log('💬 Sending chat message:', message.substring(0, 50) + '...');

            // Send to backend
            $.ajax({
                url: aisbpData.ajaxUrl,
                type: 'POST',
                data: requestData,
                timeout: 120000, // 2 minutes timeout
                success: function(response) {
                    $(`#${thinkingId}`).remove();

                    console.log('📨 Chat response:', response);

                    if (response.success && response.data) {
                        // Show AI response
                        const aiResponse = response.data.response ||
                            (typeof response.data === 'string' ? response.data : 'تم استلام الرد');
                        self.addChatMessage('ai', aiResponse);

                        // If code was modified, update preview
                        if (response.data.code) {
                            self.generatedCode = response.data.code;
                            self.updatePreview(response.data.code);
                            self.showNotification('✅ تم تحديث الموقع بناءً على طلبك', 'success');
                        }
                    } else {
                        // Show error from server
                        const errorMsg = (response.data && response.data.message) ?
                            response.data.message :
                            'حدث خطأ غير متوقع';
                        self.addChatMessage('ai', '⚠️ ' + errorMsg);
                    }
                },
                error: function(xhr, status, error) {
                    $(`#${thinkingId}`).remove();

                    let errorMsg = 'فشل الاتصال بالخادم';
                    if (status === 'timeout') {
                        errorMsg = 'انتهت مهلة الطلب. حاول مرة أخرى.';
                    } else if (xhr.responseJSON && xhr.responseJSON.data) {
                        errorMsg = xhr.responseJSON.data.message || error;
                    }

                    console.error('❌ Chat error:', status, error, xhr.responseText);
                    self.addChatMessage('ai', '❌ ' + errorMsg);
                }
            });
        },

        // Add chat message with optional ID and class
        addChatMessage: function(role, content, id = '', extraClass = '') {
            const $container = $('#aisbp-chat-messages');
            const msgId = id || 'msg-' + Date.now();

            let html = `<div class="aisbp-chat-msg ${role} ${extraClass}" id="${msgId}">`;

            if (role === 'ai') {
                html += `<div class="aisbp-chat-avatar"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="2"/><circle cx="12" cy="5" r="2"/><path d="M12 7v4"/></svg></div>`;
            }

            html += `<div class="aisbp-chat-content">${this.formatChatMessage(content)}</div>`;
            html += `</div>`;

            $container.append(html);
            $container.scrollTop($container[0].scrollHeight);
        },

        // Format chat message (basic markdown)
        formatChatMessage: function(content) {
            // Bold
            content = content.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
            // Line breaks
            content = content.replace(/\\n/g, '<br>');
            content = content.replace(/\n/g, '<br>');
            return content;
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        if ($('.aisbp-wizard').length || $('#aisbp-app').length || $('.aisbp-page').length) {
            AISBP.init();
        }
    });

    // Expose to global scope for debugging
    window.AISBP = AISBP;

})(jQuery);