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

    function findOptionByValue($select, value) {
        return $select.find('option').filter(function () {
            return this.value === value;
        }).get(0);
    }

    function syncGroup($select, $group) {
        var currentValue = $select.val() || '';
        var currentText = '';

        $group.find('.cf-variation-pill').each(function () {
            var $pill = $(this);
            var isActive = $pill.data('value') === currentValue;
            var option = findOptionByValue($select, $pill.data('value'));
            var isAvailable = option && !option.disabled;

            $pill.toggleClass('is-hidden', !isAvailable);
            $pill.prop('disabled', !isAvailable);
            $pill.attr('aria-hidden', isAvailable ? 'false' : 'true');

            $pill.toggleClass('is-active', isActive && isAvailable);
            $pill.attr('aria-pressed', isActive && isAvailable ? 'true' : 'false');

            if (isActive && isAvailable) {
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
        var syncAllGroups = function () {
            $form.find('table.variations select').each(function () {
                var $select = $(this);
                var $group = $wrap.find('.cf-variation-group').eq($form.find('table.variations select').index(this));
                syncGroup($select, $group);
            });
        };

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

        syncAllGroups();

        $form.on('woocommerce_update_variation_values reset_data', function () {
            window.setTimeout(function () {
                syncAllGroups();
            }, 0);
        });
    }

    function productScope($form) {
        var $scope = $form.closest('.product');

        if (!$scope.length) {
            $scope = $form.closest('.cf-product-page');
        }

        return $scope.length ? $scope : $(document);
    }

    function variationPriceHtml(variation) {
        if (!variation || !variation.price_html) {
            return '';
        }

        var $price = $('<div />').html(variation.price_html).find('.price').first();

        return $price.length ? $price.html() : variation.price_html;
    }

    function bindVariationPriceSync($form) {
        if ($form.data('cfVariationPriceSyncReady')) {
            return;
        }

        var $price = productScope($form).find('.cf-product-price .price').first();

        if (!$price.length) {
            return;
        }

        $form.data('cfVariationPriceSyncReady', true);
        $price.data('cfOriginalPriceHtml', $price.html());

        $form.on('found_variation show_variation', function (event, variation) {
            var priceHtml = variationPriceHtml(variation);

            if (priceHtml) {
                $price.html(priceHtml);
            }
        });

        $form.on('reset_data hide_variation', function () {
            var originalPriceHtml = $price.data('cfOriginalPriceHtml');

            if (originalPriceHtml) {
                $price.html(originalPriceHtml);
            }
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
            bindVariationPriceSync($form);
            addBuyNowButtons($form);
        });
    });
})(jQuery);
