/**
 * AI Site Builder Pro - Preview Before Commit System
 *
 * Modal system for previewing generated code before saving
 *
 * @package AISiteBuilderPro
 * @since 1.2.0
 */

(function($) {
    'use strict';

    // Preview System Controller
    const AISBPPreview = {
        
        // State
        currentCode: '',
        originalCode: '',
        projectId: null,
        isEditing: false,
        editor: null,

        /**
         * Initialize preview system
         */
        init: function() {
            this.createModal();
            this.bindEvents();
            console.log('👁️ Preview System initialized');
        },

        /**
         * Create the preview modal
         */
        createModal: function() {
            if ($('#aisbp-preview-modal').length) return;

            const modalHtml = `
                <div id="aisbp-preview-modal" class="aisbp-preview-modal">
                    <div class="aisbp-preview-backdrop"></div>
                    <div class="aisbp-preview-container">
                        <!-- Header -->
                        <div class="aisbp-preview-header">
                            <div class="aisbp-preview-title">
                                <span class="preview-icon">👁️</span>
                                <h2>معاينة الكود قبل الحفظ</h2>
                                <span class="aisbp-preview-badge">Preview Mode</span>
                            </div>
                            <div class="aisbp-preview-tabs">
                                <button type="button" class="aisbp-tab active" data-tab="preview">
                                    <span class="dashicons dashicons-visibility"></span>
                                    المعاينة
                                </button>
                                <button type="button" class="aisbp-tab" data-tab="code">
                                    <span class="dashicons dashicons-editor-code"></span>
                                    الكود
                                </button>
                                <button type="button" class="aisbp-tab" data-tab="split">
                                    <span class="dashicons dashicons-columns"></span>
                                    مقسّم
                                </button>
                            </div>
                            <button type="button" class="aisbp-preview-close">&times;</button>
                        </div>

                        <!-- Body -->
                        <div class="aisbp-preview-body">
                            <!-- Preview Panel -->
                            <div class="aisbp-preview-panel active" data-panel="preview">
                                <div class="aisbp-preview-toolbar">
                                    <div class="aisbp-device-switcher">
                                        <button type="button" class="device-btn active" data-device="desktop">
                                            <span class="dashicons dashicons-desktop"></span>
                                        </button>
                                        <button type="button" class="device-btn" data-device="tablet">
                                            <span class="dashicons dashicons-tablet"></span>
                                        </button>
                                        <button type="button" class="device-btn" data-device="mobile">
                                            <span class="dashicons dashicons-smartphone"></span>
                                        </button>
                                    </div>
                                    <div class="aisbp-preview-url">
                                        <span class="url-icon">🔗</span>
                                        <span class="url-text">preview://localhost/</span>
                                    </div>
                                    <button type="button" class="aisbp-btn aisbp-btn-sm aisbp-btn-ghost" id="refresh-preview">
                                        <span class="dashicons dashicons-update"></span>
                                    </button>
                                </div>
                                <div class="aisbp-preview-frame-container">
                                    <iframe id="aisbp-preview-iframe" class="aisbp-preview-iframe"></iframe>
                                </div>
                            </div>

                            <!-- Code Panel -->
                            <div class="aisbp-preview-panel" data-panel="code">
                                <div class="aisbp-code-toolbar">
                                    <div class="aisbp-code-lang">HTML / CSS / JS</div>
                                    <div class="aisbp-code-actions">
                                        <button type="button" class="aisbp-btn aisbp-btn-sm aisbp-btn-ghost" id="copy-code">
                                            <span class="dashicons dashicons-clipboard"></span>
                                            نسخ
                                        </button>
                                        <button type="button" class="aisbp-btn aisbp-btn-sm aisbp-btn-ghost" id="download-code">
                                            <span class="dashicons dashicons-download"></span>
                                            تنزيل
                                        </button>
                                        <button type="button" class="aisbp-btn aisbp-btn-sm aisbp-btn-ghost" id="format-code">
                                            <span class="dashicons dashicons-editor-alignleft"></span>
                                            تنسيق
                                        </button>
                                    </div>
                                </div>
                                <div class="aisbp-code-editor">
                                    <div class="aisbp-line-numbers"></div>
                                    <textarea id="aisbp-code-textarea" spellcheck="false"></textarea>
                                </div>
                            </div>

                            <!-- Split Panel -->
                            <div class="aisbp-preview-panel" data-panel="split">
                                <div class="aisbp-split-view">
                                    <div class="aisbp-split-code">
                                        <div class="aisbp-code-editor small">
                                            <div class="aisbp-line-numbers"></div>
                                            <textarea id="aisbp-split-code-textarea" spellcheck="false"></textarea>
                                        </div>
                                    </div>
                                    <div class="aisbp-split-divider"></div>
                                    <div class="aisbp-split-preview">
                                        <iframe id="aisbp-split-preview-iframe" class="aisbp-preview-iframe"></iframe>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="aisbp-preview-footer">
                            <div class="aisbp-preview-info">
                                <span class="info-item">
                                    <span class="dashicons dashicons-editor-code"></span>
                                    <span id="code-size">0</span> bytes
                                </span>
                                <span class="info-item">
                                    <span class="dashicons dashicons-clock"></span>
                                    آخر تعديل: <span id="last-modified">الآن</span>
                                </span>
                            </div>
                            <div class="aisbp-preview-actions">
                                <button type="button" class="aisbp-btn aisbp-btn-ghost" id="preview-cancel">
                                    إلغاء
                                </button>
                                <button type="button" class="aisbp-btn aisbp-btn-outline" id="preview-edit">
                                    <span class="dashicons dashicons-edit"></span>
                                    تعديل
                                </button>
                                <button type="button" class="aisbp-btn aisbp-btn-primary aisbp-btn-pulse" id="preview-save">
                                    <span class="dashicons dashicons-yes"></span>
                                    حفظ وتطبيق
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(modalHtml);
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            const self = this;

            // Close modal
            $(document).on('click', '.aisbp-preview-close, .aisbp-preview-backdrop, #preview-cancel', function() {
                self.hide();
            });

            // Tab switching
            $(document).on('click', '.aisbp-preview-tabs .aisbp-tab', function() {
                const tab = $(this).data('tab');
                self.switchTab(tab);
            });

            // Device switching
            $(document).on('click', '.device-btn', function() {
                const device = $(this).data('device');
                self.switchDevice(device);
            });

            // Refresh preview
            $(document).on('click', '#refresh-preview', function() {
                self.updatePreview();
            });

            // Copy code
            $(document).on('click', '#copy-code', function() {
                self.copyCode();
            });

            // Download code
            $(document).on('click', '#download-code', function() {
                self.downloadCode();
            });

            // Format code
            $(document).on('click', '#format-code', function() {
                self.formatCode();
            });

            // Edit mode
            $(document).on('click', '#preview-edit', function() {
                self.toggleEdit();
            });

            // Save
            $(document).on('click', '#preview-save', function() {
                self.save();
            });

            // Code changes (live preview in split mode)
            $(document).on('input', '#aisbp-split-code-textarea', function() {
                self.currentCode = $(this).val();
                self.updateSplitPreview();
            });

            // Sync main code textarea with split
            $(document).on('input', '#aisbp-code-textarea', function() {
                self.currentCode = $(this).val();
                $('#aisbp-split-code-textarea').val(self.currentCode);
            });

            // ESC to close
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $('#aisbp-preview-modal').is(':visible')) {
                    self.hide();
                }
            });

            // Ctrl+S to save
            $(document).on('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 's' && $('#aisbp-preview-modal').is(':visible')) {
                    e.preventDefault();
                    self.save();
                }
            });
        },

        /**
         * Show preview modal
         */
        show: function(code, projectId) {
            this.currentCode = code;
            this.originalCode = code;
            this.projectId = projectId;

            // Update textareas
            $('#aisbp-code-textarea').val(code);
            $('#aisbp-split-code-textarea').val(code);

            // Update info
            $('#code-size').text(new Blob([code]).size.toLocaleString());
            $('#last-modified').text('الآن');

            // Update line numbers
            this.updateLineNumbers();

            // Show modal
            $('#aisbp-preview-modal').addClass('show');
            $('body').addClass('aisbp-modal-open');

            // Load preview
            this.updatePreview();
        },

        /**
         * Hide preview modal
         */
        hide: function() {
            $('#aisbp-preview-modal').removeClass('show');
            $('body').removeClass('aisbp-modal-open');
        },

        /**
         * Switch tab
         */
        switchTab: function(tab) {
            $('.aisbp-preview-tabs .aisbp-tab').removeClass('active');
            $(`.aisbp-preview-tabs .aisbp-tab[data-tab="${tab}"]`).addClass('active');

            $('.aisbp-preview-panel').removeClass('active');
            $(`.aisbp-preview-panel[data-panel="${tab}"]`).addClass('active');

            if (tab === 'split') {
                this.updateSplitPreview();
            }
        },

        /**
         * Switch device
         */
        switchDevice: function(device) {
            const $frame = $('.aisbp-preview-frame-container');
            const $iframe = $('#aisbp-preview-iframe');

            $('.device-btn').removeClass('active');
            $(`.device-btn[data-device="${device}"]`).addClass('active');

            $frame.removeClass('device-desktop device-tablet device-mobile');
            $frame.addClass('device-' + device);

            const sizes = {
                desktop: '100%',
                tablet: '768px',
                mobile: '375px'
            };

            $iframe.css('width', sizes[device]);
        },

        /**
         * Update preview iframe
         */
        updatePreview: function() {
            const iframe = document.getElementById('aisbp-preview-iframe');
            const doc = iframe.contentDocument || iframe.contentWindow.document;
            
            doc.open();
            doc.write(this.currentCode);
            doc.close();
        },

        /**
         * Update split preview
         */
        updateSplitPreview: function() {
            const iframe = document.getElementById('aisbp-split-preview-iframe');
            const doc = iframe.contentDocument || iframe.contentWindow.document;
            
            doc.open();
            doc.write(this.currentCode);
            doc.close();
        },

        /**
         * Update line numbers
         */
        updateLineNumbers: function() {
            const lines = (this.currentCode.match(/\n/g) || []).length + 1;
            const lineNumbers = Array.from({ length: lines }, (_, i) => i + 1).join('\n');
            
            $('.aisbp-line-numbers').text(lineNumbers);
        },

        /**
         * Copy code to clipboard
         */
        copyCode: function() {
            navigator.clipboard.writeText(this.currentCode).then(() => {
                if (window.AISBPUltraEffects) {
                    AISBPUltraEffects.showToast('تم النسخ', 'تم نسخ الكود للحافظة', 'success');
                }
            });
        },

        /**
         * Download code as HTML file
         */
        downloadCode: function() {
            const blob = new Blob([this.currentCode], { type: 'text/html' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `aisbp-export-${Date.now()}.html`;
            a.click();
            URL.revokeObjectURL(url);
        },

        /**
         * Format code (basic HTML formatting)
         */
        formatCode: function() {
            // Basic HTML formatting
            let formatted = this.currentCode
                .replace(/></g, '>\n<')
                .replace(/\n\s*\n/g, '\n');

            // Indent
            let indent = 0;
            const lines = formatted.split('\n');
            formatted = lines.map(line => {
                line = line.trim();
                if (line.match(/^<\/(div|section|header|footer|main|article|aside|nav|ul|ol|table|tbody|thead|tr)/i)) {
                    indent--;
                }
                const indented = '  '.repeat(Math.max(0, indent)) + line;
                if (line.match(/^<(div|section|header|footer|main|article|aside|nav|ul|ol|table|tbody|thead|tr)[^/]*>$/i)) {
                    indent++;
                }
                return indented;
            }).join('\n');

            this.currentCode = formatted;
            $('#aisbp-code-textarea').val(formatted);
            $('#aisbp-split-code-textarea').val(formatted);
            this.updateLineNumbers();

            if (window.AISBPUltraEffects) {
                AISBPUltraEffects.showToast('تم التنسيق', 'تم تنسيق الكود', 'success');
            }
        },

        /**
         * Toggle edit mode
         */
        toggleEdit: function() {
            this.isEditing = !this.isEditing;
            
            if (this.isEditing) {
                $('#preview-edit').text('إلغاء التعديل').addClass('aisbp-btn-warning');
                $('#aisbp-code-textarea, #aisbp-split-code-textarea').prop('readonly', false);
                this.switchTab('split');
            } else {
                $('#preview-edit').html('<span class="dashicons dashicons-edit"></span> تعديل').removeClass('aisbp-btn-warning');
                $('#aisbp-code-textarea, #aisbp-split-code-textarea').prop('readonly', true);
                this.currentCode = this.originalCode;
                $('#aisbp-code-textarea, #aisbp-split-code-textarea').val(this.originalCode);
                this.updatePreview();
            }
        },

        /**
         * Save the code
         */
        save: function() {
            const self = this;
            const $btn = $('#preview-save');
            
            $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> جاري الحفظ...');

            $.post(aisbpData.ajaxUrl, {
                action: 'aisbp_save_project',
                nonce: aisbpData.nonce,
                project_id: this.projectId,
                code: this.currentCode
            }).done(function(response) {
                if (response.success) {
                    if (window.AISBPUltraEffects) {
                        AISBPUltraEffects.showToast('تم الحفظ', 'تم حفظ الكود بنجاح', 'success');
                        AISBPUltraEffects.showConfetti();
                    }
                    self.hide();
                    
                    // Trigger update in main app
                    $(document).trigger('aisbp_code_saved', [self.currentCode]);
                } else {
                    if (window.AISBPUltraEffects) {
                        AISBPUltraEffects.showToast('خطأ', response.data?.message || 'فشل الحفظ', 'error');
                    }
                }
            }).fail(function() {
                if (window.AISBPUltraEffects) {
                    AISBPUltraEffects.showToast('خطأ', 'حدث خطأ في الاتصال', 'error');
                }
            }).always(function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-yes"></span> حفظ وتطبيق');
            });
        }
    };

    // Initialize on DOM ready
    $(document).ready(function() {
        AISBPPreview.init();
    });

    // Expose to global
    window.AISBPPreview = AISBPPreview;

})(jQuery);
