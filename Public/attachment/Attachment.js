var Attachment = window.Attachment = (function(window) {

    var that = this;

    this.Create = function(name, options) {
        if (!typeof $().emulateTransitionEnd == 'function') {
            throw 'bootstrap3 has not been loaded';
        }

        return new that[name](options);
    }

    this.Opener = function(options) {
        this.modalId = options.modalId;
        this.iframeSrc = options.src;
        this.pickedCallback = options.pickedCallback;
        this.iframe;
        this.$modal;
        this.initIframe();
        this.appendModal();
    };

    this.Opener.prototype = {

        pickedCallback: null,

        iframeSrc: null,

        initIframe: function() {
            var instance = this;
            this.iframe = document.createElement('iframe');
            this.iframe.style = 'width:100%;height:100%;border:none';
            this.iframe.addEventListener('load', function() {
                instance.iframeOnLoad.apply(instance, [this]);
            });
        },

        iframeOnLoad: function(iframe) {
            if (iframe.contentWindow.Attachment !== undefined) {
                iframe.contentWindow.Attachment.fromOpener = this;
            }
        },
    
        appendModal: function() {
            var instance = this;
            var html = '<div class="modal fade" tabindex="-1" role="dialog" id="' + this.modalId + '">';
                    html += '<div class="modal-dialog modal-lg" role="document">';
                        html += '<div class="modal-content">';
                            html += '<div class="modal-header">';
                                html += '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
                                html += '<h4 class="modal-title">附件管理</h4>';
                            html += '</div>';
                        html += '<div class="modal-body"></div>';
                    html += '</div>';
                html += '</div>';
            var $html = $(html);
            $('body').append($html);
            this.$modal = $('#' + this.modalId);
            this.$modal.on('hidden.bs.modal', function(e) {
                instance.$modal.remove();
                instance.iframe.removeEventListener('load', instance.iframeOnLoad);
                delete instance;
            });
            this.open(this.iframeSrc);
        },

        open: function(iframeSrc) {
            this.iframe.src = iframeSrc;
            this.$modal.find('.modal-body').append(this.iframe);
            this.$modal.find('.modal-body').css('height', $(window).height() * 0.7 + 'px');
            this.$modal.modal('show');
        },

        close: function() {
            this.$modal.modal('hide');
        }
    };

    this.Picker = function(options) {
        var instance = this;

        this.events = {
            'picked': [],
            'itemClicked': [instance.itemClicked]
        };

        this.$items = options.$items;
        this.$pickerBtn = options.$pickerBtn;
        this.mode = options.mode === undefined ? 'single' : options.mode;
        this.$selectedItems = [];
        this.pickedDataset = [];
        this.initItemsListener();
        this.initPickerBtnListener();
    };

    this.Picker.prototype = {
        on: function(event, listener) {
            if (this.events[event] !== undefined) {
                this.events[event].push(listener);
            }
        },

        dispatch: function(event, args) {
            var lastReturn;
            if (this.events[event] !== undefined) {
                for (var i in this.events[event]) {
                    lastReturn = this.events[event][i].apply(this, args);
                }
            }

            return lastReturn;
        },

        initItemsListener: function() {
            var instance = this;
            this.$items.on('click', function() {
                if (instance.mode == 'single') {
                    instance.choiceItem(instance.$items, $(this));
                } else {
                    instance.toggleItem($(this));
                }
            });
        },

        choiceItem: function($items, $item) {
            $items.removeClass('selected');
            $item.addClass('selected');
            this.dispatch('itemClicked');
        },

        toggleItem: function($item) {
            if (!$item.hasClass('selected')) {
                $item.addClass('selected');
            } else {
                $item.removeClass('selected');
            }

            this.dispatch('itemClicked');
        },

        itemClicked: function() {
            this.$selectedItems = this.$items.filter('.selected');

            if (this.$selectedItems.length > 0) {
                this.$pickerBtn.show();
            } else {
                this.$pickerBtn.hide();
            }
        },

        initPickerBtnListener: function() {
            var instance = this;
            var pickedDataset;
            this.$pickerBtn.on('click', function() {
                if (instance.$selectedItems.length > 0) {
                    pickedDataset = instance.dispatch('picked', [instance.$selectedItems]);
                }

                if (
                    Attachment.fromOpener !== null
                    && Attachment.fromOpener.pickedCallback !== null
                ) {
                    if (instance.mode == 'single') {
                        Attachment.fromOpener.pickedCallback.apply(null, [pickedDataset[0]]);
                    } else {
                        Attachment.fromOpener.pickedCallback.apply(null, [pickedDataset]);
                    }
                    Attachment.fromOpener.close();
                }
            });
        }
    };

    return {
        Create: Create,
        fromOpener: null
    };
})(window);