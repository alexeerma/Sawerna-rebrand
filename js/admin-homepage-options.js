(function ($) {
  $(function () {
    const toggleManualProductsState = () => {
      const checkbox = $('#new_products_use_latest');
      const manualField = $('#new_products_ids').closest('.sawerna-home-options__field');

      if (!checkbox.length || !manualField.length) {
        return;
      }

      manualField.css({
        opacity: checkbox.is(':checked') ? '0.55' : '1',
        pointerEvents: checkbox.is(':checked') ? 'none' : 'auto'
      });
    };

    const toggleManualSaleProductsState = () => {
      const checkbox = $('#sale_products_use_random');
      const manualField = $('#sale_products_ids').closest('.sawerna-home-options__field');

      if (!checkbox.length || !manualField.length) {
        return;
      }

      manualField.css({
        opacity: checkbox.is(':checked') ? '0.55' : '1',
        pointerEvents: checkbox.is(':checked') ? 'none' : 'auto'
      });
    };

    if ($.fn.selectWoo) {
      $('.wc-enhanced-select').filter(':not(.enhanced)').each(function () {
        $(this).selectWoo();
      });
    }

    $(document.body).trigger('wc-enhanced-select-init');
    toggleManualProductsState();
    toggleManualSaleProductsState();
    $('#new_products_use_latest').on('change', toggleManualProductsState);
    $('#sale_products_use_random').on('change', toggleManualSaleProductsState);
  });
})(jQuery);
