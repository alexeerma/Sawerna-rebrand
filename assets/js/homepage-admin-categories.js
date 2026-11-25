/**
 * Page options > Home page: category picker (search, add, remove, reorder).
 * Order of the hidden category_ids[] inputs in the "selected" list is the saved display order.
 */
(function ($) {
  $(function () {
    const picker = $('[data-home-catpicker="1"]');

    if (!picker.length) {
      return;
    }

    const available = picker.find('[data-home-catpicker-available="1"]');
    const selected = picker.find('[data-home-catpicker-selected="1"]');
    const search = picker.find('[data-home-catpicker-search="1"]');
    const empty = picker.find('[data-home-catpicker-empty="1"]');
    const template = document.getElementById('sawerna-home-catpicker-item-template');

    const refreshState = () => {
      const ids = selected.children('[data-term-id]').map(function () {
        return String($(this).attr('data-term-id'));
      }).get();

      available.children('[data-term-id]').each(function () {
        const option = $(this);
        option.find('[data-home-catpicker-add="1"]').prop('disabled', ids.indexOf(String(option.attr('data-term-id'))) !== -1);
      });

      empty.prop('hidden', ids.length > 0);
    };

    search.on('input', function () {
      const query = String($(this).val() || '').trim().toLowerCase();

      available.children('[data-term-id]').each(function () {
        const option = $(this);
        const label = String(option.attr('data-label') || '').toLowerCase();
        option.prop('hidden', query !== '' && label.indexOf(query) === -1);
      });
    });

    available.on('click', '[data-home-catpicker-add="1"]', function () {
      const option = $(this).closest('[data-term-id]');
      const termId = String(option.attr('data-term-id') || '');

      if (!termId || !template || selected.children('[data-term-id="' + termId + '"]').length) {
        return;
      }

      const item = $(template.innerHTML.trim());
      item.attr('data-term-id', termId);
      item.find('.sawerna-home-catpicker__item-label').text(String(option.attr('data-label') || ''));
      item.find('input[type="hidden"]').val(termId);
      selected.append(item);
      refreshState();
    });

    selected.on('click', '[data-home-catpicker-remove="1"]', function () {
      $(this).closest('[data-term-id]').remove();
      refreshState();
    });

    selected.on('click', '[data-home-catpicker-up="1"]', function () {
      const item = $(this).closest('[data-term-id]');
      item.prev('[data-term-id]').before(item);
    });

    selected.on('click', '[data-home-catpicker-down="1"]', function () {
      const item = $(this).closest('[data-term-id]');
      item.next('[data-term-id]').after(item);
    });

    if ($.fn.sortable) {
      selected.sortable({
        items: '> [data-term-id]',
        handle: '.sawerna-home-catpicker__handle',
        axis: 'y',
        placeholder: 'sawerna-home-catpicker__placeholder'
      });
    }

    refreshState();
  });
})(jQuery);
