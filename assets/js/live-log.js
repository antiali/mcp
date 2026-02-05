/**
 * AI Site Builder Pro - Live Log System
 *
 * Provides real-time logging display during generation
 *
 * @package AISiteBuilderPro
 * @since 1.1.0
 */

(function($) {
    'use strict';

    // Live Log Controller
    const AISBPLiveLog = {
        // Configuration
        config: {
            pollInterval: 1000, // Poll every second
            maxEntries: 50,     // Maximum entries to display
        },

        // State
        sessionId: null,
        isPolling: false,
        pollTimer: null,
        displayedEntries: 0,
        errors: [],

        /**
         * Initialize live log for a build session
         */
        init: function(sessionId) {
            this.sessionId = sessionId;
            this.displayedEntries = 0;
            this.errors = [];
            this.createLogPanel();
            this.startPolling();
        },

        /**
         * Create the log panel UI
         */
        createLogPanel: function() {
            // Check if panel already exists
            if ($('#aisbp-live-log-panel').length) {
                $('#aisbp-live-log-entries').empty();
                return;
            }

            const panelHtml = `
                <div id="aisbp-live-log-panel" class="aisbp-live-log-panel">
                    <div class="aisbp-live-log-header">
                        <span class="aisbp-live-log-title">
                            <span class="aisbp-live-log-indicator"></span>
                            سجل البناء المباشر
                        </span>
                        <div class="aisbp-live-log-actions">
                            <button type="button" class="aisbp-log-toggle-btn" title="تصغير/تكبير">
                                <span class="dashicons dashicons-arrow-down-alt2"></span>
                            </button>
                            <button type="button" class="aisbp-log-clear-btn" title="مسح">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    </div>
                    <div class="aisbp-live-log-body">
                        <div id="aisbp-live-log-entries" class="aisbp-live-log-entries"></div>
                    </div>
                    <div class="aisbp-live-log-footer">
                        <span class="aisbp-log-status">جاري التسجيل...</span>
                        <span class="aisbp-log-stats">
                            <span class="aisbp-log-entry-count">0</span> سجل
                            <span class="aisbp-log-error-count" style="display:none;">
                                | <span class="error-num">0</span> أخطاء
                            </span>
                        </span>
                    </div>
                </div>
            `;

            // Insert after progress phases
            const $progressPhases = $('.aisbp-progress-phases');
            if ($progressPhases.length) {
                $progressPhases.after(panelHtml);
            } else {
                $('.aisbp-generation-sidebar .aisbp-card').append(panelHtml);
            }

            // Bind events
            this.bindEvents();
        },

        /**
         * Bind panel events
         */
        bindEvents: function() {
            const self = this;

            // Toggle panel
            $(document).on('click', '.aisbp-log-toggle-btn', function() {
                const $panel = $('#aisbp-live-log-panel');
                $panel.toggleClass('minimized');
                $(this).find('.dashicons')
                    .toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
            });

            // Clear log
            $(document).on('click', '.aisbp-log-clear-btn', function() {
                $('#aisbp-live-log-entries').empty();
                self.displayedEntries = 0;
                self.updateStats();
            });
        },

        /**
         * Start polling for log updates
         */
        startPolling: function() {
            if (this.isPolling) return;
            
            this.isPolling = true;
            this.poll();

            // Add pulsing indicator
            $('.aisbp-live-log-indicator').addClass('pulsing');
        },

        /**
         * Stop polling
         */
        stopPolling: function() {
            this.isPolling = false;
            if (this.pollTimer) {
                clearTimeout(this.pollTimer);
                this.pollTimer = null;
            }
            
            // Remove pulsing indicator
            $('.aisbp-live-log-indicator').removeClass('pulsing');
            $('.aisbp-log-status').text('مكتمل');
        },

        /**
         * Poll for log updates
         */
        poll: function() {
            if (!this.isPolling || !this.sessionId) return;

            const self = this;

            $.ajax({
                url: aisbpData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'aisbp_get_live_log',
                    nonce: aisbpData.nonce,
                    session_id: this.sessionId,
                    offset: this.displayedEntries
                },
                success: function(response) {
                    if (response.success && response.data.entries) {
                        self.addEntries(response.data.entries);
                        
                        if (response.data.is_complete) {
                            self.stopPolling();
                        }
                    }
                },
                complete: function() {
                    if (self.isPolling) {
                        self.pollTimer = setTimeout(function() {
                            self.poll();
                        }, self.config.pollInterval);
                    }
                }
            });
        },

        /**
         * Add log entries to display
         */
        addEntries: function(entries) {
            const $container = $('#aisbp-live-log-entries');
            const $activePhaseLog = $('.aisbp-progress-phase.active .aisbp-phase-micro-log');
            
            entries.forEach(entry => {
                this.displayedEntries++;
                
                const entryHtml = this.formatEntry(entry);
                $container.append(entryHtml);

                // Track errors
                if (entry.level === 'error') {
                    this.errors.push(entry);
                }

                // Update active phase micro-log
                if ($activePhaseLog.length) {
                    $activePhaseLog.text(`> ${entry.message}`).addClass('flash');
                    setTimeout(() => $activePhaseLog.removeClass('flash'), 200);
                }
            });

            // Auto-scroll to bottom
            $container.scrollTop($container[0].scrollHeight);

            // Update stats
            this.updateStats();

            // Trim old entries if exceeded max
            while ($container.children().length > this.config.maxEntries) {
                $container.children().first().remove();
            }
        },

        /**
         * Format a single log entry
         */
        formatEntry: function(entry) {
            const levelIcons = {
                'debug': '🔍',
                'info': 'ℹ️',
                'warning': '⚠️',
                'error': '❌',
                'success': '✅'
            };

            const levelClasses = {
                'debug': 'log-debug',
                'info': 'log-info',
                'warning': 'log-warning',
                'error': 'log-error',
                'success': 'log-success'
            };

            const icon = levelIcons[entry.level] || 'ℹ️';
            const levelClass = levelClasses[entry.level] || 'log-info';
            const time = entry.elapsed_ms ? `+${entry.elapsed_ms}ms` : '';

            return `
                <div class="aisbp-log-entry ${levelClass}">
                    <span class="log-icon">${icon}</span>
                    <span class="log-time">${time}</span>
                    <span class="log-message">${this.escapeHtml(entry.message)}</span>
                </div>
            `;
        },

        /**
         * Update statistics display
         */
        updateStats: function() {
            $('.aisbp-log-entry-count').text(this.displayedEntries);
            
            if (this.errors.length > 0) {
                $('.aisbp-log-error-count').show().find('.error-num').text(this.errors.length);
            } else {
                $('.aisbp-log-error-count').hide();
            }
        },

        /**
         * Display an error immediately
         */
        showError: function(message, code = '') {
            const entry = {
                level: 'error',
                message: message,
                elapsed_ms: 0
            };
            this.addEntries([entry]);
        },

        /**
         * Display a success message
         */
        showSuccess: function(message) {
            const entry = {
                level: 'success',
                message: message,
                elapsed_ms: 0
            };
            this.addEntries([entry]);
            this.stopPolling();
        },

        /**
         * Check if there are any errors
         */
        hasErrors: function() {
            return this.errors.length > 0;
        },

        /**
         * Get all errors
         */
        getErrors: function() {
            return this.errors;
        },

        /**
         * Escape HTML for safe display
         */
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        /**
         * Destroy the log panel
         */
        destroy: function() {
            this.stopPolling();
            $('#aisbp-live-log-panel').remove();
            this.displayedEntries = 0;
            this.errors = [];
        }
    };

    // Expose to global scope
    window.AISBPLiveLog = AISBPLiveLog;

})(jQuery);
