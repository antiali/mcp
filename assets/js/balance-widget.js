/**
 * AI Site Builder Pro - Balance Widget
 *
 * Display AI model credits/balance in the dashboard
 *
 * @package AISiteBuilderPro
 * @since 1.2.0
 */

(function($) {
    'use strict';

    // Balance Widget Controller
    const AISBPBalance = {
        
        // State
        balances: {},
        isLoading: false,
        container: null,

        /**
         * Initialize balance widget
         */
        init: function() {
            this.createWidget();
            this.bindEvents();
            this.loadBalances();
            console.log('💰 Balance Widget initialized');
        },

        /**
         * Create the balance widget
         */
        createWidget: function() {
            // Find the header or sidebar
            const $header = $('.aisbp-page-header');
            const $sidebar = $('.aisbp-wizard-sidebar');
            
            if ($header.length) {
                // Add to header
                const widgetHtml = this.getWidgetHtml();
                $header.find('.aisbp-header-actions').prepend(widgetHtml);
            } else if ($sidebar.length) {
                // Add to sidebar
                const widgetHtml = this.getFullWidgetHtml();
                $sidebar.prepend(widgetHtml);
            }
            
            this.container = $('#aisbp-balance-widget');
        },

        /**
         * Get compact widget HTML
         */
        getWidgetHtml: function() {
            return `
                <div id="aisbp-balance-widget" class="aisbp-balance-widget compact">
                    <button type="button" class="aisbp-balance-toggle" title="رصيد النماذج">
                        <span class="balance-icon">💳</span>
                        <span class="balance-summary">--</span>
                        <span class="balance-arrow">▼</span>
                    </button>
                    <div class="aisbp-balance-dropdown">
                        <div class="balance-dropdown-header">
                            <h4>رصيد نماذج AI</h4>
                            <button type="button" class="balance-refresh" title="تحديث">
                                <span class="dashicons dashicons-update"></span>
                            </button>
                        </div>
                        <div class="balance-list">
                            <div class="balance-loading">
                                <span class="spinner"></span>
                                جاري التحميل...
                            </div>
                        </div>
                        <div class="balance-dropdown-footer">
                            <small>آخر تحديث: <span class="last-update">--</span></small>
                        </div>
                    </div>
                </div>
            `;
        },

        /**
         * Get full widget HTML (for sidebar)
         */
        getFullWidgetHtml: function() {
            return `
                <div id="aisbp-balance-widget" class="aisbp-balance-widget full">
                    <div class="balance-widget-header">
                        <h4>💳 رصيد نماذج AI</h4>
                        <button type="button" class="balance-refresh" title="تحديث">
                            <span class="dashicons dashicons-update"></span>
                        </button>
                    </div>
                    <div class="balance-list">
                        <div class="balance-loading">
                            <span class="spinner"></span>
                            جاري التحميل...
                        </div>
                    </div>
                    <div class="balance-widget-footer">
                        <small>آخر تحديث: <span class="last-update">--</span></small>
                    </div>
                </div>
            `;
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            const self = this;

            // Toggle dropdown
            $(document).on('click', '.aisbp-balance-toggle', function(e) {
                e.stopPropagation();
                $(this).closest('.aisbp-balance-widget').toggleClass('open');
            });

            // Close dropdown on outside click
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.aisbp-balance-widget').length) {
                    $('.aisbp-balance-widget').removeClass('open');
                }
            });

            // Refresh balances
            $(document).on('click', '.balance-refresh', function(e) {
                e.stopPropagation();
                self.loadBalances(true);
            });

            // Refresh specific model
            $(document).on('click', '.balance-item-refresh', function(e) {
                e.stopPropagation();
                const model = $(this).data('model');
                self.refreshModel(model);
            });
        },

        /**
         * Load all balances
         */
        loadBalances: function(forceRefresh = false) {
            const self = this;
            
            if (this.isLoading) return;
            this.isLoading = true;

            this.showLoading();

            $.post(aisbpData.ajaxUrl, {
                action: 'aisbp_get_balances',
                nonce: aisbpData.nonce,
                refresh: forceRefresh ? 'true' : 'false'
            }).done(function(response) {
                if (response.success) {
                    self.balances = response.data.balances;
                    self.renderBalances();
                } else {
                    self.showError(response.data?.message || 'فشل تحميل الرصيد');
                }
            }).fail(function() {
                self.showError('خطأ في الاتصال');
            }).always(function() {
                self.isLoading = false;
            });
        },

        /**
         * Refresh specific model
         */
        refreshModel: function(model) {
            const self = this;
            const $item = $(`.balance-item[data-model="${model}"]`);
            
            $item.addClass('refreshing');

            $.post(aisbpData.ajaxUrl, {
                action: 'aisbp_refresh_balance',
                nonce: aisbpData.nonce,
                model: model
            }).done(function(response) {
                if (response.success) {
                    self.balances[model] = response.data.balance;
                    self.renderBalances();
                    
                    // Show success animation
                    $item.addClass('updated');
                    setTimeout(() => $item.removeClass('updated'), 1000);
                }
            }).always(function() {
                $item.removeClass('refreshing');
            });
        },

        /**
         * Render balances list
         */
        renderBalances: function() {
            const $list = $('.balance-list');
            
            if (Object.keys(this.balances).length === 0) {
                $list.html(`
                    <div class="balance-empty">
                        <span class="empty-icon">🔑</span>
                        <p>لم يتم تكوين أي مفاتيح API</p>
                        <a href="${aisbpData.pluginUrl}admin.php?page=ai-site-builder-settings" class="balance-setup-link">
                            إعداد المفاتيح
                        </a>
                    </div>
                `);
                return;
            }

            let html = '';
            let totalUSD = 0;
            let activeCount = 0;

            for (const [model, data] of Object.entries(this.balances)) {
                const statusClass = this.getStatusClass(data);
                const balanceDisplay = this.formatBalance(data);
                const icon = this.getModelIcon(model);
                
                if (data.available) activeCount++;
                if (data.balance_usd) totalUSD += parseFloat(data.balance_usd);

                html += `
                    <div class="balance-item ${statusClass}" data-model="${model}">
                        <div class="balance-item-icon">${icon}</div>
                        <div class="balance-item-info">
                            <span class="balance-model-name">${data.model}</span>
                            <span class="balance-amount">${balanceDisplay}</span>
                            ${data.note ? `<span class="balance-note">${data.note}</span>` : ''}
                            ${data.error ? `<span class="balance-error">${data.error}</span>` : ''}
                        </div>
                        <div class="balance-item-actions">
                            ${data.dashboard_url ? `
                                <a href="${data.dashboard_url}" target="_blank" class="balance-dashboard-link" title="لوحة التحكم">
                                    <span class="dashicons dashicons-external"></span>
                                </a>
                            ` : ''}
                            <button type="button" class="balance-item-refresh" data-model="${model}" title="تحديث">
                                <span class="dashicons dashicons-update"></span>
                            </button>
                        </div>
                    </div>
                `;
            }

            $list.html(html);

            // Update summary
            if (totalUSD > 0) {
                $('.balance-summary').text('$' + totalUSD.toFixed(2));
            } else if (activeCount > 0) {
                $('.balance-summary').text(activeCount + ' نشط');
            } else {
                $('.balance-summary').text('--');
            }

            // Update last update time
            $('.last-update').text(this.formatTime(new Date()));
        },

        /**
         * Show loading state
         */
        showLoading: function() {
            $('.balance-list').html(`
                <div class="balance-loading">
                    <span class="spinner is-active"></span>
                    جاري التحميل...
                </div>
            `);
        },

        /**
         * Show error
         */
        showError: function(message) {
            $('.balance-list').html(`
                <div class="balance-error-state">
                    <span class="error-icon">❌</span>
                    <p>${message}</p>
                    <button type="button" class="balance-refresh aisbp-btn aisbp-btn-sm">
                        إعادة المحاولة
                    </button>
                </div>
            `);
        },

        /**
         * Get status class
         */
        getStatusClass: function(data) {
            if (!data.available) return 'status-error';
            if (data.is_free_tier) return 'status-free';
            if (data.status === 'low') return 'status-warning';
            if (data.status === 'rate_limited') return 'status-warning';
            return 'status-active';
        },

        /**
         * Format balance for display
         */
        formatBalance: function(data) {
            if (!data.available) {
                return '<span class="text-error">غير متاح</span>';
            }
            if (data.is_free_tier) {
                return '<span class="text-free">🆓 مجاني</span>';
            }
            if (data.balance_usd !== null && data.balance_usd !== undefined) {
                return '<span class="text-balance">$' + parseFloat(data.balance_usd).toFixed(2) + '</span>';
            }
            return '<span class="text-active">✅ نشط</span>';
        },

        /**
         * Get model icon
         */
        getModelIcon: function(model) {
            const icons = {
                'deepseek': '🚀',
                'openai': '🧠',
                'gemini': '⚡',
                'claude': '🎯'
            };
            return icons[model] || '🤖';
        },

        /**
         * Format time
         */
        formatTime: function(date) {
            return date.toLocaleTimeString('ar-EG', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    };

    // Initialize on DOM ready
    $(document).ready(function() {
        // Only init if we have the widget container potential
        if ($('.aisbp-page-header, .aisbp-wizard-sidebar').length) {
            AISBPBalance.init();
        }
    });

    // Expose to global
    window.AISBPBalance = AISBPBalance;

})(jQuery);
