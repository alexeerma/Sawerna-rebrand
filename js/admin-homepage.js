(function ($) {
  const bindImageControls = (context) => {
    context.find('[data-home-image-control="1"]').each(function () {
      const control = $(this);

      control.find('[data-home-image-upload="1"]').off('click').on('click', function () {
        const frame = wp.media({
          title: 'Select image',
          button: { text: 'Use image' },
          multiple: false
        });

        frame.on('select', function () {
          const attachment = frame.state().get('selection').first().toJSON();
          control.find('[data-home-image-id="1"]').val(attachment.id);
          control.find('[data-home-image-preview="1"]').html(`<img src="${attachment.url}" alt="">`).prop('hidden', false);
          control.find('[data-home-image-remove="1"]').prop('hidden', false);
        });

        frame.open();
      });

      control.find('[data-home-image-remove="1"]').off('click').on('click', function () {
        control.find('[data-home-image-id="1"]').val('');
        control.find('[data-home-image-preview="1"]').empty().prop('hidden', true);
        $(this).prop('hidden', true);
      });
    });
  };

  const bindRemoveButtons = (root) => {
    root.find('[data-home-slide-remove="1"]').off('click').on('click', function () {
      $(this).closest('[data-home-slide-item="1"]').remove();
    });
  };

  const nextIndexForRoot = (root) => root.find('[data-home-slide-item="1"]').length;

  $(function () {
    const roots = $('[data-home-slides-root="1"]');

    bindImageControls($(document));

    if (!roots.length) {
      return;
    }

    $('[data-home-slide-add="1"]').on('click', function () {
      const button = $(this);
      const rootId = button.attr('data-home-slides-root-target');
      const templateId = button.attr('data-home-template-id');
      const root = rootId ? $('#' + rootId) : $();
      const template = templateId ? document.getElementById(templateId) : null;

      if (!root.length || !template) {
        return;
      }

      const html = template.innerHTML.replace(/__INDEX__/g, String(nextIndexForRoot(root)));
      const slide = $(html);
      root.append(slide);
      bindRemoveButtons(slide);
      bindImageControls(slide);
    });

    roots.each(function () {
      const root = $(this);
      bindRemoveButtons(root);
      bindImageControls(root);
    });
  });
})(jQuery);
