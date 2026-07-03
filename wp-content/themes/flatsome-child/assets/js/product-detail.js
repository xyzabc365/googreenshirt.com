(function ($) {
    'use strict';

    function formatAttributeName(name) {
        return String(name || '')
            .replace(/^attribute_/, '')
            .replace(/^pa_/, '')
            .replace(/[_-]+/g, ' ')
            .replace(/\b\w/g, function (letter) {
                return letter.toUpperCase();
            });
    }

    function optionLabel(option) {
        return option.text || option.value || '';
    }

    function syncGroup($select, $group) {
        var currentValue = $select.val() || '';
        var currentText = '';

        $group.find('.cf-variation-pill').each(function () {
            var $pill = $(this);
            var isActive = $pill.data('value') === currentValue;

            $pill.toggleClass('is-active', isActive);
            $pill.attr('aria-pressed', isActive ? 'true' : 'false');

            if (isActive) {
                currentText = $pill.text();
            }
        });

        $group.find('.cf-variation-current').text(currentText);
    }

    function buildVariationSwatches($form) {
        var $variationTable = $form.find('table.variations').first();

        if (!$variationTable.length || $form.data('cfSwatchesReady')) {
            return;
        }

        var $wrap = $('<div class="cf-variation-swatches" />');

        $variationTable.find('select').each(function () {
            var select = this;
            var $select = $(select);
            var labelText = $variationTable.find('label[for="' + select.id + '"]').text() || formatAttributeName(select.name);
            var $group = $('<div class="cf-variation-group" />');
            var $title = $('<div class="cf-variation-title" />');
            var $options = $('<div class="cf-variation-options" role="group" />');

            $title.append($('<span />').text(labelText + ':'));
            $title.append($('<strong class="cf-variation-current" />'));

            $select.find('option').each(function () {
                var option = this;

                if (!option.value) {
                    return;
                }

                var $pill = $('<button class="cf-variation-pill" type="button" aria-pressed="false" />')
                    .text(optionLabel(option))
                    .attr('data-value', option.value)
                    .data('value', option.value);

                if (option.disabled) {
                    $pill.prop('disabled', true);
                }

                $pill.on('click', function () {
                    $select.val(option.value).trigger('change');
                    syncGroup($select, $group);
                });

                $options.append($pill);
            });

            $group.append($title, $options);
            $wrap.append($group);

            $select.on('change', function () {
                syncGroup($select, $group);
            });
        });

        if (!$wrap.children().length) {
            return;
        }

        $variationTable.before($wrap);
        $form.addClass('cf-variation-swatches-ready').data('cfSwatchesReady', true);

        $form.find('select').each(function () {
            var $select = $(this);
            var $group = $wrap.find('.cf-variation-group').eq($form.find('table.variations select').index(this));
            syncGroup($select, $group);
        });

        $form.on('reset_data', function () {
            window.setTimeout(function () {
                $form.find('select').each(function () {
                    var $select = $(this);
                    var $group = $wrap.find('.cf-variation-group').eq($form.find('table.variations select').index(this));
                    syncGroup($select, $group);
                });
            }, 0);
        });
    }

    function addBuyNowButtons($form) {
        if ($form.find('.cf-buy-now-button').length) {
            return;
        }

        var $cartButton = $form.find('.single_add_to_cart_button').first();

        if (!$cartButton.length) {
            return;
        }

        var $buyNow = $('<button class="button alt cf-buy-now-button" type="submit">Buy Now</button>');
        var cartButtonName = $cartButton.attr('name');
        var cartButtonValue = $cartButton.attr('value');

        if (cartButtonName) {
            $buyNow.attr('name', cartButtonName);
        }

        if (cartButtonValue) {
            $buyNow.attr('value', cartButtonValue);
        }

        $cartButton.on('click', function () {
            $form.find('input[name="cf_buy_now"]').remove();
        });

        $buyNow.on('click', function () {
            $form.find('input[name="cf_buy_now"]').remove();
            $form.append('<input type="hidden" name="cf_buy_now" value="1">');
        });

        $cartButton.after($buyNow);

        var syncDisabled = function () {
            $buyNow.prop('disabled', $cartButton.prop('disabled') || $cartButton.hasClass('disabled'));
        };

        syncDisabled();
        $form.on('change found_variation show_variation hide_variation reset_data', syncDisabled);

        if (window.MutationObserver) {
            new MutationObserver(syncDisabled).observe($cartButton.get(0), {
                attributes: true,
                attributeFilter: ['class', 'disabled']
            });
        }
    }

    $(function () {
        $('.cf-product-cart form.cart').each(function () {
            var $form = $(this);

            buildVariationSwatches($form);
            addBuyNowButtons($form);
        });
    });
})(jQuery);
